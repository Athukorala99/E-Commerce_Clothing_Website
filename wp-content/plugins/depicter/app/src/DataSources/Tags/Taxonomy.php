<?php
namespace Depicter\DataSources\Tags;

use Depicter\Document\CSS\Selector;
use Depicter\Html\Tag;

/**
 * Asset Group for Wp Taxonomies
 *
 * {{{module->slug|func('a','b')}}}
 *
 */
class Taxonomy extends TagBase {

	/**
	 *  Asset group ID
	 */
	const ASSET_GROUP_ID = 'taxonomy';

	/**
	 * Get label of asset group
	 *
	 * @return string
	 */
	public function getName(){
		return __( "Taxonomies", 'depicter' );
	}

	/**
	 * Get list of assets in this group
	 *
	 * @param array  $args
	 *
	 * @return array
	 */
	public function getAssetBlocks( array $args = [] ){
		if( empty( $args['postType'] ) ){
			return [];
		}
		if( ! $taxonomyObjects = get_object_taxonomies( $args['postType'], 'objects' ) ){
			return [];
		}

		$blocks = [];
		$excludedAssetIds = $this->getExcludedAssetIds( $args );

		foreach( $taxonomyObjects as $taxonomySlug => $taxonomyObject ){
			if( ! $taxonomyObject->public ?? false ){
				continue;
			}
			if( in_array( $taxonomySlug, $excludedAssetIds ) ){
				continue;
			}
			$blocks[] = [
				'id'    => $taxonomySlug,
				'title' => $taxonomyObject->label ?? $taxonomyObject->labels->name ?? $taxonomyObject->name ?? 'Undefined',
				'previewOptions' => [
					'size' 	=> 50,
					'variant' => 'button',
					'badge'	=> null
				],
				'type'  => 'dynamicTagList',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( self::ASSET_GROUP_ID . '->' . $taxonomySlug . '.terms' )
				]
			];
		}

		return $blocks;
	}

	/**
	 * Get value of tag by tag name (slug)
	 *
	 * @param string $tagName  Tag name
	 * @param array  $args     Arguments of current document section
	 *
	 * @return false|string|\WP_Error|\WP_Term[]
	 */
	public function getSlugValue( string $tagName = '', array $args = [] ){
		if( ! $post = get_post( $args['post'] ?? null ) ){
			return $tagName;
		}

		$tagName = $tagName === 'tag' ? 'post_tag' : $tagName;
		if( ! $tagName = explode( '.', $tagName )[0] ?? '' ){
			return '';
		}
		if( ! $terms = get_the_terms( $post->ID, $tagName ) ){
			return '';
		}

		if ( !empty( $args['limit'] ) && count( $terms ) > $args['limit'] ) {
			$terms = array_slice( $terms, 0, $args['limit'] );
		}

		$classPrefix = Selector::prefixify('tag');

		$termsList = array_map( function( $term ) use ( $args, $classPrefix ) {
			$label = $term->name;
			if ( ! empty( $args['linkTags'] ) ) {
				$label = Tag::el( 'a', [ "href" => get_term_link( $term ) ], $label );
			}

			return Tag::el( 'span', [ "class" => $classPrefix . "-item" ], $label );
		}, $terms );


		return !empty( $args['separator'] ) ? implode( "<span class=\"{$classPrefix}-sep\">" . $args['separator'] . '</span>', $termsList ) : implode( '', $termsList );
	}

	/**
	 * Converts list of terms to string
	 *
	 * @param mixed $pipedValue The tag value
	 * @param array $funcArgs   Function args presented in dynamic tag
	 * @param array $args       Arguments of current document section
	 *
	 * @return mixed|string
	 */
	public function toStr( $pipedValue, array $funcArgs = [], array $args = [] ){
		if ( !empty( $pipedValue ) ) {
			return join( ', ', wp_list_pluck( $pipedValue, 'name') );
		}
		return $pipedValue;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param array  $args
	 *
	 * @return array
	 */
	protected function getExcludedAssetIds( $args = [] ){
		return apply_filters( 'depicter/dataSource/tags/taxonomy/assets/excluded', [ 'post_format', 'product_shipping_class' ], $args );
	}
}
