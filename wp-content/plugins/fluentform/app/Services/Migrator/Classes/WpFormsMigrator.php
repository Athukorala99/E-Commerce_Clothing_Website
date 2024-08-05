<?php

namespace FluentForm\App\Services\Migrator\Classes;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Form\Form;
use FluentForm\App\Services\Migrator\Classes\BaseMigrator;
use FluentForm\Framework\Helpers\ArrayHelper;

class WpFormsMigrator extends BaseMigrator
{
    public function __construct()
    {
        $this->key = 'wpforms';
        $this->title = 'WPForms';
        $this->shortcode = 'wp_form';
        $this->hasStep = false;
    }

    protected function getForms()
    {
        $forms = [];
        if (function_exists('wpforms')) {
            $formItems = wpforms()->form->get('');
            foreach ($formItems as $form) {
                $formData = json_decode($form->post_content, true);
                $forms[] = [
                    'ID'       => $form->ID,
                    'name'     => $form->post_title,
                    'fields'   => ArrayHelper::get($formData, 'fields'),
                    'settings' => ArrayHelper::get($formData, 'settings'),
                ];
            }
        }
        return $forms;
    }
    
    public function getFields($form)
    {
        $fluentFields = [];
        $fields = ArrayHelper::get($form, 'fields');
        
        foreach ($fields as $field) {
            if (
                "pagebreak" == ArrayHelper::get($field, 'type') &&
                $position = ArrayHelper::get($field, 'position')
            ) {
                if ("top" == $position || "bottom" == $position) {
                    continue;
                }
            }
            $fieldType = ArrayHelper::get($this->fieldTypeMap(), ArrayHelper::get($field, 'type'));
            $args = $this->formatFieldData($field, $fieldType);
            if ('select' == $fieldType && ArrayHelper::isTrue($field, 'multiple')) {
                $fieldType = 'multi_select';
            }
            if ($fieldData = $this->getFluentClassicField($fieldType, $args)) {
                $fluentFields[$field['id']] = $fieldData;
            } else {
                $this->unSupportFields[] = ArrayHelper::get($field, 'label');
            }
        }
        $submitBtn = $this->getSubmitBttn([
            'uniqElKey' => 'button_' . time(),
            'label'     => ArrayHelper::get($form, 'settings.submit_text'),
            'class'     => ArrayHelper::get($form, 'settings.submit_class'),
        ]);
        if (empty($fluentFields)) {
            return false;
        }

        $returnData = [
            'fields'       => $fluentFields,
            'submitButton' => $submitBtn
        ];

        if ($this->hasStep && defined('FLUENTFORMPRO')) {
            $returnData['stepsWrapper'] = $this->getStepWrapper();
        }
        return $returnData;
    }
    
    public function getSubmitBttn($args)
    {
        return [
            'uniqElKey'      => $args['uniqElKey'],
            'element'        => 'button',
            'attributes'     => [
                'type'  => 'submit',
                'class' => $args['class']
            ],
            'settings'       => [
                'container_class'  => '',
                'align'            => 'left',
                'button_style'     => 'default',
                'button_size'      => 'md',
                'color'            => '#ffffff',
                'background_color' => '#409EFF',
                'button_ui'        => [
                    'type'    => ArrayHelper::get($args, 'type', 'default'),
                    'text'    => $args['label'],
                    'img_url' => ArrayHelper::get($args, 'img_url', '')
                ],
                'normal_styles'    => [],
                'hover_styles'     => [],
                'current_state'    => "normal_styles"
            ],
            'editor_options' => [
                'title' => 'Submit Button',
            ],
        
        ];
    }
    
    protected function getFormName($form)
    {
        return $form['name'];
    }
    
