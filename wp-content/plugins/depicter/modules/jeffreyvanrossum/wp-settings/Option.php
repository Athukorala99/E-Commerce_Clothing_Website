<?php

namespace Depicter\Jeffreyvr\WPSettings;

use Depicter\Jeffreyvr\WPSettings\Section;
use Depicter\Jeffreyvr\WPSettings\Options\Text;
use Depicter\Jeffreyvr\WPSettings\Options\Select;
use Depicter\Jeffreyvr\WPSettings\Options\Choices;
use Depicter\Jeffreyvr\WPSettings\Options\Checkbox;
use Depicter\Jeffreyvr\WPSettings\Options\Textarea;
use Depicter\Jeffreyvr\WPSettings\Options\WPEditor;
use Depicter\Jeffreyvr\WPSettings\Options\CodeEditor;
use Depicter\Jeffreyvr\WPSettings\Options\SelectMultiple;

class Option
{
    public $section;
    public $type;
    public $args = [];
    public $implementation;

    public function __construct($section, $type, $args = [])
    {
        $this->section = $section;
        $this->type = $type;
        $this->args = $args;

        $type_map = apply_filters('wp_settings_option_type_map', [
            'text' => Text::class,
            'checkbox' => Checkbox::class,
            'choices' => Choices::class,
            'textarea' => Textarea::class,
            'wp-editor' => WPEditor::class,
            'code-editor' => CodeEditor::class,
            'select' => Select::class,
            'select-multiple' => SelectMultiple::class
        ]);

        $this->implementation = new $type_map[$this->type]($section, $args);
    }

    public function get_arg($key, $fallback = null)
    {
        return $this->args[$key] ?? $fallback;
    }

    public function sanitize($value)
    {
        if (\is_callable($this->get_arg('sanitize'))) {
            return $this->get_arg('sanitize')($value);
        }

        return $this->implementation->sanitize($value);
    }

    public function validate($value)
    {
        if (is_array($this->get_arg('validate'))) {
            foreach ($this->get_arg('validate') as $validate) {
                if (! \is_callable($validate['callback'])) {
                    continue;
                }

                $valid = $validate['callback']($value);

                if (!$valid) {
                    $this->section->tab->settings->errors->add($this->get_arg('name'), $validate['feedback']);

                    return false;
                }
            }

            return true;
        }

        if (\is_callable($this->get_arg('validate'))) {
            return $this->get_arg('validate')($value);
        }

        return $this->implementation->validate($value);
    }

    public function render()
    {
        if (\is_callable($this->get_arg('visible')) && $this->get_arg('visible')() === false) {
            return;
        }

        if (\is_callable($this->get_arg('render'))) {
            echo $this->get_arg('render')($this->implementation);

            return;
        }

        echo $this->implementation->render();
    }
}
