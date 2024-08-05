<?php

namespace FluentForm\App\Modules\Form;

use FluentForm\App\Databases\Migrations\SubmissionDetails;
use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Activator;
use FluentForm\App\Modules\Entries\Entries;
use FluentForm\App\Modules\ReCaptcha\ReCaptcha;
use FluentForm\App\Modules\HCaptcha\HCaptcha;
use FluentForm\App\Modules\Turnstile\Turnstile;
use FluentForm\App\Services\Browser\Browser;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentForm\Framework\Helpers\ArrayHelper;

/* @deprecated Use class \FluentForm\App\Http\Controllers\SubmissionHandlerController */

class FormHandler
{
    /**
     * App instance
     *
     * @var \FluentForm\Framework\Foundation\Application
     */
    protected $app;

    /**
     * Request object
     *
     * @var \FluentForm\Framework\Request\Request
     */
    protected $request;

    /**
     * Form Data
     *
     * @var array $formData
     */
    protected $formData;

    /**
     * The Fluent Forms object.
     *
     * @var \stdClass
     */
    protected $form;

    /**
     * Form Handler constructor.
     *
     * @param \FluentForm\Framework\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->request = $app->request;
    }

    /**
     * Set the form using it's ID.
     *
     * @param $formId
     *
     * @return $this
     */
    public function setForm($formId)
    {
        $this->form = wpFluent()->table('fluentform_forms')->find($formId);
        return $this;
    }

