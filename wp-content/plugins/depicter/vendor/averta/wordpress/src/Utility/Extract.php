<?php
namespace Averta\WordPress\Utility;


class Extract
{
	/**
	 * Extract all images from content
	 *
	 * @param string $text The text to extract images from.
	 *
	 * @return bool|array  List of images in array
	 */
	public static function imagesFromText( $text )
	{
		preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', $text, $matches );
		return isset( $matches ) && count( $matches[0] ) ? $matches : false;
	}

	/**
	 * Get first image src from content
	 *
	 * @param  string $text   The content to extract image from.
	 *
	 * @return string         First image URL on success and empty string if nothing found
	 */
	public static function firstImageSrcFromText( $text )
	{
		$images = self::imagesFromText( $text );

    	return ( $images && count( $images[1]) ) ? $images[1][0] : '';
	}

	/**
	 * Get first image tag from string
	 *
	 * @param  string $text  The text to extract image from.
	 *
	 * @return string        First image tag on success and empty string if nothing found
	 */
	public static function firstImageFromText( $text )
	{
		$images = self::imagesFromText( $text );

    	return ( $images && count( $images[0]) ) ? $images[0][0] : '';
	}


    /**
	 * Crawls the content and checks if the shortcode is present in the content or not
	 * - If list of $attributes was passed, it tries to extract and collect the value of attributes
	 * - and returns an array containing the values
	 *
	 * @param string $content        Content to search for shortcodes.
	 * @param string $shortcodeName  Shortcode tag name to check.
	 * @param array  $attributes     List of shortcode attribute which should be extracted
	 *
	 * @return array|bool            False if shortcode does not exist in the content.
     *                               An array containing attribute values if $attributes is not empty and shortcode exists in the content
     *                               True if shortcode exits in the content
	 */
	public static function shortcodeAttributes( $content, $shortcodeName, $attributes = [] ){
		$hasShortcode = false;

		if ( false === strpos( $content, '[' ) ) {
			return false;
		}
		preg_match_all( '/' . get_shortcode_regex( [ $shortcodeName ] ) . '/', $content, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return false;
		}

		$extractedAttributes = [];

		foreach ( $matches as $shortcode ) {
			// if shortcode found in the content
			if ( $shortcodeName === $shortcode[2] ) {
				$hasShortcode = $hasShortcode || 1;

				if( empty( $attributes ) && $hasShortcode ){
					return true;
				}

				if( empty( $shortcode[3] ) ){
					continue;
				}

				if( is_array( $attributes ) ){
					foreach( $attributes as $attributeName ){
						preg_match( '/'. $attributeName .'=["|\']([^\"\']+)["|\']/m', $shortcode[3], $attrMatches, PREG_OFFSET_CAPTURE, 0);
						if( !empty( $attrMatches[1][0] ) ){
							$extractedAttributes[ $attributeName ][] = $attrMatches[1][0];
						}
					}
				}
			}
		}

		if( !empty( $extractedAttributes ) ){
			return $extractedAttributes;
		}

		return $hasShortcode;
	}
}
