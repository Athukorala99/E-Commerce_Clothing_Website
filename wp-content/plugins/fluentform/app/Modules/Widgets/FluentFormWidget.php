<?php

namespace FluentForm\App\Modules\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Group_Control_Background;
use Elementor\Core\Schemes\Color as Scheme_Color;
use FluentForm\App\Helpers\Helper;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class FluentFormWidget extends Widget_Base
{
    public function get_name()
    {
        return 'fluent-form-widget';
    }

    public function get_title()
    {
        return __('Fluent Forms', 'fluentform');
    }

    public function get_icon()
    {
        return 'eicon-form-horizontal';
    }

    public function get_keywords()
    {
        return [
            'fluentform',
            'fluentforms',
            'fluent form',
            'fluent forms',
            'contact form',
            'form',
            'elementor form',
        ];
    }

    public function get_categories()
    {
        return ['general'];
    }

    public function get_style_depends()
    {
        return [
            'fluent-form-styles',
            'fluentform-public-default',
        ];
    }

    public function get_script_depends()
    {
        return ['fluentform-elementor'];
    }

    protected function register_controls()
    {
        $this->register_general_controls();
        $this->register_error_controls();
        $this->register_title_description_style_controls();
        $this->register_form_container_style_controls();
        $this->register_label_style_controls();
        $this->register_input_textarea_style_controls();
        $this->register_placeholder_style_controls();
        $this->register_radio_checkbox_style_controls();
        $this->register_terms_gdpr_style_controls();
        $this->register_section_break_style_controls();
        $this->register_checkbox_grid_style_controls();
        $this->register_address_line_style_controls();
        $this->register_image_upload_style_controls();
        $this->register_pagination_style_controls();
        $this->register_submit_button_style_controls();
        $this->register_success_message_style_controls();
        $this->register_errors_style_controls();
    }

    protected function register_general_controls()
    {
        $this->start_controls_section(
            'section_fluent_form',
            [
                'label' => __('Fluent Forms', 'fluentform'),
            ]
        );

        $this->add_control(
            'form_list',
            [
                'label'       => esc_html__('Fluent Forms', 'fluentform'),
                'type'        => Controls_Manager::SELECT,
                'label_block' => true,
                'options'     => Helper::getForms(),
                'default'     => '0',
            ]
        );

        $this->add_control(
            'custom_title_description',
            [
                'label'        => __('Custom Title & Description', 'fluentform'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'fluentform'),
                'label_off'    => __('No', 'fluentform'),
                'return_value' => 'yes',
            ]
        );

        $this->add_control(
            'form_title_custom',
            [
                'label'       => esc_html__('Title', 'fluentform'),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '',
                'condition'   => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_description_custom',
            [
                'label'     => esc_html__('Description', 'fluentform'),
                'type'      => Controls_Manager::TEXTAREA,
                'default'   => '',
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'labels_switch',
            [
                'label'        => __('Labels', 'fluentform'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'label_on'     => __('Show', 'fluentform'),
                'label_off'    => __('Hide', 'fluentform'),
                'return_value' => 'yes',
            ]
        );

        $this->add_control(
            'placeholder_switch',
            [
                'label'        => __('Placeholder', 'fluentform'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'label_on'     => __('Show', 'fluentform'),
                'label_off'    => __('Hide', 'fluentform'),
                'return_value' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_error_controls()
    {
        $this->start_controls_section(
            'section_errors',
            [
                'label' => __('Errors', 'fluentform'),
            ]
        );

        $this->add_control(
            'error_messages',
            [
                'label'        => __('Error Messages', 'fluentform'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'label_on'     => __('Show', 'fluentform'),
                'label_off'    => __('Hide', 'fluentform'),
                'return_value' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_title_description_style_controls()
    {
        $this->start_controls_section(
            'section_form_title_style',
            [
                'label'     => __('Title & Description', 'fluentform'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'heading_alignment',
            [
                'label'   => __('Alignment', 'fluentform'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'fluentform'),
                        'icon'  => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'fluentform'),
                        'icon'  => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'fluentform'),
                        'icon'  => 'fa fa-align-right',
                    ],
                ],
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-title'       => 'text-align: {{VALUE}};',
                    '{{WRAPPER}} .fluentform-widget-description' => 'text-align: {{VALUE}};',
                ],
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'heading_title',
            [
                'label'     => __('Title', 'fluentform'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_title_text_color',
            [
                'label'     => __('Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-title' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_title_typography',
                'label'     => __('Typography', 'fluentform'),
                'selector'  => '{{WRAPPER}} .fluentform-widget-title',
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_title_margin',
            [
                'label'              => __('Margin', 'fluentform'),
                'type'               => Controls_Manager::DIMENSIONS,
                'size_units'         => ['px', 'em', '%'],
                'allowed_dimensions' => 'vertical',
                'placeholder'        => [
                    'top'    => '',
                    'right'  => 'auto',
                    'bottom' => '',
                    'left'   => 'auto',
                ],
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_title_padding',
            [
                'label'      => esc_html__('Padding', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_description',
            [
                'label'     => __('Description', 'fluentform'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'heading_description_text_color',
            [
                'label'     => __('Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-description' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'heading_description_typography',
                'label'     => __('Typography', 'fluentform'),
                'scheme'    => Scheme_Typography::TYPOGRAPHY_4,
                'selector'  => '{{WRAPPER}} .fluentform-widget-description',
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'heading_description_margin',
            [
                'label'              => __('Margin', 'fluentform'),
                'type'               => Controls_Manager::DIMENSIONS,
                'size_units'         => ['px', 'em', '%'],
                'allowed_dimensions' => 'vertical',
                'placeholder'        => [
                    'top'    => '',
                    'right'  => 'auto',
                    'bottom' => '',
                    'left'   => 'auto',
                ],
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'custom_title_description' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'heading_description_padding',
            [
                'label'      => esc_html__('Padding', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-description' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_form_container_style_controls()
    {
        $this->start_controls_section(
            'section_form_container_style',
            [
                'label' => __('Form Container', 'fluentform'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'form_container_background',
                'label'    => __('Background', 'fluentform'),
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper',
            ]
        );

        $this->add_control(
            'form_container_link_color',
            [
                'label'     => __('Link Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_container_max_width',
            [
                'label'      => esc_html__('Max Width', 'fluentform'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range'      => [
                    'px' => [
                        'min' => 10,
                        'max' => 1500,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 80,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_container_alignment',
            [
                'label'       => esc_html__('Alignment', 'fluentform'),
                'type'        => Controls_Manager::CHOOSE,
                'label_block' => true,
                'options'     => [
                    'default' => [
                        'title' => __('Default', 'fluentform'),
                        'icon'  => 'fa fa-ban',
                    ],
                    'left' => [
                        'title' => esc_html__('Left', 'fluentform'),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'fluentform'),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'fluentform'),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'default',
            ]
        );

        $this->add_responsive_control(
            'form_container_margin',
            [
                'label'      => esc_html__('Margin', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_container_padding',
            [
                'label'      => esc_html__('Padding', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'form_container_border',
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper',
            ]
        );

        $this->add_control(
            'form_container_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'separator'  => 'before',
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'form_container_box_shadow',
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_label_style_controls()
    {
        $this->start_controls_section(
            'section_form_label_style',
            [
                'label' => __('Labels', 'fluentform'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'form_label_text_color',
            [
                'label'     => __('Text Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-input--label label' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'form_label_typography',
                'label'    => __('Typography', 'fluentform'),
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-input--label label',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_input_textarea_style_controls()
    {
        $this->start_controls_section(
            'section_form_fields_style',
            [
                'label' => __('Input & Textarea', 'fluentform'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'input_alignment',
            [
                'label'   => __('Alignment', 'fluentform'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'fluentform'),
                        'icon'  => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'fluentform'),
                        'icon'  => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'fluentform'),
                        'icon'  => 'fa fa-align-right',
                    ],
                ],
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group select' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_form_fields_style');

        $this->start_controls_tab(
            'tab_form_fields_normal',
            [
                'label' => __('Normal', 'fluentform'),
            ]
        );

        $this->add_control(
            'form_field_bg_color',
            [
                'label'     => __('Background Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):not(.select2-search__field), {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group select, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .select2-container--default .select2-selection--multiple' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'form_field_text_color',
            [
                'label'     => __('Text Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group select' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'form_field_border',
                'label'       => __('Border', 'fluentform'),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):not(.select2-search__field), {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group select,  {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .select2-container--default .select2-selection--multiple',
                'separator'   => 'before',
            ]
        );

        $this->add_control(
            'form_field_radius',
            [
                'label'      => __('Border Radius', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group select,  {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .select2-container--default .select2-selection--multiple' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_field_text_indent',
            [
                'label' => __('Text Indent', 'fluentform'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 60,
                        'step' => 1,
                    ],
                    '%' => [
                        'min'  => 0,
                        'max'  => 30,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group select' => 'text-indent: {{SIZE}}{{UNIT}}',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'form_input_width',
            [
                'label' => __('Input Width', 'fluentform'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group select' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_input_height',
            [
                'label' => __('Input Height', 'fluentform'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 80,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group select' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_textarea_width',
            [
                'label' => __('Textarea Width', 'fluentform'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_textarea_height',
            [
                'label' => __('Textarea Height', 'fluentform'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 400,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_field_padding',
            [
                'label'      => __('Padding', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_field_spacing',
            [
                'label' => __('Spacing', 'fluentform'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_field_typography',
                'label'     => __('Typography', 'fluentform'),
                'selector'  => '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group select',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'form_field_box_shadow',
                'selector'  => '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group select',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_form_fields_focus',
            [
                'label' => __('Focus', 'fluentform'),
            ]
        );

        $this->add_control(
            'form_field_bg_color_focus',
            [
                'label'     => __('Background Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):focus, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea:focus' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'form_input_focus_border',
                'label'       => __('Border', 'fluentform'),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):focus, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea:focus',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'form_input_focus_box_shadow',
                'selector'  => '{{WRAPPER}} .fluentform-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):focus, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea:focus',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function register_terms_gdpr_style_controls()
    {
        $this->start_controls_section(
            'section_form_terms_gdpr_style',
            [
                'label' => __('GDPR , Terms & Condition ', 'fluentform'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'form_terms_gdpr_alignment',
            [
                'label'   => __('Alignment', 'fluentform'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'fluentform'),
                        'icon'  => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'fluentform'),
                        'icon'  => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'fluentform'),
                        'icon'  => 'fa fa-align-right',
                    ],
                ],
                'default'   => '',
                'selectors' => ['{{WRAPPER}} .fluentform-widget-wrapper .ff_t_c' => 'text-align: {{VALUE}};'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'form_terms_gdpr_typography',
                'label'    => __('Typography', 'fluentform'),
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper .ff_t_c ',

            ]
        );
        $this->add_control(
            'form_terms_gdpr_color',
            [
                'label'     => __('Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => ['{{WRAPPER}} .fluentform-widget-wrapper .ff_t_c ' => 'color: {{VALUE}};'],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_placeholder_style_controls()
    {
        $this->start_controls_section(
            'section_placeholder_style',
            [
                'label'     => __('Placeholder', 'fluentform'),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'placeholder_switch' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_placeholder_text_color',
            [
                'label'     => __('Text Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group input::-webkit-input-placeholder, {{WRAPPER}} .fluentform-widget-wrapper .ff-el-group textarea::-webkit-input-placeholder' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'placeholder_switch' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_radio_checkbox_style_controls()
    {
        $this->start_controls_section(
            'section_form_radio_checkbox_style',
            [
                'label' => __('Radio & Checkbox', 'fluentform'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'form_custom_radio_checkbox',
            [
                'label'        => __('Custom Styles', 'fluentform'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'fluentform'),
                'label_off'    => __('No', 'fluentform'),
                'return_value' => 'yes',
            ]
        );

        $this->add_responsive_control(
            'form_radio_checkbox_size',
            [
                'label'   => __('Size', 'fluentform'),
                'type'    => Controls_Manager::SLIDER,
                'default' => [
                    'size' => '15',
                    'unit' => 'px',
                ],
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 80,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="checkbox"], {{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="radio"]' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_radio_checkbox_text_indent',
            [
                'label' => __('Text Indent', 'fluentform'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 60,
                        'step' => 1,
                    ],
                    '%' => [
                        'min'  => 0,
                        'max'  => 30,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="checkbox"], {{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="radio"]' => 'margin-right: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_radio_checkbox_style');

        $this->start_controls_tab(
            'form_radio_checkbox_normal',
            [
                'label'     => __('Normal', 'fluentform'),
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_radio_checkbox_bg_color',
            [
                'label'     => __('Background Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="checkbox"]:after, {{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="radio"]:after' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_checkbox_border_width',
            [
                'label' => __('Border Width', 'fluentform'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 15,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="checkbox"]:after, {{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="radio"]:after' => 'border-width: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_checkbox_border_color',
            [
                'label'     => __('Border Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="checkbox"]:after, {{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="radio"]:after' => 'border-color: {{VALUE}}',
                ],
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_checkbox_heading',
            [
                'label'     => __('Checkbox', 'fluentform'),
                'type'      => Controls_Manager::HEADING,
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_checkbox_border_radius',
            [
                'label'      => __('Border Radius', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="checkbox"]:after, {{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="radio"]:after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_radio_heading',
            [
                'label'     => __('Radio Buttons', 'fluentform'),
                'type'      => Controls_Manager::HEADING,
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_radio_border_radius',
            [
                'label'      => __('Border Radius', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="radio"]:after, {{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="radio"]:after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'form_radio_checkbox_checked',
            [
                'label'     => __('Checked', 'fluentform'),
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_radio_checkbox_bg_color_checked',
            [
                'label'     => __('Background Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="checkbox"]:checked:after, {{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="radio"]:checked:after' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'form_radio_checkbox_border_checked',
            [
                'label'     => __('Border Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="checkbox"]:checked:after, {{WRAPPER}} .fluentform-widget-custom-radio-checkbox input[type="radio"]:checked:after' => 'border-color: {{VALUE}}',
                ],
                'condition' => [
                    'form_custom_radio_checkbox' => 'yes',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function register_section_break_style_controls()
    {
        $this->start_controls_section(
            'form_section_break_style',
            [
                'label' => __('Section Break', 'fluentform'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'form_section_break_label',
            [
                'label' => __('Label', 'fluentform'),
                'type'  => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'form_section_break_label_color',
            [
                'label'     => __('Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-section-break .ff-el-section-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_section_break_label_typography',
                'label'     => __('Typography', 'fluentform'),
                'selector'  => '.fluentform-widget-wrapper .ff-el-section-break .ff-el-section-title',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'form_section_break_label_padding',
            [
                'label'      => __('Padding', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-section-break .ff-el-section-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_section_break_label_margin',
            [
                'label'      => __('Margin', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-section-break .ff-el-section-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'form_section_break_description',
            [
                'label'     => __('Description', 'fluentform'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'form_section_break_description_color',
            [
                'label'     => __('Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-section-break .ff-section_break_desk' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_section_break_description_typography',
                'label'     => __('Typography', 'fluentform'),
                'selector'  => '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-section-break div',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'form_section_break_description_padding',
            [
                'label'      => __('Padding', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-section-break .ff-section_break_desk' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_section_break_description_margin',
            [
                'label'      => __('Margin', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-section-break .ff-section_break_desk' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_section_break_alignment',
            [
                'label'   => __('Alignment', 'fluentform'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'fluentform'),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'fluentform'),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'fluentform'),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'prefix_class' => 'fluentform-widget-section-break-content-',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_checkbox_grid_style_controls()
    {
        $this->start_controls_section(
            'section_form_checkbox_grid',
            [
                'label' => __('Checkbox Grid', 'fluentform'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'section_form_checkbox_grid_head',
            [
                'label'     => __('Grid Table Head', 'fluentform'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'form_checkbox_grid_table_head_text_color',
            [
                'label'     => __('Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-table thead th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'form_checkbox_grid_table_head_color',
            [
                'label'     => __('Background Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-table thead th' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_checkbox_grid_table_head_typography',
                'label'     => __('Typography', 'fluentform'),
                'selector'  => '{{WRAPPER}} .fluentform-widget-wrapper .ff-table thead th',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'form_checkbox_grid_table_head_height',
            [
                'label' => __('Height', 'fluentform'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-table thead th' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'form_checkbox_grid_table_head_border',
                'label'    => __('Border', 'fluentform'),
                'default'  => '',
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper .ff-table thead tr',
            ]
        );

        $this->add_responsive_control(
            'form_checkbox_grid_table_head_padding',
            [
                'label'      => __('Padding', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-table thead th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'form_checkbox_grid_table_item',
            [
                'label'     => __('Grid Table Item', 'fluentform'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'form_checkbox_grid_table_item_color',
            [
                'label'     => __('Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-table tbody tr td' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'form_checkbox_grid_table_item_bg_color',
            [
                'label'     => __('Background Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-table tbody tr td' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'form_checkbox_grid_table_item_odd_bg_color',
            [
                'label'     => __('Odd Item Background Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper tbody>tr:nth-child(2n)>td' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'form_checkbox_grid_table_item_typography',
                'label'    => __('Typography', 'fluentform'),
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper .ff-table tbody tr td',
            ]
        );

        $this->add_responsive_control(
            'form_checkbox_grid_table_item_height',
            [
                'label' => __('Height', 'fluentform'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-table tbody tr td' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'form_checkbox_grid_table_item_border',
                'label'    => __('Border', 'fluentform'),
                'default'  => '',
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper .ff-table tbody tr',
            ]
        );

        $this->add_responsive_control(
            'form_checkbox_grid_table_item_padding',
            [
                'label'      => __('Padding', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-table tbody tr td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_address_line_style_controls()
    {
        $this->start_controls_section(
            'section_form_address_line_style',
            [
                'label' => __('Address Line', 'fluentform'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'address_line_label_color',
            [
                'label'     => __('Label Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .fluent-address label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'address_line_label_typography',
                'label'    => __('Typography', 'fluentform'),
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper .fluent-address label',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_image_upload_style_controls()
    {
        $this->start_controls_section(
            'section_form_image_upload_style',
            [
                'label' => __('Image Upload', 'fluentform'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('tabs_form_image_upload_button_style');

        $this->start_controls_tab(
            'tab_form_image_upload_button_normal',
            [
                'label' => __('Normal', 'fluentform'),
            ]
        );

        $this->add_control(
            'form_image_upload_bg_color',
            [
                'label'     => __('Background Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff_upload_btn.ff-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'form_image_upload_button_border_normal',
                'label'    => __('Border', 'fluentform'),
                'default'  => '',
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper .ff_upload_btn.ff-btn',
            ]
        );

        $this->add_control(
            'form_image_upload_button_border_radius',
            [
                'label'      => __('Border Radius', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff_upload_btn.ff-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_image_upload_button_padding',
            [
                'label'      => __('Padding', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff_upload_btn.ff-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'form_image_upload_typography',
                'label'    => __('Typography', 'fluentform'),
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper .ff_upload_btn.ff-btn',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'form_image_upload_button_box_shadow',
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper .ff_upload_btn.ff-btn',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_form_image_upload_button_hover',
            [
                'label' => __('Hover', 'fluentform'),
            ]
        );

        $this->add_control(
            'form_image_upload_button_bg_color_hover',
            [
                'label'     => __('Background Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff_upload_btn.ff-btn:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'form_image_upload_button_text_color_hover',
            [
                'label'     => __('Text Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff_upload_btn.ff-btn:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'form_image_upload_button_border_color_hover',
            [
                'label'     => __('Border Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff_upload_btn.ff-btn:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();
        $this->end_controls_section();
    }

    protected function register_pagination_style_controls()
    {
        if (defined('FLUENTFORMPRO')) {
            $this->start_controls_section(
                'section_form_pagination_style',
                [
                    'label' => __('Pagination', 'fluentform'),
                    'tab'   => Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'form_pagination_progressbar_label',
                [
                    'label' => __('Progressbar Label', 'fluentform'),
                    'type'  => Controls_Manager::HEADING,
                ]
            );

            $this->add_control(
                'show_label',
                [
                    'label'        => __('Show Label', 'fluentform'),
                    'type'         => Controls_Manager::SWITCHER,
                    'label_on'     => __('Show', 'fluentform'),
                    'label_off'    => __('Hide', 'fluentform'),
                    'return_value' => 'yes',
                    'default'      => 'yes',
                    'prefix_class' => 'fluent-form-widget-step-header-',
                ]
            );

            $this->add_control(
                'form_progressbar_label_color',
                [
                    'label'  => __('Label Color', 'fluentform'),
                    'type'   => Controls_Manager::COLOR,
                    'scheme' => [
                        'type'  => Scheme_Color::get_type(),
                        'value' => Scheme_Color::COLOR_1,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .ff-el-progress-status' => 'color: {{VALUE}}',
                    ],
                    'condition' => [
                        'show_label' => 'yes',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name'      => 'form_progressbar_label_typography',
                    'label'     => __('Typography', 'fluentform'),
                    'scheme'    => Scheme_Typography::TYPOGRAPHY_1,
                    'selector'  => '{{WRAPPER}} .ff-el-progress-status',
                    'condition' => [
                        'show_label' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                'form_progressbar_label_space',
                [
                    'label'      => __('Spacing', 'fluentform'),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', '%', 'em'],
                    'selectors'  => [
                        '{{WRAPPER}} .ff-el-progress-status' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'condition' => [
                        'show_label' => 'yes',
                    ],
                    'separator' => 'after',
                ]
            );

            $this->add_control(
                'form_pagination_progressbar',
                [
                    'label' => __('Progressbar', 'fluentform'),
                    'type'  => Controls_Manager::HEADING,
                ]
            );

            $this->add_control(
                'show_form_progressbar',
                [
                    'label'        => __('Show Progressbar', 'fluentform'),
                    'type'         => Controls_Manager::SWITCHER,
                    'label_on'     => __('Show', 'fluentform'),
                    'label_off'    => __('Hide', 'fluentform'),
                    'return_value' => 'yes',
                    'default'      => 'yes',
                    'prefix_class' => 'fluent-form-widget-step-progressbar-',
                ]
            );

            $this->start_controls_tabs('form_progressbar_style_tabs');

            $this->start_controls_tab(
                'form_progressbar_normal',
                [
                    'label'     => __('Normal', 'fluentform'),
                    'condition' => [
                        'show_form_progressbar' => 'yes',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Background::get_type(),
                [
                    'name'      => 'form_progressbar_bg',
                    'label'     => __('Background', 'fluentform'),
                    'types'     => ['classic', 'gradient'],
                    'selector'  => '{{WRAPPER}} .ff-el-progress',
                    'condition' => [
                        'show_form_progressbar' => 'yes',
                    ],
                    'exclude' => [
                        'image',
                    ],
                ]
            );

            $this->add_control(
                'form_progressbar_color',
                [
                    'label'  => __('Text Color', 'fluentform'),
                    'type'   => Controls_Manager::COLOR,
                    'scheme' => [
                        'type'  => Scheme_Color::get_type(),
                        'value' => Scheme_Color::COLOR_1,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .ff-el-progress-bar span' => 'color: {{VALUE}};',
                    ],
                    'condition' => [
                        'show_form_progressbar' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                'form_progressbar_height',
                [
                    'label'      => __('Height', 'fluentform'),
                    'type'       => Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range'      => [
                        'px' => [
                            'min'  => 0,
                            'max'  => 100,
                            'step' => 1,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .ff-el-progress' => 'height: {{SIZE}}{{UNIT}};',
                    ],
                    'condition' => [
                        'show_form_progressbar' => 'yes',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name'      => 'form_progressbar_border',
                    'label'     => __('Border', 'fluentform'),
                    'selector'  => '{{WRAPPER}} .ff-el-progress',
                    'condition' => [
                        'show_form_progressbar' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                'form_progressbar_border_radius',
                [
                    'label'      => __('Border Radius', 'fluentform'),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', '%', 'em'],
                    'selectors'  => [
                        '{{WRAPPER}} .ff-el-progress' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'condition' => [
                        'show_form_progressbar' => 'yes',
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->start_controls_tab(
                'form_progressbar_filled',
                [
                    'label'     => __('Filled', 'fluentform'),
                    'condition' => [
                        'show_form_progressbar' => 'yes',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Background::get_type(),
                [
                    'name'      => 'form_progressbar_bg_filled',
                    'label'     => __('Background', 'fluentform'),
                    'types'     => ['classic', 'gradient'],
                    'selector'  => '{{WRAPPER}} .ff-el-progress-bar',
                    'condition' => [
                        'show_form_progressbar' => 'yes',
                    ],
                    'exclude' => [
                        'image',
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_control(
                'form_pagination_button_style',
                [
                    'label'     => __('Button', 'fluentform'),
                    'type'      => Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );

            $this->start_controls_tabs(
                'form_pagination_button_style_tabs'
            );

            $this->start_controls_tab(
                'form_pagination_button',
                [
                    'label' => __('Normal', 'fluentform'),
                ]
            );

            $this->add_control(
                'form_pagination_button_color',
                [
                    'label'     => __('Color', 'fluentform'),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .step-nav button' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name'     => 'form_pagination_button_typography',
                    'label'    => __('Typography', 'fluentform'),
                    'scheme'   => Scheme_Typography::TYPOGRAPHY_1,
                    'selector' => '{{WRAPPER}} .step-nav button',
                ]
            );

            $this->add_group_control(
                Group_Control_Background::get_type(),
                [
                    'name'     => 'form_pagination_button_bg',
                    'label'    => __('Background', 'fluentform'),
                    'types'    => ['classic', 'gradient'],
                    'selector' => '{{WRAPPER}} .step-nav button',
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name'     => 'form_pagination_button_border',
                    'label'    => __('Border', 'fluentform'),
                    'selector' => '{{WRAPPER}} .step-nav button',
                ]
            );

            $this->add_control(
                'form_pagination_button_border_radius',
                [
                    'label'      => __('Border Radius', 'fluentform'),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', '%', 'em'],
                    'selectors'  => [
                        '{{WRAPPER}} .step-nav button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_control(
                'form_pagination_button_padding',
                [
                    'label'      => __('Padding', 'fluentform'),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', '%', 'em'],
                    'selectors'  => [
                        '{{WRAPPER}} .step-nav button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->start_controls_tab(
                'form_pagination_button_hover',
                [
                    'label' => __('Hover', 'fluentform'),
                ]
            );

            $this->add_control(
                'form_pagination_button_hover_color',
                [
                    'label'     => __('Color', 'fluentform'),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .step-nav button:hover' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Background::get_type(),
                [
                    'name'     => 'form_pagination_button_hover_bg',
                    'label'    => __('Background', 'fluentform'),
                    'types'    => ['classic', 'gradient'],
                    'selector' => '{{WRAPPER}} .step-nav button:hover',
                ]
            );

            $this->add_control(
                'form_pagination_button_border_hover_color',
                [
                    'label'     => __('Border Color', 'fluentform'),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .step-nav button:hover' => 'border-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'form_pagination_button_border_hover_radius',
                [
                    'label'      => __('Border Radius', 'fluentform'),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', '%', 'em'],
                    'selectors'  => [
                        '{{WRAPPER}} .step-nav button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->end_controls_section();
        }
    }

    protected function register_submit_button_style_controls()
    {
        $this->start_controls_section(
            'section_form_submit_button_style',
            [
                'label' => __('Submit Button', 'fluentform'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'form_submit_button_align',
            [
                'label'   => __('Alignment', 'fluentform'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'fluentform'),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'fluentform'),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'fluentform'),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'default'      => '',
                'prefix_class' => 'fluentform-widget-submit-button-',
                'condition'    => [
                    'form_submit_button_width_type' => 'custom',
                ],
            ]
        );

        $this->add_control(
            'form_submit_button_width_type',
            [
                'label'   => __('Width', 'fluentform'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'custom',
                'options' => [
                    'full-width' => __('Full Width', 'fluentform'),
                    'custom'     => __('Custom', 'fluentform'),
                ],
                'prefix_class' => 'fluentform-widget-submit-button-',
            ]
        );

        $this->add_responsive_control(
            'form_submit_button_width',
            [
                'label' => __('Width', 'fluentform'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .ff-btn-submit' => 'width: {{SIZE}}{{UNIT}}', ],
                'condition' => [
                    'form_submit_button_width_type' => 'custom',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_submit_button_style');

        $this->start_controls_tab(
            'tab_submit_button_normal',
            [
                'label' => __('Normal', 'fluentform'),
            ]
        );

        $this->add_control(
            'form_submit_button_bg_color_normal',
            [
                'label'     => __('Background Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#1a7efb',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .ff-btn-submit' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'form_submit_button_text_color_normal',
            [
                'label'     => __('Text Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .ff-btn-submit' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'form_submit_button_border_normal',
                'label'       => __('Border', 'fluentform'),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .ff-btn-submit',
            ]
        );

        $this->add_control(
            'form_submit_button_border_radius',
            [
                'label'      => __('Border Radius', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .ff-btn-submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_submit_button_padding',
            [
                'label'      => __('Padding', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .ff-btn-submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_submit_button_margin',
            [
                'label' => __('Margin Top', 'fluentform'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 150,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .ff-btn-submit' => 'margin-top: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_submit_button_typography',
                'label'     => __('Typography', 'fluentform'),
                'selector'  => '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .ff-btn-submit',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'form_submit_button_box_shadow',
                'selector'  => '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .ff-btn-submit',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_submit_button_hover',
            [
                'label' => __('Hover', 'fluentform'),
            ]
        );

        $this->add_control(
            'form_submit_button_bg_color_hover',
            [
                'label'     => __('Background Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .ff-btn-submit:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'form_submit_button_text_color_hover',
            [
                'label'     => __('Text Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .ff-btn-submit:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'form_submit_button_border_color_hover',
            [
                'label'     => __('Border Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-el-group .ff-btn-submit:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function register_success_message_style_controls()
    {
        $this->start_controls_section(
            'section_form_success_message_style',
            [
                'label' => __('Success Message', 'fluentform'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'form_success_message_bg_color',
            [
                'label'     => __('Background Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-message-success' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'form_success_message_text_color',
            [
                'label'     => __('Text Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .ff-message-success' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'form_success_message_border',
                'label'       => __('Border', 'fluentform'),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .fluentform-widget-wrapper .ff-message-success',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'form_success_message_typography',
                'label'    => __('Typography', 'fluentform'),
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper .ff-message-success',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_errors_style_controls()
    {
        $this->start_controls_section(
            'section_form_error_style',
            [
                'label' => __('Error Message', 'fluentform'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'form_error_message_text_color',
            [
                'label'     => __('Color', 'fluentform'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .error.text-danger' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'form_error_message_typography',
                'label'    => __('Typography', 'fluentform'),
                'selector' => '{{WRAPPER}} .fluentform-widget-wrapper .error.text-danger',
            ]
        );

        $this->add_responsive_control(
            'form_error_message_padding',
            [
                'label'      => __('Padding', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .error.text-danger' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_error_message_margin',
            [
                'label'      => __('Margin', 'fluentform'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .fluentform-widget-wrapper .error.text-danger' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     *
     * @access protected
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        extract($settings);

        $this->add_render_attribute(
            'fluentform_widget_wrapper',
            [
                'class' => [
                    'fluentform-widget-wrapper',
                ],
            ]
        );

        if ('yes' != $placeholder_switch) {
            $this->add_render_attribute('fluentform_widget_wrapper', 'class', 'hide-placeholder');
        }

        if ('yes' != $labels_switch) {
            $this->add_render_attribute('fluentform_widget_wrapper', 'class', 'hide-fluent-form-labels');
        }

        if ('no' == $error_messages) {
            $this->add_render_attribute('fluentform_widget_wrapper', 'class', 'hide-error-message');
        }

        if ('yes' == $form_custom_radio_checkbox) {
            $this->add_render_attribute('fluentform_widget_wrapper', 'class', 'fluentform-widget-custom-radio-checkbox');
        }

        if ($form_container_alignment) {
            $this->add_render_attribute('fluentform_widget_wrapper', 'class', 'fluentform-widget-align-' . $form_container_alignment . '');
        }

        if (!empty($form_list)) { ?>

            <div <?php echo wp_kses_post($this->get_render_attribute_string('fluentform_widget_wrapper')); ?>>

            <?php if ('yes' == $custom_title_description) { ?>
                <div class="fluentform-widget-heading">
                    <?php if ('' != $form_title_custom) { ?>
                    <h3 class="fluentform-widget-title">
                        <?php echo esc_attr($form_title_custom); ?>
                    </h3>
                    <?php } ?>
                    <?php if ('' != $form_description_custom) { ?>
                    <p class="fluentform-widget-description">
                        <?php echo wp_kses_post($this->parse_text_editor($form_description_custom)); ?>
                    </p>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php echo do_shortcode('[fluentform id="' . $form_list . '"]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $form_list is escaped before being passed in.?>
            </div>

            <?php
        }
    }

    /**
     * Render the widget output in the editor.
     *
     * Written as a Backbone JavaScript template and used to generate the live preview.
     *
     * @since 1.0.0
     *
     * @access protected
     */
    protected function content_template()
    {
    }
}
