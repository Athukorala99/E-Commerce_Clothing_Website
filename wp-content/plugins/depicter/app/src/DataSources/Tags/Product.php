<?php

namespace Depicter\DataSources\Tags;

use Averta\WordPress\Utility\Plugin;

/**
 * Asset Group for WooCommerce
 *
 * {{{module->slug|func}}}
 * {{{module->slug|func('a','b')}}}
 *
 */
class Product extends TagBase implements TagInterface {
	/**
	 *  Asset group ID
	 */
	const ASSET_GROUP_ID = 'product';

	/**
	 * Get label of asset group
	 *
	 * @return string
	 */
	public function getName(){
		return __( "WooCommerce", 'depicter' );
	}

	/**
	 * Whether the asset group is enabled (available) or not
	 *
	 * @param array  $args
	 *
	 * @return bool
	 */
	public function isAvailable( array $args = [] ){
		return Plugin::isActive( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Get list of assets in this group
	 *
	 * @param array  $args
	 *
	 * @return array
	 */
	public function getAssetBlocks( array $args = [] ){

		return [
			[
				'id'    => 'secondaryImage',
				'title' => __( 'Secondary Image', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'badge' => null
				],
				'type'  => 'dynamicMedia',
				'sourceType' => 'image',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'product->secondaryImage|toImage' ),
					'src'    => $this->wrapCurly( 'product->secondaryImage.src' )
				]
			],
			[
				'id'    => 'price',
				'title' => __( 'Product price', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'multiline' => false,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicText',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'product->price' )
				]
			],
			[
				'id'    => 'regularPrice',
				'title' => __( 'Product regular price', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'multiline' => false,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicText',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'product->regularPrice' )
				]
			],
			[
				'id'    => 'salePrice',
				'title' => __( 'Product on sale price', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'multiline' => false,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicText',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'product->salePrice' )
				]
			],
			[
				'id'    => 'rating',
				'title' => __( 'Product Rating', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'multiline' => false,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicRating',
				'func'  => 'toStr',
				'payload' => [
					'source' => $this->wrapCurly( 'product->rating' )
				]
			],
			[
				'id'    => 'ratingCount',
				'title' => __( 'Product Rating Count', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'multiline' => false,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicText',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'product->ratingCount' )
				]
			],
			[
				'id'    => 'shortDescription',
				'title' => __( 'Short description', 'depicter' ),
				'previewOptions' => [
					"size" => 100,
					'multiline' => false,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicText',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'product->shortDescription|wTrim(50)' )
				]
			],
			[
				'id'    => 'stockStatus',
				'title' => __( 'In stock status', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'multiline' => false,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicStockStatus',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'product->stockStatus' )
				]
			],
			[
				'id'    => 'stockQuantity',
				'title' => __( 'In stock quantity', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'multiline' => false,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicText',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'product->stockQuantity' )
				]
			],
			[
				'id'    => 'sku',
				'title' => __( 'SKU', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'multiline' => false,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicText',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'product->sku' )
				]
			],
			[
				'id'    => 'dimensions',
				'title' => __( 'Dimensions', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'multiline' => false,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicText',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'product->dimensions' )
				]
			]
		];

	}

    /**
	 * Get value of tag slug
	 *
	 * @param string $tagName  Tag name
	 * @param array  $args     Arguments of current document section
	 *
	 * @return string|null
	 */
	public function getSlugValue( string $tagName = '', array $args = [] ){

		if( ! $product = wc_get_product( $args['post'] ?? null ) ){
			return $tagName;
		}

		$result = $tagName;

		switch ( $tagName ) {

			case 'secondaryImage':
				$attachment_ids = $product->get_gallery_image_ids();
				if( !empty( $attachment_ids[0] ) ){
					$result = $attachment_ids[0];
				}
				break;

			case 'price':
				$result = wc_price( $product->get_price() ) . $product->get_price_suffix();
				break;

			case 'regularPrice':
				$result = wc_price( $product->get_regular_price() ) . $product->get_price_suffix();
				break;

			case 'salePrice':
				$result = $product->is_on_sale() ? wc_price( $product->get_sale_price() ) . $product->get_price_suffix() : '';
				break;

            case 'rating':
                $result = $product->get_average_rating();
                break;

            case 'ratingCount':
				$result = $product->get_rating_count();
				break;

			case 'shortDescription':
				$result = $product->get_short_description();
				break;

			case 'stockStatus':
				$result = $this->getStockStatus( $product );
				break;

			case 'stockStatusClass':
				$result = $product->is_in_stock() ? 'in-stock' : 'out-of-stock';
				break;

			case 'stockQuantity':
				$result = $product->get_stock_quantity();
				break;

			case 'sku':
				$result = $product->get_sku();
				break;

			case 'dimensions':
				$result = $product->get_dimensions();
				break;

			default:
				$result = null;
				break;
		}

		return $result;
	}

    /**
	 * Get Rating Stars
	 *
	 * @param mixed $pipedValue The tag value
	 * @param array $funcArgs   Function args presented in dynamic tag
	 * @param array $args       Arguments of current document section
	 *
	 * @return string
	 */
	public function toStar( $pipedValue, array $funcArgs = [], array $args = [] ){
        return wc_get_star_rating_html( $pipedValue );
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
}
