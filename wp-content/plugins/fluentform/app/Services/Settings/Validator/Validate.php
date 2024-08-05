<?php

namespace FluentForm\App\Services\Settings\Validator;

use FluentForm\Framework\Validator\Validator;
use FluentForm\Framework\Validator\ValidationException;

abstract class Validate
{
    /**
     * Validates confirmations settings data.
     *
     * @param array $data
     *
     * @return bool
     */
    public static function validate($data = [])
    {
        // Prepare the validation rules & messages.
        [$rules, $messages] = static::validations();

        // Make validator instance.
        $validator = wpFluentForm('validator')->make($data, $rules, $messages);

        // Add conditional validations if there's any.
        $validator = static::conditionalValidations($validator);

        // Validate and process response.
        if ($validator->validate()->fails()) {
            throw new ValidationException('Unprocessable Entity!', 422, null, $validator->errors());
        }

        return true;
    }

    /**
     * Produce the necessary validation rules and corresponding messages
     *
     * @return array
     */
    public static function validations()
    {
        return [[], []];
    }

    /**
     * Add conditional validations to the validator.
     *
     * @param \FluentForm\Framework\Validator\Validator $validator
     *
     * @return \FluentForm\Framework\Validator\Validator
     */
    public static function conditionalValidations(Validator $validator)
    {
        return $validator;
    }
}
