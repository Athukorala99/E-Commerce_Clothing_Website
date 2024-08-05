<?php
namespace Depicter\Utility;

use \Averta\WordPress\Utility\Sanitize as SanitizeBase;

class Sanitize extends SanitizeBase {

	public static function html( $input, $allowed_tags = null, $namespace = null, $auto_p = false ){
		// A fix to allow empty data url for src in image tag
		if( $namespace === 'depicter/output' ){
			add_filter( 'wp_kses_uri_attributes', [ __CLASS__, 'skipSrcEscapeTemporary' ], 25 );
			add_filter( 'safe_style_css', [ __CLASS__, 'modifyAllowedCssAttributes' ]);
			add_filter( 'safecss_filter_attr_allow_css', [ __CLASS__, 'checkAllowedCssValue' ], 10, 2);
		}
		$sanitized = parent::html( $input, $allowed_tags, $namespace, $auto_p );
		if( $namespace === 'depicter/output' ){
			remove_filter( 'wp_kses_uri_attributes', [ __CLASS__, 'skipSrcEscapeTemporary' ], 25 );
			remove_filter( 'safe_style_css', [ __CLASS__, 'modifyAllowedCssAttributes' ]);
			remove_filter( 'safecss_filter_attr_allow_css', [ __CLASS__, 'checkAllowedCssValue' ], 10, 2);
		}

		return $sanitized;
	}

	/**
     * Retrieves default WordPress HTML tags
     *
     * @return array
     */
	protected static function defaultAllowedTags(){
		$tags = parent::defaultAllowedTags();

		$tags['style'] = [
			'type'  => true
		];
		$tags['script'] = [
			'id'    => true,
			'src'   => true
		];
		$tags['link'] = [
			'rel'   => true,
			'id'    => true,
			'href'  => true,
			'media' => true,
		];
		return $tags;
	}

	/**
	 * Ignore src escaping because `wp_kses` strips `data:` from image placeholder source in PHP 8.0+
	 *
	 * @param array $uriAttributes
	 *
	 * @return array $uriAttributes
	 */
	public static function skipSrcEscapeTemporary( $uriAttributes ) {
	    if ( ( $key = array_search( 'src', $uriAttributes ) ) !== false) {
			unset( $uriAttributes[ $key ] );
		}
		return $uriAttributes;
	}

	/**
	 * Modify allowed css attributes
	 *
	 * @param $properties
	 *
	 * @return mixed
	 */
	public static function modifyAllowedCssAttributes( $properties ) {
		$properties = array_merge( $properties, [
			'fill',
			'opacity',
			'stroke',
			'stroke-width',
			'stroke-opacity',
			'fill-opacity',
			'transform'
		]);

		return $properties;
	}

	/**
	 * Check for allowed css values
	 *
	 * @param $allowed
	 * @param $css_test_string
	 *
	 * @return bool
	 */
	public static function checkAllowedCssValue( $allowed, $css_test_string ): bool{

		$allowedCssValues = [
			'rotate',
			'scale'
		];

		foreach( $allowedCssValues as $value ) {
			if ( ! $allowed && str_contains( $css_test_string, $value ) ) {
				return true;
			}
		}
		return $allowed;
	}
}
