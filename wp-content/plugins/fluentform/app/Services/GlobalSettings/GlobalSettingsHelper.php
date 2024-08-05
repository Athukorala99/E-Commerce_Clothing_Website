<?php

namespace FluentForm\App\Services\GlobalSettings;

use FluentForm\App\Modules\HCaptcha\HCaptcha;
use FluentForm\App\Modules\ReCaptcha\ReCaptcha;
use FluentForm\App\Modules\Turnstile\Turnstile;
use FluentForm\App\Services\Integrations\MailChimp\MailChimp;
use FluentForm\Framework\Support\Arr;

class GlobalSettingsHelper
{
    public function storeReCaptcha($attributes)
    {
        $data = Arr::get($attributes, 'reCaptcha');

        if ('clear-settings' == $data) {
            delete_option('_fluentform_reCaptcha_details');

            update_option('_fluentform_reCaptcha_keys_status', false, 'no');

            return([
                'message' => __('Your reCAPTCHA settings are deleted.', 'fluentform'),
                'status'  => false,
            ]);
        }

        $token = Arr::get($data, 'token');
        $secretKey = Arr::get($data, 'secretKey');

        // If token is not empty meaning user verified their captcha.
        if ($token) {
            // Validate the reCaptcha response.
            $version = Arr::get($data, 'api_version', 'v2_visible');

            $status = ReCaptcha::validate($token, $secretKey, $version);

            // reCaptcha is valid. So proceed to store.
            if ($status) {
                // Prepare captcha data.
                $captchaData = [
                    'siteKey'     => sanitize_text_field(Arr::get($data, 'siteKey')),
                    'secretKey'   => sanitize_text_field($secretKey),
                    'api_version' => Arr::get($data, 'api_version'),
                ];

                // Update the reCaptcha details with siteKey & secretKey.
                update_option('_fluentform_reCaptcha_details', $captchaData, 'no');

                // Update the reCaptcha validation status.
                update_option('_fluentform_reCaptcha_keys_status', $status, 'no');

                // Send success response letting the user know that
                // that the reCaptcha is valid and saved properly.
                return ([
                    'message' => __('Your reCAPTCHA is valid and saved.', 'fluentform'),
                    'status'  => $status,
                ]);
            } else { // reCaptcha is not valid.
                $message = __('Sorry, Your reCAPTCHA is not valid. Please try again', 'fluentform');
            }
        } else { // The token is empty, so the user didn't verify their captcha.
            $message = __('Please validate your reCAPTCHA first and then hit save.', 'fluentform');

            // Get the already stored reCaptcha status.
            $status = get_option('_fluentform_reCaptcha_keys_status');

            if ($status) {
                $message = __('Your reCAPTCHA details are already valid. So no need to save again.', 'fluentform');
            }
        }

        return([
            'message' => $message,
            'status'  => $status,
        ]);
    }

    public function storeHCaptcha($attributes)
    {
        $data = Arr::get($attributes, 'hCaptcha');

        if ('clear-settings' == $data) {
            delete_option('_fluentform_hCaptcha_details');

            update_option('_fluentform_hCaptcha_keys_status', false, 'no');

            return([
                'message' => __('Your hCaptcha settings are deleted.', 'fluentform'),
                'status'  => false,
            ]);
        }

        $token = Arr::get($data, 'token');
        $secretKey = Arr::get($data, 'secretKey');

        // If token is not empty meaning user verified their captcha.
        if ($token) {
            // Validate the hCaptcha response.
            $status = HCaptcha::validate($token, $secretKey);

            // hCaptcha is valid. So proceed to store.
            if ($status) {
                // Prepare captcha data.
                $captchaData = [
                    'siteKey'   => sanitize_text_field(Arr::get($data, 'siteKey')),
                    'secretKey' => sanitize_text_field($secretKey),
                ];

                // Update the hCaptcha details with siteKey & secretKey.
                update_option('_fluentform_hCaptcha_details', $captchaData, 'no');

                // Update the hCaptcha validation status.
                update_option('_fluentform_hCaptcha_keys_status', $status, 'no');

                // Send success response letting the user know that
                // that the hCaptcha is valid and saved properly.
                return([
                    'message' => __('Your hCaptcha is valid and saved.', 'fluentform'),
                    'status'  => $status,
                ]);
            } else { // hCaptcha is not valid.
                $message = __('Sorry, Your hCaptcha is not valid, Please try again', 'fluentform');
            }
        } else { // The token is empty, so the user didn't verify their captcha.
            $message = __('Please validate your hCaptcha first and then hit save.', 'fluentform');

            // Get the already stored hCaptcha status.
            $status = get_option('_fluentform_hCaptcha_keys_status');

            if ($status) {
                $message = __('Your hCaptcha details are already valid, So no need to save again.', 'fluentform');
            }
        }

        return([
            'message' => $message,
            'status'  => $status,
        ]);
    }

