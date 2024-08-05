<?php

/**
 * Add all ajax hooks
 */

use FluentForm\App\Modules\Acl\Acl;

/**
 * App instance
 *
 * @var $app \FluentForm\Framework\Foundation\Application
 */

$app->addAction('wp_ajax_nopriv_fluentform_submit', function () use ($app) {
    (new \FluentForm\App\Modules\SubmissionHandler\SubmissionHandler($app))->submit();
});

$app->addAction('wp_ajax_fluentform_submit', function () use ($app) {
    //    (new \FluentForm\App\Modules\Form\FormHandler($app))->onSubmit();
    (new \FluentForm\App\Modules\SubmissionHandler\SubmissionHandler($app))->submit();
});

/*
 * We are using this ajax call for updating form fields
 * REST API seems not working for some servers with Mod Security Enabled
 */
$app->addAction('wp_ajax_fluentform-form-update', function () use ($app) {
    Acl::verify('fluentform_forms_manager');
    try {
        $data = $app->request->all();
        $isValidJson = (!empty($data['formFields'])) && json_decode($data['formFields'], true);

        if(!$isValidJson) {
            wp_send_json([
                'message' => 'Looks like the provided JSON is invalid. Please try again or contact support',
                'reason' => 'formFields JSON validation failed'
            ], 422);
        }

        $formService = new \FluentForm\App\Services\Form\FormService();
        $form = $formService->update($data);
        wp_send_json([
            'message' => __('The form is successfully updated.', 'fluentform')
        ], 200);
    } catch (\Exception $exception) {
        wp_send_json([
            'message' => $exception->getMessage(),
        ], 422);
    }
});

/*
 * This ajax endpoint is used to update form general settings
 * Mod-Security also block this request
 */
$app->addAction('wp_ajax_fluentform-save-settings-general-formSettings', function () use ($app) {
    Acl::verify('fluentform_forms_manager');
    try {
        $settingsService = new \FluentForm\App\Services\Settings\SettingsService();
        $settingsService->saveGeneral($app->request->all());
        wp_send_json([
            'message' => __('Settings has been saved.', 'fluentform'),
        ]);
    } catch (\FluentForm\Framework\Validator\ValidationException $exception) {
        wp_send_json($exception->errors(), 422);
    }
});

/*
 * This ajax endpoint is used to update form email notifications settings
 * Mod-Security also block this request
 */
$app->addAction('wp_ajax_fluentform-save-form-email-notification', function () use ($app) {
    Acl::verify('fluentform_forms_manager');
    try {
        $settingsService = new \FluentForm\App\Services\Settings\SettingsService();
        [$settingsId, $settings] = $settingsService->store($app->request->all());

        wp_send_json([
            'message'  => __('Settings has been saved.', 'fluentform'),
            'id'       => $settingsId,
            'settings' => $settings,
        ]);
    } catch (\FluentForm\Framework\Validator\ValidationException $exception) {
        wp_send_json($exception->errors(), 422);
    }
});


$app->addAction('wp_ajax_fluentform-forms', function () use ($app) {
    dd('wp_ajax_fluentform-forms');
    Acl::verify('fluentform_dashboard_access');
    (new \FluentForm\App\Modules\Form\Form($app))->index();
});

$app->addAction('wp_ajax_fluentform-form-store', function () use ($app) {
    dd('wp_ajax_fluentform-form-store');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Form($app))->store();
});

$app->addAction('wp_ajax_fluentform-form-find', function () use ($app) {
    //No usage found
    Acl::verify('fluentform_dashboard_access');
    (new \FluentForm\App\Modules\Form\Form($app))->find();
});

$app->addAction('wp_ajax_fluentform-form-delete', function () use ($app) {
    dd('wp_ajax_fluentform-form-delete');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Form($app))->delete();
});

$app->addAction('wp_ajax_fluentform-form-duplicate', function () use ($app) {
    dd('wp_ajax_fluentform-form-duplicate');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Form($app))->duplicate();
});
$app->addAdminAjaxAction('fluentform-form-find-shortcode-locations', function () use ($app) {
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Form($app))->findFormLocations();
});

$app->addAction('wp_ajax_fluentform-convert-to-conversational', function () use ($app) {
    dd('wp_ajax_fluentform-convert-to-conversational');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Form($app))->convertToConversational();
});

$app->addAction('wp_ajax_fluentform_get_all_entries', function () {
    dd('wp_ajax_fluentform_get_all_entries');
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Entries\Entries())->getAllFormEntries();
});

$app->addAction('wp_ajax_fluentform_get_all_entries_report', function () {
    dd('wp_ajax_fluentform_get_all_entries_report');
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Entries\Entries())->getEntriesReport();
});

