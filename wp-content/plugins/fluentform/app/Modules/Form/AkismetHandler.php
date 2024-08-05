<?php

namespace FluentForm\App\Modules\Form;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Helpers\ArrayHelper;

class AkismetHandler
{
    public static function isEnabled()
    {
        if (!self::isPluginEnabled()) {
            return false;
        }

        $settings = get_option('_fluentform_global_form_settings');
        return $settings && 'yes' == ArrayHelper::get($settings, 'misc.akismet_status');
    }

    public static function isPluginEnabled()
    {
        $exists = method_exists('Akismet', 'http_post');
        if ($exists) {
            return !!\Akismet::get_api_key();
        }
        return false;
    }

    public static function isSpamSubmission($formData, $form)
    {
        global $akismet_api_host, $akismet_api_port;
        $fields = self::getAkismetFields($formData, $form);
        $response = \Akismet::http_post($fields, 'comment-check');

        return 'true' == ArrayHelper::get($response, 1);
    }

    protected static function getAkismetFields($data, $form)
    {
        $app = wpFluentForm();
        $ip = $app->request->getIp();

        $info = [
            'comment_type'         => 'contact-form',
            'comment_author'       => '',
            'comment_author_email' => '',
            'comment_content'      => '',
            'contact_form_subject' => $form->title,
            'comment_author_IP'    => $ip,
            'permalink'            => urlencode($data['_wp_http_referer']),
            'user_ip'              => preg_replace('/[^0-9., ]/', '', $ip),
            'user_agent'           => $app->request->header('USER_AGENT'),
            'blog'                 => get_option('home'),
        ];

        $maps = [
            'input_name'  => 'comment_author',
            'input_email' => 'comment_author_email',
            'textarea'    => 'comment_content',
        ];
        $inputs = FormFieldsParser::getInputs($form, ['attributes']);

        foreach ($inputs as $input) {
            $element = ArrayHelper::get($input, 'element');
            $key = ArrayHelper::get($input, 'attributes.name');
            if (isset($maps[$element]) && !$info[$maps[$element]]) {
                $value = ArrayHelper::get($data, $key);
                if ($value) {
                    if (is_array($value)) {
                        $value = implode(' ', $value);
                    }
                    $info[$maps[$element]] = $value;
                }
            }
        }
    
        $info = apply_filters_deprecated(
            'fluentform_akismet_fields',
            [
                $info,
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/akismet_fields',
            'Use fluentform/akismet_fields instead of fluentform_akismet_fields.'
        );

        $info = apply_filters('fluentform/akismet_fields', $info, $data, $form);

        return http_build_query($info);
    }
}
