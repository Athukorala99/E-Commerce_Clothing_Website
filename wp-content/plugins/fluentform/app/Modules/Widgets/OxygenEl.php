<?php

namespace FluentForm\App\Modules\Widgets;

if (!class_exists('OxyEl')) {
    return;
}

class OxygenEl extends \OxyEl
{
    public function init()
    {
        $this->El->useAJAXControls();
    }

    public function class_names()
    {
        return ['ff-oxy-element'];
    }

    public function button_place()
    {
        $button_place = $this->accordion_button_place();

        if ($button_place) {
            return 'fluentform::' . $button_place;
        }

        return '';
    }

    public function button_priority()
    {
        return '';
    }

    public function isBuilderEditorActive()
    {
        if (wpFluentForm('request')->get('oxygen_iframe') || defined('OXY_ELEMENTS_API_AJAX')) {
            return true;
        }

        return false;
    }
}
