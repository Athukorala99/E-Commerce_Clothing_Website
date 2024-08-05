<?php

namespace FluentForm\App\Services\Migrator\Classes;


use \FluentForm\App\Modules\Form\Form;
use \FluentForm\Framework\Helpers\ArrayHelper;

class NinjaFormsMigrator extends BaseMigrator
{

    public function __construct()
    {
        $this->key = 'ninja_forms';
        $this->title = 'Ninja Forms';
        $this->shortcode = 'ninja_form';
    }

    /**
     * Check if form type exists
     * @return bool
     */
    public function exist()
    {
        return !!defined('NF_SERVER_URL');
    }

    /**
     * Get array of all forms
     * @return array Forms with fields
     */
    public function getForms()
    {
        $forms = [];
        $items = (new \NF_Database_FormsController())->getFormsData();
        foreach ($items as $item) {
            $fields = Ninja_Forms()->form($item->id)->get_fields();
            $field_settings = [];
            foreach ($fields as $key => $field) {
                $field_settings[$key] = $field->get_settings();
            }
            $forms[] = [
                'ID'     => $item->id,
                'name'   => $item->title,
                'fields' => $field_settings,
            ];
        }
        return $forms;
    }

    public function getForm($id)
    {
        $formModel = \Ninja_Forms()->form($id)->get();
        if (!$formModel) {
            return [];
        }
        $forms = $this->getForms();
        $formArray = array_filter($forms, function ($form) use ($id) {
            if ($form['ID'] == $id) {
                return $form;
            }
        });
        return array_shift($formArray);
    }

    public function getFormsFormatted()
    {
        $forms = [];
        $items = $this->getForms();
        foreach ($items as $item) {
            $forms[] = [
                'name'           => $this->getFormName($item),
                'id'             => $this->getFormId($item),
                'imported_ff_id' => $this->isAlreadyImported($item),
            ];
        }
        return $forms;
    }

    /**
     *
     * Get formatted fields form array
     * @param array $form
     * @return array fluentform data formatted for database
     */
    public function getFields($form)
    {
        $fluentFields = [];
        $fields = ArrayHelper::get($form, 'fields');

        foreach ($fields as $field) {
            list($type, $args) = $this->formatFieldData($field);
            if ($value = $this->getFluentClassicField($type, $args)) {
                $fluentFields[$field['key']] = $value;
            } else {
                if (ArrayHelper::get($field, 'type') != 'submit') {
                    $this->unSupportFields[] = ArrayHelper::get($field, 'label');
                }
            }
        }

        $submitBtn = $this->getSubmitBttn([
            'uniqElKey' => $field['key'],
            'label'     => $field['label'],
            'class'     => $field['element_class'],
        ]);
        if (empty($fluentFields)) {
            return false;
        }
        $returnData = [
            'fields'       => $fluentFields,
            'submitButton' => $this->submitBtn
        ];
        return $returnData;
    }

