<?php
namespace Depicter\Services;

use Averta\WordPress\Cache\DatabaseCache;
use Averta\WordPress\Cache\WPCache;
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

		// register Cache modules
		$container[ 'depicter.services.cache.base' ] = function () {
			return new WPCache('depicter__');
		};

		$container[ 'depicter.services.cache.api' ] = function () {
			return new WPCache('depicter_api_');
		};

		$container[ 'depicter.services.cache.document' ] = function () {
			return new DatabaseCache('depicter_doc_id_');
		};
		// persistent database cache module
		$container[ 'depicter.services.cache.database' ] = function () {
			return new DatabaseCache('depicter_d_');
		};

		// register cache alias for retrieving a cache module
		$app->alias( 'cache', function () use ( $app ) {
			$module = !empty( func_get_args()['0'] ) ? strtolower( func_get_args()['0'] ) : 'api';
			return $app->resolve( 'depicter.services.cache.' . $module );
		});

		$container[ 'depicter.media.library' ] = function () {
			return new MediaLibraryService();
		};
		$app->alias( 'mediaLibrary', 'depicter.media.library' );

		$container[ 'depicter.media.bridge' ] = function () {
			return new MediaBridge();
		};
		$app->alias( 'media', 'depicter.media.bridge' );

		$container[ 'depicter.services.document.fonts' ] = function () {
			return new DocumentFontsV1Service();
		};
		$app->alias( 'documentFonts', 'depicter.services.document.fonts' );

		$container[ 'depicter.services.google.fonts' ] = function () {
			return new GoogleFontsService();
		};
		$app->alias( 'googleFontsService', 'depicter.services.google.fonts' );

		$container[ 'depicter.services.remote.api' ] = function () {
			return new RemoteAPIService();
		};
		$app->alias( 'remote', 'depicter.services.remote.api' );

		$container[ 'depicter.services.storage.disk' ] = function () {
			return new StorageService();
		};
		$app->alias( 'storage', 'depicter.services.storage.disk' );

		$container[ 'depicter.export.service' ] = function () {
			return new ExportService();
		};
		$app->alias( 'exportService', 'depicter.export.service' );

		$container[ 'depicter.import.service' ] = function () {
			return new ImportService();
		};
		$app->alias( 'importService', 'depicter.import.service' );

		$container[ 'depicter.security.authorization' ] = function () {
			return new AuthorizationService();
		};
		$app->alias( 'authorization', 'depicter.security.authorization' );

		$container[ 'depicter.security.authentication' ] = function () {
			return new AuthenticationService();
		};
		$app->alias( 'auth', 'depicter.security.authentication' );

		$container[ 'depicter.ai.wizard.service' ] = function () {
			return new AIWizardService();
		};
		$app->alias( 'AIWizard', 'depicter.ai.wizard.service' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		if ( is_admin() ) {
			\Depicter::resolve('depicter.services.deactivation.feedback');
		}
	}

}
