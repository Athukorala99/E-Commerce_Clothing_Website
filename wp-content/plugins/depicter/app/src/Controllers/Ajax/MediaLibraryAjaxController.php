<?php
namespace Depicter\Controllers\Ajax;


use Depicter\Services\MediaLibraryService;
use Depicter\Utility\Sanitize;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class MediaLibraryAjaxController
{

	/**
	 * @var MediaLibraryService
	 */
	private $mediaLibraryService;


	public function __construct()
	{
		$this->mediaLibraryService = \Depicter::app()->mediaLibrary();
	}

	/**
	 * Get search result of images from media library
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 */
	public function images( RequestInterface $request, $view )
	{
		return $this->query( $request, $view, 'photos');
	}

	/**
	 * Get search result of videos from media library
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 */
	public function videos( RequestInterface $request, $view )
	{
		return $this->query( $request, $view, 'video');
	}

	/**
	 * Get search result of audios from media library
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 */
	public function audios( RequestInterface $request, $view )
	{
		return $this->query( $request, $view, 'audio');
	}

	/**
	 * Get search result of vectors from media library
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 */
	public function vectors( RequestInterface $request, $view )
	{
		return $this->query( $request, $view, 'vector');
	}

	/**
	 * Get media results from media library
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @param string           $assetType
	 *
	 * @return ResponseInterface
	 */
	public function query( RequestInterface $request, $view, $assetType = 'all' )
	{
		// sanitize incoming params
		$perPage = ! empty( $request->query( 'perpage' ) ) ? Sanitize::int( $request->query('perpage') ) : 20;
		$page    = ! empty( $request->query( 'page'    ) ) ? Sanitize::int( $request->query('page'   ) ) : 1;
		$search  = ! empty( $request->query( 's'       ) ) ? Sanitize::textfield( $request->query('s' ) ) : '';

		$queryParams = [
			'post_type'         => 'attachment',
			'post_status'       => 'inherit',
			'posts_per_page'    => $perPage,
			'paged'             => $page
		];

		// add search term to query params if was defined
		if( ! empty( $search ) ){
			$queryParams['s'] = $search;
		}

		// change "images" to "image" for accurate mime_type
		$assetType = rtrim( $assetType, 's' );

		if( $mimTypes = $this->mediaLibraryService->getSupportedMimeTypes( $assetType ) ){
			$queryParams['post_mime_type'] = $mimTypes;
		}

		$attachments = $this->mediaLibraryService->query( $queryParams );
		$result = $this->mediaLibraryService->getQueryOutput( $attachments, $assetType );
		if( empty( $result ) ){
			return \Depicter::json(['errors' => ['Search not found.'] ])->withStatus(404);
		}

		$totalPages = $result['totalPages'];
		unset( $result['totalPages'] );

		$total = $result['total'];
		unset( $result['total'] );

		return \Depicter::json( $result )
			->withHeader('X-Total-Pages', $totalPages )
			->withHeader('X-Total', $total )
			->withStatus(200 );
	}

}
