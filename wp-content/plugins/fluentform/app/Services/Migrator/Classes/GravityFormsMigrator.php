<?php

namespace FluentForm\App\Services\Migrator\Classes;


use FluentForm\App\Modules\Form\Form;
use FluentForm\Framework\Helpers\ArrayHelper;

class GravityFormsMigrator extends BaseMigrator
{

    public function __construct()
    {
        $this->key = 'gravityform';
        $this->title = 'Gravity Forms';
        $this->shortcode = 'gravity_form';
        $this->hasStep = false;
    }

    public function exist()
    {
        return class_exists('GFForms');
    }

    /**
     * @param $form
     * @return array
     */
    public function getFields($form)
    {
        $fluentFields = [];
        $fields = $form['fields'];

        foreach ($fields as $name => $field) {
            $field = (array)$field;
            list($type, $args) = $this->formatFieldData($field);
            if ($value = $this->getFluentClassicField($type, $args)) {
                $fluentFields[$field['id']] = $value;
            } else {
                $this->unSupportFields[] = ArrayHelper::get($field, 'label');
            }
        }

        $submitBtn = $this->getSubmitBttn([
            'uniqElKey' => time(),
            'class'     => '',
            'label'     => ArrayHelper::get($form, 'button.text', 'Submit'),
            'type'      => ArrayHelper::get($form, 'button.type') == 'text' ? 'default' : 'image',
            'img_url'   => ArrayHelper::get($form, 'button.imageUrl'),
        ]);

        $returnData = [
            'fields'       => $this->getContainer($fields, $fluentFields),
            'submitButton' => $submitBtn
        ];

        if ($this->hasStep && defined('FLUENTFORMPRO')) {
            $returnData['stepsWrapper'] = $this->getStepWrapper();
        }

        return $returnData;
    }

