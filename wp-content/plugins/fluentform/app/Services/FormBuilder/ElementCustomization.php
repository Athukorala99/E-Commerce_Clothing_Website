<?php

/**
 * element_customization_settings
 *
 * Returns an array of countries and codes.
 *
 * @author      WooThemes
 *
 * @category    i18n
 * @package     fluentform/i18n
 *
 * @version     2.5.0
 */
if (! defined('ABSPATH')) {
    exit;
}

$dateFormats = (new \FluentForm\App\Services\FormBuilder\Components\DateTime())->getAvailableDateFormats();

$dateConfigSettings = [
    'template'         => 'inputTextarea',
    'label'            => __('Advanced Date Configuration', 'fluentform'),
    'placeholder'      => __('Advanced Date Configuration', 'fluentform'),
    'rows'             => (defined('FLUENTFORMPRO')) ? 10 : 2,
    'start_text'       => (defined('FLUENTFORMPRO')) ? 10 : 2,
    'disabled'         => ! defined('FLUENTFORMPRO'),
    'css_class'        => 'ff_code_editor',
    'inline_help_text' => 'Only valid JS object will work. Please check <a target="_blank" href="https://wpmanageninja.com/docs/fluent-form/field-types/time-date#advanced_configaration">the documentation for available config options</a>',
    'help_text'        => __('You can write your own date configuration as JS object. Please write valid configuration as per flatpickr config.', 'fluentform'),
];

if (! defined('FLUENTFORMPRO')) {
    $dateConfigSettings['inline_help_text'] = 'Available on Fluent Forms Pro';
}