    /**
     * Format each field with proper data
     * @param $field
     * @return array required arguments for single field
     */
    protected function formatFieldData($field)
    {

        $type = ArrayHelper::get($this->fieldTypes(), $field['type'], '');

        $args = [
            'uniqElKey'       => $field['key'] . '-' . time(),
            'index'           => $field['order'],
            'required'        => ArrayHelper::isTrue($field, 'required'),
            'label'           => $field['label'],
            'name'            => ArrayHelper::get($field, 'key'),
            'placeholder'     => ArrayHelper::get($field, 'placeholder', ''),
            'class'           => ArrayHelper::get($field, 'element_class', ''),
            'value'           => $this->dynamicShortcodeConverter(ArrayHelper::get($field, 'value', '')),
            'help_message'    => ArrayHelper::get($field, 'desc_text'),
            'container_class' => ArrayHelper::get($field, 'container_class'),
        ];
        if (empty($args['name'])) {
            $name = ArrayHelper::get($field, 'id');
            $args['name'] = str_replace('.', '_', $name);
        }


        switch ($type) {

            case 'select':
            case 'input_radio':
            case 'input_checkbox':
            case 'multi_select':
                $optionsData = $this->getOptions(ArrayHelper::get($field, 'options', []));
                $args['options'] = ArrayHelper::get($optionsData, 'options');
                $args['value'] = ArrayHelper::get($optionsData, 'selectedOption.0', '');

                if ($field['type'] == 'listimage') {
                    $args['enable_image_input'] = true;
                    $optionsData = $this->getOptions(ArrayHelper::get($field, 'image_options', []), $hasImage = true);
                    $args['options'] = $optionsData['options'];
                    $args['value'] = ArrayHelper::get($optionsData, 'selectedOption.0', '');

                    if (ArrayHelper::isTrue($field, 'allow_multi_select')) {
                        $type = 'input_checkbox';
                    }

                } else {
                    if ($field['type'] == 'checkbox' && empty($args['options'])) {
                        //single item checkbox
                        $arr = [
                            'label'      => ArrayHelper::get($field, 'checked_value'),
                            'value'      => ArrayHelper::get($field, 'checked_value'),
                            'calc_value' => '',
                            'id'         => 0
                        ];
                        $args['options'] = [$arr];
                    } else {
                        if ($field['type'] == 'checkbox' && $args['allow_multi_select'] == 1) {
                            //img with multi item checkbox make it check box
                            $type = 'input_checkbox';
                            $args['value'] = ArrayHelper::get($optionsData, 'selectedOption');
                        }
                    }
                }
                if ($type == 'input_checkbox' || $type == 'multi_select') {
                    //array values
                    $args['value'] = ArrayHelper::get($optionsData, 'selectedOption', '');
                }

                break;
            case 'input_date':
                $args['format'] = Arrayhelper::get($field, 'date_format');
                if ($args['format'] == 'default') {
                    $args['format'] = 'd/m/Y';
                }
                break;
            case 'input_number':
                $args['step'] = $field['num_step'];
                $args['min'] = $field['num_min'];
                $args['max'] = $field['num_max'];
                break;
            case 'input_hidden':
                $args['value'] = ArrayHelper::get($field, 'default', '');
                break;
            case 'ratings':
                $number = ArrayHelper::get($field, 'number_of_stars', 5);
                $args['options'] = array_combine(range(1, $number), range(1, $number));
                break;
            case 'input_file':

                break;
            case 'custom_html':
                $args['html_codes'] = $field['default'];
                break;

            case 'gdpr_agreement': // ??
                $args['tnc_html'] = $field['config']['agreement'];
                break;
            case 'repeater_field':
                $repeaterFields = ArrayHelper::get($field, 'fields', []);
                $arr = [];
                foreach ($repeaterFields as $serial => $repeaterField) {
                    $type = ArrayHelper::get($this->fieldTypes(), $repeaterField['type'], '');
                    $supportedRepeaterFields = ['input_text', 'select', 'input_number', 'email'];

                    if (in_array($type, $supportedRepeaterFields)) {
                        list($type, $args) = $this->formatFieldData($repeaterField);
                        $arr[] = $this->getFluentClassicField($type, $args);
                    }
                }
                if (empty($arr)) {
                    return '';
                }
                $args['fields'] = $arr;
                return array('repeater_field', $args);
            case 'submit':
                $this->submitBtn = $this->getSubmitBttn(
                    [
                        'uniqElKey' => $field['key'],
                        'label'     => $field['label'],
                        'class'     => $field['element_class'],
                    ]
                );
                break;
        }
        return array($type, $args);
    }

