<?php

namespace FluentForm\App\Modules\Form\Settings\Validator;

class Validator
{
    /**
     * Centralized validator for all the settings data.
     *
     * @param $key
     * @param array $data
     */
    public static function validate($key, $data = [])
    {
        $key = ucwords($key);

        $class = '\FluentForm\App\Modules\Form\Settings\Validator\\' . $key;

        if (class_exists($class)) {
            /**
             * Validator class
             *
             * @var $class Confirmations|MailChimps|Notifications|Pdfs
             */
            $class::validate($data);
        }
    }
}