    protected function getFormMetas($form)
    {
        $formObject = new Form(wpFluentForm());
        $defaults = $formObject->getFormsDefaultSettings();
        $confirmationsFormatted = $this->getConfirmations($form, $defaults['confirmation']);
        $defaultConfirmation = array_pop($confirmationsFormatted);
        
        $notifications = $this->getNotifications($form);
        $metas = [
            'formSettings'               => [
                'confirmation' => $defaultConfirmation,
                'restrictions' => $defaults['restrictions'],
                'layout'       => $defaults['layout'],
            ],
            'advancedValidationSettings' => $this->getAdvancedValidation(),
            'delete_entry_on_submission' => 'no',
            'notifications'              => $notifications,
            'confirmations'              => $confirmationsFormatted,
        ];
        if ($webhooks = $this->getWebhooks($form)) {
            $metas['webhooks'] = $webhooks;
        }
        return $metas;
    }
    
    protected function getFormId($form)
    {
        return $form['ID'];
    }

    protected function getForm($id)
    {
        if (function_exists('wpforms') && $form = wpforms()->form->get($id)) {
            $formData = json_decode($form->post_content, true);
            return [
                'ID'       => $form->ID,
                'name'     => $form->post_title,
                'fields'   => ArrayHelper::get($formData, 'fields'),
                'settings' => ArrayHelper::get($formData, 'settings'),
            ];
        }
        return false;
    }

    public function getEntries($formId)
    {
        if(!wpforms()->is_pro()){
            wp_send_json([
                'message' => __("Entries not available in WPForms Lite",'fluentform')
            ], 200);
        }
        $form = $this->getForm($formId);
        if (empty($form)) {
            return false;
        }
        $formFields = $this->getFields($form);
        if ($formFields) {
            $formFields = $formFields['fields'];
        }
        $args = [
            'form_id' => $form['ID'],
            'order'  => 'asc',
        ];
        $totalEntries = wpforms()->entry->get_entries($args, true);// 2nd parameter 'true' means return total entries count
        $args['number'] = apply_filters('fluentform/entry_migration_max_limit', static::DEFAULT_ENTRY_MIGRATION_MAX_LIMIT, $this->key,  $totalEntries, $formId);
        $submissions = wpforms()->entry->get_entries($args);
        $entries = [];
        if (!$submissions || !is_array($submissions)) {
            return $entries;
        }
        foreach ($submissions as $submission) {
            $fields = \json_decode( $submission->fields , true);
            if (!$fields) {
                continue;
            }
            $entry = [];
            foreach ($fields as $fieldId => $field) {
                if (!isset($formFields[$fieldId])) {
                    continue;
                }
                $formField = $formFields[$fieldId];
                $name = ArrayHelper::get($formField, 'attributes.name');
                if (!$name) {
                    continue;
                }
                $type = ArrayHelper::get($formField, 'element');
                // format entry value by field name
                $finalValue = ArrayHelper::get($field, 'value');
                if ("input_name" == $type) {
                    $finalValue = $this->getSubmissionNameValue($formField['fields'], $field);
                } elseif (
                    "input_checkbox" == $type ||
                    (
                        "select" == $type &&
                        ArrayHelper::isTrue($formField, 'attributes.multiple')
                    )
                ) {
                    $finalValue = explode("\n", $finalValue);
                } elseif ("address" == $type) {
                    $finalValue = [
                        "address_line_1" => ArrayHelper::get($field, 'address1', ''),
                        "address_line_2" => ArrayHelper::get($field, 'address2', ''),
                        "city" => ArrayHelper::get($field, 'city', ''),
                        "state" => ArrayHelper::get($field, 'state', ''),
                        "zip" => ArrayHelper::get($field, 'postal', ''),
                        "country" => ArrayHelper::get($field, 'country', ''),
                    ];
                } elseif ("input_file" == $type && $value = ArrayHelper::get($field, 'value')) {
                    $finalValue = $this->migrateFilesAndGetUrls($value);
                }
                if (null == $finalValue) {
                    $finalValue = "";
                }
                $entry[$name] = $finalValue;
            }
            if ($submission->date) {
                $entry['created_at'] = $submission->date;
            }
            if ($submission->date_modified) {
                $entry['updated_at'] = $submission->date_modified;
            }
            if ($submission->starred) {
                $entry['is_favourite'] = $submission->starred;
            }
            if ($submission->viewed) {
                $entry['status'] = 'read';
            }
            $entries[] = $entry;
        }
        return $entries;
    }

