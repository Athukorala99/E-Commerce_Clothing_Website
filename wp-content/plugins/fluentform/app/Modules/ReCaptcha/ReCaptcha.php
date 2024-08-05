<?php

namespace FluentForm\App\Modules\ReCaptcha;

use FluentForm\Framework\Helpers\ArrayHelper;

class ReCaptcha
{
    /**
     * Verify reCaptcha response.
     *
     * @param string $token response from the user.
     * @param null $secret provided or already stored secret key.
     *
     * @return bool
     */
    public static function validate($token, $secret = null, $version = 'v2_visible')
    {
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $secret = $secret ?: ArrayHelper::get(get_option('_fluentform_reCaptcha_details'), 'secretKey');

        $response = wp_remote_post($verifyUrl, [
            'method' => 'POST',
            'body'   => [
                'secret'   => $secret,
                'response' => $token
            ],
        ]);


        $isValid = false;

        if (! is_wp_error($response)) {
            $result = json_decode(wp_remote_retrieve_body($response));
            if($version == 'v3_invisible' && $result->success) {
                $score = $result->score;
                $value = 0.5;
                $value = apply_filters_deprecated(
                    'fluentforms_recaptcha_v3_ref_score',
                    [
                        $value
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/recaptcha_v3_ref_score',
                    'Use fluentform/recaptcha_v3_ref_score instead of fluentforms_recaptcha_v3_ref_score.'
                );
                $checkScore = apply_filters('fluentform/recaptcha_v3_ref_score', $value);
                $isValid = $score >= $checkScore;
            } else {
                $isValid = $result->success;
            }
        }

        return $isValid;
    }
}
