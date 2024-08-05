<?php

namespace Depicter\Jeffreyvr\WPSettings\Options;

use function Depicter\Jeffreyvr\WPSettings\view as view;
use Depicter\Jeffreyvr\WPSettings\Options\OptionAbstract;

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
