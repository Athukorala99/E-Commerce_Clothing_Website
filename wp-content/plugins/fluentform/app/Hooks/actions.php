<?php

use FluentForm\App\Modules\Component\Component;
use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

/**
 * All registered action's handlers should be in app\Hooks\Handlers,
 * addAction is similar to add_action and addCustomAction is just a
 * wrapper over add_action which will add a prefix to the hook name
 * using the plugin slug to make it unique in all wordpress plugins,
 * ex: $app->addCustomAction('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_action('slug-foo', ['FooHandler', 'handleFoo']).
 */

/**
 * @var $app FluentForm\Framework\Foundation\Application
 */

// From MenuProvider.php
$app->addAction(
    'admin_menu',
    function () use ($app) {
        (new \FluentForm\App\Modules\Registerer\Menu($app))->register();
    }
);

$app->addAction(
    'fluentform/form_application_view_editor',
    function ($formId) use ($app) {
        (new \FluentForm\App\Modules\Registerer\Menu($app))->renderEditor($formId);
    }
);

$app->addAction(
    'fluentform/form_application_view_settings',
    function ($formId) use ($app) {
        (new \FluentForm\App\Modules\Registerer\Menu($app))->renderSettings($formId);
    }
);

$app->addAction(
    'fluentform/form_settings_container_form_settings',
    function ($formId) use ($app) {
        (new \FluentForm\App\Modules\Registerer\Menu($app))->renderFormSettings($formId);
    }
);

$app->addAction(
    'fluentform/global_settings_component_settings',
    function () use ($app) {
        (new \FluentForm\App\Modules\Renderer\GlobalSettings\Settings($app))->render();
    }
);

$app->addAction(
    'fluentform/global_settings_component_reCaptcha',
    function () use ($app) {
        (new \FluentForm\App\Modules\Renderer\GlobalSettings\Settings($app))->render();
    }
);

$app->addAction(
    'fluentform/global_settings_component_hCaptcha',
    function () use ($app) {
        (new \FluentForm\App\Modules\Renderer\GlobalSettings\Settings($app))->render();
    }
);

// From Backend.php
add_action('admin_init', function () use ($app) {
    (new \FluentForm\App\Modules\Registerer\Menu($app))->reisterScripts();
    (new \FluentForm\App\Modules\Registerer\AdminBar())->register();
}, 9);

add_action('admin_enqueue_scripts', function () use ($app) {
    (new \FluentForm\App\Modules\Registerer\Menu($app))->enqueuePageScripts();
}, 10);

// Add Entries Menu
$app->addAction('fluentform/form_application_view_entries', function ($form_id) {
    (new \FluentForm\App\Modules\Entries\Entries())->renderEntries($form_id);
});

$app->addAction('fluentform/after_form_navigation', function ($form_id) use ($app) {
    (new \FluentForm\App\Modules\Registerer\Menu($app))->addCopyShortcodeButton($form_id);
    (new \FluentForm\App\Modules\Registerer\Menu($app))->addPreviewButton($form_id);
});

$app->addAction('media_buttons', function () {
    (new \FluentForm\App\Modules\EditorButtonModule())->addButton();
});

$app->addAction('fluentform/addons_page_render_fluentform_add_ons', function () {
    (new \FluentForm\App\Modules\AddOnModule())->showFluentAddOns();
});

// This is temp, we will remove this after 2-3 versions.
add_filter('pre_set_site_transient_update_plugins', function ($updates) {
    if (!empty($updates->response['fluentformpro'])) {
        $updates->response['fluentformpro/fluentformpro.php'] = $updates->response['fluentformpro'];
        unset($updates->response['fluentformpro']);
    }

    return $updates;
}, 999, 1);

$app->addAction('fluentform/global_menu', function () use ($app) {
    $menu = new \FluentForm\App\Modules\Registerer\Menu($app);
    $menu->renderGlobalMenu();
    if ('yes' != get_option('fluentform_scheduled_actions_migrated')) {
        \FluentForm\Database\Migrations\ScheduledActions::migrate();
    }

    $hookName = 'fluentform_do_scheduled_tasks';
    if (!wp_next_scheduled($hookName)) {
        wp_schedule_event(time(), 'ff_every_five_minutes', $hookName);
    }

    $emailReportHookName = 'fluentform_do_email_report_scheduled_tasks';
    if (!wp_next_scheduled($emailReportHookName)) {
        wp_schedule_event(time(), 'daily', $emailReportHookName);
    }
});

