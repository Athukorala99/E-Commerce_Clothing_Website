<?php

namespace FluentForm\App\Modules\Form;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;

class Form
{
    /**
     * Request object
     *
     * @var \FluentForm\Framework\Request\Request $request
     */
    protected $request;

    /**
     * Set this value when we need predefined default settings.
     *
     * @var array $defaultSettings
     */
    protected $defaultSettings;

    /**
     * Set this value when we need predefined default notifications.
     *
     * @var array $defaultNotifications
     */
    protected $defaultNotifications;

    /**
     * Set this value when we need predefined form fields.
     *
     * @var array $formFields
     */
    protected $formFields;

    protected $metas = [];

    protected $formType = 'form';

    protected $hasPayment = 0;
    /**
     * @var \FluentForm\Framework\Database\Query\Builder
     */
    protected $model = null;

    /**
     * Form constructor.
     *
     * @param \FluentForm\Framework\Foundation\Application $application
     */
    public function __construct(Application $application)
    {
        $this->request = $application->request;
        $this->model = wpFluent()->table('fluentform_forms');
    }

    /**
     * Get all forms from database
     */
    public function index()
    {
        $forms = fluentFormApi('forms')->forms([
            'search'      => $this->request->get('search'),
            'status'      => $this->request->get('status'),
            'filter_by'   => $this->request->get('filter_by', 'all'),
            'date_range'  => $this->request->get('date_range', []),
            'sort_column' => $this->request->get('sort_column', 'id'),
            'sort_by'     => $this->request->get('sort_by', 'DESC'),
            'per_page'    => $this->request->get('per_page', 10),
            'page'        => $this->request->get('page', 1),
        ]);

        wp_send_json($forms, 200);
    }

