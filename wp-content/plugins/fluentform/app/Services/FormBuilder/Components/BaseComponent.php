<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Component\Component;
use FluentForm\Framework\Support\Helper;

class BaseComponent
{
    public $app;

    public function __construct($key = '', $title = '', $tags = [], $position = 'advanced')
    {
        $this->app = wpFluentForm();
    }

    /**
     * Build unique ID concatenating form id and name attribute
     *
     * @param array $data $form
     *
     * @return string for id value
     */
    protected function makeElementId($data, $form)
    {
        if (isset($data['attributes']['name'])) {
            $formInstance = \FluentForm\App\Helpers\Helper::$formInstance;
            if (!empty($data['attributes']['id'])) {
                return $data['attributes']['id'];
            }
            $elementName = $data['attributes']['name'];
            $elementName = str_replace(['[', ']', ' '], '_', $elementName);

            $suffix = esc_attr($form->id);
            if($formInstance > 1) {
                $suffix = $suffix.'_'.$formInstance;
            }

            $suffix .= '_'.$elementName;

            return 'ff_' . esc_attr($suffix);
        }
    }

    /**
     * Build attributes for any html element
     *
     * @param array $attributes
     *
     * @return string [Compiled key='value' attributes]
     */
    protected function buildAttributes($attributes, $form = null)
    {
        $atts = '';

        foreach ($attributes as $key => $value) {
            if ($value || 0 === $value || '0' === $value) {
                $value = htmlspecialchars($value);
                $atts .= esc_attr($key) . '="' . $value . '" ';
            }
        }

        return $atts;
    }

    /**
     * Extract value attribute from attribute list
     *
     * @param array &$element
     *
     * @return string
     */
    protected function extractValueFromAttributes(&$element)
    {
        $value = '';

        if (isset($element['attributes']['value'])) {
            $value = $element['attributes']['value'];
            unset($element['attributes']['value']);
        }

        return $value;
    }

    protected function extractDynamicValues($data, $form)
    {
        $defaultValues = [];
        if ($dynamicDefaultValue = ArrayHelper::get($data, 'settings.dynamic_default_value')) {
            $parseValue = $this->parseEditorSmartCode($dynamicDefaultValue, $form);
            if ($parseValue) {
                $defaultValues = explode(',', $parseValue);
                $defaultValues = array_map('trim', $defaultValues);
            }
        }
        return $defaultValues;
    }

    /**
     * Determine if the given element has conditions bound
     *
     * @param array $element [Html element being compiled]
     *
     * @return boolean
     */
    protected function hasConditions($element)
    {
        $conditionals = ArrayHelper::get($element, 'settings.conditional_logics');

        if (isset($conditionals['status']) && $conditionals['status']) {
            return array_filter($conditionals['conditions'], function ($item) {
                return $item['field'] && $item['operator'];
            });
        }
    }

    /**
     * Generate a unique id for an element
     *
     * @param string $str [preix]
     *
     * @return string [Unique id]
     */
    protected function getUniqueId($str)
    {
        return $str . '_' . md5(uniqid(mt_rand(), true));
    }

    /**
     * Get a default class for each form element wrapper
     *
     * @return string
     */
    protected function getDefaultContainerClass()
    {
        return 'ff-el-group ';
    }

    /**
     * Get required class for form element wrapper
     *
     * @param array $rules [Validation rules]
     *
     * @return mixed
     */
    protected function getRequiredClass($rules)
    {
        if (isset($rules['required'])) {
            return $rules['required']['value'] ? 'ff-el-is-required ' : '';
        }
    }

    /**
     * Get asterisk placement for the required form elements
     *
     * @return string
     */
    protected function getAsteriskPlacement($form)
    {
        // for older version compatibility
        $asteriskPlacement = 'asterisk-right';

        if (isset($form->settings['layout']['asteriskPlacement'])) {
            $asteriskPlacement = $form->settings['layout']['asteriskPlacement'];
        }

        return $asteriskPlacement . ' ';
    }

    /**
     * Generate a label for any element
     *
     * @param array $data
     *
     * @return string [label Html element]
     */
    protected function buildElementLabel($data, $form)
    {
        $helpMessage = '';
        if ('with_label' == $form->settings['layout']['helpMessagePlacement']) {
            $helpMessage = $this->getLabelHelpMessage($data);
        }

        $id = isset($data['attributes']['id']) ? $data['attributes']['id'] : '';
        $label = isset($data['settings']['label']) ? $data['settings']['label'] : '';
        $requiredClass = $this->getRequiredClass(ArrayHelper::get($data, 'settings.validation_rules', []));
        $classes = trim('ff-el-input--label ' . $requiredClass . $this->getAsteriskPlacement($form));

        return "<div class='" . esc_attr($classes) . "'><label aria-label='" . esc_attr($this->removeShortcode($label)) . "' for='" . esc_attr($id) . "'>" . fluentform_sanitize_html($label) . '</label>' . $helpMessage . '</div>';
    }

