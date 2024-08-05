<?php

namespace FluentForm\App\Services\FormBuilder\Notifications;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;

class EmailNotificationActions
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function register()
    {
        add_filter('fluentform/notifying_async_email_notifications', '__return_false', 9);
        add_filter('fluentform/notifying_async_notifications', '__return_false', 9);

        add_filter('fluentform/global_notification_active_types', function ($types) {
            $types['notifications'] = 'email_notifications';
            return $types;
        });

        add_action('fluentform/integration_notify_notifications', [$this, 'notify'], 10, 4);

        add_action('fluentform/notify_on_form_submit', [$this, 'notifyOnSubmitPaymentForm'], 10, 3);
    }

    public function notifyOnSubmitPaymentForm($submissionId, $submissionData, $form)
    {
        $emailFeeds = wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $form->id)
            ->where('meta_key', 'notifications')
            ->get();

        if (! $emailFeeds) {
            return;
        }

        $formData = $this->getFormData($submissionId);
        $notificationManager = new  \FluentForm\App\Services\Integrations\GlobalNotificationManager(wpFluentForm());

        $activeEmailFeeds = $notificationManager->getEnabledFeeds($emailFeeds, $formData, $submissionId);
        if (! $activeEmailFeeds) {
            return;
        }

        $onSubmitEmailFeeds = array_filter($activeEmailFeeds, function ($feed) {
            return 'payment_form_submit' == ArrayHelper::get($feed, 'settings.feed_trigger_event');
        });

        if (! $onSubmitEmailFeeds || 'yes' === Helper::getSubmissionMeta($submissionId, '_ff_on_submit_email_sent')) {
            return;
        }

        $entry = $this->getEntry($submissionId);

        foreach ($onSubmitEmailFeeds as $feed) {
            $processedValues = $feed['settings'];
            unset($processedValues['conditionals']);

            $processedValues = ShortCodeParser::parse(
                $processedValues,
                $submissionId,
                $formData,
                $form,
                false,
                $feed['meta_key']
            );
            $feed['processedValues'] = $processedValues;

            $this->notify($feed, $formData, $entry, $form);
        }

        Helper::setSubmissionMeta($submissionId, '_ff_on_submit_email_sent', 'yes', $form->id);
    }

    public function notify($feed, $formData, $entry, $form)
    {
        $notifier = $this->app->make(
            'FluentForm\App\Services\FormBuilder\Notifications\EmailNotification'
        );

        $emailData = $feed['processedValues'];
        $emailAttachments = $this->getAttachments($emailData, $formData, $entry, $form);
        if ($emailAttachments) {
            $emailData['attachments'] = $emailAttachments;
        }

        $notifier->notify($emailData, $formData, $form, $entry->id);
    }

    /**
     * @param $emailData
     * @param $formData
     * @param $entry
     * @param $form
     *
     * @return array
     */
    private function getAttachments($emailData, $formData, $entry, $form)
    {
        $emailAttachments = [];
        if (! empty($emailData['attachments']) && is_array($emailData['attachments'])) {
            $attachments = [];
            foreach ($emailData['attachments'] as $name) {
                $fileUrls = ArrayHelper::get($formData, $name);
                if ($fileUrls && is_array($fileUrls)) {
                    foreach ($fileUrls as $url) {
                        $filePath = str_replace(
                            site_url(''),
                            wp_normalize_path(untrailingslashit(ABSPATH)),
                            $url
                        );
                        if (file_exists($filePath)) {
                            $attachments[] = $filePath;
                        }
                    }
                }
            }
            $emailAttachments = $attachments;
        }
        $mediaAttachments = ArrayHelper::get($emailData, 'media_attachments');
        if (! empty($mediaAttachments) && is_array($mediaAttachments)) {
            $attachments = [];
            foreach ($mediaAttachments as $file) {
                $fileUrl = ArrayHelper::get($file, 'url');
                if ($fileUrl) {
                    $filePath = str_replace(
                        site_url(''),
                        wp_normalize_path(untrailingslashit(ABSPATH)),
                        $fileUrl
                    );
                    if (file_exists($filePath)) {
                        $attachments[] = $filePath;
                    }
                }
            }
            $emailAttachments = array_merge($emailAttachments, $attachments);
        }
    
        $emailAttachments = apply_filters_deprecated(
            'fluentform_email_attachments',
            [
                $emailAttachments,
                $emailData,
                $formData,
                $entry,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/email_attachments',
            'Use fluentform/email_attachments instead of fluentform_email_attachments.'
        );
        // let others to apply attachments
        $emailAttachments = apply_filters(
            'fluentform/email_attachments',
            $emailAttachments,
            $emailData,
            $formData,
            $entry,
            $form
        );
        return $emailAttachments;
    }

    public function getFormData($submissionId)
    {
        $submission = wpFluent()->table('fluentform_submissions')
            ->where('id', $submissionId)
            ->first();

        if (! $submission) {
            return false;
        }

        return json_decode($submission->response, true);
    }

    public function getEntry($submissionId)
    {
        return wpFluent()->table('fluentform_submissions')
            ->where('id', $submissionId)
            ->first();
    }
}
