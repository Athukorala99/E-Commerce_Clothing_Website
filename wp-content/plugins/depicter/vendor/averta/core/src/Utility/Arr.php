<?php
namespace Averta\Core\Utility;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class Arr
{

	/**
     * Whether keys exist or not
     *
     * @param array $keys   An array of keys to search for
     * @param array $array  The array to search in
     *
     * @return bool
     */
    public static function keysExist( array $keys, array $array )
    {
        return ! array_diff_key( array_flip( $keys ), $array );
    }


	/**
     * camelCase keys of an array
     *
     * @param array   $array        The array or object to check
	 * @param string  $separator    The character that should be removed
     * @param array   $ignoreKeys   The list of keys to ignore
     * @param bool    $recursive    Whether to loop in indented arrays recursively or not
	 *
     * @return mixed
     */
    public static function camelizeKeys( $array, $separator = '_', $ignoreKeys = [], $recursive = false ) {

    	if( is_object( $array ) ){
    		$array = (array) $array;
		}

		if( ! is_array( $array ) ){
			return $array;
		}

		$result = [];

		foreach( $array as $key => $value ) {
			if( ! in_array( $key, $ignoreKeys ) ){
				$key = Str::camelize( $key, $separator );
			}

			if( $recursive && is_array( $value ) ){
				$value = self::camelizeKeys( $value );
			}

			$result[ $key ] = $value;
		}

		return $result;
	}

	/**
	 * Merges the defined arguments into defaults array.
	 *
	 * @param array|object $args     Value to merge with $defaults.
	 * @param array $defaults        Array that serves as the defaults.
	 *
	 * @return array
	 */
	public static function merge( $args, $defaults = [] ) {
		if ( is_object( $args ) ) {
			$args = get_object_vars( $args );
		}

		if ( is_array( $defaults ) && $defaults ) {
			return array_merge( $defaults, $args );
		}

		return $args;
	}

	/**
	 *  Applies the callback to the elements of the given arrays recursively
	 *
	 * @param array $array
	 * @param       $callback
	 */
	public static function mapDeep( $array, $callback ){
		if ( ! empty( $array ) ) {
			foreach ( $array as $index => $value ) {
				call_user_func( $callback, $value, $index );
				if ( is_array( $value ) ) {
					static::mapDeep( $value, $callback );
				}
			}
		}
	}

	/**
	 * Searches the array for a given key and returns the first corresponding value if successful
	 *
	 * @param $haystack
	 * @param $needle
	 *
	 * @return mixed|null
	 */
	public static function searchKeyDeep( $haystack, $needle ){
		$iterator  = new RecursiveArrayIterator( $haystack );
		$recursive = new RecursiveIteratorIterator(
			$iterator,
			RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ( $recursive as $key => $value ) {
			if ( $key === $needle ) {
				return $value;
			}
		}
		return null;
	}

}
