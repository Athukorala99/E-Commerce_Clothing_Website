<?php

namespace FluentForm\App\Modules\Registerer;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Hooks\Handlers\ActivationHandler;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Modules\AddOnModule;
use FluentForm\App\Modules\DocumentationModule;
use FluentForm\App\Services\FluentConversational\Classes\Converter\Converter;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;

class Menu
{
    /**
     * App instance
     *
     * @var \FluentForm\Framework\Foundation\Application
     */
    protected $app;

    /**
     * Menu constructor.
     *
     * @param \FluentForm\Framework\Foundation\Application $application
     */
    public function __construct(Application $application)
    {
        $this->app = $application;

        $this->app->addFilter('fluentform/form_settings_menu', [
            $this, 'filterFormSettingsMenu',
        ], 10, 2);
    }

    public function reisterScripts()
    {
        if (!$this->isFluentPages()) {
            return;
        }

        $app = $this->app;

        wp_register_script(
            'fluent_forms_global',
            fluentFormMix('js/fluent_forms_global.js'),
            ['jquery'],
            FLUENTFORM_VERSION,
            true
        );

        $settingsGlobalStyle = fluentFormMix('css/settings_global.css');
        $allFormsStyle = fluentFormMix('css/fluent-all-forms.css');
        $fluentFormAdminEditorStyles = fluentFormMix('css/fluent-forms-admin-sass.css');
        $fluentFormAdminCSS = fluentFormMix('css/fluent-forms-admin.css');
        $addOnsCss = fluentFormMix('css/add-ons.css');
        $adminDocCss = fluentFormMix('css/admin_docs.css');
        if (is_rtl()) {
            $settingsGlobalStyle = fluentFormMix('css/settings_global_rtl.css');
            $allFormsStyle = fluentFormMix('css/fluent-all-forms-rtl.css');
            $fluentFormAdminEditorStyles = fluentFormMix('css/fluent-forms-admin-sass-rtl.css');
            $fluentFormAdminCSS = fluentFormMix('css/fluent-forms-admin-rtl.css');
            $addOnsCss = fluentFormMix('css/add-ons-rtl.css');
            $adminDocCss = fluentFormMix('css/admin_docs_rtl.css');
        }

        wp_register_style(
            'fluentform_settings_global',
            $settingsGlobalStyle,
            [],
            FLUENTFORM_VERSION,
            'all'
        );

        wp_register_script(
            'clipboard',
            fluentFormMix('libs/clipboard.min.js'),
            [],
            false,
            true
        );

        wp_register_script(
            'copier',
            fluentFormMix('js/copier.js'),
            [],
            false,
            true
        );

        wp_register_script(
            'fluentform_form_settings',
            fluentFormMix('js/form_settings_app.js'),
            ['jquery'],
            FLUENTFORM_VERSION,
            true
        );

        wp_register_script(
            'fluent_all_forms',
            fluentFormMix('js/fluent-all-forms-admin.js'),
            ['jquery'],
            FLUENTFORM_VERSION,
            true
        );

        wp_register_style(
            'fluent_all_forms',
            $allFormsStyle,
            [],
            FLUENTFORM_VERSION,
            'all'
        );

        wp_register_script(
            'fluentform_editor_script',
            fluentFormMix('js/fluent-forms-editor.js'),
            ['jquery'],
            FLUENTFORM_VERSION,
            true
        );

        wp_register_style(
            'fluentform_editor_style',
            $fluentFormAdminEditorStyles,
            [],
            FLUENTFORM_VERSION,
            'all'
        );

        wp_register_style(
            'fluentform_editor_sass',
            $fluentFormAdminCSS,
            [],
            FLUENTFORM_VERSION,
            'all'
        );

        wp_register_script(
            'fluentform-transfer-js',
            fluentFormMix('js/fluentform-transfer.js'),
            ['jquery'],
            FLUENTFORM_VERSION,
            true
        );

        wp_register_script(
            'fluentform-global-settings-js',
            fluentFormMix('js/fluentform-global-settings.js'),
            ['jquery'],
            FLUENTFORM_VERSION,
            true
        );

        wp_register_script(
            'fluentform-modules',
            fluentFormMix('js/modules.js'),
            ['jquery'],
            FLUENTFORM_VERSION,
            true
        );

        wp_register_script(
            'fluentform-docs',
            fluentFormMix('js/docs.js'),
            ['jquery'],
            FLUENTFORM_VERSION,
            true
        );

        wp_register_script(
            'fluentform_form_entries',
            fluentFormMix('js/form_entries.js'),
            ['jquery', 'fluentform_chart_js', 'fluentform_vue_chart_js'],
            FLUENTFORM_VERSION,
            true
        );

        wp_register_script(
            'fluentform_all_entries',
            fluentFormMix('js/all_entries.js'),
            ['jquery', 'fluentform_chart_js', 'fluentform_vue_chart_js'],
            FLUENTFORM_VERSION,
            true
        );

        wp_register_script(
            'fluentform_chart_js',
            fluentFormMix('libs/chartjs/chart.min.js'),
            [],
            FLUENTFORM_VERSION,
            true
        );

        wp_register_script(
            'fluentform_vue_chart_js',
            fluentFormMix('libs/chartjs/vue-chartjs.min.js'),
            [],
            FLUENTFORM_VERSION,
            true
        );

        wp_register_style(
            'fluentform-add-ons',
            $addOnsCss,
            [],
            FLUENTFORM_VERSION,
            'all'
        );

        wp_register_style(
            'fluentform_doc_style',
            $adminDocCss,
            [],
            FLUENTFORM_VERSION,
            'all'
        );
    
        add_filter('admin_footer_text', function ($text) {
            return '<span id="footer-thankyou">If you like the plugin please rate Fluent Forms <a target="_blank" rel="nofollow" href="https://wordpress.org/support/plugin/fluentform/reviews/#new-post">★★★★★ </a> on  <a target="_blank" rel="nofollow" href="https://wordpress.org/support/plugin/fluentform/reviews/#new-post">WordPress.org</a> to help us spread the word ♥ from the Fluent Forms team. </span>';
        });

        add_filter('update_footer', function ($text) {
            $footerContent = 'Version ' . FLUENTFORM_VERSION;
            if (defined('FLUENTFORMPRO')) {
                $footerContent .= ' & Pro ' . FLUENTFORMPRO_VERSION;
            }
            return $footerContent;
        });

        $elementUIStyle = fluentFormMix('css/element-ui-css.css');
        if (is_rtl()) {
            $elementUIStyle = fluentFormMix('css/element-ui-css-rtl.css');
        }
        wp_enqueue_style(
            'fluentform_global_elements',
            $elementUIStyle,
            [],
            FLUENTFORM_VERSION,
            'all'
        );
    }

