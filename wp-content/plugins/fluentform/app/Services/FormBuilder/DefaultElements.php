<?php
$defaultGlobalMessages = \FluentForm\App\Helpers\Helper::getAllGlobalDefaultMessages();
$defaultElements = [
    'general' => [
        'input_name' => [
            'index'      => 0,
            'element'    => 'input_name',
            'attributes' => [
                'name'      => 'names',
                'data-type' => 'name-element',
            ],
            'settings' => [
                'container_class'    => '',
                'admin_field_label'  => 'Name',
                'conditional_logics' => [],
                'label_placement'    => 'top',
            ],
            'fields' => [
                'first_name' => [
                    'element'    => 'input_text',
                    'attributes' => [
                        'type'        => 'text',
                        'name'        => 'first_name',
                        'value'       => '',
                        'id'          => '',
                        'class'       => '',
                        'placeholder' => __('First Name', 'fluentform'),
                        'maxlength'   => '',
                    ],
                    'settings' => [
                        'container_class'  => '',
                        'label'            => __('First Name', 'fluentform'),
                        'help_message'     => '',
                        'visible'          => true,
                        'validation_rules' => [
                            'required' => [
                                'value'   => false,
                                'message' => $defaultGlobalMessages['required'],
                                'global_message' => $defaultGlobalMessages['required'],
                                'global'  => true,
                            ],
                        ],
                        'conditional_logics' => [],
                    ],
                    'editor_options' => [
                        'template' => 'inputText',
                    ],
                ],
                'middle_name' => [
                    'element'    => 'input_text',
                    'attributes' => [
                        'type'        => 'text',
                        'name'        => 'middle_name',
                        'value'       => '',
                        'id'          => '',
                        'class'       => '',
                        'placeholder' => __('Middle Name', 'fluentform'),
                        'required'    => false,
                        'maxlength'   => '',
                    ],
                    'settings' => [
                        'container_class'  => '',
                        'label'            => __('Middle Name', 'fluentform'),
                        'help_message'     => '',
                        'error_message'    => '',
                        'visible'          => false,
                        'validation_rules' => [
                            'required' => [
                                'value'   => false,
                                'message' => $defaultGlobalMessages['required'],
                                'global_message' => $defaultGlobalMessages['required'],
                                'global'  => true,
                            ],
                        ],
                        'conditional_logics' => [],
                    ],
                    'editor_options' => [
                        'template' => 'inputText',
                    ],
                ],
                'last_name' => [
                    'element'    => 'input_text',
                    'attributes' => [
                        'type'        => 'text',
                        'name'        => 'last_name',
                        'value'       => '',
                        'id'          => '',
                        'class'       => '',
                        'placeholder' => __('Last Name', 'fluentform'),
                        'required'    => false,
                        'maxlength'   => '',
                    ],
                    'settings' => [
                        'container_class'  => '',
                        'label'            => __('Last Name', 'fluentform'),
                        'help_message'     => '',
                        'error_message'    => '',
                        'visible'          => true,
                        'validation_rules' => [
                            'required' => [
                                'value'   => false,
                                'message' => $defaultGlobalMessages['required'],
                                'global_message' => $defaultGlobalMessages['required'],
                                'global'  => true,
                            ],
                        ],
                        'conditional_logics' => [],
                    ],
                    'editor_options' => [
                        'template' => 'inputText',
                    ],
                ],
            ],
            'editor_options' => [
                'title'      => 'Name Fields',
                'element'    => 'name-fields',
                'icon_class' => 'ff-edit-name',
                'template'   => 'nameFields',
            ],
        ],
        'input_email' => [
            'index'      => 1,
            'element'    => 'input_email',
            'attributes' => [
                'type'        => 'email',
                'name'        => 'email',
                'value'       => '',
                'id'          => '',
                'class'       => '',
                'placeholder' => 'Email Address',
            ],
            'settings' => [
                'container_class'   => '',
                'label'             => __('Email', 'fluentform'),
                'label_placement'   => '',
                'help_message'      => '',
                'admin_field_label' => '',
                'prefix_label'      => '',
                'suffix_label'      => '',
                'validation_rules'  => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                    'email' => [
                        'value'   => true,
                        'message' => $defaultGlobalMessages['email'],
                        'global_message' => $defaultGlobalMessages['email'],
                        'global'  => true
                    ],
                ],
                'conditional_logics'        => [],
                'is_unique'                 => 'no',
                'unique_validation_message' => __('Email address need to be unique.', 'fluentform'),
            ],
            'editor_options' => [
                'title'      => __('Email', 'fluentform'),
                'icon_class' => 'ff-edit-email',
                'template'   => 'inputText',
            ],
        ],
        'input_text' => [
            'index'      => 2,
            'element'    => 'input_text',
            'attributes' => [
                'type'        => 'text',
                'name'        => 'input_text',
                'value'       => '',
                'class'       => '',
                'placeholder' => '',
                'maxlength'   => '',
            ],
            'settings' => [
                'container_class'   => '',
                'label'             => __('Text Input', 'fluentform'),
                'label_placement'   => '',
                'admin_field_label' => '',
                'help_message'      => '',
                'prefix_label'      => '',
                'suffix_label'      => '',
                'validation_rules'  => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
                'conditional_logics'        => [],
                'is_unique'                 => 'no',
                'unique_validation_message' => __('This value need to be unique.', 'fluentform'),
            ],
            'editor_options' => [
                'title'      => __('Simple Text', 'fluentform'),
                'icon_class' => 'ff-edit-text',
                'template'   => 'inputText',
            ],
        ],
        'input_mask' => [
            'index'      => 2,
            'element'    => 'input_text',
            'attributes' => [
                'type'        => 'text',
                'name'        => 'input_mask',
                'data-mask'   => '',
                'value'       => '',
                'class'       => '',
                'placeholder' => '',
            ],
            'settings' => [
                'container_class'         => '',
                'label'                   => __('Mask Input', 'fluentform'),
                'label_placement'         => '',
                'admin_field_label'       => '',
                'help_message'            => '',
                'temp_mask'               => '',
                'prefix_label'            => '',
                'suffix_label'            => '',
                'data-mask-reverse'       => 'no',
                'data-clear-if-not-match' => 'no',
                'validation_rules'        => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => __('Mask Input', 'fluentform'),
                'icon_class' => 'ff-edit-mask',
                'template'   => 'inputText',
            ],
        ],
        'textarea' => [
            'index'      => 3,
            'element'    => 'textarea',
            'attributes' => [
                'name'        => 'description',
                'value'       => '',
                'id'          => '',
                'class'       => '',
                'placeholder' => '',
                'rows'        => 3,
                'cols'        => 2,
                'maxlength'   => '',
            ],
            'settings' => [
                'container_class'   => '',
                'label'             => __('Textarea', 'fluentform'),
                'admin_field_label' => '',
                'label_placement'   => '',
                'help_message'      => '',
                'validation_rules'  => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => __('Text Area', 'fluentform'),
                'icon_class' => 'ff-edit-textarea',
                'template'   => 'inputTextarea',
            ],
        ],
        'address' => [
            'index'      => 4,
            'element'    => 'address',
            'attributes' => [
                'id'        => '',
                'class'     => '',
                'name'      => 'address_1',
                'data-type' => 'address-element',
            ],
            'settings' => [
                'label'                 => __('Address', 'fluentform'),
                'enable_g_autocomplete' => 'no',
                'admin_field_label'     => 'Address',
                'field_order'           => [
                    ['id' => 1, 'value' => 'address_line_1'],
                    ['id' => 2, 'value' => 'address_line_2'],
                    ['id' => 3, 'value' => 'city'],
                    ['id' => 4, 'value' => 'state'],
                    ['id' => 5, 'value' => 'zip'],
                    ['id' => 6, 'value' => 'country'],
                ],
                'conditional_logics' => [],
            ],
            'fields' => [
                'address_line_1' => [
                    'element'    => 'input_text',
                    'attributes' => [
                        'type'        => 'text',
                        'name'        => 'address_line_1',
                        'value'       => '',
                        'id'          => '',
                        'class'       => '',
                        'placeholder' => __('Address Line 1', 'fluentform'),
                    ],
                    'settings' => [
                        'container_class'   => '',
                        'label'             => __('Address Line 1', 'fluentform'),
                        'admin_field_label' => '',
                        'help_message'      => '',
                        'visible'           => true,
                        'validation_rules'  => [
                            'required' => [
                                'value'   => false,
                                'message' => $defaultGlobalMessages['required'],
                                'global_message' => $defaultGlobalMessages['required'],
                                'global'  => true,
                            ],
                        ],
                        'conditional_logics' => [],
                    ],
                    'editor_options' => [
                        'template' => 'inputText',
                    ],
                ],
                'address_line_2' => [
                    'element'    => 'input_text',
                    'attributes' => [
                        'type'        => 'text',
                        'name'        => 'address_line_2',
                        'value'       => '',
                        'id'          => '',
                        'class'       => '',
                        'placeholder' => __('Address Line 2', 'fluentform'),
                    ],
                    'settings' => [
                        'container_class'   => '',
                        'label'             => __('Address Line 2', 'fluentform'),
                        'admin_field_label' => '',
                        'help_message'      => '',
                        'visible'           => true,
                        'validation_rules'  => [
                            'required' => [
                                'value'   => false,
                                'message' => $defaultGlobalMessages['required'],
                                'global_message' => $defaultGlobalMessages['required'],
                                'global'  => true,
                            ],
                        ],
                        'conditional_logics' => [],
                    ],
                    'editor_options' => [
                        'template' => 'inputText',
                    ],
                ],
                'city' => [
                    'element'    => 'input_text',
                    'attributes' => [
                        'type'        => 'text',
                        'name'        => 'city',
                        'value'       => '',
                        'id'          => '',
                        'class'       => '',
                        'placeholder' => __('City', 'fluentform'),
                    ],
                    'settings' => [
                        'container_class'   => '',
                        'label'             => __('City', 'fluentform'),
                        'admin_field_label' => '',
                        'help_message'      => '',
                        'error_message'     => '',
                        'visible'           => true,
                        'validation_rules'  => [
                            'required' => [
                                'value'   => false,
                                'message' => $defaultGlobalMessages['required'],
                                'global_message' => $defaultGlobalMessages['required'],
                                'global'  => true,
                            ],
                        ],
                        'conditional_logics' => [],
                    ],
                    'editor_options' => [
                        'template' => 'inputText',
                    ],
                ],
                'state' => [
                    'element'    => 'input_text',
                    'attributes' => [
                        'type'        => 'text',
                        'name'        => 'state',
                        'value'       => '',
                        'id'          => '',
                        'class'       => '',
                        'placeholder' => __('State', 'fluentform'),
                    ],
                    'settings' => [
                        'container_class'   => '',
                        'label'             => __('State', 'fluentform'),
                        'admin_field_label' => '',
                        'help_message'      => '',
                        'error_message'     => '',
                        'visible'           => true,
                        'validation_rules'  => [
                            'required' => [
                                'value'   => false,
                                'message' => $defaultGlobalMessages['required'],
                                'global_message' => $defaultGlobalMessages['required'],
                                'global'  => true,
                            ],
                        ],
                        'conditional_logics' => [],
                    ],
                    'editor_options' => [
                        'template' => 'inputText',
                    ],
                ],
                'zip' => [
                    'element'    => 'input_text',
                    'attributes' => [
                        'type'        => 'text',
                        'name'        => 'zip',
                        'value'       => '',
                        'id'          => '',
                        'class'       => '',
                        'placeholder' => __('Zip', 'fluentform'),
                        'required'    => false,
                    ],
                    'settings' => [
                        'container_class'   => '',
                        'label'             => __('Zip Code', 'fluentform'),
                        'admin_field_label' => '',
                        'help_message'      => '',
                        'error_message'     => '',
                        'visible'           => true,
                        'validation_rules'  => [
                            'required' => [
                                'value'   => false,
                                'message' => $defaultGlobalMessages['required'],
                                'global_message' => $defaultGlobalMessages['required'],
                                'global'  => true,
                            ],
                        ],
                        'conditional_logics' => [],
                    ],
                    'editor_options' => [
                        'template' => 'inputText',
                    ],
                ],
                'country' => [
                    'element'    => 'select_country',
                    'attributes' => [
                        'name'        => 'country',
                        'value'       => '',
                        'id'          => '',
                        'class'       => '',
                        'placeholder' => __('Select Country', 'fluentform'),
                        'required'    => false,
                    ],
                    'settings' => [
                        'container_class'   => '',
                        'label'             => __('Country', 'fluentform'),
                        'admin_field_label' => '',
                        'help_message'      => '',
                        'error_message'     => '',
                        'visible'           => true,
                        'validation_rules'  => [
                            'required' => [
                                'value'   => false,
                                'message' => $defaultGlobalMessages['required'],
                                'global_message' => $defaultGlobalMessages['required'],
                                'global'  => true,
                            ],
                        ],
                        'country_list' => [
                            'active_list'  => 'all',
                            'visible_list' => [],
                            'hidden_list'  => [],
                        ],
                        'conditional_logics' => [],
                    ],
                    'options' => [
                        'US' => 'US of America',
                        'UK' => 'United Kingdom',
                    ],
                    'editor_options' => [
                        'title'      => 'Country List',
                        'element'    => 'country-list',
                        'icon_class' => 'icon-text-width',
                        'template'   => 'selectCountry',
                    ],
                ],
            ],
            'editor_options' => [
                'title'      => __('Address Fields', 'fluentform'),
                'element'    => 'address-fields',
                'icon_class' => 'ff-edit-address',
                'template'   => 'addressFields',
            ],
        ],
        'input_number' => [
            'index'      => 6,
            'element'    => 'input_number',
            'attributes' => [
                'type'        => 'number',
                'name'        => 'numeric-field',
                'value'       => '',
                'id'          => '',
                'class'       => '',
                'placeholder' => '',
            ],
            'settings' => [
                'container_class'   => '',
                'label'             => __('Numeric Field', 'fluentform'),
                'admin_field_label' => '',
                'label_placement'   => '',
                'help_message'      => '',
                'number_step'       => '',
                'prefix_label'      => '',
                'suffix_label'      => '',
                'numeric_formatter' => '',
                'validation_rules'  => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                    'numeric' => [
                        'value'   => true,
                        'message' => $defaultGlobalMessages['numeric'],
                        'global_message' => $defaultGlobalMessages['numeric'],
                        'global'  => true
                    ],
                    'min' => [
                        'value'   => '',
                        'message' => $defaultGlobalMessages['min'],
                        'global_message' => $defaultGlobalMessages['min'],
                        'global'  => true
                    ],
                    'max' => [
                        'value'   => '',
                        'message' => $defaultGlobalMessages['max'],
                        'global_message' => $defaultGlobalMessages['max'],
                        'global'  => true
                    ],
                    'digits' => [
                        'value'   => '',
                        'message' => $defaultGlobalMessages['digits'],
                        'global_message' => $defaultGlobalMessages['digits'],
                        'global'  => true
                    ],
                ],
                'conditional_logics'   => [],
                'calculation_settings' => [
                    'status'  => false,
                    'formula' => '',
                ],
            ],
            'editor_options' => [
                'title'      => __('Numeric Field', 'fluentform'),
                'icon_class' => 'ff-edit-numeric',
                'template'   => 'inputText',
            ],
        ],
        'select' => [
            'index'      => 7,
            'element'    => 'select',
            'attributes' => [
                'name'  => 'dropdown',
                'value' => '',
                'id'    => '',
                'class' => '',
            ],
            'settings' => [
                'dynamic_default_value' => '',
                'label'                 => __('Dropdown', 'fluentform'),
                'admin_field_label'     => '',
                'help_message'          => '',
                'container_class'       => '',
                'label_placement'       => '',
                'placeholder'           => '- Select -',
                'advanced_options'      => [
                    [
                        'label'      => 'Option 1',
                        'value'      => 'Option 1',
                        'calc_value' => '',
                    ],
                    [
                        'label'      => 'Option 2',
                        'value'      => 'Option 2',
                        'calc_value' => '',
                    ],
                ],
                'calc_value_status'  => false,
                'enable_image_input' => false,
                'values_visible'     => false,
                'enable_select_2'    => 'no',
                'validation_rules'   => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
                'conditional_logics' => [],
                'randomize_options'  => 'no',
            ],
            'editor_options' => [
                'title'      => __('Dropdown', 'fluentform'),
                'icon_class' => 'ff-edit-dropdown',
                'element'    => 'select',
                'template'   => 'select',
            ],
        ],
        'input_radio' => [
            'index'      => 8,
            'element'    => 'input_radio',
            'attributes' => [
                'type'  => 'radio',
                'name'  => 'input_radio',
                'value' => '',
            ],
            'settings' => [
                'dynamic_default_value' => '',
                'container_class'       => '',
                'label'                 => __('Radio Field', 'fluentform'),
                'admin_field_label'     => '',
                'label_placement'       => '',
                'display_type'          => '',
                'help_message'          => '',
                'randomize_options'     => 'no',
                'advanced_options'      => [
                    [
                        'label'      => 'Yes',
                        'value'      => 'yes',
                        'calc_value' => '',
                        'image'      => '',
                    ],
                    [
                        'label'      => 'No',
                        'value'      => 'no',
                        'calc_value' => '',
                        'image'      => '',
                    ],
                ],
                'calc_value_status'  => false,
                'enable_image_input' => false,
                'values_visible'     => false,
                'validation_rules'   => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
                'conditional_logics' => [],
                'layout_class'       => '',
            ],
            'editor_options' => [
                'title'      => __('Radio Field', 'fluentform'),
                'icon_class' => 'ff-edit-radio',
                'element'    => 'input-radio',
                'template'   => 'inputCheckable',
            ],
        ],
        'input_checkbox' => [
            'index'      => 9,
            'element'    => 'input_checkbox',
            'attributes' => [
                'type'  => 'checkbox',
                'name'  => 'checkbox',
                'value' => [],
            ],
            'settings' => [
                'dynamic_default_value' => '',
                'container_class'       => '',
                'label'                 => __('Checkbox Field', 'fluentform'),
                'admin_field_label'     => '',
                'label_placement'       => '',
                'display_type'          => '',
                'help_message'          => '',
                'advanced_options'      => [
                    [
                        'label'      => 'Item 1',
                        'value'      => 'Item 1',
                        'calc_value' => '',
                        'image'      => '',
                    ],
                    [
                        'label'      => 'Item 2',
                        'value'      => 'Item 2',
                        'calc_value' => '',
                        'image'      => '',
                    ],
                    [
                        'label'      => 'Item 3',
                        'value'      => 'Item 3',
                        'calc_value' => '',
                        'image'      => '',
                    ],
                ],
                'calc_value_status'  => false,
                'enable_image_input' => false,
                'values_visible'     => false,
                'randomize_options'  => 'no',
                'validation_rules'   => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
                'conditional_logics' => [],
                'layout_class'       => '',
            ],
            'editor_options' => [
                'title'      => __('Check Box', 'fluentform'),
                'icon_class' => 'ff-edit-checkbox-1',
                'template'   => 'inputCheckable',
            ],
        ],
        'multi_select' => [
            'index'      => 10,
            'element'    => 'select',
            'attributes' => [
                'name'        => 'multi_select',
                'value'       => [],
                'id'          => '',
                'class'       => '',
                'placeholder' => '',
                'multiple'    => true,
            ],
            'settings' => [
                'dynamic_default_value' => '',
                'help_message'          => '',
                'container_class'       => '',
                'label'                 => __('Multiselect', 'fluentform'),
                'admin_field_label'     => '',
                'label_placement'       => '',
                'placeholder'           => '',
                'max_selection'         => '',
                'advanced_options'      => [
                    [
                        'label'      => 'Option 1',
                        'value'      => 'Option 1',
                        'calc_value' => '',
                    ],
                    [
                        'label'      => 'Option 2',
                        'value'      => 'Option 2',
                        'calc_value' => '',
                    ],
                ],
                'calc_value_status'  => false,
                'enable_image_input' => false,
                'randomize_options'  => 'no',
                'validation_rules'   => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => __('Multiple Choice', 'fluentform'),
                'icon_class' => 'ff-edit-multiple-choice',
                'element'    => 'select',
                'template'   => 'select',
            ],
        ],
        'input_url' => [
            'index'      => 11,
            'element'    => 'input_url',
            'attributes' => [
                'type'        => 'url',
                'name'        => 'url',
                'value'       => '',
                'class'       => '',
                'placeholder' => '',
            ],
            'settings' => [
                'container_class'   => '',
                'label'             => __('URL', 'fluentform'),
                'admin_field_label' => '',
                'label_placement'   => '',
                'help_message'      => '',
                'validation_rules'  => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                    'url' => [
                        'value'   => true,
                        'message' => $defaultGlobalMessages['url'],
                        'global_message' => $defaultGlobalMessages['url'],
                        'global'  => true
                    ],
                ],
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => __('Website URL', 'fluentform'),
                'icon_class' => 'ff-edit-website-url',
                'template'   => 'inputText',
            ],
        ],
        'input_date' => [
            'index'      => 13,
            'element'    => 'input_date',
            'attributes' => [
                'type'        => 'text',
                'name'        => 'datetime',
                'value'       => '',
                'id'          => '',
                'class'       => '',
                'placeholder' => '',
            ],
            'settings' => [
                'container_class'   => '',
                'label'             => __('Date / Time', 'fluentform'),
                'admin_field_label' => '',
                'label_placement'   => '',
                'date_config'       => '',
                'date_format'       => 'd/m/Y',
                'help_message'      => '',
                'is_time_enabled'   => true,
                'validation_rules'  => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => __('Time & Date', 'fluentform'),
                'icon_class' => 'ff-edit-date',
                'template'   => 'inputText',
            ],
        ],
        'input_image' => [
            'index'      => 15,
            'element'    => 'input_image',
            'attributes' => [
                'type'   => 'file',
                'name'   => 'image-upload',
                'value'  => '',
                'id'     => '',
                'class'  => '',
                'accept' => 'image/*',
            ],
            'settings' => [
                'container_class'      => '',
                'label'                => __('Image Upload', 'fluentform'),
                'admin_field_label'    => '',
                'label_placement'      => '',
                'btn_text'             => 'Choose File',
                'upload_file_location' => 'default',
                'file_location_type'   => 'follow_global_settings',
                'help_message'         => '',
                'validation_rules'     => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                    'max_file_size' => [
                        'value'      => 1048576,
                        '_valueFrom' => 'MB',
                        'message'    => $defaultGlobalMessages['max_file_size'],
                        'global_message'    => $defaultGlobalMessages['max_file_size'],
                        'global'     => true
                    ],
                    'max_file_count' => [
                        'value'   => 1,
                        'message' => $defaultGlobalMessages['max_file_count'],
                        'global_message' => $defaultGlobalMessages['max_file_count'],
                        'global'  => true
                    ],
                    'allowed_image_types' => [
                        'value'   => [],
                        'message' => $defaultGlobalMessages['allowed_image_types'],
                        'global_message' => $defaultGlobalMessages['allowed_image_types'],
                        'global'  => true
                    ],
                ],
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => __('Image Upload', 'fluentform'),
                'icon_class' => 'ff-edit-images',
                'template'   => 'inputFile',
            ],
        ],
        'input_file' => [
            'index'      => 16,
            'element'    => 'input_file',
            'attributes' => [
                'type'  => 'file',
                'name'  => 'file-upload',
                'value' => '',
                'id'    => '',
                'class' => '',
            ],
            'settings' => [
                'container_class'      => '',
                'label'                => __('File Upload', 'fluentform'),
                'admin_field_label'    => '',
                'label_placement'      => '',
                'btn_text'             => 'Choose File',
                'help_message'         => '',
                'upload_file_location' => 'default',
                'file_location_type'   => 'follow_global_settings',
                'validation_rules'     => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                    'max_file_size' => [
                        'value'      => 1048576,
                        '_valueFrom' => 'MB',
                        'message'    => $defaultGlobalMessages['max_file_size'],
                        'global_message'    => $defaultGlobalMessages['max_file_size'],
                        'global'     => true
                    ],
                    'max_file_count' => [
                        'value'   => 1,
                        'message' => $defaultGlobalMessages['max_file_count'],
                        'global_message' => $defaultGlobalMessages['max_file_count'],
                        'global'  => true
                    ],
                    'allowed_file_types' => [
                        'value'   => [],
                        'message' => $defaultGlobalMessages['allowed_image_types'],
                        'global_message' => $defaultGlobalMessages['allowed_image_types'],
                        'global'  => true
                    ],
                ],
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => __('File Upload', 'fluentform'),
                'icon_class' => 'ff-edit-files',
                'template'   => 'inputFile',
            ],
        ],
        'select_country' => [
            'index'      => 5,
            'element'    => 'select_country',
            'attributes' => [
                'name'        => 'country-list',
                'value'       => '',
                'id'          => '',
                'class'       => '',
                'placeholder' => __('Select Country', 'fluentform'),
            ],
            'settings' => [
                'container_class'   => '',
                'label'             => __('Country', 'fluentform'),
                'admin_field_label' => '',
                'label_placement'   => '',
                'help_message'      => '',
                'enable_select_2'   => 'no',
                'validation_rules'  => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
                'country_list' => [
                    'active_list'  => 'all',
                    'visible_list' => [],
                    'hidden_list'  => [],
                ],
                'conditional_logics' => [],
            ],
            'options' => [
                'US' => 'United States of America',
            ],
            'editor_options' => [
                'title'      => __('Country List', 'fluentform'),
                'element'    => 'country-list',
                'icon_class' => 'ff-edit-country',
                'template'   => 'selectCountry',
            ],
        ],
        'custom_html' => [
            'index'      => 17,
            'element'    => 'custom_html',
            'attributes' => [],
            'settings'   => [
                'html_codes'         => '<p>Some description about this section</p>',
                'conditional_logics' => [],
                'container_class'    => '',
            ],
            'editor_options' => [
                'title'      => __('Custom HTML', 'fluentform'),
                'icon_class' => 'ff-edit-html',
                'template'   => 'customHTML',
            ],
        ],
    ],
    'advanced' => [
        'ratings' => [
            'index'      => 8,
            'element'    => 'ratings',
            'attributes' => [
                'class' => '',
                'value' => 0,
                'name'  => 'ratings',
            ],
            'settings' => [
                'label'              => __('Ratings', 'fluentform'),
                'show_text'          => 'no',
                'help_message'       => '',
                'label_placement'    => '',
                'admin_field_label'  => '',
                'container_class'    => '',
                'conditional_logics' => [],
                'validation_rules'   => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
            ],
            'options' => [
                '1' => __('Nice', 'fluentform'),
                '2' => __('Good', 'fluentform'),
                '3' => __('Very Good', 'fluentform'),
                '4' => __('Awesome', 'fluentform'),
                '5' => __('Amazing', 'fluentform'),
            ],
            'editor_options' => [
                'title'      => __('Ratings', 'fluentform'),
                'icon_class' => 'ff-edit-rating',
                'template'   => 'ratings',
            ],
        ],
        'input_hidden' => [
            'index'      => 0,
            'element'    => 'input_hidden',
            'attributes' => [
                'type'  => 'hidden',
                'name'  => 'hidden',
                'value' => '',
            ],
            'settings' => [
                'admin_field_label' => '',
            ],
            'editor_options' => [
                'title'      => __('Hidden Field', 'fluentform'),
                'icon_class' => 'ff-edit-hidden-field',
                'template'   => 'inputHidden',
            ],
        ],
        'tabular_grid' => [
            'index'      => 9,
            'element'    => 'tabular_grid',
            'attributes' => [
                'name'      => 'tabular_grid',
                'data-type' => 'tabular-element',
            ],
            'settings' => [
                'tabular_field_type' => 'checkbox',
                'container_class'    => '',
                'label'              => __('Checkbox Grid', 'fluentform'),
                'admin_field_label'  => '',
                'label_placement'    => '',
                'help_message'       => '',
                'validation_rules'   => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                        'per_row' => false,
                    ],
                ],
                'conditional_logics' => [],
                'grid_columns'       => [
                    'Column-1' => 'Column 1',
                ],
                'grid_rows' => [
                    'Row-1' => 'Row 1',
                ],
                'selected_grids' => [],
            ],
            'editor_options' => [
                'title'      => __('Checkable Grid', 'fluentform'),
                'icon_class' => 'ff-edit-checkable-grid',
                'template'   => 'checkableGrids',
            ],
        ],
        'section_break' => [
            'index'      => 1,
            'element'    => 'section_break',
            'attributes' => [
                'id'    => '',
                'class' => '',
            ],
            'settings' => [
                'label'              => __('Section Break', 'fluentform'),
                'description'        => __('Some description about this section', 'fluentform'),
                'align'              => 'left',
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => __('Section Break', 'fluentform'),
                'icon_class' => 'ff-edit-section-break',
                'template'   => 'sectionBreak',
            ],
        ],
        'input_password' => [
            'index'      => 11,
            'element'    => 'input_password',
            'attributes' => [
                'type'        => 'password',
                'name'        => 'password',
                'value'       => '',
                'id'          => '',
                'class'       => '',
                'placeholder' => '',
            ],
            'settings' => [
                'container_class'   => '',
                'label'             => __('Password', 'fluentform'),
                'admin_field_label' => '',
                'label_placement'   => '',
                'help_message'      => '',
                'validation_rules'  => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => __('Password', 'fluentform'),
                'icon_class' => 'ff-edit-password',
                'template'   => 'inputText',
            ],
        ],
        'form_step' => [
            'index'      => 7,
            'element'    => 'form_step',
            'attributes' => [
                'id'    => '',
                'class' => '',
            ],
            'settings' => [
                'prev_btn' => [
                    'type'    => 'default',
                    'text'    => __('Previous', 'fluentform'),
                    'img_url' => '',
                ],
                'next_btn' => [
                    'type'    => 'default',
                    'text'    => __('Next', 'fluentform'),
                    'img_url' => '',
                ],
            ],
            'editor_options' => [
                'title'      => __('Form Step', 'fluentform'),
                'icon_class' => 'ff-edit-step',
                'template'   => 'formStep',
            ],
        ],
        'terms_and_condition' => [
            'index'      => 5,
            'element'    => 'terms_and_condition',
            'attributes' => [
                'type'  => 'checkbox',
                'name'  => 'terms-n-condition',
                'value' => false,
                'class' => '',
            ],
            'settings' => [
                'tnc_html'          => 'I have read and agree to the <a target="_blank" rel="noopener" href="#">Terms and Conditions</a> and <a target="_blank" rel="noopener" href="#">Privacy Policy</a>',
                'has_checkbox'      => true,
                'admin_field_label' => __('Terms and Conditions', 'fluentform'),
                'container_class'   => '',
                'validation_rules'  => [
                    'required' => [
                        'value'   => false,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => __('Terms & Conditions', 'fluentform'),
                'icon_class' => 'ff-edit-terms-condition',
                'template'   => 'termsCheckbox',
            ],
        ],
        'gdpr_agreement' => [
            'index'      => 10,
            'element'    => 'gdpr_agreement',
            'attributes' => [
                'type'  => 'checkbox',
                'name'  => 'gdpr-agreement',
                'value' => false,
                'class' => 'ff_gdpr_field',
            ],
            'settings' => [
                'label'             => __('GDPR Agreement', 'fluentform'),
                'tnc_html'          => __('I consent to have this website store my submitted information so they can respond to my inquiry', 'fluentform'),
                'admin_field_label' => __('GDPR Agreement', 'fluentform'),
                'has_checkbox'      => true,
                'container_class'   => '',
                'validation_rules'  => [
                    'required' => [
                        'value'   => true,
                        'message' => $defaultGlobalMessages['required'],
                        'global_message' => $defaultGlobalMessages['required'],
                        'global'  => true,
                    ],
                ],
                'required_field_message' => '',
                'conditional_logics'     => [],
            ],
            'editor_options' => [
                'title'      => __('GDPR Agreement', 'fluentform'),
                'icon_class' => 'ff-edit-gdpr',
                'template'   => 'termsCheckbox',
            ],
        ],
        'recaptcha' => [
            'index'      => 2,
            'element'    => 'recaptcha',
            'attributes' => ['name' => 'g-recaptcha-response'],
            'settings'   => [
                'label'            => '',
                'label_placement'  => '',
                'validation_rules' => [],
            ],
            'editor_options' => [
                'title'              => __('reCaptcha', 'fluentform'),
                'icon_class'         => 'ff-edit-recaptha',
                'why_disabled_modal' => 'recaptcha',
                'template'           => 'recaptcha',
            ],
        ],
        'hcaptcha' => [
            'index'      => 3,
            'element'    => 'hcaptcha',
            'attributes' => ['name' => 'h-captcha-response'],
            'settings'   => [
                'label'            => '',
                'label_placement'  => '',
                'validation_rules' => [],
            ],
            'editor_options' => [
                'title'              => __('hCaptcha', 'fluentform'),
                'icon_class'         => 'ff-edit-recaptha',
                'why_disabled_modal' => 'hcaptcha',
                'template'           => 'hcaptcha',
            ],
        ],
        'turnstile' => [
            'index'      => 3,
            'element'    => 'turnstile',
            'attributes' => ['name' => 'cf-turnstile-response'],
            'settings'   => [
                'label'            => '',
                'label_placement'  => '',
                'validation_rules' => []
            ],
            'editor_options' => [
                'title'              => __('Turnstile', 'fluentform'),
                'icon_class'         => 'ff-edit-recaptha',
                'why_disabled_modal' => 'turnstile',
                'template'           => 'turnstile',
            ],
        ],
        'shortcode' => [
            'index'      => 4,
            'element'    => 'shortcode',
            'attributes' => [
                'id'    => '',
                'class' => '',
            ],
            'settings' => [
                'shortcode'          => '[your_shortcode_here]',
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => __('Shortcode', 'fluentform'),
                'icon_class' => 'ff-edit-shortcode',
                'template'   => 'shortcode',
            ],
        ],
        'action_hook' => [
            'index'      => 6,
            'element'    => 'action_hook',
            'attributes' => [
                'id'    => '',
                'class' => '',
            ],
            'settings' => [
                'hook_name'          => 'YOUR_CUSTOM_HOOK_NAME',
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => __('Action Hook', 'fluentform'),
                'icon_class' => 'ff-edit-action-hook',
                'template'   => 'actionHook',
            ],
        ],
    ],
    'container' => [
        'container_1_col' => [
            'index'      => 1,
            'element'    => 'container',
            'attributes' => [],
            'settings'   => [
                'container_class'    => '',
                'conditional_logics' => [],
                'is_width_auto_calc' => true,
            ],
            'columns' => [
                ['width' => '', 'left' => '', 'fields' => []],
            ],
            'editor_options' => [
                'title'      => __('One Column Container', 'fluentform'),
                'icon_class' => 'dashicons dashicons-align-center',
            ],
        ],
        'container_2_col' => [
            'index'      => 1,
            'element'    => 'container',
            'attributes' => [],
            'settings'   => [
                'container_class'    => '',
                'conditional_logics' => [],
                'container_width'    => '',
                'is_width_auto_calc' => true,
            ],
            'columns' => [
                ['width' => 50, 'fields' => []],
                ['width' => 50, 'fields' => []],
            ],
            'editor_options' => [
                'title'      => __('Two Column Container', 'fluentform'),
                'icon_class' => 'ff-edit-column-2',
            ],
        ],
        'container_3_col' => [
            'index'      => 2,
            'element'    => 'container',
            'attributes' => [],
            'settings'   => [
                'container_class'    => '',
                'conditional_logics' => [],
                'container_width'    => '',
                'is_width_auto_calc' => true,
            ],
            'columns' => [
                ['width' => 33.33, 'fields' => []],
                ['width' => 33.33, 'fields' => []],
                ['width' => 33.33, 'fields' => []],
            ],
            'editor_options' => [
                'title'      => __('Three Column Container', 'fluentform'),
                'icon_class' => 'ff-edit-three-column',
            ],
        ],
        'container_4_col' => [
            'index'      => 3,
            'element'    => 'container',
            'attributes' => [],
            'settings'   => [
                'container_class'    => '',
                'conditional_logics' => [],
                'container_width'    => '',
                'is_width_auto_calc' => true,
            ],
            'columns' => [
                ['width' => 25, 'fields' => []],
                ['width' => 25, 'fields' => []],
                ['width' => 25, 'fields' => []],
                ['width' => 25, 'fields' => []],
            ],
            'editor_options' => [
                'title'      => __('Four Column Container', 'fluentform'),
                'icon_class' => 'ff-edit-three-column',
            ],
        ],
        'container_5_col' => [
            'index'      => 5,
            'element'    => 'container',
            'attributes' => [],
            'settings'   => [
                'container_class'    => '',
                'conditional_logics' => [],
                'container_width'    => '',
                'is_width_auto_calc' => true,
            ],
            'columns' => [
                ['width' => 20, 'fields' => []],
                ['width' => 20, 'fields' => []],
                ['width' => 20, 'fields' => []],
                ['width' => 20, 'fields' => []],
                ['width' => 20, 'fields' => []],
            ],
            'editor_options' => [
                'title'      => __('Five Column Container', 'fluentform'),
                'icon_class' => 'ff-edit-three-column',
            ],
        ],
        'container_6_col' => [
            'index'      => 6,
            'element'    => 'container',
            'attributes' => [],
            'settings'   => [
                'container_class'    => '',
                'conditional_logics' => [],
                'container_width'    => '',
                'is_width_auto_calc' => true,
            ],
            'columns' => [
                ['width' => 16.67, 'fields' => []],
                ['width' => 16.67, 'fields' => []],
                ['width' => 16.67, 'fields' => []],
                ['width' => 16.67, 'fields' => []],
                ['width' => 16.67, 'fields' => []],
                ['width' => 16.67, 'fields' => []],
            ],
            'editor_options' => [
                'title'      => __('Six Column Container', 'fluentform'),
                'icon_class' => 'ff-edit-three-column',
            ],
        ],
    ],
];

