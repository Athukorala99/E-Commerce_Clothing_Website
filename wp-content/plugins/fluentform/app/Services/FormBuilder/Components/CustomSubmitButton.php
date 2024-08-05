<?php

namespace FluentForm\App\Services\FormBuilder\Components;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Services\FormBuilder\BaseFieldManager;
use FluentForm\Framework\Helpers\ArrayHelper;

class CustomSubmitButton extends BaseFieldManager
{
    public function __construct()
    {
        parent::__construct(
            'custom_submit_button',
            'Custom Submit Button',
            ['submit', 'button', 'custom'],
            'advanced'
        );
    }

    public function pushFormInputType($types)
    {
        return $types;
    }

    public function getComponent()
    {
        return [
            'index'      => 12,
            'element'    => $this->key,
            'attributes' => [
                'class' => '',
                'type'  => 'submit',
            ],
            'settings' => [
                'button_style'     => '',
                'button_size'      => 'md',
                'align'            => 'left',
                'container_class'  => '',
                'current_state'    => 'normal_styles',
                'background_color' => 'rgb(64, 158, 255)',
                'color'            => 'rgb(255, 255, 255)',
                'hover_styles'     => (object) [
                    'backgroundColor' => '#ffffff',
                    'borderColor'     => '#1a7efb',
                    'color'           => '#1a7efb',
                    'borderRadius'    => '',
                    'minWidth'        => '100%',
                ],
                'normal_styles' => (object) [
                    'backgroundColor' => '#1a7efb',
                    'borderColor'     => '#1a7efb',
                    'color'           => '#ffffff',
                    'borderRadius'    => '',
                    'minWidth'        => '100%',
                ],
                'button_ui' => (object) [
                    'text'    => 'Submit',
                    'type'    => 'default',
                    'img_url' => '',
                ],
                'conditional_logics' => [],
            ],
            'editor_options' => [
                'title'      => $this->title,
                'icon_class' => 'dashicons dashicons-arrow-right-alt',
                'template'   => 'customButton',
            ],
        ];
    }

    public function pushConditionalSupport($conditonalItems)
    {
        return $conditonalItems;
    }

    public function getGeneralEditorElements()
    {
        return [
            'btn_text',
            'button_ui',
            'button_style',
            'button_size',
            'align',
        ];
    }

    public function getAdvancedEditorElements()
    {
        return [
            'container_class',
            'class',
            'conditional_logics',
        ];
    }

    public function render($data, $form)
    {
        // @todo: We will remove this in our next version [added: 4.0.0]
        if (class_exists('\FluentFormPro\Components\CustomSubmitField')) {
            return '';
        }

        $hideSubmit = apply_filters_deprecated(
            'fluentform_is_hide_submit_btn_' . $form->id,
            [
                '__return_true'
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/is_hide_submit_btn_' . $form->id,
            'Use fluentform/is_hide_submit_btn_' . $form->id . ' instead of fluentform_is_hide_submit_btn_' . $form->id
        );

        add_filter('fluentform/is_hide_submit_btn_' . $form->id, $hideSubmit);

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

        $btnSize = 'ff-btn-';
        $btnSize .= isset($data['settings']['button_size']) ? $data['settings']['button_size'] : 'md';
        $oldBtnType = isset($data['settings']['button_style']) ? '' : ' ff-btn-primary ';

        $align = 'ff-el-group ff-text-' . @$data['settings']['align'];

        $btnClasses = [
            'ff-btn ff-btn-submit',
            $oldBtnType,
            $btnSize,
            $data['attributes']['class'],
        ];

        if ('no_style' == $btnStyle) {
            $btnClasses[] = 'ff_btn_no_style';
        } else {
            $btnClasses[] = 'ff_btn_style';
        }

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
            $styles .= 'form.fluent_form_' . $form->id . ' .ff-btn-submit { background-color: ' . esc_attr(ArrayHelper::get($data, 'settings.background_color')) . '; color: ' . esc_attr(ArrayHelper::get($data, 'settings.color')) . '; }';
        }

        $atts = $this->buildAttributes($data['attributes']);
        $hasConditions = $this->hasConditions($data) ? 'has-conditions ' : '';
        $cls = trim($align . ' ' . $data['settings']['container_class'] . ' ' . $hasConditions);

        $html = "<div class='" . esc_attr($cls) . " ff_submit_btn_wrapper ff_submit_btn_wrapper_custom'>";

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
            $html .= '<style>' . $styles . '</style>';
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