    public function isFluentPages()
    {
        return Helper::isFluentAdminPage();
    }

    public function enqueuePageScripts()
    {
        if (!$this->isFluentPages()) {
            return;
        }

        wp_enqueue_script('fluent_forms_global');
        wp_localize_script('fluent_forms_global', 'fluent_forms_global_var', [
            'fluent_forms_admin_nonce' => wp_create_nonce('fluent_forms_admin_nonce'),
            'ajaxurl'                  => admin_url('admin-ajax.php'),
            'admin_i18n'               => TranslationString::getAdminI18n(),
            'global_search_active'     => apply_filters('fluentform/global_search_active', 'yes'),
            'payments_str'             => TranslationString::getPaymentsI18n(),
            'permissions'              => Acl::getCurrentUserPermissions(),
            'rest'                     => Helper::getRestInfo(),
            'card_brands'              => [
                'visa'                 => fluentformMix('img/card-brand/visa.jpg'),
                'paypal'               => fluentformMix('img/card-brand/paypal.jpg'),
                'mastercard'           => fluentformMix('img/card-brand/mastercard.jpg'),
                'amex'                 => fluentformMix('img/card-brand/amex.jpg')
            ],
            'payment_icons'            => [
                'offline'              => fluentformMix('img/payment/offline.png'),
                'mollie'               => fluentformMix('img/payment/mollie.png'),
                'paypal'               => fluentformMix('img/payment/paypal.png'),
                'stripe'               => fluentformMix('img/payment/stripe.png')
            ]
        ]);

        $page = sanitize_text_field($this->app->request->get('page'));
        $route = sanitize_text_field($this->app->request->get('route'));
        $formId = intval($this->app->request->get('form_id'));

        if ('fluent_forms' == $page && $route && $formId) {
            if (true) {
                wp_enqueue_style('fluentform_settings_global');
                wp_enqueue_script('clipboard');
                wp_enqueue_script('copier');

                if (Acl::hasPermission('fluentform_forms_manager')) {
                    if ('settings' == $route) {
                        if (function_exists('wp_enqueue_editor')) {
                            add_filter('user_can_richedit', function ($status) {
                                return true;
                            });

                            wp_enqueue_editor();
                            wp_enqueue_media();
                        }

                        wp_enqueue_script('fluentform_form_settings');
                    } elseif ('editor' == $route) {
                        $this->enqueueEditorAssets();
                    }
                }
            }
        } elseif ('fluent_forms' == $page) {
            wp_enqueue_script('fluent_all_forms');
            wp_enqueue_style('fluent_all_forms');
        }elseif ('fluent_forms_transfer' == $page) {
            wp_enqueue_style('fluentform_settings_global');
            wp_enqueue_script('fluentform-transfer-js');
        } elseif (
            'fluent_forms_settings' == $page ||
            'fluent_forms_payment_entries' == $page ||
            'fluent_forms_all_entries' == $page
        ) {
            wp_enqueue_style('fluentform_settings_global');
        } elseif ('fluent_forms_add_ons' == $page) {
            wp_enqueue_style('fluentform-add-ons');
        } elseif ('fluent_forms_docs' == $page || 'fluent_forms_smtp' == $page) {
            wp_enqueue_style('fluentform_doc_style');
        }
    }

