<?php

namespace Depicter\WordPress\Settings\Options;

use Depicter\Jeffreyvr\WPSettings\Options\OptionAbstract as JeffreyvrOptionAbstract;

abstract class OptionAbstract extends JeffreyvrOptionAbstract
{

    public function get_value_attribute()
    {
        return get_option( $this->section->tab->settings->prefix . $this->get_arg('name') ) ?? null;
    }
}
