<?php

namespace FluentForm\App\Services\FormBuilder\Notifications;

use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\Framework\Foundation\Application;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\Emogrifier\Emogrifier;

class EmailNotification
{
    /**
     * FluentForm\Framework\Foundation\Application
     *
     * @var $app
     */
    protected $app = null;

    /**
     * Biuld the instance of this class
     *
     * @param \FluentForm\Framework\Foundation\Application $app
     *
     * @return $this
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Send the email notification
     *
     * @param array     $notification  [Notification settings from form meta]
     * @param array     $submittedData [User submitted form data]
     * @param \StdClass $form          [The form object from database]
     *
     * @return bool
     */
    public function notify($notification, $submittedData, $form, $entryId = false)
    {
        $isSendAsPlain = 'yes' == ArrayHelper::get($notification, 'asPlainText');
    
        $isSendAsPlain= apply_filters_deprecated(
            'fluentform_send_plain_html_email',
            [
                $isSendAsPlain,
                $form,
                $notification
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/send_plain_html_email',
            'Use fluentform/send_plain_html_email instead of fluentform_send_plain_html_email.'
        );

        $isSendAsPlain = apply_filters('fluentform/send_plain_html_email', $isSendAsPlain, $form, $notification);

        $emailBody = $notification['message'];
    
        $emailBody = apply_filters_deprecated(
            'fluentform_submission_message_parse',
            [
                $emailBody,
                $entryId,
                $submittedData,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/submission_message_parse',
            'Use fluentform/submission_message_parse instead of fluentform_submission_message_parse.'
        );

        $emailBody = apply_filters('fluentform/submission_message_parse', $emailBody, $entryId, $submittedData, $form);

        $notification['parsed_message'] = $emailBody;

        if (! $isSendAsPlain) {
            $emailBody = $this->getEmailWithTemplate($emailBody, $form, $notification);
        }

        $sendAddresses = $this->getSendAddresses($notification, $submittedData);
        $notification['subject'] = apply_filters_deprecated(
            'fluentform_email_subject',
            [
                $notification['subject'],
                $notification,
                $submittedData,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/email_subject',
            'Use fluentform/email_subject instead of fluentform_email_subject.'
        );

        $subject = apply_filters('fluentform/email_subject', $notification['subject'], $notification, $submittedData, $form);

        if (! $sendAddresses || ! $subject) {
            if ($entryId) {
                do_action('fluentform/log_data', [
                    'parent_source_id' => $form->id,
                    'source_type'      => 'submission_item',
                    'source_id'        => $entryId,
                    'component'        => 'EmailNotification',
                    'status'           => 'error',
                    'title'            => 'Email sending skipped',
                    'description'      => "Email skipped to send because email/subject may not valid.<br />Subject: {$notification['subject']}. <br/>Email: " . implode(', ', $sendAddresses),
                ]);
            }
            return null;
        }

        $headers = $this->getHeaders($notification, $isSendAsPlain);
        $notificationAttachments = isset($notification['attachments']) ? $notification['attachments'] : [];
        $notificationAttachments = apply_filters_deprecated(
            'fluentform_filter_email_attachments',
            [
                $notificationAttachments,
                $notification,
                $form,
                $submittedData
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/filter_email_attachments',
            'Use fluentform/filter_email_attachments instead of fluentform_filter_email_attachments.'
        );
        $attachments = $this->app->applyFilters(
            'fluentform/filter_email_attachments',
            $notificationAttachments,
            $notification,
            $form,
            $submittedData
        );
    
        $emailBody = apply_filters_deprecated(
            'fluentform_email_body',
            [
                $emailBody,
                $notification,
                $submittedData,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/email_body',
            'Use fluentform/email_body instead of fluentform_email_body.'
        );

        $emailBody = apply_filters('fluentform/email_body', $emailBody, $notification, $submittedData, $form);

        if ($entryId) {
            /*
            * Inline email logger. It will work fine hopefully
            */
            add_action('wp_mail_failed', function ($error) use ($notification, $form, $entryId) {
                $failedMailSubject = ArrayHelper::get($error->error_data, 'wp_mail_failed.subject');
                if ($failedMailSubject == $notification['subject']) {
                    $reason = $error->get_error_message();
                    do_action('fluentform/log_data', [
                        'parent_source_id' => $form->id,
                        'source_type'      => 'submission_item',
                        'source_id'        => $entryId,
                        'component'        => 'EmailNotification',
                        'status'           => 'failed',
                        'title'            => 'Email sending failed',
                        'description'      => "Email Notification failed to sent.<br />Subject: {$notification['subject']}. <br/>Reason: " . $reason,
                    ]);
                }
            }, 10, 1);
        }
        $result = false;
        foreach ($sendAddresses as $address) {
            $address =  apply_filters_deprecated(
                'fluentform_email_to',
                [
                    $address,
                    $notification,
                    $submittedData,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/email_to',
                'Use fluentform/email_to instead of fluentform_email_to.'
            );
            $emailTo = apply_filters('fluentform/email_to', $address, $notification, $submittedData, $form);

            do_action('fluentform/log_data', [
                'parent_source_id' => $form->id,
                'source_type'      => 'submission_item',
                'source_id'        => $entryId,
                'component'        => 'EmailNotification',
                'status'           => 'info',
                'title'            => 'Email sending initiated',
                'description'      => 'Email Notification broadcasted to ' . $address . ".<br />Subject: {$subject}",
            ]);

            $result = $this->broadCast([
                'email'       => $emailTo,
                'subject'     => $subject,
                'body'        => $emailBody,
                'headers'     => $headers,
                'attachments' => $attachments,
            ]);
        }
        return $result;
    }

    private function broadCast($data)
    {
        $sendEmail = explode(',', $data['email']);
        if (count($sendEmail) > 1) {
            $data['email'] = $sendEmail;
        }

        return wp_mail(
            $data['email'],
            $data['subject'],
            $data['body'],
            $data['headers'],
            $data['attachments']
        );
    }

    private function getSendAddresses($notification, $submittedData)
    {
        $sendAddresses = [
            ArrayHelper::get($notification, 'sendTo.email'),
        ];

        if ('field' == ArrayHelper::get($notification, 'sendTo.type') && ! empty($notification['sendTo']['field'])) {
            $sendAddresses = [
                ArrayHelper::get($submittedData, $notification['sendTo']['field']),
            ];
            $sendAddresses = array_filter($sendAddresses, 'is_email');
        }

        if ('routing' != ArrayHelper::get($notification, 'sendTo.type')) {
            return $sendAddresses;
        }

        $routings = ArrayHelper::get($notification, 'sendTo.routing');
        $validAddresses = [];
        foreach ($routings as $routing) {
            $inputValue = ArrayHelper::get($routing, 'input_value');
            if (! $inputValue || ! is_email($inputValue)) {
                continue;
            }
            $condition = [
                'conditionals' => [
                    'status'     => true,
                    'type'       => 'any',
                    'conditions' => [
                        $routing,
                    ],
                ],
            ];
            if (\FluentForm\App\Services\ConditionAssesor::evaluate($condition, $submittedData)) {
                $validAddresses[] = $inputValue;
            }
        }

        return $validAddresses;
    }

    /**
     * @param $formId
     *
     * @return array
     *
     * @todo: Implement Caching mechanism so we don't have to parse these things for every request
     */
    private function getFormInputsAndLabels($form)
    {
        $formInputs = FormFieldsParser::getInputs($form);

        $inputLabels = FormFieldsParser::getAdminLabels($form, $formInputs);

        return [
            'inputs' => $formInputs,
            'labels' => $inputLabels,
        ];
    }

    public function getEmailWithTemplate($emailBody, $form, $notification)
    {
        $originalEmailBody = $emailBody;
        $emailHeader = "";
        $emailHeader = apply_filters_deprecated(
            'fluentform_email_header',
            [
                $emailHeader,
                $form,
                $notification
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/email_header',
            'Use fluentform/email_header instead of fluentform_email_header.'
        );
        $emailHeader = apply_filters('fluentform/email_header', $emailHeader, $form, $notification);
    
        $emailFooter = '';
        $emailFooter = apply_filters_deprecated(
            'fluentform_email_footer',
            [
                $emailFooter,
                $form,
                $notification
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/email_footer',
            'Use fluentform/email_footer instead of fluentform_email_footer.'
        );
        $emailFooter = apply_filters('fluentform/email_footer', $emailFooter, $form, $notification);

        if (empty($emailHeader)) {
            $emailHeader = wpFluentForm('view')->make('email.template.header', [
                'form'         => $form,
                'notification' => $notification,
            ]);
        }

        if (empty($emailFooter)) {
            $emailFooter = wpFluentForm('view')->make('email.template.footer', [
                'form'         => $form,
                'notification' => $notification,
                'footerText'   => $this->getFooterText($form, $notification),
            ]);
        }

        $css = wpFluentForm('view')->make('email.template.styles');
        $css = apply_filters_deprecated(
            'fluentform_email_styles',
            [
                $css,
                $form,
                $notification
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/email_styles',
            'Use fluentform/email_styles instead of fluentform_email_styles.'
        );
        $css = apply_filters('fluentform/email_styles', $css, $form, $notification);
        $emailBody = $emailHeader . $emailBody . $emailFooter;

        ob_start();
        try {
            // apply CSS styles inline for picky email clients
            $emogrifier = new Emogrifier($emailBody, $css);
            $emailBody = $emogrifier->emogrify();
        } catch (\Exception $e) {
        }
        $maybeError = ob_get_clean();

        if ($maybeError) {
            return $originalEmailBody;
        }

        return $emailBody;
    }

    private function getFooterText($form, $notification)
    {
        $option = get_option('_fluentform_global_form_settings');
        if ($option && ! empty($option['misc']['email_footer_text'])) {
            $footerText = $option['misc']['email_footer_text'];
        } else {
            $footerText = '&copy; ' . get_bloginfo('name', 'display') . '.';
        }
    
        $footerText = apply_filters_deprecated(
            'fluentform_email_template_footer_text',
            [
                $footerText,
                $form,
                $notification
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/email_template_footer_text',
            'Use fluentform/email_template_footer_text instead of fluentform_email_header.'
        );
        return apply_filters('fluentform/email_template_footer_text', $footerText, $form, $notification);
    }

    public function getHeaders($notification, $isSendAsPlain = false)
    {
        $headers = [
            'Content-Type: text/html; charset=utf-8',
        ];

        $fromEmail = $notification['fromEmail'];

        if (! is_email($fromEmail)) {
            $fromEmail = false;
        }

        if ($notification['fromName'] && $fromEmail) {
            $headers[] = "From: {$notification['fromName']} <{$fromEmail}>";
        } elseif ($fromEmail) {
            $headers[] = "From: <{$fromEmail}>";
        }

        if (! empty($notification['bcc'])) {
            $bccEmail = $notification['bcc'];
            $headers[] = 'bcc: ' . $bccEmail;
        }

        if (! empty($notification['cc'])) {
            $ccEmail = $notification['cc'];
            $headers[] = 'cc: ' . $ccEmail;
        }

        if ($notification['replyTo'] && is_email($notification['replyTo'])) {
            $headers[] = 'Reply-To: <' . $notification['replyTo'] . '>';
        }
    
        $headers = apply_filters_deprecated(
            'fluenttform_email_header',
            [
                $headers,
                $notification
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/email_template_header',
            'Use fluentform/email_template_header instead of fluenttform_email_header.'
        );
        $headers = $this->app->applyFilters(
            'fluentform/email_template_header',
            $headers,
            $notification
        );

        return $headers;
    }
}
