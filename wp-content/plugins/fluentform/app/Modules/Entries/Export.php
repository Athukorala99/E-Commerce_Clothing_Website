<?php

namespace FluentForm\App\Modules\Entries;

use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentForm\App\Helpers\Helper;

class Export
{
    /**
     * App instance
     *
     * @var \FluentForm\Framework\Foundation\Application
     */
    protected $app;

    /**
     * Request object
     *
     * @var \FluentForm\Framework\Request\Request
     */
    protected $request;

    /**
     * Table name
     *
     * @var String table/data source name
     */
    protected $tableName;

    /**
     * Export constructor.
     *
     * @param \FluentForm\Framework\Foundation\Application $application
     */
    public function __construct(Application $application, $tableName = 'fluentform_submissions')
    {
        $this->app = $application;
        $this->request = $application->request;
        $this->tableName = $tableName;
    }

    /**
     * Only used exports form partial entries
     *
     * @todo:: refactor.
     */
    public function index()
    {
        if (!defined('FLUENTFORM_EXPORTING_ENTRIES')) {
            define('FLUENTFORM_EXPORTING_ENTRIES', true);
        }

        $formId = intval($this->request->get('form_id'));

        $form = wpFluent()->table('fluentform_forms')->find($formId);

        if (!$form) {
            exit('No Form Found');
        }

        $type = sanitize_key($this->request->get('format', 'csv'));
        if (!in_array($type, ['csv', 'ods', 'xlsx', 'json'])) {
            exit('Invalid requested format');
        }

        if ('json' == $type) {
            $this->exportAsJSON($form);
        }

        if (!defined('FLUENTFORM_DOING_CSV_EXPORT')) {
            define('FLUENTFORM_DOING_CSV_EXPORT', true);
        }

        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);

        $inputLabels = FormFieldsParser::getAdminLabels($form, $formInputs);

        $submissions = $this->getSubmissions($formId);

        $submissions = FormDataParser::parseFormEntries($submissions, $form, $formInputs);
        $exportData = [];

        foreach ($submissions as $submission) {
            $submission->response = json_decode($submission->response, true);
            $temp = [];
            foreach ($inputLabels as $field => $label) {
                // format tabular grid data for CSV/XLSV/ODS export
                if (isset($formInputs[$field]['element']) && "tabular_grid" === $formInputs[$field]['element']) {
                    $gridRawData = Arr::get($submission->response, $field);
                    $content = Helper::getTabularGridFormatValue($gridRawData, Arr::get($formInputs, $field), ' | ');
                } else {
                    $content = trim(
                        wp_strip_all_tags(
                            FormDataParser::formatValue(
                                Arr::get($submission->user_inputs, $field)
                            )
                        )
                    );
                }
                $temp[] = Helper::sanitizeForCSV($content);
            }

            if ($form->has_payment && 'fluentform_submissions' == $this->tableName) {
                $temp[] = round($submission->payment_total / 100, 1);
                $temp[] = $submission->payment_status;
                $temp[] = $submission->currency;
            }

            $temp[] = @$submission->id;
            $temp[] = @$submission->status;
            $temp[] = @$submission->created_at;

            $exportData[] = $temp;
        }

        $extraLabels = [];
        if ($form->has_payment && 'fluentform_submissions' == $this->tableName) {
            $extraLabels[] = 'payment_total';
            $extraLabels[] = 'payment_status';
            $extraLabels[] = 'currency';
        }

        $extraLabels[] = 'entry_id';
        $extraLabels[] = 'entry_status';
        $extraLabels[] = 'created_at';

        $inputLabels = array_merge($inputLabels, $extraLabels);

        $data = array_merge([array_values($inputLabels)], $exportData);
    
        $data = apply_filters_deprecated(
            'fluentform_export_data',
            [
                $data,
                $form,
                $exportData,
                $inputLabels
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/export_data',
            'Use fluentform/export_data instead of fluentform_export_data.'
        );

        $data = apply_filters('fluentform/export_data', $data, $form, $exportData, $inputLabels);

        $fileName = sanitize_title($form->title, 'export', 'view') . '-' . date('Y-m-d');

        $this->downloadOfficeDoc($data, $type, $fileName);
    }

    private function downloadOfficeDoc($data, $type = 'csv', $fileName = null)
    {
        $data = array_map(function ($item) {
            return array_map(function ($itemValue) {
                if (is_array($itemValue)) {
                    return implode(', ', $itemValue);
                }
                return $itemValue;
            }, $item);
        }, $data);
        require_once $this->app->make('path.app') . '/Services/Spout/Autoloader/autoload.php';
        $fileName = ($fileName) ? $fileName . '.' . $type : 'export-data-' . date('d-m-Y') . '.' . $type;
        $writer = \Box\Spout\Writer\WriterFactory::create($type);
        $writer->openToBrowser($fileName);
        $writer->addRows($data);
        $writer->close();
        die();
    }

    private function exportAsJSON($form)
    {
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);

        $inputLabels = FormFieldsParser::getAdminLabels($form, $formInputs);

        $submissions = $this->getSubmissions($form->id);

        $submissions = FormDataParser::parseFormEntries($submissions, $form, $formInputs);
        $exportData = [];

        foreach ($submissions as $submission) {
            $submission->response = json_decode($submission->response, true);
        }

        header('Content-disposition: attachment; filename=' . sanitize_title($form->title, 'export', 'view') . '-' . date('Y-m-d') . '.json');
        header('Content-type: application/json');
        echo json_encode($submissions); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $submissions is escaped before being passed in.
        exit();
    }

    private function getSubmissions($formId)
    {
        $query = wpFluent()->table($this->tableName)
            ->where('form_id', $formId)
            ->orderBy('id', $this->request->get('sort_by', 'DESC'));

        if ('fluentform_submissions' == $this->tableName) {
            $dateRange = $this->request->get('date_range');
            if ($dateRange) {
                $query->where('created_at', '>=', $dateRange[0] . ' 00:00:01');
                $query->where('created_at', '<=', $dateRange[1] . ' 23:59:59');
            }

            $isFavourite = $this->request->get('is_favourite');

            if ('yes' == $isFavourite) {
                $query->where('is_favourite', '1');
            }

            $status = $this->request->get('entry_type');

            if ('trashed' == $status) {
                $query->where('status', 'trashed');
            } elseif ($status && 'all' != $status) {
                $query->where('status', $status);
            } else {
                $query->where('status', '!=', 'trashed');
            }
            $entries = fluentFormSanitizer($this->request->get('entries', []));

            if (is_array($entries) && (count($entries) > 0)) {
                $query->whereIn('id', $entries);
            }

            if ($paymentStatuses = $this->request->get('payment_statuses')) {
                if (is_array($paymentStatuses)) {
                    $query->whereIn('payment_status', $paymentStatuses);
                }
            }
        }

        $searchString = $this->request->get('search');

        if ($searchString) {
            $query->where(function ($q) use ($searchString) {
                $q->where('id', 'LIKE', "%{$searchString}%")
                    ->orWhere('response', 'LIKE', "%{$searchString}%");

                if ('fluentform_submissions' == $this->tableName) {
                    $q->orWhere('status', 'LIKE', "%{$searchString}%")
                        ->orWhere('created_at', 'LIKE', "%{$searchString}%");
                }
            });
        }

        return $query->get();
    }
}
