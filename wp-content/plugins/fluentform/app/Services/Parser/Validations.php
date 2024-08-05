<?php

namespace FluentForm\App\Services\Parser;

use FluentForm\App\Services\ConditionAssesor;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;

class Validations
{
    /**
     * Form fields that were defined when the form was created.
     *
     * @var array
     */
    protected $fields;

    /**
     * Form inputs that were submited by the user.
     *
     * @var array
     */
    protected $inputs;

    /**
     * The current field accessor of the fields' iteration.
     *
     * @var string
     */
    protected $accessor;

    /**
     * The repeater field settings. It is being set if the
     * field in iteration is indeed a repeater field.
     *
     * @var array
     */
    protected $repeater = [
        'status'    => false,
        'attribute' => '',
        'length'    => 0,
        'rule'      => ''
    ];

    /**
     * The extracted validation rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * The extracted validation messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The validation extractor constructor.
     *
     * @param array $formFields
     * @param array $formData
     */
    public function __construct($formFields = [], $formData = [])
    {
        $this->fields = $formFields;
        $this->inputs = $formData;
    }

    /**
     * Get the extracted validation rules and messages.
     *
     * @return array
     */
    public function get()
    {
        foreach ($this->fields as $fieldName => $field) {
            $this->setFieldAccessor($fieldName);

            $fieldValue = $this->getFieldValue();

            $rules = (array) $field['rules'];

            $hasRequiredRule = Arr::get($rules, 'required.value');

            // If the field is a repeater we'll set some settings here.
            $this->setRepeater($fieldName, $field);

            foreach ($rules as $ruleName => $rule) {
                if ($this->shouldNotSkipThisRule($rule, $fieldValue, $hasRequiredRule)) {
                    $this->prepareValidations($fieldName, $ruleName, $rule);
                }
            }
        }


        return [$this->rules, $this->messages];
    }

    /**
     * Set the field accessor by replacing the `[]`, `*` by `.`
     * so that dot notation can be used to access the inputs.
     *
     * @param  string $fieldName
     * @return $this
     */
    protected function setFieldAccessor($fieldName)
    {
        $this->accessor = rtrim(str_replace(['[', ']', '*'], ['.'], $fieldName), '.');

        return $this;
    }

    /**
     * Get the field value from the form data.
     *
     * @return mixed
     */
    protected function getFieldValue()
    {
        return Arr::get($this->inputs, $this->accessor);
    }

    /**
     * Set the repeater settings if the field in
     * iteration is indeed a repeater field.
     *
     * @param  string $fieldName
     * @param  array  $field
     * @return $this
     */
    protected function setRepeater($fieldName, $field)
    {
        $isRepeater = Arr::get($field, 'element') === 'input_repeat' || Arr::get($field, 'element') == 'repeater_field';

        if ($isRepeater) {
	        $attribute = Arr::get($field, 'attributes.name');
	        $length = isset($attribute[0]) ? count($attribute[0]) : 0;
            $this->repeater = [
                'status'    => $isRepeater,
                'attribute' => $attribute,
                'length'    => $length,
                'rule'      => rtrim($fieldName, '.*')
            ];
        } else {
            $this->repeater['status'] = $isRepeater;
        }

        return $this;
    }

    /**
     * Determines if the iteration should skip this rule or not.
     *
     * @param  array   $rule
     * @return boolean
     */
    protected function shouldNotSkipThisRule($rule, $fieldValue, $hasRequiredRule)
    {
        // If the rule is enabled and the field is not empty and
        // it does have at least one required rule then we
        // should validate it for other rules. Else, we
        // will skip this rule meaning enabling empty
        // submission for this field.
        return $rule['value'] && !($fieldValue === '' && !$hasRequiredRule);
    }

    /**
     * Prepare the validation extraction.
     *
     * @param string $fieldName
     * @param string $ruleName
     * @param array  $rule
     */
    protected function prepareValidations($fieldName, $ruleName, $rule)
    {
        $logic = $this->getLogic($ruleName, $rule);

        if ($this->repeater['status']) {
            for ($i = 0; $i < $this->repeater['length']; $i++) {
                // We need to modify the field name for repeater field.
                $fieldName = $this->repeater['rule'].'['.$i.']';

                $this->setValidations($fieldName, $ruleName, $rule, $logic);
            }
        } else {
            $this->setValidations($fieldName, $ruleName, $rule, $logic);
        }
    }

    /**
     * Set the validation rules & messages
     *
     * @param string $fieldName
     * @param string $ruleName
     * @param array  $rule
     * @param string $logic
     */
    protected function setValidations($fieldName, $ruleName, $rule, $logic)
    {
        // if there is already a rule for this field we need to
        // concat current rule to it. else assign current rule.
        $this->rules[$fieldName] = isset($this->rules[$fieldName]) ?
                                   $this->rules[$fieldName].'|'.$logic : $logic;

        $this->messages[$fieldName.'.'.$ruleName] = $rule['message'];
    }

    /**
     * Get the logic name for the current rule.
     *
     * @param  string $ruleName
     * @param  array  $rule
     * @return string
     */
    protected function getLogic($ruleName, $rule)
    {
        // The file type input has rule values in an array. For
        // that we are taking arrays into consideration.
        $ruleValue = is_array($rule['value']) ?
                     implode(',', array_filter(array_values(str_replace('|', ',', $rule['value'])))) :
                     $rule['value'];

        return $ruleName.':'.$ruleValue;
    }
}
