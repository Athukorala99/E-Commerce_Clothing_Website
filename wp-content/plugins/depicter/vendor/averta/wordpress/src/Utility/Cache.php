<?php
namespace Averta\WordPress\Utility;


class Cache{

	public static function prevent(){
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( 'DONOTCACHEDB', true );
		}

		if ( ! defined( 'DONOTMINIFY' ) ) {
			define( 'DONOTMINIFY', true );
		}

		if ( ! defined( 'DONOTCDN' ) ) {
			define( 'DONOTCDN', true );
		}

		if ( ! defined( 'DONOTCACHCEOBJECT' ) ) {
			define( 'DONOTCACHCEOBJECT', true );
		}

		// prevent caching.
		nocache_headers();
	}

	/**
	 * Get the value of a transient.
	 *
	 * If the transient does not exist, does not have a value, or has expired,
	 * then the return value will be false.
	 *
	 * @param string $key  Cache key. Expected to not be SQL-escaped.
	 *
	 * @return mixed Value of transient.
	 */
	public static function getDatabaseCache( $key ) {
		global $_wp_using_ext_object_cache;

		$current_using_cache = $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;

		$result = get_transient( $key );

		$_wp_using_ext_object_cache = $current_using_cache;

		return $result;
	}

	/**
	 * Set/update the value of a transient.
	 *
	 * You do not need to serialize values. If the value needs to be serialized, then
	 * it will be serialized before it is set.
	 *
	 *
	 * @param string $key  		 Cache key. Expected to not be SQL-escaped. Must be
	 *                           172 characters or fewer in length.
	 * @param mixed  $value      Transient value. Must be serializable if non-scalar.
	 *                           Expected to not be SQL-escaped.
	 * @param int    $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
	 *
	 * @return bool False if value was not set and true if value was set.
	 */
	public static function setDatabaseCache( $key, $value, $expiration = 0 ) {
		global $_wp_using_ext_object_cache;

		$current_using_cache = $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;

		$result = set_transient( $key, $value, $expiration );

		$_wp_using_ext_object_cache = $current_using_cache;

		return $result;
	}

	/**
	 * Delete a transient.
	 *
	 * @param string $key  Cache key. Expected to not be SQL-escaped.
	 *
	 * @return bool true if successful, false otherwise
	 */
	public static function deleteDatabaseCache( $key ) {
		global $_wp_using_ext_object_cache;

		$current_using_cache = $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;

		$result = delete_transient( $key );

		$_wp_using_ext_object_cache = $current_using_cache;

		return $result;
	}

}

