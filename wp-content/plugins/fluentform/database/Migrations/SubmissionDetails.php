<?php

namespace FluentForm\Database\Migrations;

class SubmissionDetails
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
        $table = $wpdb->prefix . 'fluentform_entry_details';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
			  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			  `form_id` BIGINT(20) UNSIGNED NULL,
			  `submission_id` BIGINT(20) UNSIGNED NULL,
			  `field_name` VARCHAR(255) NULL,
			  `sub_field_name` VARCHAR(255) NULL,
			  `field_value` LONGTEXT NULL,
			  PRIMARY KEY (`id`)) $charsetCollate;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        update_option('fluentform_entry_details_migrated', 'yes', 'no');
    }
}
