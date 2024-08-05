<?php

namespace FluentForm\App\Services\Integrations\Slack;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Foundation\Application;

class SlackNotificationActions
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
//        add_filter('fluentform/notifying_async_slack', '__return_false');
    }

    public function register()
    {
        add_filter('fluentform/global_notification_active_types', function ($types) {
            $isEnabled = Helper::isSlackEnabled();
            if ($isEnabled) {
                $types['slack'] = 'slack';
            }
            return $types;
        });
        add_action('fluentform/integration_notify_slack', [$this, 'notify'], 20, 4);
        add_filter('fluentform/get_meta_key_settings_response', function ($response, $formId, $key) {
            if ('slack' == $key) {
                $formApi = fluentFormApi()->form($formId);
                $response['formattedFields'] = array_values($formApi->labels());
            }

            return $response;
        }, 10, 3);
    }

    public function notify($feed, $formData, $entry, $form)
    {
        $isEnabled = Helper::isSlackEnabled();
        if (! $isEnabled) {
            return;
        }
        $response = Slack::handle($feed, $formData, $form, $entry);
        if ('success' === $response['status']) {
            do_action('fluentform/log_data', [
                'parent_source_id' => $form->id,
                'source_type'      => 'submission_item',
                'source_id'        => $entry->id,
                'component'        => 'slack',
                'status'           => 'success',
                'title'            => $feed['meta_key'],
                'description'      => 'Slack feed has been successfully initialed and pushed data',
            ]);
        } else {
            do_action('fluentform/log_data', [
                'parent_source_id' => $form->id,
                'source_type'      => 'submission_item',
                'source_id'        => $entry->id,
                'component'        => 'slack',
                'status'           => 'failed',
                'title'            => $feed['meta_key'],
                'description'      => $response['message'],
            ]);
        }
    }
}
