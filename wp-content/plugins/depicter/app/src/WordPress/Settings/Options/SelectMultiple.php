<?php

namespace Depicter\WordPress\Settings\Options;

use Depicter\WordPress\Settings\Options\OptionAbstract;

class SelectMultiple extends OptionAbstract
{
    public $view = 'select-multiple';

    public function get_name_attribute()
    {
        $name = parent::get_name_attribute();

        return "{$name}[]";
    }

    public function sanitize($value)
    {
        return (array) $value;
    }
}
