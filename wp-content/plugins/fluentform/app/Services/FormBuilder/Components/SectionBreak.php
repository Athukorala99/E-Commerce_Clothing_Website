<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\Framework\Helpers\ArrayHelper;

class SectionBreak extends BaseComponent
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
            'fluentform/rendering_field_data_' . $elementName,
            [
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/rendering_field_data_' . $elementName,
            'Use fluentform/rendering_field_data_' . $elementName . ' instead of fluentform_rendering_field_data_' . $elementName
        );
        $data = apply_filters('fluentform/rendering_field_data_' . $elementName, $data, $form);

        $alignment = ArrayHelper::get($data, 'settings.align');
        if ($alignment) {
            $data['attributes']['class'] .= ' ff_' . $alignment;
        }

        $hasConditions = $this->hasConditions($data) ? 'has-conditions ' : '';
        $cls = trim($this->getDefaultContainerClass() . ' ' . $hasConditions);
        $data['attributes']['class'] = $cls . ' ff-el-section-break ' . $data['attributes']['class'];
        $data['attributes']['class'] = trim($data['attributes']['class']);
        $atts = $this->buildAttributes(
            ArrayHelper::except($data['attributes'], 'name')
        );
        $html = "<div {$atts}>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $atts is escaped before being passed in.
        $html .= "<h3 class='ff-el-section-title'>" . fluentform_sanitize_html($data['settings']['label']) . '</h3>';
        $html .= "<div class='ff-section_break_desk'>" . fluentform_sanitize_html($data['settings']['description']) . '</div>';
        $html .= '<hr />';
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