    /**
     * Create a form from backend/editor
     *
     * @return void|array
     */
    public function store($returnJSON = true)
    {
        $type = $this->request->get('type', $this->formType);
        $title = $this->request->get('title', 'My New Form');
        $status = $this->request->get('status', 'published');
        $createdBy = get_current_user_id();

        $now = current_time('mysql');

        $insertData = [
            'title'      => $title,
            'type'       => $type,
            'status'     => $status,
            'created_by' => $createdBy,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($this->formFields) {
            $insertData['form_fields'] = $this->formFields;
        }

        if ($this->hasPayment) {
            $insertData['has_payment'] = $this->hasPayment;
        }

        $formId = $this->model->insertGetId($insertData);

        // Rename the form name  here
        wpFluent()->table('fluentform_forms')->where('id', $formId)->update([
            'title' => $title . ' (#' . $formId . ')',
        ]);

        if ($this->metas && is_array($this->metas)) {
            foreach ($this->metas as $meta) {
                $meta['value'] = trim(preg_replace('/\s+/', ' ', $meta['value']));

                wpFluent()->table('fluentform_form_meta')
                    ->insert([
                        'form_id'  => $formId,
                        'meta_key' => $meta['meta_key'],
                        'value'    => $meta['value'],
                    ]);
            }
        } else {
            // add default form settings now
            $defaultSettings = $this->defaultSettings ?: $this->getFormsDefaultSettings($formId);
    
            $defaultSettings = apply_filters_deprecated(
                'fluentform_create_default_settings',
                [
                    $defaultSettings
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/create_default_settings',
                'Use fluentform/create_default_settings instead of fluentform_create_default_settings.'
            );

            $defaultSettings = apply_filters('fluentform/create_default_settings', $defaultSettings);

            wpFluent()->table('fluentform_form_meta')
                ->insert([
                    'form_id'  => $formId,
                    'meta_key' => 'formSettings',
                    'value'    => json_encode($defaultSettings),
                ]);

            if ($this->defaultNotifications) {
                wpFluent()->table('fluentform_form_meta')
                    ->insert([
                        'form_id'  => $formId,
                        'meta_key' => 'notifications',
                        'value'    => json_encode($this->defaultNotifications),
                    ]);
            }
        }

        do_action_deprecated(
            'fluentform_inserted_new_form',
            [
                $formId,
                $insertData
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/inserted_new_form',
            'Use fluentform/inserted_new_form instead of fluentform_inserted_new_form.'
        );

        do_action('fluentform/inserted_new_form', $formId, $insertData);

        $data = [
            'formId'       => $formId,
            'redirect_url' => admin_url('admin.php?page=fluent_forms&form_id=' . $formId . '&route=editor'),
            'message'      => __('Successfully created a form.', 'fluentform'),
        ];

        if ($returnJSON) {
            wp_send_json_success($data, 200);
        }

        return $data;
    }

    public function getFormsDefaultSettings($formId = false)
    {
        $defaultSettings = [
            'confirmation' => [
                'redirectTo'           => 'samePage',
                'messageToShow'        => __('Thank you for your message. We will get in touch with you shortly', 'fluentform'),
                'customPage'           => null,
                'samePageFormBehavior' => 'hide_form',
                'customUrl'            => null,
            ],
            'restrictions' => [
                'limitNumberOfEntries' => [
                    'enabled'         => false,
                    'numberOfEntries' => null,
                    'period'          => 'total',
                    'limitReachedMsg' => 'Maximum number of entries exceeded.',
                ],
                'scheduleForm' => [
                    'enabled'      => false,
                    'start'        => null,
                    'end'          => null,
                    'selectedDays' => null,
                    'pendingMsg'   => __('Form submission is not started yet.', 'fluentform'),
                    'expiredMsg'   => __('Form submission is now closed.', 'fluentform'),
                ],
                'requireLogin' => [
                    'enabled'         => false,
                    'requireLoginMsg' => 'You must be logged in to submit the form.',
                ],
                'denyEmptySubmission' => [
                    'enabled' => false,
                    'message' => __('Sorry, you cannot submit an empty form. Let\'s hear what you wanna say.', 'fluentform'),
                ],
            ],
            'layout' => [
                'labelPlacement'        => 'top',
                'helpMessagePlacement'  => 'with_label',
                'errorMessagePlacement' => 'inline',
                'cssClassName'          => '',
                'asteriskPlacement'     => 'asterisk-right'
            ],
            'delete_entry_on_submission' => 'no',
        ];

        if ($formId) {
            $value = $this->getMeta($formId, 'formSettings', true);
            if ($value) {
                $defaultSettings = wp_parse_args($value, $defaultSettings);
            }
        } else {
            $globalSettings = get_option('_fluentform_global_form_settings');
            if (isset($globalSettings['layout'])) {
                $defaultSettings['layout'] = $globalSettings['layout'];
            }
        }

        return $defaultSettings;
    }

    public function getAdvancedValidationSettings($formId)
    {
        $settings = [
            'status'     => false,
            'type'       => 'all',
            'conditions' => [
                [
                    'field'    => '',
                    'operator' => '=',
                    'value'    => '',
                ],
            ],
            'error_message'   => '',
            'validation_type' => 'fail_on_condition_met',
        ];

        $metaSettings = $this->getMeta($formId, 'advancedValidationSettings', true);

        if ($metaSettings && is_array($metaSettings)) {
            $settings = wp_parse_args($metaSettings, $settings);
        }

        return $settings;
    }

    public function getMeta($formId, $metaKey, $isJson = true)
    {
        $settingsMeta = wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $formId)
            ->where('meta_key', $metaKey)
            ->first();
        if ($settingsMeta) {
            if ($isJson) {
                return \json_decode($settingsMeta->value, true);
            } else {
                return $settingsMeta->value;
            }
        }
        return false;
    }

    public function updateMeta($formId, $metaKey, $metaValue)
    {
        $exist = wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $formId)
            ->where('meta_key', $metaKey)
            ->first();

        if (is_array($metaValue) || is_object($metaValue)) {
            $metaValue = \json_encode($metaValue);
        }

        if ($exist) {
            return wpFluent()->table('fluentform_form_meta')
                ->where('id', $exist->id)
                ->update([
                    'value' => $metaValue,
                ]);
        }

        return wpFluent()->table('fluentform_form_meta')->insertGetId([
            'form_id'  => $formId,
            'meta_key' => $metaKey,
            'value'    => $metaValue,
        ]);
    }

    public function deleteMeta($formId, $metaKey)
    {
        return wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $formId)
            ->where('meta_key', $metaKey)
            ->delete();
    }

