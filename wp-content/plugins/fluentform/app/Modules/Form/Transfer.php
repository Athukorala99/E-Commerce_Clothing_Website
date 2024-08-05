<?php

namespace FluentForm\App\Modules\Form;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\Framework\Request\File;

/* @deprecated Current File FluentForm\App\Http\Controllers\TransferController */

class Transfer
{
    /**
     * Request object
     *
     * @var \FluentForm\Framework\Request\Request $request
     */
    protected $request;

    /**
     * Transfer constructor.
     *
     * @param \FluentForm\Framework\Foundation\Application $application
     */
    public function __construct(Application $application)
    {
        $this->request = $application->make('request');
    }

    /**
     * Export forms as JSON.
     */
    public function export()
    {
        // Get the IDs of the forms the user wants to be exported.
        $formIds = $this->request->get('forms');

        // Load the forms for a given form IDs and the settings from the DB.
        $result = wpFluent()
            ->table('fluentform_forms')
            ->whereIn('id', $formIds)
            ->get();

        // Prepare the loaded query results to form and it's settings objects.
        $forms = [];
        foreach ($result as $item) {
            $form = $item;
            $form->form_fields = json_decode($form->form_fields);
            $form->metas = $this->getFormMetas($item->id);
            $forms[] = $form;
        }

        $fileName = 'fluentform-export-forms-' . count($forms) . '-' . date('d-m-Y') . '.json';

        header('Content-disposition: attachment; filename=' . $fileName);

        header('Content-type: application/json');

        echo json_encode(array_values($forms)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $forms is escaped before being passed in.

        die();
    }

    /**
     * Import forms from a previously exported JSON file.
     */
    public function import()
    {
        $file = $this->request->file('file');

        if ($file instanceof File) {
            $forms = \json_decode($file->getContents(), true);
            $insertedForms = [];
            if ($forms && is_array($forms)) {
                foreach ($forms as $formItem) {
                    // First of all make the form object.
                    $formFields = json_encode([]);
                    if ($fields = ArrayHelper::get($formItem, 'form', '')) {
                        $formFields = json_encode($fields);
                    } elseif ($fields = ArrayHelper::get($formItem, 'form_fields', '')) {
                        $formFields = json_encode($fields);
                    } else {
                        wp_send_json([
                            'message' => __('You have a faulty JSON file, please export the Fluent Forms again.', 'fluentform'),
                        ], 422);
                    }

                    $form = [
                        'title'       => ArrayHelper::get($formItem, 'title'),
                        'form_fields' => $formFields,
                        'status'      => ArrayHelper::get($formItem, 'status', 'published'),
                        'has_payment' => ArrayHelper::get($formItem, 'has_payment', 0),
                        'type'        => ArrayHelper::get($formItem, 'type', 'form'),
                        'created_by'  => get_current_user_id(),
                    ];

                    if (ArrayHelper::get($formItem, 'conditions')) {
                        $form['conditions'] = ArrayHelper::get($formItem, 'conditions');
                    }

                    if (isset($formItem['appearance_settings'])) {
                        $form['appearance_settings'] = $formItem['appearance_settings'];
                    }

                    // Insert the form to the DB.
                    $formId = wpFluent()->table('fluentform_forms')->insertGetId($form);

                    $insertedForms[$formId] = [
                        'title'    => $form['title'],
                        'edit_url' => admin_url('admin.php?page=fluent_forms&route=editor&form_id=' . $formId),
                    ];

                    if (isset($formItem['metas'])) {
                        foreach ($formItem['metas'] as $metaData) {
                            $settings = [
                                'form_id'  => $formId,
                                'meta_key' => $metaData['meta_key'],
                                'value'    => $metaData['value'],
                            ];
                            wpFluent()->table('fluentform_form_meta')->insert($settings);
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
                                $settings = [
                                    'form_id'  => $formId,
                                    'meta_key' => $key,
                                    'value'    => json_encode($formItem[$key]),
                                ];
                                wpFluent()->table('fluentform_form_meta')->insert($settings);
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

                wp_send_json([
                    'message'        => __('You form has been successfully imported.', 'fluentform'),
                    'inserted_forms' => $insertedForms,
                ], 200);
            }
        }

        wp_send_json([
            'message' => __('You have a faulty JSON file, please export the Fluent Forms again.', 'fluentform'),
        ], 422);
    }

    public function getFormMetas($formId)
    {
        return wpFluent()
            ->table('fluentform_form_meta')
            ->select(['meta_key', 'value'])
            ->where('form_id', $formId)
            ->whereNotIn('meta_key', ['_total_views', '_ff_form_styler_css'])
            ->get();
    }
}
