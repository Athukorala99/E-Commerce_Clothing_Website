<?php
namespace Depicter\Services;

use Averta\WordPress\Utility\JSON;
use Depicter\GuzzleHttp\Exception\GuzzleException;

/**
 * A bridge for fetching assets from averta assets API
 *
 * @package Depicter\Services
 */
class AssetsAPIService
{
	/**
	 * Search Media AssetsProvider
	 *
	 * @param string $assetType
	 * @param array  $options
	 *
	 * @return array|mixed
	 * @throws GuzzleException
	 */
	public static function searchAssets( string $assetType = 'photos', array $options = [] )
	{
		$availableTypes = ['photos', 'videos', 'vectors'];
		if ( !in_array( $assetType, $availableTypes ) ) {
			return [];
		}

		$response = \Depicter::remote()->get( 'v1/search/'. $assetType, [ "query" => $options ] );

		return JSON::decode( $response->getBody(), true );
	}

	/**
	 * Get Asset Hotlink URL
	 *
	 * @param string $id    Media ID
	 * @param string $size  Media size to return
	 *
	 * @param array  $args
	 *
	 * @return mixed
	 */
	public static function getHotlink( $id, string $size = 'full', array $args = [ 'forcePreview' => 'false' ] ){
		$endpointNumber = 1;

		/**
		 * Regex to find possible endpoint number other than default one (1)
		 *
		 * @example if $id = @Fd2X@UnSiqwe8TLRnG8k, $endpointNumber is 2, and $id @UnSiqwe8TLRnG8k
		 */
		preg_match( '/^@Fd(\d{1,})X(.*)/', $id, $matches );
		if( !empty( $matches[1] ) && !empty( $matches[2] ) ){
			$endpointNumber = $matches[1];
			$id = $matches[2]; // set extracted id
		}

		$endpoint = \Depicter::remote()->endpoint( $endpointNumber ) . 'v1/media/' . $id . '/' . $size . '/';

		if ( !empty( $args ) && is_array( $args ) ) {
			$endpoint = add_query_arg( $args, $endpoint );
		}

		return $endpoint;
	}

	/**
	 * Get Urls of an asset
	 *
	 * @param string $id  Asset ID
	 *
	 * @return mixed
	 * @throws GuzzleException
	 */
	public static function getAssetUrls( $id ){
		$response = \Depicter::remote()->get( 'v1/asset/' . $id . '/urls' );
		return JSON::decode( $response->getBody(), true );
	}

	/**
	 * Search Elements
	 *
	 * @param array $options
	 *
	 * @return mixed
	 * @throws GuzzleException
	 */
	public static function searchElements( array $options = [] ) {
		$response = \Depicter::remote()->get(
			'v1/curated/elements',
			[ 'query' => $options ],
			$options['directory'] ?? 1
		);

		return JSON::decode( $response->getBody(), true );
	}

	/**
	 * Search Document Templates
	 *
	 * @param array $options
	 *
	 * @return mixed
	 * @throws GuzzleException
	 */
	public static function searchDocumentTemplates( array $options = [] ) {
		$response = \Depicter::remote()->get(
			'v1/curated/document/templates',
			[ 'query' => $options ],
			$options['directory'] ?? 2
		);

		return JSON::decode( $response->getBody(), true );
	}

	/**
	 * Get templates categories
	 *
	 * @param array $options
	 *
	 * @return mixed
	 * @throws GuzzleException
	 */
	public static function getDocumentTemplateCategories( array $options = [] ) {
		$response = \Depicter::remote()->get(
			'v1/curated/document/templates/categories',
			[ 'query' => $options ],
			$options['directory'] ?? 2
		);

		return JSON::decode( $response->getBody(), true );
	}

	/**
	 * Get Template Data
	 *
	 * @param int   $templateID  Template ID
	 * @param array $options
	 * @param bool  $associative
	 *
	 * @return mixed
	 * @throws GuzzleException
	 */
	public static function getDocumentTemplateData( $templateID, $options = [], $associative = false ) {
		$response = \Depicter::remote()->get(
			'v1/curated/document/templates/' . $templateID,
			[ 'query' => $options ],
			$options['directory'] ?? 2
		);

		return JSON::decode( $response->getBody(), $associative );
	}

	/**
	 * Preview a Document Template
	 *
	 * @param int      $templateID
	 * @param int|null $directory
	 *
	 * @return mixed
	 * @throws GuzzleException
	 */
	public static function previewDocumentTemplate( $templateID, $directory = null ) {
		$directory = $directory ?? 2;
		$response = \Depicter::remote()->get(
			'v1/curated/document/templates/preview',
			[ "query" => [ "id" => $templateID, "directory" => $directory ] ],
			$directory
		);

		return $response->getBody();
	}

	/**
	 * Search Animations
	 *
	 * @param array $options
	 *
	 * @return mixed
	 * @throws GuzzleException
	 */
	public static function searchAnimations( array $options = [] ) {
		$response = \Depicter::remote()->get(
			'v1/curated/animations',
			[ 'query' => $options ],
			$options['directory'] ?? 1
		);

		return JSON::decode( $response->getBody(), true );
	}

	/**
	 * Retrieving animation phases
	 *
	 * @param array $options
	 *
	 * @return mixed
	 * @throws GuzzleException
	 */
	public static function getAnimationsCategories( array $options = [] ) {
		$response = \Depicter::remote()->get(
			'v1/curated/animations/categories',
			[ 'query' => $options ],
			$options['directory'] ?? 1
		);

		return JSON::decode( $response->getBody(), true );
	}

	/**
	 * Get Template Groups
	 *
	 * @param array $options
	 *
	 * @return void
	 * @throws GuzzleException
	 */
	public static function getDocumentTemplateGroups( array $options = [] ) {
		$response = \Depicter::remote()->get(
			'v2/curated/document/templates/groups',
			[ 'query' => $options ],
			$options['directory'] ?? 1
		);

		return JSON::decode( $response->getBody(), true );
	}

	/**
	 * Search Document Templates
	 *
	 * @param array $options
	 *
	 * @return mixed
	 * @throws GuzzleException
	 */
	public static function searchDocumentTemplatesV2( array $options = [] ) {
		$response = \Depicter::remote()->get(
			'v2/curated/document/templates',
			[ 'query' => $options ],
			$options['directory'] ?? 2
		);

		return JSON::decode( $response->getBody(), true );
	}
}
