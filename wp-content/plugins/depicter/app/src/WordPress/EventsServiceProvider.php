<?php
namespace Depicter\WordPress;


use Averta\WordPress\Event\Action;
use Averta\WordPress\Event\Filter;
use WPEmerge\ServiceProviders\ServiceProviderInterface;

class EventsServiceProvider implements ServiceProviderInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		$app = $container[ WPEMERGE_APPLICATION_KEY ];

		// register hook event managers
		$container[ 'depicter.events.action' ] = function () {
			return new Action();
		};

		$app->alias( 'action', 'depicter.events.action' );

		// register hook event managers
		$container[ 'depicter.events.filter' ] = function () {
			return new Filter();
		};

		$app->alias( 'filter', 'depicter.events.filter' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {

	}

}
