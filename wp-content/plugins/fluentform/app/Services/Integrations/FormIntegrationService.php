<?php

namespace FluentForm\App\Services\Integrations;

use FluentForm\App\Models\FormMeta;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;

class FormIntegrationService
{
    public function find($attr)
    {
        $formId = intval(Arr::get($attr, 'form_id'));
        $integrationId = intval(Arr::get($attr, 'integration_id'));
        $integrationName = sanitize_text_field(Arr::get($attr, 'integration_name'));
        
        $settings = [
            'conditionals' => [
                'conditions' => [],
                'status'     => false,
                'type'       => 'all',
            ],
            'enabled'      => true,
            'list_id'      => '',
            'list_name'    => '',
            'name'         => '',
            'merge_fields' => [],
        ];
        
        $mergeFields = false;
        if ($integrationId) {
            $feed = FormMeta::where(['form_id' => $formId, 'id' => $integrationId])->first();
            
            
            if ($feed->value) {
                $settings = json_decode($feed->value, true);
                $settings = apply_filters_deprecated(
                    'fluentform_get_integration_values_' . $integrationName,
                    [
                        $settings,
                        $feed,
                        $formId
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/get_integration_values_' . $integrationName,
                    'Use fluentform/get_integration_values_' . $integrationName . ' instead of fluentform_get_integration_values_' . $integrationName
                );
                $settings = apply_filters('fluentform/get_integration_values_' . $integrationName, $settings, $feed, $formId);
                if (!empty($settings['list_id'])) {
                    $mergeFields = apply_filters_deprecated(
                        'fluentform_get_integration_merge_fields_' . $integrationName,
                        [
                            false,
                            $settings['list_id'],
                            $formId
                        ],
                        FLUENTFORM_FRAMEWORK_UPGRADE,
                        'fluentform/get_integration_merge_fields_' . $integrationName,
                        'Use fluentform/get_integration_merge_fields_' . $integrationName . ' instead of fluentform_get_integration_merge_fields_' . $integrationName
                    );
                    $mergeFields = apply_filters('fluentform/get_integration_merge_fields_' . $integrationName, false, $settings['list_id'], $formId);
                }
            }
        } else {
            $settings = apply_filters_deprecated(
                'fluentform_get_integration_defaults_' . $integrationName,
                [
                    false,
                    $formId
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/get_integration_defaults_' . $integrationName,
                'Use fluentform/get_integration_defaults_' . $integrationName . ' instead of fluentform_get_integration_defaults_' . $integrationName
            );
            $settings = apply_filters('fluentform/get_integration_defaults_' . $integrationName, $settings, $formId);
        }
        
        if ('true' == $settings['enabled']) {
            $settings['enabled'] = true;
        } elseif ('false' == $settings['enabled'] || $settings['enabled']) {
            $settings['enabled'] = false;
        }
        $settings = apply_filters_deprecated(
            'fluentform_get_integration_settings_fields_' . $integrationName,
            [
                $settings,
                $formId,
                $settings
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/get_integration_settings_fields_' . $integrationName,
            'Use fluentform/get_integration_settings_fields_' . $integrationName . ' instead of fluentform_get_integration_settings_fields_' . $integrationName
        );
        $settingsFields = apply_filters('fluentform/get_integration_settings_fields_' . $integrationName, $settings, $formId, $settings);
        
        return ([
            'settings'        => $settings,
            'settings_fields' => $settingsFields,
            'merge_fields'    => $mergeFields,
        ]);
    }
    
    public function update($attr)
    {
        $formId = intval(Arr::get($attr, 'form_id'));
        $integrationId = intval(Arr::get($attr, 'integration_id'));
        $integrationName = sanitize_text_field(Arr::get($attr, 'integration_name'));
        $dataType = sanitize_text_field(Arr::get($attr, 'data_type'));
        $status = Arr::get($attr, 'status', true);
        $metaValue = Arr::get($attr, 'integration');
        
        
        if ('stringify' == $dataType) {
            $metaValue = \json_decode($metaValue, true);
        } else {
            $metaValue = wp_unslash($metaValue);
        }
        $isUpdatingStatus = empty($metaValue);
        
        if ($isUpdatingStatus) {
            $integrationData = FormMeta::findOrFail($integrationId);
            $metaValue = \json_decode($integrationData->value, true);
            $metaValue['enabled'] = $status;
            $metaKey = $integrationData->meta_key;
        } else {
            if (empty($metaValue['name'])) {
                $errors['name'] = [__('Feed name is required', 'fluentform')];
                wp_send_json_error([
                    'message' => __('Validation Failed! Feed name is required', 'fluentform'),
                    'errors'  => $errors
                ], 423);
            }
            $metaValue = apply_filters_deprecated(
                'fluentform_save_integration_value_' . $integrationName,
                [
                    $metaValue,
                    $integrationId,
                    $formId
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/save_integration_value_' . $integrationName,
                'Use fluentform/save_integration_value_' . $integrationName . ' instead of fluentform_save_integration_value_' . $integrationName
            );
            $metaValue = apply_filters('fluentform/save_integration_value_' . $integrationName, $metaValue,
                $integrationId, $formId);
            $metaKey = $integrationName . '_feeds';
        }
        $data = [
            'form_id'  => $formId,
            'meta_key' => $metaKey,
            'value'    => \json_encode($metaValue),
        ];
        $data = apply_filters_deprecated(
            'fluentform_save_integration_settings_' . $integrationName,
            [
                $data,
                $integrationId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/save_integration_settings_' . $integrationName,
            'Use fluentform/save_integration_settings_' . $integrationName . ' instead of fluentform_save_integration_settings_' . $integrationName
        );
        $data = apply_filters('fluentform/save_integration_settings_' . $integrationName, $data, $integrationId);
        $created = false;
        if ($integrationId) {
            FormMeta::where('form_id', $formId)
                ->where('id', $integrationId)
                ->update($data);
        } else {
            $integrationId = FormMeta::insertGetId($data);
            $created = true;
        }
        
        
        return ([
            'message'          => __('Integration successfully saved', 'fluentform'),
            'integration_id'   => $integrationId,
            'integration_name' => $integrationName,
            'created'          => $created,
        ]);
    }
    
    public function get($formId)
    {
        $notificationKeys = apply_filters_deprecated(
            'fluentform_global_notification_types',
            [
                [],
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/global_notification_types',
            'Use fluentform/global_notification_types instead of fluentform_global_notification_types.'
        );
        $notificationKeys = apply_filters('fluentform/global_notification_types', $notificationKeys, $formId);
        $feeds = [];
        if ($notificationKeys) {
            $feeds = FormMeta::whereIn('meta_key', $notificationKeys)->where('form_id', $formId)->get();
        }
        $formattedFeeds = [];
      
        if (!empty($feeds)) {
            foreach ($feeds as $feed) {
                $data = json_decode($feed->value, true);
                $enabled = $data['enabled'];
                if ($enabled && 'true' == $enabled) {
                    $enabled = true;
                } elseif ('false' == $enabled) {
                    $enabled = false;
                }
                $feedData = [
                    'id'       => $feed->id,
                    'name'     => Arr::get($data, 'name'),
                    'enabled'  => $enabled,
                    'provider' => $feed->meta_key,
                    'feed'     => $data,
                ];
                $feedData = apply_filters_deprecated(
                    'fluentform_global_notification_feed_' . $feed->meta_key,
                    [
                        $feedData,
                        $formId
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/global_notification_feed_' . $feed->meta_key,
                    'Use fluentform/global_notification_feed_' . $feed->meta_key . ' instead of fluentform_global_notification_feed_' . $feed->meta_key
                );
                $feedData = apply_filters('fluentform/global_notification_feed_' . $feed->meta_key, $feedData,
                    $formId);
                $formattedFeeds[] = $feedData;
            }
        }
        $availableIntegrations = apply_filters_deprecated(
            'fluentform_get_available_form_integrations',
            [
                [],
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/get_available_form_integrations',
            'Use fluentform/get_available_form_integrations instead of fluentform_get_available_form_integrations.'
        );
        $availableIntegrations = apply_filters('fluentform/get_available_form_integrations', $availableIntegrations, $formId);
        
        return ([
            'feeds'                  => $formattedFeeds,
            'available_integrations' => $availableIntegrations,
            'all_module_config_url'  => admin_url('admin.php?page=fluent_forms_add_ons'),
        ]);
    }
    
    public function delete($id)
    {
        FormMeta::where('id',$id)->delete();
    }
    
}