    protected function getSubmissionNameValue($nameFields, $submissionField) {
        $finalValue = [];
        foreach ($nameFields as $key => $field) {
            if ($name = ArrayHelper::get($field, 'attributes.name')) {
                $value = "";
                if ("first_name" == $key) {
                    $value = ArrayHelper::get($submissionField, 'first');
                } elseif ("middle_name" == $key) {
                    $value = ArrayHelper::get($submissionField, 'middle');
                } elseif ("last_name" == $key) {
                    $value = ArrayHelper::get($submissionField, 'last');
                }
                $finalValue[$name] = $value;
            }
        }
        return $finalValue;
    }

    
    public function getFormsFormatted()
    {
        $forms = [];
        $items = $this->getForms();
        foreach ($items as $item) {
            $forms[] = [
                'name'           => $item['name'],
                'id'             => $item['ID'],
                'imported_ff_id' => $this->isAlreadyImported($item),
            ];
        }
        return $forms;
    }
    
    public function exist()
    {
        return !!defined('WPFORMS_VERSION');
    }
    
    protected function formatFieldData($field, $type)
    {
        $args = [
            'uniqElKey'       => $field['id'] . '-' . time(),
            'index'           => $field['id'],
            'required'        => ArrayHelper::isTrue($field, 'required'),
            'label'           => ArrayHelper::get($field, 'label', ''),
            'name'            => ArrayHelper::get($field, 'type') . '_' . $field['id'],
            'placeholder'     => ArrayHelper::get($field, 'placeholder', ''),
            'class'           => '',
            'value'           => ArrayHelper::get($field, 'default_value') ?: "",
            'help_message'    => ArrayHelper::get($field, 'description', ''),
            'container_class' => ArrayHelper::get($field, 'css', ''),
        ];
        
        switch ($type) {
            case 'input_text':
                if (ArrayHelper::isTrue($field, 'limit_enabled')) {
                    $max_length = ArrayHelper::get($field, 'limit_count', '');
                    $mode = ArrayHelper::get($field, 'limit_mode', '');
                    if ("words" == $mode && $max_length) {
                        $max_length = (int)$max_length * 6; // average 6 characters is a word
                    }
                    $args['maxlength'] = $max_length;
                }
                break;
            case 'phone':
                $args['valid_phone_number'] = "1";
                break;
            case 'input_name':
                $args['input_name_args'] = [];
                $fields = ArrayHelper::get($field, 'format');
                if (!$fields) {
                    break;
                }
                $fields = explode('-', $fields);
                $required = ArrayHelper::isTrue($field, 'required');
                foreach ($fields as $subField) {
                    if ($subField == 'simple') {
                        $label = $args['label'];
                        $subName = 'first_name';
                        $hideLabel = ArrayHelper::isTrue($field, 'label_hide');
                    } else {
                        $subName = $subField . '_name';
                        $label = ucfirst($subField);
                        $hideLabel = ArrayHelper::isTrue($field, 'sublabel_hide');
                    }
                    $placeholder = ArrayHelper::get($field, $subField . "_placeholder" , '');
                    $default = ArrayHelper::get($field, $subField . "_default" , '');
                    $args['input_name_args'][$subName] = [
                        "visible" => true,
                        "required" => $required,
                        "name" => $subName,
                        "default" => $default,
                    ];
                    if (!$hideLabel) {
                        $args['input_name_args'][$subName]['label'] = $label;
                    }
                    if ($placeholder) {
                        $args['input_name_args'][$subName]['placeholder'] = $placeholder;
                    }
                }
                break;
            case 'select':
            case 'input_radio':
            case 'input_checkbox':
                list($options, $defaultVal) = $this->getOptions(ArrayHelper::get($field, 'choices', []));
                $args['options'] = $options;
                $args['randomize_options'] = ArrayHelper::isTrue($field, 'random');
                if ($type == 'select') {
                    $isMulti = ArrayHelper::isTrue($field, 'multiple');
                    if ($isMulti) {
                        $args['multiple'] = true;
                        $args['value'] = $defaultVal;
                    } else {
                        $args['value'] = array_shift($defaultVal) ?: "";
                    }
                } elseif ($type == 'input_checkbox') {
                    $args['value'] = $defaultVal;
                } elseif ($type == 'input_radio') {
                    $args['value'] = array_shift($defaultVal) ?: "";
                }
                break;
            case 'input_date':
                $format = ArrayHelper::get($field, 'format');
                if ("date" == $format) {
                    $format = ArrayHelper::get($field, 'date_format', 'd/m/Y');
                } elseif ("time" == $format) {
                    $format = ArrayHelper::get($field, 'time_format', 'H:i');
                } else {
                    $format = ArrayHelper::get($field, 'date_format', 'd/m/Y') . ' ' .ArrayHelper::get($field, 'time_format', 'H:i');
                }
                $args['format'] = $format;
                break;
            case 'rangeslider':
                $args['step'] = $field['step'];
                $args['min'] = $field['min'];
                $args['max'] = $field['max'];
                break;
            case 'ratings':
                $number = ArrayHelper::get($field, 'scale', 5);
                $args['options'] = array_combine(range(1, $number), range(1, $number));
                break;
            case 'input_file':
                $args['allowed_file_types'] = $this->getFileTypes($field, 'extensions');
                $args['max_size_unit'] = 'MB';
                $max_size = ArrayHelper::get($field, 'max_size') ?: 1;
                $args['max_file_size'] = ceil( $max_size * 1048576); // 1MB = 1048576 Bytes
                $args['max_file_count'] = ArrayHelper::get($field, 'max_file_number', 1);
                $args['upload_btn_text'] = ArrayHelper::get($field, 'label', 'File Upload');
                break;
            case 'custom_html':
                $args['html_codes'] = ArrayHelper::get($field, 'code', '');
                break;
            case 'form_step':
                $this->hasStep = true;
                break;
            case 'address':
                $args['address_args'] = $this->getAddressArgs($field, $args);
                break;
            case 'rich_text_input':
                $size = ArrayHelper::get($field, 'size');
                if ('small' == $size) {
                    $rows = 2;
                } elseif ('large' == $size) {
                    $rows = 5;
                } else {
                    $rows =3;
                }
                $args['rows'] = $rows;
                break;
            case 'section_break':
                $args['section_break_desc'] = ArrayHelper::get($field, 'description');
                break;
            case 'input_number':
                $args['min'] = '';
                $args['max'] = '';
                break;
            default :
                break;
        }
        return $args;
    }
    