    /**
     * Find/Read a from from the database
     */
    public function find()
    {
        $form = $this->fetchForm($this->request->get('formId'));
        wp_send_json(['form' => $form, 'metas' => []], 200);
    }

    /**
     * Fetch a from from the database
     * Note: required for ninja-tables
     *
     * @return mixed
     */
    public function fetchForm($formId)
    {
        return $this->model->find($formId);
    }

    /**
     * Save/update a form from backend/editor
     */
    public function update()
    {
        $formId = $this->request->get('formId');
        $title = sanitize_text_field($this->request->get('title'));
        $status = $this->request->get('status', 'published');

        $this->validate();

        $data = [
            'title'      => $title,
            'status'     => $status,
            'updated_at' => current_time('mysql'),
        ];

        if ($formFields = $this->request->get('formFields')) {
            $formFields = apply_filters_deprecated(
                'fluentform_form_fields_update',
                [
                    $formFields,
                    $formId
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/form_fields_update',
                'Use fluentform/form_fields_update instead of fluentform_form_fields_update.'
            );
            $formFields = apply_filters('fluentform/form_fields_update', $formFields, $formId);
            $formFields = $this->sanitizeFields($formFields);
            $data['form_fields'] = $formFields;
        }

        $this->model->where('id', $formId)->update($data);

        $form = $this->fetchForm($formId);

        if (FormFieldsParser::hasPaymentFields($form)) {
            $this->model->where('id', $formId)->update([
                'has_payment' => 1,
            ]);
        } elseif ($form->has_payment) {
            $this->model->where('id', $formId)->update([
                'has_payment' => 0,
            ]);
        }

        $emailInputs = FormFieldsParser::getElement($form, ['input_email'], ['element', 'attributes']);
        if ($emailInputs) {
            $emailInput = array_shift($emailInputs);
            $emailInputName = ArrayHelper::get($emailInput, 'attributes.name');
            $this->updateMeta($formId, '_primary_email_field', $emailInputName);
        } else {
            $this->updateMeta($formId, '_primary_email_field', '');
        }

        wp_send_json([
            'message' => __('The form is successfully updated.', 'fluentform'),
        ], 200);
    }

    private function sanitizeFields($formFields)
    {
        if (fluentformCanUnfilteredHTML()) {
            return $formFields;
        }

        $fieldsArray = json_decode($formFields, true);

        if (isset($fieldsArray['submitButton'])) {
            $fieldsArray['submitButton']['settings']['button_ui']['text'] = fluentform_sanitize_html($fieldsArray['submitButton']['settings']['button_ui']['text']);
            if (!empty($fieldsArray['submitButton']['settings']['button_ui']['img_url'])) {
                $fieldsArray['submitButton']['settings']['button_ui']['img_url'] = sanitize_url($fieldsArray['submitButton']['settings']['button_ui']['img_url']);
            }
        }

        $fieldsArray['fields'] = $this->sanitizeFieldMaps($fieldsArray['fields']);

        return json_encode($fieldsArray);
    }

    private function sanitizeFieldMaps($fields)
    {
        if (!is_array($fields)) {
            return $fields;
        }

        $attributesMap = [
            'name'        => 'sanitize_key',
            'value'       => 'sanitize_textarea_field',
            'id'          => 'sanitize_key',
            'class'       => 'sanitize_text_field',
            'placeholder' => 'sanitize_text_field',
        ];
        $attributesKeys = array_keys($attributesMap);
        $settingsMap = [
            'container_class'           => 'sanitize_text_field',
            'label'                     => 'wp_kses_post',
            'label_placement'           => 'sanitize_text_field',
            'help_message'              => 'wp_kses_post',
            'admin_field_label'         => 'sanitize_text_field',
            'prefix_label'              => 'sanitize_text_field',
            'suffix_label'              => 'sanitize_text_field',
            'unique_validation_message' => 'sanitize_text_field',
            'advanced_options'          => 'fluentform_options_sanitize',
            'html_codes'                => 'fluentform_sanitize_html',
        ];
        $settingsKeys = array_keys($settingsMap);
        $stylePrefMap = [
            'layout'   => 'sanitize_key',
            'media'    => 'sanitize_url',
            'alt_text' => 'sanitize_text_field',
        ];
        $stylePrefKeys = array_keys($stylePrefMap);

        foreach ($fields as $fieldIndex => $field) {
            $element = ArrayHelper::get($field, 'element');

            if ('container' == $element) {
                $columns = $field['columns'];
                foreach ($columns as $columnIndex => $column) {
                    $fields[$fieldIndex]['columns'][$columnIndex]['fields'] = $this->sanitizeFieldMaps($column['fields']);
                }
                return $fields;
            }

            /*
             * Handle Name or address fields
             */
            if (!empty($field['fields'])) {
                $fields[$fieldIndex]['fields'] = $this->sanitizeFieldMaps($field['fields']);
                return $fields;
            }

            if (!empty($field['attributes'])) {
                $attributes = array_filter(ArrayHelper::only($field['attributes'], $attributesKeys));
                foreach ($attributes as $key => $value) {
                    $fields[$fieldIndex]['attributes'][$key] = call_user_func($attributesMap[$key], $value);
                }
            }

            if (!empty($field['settings'])) {
                $settings = array_filter(ArrayHelper::only($field['settings'], $settingsKeys));
                foreach ($settings as $key => $value) {
                    $fields[$fieldIndex]['settings'][$key] = call_user_func($settingsMap[$key], $value);
                }
            }

            if (!empty($field['style_pref'])) {
                $settings = array_filter(ArrayHelper::only($field['style_pref'], $stylePrefKeys));
                foreach ($settings as $key => $value) {
                    $fields[$fieldIndex]['style_pref'][$key] = call_user_func($stylePrefMap[$key], $value);
                }
            }
        }

        return $fields;
    }

    /**
     * Delete a from from database
     */
    public function delete()
    {
        $formId = $this->request->get('formId');

        $this->model->where('id', $formId)->delete();

        $maybeErrors = $this->deleteFormAssests($formId);

        wp_send_json([
            'message' => __('Successfully deleted the form.', 'fluentform'),
            'errors'  => $maybeErrors,
        ], 200);
    }

    protected function deleteFormAssests($formId)
    {
        // Now Let's delete associate items
        wpFluent()->table('fluentform_submissions')
            ->where('form_id', $formId)
            ->delete();

        wpFluent()->table('fluentform_submission_meta')
            ->where('form_id', $formId)
            ->delete();

        wpFluent()->table('fluentform_entry_details')
            ->where('form_id', $formId)
            ->delete();

        wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $formId)
            ->delete();

        wpFluent()->table('fluentform_form_analytics')
            ->where('form_id', $formId)
            ->delete();

        wpFluent()->table('fluentform_logs')
            ->where('parent_source_id', $formId)
            ->whereIn('source_type', ['submission_item', 'form_item', 'draft_submission_meta'])
            ->delete();

        ob_start();
        if (defined('FLUENTFORMPRO')) {
            try {
                wpFluent()->table('fluentform_order_items')
                    ->where('form_id', $formId)
                    ->delete();

                wpFluent()->table('fluentform_transactions')
                    ->where('form_id', $formId)
                    ->delete();
            } catch (\Exception $exception) {
            }
        }

        $errors = ob_get_clean();
        return $errors;
    }

