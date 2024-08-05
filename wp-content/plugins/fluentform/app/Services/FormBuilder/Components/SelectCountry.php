<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

class SelectCountry extends BaseComponent
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

        $data = $this->loadCountries($data);
        $defaultValues = (array) $this->extractValueFromAttributes($data);
        $data['attributes']['class'] = trim('ff-el-form-control ' . $data['attributes']['class']);
        $data['attributes']['id'] = $this->makeElementId($data, $form);
        $isSearchable = ArrayHelper::get($data, 'settings.enable_select_2');
        if ('yes' == $isSearchable) {
            wp_enqueue_script('choices');
            wp_enqueue_style('ff_choices');
            $data['attributes']['class'] .= ' ff_has_multi_select';
        }

        if ($tabIndex = Helper::getNextTabIndex()) {
            $data['attributes']['tabindex'] = $tabIndex;
        }

        $placeholder = ArrayHelper::get($data, 'attributes.placeholder');

        $activeList = ArrayHelper::get($data, 'settings.country_list.active_list');

        $ariaRequired = 'false';
        if (ArrayHelper::get($data, 'settings.validation_rules.required.value')) {
            $ariaRequired = 'true';
        }

        $elMarkup = '<select ' . $this->buildAttributes($data['attributes']) . "aria-invalid='false' aria-required=$ariaRequired><option value=''>" . wp_strip_all_tags($placeholder) . '</option>';

        if ('priority_based' == $activeList) {
            $selectCountries = ArrayHelper::get($data, 'settings.country_list.priority_based', []);
            $priorityCountries = $this->getSelectedCountries($selectCountries);
            $primaryListLabel = ArrayHelper::get($data, 'settings.primary_label');
            $otherListLabel = ArrayHelper::get($data, 'settings.other_label');
            $elMarkup .= '<optgroup label="' . wp_strip_all_tags($primaryListLabel) . '">';
            $elMarkup .= $this->buildOptions($priorityCountries, $defaultValues);
            $elMarkup .= '</optgroup><optgroup label="' . wp_strip_all_tags($otherListLabel) . '">';
            $elMarkup .= $this->buildOptions($data['options'], $defaultValues);
            $elMarkup .= '</optgroup>';
        } else {
            $elMarkup .= $this->buildOptions($data['options'], $defaultValues);
        }

        $elMarkup .= '</select>';

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
    /**
     * Load countt list from file
     *
     * @param array $data
     *
     * @return array
     */
    public function loadCountries($data)
    {
        $app = wpFluentForm();
        $data['options'] = [];
        $activeList = ArrayHelper::get($data, 'settings.country_list.active_list');
        $countries = getFluentFormCountryList();

        if ('visible_list' == $activeList) {
            $selectCountries = ArrayHelper::get($data, 'settings.country_list.' . $activeList, []);
            foreach ($selectCountries as $value) {
                $data['options'][$value] = $countries[$value];
            }
        } elseif ('hidden_list' == $activeList || 'priority_based' == $activeList) {
            $data['options'] = $countries;
            $selectCountries = ArrayHelper::get($data, 'settings.country_list.' . $activeList, []);
            foreach ($selectCountries as $value) {
                unset($data['options'][$value]);
            }
        } else {
            $data['options'] = $countries;
        }

        $selectedCountries = $data['options'];
        $selectedCountries = array_flip($selectedCountries);
        ksort($selectedCountries);
        $selectedCountries = array_flip($selectedCountries);
        $data['options'] = $selectedCountries;

        return $data;
    }

    /**
     * Build options for country list/select
     *
     * @param array $options
     *
     * @return string/html [compiled options]
     */
    protected function buildOptions($options, $defaultValues = [])
    {
        $opts = '';
        foreach ($options as $value => $label) {
            if (in_array($value, $defaultValues)) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $opts .= "<option value='" . esc_attr($value) . "' {$selected}>" . esc_attr($label) . '</option>';
        }
        return $opts;
    }

    public function getSelectedCountries($keys = [])
    {
        $options = [];
        $countries = getFluentFormCountryList();
        foreach ($keys as $value) {
            $options[$value] = $countries[$value];
        }

        $options = array_flip($options);
        ksort($options);
        return array_flip($options);
    }
}
