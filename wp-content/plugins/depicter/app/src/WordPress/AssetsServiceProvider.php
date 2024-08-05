<?php
namespace Depicter\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Register and enqueues assets.
 */
class AssetsServiceProvider implements ServiceProviderInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		// Nothing to register.
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		add_action( 'admin_enqueue_scripts', [$this, 'enqueueAdminAssets'] );
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @return void
	 */
	public function enqueueAdminAssets() {
		// Enqueue styles.
		$style = \Depicter::core()->assets()->getUrl() . '/resources/styles/admin/admin.css';

		if ( $style ) {
			\Depicter::core()->assets()->enqueueStyle(
				'depicter-admin-css-bundle',
				$style
			);
		}
	}

}
