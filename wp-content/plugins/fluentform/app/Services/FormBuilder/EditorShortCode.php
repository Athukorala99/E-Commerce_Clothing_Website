<?php

namespace FluentForm\App\Services\FormBuilder;

use FluentForm\App\Modules\Form\FormFieldsParser;

class EditorShortCode
{
    public static function getGeneralShortCodes()
    {
        return [
            'title'      => 'General SmartCodes',
            'shortcodes' => [
                '{wp.admin_email}'            => __('Admin Email', 'fluentform'),
                '{wp.site_url}'               => __('Site URL', 'fluentform'),
                '{wp.site_title}'             => __('Site Title', 'fluentform'),
                '{ip}'                        => __('IP Address', 'fluentform'),
                '{date.m/d/Y}'                => __('Date (mm/dd/yyyy)', 'fluentform'),
                '{date.d/m/Y}'                => __('Date (dd/mm/yyyy)', 'fluentform'),
                '{embed_post.ID}'             => __('Embedded Post/Page ID', 'fluentform'),
                '{embed_post.post_title}'     => __('Embedded Post/Page Title', 'fluentform'),
                '{embed_post.permalink}'      => __('Embedded URL', 'fluentform'),
                '{http_referer}'              => __('HTTP Referer URL', 'fluentform'),
                '{user.ID}'                   => __('User ID', 'fluentform'),
                '{user.display_name}'         => __('User Display Name', 'fluentform'),
                '{user.first_name}'           => __('User First Name', 'fluentform'),
                '{user.last_name}'            => __('User Last Name', 'fluentform'),
                '{user.user_email}'           => __('User Email', 'fluentform'),
                '{user.user_login}'           => __('User Username', 'fluentform'),
                '{browser.name}'              => __('User Browser Client', 'fluentform'),
                '{browser.platform}'          => __('User Operating System', 'fluentform'),
                '{random_string.your_prefix}' => __('Random String with Prefix', 'fluentform'),
            ],
        ];
    }

    public static function getFormShortCodes($form)
    {
        $form = static::getForm($form);
        $formFields = FormFieldsParser::getShortCodeInputs(
            $form,
            [
                'admin_label', 'attributes', 'options',
            ]
        );

        $formShortCodes = [
            'shortcodes' => [],
            'title'      => 'Input Options',
        ];

        $formShortCodes['shortcodes']['{all_data}'] = 'All Submitted Data';
        $formShortCodes['shortcodes']['{all_data_without_hidden_fields}'] = 'All Data Without Hidden Fields';
        foreach ($formFields as $key => $value) {
            $formShortCodes['shortcodes']['{inputs.' . $key . '}'] = $value['admin_label'];
        }

        return $formShortCodes;
    }

    public static function getSubmissionShortcodes($form = false)
    {
        $submissionProperties = [
            '{submission.id}'             => __('Submission ID', 'fluentform'),
            '{submission.serial_number}'  => __('Submission Serial Number', 'fluentform'),
            '{submission.source_url}'     => __('Source URL', 'fluentform'),
            '{submission.user_id}'        => __('User Id', 'fluentform'),
            '{submission.browser}'        => __('Submitter Browser', 'fluentform'),
            '{submission.device}'         => __('Submitter Device', 'fluentform'),
            '{submission.status}'         => __('Submission Status', 'fluentform'),
            '{submission.created_at}'     => __('Submission Create Date', 'fluentform'),
            '{submission.admin_view_url}' => __('Submission Admin View URL', 'fluentform'),
        ];

        if ($form) {
            $form = static::getForm($form);
            if ($form && $form->has_payment) {
                $submissionProperties['{submission.currency}'] = __('Currency', 'fluentform');
                $submissionProperties['{submission.payment_method}'] = __('Payment Method', 'fluentform');
                $submissionProperties['{submission.payment_status}'] = __('Payment Status', 'fluentform');
                $submissionProperties['{submission.total_paid}'] = __('Paid Total Amount', 'fluentform');
                $submissionProperties['{submission.payment_total}'] = __('Payment Amount', 'fluentform');
            }
        }

        return [
            'title'      => 'Entry Attributes',
            'shortcodes' => $submissionProperties,
        ];
    }

    public static function getPaymentShortcodes($form)
    {
        return [
            'title'      => 'Payment Details',
            'shortcodes' => [
                '{payment.receipt}'        => __('Payment Receipt', 'fluentform'),
                '{payment.summary}'        => __('Payment Summary', 'fluentform'),
                '{payment.order_items}'    => __('Order Items Table', 'fluentform'),
                '{payment.payment_status}' => __('Payment Status', 'fluentform'),
                '{payment.payment_total}'  => __('Payment Total', 'fluentform'),
                '{payment.payment_method}' => __('Payment Method', 'fluentform'),
            ],
        ];
    }

    public static function getShortCodes($form)
    {
        $form = static::getForm($form);
        $groups = [
            static::getFormShortCodes($form),
            static::getGeneralShortCodes(),
            static::getSubmissionShortcodes($form),
        ];

        if ($form->has_payment) {
            $groups[] = static::getPaymentShortcodes($form);
        }
    
        $groups = apply_filters_deprecated(
            'fluentform_form_settings_smartcodes',
            [
                $groups,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_settings_smartcodes',
            'Use fluentform/form_settings_smartcodes instead of fluentform_form_settings_smartcodes.'
        );

        return apply_filters('fluentform/form_settings_smartcodes', $groups, $form);
    }

    public static function parse($string, $data, callable $arrayFormatter = null)
    {
        if (is_array($string)) {
            return static::parseArray($string, $data, $arrayFormatter);
        }

        return static::parseString($string, $data, $arrayFormatter);
    }

    public static function parseArray($string, $data, $arrayFormatter)
    {
        foreach ($string as $key => $value) {
            if (is_array($value)) {
                $string[$key] = static::parseArray($value, $data, $arrayFormatter);
            } else {
                $string[$key] = static::parseString($value, $data, $arrayFormatter);
            }
        }

        return $string;
    }

    public static function parseString($string, $data, callable $arrayFormatter = null)
    {
        return preg_replace_callback('/{+(.*?)}/', function ($matches) use (&$data, &$arrayFormatter) {
            if (! isset($data[$matches[1]])) {
                return $matches[0];
            } elseif (is_array($value = $data[$matches[1]])) {
                return is_callable($arrayFormatter) ? $arrayFormatter($value) : implode(', ', $value);
            }
            return $data[$matches[1]];
        }, $string);
    }

    protected static function getForm($form)
    {
        if (is_object($form)) {
            return $form;
        }

        return wpFluent()->table('fluentform_forms')->find($form);
    }
}
