<?php

namespace FluentForm\App\Services\Form;

use FluentForm\App\Models\Form;
use FluentForm\Framework\Support\Arr;
use FluentForm\App\Modules\Form\FormFieldsParser;

class Fields
{
    public function get($formId)
    {
        $form = Form::find($formId);

        $fields = FormFieldsParser::getShortCodeInputs($form, [
            'admin_label', 'attributes', 'options',
        ]);

        $fields = array_filter($fields, function ($field) {
            return in_array($field['element'], $this->supportedConditionalFields());
        });

        return $this->filterEditorFields($fields);
    }

    public function supportedConditionalFields()
    {
        $supportedConditionalFields = [
            'select',
            'ratings',
            'net_promoter',
            'textarea',
            'shortcode',
            'input_url',
            'input_text',
            'input_date',
            'input_email',
            'input_radio',
            'input_number',
            'select_country',
            'input_checkbox',
            'input_password',
            'terms_and_condition',
            'gdpr_agreement',
            'input_hidden',
            'input_file',
            'input_image',
            'subscription_payment_component',
        ];
    
        $supportedConditionalFields = apply_filters_deprecated(
            'fluentform_supported_conditional_fields',
            [
                $supportedConditionalFields
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/supported_conditional_fields',
            'Use fluentform/supported_conditional_fields instead of fluentform_supported_conditional_fields.'
        );
        return apply_filters('fluentform/supported_conditional_fields', $supportedConditionalFields);
    }

    public function filterEditorFields($fields)
    {
        foreach ($fields as $index => $field) {
            $element = Arr::get($field, 'element');
            if ('select_country' == $element) {
                $fields[$index]['options'] = getFluentFormCountryList();
            } elseif ('gdpr-agreement' == $element || 'terms_and_condition' == $element) {
                $fields[$index]['options'] = ['on' => 'Checked'];
            }
        }

        return $fields;
    }
}
