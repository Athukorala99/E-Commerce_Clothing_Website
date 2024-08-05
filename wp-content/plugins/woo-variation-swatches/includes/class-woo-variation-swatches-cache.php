<?php

defined( 'ABSPATH' ) || exit;


/**
 * How to use:
 *
 * $cache  = new Woo_Variation_Swatches_Cache( 'name', 'group' );
 * $cache->delete_transient();
 * $cache->delete_all_transient();
 * $cache->delete_all_transient_from_db();
 *
 * if ( false === ( $results = $cache->get_transient() ) ) {
 * // It wasn't there, so regenerate the data and save the transient
 *   $results = 'GENERATED DATA';
 *   $cache->set_transient( $results );
 * }
 *
 *
 * echo $results;
 *
 * print_r( Woo_Variation_Swatches_Cache::get_transients() );
 */


if ( ! class_exists( 'Woo_Variation_Swatches_Cache' ) ) {
	class Woo_Variation_Swatches_Cache {

		private $name;
		private $group_name;
		static $transients = array();

		public function __construct( $name, $group_name ) {
			$this->name       = $name;
			$this->group_name = $group_name;

			if ( empty( self::$transients[ $this->group_name ] ) ) {
				self::$transients[ $this->group_name ] = array( $this->name );
			} else {
				self::$transients[ $this->group_name ][] = $this->name;
			}

			return $this;
		}

		public static function get_transients() {
			return self::$transients;
		}

		public static function delete_transient_group( $group ) {

			$transient_name  = $group . '-transient-version';
			$transient_value = (string) time();

			set_transient( $transient_name, $transient_value );

			return $transient_value;
		}

		public static function delete_cache_group( $group ) {
			$prefix_key    = 'woo_variation_swatches_';
			$prefix_string = $prefix_key . $group . '_cache_prefix';
			wp_cache_set( $prefix_string, microtime(), $group );
		}


		public function get_transient_version( $group ) {
			$transient_name  = $group . '-transient-version';
			$transient_value = get_transient( $transient_name );

			if ( false === $transient_value ) {
				$transient_value = (string) time();

				set_transient( $transient_name, $transient_value );
			}

			return $transient_value;
		}

		// Transient Cache
		public function get_transient_group() {
			return $this->get_transient_version( $this->group_name );
		}

		public function get_cache_prefix( $group ) {

			$prefix_key = 'woo_variation_swatches_';

			// Get cache key - uses cache key {woo_variation_swatches_products_cache_prefix} to invalidate when needed.
			$prefix_string = $prefix_key . $group . '_cache_prefix';
			$prefix        = wp_cache_get( $prefix_string, $group );

			if ( false === $prefix ) {
				$prefix = microtime();
				wp_cache_set( $prefix_string, $prefix, $group );
			}

			return $prefix_key . '_cache_' . $prefix . '_';
		}


		public function get_transient_name() {
			return $this->name;
		}

		// the name should be 172 characters or less in length
		public function set_transient( $value, $expiration = 0 ) {

			$transient_version = $this->get_transient_group();
			$transient_value   = array(
				'version' => $transient_version,
				'value'   => $value,
			);

			return set_transient( $this->get_transient_name(), $transient_value, $expiration );
		}

		public function get_transient( $transient = false ) {

			$transient_name = $transient ? $transient : $this->get_transient_name();

			$transient_version = $this->get_transient_group();
			$transient_value   = get_transient( $transient_name );

			if ( isset( $transient_value['value'], $transient_value['version'] ) && $transient_value['version'] === $transient_version ) {
				return $transient_value['value'];
			}

			return false;
		}

		public function delete_transient( $transient = false ) {

			$transient_name = $transient ? $transient : $this->get_transient_name();

			return delete_transient( $transient_name );
		}

		public function delete_all_transient( $transient_group = false ) {

			$group_name = $transient_group ? $transient_group : $this->group_name;

			self::delete_transient_group( $group_name );
		}

		public function delete_all_transient_from_db( $transient_name = false, $transient_group = false ) {

			if ( ! wp_using_ext_object_cache() ) {
				global $wpdb;

				$name  = sprintf( '_transient_%s', ( $transient_name ? $transient_name : $this->name ) );
				$group = sprintf( '_transient_%s-transient-version', ( $transient_group ? $transient_group : $this->group_name ) );

				// Delete Version
				$sql_1 = $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT %d;", $group, 100 );
				$wpdb->query( $sql_1 ); // WPCS: cache ok, db call ok.

				// Delete Name
				$sql_2 = $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT %d;", $name, 10 );
				$wpdb->query( $sql_2 ); // WPCS: cache ok, db call ok.

			}
		}

		// Object cache
		public function get_cache_key( $name = false ) {
			return $this->get_cache_prefix( $this->group_name ) . ( $name ? $name : $this->name );
		}

		public function set_cache( $value, $name = false ) {
			$cache_key = $this->get_cache_key( $name );
			wp_cache_set( $cache_key, $value, $this->group_name );
		}

		public function get_cache( $cache_key = false ) {

			$cache_key = $cache_key ? $cache_key : $this->get_cache_key( $cache_key );

			return wp_cache_get( $cache_key, $this->group_name );
		}

		public function delete_cache( $cache_key = false ) {

			$cache_key = $cache_key ? $cache_key : $this->get_cache_key( $cache_key );

			return wp_cache_delete( $cache_key, $this->group_name );
		}

		public function delete_all_cache( $group_name = false ) {

			$group         = $group_name ? $group_name : $this->group_name;
			$prefix_key    = 'woo_variation_swatches_';
			$prefix_string = $prefix_key . $group . '_cache_prefix';

			wp_cache_set( $prefix_string, microtime(), $group );
		}

		// Support WP 6.1
		public function delete_group_cache( $group_name = false ) {

			$name = $group_name ? $group_name : $this->group_name;

			if ( ! wp_cache_supports( 'flush_group' ) ) {
				_doing_it_wrong( __FUNCTION__, __( 'Your object cache implementation does not support flushing individual groups.' ), '6.1.0' );

				return false;
			}

			wp_cache_flush_group( $name );
		}
	}
}
	