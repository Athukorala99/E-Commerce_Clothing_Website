<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

class TermsAndConditions extends BaseComponent
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

        $hasConditions = $this->hasConditions($data) ? 'has-conditions ' : '';

        $cls = trim(
            $this->getDefaultContainerClass()
            . ' ' . @$data['settings']['container_class']
            . ' ' . $hasConditions
            . ' ff-el-input--content'
        );

        $uniqueId = $this->getUniqueId($data['attributes']['name']);

        $data['attributes']['id'] = $uniqueId;
        $data['attributes']['class'] = trim(
            'ff-el-form-check-input ' .
            $data['attributes']['class']
        );

        if ($tabIndex = Helper::getNextTabIndex()) {
            $data['attributes']['tabindex'] = $tabIndex;
        }

        $atts = $this->buildAttributes($data['attributes']);
        $checkbox = '';

        $ariaRequired = 'false';
        if (ArrayHelper::get($data, 'settings.validation_rules.required.value')) {
            $ariaRequired = 'true';
        }

        if ($data['settings']['has_checkbox']) {
            $checkbox = "<span class='ff_tc_checkbox'><input {$atts} value='on' aria-invalid='false' aria-required={$ariaRequired}></span>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $atts is escaped before being passed in.
        }

        $html = "<div class='" . esc_attr($cls) . "'>";
        $html .= "<div class='ff-el-form-check ff-el-tc'>";
        $html .= "<label aria-label='terms & conditions' class='ff-el-form-check-label ff_tc_label' for={$uniqueId}>";
        $html .= "{$checkbox} <div class='ff_t_c'>" . fluentform_sanitize_html($data['settings']['tnc_html']) . '</div>';
        $html .= '</label>';
        $html .= '</div>';
        $html .= '</div>';
    
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
