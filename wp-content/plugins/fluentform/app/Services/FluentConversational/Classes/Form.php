<?php

namespace FluentForm\App\Services\FluentConversational\Classes;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Form\Settings\FormCssJs;
use FluentForm\App\Services\FluentConversational\Classes\Converter\Converter;
use FluentForm\App\Services\FluentConversational\Classes\Elements\WelcomeScreen;

class Form
{
    protected $addOnKey = 'conversational_forms';

    protected $metaKey = 'ffc_form_settings';

    public function boot()
    {
        add_action('wp', [$this, 'render'], 100);

        add_filter('fluentform/editor_components', [$this, 'filterAcceptedFields'], 999, 2);

        add_filter('fluentform/form_admin_menu', [$this, 'pushDesignTab'], 10, 2);

        add_action('fluentform/form_application_view_conversational_design', [$this, 'renderDesignSettings'], 10, 1);

        add_filter('fluentform/editor_element_settings_placement', [$this, 'maybeAlterPlacement'], 10, 2);

        // elements
        new WelcomeScreen();
    }

    public function pushDesignTab($menuItems, $formId)
    {
        if (!Helper::isConversionForm($formId)) {
            return $menuItems;
        }

        $newItems = $menuItems;

        if (Acl::hasPermission('fluentform_forms_manager')) {
            $newItems = array_slice($menuItems, 0, 1, true) + [
                    'conversational_design' => [
                        'slug'  => 'conversational_design',
                        'title' => __('Design', 'fluentform'),
                        'url'   => admin_url('admin.php?page=fluent_forms&form_id=' . $formId . '&route=conversational_design'),
                    ],
                ] + array_slice($menuItems, 1, count($menuItems) - 1, true);
        }

        return $newItems;
    }