$app->addAction('wp_ajax_fluentform-form-inputs', function () use ($app) {
    dd('wp_ajax_fluentform-form-inputs');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Inputs($app))->index();
});

$app->addAction('wp_ajax_fluentform-load-editor-shortcodes', function () use ($app) {
    dd('wp_ajax_fluentform-load-editor-shortcodes');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Component\Component($app))->getEditorShortcodes();
});

$app->addAction('wp_ajax_fluentform-load-all-editor-shortcodes', function () use ($app) {
    dd('wp_ajax_fluentform-load-all-editor-shortcodes');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Component\Component($app))->getAllEditorShortcodes();
});

$app->addAction('wp_ajax_fluentform-settings-formSettings', function () use ($app) {
    dd('wp_ajax_fluentform-settings-formSettings');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Settings\FormSettings($app))->index();
});

$app->addAction('wp_ajax_fluentform-settings-general-formSettings', function () use ($app) {
    dd('wp_ajax_fluentform-settings-general-formSettings');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Settings\FormSettings($app))->getGeneralSettingsAjax();
});

$app->addAction('wp_ajax_fluentform-settings-formSettings-store', function () use ($app) {
    dd('wp_ajax_fluentform-settings-formSettings-store');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Settings\FormSettings($app))->store();
});

$app->addAction('wp_ajax_fluentform-settings-formSettings-remove', function () use ($app) {
    dd('wp_ajax_fluentform-settings-formSettings-remove');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Settings\FormSettings($app))->remove();
});

$app->addAction('wp_ajax_fluentform-get-form-custom_css_js', function () {
    dd('wp_ajax_fluentform-get-form-custom_css_js');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Settings\FormCssJs())->getSettingsAjax();
});

$app->addAction('wp_ajax_fluentform-save-form-custom_css_js', function () {
    dd('wp_ajax_fluentform-save-form-custom_css_js');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Settings\FormCssJs())->saveSettingsAjax();
});

$app->addAction('wp_ajax_fluentform-save-form-entry_column_view_settings', function () {
    dd('wp_ajax_fluentform-save-form-entry_column_view_settings');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Settings\EntryColumnViewSettings())->saveVisibleColumnsAjax();
});

$app->addAction('wp_ajax_fluentform-save-form-entry_column_order_settings', function () {
    dd('wp_ajax_fluentform-save-form-entry_column_order_settings');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Settings\EntryColumnViewSettings())->saveEntryColumnsOrderAjax();
});

$app->addAction('wp_ajax_fluentform-reset-form-entry_column_order_settings', function () {
    dd('wp_ajax_fluentform-reset-form-entry_column_order_settings');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Settings\EntryColumnViewSettings())->resetEntryDisplaySettings();
});

$app->addAction('wp_ajax_fluentform-load-editor-components', function () use ($app) {
    dd('wp_ajax_fluentform-load-editor-components');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Component\Component($app))->index();
});

$app->addAction('wp_ajax_fluentform-form-entry-counts', function () {
    dd('wp_ajax_fluentform-form-entry-counts');
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Entries\Entries())->getEntriesGroup();
});

$app->addAction('wp_ajax_fluentform-form-entries', function () {
    dd('wp_ajax_fluentform-form-entries');
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Entries\Entries())->getEntries();
});

$app->addAction('wp_ajax_fluentform-form-report', function () use ($app) {
    dd('wp_ajax_fluentform-form-report');
    $formId = intval($app->request->get('form_id'));
    Acl::verify('fluentform_entries_viewer', $formId);
    (new \FluentForm\App\Modules\Entries\Report($app))->getReport($formId);
});

$app->addAction('wp_ajax_fluentform-form-entries-export', function () use ($app) {
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Transfer\Transfer())->exportEntries();
});

$app->addAction('wp_ajax_fluentform-get-entry', function () {
    //No usage found
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Entries\Entries())->getEntry();
});

$app->addAction('wp_ajax_fluentform-update-entry-user', function () {
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Entries\Entries())->changeEntryUser();
});

$app->addAction('wp_ajax_fluentform-get-users', function () {
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Entries\Entries())->getUsers();
});

$app->addAction('wp_ajax_fluentform-get-entry-notes', function () {
    dd('wp_ajax_fluentform-get-entry-notes');
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Entries\Entries())->getNotes();
});

$app->addAction('wp_ajax_fluentform-add-entry-note', function () {
    dd('wp_ajax_fluentform-add-entry-note');
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Entries\Entries())->addNote();
});

