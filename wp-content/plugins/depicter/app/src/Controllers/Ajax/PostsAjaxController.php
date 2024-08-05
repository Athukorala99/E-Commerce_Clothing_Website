<?php
namespace Depicter\Controllers\Ajax;

use Averta\Core\Utility\Data;
use Averta\WordPress\Utility\JSON;
use Depicter\Utility\Sanitize;
use Psr\Http\Message\ResponseInterface;
use WPEmerge\Requests\RequestInterface;

class PostsAjaxController
{
	/**
	 * list available post types with their taxonomies
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return ResponseInterface
	 */
    public function getPostTypes( RequestInterface $request, $view ) {
	    $postType = !empty( $request->query('postType') ) ? Sanitize::textfield( $request->query('postType') ) : 'all';
    	$result = \Depicter::dataSource()->posts()->getTypes( $postType );

		return \Depicter::json([
			'hits' => $result
		])->withStatus(200);
    }

	/**
	 * List available posts for custom post type
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return ResponseInterface
	 */
    public function getPosts( RequestInterface $request, $view ) {
    	$args = [];

		if( !Data::isNullOrEmptyStr( $request->body('postType') ) ){
			$args['postType'] = Sanitize::textfield( $request->body('postType') );
		}
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
;		}
		if( !Data::isNullOrEmptyStr( $request->body('includedIds') ) ){
			$args['includedIds'] = JSON::decode( $request->body('includedIds') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('excludeNonThumbnail') ) ){
			$args['excludeNonThumbnail'] = Sanitize::textfield( $request->body('excludeNonThumbnail') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('taxonomies') ) ){
			$args['taxonomies'] = Sanitize::textfield( $request->body('taxonomies') );
		}

		// check if request is for handpicked data or not
		if ( !Data::isNullOrEmptyStr( $request->body('handpicked') ) ) {
			$args['orderBy'] = 'post__in';
		}

		$posts = \Depicter::dataSource()->posts()->previewRecords( $args );

	    return \Depicter::json([
	    	'hits' => $posts
	    ])->withStatus(200);
    }

	/**
	 * Search posts
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return ResponseInterface
	 */
    public function searchPosts( RequestInterface $request, $view ) {
		$args = [];

		if( !Data::isNullOrEmptyStr( $request->body('postType') ) ){
			$args['postType'] = Sanitize::textfield( $request->body('postType') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('perpage') ) ){
			$args['perpage'] = Sanitize::int( $request->body('perpage') );
		} else {
			$args['perpage'] = 20;
		}
		if( !Data::isNullOrEmptyStr( $request->body('page') ) ){
			$args['page'] = Sanitize::int( $request->body('page') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('s') ) ){
			$args['s'] = Sanitize::textfield( $request->body('s') );
		}
		if( !Data::isNullOrEmptyStr( $request->body('excludedIds') ) ){
			$args['excludedIds'] = JSON::decode( $request->body('excludedIds') );
		}

		$posts = \Depicter::dataSource()->posts()->searchRecordsByTitle( $args );

		return \Depicter::json([
			'hits' => $posts
	    ])->withStatus(200);
	}
}
