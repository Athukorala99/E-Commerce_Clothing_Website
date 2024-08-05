<?php
namespace Depicter\Middleware;


use WPEmerge\Requests\RequestInterface;
use WPEmerge\Responses\ResponseService;

class CapabilityMiddleware {

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
	 * @param string           $capability
	 *
	 * @return mixed|ResponseService
	 */
	public function handle( RequestInterface $request, $next, string $capability = 'manage_options' ){
		// ignore the request if the current user doesn't have sufficient permissions
		if ( ! current_user_can( $capability ) ) {
			return $this->responseService->json([
				'errors' => [ __( "Sorry, insufficient permission!", 'depicter' ) ]
			])->withStatus(403);
		}

		return $next( $request );
	}
}