    private function formatFieldData(array $field)
    {
        $args = [
            'uniqElKey'         => $field['id'],
            'index'             => $field['id'],
            'required'          => $field['isRequired'],
            'label'             => $field['label'],
            'label_placement'   => $this->getLabelPlacement($field),
            'admin_field_label' => ArrayHelper::get($field, 'adminLabel'),
            'name'              => $this->getInputName($field),
            'placeholder'       => ArrayHelper::get($field, 'placeholder'),
            'class'             => $field['cssClass'],
            'value'             => ArrayHelper::get($field, 'defaultValue'),
            'help_message'      => ArrayHelper::get($field, 'description'),
        ];

        $type = ArrayHelper::get($this->fieldTypes(), $field['type'], '');

        switch ($type) {

            case 'input_name':
                $args['input_name_args'] = $field['inputs'];
                $args['input_name_args']['first_name']['name'] = $this->getInputName($field['inputs'][1]);
                $args['input_name_args']['middle_name']['name'] = $this->getInputName($field['inputs'][2]);
                $args['input_name_args']['last_name']['name'] = $this->getInputName($field['inputs'][3]);
                $args['input_name_args']['first_name']['label'] = ArrayHelper::get($field['inputs'][1], 'label');
                $args['input_name_args']['middle_name']['label'] = ArrayHelper::get($field['inputs'][2], 'label');
                $args['input_name_args']['last_name']['label'] = ArrayHelper::get($field['inputs'][3], 'label');
                $args['input_name_args']['first_name']['visible'] = ArrayHelper::get($field, 'inputs.1.isHidden', true);
                $args['input_name_args']['middle_name']['visible'] = ArrayHelper::get($field, 'inputs.2.isHidden',
                    true);
                $args['input_name_args']['last_name']['visible'] = ArrayHelper::get($field, 'inputs.3.isHidden', true);
                break;
            case 'input_textarea':
                $args['maxlength'] = $field['maxLength'];
                break;
            case 'input_text':
                $args['maxlength'] = $field['maxLength'];
                $args['is_unique'] = ArrayHelper::isTrue($field, 'noDuplicates');
                if (ArrayHelper::isTrue($field, 'inputMask')) {
                    $type = 'input_mask';
                    $args['temp_mask'] = 'custom';
                    $args['mask'] = $field['inputMaskValue'];
                }
                if (ArrayHelper::isTrue($field, 'enablePasswordInput')) {
                    $type = 'input_password';
                }
                break;
            case 'address':
                $args['address_args'] = $this->getAddressArgs($field);
                break;
            case 'select':
            case 'input_radio':
                $optionData = $this->getOptions(ArrayHelper::get($field, 'choices'));
                $args['options'] = ArrayHelper::get($optionData, 'options');
                $args['value'] = ArrayHelper::get($optionData, 'selectedOption.0');
            case 'multi_select':
            case 'input_checkbox':
                $optionData = $this->getOptions(ArrayHelper::get($field, 'choices'));
                $args['options'] = ArrayHelper::get($optionData, 'options');
                $args['value'] = ArrayHelper::get($optionData, 'selectedOption');

                break;
            case 'input_date':
                if ($field['type'] == 'time') {
                    $args['format'] = 'H:i';
                    $args['is_time_enabled'] = true;
                }
                break;
            case 'input_number':
                $args['min'] = $field['rangeMin'];
                $args['max'] = $field['rangeMax'];
                break;
            case 'repeater_field':
                $repeaterFields = ArrayHelper::get($field, 'choices', []);
                $args['fields'] = $this->getRepeaterFields($repeaterFields, $field['label']);;
            case 'input_file':
                $args['allowed_file_types'] = $this->getFileTypes($field, 'allowedExtensions');
                $args['max_size_unit'] = 'MB';
                $args['max_file_size'] = $this->getFileSize($field);;
                $args['max_file_count'] = ArrayHelper::isTrue($field,
                    'multipleFiles') ? $field['maxFiles'] : 1;
                $args['upload_btn_text'] = 'File Upload';
                break;
            case 'custom_html':
                $args['html_codes'] = $field['content'];
                break;
            case 'section_break':
                $args['section_break_desc'] = $field['description'];
                break;
            case 'terms_and_condition':
                $args['tnc_html'] = $field['description'];
                break;
            case 'form_step':
                $this->hasStep = true;
                $args['next_btn'] = $field['nextButton'];
                $args['next_btn']['type'] = $field['nextButton']['type'] == 'text' ? 'default' : 'img';
                $args['next_btn']['img_url'] = $field['nextButton']['imageUrl'];
                $args['prev_btn'] = $field['previousButton'];
                $args['prev_btn']['type'] = $field['previousButton']['type'] == 'text' ? 'default' : 'img';
                $args['prev_btn']['img_url'] = $field['previousButton']['imageUrl'];

                break;
        }
        return array($type, $args);

    }


    private function getInputName($field)
    {
        return str_replace('-', '_', sanitize_title($field['label'] . '-' . $field['id']));
    }

    private function getLabelPlacement($field)
    {
        if ($field['labelPlacement'] == 'hidden_label') {
            return 'hide_label';
        }
        return 'top';
    }

    /**
     * @param $field
     * @return filesize in MB
     */
    private function getFileSize($field)
    {
        $fileSizeByte = ArrayHelper::get($field, 'maxFileSize', 10);

        if (empty($fileSizeByte)) {
            $fileSizeByte = 1;
        }

        $fileSizeMB = ceil($fileSizeByte * 1048576);  // 1MB = 1048576 Bytes

        return $fileSizeMB;
    }

