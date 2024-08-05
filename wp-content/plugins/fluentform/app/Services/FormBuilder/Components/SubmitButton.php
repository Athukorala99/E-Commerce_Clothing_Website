<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

class SubmitButton extends BaseComponent
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
        $maybeHide = apply_filters_deprecated(
            'fluentform_is_hide_submit_btn_' . $form->id,
            [
                false
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/is_hide_submit_btn_' . $form->id,
            'Use fluentform/is_hide_submit_btn_' . $form->id. ' instead of fluentform_is_hide_submit_btn_' . $form->id
        );
        if (apply_filters('fluentform/is_hide_submit_btn_' . $form->id, $maybeHide)) {
            return '';
        }

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

        $btnStyle = ArrayHelper::get($data['settings'], 'button_style');
        
        /* This filter is deprecated and will be removed soon */
        $noStyle = apply_filters('fluentform_submit_button_force_no_style', false);
        
        if (apply_filters('fluentform/submit_button_force_no_style', $noStyle)) {
            $btnStyle = 'no_style';
        }
        
        $btnSize = 'ff-btn-';
        $btnSize .= isset($data['settings']['button_size']) ? $data['settings']['button_size'] : 'md';
        $oldBtnType = isset($data['settings']['button_style']) ? '' : ' ff-btn-primary ';

        $btnClasses = [
            'ff-btn ff-btn-submit',
            $oldBtnType,
            $btnSize,
            $data['attributes']['class'],
        ];
        
        $loadDefaultFluentStyle = $form->theme != 'ffs_inherit_theme';
        if(!$loadDefaultFluentStyle){
            $btnStyle = 'no_style';
        }
        if ('no_style' == $btnStyle) {
            $btnClasses[] = 'ff_btn_no_style';
        } else {
            $btnClasses[] = 'ff_btn_style';
        }

        $align = 'ff-el-group ff-text-' . @$data['settings']['align'];
        $data['attributes']['class'] = trim(implode(' ', array_filter($btnClasses)));

        if ($tabIndex = Helper::getNextTabIndex()) {
            $data['attributes']['tabindex'] = $tabIndex;
        }

        $styles = '';
        if ('' == ArrayHelper::get($data, 'settings.button_style')) {
            $data['attributes']['class'] .= ' wpf_has_custom_css';
            // it's a custom button
            $buttonActiveStyles = ArrayHelper::get($data, 'settings.normal_styles', []);
            $buttonHoverStyles = ArrayHelper::get($data, 'settings.hover_styles', []);

            $activeStates = '';
            foreach ($buttonActiveStyles as $styleAtr => $styleValue) {
                if (! $styleValue) {
                    continue;
                }
                if ('borderRadius' == $styleAtr) {
                    $styleValue .= 'px';
                }
                $activeStates .= ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '-$0', $styleAtr)), '_') . ':' . $styleValue . ';';
            }
            if ($activeStates) {
                $styles .= 'form.fluent_form_' . $form->id . ' .wpf_has_custom_css.ff-btn-submit { ' . $activeStates . ' }';
            }
            $hoverStates = '';
            foreach ($buttonHoverStyles as $styleAtr => $styleValue) {
                if (! $styleValue) {
                    continue;
                }
                if ('borderRadius' == $styleAtr) {
                    $styleValue .= 'px';
                }
                $hoverStates .= ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '-$0', $styleAtr)), '-') . ':' . $styleValue . ';';
            }
            if ($hoverStates) {
                $styles .= 'form.fluent_form_' . $form->id . ' .wpf_has_custom_css.ff-btn-submit:hover { ' . $hoverStates . ' } ';
            }
        } elseif ('no_style' != $btnStyle) {

            $styles .= 'form.fluent_form_' . $form->id . ' .ff-btn-submit:not(.ff_btn_no_style) { background-color: ' . esc_attr(ArrayHelper::get($data, 'settings.background_color')) . '; color: ' . esc_attr(ArrayHelper::get($data, 'settings.color')) . '; }';
        }

        $atts = $this->buildAttributes($data['attributes']);
        $cls = trim($align . ' ' . $data['settings']['container_class']);

        $html = "<div class='" . esc_attr($cls) . " ff_submit_btn_wrapper'>";

        // ADDED IN v1.2.6 - updated in 1.4.4
        if (isset($data['settings']['button_ui'])) {
            if ('default' == $data['settings']['button_ui']['type']) {
                $html .= '<button ' . $atts . '>' . fluentform_sanitize_html($data['settings']['button_ui']['text']) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $atts is escaped before being passed in.
            } else {
                $html .= "<button class='ff-btn-submit' type='submit'><img style='max-width: 200px;' src='" . esc_url($data['settings']['button_ui']['img_url']) . "' alt='Submit Form'></button>";
            }
        } else {
            $html .= '<button ' . $atts . '>' . fluentform_sanitize_html($data['settings']['btn_text']) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $atts is escaped before being passed in.
        }

        if ($styles) {
            if (did_action('wp_footer') || Helper::isBlockEditor()) {
                $html .= '<style>' . $styles . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $styles is escaped before being passed in.
            } else {
                add_action('wp_footer', function () use ($styles) {
                    echo '<style>' . $styles . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $styles is escaped before being passed in.
                });
            }
        }

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
