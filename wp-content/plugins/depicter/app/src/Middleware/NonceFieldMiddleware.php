<?php
namespace Depicter\Middleware;


use WPEmerge\Requests\RequestInterface;
use WPEmerge\Responses\ResponseService;

class NonceFieldMiddleware
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
	 * @param string           $action
	 * @param string           $nonce
	 * @param string           $method
	 *
	 * @return mixed|ResponseService
	 */
	public function handle( RequestInterface $request, $next, string $action = 'depicter-nonce', string $nonce = '_wpnonce', string $method = 'post' ){
		$nonce = $method == 'post' ? $request->body($nonce ) : $request->query($nonce);
		if ( empty($nonce) || ! wp_verify_nonce( $nonce, $action ) ) {
			return $this->responseService->json([
				'errors' => ['Nonce is invalid']
			]);
		}

		return $next( $request );
	}
}
