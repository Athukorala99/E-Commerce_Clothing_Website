<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\Framework\Helpers\ArrayHelper;

class CustomHtml extends BaseComponent
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
        $cls = trim($this->getDefaultContainerClass() . ' ff-' . $elementName . ' ' . $hasConditions);
        if ($containerClass = ArrayHelper::get($data, 'settings.container_class')) {
            $cls .= ' ' . $containerClass;
        }
        $atts = $this->buildAttributes(
            ArrayHelper::except($data['attributes'], 'name')
        );
        $html = "<div class='" . esc_attr($cls) . "' {$atts}>" . fluentform_sanitize_html($data['settings']['html_codes']) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $atts is escaped before being passed in.
    
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
