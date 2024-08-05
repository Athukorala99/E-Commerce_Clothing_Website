<?php
namespace Depicter\WordPress;

use Depicter\Services\ClientService;
use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Register admin-related entities and hooks, like admin menu pages.
 */
class AdminServiceProvider implements ServiceProviderInterface {

	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		$app = $container[ WPEMERGE_APPLICATION_KEY ];

		$container['depicter.system.check'] = function() {
			return new SystemCheckService();
		};

		// register deactivation feedback
		$container[ 'depicter.deactivation.feedback' ] = function () {
			return new DeactivationFeedbackService();
		};
		$app->alias( 'deactivationFeedback', 'depicter.deactivation.feedback' );

		// register client service
		$container['depicter.services.client.api'] = function() {
			return new ClientService();
		};
		$app->alias( 'client', 'depicter.services.client.api' );

		// register wp file upload service
		$container['depicter.services.file.uploader'] = function() {
			return new FileUploaderService();
		};
		$app->alias( 'fileUploader', 'depicter.services.file.uploader' );

		// register wp scheduling service
		$container['depicter.services.schedule'] = function() {
			return new SchedulingService();
		};
		$app->alias( 'schedule', 'depicter.services.schedule' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {

		if ( is_admin() ){

			// Only executes in admin pages
			if( ! ( defined('DOING_AJAX') && DOING_AJAX ) ){
				\Depicter::resolve('depicter.deactivation.feedback');
				\Depicter::resolve('depicter.auto.update.check' );
				\Depicter::resolve('depicter.system.check');

				\Depicter::client()->authorize();
			}
		}

		\Depicter::schedule()->hooks();
	}


}
