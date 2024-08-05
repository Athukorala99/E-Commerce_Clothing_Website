<?php

namespace FluentForm\Database\Migrations;

class FormMeta
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

        $table = $wpdb->prefix . 'fluentform_form_meta';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `form_id` INT UNSIGNED NULL,
			  `meta_key` VARCHAR(255) NOT NULL,
			  `value` LONGTEXT NULL,
			  PRIMARY KEY (`id`)) $charsetCollate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            dbDelta($sql);
        }
    }
}
