<?php

namespace FluentForm\Database\Migrations;

class FormAnalytics
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

        $table = $wpdb->prefix . 'fluentform_form_analytics';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `form_id` INT UNSIGNED NULL,
			  `user_id` INT UNSIGNED NULL,
			  `source_url` VARCHAR(255) NOT NULL,
			  `platform` CHAR(30) NULL,
			  `browser` CHAR(30) NULL,
			  `city` VARCHAR (100) NULL,
			  `country` VARCHAR (100) NULL,
			  `ip` CHAR(15) NULL,
			  `count` INT DEFAULT 1,
			  `created_at` TIMESTAMP NULL,
			  PRIMARY KEY (`id`) ) $charsetCollate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            dbDelta($sql);
        }
    }
}
