<?php
namespace Depicter\Database;

use Averta\WordPress\Database\Migration as BaseMigration;

// no direct access allowed
if ( ! defined('ABSPATH') ) {
    die();
}

/**
 * Migration for custom tables
 *
 * @package Depicter\Database
 */
class Migration extends BaseMigration {

	/**
	 * Current tables migration version
	 */
	const MIGRATION_VERSION = "0.3.0";

	/**
	 * Prefix for version option name
	 */
	const VERSION_PREFIX = "depicter_";

	/**
	 * Table prefix
	 */
	const TABLE_PREFIX = 'depicter_';

	/**
	* Table names
	*/
	protected $table_names = [ 'documents', 'options', 'meta' ];


	/**
	 * Create documents table
	 *
	 * @since 1.0
	 * @return null
	 */
	protected function create_table_documents() {

		$sql_create_table = "CREATE TABLE {$this->documents} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name  text NOT NULL,
            slug  varchar(100) NOT NULL,
            type  varchar(50) NOT NULL DEFAULT 'custom',
            author bigint(20) unsigned NOT NULL DEFAULT '0',
            sections_count mediumint NOT NULL DEFAULT '0',
            created_at  datetime DEFAULT NULL,
            modified_at datetime DEFAULT NULL,
            thumbnail varchar(255) NOT NULL,
            content longtext NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'draft',
            parent bigint(20) unsigned NOT NULL DEFAULT '0',
            password varchar(255) NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            KEY created_at (created_at),
            KEY slug (slug)
        ) {$this->charset_collate()};\n";

		$this->dbDelta( $sql_create_table );
	}


	/**
	 * Create options table
	 *
	 * @since 1.0
	 * @return null
	 */
	public function create_table_options() {

		$sql_create_table = "CREATE TABLE {$this->options} (
            id mediumint unsigned NOT NULL AUTO_INCREMENT,
            option_name varchar(191) NOT NULL DEFAULT '',
            option_value text NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            UNIQUE KEY option_name (option_name)
        ) {$this->charset_collate()};\n";

	 	$this->dbDelta( $sql_create_table );
	}

	/**
	 * Create meta table
	 *
	 * @since 1.0
	 * @return null
	 */
	public function create_table_meta() {

		$sql_create_table = "CREATE TABLE {$this->meta} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            relation varchar(30) NOT NULL DEFAULT '',
            relation_id bigint(20) NOT NULL,
            meta_key varchar(30) NOT NULL DEFAULT '',
            meta_value text NOT NULL DEFAULT '',
            PRIMARY KEY  (id)
        ) {$this->charset_collate()};\n";

		$this->dbDelta( $sql_create_table );
	}

}


