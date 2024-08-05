<?php

namespace FluentForm\App\Hooks\Handlers;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Database\DBMigrator;
use FluentForm\Database\Migrations\Logs;
use FluentForm\Database\Migrations\SubmissionDetails;

class ActivationHandler
{
    public function handle($network_wide = false)
    {
        global $wpdb;
        if ($network_wide) {
            // Retrieve all site IDs from this network (WordPress >= 4.6 provides easy to use functions for that).
            if (function_exists('get_sites') && function_exists('get_current_network_id')) {
                $site_ids = get_sites(['fields' => 'ids', 'network_id' => get_current_network_id()]);
            } else {
                $site_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs WHERE site_id = $wpdb->siteid;");
            }
            // Install the plugin for all these sites.
            foreach ($site_ids as $site_id) {
                switch_to_blog($site_id);
                $this->migrate();
                restore_current_blog();
            }
        } else {
            $this->migrate();
            $this->maybeMigrateDefaultForms();
        }

        $this->setCronSchedule();
    }

    public function migrate()
    {
        DBMigrator::run();
        Acl::setPermissions();
        $this->setDefaultGlobalSettings();
        $this->setCurrentVersion();
        $this->maybeMigrateDB();
    }

    private function setDefaultGlobalSettings()
    {
        if (!get_option('_fluentform_global_form_settings')) {
            update_option('__fluentform_global_form_settings', [
                'layout' => [
                    'labelPlacement'        => 'top',
                    'asteriskPlacement'     => 'asterisk-right',
                    'helpMessagePlacement'  => 'with_label',
                    'errorMessagePlacement' => 'inline',
                    'cssClassName'          => '',
                ],
            ], 'no');
        }
    }

    private function setCurrentVersion()
    {
        update_option('_fluentform_installed_version', FLUENTFORM_VERSION, 'no');
    }

    public function maybeMigrateDB()
    {
        // First we have to check if global modules is set or not
        $this->migrateGlobalAddOns();

        if (!get_option('fluentform_entry_details_migrated')) {
            SubmissionDetails::migrate();
        }

        if (!get_option('fluentform_db_fluentform_logs_added')) {
            Logs::migrate();
        }
    }