$app->addAction('wp_ajax_fluentform-get-entry-logs', function () use ($app) {
    dd('wp_ajax_fluentform-get-entry-logs');
    Acl::verify('fluentform_entries_viewer');
    $entry_id = intval($app->request->get('entry_id'));
    $logType = sanitize_text_field($app->request->get('log_type'));
    (new \FluentForm\App\Modules\Logger\DataLogger($app))->getLogsByEntry($entry_id, $logType);
});

$app->addAction('wp_ajax_fluentform_get_activity_log_filters', function () use ($app) {
    dd('wp_ajax_fluentform_get_activity_log_filters');
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Logger\DataLogger($app))->getLogFilters();
});

$app->addAction('wp_ajax_fluentform_get_activity_api_log_filters', function () use ($app) {
    dd('wp_ajax_fluentform_get_activity_api_log_filters');
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Logger\DataLogger($app))->getApiLogFilters();
});

$app->addAction('wp_ajax_fluentform_get_all_logs', function () use ($app) {
    dd('wp_ajax_fluentform_get_all_logs');
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Logger\DataLogger($app))->getAllLogs();
});

$app->addAction('wp_ajax_fluentform_get_api_logs', function () use ($app) {
    dd('wp_ajax_fluentform_get_api_logs');
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Logger\DataLogger($app))->getApiLogs();
});

$app->addAction('wp_ajax_fluentform_retry_api_action', function () use ($app) {
    // No usage found
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Logger\DataLogger($app))->retryApiAction();
});

$app->addAction('wp_ajax_fluentform_delete_logs_by_ids', function () use ($app) {
    dd('wp_ajax_fluentform_delete_logs_by_ids');
    Acl::verify('fluentform_manage_entries');
    (new \FluentForm\App\Modules\Logger\DataLogger($app))->deleteLogsByIds();
});

$app->addAction('wp_ajax_fluentform_delete_api_logs_by_ids', function () use ($app) {
    dd('wp_ajax_fluentform_delete_api_logs_by_ids');
    Acl::verify('fluentform_manage_entries');
    (new \FluentForm\App\Modules\Logger\DataLogger($app))->deleteApiLogsByIds();
});

$app->addAction('wp_ajax_fluentform-reset-analytics', function () use ($app) {
    dd('wp_ajax_fluentform-reset-analytics');
    Acl::verify('fluentform_manage_entries');
    (new \FluentForm\App\Modules\Form\Analytics($app))->resetFormAnalytics();
});

$app->addAction('wp_ajax_fluentform-change-entry-status', function () {
    Acl::verify('fluentform_manage_entries');
    (new \FluentForm\App\Modules\Entries\Entries())->changeEntryStatus();
});

$app->addAction('wp_ajax_fluentform-delete-entry', function () {
    dd('wp_ajax_fluentform-delete-entry');
    Acl::verify('fluentform_manage_entries');
    (new \FluentForm\App\Modules\Entries\Entries())->deleteEntry();
});

$app->addAction('wp_ajax_fluentform-change-entry-favorites', function () {
    dd('wp_ajax_fluentform-change-entry-favorites');
    Acl::verify('fluentform_entries_viewer');
    (new \FluentForm\App\Modules\Entries\Entries())->favoriteChange();
});

$app->addAction('wp_ajax_fluentform-do_entry_bulk_actions', function () {
    dd('wp_ajax_fluentform-change-entry-favorites');
    Acl::verify('fluentform_manage_entries');
    (new \FluentForm\App\Modules\Entries\Entries())->handleBulkAction();
});

$app->addAction('wp_ajax_fluentform-get-extra-form-settings', function () use ($app) {
    dd('fluentform-get-extra-form-settings'); // ajax call from resources/assets/admin/views/GlobalSettings.vue, that is never used
    Acl::verify('fluentform_forms_manager');
    (new FluentForm\App\Modules\Form\Settings\ExtraSettings($app))->getExtraSettingNavs();
});

$app->addAction('wp_ajax_fluentform-get-form-settings-extra-component', function () use ($app) {
    dd('wp_ajax_fluentform-get-form-settings-extra-component');
    Acl::verify('fluentform_forms_manager');
    (new FluentForm\App\Modules\Form\Settings\ExtraSettings($app))->getExtraSettingsComponent();
});

$app->addAction(
    'wp_ajax_fluentform-get-pages',
    function () {
        dd('wp_ajax_fluentform-get-pages');
        Acl::verify('fluentform_forms_manager');

        $pages = get_pages();
        $formattedPages = [];

        foreach ($pages as $index => $page) {
            $formattedPages[] = [
                'ID'         => $page->ID,
                'post_title' => $page->post_title,
                'guid'       => $page->guid,
            ];
        }

        wp_send_json_success([
            'pages' => $formattedPages,
        ], 200);
    }
);

