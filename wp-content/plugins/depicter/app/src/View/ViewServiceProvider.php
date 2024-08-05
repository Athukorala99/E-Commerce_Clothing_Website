<?php
namespace Depicter\View;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Register view composers and globals.
 * This is an example class so feel free to modify or remove it.
 */
class ViewServiceProvider implements ServiceProviderInterface {
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
		$this->registerGlobals();
		$this->registerComposers();
	}

	/**
	 * Register view globals.
	 *
	 * @return void
	 */
	protected function registerGlobals() {

	}

	/**
	 * Register view composers.
	 *
	 * @link https://docs.wpemerge.com/#/framework/views/view-composers
	 *
	 * @return void
	 */
	protected function registerComposers() {
	}
}