    /**
     * Duplicate a from
     */
    public function duplicate()
    {
        $formId = absint($this->request->get('formId'));
        $form = $this->model->where('id', $formId)->first();

        $data = [
            'title'               => $form->title,
            'status'              => $form->status,
            'appearance_settings' => $form->appearance_settings,
            'form_fields'         => $form->form_fields,
            'type'                => $form->type,
            'has_payment'         => $form->has_payment,
            'conditions'          => $form->conditions,
            'created_by'          => get_current_user_id(),
            'created_at'          => current_time('mysql'),
            'updated_at'          => current_time('mysql'),
        ];

        $newFormId = $this->model->insertGetId($data);

        // Rename the form name  here
        wpFluent()->table('fluentform_forms')
            ->where('id', $newFormId)
            ->update([
                'title' => $form->title . ' (#' . $newFormId . ')',
            ]);

        $formMetas = wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $formId)
            ->whereNot('meta_key', ['_total_views'])
            ->get();

        // Required for duplicating PDF feeds
        $extras = [];

        foreach ($formMetas as $meta) {
            if ('notifications' == $meta->meta_key || '_pdf_feeds' == $meta->meta_key) {
                $extras[$meta->meta_key][] = $meta;
                continue;
            }
            if ("ffc_form_settings_generated_css" == $meta->meta_key || "ffc_form_settings_meta" == $meta->meta_key) {
                $meta->value = str_replace('ff_conv_app_' . $formId, 'ff_conv_app_' . $newFormId, $meta->value);
            }
            $metaData = [
                'meta_key' => $meta->meta_key,
                'value'    => $meta->value,
                'form_id'  => $newFormId,
            ];

            wpFluent()->table('fluentform_form_meta')->insert($metaData);
        }