    /**
     * Generate html/markup for any element
     *
     * @param string    $elMarkup [Predifined partial markup]
     * @param array     $data
     * @param \stdClass $form     [Form object]
     *
     * @return string [Compiled markup]
     */
    protected function buildElementMarkup($elMarkup, $data, $form)
    {
        $hasConditions = $this->hasConditions($data) ? 'has-conditions ' : '';

        $labelPlacement = ArrayHelper::get($data, 'settings.label_placement');

        $labelPlacementClass = $labelPlacement ? 'ff-el-form-' . $labelPlacement . ' ' : '';

        $validationRules = ArrayHelper::get($data, 'settings.validation_rules');

        $requiredClass = $this->getRequiredClass($validationRules);

        $labelClass = trim(
            'ff-el-input--label ' .
            $requiredClass .
            $this->getAsteriskPlacement($form)
        );

        $formGroupClass = trim(
            $this->getDefaultContainerClass() .
            $labelPlacementClass .
            $hasConditions .
            ArrayHelper::get($data, 'settings.container_class')
        );

        $labelHelpText = $inputHelpText = '';

        $labelPlacement = $form->settings['layout']['helpMessagePlacement'];
        if ('with_label' == $labelPlacement) {
            $labelHelpText = $this->getLabelHelpMessage($data);
        } elseif ('on_focus' == $labelPlacement) {
            $inputHelpText = $this->getInputHelpMessage($data, 'ff-hidden');
        } elseif ('after_label' == $labelPlacement) {
            $inputHelpText = $this->getInputHelpMessage($data, 'ff_ahm');
        } else {
            $inputHelpText = $this->getInputHelpMessage($data);
        }

        $forStr = '';
        if (isset($data['attributes']['id'])) {
            $forStr = "for='" . esc_attr($data['attributes']['id']) . "'";
        }

        $labelMarkup = '';
    
        if (!empty($data['settings']['label'])) {
            $label = ArrayHelper::get($data, 'settings.label');
            $ariaLabel = $label;
            
            $hasShortCodeIndex = strpos($label, '{dynamic.');
            if ($hasShortCodeIndex !== false) {
                $ariaLabel = trim(substr($label, 0, $hasShortCodeIndex));
            }
    
            $labelMarkup = sprintf(
                '<div class="%1$s"><label %2$s aria-label="%3$s">%4$s</label>%5$s</div>',
                esc_attr($labelClass),
                $forStr,
                esc_attr($this->removeShortcode($label)),
                fluentform_sanitize_html($label),
                fluentform_sanitize_html($labelHelpText)
            );
        }

        $inputHelpText = fluentform_sanitize_html($inputHelpText);

        if ('after_label' == $labelPlacement) {
            $elMarkup = $inputHelpText . $elMarkup;
            $inputHelpText = '';
        }

        return sprintf(
            "<div class='%s'>%s<div class='ff-el-input--content'>%s%s</div></div>",
            esc_attr($formGroupClass),
            $labelMarkup,
            $elMarkup,
            $inputHelpText
        );
    }

    /**
     * Generate a help message for any element beside label
     *
     * @param array $data
     *
     * @return string [Html]
     */
    protected function getLabelHelpMessage($data)
    {
        if (isset($data['settings']['help_message']) && '' != $data['settings']['help_message']) {
            $text = htmlspecialchars($data['settings']['help_message']);
            $icon = '<svg width="16" height="16" viewBox="0 0 25 25"><path d="m329 393l0-46c0-2-1-4-2-6-2-2-4-3-7-3l-27 0 0-146c0-3-1-5-3-7-2-1-4-2-7-2l-91 0c-3 0-5 1-7 2-1 2-2 4-2 7l0 46c0 2 1 5 2 6 2 2 4 3 7 3l27 0 0 91-27 0c-3 0-5 1-7 3-1 2-2 4-2 6l0 46c0 3 1 5 2 7 2 1 4 2 7 2l128 0c3 0 5-1 7-2 1-2 2-4 2-7z m-36-256l0-46c0-2-1-4-3-6-2-2-4-3-7-3l-54 0c-3 0-5 1-7 3-2 2-3 4-3 6l0 46c0 3 1 5 3 7 2 1 4 2 7 2l54 0c3 0 5-1 7-2 2-2 3-4 3-7z m182 119c0 40-9 77-29 110-20 34-46 60-80 80-33 20-70 29-110 29-40 0-77-9-110-29-34-20-60-46-80-80-20-33-29-70-29-110 0-40 9-77 29-110 20-34 46-60 80-80 33-20 70-29 110-29 40 0 77 9 110 29 34 20 60 46 80 80 20 33 29 70 29 110z" transform="scale(0.046875 0.046875)"></path></svg>';
            return sprintf('<div class="ff-el-tooltip" data-content="%s">%s</div>', $text, $icon);
        }
    }

    /**
     * Generate a help message for any element beside form element
     *
     * @param array $data
     *
     * @return string [Html]
     */
    protected function getInputHelpMessage($data, $hideClass = '')
    {
        $class = trim('ff-el-help-message ' . $hideClass);

        if (isset($data['settings']['help_message']) && ! empty($data['settings']['help_message'])) {
            return "<div class='" . esc_attr($class) . "'>" . fluentform_sanitize_html($data['settings']['help_message']) . '</div>';
        }
        return false;
    }

    protected function parseEditorSmartCode($text, $form)
    {
        return (new Component($this->app))->replaceEditorSmartCodes($text, $form);
    }

    protected function printContent($hook, $html, $data, $form)
    {
        echo apply_filters($hook, $html, $data, $form); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $html is escaped before being passed in.
    }

    /**
     * A helper method for remove shortcode from aria label
     *
     * @param string $label
     *
     * @return string $label
     */
    protected function removeShortcode($label)
    {
        // Find all occurrences of text enclosed in curly braces.
        preg_match_all('/{(.*?)}/', $label, $matches);
    
        // If there are matches, remove them from the label.
        if ($matches[0]) {
            $label = trim(str_replace($matches[0], '', $label));
        }
        return $label;
    }
}