    /**
     * Get field type in fluentforms type
     * @return array
     */
    public function fieldTypes()
    {
        $fieldTypes = [
            'email'           => 'email',
            'textbox'         => 'input_text',
            'address'         => 'input_text',
            'city'            => 'input_text',
            'zip'             => 'input_text',
            'liststate'       => 'select',
            'firstname'       => 'input_text',
            'lastname'        => 'input_text',
            'listcountry'     => 'select_country',
            'textarea'        => 'input_textarea',
            'phone'           => 'phone',
            'select'          => 'select',
            'listselect'      => 'select',
            'listmultiselect' => 'multi_select',
            'radio'           => 'input_radio',
            'listcheckbox'    => 'input_checkbox',
            'listimage'       => 'input_radio',
            'listradio'       => 'input_radio',
            'checkbox'        => 'input_checkbox',
            'date'            => 'input_date',
            'html'            => 'custom_html',
            'hr'              => 'section_break',
            'repeater'        => 'repeater_field',
            'starrating'      => 'ratings',
            'recaptcha'       => 'reCaptcha',
            'number'          => 'input_number',
            'hidden'          => 'input_hidden',
            'submit'          => 'submit',
        ];
        return $fieldTypes;
    }

    /**
     * Get formatted options for select,radio etc type fields
     * @param $options
     * @param bool $hasImage
     * @return array (options list and selected option)
     */
    public function getOptions($options, $hasImage = false)
    {
        $formattedOptions = [];
        $selectedOption = [];
        foreach ($options as $key => $option) {
            $arr = [
                'label'      => ArrayHelper::get($option, 'label', 'Item -' . $key),
                'value'      => ArrayHelper::get($option, 'value'),
                'calc_value' => ArrayHelper::get($option, 'calc'),
                'id'         => ArrayHelper::get($option, 'order')
            ];
            if ($hasImage) {
                $arr['image'] = ArrayHelper::get($option, 'image');
            }
            if (ArrayHelper::isTrue($option, 'selected')) {
                $selectedOption[] = ArrayHelper::get($option, 'value', '');
            }
            $formattedOptions[] = $arr;
        }

        return ['options' => $formattedOptions, 'selectedOption' => $selectedOption];
    }

    /**
     * Get Form Metas
     * @param $form
     * @return array
     */
    public function getFormMetas($form)
    {
        $this->addRecaptcha();

        $formObject = new Form(wpFluentForm());
        $defaults = $formObject->getFormsDefaultSettings();
        $formSettings = $this->getFormSettings($form);

        $formMeta = [];
        $actions = Ninja_Forms()->form($this->getFormId($form))->get_actions();
        if (is_array($actions)) {
            foreach ($actions as $action) {
                if ($action->get_type() != 'action') {
                    continue;
                }
                $actionData = $action->get_settings();

                if ($actionData['type'] == 'email') {
                    $formMeta['notifications'] [] = $this->getNotificationData($actionData);
                } elseif ($actionData['type'] == 'successmessage') {
                    $formMeta['formSettings']['confirmation'] = [
                        'messageToShow'        => $this->dynamicShortcodeConverter($actionData['success_msg']),
                        'samePageFormBehavior' => ($formSettings['hide_complete']) ? 'hide_form' : 'reset_form',
                        'redirectTo'           => 'samePage'
                    ];
                } elseif ($actionData['type'] == 'save') {
                    $isAutoDelete = intval(ArrayHelper::get($actionData, 'set_subs_to_expire')) == 1;
                    if ($isAutoDelete) {
                        $formMeta['formSettings'] = [
                            'delete_after_x_days' => true,
                            'auto_delete_days'    => $actionData['subs_expire_time'],
                        ];
                    }
                } elseif ($actionData['type'] == 'redirect') {
                    $formMeta['formSettings']['confirmation'] = [
                        'messageToShow'        => $this->dynamicShortcodeConverter($actionData['success_msg']),
                        'samePageFormBehavior' => isset($form['hide_form']) ? 'hide_form' : 'reset_form',
                        'redirectTo'           => 'customUrl',
                        'customUrl'            => $actionData['redirect_url'],
                    ];
                }
            }
        }


        $advancedValidation = [
            'status'          => false,
            'type'            => 'all',
            'conditions'      => [
                [
                    'field'    => '',
                    'operator' => '=',
                    'value'    => ''
                ]
            ],
            'error_message'   => '',
            'validation_type' => 'fail_on_condition_met'
        ];

        $defaults['restrictions']['requireLogin'] = [
            'enabled'         => ArrayHelper::isTrue($formSettings, 'logged_in', false),
            'requireLoginMsg' => $formSettings['not_logged_in_msg']
        ];

        $defaults['restrictions']['limitNumberOfEntries'] = [
            'enabled'         => (intval($formSettings['sub_limit_number']) > 0),
            'numberOfEntries' => $formSettings['sub_limit_number'],
            'limitReachedMsg' => $formSettings['sub_limit_msg']
        ];

        $formMeta['formSettings']['restrictions'] = $defaults['restrictions'];
        $formMeta['formSettings']['layout'] = $defaults['layout'];
        $formMeta['advancedValidationSettings'] = $advancedValidation;
        $formMeta['delete_entry_on_submission'] = 'no';
        return $formMeta;
    }

