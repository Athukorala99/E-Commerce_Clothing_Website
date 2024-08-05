<?php
namespace Depicter\Document;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Load document data manager.
 */
class ServiceProvider implements ServiceProviderInterface {

	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		$app = $container[ WPEMERGE_APPLICATION_KEY ];

		$container[ 'depicter.document.mapper' ] = function () {
			return new Mapper();
		};
		$container[ 'depicter.document.manager' ] = function () {
			return new Manager();
		};

		$app->alias( 'document', 'depicter.document.manager' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		\Depicter::document()->bootstrap();
	}

}
