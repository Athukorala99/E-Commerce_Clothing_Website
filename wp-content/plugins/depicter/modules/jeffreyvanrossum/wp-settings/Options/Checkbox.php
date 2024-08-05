<?php

namespace Depicter\Jeffreyvr\WPSettings\Options;

use Depicter\Jeffreyvr\WPSettings\Options\OptionAbstract;

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
