<?php

namespace FluentForm\Database\Migrations;

class Logs
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

        $table = $wpdb->prefix . 'fluentform_logs';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `parent_source_id` INT UNSIGNED NULL,
			  `source_type` VARCHAR(255) NULL,
			  `source_id` INT UNSIGNED NULL,
			  `component` VARCHAR(255) NULL,
			  `status` CHAR(30) NULL,
			  `title` VARCHAR(255) NOT NULL,
			  `description` LONGTEXT NULL,
			  `created_at` TIMESTAMP NULL,
			  PRIMARY KEY (`id`) ) $charsetCollate;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        update_option('fluentform_db_fluentform_logs_added', true, 'no');
    }
}
