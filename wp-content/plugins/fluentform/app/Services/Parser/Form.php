<?php

namespace FluentForm\App\Services\Parser;

use FluentForm\App\Helpers\Str;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;

class Form
{
    /**
     * @var \stdClass $form
     */
    protected $form;

    /**
     * @var array $inputTypes
     */
    protected $inputTypes;

    /**
     * The parsed form fields.
     *
     * @var array
     */
    protected $parsed;

    /**
     * The parsed essential form fields.
     *
     * @var array
     */
    protected $essentials;

    /**
     * The parsed validations
     *
     * @var array
     */
    protected $validations;

    /**
     * Form Parser constructor.
     *
     * @param \stdClass $form
     */
    public function __construct($form)
    {
        $this->form = $form;

        $this->setInputTypes();
    }

    /**
     * Set input types of the form.
     *
     * @param array $types
     * @return \FluentForm\App\Services\Parser\Form $this
     */
    public function setInputTypes($types = [])
    {
        // If the $types is empty we'll use the default input types.
        $types = $types ?: [
            'input_text',
            'input_name',
            'textarea',
            'select',
            'input_radio',
            'input_checkbox',
            'input_email',
            'input_url',
            'input_password',
            'input_file',
            'input_image',
            'input_date',
            'select_country',
            'input_number',
            'input_repeat',
            'address',
            'terms_and_condition',
            'input_hidden',
            'ratings',
            'net_promoter',
            'tabular_grid',
            'gdpr_agreement',
            'taxonomy'
        ];
    
        $types = apply_filters_deprecated(
            'fluentform_form_input_types',
            [
                $types
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_input_types',
            'Use fluentform/form_input_types instead of fluentform_form_input_types'
        );
        // Firing an event so that others can hook into it and add other input types.
        $this->inputTypes = apply_filters('fluentform/form_input_types', $types);

        return $this;
    }

    /**
     * Get form fields.
     *
     * @param boolean $asArray
     * @return array
     */
    public function getFields($asArray = false)
    {
        $fields = json_decode($this->form->form_fields, $asArray);

        $default = $asArray ? [] : null;

        return Arr::get((array)$fields, 'fields', $default);
    }

    /**
     * Get flatten form inputs. Flatten implies that all
     * of the form fields will be in a simple array.
     *
     * @param array $with
     * @return array
     */
    public function getInputs($with = [])
    {
        // If the form is already parsed we'll return it. Otherwise,
        // we'll parse the form and return the data after saving it.
        if (!$this->parsed) {
            $fields = $this->getFields(true);

            $with = $with ?: ['admin_label', 'element', 'options', 'attributes', 'raw'];
            $this->parsed = (new Extractor($fields, $with, $this->inputTypes))->extract();
        }

        return $this->parsed;
    }

    /**
     * Get the inputs just as they setup in the form editor.
     * e.g. `names` as `names` not with the child fields.
     *
     * @param array $with
     * @return array
     */
    public function getEntryInputs($with = ['admin_label'])
    {
        $inputs = $this->getInputs($with);

        // The inputs that has `[]` in their keys are custom fields
        // & for the purpose of this scenario we'll remove those.
        foreach ($inputs as $key => $value) {
            if (Str::contains($key, '[')) {
                unset($inputs[$key]);
            }
        }

        return $inputs;
    }

    /**
     * Get the flatten inputs as the result of the `getInputs`
     * method but replace the keys those have `[]` with `.`
     * And also remove the repeat fields' child fields.
     *
     * @param array $with
     * @param array
     */
    public function getShortCodeInputs($with = ['admin_label'])
    {
        $inputs = $this->getInputs($with);

        $result = [];

        // For the purpose of this scenario we'll rename
        // the keys that have `[]` in 'em to `.` and
        // remove the keys that have `*` in 'em.
        foreach ($inputs as $key => $value) {
            if (Str::contains($key, '*')) {
                unset($inputs[$key]);
            } else {
                $key = str_replace(['[', ']'], ['.'], $key);

                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Get admin labels of the form fields.
     *
     * @param array $fields
     * @return array
     */
    public function getAdminLabels($fields = [])
    {
        $fields = $fields ?: $this->getInputs(['admin_label']);

        $labels = [];

        foreach ($fields as $key => $field) {
            $labels[$key] = Arr::get($field, 'admin_label');
        }

        return $labels;
    }

    /**
     * Get admin labels of the form fields.
     *
     * @param array $inputs
     * @param array $fields
     * @return array
     */
    public function getValidations($inputs, $fields = [])
    {
        // If the form validations are already parsed we'll return it.
        // Otherwise, we'll parse the form validation and return
        // the data after saving it to the validations array.
        if (!$this->validations) {
            $fields = $fields ?: $this->getInputs(['rules']);
            $this->validations = (new Validations($fields, $inputs))->get();
        }

        return $this->validations;
    }

    /**
     * Get an element by it's name.
     *
     * @param string|array $name
     * @param array $with
     * @return array
     */
    public function getElement($name, $with = [])
    {
        $this->inputTypes = (array) $name;

        return $this->getInputs($with);
    }

    /**
     * Determine whether the form has an element.
     *
     * @param string $name
     * @return bool
     */
    public function hasElement($name)
    {
        $elements = $this->getElement($name, ['element']);
        
        foreach ($elements as $item) {
            if ($item['element'] === $name) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Determine whether the form has any required fields.
     *
     * @param array $fields
     * @return bool
     */
    public function hasRequiredFields($fields = [])
    {
        // $fields can be user provided when called this method or,
        // the current object could have already parsed fields or,
        // we should parse the form and use the processed result.
        $fields = $fields ?: $this->parsed ?: $this->getInputs(['rules']);

        $exist = false;

        foreach ($fields as $field) {
            $exist = Arr::get($field, 'rules.required.value');

            if ($exist) {
                break;
            }
        }

        return (boolean)$exist;
    }


    /**
     * Get Payment Related Fields
     *
     * @param array $with array
     * @return array
     */
    public function getPaymentFields($with = ['element'])
    {
        $fields = $this->getInputs($with);

        $data = [
                'custom_payment_component',
                'multi_payment_component',
                'payment_method',
                'item_quantity_component',
                'payment_coupon',
	            'subscription_payment_component'
        ];
    
        $data = apply_filters_deprecated(
            'fluentform_form_payment_fields',
            [
                $data
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_payment_fields',
            'Use fluentform/form_payment_fields instead of fluentform_form_payment_fields'
        );

        $paymentElements = apply_filters('fluentform/form_payment_fields', $data);

        return array_filter($fields, function ($field) use ($paymentElements) {
            return in_array($field['element'], $paymentElements);
        });
    }

    /**
     * Get Payment Input Fields
     *
     * @return array
     */
    public function getPaymentInputFields($with = ['element'])
    {
        $fields = $this->getInputs($with);

        $data = [
            'custom_payment_component',
            'multi_payment_component'
        ];
    
        $data = apply_filters_deprecated(
            'fluentform_form_payment_inputs',
            [
                $data
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_payment_inputs',
            'Use fluentform/form_payment_inputs instead of fluentform_form_payment_inputs'
        );

        $paymentElements = apply_filters('fluentform/form_payment_inputs', $data);

        return array_filter($fields, function ($field) use ($paymentElements) {
            return in_array($field['element'], $paymentElements);
        });
    }

    /**
     * Determine whether the form has payment elements
     *
     * @return bool
     */
    public function hasPaymentFields()
    {
        $fields = $this->getInputs(['element']);

        $data = [
            'custom_payment_component',
            'multi_payment_component',
            'payment_method',
            'item_quantity_component',
            'payment_coupon',
            'subscription_payment_component'
        ];
    
        $data = apply_filters_deprecated(
            'fluentform_form_payment_fields',
            [
                $data
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_payment_fields',
            'Use fluentform/form_payment_fields instead of fluentform_form_payment_fields'
        );

        $paymentElements = apply_filters('fluentform/form_payment_fields', $data);

        foreach ($fields as $field) {
            if (in_array($field['element'], $paymentElements)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Get an specific field for an element type.
     *
     * @param $element
     * @param $attribute
     * @param array $with
     * @return array|null
     */
    public function getField($element, $attribute, $with = [])
    {
        $element = $this->getElement($element, $with);

        return array_intersect_key($element, array_flip((array)$attribute));
    }


    /**
     * Get Payment Input Fields
     *
     * @return array
     */
    public function getAttachmentInputFields($with = ['element'])
    {
        $fields = $this->getInputs($with);
        $paymentElements = [
            'input_file',
            'input_image',
            'featured_image',
            'signature'
        ];

        return array_filter($fields, function ($field) use ($paymentElements) {
            return in_array($field['element'], $paymentElements);
        });
    }

    /**
     * Get Any Field Type
     * @return array
     */

    public function getInputsByElementTypes($types, $with = ['element'])
    {
        $fields = $this->getInputs($with);

        return array_filter($fields, function ($field) use ($types) {
            return in_array($field['element'], $types);
        });
    }

    /**
     * Get Address Fields
     *
     * @return array
     */
    public function getAddressFields($with = ['admin_label', 'attributes'])
    {
        $fields = $this->getInputs($with);
        $addressElements = [
            'address'
        ];

        return array_filter($fields, function ($field) use ($addressElements) {
            return in_array($field['element'], $addressElements);
        });
    }

    public function getEssentialInputs($formData, $with = [])
    {
        // If the form is already parsed we'll return it. Otherwise,
        // we'll parse the form and return the data after saving it.
        if (!$this->essentials) {
            $fields = $this->getFields(true);

            $with = $with ?: ['rules', 'raw'];

            $this->essentials = (new Extractor($fields, $with, $this->inputTypes))->extractEssentials($formData);
        }

        return $this->essentials;
    }
}
