<?php

namespace FluentForm\App\Services\Parser;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Services\ConditionAssesor;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;

class Extractor
{
    /**
     * The form field settings.
     *
     * @var array
     */
    protected $fields;

    /**
     * The properties that need to be extracted.
     *
     * @var array
     */
    protected $with;

    /**
     * The supported form input types defined for Fluent Forms.
     *
     * @var array
     */
    protected $inputTypes;

    /**
     * The extracted result.
     *
     * @var array
     */
    protected $result = [];

    /**
     * The current form field that's being set when we loop
     * through all the form fields in the looper method.
     *
     * @var array
     */
    protected $field = [];

    /**
     * The current field attribute that's being set when we loop
     * through all the form fields in the looper method.
     *
     * @var string
     */
    protected $attribute;

    /**
     * Extractor constructor.
     *
     * @param array $fields
     * @param array $with
     * @param array $inputTypes
     */
    public function __construct($fields, $with, $inputTypes)
    {
        $this->fields = $fields;
        $this->with = $with;
        $this->inputTypes = $inputTypes;
    }

    /**
     * The extractor initializer for getting the extracted data.
     *
     * @return array
     */
    public function extract()
    {
        $this->looper($this->fields);

        return $this->result;
    }

    /**
     * The recursive looper method to loop each
     * of the fields and extract it's data.
     *
     * @param array $fields
     */
    protected function looper($fields = [])
    {
        foreach ($fields as $field) {
            // If the field is a Container (collection of other fields)
            // then we will recursively call this function to resolve.
            if ($field['element'] === 'container') {
                foreach ($field['columns'] as $item) {
                    $this->looper($item['fields']);
                }
            }

            // Now the field is supposed to be a flat field.
            // We can extract the desired keys as we want.
            else {
                if (in_array($field['element'], $this->inputTypes)) {
                    $this->extractField($field);
                }
            }
        }
    }

    /**
     * The extractor initializer for getting the extracted data.
     *
     * @return array
     */
    public function extractEssentials($formData)
    {
        $this->looperEssential($formData, $this->fields);

        return $this->result;
    }
    
    /**
     * The recursive looper method to loop each
     * of the fields and extract it's data.
     *
     * @param array $fields
     */
    protected function looperEssential($formData, $fields = [])
    {
        foreach ($fields as $field) {
            $field['conditionals'] = Arr::get($field, 'settings.conditional_logics', []);

            $matched = ConditionAssesor::evaluate($field, $formData);

            if (!$matched) {
                continue;
            }

            // If the field is a Container (collection of other fields)
            // then we will recursively call this function to resolve.
            if ($field['element'] === 'container') {             
                foreach ($field['columns'] as $item) {
                    $this->looperEssential($formData, $item['fields']);
                }
            }

            // Now the field is supposed to be a flat field.
            // We can extract the desired keys as we want.
            else {
                if (in_array($field['element'], $this->inputTypes)) {
                    $this->extractField($field);
                }
            }
        }
    }

    /**
     * Extract the form field.
     *
     * @param  array $field
     * @return $this
     */
    protected function extractField($field)
    {
        // Before starting the extraction we'll set the current
        // field and it's attribute name at first. And then we
        // will proceed to extract the field settings that
        // the developer demanded using $with initially.
        $this->prepareIteration($field, Arr::get($field, 'attributes.name'))
             ->setElement()
             ->setAdminLabel()
             ->setLabel()
             ->setOptions()
             ->setAdvancedOptions()
             ->setSettings()
	         ->setRaw()
             ->setAttributes()
             ->setValidations()
             ->handleCustomField();

        return $this;
    }

