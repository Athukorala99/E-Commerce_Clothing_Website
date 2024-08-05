<?php

namespace FluentForm\App\Services\FormBuilder;

use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\Browser\Browser;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Helpers\Helper;

class ShortCodeParser
{
    protected static $form = null;

    protected static $entry = null;

    protected static $browser = null;

    protected static $formFields = null;

    protected static $store = [
        'inputs'          => null,
        'original_inputs' => null,
        'user'            => null,
        'post'            => null,
        'other'           => null,
        'submission'      => null,
    ];

    public static function parse($parsable, $entryId, $data = [], $form = null, $isUrl = false, $providerOrIsHTML = false)
    {
        try {
            static::setDependencies($entryId, $data, $form);

            if (is_array($parsable)) {
                return static::parseShortCodeFromArray($parsable, $isUrl, $providerOrIsHTML);
            }

            return static::parseShortCodeFromString($parsable, $isUrl, $providerOrIsHTML);
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log($e->getTraceAsString());
            }
            return '';
        }
    }

    protected static function setDependencies($entry, $data, $form)
    {
        static::setEntry($entry);
        static::setData($data);
        static::setForm($form);
    }

    protected static function setEntry($entry)
    {
        static::$entry = $entry;
    }

    protected static function setdata($data)
    {
        if (! is_null($data)) {
            static::$store['inputs'] = $data;
            static::$store['original_inputs'] = $data;
        } else {
            $data = json_decode(static::getEntry()->response, true);
            static::$store['inputs'] = $data;
            static::$store['original_inputs'] = $data;
        }
    }

    protected static function setForm($form)
    {
        if (! is_null($form)) {
            static::$form = $form;
        } else {
            static::$form = static::getEntry()->form_id;
        }
    }

    protected static function parseShortCodeFromArray($parsable, $isUrl = false, $provider = false)
    {
        foreach ($parsable as $key => $value) {
            if (is_array($value)) {
                $parsable[$key] = static::parseShortCodeFromArray($value, $isUrl, $provider);
            } else {
                $isHtml = false;
                if ($provider) {
                    $isHtml = apply_filters_deprecated(
                        'ff_will_return_html',
                        [
                            false,
                            $provider,
                            $key
                        ],
                        FLUENTFORM_FRAMEWORK_UPGRADE,
                        'fluentform/will_return_html',
                        'Use fluentform/will_return_html instead of ff_will_return_html.'
                    );
                    $isHtml = apply_filters('fluentform/will_return_html', $isHtml, $provider, $key);
                }
                $parsable[$key] = static::parseShortCodeFromString($value, $isUrl, $isHtml);
            }
        }

        return $parsable;
    }

    protected static function parseShortCodeFromString($parsable, $isUrl = false, $isHtml = false)
    {
        if (! $parsable) {
            return '';
        }
        return preg_replace_callback('/{+(.*?)}/', function ($matches) use ($isUrl, $isHtml) {
            $value = '';
            if (false !== strpos($matches[1], 'inputs.')) {
                $formProperty = substr($matches[1], strlen('inputs.'));
                $value = static::getFormData($formProperty, $isHtml);
            } elseif (false !== strpos($matches[1], 'user.')) {
                $userProperty = substr($matches[1], strlen('user.'));
                $value = static::getUserData($userProperty);
            } elseif (false !== strpos($matches[1], 'embed_post.')) {
                $postProperty = substr($matches[1], strlen('embed_post.'));
                $value = static::getPostData($postProperty);
            } elseif (false !== strpos($matches[1], 'wp.')) {
                $wpProperty = substr($matches[1], strlen('wp.'));
                $value = static::getWPData($wpProperty);
            } elseif (false !== strpos($matches[1], 'submission.')) {
                $submissionProperty = substr($matches[1], strlen('submission.'));
                $value = static::getSubmissionData($submissionProperty);
            } elseif (false !== strpos($matches[1], 'cookie.')) {
                $scookieProperty = substr($matches[1], strlen('cookie.'));
                $value = wpFluentForm('request')->cookie($scookieProperty);
            } elseif (false !== strpos($matches[1], 'payment.')) {
                $property = substr($matches[1], strlen('payment.'));
                $deprecatedValue = apply_filters_deprecated(
                    'fluentform_payment_smartcode',
                    [
                        '',
                        $property,
                        self::getInstance()
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/payment_smartcode',
                    'Use fluentform/payment_smartcode instead of fluentform_payment_smartcode.'
                );

                $value = apply_filters('fluentform/payment_smartcode', $deprecatedValue, $property, self::getInstance());
            } else {
                $value = static::getOtherData($matches[1]);
            }

            if (is_array($value)) {
                $value = fluentImplodeRecursive(', ', $value);
            }

            if ($isUrl) {
                $value = urlencode($value);
            }

            return $value;
        }, $parsable);
    }

    protected static function getFormData($key, $isHtml = false)
    {
        if (strpos($key, '.label')) {
            $key = str_replace('.label', '', $key);
            $isHtml = true;
        }

        if (strpos($key, '.value')) {
            $key = str_replace('.value', '', $key);
            return ArrayHelper::get(static::$store['original_inputs'], $key);
        }

        if (strpos($key, '.') && ! isset(static::$store['inputs'][$key])) {
            return ArrayHelper::get(
                static::$store['original_inputs'],
                $key,
                ''
            );
        }

        if (! isset(static::$store['inputs'][$key])) {
            static::$store['inputs'][$key] = ArrayHelper::get(
                static::$store['inputs'],
                $key,
                ''
            );
        }

        if (is_null(static::$formFields)) {
            static::$formFields = FormFieldsParser::getShortCodeInputs(
                static::getForm(),
                ['admin_label', 'attributes', 'options', 'raw']
            );
        }

        $field = ArrayHelper::get(static::$formFields, $key, '');

        if (! $field) {
            return '';
        }

        if ($isHtml) {
            $originalInput = static::$store['original_inputs'][$key];
            $originalInput = apply_filters_deprecated(
                'fluentform_response_render_' . $field['element'],
                [
                    $originalInput,
                    $field,
                    static::getForm()->id,
                    $isHtml
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/response_render_' . $field['element'],
                'Use fluentform/response_render_' . $field['element'] . ' instead of fluentform_response_render_' . $field['element']
            );
            return apply_filters(
                'fluentform/response_render_' . $field['element'],
                $originalInput,
                $field,
                static::getForm()->id,
                $isHtml
            );
        }
    
        static::$store['inputs'][$key] = apply_filters_deprecated(
            'fluentform_response_render_' . $field['element'],
            [
                static::$store['inputs'][$key],
                $field,
                static::getForm()->id,
                $isHtml
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/response_render_' . $field['element'],
            'Use fluentform/response_render_' . $field['element'] . ' instead of fluentform_response_render_' . $field['element']
        );

        return static::$store['inputs'][$key] = apply_filters(
            'fluentform/response_render_' . $field['element'],
            static::$store['inputs'][$key],
            $field,
            static::getForm()->id,
            $isHtml
        );
    }

    protected static function getUserData($key)
    {
        if (is_null(static::$store['user'])) {
            static::$store['user'] = wp_get_current_user();
        }
        return static::$store['user']->{$key};
    }

    protected static function getPostData($key)
    {
        if (is_null(static::$store['post'])) {
            $postId = static::$store['inputs']['__fluent_form_embded_post_id'];
            static::$store['post'] = get_post($postId);
            if (is_null(static::$store['post'])) {
                return '';
            }
            static::$store['post']->permalink = get_the_permalink(static::$store['post']);
        }

        if (false !== strpos($key, 'author.')) {
            $authorProperty = substr($key, strlen('author.'));
            $authorId = static::$store['post']->post_author;
            if ($authorId) {
                $data = get_the_author_meta($authorProperty, $authorId);
                if (! is_array($data)) {
                    return $data;
                }
            }
            return '';
        } elseif (false !== strpos($key, 'meta.')) {
            $metaKey = substr($key, strlen('meta.'));
            $postId = static::$store['post']->ID;
            $data = get_post_meta($postId, $metaKey, true);
            if (! is_array($data)) {
                return $data;
            }
            return '';
        } elseif (false !== strpos($key, 'acf.')) {
            $metaKey = substr($key, strlen('acf.'));
            $postId = static::$store['post']->ID;
            if (function_exists('get_field')) {
                $data = get_field($metaKey, $postId, true);
                if (! is_array($data)) {
                    return $data;
                }
                return '';
            }
        }

        return static::$store['post']->{$key};
    }

    protected static function getWPData($key)
    {
        if ('admin_email' == $key) {
            return get_option('admin_email');
        }
        if ('site_url' == $key) {
            return site_url();
        }
        if ('site_title' == $key) {
            return get_option('blogname');
        }
        return $key;
    }

    protected static function getSubmissionData($key)
    {
        $entry = static::getEntry();

        if (empty($entry->id)) {
            return  '';
        }

        if (property_exists($entry, $key)) {
            if ('total_paid' == $key || 'payment_total' == $key) {
                return round($entry->{$key} / 100, 2);
            }
            if ('payment_method' == $key && 'test' == $key) {
                return __('Offline', 'fluentform');
            }
            return $entry->{$key};
        }
        if ('admin_view_url' == $key) {
            return admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $entry->form_id . '#/entries/' . $entry->id);
        } elseif (false !== strpos($key, 'meta.')) {
            $metaKey = substr($key, strlen('meta.'));
            $data = Helper::getSubmissionMeta($entry->id, $metaKey);
            if (! is_array($data)) {
                return $data;
            }
            return '';
        }

        return '';
    }

    protected static function getOtherData($key)
    {
        if (0 === strpos($key, 'date.')) {
            $format = str_replace('date.', '', $key);
            return date($format, strtotime(current_time('mysql')));
        } elseif ('admin_email' == $key) {
            return get_option('admin_email', false);
        } elseif ('ip' == $key) {
            return static::getRequest()->getIp();
        } elseif ('browser.platform' == $key) {
            return static::getUserAgent()->getPlatform();
        } elseif ('browser.name' == $key) {
            return static::getUserAgent()->getBrowser();
        } elseif (in_array($key, ['all_data', 'all_data_without_hidden_fields'])) {
            $formFields = FormFieldsParser::getEntryInputs(static::getForm());
            $inputLabels = FormFieldsParser::getAdminLabels(static::getForm(), $formFields);
            $response = FormDataParser::parseFormSubmission(static::getEntry(), static::getForm(), $formFields, true);

            $status = apply_filters_deprecated(
                'fluentform_all_data_skip_password_field',
                [
                    __return_true()
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/all_data_skip_password_field',
                'Use fluentform/all_data_skip_password_field instead of fluentform_all_data_skip_password_field.'
            );

            if (apply_filters('fluentform/all_data_skip_password_field', $status)) {
                $passwords = FormFieldsParser::getInputsByElementTypes(static::getForm(), ['input_password']);
                if (is_array($passwords) && ! empty($passwords)) {
                    ArrayHelper::forget($response->user_inputs, array_keys($passwords));
                }
            }

            $hideHiddenField = true;
            $hideHiddenField = apply_filters_deprecated(
                'fluentform_all_data_without_hidden_fields',
                [
                    $hideHiddenField
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/all_data_without_hidden_fields',
                'Use fluentform/all_data_without_hidden_fields instead of fluentform_all_data_without_hidden_fields.'
            );
            $skipHiddenFields = ('all_data_without_hidden_fields' == $key) &&
                                apply_filters('fluentform/all_data_without_hidden_fields', $hideHiddenField);

            if ($skipHiddenFields) {
                $hiddenFields = FormFieldsParser::getInputsByElementTypes(static::getForm(), ['input_hidden']);
                if (is_array($hiddenFields) && ! empty($hiddenFields)) {
                    ArrayHelper::forget($response->user_inputs, array_keys($hiddenFields));
                }
            }

            $html = '<table class="ff_all_data" width="600" cellpadding="0" cellspacing="0"><tbody>';
            foreach ($inputLabels as $inputKey => $label) {
                if (array_key_exists($inputKey, $response->user_inputs) && '' !== ArrayHelper::get($response->user_inputs, $inputKey)) {
                    $data = ArrayHelper::get($response->user_inputs, $inputKey);
                    if (is_array($data) || is_object($data)) {
                        continue;
                    }
                    $html .= '<tr class="field-label"><th style="padding: 6px 12px; background-color: #f8f8f8; text-align: left;"><strong>' . $label . '</strong></th></tr><tr class="field-value"><td style="padding: 6px 12px 12px 12px;">' . $data . '</td></tr>';
                }
            }

            $html .= '</tbody></table>';
            $html = apply_filters_deprecated(
                'fluentform_all_data_shortcode_html',
                [
                    $html,
                    $formFields,
                    $inputLabels,
                    $response
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/all_data_shortcode_html',
                'Use fluentform/all_data_shortcode_html instead of fluentform_all_data_shortcode_html.'
            );
            return apply_filters('fluentform/all_data_shortcode_html', $html, $formFields, $inputLabels, $response);
        } elseif ('http_referer' === $key) {
            return wp_get_referer();
        } elseif (0 === strpos($key, 'pdf.download_link.')) {
            $key = apply_filters_deprecated(
                'fluentform_shortcode_parser_callback_pdf.download_link.public',
                [
                    $key, static::getInstance()
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/shortcode_parser_callback_pdf.download_link.public',
                'Use fluentform/shortcode_parser_callback_pdf.download_link.public instead of fluentform_shortcode_parser_callback_pdf.download_link.public.'
            );
            return apply_filters('fluentform/shortcode_parser_callback_pdf.download_link.public', $key, static::getInstance());
        } elseif (false !== strpos($key, 'random_string.')) {
            $exploded = explode('.', $key);
            $prefix = array_pop($exploded);
            $value = $prefix . uniqid();

            return apply_filters('fluentform/shortcode_parser_callback_random_string', $value, $prefix, static::getInstance());
        }


        // if it's multi line then just return
        if (false !== strpos($key, PHP_EOL)) { // most probably it's a css
            return '{' . $key . '}';
        }


        $groups = explode('.', $key);
        if (count($groups) > 1) {
            $group = array_shift($groups);
            $property = implode('.', $groups);
            $handlerValue = apply_filters_deprecated(
                'fluentform_smartcode_group_' . $group,
                [
                    $property,
                    static::getInstance()
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/smartcode_group_' . $group,
                'Use fluentform/smartcode_group_' . $group . ' instead of fluentform_smartcode_group_' . $group
            );

            $handlerValue = apply_filters('fluentform/smartcode_group_' . $group, $handlerValue, static::getInstance());
            if ($handlerValue != $property) {
                return $handlerValue;
            }
        }

        // This fallback actually
        $handlerValue = apply_filters_deprecated(
            'fluentform_shortcode_parser_callback_' . $key,
            [
                '{' . $key . '}',
                static::getInstance()
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/shortcode_parser_callback_' . $key,
            'Use fluentform/shortcode_parser_callback_' . $key . ' instead of fluentform_shortcode_parser_callback_' . $key
        );


        $handlerValue = apply_filters('fluentform/shortcode_parser_callback_' . $key, $handlerValue, static::getInstance());

        if ($handlerValue) {
            return $handlerValue;
        }

        return '';
    }

    public static function getForm()
    {
        if (! is_object(static::$form)) {
            static::$form = wpFluent()->table('fluentform_forms')->find(static::$form);
        }

        return static::$form;
    }

    public static function getEntry()
    {
        if (! is_object(static::$entry)) {
            static::$entry = wpFluent()->table('fluentform_submissions')->find(static::$entry);
        }

        return static::$entry;
    }

    protected static function getRequest()
    {
        return wpFluentForm('request');
    }

    protected static function getUserAgent()
    {
        if (is_null(static::$browser)) {
            static::$browser = new Browser();
        }
        return static::$browser;
    }

    public static function getInstance()
    {
        static $instance;
        if ($instance) {
            return $instance;
        }
        $instance = new static();
        return $instance;
    }

    public static function getInputs()
    {
        return static::$store['original_inputs'];
    }

    public static function resetData()
    {
        self::$form = null;
        self::$entry = null;
        self::$browser = null;
        self::$formFields = null;

        FormFieldsParser::resetData();
        FormDataParser::resetData();
    }
}
