<?php

namespace FluentForm\App\Services\Transfer;

use Exception;
use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\Form;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Models\Submission;
use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Foundation\App;
use FluentForm\Framework\Request\File;
use FluentForm\Framework\Support\Arr;

class TransferService
{
    public static function exportForms($formIds)
    {
        $result = Form::with(['formMeta'])
            ->whereIn('id', $formIds)
            ->get();
        $forms = [];
        foreach ($result as $item) {
            $form = json_decode($item);
            $formMetaFiltered = array_filter($form->form_meta, function ($item) {
                return ($item->meta_key !== '_total_views');
            });
            $form->metas = $formMetaFiltered;
            $form->form_fields = json_decode($form->form_fields);
            $forms[] = $form;
        }
    
        $fileName = 'fluentform-export-forms-' . count($forms) . '-' . date('d-m-Y') . '.json';

        header('Content-disposition: attachment; filename=' . $fileName);

        header('Content-type: application/json');

        echo json_encode(array_values($forms)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $forms is escaped before being passed in.

        die();
    }

    /**
     * @throws Exception
     */
    public static function importForms($file)
    {
        if ($file instanceof File) {
            $forms = \json_decode($file->getContents(), true);
            $insertedForms = [];
            if ($forms && is_array($forms)) {
                foreach ($forms as $formItem) {
                    $formFields = json_encode([]);
                    if ($fields = Arr::get($formItem, 'form', '')) {
                        $formFields = json_encode($fields);
                    } elseif ($fields = Arr::get($formItem, 'form_fields', '')) {
                        $formFields = json_encode($fields);
                    } else {
                        throw new Exception(__('You have a faulty JSON file, please export the Fluent Forms again.', 'fluentform'));
                    }

                    $form = [
                        'title'       => Arr::get($formItem, 'title'),
                        'form_fields' => $formFields,
                        'status'      => Arr::get($formItem, 'status', 'published'),
                        'has_payment' => Arr::get($formItem, 'has_payment', 0),
                        'type'        => Arr::get($formItem, 'type', 'form'),
                        'created_by'  => get_current_user_id(),
                    ];

                    if (Arr::get($formItem, 'conditions')) {
                        $form['conditions'] = Arr::get($formItem, 'conditions');
                    }

                    if (isset($formItem['appearance_settings'])) {
                        $form['appearance_settings'] = Arr::get($formItem, 'appearance_settings');
                    }

                    $formId = Form::insertGetId($form);
                    $insertedForms[$formId] = [
                        'title'    => $form['title'],
                        'edit_url' => admin_url('admin.php?page=fluent_forms&route=editor&form_id=' . $formId),
                    ];

                    if (isset($formItem['metas'])) {
                        foreach ($formItem['metas'] as $metaData) {
                            $metaKey = Arr::get($metaData, 'meta_key');
                            $metaValue = Arr::get($metaData, 'value');
                            if ("ffc_form_settings_generated_css" == $metaKey || "ffc_form_settings_meta" == $metaKey) {
                                $metaValue = str_replace('ff_conv_app_' . Arr::get($formItem, 'id'), 'ff_conv_app_' . $formId, $metaValue);
                            }
                            $settings = [
                                'form_id'  => $formId,
                                'meta_key' => $metaKey,
                                'value'    => $metaValue,
                            ];
                            FormMeta::insert($settings);
                        }
                    } else {
                        $oldKeys = [
                            'formSettings',
                            'notifications',
                            'mailchimp_feeds',
                            'slack',
                        ];
                        foreach ($oldKeys as $key) {
                            if (isset($formItem[$key])) {
                                FormMeta::persist($formId, $key, json_encode(Arr::get($formItem, $key)));
                            }
                        }
                    }
                    do_action_deprecated(
                        'fluentform_form_imported',
                        [
                            $formId
                        ],
                        FLUENTFORM_FRAMEWORK_UPGRADE,
                        'fluentform/form_imported',
                        'Use fluentform/form_imported instead of fluentform_form_imported.'
                    );
                    do_action('fluentform/form_imported', $formId);
                }

                return ([
                    'message'        => __('You form has been successfully imported.', 'fluentform'),
                    'inserted_forms' => $insertedForms,
                ]);
            }
        }
        throw new Exception(__('You have a faulty JSON file, please export the Fluent Forms again.', 'fluentform'));
    }

    public static function exportEntries($args)
    {
        if (!defined('FLUENTFORM_EXPORTING_ENTRIES')) {
            define('FLUENTFORM_EXPORTING_ENTRIES', true);
        }
        $formId = (int)Arr::get($args, 'form_id');
        try {
            $form = Form::findOrFail($formId);
        } catch (Exception $e) {
            exit('No Form Found');
        }
        $type = sanitize_key(Arr::get($args, 'format', 'csv'));
        if (!in_array($type, ['csv', 'ods', 'xlsx', 'json'])) {
            exit('Invalid requested format');
        }
        if ('json' == $type) {
            self::exportAsJSON($form, $args);
        }
        if (!defined('FLUENTFORM_DOING_CSV_EXPORT')) {
            define('FLUENTFORM_DOING_CSV_EXPORT', true);
        }
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);
        $inputLabels = FormFieldsParser::getAdminLabels($form, $formInputs);
        $selectedLabels = Arr::get($args,'fields_to_export');
        $selectedLabels = fluentFormSanitizer($selectedLabels);
       
        //filter out unselected fields
        foreach ($inputLabels as $key => $value) {
            if (!in_array($key,$selectedLabels) && isset($inputLabels[$key])) {
                unset($inputLabels[$key]); // Remove the element with the specified key
            }
        }
    
        $submissions = self::getSubmissions($args);
        $submissions = FormDataParser::parseFormEntries($submissions, $form, $formInputs);
        $parsedShortCodes = [];
        $exportData = [];
        foreach ($submissions as $submission) {

            $submission->response = json_decode($submission->response, true);
           
            $temp = [];
            foreach ($inputLabels as $field => $label) {
                
                //format tabular grid data for CSV/XLSV/ODS export
                if (isset($formInputs[$field]['element']) && "tabular_grid" === $formInputs[$field]['element']) {
                    $gridRawData = Arr::get($submission->response, $field);
                    $content = Helper::getTabularGridFormatValue($gridRawData, Arr::get($formInputs, $field), ' | ');
                } else {
                    $content = trim(wp_strip_all_tags(FormDataParser::formatValue(
                                Arr::get($submission->user_inputs, $field)))
                    );
                }
                $temp[] = Helper::sanitizeForCSV($content);
            }
    
            if($selectedShortcodes = Arr::get($args,'shortcodes_to_export')){
                $selectedShortcodes = fluentFormSanitizer($selectedShortcodes);
                $parsedShortCodes = ShortCodeParser::parse(
                    $selectedShortcodes,
                    $submission->id,
                    $submission->response,
                    $form,
                    false,
                    true
                );
                if(!empty($parsedShortCodes)){
                    foreach ($parsedShortCodes as $code){
                        $temp[] = Arr::get($code,'value');
                    }
                }
            }
            
            $exportData[] = $temp;
        }

        $extraLabels = [];

        if(!empty($parsedShortCodes)){
            foreach ($parsedShortCodes as $code){
                $extraLabels[] = Arr::get($code,'label');
            }
        }
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
            'Use fluentform/export_data instead of fluentform_export_data'
        );
        $data = apply_filters('fluentform/export_data', $data, $form, $exportData, $inputLabels);
        $fileName = sanitize_title($form->title, 'export', 'view') . '-' . date('Y-m-d');
        self::downloadOfficeDoc($data, $type, $fileName);
    }

    private static function exportAsJSON($form, $args)
    {
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);
        $submissions = self::getSubmissions($args);
        $submissions = FormDataParser::parseFormEntries($submissions, $form, $formInputs);
        foreach ($submissions as $submission) {
            $submission->response = json_decode($submission->response, true);
        }
        header('Content-disposition: attachment; filename=' . sanitize_title($form->title, 'export', 'view') . '-' . date('Y-m-d') . '.json');
        header('Content-type: application/json');
        echo json_encode($submissions); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $submissions is escaped before being passed in.
        exit();
    }

    private static function getSubmissions($args)
    {
        $query = (new Submission)->customQuery($args);
        $entries = fluentFormSanitizer(Arr::get($args, 'entries', []));
        $query->when(is_array($entries) && (count($entries) > 0), function ($q) use ($entries) {
            return $q->whereIn('id', $entries);
        });
        return $query->get();
    }

    private static function downloadOfficeDoc($data, $type = 'csv', $fileName = null)
    {
        $data = array_map(function ($item) {
            return array_map(function ($itemValue) {
                if (is_array($itemValue)) {
                    return implode(', ', $itemValue);
                }
                return $itemValue;
            }, $item);
        }, $data);
        require_once (App::getInstance())->make('path.app') . '/Services/Spout/Autoloader/autoload.php';
        $fileName = ($fileName) ? $fileName . '.' . $type : 'export-data-' . date('d-m-Y') . '.' . $type;
        $writer = \Box\Spout\Writer\WriterFactory::create($type);
        $writer->openToBrowser($fileName);
        $writer->addRows($data);
        $writer->close();
        die();
    }

}