    /**
     * Set the field and attribute of the current iteration when
     * we loop through the form fields using the looper method.
     *
     * @param  array  $field
     * @param  string $attribute
     * @return $this
     */
    protected function prepareIteration($field, $attribute)
    {
        $this->field = $field;

        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Set the element of the form field.
     *
     * @param  array  $field
     * @param  string $attributeName
     * @return $this
     */
    protected function setElement()
    {
        $this->result[$this->attribute]['element'] = $this->field['element'];
        return $this;
    }


    /**
     * Set the label of the form field.
     *
     * @return $this
     */
    protected function setLabel()
    {
        if (in_array('label', $this->with)) {
            $this->result[$this->attribute]['label'] = Arr::get($this->field, 'settings.label', '');
        }

        return $this;
    }

    /**
     * Set the admin label of the form field.
     *
     * @return $this
     */
    protected function setAdminLabel()
    {
        if (in_array('admin_label', $this->with)) {
            $adminLabel = Arr::get($this->field, 'settings.admin_field_label') ?:
                          Arr::get($this->field, 'settings.label') ?:
                          Arr::get($this->field, 'element');

            $this->result[$this->attribute]['admin_label'] = $adminLabel;
        }


        return $this;
    }

    /**
     * Set the options of the form field.
     *
     * @return $this
     */
    protected function setOptions()
    {
        if (in_array('options', $this->with)) {
            $options = Arr::get($this->field, 'options', []);

            if(!$options) {
                $newOptions = Arr::get($this->field, 'settings.advanced_options', []);

                if(
                    !$newOptions
                    && Arr::get($this->field,'element') == 'multi_payment_component'
                    && Arr::get($this->field,'attributes.type') != 'single'
                ) {
                    $pricingOptions = Arr::get($this->field, 'settings.pricing_options', []);
                    foreach ($pricingOptions as $pricingOption) {
                        $newOptions[] = [
                            'value' => $pricingOption['label'],
                            'label' => $pricingOption['label']
                        ];
                    }
                }

                $options = [];
                if($newOptions) {
                    foreach ($newOptions as $option) {
                        $value = sanitize_text_field($option['value']);
                        $options[$value] = sanitize_text_field($option['label']);
                    }
                }
            }
            $this->result[$this->attribute]['options'] = $options;
        }
        return $this;
    }

    /**
     * Set the advanced options of the form field.
     *
     * @return $this
     */
    protected function setAdvancedOptions()
    {
        if (in_array('advanced_options', $this->with)) {
            $this->result[$this->attribute]['advanced_options'] = Arr::get($this->field, 'settings.advanced_options', []);
        }
        return $this;
    }

    protected function setSettings()
    {
        if (in_array('settings', $this->with)) {
            $this->result[$this->attribute]['settings'] = Arr::get($this->field, 'settings', []);
        }
        return $this;
    }

    /**
     * Set the attributes of the form field.
     *
     * @return $this
     */
    protected function setAttributes()
    {
        if (in_array('attributes', $this->with)) {
            $this->result[$this->attribute]['attributes'] = Arr::get($this->field, 'attributes');
        }


        return $this;
    }

    /**
     * Set the validation rules and conditions of the form field.
     *
     * @return $this
     */
    protected function setValidations()
    {
        if (in_array('rules', $this->with)) {
            $this->result[$this->attribute]['rules'] = Arr::get(
                $this->field,
                'settings.validation_rules'
            );

            $this->handleMaxLengthValidation();

            $this->result[$this->attribute]['conditionals'] = Arr::get(
                $this->field,
                'settings.conditional_logics'
            );
        }

        return $this;
    }

    protected function handleMaxLengthValidation()
    {
        $maxLength = Arr::get($this->field, 'attributes.maxlength');
        $fieldHasMaxValidation = Arr::get($this->field, 'settings.validation_rules.max');
        $shouldSetMaxValidation = $maxLength && !$fieldHasMaxValidation;

        if ($shouldSetMaxValidation) {
            $this->result[$this->attribute]['rules']['max'] = [
                'value'   => $maxLength,
                "message" => Helper::getGlobalDefaultMessage('max'),
            ];
        }

        return $this;
    }

    /**
     * Handle the child fields of the custom field.
     *
     * @return $this
     */
    protected function handleCustomField()
    {
        // If this field is a custom field we'll assume it has it's child fields
        // under the `fields` key. Then we are gonna modify those child fields'
        // attribute `name`, `label` & `conditional_logics` properties using
        // the parent field. The current implementation will modify those
        // properties in a way so that we can use dot notation to access.
        $customFields = Arr::get($this->field, 'fields');

        if ($customFields) {
            $parentAttribute = Arr::get($this->field, 'attributes.name');

            $parentConditionalLogics = Arr::get($this->field, 'settings.conditional_logics', []);

            $isAddressOrNameField = in_array(Arr::get($this->field, 'element'), ['address', 'input_name']);

            $isRepeatField = Arr::get($this->field, 'element') === 'input_repeat' || Arr::get($this->field, 'element') == 'repeater_field';

            foreach ($customFields as $index => $customField) {
                // If the current field is in fact `address` || `name` field
                // then we have to only keep the enabled child fields
                // by the user from the form editor settings.
                if ($isAddressOrNameField) {
                    if (!Arr::get($customField, 'settings.visible', false)) {
                        unset($customFields[$index]);
                        continue;
                    }
                }

                // Depending on whether the parent field is a repeat field or not
                // the modified attribute name of the child field will vary.
                if ($isRepeatField) {
                    $modifiedAttribute = $parentAttribute.'['.$index.'].*';
                } else {
                    $modifiedAttribute = $parentAttribute.'['.Arr::get($customField, 'attributes.name').']';
                }

                $modifiedLabel = $parentAttribute.'['.Arr::get($customField, 'settings.label').']';

                $customField['attributes']['name'] = $modifiedAttribute;

                $customField['settings']['label'] = $modifiedLabel;

                // Now, we'll replace the `conditional_logics` property
                $customField['settings']['conditional_logics'] = $parentConditionalLogics;

                // Now that this field's properties are handled we can pass
                // it to the extract field method to extract it's data.
                $this->extractField($customField);
            }
        }

        return $this;
    }
    
	/**
	 * Set the raw field of the form field.
	 *
	 * @return $this
	 */
	protected function setRaw()
	{
		if (in_array('raw', $this->with)) {
			$this->result[$this->attribute]['raw'] = $this->field;
		}

		return $this;
	}
}
