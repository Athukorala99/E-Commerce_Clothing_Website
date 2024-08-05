<?php

namespace Depicter\Jeffreyvr\WPSettings\Options;

use function Depicter\Jeffreyvr\WPSettings\view as view;
use Depicter\Jeffreyvr\WPSettings\Options\OptionAbstract;

class WPEditor extends OptionAbstract
{
    public $view = 'wp-editor';

    public function sanitize($value)
    {
        return $value;
    }
}
