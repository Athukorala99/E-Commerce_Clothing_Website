<?php

namespace Depicter\WordPress\Settings\Options;

use Depicter\WordPress\Settings\Options\OptionAbstract;

class Checkbox extends OptionAbstract
{
    public $view = 'checkbox';

    public function get_value_attribute()
    {
        return '1';
    }

    public function is_checked()
    {
        return parent::get_value_attribute();
    }
}