$app->addAction('wp_dashboard_setup', function () {
    $roleManager = new \FluentForm\App\Modules\Acl\Acl();

    if (!$roleManager->getCurrentUserCapability()) {
        return;
    }
    wp_add_dashboard_widget('fluentform_stat_widget', __('Fluent Forms Latest Form Submissions', 'fluentform'), function () {
        (new \FluentForm\App\Modules\DashboardWidgetModule())->showStat();
    }, 10, 1);
});

add_action('admin_init', function () {
    $disablePages = [
        'fluent_forms',
        'fluent_forms_transfer',
        'fluent_forms_settings',
        'fluent_forms_add_ons',
        'fluent_forms_docs',
        'fluent_forms_all_entries',
        'msformentries',
        'fluent_forms_payment_entries'
    ];

    $page = wpFluentForm('request')->get('page');

    if ($page && in_array($page, $disablePages)) {
        remove_all_actions('admin_notices');
        (new \FluentForm\App\Modules\Registerer\ReviewQuery())->register();
    }
});

add_action('wp_print_scripts', function () {
    if (is_admin()) {
        if (\FluentForm\App\Helpers\Helper::isFluentAdminPage()) {
            $option = get_option('_fluentform_global_form_settings');
            $isSkip = 'no' == \FluentForm\Framework\Helpers\ArrayHelper::get($option, 'misc.noConflictStatus');

            $isSkip = apply_filters_deprecated(
                'fluentform_skip_no_conflict',
                [
                    $isSkip
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/skip_no_conflict',
                'Use fluentform/skip_no_conflict instead of fluentform_skip_no_conflict.'
            );

            $isSkip = apply_filters('fluentform/skip_no_conflict', $isSkip);

            if ($isSkip) {
                return;
            }

            global $wp_scripts;
            if (!$wp_scripts) {
                return;
            }

            $pluginUrl = plugins_url();
            foreach ($wp_scripts->queue as $script) {
                if (!isset($wp_scripts->registered[$script])) {
                    continue;
                }

                $src = $wp_scripts->registered[$script]->src;
                if (false !== strpos($src, $pluginUrl) && false !== !strpos($src, 'fluentform')) {
                    wp_dequeue_script($wp_scripts->registered[$script]->handle);
                }
            }
        }
    }
}, 1);

$app->addAction('fluentform/loading_editor_assets', function ($form) {
    add_filter('fluentform/editor_init_element_input_name', function ($field) {
        if (empty($field['settings']['label_placement'])) {
            $field['settings']['label_placement'] = '';
        }
        return $field;
    });

    $upgradableCheckInputs = [
        'input_radio',
        'select',
        'select_country',
        'input_checkbox',
    ];
    foreach ($upgradableCheckInputs as $upgradeElement) {
        add_filter('fluentform/editor_init_element_' . $upgradeElement, function ($element) use ($upgradeElement, $form) {
            $element = apply_filters('fluentform/rendering_field_data_' . $upgradeElement, $element, $form);

            if (!\FluentForm\Framework\Helpers\ArrayHelper::get($element, 'settings.advanced_options')) {
                $formattedOptions = [];
                $oldOptions = \FluentForm\Framework\Helpers\ArrayHelper::get($element, 'options', []);
                foreach ($oldOptions as $value => $label) {
                    $formattedOptions[] = [
                        'label'      => $label,
                        'value'      => $value,
                        'calc_value' => '',
                        'image'      => '',
                    ];
                }
                $element['settings']['advanced_options'] = $formattedOptions;
                $element['settings']['enable_image_input'] = false;
                $element['settings']['calc_value_status'] = false;
                unset($element['options']);

                if ('input_radio' == $upgradeElement || 'input_checkbox' == $upgradeElement) {
                    $element['editor_options']['template'] = 'inputCheckable';
                }
            }

            if (!isset($element['settings']['layout_class']) && in_array($upgradeElement, ['input_radio', 'input_checkbox'])) {
                $element['settings']['layout_class'] = '';
            }

            if (!isset($element['settings']['dynamic_default_value'])) {
                $element['settings']['dynamic_default_value'] = '';
            }

            if ('select_country' != $upgradeElement && !isset($element['settings']['randomize_options'])) {
                $element['settings']['randomize_options'] = 'no';
            }

            if ('select' == $upgradeElement && \FluentForm\Framework\Helpers\ArrayHelper::get($element, 'attributes.multiple')) {
                if (empty($element['settings']['max_selection'])) {
                    $element['settings']['max_selection'] = '';
                }
                if (isset($element['settings']['enable_select_2'])) {
                    \FluentForm\Framework\Helpers\ArrayHelper::forget($element, 'settings.enable_select_2');
                }
            }

            if (
                (
                    (
                        'select' == $upgradeElement &&
                        !\FluentForm\Framework\Helpers\ArrayHelper::get($element, 'attributes.multiple')
                    ) ||
                    'select_country' == $upgradeElement
                ) &&
                !isset($element['settings']['enable_select_2'])
            ) {
                $element['settings']['enable_select_2'] = 'no';
            }

            if ('select_country' != $upgradeElement && !isset($element['settings']['values_visible'])) {
                $element['settings']['values_visible'] = false;
            }

            return $element;
        });
    }

    $upgradableFileInputs = [
        'input_file',
        'input_image',
    ];
    foreach ($upgradableFileInputs as $upgradeElement) {
        add_filter('fluentform/editor_init_element_' . $upgradeElement, function ($element) {
            if (!isset($element['settings']['upload_file_location'])) {
                $element['settings']['upload_file_location'] = 'default';
            }
            if (!isset($element['settings']['file_location_type'])) {
                $element['settings']['file_location_type'] = 'follow_global_settings';
            }
            return $element;
        });
    }

    add_filter('fluentform/editor_init_element_gdpr_agreement', function ($element) {
        if (!isset($element['settings']['required_field_message'])) {
            $element['settings']['required_field_message'] = '';
        }
        return $element;
    });

    add_filter('fluentform/editor_init_element_input_text', function ($element) {
        if (!isset($element['attributes']['maxlength'])) {
            $element['attributes']['maxlength'] = '';
        }
        return $element;
    });

    add_filter('fluentform/editor_init_element_textarea', function ($element) {
        if (!isset($element['attributes']['maxlength'])) {
            $element['attributes']['maxlength'] = '';
        }
        return $element;
    });

    add_filter('fluentform/editor_init_element_input_date', function ($item) {
        if (!isset($item['settings']['date_config'])) {
            $item['settings']['date_config'] = '';
        }
        return $item;
    });


    add_filter('fluentform/editor_init_element_container', function ($item) {
        if (!isset($item['settings']['conditional_logics'])) {
            $item['settings']['conditional_logics'] = [];
        }

        if (!isset($item['settings']['container_width'])) {
            $item['settings']['container_width'] = '';
        }

        if (!isset($item['settings']['is_width_auto_calc'])) {
            $item['settings']['is_width_auto_calc'] = true;
        }

        $shouldSetWidth = !empty($item['columns']) && (!isset($item['columns'][0]['width']) || !$item['columns'][0]['width']);

        if ($shouldSetWidth) {
            $perColumn = round(100 / count($item['columns']), 2);

            foreach ($item['columns'] as &$column) {
                $column['width'] = $perColumn;
            }
        }

        return $item;
    });

    add_filter('fluentform/editor_init_element_input_number', function ($item) {
        if (!isset($item['settings']['number_step'])) {
            $item['settings']['number_step'] = '';
        }
        if (!isset($item['settings']['numeric_formatter'])) {
            $item['settings']['numeric_formatter'] = '';
        }
        if (!isset($item['settings']['prefix_label'])) {
            $item['settings']['prefix_label'] = '';
        }
        if (!isset($item['settings']['suffix_label'])) {
            $item['settings']['suffix_label'] = '';
        }
        return $item;
    });

    add_filter('fluentform/editor_init_element_input_email', function ($item) {
        if (!isset($item['settings']['is_unique'])) {
            $item['settings']['is_unique'] = 'no';
        }
        if (!isset($item['settings']['unique_validation_message'])) {
            $item['settings']['unique_validation_message'] = __('Email address need to be unique.', 'fluentform');
        }
        if (!isset($item['settings']['prefix_label'])) {
            $item['settings']['prefix_label'] = '';
        }
        if (!isset($item['settings']['suffix_label'])) {
            $item['settings']['suffix_label'] = '';
        }
        return $item;
    });


    add_filter('fluentform/editor_init_element_input_text', function ($item) {
        if (isset($item['attributes']['data-mask'])) {
            if (!isset($item['settings']['data-mask-reverse'])) {
                $item['settings']['data-mask-reverse'] = 'no';
            }
            if (!isset($item['settings']['data-clear-if-not-match'])) {
                $item['settings']['data-clear-if-not-match'] = 'no';
            }
        } else {
            if (!isset($item['settings']['is_unique'])) {
                $item['settings']['is_unique'] = 'no';
            }
            if (!isset($item['settings']['unique_validation_message'])) {
                $item['settings']['unique_validation_message'] = __('This field value need to be unique.', 'fluentform');
            }
        }

        if (!isset($item['settings']['prefix_label'])) {
            $item['settings']['prefix_label'] = '';
        }
        if (!isset($item['settings']['suffix_label'])) {
            $item['settings']['suffix_label'] = '';
        }
        return $item;
    });

    if ($inputs = \FluentForm\App\Modules\Form\FormFieldsParser::getInputs($form, ['element'])) {
        foreach ($inputs as $input) {
            add_filter('fluentform/editor_init_element_'. $input['element'], function ($field) {
                Helper::resolveValidationRulesGlobalOption($field);
                return $field;
            });
        }
    }

    add_filter('fluentform/editor_init_element_recaptcha', function ($item, $form) {
        $item['attributes']['name'] = 'g-recaptcha-response';
        return $item;
    }, 10, 2);

    add_filter('fluentform/editor_init_element_hcaptcha', function ($item, $form) {
        $item['attributes']['name'] = 'h-captcha-response';
        return $item;
    }, 10, 2);

    add_filter('fluentform/editor_init_element_turnstile', function ($item, $form) {
        $item['attributes']['name'] = 'cf-turnstile-response';
        return $item;
    }, 10, 2);
}, 10);

$app->addAction('fluentform/addons_page_render_fluentform_pdf', function () use ($app) {
    $url = '';
    if (!defined('FLUENTFORM_PDF_VERSION')) {
        $url = wp_nonce_url(
            self_admin_url('update.php?action=install-plugin&plugin=fluentforms-pdf'),
            'install-plugin_fluentforms-pdf'
        );
    }

    $app->view->render('admin.addons.pdf_promo', [
        'public_url'   => fluentFormMix(),
        'install_url'  => $url,
        'is_installed' => defined('FLUENTFORM_PDF_VERSION'),
    ]);
});

$app->addAction('fluentform/installed_by', function ($by) {
    if (is_string($by) && !get_option('_ff_ins_by')) {
        update_option('_ff_ins_by', sanitize_text_field($by), 'no');
    }
});

// from Frontend.php
if (defined('WP_ROCKET_VERSION')) {
    add_filter('rocket_excluded_inline_js_content', function ($lines) {
        $lines[] = 'fluent_form_ff_form_instance';
        $lines[] = 'fluentFormVars';
        $lines[] = 'fluentform_payment';

        return $lines;
    });
}

// from Common.php
add_action('save_post', function ($post_id) use ($app) {

    if (!is_post_type_viewable(get_post_type($post_id))) {
        return;
    }
    
    $post_content = isset($_REQUEST['post_content']) ? $_REQUEST['post_content'] : false;
    if ($post_content && is_string($post_content)) {
        $post_content = wp_kses_post(wp_unslash($post_content));
    } else {
        $post = get_post($post_id);
        $post_content = $post->post_content;
    }

    $shortcodeIds = Helper::getShortCodeIds(
        $post_content,
        'fluentform',
        'id'
    );

    $attributes = ArrayHelper::get($shortcodeIds, 'attributes', []);
    ArrayHelper::forget($shortcodeIds, 'attributes');
    
    $shortcodeModalIds = Helper::getShortCodeIds(
        $post_content,
        'fluentform_modal',
        'form_id'
    );

    $gutenbergIds = Helper::getFormsIdsFromBlocks($post_content);

    if ($shortcodeModalIds) {
        $modalAttributes = ArrayHelper::get($shortcodeModalIds, 'attributes', []);
        ArrayHelper::forget($shortcodeModalIds, 'attributes');

        $shortcodeIds = array_merge($shortcodeIds, $shortcodeModalIds);

        if ($modalAttributes) {
            $attributes = array_merge($attributes, $modalAttributes);
        }
    }

    if ($gutenbergIds) {
        $blockAttributes = ArrayHelper::get($gutenbergIds, 'attributes', []);
        ArrayHelper::forget($gutenbergIds, 'attributes');

        $shortcodeIds = array_merge($shortcodeIds, $gutenbergIds);

        if ($blockAttributes) {
            $attributes = array_merge($attributes, $blockAttributes);
        }
    }

    $shortcodeIds = array_unique($shortcodeIds);

    if ($attributes) {
        $data = [];

        foreach ($attributes as $attribute) {
            $data[$attribute['formId']]['themes'][] = $attribute['theme'];
        }

        $shortcodeIds['attributes'] = $data;
    }

    if ($shortcodeIds) {
        update_post_meta($post_id, '_has_fluentform', $shortcodeIds);
    } elseif (get_post_meta($post_id, '_has_fluentform', true)) {
        update_post_meta($post_id, '_has_fluentform', []);
    }
});

$component = new Component($app);
$component->addRendererActions();
$component->addFluentFormShortCode();
$component->addFluentFormDefaultValueParser();

$component->addFluentformSubmissionInsertedFilter();
$component->addIsRenderableFilter();
$component->registerInputSanitizers();

add_action('wp', function () use ($app) {
    // @todo: We will remove the fluentform_pages check from April 2021
    $fluentFormPages = $app->request->get('fluent_forms_pages') || $app->request->get('fluentform_pages');

    if ($fluentFormPages) {
        add_action('wp_enqueue_scripts', function () use ($app) {
            wp_enqueue_script('jquery');
            wp_enqueue_script(
                'fluent_forms_global',
                fluentFormMix('js/fluent_forms_global.js'),
                ['jquery'],
                FLUENTFORM_VERSION,
                true
            );
            wp_localize_script('fluent_forms_global', 'fluent_forms_global_var', [
                'fluent_forms_admin_nonce' => wp_create_nonce('fluent_forms_admin_nonce'),
                'ajaxurl'                  => admin_url('admin-ajax.php'),
                'global_search_active'     => apply_filters('fluentform/global_search_active', 'yes'),
                'rest'                     => Helper::getRestInfo()
            ]);
            wp_enqueue_style('fluent-form-styles');
            $form = wpFluent()->table('fluentform_forms')->find(intval($app->request->get('preview_id')));

            $loadPublicStyle = apply_filters_deprecated(
                'fluentform_load_default_public',
                [
                    true,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/load_default_public',
                'Use fluentform/load_default_public instead of fluentform_load_default_public.'
            );

            if (apply_filters('fluentform/load_default_public', $loadPublicStyle, $form)) {
                wp_enqueue_style('fluentform-public-default');
            }
            wp_enqueue_script('fluent-form-submission');
            wp_enqueue_style('fluent-form-preview', fluentFormMix('css/preview.css'));
        });

        (new \FluentForm\App\Modules\ProcessExteriorModule())->handleExteriorPages();
    }
}, 1);

// Register api response log hooks
$app->addAction(
    'fluentform/after_submission_api_response_success',
    function ($form, $entryId, $data, $feed, $res, $msg = '') {
        fluentform_after_submission_api_response_success($form, $entryId, $data, $feed, $res, $msg = '');
    },
    10,
    6
);

$app->addAction(
    'fluentform/after_submission_api_response_failed',
    function ($form, $entryId, $data, $feed, $res, $msg = '') {
        fluentform_after_submission_api_response_failed($form, $entryId, $data, $feed, $res, $msg = '');
    },
    10,
    6
);

function fluentform_after_submission_api_response_success($form, $entryId, $data, $feed, $res, $msg = '')
{
    try {
        $isDev = 'production' != wpFluentForm()->getEnv();

        $isDev = apply_filters_deprecated(
            'fluentform_api_success_log',
            [
                $isDev,
                $form,
                $feed
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/api_success_log',
            'Use fluentform/api_success_log instead of fluentform_api_success_log.'
        );

        if (!apply_filters('fluentform/api_success_log', $isDev, $form, $feed)) {
            return;
        }

        wpFluent()->table('fluentform_submission_meta')->insert([
            'response_id' => $entryId,
            'form_id'     => $form->id,
            'meta_key'    => 'api_log',
            'value'       => $msg,
            'name'        => $feed->formattedValue['name'],
            'status'      => 'success',
            'created_at'  => current_time('mysql'),
            'updated_at'  => current_time('mysql'),
        ]);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

function fluentform_after_submission_api_response_failed($form, $entryId, $data, $feed, $res, $msg = '')
{
    try {
        $isDev = 'production' != wpFluentForm()->getEnv();

        $isDev = apply_filters_deprecated(
            'fluentform_api_failed_log',
            [
                $isDev,
                $form,
                $feed
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/api_failed_log',
            'Use fluentform/api_failed_log instead of fluentform_api_failed_log.'
        );

        if (!apply_filters('fluentform/api_failed_log', $isDev, $form, $feed)) {
            return;
        }

        wpFluent()->table('fluentform_submission_meta')->insert([
            'response_id' => $entryId,
            'form_id'     => $form->id,
            'meta_key'    => 'api_log',
            'value'       => json_encode($res),
            'name'        => $feed->formattedValue['name'],
            'status'      => 'failed',
            'created_at'  => current_time('mysql'),
            'updated_at'  => current_time('mysql'),
        ]);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

$app->addAction('fluentform/before_form_render', function ($form, $atts) {
    $theme = ArrayHelper::get($atts, 'theme');
    
    $styles = $theme ? [$theme] : [];

    do_action(
        'fluentform/load_form_assets',
        $form->id,
        $styles
    );
}, 10, 2);

add_action('fluentform/load_form_assets', function ($formId, $styles = []) {
    $formAssetLoader = (new \FluentForm\App\Modules\Form\Settings\FormCssJs());

    $formAssetLoader->addCustomCssJs($formId);

    $notLoadedStyles = [];

    foreach ($styles as $style) {
        if (!did_action('fluent_form/loaded_styler_' . $formId . '_' . $style)) {
            $notLoadedStyles[] = $style;
        }
    }

    // check if already loaded
    if ($notLoadedStyles) {
        $formAssetLoader->addStylerCSS($formId, $notLoadedStyles);
    }
}, 10, 2);

$app->addAction('fluentform/submission_inserted', function ($insertId, $formData, $form) use ($app) {
    $notificationManager = new \FluentForm\App\Hooks\Handlers\GlobalNotificationHandler($app);
    $notificationManager->globalNotify($insertId, $formData, $form);
}, 10, 3);

$app->addAction('fluentform/schedule_feed', function ($queueId) use ($app) {
    $scheduler = $app['fluentFormAsyncRequest'];

    $scheduler->process($queueId);
});

$app->addAction('init', function () use ($app) {
    new \FluentForm\App\Services\Integrations\MailChimp\MailChimpIntegration($app);
});

$app->addAction('fluentform/form_element_start', function ($form) use ($app) {
    $honeyPot = new \FluentForm\App\Modules\Form\HoneyPot($app);
    $honeyPot->renderHoneyPot($form);
});

$app->addAction('fluentform/before_insert_submission', function ($insertData, $requestData, $form) use ($app) {
    $honeyPot = new \FluentForm\App\Modules\Form\HoneyPot($app);
    $honeyPot->verify($insertData, $requestData, $form->id);
}, 9, 3);

add_action('fluentform/log_data', function ($data) use ($app) {
    $dataLogger = new \FluentForm\App\Modules\Logger\DataLogger($app);
    $dataLogger->log($data);
});

// Support for third party plugin who do_action this hook on previous way (before 5.0.0 way)
// In Fluent Forms 5.0.0 'ff_log_data' add_action replaced with action named 'fluentform/log_data'.
// @todo - notify them for updating do_action name 'fluentform/log_data'.
// @todo - We will remove bellow add_action after 2 or more version release latter.
add_action('ff_log_data', function ($data) use ($app) {
    $dataLogger = new \FluentForm\App\Modules\Logger\DataLogger($app);
    $dataLogger->log($data);
});

// widgets
add_action('widgets_init', function () {
    register_widget('FluentForm\App\Modules\Widgets\SidebarWidgets');
});

add_action('wp', function () {
    global $post;

    if (!is_a($post, 'WP_Post')) {
        return;
    }
    
    $fluentFormIds = get_post_meta($post->ID, '_has_fluentform', true);
    $attributes = ArrayHelper::get($fluentFormIds, 'attributes', []);

    if (isset($fluentFormIds['attributes'])) {
        unset($fluentFormIds['attributes']);
    }

    if ($fluentFormIds && is_array($fluentFormIds)) {
        foreach ($fluentFormIds as $formId) {
            do_action(
                'fluentform/load_form_assets',
                $formId,
                array_unique(ArrayHelper::get($attributes, $formId . '.themes', []))
            );
        }
    }
});

add_filter('cron_schedules', function ($schedules) {
    $schedules['ff_every_five_minutes'] = [
        'interval' => 300,
        'display'  => esc_html__('Every 5 minutes (FluentForm)', 'fluentform'),
    ];

    return $schedules;
}, 10, 1);

add_action('fluentform_do_scheduled_tasks', 'fluentFormHandleScheduledTasks');
add_action('fluentform_do_email_report_scheduled_tasks', 'fluentFormHandleScheduledEmailReport');

add_action('fluentform/integration_action_result', function ($feed, $status, $note = '') {
    if (!isset($feed['scheduled_action_id']) || !$status) {
        return;
    }
    if (!$note) {
        $note = $status;
    }

    if (strlen($note) > 255) {
        if (function_exists('mb_substr')) {
            $note = mb_substr($note, 0, 251) . '...';
        } else {
            $note = substr($note, 0, 251) . '...';
        }
    }

    $actionId = intval($feed['scheduled_action_id']);
    wpFluent()->table('ff_scheduled_actions')
        ->where('id', $actionId)
        ->update([
            'status'     => $status,
            'note'       => $note,
            'updated_at' => current_time('mysql'),
        ]);
}, 10, 3);


// Support for third party plugin who do_action this hook on previous way (before 5.0.0 way)
// In Fluent Forms 5.0.0 'ff_integration_action_result' add_action replaced in above action named 'fluentform/integration_action_result'.
// @todo - notify them for updating do_action name 'fluentform/integration_action_result'.
// @todo - We will remove bellow add_action after 2 or more version release latter.
add_action('ff_integration_action_result', function ($feed, $status, $note = '') {
    if (!isset($feed['scheduled_action_id']) || !$status) {
        return;
    }
    if (!$note) {
        $note = $status;
    }

    if (strlen($note) > 255) {
        if (function_exists('mb_substr')) {
            $note = mb_substr($note, 0, 251) . '...';
        } else {
            $note = substr($note, 0, 251) . '...';
        }
    }

    $actionId = intval($feed['scheduled_action_id']);
    wpFluent()->table('ff_scheduled_actions')
        ->where('id', $actionId)
        ->update([
            'status' => $status,
            'note'   => $note,
        ]);
}, 10, 3);

add_action('fluentform/global_notify_completed', function ($insertId, $form) use ($app) {
    $isTruncate = apply_filters_deprecated(
        'fluentform_truncate_password_values',
        [
            true,
            $form->id
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/truncate_password_values',
        'Use fluentform/truncate_password_values instead of fluentform_truncate_password_values.'
    );

    if (strpos($form->form_fields, '"element":"input_password"') && apply_filters('fluentform/truncate_password_values', $isTruncate, $form->id)) {
        // we have password
        (new \FluentForm\App\Services\Integrations\GlobalNotificationService())->cleanUpPassword($insertId, $form);
    }
}, 10, 2);

/*
 * Elementor Block Init
 */

if (defined('ELEMENTOR_VERSION')) {
    new \FluentForm\App\Modules\Widgets\ElementorWidget($app);
}
/*
 * Oxygen Widget Init
 */

add_action('init', function () {
    if (class_exists('OxyEl')) {
        if (file_exists(FLUENTFORM_DIR_PATH . 'app/Modules/Widgets/OxygenWidget.php')) {
            new FluentForm\App\Modules\Widgets\OxygenWidget();
        }
    }
});

(new FluentForm\App\Services\Integrations\Slack\SlackNotificationActions($app))->register();

/*
 * Smartcode parser shortcodes
 */

new \FluentForm\App\Services\FormBuilder\Components\CustomSubmitButton();

add_action('enqueue_block_editor_assets', function () {
    
    wp_enqueue_script(
        'fluentform-gutenberg-block',
        fluentFormMix('js/fluent_gutenblock.js'),
        ['wp-element', 'wp-polyfill', 'wp-i18n', 'wp-blocks', 'wp-components','wp-server-side-render', 'wp-block-editor'],
        FLUENTFORM_VERSION
    );
    
    
    $forms = wpFluent()->table('fluentform_forms')
        ->select(['id', 'title'])
        ->orderBy('id', 'DESC')
        ->get();
    
    array_unshift($forms, (object) [
        'id'    => '',
        'title' => __('-- Select a form --', 'fluentform'),
    ]);

    $presets = [
        [
            'label' => __('Default (Form Styler)', 'fluentform'),
            'value' => '',
        ],
        [
            'label' => __('Inherit Theme Style', 'fluentform'),
            'value' => 'ffs_inherit_theme'
        ],
    ];

    $presets = apply_filters('fluentform/block_editor_style_presets', $presets);
   
    wp_localize_script('fluentform-gutenberg-block', 'fluentform_block_vars', [
        'logo'                    => fluentFormMix('img/fluent_icon.png'),
        'forms'                   => $forms,
        'style_presets'           => $presets,
        'theme_style'             => apply_filters('fluentform/load_theme_style', false) ? 'ffs_inherit_theme' : '',
        'conversational_demo_img' => fluentformMix('img/conversational-form-demo.png'),
        'rest'                    => Helper::getRestInfo()
    ]);
    
    wp_enqueue_style(
        'fluentform-gutenberg-block',
        fluentFormMix('css/fluent_gutenblock.css'),
        ['wp-edit-blocks']
    );
    $fluentFormPublicCss = fluentFormMix('css/fluent-forms-public.css');
    $fluentFormPublicDefaultCss = fluentFormMix('css/fluentform-public-default.css');
    
    if (is_rtl()) {
        $fluentFormPublicCss = fluentFormMix('css/fluent-forms-public-rtl.css');
        $fluentFormPublicDefaultCss = fluentFormMix('css/fluentform-public-default-rtl.css');
    }
    
    wp_enqueue_style(
        'fluent-form-styles',
        $fluentFormPublicCss,
        [],
        FLUENTFORM_VERSION
    );
    
    wp_enqueue_style(
        'fluentform-public-default',
        $fluentFormPublicDefaultCss,
        [],
        FLUENTFORM_VERSION
    );
});


if (function_exists('register_block_type')) {
    add_action('init', function () {
        register_block_type('fluentfom/guten-block', [
            'render_callback' => function ($atts) {
                $formId = ArrayHelper::get($atts, 'formId');
                
                if (empty($formId)) {
                    return '';
                }
                
                $className = ArrayHelper::get($atts, 'className');
                
                if ($className) {
                    $classes = explode(' ', $className);
                    $className = '';
                    if (!empty($classes)) {
                        foreach ($classes as $class) {
                            $className .= sanitize_html_class($class) . ' ';
                        }
                    }
                }
    
                $themeStyle = sanitize_text_field(ArrayHelper::get($atts, 'themeStyle'));
                
                $type = Helper::isConversionForm($formId) ? 'conversational' : '';
                
                return do_shortcode('[fluentform theme="'. $themeStyle .'" css_classes="' . $className . ' ff_guten_block" id="' . $formId . '"  type="' . $type . '"]');
            },
            'attributes'      => [
                'formId'               => [
                    'type' => 'string',
                ],
                'className'            => [
                    'type' => 'string',
                ],
                'themeStyle'           => [
                    'type'    => 'string',
                ],
                'isConversationalForm' => [
                    'type'    => 'boolean',
                    'default' => false,
                ],
                'isThemeChange'        => [
                    'type'    => 'boolean',
                    'default' => false,
                ],
            ],
        ]);
    });
}

// require the CLI
if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('fluentform', '\FluentForm\App\Modules\CLI\Commands');
}
