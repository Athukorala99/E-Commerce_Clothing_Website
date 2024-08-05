<?php

namespace Depicter\DataSources\Tags;

use Averta\WordPress\Utility\Post;

/**
 * Asset Group which keeps supporting legacy tags
 *
 * {{{slug}}}
 *
 */
class Legacy extends TagBase implements TagInterface {

	/**
	 *  Asset group ID
	 */
	const ASSET_GROUP_ID = 'v1';

	/**
	 * Get label of asset group
	 *
	 * @return string
	 */
	public function getName(){
		return __( "Legacy Tags", 'depicter' );
	}

	/**
	 * Get list of assets in this group
	 *
	 * @param array  $args
	 *
	 * @return array
	 */
	public function getAssetBlocks( array $args = [] ){
		return [];
	}

	/**
	 * Check if a legacy dynamic tag is in the given text or not, return the tag on success. false otherwise
	 *
	 * @param string  $needle
	 *
	 * @return bool|string
	 */
	public function findLegacyTag( string $needle ){
		if( ! $needle = trim( $needle ) ){
			return false;
		}

		$tags = [
			'{{{uuid}}}',
			'{{{linkSlides}}}',
			'{{{url}}}',
			'{{{id}}}',
			'{{{featuredImage}}}',
			'{{{featuredImageSrc}}}',
			'{{{author.name}}}',
			'{{{author.page}}}',
			'{{{title}}}',
			'{{{excerpt}}}',
			'{{{content}}}',
			'{{{date}}}',
			'{{{readMore}}}',
			'{{post_tagToStr}}',
			'{{categoryToStr}}',

			'{{{secondaryImage}}}',
			'{{{price}}}',
			'{{{salePrice}}}',
			'{{{ratingAverage}}}',
			'{{{shortDescription}}}',
			'{{{stockStatus}}}',
			'{{{stockStatusClass}}}',
			'{{{stockQuantity}}}',
			'{{product_catToStr}}',
			'{{product_tagToStr}}'
		];

		foreach( $tags as $tag ){
			if( false !== strpos( $tag, $needle ) ){
				return $tag;
			}
		}

		return false;
	}

	/**
	 * Get value of tag by tag name (slug)
	 *
	 * @param string $tagName  Tag name
	 * @param array  $args     Arguments of current document section
	 *
	 * @return string|null
	 */
	public function getSlugValue( string $tagName = '', array $args = [] ){

		if( ! $post = get_post( $args['post'] ?? null ) ){
			return $tagName;
		}

		$result = $tagName;

		switch ( $tagName ) {
			case '{{{uuid}}}':
			case '{{{id}}}':
				$result = $post->ID;
				break;

			case '{{{linkSlides}}}':
				$result = !! $args['linkSlides'];
				break;

			case '{{{url}}}':
				$result = get_permalink( $post->ID );
				break;

			case '{{{title}}}':
				$result = get_the_title( $post->ID );
				break;

			case '{{{featuredImage}}}':
				$result = get_post_thumbnail_id( $post->ID );
				break;

			case '{{{featuredImageSrc}}}':
				$thumbnailId = get_post_thumbnail_id( $post->ID );
				$featuredImageInfo = wp_get_attachment_image_src( $thumbnailId, 'full' );
				$result = ! empty( $featuredImageInfo[0] ) ? $featuredImageInfo[0] : '';
				break;

			case '{{{date}}}':
				$result = get_the_date('Y-m-d h:m:s', $post->ID );
				break;

			case '{{{excerpt}}}':
				$result = !empty( $args['excerptLength'] ) ? Post::getExcerptTrimmedByChars( $post->ID, $args['excerptLength'] ) : Post::getExcerptTrimmedByChars( $post->ID );
				break;

			case '{{{author.name}}}':
				$result = get_the_author_meta( 'display_name', $post->post_author );
				break;

			case '{{{author.page}}}':
				$result = get_author_posts_url( $post->post_author );
				break;

			case '{{{content}}}':
				$result = get_the_content(null, false, $post->ID );
				break;

			case '{{categoryToStr}}':
				$result = $this->getTaxonomyTermsStr( $post->ID, 'category' );
				break;

			case '{{post_tagToStr}}':
				$result = $this->getTaxonomyTermsStr( $post->ID, 'post_tag' );
				break;


			case '{{{secondaryImage}}}':
				if( ! $product = $this->getProduct( $post->ID ) ){
					break;
				}
				$attachment_ids = $product->get_gallery_image_ids();
				$result = !empty( $attachment_ids[0] ) ? $attachment_ids[0] : '';
				break;

			case '{{{price}}}':
				if( ! $product = $this->getProduct( $post->ID ) ){
					break;
				}
				$result = wc_price( $product->get_regular_price() ) . $product->get_price_suffix();
				break;

			case '{{{salePrice}}}':
				if( ! $product = $this->getProduct( $post->ID ) ){
					break;
				}
				$result = $product->is_on_sale() ? wc_price( $product->get_sale_price() ) . $product->get_price_suffix() : '';
				break;

			case '{{{ratingAverage}}}':
				if( ! $product = $this->getProduct( $post->ID ) ){
					break;
				}
				$result = $product->get_average_rating();
				break;

			case '{{{shortDescription}}}':
				if( ! $product = $this->getProduct( $post->ID ) ){
					break;
				}
				$result = $product->get_short_description();
				break;

			case '{{{stockStatus}}}':
				if( ! $product = $this->getProduct( $post->ID ) ){
					break;
				}
				$result = $this->getStockStatus( $product );
				break;

			case '{{{stockStatusClass}}}':
				if( ! $product = $this->getProduct( $post->ID ) ){
					break;
				}
				$result = $product->is_in_stock() ? 'in-stock' : 'out-of-stock';
				break;

			case '{{{stockQuantity}}}':
				if( ! $product = $this->getProduct( $post->ID ) ){
					break;
				}
				$result = $product->get_stock_quantity();
				break;

			case '{{product_catToStr}}':
				$result = $this->getTaxonomyTermsStr( $post->ID, 'product_cat' );
				break;

			case '{{product_tagToStr}}':
				$result = $this->getTaxonomyTermsStr( $post->ID, 'product_tag' );
				break;

			default:
				$result = null;
				break;
		}

		return $result;
	}

	/**
	 * Get taxonomy terms string
	 *
	 * @param int    $postID
	 * @param string $taxonomy
	 *
	 * @return string
	 */
	protected function getTaxonomyTermsStr( int $postID, string $taxonomy ) {
		$terms = get_the_terms( $postID, $taxonomy );

		if ( !empty( $terms ) ) {
			return join( ', ', wp_list_pluck($terms, 'name') );
		}

		return '';
	}

	/**
	 * Retrieves product stock status
	 *
	 * @param $product
	 *
	 * @return mixed|void
	 */
	protected function getStockStatus( $product ){
		if ( $product->is_on_backorder() ) {
			$stock_html = __( 'On backorder', 'depicter' );
		} elseif ( $product->is_in_stock() ) {
			$stock_html = __( 'In stock', 'depicter' );
		} else {
			$stock_html = __( 'Out of stock', 'depicter' );
		}
		return apply_filters( 'woocommerce_admin_stock_html', $stock_html, $product );
	}

	/**
	 * Get $product instance by product ID
	 *
	 * @param $productID
	 *
	 * @return false|\WC_Product
	 */
	private function getProduct( $productID ){
		if( ! function_exists( 'wc_get_product' ) ){
			return false;
		}
		if ( ! $product = wc_get_product( $productID ) ) {
			return false;
		}
		return $product;
	}
}
