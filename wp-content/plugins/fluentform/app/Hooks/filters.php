<?php

/**
 * All registered filter's handlers should be in app\Hooks\Handlers,
 * addFilter is similar to add_filter and addCustomFlter is just a
 * wrapper over add_filter which will add a prefix to the hook name
 * using the plugin slug to make it unique in all wordpress plugins,
 * ex: $app->addCustomFilter('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_filter('slug-foo', ['FooHandler', 'handleFoo']).
 */

/**
 * @var $app FluentForm\Framework\Foundation\Application
 */

add_filter('fluentform/addons_extra_menu', function ($menus) {
    $menus['fluentform_pdf'] = __('Fluent Forms PDF', 'fluentform');

    return $menus;
}, 99, 1);

//Add file upload location in global settings
add_filter('fluentform/get_global_settings_values', function ($values, $key) {
    if (is_array($key)) {
        if (in_array('_fluentform_global_form_settings', $key)) {
            $values['file_upload_optoins'] = FluentForm\App\Helpers\Helper::fileUploadLocations();
        }

        if (in_array('_fluentform_turnstile_details', $key)) {
            $values = FluentForm\App\Modules\Turnstile\Turnstile::ensureSettings($values);
        }

        if (in_array('_fluentform_global_default_message_setting_fields', $key)) {
            $values['_fluentform_global_default_message_setting_fields'] = \FluentForm\App\Helpers\Helper::globalDefaultMessageSettingFields();
        }
        if (in_array('_fluentform_global_form_settings', $key) && !isset($values['_fluentform_global_form_settings']['default_messages'])) {
            $values['_fluentform_global_form_settings']['default_messages'] = \FluentForm\App\Helpers\Helper::getAllGlobalDefaultMessages();
        }
    }

    return $values;
}, 10, 2);

//Enables recaptcha validation when autoload recaptcha enabled for all forms

add_action('fluentform/before_form_validation',function (){
    $autoIncludeRecaptcha = [
        [
            'type'        => 'hcaptcha',
            'is_disabled' => !get_option('_fluentform_hCaptcha_keys_status', false),
        ],
        [
            'type'        => 'recaptcha',
            'is_disabled' => !get_option('_fluentform_reCaptcha_keys_status', false),
        ],
        [
            'type'        => 'turnstile',
            'is_disabled' => !get_option('_fluentform_turnstile_keys_status', false),
        ],
    ];
    foreach ($autoIncludeRecaptcha as $input) {
        if ($input['is_disabled']) {
            continue;
        }
        
        add_filter('fluentform/has_' . $input['type'], function () use ($input) {
            $option = get_option('_fluentform_global_form_settings');
            $autoload = \FluentForm\Framework\Helpers\ArrayHelper::get($option, 'misc.autoload_captcha');
            $type = \FluentForm\Framework\Helpers\ArrayHelper::get($option, 'misc.captcha_type');
            
            if ($autoload && $type == $input['type']) {
                return true;
            }
            
            return false;
        });
    }
});

/*
 * Push captcha in all forms when enabled from global settings
 */
$app->addFilter('fluentform/rendering_form', function ($form) {
    $option = get_option('_fluentform_global_form_settings');
    $enabled = \FluentForm\Framework\Helpers\ArrayHelper::get($option, 'misc.autoload_captcha');
    if (!$enabled) {
        return $form;
    }
    $type = \FluentForm\Framework\Helpers\ArrayHelper::get($option, 'misc.captcha_type');
    $reCaptcha = [
        'element'    => 'recaptcha',
        'attributes' => [
            'name' => 'recaptcha',
        ],
    ];
    $hCaptcha = [
        'element'    => 'hcaptcha',
        'attributes' => [
            'name' => 'hcaptcha',
        ],
    ];
    $turnstile = [
        'element'    => 'turnstile',
        'attributes' => [
            'name' => 'turnstile',
        ],
    ];

    if ('recaptcha' == $type) {
        $captcha = $reCaptcha;
    } elseif ('hcaptcha' == $type) {
        $captcha = $hCaptcha;
    } elseif ('turnstile' == $type) {
        $captcha = $turnstile;
    }
    if (!isset($captcha)) {
        return $form;
    }
    // place recaptcha below custom submit button
    $hasCustomSubmit = false;
    foreach ($form->fields['fields'] as $index => $field) {
        if (in_array($field['element'], ['recaptcha', 'hcaptcha', 'turnstile'])) {
            \FluentForm\Framework\Helpers\ArrayHelper::forget($form->fields['fields'], $index);
        }
        if ('custom_submit_button' == $field['element']) {
            $hasCustomSubmit = true;
            array_splice($form->fields['fields'], $index, 0, [$captcha]);
            break;
        }
    }
    if (!$hasCustomSubmit) {
        $form->fields['fields'][] = $captcha;
    }

    return $form;
}, 10, 1);