    private function fieldTypeMap()
    {
        return [
            'email'         => 'email',
            'text'          => 'input_text',
            'name'          => 'input_name',
            'hidden'        => 'input_hidden',
            'textarea'      => 'input_textarea',
            'select'        => 'select',
            'radio'         => 'input_radio',
            'checkbox'      => 'input_checkbox',
            'number'        => 'input_number',
            'layout'        => 'container',
            'date-time'     => 'input_date',
            'address'       => 'address',
            'password'      => 'input_password',
            'html'          => 'custom_html',
            'rating'        => 'ratings',
            'divider'       => 'section_break',
            'url'           => 'input_url',
            'multi_select'  => 'multi_select',
            'number-slider' => 'rangeslider',
            'richtext'      => 'rich_text_input',
            'phone'         => 'phone',
            'file-upload'   => 'input_file',
            'pagebreak'     => 'form_step',
        ];
    }
    
    private function getConfirmations($form, $defaultValues)
    {
        $confirmations = ArrayHelper::get($form, 'settings.confirmations');
        $confirmationsFormatted = [];
        if (!empty($confirmations)) {
            foreach ($confirmations as $confirmation) {
                $type = $confirmation['type'];
                if ($type == 'redirect') {
                    $redirectTo = 'customUrl';
                } else {
                    if ($type == 'page') {
                        $redirectTo = 'customPage';
                    } else {
                        $redirectTo = 'samePage';
                    }
                }
                $confirmationsFormatted[] = wp_parse_args(
                    [
                        'name'                 => ArrayHelper::get($confirmation, 'name'),
                        'messageToShow'        => $this->getResolveShortcode(ArrayHelper::get($confirmation, 'message'), $form),
                        'samePageFormBehavior' => 'hide_form',
                        'redirectTo'           => $redirectTo,
                        'customPage'           => intval(ArrayHelper::get($confirmation, 'page')),
                        'customUrl'            => ArrayHelper::get($confirmation, 'redirect'),
                        'active'               => true,
                        'conditionals'         => $this->getConditionals($confirmation, $form)
                    ], $defaultValues
                );
            }
        }
        return $confirmationsFormatted;
    }