if (! defined('FLUENTFORMPRO')) {
    $defaultElements['general']['phone'] = [
        'index'          => 17,
        'element'        => 'phone',
        'attributes'     => [],
        'settings'       => [],
        'editor_options' => [
            'title'      => __('Phone', 'fluentform'),
            'icon_class' => 'el-icon-phone-outline',
            'template'   => 'inputText',
        ],
    ];
    $defaultElements['advanced']['net_promoter_score'] = [
        'index'          => 14,
        'element'        => 'net_promoter_score',
        'attributes'     => [],
        'settings'       => [],
        'options'        => [],
        'editor_options' => [
            'title'      => __('Net Promoter Score', 'fluentform'),
            'icon_class' => 'ff-edit-rating',
            'template'   => 'net_promoter',
        ],
    ];
    $defaultElements['advanced']['quiz_score'] = [
        'index'          => 19,
        'element'        => 'quiz_score',
        'attributes'     => [],
        'settings'       => [],
        'options'        => [],
        'editor_options' => [
            'title'      => __('Quiz Score', 'fluentform'),
            'icon_class' => 'el-icon-postcard',
            'template'   => 'inputHidden',
        ],
    ];
    $defaultElements['advanced']['cpt_selection'] = [
        'index'          => 18,
        'element'        => 'cpt_selection',
        'attributes'     => [],
        'settings'       => [],
        'options'        => [],
        'editor_options' => [
            'title'      => __('Post/CPT Selection', 'fluentform'),
            'icon_class' => 'ff-edit-dropdown',
            'element'    => 'select',
            'template'   => 'select',
        ],
    ];
    $defaultElements['advanced']['save_progress_button'] = [
        'index'          => 20,
        'element'        => 'save_progress_button',
        'attributes'     => [],
        'settings'       => [],
        'options'        => [],
        'editor_options' => [
            'title'      => __('Save & Resume', 'fluentform'),
            'icon_class' => 'dashicons dashicons-arrow-right-alt',
            'template'   => 'customButton',
        ],
    ];
    $defaultElements['advanced']['rich_text_input'] = [
        'index'          => 19,
        'element'        => 'rich_text_input',
        'attributes'     => [],
        'settings'       => [],
        'options'        => [],
        'editor_options' => [
            'title'      => __('Rich Text Input', 'fluentform'),
            'icon_class' => 'ff-edit-textarea',
            'template'   => 'inputTextarea',
        ],
    ];
    $defaultElements['advanced']['chained_select'] = [
        'index'          => 15,
        'element'        => 'chained_select',
        'attributes'     => [],
        'settings'       => [],
        'options'        => [],
        'editor_options' => [
            'title'      => __('Chained Select', 'fluentform'),
            'icon_class' => 'ff-edit-link',
            'template'   => 'chainedSelect',
        ],
    ];
    $defaultElements['advanced']['repeater_field'] = [
        'index'          => 17,
        'element'        => 'repeater_field',
        'attributes'     => [],
        'settings'       => [],
        'options'        => [],
        'editor_options' => [
            'title'      => __('Repeat Field', 'fluentform'),
            'icon_class' => 'ff-edit-repeat',
            'template'   => 'fieldsRepeatSettings',
        ],
    ];
    $defaultElements['advanced']['rangeslider'] = [
        'index'          => 13,
        'element'        => 'rangeslider',
        'attributes'     => [],
        'settings'       => [],
        'editor_options' => [
            'title'      => __('Range Slider', 'fluentform'),
            'icon_class' => 'dashicons dashicons-leftright',
            'template'   => 'inputSlider',
        ],
    ];
    $defaultElements['advanced']['color-picker'] = [
        'index'          => 16,
        'element'        => 'color-picker',
        'attributes'     => [],
        'settings'       => [],
        'editor_options' => [
            'title'      => __('Color Picker', 'fluentform'),
            'icon_class' => 'ff-edit-tint',
            'template'   => 'inputText',
        ],
    ];
    $defaultElements['payments'] = [
        'multi_payment_component' => [
            'index'          => 6,
            'element'        => 'multi_payment_component',
            'attributes'     => [],
            'settings'       => [],
            'editor_options' => [
                'title'      => __('Payment Item', 'fluentform'),
                'icon_class' => 'ff-edit-shopping-cart',
                'element'    => 'input-radio',
                'template'   => 'inputMultiPayment',
            ],
        ],
        'subscription_payment_component' => [
            'index'          => 6,
            'element'        => 'subscription_payment_component',
            'attributes'     => [],
            'settings'       => [],
            'editor_options' => [
                'title'      => __('Subscription', 'fluentform'),
                'icon_class' => 'ff-edit-shopping-cart',
                'element'    => 'input-radio',
                'template'   => 'inputSubscriptionPayment',
            ],
        ],
        'custom_payment_component' => [
            'index'          => 6,
            'element'        => 'custom_payment_component',
            'attributes'     => [],
            'settings'       => [],
            'editor_options' => [
                'title'      => __('Custom Payment Amount', 'fluentform'),
                'icon_class' => 'ff-edit-keyboard-o',
                'template'   => 'inputText',
            ],
        ],
        'item_quantity_component' => [
            'index'          => 6,
            'element'        => 'item_quantity_component',
            'attributes'     => [],
            'settings'       => [],
            'editor_options' => [
                'title'      => __('Item Quantity', 'fluentform'),
                'icon_class' => 'ff-edit-keyboard-o',
                'template'   => 'inputText',
            ],
        ],
        'payment_method' => [
            'index'          => 6,
            'element'        => 'payment_method',
            'attributes'     => [],
            'settings'       => [],
            'editor_options' => [
                'title'      => __('Payment Method', 'fluentform'),
                'icon_class' => 'ff-edit-credit-card',
                'template'   => 'inputPaymentMethods',
            ],
        ],
        'payment_summary_component' => [
            'index'          => 6,
            'element'        => 'payment_summary_component',
            'attributes'     => [],
            'settings'       => [],
            'editor_options' => [
                'title'      => __('Payment Summary', 'fluentform'),
                'icon_class' => 'ff-edit-html',
                'template'   => 'customHTML',
            ],
        ],
        'payment_coupon' => [
            'index'          => 6,
            'element'        => 'payment_coupon',
            'attributes'     => [],
            'settings'       => [],
            'editor_options' => [
                'title'      => __('Coupon', 'fluentform'),
                'icon_class' => 'el-icon-postcard',
                'template'   => 'inputText',
            ],
        ],
    ];
}

return $defaultElements;
