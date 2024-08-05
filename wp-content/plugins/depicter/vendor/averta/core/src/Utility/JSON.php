<?php
namespace Averta\Core\Utility;


class JSON
{
	/**
     * Detect is JSON
     *
     * @param $args
     *
     * @return bool
     */
    public static function isJson(...$args)
    {
        if(is_array($args[0]) || is_object($args[0])) {
            return false;
        }

        if (trim($args[0]) === '') {
            return false;
        }

        json_decode(...$args);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Remove extra white-spaces and tabs from json string
     *
     * @param string $json
     *
     * @return string
     */
    public static function normalize( $json )
    {
        if( ! is_string( $json ) ) {
            return $json;
        }

        if (trim( $json ) === '') {
            return '';
        }

        $decoded = json_decode( $json );
        return (json_last_error() == JSON_ERROR_NONE) ? json_encode( $decoded ) : $json;
    }

    /**
	 * Encode a variable into JSON, with some sanity checks.
	 *
	 * @param mixed $value  The value being encoded. Can be any type except a resource.
	 * 						All string data must be UTF-8 encoded.
	 * @param int   $flags  Options to be passed to json_encode(). Default 0.
	 * @param int   $depth  Set the maximum depth. Must be greater than zero.
	 *
	 * @return false|string
	 */
    public static function encode( $value, $flags = 0, $depth = 512 )
    {
        return json_encode( $value, $flags, $depth );
    }

	/**
	 * Takes a JSON encoded string and converts it into a PHP variable.
	 *
	 * @param string    $json        The json string being decoded.
	 * @param bool|null $associative When true, JSON objects will be returned as associative arrays; when false, JSON objects will be returned as objects.
	 * 								 When null, JSON objects will be returned as associative arrays or objects depending on whether JSON_OBJECT_AS_ARRAY is set in the flags.
	 * @param int       $depth       Maximum nesting depth of the structure being decoded.
	 * @param int       $flags       Bitmask of JSON_BIGINT_AS_STRING, JSON_INVALID_UTF8_IGNORE, JSON_INVALID_UTF8_SUBSTITUTE, JSON_OBJECT_AS_ARRAY, JSON_THROW_ON_ERROR
	 *
	 * @return mixed
	 */
    public static function decode( $json, $associative = null, $depth = 512, $flags = 0 )
    {
        return json_decode( $json, $associative, $depth, $flags );
    }

}