    /**
     * @return array
     */
    public function fieldTypes()
    {
        $fieldTypes = [
            'email'       => 'email',
            'text'        => 'input_text',
            'name'        => 'input_name',
            'hidden'      => 'input_hidden',
            'textarea'    => 'input_textarea',
            'website'     => 'input_url',
            'phone'       => 'phone',
            'select'      => 'select',
            'list'        => 'repeater_field',
            'multiselect' => 'multi_select',
            'checkbox'    => 'input_checkbox',
            'radio'       => 'input_radio',
            'date'        => 'input_date',
            'time'        => 'input_date',
            'number'      => 'input_number',
            'fileupload'  => 'input_file',
            'consent'     => 'terms_and_condition',
            'captcha'     => 'reCaptcha',
            'html'        => 'custom_html',
            'section'     => 'section_break',
            'page'        => 'form_step',
            'address'     => 'address',
        ];
        //todo pro fields remove
        return $fieldTypes;
    }

    /**
     * @param array $field
     * @return array[]
     */
    private function getAddressArgs(array $field)
    {
        $required = ArrayHelper::isTrue($field, 'isRequired');
        return [
            'address_line_1' => [
                'name'    => $this->getInputName($field['inputs'][0]),
                'label'   => $field['inputs'][0]['label'],
                'visible' => ArrayHelper::get($field, 'inputs.0.isHidden', true),
                'required' => $required,
            ],
            'address_line_2' => [
                'name'    => $this->getInputName($field['inputs'][1]),
                'label'   => $field['inputs'][1]['label'],
                'visible' => ArrayHelper::get($field, 'inputs.1.isHidden', true),
                'required' => $required,
            ],
            'city'           => [
                'name'    => $this->getInputName($field['inputs'][2]),
                'label'   => $field['inputs'][2]['label'],
                'visible' => ArrayHelper::get($field, 'inputs.2.isHidden', true),
                'required' => $required,
            ],
            'state'          => [
                'name'    => $this->getInputName($field['inputs'][3]),
                'label'   => $field['inputs'][3]['label'],
                'visible' => ArrayHelper::get($field, 'inputs.3.isHidden', true),
                'required' => $required,
            ],
            'zip'            => [
                'name'    => $this->getInputName($field['inputs'][4]),
                'label'   => $field['inputs'][4]['label'],
                'visible' => ArrayHelper::get($field, 'inputs.4.isHidden', true),
                'required' => $required,
            ],
            'country'        => [
                'name'    => $this->getInputName($field['inputs'][5]),
                'label'   => $field['inputs'][5]['label'],
                'visible' => ArrayHelper::get($field, 'inputs.5.isHidden', true),
                'required' => $required,
            ],
        ];
    }

    /**
     * @param $options
     * @return array
     */
    public function getOptions($options = [])
    {
        $formattedOptions = [];
        $selectedOption = [];
        foreach ($options as $key => $option) {
            $arr = [
                'label' => ArrayHelper::get($option, 'text', 'Item -' . $key),
                'value' => ArrayHelper::get($option, 'value'),
                'id'    => ArrayHelper::get($option, $key)
            ];
            if (ArrayHelper::isTrue($option, 'isSelected')) {
                $selectedOption[] = ArrayHelper::get($option, 'value', '');
            }
            $formattedOptions[] = $arr;
        }

        return ['options' => $formattedOptions, 'selectedOption' => $selectedOption];
    }

    /**
     * @param $repeaterFields
     * @param $label
     * @return array
     */
    protected function getRepeaterFields($repeaterFields, $label)
    {
        $arr = [];
        if (empty($repeaterFields)) {
            $arr[] = [
                'element'    => 'input_text',
                'attributes' => array(
                    'type'        => 'text',
                    'value'       => '',
                    'placeholder' => '',
                ),
                'settings'   => array(
                    'label'            => $label,
                    'help_message'     => '',
                    'validation_rules' => array(
                        'required' => array(
                            'value'   => false,
                            'message' => __('This field is required', 'fluentform'),
                        ),
                    )
                )
            ];
        } else {
            foreach ($repeaterFields as $serial => $repeaterField) {
                $arr[] = [
                    'element'    => 'input_text',
                    'attributes' => array(
                        'type'        => 'text',
                        'value'       => '',
                        'placeholder' => '',
                    ),
                    'settings'   => array(
                        'label'            => ArrayHelper::get($repeaterField, 'label', ''),
                        'help_message'     => '',
                        'validation_rules' => array(
                            'required' => array(
                                'value'   => false,
                                'message' => __('This field is required', 'fluentform'),
                            ),
                        )
                    )
                ];

            }
        }
        return $arr;
    }

