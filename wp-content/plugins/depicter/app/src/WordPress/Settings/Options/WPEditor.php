<?php

namespace Depicter\WordPress\Settings\Options;

use Depicter\WordPress\Settings\Options\OptionAbstract;

class WPEditor extends OptionAbstract
{
    public $view = 'wp-editor';

    public function sanitize($value)
    {
        return $value;
    }
}
