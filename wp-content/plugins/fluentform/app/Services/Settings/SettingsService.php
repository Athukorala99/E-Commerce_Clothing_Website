<?php

namespace FluentForm\App\Services\Settings;

use FluentForm\App\Models\Form;
use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\FormMeta;
use FluentForm\Framework\Support\Arr;
use FluentForm\App\Services\FluentConversational\Classes\Form as FluentConversational;

class SettingsService
{
    public function get($attributes = [])
    {
        $metaKey = sanitize_text_field(Arr::get($attributes, 'meta_key'));

        $formId = (int) Arr::get($attributes, 'form_id');

        $result = FormMeta::where(['meta_key' => $metaKey, 'form_id' => $formId])->get();

        foreach ($result as $item) {
            $value = Helper::isJson($item->value) ? json_decode($item->value, true) : $item->value;

            if ('notifications' == $metaKey) {
                if (!$value) {
                    $value = ['name' => ''];
                }
            }

            if (isset($value['layout']) && !isset($value['layout']['asteriskPlacement'])) {
                $value['layout']['asteriskPlacement'] = 'asterisk-right';
            }

            $item->value = $value;
        }
    
        $result = apply_filters_deprecated(
            'fluentform_get_meta_key_settings_response',
            [
                $result,
                $formId,
                $metaKey
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/get_meta_key_settings_response',
            'Use fluentform/get_meta_key_settings_response instead of fluentform_get_meta_key_settings_response'
        );

        return apply_filters('fluentform/get_meta_key_settings_response', $result, $formId, $metaKey);
    }

    public function general($formId)
    {
        $settings = [
            'generalSettings'            => Form::getFormsDefaultSettings($formId),
            'advancedValidationSettings' => Form::getAdvancedValidationSettings($formId),
        ];

        $settings = apply_filters_deprecated(
            'fluentform_form_settings_ajax',
            [
                $settings,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_settings_ajax',
            'Use fluentform/form_settings_ajax instead of fluentform/form_settings_ajax'
        );

        $settings = apply_filters('fluentform/form_settings_ajax', $settings, $formId);

        return $settings;
    }

    public function saveGeneral($attributes = [])
    {
        $formId = (int) Arr::get($attributes, 'form_id');

        $formSettings = json_decode(Arr::get($attributes, 'formSettings'), true);

        $formSettings = $this->sanitizeData($formSettings);

        $advancedValidationSettings = json_decode(Arr::get($attributes, 'advancedValidationSettings'), true);

        $advancedValidationSettings = $this->sanitizeData($advancedValidationSettings);

        Validator::validate(
            'confirmations',
            Arr::get($formSettings, 'confirmation', [])
        );

        FormMeta::persist($formId, 'formSettings', $formSettings);

        FormMeta::persist($formId, 'advancedValidationSettings', $advancedValidationSettings);

        $deleteAfterXDaysStatus = Arr::get($formSettings, 'delete_after_x_days');
        $deleteDaysCount = Arr::get($formSettings, 'auto_delete_days');
        $deleteOnSubmission = Arr::get($formSettings, 'delete_entry_on_submission');

        if ('yes' != $deleteOnSubmission && $deleteDaysCount && 'yes' == $deleteAfterXDaysStatus) {
            // We have to set meta values
            FormMeta::persist($formId, 'auto_delete_days', $deleteDaysCount);
        } else {
            // we have to delete meta values
            FormMeta::remove($formId, 'auto_delete_days');
        }

        do_action_deprecated(
            'fluentform_after_save_form_settings',
            [
                $formId,
                $attributes
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/after_save_form_settings',
            'Use fluentform/after_save_form_settings instead of fluentform_after_save_form_settings.'
        );

        do_action('fluentform/after_save_form_settings', $formId, $attributes);
    }

    private function sanitizeData($settings)
    {
        if (fluentformCanUnfilteredHTML()) {
            return $settings;
        }

        $sanitizerMap = [
            'redirectTo'                 => 'sanitize_text_field',
            'redirectMessage'            => 'fluentform_sanitize_html',
            'messageToShow'              => 'fluentform_sanitize_html',
            'customPage'                 => 'sanitize_text_field',
            'samePageFormBehavior'       => 'sanitize_text_field',
            'customUrl'                  => 'sanitize_url',
            'enabled'                    => 'rest_sanitize_boolean',
            'numberOfEntries'            => 'intval',
            'period'                     => 'intval',
            'limitReachedMsg'            => 'sanitize_text_field',
            'start'                      => 'sanitize_text_field',
            'end'                        => 'sanitize_text_field',
            'pendingMsg'                 => 'sanitize_text_field',
            'expiredMsg'                 => 'sanitize_text_field',
            'requireLoginMsg'            => 'sanitize_text_field',
            'labelPlacement'             => 'sanitize_text_field',
            'helpMessagePlacement'       => 'sanitize_text_field',
            'errorMessagePlacement'      => 'sanitize_text_field',
            'asteriskPlacement'          => 'sanitize_text_field',
            'delete_entry_on_submission' => 'sanitize_text_field',
            'id'                         => 'intval',
            'showLabel'                  => 'rest_sanitize_boolean',
            'showCount'                  => 'rest_sanitize_boolean',
            'status'                     => 'rest_sanitize_boolean',
            'type'                       => 'sanitize_text_field',
            'field'                      => 'sanitize_text_field',
            'operator'                   => 'sanitize_text_field',
            'value'                      => 'sanitize_text_field',
            'error_message'              => 'sanitize_text_field',
            'validation_type'            => 'sanitize_text_field',
            'name'                       => 'sanitize_text_field',
            'email'                      => 'sanitize_text_field',
            'fromName'                   => 'sanitize_text_field',
            'fromEmail'                  => 'sanitize_text_field',
            'replyTo'                    => 'sanitize_text_field',
            'bcc'                        => 'sanitize_text_field',
            'subject'                    => 'sanitize_text_field',
            'message'                    => 'fluentform_sanitize_html',
            'url'                        => 'sanitize_url',
            'webhook'                    => 'sanitize_url',
            'textTitle'                  => 'sanitize_text_field',
        ];

        return fluentform_backend_sanitizer($settings, $sanitizerMap);
    }

    public function store($attributes = [])
    {
        $formId = (int) Arr::get($attributes, 'form_id');

        $value = Arr::get($attributes, 'value', '');

        $valueArray = $value ? json_decode($value, true) : [];

        $key = sanitize_text_field(Arr::get($attributes, 'meta_key'));

        if ('formSettings' == $key) {
            Validator::validate(
                'confirmations',
                Arr::get(
                    $valueArray,
                    'confirmation',
                    []
                )
            );
        } else {
            Validator::validate($key, $valueArray);
        }

        $valueArray = $this->sanitizeData($valueArray);

        $value = json_encode($valueArray);

        $data = [
            'meta_key' => $key,
            'value'    => $value,
            'form_id'  => $formId,
        ];

        // If the request has an valid id field it's safe to assume
        // that the user wants to update an existing settings.
        // So, we'll proceed to do so by finding it first.
        $id = (int) Arr::get($attributes, 'meta_id');

        $settingsQuery = FormMeta::where('form_id', $formId);

        $settings = null;
        if ($id) {
            $settings = $settingsQuery->find($id);
        }

        if (!empty($settings)) {
            $settingsQuery->where('id', $settings->id)->update($data);
            $insertId = $settings->id;
        } else {
            $insertId = $settingsQuery->insertGetId($data);
        }

        return [
            $insertId,
            $valueArray,
        ];
    }

    public function remove($attributes = [])
    {
        $formId = intval(Arr::get($attributes, 'form_id'));
        $id = intval(Arr::get($attributes, 'meta_id'));

        FormMeta::where('form_id', $formId)->where('id', $id)->delete();
    }

    public function conversationalDesign($formId)
    {
        $conversationalForm = new FluentConversational();

        return [
            'design_settings' => $conversationalForm->getDesignSettings($formId),
            'meta_settings'   => $conversationalForm->getMetaSettings($formId),
            'has_pro'         => defined('FLUENTFORMPRO'),
        ];
    }

    public function storeConversationalDesign($attributes, $formId)
    {
        $metaKey = "ffc_form_settings";
        $formId = intval($formId);

        $settings = Arr::get($attributes, 'design_settings');
        FormMeta::persist($formId, $metaKey . '_design', $settings);

        $generatedCss = wp_strip_all_tags(Arr::get($attributes, 'generated_css'));
        if ($generatedCss) {
            FormMeta::persist($formId, $metaKey . '_generated_css', $generatedCss);
        }

        $meta = Arr::get($attributes, 'meta_settings', []);
        if ($meta) {
            FormMeta::persist($formId, $metaKey . '_meta', $meta);
        }

        $params = [
            'fluent-form' => $formId,
        ];
        if (isset($meta['share_key']) && !empty($meta['share_key'])) {
            $params['form'] = $meta['share_key'];
        }

        $shareUrl = add_query_arg($params, site_url());
        return [
            'message'   => __('Settings successfully updated'),
            'share_url' => $shareUrl,
        ];
    }
}
