<?php
namespace FluentForm\App\Helpers\Traits;

use FluentForm\Framework\Helpers\ArrayHelper as Arr;

trait GlobalDefaultMessages
{
    private static $globalDefaultMessages = [];

    public static function getGlobalDefaultMessage($key)
    {
        if (isset(static::$globalDefaultMessages[$key])) {
            return static::$globalDefaultMessages[$key];
        }
        $globalSettings = get_option('_fluentform_global_form_settings');
        if (!$globalSettings || !Arr::get($globalSettings, 'default_messages')) {
            static::setGlobalDefaultMessages();
        } else if ($message = Arr::get($globalSettings, 'default_messages.' . $key, '')) {
            static::$globalDefaultMessages[$key] = $message;
        }
        return apply_filters('fluentform/global_default_message', Arr::get(static::$globalDefaultMessages, $key , ''), $key);
    }

    public static function getAllGlobalDefaultMessages()
    {
        static::setGlobalDefaultMessages();
        return static::$globalDefaultMessages;
    }

    private static function setGlobalDefaultMessages()
    {
        $default_messages = [];
        foreach (static::globalDefaultMessageSettingFields() as $key => $field) {
            $default_messages[$key] = $field['value'];
        }
        $globalSettings = get_option('_fluentform_global_form_settings');
        if ($globalSettings && $messages = Arr::get($globalSettings, 'default_messages', [])) {
            $default_messages = array_merge($default_messages, $messages);
        }
        static::$globalDefaultMessages = apply_filters('fluentform/global_default_messages', $default_messages);
    }

    public static function globalDefaultMessageSettingFields()
    {
        $default_message_setting_fields = [
            'required'       => [
                'label'     => __('Required Field', 'fluentform'),
                'value'     => __('This field is required', 'fluentform'),
                'help_text' => __("This message will be shown if validation fails for required field.",
                    'fluentform'),
            ],
            'email'                => [
                'label'     => __('Email', 'fluentform'),
                'value'     => __('This field must contain a valid email', 'fluentform'),
                'help_text' => __("This message will be shown if validation fails for email.", 'fluentform'),
            ],
            'numeric'              => [
                'label'     => __('Numeric', 'fluentform'),
                'value'     => __('This field must contain numeric value', 'fluentform'),
                'help_text' => __("This message will be shown if validation fails for numeric value.", 'fluentform'),
            ],
            'min'                 => [
                'label'     => __('Minimum', 'fluentform'),
                'value'     => __('Validation fails for minimum value', 'fluentform'),
                'help_text' => __("This message will be shown if validation fails for minimum value.", 'fluentform'),
            ],
            'max'                 => [
                'label'     => __('Maximum', 'fluentform'),
                'value'     => __('Validation fails for maximum value', 'fluentform'),
                'help_text' => __("This message will be shown if validation fails for maximum value.", 'fluentform'),
            ],
            'digits'              => [
                'label'     => __('Digits', 'fluentform'),
                'value'     => __('Validation fails for limited digits', 'fluentform'),
                'help_text' => __("This message will be shown if validation fails for digits value.", 'fluentform'),
            ],
            'url'                  => [
                'label'     => __('Url', 'fluentform'),
                'value'     => __('This field must contain a valid url', 'fluentform'),
                'help_text' => __("This message will be shown if validation fails for validate URL.", 'fluentform'),
            ],
            'allowed_image_types'  => [
                'label'     => __('Allowed Image Types', 'fluentform'),
                'value'     => __('Allowed image types does not match', 'fluentform'),
                'help_text' => __("This message will be shown if validation fails for image types.", 'fluentform'),
            ],
            'allowed_file_types'   => [
                'label'     => __('Allowed File Types', 'fluentform'),
                'value'     => __('Invalid file type', 'fluentform'),
                'help_text' => __("This message will be shown if validation fails for allowed file type.", 'fluentform'),
            ],
            'max_file_size'   => [
                'label'     => __('Maximum File Size', 'fluentform'),
                'value'     => __('Validation fails for maximum file size', 'fluentform'),
                'help_text' => __("This message will be shown if validation fails for maximum file size.", 'fluentform'),
            ],
            'max_file_count'   => [
                'label'     => __('Maximum File Count', 'fluentform'),
                'value'     => __('Validation fails for maximum file count', 'fluentform'),
                'help_text' => __("This message will be shown if validation fails for maximum file count.", 'fluentform'),
            ],
        ];

        if (defined('FLUENTFORMPRO')) {
            $default_message_setting_fields['valid_phone_number'] = [
                'label'     => __('Valid Phone Number', 'fluentformpro'),
                'value'     => __('Phone number is not valid', 'fluentformpro'),
                'help_text' => __("This message will be shown if validation fails for validate phone number.",
                    'fluentform'),
            ];
        }
        return apply_filters('fluentform/global_default_message_setting_fields', $default_message_setting_fields);
    }
}