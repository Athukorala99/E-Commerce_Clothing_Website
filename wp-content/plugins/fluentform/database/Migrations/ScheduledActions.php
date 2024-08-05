<?php

namespace FluentForm\Database\Migrations;

class ScheduledActions
{
    /**
     * Migrate the table.
     *
     * @return void
     */
    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . 'ff_scheduled_actions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
			  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			  `action` VARCHAR(255) NULL,
			  `form_id` BIGINT(20) UNSIGNED NULL, 
			  `origin_id` BIGINT(20) UNSIGNED NULL, 
			  `feed_id` BIGINT(20) UNSIGNED NULL,
			  `type` VARCHAR(255) DEFAULT 'submission_action',
			  `status` VARCHAR(255) NULL,
			  `data` LONGTEXT NULL,
			  `note` TINYTEXT NULL,
			  `retry_count` INT UNSIGNED DEFAULT 0,
			  `created_at` TIMESTAMP NULL,
			  `updated_at` TIMESTAMP NULL,
			  PRIMARY KEY (`id`)) $charsetCollate;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        update_option('fluentform_scheduled_actions_migrated', 'yes', 'no');
    }
}