        $pdfFeedMap = $this->getPdfFeedMap($extras, $newFormId);
        if (array_key_exists('notifications', $extras)) {
            $extras = $this->notificationWithPdfMap($extras, $pdfFeedMap);
            foreach ($extras['notifications'] as $notify) {
                $notifyData = [
                    'meta_key' => $notify->meta_key,
                    'value'    => $notify->value,
                    'form_id'  => $newFormId,
                ];
                wpFluent()->table('fluentform_form_meta')->insert($notifyData);
            }
        }

        do_action_deprecated(
            'flentform_form_duplicated',
            [
                $newFormId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_duplicated',
            'Use fluentform/form_duplicated instead of flentform_form_duplicated.'
        );

        do_action('flentform/form_duplicated', $newFormId);

        wp_send_json([
            'message'  => __('Form has been successfully duplicated.', 'fluentform'),
            'form_id'  => $newFormId,
            'redirect' => admin_url('admin.php?page=fluent_forms&route=editor&form_id=' . $newFormId),
        ], 200);
    }

    /**
     * Validate a form  by form title & for duplicate name attributes
     */
    private function validate()
    {
        $fields = $this->request->get('formFields');
        if ($fields) {
            $duplicates = Helper::getDuplicateFieldNames($fields);
            if ($duplicates) {
                $duplicateString = implode(', ', $duplicates);
                wp_send_json([
                    'title' => sprintf('Name attribute %s has duplicate value.', $duplicateString),
                ], 422);
            }
        }

        if (!sanitize_text_field($this->request->get('title'))) {
            wp_send_json([
                'title' => 'The title field is required.',
            ], 422);
        }
    }

    public function convertToConversational()
    {
        $formId = $this->request->get('form_id');
        $form = $this->fetchForm($formId);

        if (!$form) {
            wp_send_json([
                'message' => __('Form Not Found! Try Again.', 'fluentform'),
            ], 422);
        }

        $conversationalMeta = $this->getMeta($formId, 'is_conversion_form', false);

        $shouldConvert = in_array($conversationalMeta, [false, 'no']);

        if ($shouldConvert) {
            $formConverted['form_fields'] = \FluentForm\App\Services\FluentConversational\Classes\Converter\Converter::convertExistingForm($form);

            $this->model->where('id', $formId)->update($formConverted);

            $conversationalMetaValue = 'yes';
        } else {
            $conversationalMetaValue = 'no';
        }

        $this->updateMeta($formId, 'is_conversion_form', $conversationalMetaValue);

        wp_send_json_success([
            'message' => __('Form has been successfully converted.', 'fluentform'),
        ], 200);
    }
    
    private function getAdminPermalink($route, $form)
    {
        $baseUrl = admin_url('admin.php?page=fluent_forms');
        return $baseUrl . '&route=' . $route . '&form_id=' . $form->id;
    }

    private function getSettingsUrl($form)
    {
        $baseUrl = admin_url('admin.php?page=fluent_forms');
        return $baseUrl . '&form_id=' . $form->id . '&route=settings&sub_route=form_settings#basic_settings';
    }

    public function getAllForms()
    {
        $fields = $this->request->get('fields');

        if ($fields) {
            $forms = $this->model
                ->select($fields)
                ->orderBy('created_at', 'DESC')->get();
        } else {
            $forms = $this->model->orderBy('created_at', 'DESC')->get();
        }

        wp_send_json($forms, 200);
    }

