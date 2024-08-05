<?php

namespace FluentForm\App\Services\Integrations;

use FluentForm\Database\Migrations\ScheduledActions;
use FluentForm\App\Models\EntryDetails;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Models\Submission;
use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\ConditionAssesor;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;

/**
 * @deprecated deprecated use  FluentForm\App\Hooks\Handlers\GlobalNotificationHandler;
 */
class GlobalNotificationManager
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function globalNotify($insertId, $formData, $form)
    {
        $notifications = apply_filters_deprecated(
            'fluentform_global_notification_active_types',
            [
                [],
                $form->id
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/global_notification_active_types',
            'Use fluentform/global_notification_active_types instead of fluentform_global_notification_active_types.'
        );
        // Let's find the feeds that are available for this form
        $feedKeys = apply_filters('fluentform/global_notification_active_types', $notifications, $form->id);

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

        $feeds = FormMeta::where('form_id', $form->id)
            ->whereIn('meta_key', $feedMetaKeys)
            ->orderBy('id', 'ASC')
            ->get();

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
        $enabledFeeds = $this->getEnabledFeeds($feeds, $formData, $insertId);

        if (! $enabledFeeds) {
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

        foreach ($enabledFeeds as $feed) {
            // We will decide if this feed will run on async or sync
            $integrationKey = ArrayHelper::get($feedKeys, $feed['meta_key']);

            $action = 'fluentform/integration_notify_' . $feed['meta_key'];

            if (! $entry) {
                $entry = $this->getEntry($insertId, $form);
            }
            // skip emails which will be sent on payment form submit otherwise email is sent after payment success
            if (! ! $form->has_payment && ('notifications' == $feed['meta_key'])) {
                if (('payment_form_submit' == ArrayHelper::get($feed, 'settings.feed_trigger_event'))) {
                    continue;
                }
            }

            // It's sync
            $processedValues = $feed['settings'];
            unset($processedValues['conditionals']);
            $processedValues = ShortCodeParser::parse($processedValues, $insertId, $formData, $form, false, $feed['meta_key']);
            $feed['processedValues'] = $processedValues;

            $isNotifyAsync = apply_filters_deprecated(
                'fluentform/notifying_async_' . $integrationKey,
                [
                    true,
                    $form->id
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/notifying_async_' . $integrationKey,
                'Use fluentform/notifying_async_' . $integrationKey . ' instead of fluentform_notifying_async_' . $integrationKey
            );

            $isNotifyAsync = apply_filters('fluentform/notifying_async_' . $integrationKey, $isNotifyAsync, $form->id);

            if ($isNotifyAsync) {
                // It's async
                $asyncFeeds[] = [
                    'action'     => $action,
                    'form_id'    => $form->id,
                    'origin_id'  => $insertId,
                    'feed_id'    => $feed['id'],
                    'type'       => 'submission_action',
                    'status'     => 'pending',
                    'data'       => maybe_serialize($feed),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ];
            } else {
                do_action_deprecated(
                    'fluentform_integration_notify_' . $feed['meta_key'],
                    [
                        $feed,
                        $formData,
                        $entry,
                        $form
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    $action,
                    'Use ' . $action . ' instead of fluentform_integration_notify_' . $feed['meta_key']
                );
                do_action($action, $feed, $formData, $entry, $form);
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

        // Now we will push this async feeds
        $handler = $this->app['fluentFormAsyncRequest'];
        $handler->queueFeeds($asyncFeeds);

        $handler->dispatchAjax(['origin_id' => $insertId]);
    }

    public function checkCondition($parsedValue, $formData, $insertId)
    {
        $conditionSettings = ArrayHelper::get($parsedValue, 'conditionals');
        if (
            ! $conditionSettings ||
            ! ArrayHelper::isTrue($conditionSettings, 'status') ||
            ! count(ArrayHelper::get($conditionSettings, 'conditions'))
        ) {
            return true;
        }

        return ConditionAssesor::evaluate($parsedValue, $formData);
    }

    public function getEntry($id, $form)
    {
        $submission = Submission::find($id);
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);
        return FormDataParser::parseFormEntry($submission, $form, $formInputs);
    }

    public function cleanUpPassword($entryId, $form)
    {
        // Let's get the password fields
        $inputs = FormFieldsParser::getInputsByElementTypes($form, ['input_password']);

        if (! $inputs) {
            return;
        }
        $passwordKeys = array_keys($inputs);

        // Let's delete from entry details
        EntryDetails::where('form_id', $form->id)
            ->whereIn('field_name', $passwordKeys)
            ->where('submission_id', $entryId)
            ->delete();

        // Let's alter from main submission data
        $submission = Submission::where('id', $entryId)
                        ->first();

        if (! $submission) {
            return;
        }

        $responseInputs = \json_decode($submission->response, true);

        $replaced = false;
        foreach ($passwordKeys as $passwordKey) {
            if (! empty($responseInputs[$passwordKey])) {
                $responseInputs[$passwordKey] = str_repeat('*', 6) . ' ' . __('(truncated)', 'fluentform');
                $replaced = true;
            }
        }

        if ($replaced) {
            Submission::where('id', $entryId)
                ->update([
                    'response' => \json_encode($responseInputs),
                ]);
        }
    }

    /**
     * @param $feeds
     * @param $formData
     * @param $insertId
     *
     * @return array
     */
    public function getEnabledFeeds($feeds, $formData, $insertId)
    {
        $enabledFeeds = [];
        foreach ($feeds as $feed) {
            $parsedValue = json_decode($feed->value, true);
            if ($parsedValue && ArrayHelper::isTrue($parsedValue, 'enabled')) {
                // Now check if conditions matched or not
                $isConditionMatched = $this->checkCondition($parsedValue, $formData, $insertId);
                if ($isConditionMatched) {
                    $item = [
                        'id'       => $feed->id,
                        'meta_key' => $feed->meta_key,
                        'settings' => $parsedValue,
                    ];
                    if ('user_registration_feeds' == $feed->meta_key) {
                        array_unshift($enabledFeeds, $item);
                    } else {
                        $enabledFeeds[] = $item;
                    }
                }
            }
        }
        return $enabledFeeds;
    }
}
