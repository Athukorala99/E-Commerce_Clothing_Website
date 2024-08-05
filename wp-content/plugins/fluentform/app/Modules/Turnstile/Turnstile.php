<?php

namespace FluentForm\App\Modules\Turnstile;

use FluentForm\Framework\Helpers\ArrayHelper;

class Turnstile
{
    /**
     * Verify turnstile response.
     *
     * @param string $token  response from the user.
     * @param null   $secret provided or already stored secret key.
     *
     * @return bool
     */
    public static function validate($token, $secret)
    {
        $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

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

    public static function ensureSettings($values)
    {
        $settings = ArrayHelper::get($values, '_fluentform_turnstile_details');

        $settings['invisible'] = ArrayHelper::get($settings, 'invisible', 'no');
        $settings['theme'] = ArrayHelper::get($settings, 'theme', 'auto');
        unset($settings['token']);

        $values['_fluentform_turnstile_details'] = $settings;

        return $values;
    }
}