    public function renderDesignSettings($formId)
    {
        if (!Helper::isConversionForm($formId)) {
            echo 'Sorry! This is not a conversational form';
            return;
        }

        if (function_exists('wp_enqueue_editor')) {
            add_filter('user_can_richedit', '__return_true');
            wp_enqueue_editor();
            wp_enqueue_media();
        }

        wp_enqueue_script(
            'fluent_forms_conversational_design',
            fluentformMix('js/conversational_design.js'),
            ['jquery'],
            FLUENTFORM_VERSION,
            true
        );

        $slug = apply_filters_deprecated(
            'fluentform_conversational_url_slug',
            [
                'fluent-form'
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/conversational_url_slug',
            'Use fluentform/conversational_url_slug instead of fluentform_conversational_url_slug.'
        );

        $paramKey = apply_filters('fluentform/conversational_url_slug', $slug);

        if ('form' == $paramKey) {
            $paramKey = 'fluent-form';
        }

        wp_localize_script('fluent_forms_conversational_design', 'ffc_conv_vars', [
            'form_id'     => $formId,
            'preview_url' => site_url('?' . $paramKey . '=' . $formId),
            'fonts'       => Fonts::getFonts(),
            'has_pro'     => defined('FLUENTFORMPRO'),
        ]);

        wp_enqueue_style(
            'fluent_forms_conversion_style',
            fluentformMix('css/conversational_design.css'),
            [],
            FLUENTFORM_VERSION
        );

        echo '<div id="ff_conversation_form_design_app"><design-skeleton><h1 style="text-align: center; margin: 60px 0px;">Loading App Please wait....</h1></design-skeleton><global-search></global-search></div>';
    }

    public function getDesignSettings($formId)
    {
        $settings = Helper::getFormMeta($formId, $this->metaKey . '_design', []);

        $defaults = [
            'background_color'      => '#FFFFFF',
            'question_color'        => '#191919',
            'answer_color'          => '#0445AF',
            'button_color'          => '#0445AF',
            'button_text_color'     => '#FFFFFF',
            'background_image'      => '',
            'background_brightness' => 0,
            'disable_branding'      => 'no',
            'hide_media_on_mobile'  => 'no',
            'key_hint'              => 'yes',
            'asteriskPlacement'     => $this->getAsteriskPlacement($formId)
        ];

        return wp_parse_args($settings, $defaults);
    }

    public function getMetaSettings($formId)
    {
        $settings = Helper::getFormMeta($formId, $this->metaKey . '_meta', []);
        $defaults = [
            'title'            => '',
            'description'      => '',
            'featured_image'   => '',
            'share_key'        => '',
            'google_font_href' => '',
            'font_css'         => '',
            'i18n'             => [
                'skip_btn'             => 'SKIP',
                'confirm_btn'          => 'OK',
                'continue'             => 'Continue',
                'keyboard_instruction' => 'Press <b>Enter ↵</b>',
                'multi_select_hint'    => 'Choose as many as you like',
                'single_select_hint'   => 'Choose one option',
                'progress_text'        => '{percent}% completed',
                'long_text_help'       => '<b>Shift ⇧</b> + <b>Enter ↵</b> to make a line break.',
                'invalid_prompt'       => 'Please fill out the field correctly',
                'errorMaxLength'       => 'The maximum {maxLength} number of characters accept',
                'default_placeholder'  => 'Type Your answer here',
                'key_hint_text'        => 'Key',
                'key_hint_tooltip'     => 'Press the key to select',
                'choose_file'          => '<b>Choose file</b> or <b>drag here</b>',
                'limit'                => 'Size limit: '
            ],
        ];

        if ($settings && !isset($settings['i18n']['key_hint_text'])) {
            $settings['i18n']['key_hint_text'] = $defaults['i18n']['key_hint_text'];
            $settings['i18n']['key_hint_tooltip'] = $defaults['i18n']['key_hint_tooltip'];
        }

        if (!$settings || empty($settings['title'])) {
            $form = wpFluent()->table('fluentform_forms')->find($formId);
            $settings['title'] = $form->title;
        }

        return wp_parse_args($settings, $defaults);
    }

    private function getGeneratedCss($formId)
    {
        $prefix = '.ff_conv_app_' . $formId;
        if (defined('FLUENTFORMPRO')) {
            $css = Helper::getFormMeta($formId, $this->metaKey . '_generated_css', '');
            if ($css) {
                return $css;
            }
        }

        return $prefix . ' { background-color: #FFFFFF; }' . $prefix . ' .ffc-counter-div span { color: #0445AF; }' . $prefix . ' .ffc-counter-div .counter-icon-span svg { fill: #0445AF !important; }' . $prefix . ' .f-label-wrap, ' . $prefix . ' .f-answer { color: #0445AF !important; }' . $prefix . ' .f-label-wrap .f-key { border-color: #0445AF !important; }' . $prefix . ' .f-label-wrap .f-key-hint { border-color: #0445AF !important; }' . $prefix . ' .f-answer .f-radios-wrap ul li { background-color: rgba(4,69,175, 0.1) !important; border: 1px solid #0445AF; }' . $prefix . ' .f-answer .f-radios-wrap ul li:focus { background-color: rgba(4,69,175, 0.3) !important }' . $prefix . ' .f-answer .f-radios-wrap ul li:hover { background-color: rgba(4,69,175, 0.3) !important }' . $prefix . ' .f-answer .f-radios-wrap ul li.f-selected .f-key { background-color: #0445AF !important; color: white; }' . $prefix . ' .f-answer .f-radios-wrap ul li.f-selected .f-key-hint { background-color: #0445AF; }' . $prefix . ' .f-answer .f-radios-wrap ul li.f-selected svg { fill: #0445AF !important; }' . $prefix . ' .f-answer input, ' . $prefix . ' .f-answer textarea{ color: #0445AF !important; box-shadow: #0445AF  0px 1px; }' . $prefix . ' .f-answer input:focus, ' . $prefix . ' .f-answer textarea:focus { box-shadow: #0445AF  0px 2px !important; }' . $prefix . ' .f-answer textarea::placeholder, ' . $prefix . ' .f-answer input::placeholder { color: #0445AF !important; }' . $prefix . ' .text-success { color: #0445AF !important; }' . $prefix . ' .f-answer .f-matrix-table tbody td { background-color: rgba(4,69,175, 0.1); }' . $prefix . ' .f-answer .f-matrix-table input { border-color: rgba(4,69,175, 0.8); }' . $prefix . ' .f-answer .f-matrix-table input.f-radio-control:checked::after { background-color: #0445AF; }' . $prefix . ' .f-answer .f-matrix-table input:focus::before { border-color: #0445AF; }' . $prefix . ' .f-answer .f-matrix-table input.f-checkbox-control:checked { background-color: #0445AF; }' . $prefix . ' .f-answer .f-matrix-table tbody tr::after { border-right-color: #0445AF; }' . $prefix . ' .f-answer .f-matrix-table .f-table-cell.f-row-cell { box-shadow: rgba(4,69,175, 0.1) 0px 0px 0px 100vh inset; }' . $prefix . ' .f-answer .ff_file_upload_field_wrap { background-color: rgba(4,69,175, 0.1); border-color: rgba(4,69,175, 0.8); }' . $prefix . ' .f-answer .ff_file_upload_field_wrap:hover { background-color: rgba(4,69,175, 0.3);}' . $prefix . ' .f-answer .ff_file_upload_field_wrap:focus-within { background-color: rgba(4,69,175, 0.3); }' . $prefix . ' .f-answer .ff-upload-preview { border-color: rgba(4,69,175, 0.8); }' . $prefix . ' .f-answer .ff-upload-preview .ff-upload-thumb { background-color: rgba(4,69,175, 0.3); }' . $prefix . ' .f-answer .ff-upload-preview .ff-upload-details { border-left-color: rgba(4,69,175, 0.8); }' . $prefix . ' .f-answer .ff-upload-preview .ff-upload-details .ff-el-progress { border-left-color: rgba(4,69,175, 0.8); }' . $prefix . ' .f-answer .ff-upload-preview .ff-upload-details .ff-el-progress { background-color: rgba(4,69,175, 0.1); }' . $prefix . ' .f-answer .ff-upload-preview .ff-upload-details .ff-el-progress .ff-el-progress-bar { background-color: #0445AF; }' . $prefix . ' .f-answer .f-star-wrap .f-star-field-wrap::before { background-color: #0445AF; }' . $prefix . ' .f-answer .f-star-wrap .f-star-field-wrap .f-star-field .f-star-field-star .symbolOutline { fill: #0445AF; }' . $prefix . ' .f-answer .f-star-wrap .f-star-field-wrap .f-star-field .f-star-field-rating { color: #0445AF; }' . $prefix . ' .f-answer .f-star-wrap .f-star-field-wrap.is-hovered .symbolFill { fill: rgba(4,69,175, 0.1); }' . $prefix . ' .f-answer .f-star-wrap .f-star-field-wrap.is-selected .symbolFill { fill: #0445AF; }' . $prefix . ' .f-answer .f-payment-summary-wrap tbody td { background-color: rgba(4,69,175, 0.1); }' . $prefix . ' .f-answer .f-payment-summary-wrap tfoot th { background-color: rgba(4,69,175, 0.1); }' . $prefix . ' .f-answer .stripe-inline-holder { border-bottom: 1px solid #0445AF; }' . $prefix . ' .f-answer .StripeElement--focus { border-bottom: 2.5px solid #0445AF; }' . $prefix . ' .ff_conv_input .f-info { color: #0445AF; }' . $prefix . ' .fh2 .f-text { color: #191919; }' . $prefix . ' .fh2 .f-tagline, ' . $prefix . ' .f-sub .f-help { color: rgba(25,25,25, 0.70); }' . $prefix . ' .fh2 .stripe-inline-header { color: #191919; }' . $prefix . ' .q-inner .o-btn-action, ' . $prefix . ' .footer-inner-wrap .f-nav { background-color: #0445AF; }' . $prefix . ' .q-inner .o-btn-action span, ' . $prefix . ' .footer-inner-wrap .f-nav a { color: #FFFFFF; } ' . $prefix . ' .f-enter .f-enter-desc { color: #0445AF; }' . $prefix . ' .footer-inner-wrap .f-nav a svg { fill: #FFFFFF; }' . $prefix . ' .vff-footer .f-progress-bar { background-color: rgba(4,69,175, 0.3); }' . $prefix . ' .vff-footer .f-progress-bar-inner { background-color: #0445AF; }' . $prefix . ' .q-inner .o-btn-action:hover { background-color: #0445AFD6; }' . $prefix . ' .q-inner .o-btn-action:focus::after { border-radius: 6px; inset: -3px; box-shadow: #0445AF 0px 0px 0px 2px; }' . $prefix . ' .f-answer .f-radios-wrap ul li.f-selected .f-key { color: #FFFFFF; }';
    }

    public function render()
    {
        $slug = 'fluent-form';
        $paramKey = apply_filters_deprecated(
            'fluentform_conversational_url_slug',
            [
                $slug
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/conversational_url_slug',
            'Use fluentform/conversational_url_slug instead of fluentform_conversational_url_slug.'
        );

        $paramKey = apply_filters('fluentform/conversational_url_slug', $paramKey);

        if ('form' == $paramKey) {
            $paramKey = $slug;
        }

        if(!isset($_REQUEST[$paramKey])) {
            return;
        }

        $request = wpFluentForm('request')->get();

        if ((isset($request[$paramKey])) && !wp_doing_ajax()) {
            $formId = (int) ArrayHelper::get($request, $paramKey);
            $shareKey = ArrayHelper::get($request, 'form');
            $this->renderFormHtml($formId, $shareKey);
        }
    }

    public function isEnabled()
    {
        $globalModules = get_option('fluentform_global_modules_status');

        $addOn = ArrayHelper::get($globalModules, $this->addOnKey);

        if (!$addOn || 'yes' == $addOn) {
            return true;
        }

        return false;
    }

    private function getSubmitBttnStyle($form)
    {
        $data = $form->submit_button;
        $styles = '';

        if ('' == ArrayHelper::get($data, 'settings.button_style')) {
            // it's a custom button
            $buttonActiveStyles = ArrayHelper::get($data, 'settings.normal_styles', []);
            $buttonHoverStyles = ArrayHelper::get($data, 'settings.hover_styles', []);
            $activeStates = '';
            foreach ($buttonActiveStyles as $styleAtr => $styleValue) {
                if (!$styleValue) {
                    continue;
                }
                if ('borderRadius' == $styleAtr) {
                    $styleValue .= 'px';
                }
                $activeStates .= ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '-$0', $styleAtr)), '_') . ':' . $styleValue . ';';
            }
            if ($activeStates) {
                $styles .= ' .ff-btn-submit { ' . $activeStates . ' }';
            }
            $hoverStates = '';
            foreach ($buttonHoverStyles as $styleAtr => $styleValue) {
                if (!$styleValue) {
                    continue;
                }
                if ('borderRadius' == $styleAtr) {
                    $styleValue .= 'px';
                }
                $hoverStates .= ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '-$0', $styleAtr)), '-') . ':' . $styleValue . ';';
            }
            if ($hoverStates) {
                $styles .= ' .wpf_has_custom_css.ff-btn-submit:hover { ' . $hoverStates . ' } ';
            }
        } else {
            $styles .= ' .ff-btn-submit { background-color: ' . ArrayHelper::get($data, 'settings.background_color') . '; color: ' . ArrayHelper::get($data, 'settings.color') . '; }';
        }

        if (defined('FLUENTFORMPRO')) {
            $customCssJsClass = new FormCssJs();
            $customCss = $customCssJsClass->getCss($form->id);
            $styles .= $customCss;
        }

        return $styles;
    }

    public function filterAcceptedFields($components, $formId)
    {
        if (!Helper::isConversionForm($formId)) {
            return $components;
        }

        $generalFields = ArrayHelper::get($components, 'general', []);
        $advancedFields = ArrayHelper::get($components, 'advanced', []);
        $paymentFields = ArrayHelper::get($components, 'payments', []);

        $acceptedFieldElements = [
            'phone',
            'select',
            'select',
            'ratings',
            'textarea',
            'address',
            'input_url',
            'input_text',
            'input_date',
            'input_file',
            'input_email',
            'input_radio',
            'custom_html',
            'input_image',
            'input_hidden',
            'input_number',
            'tabular_grid',
            'section_break',
            'select_country',
            'input_checkbox',
            'input_password',
            'terms_and_condition',
            'gdpr_agreement',
            'multi_payment_component',
            'subscription_payment_component',
            'custom_payment_component',
            'item_quantity_component',
            'payment_method',
            'payment_summary_component',
            'payment_coupon',
            'recaptcha',
            'hcaptcha',
            'quiz_score',
            'rangeslider'
        ];

        $acceptedFieldElements = apply_filters(
            'fluentform/conversational_accepted_field_elements',
            $acceptedFieldElements,
            $formId
        );

        $elements = [];

        $allFields = [
            'general'  => $generalFields,
            'advanced' => $advancedFields,
            'payments' => $paymentFields,
        ];

        foreach ($allFields as $groupType => $group) {
            foreach ($group as $field) {
                $element = $field['element'];
                if (in_array($element, $acceptedFieldElements)) {
                    $field['style_pref'] = [
                        'layout'           => 'default',
                        'media'            => fluentFormGetRandomPhoto(),
                        'brightness'       => 0,
                        'alt_text'         => '',
                        'media_x_position' => 50,
                        'media_y_position' => 50,
                    ];

                    if ('terms_and_condition' == $element || 'gdpr_agreement' == $element) {
                        $existingSettings = $field['settings'];
                        $existingSettings['tc_agree_text'] = __('I accept', 'fluentform');
                        if ('terms_and_condition' == $element) {
                            $existingSettings['tc_dis_agree_text'] = __('I don\'t accept', 'fluentform');
                        }
                        $field['settings'] = $existingSettings;
                    }
                    //adding required settings for captcha in conversational form
                    if ('hcaptcha' == $element || 'recaptcha' == $element) {
                        $existingSettings = $field['settings'];
                        if (empty($existingSettings['validation_rules'])) {
                            $existingSettings['validation_rules'] = [
                                'required' => [
                                    'value'   => true,
                                    'message' => __('This field is required', 'fluentform'),
                                ],
                            ];
                        }
                        $field['settings'] = $existingSettings;
                    }

                    $elements[$groupType][] = $field;
                }
            }
        }

        $elements = apply_filters_deprecated(
            'fluent_conversational_editor_elements',
            [
                $elements
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/conversational_editor_elements',
            'Use fluentform/conversational_editor_elements instead of fluent_conversational_editor_elements.'
        );

        $elements = apply_filters('fluentform/conversational_editor_elements', $elements, $formId);

        return $elements;
    }

    public function printLoadedScripts()
    {
        $jsScripts = $this->getRegisteredScripts();
        if ($jsScripts) {
            add_action('fluentform/conversational_frame_footer', function () use ($jsScripts) {
                foreach ($jsScripts as $handle => $jsScript) {
                    if (empty($jsScript->src)) {
                        continue;
                    }
                    if ($data = ArrayHelper::get($jsScript->extra, 'data')) {
                        printf("<script type='text/javascript' id='%s-js-extra'>\n", esc_attr($handle));
                        echo "$data\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $data is hardcoded localized data and escaped before being passed in.
                        echo "</script>\n";
                    }
                    $src = $jsScript->src;
                    $src = add_query_arg('ver', $jsScript->ver, $src);
                    echo "<script type='text/javascript' id='" . esc_attr($handle) . "' src='" . esc_url($src) . "'></script>\n";
                }
            }, 1);
        }

        $cssStyles = $this->getRegisteredStyles();
        if ($cssStyles) {
            add_action('fluentform/conversational_frame_head', function () use ($cssStyles) {
                foreach ($cssStyles as $styleName => $cssStyle) {
                    if (empty($cssStyle->src)) {
                        continue;
                    }
                    $src = add_query_arg('ver', $cssStyle->ver, $cssStyle->src);

                    echo "<link rel='stylesheet' id='" . esc_attr($styleName) . "' href='" . esc_url($src) . "' type='text/css' media='all' />\n";
                }
            });
        }
    }

    private function getRegisteredScripts()
    {
        global $wp_scripts;
        if (!$wp_scripts) {
            return [];
        }

        $jsScripts = [];

        $pluginUrl = plugins_url() . '/fluentform';

        foreach ($wp_scripts->queue as $script) {

            if (!isset($wp_scripts->registered[$script])) {
                continue;
            }

            $item = $wp_scripts->registered[$script];
            $src = $wp_scripts->registered[$script]->src;

            if (false === !strpos($src, $pluginUrl)) {
                continue;
            }

            foreach ($item->deps as $dep) {
                if (!isset($items[$dep])) {
                    $child = $wp_scripts->registered[$dep];
                    if ($child->src) {
                        $jsScripts[$dep] = $child;
                    } else {
                        // this core file maybe
                        $childDependencies = $child->deps;
                        foreach ($childDependencies as $childDependency) {
                            $childX = $wp_scripts->registered[$childDependency];
                            if ($childX->src) {
                                $jsScripts[$childDependency] = $childX;
                            }
                        }
                    }
                }
            }
            $jsScripts[$script] = $item;
        }

        return $jsScripts;
    }

    private function getRegisteredStyles()
    {
        $wp_styles = wp_styles();
        if (!$wp_styles) {
            return [];
        }

        $cssStyles = [];

        $pluginUrl = plugins_url() . '/fluentform';

        foreach ($wp_styles->queue as $style) {

            if (!isset($wp_styles->registered[$style])) {
                continue;
            }

            $item = $wp_styles->registered[$style];
            $src = $wp_styles->registered[$style]->src;

            if (false === !strpos($src, $pluginUrl)) {
                continue;
            }

            if ($item->deps) {
                foreach ($item->deps as $dep) {
                    if (!isset($items[$dep])) {
                        $child = $wp_styles->registered[$dep];
                        if ($child->src) {
                            $cssStyles[$dep] = $child;
                        } else {
                            // this core file maybe
                            $childDependencies = $child->deps;
                            if ($childDependencies && is_array($childDependencies)) {
                                foreach ($childDependencies as $childDependency) {
                                    $childX = $wp_styles->registered[$childDependency];
                                    if ($childX->src) {
                                        $cssStyles[$childDependency] = $childX;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $cssStyles[$style] = $item;
        }

        return $cssStyles;
    }

    public function renderShortcode($form)
    {
        $formId = $form->id;
        $form = Converter::convert($form);
        $submitCss = $this->getSubmitBttnStyle($form);

        $this->enqueueScripts();

        $metaSettings = $this->getMetaSettings($formId);
        $designSettings = $this->getDesignSettings($formId);
        $instanceId = $form->instance_index;
        $varName = 'fluent_forms_global_var_' . $instanceId;
        wp_localize_script('fluent_forms_conversational_form', $varName, [
            'fluent_forms_admin_nonce' => wp_create_nonce('fluent_forms_admin_nonce'),
            'ajaxurl'                  => admin_url('admin-ajax.php'),
            'form'                     => [
                'id'             => $form->id,
                'questions'      => $form->questions,
                'image_preloads' => $form->image_preloads,
                'submit_button'  => $form->submit_button,
                'hasPayment'     => (bool)$form->has_payment,
                'hasCalculation' => (bool)$form->hasCalculation,
                'reCaptcha'      => $form->reCaptcha,
                'hCaptcha'       => $form->hCaptcha,
            ],
            'assetBaseUrl'             => FLUENT_CONVERSATIONAL_FORM_DIR_URL . 'public',
            'i18n'                     => $metaSettings['i18n'],
            'form_id'                  => $form->id,
            'hasPro'                   => defined('FLUENTFORMPRO'),
            'is_inline_form'           => true,
            'design'                   => $designSettings,
            'extra_inputs'             => $this->getExtraHiddenInputs($formId),
            'uploading_txt'            => __('Uploading', 'fluentform'),
            'upload_completed_txt'     => __('100% Completed', 'fluentform'),
            'paymentConfig'            => $this->getPaymentConfig($form),
            'date_i18n'                => \FluentForm\App\Modules\Component\Component::getDatei18n()
        ]);
    
        /* This filter is deprecated and will be removed soon */
        $disableAnalytics = apply_filters('fluentform-disabled_analytics', false);

        if (!apply_filters('fluentform/disabled_analytics', $disableAnalytics)) {
            if (!Acl::hasAnyFormPermission($form->id)) {
                (new \FluentForm\App\Services\Analytics\AnalyticsService())->store($formId);
            }
        }

        return wpFluentForm('view')->make('public.conversational-form-inline', [
            'generated_css'   => $this->getGeneratedCss($formId),
            'design'          => $designSettings,
            'submit_css'      => $submitCss,
            'form_id'         => $formId,
            'meta'            => $metaSettings,
            'global_var_name' => $varName,
            'instance_id'     => $instanceId,
            'is_inline'       => 'yes',
        ]);
    }

    public function maybeAlterPlacement($placements, $form)
    {
        if (!Helper::isConversionForm($form->id) || empty($placements['terms_and_condition'])) {
            return $placements;
        }

        $placements['terms_and_condition']['general'] = [
            'admin_field_label',
            'validation_rules',
            'tnc_html',
            'tc_agree_text',
            'tc_dis_agree_text',
        ];

        $placements['terms_and_condition']['generalExtras'] = [
            'tc_agree_text'     => [
                'template' => 'inputText',
                'label'    => 'Agree Button Text',
            ],
            'tc_dis_agree_text' => [
                'template' => 'inputText',
                'label'    => 'Disagree Button Text',
            ],
        ];

        return $placements;
    }

    private function getExtraHiddenInputs($formId)
    {
        return [
            '__fluent_form_embded_post_id'                => get_the_ID(),
            '_fluentform_' . $formId . '_fluentformnonce' => wp_create_nonce('fluentform-submit-form'),
            '_wp_http_referer'                            => esc_attr(wp_unslash(wpFluentForm('request')->server('REQUEST_URI'))),
        ];
    }

    public function getRandomPhoto()
    {
        return fluentFormGetRandomPhoto();
    }

    private function renderFormHtml($formId, $providedKey = '')
    {
        $form = wpFluent()->table('fluentform_forms')->find($formId);

        if (!$form) {
            return '';
        }

        $formSettings = wpFluent()
            ->table('fluentform_form_meta')
            ->where('form_id', $formId)
            ->where('meta_key', 'formSettings')
            ->first();

        if (!$formSettings) {
            return '';
        }

        $form->fields = json_decode($form->form_fields, true);

        if (!$form->fields['fields']) {
            return '';
        }

        $form->settings = json_decode($formSettings->value, true);

        if($form->status == 'unpublished' && !Acl::hasAnyFormPermission($formId)) {
            return '';
        }

        $metaSettings = $this->getMetaSettings($formId);

        $shareKey = ArrayHelper::get($metaSettings, 'share_key');
        if ($shareKey) {
            if ($providedKey != $shareKey && !Acl::hasAnyFormPermission($formId)) {
                return '';
            }
        }

        $isRenderable = apply_filters('fluentform/is_form_renderable', [
            'status'  => true,
            'message' => '',
        ], $form);

        if (is_array($isRenderable) && !$isRenderable['status'] && !Acl::hasAnyFormPermission($formId)) {
            echo "<div style='text-align: center; font-size: 16px; margin: 100px 20px;' id='ff_form_{$form->id}' class='ff_form_not_render'>{$isRenderable['message']}</div>";
            exit(200);
        }
        /* This filter is deprecated and will be removed soon */
        $form = wpFluentForm()->applyFilters('fluentform_rendering_form', $form);

        $form = wpFluentForm()->applyFilters('fluentform/rendering_form', $form);
        $form = Converter::convert($form);

        $formSettings = wpFluent()
            ->table('fluentform_form_meta')
            ->where('form_id', $form->id)
            ->where('meta_key', 'formSettings')
            ->first();

        if (!$formSettings) {
            return '';
        }

        $form->settings = json_decode($formSettings->value, true);

        $submitCss = $this->getSubmitBttnStyle($form);

        $this->enqueueScripts();

        $designSettings = $this->getDesignSettings($formId);

        wp_localize_script('fluent_forms_conversational_form', 'fluent_forms_global_var', [
            'fluent_forms_admin_nonce' => wp_create_nonce('fluent_forms_admin_nonce'),
            'ajaxurl'                  => admin_url('admin-ajax.php'),
            'form'                     => [
                'id'             => $form->id,
                'questions'      => $form->questions,
                'image_preloads' => $form->image_preloads,
                'submit_button'  => $form->submit_button,
                'hasPayment'     => (bool)$form->has_payment,
                'hasCalculation' => (bool)$form->hasCalculation,
                'reCaptcha'      => $form->reCaptcha,
                'hCaptcha'       => $form->hCaptcha,
            ],
            'form_id'                  => $form->id,
            'assetBaseUrl'             => FLUENT_CONVERSATIONAL_FORM_DIR_URL . 'public',
            'i18n'                     => $metaSettings['i18n'],
            'design'                   => $designSettings,
            'hasPro'                   => defined('FLUENTFORMPRO'),
            'extra_inputs'             => $this->getExtraHiddenInputs($formId),
            'uploading_txt'            => __('Uploading', 'fluentform'),
            'upload_completed_txt'     => __('100% Completed', 'fluentform'),
            'paymentConfig'            => $this->getPaymentConfig($form),
            'date_i18n'                => \FluentForm\App\Modules\Component\Component::getDatei18n(),
            'rest'                     => Helper::getRestInfo()
        ]);

        $this->printLoadedScripts();

        $isRenderable = [
            'status'  => true,
            'message' => '',
        ];
    
        /* This filter is deprecated and will be removed soon */
        $isRenderable = apply_filters('fluentform_is_form_renderable', $isRenderable, $form);
    
    
        $isRenderable = apply_filters('fluentform/is_form_renderable', $isRenderable, $form);

        if (is_array($isRenderable) && !$isRenderable['status']) {
            if (!Acl::hasAnyFormPermission($form->id)) {
                echo "<h1 style='width: 600px; margin: 200px auto; text-align: center;' id='ff_form_{$form->id}' class='ff_form_not_render'>" . wp_kses_post($isRenderable['message']) . '</h1>';
                exit();
            }
        }
        /* This filter is deprecated and will be removed soon */
        $status = apply_filters('fluentform-disabled_analytics', false);

        if (!apply_filters('fluentform/disabled_analytics', $status)) {
            if (!Acl::hasAnyFormPermission($form->id)) {
                (new \FluentForm\App\Services\Analytics\AnalyticsService())->store($form->id);
            }
        }
        wpFluentForm('view')->render('public.conversational-form', [
            'generated_css' => $this->getGeneratedCss($formId),
            'design'        => $designSettings,
            'submit_css'    => $submitCss,
            'form_id'       => $formId,
            'meta'          => $metaSettings,
            'form'          => $form,
        ]);

        exit(200);
    }

    /**
     * Enqueue proper stylesheet based on rtl & JS script.
     */
    private function enqueueScripts()
    {
        $cssFileName = 'conversationalForm';

        if (is_rtl()) {
            $cssFileName .= '-rtl';
        }

        wp_enqueue_style(
            'fluent_forms_conversational_form',
            FLUENT_CONVERSATIONAL_FORM_DIR_URL . 'public/css/' . $cssFileName . '.css',
            [],
            FLUENTFORM_VERSION
        );

        wp_enqueue_script(
            'fluent_forms_conversational_form',
            FLUENT_CONVERSATIONAL_FORM_DIR_URL . 'public/js/conversationalForm.js',
            [],
            FLUENTFORM_VERSION,
            true
        );
    }

    /**
     * Get the payment configuration of this form.
     *
     * @param $form
     */
    private function getPaymentConfig($form)
    {
        $paymentConfig = null;

        if ($form->has_payment && defined('FLUENTFORMPRO')) {
            $publishableKeyStripe = \FluentFormPro\Payments\PaymentMethods\Stripe\StripeSettings::getPublishableKey($form->id);
            $publishableKeyStripe = apply_filters_deprecated(
                'fluentform-payment_stripe_publishable_key',
                [
                    $publishableKeyStripe,
                    $form->id
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/payment_stripe_publishable_key',
                'Use fluentform/payment_stripe_publishable_key instead of fluentform-payment_stripe_publishable_key.'
            );

            $publishableKey = apply_filters(
                'fluentform/payment_stripe_publishable_key',
                $publishableKeyStripe,
                $form->id
            );

            $paymentConfig = [
                'currency_settings' => \FluentFormPro\Payments\PaymentHelper::getCurrencyConfig($form->id),
                'stripe'            => [
                    'publishable_key' => $publishableKey,
                    'inlineConfig'    => \FluentFormPro\Payments\PaymentHelper::getStripeInlineConfig($form->id),
                ],
                'stripe_app_info'   => [
                    'name'       => 'Fluent Forms',
                    'version'    => FLUENTFORMPRO_VERSION,
                    'url'        => site_url(),
                    'partner_id' => 'pp_partner_FN62GfRLM2Kx5d',
                ],
                'i18n'              => [
                    'item'            => __('Item', 'fluentformpro'),
                    'price'           => __('Price', 'fluentformpro'),
                    'qty'             => __('Qty', 'fluentformpro'),
                    'line_total'      => __('Line Total', 'fluentformpro'),
                    'total'           => __('Total', 'fluentformpro'),
                    'not_found'       => __('No payment item selected yet', 'fluentformpro'),
                    'discount:'       => __('Discount:', 'fluentformpro'),
                    'processing_text' => __('Processing payment. Please wait...', 'fluentformpro'),
                    'confirming_text' => __('Confirming payment. Please wait...', 'fluentformpro'),
                ],
            ];

            $paymentConfig['currency_settings']['currency_symbol'] = \html_entity_decode($paymentConfig['currency_settings']['currency_sign']);
        }

        return $paymentConfig;
    }

    protected function getAsteriskPlacement($formId)
    {
        $asteriskPlacement = 'asterisk-right';

        $formSettings = wpFluent()
            ->table('fluentform_form_meta')
            ->where('form_id', $formId)
            ->where('meta_key', 'formSettings')
            ->first();

        if (!$formSettings) {
            return '';
        }

        $formSettings = json_decode($formSettings->value, true);

        if (isset($formSettings['layout']['asteriskPlacement'])) {
            $asteriskPlacement = $formSettings['layout']['asteriskPlacement'];
        }

        return $asteriskPlacement;
    }
}