    /**
     * Update recaptcha key if already not has
     */
    protected function addRecaptcha()
    {
        $ffRecap = get_option('_fluentform_reCaptcha_details');
        if ($ffRecap) {
            return;
        }
        $recaptcha_site_key = Ninja_Forms()->get_settings();
        $arr = '';
        if ($recaptcha_site_key['recaptcha_site_key'] != '') {
            $arr = [
                'siteKey'     => $recaptcha_site_key['recaptcha_site_key'],
                'secretKey'   => $recaptcha_site_key['recaptcha_secret_key'],
                'api_version' => 'v2_visible'
            ];
        } elseif ($recaptcha_site_key['recaptcha_site_key_3'] != '') {
            $arr = [
                'siteKey'     => $recaptcha_site_key['recaptcha_site_key_3'],
                'secretKey'   => $recaptcha_site_key['recaptcha_secret_key_3'],
                'api_version' => 'v3_invisible',
            ];
        }
        update_option('_fluentform_reCaptcha_details', $arr, false);
    }

    /**
     * Get notification data for metas
     * @param $actionData
     * @return array
     */
    private function getNotificationData($actionData)
    {

        // Convert all shortcodes
        $actionData['to'] = $this->dynamicShortcodeConverter($actionData['to']);
        $actionData['reply_to'] = $this->dynamicShortcodeConverter($actionData['reply_to']);
        $actionData['email_subject'] = $this->dynamicShortcodeConverter($actionData['email_subject']);
        $actionData['email_message'] = $this->dynamicShortcodeConverter($actionData['email_message']);

        $notification =
            [
                'sendTo'      => [
                    'type'      => 'email',
                    'email'     => ($actionData['to'] == '{system:admin_email}') ? '{wp.admin_email}' : $actionData['to'],
                    'fromEmail' => $actionData['from_address'],
                    'field'     => 'email',
                    'routing'   => '',
                ],
                'enabled'     => ArrayHelper::isTrue($actionData, 'active'),
                'name'        => $actionData['label'],
                'subject'     => $actionData['email_subject'],
                'to'          => ($actionData['to'] == '{system:admin_email}') ? '{wp.admin_email}' : $actionData['to'],
                'replyTo'     => ($actionData['to'] == '{system:admin_email}') ? '{wp.admin_email}' : $actionData['reply_to'],
                'message'     => $actionData['email_message'],
                'fromName'    => ArrayHelper::get($actionData, 'from_name'),
                'fromAddress' => ArrayHelper::get($actionData, 'from_address'),
                'bcc'         => ArrayHelper::get($actionData, 'bcc'),
            ];
        return $notification;
    }

    /**
     * Convert Ninja Forms merge Tags to Fluent forms dynamic shortcodes.
     * @param $msg
     * @return string
     */
    private function dynamicShortcodeConverter($msg)
    {

        $shortcodes = $this->dynamicShortcodes();

        $msg = str_replace(array_keys($shortcodes), array_values($shortcodes), $msg);

        return $msg;
    }