    private function getConditionals($notification, $form)
    {
        $conditionals = ArrayHelper::get($notification, 'conditionals', []);
        $status = ArrayHelper::isTrue($notification, 'conditional_logic');
        if ('stop' == ArrayHelper::get($notification, 'conditional_type')) {
            $status = false;
        }
        $type = 'all';
        $conditions = [];
        if ($conditionals) {
            if (count($conditionals) > 1) {
                $type = 'any';
                $conditionals = array_filter(array_column($conditionals, 0));
            } else {
                $conditionals = ArrayHelper::get($conditionals, 0, []);
            }
            foreach ($conditionals as $condition) {
                $fieldId = ArrayHelper::get($condition, 'field');
                list ($fieldName, $fieldType) = $this->getFormFieldName($fieldId, $form);
                if (!$fieldName) {
                    continue;
                }
                if ($operator = $this->getResolveOperator(ArrayHelper::get($condition, 'operator', ''))) {
                    $value = ArrayHelper::get($condition, 'value', '');
                    if (
                        in_array($fieldType, ['select', 'multi_select', 'input_radio', 'input_checkbox']) &&
                        $choices = ArrayHelper::get($form, "fields.$fieldId.choices")
                    ) {
                        $choiceValue = ArrayHelper::get($choices,  "$value.value", '');
                        if (!$choiceValue) {
                            $choiceValue = ArrayHelper::get($choices, "$value.label", '');
                        }
                        $value = $choiceValue;
                    }
                    $conditions[] = [
                        'field' => $fieldName,
                        'operator' => $operator,
                        'value' => $value
                    ];
                }
            }
        }
        return [
            "status" => $status,
            "type" => $type,
            'conditions' => $conditions
        ];
    }
    