    public function storeTurnstile($attributes)
    {
        $data = Arr::get($attributes, 'turnstile');

        if ('clear-settings' == $data) {
            delete_option('_fluentform_turnstile_details');

            update_option('_fluentform_turnstile_keys_status', false, 'no');

            return([
                'message' => __('Your Turnstile settings are deleted.', 'fluentform'),
                'status'  => false,
            ]);
        }

        $token = Arr::get($data, 'token');
        $secretKey = sanitize_text_field(Arr::get($data, 'secretKey'));

        // Prepare captcha data.
        $captchaData = [
            'siteKey'   => Arr::get($data, 'siteKey'),
            'secretKey' => $secretKey,
            'invisible' => Arr::get($data, 'invisible', 'no'),
            'theme'     => Arr::get($data, 'theme', 'auto')
        ];

        // If token is not empty meaning user verified their captcha.
        if ($token) {
            // Validate the turnstile response.
            $status = Turnstile::validate($token, $secretKey);

            // turnstile is valid. So proceed to store.
            if ($status) {
                // Update the turnstile details with siteKey & secretKey.
                update_option('_fluentform_turnstile_details', $captchaData, 'no');

                // Update the turnstile validation status.
                update_option('_fluentform_turnstile_keys_status', $status, 'no');

                // Send success response letting the user know that
                // that the turnstile is valid and saved properly.
                return([
                    'message' => __('Your Turnstile Keys are valid.', 'fluentform'),
                    'status'  => $status,
                ]);
            } else {
                // turnstile is not valid.
                $message = __('Sorry, Your Turnstile Keys are not valid. Please try again!', 'fluentform');
            }
        } else {
            // The token is empty, so the user didn't verify their captcha.
            $message = __('Please validate your Turnstile first and then hit save.', 'fluentform');

            // Get the already stored reCaptcha status.
            $status = get_option('_fluentform_turnstile_keys_status');

            if ($status) {
                update_option('_fluentform_turnstile_details', $captchaData, 'no');
                $message = __('Your Turnstile settings is saved.', 'fluentform');

                return([
                    'message' => $message,
                    'status'  => $status,
                ]);
            }
        }

        return([
            'message' => $message,
            'status'  => $status,
        ]);
    }

    public function storeSaveGlobalLayoutSettings($attributes)
    {
        $settings = Arr::get($attributes, 'form_settings');
        $settings = json_decode($settings, true);
        $sanitizedSettings = fluentFormSanitizer($settings);

        if (Arr::get($settings, 'misc.email_footer_text')) {
            $sanitizedSettings['misc']['email_footer_text'] = wp_unslash($settings['misc']['email_footer_text']);
        }

        update_option('_fluentform_global_form_settings', $sanitizedSettings, 'no');

        return ([
            'message' => __('Global settings has been saved')
        ]);
    }

    public function storeMailChimpSettings($attributes)
    {
        $mailChimp = Arr::get($attributes, 'mailchimp');

        if (!$mailChimp['apiKey']) {
            $mailChimpSettings = [
                'apiKey' => '',
                'status' => false,
            ];
            // Update the reCaptcha details with siteKey & secretKey.

            update_option('_fluentform_mailchimp_details', $mailChimpSettings, 'no');

            return([
                'message' => __('Your settings has been updated', 'fluentform'),
                'status'  => false,
            ]);
        }

        // Verify API key now
        try {
            $MailChimp = new MailChimp($mailChimp['apiKey']);
            $result = $MailChimp->get('lists');
            if (!$MailChimp->success()) {
                throw new \Exception($MailChimp->getLastError());
            }
        } catch (\Exception $exception) {
            return([
                'message' => $exception->getMessage(),
            ]);
        }

        // Mailchimp key is verified now, Proceed now

        $mailChimpSettings = [
            'apiKey' => sanitize_text_field($mailChimp['apiKey']),
            'status' => true,
        ];

        // Update the reCaptcha details with siteKey & secretKey.
        update_option('_fluentform_mailchimp_details', $mailChimpSettings, 'no');

        return([
            'message' => __('Your mailchimp api key has been verfied and successfully set', 'fluentform'),
            'status'  => true,
        ]);
    }

    public function storeEmailSummarySettings($attributes)
    {
        $settings = Arr::get($attributes, 'email_report');
        $settings = json_decode($settings, true);

        $defaults = [
            'status'            => 'yes',
            'send_to_type'      => 'admin_email',
            'custom_recipients' => '',
            'sending_day'       => 'Mon',
        ];

        $settings = wp_parse_args($settings, $defaults);

        update_option('_fluentform_email_report_summary', $settings);

        $emailReportHookName = 'fluentform_do_email_report_scheduled_tasks';
        if (!wp_next_scheduled($emailReportHookName)) {
            wp_schedule_event(time(), 'daily', $emailReportHookName);
        }

        return true;
    }
}
