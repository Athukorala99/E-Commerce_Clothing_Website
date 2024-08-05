<?php

namespace FluentForm\App\Helpers;


use FluentForm\App\Models\FormMeta;
use FluentForm\App\Services\Integrations\FormIntegrationService;

class IntegrationManagerHelper
{
    protected $settingsKey;
    protected $formId;
    protected $isMultiple;
    
    protected $integrationService;
    
    public function __construct($settingsKey = '', $form_id = false, $isMultiple = false)
    {
        $this->settingsKey = $settingsKey;
        $this->isMultiple = $isMultiple;
        $this->formId = $form_id;
        $this->integrationService = new FormIntegrationService();
    }
    
    public function get($settingsId)
    {
        $settings = FormMeta::where('form_id', $this->formId)
            ->where('meta_key', $this->settingsKey)
            ->find($settingsId);
        $settings->formattedValue = $this->getFormattedValue($settings);
        return $settings;
    }
    
    public function save($settings)
    {
        return FormMeta::insertGetId([
            'meta_key' => $this->settingsKey,
            'form_id'  => $this->formId,
            'value'    => json_encode($settings),
        ]);
    }
    
    public function update($settingsId, $settings)
    {
        return FormMeta::where('id', $settingsId)
            ->update([
                'value' => json_encode($settings),
            ]);
    }
    
    public function delete($settingsId)
    {
        FormMeta::where('id', $settingsId)
            ->delete();
    }
    
    public function getAll()
    {
        $settingsQuery = FormMeta::where('form_id', $this->formId)
            ->where('meta_key', $this->settingsKey);
        
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
    
    protected function logResponse($response, $feed, $data, $form, $entryId, $status)
    {
        if (!$response) {
            return;
        }

        $oldAction = 'fluentform_after_submission_api_response_' . $status;
        $newAction = 'fluentform/after_submission_api_response_' . $status;

        do_action_deprecated(
            $oldAction,
            [
                $form,
                $entryId,
                $data,
                $feed,
                $response,
                $this->getApiResponseMessage($response, $status)
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            $newAction,
            'Use ' . $newAction . ' instead of ' .$oldAction
        );
        
        do_action(
            $newAction,
            $form,
            $entryId,
            $data,
            $feed,
            $response,
            $this->getApiResponseMessage($response, $status)
        );
    }
    
    protected function getApiResponseMessage($response, $status)
    {
        if (is_array($response) && isset($response['message'])) {
            return $response['message'];
        }
        
        return $status;
    }
    
    public function getFormattedValue($setting)
    {
        if (Helper::isJson($setting->value)) {
            return json_decode($setting->value, true);
        }
        
        return $setting->value;
    }
    
    
}
