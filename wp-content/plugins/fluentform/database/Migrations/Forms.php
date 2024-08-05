<?php

namespace FluentForm\Database\Migrations;

class Forms
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

        $table = $wpdb->prefix . 'fluentform_forms';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `title` VARCHAR(255) NOT NULL,
			  `status` VARCHAR(45) NULL DEFAULT 'Draft',
			  `appearance_settings` TEXT NULL,
			  `form_fields` LONGTEXT NULL,
			  `has_payment` TINYINT(1) NOT NULL DEFAULT 0,
			  `type` VARCHAR(45) NULL,
			  `conditions` TEXT NULL,
			  `created_by` INT NULL,
			  `created_at` TIMESTAMP NULL,
			  `updated_at` TIMESTAMP NULL,
			  PRIMARY KEY (`id`)) $charsetCollate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            dbDelta($sql);
        }
    }
}