$app->addAction('wp_ajax_fluentform_notice_action_track_yes', function () {
    Acl::hasAnyFormPermission();
    (new FluentForm\App\Modules\Track\TrackModule())->sendInitialInfo();
});

$app->addAction('wp_ajax_fluentform_install_fluentsmtp', function () {
    Acl::verify('fluentform_settings_manager');
    (new FluentForm\App\Modules\Track\SetupModule())->installPlugin('fluent-smtp');
});

// Export forms
$app->addAction('wp_ajax_fluentform-export-forms', function () use ($app) {
    Acl::verify('fluentform_settings_manager');
    (new \FluentForm\App\Modules\Transfer\Transfer())->exportForms();
});

// Import forms
$app->addAction('wp_ajax_fluentform-import-forms', function () use ($app) {
    Acl::verify('fluentform_settings_manager');
    (new \FluentForm\App\Modules\Transfer\Transfer())->importForms();
});

$app->addAction('wp_ajax_fluentform-get-all-forms', function () use ($app) {
    dd('wp_ajax_fluentform-get-all-forms'); //Need to check this, could not find any use
    Acl::verify(['fluentform_settings_manager', 'fluentform_forms_manager']);
    (new \FluentForm\App\Modules\Form\Form($app))->getAllForms();
});

// Fetch simplified information for all predefined forms
$app->addAction('wp_ajax_fluentform-predefined-forms', function () use ($app) {
    dd('wp_ajax_fluentform-predefined-forms');
    Acl::hasAnyFormPermission();
    (new \FluentForm\App\Modules\Form\Predefined($app))->all();
});

// Create a form by predefined data
$app->addAction('wp_ajax_fluentform-predefined-create', function () use ($app) {
    dd('wp_ajax_fluentform-predefined-create');
    Acl::verify('fluentform_forms_manager');
    (new \FluentForm\App\Modules\Form\Predefined($app))->create();
});

/**
 * Add fluentform_submission_inserted actions for
 * slack and mailchimp if the form was submitted.
 */

// Permission settings
$app->addAction('wp_ajax_fluentform_get_access_roles', function () {
    dd('wp_ajax_fluentform_get_access_roles');
    Acl::verify('fluentform_full_access');
    $roleManager = new \FluentForm\App\Modules\Acl\RoleManager();
    $roleManager->getRoles();
});

$app->addAction('wp_ajax_fluentform_set_access_roles', function () {
    dd('wp_ajax_fluentform_set_access_roles');
    Acl::verify('fluentform_full_access');
    $roleManager = new \FluentForm\App\Modules\Acl\RoleManager();
    $roleManager->setRoles();
});

$app->addAction('wp_ajax_fluentform_get_managers', function () {
    dd('wp_ajax_fluentform_get_managers');
    Acl::verify('fluentform_full_access');
    (new \FluentForm\App\Modules\Acl\Managers())->get();
});

$app->addAction('wp_ajax_fluentform_set_managers', function () {
    dd('wp_ajax_fluentform_set_managers');
    Acl::verify('fluentform_full_access');
    (new \FluentForm\App\Modules\Acl\Managers())->store();
});

$app->addAction('wp_ajax_fluentform_del_managers', function () {
    dd('wp_ajax_fluentform_del_managers');
    Acl::verify('fluentform_full_access');
    (new \FluentForm\App\Modules\Acl\Managers())->remove();
});

/*
 * Background Process Receiver
 */

$app->addAction('wp_ajax_fluentform_background_process', function () {
    $this->app['fluentFormAsyncRequest']->handleBackgroundCall();
});

$app->addAction('wp_ajax_nopriv_fluentform_background_process', function () {
    $this->app['fluentFormAsyncRequest']->handleBackgroundCall();
});

/*
 * For REST API Nonce Renewal
 */
$app->addAction('wp_ajax_fluentform_renew_rest_nonce', function () {
    if (!Acl::getCurrentUserPermissions()) {
        wp_send_json([
            'error' => 'You do not have permission to do this',
        ], 403);
    }
    
    wp_send_json([
        'nonce' => wp_create_nonce('wp_rest'),
    ], 200);
});
/*
 * For selectGroup Component Grouped Options
 * Use this filter to pass data to component
 */

add_action('wp_ajax_fluentform_select_group_ajax_data', function () {
    $requestData = wpFluentForm('request')->all();
    $ajaxList = apply_filters('fluentform/select_group_component_ajax_options', [], $requestData);
    wp_send_json_success($ajaxList);
});
