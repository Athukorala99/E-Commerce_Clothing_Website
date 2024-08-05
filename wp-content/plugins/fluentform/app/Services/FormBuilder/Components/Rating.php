<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

class Rating extends BaseComponent
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

        $data['attributes']['type'] = 'radio';

        $defaultValues = (array) $this->extractValueFromAttributes($data);

        $elMarkup = "<div class='ff-el-ratings jss-ff-el-ratings'>";
        $ratingText = '';

        foreach ($data['options'] as $value => $label) {
            $starred = '';
            if (in_array($value, $defaultValues)) {
                $data['attributes']['checked'] = true;
                $starred = 'active';
            } else {
                $data['attributes']['checked'] = false;
            }

            if ($tabIndex = Helper::getNextTabIndex()) {
                $data['attributes']['tabindex'] = $tabIndex;
            }

            $atts = $this->buildAttributes($data['attributes']);
            $id = esc_attr($this->getUniqueid(str_replace(['[', ']'], ['', ''], $data['attributes']['name'])));

            $ariaRequired = 'false';
            if (ArrayHelper::get($data, 'settings.validation_rules.required.value')) {
                $ariaRequired = 'true';
            }

            $elMarkup .= "<label class='{$starred}'><input {$atts} id={$id} aria-valuenow='" . esc_attr($value) . "' value='" . esc_attr($value) . "' aria-required={$ariaRequired} aria-invalid='false'>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $starred, $atts, $id are escaped before being passed in.
            $elMarkup .= '<?xml version="1.0" encoding="iso-8859-1"?><svg class="jss-ff-svg ff-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 53.867 53.867" style="enable-background:new 0 0 53.867 53.867;" xml:space="preserve"><polygon points="26.934,1.318 35.256,18.182 53.867,20.887 40.4,34.013 43.579,52.549 26.934,43.798 10.288,52.549 13.467,34.013 0,20.887 18.611,18.182 "/><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>';
            $elMarkup .= '</label>';

            if ('yes' == ArrayHelper::get($data, 'settings.show_text')) {
                $displayDefaultText = in_array($value, $defaultValues) ? 'display: inline-block' : 'display: none';
                $ratingText .= "<span style='{$displayDefaultText}' class='ff-el-rating-text' data-id='{$id}'>" . fluentform_sanitize_html($label) . '</span>';
            }
        };

        $elMarkup .= '</div>' . $ratingText;

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
}
