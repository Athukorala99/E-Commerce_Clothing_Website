<?php

namespace FluentForm\App\Services\Settings;

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

        $class = '\FluentForm\App\Services\Settings\Validator\\' . $key;

        if (class_exists($class)) {
            /**
             * Validator class
             *
             * @var $class \FluentForm\App\Services\Settings\Validator\Validate
             */
            $class::validate($data);
        }
    }
}
