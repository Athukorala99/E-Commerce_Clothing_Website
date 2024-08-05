<?php

namespace FluentForm\App\Services\Integrations;

use FluentForm\App\Models\EntryDetails;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\ConditionAssesor;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Models\Submission;


class GlobalNotificationService
{
    public function checkCondition($parsedValue, $formData, $insertId)
    {
        $conditionSettings = ArrayHelper::get($parsedValue, 'conditionals');
        if (
            !$conditionSettings ||
            !ArrayHelper::isTrue($conditionSettings, 'status') ||
            !count(ArrayHelper::get($conditionSettings, 'conditions'))
        ) {
            return true;
        }
        
        return ConditionAssesor::evaluate($parsedValue, $formData);
    }
    
    public function getEntry($id, $form)
    {
        $submission = Submission::find($id);
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);
        return FormDataParser::parseFormEntry($submission, $form, $formInputs);
    }
    
    public function cleanUpPassword($insertId, $form)
    {
        // Let's get the password fields
        $inputs = FormFieldsParser::getInputsByElementTypes($form, ['input_password']);
        
        if (!$inputs) {
            return;
        }
        $passwordKeys = array_keys($inputs);
        
        // Let's delete from entry details
        EntryDetails::where('form_id', $form->id)->where('field_name', $passwordKeys)->where('submission_id',
            $insertId)->delete();
        // Let's alter from main submission data
        $submission = Submission::find($insertId);
        
        if (!$submission) {
            return;
        }
        
        $responseInputs = \json_decode($submission->response, true);
        
        $replaced = false;
        foreach ($passwordKeys as $passwordKey) {
            if (!empty($responseInputs[$passwordKey])) {
                $responseInputs[$passwordKey] = str_repeat('*', 6) . ' ' . __('(truncated)', 'fluentform');
                $replaced = true;
            }
        }
        
        if ($replaced) {
            Submission::where('id', $insertId)->update(['response' => \json_encode($responseInputs)]);
        }
    }
    
    /**
     * @param $feeds
     * @param $formData
     * @param $insertId
     *
     * @return array
     */
    public function getEnabledFeeds($feeds, $formData, $insertId)
    {
        $enabledFeeds = [];
        foreach ($feeds as $feed) {
            $parsedValue = json_decode($feed->value, true);
            if ($parsedValue && ArrayHelper::isTrue($parsedValue, 'enabled')) {
                // Now check if conditions matched or not
                $isConditionMatched = $this->checkCondition($parsedValue, $formData, $insertId);
                if ($isConditionMatched) {
                    $item = [
                        'id'       => $feed->id,
                        'meta_key' => $feed->meta_key,
                        'settings' => $parsedValue,
                    ];
                    if ('user_registration_feeds' == $feed->meta_key) {
                        array_unshift($enabledFeeds, $item);
                    } else {
                        $enabledFeeds[] = $item;
                    }
                }
            }
        }
        return $enabledFeeds;
    }
    
    public function getNotificationFeeds($form, $feedMetaKeys)
    {
        return FormMeta::where('form_id', $form->id)->whereIn('meta_key', $feedMetaKeys)->orderBy('id', 'ASC')->get();
    }
    
}
