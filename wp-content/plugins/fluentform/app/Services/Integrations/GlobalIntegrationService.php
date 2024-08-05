<?php

namespace FluentForm\App\Services\Integrations;

use Exception;
use FluentForm\Framework\Support\Arr;
class GlobalIntegrationService
{
    
    public function get($attr)
    {
        $settingsKey = sanitize_text_field(Arr::get($attr, 'settings_key'));
        $settings = apply_filters_deprecated(
            'fluentform_global_integration_settings_' . $settingsKey,
            [
                []
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/global_integration_settings_' . $settingsKey,
            'Use fluentform/global_integration_settings_' . $settingsKey . ' instead of fluentform_global_integration_settings_' . $settingsKey
        );
        $settings = apply_filters('fluentform/global_integration_settings_' . $settingsKey, $settings);
        $fieldSettings = apply_filters_deprecated(
            'fluentform_global_integration_fields_' . $settingsKey,
            [
                []
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/global_integration_fields_' . $settingsKey,
            'Use fluentform/global_integration_fields_' . $settingsKey . ' instead of fluentform_global_integration_fields_' . $settingsKey
        );
        $fieldSettings = apply_filters('fluentform/global_integration_fields_' . $settingsKey, $fieldSettings);
        if (!$fieldSettings) {
            $message = __('Sorry! No integration failed found with: ', 'fluentform').$settingsKey;
           return [
               'status' => false,
               'message'=> $message,
               'integration' => $settings,
               'settings'    => $fieldSettings,
           ];
        }
        
        if (!Arr::exists($fieldSettings,'save_button_text')) {
            $fieldSettings['save_button_text'] = __('Save Settings', 'fluentform');
        }
        
        if (!Arr::exists($fieldSettings,'valid_message')) {
            $fieldSettings['valid_message'] = __('Your API Key is valid', 'fluentform');
        }
        
        if (!Arr::exists($fieldSettings,'invalid_message')) {
            $fieldSettings['invalid_message'] = __('Your API Key is not valid', 'fluentform');
        }
        return [
            'status' => true,
            'integration' => $settings,
            'settings'    => $fieldSettings,
        ];
    }
    
    public function isEnabled($integrationKey)
    {
        $globalModules = get_option('fluentform_global_modules_status');
        $isEnabled = $globalModules && isset($globalModules[$integrationKey]) && 'yes' == $globalModules[$integrationKey];

        return apply_filters('fluentform/is_integration_enabled_'.$integrationKey, $isEnabled);
    }

    /**
     * @param $args - key value pair array
     * @throws Exception
     * @return void
     */
    public function updateModuleStatus($args) {
        $moduleKey = sanitize_text_field(Arr::get($args, 'module_key'));
        $moduleStatus = sanitize_text_field(Arr::get($args, 'module_status'));
        if (!$moduleKey || !in_array($moduleStatus, ['yes', 'no'])) {
            throw new Exception(__('Status updated failed. Not valid module or status', 'fluentform'));
        }
        try {
            $modules = (array)get_option('fluentform_global_modules_status');
            $modules[$moduleKey] = $moduleStatus;
            update_option('fluentform_global_modules_status', $modules, 'no');
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