    /**
     * Register menu and sub-menus.
     */
    public function register()
    {
        $dashBoardCapability = apply_filters_deprecated(
            'fluentform_dashboard_capability',
            [
                'fluentform_dashboard_access'
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/dashboard_capability',
            'Use fluentform/dashboard_capability instead of fluentform_dashboard_capability.'
        );

        $dashBoardCapability = $this->app->applyFilters(
            'fluentform/dashboard_capability',
            $dashBoardCapability
        );
    
        $settingManager = apply_filters_deprecated(
            'fluentform_settings_capability',
            [
                'fluentform_settings_manager'
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/settings_capability',
            'Use fluentform/settings_capability instead of fluentform_settings_capability.'
        );

        $settingsCapability = $this->app->applyFilters(
            'fluentform/settings_capability',
            $settingManager
        );

        $fromRole = false;
        if (!current_user_can($dashBoardCapability) && !current_user_can($settingsCapability)) {
            $currentUserCapability = Acl::getCurrentUserCapability();

            if (!$currentUserCapability) {
                return;
            } else {
                $fromRole = true;
                $dashBoardCapability = $settingsCapability = $currentUserCapability;
            }
        }

        if (Acl::isSuperMan()) {
            $fromRole = true;
        }

        if (defined('FLUENTFORMPRO')) {
            $title = __('Fluent Forms Pro', 'fluentform');
        } else {
            $title = __('Fluent Forms', 'fluentform');
        }

        $menuPriority = 25;

        if (defined('FLUENTCRM')) {
            $menuPriority = 3;
        }

        add_menu_page(
            $title,
            $title,
            $dashBoardCapability,
            'fluent_forms',
            [$this, 'renderFormAdminRoute'],
            $this->getMenuIcon(),
            $menuPriority
        );

        add_submenu_page(
            'fluent_forms',
            __('Forms', 'fluentform'),
            __('Forms', 'fluentform'),
            $dashBoardCapability,
            'fluent_forms',
            [$this, 'renderFormAdminRoute']
        );

        if ($settingsCapability) {
            add_submenu_page(
                'fluent_forms',
                __('New Form', 'fluentform'),
                __('New Form', 'fluentform'),
                $fromRole ? $settingsCapability : 'fluentform_forms_manager',
                'fluent_forms#add=1',
                [$this, 'renderAddNewFormRoute']
            );

            $entriesTitle = __('Entries', 'fluentform');

            if (Helper::isFluentAdminPage()) {
                $entriesCount = wpFluent()->table('fluentform_submissions')
                    ->where('status', 'unread')
                    ->count();

                if ($entriesCount) {
                    $entriesTitle .= ' <span class="ff_unread_count" style="background: #197efb;color: white;border-radius: 8px;padding: 1px 8px;">' . $entriesCount . '</span>';
                }
            }

            // Register entries intermediary page
            add_submenu_page(
                'fluent_forms',
                $entriesTitle,
                $entriesTitle,
                $fromRole ? $settingsCapability : 'fluentform_entries_viewer',
                'fluent_forms_all_entries',
                [$this, 'renderAllEntriesAdminRoute']
            );

            $isShowPaymentSubmission = apply_filters_deprecated(
                'fluentform_show_payment_entries',
                [
                    false
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/show_payment_entries',
                'Use fluentform/show_payment_entries instead of fluentform_show_payment_entries.'
            );

            if (apply_filters('fluentform/show_payment_entries', $isShowPaymentSubmission)) {
                add_submenu_page(
                    'fluent_forms',
                    __('Payments', 'fluentform'),
                    __('Payments', 'fluentform'),
                    $fromRole ? $settingsCapability : 'fluentform_view_payments',
                    'fluent_forms_payment_entries',
                    [$this, 'renderPaymentEntries']
                );
            }

            // Register global settings sub menu page.
            add_submenu_page(
                'fluent_forms',
                __('Global Settings', 'fluentform'),
                __('Global Settings', 'fluentform'),
                $fromRole ? $settingsCapability : 'fluentform_settings_manager',
                'fluent_forms_settings',
                [$this, 'renderGlobalSettings']
            );

            // Register import/export sub menu page.
            add_submenu_page(
                'fluent_forms',
                __('Tools', 'fluentform'),
                __('Tools', 'fluentform'),
                $fromRole ? $settingsCapability : 'fluentform_settings_manager',
                'fluent_forms_transfer',
                [$this, 'renderTransfer']
            );

            // Register FluentSMTP Sub Menu.
            add_submenu_page(
                'fluent_forms',
                __('SMTP', 'fluentform'),
                __('SMTP', 'fluentform'),
                $fromRole ? $settingsCapability : 'fluentform_settings_manager',
                'fluent_forms_smtp',
                [$this, 'renderSmtpPromo']
            );

            // Register Add-Ons
            add_submenu_page(
                'fluent_forms',
                __('Integrations', 'fluentform'),
                __('Integrations', 'fluentform'),
                $fromRole ? $settingsCapability : 'fluentform_settings_manager',
                'fluent_forms_add_ons',
                [$this, 'renderAddOns']
            );
        }

        // Register Documentation
        add_submenu_page(
            'fluent_forms',
            __('Support', 'fluentform'),
            __('Support', 'fluentform'),
            $dashBoardCapability,
            'fluent_forms_docs',
            [$this, 'renderDocs']
        );

        $this->commonAction();
    }

    private function getMenuIcon()
    {
        return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><defs><style>.cls-1{fill:#fff;}</style></defs><title>dashboard_icon</title><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path class="cls-1" d="M15.57,0H4.43A4.43,4.43,0,0,0,0,4.43V15.57A4.43,4.43,0,0,0,4.43,20H15.57A4.43,4.43,0,0,0,20,15.57V4.43A4.43,4.43,0,0,0,15.57,0ZM12.82,14a2.36,2.36,0,0,1-1.66.68H6.5A2.31,2.31,0,0,1,7.18,13a2.36,2.36,0,0,1,1.66-.68l4.66,0A2.34,2.34,0,0,1,12.82,14Zm3.3-3.46a2.36,2.36,0,0,1-1.66.68H3.21a2.25,2.25,0,0,1,.68-1.64,2.36,2.36,0,0,1,1.66-.68H16.79A2.25,2.25,0,0,1,16.12,10.53Zm0-3.73a2.36,2.36,0,0,1-1.66.68H3.21a2.25,2.25,0,0,1,.68-1.64,2.36,2.36,0,0,1,1.66-.68H16.79A2.25,2.25,0,0,1,16.12,6.81Z"/></g></g></svg>');
    }

    public function renderFormAdminRoute()
    {
        $formId = intval($this->app->request->get('form_id'));
        $route = sanitize_key($this->app->request->get('route'));

        if ($route && $formId) {
            $formRoutePermissionSet = apply_filters('fluentform/form_inner_route_permission_set', [
                'editor'   => 'fluentform_forms_manager',
                'settings' => 'fluentform_forms_manager',
                'entries'  => 'fluentform_entries_viewer',
            ]);

            $toVerifyPermission = ArrayHelper::get(
                $formRoutePermissionSet,
                $route,
                'fluentform_forms_manager'
            );

            $hasPermission = apply_filters(
                'fluentform/inner_route_has_permission',
                Acl::hasPermission($toVerifyPermission),
                $route,
                $formId
            );

            if ($hasPermission) {
                return $this->renderFormInnerPages();
            } else {
                return $this->app->view->render('admin.no_permission');
            }
        }

        $this->renderForms();
    }

    public function renderAllEntriesAdminRoute()
    {
        wp_enqueue_script('fluentform_all_entries');
        $this->app->view->render('admin.all_entries', []);
    }

    public function renderFormInnerPages()
    {
        $form_id = intval($this->app->request->get('form_id'));

        $form = wpFluent()->table('fluentform_forms')->find($form_id);

        if (!$form) {
            echo __('<h2>No form found</h2>', 'fluentform');
            return;
        }

        $formAdminMenus = [];

        if (Acl::hasPermission('fluentform_forms_manager')) {
            $formAdminMenus = [
                'editor' => [
                    'slug'  => 'editor',
                    'title' => __('Editor', 'fluentform'),
                    'url'   => admin_url('admin.php?page=fluent_forms&form_id=' . $form_id . '&route=editor'),
                ],
                'settings' => [
                    'slug'      => 'settings',
                    'hash'      => 'basic_settings',
                    'title'     => __('Settings & Integrations', 'fluentform'),
                    'sub_route' => 'form_settings',
                    'url'       => admin_url('admin.php?page=fluent_forms&form_id=' . $form_id . '&route=settings&sub_route=form_settings'),
                ],
            ];
        }

        if (Acl::hasPermission('fluentform_entries_viewer')) {
            $formAdminMenus['entries'] = [
                'slug'  => 'entries',
                'hash'  => '/',
                'title' => __('Entries', 'fluentform'),
                'url'   => admin_url('admin.php?page=fluent_forms&form_id=' . $form_id . '&route=entries'),
            ];
        }

        $formAdminMenus = apply_filters_deprecated(
            'fluentform_form_admin_menu',
            [
                $formAdminMenus,
                $form_id,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_admin_menu',
            'Use fluentform/form_admin_menu instead of fluentform_form_admin_menu.'
        );

        $formAdminMenus = apply_filters('fluentform/form_admin_menu', $formAdminMenus, $form_id, $form);

        $route = sanitize_key($this->app->request->get('route'));

        $this->app->view->render('admin.form.form_wrapper', [
            'route'      => $route,
            'form_id'    => $form_id,
            'form'       => $form,
            'menu_items' => $formAdminMenus,
        ]);
    }

    public function renderSettings($form_id)
    {
        $settingsMenus = [
            'form_settings' => [
                'title' => __('Form Settings', 'fluentform'),
                'slug'  => 'form_settings',
                'hash'  => 'basic_settings',
                'route' => '/',
            ],
            'email_notifications' => [
                'title' => __('Email Notifications', 'fluentform'),
                'slug'  => 'form_settings',
                'hash'  => 'email_notifications',
                'route' => '/email-settings',
            ],
            'conditional_confirmations' => [
                'title' => __('Conditional Confirmations', 'fluentform'),
                'slug'  => 'form_settings',
                'hash'  => 'conditional_confirmations',
                'route' => '/conditional-confirmations',
            ],
            'all_integrations' => [
                'title' => __('Configure Integrations', 'fluentform'),
                'slug'  => 'form_settings',
                'route' => '/all-integrations',
            ],
        ];

        if (Helper::isSlackEnabled()) {
            $settingsMenus['slack'] = [
                'title' => __('Slack', 'fluentform'),
                'slug'  => 'form_settings',
                'hash'  => 'slack',
                'route' => '/slack',
            ];
        }

        $settingsMenus = apply_filters_deprecated('fluentform_form_settings_menu', [
                $settingsMenus,
                $form_id
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_settings_menu',
            'Use fluentform/form_settings_menu instead of fluentform_form_settings_menu.'
        );

        $settingsMenus = apply_filters('fluentform/form_settings_menu', $settingsMenus, $form_id);

        $externalMenuItems = [];
        foreach ($settingsMenus as $key => $menu) {
            if (empty($menu['hash'])) {
                unset($settingsMenus[$key]);
                $externalMenuItems[$key] = $menu;
            }
        }

        $settingsMenus['custom_css_js'] = [
            'title' => __('Custom CSS/JS', 'fluentform'),
            'slug'  => 'form_settings',
            'hash'  => 'custom_css_js',
            'route' => '/custom-css-js',
        ];

        $settingsMenus = array_filter(array_merge($settingsMenus, $externalMenuItems));

        $currentRoute = sanitize_key($this->app->request->get('sub_route', ''));

        $this->app->view->render('admin.form.settings_wrapper', [
            'form_id'           => $form_id,
            'settings_menus'    => $settingsMenus,
            'current_sub_route' => $currentRoute,
        ]);
    }

    /**
     * Remove the inactive addOn menu items
     *
     * @param string $addOn
     *
     * @return boolean
     */
    public function filterFormSettingsMenu($settingsMenus, $form_id)
    {
        if (array_key_exists('mailchimp_integration', $settingsMenus)) {
            $option = (array) get_option('_fluentform_mailchimp_details');
            if (!isset($option['status']) || !$option['status']) {
                unset($settingsMenus['mailchimp_integration']);
            }
        }

        return $settingsMenus;
    }

    public function renderFormSettings($form_id)
    {
        wp_localize_script('fluentform_form_settings', 'FluentFormApp', [
            'form_id'              => $form_id,
            'plugin'               => $this->app->config->get('app.slug'),
            'hasPro'               => defined('FLUENTFORMPRO'),
            'hasPDF'               => defined('FLUENTFORM_PDF_VERSION'),
            'hasFluentCRM'         => defined('FLUENTCRM'),
            'upgrade_url'          => fluentform_upgrade_url(),
            'ace_path_url'         => fluentformMix('libs/ace'),
            'is_conversion_form'   => Helper::isConversionForm($form_id),
            'has_fluent_smtp'      => defined('FLUENTMAIL'),
            'fluent_smtp_url'      => admin_url('admin.php?page=fluent_forms_smtp'),
            'form_settings_str'    => TranslationString::getSettingsI18n(),
            'integrationsResource' => [
                'asset_url'   => fluentformMix('img/integrations.png'),
                'list_url'    => fluentform_integrations_url(),
                'instruction' => __("Fluent Forms Pro has tons of integrations to take your forms to the next level. From payment gateways to quiz building, SMS notifications to email marketing - you'll get integrations for various purposes. Even if you don't find your favorite tools, you can integrate them easily with Zapier.", 'fluentform'),
            ],
            'countries'            => getFluentFormCountryList(),
            'getIpInfo'            => Helper::getIpinfo(),
        ]);

        $this->app->view->render('admin.form.settings', [
            'form_id' => $form_id,
        ]);
    }

    public function renderForms()
    {
        if (!get_option('_fluentform_installed_version')) {
            (new ActivationHandler())->migrate();
        }

        $formsCount = wpFluent()->table('fluentform_forms')->count();

        $isDisabledAnalytics = apply_filters_deprecated(
            'fluentform-disabled_analytics',
            [
                false
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/disabled_analytics',
            'Use fluentform/disabled_analytics instead of fluentform-disabled_analytics.'
        );
    
        $data = [
            'plugin'                  => $this->app->config->get('app.slug'),
            'formsCount'              => $formsCount,
            'hasPro'                  => defined('FLUENTFORMPRO'),
            'upgrade_url'             => fluentform_upgrade_url(),
            'adminUrl'                => admin_url('admin.php?page=fluent_forms'),
            'adminUrlWithoutPageHash' => admin_url('admin.php'),
            'isDisableAnalytics'      => $this->app->applyFilters('fluentform/disabled_analytics', $isDisabledAnalytics),
            'plugin_public_url'       => fluentformMix(),
        ];

        $data = apply_filters_deprecated(
            'fluent_all_forms_vars',
            [
                $data
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/all_forms_vars',
            'Use fluentform/all_forms_vars instead of fluent_all_forms_vars.'
        );

        wp_localize_script('fluent_all_forms', 'FluentFormApp', apply_filters('fluentform/all_forms_vars', $data));

        $this->app->view->render('admin.all_forms', []);
    }

    public function renderEditor($form_id)
    {
        $this->app->view->render('admin.form.editor', [
            'plugin'  => $this->app->config->get('app.slug'),
            'form_id' => $form_id,
        ]);
    }

    public function renderDocs()
    {
        (new DocumentationModule())->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --
    }

    public function renderAddOns()
    {
        (new AddOnModule())->render();
    }

    private function enqueueEditorAssets()
    {
        $formId = (int) $this->app->request->get('form_id');

        $form = wpFluent()->table('fluentform_forms')->find($formId);

        do_action_deprecated(
            'fluentform_loading_editor_assets',
            [
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/loading_editor_assets',
            'Use fluentform/loading_editor_assets instead of fluentform_loading_editor_assets'
        );

        do_action('fluentform/loading_editor_assets', $form);

        wp_enqueue_style('fluentform_editor_sass');
        wp_enqueue_style('fluentform_editor_style');

        $pluginSlug = $this->app->config->get('app.slug');

        if (function_exists('wp_enqueue_editor')) {
            add_filter('user_can_richedit', '__return_true');
            wp_enqueue_editor();
            wp_enqueue_media();
        }

        wp_enqueue_script('fluentform_editor_script');

        $jsonData = $form->form_fields;

        if (!defined('FLUENTFORM_DISABLE_EDITOR_FILED_HOOOK')) {
            $formFields = $form->form_fields;

            if ($formFields) {
                $formFields = json_decode($formFields, true);

                foreach ($formFields['fields'] as $index => $formField) {
                    $formField = apply_filters_deprecated(
                        'fluentform_editor_init_element_' . $formField['element'],
                        [
                            $formField,
                            $form
                        ],
                        FLUENTFORM_FRAMEWORK_UPGRADE,
                        'fluentform/editor_init_element_' . $formField['element'],
                        'Use fluentform/editor_init_element_' . $formField['element'] . ' instead of fluentform_editor_init_element_' . $formField['element']
                    );

                    $formField = $formFields['fields'][$index] = apply_filters(
                        'fluentform/editor_init_element_' . $formField['element'],
                        $formField,
                        $form
                    );

                    if (!$formFields['fields'][$index]) {
                        unset($formFields['fields'][$index]);
                        continue;
                    }

                    if ('container' == $formField['element']) {
                        $columns = $formField['columns'];
                        foreach ($columns as $columnIndex => $column) {
                            foreach ($column['fields'] as $fieldIndex => $columnField) {
                                $columnField = apply_filters_deprecated(
                                    'fluentform_editor_init_element_' . $columnField['element'],
                                    [
                                        $columnField,
                                        $form
                                    ],
                                    FLUENTFORM_FRAMEWORK_UPGRADE,
                                    'fluentform/editor_init_element_' . $columnField['element'],
                                    'Use fluentform/editor_init_element_' . $columnField['element'] . ' instead of fluentform_editor_init_element_' . $columnField['element']
                                );

                                $columns[$columnIndex]['fields'][$fieldIndex] = apply_filters(
                                    'fluentform/editor_init_element_' . $columnField['element'],
                                    $columnField,
                                    $form
                                );

                                if (!$columns[$columnIndex]['fields'][$fieldIndex]) {
                                    unset($columns[$columnIndex]['fields'][$fieldIndex]);
                                }
                            }
                        }

                        $formFields['fields'][$index]['columns'] = array_values($columns);
                    }
                }

                $formFields['fields'] = array_values($formFields['fields']);
                $formFields = json_encode($formFields, true);
            }

            $form->form_fields = $formFields;
        }

        $searchTags = fluentformLoadFile('Services/FormBuilder/ElementSearchTags.php');
    
        $searchTags = apply_filters_deprecated(
            'fluentform_editor_element_search_tags',
            [
                $searchTags
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/editor_element_search_tags',
            'Use fluentform/editor_element_search_tags instead of fluent_editor_element_search_tags.'
        );
        $searchTags = apply_filters('fluentform/editor_element_search_tags', $searchTags, $form);
    
        $elementPlacements = fluentformLoadFile('Services/FormBuilder/ElementSettingsPlacement.php');
        $elementPlacements = apply_filters_deprecated(
            'fluentform_editor_element_settings_placement',
            [
                $elementPlacements,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/editor_element_settings_placement',
            'Use fluentform/editor_element_settings_placement instead of fluent_editor_element_settings_placement.'
        );

        $elementPlacements = apply_filters(
            'fluentform/editor_element_settings_placement',
            $elementPlacements,
            $form
        );
        $data = [
            'plugin'                         => $pluginSlug,
            'form_id'                        => $formId,
            'plugin_public_url'              => fluentformMix(),
            'preview_url'                    => Helper::getPreviewUrl($formId),
            'form'                           => $form,
            'hasPro'                         => defined('FLUENTFORMPRO'),
            'countries'                      => getFluentFormCountryList(),
            'element_customization_settings' => fluentformLoadFile('Services/FormBuilder/ElementCustomization.php'),
            'validation_rule_settings'       => fluentformLoadFile('Services/FormBuilder/ValidationRuleSettings.php'),
            'conversational_form_fields'     => array_keys(Converter::fieldTypes()),
            'form_editor_str'                => TranslationString::getEditorI18n(),
            'element_search_tags'            => $searchTags,
            'element_settings_placement'     => $elementPlacements,
            'all_forms_url'                  => admin_url('admin.php?page=fluent_forms'),
            'has_payment_features'           => !defined('FLUENTFORMPRO'),
            'upgrade_url'                    => fluentform_upgrade_url(),
            'is_conversion_form'             => Helper::isConversionForm($formId),
            'is_autoload_captcha'            => Helper::isAutoloadCaptchaEnabled(),
            'used_name_attributes'           => $this->usedNameAttributes($formId),
            'bulk_options_json'              => '{"Countries":["Afghanistan","Albania","Algeria","American Samoa","Andorra","Angola","Anguilla","Antarctica","Antigua and Barbuda","Argentina","Armenia","Aruba","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bermuda","Bhutan","Bolivia","Bonaire, Sint Eustatius and Saba","Bosnia and Herzegovina","Botswana","Bouvet Island","Brazil","British Indian Ocean Territory","Brunei Darussalam","Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Canada","Cape Verde","Cayman Islands","Central African Republic","Chad","Chile","China","Christmas Island","Cocos Islands","Colombia","Comoros","Congo, Democratic Republic of the","Congo, Republic of the","Cook Islands","Costa Rica","Croatia","Cuba","Cura\u00e7ao","Cyprus","Czech Republic","C\u00f4te d\'Ivoire","Denmark","Djibouti","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Eswatini (Swaziland)","Ethiopia","Falkland Islands","Faroe Islands","Fiji","Finland","France","French Guiana","French Polynesia","French Southern Territories","Gabon","Gambia","Georgia","Germany","Ghana","Gibraltar","Greece","Greenland","Grenada","Guadeloupe","Guam","Guatemala","Guernsey","Guinea","Guinea-Bissau","Guyana","Haiti","Heard and McDonald Islands","Holy See","Honduras","Hong Kong","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Isle of Man","Israel","Italy","Jamaica","Japan","Jersey","Jordan","Kazakhstan","Kenya","Kiribati","Kuwait","Kyrgyzstan","Lao People\'s Democratic Republic","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Macau","Macedonia","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Marshall Islands","Martinique","Mauritania","Mauritius","Mayotte","Mexico","Micronesia","Moldova","Monaco","Mongolia","Montenegro","Montserrat","Morocco","Mozambique","Myanmar","Namibia","Nauru","Nepal","Netherlands","New Caledonia","New Zealand","Nicaragua","Niger","Nigeria","Niue","Norfolk Island","North Korea","Northern Mariana Islands","Norway","Oman","Pakistan","Palau","Palestine, State of","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Pitcairn","Poland","Portugal","Puerto Rico","Qatar","Romania","Russia","Rwanda","R\u00e9union","Saint Barth\u00e9lemy","Saint Helena","Saint Kitts and Nevis","Saint Lucia","Saint Martin","Saint Pierre and Miquelon","Saint Vincent and the Grenadines","Samoa","San Marino","Sao Tome and Principe","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Sint Maarten","Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Georgia","South Korea","South Sudan","Spain","Sri Lanka","Sudan","Suriname","Svalbard and Jan Mayen Islands","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Timor-Leste","Togo","Tokelau","Tonga","Trinidad and Tobago","Tunisia","Turkey","Turkmenistan","Turks and Caicos Islands","Tuvalu","US Minor Outlying Islands","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States","Uruguay","Uzbekistan","Vanuatu","Venezuela","Vietnam","Virgin Islands, British","Virgin Islands, U.S.","Wallis and Futuna","Western Sahara","Yemen","Zambia","Zimbabwe","\u00c5land Islands"],"U.S. States":["Alabama","Alaska","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","District of Columbia","Florida","Georgia","Hawaii","Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine","Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri","Montana","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York","North Carolina","North Dakota","Ohio","Oklahoma","Oregon","Pennsylvania","Rhode Island","South Carolina","South Dakota","Tennessee","Texas","Utah","Vermont","Virginia","Washington","West Virginia","Wisconsin","Wyoming","Armed Forces Americas","Armed Forces Europe","Armed Forces Pacific"],"Canadian Province\/Territory":["Alberta","British Columbia","Manitoba","New Brunswick","Newfoundland and Labrador","Northwest Territories","Nova Scotia","Nunavut","Ontario","Prince Edward Island","Quebec","Saskatchewan","Yukon"],"Continents":["Africa","Antarctica","Asia","Australia","Europe","North America","South America"],"Gender":["Male","Female","Prefer Not to Answer"],"Age":["Under 18","18-24","25-34","35-44","45-54","55-64","65 or Above","Prefer Not to Answer"],"Marital Status":["Single","Married","Divorced","Widowed"],"Employment":["Employed Full-Time","Employed Part-Time","Self-employed","Not employed but looking for work","Not employed and not looking for work","Homemaker","Retired","Student","Prefer Not to Answer"],"Job Type":["Full-Time","Part-Time","Per Diem","Employee","Temporary","Contract","Intern","Seasonal"],"Industry":["Accounting\/Finance","Advertising\/Public Relations","Aerospace\/Aviation","Arts\/Entertainment\/Publishing","Automotive","Banking\/Mortgage","Business Development","Business Opportunity","Clerical\/Administrative","Construction\/Facilities","Consumer Goods","Customer Service","Education\/Training","Energy\/Utilities","Engineering","Government\/Military","Green","Healthcare","Hospitality\/Travel","Human Resources","Installation\/Maintenance","Insurance","Internet","Job Search Aids","Law Enforcement\/Security","Legal","Management\/Executive","Manufacturing\/Operations","Marketing","Non-Profit\/Volunteer","Pharmaceutical\/Biotech","Professional Services","QA\/Quality Control","Real Estate","Restaurant\/Food Service","Retail","Sales","Science\/Research","Skilled Labor","Technology","Telecommunications","Transportation\/Logistics","Other"],"Education":["High School","Associate Degree","Bachelor\'s Degree","Graduate or Professional Degree","Some College","Other","Prefer Not to Answer"],"Days of the Week":["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],"Months of the Year":["January","February","March","April","May","June","July","August","September","October","November","December"],"How Often":["Every day","Once a week","2 to 3 times a week","Once a month","2 to 3 times a month","Less than once a month"],"How Long":["Less than a month","1-6 months","1-3 years","Over 3 years","Never used"],"Satisfaction":["Very Satisfied","Satisfied","Neutral","Unsatisfied","Very Unsatisfied"],"Importance":["Very Important","Important","Somewhat Important","Not Important"],"Agreement":["Strongly Agree","Agree","Disagree","Strongly Disagree"],"Comparison":["Much Better","Somewhat Better","About the Same","Somewhat Worse","Much Worse"],"Would You":["Definitely","Probably","Not Sure","Probably Not","Definitely Not"],"Size":["Extra Small","Small","Medium","Large","Extra Large"],"Timezone":["(GMT -12-00) Eniwetok, Kwajalein:-12","(GMT -11-00) Midway Island, Samoa:-11","(GMT -10-00) Hawaii:-10","(GMT -9-00) Alaska:-9","(GMT -8-00) Pacific Time (US & Canada):-8","(GMT -7-00) Mountain Time (US & Canada):-7","(GMT -6-00) Central Time (US & Canada), Mexico City:-6","(GMT -5-00) Eastern Time (US & Canada), Bogota, Lima:-5","(GMT -4-00) Atlantic Time (Canada), Caracas, La Paz:-4","(GMT -3-30) Newfoundland:-3.5","(GMT -3-00) Brazil, Buenos Aires, Georgetown:-3","(GMT -2-00) Mid-Atlantic:-2","(GMT -1-00) Azores, Cape Verde Islands:-1","(GMT) Western Europe Time, London, Lisbon, Casablanca:0","(GMT +1-00) Brussels, Copenhagen, Madrid, Paris:1","(GMT +2-00) Kaliningrad, South Africa:2","(GMT +3-00) Baghdad, Riyadh, Moscow, St. Petersburg:3","(GMT +3-30) Tehran:3.5","(GMT +4-00) Abu Dhabi, Muscat, Baku, Tbilisi:4","(GMT +4-30) Kabul:4.5","(GMT +5-00) Ekaterinburg, Islamabad, Karachi, Tashkent:5","(GMT +5-30) Bombay, Calcutta, Madras, New Delhi:5.5","(GMT +5-45) Kathmandu:5.75","(GMT +6-00) Almaty, Dhaka, Colombo:6","(GMT +7-00) Bangkok, Hanoi, Jakarta:7","(GMT +8-00) Beijing, Perth, Singapore, Hong Kong:8","(GMT +9-00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk:9","(GMT +9-30) Adelaide, Darwin:9.5","(GMT +10-00) Eastern Australia, Guam, Vladivostok:10","(GMT +11-00) Magadan, Solomon Islands, New Caledonia:11","(GMT +12-00) Auckland, Wellington, Fiji, Kamchatka:12"]}',
        ];
    
        $data = apply_filters_deprecated(
            'fluentform_editor_vars',
            [
                $data
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/editor_vars',
            'Use fluentform/editor_vars instead of fluentform_editor_vars.'
        );

        wp_localize_script('fluentform_editor_script', 'FluentFormApp', apply_filters('fluentform/editor_vars', $data));

        do_action('fluentform/editor_script_loaded', $form);
    }

    /**
     * Render global settings page.
     *
     * @throws \Exception
     */
    public function renderGlobalSettings()
    {
        if (function_exists('wp_enqueue_editor')) {
            add_filter('user_can_richedit', '__return_true');
            wp_enqueue_editor();
            wp_enqueue_media();
        }

        // Fire an event letting others know the current component
        // that fluentform is rendering for the global settings
        // page. So that they can hook and load their custom
        // components on this page dynamically & easily.
        // N.B. native 'components' will always use
        // 'settings' as their current component.
        $currentComponent =  $this->app->request->get('component', 'settings');
      
        $currentComponent = apply_filters_deprecated('fluentform_global_settings_current_component', [
                $currentComponent
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/global_settings_current_component',
            'Use fluentform/global_settings_current_component instead of fluentform_global_settings_current_component.'
        );
        
        $currentComponent = apply_filters(
            'fluentform/global_settings_current_component',
            $currentComponent
        );

        $currentComponent = sanitize_key($currentComponent);
        $components = [];
        $components = apply_filters_deprecated('fluentform_global_settings_components', [
                $components
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/global_settings_components',
            'Use fluentform/global_settings_components instead of fluentform_global_settings_components.'
        );
        
        $components = apply_filters('fluentform/global_settings_components', $components);
    

        $components['reCAPTCHA'] = [
            'hash'  => 're_captcha',
            'title' => 'reCAPTCHA',
        ];

        $components['hCAPTCHA'] = [
            'hash'  => 'h_captcha',
            'title' => 'hCaptcha',
        ];

        $components['Turnstile'] = [
            'hash'  => 'turnstile',
            'title' => 'Turnstile (Beta)',
        ];
    
    
        
        $this->app->view->render('admin.globalSettings.menu', [
            'components'       => $components,
            'currentComponent' => $currentComponent,
        ]);
    }

    public function renderTransfer()
    {
        $forms = wpFluent()->table('fluentform_forms')
            ->orderBy('id', 'desc')
            ->select(['id', 'title'])
            ->get();

        wp_localize_script('fluentform-transfer-js', 'FluentFormApp', [
            'plugin'       => $this->app->config->get('app.slug'),
            'forms'        => $forms,
            'upgrade_url'  => fluentform_upgrade_url(),
            'hasPro'       => defined('FLUENTFORMPRO'),
            'transfer_str' => TranslationString::getTransferModuleI18n(),
            'source_query' => sanitize_text_field($this->app->request->get('source')),
            'status_query' => sanitize_text_field($this->app->request->get('status')),
            'date_start_query' => sanitize_text_field($this->app->request->get('start_date')),
            'date_end_query' => sanitize_text_field($this->app->request->get('end_date')),
        ]);

        $this->app->view->render('admin.tools.index');
    }
    
    public function addPreviewButton($formId)
    {
        $previewUrl = Helper::getPreviewUrl($formId);
        $previewText = __('Preview & Design', 'fluentform');
        $formId = (int)$formId;
        if (Helper::isConversionForm($formId)) {
            $previewText = __('Preview', 'fluentform');
        }
        echo '<a target="_blank" class="el-button el-button--info is-plain" href="' . esc_url($previewUrl) . '">' . '<i class="ff-icon ff-icon-eye-filled fs-15"></i> ' . '<span>' . esc_attr($previewText) . '</span>' . '</a>';
    }

    public function addCopyShortcodeButton($formId)
    {
        $formId = (int) $formId;
        $shortcode = '[fluentform id="' . $formId . '"]';
        if (Helper::isConversionForm($formId)) {
            $shortcode = '[fluentform type="conversational" id="' . $formId . '"]';
        }
        echo '<button title="Click to Copy" class="ff_shortcode_btn ff_shortcode_btn_md copy truncate" data-clipboard-text=\'' . $shortcode . '\'><i class="el-icon el-icon-document-copy"></i> ' . $shortcode . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $shortcode is escaped before being passed in.
        return;
    }

    public function commonAction()
    {
        $fluentFormPages = [
            'fluent_forms',
            'fluent_forms_transfer',
            'fluent_forms_settings',
            'fluent_forms_add_ons',
            'fluent_forms_docs',
            'fluent_forms_smtp',
        ];

        $page = sanitize_text_field($this->app->request->get('page'));

        if ($page && in_array($page, $fluentFormPages)) {
            // Let's deregister existing vuejs by other devs
            // Other devs should not regis
            add_action('admin_print_scripts', function () {
                wp_dequeue_script('vuejs');
                wp_dequeue_script('vue');
                wp_deregister_script('elementor-admin-app');
            });
        }
    }

    public function renderGlobalMenu()
    {
        $showPayment = false;
        if (defined('FLUENTFORMPRO')) {
            $showPayment = !get_option('__fluentform_payment_module_settings');
            if ($showPayment) {
                $formCount = wpFluent()->table('fluentform_forms')
                                ->count();
                $showPayment = $formCount > 2;
            }
        }
        $showPaymentEntry = apply_filters_deprecated('fluentform_show_payment_entries', [
                false
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/show_payment_entries',
            'Use fluentform/show_payment_entries instead of fluentform/show_payment_entries.'
        );

        $this->app->view->render('admin.global_menu', [
            'logo' => fluentformMix('img/fluentform-logo.svg'),
            'show_payment'         => $showPayment,
            'show_payment_entries' => apply_filters('fluentform/show_payment_entries', $showPaymentEntry),
        ]);
    }

    public function renderPaymentEntries()
    {
        do_action_deprecated(
            'flunetform_render_payment_entries', [],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/render_payment_entries',
            'Use fluentform/render_payment_entries instead of flunetform/render_payment_entries'
        );

        do_action('fluentform/render_payment_entries');
    }

    public function renderSmtpPromo()
    {
        wp_enqueue_script('fluentform_admin_notice', fluentformMix('js/admin_notices.js'), [
            'jquery',
        ], FLUENTFORM_VERSION);

        $this->app->view->render('admin.smtp.index', [
            'logo'         => fluentformMix('img/fluentsmtp.svg'),
            'banner_image' => fluentformMix('img/fluentsmtp-banner.png'),
            'is_installed' => defined('FLUENTMAIL'),
            'setup_url'    => admin_url('options-general.php?page=fluent-mail#/connections'),
        ]);
    }

    private function usedNameAttributes($formId)
    {
        return wpFluent()->table('fluentform_entry_details')
            ->select(['field_name'])
            ->where('form_id', $formId)
            ->orderBy('submission_id', 'desc')
            ->groupBy('field_name')
            ->get();
    }
}
