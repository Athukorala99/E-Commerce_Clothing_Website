<?php

namespace FluentForm\App\Services\FluentConversational\Classes\Elements;

use FluentForm\App\Services\FormBuilder\BaseFieldManager;
use FluentForm\Framework\Helpers\ArrayHelper;

class WelcomeScreen extends BaseFieldManager
{
    public function __construct()
    {
        parent::__construct(
            'welcome_screen',
            'Welcome Screen',
            ['welcome', 'screen', 'content'],
            'general'
        );

        add_filter('fluentform/conversational_editor_elements', [$this, 'pushConversationalComponent'], 10, 1);
    }

    public function getComponent()
    {
        return []; // we don't this in normal forms
    }

    public function pushConversationalComponent($components)
    {
        $components['advanced'][] = [
            'index'      => 50,
            'element'    => 'welcome_screen',
            'attributes' => [],
            'settings'   => [
                'label'              => __('Welcome Heading', 'fluentform'),
                'description'        => __('Sub Heading', 'fluentform'),
                'align'              => 'center',
                'conditional_logics' => [],
                'button_style'       => 'default',
                'button_size'        => 'md',
                'container_class'    => '',
                'current_state'      => 'normal_styles',
                'background_color'   => 'rgb(64, 158, 255)',
                'color'              => 'rgb(255, 255, 255)',
                'hover_styles'       => (object) [
                    'backgroundColor' => '#ffffff',
                    'borderColor'     => '#1a7efb',
                    'color'           => '#1a7efb',
                    'borderRadius'    => '',
                    'minWidth'        => '',
                ],
                'normal_styles' => (object) [
                    'backgroundColor' => '#1a7efb',
                    'borderColor'     => '#1a7efb',
                    'color'           => '#ffffff',
                    'borderRadius'    => '',
                    'minWidth'        => '',
                ],
                'button_ui' => (object) [
                    'text' => 'Start Here',
                    'type' => 'default',
                ],
            ],
            'editor_options' => [
                'title'      => __('Welcome Screen', 'fluentform'),
                'icon_class' => 'dashicons dashicons-align-wide',
                'template'   => 'welcomeScreen',
            ],
            'style_pref' => [
                'layout'           => 'default',
                'media'            => '',
                'brightness'       => 0,
                'alt_text'         => '',
                'media_x_position' => 50,
                'media_y_position' => 50,
            ],
        ];

        return $components;
    }

    public function getGeneralEditorElements()
    {
        return [
            'label',
            'description',
            'align',
            'btn_text',
            'button_ui',
        ];
    }

    public function getAdvancedEditorElements()
    {
        return [
            'button_style',
            'button_size',
        ];
    }

    public function pushFormInputType($types)
    {
        return $types;
    }

    public function render($data, $form)
    {
        $elementName = $data['element'];

        $app = wpFluentForm();
    
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

        $data = $app->applyFilters('fluentform/rendering_field_data_' . $elementName, $data, $form);

        $alignment = ArrayHelper::get($data, 'settings.align');
        if ($alignment) {
            if (empty($data['attributes']['class'])) {
                $data['attributes']['class'] = '';
            }
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
        $html .= '</div>';

        $this->printContent('fluentform/rendering_field_html_' . $elementName, $html, $data, $form);
    }
}
