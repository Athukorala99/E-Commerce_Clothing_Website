<?php

namespace FluentForm\App\Services\Settings\Validator;

use FluentForm\Framework\Validator\Validator;

class Confirmations extends Validate
{
    /**
     * Produce the necessary validation rules and corresponding messages
     *
     * @return array
     */
    public static function validations()
    {
        return [
            [
                'redirectTo' => 'required',
                'customPage' => 'required_if:redirectTo,customPage',
                'customUrl'  => 'required_if:redirectTo,customUrl',
            ],
            [
                'redirectTo.required'    => __('The Confirmation Type field is required.', 'fluentform'),
                'customPage.required_if' => __('The Page field is required when Confirmation Type is Page.', 'fluentform'),
                'customUrl.required_if'  => __('The Redirect URL field is required when Confirmation Type is Redirect.', 'fluentform'),
                'customUrl.required'     => __('The Redirect URL format is invalid.', 'fluentform'),
            ],
        ];
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
        $validator->sometimes('customUrl', 'required', function ($input) {
            return 'customUrl' === $input['redirectTo'];
        });

        return $validator;
    }
}