    private function getAdvancedValidation(): array
    {
        return [
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
    }
    
    private function getNotifications($form)
    {
        $notificationsFormatted = [];
        $enabled = ArrayHelper::isTrue($form, 'settings.notification_enable');
        $notifications = ArrayHelper::get($form, 'settings.notifications');

        foreach ($notifications as $notification) {
            $email = ArrayHelper::get($notification, 'email', '');
            $sendTo = [
                'type'    => 'email',
                'email'   => '{wp.admin_email}',
                'field'   => '',
                'routing' => [],
            ];
            if ($this->isFormField($email)) {
                list($fieldName) = $this->getFormFieldName($email, $form);
                $sendTo['type'] = 'field';
                $sendTo['field'] = $fieldName;
                $sendTo['email'] = '';
            } else {
                if ($email) {
                    $sendTo['email'] = $this->getResolveShortcode($email, $form);
                }
            }
            $message = $this->getResolveShortcode(ArrayHelper::get($notification, 'message', ''), $form);
            $replyTo = $this->getResolveShortcode(ArrayHelper::get($notification, 'replyto', ''), $form);
            $notificationsFormatted[] = [
                'sendTo'       => $sendTo,
                'enabled'      => $enabled,
                'name'         => ArrayHelper::get($notification, 'notification_name', 'Admin Notification'),
                'subject'      => $this->getResolveShortcode(ArrayHelper::get($notification, 'subject', 'Notification'), $form),
                'to'           => $sendTo['email'],
                'replyTo'      => $replyTo ?: '{wp.admin_email}',
                'message'      => str_replace("\n", "<br />", $message),
                'fromName'     => $this->getResolveShortcode(ArrayHelper::get($notification, 'sender_name', ''), $form),
                'fromEmail'    => $this->getResolveShortcode(ArrayHelper::get($notification, 'sender_address', ''), $form),
                'bcc'          => '',
                'conditionals' => $this->getConditionals($notification, $form)
            ];
        }
        return $notificationsFormatted;
    }

    /**
     * Get webhooks feeds
     *
     * @param array $form
     * @return array
     */
    private function getWebhooks($form)
    {
        $webhooksFeeds = [];
        foreach (ArrayHelper::get($form, 'settings.webhooks', []) as $webhook) {
            list($headers, $headersKeysStatus, $headersValuesStatus) = $this->getResolveMappingFields(ArrayHelper::get($webhook, 'headers', []), $form);

            $body = ArrayHelper::get($webhook, 'body', []);
            // ff webhook body parameter doesn't support custom type fields
            // remove custom type fields, wpforms add "custom_" prefix on key for custom type value
            $body = array_filter($body, function ($key) {
                return !(strpos($key, 'custom_') !== false);
            }, ARRAY_FILTER_USE_KEY);
            list($body) = $this->getResolveMappingFields($body, $form);

            $webhooksFeeds[] = [
                'name'                 => ArrayHelper::get($webhook, 'name', ''),
                'request_url'          => ArrayHelper::get($webhook, 'url', ''),
                'with_header'          => count($headers) > 0 ? 'yup' : 'nop',
                'request_method'       => strtoupper(ArrayHelper::get($webhook, 'method', 'GET')),
                'request_format'       => strtoupper(ArrayHelper::get($webhook, 'format', 'FORM')),
                'request_body'         => 'selected_fields',
                'custom_header_keys'   => $headersKeysStatus,
                'custom_header_values' => $headersValuesStatus,
                'fields'               => $body,
                'request_headers'      => $headers,
                'conditionals'         => $this->getConditionals($webhook, $form),
                'enabled'              => ArrayHelper::isTrue($form, 'settings.webhooks_enable')
            ];
        }
        return $webhooksFeeds;
    }

    /**
     * Get resolved mapping fields
     *
     * @param array $fields
     * @param array $form
     * @return array
     */
    private function getResolveMappingFields($fields, $form)
    {
        $mapping = [];
        $mappingKeysStatus = [];
        $mappingValuesStatus = [];
        foreach ($fields as $key => $value) {
            // wpforms add "custom_" prefix on key for custom type value
            if ((strpos($key, 'custom_') !== false) || is_array($value)) {
                $key = str_replace('custom_', '', $key);
                // ff not support secure value, when value is secure decrypt it's by wpforms helper
                if (ArrayHelper::isTrue($value, 'secure') && method_exists('\WPForms\Helpers\Crypto', 'decrypt')) {
                    $value = ArrayHelper::get($value, 'value', '');
                    if ($decryptValue = \WPForms\Helpers\Crypto::decrypt($value)) {
                        $value = $decryptValue;
                    }
                } else {
                    $value = ArrayHelper::get($value, 'value', '');
                }
                $mappingKeysStatus[] = true;
                $mappingValuesStatus[] = true;
            } else {
                list ($fieldName) = $this->getFormFieldName($value, $form);
                if (!$fieldName) {
                    continue;
                }
                $value = "{inputs.$fieldName}";
                $mappingKeysStatus[] = false;
                $mappingValuesStatus[] = false;
            }
            $mapping[] = [
                'key'   => $key,
                'value' => $value
            ];
        }
        return [$mapping, $mappingKeysStatus, $mappingValuesStatus];
    }

    /**
     * Get bool value depend on shortcode is form inputs or not
     *
     * @param string $string
     * @return boolean
     */
    private function isFormField($string)
    {
        return (strpos($string, '{field_id=') !== false);
    }

    /**
     * Get form field name in fluentforms format
     *
     * @param string $str
     * @param array $form
     * @return array
     */
    private function getFormFieldName($str, $form)
    {
        preg_match('/\d+/', $str, $fieldId);
        $field = ArrayHelper::get($form, 'fields.' . ArrayHelper::get($fieldId, 0, '0'));
        $fieldType = ArrayHelper::get($this->fieldTypeMap(), ArrayHelper::get($field, 'type'));
        if (in_array(ArrayHelper::get($field, 'label'), $this->unSupportFields)) {
            return ['', $fieldType];
        }
        $fieldName = ArrayHelper::get($this->formatFieldData($field, $fieldType), 'name', '');
        return [$fieldName, $fieldType];
    }

    /**
     * Get shortcode in fluentforms format
     * @return array
     */
    private function dynamicShortcodes()
    {
        return [
            '{all_fields}'                => '{all_data}',
            '{admin_email}'               => '{wp.admin_email}',
            '{user_ip}'                   => '{ip}',
            '{date format="m/d/Y"}'       => '{date.d/m/Y}',
            '{page_id}'                   => '{embed_post.ID}',
            '{page_title}'                => '{embed_post.post_title}',
            '{page_url}'                  => '{embed_post.permalink}',
            '{user_id}'                   => '{user.ID}',
            '{user_first_name}'           => '{user.first_name}',
            '{user_last_name}'            => '{user.last_name}',
            '{user_display}'              => '{user.display_name}',
            '{user_full_name}'            => '{user.first_name} {user.last_name}',
            '{user_email}'                => '{user.user_email}',
            '{entry_id}'                  => '{submission.id}',
            '{entry_date format="d/m/Y"}' => '{submission.created_at}',
            '{entry_details_url}'         => '{submission.admin_view_url}',
            '{url_referer}'               => '{http_referer}'
        ];
    }

    /**
     * Resolve shortcode in fluentforms format
     *
     * @param string $message
     * @param array $form
     * @return string
     */
    private function getResolveShortcode($message, $form)
    {
        if (!$message) {
            return $message;
        }
        preg_match_all('/{(.*?)}/', $message, $matches);
        if (!$matches[0]) {
            return $message;
        }
        $shortcodes = $this->dynamicShortcodes();
        foreach ($matches[0] as $match) {
            $replace = '';
            if (isset($shortcodes[$match])) {
                $replace = $shortcodes[$match];
            } elseif ($this->isFormField($match)) {
                list($fieldName) = $this->getFormFieldName($match, $form);
                if ($fieldName) {
                    $replace = "{inputs.$fieldName}";
                }
            } elseif (strpos($match, 'query_var') !== false) {
                preg_match('#key=["\'](\S+)["\']#', $match, $result);
                if ($key = ArrayHelper::get($result, 1)) {
                    $replace = "{get.$key}";
                }
            }
            $message = str_replace($match, $replace, $message);
        }
        return $message;
    }
    
    public function getOptions($options)
    {
        $formattedOptions = [];
        $defaults = [];
        foreach ($options as $key => $option) {
            $formattedOption = [
                'label'      => ArrayHelper::get($option, 'label', 'Item -' . $key),
                'value'      => !empty(ArrayHelper::get($option, 'value')) ? ArrayHelper::get($option,
                    'value') : ArrayHelper::get($option, 'label', 'Item -' . $key),
                'image'      => ArrayHelper::get($option, 'image'),
                'calc_value' => '',
                'id'         => $key,
            ];
            if (ArrayHelper::isTrue($option, 'default')) {
                $defaults[] = $formattedOption['value'];
            }
            $formattedOptions[] = $formattedOption;

        }
        return [$formattedOptions, $defaults];
    }

    /**
     * @return array
     */
    private function getStepWrapper()
    {
        return [
            'stepStart' => [
                'element'        => 'step_start',
                'attributes'     => [
                    'id'    => '',
                    'class' => '',
                ],
                'settings'       => [
                    'progress_indicator'           => 'progress-bar',
                    'step_titles'                  => [],
                    'disable_auto_focus'           => 'no',
                    'enable_auto_slider'           => 'no',
                    'enable_step_data_persistency' => 'no',
                    'enable_step_page_resume'      => 'no',
                ],
                'editor_options' => [
                    'title' => 'Start Paging'
                ],
            ],
            'stepEnd'   => [
                'element'        => 'step_end',
                'attributes'     => [
                    'id'    => '',
                    'class' => '',
                ],
                'settings'       => [
                    'prev_btn' => [
                        'type'    => 'default',
                        'text'    => 'Previous',
                        'img_url' => ''
                    ]
                ],
                'editor_options' => [
                    'title' => 'End Paging'
                ],
            ]

        ];
    }

    /**
     * @param array $field
     * @return array[]
     */
    private function getAddressArgs($field, $args)
    {
        if ('us' == ArrayHelper::get($field, 'scheme')) {
            $field['country_default'] = 'US';
        }
        $hideSubLabel = ArrayHelper::isTrue($field, 'sublabel_hide');
        $name = ArrayHelper::get($args, 'name', 'address');
        return [
            'address_line_1' => [
                'name'        => $name . '_address_line_1',
                'default'     => ArrayHelper::get($field, 'address1_default', ''),
                'placeholder' => ArrayHelper::get($field, 'address1_placeholder', ''),
                'label'       => $hideSubLabel ? '' : 'Address Line 1',
                'visible'     => true,
            ],
            'address_line_2' => [
                'name'        => $name . '_address_line_2',
                'default'     => ArrayHelper::get($field, 'address2_default', ''),
                'placeholder' => ArrayHelper::get($field, 'address2_placeholder', ''),
                'label'       => $hideSubLabel ? '' : 'Address Line 2',
                'visible'     => !ArrayHelper::isTrue($field, 'address2_hide'),
            ],
            'city'           => [
                'name'        => $name . '_city',
                'default'     => ArrayHelper::get($field, 'city_default', ''),
                'placeholder' => ArrayHelper::get($field, 'city_placeholder', ''),
                'label'       => $hideSubLabel ? '' : 'City',
                'visible'     => true,
            ],
            'state'          => [
                'name'        => $name . '_state',
                'default'     => ArrayHelper::get($field, 'state_default', ''),
                'placeholder' => ArrayHelper::get($field, 'state_placeholder', ''),
                'label'       => $hideSubLabel ? '' : 'State',
                'visible'     => true,
            ],
            'zip'            => [
                'name'        => $name . '_zip',
                'default'     => ArrayHelper::get($field, 'postal_default', ''),
                'placeholder' => ArrayHelper::get($field, 'postal_placeholder', ''),
                'label'       => $hideSubLabel ? '' : 'Zip',
                'visible'     => !ArrayHelper::isTrue($field, 'postal_hide'),
            ],
            'country'        => [
                'name'        => $name . '_country',
                'default'     => ArrayHelper::get($field, 'country_default', ''),
                'placeholder' => ArrayHelper::get($field, 'country_placeholder', ''),
                'label'       => $hideSubLabel ? '' : 'Country',
                'visible'     => !ArrayHelper::isTrue($field, 'country_hide'),
            ],
        ];
    }
    
}
