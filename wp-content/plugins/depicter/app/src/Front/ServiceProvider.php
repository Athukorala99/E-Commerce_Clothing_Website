<?php
namespace Depicter\Front;


use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * initialize common services
 */
class ServiceProvider implements ServiceProviderInterface
{

	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		$app = $container[ WPEMERGE_APPLICATION_KEY ];

		$container[ 'depicter.front.manager' ] = function () {
			return new Front();
		};
		$app->alias( 'front', 'depicter.front.manager' );

		$container[ 'depicter.front.document.assets' ] = function () {
			return new Assets();
		};
		$container[ 'depicter.front.document.preview' ] = function () {
			return new Preview();
		};

		$container[ 'depicter.front.document.render' ] = function () {
			return new Render();
		};

		$container[ 'depicter.front.symbols' ] = function () {
			return new Symbols();
		};
		$app->alias( 'symbolsProvider', 'depicter.front.symbols' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		\Depicter::front()->bootstrap();
	}

}
