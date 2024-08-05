<?php

/**
 * @var $router \FluentForm\Framework\Http\Router
 */

/*
* Forms resource
*/
$router->prefix('forms')->withPolicy('FormPolicy')->group(function ($router) {
    $router->get('/', 'FormController@index');
    $router->post('/', 'FormController@store');
    $router->get('templates', 'FormController@templates');
    $router->get('ping', 'FormController@ping');

    $router->prefix('{form_id}')->group(function ($router) {
        $router->get('/', 'FormController@find');
        $router->post('/', 'FormController@update');
        $router->delete('/', 'FormController@delete');
        $router->post('duplicate', 'FormController@duplicate');
        $router->post('convert', 'FormController@convert');
        $router->get('resources', 'FormController@resources');
        $router->get('pages', 'FormController@pages');
        $router->get('fields', 'FormController@fields');
        $router->get('shortcodes', 'FormController@shortcodes');
        $router->get('findShortCodePage', 'FormController@findShortCodePage');
    });
});

/*
* Form Settings
*/
$router->prefix('settings')->withPolicy('FormPolicy')->group(function ($router) {
    $router->prefix('{form_id}')->group(function ($router) {
        $router->get('/', 'FormSettingsController@index');
        $router->post('/', 'FormSettingsController@store');
        $router->delete('/', 'FormSettingsController@remove');

        $router->get('general', 'FormSettingsController@general');
        $router->post('general', 'FormSettingsController@saveGeneral');

        $router->get('customizer', 'FormSettingsController@customizer');
        $router->post('customizer', 'FormSettingsController@storeCustomizer');

        $router->post('entry-columns', 'FormSettingsController@storeEntryColumns');

        $router->get('conversational-design', 'FormSettingsController@conversationalDesign');
        $router->post('store-conversational-design', 'FormSettingsController@storeConversationalDesign');
    });
});
/*
* Form Submissions
*/
$router->prefix('submissions')->withPolicy('SubmissionPolicy')->group(function ($router) {
    $router->get('/', 'SubmissionController@index');
    $router->get('resources', 'SubmissionController@resources');
    $router->post('bulk-actions', 'SubmissionController@handleBulkActions');
    $router->get('all', 'SubmissionController@all');
    $router->delete('/{entry_id}', 'SubmissionController@remove');

    $router->prefix('{entry_id}')->group(function ($router) {
        $router->get('/', 'SubmissionController@find');
    
        $router->post('status', 'SubmissionController@updateStatus');
        $router->post('is-favorite', 'SubmissionController@toggleIsFavorite');

        $router->get('logs', 'SubmissionLogController@get');
        $router->delete('logs', 'SubmissionLogController@remove');

        $router->get('notes', 'SubmissionNoteController@get');
        $router->post('notes', 'SubmissionNoteController@store');
        
        $router->get('submission-users','SubmissionController@submissionUsers');
        $router->post('update-submission-user','SubmissionController@updateSubmissionUser');
    });
});

/*
* Logs
*/
$router->prefix('logs')->withPolicy('SubmissionPolicy')->group(function ($router) {
    $router->get('/', 'LogController@get');
    $router->delete('/', 'LogController@remove');
    $router->get('/filters', 'LogController@getFilters');
});
/*
* Global Integrations
*/
$router->prefix('integrations')->withPolicy('FormPolicy')->group(function ($router) {
    $router->get('/', 'GlobalIntegrationController@index');
    $router->post('/', 'GlobalIntegrationController@update');
    $router->post('update-status', 'GlobalIntegrationController@updateModuleStatus');
    
    /*
    * Form Integrations
    */
    $router->prefix('{form_id}')->group(function ($router) {
        $router->get('/form-integrations', 'FormIntegrationController@index');
        $router->get('/', 'FormIntegrationController@find');
        $router->post('/', 'FormIntegrationController@update');
        $router->delete('/', 'FormIntegrationController@delete');
        
        $router->get('/integration-list-id', 'FormIntegrationController@integrationListComponent');
    });
});
/*
* Global Settings
*/
$router->prefix('global-settings')->withPolicy('GlobalSettingsPolicy')->group(function ($router) {
    $router->get('/', 'GlobalSettingsController@index');
    $router->post('/', 'GlobalSettingsController@store');
});
/*
* Permission Roles
*/
$router->prefix('roles')->withPolicy('RoleManagerPolicy')->group(function ($router) {
    $router->get('/', 'RolesController@index');
    $router->post('/', 'RolesController@addCapability');
});
/*
* Permission Managers
*/
$router->prefix('managers')->withPolicy('RoleManagerPolicy')->group(function ($router) {
    $router->get('/', 'ManagersController@index');
    $router->post('/', 'ManagersController@addManager');
    $router->delete('/', 'ManagersController@removeManager');
});
/*
* Form Analytics
*/
$router->prefix('analytics')->withPolicy('FormPolicy')->group(function ($router) {
    $router->post('/{form_id}/reset', 'AnalyticsController@reset');
});

/*
* Form Submission Handler
*/
$router->post('form-submit', 'SubmissionHandlerController@submit')->withPolicy('SubmissionPolicy');
/*
* Form Report
*/
$router->prefix('report')->withPolicy('ReportPolicy')->group(function ($router) {
    $router->get('/submissions', 'ReportController@submissions');
    $router->get('/forms/{form_id}', 'ReportController@form');
});
/*
* Review Query
*/
$router->post('notice', 'AdminNoticeController@noticeActions')->withPolicy('FormPolicy');

/*
* Global Query
*/
$router->get('global-search', 'GlobalSearchController@index')->withPolicy('FormPolicy');

