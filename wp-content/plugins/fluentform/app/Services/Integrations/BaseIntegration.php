<?php

namespace FluentForm\App\Services\Integrations;

/**
 * @deprecated deprecated use FluentForm\App\Http\Controllers\IntegrationManagerHelper;
 */


class BaseIntegration
{
    private $setting_key = '';
    private $isMultiple = false;
    private $formId = false;
    private $isJsonValue = true;

    public function __construct($settings_key = '', $form_id = false, $isMultiple = false)
    {
        $this->setting_key = $settings_key;
        $this->isMultiple = $isMultiple;
        $this->formId = $form_id;
    }

    public function setSettingsKey($key)
    {
        $this->setting_key = $key;
    }

    public function setIsMultiple($isMultiple)
    {
        $this->isMultiple = $isMultiple;
    }

    public function setFormId($formId)
    {
        $this->formId = $formId;
    }

    public function setJasonType($type)
    {
        $this->isJsonValue = $type;
    }

    public function save($settings)
    {
        return wpFluent()->table('fluentform_form_meta')
                         ->insertGetId([
                             'meta_key' => $this->setting_key,
                             'form_id'  => $this->formId,
                             'value'    => json_encode($settings),
                         ]);
    }

    public function update($settingsId, $settings)
    {
        return wpFluent()->table('fluentform_form_meta')
                    ->where('id', $settingsId)
                    ->update([
                        'value' => json_encode($settings),
                    ]);
    }

    public function get($settingsId)
    {
        $settings = wpFluent()->table('fluentform_form_meta')
                                   ->where('form_id', $this->formId)
                                   ->where('meta_key', $this->setting_key)
                                   ->find($settingsId);
        $settings->formattedValue = $this->getFormattedValue($settings);
        return $settings;
    }

    public function getAll()
    {
        $settingsQuery = wpFluent()->table('fluentform_form_meta')
                              ->where('form_id', $this->formId)
                              ->where('meta_key', $this->setting_key);

        if ($this->isMultiple) {
            $settings = $settingsQuery->get();
            foreach ($settings as $setting) {
                $setting->formattedValue = $this->getFormattedValue($setting);
            }
        } else {
            $settings = $settingsQuery->first();
            $settings->formattedValue = $this->getFormattedValue($settings);
        }
        return $settings;
    }

    public function delete($settingsId)
    {
        return wpFluent()->table('fluentform_form_meta')
                    ->where('meta_key', $this->setting_key)
                    ->where('form_id', $this->formId)
                    ->where('id', $settingsId)
                    ->delete();
    }

    protected function validate($notification)
    {
        $validate = fluentValidator($notification, [
            'name'              => 'required',
            'list_id'           => 'required',
            'fieldEmailAddress' => 'required',
        ], [
            'name.required'              => __('Feed Name is required', 'fluentform'),
            'list.required'              => __(' List is required', 'fluentform'),
            'fieldEmailAddress.required' => __('Email Address is required'),
        ])->validate();

        if ($validate->fails()) {
            wp_send_json_error([
                'errors'  => $validate->errors(),
                'message' => __('Please fix the errors', 'fluentform'),
            ], 400);
        }
        return true;
    }

    private function getFormattedValue($setting)
    {
        if ($this->isJsonValue) {
            return json_decode($setting->value, true);
        }

        return $setting->value;
    }

    public function deleteAll()
    {
        // ...
    }
}