    private function getContainer($fields, $fluentFields)
    {

        $layoutGroupIds = array_column($fields, 'layoutGroupId');
        $cols = array_count_values($layoutGroupIds); // if inputs has more then one duplicate layoutGroupIds then it has container
        if (intval($cols) < 2) {
            return $fluentFields;
        }

        $final = [];
        //get fields array for inserting into containers
        $containers = self::getLayout($fields);

        //set fields array map for inserting into containers
        foreach ($containers as $index => $fields) {
            $final[$index][] = array_map(function ($id) use ($fluentFields) {
                if (isset($fluentFields[$id])) {
                    return $fluentFields[$id];
                }
            }, $fields);
        }
        $final = self::arrayFlat($final);
        $withContainer = [];
        foreach ($final as $row => $columns) {
            $colsCount = count($columns);
            $containerConfig = [];
            //with container
            if ($colsCount != 1) {

                $fields = [];
                foreach ($columns as $col) {
                    $fields[]['fields'] = [$col];
                }

                $containerConfig[] = [
                    'index'          => $row,
                    'element'        => 'container',
                    "attributes"     => [],
                    'settings'       => [
                        'container_class',
                        'conditional_logics'
                    ],
                    'editor_options' => [
                        'title'      => $colsCount . ' Column Container',
                        'icon_class' => 'ff-edit-column-' . $colsCount
                    ],
                    'columns'        => $fields,
                    'uniqElKey'      => 'col' . '_' . md5(uniqid(mt_rand(), true))
                ];
            } else {
                //without container
                $containerConfig = $columns;

            }
            $withContainer[] = $containerConfig;
        }
        return (array_filter(self::arrayFlat($withContainer)));
    }

    protected static function getLayout($fields, $id = '')
    {
        $layoutGroupIds = array_column($fields, 'layoutGroupId');
        $rows = array_count_values($layoutGroupIds);
        $layout = [];
        foreach ($rows as $key => $value) {
            $layout[] = self::getInputIdsFromLayoutGrp($key, $fields);
        }
        return $layout;
    }

    public static function getInputIdsFromLayoutGrp($id, $array)
    {
        $keys = [];
        foreach ($array as $key => $val) {
            if ($val['layoutGroupId'] === $id) {
                $keys[] = $val['id'];
            }
        }
        return $keys;
    }