    /**
     * Map pdf feed ID to replace with duplicated PDF feed ID when duplicating form
     *
     * @param array $extras
     * @param array $newFormId
     *
     * @return array
     */
    private function getPdfFeedMap($extras, $newFormId)
    {
        $pdfFeedMap = [];
        if (array_key_exists('_pdf_feeds', $extras)) {
            foreach ($extras['_pdf_feeds'] as $pdf_feed) {
                $pdfData = [
                    'meta_key' => $pdf_feed->meta_key,
                    'value'    => $pdf_feed->value,
                    'form_id'  => $newFormId,
                ];
                $pdfFeedMap[$pdf_feed->id] = wpFluent()->table('fluentform_form_meta')->insertGetId($pdfData);
            }
        }
        return $pdfFeedMap;
    }

    /**
     * Map notification data with PDF feed map
     *
     * @param array $extras
     * @param array $pdfFeedMap
     *
     * @return array
     */
    private function notificationWithPdfMap($extras, $pdfFeedMap)
    {
        foreach ($extras['notifications'] as $key => $notification) {
            $notificationValue = json_decode($notification->value);
            $pdf_attachments = [];
            if (isset($notificationValue->pdf_attachments) && count($notificationValue->pdf_attachments)) {
                foreach ($notificationValue->pdf_attachments as $attachment) {
                    $pdf_attachments[] = json_encode($pdfFeedMap[$attachment]);
                }
            }
            $notificationValue->pdf_attachments = $pdf_attachments;
            $notification->value = json_encode($notificationValue);

            $extras['notifications'][$key] = $notification;
        }
        return $extras;
    }
    
    public function findFormLocations()
    {
        $formId = intval($this->request->get('form_id'));
    
        $excluded = ['attachment'];
        $post_types = get_post_types(['show_in_menu' => true], 'objects', 'or');
        $postTypes = [];
        foreach($post_types as $post_type) {
            $postTypeName = $post_type->name;
            if (in_array($postTypeName, $excluded)) {
                continue;
            }
            $postTypes[] = $postTypeName;
        }
    
        $params = array(
            'post_type'      => $postTypes,
            'posts_per_page' => -1
        );
    
        $params = apply_filters_deprecated(
            'fluentform_find_shortcode_params',
            [
                $params
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/find_shortcode_params',
            'Use fluentform/find_shortcode_params instead of fluentform_find_shortcode_params.'
        );

        $params = apply_filters('fluentform/find_shortcode_params', $params);

        $formLocations = [];
        $posts = get_posts($params);
        foreach($posts as $post) {
        
            $formIds = self::getShortCodeIds($post->post_content);
            if(!empty($formIds) && in_array($formId,$formIds)) {
    
                $postType = get_post_type_object($post->post_type);
                $formLocations[] = [
                    'id'        => $post->ID,
                    'name'      => $postType->labels->singular_name,
                    'title'     => (empty($post->post_title) ? $post->ID : $post->post_title),
                    'edit_link' => sprintf("%spost.php?post=%s&action=edit", admin_url(), $post->ID),
                ];
            }
        }
        $data = [
            'locations' => $formLocations,
            'status'    => !empty($formLocations),
        ];
        wp_send_json($data, 200);
    
    }
    
    public static function getShortCodeIds($content)
    {
        $ids = [];
        $tag = 'fluentform';
        $selector = 'id';
        
        if (function_exists('parse_blocks')) {
            $parsedBlocks = parse_blocks($content);
            foreach ($parsedBlocks as $block) {
                if (!ArrayHelper::exists($block, 'blockName') || ArrayHelper::exists($block, 'attrs.formId')) {
                    continue;
                }
                $hasBlock = strpos($block['blockName'], 'fluentfom/guten-block') === 0;
                if ($hasBlock) {
                    $ids[] = intval($block['attrs']['formId']);
                }
            }
        }

        $hasShortCode = has_shortcode($content, $tag);
        if(!$hasShortCode){
            return $ids;
        }

        preg_match_all('/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER);
        if (empty($matches)) {
            return $ids;
        }
        
        
        foreach ($matches as $shortcode) {
            if (count($shortcode) >= 2 && $tag === $shortcode[2]) {
                $parsedCode = str_replace(['[', ']', '&#91;', '&#93;'], '', $shortcode[0]);
                
                $result = shortcode_parse_atts($parsedCode);
                
                if (!empty($result[$selector])) {
                    $ids[] = $result[$selector];
                }
            }
        }
        return $ids;
    }
}
