<?php

namespace Averta\WordPress\Models;

/**
 * An interface for WordPress Options API.
 */
class WPOptions {

	/**
	 * The prefix added to all option keys.
	 *
	 * @var string
	 */
	private $prefix = '__';

	/**
	 * Create new Options instance.
	 *
	 * @param string $prefix The prefix that will be added to option names automatically.
	 */
	public function __construct( $prefix ) {
		$this->prefix = $prefix;
	}

	/**
	 * Retrieves an option value.
	 *
	 * @param string $option  The option name (without the prefix).
	 * @param mixed  $default Optional. Default value to return if the option does not exist. Default null.
	 * @param bool   $raw     Optional. Use the raw option name (i.e. don't call get_option_name). Default false.
	 *
	 * @return mixed Value set for the option.
	 */
	public function get( $option, $default = null, $raw = false ) {
		$value = \get_option( $raw ? $option : $this->get_option_name( $option ), $default );

		if ( is_array( $default ) && '' === $value ) {
			$value = [];
		}

		return $value;
	}

	/**
	 * Sets or updates an option.
	 *
	 * @param string $option   The option name (without the prefix).
	 * @param mixed  $value    The value to store.
	 * @param bool   $autoload Optional. Whether to load the option when WordPress
	 *                         starts up. For existing options, $autoload can only
	 *                         be updated using update_option() if $value is also
	 *                         changed. Default true.
	 * @param bool   $raw      Optional. Use the raw option name (i.e. don't call get_option_name). Default false.
	 *
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function set( $option, $value, $autoload = true, $raw = false ) {
		return \update_option( $raw ? $option : $this->get_option_name( $option ), $value, $autoload );
	}

	/**
	 * Deletes an option.
	 *
	 * @param string $option The option name (without the prefix).
	 * @param bool   $raw    Optional. Use the raw option name (i.e. don't call get_option_name). Default false.
	 *
	 * @return bool True, if option is successfully deleted. False on failure.
	 */
	public function delete( $option, $raw = false ) {
		return \delete_option( $raw ? $option : $this->get_option_name( $option ) );
	}

	/**
	 * Retrieves the complete option name to use.
	 *
	 * @param  string $option The option name (without the prefix).
	 *
	 * @return string
	 */
	public function get_option_name( $option ) {
		return "{$this->prefix}{$option}";
	}
}