    /**
     * Get shortcode in fluentforms format
     * @return array
     */
    protected function dynamicShortcodes()
    {
        $dynamicShortcodes = [
            // Input Options
            'field:'                    => 'inputs.',
            '{all_fields_table}'        => '{all_data}',
            '{fields_table}'            => '{all_data}',
            // General Smartcodes
            '{wp:site_title}'           => '{wp.site_title}',
            '{wp:site_url}'             => '{wp.site_url}',
            '{wp:admin_email}'          => '{wp.admin_email}',
            '{other:user_ip}'           => '{ip}',
            '{other:date}'              => '{date.d/m/Y}',
            '{other:time}'              => '',
            '{wp:post_id}'              => '{embed_post.ID}',
            '{wp:post_title}'           => '{embed_post.post_title}',
            '{wp:post_url}'             => '{embed_post.permalink}',
            '{wp:post_author}'          => '',
            '{wp:post_author_email}'    => '',
            '{post_meta:YOUR_META_KEY}' => '{embed_post.meta.YOUR_META_KEY}',
            '{wp:user_id}'              => '{user.ID}',
            '{wp:user_first_name}'      => '{user.first_name}',
            '{wp:user_last_name}'       => '{user.last_name}',
            '{wp:user_username}'        => '{user.user_login}',
            '{wp:user_display_name}'    => '{user.display_name}',
            '{wp:user_email}'           => '{user.user_email}',
            '{wp:user_url}'             => '{wp.site_url}',
            '{user_meta:YOUR_META_KEY}' => '',
            // Entry Attributes
            '{form:id}'                 => '',
            '{form:title}'              => '',
            '{submission:sequence}'     => '{submission.serial_number}',
            '{submission:count}'        => '{submission.serial_number}',
            'querystring:'              => 'get.'
        ];

        return $dynamicShortcodes;
    }

    /**
     * Get form settings
     * @param $form
     * @return array $formSettings
     */
    protected function getFormSettings($form)
    {
        $formData = Ninja_Forms()->form($form['ID'])->get();
        return $formData->get_settings();
    }

    /**
     * @param $form
     * @return mixed
     */
    protected function getFormId($form)
    {
        return intval($form['ID']);
    }

    /**
     * @param $form
     * @return mixed
     */
    protected function getFormName($form)
    {
        return $form['name'];
    }

    public function getEntries($formId)
    {

        $form = $this->getForm($formId);
        if (empty($form)) {
            return false;
        }

        $submissions = \Ninja_Forms()->form($formId)->get_subs();
        if (!$submissions || !is_array($submissions)) {
            return [];
        }

        $totalEntries = count($submissions);
        $max_limit = apply_filters('fluentform/entry_migration_max_limit', static::DEFAULT_ENTRY_MIGRATION_MAX_LIMIT, $this->key, $totalEntries, $formId);
        if ($totalEntries && $max_limit && $totalEntries > $max_limit) {
            $submissions = array_slice($submissions, 0 , $max_limit);
        }
        $entries = [];
        $fieldsMap = $this->getFieldKeyMaps($form);

        foreach ($submissions as $submission) {
            $values = $submission->get_field_values();
            $values = ArrayHelper::only($values, $fieldsMap);
            $values = $this->formatEntries($values);
            if ($created_at = $submission->get_sub_date('Y-m-d H:i:s')) {
                $values['created_at'] = $created_at;
            }
            if ($updated_at = $submission->get_mod_date('Y-m-d H:i:s')) {
                $values['updated_at'] = $updated_at;
            }
            $entries[] = $values;
        }
        return array_reverse($entries);
    }

    public function getFieldKeyMaps($form)
    {
        $fields = ArrayHelper::get($form, 'fields');
        $fieldsMap = [];
        foreach ($fields as $field) {
            $fieldsMap[] = $field['key'];
        }
        return $fieldsMap;
    }

    /**
     * @param array $values
     * @return array
     */
    public function formatEntries(array $values)
    {
        $formattedData = [];
        foreach ($values as $key => $value) {
            $key = str_replace('.', '_', $key);
            $value = maybe_unserialize($value);
            $formattedData[$key] = $value;
            if (is_array($value)) {
                $value = $this->formatEntries($value);
            }
        }
        return $formattedData;

    }

}
