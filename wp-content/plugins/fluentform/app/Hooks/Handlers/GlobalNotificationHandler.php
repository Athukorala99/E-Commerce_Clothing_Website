<?php

namespace FluentForm\App\Hooks\Handlers;


use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;
Use FluentForm\App\Services\Integrations\GlobalNotificationService;

class GlobalNotificationHandler
{
    
    /**
     * @param \FluentForm\Framework\Foundation\Application $app
     */
    protected $app;
    /**
     * @var GlobalNotificationService
     */
    private $globalNotificationService;
    
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->globalNotificationService = new GlobalNotificationService();
    }
    
    public function globalNotify($insertId, $formData, $form)
    {
        // Let's find the feeds that are available for this form
        $feeds = apply_filters_deprecated(
            'fluentform_global_notification_active_types',
            [
                [],
                $form->id
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/global_notification_active_types',
            'Use fluentform/global_notification_active_types instead of fluentform_global_notification_active_types.'
        );

        $feedKeys = apply_filters('fluentform/global_notification_active_types', $feeds, $form->id);

        if (! $feedKeys) {
            do_action_deprecated(
                'fluentform_global_notify_completed',
                [
                    $insertId,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/global_notify_completed',
                'Use fluentform/global_notify_completed instead of fluentform_global_notify_completed.'
            );
            do_action('fluentform/global_notify_completed', $insertId, $form);
            return;
        }
        
        $feedMetaKeys = array_keys($feedKeys);
        $feeds = $this->globalNotificationService->getNotificationFeeds($form, $feedMetaKeys);
        
        if (! $feeds) {
            do_action_deprecated(
                'fluentform_global_notify_completed',
                [
                    $insertId,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/global_notify_completed',
                'Use fluentform/global_notify_completed instead of fluentform_global_notify_completed.'
            );
            do_action('fluentform/global_notify_completed', $insertId, $form);
            return;
        }
        
        // Now we have to filter the feeds which are enabled
        $enabledFeeds = $this->globalNotificationService->getEnabledFeeds($feeds, $formData, $insertId);
    
        if (!$enabledFeeds) {
            do_action_deprecated(
                'fluentform_global_notify_completed',
                [
                    $insertId,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/global_notify_completed',
                'Use fluentform/global_notify_completed instead of fluentform_global_notify_completed.'
            );
            do_action('fluentform/global_notify_completed', $insertId, $form);
            return;
        }
        
        $entry = false;
        $asyncFeeds = [];

        $scheduler = $this->app['fluentFormAsyncRequest'];
        
        foreach ($enabledFeeds as $feed) {
            // We will decide if this feed will run on async or sync
            $integrationKey = ArrayHelper::get($feedKeys, $feed['meta_key']);

            $oldAction = 'fluentform_integration_notify_' . $feed['meta_key'];
            $newAction = 'fluentform/integration_notify_' . $feed['meta_key'];
            
            if (! $entry) {
                $entry = $this->globalNotificationService->getEntry($insertId, $form);
            }
            // skip emails which will be sent on payment form submit otherwise email is sent after payment success
            if (!! $form->has_payment && ('notifications' == $feed['meta_key'])) {
                if (('payment_form_submit' == ArrayHelper::get($feed, 'settings.feed_trigger_event'))) {
                    continue;
                }
            }
            
            // It's sync
            $processedValues = $feed['settings'];
            unset($processedValues['conditionals']);
            $processedValues = ShortCodeParser::parse($processedValues, $insertId, $formData, $form, false, $feed['meta_key']);
            $feed['processedValues'] = $processedValues;

            $isAsync = apply_filters_deprecated(
                'fluentform_notifying_async_' . $integrationKey,
                [
                    true,
                    $form->id
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/notifying_async_' . $integrationKey,
                'Use fluentform/notifying_async_' . $integrationKey . ' instead of fluentform_notifying_async_' . $integrationKey
            );
            
            if (apply_filters('fluentform/notifying_async_' . $integrationKey, $isAsync, $form->id)) {
                // It's async
                $asyncFeed = [
                    'action'     => $newAction,
                    'form_id'    => $form->id,
                    'origin_id'  => $insertId,
                    'feed_id'    => $feed['id'],
                    'type'       => 'submission_action',
                    'status'     => 'pending',
                    'data'       => maybe_serialize($feed),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ];

                $asyncFeeds[] = $asyncFeed;

                $queueId = $scheduler->queue($asyncFeed);

                as_enqueue_async_action('fluentform/schedule_feed', ['queueId' => $queueId], 'fluentform');
            } else {
                do_action_deprecated(
                    $oldAction,
                    [
                        $feed,
                        $formData,
                        $entry,
                        $form
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    $newAction,
                    'Use ' . $newAction . ' instead of ' . $oldAction
                );

                do_action($newAction, $feed, $formData, $entry, $form);
            }
        }
        
        if (! $asyncFeeds) {
            do_action_deprecated(
                'fluentform_global_notify_completed',
                [
                    $insertId,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/global_notify_completed',
                'Use fluentform/global_notify_completed instead of fluentform_global_notify_completed.'
            );
            do_action('fluentform/global_notify_completed', $insertId, $form);
            return;
        }
    }
}