    /**
     * @param null $array
     * @param int $depth
     * @return array
     */
    public static function arrayFlat($array = null, $depth = 1)
    {
        $result = [];
        if (!is_array($array)) {
            $array = func_get_args();
        }
        foreach ($array as $key => $value) {
            if (is_array($value) && $depth) {
                $result = array_merge($result, self::arrayFlat($value, $depth - 1));
            } else {
                $result = array_merge($result, [$key => $value]);
            }
        }
        return $result;
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
     * @param $form
     * @return array default parsed form metas
     * @throws \Exception
     */
    public function getFormMetas($form)
    {
        $formObject = new Form(wpFluentForm());
        $defaults = $formObject->getFormsDefaultSettings();
        $confirmationsFormatted = $this->getConfirmations($form, $defaults['confirmation']);
        $defaultConfirmation = array_shift($confirmationsFormatted);
        $notifications = $this->getNotifications($form);
        $defaults['restrictions']['requireLogin']['enabled'] = ArrayHelper::isTrue($form, 'requireLogin');
        $defaults['restrictions']['requireLogin']['requireLoginMsg'] = ArrayHelper::isTrue($form,
            'requireLoginMessage');
        $defaults['restrictions']['limitNumberOfEntries']['enabled'] = ArrayHelper::isTrue($form, 'limitEntries');
        $defaults['restrictions']['limitNumberOfEntries']['numberOfEntries'] = ArrayHelper::isTrue($form,
            'limitEntriesCount');
        $defaults['restrictions']['limitNumberOfEntries']['period'] = ArrayHelper::isTrue($form, 'limitEntriesPeriod');
        $defaults['restrictions']['limitNumberOfEntries']['limitReachedMsg'] = ArrayHelper::isTrue($form,
            'limitEntriesMessage');
        $defaults['restrictions']['scheduleForm']['enabled'] = ArrayHelper::isTrue($form, 'scheduleForm');
        $defaults['restrictions']['scheduleForm']['start'] = ArrayHelper::isTrue($form, 'scheduleStart');
        $defaults['restrictions']['scheduleForm']['end'] = ArrayHelper::isTrue($form, 'scheduleEnd');
        $defaults['restrictions']['scheduleForm']['pendingMsg'] = ArrayHelper::isTrue($form, 'schedulePendingMessage');
        $defaults['restrictions']['scheduleForm']['expiredMsg'] = ArrayHelper::isTrue($form, 'scheduleMessage');
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
        return [
            'formSettings'               => [
                'confirmation' => $defaultConfirmation,
                'restrictions' => $defaults['restrictions'],
                'layout'       => $defaults['layout'],
            ],
            'advancedValidationSettings' => $advancedValidation,
            'delete_entry_on_submission' => 'no',
            'confirmations'              => $confirmationsFormatted,
            'notifications'              => $notifications
        ];
    }
    private function getNotifications($form)
    {
        $notificationsFormatted = [];
        foreach (ArrayHelper::get($form, 'notifications', []) as $notification) {
            $fieldType = ArrayHelper::get($notification, 'toType', 'email');
            $sendTo = [
                'type'    => $fieldType,
                'email'   => '',
                'field'   => '',
                'routing' => [],
            ];
            if ('field' == $fieldType) {
                $sendTo['field'] = $this->getFormFieldName(ArrayHelper::get($notification, 'to', ''), $form);
            } elseif('routing' == $fieldType) {
                foreach (ArrayHelper::get($notification, 'routing', []) as $route) {
                    $fieldName = $this->getFormFieldName(ArrayHelper::get($route, 'fieldId'), $form);
                    $routeEmail = ArrayHelper::get($route, 'email');
                    if (!$fieldName || !$routeEmail) {
                        continue;
                    }
                    if ($operator = $this->getResolveOperator(ArrayHelper::get($route, 'operator', ''))) {
                        $sendTo['routing'][] = [
                            'field' => $fieldName,
                            'operator' => $operator,
                            'input_value' => $routeEmail,
                            'value' => ArrayHelper::get($route, 'value', '')
                        ];
                    }
                }
            } else {
                $sendTo['email'] = $this->getResolveShortcodes(ArrayHelper::get($notification, 'to', ''), $form);
            }
            $message = $this->getResolveShortcodes(ArrayHelper::get($notification, 'message', ''), $form);
            $replyTo = $this->getResolveShortcodes(ArrayHelper::get($notification, 'replyTo', ''), $form);
            $notificationsFormatted[] = [
                'sendTo'       => $sendTo,
                'enabled'      => ArrayHelper::get($notification, 'isActive', true),
                'name'         => ArrayHelper::get($notification, 'name', 'Admin Notification'),
                'subject'      => $this->getResolveShortcodes(ArrayHelper::get($notification, 'subject', 'Notification'), $form),
                'to'           => $sendTo['email'],
                'replyTo'      => $replyTo ?: '{wp.admin_email}',
                'message'      => str_replace("\n", "<br />", $message),
                'fromName'     => $this->getResolveShortcodes(ArrayHelper::get($notification, 'fromName', ''), $form),
                'fromEmail'    => $this->getResolveShortcodes(ArrayHelper::get($notification, 'from', ''), $form),
                'bcc'          => $this->getResolveShortcodes(ArrayHelper::get($notification, 'bcc', ''), $form),
                'conditionals' => $this->getConditionals($notification,'notification', $form)
            ];
        }
        return $notificationsFormatted;
    }

    private function getConditionals($notification, $key, $form)
    {
        $conditionals = ArrayHelper::get($notification, 'conditionalLogic', []);
        $conditions = [];
        if (!$conditionals) {
            $conditionals = ArrayHelper::get($notification, $key.'_conditional_logic_object', []);
        }
        $type = 'any';
        if ($conditionals) {
            $type = ArrayHelper::get($conditionals, 'logicType', 'any');
            foreach (ArrayHelper::get($conditionals, 'rules', []) as $rule) {
                $fieldName = $this->getFormFieldName(ArrayHelper::get($rule, 'fieldId'), $form);
                if (!$fieldName) {
                    continue;
                }
                if ($operator = $this->getResolveOperator(ArrayHelper::get($rule, 'operator', ''))) {
                    $conditions[] = [
                        'field' => $fieldName,
                        'operator' => $operator,
                        'value' => ArrayHelper::get($rule, 'value', '')
                    ];
                }
            }
        }
        return [
            "status" => ArrayHelper::isTrue($notification, $key . '_conditional_logic'),
            "type" => $type,
            'conditions' => $conditions
        ];
    }

    private function getConfirmations($form, $defaultValues)
    {
        $confirmationsFormatted = [];
        foreach (ArrayHelper::get($form, 'confirmations', []) as $confirmation) {
            $type = ArrayHelper::get($confirmation, 'type');
            $queryString = "";
            if ($type == 'redirect') {
                $redirectTo = 'customUrl';
                $queryString = $this->getResolveShortcodes(ArrayHelper::get($confirmation, 'queryString', ''), $form);
            } elseif ($type == 'page') {
                $queryString = $this->getResolveShortcodes(ArrayHelper::get($confirmation, 'queryString', ''), $form);
                $redirectTo = 'customPage';
            } else {
                $redirectTo = 'samePage';
            }

            $format = [
                'name'                 => ArrayHelper::get($confirmation, 'name', 'Confirmation'),
                'messageToShow'        => str_replace("\n", "<br />", $this->getResolveShortcodes(ArrayHelper::get($confirmation, 'message', ''), $form)),
                'samePageFormBehavior' => 'hide_form',
                'redirectTo'           => $redirectTo,
                'customPage'           => intval(ArrayHelper::get($confirmation, 'page')),
                'customUrl'            => ArrayHelper::get($confirmation, 'url'),
                'active'               => ArrayHelper::get($confirmation, 'isActive', true),
                'enable_query_string'  => $queryString ? 'yes' : 'no',
                'query_strings'        => $queryString,
            ];
            $isDefault = ArrayHelper::isTrue($confirmation, 'isDefault');
            if (!$isDefault) {
                $format['conditionals'] = $this->getConditionals($confirmation,'confirmation', $form);
            }
            $confirmationsFormatted[] =  wp_parse_args($format, $defaultValues);
        }
        return $confirmationsFormatted;
    }

    /**
     * Get form field name in fluentforms format
     *
     * @param string $str
     * @param array $form
     * @return string
     */
    private function getFormFieldName($str, $form)
    {
        preg_match('/[0-9]+[.]?[0-9]*/', $str, $fieldId);
        $fieldId = ArrayHelper::get($fieldId, 0, '0');
        if (!$fieldId) {
            return '';
        }
        $fieldIds = [];
        if (strpos($fieldId, '.') !== false) {
            $fieldIds = explode('.', $fieldId);
            $fieldId = $fieldId[0];
        }
        $field = [];
        foreach (ArrayHelper::get($form, 'fields', []) as $formField) {
            if (isset($formField->id) && $formField->id == $fieldId) {
                $field = (array)$formField;
                break;
            }
        }
        list($type, $args) = $this->formatFieldData($field);
        $name = ArrayHelper::get($args, 'name', '');
        if ($fieldIds && ArrayHelper::get($fieldIds, 1)) {
            foreach (ArrayHelper::get($field, "inputs", []) as $input) {
                if (ArrayHelper::get($input, 'id') != join('.', $fieldIds)) {
                    continue;
                }
                if ($subName = $this->getInputName($input)) {
                    $name .= ".$subName";
                }
                break;
            }
        }
        return $name;
    }

    /**
     * Resolve shortcode in fluentforms format
     *
     * @param string $message
     * @param array $form
     * @return string
     */
    private function getResolveShortcodes($message, $form)
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
            } elseif ($this->isFormField($match) && $name = $this->getFormFieldName($match, $form)) {
                $replace = "{inputs.$name}";
            }
            $message = str_replace($match, $replace, $message);
        }
        return $message;
    }

    /**
     * Get bool value depend on shortcode is form inputs or not
     *
     * @param string $shortcode
     * @return boolean
     */
    private function isFormField($shortcode)
    {
        preg_match('/:[0-9]+[.]?[0-9]*/', $shortcode, $fieldId);
        return ArrayHelper::isTrue($fieldId, '0');
    }

    /**
     * Get shortcode in fluentforms format
     * @return array
     */
    private function dynamicShortcodes()
    {
        return [
            '{all_fields}'            => '{all_data}',
            '{admin_email}'           => '{wp.admin_email}',
            '{ip}'                    => '{ip}',
            '{date_mdy}'              => '{date.m/d/Y}',
            '{date_dmy}'              => '{date.d/m/Y}',
            '{embed_post:ID}'         => '{embed_post.ID}',
            '{embed_post:post_title}' => '{embed_post.post_title}',
            '{embed_url}'             => '{embed_post.permalink}',
            '{user:id}'               => '{user.ID}',
            '{user:first_name}'       => '{user.first_name}',
            '{user:last_name}'        => '{user.last_name}',
            '{user:user_login}'       => '{user.user_login}',
            '{user:display_name}'     => '{user.display_name}',
            '{user_full_name}'        => '{user.first_name} {user.last_name}',
            '{user:user_email}'       => '{user.user_email}',
            '{entry_id}'              => '{submission.id}',
            '{entry_url}'             => '{submission.admin_view_url}',
            '{referer}'               => '{http_referer}',
            '{user_agent}'            => '{browser.name}'
        ];
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

    protected function getForms()
    {
        $forms = \GFAPI::get_forms();
        return $forms;
    }

    protected function getForm($id)
    {
        if ($form = \GFAPI::get_form($id)) {
            return $form;
        }
        return false;
    }

    protected function getFormName($form)
    {
        return $form['title'];
    }

    public function getEntries($formId)
    {
        $form = $this->getForm($formId);
        if (empty($form)) {
            return false;
        }

        /**
         * Note - more-then 5000/6000 (based on sever) entries process make timout response / set default limit 1000
         * @todo need silently async processing for support all entries migrate at a time, and improve frontend entry-migrate with more settings options
         */
        $totalEntries = \GFAPI::count_entries($formId);
        $perPage = apply_filters('fluentform/entry_migration_max_limit', static::DEFAULT_ENTRY_MIGRATION_MAX_LIMIT, $this->key , $totalEntries, $formId);
        $offset = 0;
        $paging = [
            'offset'    => $offset,
            'page_size' => $perPage
        ];
        $shorting = [
            'key' => 'id',
            'direction' => 'ASC',
        ];
        $submissions = \GFAPI::get_entries($formId, [], $shorting, $paging);
        $entries = [];
        if (!is_array($submissions)) {
            return $entries;
        }

        $fieldsMap = $this->getFields($form);
        foreach ($submissions as $submission) {
            $entry = [];
            foreach ($fieldsMap['fields'] as $id => $field) {
                $name = ArrayHelper::get($field, 'attributes.name');
                if (!$name) {
                    continue;
                }

                $type = ArrayHelper::get($field, 'element');
                $fieldModel = \GFFormsModel::get_field($form, $id);

                // format entry value by field name
                $finalValue = null;
                if ("input_file" == $type && $value = $this->getSubmissionValue($id, $submission)) {
                    $finalValue = $this->migrateFilesAndGetUrls($value);
                } elseif ("repeater_field" == $type && $value = $this->getSubmissionValue($id, $submission)) {
                    if ($repeatData = (array)maybe_unserialize($value)) {
                        $finalValue = [];
                        foreach ($repeatData as $data) {
                            $finalValue[] = array_values($data);
                        }
                    }
                } elseif (
                    "select" == $type &&
                    ArrayHelper::isTrue($field, 'attributes.multiple') &&
                    $value = $this->getSubmissionValue($id, $submission)
                ) {
                    $finalValue = \json_decode($value);
                } elseif (
                    in_array($type, ["input_checkbox", "address", "input_name", "terms_and_condition"]) &&
                    isset($fieldModel['inputs'])
                ) {
                    $finalValue = $this->getSubmissionArrayValue($type, $field, $fieldModel['inputs'], $submission);
                    if ("input_checkbox" == $type) {
                        $finalValue = array_values($finalValue);
                    } elseif ("terms_and_condition" == $type) {
                        $finalValue = $finalValue ? 'on' : 'off';
                    }
                }

                if (!$finalValue) {
                    $finalValue = is_object($fieldModel) ? $fieldModel->get_value_export($submission, $id) : '';
                }
                $entry[$name] = $finalValue;
            }
            if ($created_at = ArrayHelper::get($submission, 'date_created')) {
                $entry['created_at'] = $created_at;
            }
            if ($updated_at = ArrayHelper::get($submission, 'date_updated')) {
                $entry['updated_at'] = $updated_at;
            }
            if ($is_favourite = ArrayHelper::get($submission, 'is_starred')) {
                $entry['is_favourite'] = $is_favourite;
            }
            if ($status = ArrayHelper::get($submission, 'status')) {
                if ('trash' == $status || 'spam' == $status) {
                    $entry['status'] = 'trashed';
                } elseif ('active' == $status && ArrayHelper::isTrue($submission, 'is_read')) {
                    $entry['status'] = 'read';
                }
            }
            $entries[] = $entry;
        }
        return $entries;
    }

    /**
     * @param $form
     * @return mixed
     */
    protected function getFormId($form)
    {
        if (isset($form['id'])) {
            return $form['id'];
        }
        return false;
    }

    protected function getSubmissionArrayValue($type, $field, $inputs, $submission)
    {
        $arrayValue = [];
        foreach ($inputs as $input) {
            if (!isset($submission[$input['id']])) {
                continue;
            }
            if ("input_name" == $type && $subFields = ArrayHelper::get($field, 'fields')) {
                foreach ($subFields as $subField) {
                    if (
                        $input['label'] == ArrayHelper::get($subField, 'settings.label') &&
                        $subName = ArrayHelper::get($subField, 'attributes.name', '')
                    ) {
                        $arrayValue[$subName] = $submission[$input['id']];
                    }
                }
            } else {
                $arrayValue[] = $submission[$input['id']];
            }
        }
        if ('address' == $type) {
            $arrayValue = array_combine([
                "address_line_1",
                "address_line_2",
                "city",
                "state",
                "zip",
                "country"
            ], $arrayValue);
        }
        return array_filter($arrayValue);
    }

    protected function getSubmissionValue($id, $submission)
    {
        return  isset($submission[$id]) ? $submission[$id] : "";
    }
}
