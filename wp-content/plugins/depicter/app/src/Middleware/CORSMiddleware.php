<?php
namespace Depicter\Middleware;

use WPEmerge\Requests\RequestInterface;
use WPEmerge\Responses\ResponseService;


class CORSMiddleware
{

	/**
	 * Response service.
	 *
	 * @var ResponseService
	 */
	protected $responseService = null;

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 * @param ResponseService $responseService
	 */
	public function __construct( ResponseService $responseService ) {
		$this->responseService = $responseService;
	}

	/**
	 * @param RequestInterface $request
	 * @param                  $next
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function handle( RequestInterface $request, $next ) {

    	//return $this->responseService->json( $result )->withHeader('Cache-Control', 'max-age=' . $expiration );

    	$response = $next( $request );

		$response->withHeader('Access-Control-Allow-Origin' , '*');
		$response->withHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
		$response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application');

		return $response;
    }

}
