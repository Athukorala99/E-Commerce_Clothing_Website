<?php
namespace Depicter\Controllers\Ajax;

use Depicter;
use Depicter\GuzzleHttp\Exception\ConnectException;
use Depicter\GuzzleHttp\Exception\GuzzleException;
use Depicter\Services\AssetsAPIService;
use Depicter\Utility\Sanitize;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WPEmerge\Requests\Request;

/**
 * Retrieves data from depicter server
 *
 * Class MediaAssetsAPIAjaxController
 *
 * @package Depicter\Controllers\Ajax
 */
class MediaAssetsAPIAjaxController
{

	/**
	 * Search Photos
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 * @throws GuzzleException
	 */
	public function searchImages( RequestInterface $request, $view )
	{
		// available values are => "landscape", "portrait", "squarish"
		$search = ! empty( $request->query('s') ) ? Sanitize::textfield( $request->query('s') ) : '';

		$page = !empty( $request->query('page') ) ? Sanitize::int( $request->query('page') ) : 1;
		$perpage = !empty( $request->query('perpage') ) ? Sanitize::int( $request->query('perpage') ) : 20;

		// available values are => "landscape", "portrait", "squarish"
		$orientation = !empty( $request->query('orientation') ) ? Sanitize::textfield( $request->query('orientation') ) : null;

		// available values are => Default: null / If multiple, comma-separated
		$collections = !empty( $request->query('collections') ) ? Sanitize::textfield( $request->query('collections') ) : null;

		// available values are => relevant, latest
		$orderBy = !empty( $request->query('orderBy') ) ? Sanitize::textfield( $request->query('orderBy') ) : 'relevant';

		$options = [
			's'             => $search,
			'page'          => $page,
			'perpage'       => $perpage,
			'orientation'   => $orientation,
			'collections'   => $collections,
			'orderBy'       => $orderBy
		];
		try {
			$result = AssetsAPIService::searchAssets( 'photos', $options );

			return \Depicter::json( $result );

		} catch ( ConnectException $exception ) {
			return \Depicter::json([
				'errors' => [ 'Connection error ..', $exception->getMessage() ]
			])->withStatus(503);
		} catch ( \Exception  $exception ) {
			// set status code to 404 if 404 Not found was in response of unsplash API
			$statusCode = false !== strpos( $exception->getMessage(), '404' ) ? 404 : 200;
			return \Depicter::json([
				'errors' => [ $exception->getMessage() ]
			])->withStatus( $statusCode );
		}

	}

	/**
	 * Hotlinks to a media src
	 *
	 * @param Request $request
	 * @param string  $view
	 *
	 * @return ResponseInterface
	 * @throws \Exception
	 */
	public function getMedia( Request $request, $view ) {
		$id   = ! empty( $request->query('id'  ) ) ? Sanitize::textfield( $request->query('id'  ) ) : '';
		$size = ! empty( $request->query('size') ) ? Sanitize::textfield( $request->query('size') ) : '';

		$mediaHotlinkUrl = \Depicter::media()->getSourceUrl( $id );
		$clientKey = \Depicter::auth()->getClientKey();

		return Depicter::redirect()->to( $mediaHotlinkUrl )
		                           ->withHeader( 'Access-Control-Allow-Origin', '*' )
		                           ->withHeader( 'X-DEPICTER-CKEY', $clientKey );
	}

	/**
	 * Retrieves url of a media by size
	 *
	 * @param Request $request
	 * @param string  $view
	 *
	 * @return ResponseInterface
	 * @throws \Exception
	 */
	public function getMediaUrl( Request $request, $view ) {
		$id   = ! empty( $request->query('id'  ) ) ? Sanitize::textfield( $request->query('id'  ) ) : '';
		$size = ! empty( $request->query('size') ) ? Sanitize::textfield( $request->query('size') ) : 'large';

		try {
			$mediaHotlinkUrl = \Depicter::media()->getSourceUrl( $id, $size );
			return \Depicter::json([ 'url'=> $mediaHotlinkUrl ]);

		} catch ( GuzzleException $exception ) {
			return \Depicter::json([
				'errors' => [ 'Connection error ..', $exception->getMessage() ]
			])->withStatus(503);
		} catch ( \Exception  $exception ) {
			return \Depicter::json([
				'errors' => [ $exception->getMessage() ]
			]);
		}
	}

	/**
	 * Search pixabay Videos
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return ResponseInterface
	 * @throws GuzzleException
	 */
	public function searchVideos( RequestInterface $request, $view ) {
		$options = $this->getPixabayQueryParams( $request );
		try {
			$result = AssetsAPIService::searchAssets( 'videos', $options );
			return \Depicter::json( $result );

		} catch ( ConnectException $exception ) {
			return \Depicter::json([
				'errors' => [ 'Connection error ..', $exception->getMessage() ]
			])->withStatus(503);
		} catch ( \Exception  $exception ) {
			return \Depicter::json([
				'errors' => [ $exception->getMessage() ]
			]);
		}
	}

	/**
	 * Query Pixabay vectors
	 *
	 * @param Request $request
	 * @param string  $view
	 *
	 * @return ResponseInterface
	 * @throws GuzzleException
	 */
	public function searchVectors( Request $request, $view ) {

		$options = $this->getPixabayQueryParams( $request );
		try {
			$result = AssetsAPIService::searchAssets( 'vectors', $options );
			return \Depicter::json( $result );

		} catch ( ConnectException $exception ) {
			return \Depicter::json([
				'errors' => [ 'Connection error ..', $exception->getMessage() ]
			])->withStatus(503);
		} catch ( \Exception  $exception ) {
			return \Depicter::json([
				'errors' => [ $exception->getMessage() ]
			]);
		}

	}

	/**
	 * Get sanitized query params
	 *
	 * @param RequestInterface $request
	 *
	 * @return array
	 */
	protected function getPixabayQueryParams( RequestInterface $request ){

		$args = [
			'page' => !empty( $request->query('page') ) ? Sanitize::int( $request->query('page') ) : 1,
			'perpage' => !empty( $request->query('perpage') ) ? Sanitize::int( $request->query('perpage') ) : 20,
			'videoType' => ! empty( $request->query('videoType') ) ? Sanitize::textfield( $request->query('videoType') ) : 'all',
			'minWidth' => ! empty( $request->query('minWidth') ) ? Sanitize::int( $request->query('minWidth') ) : 0,
			'minHeight' => ! empty( $request->query('minHeight') ) ? Sanitize::int( $request->query('minHeight') ) : 0,
			'editorsChoice' => ! empty( $request->query('editorsChoice') ) ? Sanitize::textfield( $request->query('editorsChoice') ) : 'false',
			'safe' => ! empty( $request->query('safe') ) ? Sanitize::textfield( $request->query('safe') ) : 'false',
			'order' => ! empty( $request->query('orderBy') ) ? Sanitize::textfield( $request->query('orderBy') ) : 'popular',
		];

		if( ! empty( $request->query('s') ) ){
			$args['s'] = Sanitize::textfield( $request->query('s') );
		}

		if( ! empty( $request->query('category') ) ){
			$args['category'] = Sanitize::textfield( $request->query('category') );
		}

		return $args;
	}
}
