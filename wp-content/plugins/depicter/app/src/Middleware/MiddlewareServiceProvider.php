<?php
namespace Depicter\Middleware;


use Depicter\Security\CSRF;
use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Provide middleware dependencies.
 *
 * @codeCoverageIgnore
 */
class MiddlewareServiceProvider implements ServiceProviderInterface {
	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		$container[ CacheMiddleware::class ] = function ( $c ) {
			return new CacheMiddleware( $c[ WPEMERGE_RESPONSE_SERVICE_KEY ] );
		};

		$container[ CORSMiddleware::class ] = function ( $c ) {
			return new CORSMiddleware( $c[ WPEMERGE_RESPONSE_SERVICE_KEY ] );
		};

		$container[ CsrfAPIMiddleware::class ] = function ( $c ) {
			return new CsrfAPIMiddleware( $c[ WPEMERGE_RESPONSE_SERVICE_KEY ], $c[ CSRF::class ] );
		};

		$container[ NonceFieldMiddleware::class ] = function ( $c ) {
			return new NonceFieldMiddleware( $c[ WPEMERGE_RESPONSE_SERVICE_KEY ] );
		};

		$container[ CapabilityMiddleware::class ] = function ( $c ) {
			return new CapabilityMiddleware( $c[ WPEMERGE_RESPONSE_SERVICE_KEY ] );
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		// Nothing to bootstrap.
	}
}
