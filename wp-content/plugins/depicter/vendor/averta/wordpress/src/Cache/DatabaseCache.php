<?php
namespace Averta\WordPress\Cache;


class DatabaseCache extends WPCache {

	/**
	 * {@inheritDoc}
	 */
	public function get( $key, $default = false ) {
		global $_wp_using_ext_object_cache;

		$current_using_cache = $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;

		$result = parent::get( $key, $default );

		$_wp_using_ext_object_cache = $current_using_cache;

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set( $key, $value, $ttl = null ): bool {
		global $_wp_using_ext_object_cache;

		$current_using_cache = $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;

		$result = parent::set( $key, $value, $ttl );

		$_wp_using_ext_object_cache = $current_using_cache;

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete( $key ): bool {
		global $_wp_using_ext_object_cache;

		$current_using_cache = $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;

		$result = parent::delete( $key );

		$_wp_using_ext_object_cache = $current_using_cache;

		return $result;
	}

}
