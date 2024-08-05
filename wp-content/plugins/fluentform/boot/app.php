<?php

use FluentForm\Framework\Foundation\Application;
use FluentForm\App\Hooks\Handlers\ActivationHandler;
use FluentForm\App\Hooks\Handlers\DeactivationHandler;
use FluentForm\App\Services\Migrator\Bootstrap as FormsMigrator;
use FluentForm\App\Services\FluentConversational\Classes\Form as FluentConversational;

return function ($file) {
    add_action('plugins_loaded', function () {
        $isNotCompatible = defined('FLUENTFORMPRO') && version_compare(FLUENTFORMPRO_VERSION, '5.0.0', '<');
        if ($isNotCompatible) {
            $message = '<div><h3>Fluent Forms Pro is not working. Update required!</h3><p>Current version of the pro plugin is not compatible with the latest version of Core Plugin. <a href="' . admin_url('plugins.php?s=fluentformpro&plugin_status=all&force-check=1') . '">' . __('Please update Fluent Forms Pro to latest version', 'fluentform') . '</a>.</p></div>';
            $actions = [
                'fluentform/global_menu',
                'fluentform/after_form_menu',
                'admin_notices'
            ];
            foreach ($actions as $action) {
                add_action($action, function () use ($message) {
                    printf('<div class="fluentform-admin-notice notice notice-error">%1$s</div>', $message);
                });
            }
        }
    });

    $app = new Application($file);

    register_activation_hook($file, function ($network_wide) use ($app) {
        ($app->make(ActivationHandler::class))->handle($network_wide);
    });

    add_action('wp_insert_site', function ($blog) use ($app) {
        if (is_plugin_active_for_network('fluentform/fluentform.php')) {
            switch_to_blog($blog->blog_id);
            ($app->make(ActivationHandler::class))->handle(false);
            restore_current_blog();
        }
    });

    register_deactivation_hook($file, function () use ($app) {
        ($app->make(DeactivationHandler::class))->handle();
    });

    add_action('plugins_loaded', function () use ($app) {
        do_action_deprecated(
            'fluentform_loaded',
            [
                $app
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/loaded',
            'Use fluentform/loaded instead of fluentform_loaded.'
        );

        do_action_deprecated(
            'fluentform-loaded',
            [
                $app
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/loaded',
            'Use fluentform/loaded instead of fluentform-loaded.'
        );

        do_action('fluentform/loaded', $app);
    });

    fluentformLoadFile('Services/FluentConversational/plugin.php');
    fluentformLoadFile('Services/Libraries/action-scheduler/action-scheduler.php');
    
    (new FluentConversational)->boot();
    (new FormsMigrator())->boot();
    
    /* Plugin Meta Links */
    
    add_filter('plugin_row_meta', 'fluentform_plugin_row_meta', 10, 2);
    
    function fluentform_plugin_row_meta($links, $file)
    {
        if ('fluentform/fluentform.php' == $file) {
            $row_meta = [
                'docs'    => '<a rel="noopener" href="https://fluentforms.com/docs/" style="color: #197efb;font-weight: 600;" aria-label="' . esc_attr(esc_html__('View Fluent Form Documentation', 'fluentform')) . '" target="_blank">' . esc_html__('Docs', 'fluentform') . '</a>',
                'support' => '<a rel="noopener" href="https://wpmanageninja.com/support-tickets/#/" style="color: #197efb;font-weight: 600;" aria-label="' . esc_attr(esc_html__('Get Support', 'fluentform')) . '" target="_blank">' . esc_html__('Support', 'fluentform') . '</a>',
                'developer_docs' => '<a rel="noopener" href="https://developers.fluentforms.com" style="color: #197efb;font-weight: 600;" aria-label="' . esc_attr(esc_html__('Developer Docs', 'fluentform')) . '" target="_blank">' . esc_html__('Developer Docs', 'fluentform') . '</a>',
            ];
            if (!defined('FLUENTFORMPRO')) {
                $row_meta['pro'] = '<a rel="noopener" href="https://fluentforms.com" style="color: #7742e6;font-weight: bold;" aria-label="' . esc_attr(esc_html__('Upgrade to Pro', 'fluentform')) . '" target="_blank">' . esc_html__('Upgrade to Pro', 'fluentform') . '</a>';
            }
            return array_merge($links, $row_meta);
        }
        return (array)$links;
    }
};
