<?php
namespace Depicter\Middleware;

use Averta\WordPress\Utility\Sanitize;
use Closure;
use Psr\Http\Message\ResponseInterface;
use WPEmerge\Csrf\Csrf;
use WPEmerge\Csrf\CsrfMiddleware as WPEmergeCsrfMiddleware;
use WPEmerge\Requests\RequestInterface;
use WPEmerge\Responses\ResponseService;

class CsrfAPIMiddleware extends WPEmergeCsrfMiddleware
{
	/**
	 * CSRF service.
	 *
	 * @var Csrf
	 */
	protected $csrf = null;

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
	 *
	 * @param ResponseService $responseService
	 * @param Csrf            $csrf
	 */
	public function __construct( ResponseService $responseService, Csrf $csrf ) {
		$this->responseService = $responseService;
		$this->csrf = $csrf;
	}

	/**
	 * Reject requests that fail nonce validation.
	 *
	 * @param RequestInterface $request
	 * @param Closure          $next
	 * @param mixed            $action
	 * @param bool             $byLoggedInUser  Whether to check if request is by logged-in user or not
	 *
	 * @return ResponseInterface
	 */
	public function handle( RequestInterface $request, Closure $next, $action = -1, bool $byLoggedInUser = true ) {

		$isValidToken = false;

		// Get token form request
		if( ! $token = $this->csrf->getTokenFromRequest( $request ) ){
			$token = Sanitize::key( $request->getHeaderLine( 'X-DEPICTER-CSRF' ) );
		}

		// use WordPress Auth Key for CSRF authentication on depicter debug mode
		if( defined( 'DEPICTER_DEBUG' ) && DEPICTER_DEBUG &&
		    defined( 'AUTH_KEY' ) && ( AUTH_KEY === $token )
		){
			$isValidToken = true;

		} else {
			// Check if it's a logged-in user
			if( $byLoggedInUser && ! is_user_logged_in() ){
				return $this->responseService->json([
					'errors' => 'Not an authorized user.'
				])->withStatus(401);
			}

			$actions = explode('|', $action );
			// Authenticate request token
			foreach( $actions as $csrfAction ){
				$isValidToken = $isValidToken || $this->csrf->isValidToken( $token, $csrfAction );
			}
		}

		// Terminate cycle on authentication failure
		if ( ! $isValidToken ) {
			return $this->responseService->json([
				'errors' => ['Session expired, please login again.']
			])->withStatus(401);
		}

		return $next( $request );
	}
}
