<?php

namespace FluentForm\App\Modules\Widgets;

if (!class_exists('OxyEl')) {
    return;
}

class OxygenWidget
{
    public function __construct()
    {
        if (version_compare(CT_VERSION, '3.2', '<')) {
            return; //minimum version requirement
        }
        add_action('plugins_loaded', [$this, 'initOxygenEl']);
        add_action('oxygen_add_plus_sections', [$this, 'addAccordionSection']);
        add_action('oxygen_add_plus_fluentform_section_content', [$this, 'registerAddPlusSubsections']);
        $this->initWidgets();
    }

    public function initOxygenEl()
    {
        if (!class_exists('OxyEl')) {
            return;
        }
        new OxygenEl();
    }

    public function initWidgets()
    {
        if (file_exists(FLUENTFORM_DIR_PATH . 'app/Modules/Widgets/OxyFluentFormWidget.php')) {
            require_once FLUENTFORM_DIR_PATH . 'app/Modules/Widgets/OxyFluentFormWidget.php';
        }
    }

    public function addAccordionSection()
    {
        $brand_name = __('Fluent Forms', 'fluentform');
        \CT_Toolbar::oxygen_add_plus_accordion_section('fluentform', $brand_name);
    }

    public function registerAddPlusSubsections()
    {
        do_action('oxygen_add_plus_fluentform_form');
    }
}
