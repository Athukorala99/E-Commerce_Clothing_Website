<?php
namespace Depicter\Middleware;


use Averta\WordPress\Utility\JSON;
use Psr\Http\Message\ResponseInterface;
use WPEmerge\Requests\RequestInterface;
use WPEmerge\Responses\ResponseService;

class CacheMiddleware
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
	 * @param int              $expiration     Expiration time in seconds
	 * @param string           $module
	 * @param string           $allowedMethod  pass 'ANY or ALL or *' for all methods
	 *
	 * @return ResponseInterface
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function handle( RequestInterface $request, $next, $expiration = 60, $module = 'api', $allowedMethod = '*' ) {

    	// only continue for allowed methods
    	if( ! in_array( $allowedMethod, [ $request->getMethod(), 'ANY', 'ALL', '*' ] ) ){
    		return $next( $request );
		}

    	// Append Cache_control and Access-Control to response ( $next( $request ) )
    	if( $module === 'browser' ){
			$response = $next( $request );

    		// To flush cache we dont set cache-control and let WordPress send nocache headers
            if( $this->hasForceFlush( $request ) ){
                return $response;
            }

			$responseBody = $response->getBody()->getContents();
			if ( JSON::isJson( $responseBody ) ) {
				$responseArray = JSON::decode( $responseBody, true );
				if ( isset( $responseArray['errors'] ) ) {
					return $response;
				}
			}

		    // disable WordPress nocache header for this request
		    add_filter( 'nocache_headers', 'depicter_disable_nocache_headers' );

		    return $response->withHeader( 'Cache-Control', 'max-age=' . $expiration )
		                            ->withHeader( 'Access-Control-Allow-Origin' , '*' );
	    }

    	$cache = \Depicter::cache( $module );
    	$key   = $cache->hashRequest( $request );

    	// flush cache
    	if( $this->hasForceFlush( $request ) ){
    		$cache->delete( $key );

    	// read and return cache if exists
		} elseif ( $cache->has( $key ) ) {

    		// disable WordPress nocache header for this request
            add_filter( 'nocache_headers', 'depicter_disable_nocache_headers' );

			$result   = $cache->get( $key );
			$response = ( is_array( $result ) || JSON::isJson( $result ) ) ? $this->responseService->json( $result ) : $this->responseService->output( $result );

			return $response->withHeader( 'Cache-Control', 'max-age=' . $expiration )
			                ->withHeader( 'Access-Control-Allow-Origin' , '*' );
		}

        return $next( $request );
    }

	/**
     * Whether to flush cache or not
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    protected function hasForceFlush( RequestInterface $request ){
		return $request->query('flush') == 'true';
	}
}