$elements = [
    'select',
    'input_checkbox',
    'input_radio',
    'address',
    'select_country',
    'gdpr_agreement',
    'terms_and_condition',
    'multi_payment_component'
];

foreach ($elements as $element) {
    $event = 'fluentform/response_render_' . $element;
    $app->addFilter($event, function ($response, $field, $form_id, $isLabel = false) {
        $element = $field['element'];

        if ('address' == $element && !empty($response->country)) {
            $countryList = getFluentFormCountryList();
            if (isset($countryList[$response->country])) {
                $response->country = $countryList[$response->country];
            }
        }

        if ('select_country' == $element) {
            $countryList = getFluentFormCountryList();
            if (isset($countryList[$response])) {
                $response = $countryList[$response];
            }
        }

        if (in_array($field['element'], ['gdpr_agreement', 'terms_and_condition'])) {
            if (!empty($response) && 'on' == $response) {
                $response = __('Accepted', 'fluentform');
            } else {
                $response = __('Declined', 'fluentform');
            }
        }

        if ($response && $isLabel && in_array($element, ['select', 'input_radio']) && !is_array($response)) {
            if (!isset($field['options'])) {
                $field['options'] = [];
                foreach (\FluentForm\Framework\Helpers\ArrayHelper::get($field, 'raw.settings.advanced_options', []) as $option) {
                    $field['options'][$option['value']] = $option['label'];
                }
            }
            if (isset($field['options'][$response])) {
                return $field['options'][$response];
            }
        }

        if (in_array($element, ['select', 'input_checkbox', 'multi_payment_component']) && is_array($response)) {
            return \FluentForm\App\Modules\Form\FormDataParser::formatCheckBoxValues($response, $field, $isLabel);
        }

        return \FluentForm\App\Modules\Form\FormDataParser::formatValue($response);
    }, 10, 4);
}

/*
 * Validation rule wise resolve global validation message.
 *
 */
$rules = [
    "required",
    "email",
    "numeric",
    "min",
    "max",
    "digits",
    "url",
    "allowed_image_types",
    "allowed_file_types",
    "max_file_size",
    "max_file_count",
    "valid_phone_number",
];
foreach ($rules as $ruleName) {
    $app->addFilter('fluentform/get_global_message_' . $ruleName,
        function ($message) use ($ruleName) {
            return \FluentForm\App\Helpers\Helper::getGlobalDefaultMessage($ruleName);
        }
    );
}


$app->addFilter('fluentform/response_render_textarea', function ($value, $field, $formId, $isHtml) {
    $value = $value ? nl2br($value) : $value;

    if (!$isHtml || !$value) {
        return $value;
    }

    return '<span style="white-space: pre-line">' . $value . '</span>';
}, 10, 4);

$app->addFilter('fluentform/response_render_input_file', function ($response, $field, $form_id, $isHtml = false) {
    return \FluentForm\App\Modules\Form\FormDataParser::formatFileValues($response, $isHtml, $form_id);
}, 10, 4);

$app->addFilter('fluentform/response_render_input_image', function ($response, $field, $form_id, $isHtml = false) {
    return \FluentForm\App\Modules\Form\FormDataParser::formatImageValues($response, $isHtml, $form_id);
}, 10, 4);

$app->addFilter('fluentform/response_render_input_repeat', function ($response, $field, $form_id) {
    return \FluentForm\App\Modules\Form\FormDataParser::formatRepeatFieldValue($response, $field, $form_id);
}, 10, 3);

