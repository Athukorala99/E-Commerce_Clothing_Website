<?php

namespace FluentForm\App\Modules\HCaptcha;

use FluentForm\Framework\Helpers\ArrayHelper;

class HCaptcha
{
    /**
     * Verify hCaptcha response.
     *
     * @param string $token  response from the user.
     * @param null   $secret provided or already stored secret key.
     *
     * @return bool
     */
    public static function validate($token, $secret = null)
    {
        $verifyUrl = 'https://hcaptcha.com/siteverify';

        $secret = $secret ?: ArrayHelper::get(get_option('_fluentform_hCaptcha_details'), 'secretKey');

        $response = wp_remote_post($verifyUrl, [
            'method' => 'POST',
            'body'   => [
                'secret'   => $secret,
                'response' => $token,
            ],
        ]);

        $isValid = false;

        if (!is_wp_error($response)) {
            $result = json_decode(wp_remote_retrieve_body($response));
            $isValid = $result->success;
        }

        return $isValid;
    }
}
