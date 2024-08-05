<?php

namespace FluentForm\App\Modules\CLI;

use FluentForm\App\Models\Form;
use FluentForm\App\Models\Submission;

class Commands
{
    public function stats($args, $assoc_args)
    {
        $overallStats = [
            [
                'title' => __('All Forms', 'fluentform'),
                'count' => Form::count(),
            ],
            [
                'title' => __('All Submissions', 'fluentform'),
                'count' => Submission::count(),
            ],
            [
                'title' => __('Unread Submissions', 'fluentform'),
                'count' => Submission::where('status', 'unread')
                    ->count(),
            ],
        ];

        $format = \WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');

        \WP_CLI\Utils\format_items(
            $format,
            $overallStats,
            ['title', 'count']
        );
    }

    public function activate_license($args, $assoc_args)
    {
        if (!defined('FLUENTFORMPRO')) {
            \WP_CLI::line('Fluent Forms pro is not available');
            return;
        }

        if (empty($assoc_args['key'])) {
            \WP_CLI::line('use --key=LICENSE_KEY to activate the license');
            return;
        }

        $licenseKey = trim(sanitize_text_field($assoc_args['key']));

        if (!function_exists('fluentFormProActivateLicense')) {
            \WP_CLI::line('Sorry, fluent forms pro plugin is not up to date');
            return;
        }

        \WP_CLI::line('Validating License, Please wait');

        $response = fluentFormProActivateLicense($licenseKey);

        if (is_wp_error($response)) {
            \WP_CLI::error($response->get_error_message());
            return;
        }

        \WP_CLI::success('Your license key has been successfully updated');

        \WP_CLI::line('Your License Status: ' . $response['status']);
        \WP_CLI::line('Expire Date: ' . $response['response']->expires);
        return;
    }

    public function license_status()
    {
        if (!defined('FLUENTFORMPRO')) {
            \WP_CLI::line('Fluent Forms pro is not available');
            return;
        }

        $instance = \FluentFormAddOnChecker::getInstance();

        if (!$instance) {
            \WP_CLI::line('FluentCRM Pro is not initiated');
            return;
        }

        \WP_CLI::line('Fetching License details, Please wait');

        $response = $instance->getRemoteLicense();

        if (is_wp_error($response)) {
            \WP_CLI::error($response->get_error_message());
            return;
        }

        if (!$response) {
            \WP_CLI::error('License key has not been set!');
            return;
        }

        \WP_CLI::line('Your License Status: ' . $response->license);
        \WP_CLI::line('Expires: ' . $response->expires);
        return;
    }
}