    public function migrateGlobalAddOns()
    {
        $globalModules = get_option('fluentform_global_modules_status');
        // Modules already set
        if (is_array($globalModules)) {
            return;
        }

        $possibleGloablModules = [
            'mailchimp'         => '_fluentform_mailchimp_details',
            'activecampaign'    => '_fluentform_activecampaign_settings',
            'campaign_monitor'  => '_fluentform_campaignmonitor_settings',
            'constatantcontact' => '_fluentform_constantcontact_settings',
            'getresponse'       => '_fluentform_getresponse_settings',
            'icontact'          => '_fluentform_icontact_settings',
        ];

        $possibleMetaModules = [
            'webhook' => 'fluentform_webhook_feed',
            'zapier'  => 'fluentform_zapier_feed',
            'slack'   => 'slack',
        ];

        $moduleStatuses = [];
        foreach ($possibleGloablModules as $moduleName => $settingsKey) {
            if (get_option($settingsKey)) {
                $moduleStatuses[$moduleName] = 'yes';
            } else {
                $moduleStatuses[$moduleName] = 'no';
            }
        }

        global $wpdb;

        foreach ($possibleMetaModules as $moduleName => $metaName) {
            $row = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}fluentform_form_meta` WHERE `meta_key` = '{$metaName}' LIMIT 1");

            if ($row) {
                $moduleStatuses[$moduleName] = 'yes';
            } else {
                $moduleStatuses[$moduleName] = 'no';
            }
        }

        update_option('fluentform_global_modules_status', $moduleStatuses, 'no');
    }

    public function maybeMigrateDefaultForms()
    {
        global $wpdb;
        $formsTable = $wpdb->prefix . 'fluentform_forms';
        $firstForm = $wpdb->get_row('SELECT * FROM ' . $formsTable . ' LIMIT 1');

        if (!$firstForm) {
            $forms = [
                '[{"id":"9","title":"Contact Form Demo","status":"published","appearance_settings":null,"form_fields":{"fields":[{"index":0,"element":"input_name","attributes":{"name":"names","data-type":"name-element"},"settings":{"container_class":"","admin_field_label":"Name","conditional_logics":[]},"fields":{"first_name":{"element":"input_text","attributes":{"type":"text","name":"first_name","value":"","id":"","class":"","placeholder":"First Name"},"settings":{"container_class":"","label":"First Name","help_message":"","visible":true,"validation_rules":{"required":{"value":false,"message":"This field is required"}},"conditional_logics":[]},"editor_options":{"template":"inputText"}},"middle_name":{"element":"input_text","attributes":{"type":"text","name":"middle_name","value":"","id":"","class":"","placeholder":"","required":false},"settings":{"container_class":"","label":"Middle Name","help_message":"","error_message":"","visible":false,"validation_rules":{"required":{"value":false,"message":"This field is required"}},"conditional_logics":[]},"editor_options":{"template":"inputText"}},"last_name":{"element":"input_text","attributes":{"type":"text","name":"last_name","value":"","id":"","class":"","placeholder":"Last Name","required":false},"settings":{"container_class":"","label":"Last Name","help_message":"","error_message":"","visible":true,"validation_rules":{"required":{"value":false,"message":"This field is required"}},"conditional_logics":[]},"editor_options":{"template":"inputText"}}},"editor_options":{"title":"Name Fields","element":"name-fields","icon_class":"ff-edit-name","template":"nameFields"},"uniqElKey":"el_1570866006692"},{"index":1,"element":"input_email","attributes":{"type":"email","name":"email","value":"","id":"","class":"","placeholder":"Email Address"},"settings":{"container_class":"","label":"Email","label_placement":"","help_message":"","admin_field_label":"","validation_rules":{"required":{"value":true,"message":"This field is required"},"email":{"value":true,"message":"This field must contain a valid email"}},"conditional_logics":[]},"editor_options":{"title":"Email Address","icon_class":"ff-edit-email","template":"inputText"},"uniqElKey":"el_1570866012914"},{"index":2,"element":"input_text","attributes":{"type":"text","name":"subject","value":"","class":"","placeholder":"Subject"},"settings":{"container_class":"","label":"Subject","label_placement":"","admin_field_label":"Subject","help_message":"","validation_rules":{"required":{"value":false,"message":"This field is required"}},"conditional_logics":{"type":"any","status":false,"conditions":[{"field":"","value":"","operator":""}]}},"editor_options":{"title":"Simple Text","icon_class":"ff-edit-text","template":"inputText"},"uniqElKey":"el_1570878958648"},{"index":3,"element":"textarea","attributes":{"name":"message","value":"","id":"","class":"","placeholder":"Your Message","rows":4,"cols":2},"settings":{"container_class":"","label":"Your Message","admin_field_label":"","label_placement":"","help_message":"","validation_rules":{"required":{"value":true,"message":"This field is required"}},"conditional_logics":{"type":"any","status":false,"conditions":[{"field":"","value":"","operator":""}]}},"editor_options":{"title":"Text Area","icon_class":"ff-edit-textarea","template":"inputTextarea"},"uniqElKey":"el_1570879001207"}],"submitButton":{"uniqElKey":"el_1524065200616","element":"button","attributes":{"type":"submit","class":""},"settings":{"align":"left","button_style":"default","container_class":"","help_message":"","background_color":"#1a7efb","button_size":"md","color":"#ffffff","button_ui":{"type":"default","text":"Submit Form","img_url":""}},"editor_options":{"title":"Submit Button"}}},"has_payment":"0","type":"","conditions":null,"created_by":"1","created_at":"2021-06-08 04:56:28","updated_at":"2021-06-08 04:56:28","metas":[{"meta_key": "template_name","value": "basic_contact_form"},{"meta_key":"formSettings","value":"{\"confirmation\":{\"redirectTo\":\"samePage\",\"messageToShow\":\"Thank you for your message. We will get in touch with you shortly\",\"customPage\":null,\"samePageFormBehavior\":\"hide_form\",\"customUrl\":null},\"restrictions\":{\"limitNumberOfEntries\":{\"enabled\":false,\"numberOfEntries\":null,\"period\":\"total\",\"limitReachedMsg\":\"Maximum number of entries exceeded.\"},\"scheduleForm\":{\"enabled\":false,\"start\":null,\"end\":null,\"selectedDays\":[\"Monday\",\"Tuesday\",\"Wednesday\",\"Thursday\",\"Friday\",\"Saturday\",\"Sunday\"],\"pendingMsg\":\"Form submission is not started yet.\",\"expiredMsg\":\"Form submission is now closed.\"},\"requireLogin\":{\"enabled\":false,\"requireLoginMsg\":\"You must be logged in to submit the form.\"},\"denyEmptySubmission\":{\"enabled\":false,\"message\":\"Sorry, you cannot submit an empty form. Let\'s hear what you wanna say.\"}},\"layout\":{\"labelPlacement\":\"top\",\"helpMessagePlacement\":\"with_label\",\"errorMessagePlacement\":\"inline\",\"cssClassName\":\"\",\"asteriskPlacement\":\"asterisk-right\"},\"delete_entry_on_submission\":\"no\",\"appendSurveyResult\":{\"enabled\":false,\"showLabel\":false,\"showCount\":false}}"},{"meta_key":"advancedValidationSettings","value":"{\"status\":false,\"type\":\"all\",\"conditions\":[{\"field\":\"\",\"operator\":\"=\",\"value\":\"\"}],\"error_message\":\"\",\"validation_type\":\"fail_on_condition_met\"}"},{"meta_key":"double_optin_settings","value":"{\"status\":\"no\",\"confirmation_message\":\"Please check your email inbox to confirm this submission\",\"email_body_type\":\"global\",\"email_subject\":\"Please confirm your form submission\",\"email_body\":\"<h2>Please Confirm Your Submission<\/h2><p>&nbsp;<\/p><p style=\"text-align: center;\"><a style=\"color: #ffffff; background-color: #454545; font-size: 16px; border-radius: 5px; text-decoration: none; font-weight: normal; font-style: normal; padding: 0.8rem 1rem; border-color: #0072ff;\" href=\"#confirmation_url#\">Confirm Submission<\/a><\/p><p>&nbsp;<\/p><p>If you received this email by mistake, simply delete it. Your form submission won\'t proceed if you don\'t click the confirmation link above.<\/p>\",\"email_field\":\"\",\"skip_if_logged_in\":\"yes\",\"skip_if_fc_subscribed\":\"no\"}"}]}]',
                '[{"id":"8","title":"Subscription Form","status":"published","appearance_settings":null,"form_fields":{"fields":[{"index":1,"element":"container","attributes":[],"settings":{"container_class":"","conditional_logics":{"type":"any","status":false,"conditions":[{"field":"","value":"","operator":""}]}},"columns":[{"fields":[{"index":1,"element":"input_email","attributes":{"type":"email","name":"email","value":"","id":"","class":"","placeholder":"Your Email Address"},"settings":{"container_class":"","label":"","label_placement":"","help_message":"","admin_field_label":"Email","validation_rules":{"required":{"value":true,"message":"This field is required"},"email":{"value":true,"message":"This field must contain a valid email"}},"conditional_logics":[],"is_unique":"no","unique_validation_message":"Email address need to be unique."},"editor_options":{"title":"Email Address","icon_class":"ff-edit-email","template":"inputText"},"uniqElKey":"el_16231279686950.8779857923682932"}]},{"fields":[{"index":15,"element":"custom_submit_button","attributes":{"class":"","type":"submit"},"settings":{"button_style":"","button_size":"md","align":"left","container_class":"","current_state":"normal_styles","background_color":"","color":"","hover_styles":{"backgroundColor":"#ffffff","borderColor":"#1a7efb","color":"#1a7efb","borderRadius":"","minWidth":"100%"},"normal_styles":{"backgroundColor":"#1a7efb","borderColor":"#1a7efb","color":"#ffffff","borderRadius":"","minWidth":"100%"},"button_ui":{"text":"Subscribe","type":"default","img_url":""},"conditional_logics":{"type":"any","status":false,"conditions":[{"field":"","value":"","operator":""}]}},"editor_options":{"title":"Custom Submit Button","icon_class":"dashicons dashicons-arrow-right-alt","template":"customButton"},"uniqElKey":"el_16231279798380.5947400167493171"}]}],"editor_options":{"title":"Two Column Container","icon_class":"ff-edit-column-2"},"uniqElKey":"el_16231279284710.40955091024524304"}],"submitButton":{"uniqElKey":"el_1524065200616","element":"button","attributes":{"type":"submit","class":""},"settings":{"align":"left","button_style":"default","container_class":"","help_message":"","background_color":"#1a7efb","button_size":"md","color":"#ffffff","button_ui":{"type":"default","text":"Subscribe","img_url":""}},"editor_options":{"title":"Submit Button"}}},"has_payment":"0","type":"form","conditions":null,"created_by":"1","created_at":"2021-06-08 04:51:36","updated_at":"2021-06-08 04:54:02","metas":[{"meta_key": "template_name","value": "inline_subscription"},{"meta_key":"formSettings","value":"{\"confirmation\":{\"redirectTo\":\"samePage\",\"messageToShow\":\"Thank you for your message. We will get in touch with you shortly\",\"customPage\":null,\"samePageFormBehavior\":\"hide_form\",\"customUrl\":null},\"restrictions\":{\"limitNumberOfEntries\":{\"enabled\":false,\"numberOfEntries\":null,\"period\":\"total\",\"limitReachedMsg\":\"Maximum number of entries exceeded.\"},\"scheduleForm\":{\"enabled\":false,\"start\":null,\"end\":null,\"pendingMsg\":\"Form submission is not started yet.\",\"expiredMsg\":\"Form submission is now closed.\"},\"requireLogin\":{\"enabled\":false,\"requireLoginMsg\":\"You must be logged in to submit the form.\"},\"denyEmptySubmission\":{\"enabled\":false,\"message\":\"Sorry, you cannot submit an empty form. Let\'s hear what you wanna say.\"}},\"layout\":{\"labelPlacement\":\"top\",\"helpMessagePlacement\":\"with_label\",\"errorMessagePlacement\":\"inline\",\"asteriskPlacement\":\"asterisk-right\"}}"},{"meta_key":"notifications","value":"{\"name\":\"Admin Notification Email\",\"sendTo\":{\"type\":\"email\",\"email\":\"{wp.admin_email}\",\"field\":\"email\",\"routing\":[{\"email\":null,\"field\":null,\"operator\":\"=\",\"value\":null}]},\"fromName\":\"\",\"fromEmail\":\"\",\"replyTo\":\"\",\"bcc\":\"\",\"subject\":\"[{inputs.names}] New Form Submission\",\"message\":\"<p>{all_data}<\\\/p>\\n<p>This form submitted at: {embed_post.permalink}<\\\/p>\",\"conditionals\":{\"status\":false,\"type\":\"all\",\"conditions\":[{\"field\":null,\"operator\":\"=\",\"value\":null}]},\"enabled\":false,\"email_template\":\"\"}"},{"meta_key":"step_data_persistency_status","value":"no"},{"meta_key":"_primary_email_field","value":"email"}]}]',
            ];

            foreach ($forms as $index => $formJson) {
                $structure = json_decode($formJson, true)[0];

                $insertData = [
                    'title'       => $structure['title'],
                    'type'        => $structure['type'],
                    'status'      => 'published',
                    'created_by'  => get_current_user_id(),
                    'created_at'  => current_time('mysql'),
                    'updated_at'  => current_time('mysql'),
                    'form_fields' => json_encode($structure['form_fields']),
                ];

                $wpdb->insert($formsTable, $insertData, [
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                ]);

                $formId = $wpdb->insert_id;

                foreach ($structure['metas'] as $meta) {
                    $meta['value'] = trim(preg_replace('/\s+/', ' ', $meta['value']));

                    $wpdb->insert($wpdb->prefix . 'fluentform_form_meta', [
                        'form_id'  => $formId,
                        'meta_key' => $meta['meta_key'],
                        'value'    => $meta['value'],
                    ], [
                        '%d',
                        '%s',
                        '%s',
                    ]);
                }
            }
        }
    }

    public function setCronSchedule()
    {
        add_filter('cron_schedules', function ($schedules) {
            $schedules['ff_every_five_minutes'] = [
                'interval' => 300,
                'display'  => esc_html__('Every 5 Minutes (FluentForm)', 'fluentform'),
            ];

            return $schedules;
        }, 10, 1);


        $hookName = 'fluentform_do_scheduled_tasks';
        if (!wp_next_scheduled($hookName)) {
            wp_schedule_event(time(), 'ff_every_five_minutes', $hookName);
        }

        $emailReportHookName = 'fluentform_do_email_report_scheduled_tasks';
        if (!wp_next_scheduled($emailReportHookName)) {
            wp_schedule_event(time(), 'daily', $emailReportHookName);
        }

    }
}