    /**
     * Handle form submition
     */
    public function onSubmit()
    {
        // Parse the url encoded data from the request object.
        parse_str($this->app->request->get('data'), $data);

        $data['_wp_http_referer'] = urldecode($data['_wp_http_referer']);

        // Merge it back again to the request object.
        $this->app->request->merge(['data' => $data]);

        $formId = intval($this->app->request->get('form_id'));

        $this->setForm($formId);

        if (!$this->form) {
            wp_send_json([
                'errors'  => [],
                'message' => 'Sorry, No corresponding form found',
            ], 423);
        }

        // Parse the form and get the flat inputs with validations.
        $fields = FormFieldsParser::getInputs($this->form, ['rules', 'raw']);

        // Sanitize the data properly.
        $this->formData = fluentFormSanitizer($data, null, $fields);

        // Now validate the data using the previous validations.
        $this->validate($fields);

        // Prepare the data to be inserted to the DB.
        $insertData = $this->prepareInsertData();

        if ($this->isSpam($this->formData, $this->form)) {
            $insertData['status'] = 'spam';
            $this->handleSpamError();
        }

        do_action_deprecated(
            'fluentform_before_insert_submission',
            [
                $insertData,
                $data,
                $this->form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/before_insert_submission',
            'Use fluentform/before_insert_submission instead of fluentform_before_insert_submission.'
        );

        do_action('fluentform/before_insert_submission', $insertData, $data, $this->form);

        if ($this->form->has_payment) {
            do_action_deprecated(
                'fluentform_before_insert_payment_form',
                [
                    $insertData,
                    $data,
                    $this->form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/before_insert_payment_form',
                'Use fluentform/before_insert_payment_form instead of fluentform_before_insert_payment_form.'
            );

            do_action('fluentform/before_insert_payment_form', $insertData, $data, $this->form);
        }

        $insertId = wpFluent()->table('fluentform_submissions')->insertGetId($insertData);

        do_action('fluentform/notify_on_form_submit', $insertId, $this->formData, $this->form);

        $uidHash = md5(wp_generate_uuid4() . $insertId);
        Helper::setSubmissionMeta($insertId, '_entry_uid_hash', $uidHash, $formId);

        do_action_deprecated(
            'fluentform_before_form_actions_processing',
            [
                $insertId,
                $this->formData,
                $this->form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/before_form_actions_processing',
            'Use fluentform/before_form_actions_processing instead of fluentform_before_form_actions_processing.'
        );

        do_action('fluentform/before_form_actions_processing', $insertId, $this->formData, $this->form);

        $result = $this->processFormSubmissionData($insertId, $this->formData, $this->form);

        wp_send_json_success($result, 200);
    }

    public function processFormSubmissionData($insertId, $formData, $form)
    {
        if ($insertId) {
            ob_start();
            $entries = new Entries();
            $entries->recordEntryDetails($insertId, $form->id, $formData);
            $isError = ob_get_clean();
            if ($isError) {
                SubmissionDetails::migrate();
            }
        }

        $returnData = $this->getReturnData($insertId, $form, $formData);

        $error = '';
        try {

            /*
             * We will keep this old hook for backward compatability.
             */
            do_action('fluentform_submission_inserted', $insertId, $formData, $form);

            do_action(
                'fluentform/submission_inserted',
                $insertId,
                $formData,
                $form
            );

            Helper::setSubmissionMeta($insertId, 'is_form_action_fired', 'yes');

            do_action_deprecated(
                'fluentform_submission_inserted_' . $form->type . '_form',
                [
                    $insertId,
                    $formData,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/submission_inserted',
                'Use fluentform/submission_inserted_' . $form->type . '_form' . ' instead of fluentform_submission_inserted_' . $form->type . '_form'
            );

            do_action(
                'fluentform/submission_inserted_' . $form->type . '_form',
                $insertId,
                $formData,
                $form
            );

        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $error = $e->getMessage();
            }
        }

        do_action_deprecated(
            'fluentform_before_submission_confirmation',
            [
                $insertId,
                $formData,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/before_submission_confirmation',
            'Use fluentform/before_submission_confirmation instead of fluentform_before_submission_confirmation.'
        );

        do_action('fluentform/before_submission_confirmation', $insertId, $formData, $form);

        return [
            'insert_id' => $insertId,
            'result'    => $returnData,
            'error'     => $error,
        ];
    }

    public function getReturnData($insertId, $form, $formData)
    {
        if (empty($form->settings)) {
            $formSettings = wpFluent()->table('fluentform_form_meta')
                ->where('form_id', $form->id)
                ->where('meta_key', 'formSettings')
                ->first();

            $form->settings = $formSettings ? json_decode($formSettings->value, true) : [];
        }
        $confirmation = $form->settings['confirmation'];
        $confirmation = apply_filters_deprecated(
            'fluentform_form_submission_confirmation',
            [
                $confirmation,
                $formData,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_submission_confirmation',
            'Use fluentform/form_submission_confirmation instead of fluentform_form_submission_confirmation.'
        );
    
        $confirmation = $this->app->applyFilters(
            'fluentform/form_submission_confirmation',
            $confirmation,
            $formData,
            $form
        );

        if ('samePage' == $confirmation['redirectTo']) {
    
            $confirmation['messageToShow'] = apply_filters_deprecated(
                'fluentform_submission_message_parse',
                [
                    $confirmation['messageToShow'],
                    $insertId,
                    $formData,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/submission_message_parse',
                'Use fluentform/submission_message_parse instead of fluentform_submission_message_parse.'
            );
            $confirmation['messageToShow'] = apply_filters('fluentform/submission_message_parse',
                $confirmation['messageToShow'], $insertId, $formData, $form);

            $message = ShortCodeParser::parse(
                $confirmation['messageToShow'],
                $insertId,
                $formData,
                $form,
                false,
                true
            );

            $message = $message ? $message : 'The form has been successfully submitted.';

            $returnData = [
                'message' => do_shortcode($message),
                'action'  => $confirmation['samePageFormBehavior'],
            ];
        } else {
            $redirectUrl = Arr::get($confirmation, 'customUrl');

            if ('customPage' == $confirmation['redirectTo']) {
                $redirectUrl = get_permalink($confirmation['customPage']);
            }

            if (
                ('yes' == Arr::get($confirmation, 'enable_query_string')) &&
                Arr::get($confirmation, 'query_strings')
            ) {
                if (strpos($redirectUrl, '?')) {
                    $redirectUrl .= '&' . Arr::get($confirmation, 'query_strings');
                } else {
                    $redirectUrl .= '?' . Arr::get($confirmation, 'query_strings');
                }
            }
            $parseUrl = true;
            $parseUrl = apply_filters_deprecated(
                'fluentform_will_parse_url_value',
                [
                    $parseUrl,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/will_parse_url_value',
                'Use fluentform/will_parse_url_value instead of fluentform_will_parse_url_value.'
            );

            $isUrlParser = apply_filters('fluentform/will_parse_url_value', $parseUrl, $form);

            $redirectUrl = ShortCodeParser::parse(
                $redirectUrl,
                $insertId,
                $formData,
                $form,
                $isUrlParser
            );

            if ($isUrlParser) {
                /*
                 * For Empty Redirect Value
                 */
                if (strpos($redirectUrl, '=&') || '=' == substr($redirectUrl, -1)) {
                    $urlArray = explode('?', $redirectUrl);
                    $baseUrl = array_shift($urlArray);

                    $query = wp_parse_url($redirectUrl)['query'];

                    $queryParams = explode('&', $query);

                    $params = [];
                    foreach ($queryParams as $queryParam) {
                        $paramArray = explode('=', $queryParam);
                        if (!empty($paramArray[1])) {
                            $params[$paramArray[0]] = $paramArray[1];
                        }
                    }

                    $redirectUrl = add_query_arg($params, $baseUrl);
                }
            }

            $message = ShortCodeParser::parse(
                ArrayHelper::get($confirmation, 'redirectMessage', ''),
                $insertId,
                $formData,
                $form,
                false,
                true
            );

            $returnData = [
                'redirectUrl' => wp_sanitize_redirect(urldecode($redirectUrl)),
                'message'     => $message,
            ];
        }
    
        $returnData = apply_filters_deprecated(
            'fluentform_submission_confirmation',
            [
                $returnData,
                $form,
                $confirmation
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/submission_confirmation',
            'Use fluentform/submission_confirmation instead of fluentform_submission_confirmation.'
        );

        return $this->app->applyFilters(
            'fluentform/submission_confirmation',
            $returnData,
            $form,
            $confirmation
        );
    }

    /**
     * Validate form data.
     *
     * @param $fields
     *
     * @return bool
     */
    private function validate(&$fields)
    {
        $this->preventMaliciousAttacks();

        $this->validateRestrictions($fields);

        $this->validateNonce();

        $this->validateReCaptcha();
        $this->validateHCaptcha();
        $this->validateTurnstile();

        foreach ($fields as $fieldName => $field) {
            if (isset($this->formData[$fieldName])) {
                $element = $field['element'];
    
                $this->formData[$fieldName] = apply_filters_deprecated(
                    'fluentform_input_data_' . $element,
                    [
                        $this->formData[$fieldName],
                        $field,
                        $this->formData,
                        $this->form
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/input_data_' . $element,
                    'Use fluentform/input_data_' . $element . ' instead of fluentform_input_data_' . $element
                );

                $this->formData[$fieldName] = $this->app->applyFilters('fluentform/input_data_' . $element,
                    $this->formData[$fieldName], $field, $this->formData, $this->form);
            }
        }

        $originalValidations = FormFieldsParser::getValidations($this->form, $this->formData, $fields);
    
        $originalValidations = apply_filters_deprecated(
            'fluentform_validations',
            [
                $originalValidations,
                $this->form,
                $this->formData
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/validations',
            'Use fluentform/validations instead of fluentform_validations.'
        );
        // Fire an event so that one can hook into it to work with the rules & messages.
        $validations = apply_filters('fluentform/validations', $originalValidations, $this->form, $this->formData);

        /*
         * Clean talk fix for now
        * They should not hook fluentform_validations and return nothing!
        * We will remove this extra check once it's done
         */
        if ($originalValidations && (!$validations || !array_filter($validations))) {
            $validations = $originalValidations;
        }

        $validator = wpFluentForm('validator')->make($this->formData, $validations[0], $validations[1]);

        $errors = [];
        if ($validator->validate()->fails()) {
            foreach ($validator->errors() as $attribute => $rules) {
                $position = strpos($attribute, ']');

                if ($position) {
                    $attribute = substr($attribute, 0, strpos($attribute, ']') + 1);
                }

                $errors[$attribute] = $rules;
            }
    
            $errors = apply_filters_deprecated(
                'fluentform_validation_error',
                [
                    $errors,
                    $this->form,
                    $fields,
                    $this->formData
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/validation_error',
                'Use fluentform/validation_error instead of fluentform_validation_error.'
            );
            // Fire an event so that one can hook into it to work with the errors.
            $errors = $this->app->applyFilters('fluentform/validation_error', $errors, $this->form, $fields,
                $this->formData);
        }

        foreach ($fields as $fieldKey => $field) {
            $field['data_key'] = $fieldKey;
            $inputName = \FluentForm\Framework\Helpers\ArrayHelper::get($field, 'raw.attributes.name');
            $field['name'] = $inputName;
    
            $error = apply_filters_deprecated(
                'fluentform_validate_input_item_' . $field['element'],
                [
                    '',
                    $field,
                    $this->formData,
                    $fields,
                    $this->form,
                    $errors
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform_validate_input_item_' . $field['element'],
                'Use fluentform/validate_input_item_' . $field['element'] . ' instead of fluentform_validate_input_item_' . $field['element']
            );

            $error = apply_filters('fluentform/validate_input_item_' . $field['element'], $error, $field, $this->formData, $fields, $this->form, $errors);
            if ($error) {
                if (empty($errors[$inputName])) {
                    $errors[$inputName] = [];
                }

                if (is_string($error)) {
                    $error = [$error];
                }

                $errors[$inputName] = array_merge($error, $errors[$inputName]);
            }
        }
    
        $errors = apply_filters_deprecated(
            'fluentform_validation_errors',
            [
                $errors,
                $this->formData,
                $this->form,
                $fields
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/validation_errors',
            'Use fluentform/validation_errors instead of fluentform_validation_errors.'
        );

        $errors = apply_filters('fluentform/validation_errors', $errors, $this->formData, $this->form, $fields);

        if ('yes' == Helper::getFormMeta($this->form->id, '_has_user_registration') && !get_current_user_id()) {
            $errors = apply_filters_deprecated(
                'fluentform_validation_user_registration_errors',
                [
                    $errors,
                    $this->formData,
                    $this->form,
                    $fields
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/validation_user_registration_errors',
                'Use fluentform/validation_user_registration_errors instead of fluentform_validation_user_registration_errors.'
            );

            $errors = apply_filters('fluentform/validation_user_registration_errors', $errors, $this->formData,
                $this->form, $fields);
        }

        if ('yes' == Helper::getFormMeta($this->form->id, '_has_user_update') && get_current_user_id()) {
            $errors = apply_filters_deprecated(
                'fluentform_validation_user_update_errors',
                [
                    $errors,
                    $this->formData,
                    $this->form,
                    $fields
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/validation_user_update_errors',
                'Use fluentform/validation_user_update_errors instead of fluentform_validation_user_update_errors.'
            );

            $errors = apply_filters('fluentform/validation_user_update_errors', $errors, $this->formData, $this->form, $fields);
        }

        if ($errors) {
            wp_send_json(['errors' => $errors], 423);
        }

        return true;
    }

    /**
     * Validate nonce.
     */
    protected function validateNonce()
    {
        $formId = $this->form->id;
        $nonceVerify = false;
        /* This filter is deprecated and will be removed soon. */
        $nonceVerify = $this->app->applyFilters('fluentform_nonce_verify', $nonceVerify, $formId);

        $shouldVerifyNonce = $this->app->applyFilters('fluentform/nonce_verify', $nonceVerify, $formId);

        if ($shouldVerifyNonce) {
            $nonce = Arr::get($this->formData, '_fluentform_' . $formId . '_fluentformnonce');
            if (!wp_verify_nonce($nonce, 'fluentform-submit-form')) {
                $nonceMessage = apply_filters_deprecated(
                    'fluentForm_nonce_error',
                    [
                        '_fluentformnonce' => [
                            __('Nonce verification failed, please try again.', 'fluentform'),
                        ],
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/nonce_error',
                    'Use fluentform/nonce_error instead of fluentForm_nonce_error.'
                );

                $errors = $this->app->applyFilters('fluentform/nonce_error', $nonceMessage);
                wp_send_json(['errors' => $errors], 422);
            }
        }
    }

    protected function handleSpamError()
    {
        $settings = get_option('_fluentform_global_form_settings');
        if (!$settings || 'validation_failed' != ArrayHelper::get($settings, 'misc.akismet_validation')) {
            return;
        }

        $errors = [
            '_fluentformakismet' => __('Submission marked as spammed. Please try again', 'fluentform'),
        ];

        wp_send_json(['errors' => $errors], 422);
    }

    protected function isSpam($formData, $form)
    {
        if (!AkismetHandler::isEnabled()) {
            return false;
        }
        $isSpamCheck = true;
        $isSpamCheck = apply_filters_deprecated(
            'fluentform_akismet_check_spam',
            [
                true,
                $form->id,
                $formData
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/akismet_check_spam',
            'Use fluentform/akismet_check_spam instead of fluentform_akismet_check_spam.'
        );

        $isSpamCheck = apply_filters('fluentform/akismet_check_spam', $isSpamCheck, $form->id, $formData);
        if (!$isSpamCheck) {
            return false;
        }
        // Let's validate now
        $isSpam = AkismetHandler::isSpamSubmission($formData, $form);
    
        $isSpam = apply_filters_deprecated(
            'fluentform_akismet_spam_result',
            [
                $isSpam,
                $form->id,
                $formData
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/akismet_spam_result',
            'Use fluentform/akismet_spam_result instead of fluentform_akismet_spam_result.'
        );

        return $this->app->applyFilters('fluentform/akismet_spam_result', $isSpam, $form->id, $formData);
    }

    /**
     * Validate reCaptcha.
     */
    private function validateReCaptcha()
    {
        $hasAutoRecaptcha = false;
        $hasAutoRecaptcha = apply_filters_deprecated(
            'ff_has_auto_recaptcha',
            [
                $hasAutoRecaptcha
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/has_recaptcha',
            'Use fluentform/has_recaptcha instead of ff_has_auto_recaptcha.'
        );
        $autoInclude = apply_filters('fluentform/has_recaptcha', $hasAutoRecaptcha);
        if (FormFieldsParser::hasElement($this->form, 'recaptcha') || $autoInclude) {
            $keys = get_option('_fluentform_reCaptcha_details');
            $token = Arr::get($this->formData, 'g-recaptcha-response');
            $version = 'v2_visible';
            if (!empty($keys['api_version'])) {
                $version = $keys['api_version'];
            }
            $isValid = ReCaptcha::validate($token, $keys['secretKey'], $version);

            if (!$isValid) {
                wp_send_json([
                    'errors' => [
                        'g-recaptcha-response' => [
                            __('reCaptcha verification failed, please try again.', 'fluentform'),
                        ],
                    ],
                ], 422);
            }
        }
    }

    /**
     * Validate hCaptcha.
     */
    private function validateHCaptcha()
    {
        $hasAutoHcaptcha = false;

        $hasAutoHcaptcha = apply_filters_deprecated(
            'ff_has_auto_hcaptcha',
            [
                $hasAutoHcaptcha
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/has_hcaptcha',
            'Use fluentform/has_hcaptcha instead of ff_has_auto_hcaptcha.'
        );
        $autoInclude = apply_filters('fluentform/has_hcaptcha', $hasAutoHcaptcha);
        FormFieldsParser::resetData();
        if (FormFieldsParser::hasElement($this->form, 'hcaptcha') || $autoInclude) {
            $keys = get_option('_fluentform_hCaptcha_details');
            $token = Arr::get($this->formData, 'h-captcha-response');
            $isValid = HCaptcha::validate($token, $keys['secretKey']);

            if (!$isValid) {
                wp_send_json([
                    'errors' => [
                        'h-captcha-response' => [
                            __('hCaptcha verification failed, please try again.', 'fluentform'),
                        ],
                    ],
                ], 422);
            }
        }
    }

    /**
     * Validate turnstile.
     */
    private function validateTurnstile()
    {
        $hasAutoTurnsTile = false;
        $hasAutoTurnsTile = apply_filters_deprecated(
            'ff_has_auto_turnstile',
            [
                $hasAutoTurnsTile
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/has_turnstile',
            'Use fluentform/has_turnstile instead of ff_has_auto_turnstile.'
        );
        $autoInclude = apply_filters('fluentform/has_turnstile', $hasAutoTurnsTile);
        if (FormFieldsParser::hasElement($this->form, 'turnstile') || $autoInclude) {
            $keys = get_option('_fluentform_turnstile_details');
            $token = Arr::get($this->formData, 'cf-turnstile-response');

            $isValid = Turnstile::validate($token, $keys['secretKey']);

            if (!$isValid) {
                wp_send_json([
                    'errors' => [
                        'cf-turnstile-response' => [
                            __('Turnstile verification failed, please try again.', 'fluentform'),
                        ],
                    ],
                ], 422);
            }
        }
    }

    /**
     * Validate form data based on the form restrictions settings.
     *
     * @param $fields
     */
    private function validateRestrictions(&$fields)
    {
        $formSettings = wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $this->form->id)
            ->where('meta_key', 'formSettings')
            ->first();

        $this->form->settings = $formSettings ? json_decode($formSettings->value, true) : [];

        $isAllowed = [
            'status'  => true,
            'message' => '',
        ];

        // This will check the following restriction settings.
        // 1. limitNumberOfEntries
        // 2. scheduleForm
        // 3. requireLogin
        
        /* This filter is deprecated and will be removed soon */
        $isAllowed = apply_filters('fluentform_is_form_renderable', $isAllowed, $this->form);
    
        $isAllowed = apply_filters('fluentform/is_form_renderable', $isAllowed, $this->form);

        if (!$isAllowed['status']) {
            wp_send_json([
                'errors' => [
                    'restricted' => [
                        $isAllowed['message'],
                    ],
                ],
            ], 422);
        }

        // Since we are here, we should now handle if the form should be allowed to submit empty.
        $restrictions = Arr::get($this->form->settings, 'restrictions.denyEmptySubmission', []);

        $this->handleDenyEmptySubmission($restrictions, $fields);
    }

    /**
     * Handle response when empty form submission is not allowed.
     *
     * @param array $settings
     * @param $fields
     */
    private function handleDenyEmptySubmission($settings, &$fields)
    {
        // Determine whether empty form submission is allowed or not.
        if (Arr::get($settings, 'enabled')) {
            // confirm this form has no required fields.
            if (!FormFieldsParser::hasRequiredFields($this->form, $fields)) {
                // Filter out the form data which doesn't have values.
                $filteredFormData = array_filter(
                    // Filter out the other meta fields that aren't actual inputs.
                    array_intersect_key($this->formData, $fields)
                );

                // TODO: Extract this function into global functions file...
                $arrayFilterRecursive = function ($array) use (&$arrayFilterRecursive) {
                    foreach ($array as $key => $item) {
                        is_array($item) && $array[$key] = $arrayFilterRecursive($item);
                        if (empty($array[$key])) {
                            unset($array[$key]);
                        }
                    }
                    return $array;
                };

                if (!count($arrayFilterRecursive($filteredFormData))) {
                    wp_send_json([
                        'errors' => [
                            'restricted' => [
                                __(
                                    !($m = Arr::get($settings, 'message'))
                                        ? 'Sorry! You can\'t submit an empty form.'
                                        : $m,
                                    'fluentform'
                                ),
                            ],
                        ],
                    ], 422);
                }
            }
        }
    }

    /**
     * Prepare the data to be inserted to the database.
     *
     * @param boolean $formData
     *
     * @return array
     */
    public function prepareInsertData($formData = false)
    {
        $formId = $this->form->id;

        if (!$formData) {
            $formData = $this->formData;
        }

        $previousItem = wpFluent()->table('fluentform_submissions')
            ->where('form_id', $formId)
            ->orderBy('id', 'DESC')
            ->first();

        $serialNumber = 1;

        if ($previousItem) {
            $serialNumber = $previousItem->serial_number + 1;
        }

        $browser = new Browser();

        $inputConfigs = FormFieldsParser::getEntryInputs($this->form, ['admin_label', 'raw']);
    
        $formData = apply_filters_deprecated(
            'fluentform_insert_response_data',
            [
                $formData,
                $formId,
                $inputConfigs
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/insert_response_data',
            'Use fluentform/insert_response_data instead of fluentform_insert_response_data.'
        );
        $this->formData = apply_filters('fluentform/insert_response_data', $formData, $formId, $inputConfigs);

        $ipAddress = $this->app->request->getIp();
        $disableIpLogging = false;
        $disableIpLogging = apply_filters_deprecated(
            'fluentform_disable_ip_logging',
            [
                $disableIpLogging,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/disable_ip_logging',
            'Use fluentform/disable_ip_logging instead of fluentform_disable_ip_logging.'
        );

        if ((defined('FLUENTFROM_DISABLE_IP_LOGGING') && FLUENTFROM_DISABLE_IP_LOGGING) || apply_filters('fluentform/disable_ip_logging',
                $disableIpLogging, $formId)) {
            $ipAddress = false;
        }

        $response = [
            'form_id'       => $formId,
            'serial_number' => $serialNumber,
            'response'      => json_encode($this->formData, JSON_UNESCAPED_UNICODE),
            'source_url'    => site_url(Arr::get($formData, '_wp_http_referer')),
            'user_id'       => get_current_user_id(),
            'browser'       => $browser->getBrowser(),
            'device'        => $browser->getPlatform(),
            'ip'            => $ipAddress,
            'created_at'    => current_time('mysql'),
            'updated_at'    => current_time('mysql'),
        ];
    
        $response = apply_filters_deprecated(
            'fluentform_filter_insert_data',
            [
                $response
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/filter_insert_data',
            'Use fluentform/filter_insert_data instead of fluentform_filter_insert_data.'
        );

        return apply_filters('fluentform/filter_insert_data', $response);
    }

    /**
     * Delegate the validation rules & messages to the
     * ones that the validation library recognizes.
     *
     * @param $rules
     * @param $messages
     *
     * @return array
     */
    protected function delegateValidations($rules, $messages, $search = [], $replace = [])
    {
        $search = $search ?: ['max_file_size', 'allowed_file_types'];
        $replace = $replace ?: ['max', 'mimes'];

        foreach ($rules as &$rule) {
            $rule = str_replace($search, $replace, $rule);
        }

        foreach ($messages as $key => $message) {
            $newKey = str_replace($search, $replace, $key);
            $messages[$newKey] = $message;
            unset($messages[$key]);
        }

        return [$rules, $messages];
    }

    /**
     * Prevents malicious attacks when the submission
     * count exceeds in an allowed interval.
     */
    public function preventMaliciousAttacks()
    {
        $prevent = apply_filters('fluentform/prevent_malicious_attacks', true, $this->form->id);

        if ($prevent) {
            $maxSubmissionCount = apply_filters('fluentform/max_submission_count', 5, $this->form->id);
            $minSubmissionInterval = apply_filters('fluentform/min_submission_interval', 30, $this->form->id);

            $interval = date('Y-m-d H:i:s', strtotime(current_time('mysql')) - $minSubmissionInterval);

            $submissionCount = wpFluent()->table('fluentform_submissions')
                ->where('status', '!=', 'trashed')
                ->where('ip', $this->app->request->getIp())
                ->where('created_at', '>=', $interval)
                ->count();

            if ($submissionCount >= $maxSubmissionCount) {
                wp_send_json([
                    'errors' => [
                        'restricted' => [
                            __(apply_filters('fluentform/too_many_requests', 'Too Many Requests.', $this->form->id),
                                'fluentform'),
                        ],
                    ],
                ], 429);
            }
        }
    }
}
