<?php

namespace Depicter\Controllers\Ajax;

use Averta\Core\Utility\Data;
use Averta\WordPress\Utility\Sanitize;
use Psr\Http\Message\ResponseInterface;
use WPEmerge\Requests\RequestInterface;

class DataSourceAjaxController {

	/**
	 * List available asset groups for a dataSource
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return ResponseInterface
	 */
    public function getAssets( RequestInterface $request, $view ){
	    $args = [];

		$args['type'] = Sanitize::textfield( $request->query( 'type' ) ) ?: 'wpPost';

	    if( ! Data::isNullOrEmptyStr( $request->query( 'postType' ) ) ){
		    $args['postType'] = Sanitize::textfield( $request->query( 'postType' ) );
	    } else {
			$args['postType'] = \Depicter::dataSource()->getPostTypeByType( $args['type'] );
	    }

		return \Depicter::json(
			[ 'hits' => \Depicter::dataSource()->getByType( $args['type'] )->getAssets( $args ) ]
		)->withStatus(200);
    }
}
