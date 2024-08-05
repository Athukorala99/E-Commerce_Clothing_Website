<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

class DateTime extends BaseComponent
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

        wp_enqueue_script('flatpickr');
        wp_enqueue_style('flatpickr');

        $data['attributes']['class'] = trim(
            'ff-el-form-control ff-el-datepicker ' . $data['attributes']['class']
        );
        $dateFormat = $data['settings']['date_format'];

        $data['attributes']['id'] = $this->makeElementId($data, $form);

        if ($tabIndex = Helper::getNextTabIndex()) {
            $data['attributes']['tabindex'] = $tabIndex;
        }

        $atts = $this->buildAttributes($data['attributes']);

        $ariaRequired = 'false';
        if (ArrayHelper::get($data, 'settings.validation_rules.required.value')) {
            $ariaRequired = 'true';
        }

        $elMarkup = "<input data-type-datepicker data-format='" . esc_attr($dateFormat) . "' " . $atts . " aria-invalid='false' aria-required={$ariaRequired}>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $atts is escaped before being passed in.

        $config = $this->getDateFormatConfigJSON($data['settings'], $form);
        $customConfig = $this->getCustomConfig($data['settings']);
        $this->loadToFooter($config, $customConfig, $form, $data['attributes']['id']);

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

    public function getAvailableDateFormats()
    {
        $dateFormats = apply_filters('fluentform/available_date_formats', [
            'm/d/Y'       => 'm/d/Y - (Ex: 04/28/2018)', // USA
            'd/m/Y'       => 'd/m/Y - (Ex: 28/04/2018)', // Canada, UK
            'd.m.Y'       => 'd.m.Y - (Ex: 28.04.2019)', // Germany
            'n/j/y'       => 'n/j/y - (Ex: 4/28/18)',
            'm/d/y'       => 'm/d/y - (Ex: 04/28/18)',
            'M/d/Y'       => 'M/d/Y - (Ex: Apr/28/2018)',
            'y/m/d'       => 'y/m/d - (Ex: 18/04/28)',
            'Y-m-d'       => 'Y-m-d - (Ex: 2018-04-28)',
            'd-M-y'       => 'd-M-y - (Ex: 28-Apr-18)',
            'm/d/Y h:i K' => 'm/d/Y h:i K - (Ex: 04/28/2018 08:55 PM)', // USA
            'm/d/Y H:i'   => 'm/d/Y H:i - (Ex: 04/28/2018 20:55)', // USA
            'd/m/Y h:i K' => 'd/m/Y h:i K - (Ex: 28/04/2018 08:55 PM)', // Canada, UK
            'd/m/Y H:i'   => 'd/m/Y H:i - (Ex: 28/04/2018 20:55)', // Canada, UK
            'd.m.Y h:i K' => 'd.m.Y h:i K - (Ex: 28.04.2019 08:55 PM)', // Germany
            'd.m.Y H:i'   => 'd.m.Y H:i - (Ex: 28.04.2019 20:55)', // Germany
            'h:i K'       => 'h:i K (Only Time Ex: 08:55 PM)',
            'H:i'         => 'H:i (Only Time Ex: 20:55)',
        ]);

        $formatted = [];
        foreach ($dateFormats as $format => $label) {
            $formatted[] = [
                'label' => $label,
                'value' => $format,
            ];
        }
        return $formatted;
    }

    public function getDateFormatConfigJSON($settings, $form)
    {
        $dateFormat = ArrayHelper::get($settings, 'date_format');

        if (! $dateFormat) {
            $dateFormat = 'm/d/Y';
        }

        $hasTime = $this->hasTime($dateFormat);
        $time24 = false;

        if ($hasTime && false !== strpos($dateFormat, 'H')) {
            $time24 = true;
        }

        $config = apply_filters('fluentform/frontend_date_format', [
            'dateFormat'    => $dateFormat,
            'enableTime'    => $hasTime,
            'noCalendar'    => ! $this->hasDate($dateFormat),
            'disableMobile' => true,
            'time_24hr'     => $time24,
        ], $settings, $form);

        return json_encode($config, JSON_FORCE_OBJECT);
    }

    public function getCustomConfig($settings)
    {
        $customConfigObject = trim(ArrayHelper::get($settings, 'date_config'));

        if (! $customConfigObject || '{' != substr($customConfigObject, 0, 1) || '}' != substr($customConfigObject, -1)) {
            $customConfigObject = '{}';
        }

        return $customConfigObject;
    }

    private function loadToFooter($config, $customConfigObject, $form, $id)
    {
        add_action('wp_footer', function () use ($config, $customConfigObject, $id, $form) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    function initPicker() {
                        if (typeof flatpickr == 'undefined') {
                            return;
                        }
                        flatpickr.localize(window.fluentFormVars.date_i18n);
                        var config = <?php echo fluentform_kses_js($config); ?> ;
                        try {
                            var customConfig = <?php echo fluentform_kses_js($customConfigObject); ?>;
                        } catch (e) {
                            var customConfig = {};
                        }

                        var config = $.extend({}, config, customConfig);
                        if (!config.locale) {
                            config.locale = 'default';
                        }

                        if (jQuery('#<?php echo esc_attr($id); ?>').length) {
                            flatpickr('#<?php echo esc_attr($id); ?>', config);
                        }
                    }
                    initPicker();
                    $(document).on(
                        'reInitExtras',
                        '.<?php echo esc_attr($form->instance_css_class); ?>',
                        function() {
                            initPicker();
                        }
                    );
                });
            </script>
            <?php
        }, 99999);
    }

    private function hasTime($string)
    {
        $timeStrings = ['H', 'h', 'G', 'i', 'S', 's', 'K'];
        foreach ($timeStrings as $timeString) {
            if (false != strpos($string, $timeString)) {
                return true;
            }
        }
        return false;
    }

    private function hasDate($string)
    {
        $dateStrings = ['d', 'D', 'l', 'j', 'J', 'w', 'W', 'F', 'm', 'n', 'M', 'U', 'Y', 'y', 'Z'];
        foreach ($dateStrings as $dateString) {
            if (false != strpos($string, $dateString)) {
                return 'true';
            }
        }
        return false;
    }
}
