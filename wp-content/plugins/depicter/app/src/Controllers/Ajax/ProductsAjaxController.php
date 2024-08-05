<?php
namespace Depicter\Controllers\Ajax;

use Averta\Core\Utility\Data;
use Averta\WordPress\Utility\JSON;
use Depicter\Utility\Sanitize;
use Psr\Http\Message\ResponseInterface;
use WPEmerge\Requests\RequestInterface;

class ProductsAjaxController
{

	/**
	 * List available products
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return ResponseInterface
	 */
    public function getProducts( RequestInterface $request, $view ) {
    	$args = [];

		if( !Data::isNullOrEmptyStr( $request->body('perpage') ) ){
			$args['perpage'] = Sanitize::int( $request->body('perpage') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('excerptLength') ) ){
			$args['excerptLength'] = Sanitize::int( $request->body('excerptLength') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('offset') ) ){
			$args['offset'] = Sanitize::int( $request->body('offset') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('linkSlides') ) ){
			$args['linkSlides'] = Sanitize::textfield( $request->body('linkSlides') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('orderBy') ) ){
			$args['orderBy'] = Sanitize::textfield( $request->body('orderBy') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('order') ) ){
			$args['order'] = Sanitize::textfield( $request->body('order') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('imageSource') ) ){
			$args['imageSource'] = Sanitize::textfield( $request->body('imageSource') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('excludedIds') ) ){
			$args['excludedIds'] = JSON::decode( $request->body('excludedIds') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('includedIds') ) ){
			$args['includedIds'] = JSON::decode( $request->body('includedIds') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('excludeNonThumbnail') ) ){
			$args['excludeNonThumbnail'] = Sanitize::textfield( $request->body('excludeNonThumbnail') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('from') ) ){
			$args['from'] = Sanitize::textfield( $request->body('from') );
		}
		if( Data::isBool( $request->body('inStockOnly') ) ){
			$args['inStockOnly'] = Sanitize::textfield( $request->body('inStockOnly') );
		}
		if( Data::isBool( $request->body('regularProducts') ) ){
			$args['regularProducts'] = Sanitize::textfield( $request->body('regularProducts') );
		}
		if( Data::isBool( $request->body('downloadableProducts') ) ){
			$args['downloadableProducts'] = Sanitize::textfield( $request->body('downloadableProducts') );
		}
		if( Data::isBool( $request->body('virtualProducts') ) ){
			$args['virtualProducts'] = Sanitize::textfield( $request->body('virtualProducts') );
		}
		if( Data::isBool( $request->body('filterByPrice') ) ){
			$args['filterByPrice'] = Sanitize::textfield( $request->body('filterByPrice') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('startPrice') ) ){
			$args['startPrice'] = Sanitize::textfield( $request->body('startPrice') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('endPrice') ) ){
			$args['endPrice'] = Sanitize::textfield( $request->body('endPrice') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('startSalePrice') ) ){
			$args['startSalePrice'] = Sanitize::textfield( $request->body('startSalePrice') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('endSalePrice') ) ){
			$args['endSalePrice'] = Sanitize::textfield( $request->body('endSalePrice')     );
		}
		if( !Data::isNullOrEmptyStr( $request->body('taxonomies') ) ){
			$args['taxonomies'] = Sanitize::textfield( $request->body('taxonomies') );
		}

		$products = \Depicter::dataSource()->products()->previewRecords( $args );

	    return \Depicter::json([
	    	'hits' => $products
	    ])->withStatus(200);
    }

	/**
	 * List hand picked products
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return ResponseInterface
	 */
	public function getHandPickedProducts( RequestInterface $request, $view ) {
		$args = [];

		if( !Data::isNullOrEmptyStr( $request->body('excerptLength') ) ){
			$args['excerptLength'] = Sanitize::int( $request->body('excerptLength') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('linkSlides') ) ){
			$args['linkSlides'] = Sanitize::textfield( $request->body('linkSlides') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('orderBy') ) ){
			$args['orderBy'] = Sanitize::textfield( $request->body('orderBy') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('order') ) ){
			$args['order'] = Sanitize::textfield( $request->body('order') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('imageSource') ) ){
			$args['imageSource'] = Sanitize::textfield( $request->body('imageSource') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('includedIds') ) ){
			$args['includedIds'] = JSON::decode( $request->body('includedIds') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('excludeNonThumbnail') ) ){
			$args['excludeNonThumbnail'] = Sanitize::textfield( $request->body('excludeNonThumbnail') );
		}
		if( Data::isBool( $request->body('inStockOnly') ) ){
			$args['inStockOnly'] = Sanitize::textfield( $request->body( 'inStockOnly' ) );
		}
		if( !Data::isNullOrEmptyStr( $request->body('taxonomies') ) ){
			$args['taxonomies'] = Sanitize::textfield( $request->body('taxonomies') );
		}

		$products = \Depicter::dataSource()->handPickedProducts()->previewRecords( $args );

		return \Depicter::json([
			   'hits' => $products
		])->withStatus(200);
	}
}
