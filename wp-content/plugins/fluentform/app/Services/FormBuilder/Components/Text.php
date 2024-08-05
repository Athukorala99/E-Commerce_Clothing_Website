<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

class Text extends BaseComponent
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

        // </mask input>
        if (isset($data['settings']['temp_mask']) && 'custom' != $data['settings']['temp_mask']) {
            $data['attributes']['data-mask'] = $data['settings']['temp_mask'];
        }

        if ('custom' == ArrayHelper::get($data, 'settings.temp_mask')) {
            if ('yes' == ArrayHelper::get($data, 'settings.data-mask-reverse')) {
                $data['attributes']['data-mask-reverse'] = 'true';
            }

            if ('yes' == ArrayHelper::get($data, 'settings.data-clear-if-not-match')) {
                $data['attributes']['data-clear-if-not-match'] = 'true';
            }
        }

        if (isset($data['attributes']['data-mask'])) {
            wp_enqueue_script(
                'jquery-mask',
                fluentformMix('libs/jquery.mask.min.js'),
                ['jquery'],
                false,
                true
            );
        }

        if ('input_number' == $data['element'] || 'custom_payment_component' == $data['element']) {
            if (
                ArrayHelper::get($data, 'settings.calculation_settings.status') &&
                $formula = ArrayHelper::get($data, 'settings.calculation_settings.formula')
            ) {
                $data['attributes']['data-calculation_formula'] = $formula;
                $data['attributes']['class'] .= ' ff_has_formula';
                $data['attributes']['readonly'] = true;
                $data['attributes']['type'] = 'text';

                add_filter('fluentform/form_class', function ($css_class, $targetForm) use ($form) {
                    if ($targetForm->id == $form->id) {
                        $css_class .= ' ff_calc_form';
                    }
                    return $css_class;
                }, 10, 2);
                do_action_deprecated(
                    'ff_rendering_calculation_form',
                    [
                        $form,
                        $data
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/rendering_calculation_form',
                    'Use fluentform/rendering_calculation_form instead of ff_rendering_calculation_form'
                );
                do_action('fluentform/rendering_calculation_form', $form, $data);
            } else {
                $isDisable = apply_filters_deprecated(
                    'fluentform_disable_inputmode',
                    [
                        false
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/disable_input_mode',
                    'Use fluentform/disable_input_mode instead of fluentform_disable_inputmode'
                );
                if (! apply_filters('fluentform/disable_input_mode', $isDisable)) {
                    $inputMode = apply_filters('fluentform/number_input_mode', ArrayHelper::get($data, 'attributes.inputmode'), $data, $form);
                    if (! $inputMode) {
                        $inputMode = 'numeric';
                    }
                    $data['attributes']['inputmode'] = $inputMode;
                }
            }

            if ($step = ArrayHelper::get($data, 'settings.number_step')) {
                $data['attributes']['step'] = $step;
            } elseif ('number' == ArrayHelper::get($data, 'attributes.type')) {
                $data['attributes']['step'] = 'any';
            }

            $min = ArrayHelper::get($data, 'settings.validation_rules.min.value');
            if ($min || 0 == $min) {
                $data['attributes']['min'] = $min;
                $data['attributes']['aria-valuemin'] = $min;
            }

            if ($max = ArrayHelper::get($data, 'settings.validation_rules.max.value')) {
                $data['attributes']['max'] = $max;
                $data['attributes']['aria-valuemax'] = $max;
            }

            if ($formatter = ArrayHelper::get($data, 'settings.numeric_formatter')) {
                $formatters = Helper::getNumericFormatters();
                if (! empty($formatters[$formatter]['settings'])) {
                    $data['attributes']['class'] .= ' ff_numeric';
                    $data['attributes']['data-formatter'] = json_encode($formatters[$formatter]['settings']);
                    wp_enqueue_script(
                        'currency',
                        fluentformMix('libs/currency.min.js'),
                        [],
                        '2.0.3',
                        true
                    );
                    $data['attributes']['type'] = 'text';
                }
            }
        }

        // For hidden input
        if ('hidden' == $data['attributes']['type']) {
            $attributes = $this->buildAttributes($data['attributes'], $form);
            echo '<input ' . $attributes . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $attributes is escaped before being passed in.
            return;
        }

        if ($tabIndex = Helper::getNextTabIndex()) {
            $data['attributes']['tabindex'] = $tabIndex;
        }

        $data['attributes']['class'] = @trim('ff-el-form-control ' . $data['attributes']['class']);
        $data['attributes']['id'] = $this->makeElementId($data, $form);

        $elMarkup = $this->buildInputGroup($data, $form);

        $html = $this->buildElementMarkup($elMarkup, $data, $form);
    
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

    private function buildInputGroup($data, $form)
    {
        $ariaRequired = 'false';
        if (ArrayHelper::get($data, 'settings.validation_rules.required.value')) {
            $ariaRequired = 'true';
        }

        $input = '<input ' . $this->buildAttributes($data['attributes'], $form) . ' aria-invalid="false" aria-required='.$ariaRequired.'>';
        $prefix = ArrayHelper::get($data, 'settings.prefix_label');
        $suffix = ArrayHelper::get($data, 'settings.suffix_label');
        if ($prefix || $suffix) {
            $wrapper = '<div class="ff_input-group">';
            if ($prefix) {
                $wrapper .= '<div class="ff_input-group-prepend"><span class="ff_input-group-text">' . fluentform_sanitize_html($prefix) . '</span></div>';
            }
            $wrapper .= $input;
            if ($suffix) {
                $wrapper .= '<div class="ff_input-group-append"><span class="ff_input-group-text">' . fluentform_sanitize_html($suffix) . '</span></div>';
            }
            $wrapper .= '</div>';
            return $wrapper;
        }
        return $input;
    }
}
