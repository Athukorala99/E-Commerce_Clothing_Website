<?php

namespace Depicter\Jeffreyvr\WPSettings;

use Depicter\Jeffreyvr\WPSettings\Tab;
use Depicter\Jeffreyvr\WPSettings\Option;

class Section
{
    public $tab;
    public $as_link;
    public $title;
    public $slug;
    public $description;
    public $options = [];
    public $args;

    public function __construct($tab, $title, $args = [])
    {
        $this->tab = $tab;
        $this->title = $title;
        $this->args = $args;
        $this->slug = $this->args['slug'] ?? sanitize_title($title);
        $this->description = $this->args['description'] ?? null;
        $this->as_link = $this->args['as_link'] ?? false;
    }

    public function add_option($type, $args = [])
    {
        $option = new Option($this, $type, $args);

        $this->options[] = $option;

        return $option;
    }
}
