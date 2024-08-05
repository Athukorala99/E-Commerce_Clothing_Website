<?php

namespace FluentForm\Database\Migrations;

class Submissions
{
    /**
     * Migrate the table.
     *
     * @return void
     */
    public static function migrate($force = false)
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . 'fluentform_submissions';

        $sql = "CREATE TABLE $table (
			  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			  `form_id` INT UNSIGNED NULL,
			  `serial_number` INT UNSIGNED NULL,
			  `response` LONGTEXT NULL,
			  `source_url` VARCHAR(255) NULL,
			  `user_id` INT UNSIGNED NULL,
			  `status` VARCHAR(45) NULL DEFAULT 'unread' COMMENT 'possible values: read, unread, trashed',
			  `is_favourite` TINYINT(1) NOT NULL DEFAULT 0,
			  `browser` VARCHAR(45) NULL,
			  `device` VARCHAR(45) NULL,
			  `ip` VARCHAR(45) NULL,
			  `city` VARCHAR(45) NULL,
			  `country` VARCHAR(45) NULL,
			  `payment_status` VARCHAR(45) NULL,
			  `payment_method` VARCHAR(45) NULL,
			  `payment_type` VARCHAR(45) NULL,
			  `currency` VARCHAR(45) NULL,
			  `payment_total` FLOAT NULL,
			  `total_paid` FLOAT NULL,
			  `created_at` TIMESTAMP NULL,
			  `updated_at` TIMESTAMP NULL,
			  PRIMARY KEY (`id`)) $charsetCollate;";

        $hasTable = $wpdb->get_var("SHOW TABLES LIKE '$table'") != $table;

        if ($force || !$hasTable) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }
}
