<?php
namespace Averta\Core\Utility;

class Trim
{
	/**
	 * Trim string by character length.
	 *
	 * @param        $string
	 * @param int    $max_length
	 * @param string $more
	 *
	 * @return string
	 */
    public static function text( $string, $max_length = 1000, $more = " ..." ){
		$string_length = function_exists('mb_strwidth') ? mb_strwidth( $string ) : strlen( $string );

		if( $string_length > $max_length && ! empty( $max_length ) ){
			return function_exists( 'mb_strimwidth' ) ? mb_strimwidth( $string, 0, $max_length, '' ) . $more : substr( $string, 0, $max_length ) . $more;
		}

		return $string;
	}

}
