<?php
namespace Depicter\Dashboard;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Loads dashboard.
 */
class DashboardServiceProvider implements ServiceProviderInterface {

	/**
	 * {@inheritDoc}
	 */
	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		$container[ 'depicter.dashboard.page' ] = function () {
			return new DashboardPage();
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		\Depicter::resolve('depicter.dashboard.page')->bootstrap();
	}

}
