<?php

namespace FluentForm\App\Services\Settings\Validator;

class MailChimps extends Validate
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
                'name'              => 'required',
                'list'              => 'required',
                'fieldEmailAddress' => 'required',
            ],
            [
                'name.required'              => 'The Name field is required.',
                'list.required'              => 'The Mailchimp List field is required.',
                'fieldEmailAddress.required' => 'The Email Address field is required.',
            ],
        ];
    }
}
