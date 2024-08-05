<?php
namespace Averta\WordPress\Database;



/**
 * Creates and manages the structure of WP custom tables.
 */
class Migration{

	/**
	 * Current tables migration version
	 */
	const MIGRATION_VERSION = "1.0.0";

	/**
	 * Prefix for version option_name in options table
	 */
	const VERSION_PREFIX = "plugin_slug_";

	/**
	 * Table prefix
	 */
	const TABLE_PREFIX = 'plugin_slug_';

	/**
	* Table names
	* Example: ['documents', 'options']
	*/
	protected $table_names = [];


	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'wpmu_drop_tables'  , array( $this, 'wpmu_drop_tables'    ), 11, 2 );
		add_action( 'wp_initialize_site', array( $this, 'ms_site_initialized' ), 12, 2 );
	}

	/**
	 * Returns prefixfied name of table or tables
	 *
	 * @param  string   property name
	 *
	 * @since 1.0
	 * @return string|array
	 */
	public function __get( $name ){

		if( in_array( $name, $this->table_names ) ){
			global $wpdb;
			return $wpdb->prefix . static::TABLE_PREFIX . $name;

		// Get list of table names
		} elseif( 'tables' == $name ){
			global $wpdb;
			$tables = [];

			foreach ($this->table_names as $table_name ){
				$tables[ $table_name ] = $wpdb->prefix . static::TABLE_PREFIX . $table_name;
			}
			return $tables;

		} else {
			return NULL;
		}
	}

	/**
	 * Uncomment the following method and define table structures
	 *
	protected function create_table_{table_name}() {

		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$this->{table_name}}  (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name  text NOT NULL,
            slug  varchar(100) NOT NULL,
            author bigint(20) unsigned NOT NULL DEFAULT '0',
            created_at  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            modified_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            content longtext NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'draft',
            password varchar(255) NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            KEY created_at (created_at),
            KEY slug (slug)
        ) {$this->charset_collate()};\n";

	 	$this->dbDelta( $sql_create_table );
	}
	*/


	/**
	 * Create tables
	 *
	 * Should be invoked on plugin activation
	 *
	 * @since 1.0
	 * @return null
	 */
	protected function create_tables() {

	    // call create_table_{table_name} of all tables if are defined
	    foreach ( $this->table_names as $table_name ){
	    	$method_name = 'create_table_' .  $table_name;
	    	if( method_exists( $this, $method_name ) ){
	    		$this->$method_name();
			}
		}

		do_action( 'averta/database/tables/created', $this->tables  );
	}


    /**
     * Updates tables
     *
	 * @param $last_executed_version
	 *
     * @since 1.0
     * @return null
     */
    protected function update_tables( $last_executed_version ) {
		/**
		 * Example:
		 * Execute table changes for new version 1.0.1
		 *
		 * if( version_compare( '1.0.1', $last_executed_version, '>' ) ){
			$this->runSql( "ALTER TABLE {$this->table_name} DROP COLUMN type" );
		 }
		*/
        do_action( 'averta/database/tables/updated', $this->tables  );
    }


	/**
	 * Updates tables if update is required
	 *
	 * @param bool $force  force to create tables if not exists
	 *
	 * @since 1.0
	 * @return bool  is any update required for tables?
	 */
	public function migrate( $force = false ){
		$last_executed_version = $this->get_last_executed_migration_version();
		// check if the update is required
		if( ! $force && ( $last_executed_version == static::MIGRATION_VERSION ) )
			return false;

        $this->create_tables();
		$this->update_tables( $last_executed_version );

		// update tables version to current version
		$this->update_last_executed_migration_version( static::MIGRATION_VERSION );

		return true;
	}

    /**
	 * Inserts custom tables for a new site into the database.
	 *
     * @since WP 5.1.0
     *
	 * @param int|WP_Site $site_id Site ID or object.
	 * @param array       $args
	 *
	 * @return bool|null
	 */
	public function ms_site_initialized( $site_id, array $args = array() ){
		if ( empty( $site_id ) ) {
			return null;
		}

		if ( ! $site = get_site( $site_id ) ) {
			return null;
		}

		$switch = false;
		if ( get_current_blog_id() !== $site->id ) {
			$switch = true;
			switch_to_blog( $site->id );
		}

		$this->migrate( true );

		if ( $switch ) {
			restore_current_blog();
        }

		return true;
	}

	/**
	 * Drop all custom tables of this class
	 *
	 * @since 1.0
	 * @return void
	 */
	protected function delete_tables(){
		global $wpdb;

		foreach ( $this->tables as $table_name) {
			$wpdb->query("DROP TABLE IF EXISTS $table_name");
		}
	}


	/**
	 * Filter custom tables to drop when the blog is deleted
	 *
	 * @since 1.0
	 * @return array $tables
	 */
	public function wpmu_drop_tables( $tables, $blog_id ){
		global $wpdb;

		foreach ( $this->table_names as $table_name ){
	    	$tables[] = $wpdb->prefix . $blog_id . '_' . static::TABLE_PREFIX . $name;
		}

		return $tables;
	}

	/**
	 * Creates new tables and updates existing tables to a new structure.
	 *
	 * @param string $sql
	 * @since 1.0
	 */
	protected function dbDelta( $sql = '' ){
		if( ! function_exists( 'dbDelta' ) ){
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}
		dbDelta( $sql );
	}

	/**
	 * Executes SQL
	 *
	 * @param string $sql
	 * @since 1.0
	 */
	protected function runSql( $sql = '' ){
		global $wpdb;
	 	$wpdb->query( $sql );
	}

	/**
	 * Retrieves migration version
	 *
	 * @since 1.0
	 * @return string
	 */
	protected function get_migration_version_id(){
		return static::VERSION_PREFIX . 'db_version';
	}

	/**
	 * Updates current migration version
	 *
	 * @param $version   The version to be saved
	 *
	 * @since 1.0
	 * @return bool
	 */
	protected function update_last_executed_migration_version( $version ){
		return update_option( $this->get_migration_version_id(), $version );
	}

	/**
	 * Retrieves Last executed migration version
	 *
	 * @since 1.0
	 * @return mixed|void
	 */
	protected function get_last_executed_migration_version(){
		return get_option( $this->get_migration_version_id(), '1.0.0' );
	}

	/**
	 * Gets database charset collate
	 *
	 * @return string
	 */
	protected function charset_collate(){
		global $wpdb;
		return $wpdb->get_charset_collate();
	}

}
