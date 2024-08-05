<?php

namespace FluentForm\App\Modules;

use FluentForm\Framework\Helpers\ArrayHelper;

class EditorButtonModule
{
    public function addButton()
    {
        if (! $this->pageSupportedMediaButtons()) {
            return;
        }

        $this->addMceButtonAssets();

        $url = fluentformMix('img/icon_black_small.png');
        
        echo "<button id='fluent_form_insert_button' class='button'><span style='background-image: url(" . esc_url($url) . "); width: 16px;height: 16px;background-repeat: no-repeat;display: inline-block;background-size: contain;opacity: 0.4;margin-right: 5px;vertical-align: middle;'></span>" . __('Add Form', 'fluentform') . '</button>';
    }

    private function addMceButtonAssets()
    {
        wp_enqueue_script(
            'fluentform_editor_script',
            fluentformMix('js/fluentform_editor_script.js'),
            ['jquery'],
            FLUENTFORM_VERSION
        );

        $forms = wpFluent()->table('fluentform_forms')
                    ->select(['id', 'title'])
                    ->get();

        $forms = array_map(function ($item) {
            return ['value' => $item->id, 'text' => $item->title];
        }, $forms);

        wp_localize_script('fluentform_editor_script', 'fluentform_editor_vars', [
            'forms' => $forms,
        ]);
    }

    private function pageSupportedMediaButtons()
    {
        $currentPage = basename(sanitize_text_field(wpFluentForm('request')->server('PHP_SELF')));
        $isEligiblePage = in_array($currentPage, [
            'post.php',
            'page.php',
            'page-new.php',
            'post-new.php',
            'customize.php',
        ]);

        if ($isEligiblePage) {
            $option = get_option('_fluentform_global_form_settings');
            $isEligiblePage = 'yes' == ArrayHelper::get($option, 'misc.classicEditorButton');
        }
    
        $isEligiblePage = apply_filters_deprecated(
            'fluentform_display_add_form_button',
            [
                $isEligiblePage
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/display_add_form_button',
            'Use fluentform/display_add_form_button instead of fluentform_display_add_form_button'
        );

        return apply_filters('fluentform/display_add_form_button', $isEligiblePage);
    }

    private function getMenuIcon()
    {
        return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><defs><style>.cls-1{fill:#fff;}</style></defs><title>dashboard_icon</title><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path class="cls-1" d="M15.57,0H4.43A4.43,4.43,0,0,0,0,4.43V15.57A4.43,4.43,0,0,0,4.43,20H15.57A4.43,4.43,0,0,0,20,15.57V4.43A4.43,4.43,0,0,0,15.57,0ZM12.82,14a2.36,2.36,0,0,1-1.66.68H6.5A2.31,2.31,0,0,1,7.18,13a2.36,2.36,0,0,1,1.66-.68l4.66,0A2.34,2.34,0,0,1,12.82,14Zm3.3-3.46a2.36,2.36,0,0,1-1.66.68H3.21a2.25,2.25,0,0,1,.68-1.64,2.36,2.36,0,0,1,1.66-.68H16.79A2.25,2.25,0,0,1,16.12,10.53Zm0-3.73a2.36,2.36,0,0,1-1.66.68H3.21a2.25,2.25,0,0,1,.68-1.64,2.36,2.36,0,0,1,1.66-.68H16.79A2.25,2.25,0,0,1,16.12,6.81Z"/></g></g></svg>');
    }
}