$app->addFilter('fluentform/response_render_tabular_grid', function ($response, $field, $form_id, $isHtml = false) {
    return \FluentForm\App\Modules\Form\FormDataParser::formatTabularGridFieldValue($response, $field, $form_id, $isHtml);
}, 10, 4);

$app->addFilter('fluentform/response_render_input_name', function ($response) {
    return \FluentForm\App\Modules\Form\FormDataParser::formatName($response);
}, 10, 1);

$app->addFilter('fluentform/response_render_input_password', function ($value, $field, $formId) {
    if (\FluentForm\App\Helpers\Helper::shouldHidePassword($formId)) {
        $value = str_repeat('*', 6) . ' ' . __('(truncated)', 'fluentform');
    }

    return $value;
}, 10, 3);

$app->addFilter('fluentform/filter_insert_data', function ($data) {
    $settings = get_option('_fluentform_global_form_settings', false);
    if (is_array($settings) && isset($settings['misc'])) {
        if (isset($settings['misc']['isIpLogingDisabled'])) {
            if ($settings['misc']['isIpLogingDisabled']) {
                unset($data['ip']);
            }
        }
    }

    return $data;
});

$app->addFilter('fluentform/disabled_analytics', function ($status) {
    $settings = get_option('_fluentform_global_form_settings');
    if (isset($settings['misc']['isAnalyticsDisabled']) && $settings['misc']['isAnalyticsDisabled']) {
        return true;
    }

    return $status;
});

// permission based filters
$app->addFilter('fluentform/permission_callback', function ($status, $permission) {
    return (new \FluentForm\App\Modules\Acl\RoleManager())->currentUserFormFormCapability();
}, 10, 2);

$app->addFilter('fluentform/validate_input_item_input_email', ['\FluentForm\App\Helpers\Helper', 'isUniqueValidation'], 10, 5);

$app->addFilter('fluentform/validate_input_item_input_text', ['\FluentForm\App\Helpers\Helper', 'isUniqueValidation'], 10, 5);

$app->addFilter('fluentform/will_return_html', function ($result, $integration, $key) {
    $dictionary = [
        'notifications' => ['message'],
        'pdfFeed'       => apply_filters('fluentform/pdf_html_format', [])
    ];

    if (!isset($dictionary[$integration])) {
        return $result;
    }

    if (in_array($key, $dictionary[$integration])) {
        return true;
    }

    return $result;
}, 10, 3);

$app->addFilter('fluentform/response_render_input_number', function ($response, $field, $form_id, $isHtml = false) {
    if (!$response || !$isHtml) {
        return $response;
    }
    $fieldSettings = \FluentForm\Framework\Helpers\ArrayHelper::get($field, 'raw.settings');
    $formatter = \FluentForm\Framework\Helpers\ArrayHelper::get($fieldSettings, 'numeric_formatter');
    if (!$formatter) {
        return $response;
    }

    return \FluentForm\App\Helpers\Helper::getNumericFormatted($response, $formatter);
}, 10, 4);

$app->addFilter(
    'fluentform/create_default_settings',
    function($defaultSettings) {
        if (!isset($defaultSettings['restrictions']['restrictForm'])) {
            $defaultSettings['restrictions']['restrictForm'] = [
                'enabled' => false,
                'fields' =>  [
                    'ip' => [
                        'status' => false,
                        'values' => '',
                        'message' => __('Sorry! You can\'t submit a form from your IP address.', 'fluentform'),
                        'validation_type' => 'fail_on_condition_met'
                    ],
                    'country' => [
                        'status' => false,
                        'values' => [],
                        'message' => __('Sorry! You can\'t submit a form the country you are residing.', 'fluentform'),
                        'validation_type' => 'fail_on_condition_met'
                    ],
                    'keywords' => [
                        'status' => false,
                        'values' => '',
                        'message' => __('Sorry! Your submission contains some restricted keywords.', 'fluentform')
                    ],
                ]
            ];
        }
        return $defaultSettings;
    }
);


/*
 * Remove this after WP Fusion Update their plugin
 */
add_filter('fluentform/is_integration_enabled_wpfusion', '__return_true');
