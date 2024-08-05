<?php


namespace Depicter\DataSources\Tags;


use Averta\Core\Utility\Arr;

class MetaFields extends TagBase implements TagInterface {

	/**
	 *  Asset group ID
	 */
	const ASSET_GROUP_ID = 'metafield';

	/**
	 * Excluded meta keys
	 */
	const EXCLUDED_META_KEYS = [ '_edit_lock', '_edit_last', '_pingme', '_encloseme' ];

	/**
	 * Get label of asset group
	 *
	 * @return string
	 */
	public function getName(){
		return __( "Other MetaFields", 'depicter' );
	}

	/**
	 * Get value of tag by tag name (slug)
	 *
	 * @param string $tagName  Tag name
	 * @param array  $args     Arguments of current document section
	 *
	 * @return mixed|string
	 */
	public function getSlugValue( string $tagName = '', array $args = [] ){
		if( ! $post = get_post( $args['post'] ?? null ) ){
			return $tagName;
		}

		return get_post_meta( $post->ID, $tagName, true );
	}

	/**
	 * Get all acf defined fields
	 *
	 * @param array $args
	 *
	 * @return array $result  List of assets for this module (asset group)
	 */
	public function getAssetBlocks( array $args = [] ) {
		$allMetaFields = $this->getMetaKeysForPostType( $args['postType'] );
		$metaFieldsToBeExcluded = $this->getExcludedMetaFields( $args = [] );

		$result = [];
		foreach( $allMetaFields as $metaField ) {
			if ( in_array( $metaField, array_values( $metaFieldsToBeExcluded ) ) ) {
				continue;
			}
			$title = trim( ucwords( str_replace( ['_', '-'], ' ', $metaField ) ) );

			$result[] = [
				'id'    => $metaField,
				'title' => $title,
				'previewOptions' => [
					"size" => 50,
					'multiline' => false,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicText',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'metafield->' . $metaField )
				]
			];
		}

		return $result;
	}

	/**
	 * Get meta keys for custom post type
	 *
	 * @param string  $postType
	 * @param int     $limit
	 *
	 * @return array
	 */
	protected function getMetaKeysForPostType( string $postType, int $limit = 10 ): array{

		$metaKeys = [];
		$posts    = get_posts(['post_type' => $postType, 'limit' => $limit ]);

		foreach ( $posts as $post ) {
			$postMetaKeys = get_post_custom_keys( $post->ID );
			if ( !empty( $postMetaKeys ) ) {
				$metaKeys = array_merge( $metaKeys, $postMetaKeys );
			}
		}

		// Use array_unique to remove duplicate metaKeys that we received from all posts
		// Use array_values to reset the index of the array
		return array_values( array_unique( $metaKeys ) );
	}

	/**
	 * Get excluded meta fields for a post type
	 *
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	protected function getExcludedMetaFields( array $args = [] ){
		$metaFieldsToBeExcluded = [];

		$acfMetaFields = \Depicter::dataSource()->tagsManager()->getAssetsOfGroup(
			'acf',  [ 'postType' => $args['postType'] ]
		);

		if ( ! empty( $acfMetaFields['items'] ) ) {
			foreach ( $acfMetaFields['items'] as $metaField ) {
				$metaFieldsToBeExcluded[] = $metaField['id'];
				$metaFieldsToBeExcluded[] = '_' . $metaField['id'];
			}
		}

		$metaBoxIoMetaFields = \Depicter::dataSource()->tagsManager()->getAssetsOfGroup(
			'metaboxio',  [ 'postType' => $args['postType'] ]
		);

		if ( ! empty( $metaBoxIoMetaFields['items'] ) ) {
			foreach ( $metaBoxIoMetaFields['items'] as $metaField ) {
				$metaFieldsToBeExcluded[] = $metaField['id'];
			}
		}

		$metaFieldsToBeExcluded = Arr::merge( $metaFieldsToBeExcluded, static::EXCLUDED_META_KEYS );

		return apply_filters( 'depicter/dataSource/tags/metafields/excluded', $metaFieldsToBeExcluded, $args );
	}
}
