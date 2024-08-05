<?php

namespace FluentForm\App\Services\Form;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Modules\Form\AkismetHandler;
use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Modules\HCaptcha\HCaptcha;
use FluentForm\App\Modules\ReCaptcha\ReCaptcha;
use FluentForm\App\Modules\Turnstile\Turnstile;
use FluentForm\App\Services\FormBuilder\Components\SelectCountry;
use FluentForm\Framework\Foundation\App;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentForm\Framework\Validator\ValidationException;

class FormValidationService
{
    protected $app;
    protected $form;
    protected $formData;
    
    public function __construct()
    {
        $this->app = App::getInstance();
    }
    
    public function setForm($form)
    {
        $this->form = $form;
    }
    
    public function setFormData($formData)
    {
        $this->formData = $formData;
    }
    
    /**
     * @param $fields
     * @param $formData
     * @return bool
     * @throws ValidationException
     */
    public function validateSubmission(&$fields, &$formData)
    {
        do_action('fluentform/before_form_validation', $fields, $formData);
        
        $this->preventMaliciousAttacks();
    
        $this->validateRestrictions($fields);
        
        $this->validateNonce();
        
        $this->validateReCaptcha();
        $this->validateHCaptcha();
        $this->validateTurnstile();
        
        foreach ($fields as $fieldName => $field) {
            if (isset($formData[$fieldName])) {
                $element = $field['element'];
    
                $formData[$fieldName] = apply_filters_deprecated('fluentform_input_data_' . $element, [
                        $formData[$fieldName],
                        $field,
                        $formData,
                        $this->form
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/input_data_' . $element,
                    'Use fluentform/input_data_' . $element . ' instead of fluentform_input_data_' . $element
                );
                $formData[$fieldName] = apply_filters('fluentform/input_data_' . $element, $formData[$fieldName], $field, $formData, $this->form);
            }
        }
        
        $originalValidations = FormFieldsParser::getValidations($this->form, $formData, $fields);
        
        // Fire an event so that one can hook into it to work with the rules & messages.
        $originalValidations = apply_filters_deprecated('fluentform_validations', [
                $originalValidations,
                $this->form,
                $formData
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/validations',
            'Use fluentform/validations instead of fluentform_validations.'
        );
        $validations = apply_filters('fluentform/validations', $originalValidations, $this->form, $formData);

        /*
         * Clean talk fix for now
         * They should not hook fluentform_validations and return nothing!
         * We will remove this extra check once it's done
         */
        if ($originalValidations && (!$validations || !array_filter($validations))) {
            $validations = $originalValidations;
        }
        
        $validator = wpFluentForm('validator')->make($formData, $validations[0], $validations[1]);
        
        $errors = [];
        if ($validator->validate()->fails()) {
            foreach ($validator->errors() as $attribute => $rules) {
                $position = strpos($attribute, ']');
                
                if ($position) {
                    $attribute = substr($attribute, 0, strpos($attribute, ']') + 1);
                }
                
                $errors[$attribute] = $rules;
            }
            // Fire an event so that one can hook into it to work with the errors.
            $errors = apply_filters_deprecated('fluentform_validation_error', [
                    $errors,
                    $this->form,
                    $fields,
                    $formData
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/validation_error',
                'Use fluentform/validation_error instead of fluentform_validation_error.'
            );

            $errors = $this->app->applyFilters('fluentform/validation_error', $errors, $this->form, $fields, $formData);
        }

        foreach ($fields as $fieldKey => $field) {
            $field['data_key'] = $fieldKey;
            $inputName = Arr::get($field, 'raw.attributes.name');
            $field['name'] = $inputName;
            $error = $this->validateInput($field, $formData, $this->form);
            $error = apply_filters_deprecated('fluentform_validate_input_item_' . $field['element'], [
                    $error,
                    $field,
                    $formData,
                    $fields,
                    $this->form,
                    $errors
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/validate_input_item_' . $field['element'],
                'Use fluentform/validate_input_item_' . $field['element'] . ' instead of fluentform_validate_input_item_' . $field['element']
            );

            $error = apply_filters('fluentform/validate_input_item_' . $field['element'], $error, $field, $formData, $fields, $this->form, $errors);
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
    
        $errors = apply_filters_deprecated('fluentform_validation_errors', [
                $errors,
                $formData,
                $this->form,
                $fields
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/validation_errors',
            'Use fluentform/validation_errors instead of fluentform_validation_errors.'
        );
    
        $errors = apply_filters('fluentform/validation_errors', $errors, $formData, $this->form, $fields);
    
        if ('yes' == Helper::getFormMeta($this->form->id, '_has_user_registration') && !get_current_user_id()) {
            $errors = apply_filters_deprecated('fluentform_validation_user_registration_errors', [
                    $errors,
                    $formData,
                    $this->form,
                    $fields
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/validation_user_registration_errors',
                'Use fluentform/validation_user_registration_errors instead of fluentform_validation_user_registration_errors.'
            );

            $errors = apply_filters('fluentform/validation_user_registration_errors', $errors, $formData, $this->form, $fields);
        }
    
        if ('yes' == Helper::getFormMeta($this->form->id, '_has_user_update') && get_current_user_id()) {
            $errors = apply_filters_deprecated('fluentform_validation_user_update_errors', [
                    $errors,
                    $formData,
                    $this->form,
                    $fields
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/validation_user_update_errors',
                'Use fluentform/validation_user_update_errors instead of fluentform_validation_user_update_errors.'
            );
            $errors = apply_filters('fluentform/validation_user_update_errors', $errors, $formData, $this->form, $fields);
        }
        
        if ($errors) {
            throw new ValidationException('', 423, null,  ['errors' => $errors]);
        }
        
        return true;
    }

    protected function validateInput($field, $formData, $form, $fieldName = '', $inputValue = [])
    {
        $error = '';
        if (!$fieldName) {
            $fieldName = Arr::get($field, 'name');
        }
        if (!$fieldName) {
            return $error;
        }
        if (!$inputValue) {
            $inputValue = Arr::get($formData, $fieldName);
        }
        if ($inputValue) {
            $rawField = Arr::get($field, 'raw');
            if (!$rawField) {
                $rawField = $field;
            }
            $fieldType = Arr::get($rawField, 'element');
            $rawField = apply_filters('fluentform/rendering_field_data_' . $fieldType, $rawField, $form);
            $options = [];
            if ("net_promoter_score" === $fieldType) {
                $options = Arr::get($rawField, 'options', []);
            } elseif ('ratings' == $fieldType) {
                $options = array_keys(Arr::get($rawField, 'options', []));
            } elseif ('gdpr_agreement' == $fieldType || 'terms_and_condition' == $fieldType) {
                $options = ['on'];
            } elseif (in_array($fieldType, ['input_radio', 'select', 'input_checkbox'])) {
                if (Arr::isTrue($rawField, 'attributes.multiple')) {
                    $fieldType = 'multi_select';
                }
                $options = array_column(
                    Arr::get($rawField, 'settings.advanced_options', []),
                    'value'
                );
            }

            if ($options) {
                $options = array_map('sanitize_text_field', $options);
            }

            $isValid = true;
            switch ($fieldType) {
                case 'input_radio':
                case 'select':
                case 'net_promoter_score':
                case 'ratings':
                case 'gdpr_agreement':
                case 'terms_and_condition':
                case 'input_checkbox':
                case 'multi_select':
                    if (is_array($inputValue)) {
                        $isValid = array_diff($inputValue, $options);
                        $isValid = empty($isValid);
                    } else {
                        $isValid = in_array($inputValue, $options);
                    }
                    break;
                case 'input_number':
                    if (is_array($inputValue)) {
                        $hasNonNumricValue = in_array(false, array_map('is_numeric', $inputValue));
                        if ($hasNonNumricValue) {
                            $isValid = false;
                        }
                    } else {
                        $isValid = is_numeric($inputValue);
                    }
                    break;
                case 'select_country':
                    $fieldData = Arr::get($field, 'raw');
                    $data = (new SelectCountry())->loadCountries($fieldData);
                    $validCountries = Arr::get($fieldData, 'settings.country_list.priority_based', []);
                    $validCountries = array_merge($validCountries, array_keys(Arr::get($data, 'options')));
                    $isValid = in_array($inputValue, $validCountries);
                    break;
                case 'repeater_field':
                    foreach (Arr::get($rawField, 'fields', []) as $index => $repeaterField) {
                        $repeaterFiedValue = array_filter(array_column($inputValue, $index));
                        if ($repeaterFiedValue && $error = $this->validateInput($repeaterField, $formData, $form, $fieldName, $repeaterFiedValue)) {
                            $isValid = false;
                            break;
                        }
                    }
                    break;
                case 'tabular_grid':
                    $rows = array_keys(Arr::get($rawField, 'settings.grid_rows', []));
                    $submitdRows = array_keys(Arr::get($formData, $fieldName, []));
                    $rowDiff = array_diff($submitdRows, $rows);
                    $isValid = empty($rowDiff);
                    if ($isValid) {
                        $columns = array_keys(Arr::get($rawField, 'settings.grid_columns', []));
                        $submitdCols = Arr::flatten(Arr::get($formData, $fieldName, []));
                        $colDiff = array_diff($submitdCols, $columns);
                        $isValid = empty($colDiff);
                    }
                    break;
                default:
                    break;
            }
            if (!$isValid) {
                $error = __('The given data was invalid', 'fluentform');
            }
        }
        return $error;
    }
    
    /**
     * Prevents malicious attacks when the submission
     * count exceeds in an allowed interval.
     * @throws ValidationException
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
                throw new ValidationException('', 429, null,  [
                    'errors' => [
                        'restricted' => [
                            __(apply_filters('fluentform/too_many_requests', 'Too Many Requests.', $this->form->id), 'fluentform'),
                        ],
                    ]
                ]);
            }
        }
    }
    
    /**
     * Validate form data based on the form restrictions settings.
     *
     * @param $fields
     * @throws ValidationException
     */
    private function validateRestrictions(&$fields)
    {
        $formSettings = FormMeta::retrieve('formSettings', $this->form->id);
    
        $this->form->settings = is_array($formSettings) ? $formSettings : [];
    
        $isAllowed = [
            'status'  => true,
            'message' => '',
        ];
        
        // This will check the following restriction settings.
        // 1. limitNumberOfEntries
        // 2. scheduleForm
        // 3. requireLogin
        // 4. restricted submission based on ip, country and keywords
        
        /* This filter is deprecated and will be removed soon */
        $isAllowed = apply_filters('fluentform_is_form_renderable', $isAllowed, $this->form);
        
        $isAllowed = apply_filters('fluentform/is_form_renderable', $isAllowed, $this->form);
        
        if (!$isAllowed['status']) {
            throw new ValidationException('', 422, null,  [
                'errors' => [
                    'restricted' => [
                        $isAllowed['message'],
                    ],
                ],
            ]);
        }
        
        // Since we are here, we should now handle if the form should be allowed to submit empty.
        $restrictions = Arr::get($this->form->settings, 'restrictions.denyEmptySubmission', []);
        
        $this->handleDenyEmptySubmission($restrictions, $fields);

        $formRestrictions = Arr::get($this->form->settings, 'restrictions.restrictForm', []);

        $this->handleRestrictedSubmission($formRestrictions, $fields);
    }
    
    /**
     * Handle response when empty form submission is not allowed.
     *
     * @param array $settings
     * @param $fields
     * @throws ValidationException
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
                if (!count(Helper::arrayFilterRecursive($filteredFormData))) {
                    throw new ValidationException('', 422, null,  [
                        'errors' => [
                            'restricted' => [
                                __(!($m = Arr::get($settings, 'message')) ? 'Sorry! You can\'t submit an empty form.' : $m, 'fluentform'),
                            ],
                        ],
                    ]);
                }
            }
        }
    }

    /**
     * Handle response when form submission is restricted based on ip, country or keywords.
     *
     * @param array $settings
     * @param $fields
     * @throws ValidationException
     */
    protected function handleRestrictedSubmission($settings, &$fields)
    {
        // Determine this restriction is enabled ot not
        if (!Arr::get($settings, 'enabled')) {
            return;
        }
        $ipInfo = $this->getIpInfo();
        $country = Arr::get($ipInfo, 'country');
    
        $this->checkIpRestriction($settings);
    
        $this->checkCountryRestriction($settings, $country);
    
        $this->checkKeyWordRestriction($settings);
    }
    
    
    /**
     * Validate nonce.
     * @throws ValidationException
     */
    protected function validateNonce()
    {
        $formId = $this->form->id;
        $shouldVerifyNonce = false;
        /* This filter is deprecated and will be removed soon. */
        $shouldVerifyNonce = $this->app->applyFilters('fluentform_nonce_verify', $shouldVerifyNonce, $formId);
    
        $shouldVerifyNonce = $this->app->applyFilters('fluentform/nonce_verify', $shouldVerifyNonce, $formId);
        
        if ($shouldVerifyNonce) {
            $nonce = Arr::get($this->formData, '_fluentform_' . $formId . '_fluentformnonce');
            if (!wp_verify_nonce($nonce, 'fluentform-submit-form')) {
                $errors = apply_filters_deprecated(
                    'fluentForm_nonce_error',
                    [
                        '_fluentformnonce' => [
                            __('Nonce verification failed, please try again.', 'fluentform'),
                        ],
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentForm/nonce_error',
                    'Use fluentForm/nonce_error instead of fluentForm_nonce_error.'
                );

                $errors = $this->app->applyFilters('fluentForm/nonce_error', $errors);
                throw new ValidationException('', 422, null, ['errors' => $errors]);
            }
        }
    }
    
    /** Validate Spam
     * @throws ValidationException
     */
    public function handleSpamError()
    {
        $settings = get_option('_fluentform_global_form_settings');
        if (!$settings || 'validation_failed' != Arr::get($settings, 'misc.akismet_validation')) {
            return;
        }
        
        $errors = [
            '_fluentformakismet' => __('Submission marked as spammed. Please try again', 'fluentform'),
        ];
        throw new ValidationException('', 422, null, ['errors' => $errors]);
    }
    
    public function isSpam($formData, $form)
    {
         if (!AkismetHandler::isEnabled()) {
            return false;
        }
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
        return apply_filters('fluentform/akismet_spam_result', $isSpam, $form->id, $formData);
    }
    
    /**
     * Validate reCaptcha.
     * @throws ValidationException
     */
    private function validateReCaptcha()
    {
        $hasAutoRecap =  apply_filters_deprecated(
            'ff_has_auto_recaptcha',
            [
                false
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/has_recaptcha',
            'Use fluentform/has_recaptcha instead of ff_has_auto_recaptcha.'
        );
        $autoInclude = apply_filters('fluentform/has_recaptcha', $hasAutoRecap);
        if (FormFieldsParser::hasElement($this->form, 'recaptcha') || $autoInclude) {
            $keys = get_option('_fluentform_reCaptcha_details');
            $token = Arr::get($this->formData, 'g-recaptcha-response');
            $version = 'v2_visible';
            if (!empty($keys['api_version'])) {
                $version = $keys['api_version'];
            }
            $isValid = ReCaptcha::validate($token, $keys['secretKey'], $version);
            
            if (!$isValid) {
                throw new ValidationException('', 422, null, [
                    'errors' => [
                        'g-recaptcha-response' => [
                            __('reCaptcha verification failed, please try again.', 'fluentform'),
                        ],
                    ],
                ]);
            }
        }
    }

    /**
     * Validate hCaptcha.
     * @throws ValidationException
     */
    private function validateHCaptcha()
    {
        $hasAutoHcap = apply_filters_deprecated(
            'ff_has_auto_hcaptcha',
            [
                false
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/has_hcaptcha',
            'Use fluentform/has_hcaptcha instead of ff_has_auto_hcaptcha.'
        );
        $autoInclude = apply_filters('fluentform/has_hcaptcha', $hasAutoHcap);
        FormFieldsParser::resetData();
        if (FormFieldsParser::hasElement($this->form, 'hcaptcha') || $autoInclude) {
            $keys = get_option('_fluentform_hCaptcha_details');
            $token = Arr::get($this->formData, 'h-captcha-response');
            $isValid = HCaptcha::validate($token, $keys['secretKey']);
            
            if (!$isValid) {
                throw new ValidationException('', 422, null, [
                    'errors' => [
                        'h-captcha-response' => [
                            __('hCaptcha verification failed, please try again.', 'fluentform'),
                        ],
                    ],
                ]);
            }
        }
    }
    
    /**
     * Validate turnstile.
     * @throws ValidationException
     */
    private function validateTurnstile()
    {
        $hasAutoTurnsTile = apply_filters_deprecated(
            'ff_has_auto_turnstile',
            [
                false
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
                throw new ValidationException('', 422, null, [
                    'errors' => [
                        'cf-turnstile-response' => [
                            __('Turnstile verification failed, please try again.', 'fluentform'),
                        ],
                    ],
                ]);
            }
        }
    }
    
    
    /**
     * Delegate the validation rules & messages to the
     * ones that the validation library recognizes.
     *
     * @param $rules
     * @param $messages
     * @param array $search
     * @param array $replace
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
     * Get IP info from ipinfo.io
     *
     * @throws ValidationException
     */
    private function getIpInfo() {
        $token = Helper::getIpinfo();
        $url = 'https://ipinfo.io';
        if ($token) {
            $url = 'https://ipinfo.io/?token=' . $token;
        }
        $data = wp_remote_get($url);
        $code = wp_remote_retrieve_response_code($data);
        $body = wp_remote_retrieve_body($data);
        $result = \json_decode($body, true);
        if ($code === 200) {
            return $result;
        } else {
            $message = __('Sorry! There is an error in your geocode IP address settings. Please check the token', 'fluentform');
            self::throwValidationException($message);
        }
    }
    
    /**
     * @param $value
     * @param $providedKeywords
     * @return bool
     */
    public static function containsRestrictedKeywords($value, $providedKeywords) {
        preg_match_all('/\b[\p{L}\d\s]+\b/u', $value, $matches);
        $words = $matches[0] ?? [];

        foreach ($providedKeywords as $keyword) {
            foreach ($words as $word) {
                if (strtoupper($word) === strtoupper($keyword)) {
                    return true;
                }
            }
        }

        return false;
    }
    
    
    private function checkIpRestriction($settings)
    {
        $ip = $this->app->request->getIp();
        if (Arr::get($settings, 'fields.ip.status') && $ip) {
            $providedIp = array_map('trim', explode(',', Arr::get($settings, 'fields.ip.values')));

            $isFailed = Arr::get($settings, 'fields.ip.validation_type') === 'fail_on_condition_met';

            $failedSubmissionIfExists = $isFailed && in_array($ip, $providedIp);
            $allowSubmissionIfNotExists = !$isFailed && !in_array($ip, $providedIp);

            if ($failedSubmissionIfExists || $allowSubmissionIfNotExists) {
                $defaultMessage = __('Sorry! You can\'t submit a form from your IP address.', 'fluentform');
                $message = Arr::get($settings, 'fields.ip.message', $defaultMessage);
                self::throwValidationException($message);
            }
        }
    }
    
    private function checkCountryRestriction($settings, $country)
    {
        if (Arr::get($settings, 'fields.country.status') && $country) {
            $providedCountry = Arr::get($settings, 'fields.country.values');

            $isFailed = Arr::get($settings, 'fields.country.validation_type') === 'fail_on_condition_met';

            $failedSubmissionIfExists = $isFailed && in_array($country, $providedCountry);
            $allowSubmissionIfNotExists = !$isFailed && !in_array($country, $providedCountry);

            if ($failedSubmissionIfExists || $allowSubmissionIfNotExists) {
                $defaultMessage = __('Sorry! You can\'t submit a form from the country you are residing.', 'fluentform');
                $message = Arr::get($settings, 'fields.country.message', $defaultMessage);
                self::throwValidationException($message);
            }
        }
    }
    
    private function checkKeyWordRestriction($settings)
    {
        if (!Arr::get($settings, 'fields.keywords.status')) {
            return;
        }
        
        $providedKeywords = explode(',', Arr::get($settings, 'fields.keywords.values'));
        $providedKeywords =  array_map('trim', $providedKeywords);
        $inputSubmission = array_intersect_key(
            $this->formData,
            array_flip(
                array_keys(
                    FormFieldsParser::getInputs($this->form)
                )
            )
        );
        $defaultMessage = __('Sorry! Your submission contains some restricted keywords.', 'fluentform');
        $message = Arr::get($settings, 'fields.keywords.message', $defaultMessage);

        self::checkKeywordsMatching($inputSubmission, $message, $providedKeywords);
    }

    private static function checkKeywordsMatching($inputSubmission, $message, $providedKeywords)
    {
        foreach ($inputSubmission as $value) {
            if (!empty($value)) {
                if (is_array($value)) {
                    self::checkKeywordsMatching($value, $message, $providedKeywords);
                } else {
                    if (self::containsRestrictedKeywords($value, $providedKeywords)) {
                        self::throwValidationException($message);
                    }
                }
            }
        }
    }
    
    /**
     * @throws ValidationException
     */
    public static function throwValidationException($message) {
        throw new ValidationException('', 422, null,  [
            'errors' => [
                'restricted' => [
                    $message
                ],
            ],
        ]);
    }
}
