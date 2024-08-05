<?php

namespace FluentForm\App\Modules\Form\Settings;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\App\Modules\Form\Form;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\Framework\Foundation\Application;
use FluentForm\App\Modules\Form\Settings\Validator\Validator;

class FormSettings
{
    /**
     * Request object
     *
     * @var \FluentForm\Framework\Request\Request
     */
    private $request;

    private $app;

    private $formId;

    /**
     * The settings (fluentform_form_meta) query builder.
     *
     * @var \WpFluent\QueryBuilder\QueryBuilderHandler
     */
    private $settingsQuery;

    /**
     * Construct the object
     *
     * @throws \Exception
     */
    public function __construct(Application $application)
    {
        $this->app = $application;

        $this->request = $application->request;
        $this->formId = intval($this->request->get('form_id'));

        $this->settingsQuery = wpFluent()->table('fluentform_form_meta')->where('form_id', $this->formId);
    }

    /**
     * Get settings for a particular form by id
     */
    public function index()
    {
        $metaKey = sanitize_text_field($this->request->get('meta_key'));

        // We'll always try to get a collection for a given meta key.
        // Acknowledging that a certain meta key can have multiple
        // results. The developer using the api knows beforehand
        // that whether the expected result contains multiple
        // or one value. The developer will access that way.
        $query = $this->settingsQuery->where('meta_key', $metaKey);

        $result = $query->get();

        foreach ($result as $item) {
            $item->value = json_decode($item->value, true);
            if ('notifications' == $metaKey) {
                if (!$item->value) {
                    $item->value = ['name' => ''];
                }
            }
            if (isset($item->value['layout']) && !isset($item->value['layout']['asteriskPlacement'])) {
                $item->value['layout']['asteriskPlacement'] = 'asterisk-right';
            }
        }
    
        $result = apply_filters_deprecated(
            'fluentform_get_meta_key_settings_response',
            [
                $result,
                $this->formId,
                $metaKey
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/get_meta_key_settings_response',
            'Use fluentform/get_meta_key_settings_response instead of fluentform_get_meta_key_settings_response.'
        );

        $result = $this->app->applyFilters('fluentform/get_meta_key_settings_response', $result, $this->formId, $metaKey);
        wp_send_json_success(['result' => $result], 200);
    }

    public function getGeneralSettingsAjax()
    {
        $formId = intval($this->request->get('form_id'));
        $form = new Form($this->app);
        $settings = [
            'generalSettings'            => $form->getFormsDefaultSettings($formId),
            'advancedValidationSettings' => $form->getAdvancedValidationSettings($formId),
        ];

        $settings = apply_filters_deprecated(
            'fluentform_form_settings_ajax',
            [
                $settings,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_settings_ajax',
            'Use fluentform/form_settings_ajax instead of fluentform_form_settings_ajax.'
        );

        $settings = $this->app->applyFilters('fluentform/form_settings_ajax', $settings, $formId);

        wp_send_json_success($settings, 200);
    }

    public function saveGeneralSettingsAjax()
    {
        $formId = intval($this->request->get('form_id'));
        $form = new Form($this->app);
        $formSettings = \json_decode($this->request->get('formSettings'), true);

        $sanitizerMap = [
            'redirectTo'                 => 'sanitize_text_field',
            'redirectMessage'            => 'fluentform_sanitize_html',
            'messageToShow'              => 'fluentform_sanitize_html',
            'customPage'                 => 'sanitize_text_field',
            'samePageFormBehavior'       => 'sanitize_text_field',
            'customUrl'                  => 'sanitize_url',
            'enabled'                    => 'rest_sanitize_boolean',
            'numberOfEntries'            => 'intval',
            'period'                     => 'intval',
            'limitReachedMsg'            => 'sanitize_text_field',
            'start'                      => 'sanitize_text_field',
            'end'                        => 'sanitize_text_field',
            'pendingMsg'                 => 'sanitize_text_field',
            'expiredMsg'                 => 'sanitize_text_field',
            'requireLoginMsg'            => 'sanitize_text_field',
            'message'                    => 'sanitize_text_field',
            'labelPlacement'             => 'sanitize_text_field',
            'helpMessagePlacement'       => 'sanitize_text_field',
            'errorMessagePlacement'      => 'sanitize_text_field',
            'asteriskPlacement'          => 'sanitize_text_field',
            'delete_entry_on_submission' => 'sanitize_text_field',
            'id'                         => 'intval',
            'showLabel'                  => 'rest_sanitize_boolean',
            'showCount'                  => 'rest_sanitize_boolean',
            'status'                     => 'rest_sanitize_boolean',
            'type'                       => 'sanitize_text_field',
            'field'                      => 'sanitize_text_field',
            'operator'                   => 'sanitize_text_field',
            'value'                      => 'sanitize_text_field',
            'error_message'              => 'sanitize_text_field',
            'validation_type'            => 'sanitize_text_field',
        ];
        $formSettings = $this->sanitizeData($formSettings, $sanitizerMap);

        $advancedValidationSettings = \json_decode($this->request->get('advancedValidationSettings'), true);
        $advancedValidationSettings = $this->sanitizeData($advancedValidationSettings, $sanitizerMap);

        Validator::validate(
            'confirmations',
            ArrayHelper::get($formSettings, 'confirmation', [])
        );

        $form->updateMeta($formId, 'formSettings', $formSettings);
        $form->updateMeta($formId, 'advancedValidationSettings', $advancedValidationSettings);

        $deleteAfterXDaysStatus = ArrayHelper::get($formSettings, 'delete_after_x_days');
        $deleteDaysCount = ArrayHelper::get($formSettings, 'auto_delete_days');
        $deleteOnSubmission = ArrayHelper::get($formSettings, 'delete_entry_on_submission');

        if ('yes' != $deleteOnSubmission && $deleteDaysCount && 'yes' == $deleteAfterXDaysStatus) {
            // We have to set meta values
            $form->updateMeta($formId, 'auto_delete_days', intval($deleteDaysCount));
        } else {
            // we have to delete meta values
            $form->deleteMeta($formId, 'auto_delete_days');
        }

        do_action_deprecated(
            'fluentform_after_save_form_settings',
            [
                $formId,
                $this->request->all()
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/after_save_form_settings',
            'Use fluentform/after_save_form_settings instead of fluentform_after_save_form_settings.'
        );

        $this->app->doAction('fluentform/after_save_form_settings', $formId, $this->request->all());

        wp_send_json_success([
            'message' => __('Settings has been saved.', 'fluentform'),
        ], 200);
    }

    /**
     * Save settings/meta for a form in database
     */
    public function store()
    {
        $value = $this->request->get('value', '');

        $valueArray = $value ? json_decode($value, true) : [];

        $key = sanitize_text_field($this->request->get('meta_key'));

        if ('formSettings' == $key) {
            Validator::validate(
                'confirmations',
                ArrayHelper::get(
                    $valueArray,
                    'confirmation',
                    []
                )
            );
        } else {
            Validator::validate($key, $valueArray);
        }
        $sanitizerMap = [
            'name'      => 'sanitize_text_field',
            'field'     => 'sanitize_text_field',
            'email'     => 'sanitize_text_field',
            'operator'  => 'sanitize_text_field',
            'value'     => 'sanitize_text_field',
            'fromName'  => 'sanitize_text_field',
            'fromEmail' => 'sanitize_text_field',
            'replyTo'   => 'sanitize_text_field',
            'bcc'       => 'sanitize_text_field',
            'subject'   => 'sanitize_text_field',
            'message'   => 'wp_kses_post',
            'status'    => 'rest_sanitize_boolean',
            'enabled'   => 'rest_sanitize_boolean',
            'type'      => 'sanitize_text_field',
            'url'       => 'sanitize_url',
            'webhook'   => 'sanitize_url',
            'textTitle' => 'sanitize_text_field',
        ];

        $valueArray = $this->sanitizeData($valueArray, $sanitizerMap);
        $value = json_encode($valueArray);

        $data = [
            'meta_key' => $key,
            'value'    => $value,
            'form_id'  => $this->formId,
        ];

        // If the request has an valid id field it's safe to assume
        // that the user wants to update an existing settings.
        // So, we'll proceed to do so by finding it first.
        $id = intval($this->request->get('id'));

        if ($id) {
            $settings = $this->settingsQuery->find($id);
        }

        if (isset($settings)) {
            $this->settingsQuery->where('id', $settings->id)->update($data);
            $insertId = $settings->id;
        } else {
            $insertId = $this->settingsQuery->insertGetId($data);
        }

        wp_send_json_success([
            'message'  => __('Settings has been saved.', 'fluentform'),
            'settings' => json_decode($value, true),
            'id'       => $insertId,
        ], 200);
    }

    /**
     * Delete settings/meta from database for a given form
     */
    public function remove()
    {
        $id = intval($this->request->get('id'));

        $this->settingsQuery->where('id', $id)->delete();

        wp_send_json([], 200);
    }

    private function sanitizeData($settings, $sanitizerMap)
    {
        if (fluentformCanUnfilteredHTML()) {
            return $settings;
        }
        return fluentform_backend_sanitizer($settings, $sanitizerMap);
    }
}
