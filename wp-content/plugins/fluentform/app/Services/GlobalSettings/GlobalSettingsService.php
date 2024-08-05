<?php

namespace FluentForm\App\Services\GlobalSettings;

use FluentForm\Framework\Support\Arr;

class GlobalSettingsService
{
    public function get($attributes = [])
    {
        $values = [];
        $key = Arr::get($attributes, 'key');

        if (is_array($key)) {
            foreach ($key as $key_item) {
                $sanitizedKey = sanitize_text_field($key_item);
                $values[$key_item] = get_option($sanitizedKey);
            }
        } else {
            $values[$key] = get_option($key);
        }
    
        $values = apply_filters_deprecated(
            'fluentform_get_global_settings_values',
            [
                $values,
                $key
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/get_global_settings_values',
            'Use fluentform/get_global_settings_values instead of fluentform_get_global_settings_values.'
        );

        return apply_filters('fluentform/get_global_settings_values', $values, $key);
    }

    public function store($attributes = [])
    {
        $key = Arr::get($attributes, 'key');

        $globalSettingsHelper = new GlobalSettingsHelper();

        $allowedMethods = [
            'storeReCaptcha',
            'storeHCaptcha',
            'storeTurnstile',
            'storeSaveGlobalLayoutSettings',
            'storeMailChimpSettings',
            'storeEmailSummarySettings',
        ];

        $method = '';
        $container = [];
        if (is_array($key)) {
            foreach ($key as $item) {
                $method = 'store' . ucwords($item);
                if (in_array($method, $allowedMethods)) {
                    $container[] = $globalSettingsHelper->{$method}($attributes);
                }
            }
            return $container;
        } else {
            $method = 'store' . ucwords($key);
        }

        do_action_deprecated(
            'fluentform_saving_global_settings_with_key_method',
            [
                $attributes
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/saving_global_settings_with_key_method',
            'Use fluentform/saving_global_settings_with_key_method instead of fluentform_saving_global_settings_with_key_method.'
        );

        do_action('fluentform/saving_global_settings_with_key_method', $attributes);

        if (in_array($method, $allowedMethods)) {
            return $globalSettingsHelper->{$method}($attributes);
        }
    }
}
