<?php

namespace FluentForm\App\Services\FormBuilder\Components;

class Hcaptcha extends BaseComponent
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

        $key = get_option('_fluentform_hCaptcha_details');
        if ($key && isset($key['siteKey'])) {
            $siteKey = $key['siteKey'];
        } else {
            $siteKey = '';
        }

        if (! $siteKey) {
            return false;
        }

        wp_enqueue_script(
            'hcaptcha',
            'https://js.hcaptcha.com/1/api.js',
            [],
            FLUENTFORM_VERSION,
            true
        );

        $hcaptchaBlock = "<div
		data-sitekey='" . esc_attr($siteKey) . "'
		id='fluentform-hcaptcha-{$form->id}'
		class='ff-el-hcaptcha h-captcha'></div>";

        $label = '';
        if (! empty($data['settings']['label'])) {
            $label = "<div class='ff-el-input--label'><label>" . $data['settings']['label'] . '</label></div>';
        }

        $containerClass = '';
        if (! empty($data['settings']['label_placement'])) {
            $containerClass = 'ff-el-form-' . $data['settings']['label_placement'];
        }

        $el = "<div class='ff-el-input--content'><div data-fluent_id='" . $form->id . "' name='h-captcha-response'>{$hcaptchaBlock}</div></div>";

        $html = "<div class='ff-el-group " . esc_attr($containerClass) . "' >" . fluentform_sanitize_html($label) . "{$el}</div>";

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
