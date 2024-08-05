<?php

namespace FluentForm\App\Services\Form;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\Form;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Models\Submission;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\Browser\Browser;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentForm\App\Services\Submission\SubmissionService;
use FluentForm\Database\Migrations\SubmissionDetails;
use FluentForm\Framework\Foundation\App;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use FluentForm\Framework\Validator\ValidationException;


class SubmissionHandlerService
{
    protected $app;
    protected $form;
    protected $fields;
    protected $formData;
    protected $validationService;
    protected $submissionService;
    
    public function __construct()
    {
        $this->app = App::getInstance();
        $this->validationService = new FormValidationService();
        $this->submissionService = new SubmissionService();
    }
    
    /**
     * Form Submission
     * @param $formDataRaw
     * @param $formId
     * @return array
     * @throws \FluentForm\Framework\Validator\ValidationException
     */
    public function handleSubmission($formDataRaw, $formId)
    {
        $this->prepareHandler($formId, $formDataRaw);
        $insertData = $this->handleValidation();
        $insertId = $this->insertSubmission($insertData, $formDataRaw, $formId);
    
        return $this->processSubmissionData($insertId, $this->formData, $this->form);
    }
    
    /**
     * @throws ValidationException
     */
    protected function prepareHandler($formId, $formDataRaw)
    {
        $this->form = Form::find($formId);
        
        if (!$this->form) {
            throw new ValidationException('', 422, null, ['errors' => 'Sorry, No corresponding form found']);
        }
        
        // Parse the form and get the flat inputs with validations.
        $this->fields = FormFieldsParser::getEssentialInputs($this->form, $formDataRaw, ['rules', 'raw']);
        
        $formData = fluentFormSanitizer($formDataRaw, null, $this->fields);

        $acceptedFieldKeys = array_merge($this->fields, array_flip(Helper::getWhiteListedFields($formId)));
        
        $this->formData = array_intersect_key($formData, $acceptedFieldKeys);
    }
    
    
    /**
     * Prepare the data to be inserted to the database.
     * @param boolean $formData
     * @return array
     */
    public function prepareInsertData($formData = false)
    {
        $formId = $this->form->id;
        if (!$formData) {
            $formData = $this->formData;
        }
        $previousItem = Submission::where('form_id', $formId)->orderBy('id', 'DESC')->first();
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

        $disableIpLog = apply_filters_deprecated(
            'fluentform_disable_ip_logging',
            [
                false,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/disable_ip_logging',
            'Use fluentform/disable_ip_logging instead of fluentform_disable_ip_logging.'
        );

        if ((defined('FLUENTFROM_DISABLE_IP_LOGGING') && FLUENTFROM_DISABLE_IP_LOGGING) || apply_filters('fluentform/disable_ip_logging',
                $disableIpLog, $formId)) {
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
    
    public function processSubmissionData($insertId, $formData, $form)
    {
        $form = isset($this->form) ? $this->form : $form;
        do_action_deprecated(
            'fluentform_before_form_actions_processing',
            [
                $insertId,
                $this->formData,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/before_form_actions_processing',
            'Use fluentform/before_form_actions_processing instead of fluentform_before_form_actions_processing.'
        );
    
        do_action('fluentform/before_form_actions_processing', $insertId, $this->formData, $form);
        
        if ($insertId) {
            ob_start();
            $this->submissionService->recordEntryDetails($insertId, $form->id, $formData);
            $isError = ob_get_clean();
            if ($isError) {
                SubmissionDetails::migrate();
            }
        }
        $returnData = $this->getReturnData($insertId, $form, $formData);
        $error = '';
        try {

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
                'fluentform/submission_inserted_' . $form->type . '_form',
                'Use fluentform/submission_inserted_' . $form->type . '_form instead of fluentform_submission_inserted_' . $form->type . '_form'
            );

            $this->app->doAction(
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
    
    /**
     * Return Formatted Response Data
     * @param $insertId
     * @param $form
     * @param $formData
     * @return mixed
     */
    public function getReturnData($insertId, $form, $formData)
    {
        if (empty($form->settings)) {
            $formSettings = FormMeta::retrieve('formSettings', $form->id);
            $form->settings = is_array($formSettings) ? $formSettings : [];
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

        $confirmation = apply_filters(
            'fluentform/form_submission_confirmation',
            $confirmation,
            $formData,
            $form
        );
        if ('samePage' == Arr::get($confirmation, 'redirectTo')) {
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
            $message = $message ? $message : __('The form has been successfully submitted.', 'fluentform');

            $message = fluentform_sanitize_html($message);

            $returnData = [
                'message' => do_shortcode($message),
                'action'  => $confirmation['samePageFormBehavior'],
            ];
        } else {
            $redirectUrl = Arr::get($confirmation, 'customUrl');
            if ('customPage' == $confirmation['redirectTo']) {
                $redirectUrl = get_permalink($confirmation['customPage']);
            }
            $enableQueryString = Arr::get($confirmation, 'enable_query_string') === 'yes';
            $queryStrings = Arr::get($confirmation, 'query_strings');
    
            if ($enableQueryString && $queryStrings) {
                $separator = strpos($redirectUrl, '?') !== false ? '&' : '?';
                $redirectUrl .= $separator . $queryStrings;
            }
            $parseUrl = apply_filters_deprecated('fluentform_will_parse_url_value', [
                true,
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
                 * Encode Redirect Value
                 */
                $encodeUrl = apply_filters('fluentform/will_encode_url_value',false,$redirectUrl, $insertId, $form, $formData);
                if (strpos($redirectUrl, '&') || '=' == substr($redirectUrl, -1) || $encodeUrl) {
                    $urlArray = explode('?', $redirectUrl);
                    $baseUrl = array_shift($urlArray);
                    
                    $query = wp_parse_url($redirectUrl)['query'];
                    $queryParams = explode('&', $query);
                    
                    $params = [];
                    foreach ($queryParams as $queryParam) {
                        $paramArray = explode('=', $queryParam);
                        if (!empty($paramArray[1])) {
                            $params[$paramArray[0]] = urlencode($paramArray[1]);
                        }
                    }
                    $redirectUrl = add_query_arg($params, $baseUrl);
                }
            }
            
            $message = ShortCodeParser::parse(
                Arr::get($confirmation, 'redirectMessage', ''),
                $insertId,
                $formData,
                $form,
                false,
                true
            );
    
            $redirectUrl = apply_filters('fluentform/redirect_url_value', wp_sanitize_redirect(urldecode($redirectUrl)), $insertId, $form, $formData);
            $returnData = [
                'redirectUrl' => $redirectUrl,
                'message'     => fluentform_sanitize_html($message),
            ];
        }
    
        $returnData = apply_filters_deprecated('fluentform_submission_confirmation', [
                $returnData,
                $form,
                $confirmation,
                $insertId,
                $formData
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/submission_confirmation',
            'Use fluentform/submission_confirmation instead of fluentform_submission_confirmation.'
        );
        
        return $this->app->applyFilters(
            'fluentform/submission_confirmation',
            $returnData,
            $form,
            $confirmation,
            $insertId,
            $formData
        );
    }
    
    /**
     * Validates Submission
     * @throws ValidationException
     */
    private function handleValidation()
    {
        /* Now validate the data using the previous validations. */
        $this->validationService->setForm($this->form);
        $this->validationService->setFormData($this->formData);

        $this->validationService->validateSubmission($this->fields, $this->formData);
    
        $insertData = $this->prepareInsertData();
        
        if ($this->validationService->isSpam($this->formData, $this->form)) {
            $insertData['status'] = 'spam';
            $this->validationService->handleSpamError();
        }
        return $insertData;
    }
    
    protected function insertSubmission($insertData, $formDataRaw, $formId)
    {
        do_action_deprecated(
            'fluentform_before_insert_submission',
            [
                $insertData,
                $formDataRaw,
                $this->form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/before_insert_submission',
            'Use fluentform/before_insert_submission instead of fluentform_before_insert_submission.'
        );

        do_action('fluentform/before_insert_submission', $insertData, $formDataRaw, $this->form);
        
        if ($this->form->has_payment) {
            do_action_deprecated(
                'fluentform_before_insert_payment_form',
                [
                    $insertData,
                    $formDataRaw,
                    $this->form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/before_insert_payment_form',
                'Use fluentform/before_insert_payment_form instead of fluentform_before_insert_payment_form.'
            );
            do_action('fluentform/before_insert_payment_form', $insertData, $formDataRaw, $this->form);
        }
        
        $insertId = Submission::insertGetId($insertData);
    
        do_action('fluentform/notify_on_form_submit', $insertId, $this->formData, $this->form);
        
        $uidHash = md5(wp_generate_uuid4() . $insertId);
        Helper::setSubmissionMeta($insertId, '_entry_uid_hash', $uidHash, $formId);
        
        return $insertId;
    }
    
}
