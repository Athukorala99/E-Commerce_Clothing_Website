<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Services\FormBuilder\Components\Select;

class Name extends Select
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

        $rootName = $data['attributes']['name'];

        $hasConditions = $this->hasConditions($data) ? 'has-conditions ' : '';

        if (empty($data['attributes']['class'])) {
            $data['attributes']['class'] = '';
        }

        $data['attributes']['class'] .= $hasConditions;
        $data['attributes']['class'] .= ' ff-field_container ff-name-field-wrapper';
        if ($containerClass = ArrayHelper::get($data, 'settings.container_class')) {
            $data['attributes']['class'] .= ' ' . $containerClass;
        }
        $atts = $this->buildAttributes(
            ArrayHelper::except($data['attributes'], 'name')
        );

        $html = "<div {$atts}>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $atts is escaped before being passed in.
        $html .= "<div class='ff-t-container'>";

        $labelPlacement = ArrayHelper::get($data, 'settings.label_placement');
        $labelPlacementClass = '';

        if ($labelPlacement) {
            $labelPlacementClass = ' ff-el-form-' . $labelPlacement;
        }

        foreach ($data['fields'] as $field) {
            if ($field['settings']['visible']) {
                $fieldName = $field['attributes']['name'];
                $field['attributes']['name'] = $rootName . '[' . $fieldName . ']';
                @$field['attributes']['class'] = trim(
                    'ff-el-form-control ' .
                    $field['attributes']['class']
                );

                if ($tabIndex = Helper::getNextTabIndex()) {
                    $field['attributes']['tabindex'] = $tabIndex;
                }

                @$field['settings']['container_class'] .= $labelPlacementClass;

                $field['attributes']['id'] = $this->makeElementId($field, $form);
                $nameTitleClass = '';
                $atts = $this->buildAttributes($field['attributes']);

                $ariaRequired = 'false';
                if (ArrayHelper::get($data, 'settings.validation_rules.required.value')) {
                    $ariaRequired = 'true';
                }

                if ('select' == $field['attributes']['type']) {
                    if (! defined('FLUENTFORMPRO')) {
                        continue;
                    }
                    $nameTitleClass = ' ff-name-title';

                    $defaultValues = (array) $this->extractValueFromAttributes($field);

                    $options = $this->buildOptions($field, $defaultValues);

                    $elMarkup = '<select ' . $atts . '>' . $options . '</select>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $atts and $options are escaped before being passed in.
                } else {
                    $elMarkup = '<input ' . $atts . 'aria-invalid="false" aria-required='.$ariaRequired.'>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $atts is escaped before being passed in.
                }

                $inputTextMarkup = $this->buildElementMarkup($elMarkup, $field, $form);
                $html .= "<div class='ff-t-cell " . esc_attr($nameTitleClass) . "'>{$inputTextMarkup}</div>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $inputTextMarkup is escaped before being passed in.
            }
        }
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
