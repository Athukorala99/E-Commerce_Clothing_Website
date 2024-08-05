<?php

namespace FluentForm\App\Services\Settings\Validator;

class Pdfs extends Validate
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
                'name'     => 'required',
                'template' => 'required',
                'filename' => 'required',
            ],
            [
                'name.required'     => 'The Name field is required.',
                'template.required' => 'The Template field is required.',
                'filename.required' => 'The Filename field is required.',
            ],
        ];
    }
}
