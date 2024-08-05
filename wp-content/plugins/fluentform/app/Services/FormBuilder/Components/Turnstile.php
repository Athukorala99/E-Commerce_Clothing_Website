<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\Framework\Helpers\ArrayHelper;

class Turnstile extends BaseComponent
{
    /**
     * Compile and echo the html element
     *
     * @param array     $data [element data]
     * @param \stdClass $form [Form Object]
     *
     * @return void
     */
    public function compile($data, $form)
    {
        $elementName = $data['element'];
        $data = apply_filters_deprecated(
            'fluentform_rendering_field_data_' . $elementName,
            [
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/rendering_field_data_' . $elementName,
            'Use fluentform/rendering_field_data_' . $elementName . ' instead of fluentform_rendering_field_data_' . $elementName
        );
        $data = apply_filters('fluentform/rendering_field_data_' . $elementName, $data, $form);

        $turnstile = get_option('_fluentform_turnstile_details');
        $siteKey = ArrayHelper::get($turnstile, 'siteKey');

        if (! $siteKey) {
            return false;
        }

        add_filter('fluentform/html_attributes', function ($atts) use ($siteKey) {
            $atts['data-turnstile_key'] = $siteKey;
            return $atts;
        });

        wp_enqueue_script(
            'turnstile',
            'https://challenges.cloudflare.com/turnstile/v0/api.js',
            [],
            FLUENTFORM_VERSION,
            true
        );

        $turnstileBlock = "<div
		data-sitekey='" . esc_attr($siteKey) . "'
		data-theme='" . esc_attr(ArrayHelper::get($turnstile, 'theme', 'auto')) . "'
		id='fluentform-turnstile-{$form->id}'
		class='ff-el-turnstile cf-turnstile'
		data-callback='turnstileCallback'></div>";

        $label = '';
        if (! empty($data['settings']['label'])) {
            $label = "<div class='ff-el-input--label'><label>" . fluentform_sanitize_html($data['settings']['label']) . '</label></div>';
        }

        $containerClass = '';
        if (! empty($data['settings']['label_placement'])) {
            $containerClass = 'ff-el-form-' . $data['settings']['label_placement'];
        }

        if ('yes' == ArrayHelper::get($turnstile, 'invisible')) {
            $el = "<div class='ff-el-input--content'><div data-fluent_id='" . $form->id . "' name='cf-turnstile-response' style='display: none'>{$turnstileBlock}</div></div>";
        } else {
            $el = "<div class='ff-el-input--content'><div data-fluent_id='" . $form->id . "' name='cf-turnstile-response'>{$turnstileBlock}</div></div>";
        }

        $html = "<div class='ff-el-group " . esc_attr($containerClass) . "' >{$label}{$el}</div>";
    
        $html = apply_filters_deprecated(
            'fluentform_rendering_field_html_' . $elementName,
            [
                $html,
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/rendering_field_html_' . $elementName,
            'Use fluentform/rendering_field_html_' . $elementName . ' instead of fluentform_rendering_field_html_' . $elementName
        );
        $this->printContent('fluentform/rendering_field_html_' . $elementName, $html, $data, $form);
    }
}