$element_customization_settings = [
    'name' => [
        'template'  => 'nameAttr',
        'label'     => __('Name Attribute', 'fluentform'),
        'help_text' => __('This is the field name attributes which is used to submit form data, name attribute must be unique.', 'fluentform'),
    ],
    'label' => [
        'template'  => 'inputText',
        'label'     => __('Element Label', 'fluentform'),
        'help_text' => __('This is the field title the user will see when filling out the form.', 'fluentform'),
    ],
    'label_placement' => [
        'template'  => 'radioButton',
        'label'     => __('Label Placement', 'fluentform'),
        'help_text' => __('Determine the position of label title where the user will see this. By choosing "Default", global label placement setting will be applied.', 'fluentform'),
        'options'   => [
            [
                'value' => '',
                'label' => __('Default', 'fluentform'),
            ],
            [
                'value' => 'top',
                'label' => __('Top', 'fluentform'),
            ],
            [
                'value' => 'right',
                'label' => __('Right', 'fluentform'),
            ],
            [
                'value' => 'bottom',
                'label' => __('Bottom', 'fluentform'),
            ],
            [
                'value' => 'left',
                'label' => __('Left', 'fluentform'),
            ],
            [
                'value' => 'hide_label',
                'label' => __('Hide Label', 'fluentform'),
            ],
        ],
    ],
    'button_style' => [
        'template'  => 'selectBtnStyle',
        'label'     => __('Button Style', 'fluentform'),
        'help_text' => __('Select a button style from the dropdown', 'fluentform'),
    ],
    'button_size' => [
        'template'  => 'radioButton',
        'label'     => __('Button Size', 'fluentform'),
        'help_text' => __('Define a size of the button', 'fluentform'),
        'options'   => [
            [
                'value' => 'sm',
                'label' => __('Small', 'fluentform'),
            ],
            [
                'value' => 'md',
                'label' => __('Medium', 'fluentform'),
            ],
            [
                'value' => 'lg',
                'label' => __('Large', 'fluentform'),
            ],
        ],
    ],
    'placeholder' => [
        'template'  => 'inputText',
        'label'     => __('Placeholder', 'fluentform'),
        'help_text' => __('This is the field placeholder, the user will see this if the input field is empty.', 'fluentform'),
    ],
    'date_format' => [
        'template'    => 'select',
        'label'       => __('Date Format', 'fluentform'),
        'filterable'  => true,
        'creatable'   => true,
        'placeholder' => __('Select Date Format', 'fluentform'),
        'help_text'   => __('Select any date format from the dropdown. The user will be able to choose a date in this given format.', 'fluentform'),
        'options'     => $dateFormats,
    ],
    'date_config' => $dateConfigSettings,
    'rows'        => [
        'template'  => 'inputText',
        'label'     => __('Rows', 'fluentform'),
        'help_text' => __('How many rows will textarea take in a form. It\'s an HTML attributes for browser support.', 'fluentform'),
    ],
    'cols' => [
        'template'  => 'inputText',
        'label'     => __('Columns', 'fluentform'),
        'help_text' => __('How many cols will textarea take in a form. It\'s an HTML attributes for browser support.', 'fluentform'),
    ],
    'options' => [
        'template'  => 'selectOptions',
        'label'     => __('Options', 'fluentform'),
        'help_text' => __('Create options for the field and checkmark them for default selection.', 'fluentform'),
    ],
    'advanced_options' => [
        'template'  => 'advancedOptions',
        'label'     => __('Options', 'fluentform'),
        'help_text' => __('Create visual options for the field and checkmark them for default selection.', 'fluentform'),
    ],
    'enable_select_2' => [
        'template'  => 'inputYesNoCheckBox',
        'label'     => __('Enable Searchable Smart Options', 'fluentform'),
        'help_text' => __('If you enable this then options will be searchable by select2 js library', 'fluentform'),
    ],
    'pricing_options' => [
        'template'  => 'pricingOptions',
        'label'     => __('Payment Items', 'fluentform'),
        'help_text' => __('Set your product type and corresponding prices', 'fluentform'),
    ],
    'subscription_options' => [
        'template'  => 'subscriptionOptions',
        'label'     => __('Subscription Items', 'fluentform'),
        'help_text' => __('Set your subscription plans', 'fluentform'),
    ],
    'validation_rules' => [
        'template'  => 'validationRulesForm',
        'label'     => __('Validation Rules', 'fluentform'),
        'help_text' => '',
    ],
    'required_field_message' => [
        'template'  => 'inputRequiredFieldText',
        'label'     => __('Required Validation Message', 'fluentform'),
        'help_text' => 'Message for failed validation for this field',
    ],
    'tnc_html' => [
        'template'  => 'inputHTML',
        'label'     => __('Terms & Conditions', 'fluentform'),
        'help_text' => __('Write HTML content for terms & condition checkbox', 'fluentform'),
        'rows'      => 4,
        'cols'      => 2,
    ],
    'hook_name' => [
        'template'  => 'customHookName',
        'label'     => __('Hook Name', 'fluentform'),
        'help_text' => __('WordPress Hook name to hook something in this place.', 'fluentform'),
    ],
    'has_checkbox' => [
        'template' => 'inputCheckbox',
        'options'  => [
            [
                'value' => true,
                'label' => __('Show Checkbox', 'fluentform'),
            ],
        ],
    ],
    'html_codes' => [
        'template'  => 'inputHTML',
        'rows'      => 4,
        'cols'      => 2,
        'label'     => __('HTML Code', 'fluentform'),
        'help_text' => __('Your valid HTML code will be shown to the user as normal content.', 'fluentform'),
    ],
    'description' => [
        'template'  => 'inputHTML',
        'rows'      => 4,
        'cols'      => 2,
        'label'     => __('Description', 'fluentform'),
        'help_text' => __('Description will be shown to the user as normal text content.', 'fluentform'),
    ],
    'btn_text' => [
        'template'  => 'inputText',
        'label'     => __('Button Text', 'fluentform'),
        'help_text' => __('This will be visible as button text for upload file.', 'fluentform'),
    ],
    'button_ui' => [
        'template'  => 'prevNextButton',
        'label'     => __('Submit Button', 'fluentform'),
        'help_text' => __('This is form submission button.', 'fluentform'),
    ],
    'align' => [
        'template'  => 'radio',
        'label'     => __('Content Alignment', 'fluentform'),
        'help_text' => __('How the content will be aligned.', 'fluentform'),
        'options'   => [
            [
                'value' => 'left',
                'label' => __('Left', 'fluentform'),
            ],
            [
                'value' => 'center',
                'label' => __('Center', 'fluentform'),
            ],
            [
                'value' => 'right',
                'label' => __('Right', 'fluentform'),
            ],
        ],
    ],
    'shortcode' => [
        'template'  => 'inputText',
        'label'     => __('Shortcode', 'fluentform'),
        'help_text' => __('Your shortcode to render desired content in current place.', 'fluentform'),
    ],
    'apply_styles' => [
        'template'  => 'radioButton',
        'label'     => __('Apply Styles', 'fluentform'),
        'help_text' => __('Apply styles provided here', 'fluentform'),
        'options'   => [
            [
                'value' => true,
                'label' => __('Yes', 'fluentform'),
            ],
            [
                'value' => false,
                'label' => __('No', 'fluentform'),
            ],
        ],
    ],
    'step_title' => [
        'template'  => 'inputText',
        'label'     => __('Step Title', 'fluentform'),
        'help_text' => __('Form step titles, user will see each title in each step.', 'fluentform'),
    ],
    'disable_auto_focus' => [
        'template'  => 'inputYesNoCheckBox',
        'label'     => __('Disable auto focus when changing each page', 'fluentform'),
        'help_text' => __('If you enable this then on page transition automatic scrolling will be disabled', 'fluentform'),
    ],
    'enable_auto_slider' => [
        'template'  => 'inputYesNoCheckBox',
        'label'     => __('Enable auto page change for single radio field', 'fluentform'),
        'help_text' => __('If you enable this then for last radio item field will trigger next page change', 'fluentform'),
    ],
    'enable_step_data_persistency' => [
        'template'  => 'inputYesNoCheckBox',
        'label'     => __('Enable Per step data save (Save and Continue)', 'fluentform'),
        'help_text' => __('If you enable this then on each step change the data current step data will be persisted in a step form<br />Your users can resume the form where they left', 'fluentform'),
    ],
    'enable_step_page_resume' => [
        'template'   => 'inputYesNoCheckBox',
        'label'      => __('Resume Step from last form session', 'fluentform'),
        'help_text'  => __('If you enable this then users will see the form as step page where it has been left', 'fluentform'),
        'dependency' => [
            'depends_on' => 'settings/enable_step_data_persistency',
            'value'      => 'yes',
            'operator'   => '==',
        ],
    ],
    'progress_indicator' => [
        'template'  => 'radio',
        'label'     => __('Progress Indicator', 'fluentform'),
        'help_text' => __('Select any of them below, user will see progress of form steps according to your choice.', 'fluentform'),
        'options'   => [
            [
                'value' => 'progress-bar',
                'label' => __('Progress Bar', 'fluentform'),
            ],
            [
                'value' => 'steps',
                'label' => __('Steps', 'fluentform'),
            ],
            [
                'value' => '',
                'label' => __('None', 'fluentform'),
            ],
        ],
    ],
    'step_animation' => [
        'template'  => 'radioButton',
        'label'     => __('Animation type', 'fluentform'),
        'help_text' => __('Select any of them below, steps will change according to your choice.', 'fluentform'),
        'options'   => [
            [
                'value' => 'slide',
                'label' => __('Slide Left/Right', 'fluentform'),
            ],
            [
                'value' => 'fade',
                'label' => __('Fade In/Out', 'fluentform'),
            ],
            [
                'value' => 'slide_down',
                'label' => __('Slide Down/Up', 'fluentform'),
            ],
            [
                'value' => 'none',
                'label' => __('None', 'fluentform'),
            ],
        ],
    ],
    'step_titles' => [
        'template'  => 'customStepTitles',
        'label'     => __('Step Titles', 'fluentform'),
        'help_text' => __('Form step titles, user will see each title in each step.', 'fluentform'),
    ],
    'prev_btn' => [
        'template'  => 'prevNextButton',
        'label'     => __('Previous Button', 'fluentform'),
        'help_text' => __('Multi-step form\'s previous button', 'fluentform'),
    ],
    'next_btn' => [
        'template'  => 'prevNextButton',
        'label'     => __('Next Button', 'fluentform'),
        'help_text' => __('Multi-step form\'s next button', 'fluentform'),
    ],
    'address_fields' => [
        'template' => 'addressFields',
        'label'    => __('Address Fields', 'fluentform'),
        'key'      => 'country_list',
    ],
    'name_fields' => [
        'template' => 'nameFields',
        'label'    => __('Name Fields', 'fluentform'),
    ],
    'multi_column' => [
        'template' => 'inputCheckbox',
        'options'  => [
            [
                'value' => true,
                'label' => __('Enable Multiple Columns', 'fluentform'),
            ],
        ],
    ],
    'repeat_fields' => [
        'template'  => 'customRepeatFields',
        'label'     => __('Repeat Fields', 'fluentform'),
        'help_text' => __('This is a form field which a user will be able to repeat.', 'fluentform'),
    ],
    'admin_field_label' => [
        'template'  => 'inputText',
        'label'     => __('Admin Field Label', 'fluentform'),
        'help_text' => __('Admin field label is field title which will be used for admin field title.', 'fluentform'),
    ],
    'maxlength' => [
        'template'  => 'inputNumber',
        'label'     => __('Max text length', 'fluentform'),
        'help_text' => __('The maximum number of characters the input should accept', 'fluentform'),
    ],
    'value' => [
        'template'  => 'inputValue',
        'label'     => __('Default Value', 'fluentform'),
        'help_text' => __('If you would like to pre-populate the value of a field, enter it here.', 'fluentform') . ' <a target="_blank" rel="noopener" href="https://wpmanageninja.com/docs/fluent-form/miscellaneous/form-editor-smart-codes/">View All the smartcodes here</a>',
    ],
    'dynamic_default_value' => [
        'template'  => 'inputValue',
        'type'      => 'text',
        'label'     => __('Dynamic Default Value', 'fluentform'),
        'help_text' => __('If you would like to pre-populate the value of a field, enter it here.', 'fluentform'),
    ],
    'max_selection' => [
        'template'   => 'inputNumber',
        'type'       => 'text',
        'label'      => __('Max Selection', 'fluentform'),
        'help_text'  => __('Define Max selections items that a user can select .', 'fluentform'),
        'dependency' => [
            'depends_on' => 'attributes/multiple',
            'value'      => true,
            'operator'   => '==',
        ],
    ],
    'container_class' => [
        'template'  => 'inputText',
        'label'     => __('Container Class', 'fluentform'),
        'help_text' => __('Class for the field wrapper. This can be used to style current element.', 'fluentform'),
    ],
    'class' => [
        'template'  => 'inputText',
        'label'     => __('Element Class', 'fluentform'),
        'help_text' => __('Class for the field. This can be used to style current element.', 'fluentform'),
    ],
    'country_list' => [
        'template' => 'customCountryList',
        'label'    => __('Country List', 'fluentform'),
        'key'      => 'country_list',
    ],
    'product_field_types' => [
        'template' => 'productFieldTypes',
        'label'    => __('Options', 'fluentform'),
    ],
    'help_message' => [
        'template'  => 'inputTextarea',
        'label'     => __('Help Message', 'fluentform'),
        'help_text' => __('Help message will be shown as tooltip next to sidebar or below the field.', 'fluentform'),
    ],
    'conditional_logics' => [
        'template'  => 'conditionalLogics',
        'label'     => __('Conditional Logic', 'fluentform'),
        'help_text' => __('Create rules to dynamically display or hide this field based on values from another field.', 'fluentform'),
    ],
    'background_color' => [
        'template'  => 'inputColor',
        'label'     => __('Background Color', 'fluentform'),
        'help_text' => __('The Background color of the element', 'fluentform'),
    ],
    'color' => [
        'template'  => 'inputColor',
        'label'     => __('Font Color', 'fluentform'),
        'help_text' => __('Font color of the element', 'fluentform'),
    ],
    'data-mask' => [
        'template'   => 'customMask',
        'label'      => __('Custom Mask', 'fluentform'),
        'help_text'  => __('Write your own mask for this input', 'fluentform'),
        'dependency' => [
            'depends_on' => 'settings/temp_mask',
            'value'      => 'custom',
            'operator'   => '==',
        ],
    ],
    'data-mask-reverse' => [
        'template'   => 'inputYesNoCheckBox',
        'label'      => __('Activating a reversible mask', 'fluentform'),
        'help_text'  => __('If you enable this then it the mask will work as reverse', 'fluentform'),
        'dependency' => [
            'depends_on' => 'settings/temp_mask',
            'value'      => 'custom',
            'operator'   => '==',
        ],
    ],
    'randomize_options' => [
        'template'  => 'inputYesNoCheckBox',
        'label'     => __('Shuffle the available options', 'fluentform'),
        'help_text' => __('If you enable this then the checkable options will be shuffled', 'fluentform'),
    ],
    'data-clear-if-not-match' => [
        'template'   => 'inputYesNoCheckBox',
        'label'      => __('Clear if not match', 'fluentform'),
        'help_text'  => __('Clear value if not match the mask', 'fluentform'),
        'dependency' => [
            'depends_on' => 'settings/temp_mask',
            'value'      => 'custom',
            'operator'   => '==',
        ],
    ],
    'temp_mask' => [
        'template'  => 'select',
        'label'     => __('Mask Input', 'fluentform'),
        'help_text' => __('Select a mask for the input field', 'fluentform'),
        'options'   => [
            [
                'value' => '',
                'label' => __('None', 'fluentform'),
            ],
            [
                'value' => '(000) 000-0000',
                'label' => '(###) ###-####',
            ],
            [
                'value' => '(00) 0000-0000',
                'label' => '(##) ####-####',
            ],
            [
                'value' => '00/00/0000',
                'label' => __('23/03/2018', 'fluentform'),
            ],
            [
                'value' => '00:00:00',
                'label' => __('23:59:59', 'fluentform'),
            ],
            [
                'value' => '00/00/0000 00:00:00',
                'label' => __('23/03/2018 23:59:59', 'fluentform'),
            ],
            [
                'value' => 'custom',
                'label' => __('Custom', 'fluentform'),
            ],
        ],
    ],
    'grid_columns' => [
        'template'  => 'gridRowCols',
        'label'     => __('Grid Columns', 'fluentform'),
        'help_text' => __('Write your own mask for this input', 'fluentform'),
    ],
    'grid_rows' => [
        'template'  => 'gridRowCols',
        'label'     => __('Grid Rows', 'fluentform'),
        'help_text' => __('Write your own mask for this input', 'fluentform'),
    ],
    'tabular_field_type' => [
        'template'  => 'radio',
        'label'     => __('Field Type', 'fluentform'),
        'help_text' => __('Field Type', 'fluentform'),
        'options'   => [
            [
                'value' => 'checkbox',
                'label' => __('Checkbox', 'fluentform'),
            ],
            [
                'value' => 'radio',
                'label' => __('Radio', 'fluentform'),
            ],
        ],
    ],
    'max_repeat_field' => [
        'template'  => 'inputNumber',
        'label'     => __('Max Repeat inputs', 'fluentform'),
        'help_text' => __('Please provide max number of rows the user can fill up for this repeat field. Keep blank/0 for unlimited numbers', 'fluentform'),
    ],
    'calculation_settings' => [
        'template'      => (defined('FLUENTFORMPRO')) ? 'inputCalculationSettings' : 'infoBlock',
        'text'          => '<strong>Calculation Field Settings</strong><br/>Calculate the value based on other numeric field is available on pro version of Fluent Forms. Please install Fluent Forms Pro to use this feature <br /> <a target="_blank" rel="noopener" href="https://fluentforms.com/pricing/?utm_source=plugin&amp;utm_medium=wp_install&amp;utm_campaign=ff_upgrade&amp;theme_style=twentytwentythree">
        Upgrade to Pro                </a>',
        'label'         => 'Calculation Field Settings',
        'status_label'  => 'Enable Calculation',
        'formula_label' => 'Calculation Expression',
        'formula_tips'  => 'You can use + - * / for calculating the the value. Use context icon to insert the input values',
        'status_tips'   => 'Enable this and provide formula expression if you want this field as calculated based on other numeric field value',
    ],
    'min' => [
        'template'  => 'inputNumber',
        'label'     => __('Min Value', 'fluentform'),
        'help_text' => __('Please provide minimum value', 'fluentform'),
    ],
    'max' => [
        'template'  => 'inputNumber',
        'label'     => __('Max Value', 'fluentform'),
        'help_text' => __('Please provide Maximum value', 'fluentform'),
    ],
    'digits' => [
        'template'  => 'inputNumber',
        'label'     => __('Digits Count', 'fluentform'),
        'help_text' => __('Please provide digits count value', 'fluentform'),
    ],
    'number_step' => [
        'template'  => 'inputText',
        'label'     => __('Step', 'fluentform'),
        'help_text' => __('Please provide step attribute for this field. Give value "any" for floating value', 'fluentform'),
    ],
    'prefix_label' => [
        'template'  => 'inputText',
        'label'     => __('Prefix Label', 'fluentform'),
        'help_text' => __('Provide Input Prefix Label. It will show in the input field as prefix label', 'fluentform'),
    ],
    'suffix_label' => [
        'template'  => 'inputText',
        'label'     => __('Suffix Label', 'fluentform'),
        'help_text' => __('Provide Input Suffix Label. It will show in the input field as suffix label', 'fluentform'),
    ],
    'is_unique' => [
        'template'  => 'inputYesNoCheckBox',
        'label'     => __('Validate as Unique', 'fluentform'),
        'help_text' => __('If you make it unique then it will validate as unique from previous submissions of this form', 'fluentform'),
    ],
    'show_text' => [
        'template'  => 'select',
        'label'     => __('Show Text', 'fluentform'),
        'help_text' => __('Show Text value on selection', 'fluentform'),
        'options'   => [
            [
                'value' => 'yes',
                'label' => __('Yes', 'fluentform'),
            ],
            [
                'value' => 'no',
                'label' => __('No', 'fluentform'),
            ],
        ],
    ],
    'numeric_formatter' => [
        'template'  => 'select',
        'label'     => __('Number Format', 'fluentform'),
        'help_text' => __('Select the format of numbers that are allowed in this field. You have the option to use a comma or a dot as the decimal separator.', 'fluentform'),
        'options'   => \FluentForm\App\Helpers\Helper::getNumericFormatters(),
    ],
    'unique_validation_message' => [
        'template'   => 'inputText',
        'label'      => __('Validation Message for Duplicate', 'fluentform'),
        'help_text'  => __('If validation failed then it will show this message', 'fluentform'),
        'dependency' => [
            'depends_on' => 'settings/is_unique',
            'value'      => 'yes',
            'operator'   => '==',
        ],
    ],
    'layout_class' => [
        'template'  => 'select',
        'label'     => __('Layout', 'fluentform'),
        'help_text' => __('Select the Layout for checkable items', 'fluentform'),
        'options'   => [
            [
                'value' => '',
                'label' => __('Default', 'fluentform'),
            ],
            [
                'value' => 'ff_list_inline',
                'label' => 'Inline Layout',
            ],
            [
                'value' => 'ff_list_buttons',
                'label' => 'Button Type Styles',
            ],
            [
                'value' => 'ff_list_2col',
                'label' => '2-Column Layout',
            ],
            [
                'value' => 'ff_list_3col',
                'label' => '3-Column Layout',
            ],
            [
                'value' => 'ff_list_4col',
                'label' => '4-Column Layout',
            ],
            [
                'value' => 'ff_list_5col',
                'label' => '5-Column Layout',
            ],
        ],
    ],
    'upload_file_location' => [
        'template'   => 'radio',
        'label'      => __('Save Uploads in', 'fluentform'),
        'help_text'  => __('Uploaded files can be stored in media library or your server or both.', 'fluentform'),
        'options'    => \FluentForm\App\Helpers\Helper::fileUploadLocations(),
        'dependency' => [
            'depends_on' => 'settings/file_location_type',
            'value'      => 'custom',
            'operator'   => '==',
        ],
    ],
    'file_location_type' => [
        'template'  => 'radioButton',
        'label'     => __('File Location Type', 'fluentform'),
        'help_text' => __('Set default or custom location for files', 'fluentform'),
        'options'   => [
            [
                'value' => 'follow_global_settings',
                'label' => __('As Per Global Settings', 'fluentform'),
            ],
            [
                'value' => 'custom',
                'label' => __('Custom', 'fluentform'),
            ],
        ],
    ],
    'container_width' => [
        'template'             => 'containerWidth',
        'label'                => __('Column Width %', 'fluentform'),
        'help_text'            => __('Set the width of the columns. The minimum column width is 10%.', 'fluentform'),
        'width_limitation_msg' => __('The minimum column width is 10%', 'fluentform'),
    ]
];

$element_customization_settings = apply_filters_deprecated(
    'fluentform_editor_element_customization_settings',
    [
        $element_customization_settings
    ],
    FLUENTFORM_FRAMEWORK_UPGRADE,
    'fluentform/editor_element_customization_settings',
    'Use fluentform/editor_element_customization_settings instead of fluent_editor_element_customization_settings.'
);

return apply_filters('fluentform/editor_element_customization_settings', $element_customization_settings);
