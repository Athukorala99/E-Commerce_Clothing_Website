<?php
/**
 * Configuration class.
 *
 * 
 * @package    depicter
 * @license    LICENSE.txt
 * @author     averta
 * @link       https://depicter.com/
 */


if( ! defined( 'DEPICTER_REVISIONS' ) ){
	define( 'DEPICTER_REVISIONS', 15 );
}

return [
	/**
	 * Array of service providers you wish to enable.
	 */
	'providers'           => [
		\WPEmergeAppCore\AppCore\AppCoreServiceProvider::class,
		\WPEmergeAppCore\Assets\AssetsServiceProvider::class,
		\WPEmergeAppCore\Avatar\AvatarServiceProvider::class,
		\WPEmergeAppCore\Config\ConfigServiceProvider::class,
		\WPEmergeAppCore\Image\ImageServiceProvider::class,
		\WPEmergeAppCore\Sidebar\SidebarServiceProvider::class,
		\Depicter\Security\SecurityServiceProvider::class,
		\Depicter\Middleware\MiddlewareServiceProvider::class,
		\Depicter\Services\ServiceProvider::class,
		\Depicter\Routing\RouteConditionsServiceProvider::class,
		\Depicter\DataSources\ServiceProvider::class,
		\Depicter\Document\ServiceProvider::class,
		\Depicter\Database\DatabaseServiceProvider::class,
		\Depicter\Editor\EditorServiceProvider::class,
		\Depicter\Dashboard\DashboardServiceProvider::class,
		\Depicter\Front\ServiceProvider::class,
		\Depicter\View\ViewServiceProvider::class,
		\Depicter\WordPress\RestApiServiceProvider::class,
		\Depicter\WordPress\AdminServiceProvider::class,
		\Depicter\WordPress\AssetsServiceProvider::class,
		\Depicter\WordPress\EventsServiceProvider::class,
		\Depicter\WordPress\ShortcodesServiceProvider::class,
		\Depicter\WordPress\PluginServiceProvider::class,
		\Depicter\WordPress\WidgetsServiceProvider::class,
		\Depicter\WordPress\SVGServiceProvider::class,
		\Depicter\WordPress\PermissionsServiceProvider::class,
		\Depicter\WordPress\WPCronServiceProvider::class,
		\Depicter\Modules\ModulesServiceProvider::class,
		\Depicter\Rules\Conditions\ServiceProvider::class
	],

	/**
	 * Array of route group definitions and default attributes.
	 * All of these are optional so if we are not using
	 * a certain group of routes we can skip it.
	 * If we are not using routing at all we can skip
	 * the entire 'routes' option.
	 */
	'routes'              => [
		'web'   => [
			'definitions' => __DIR__ . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php',
			'attributes'  => [
				'namespace' => 'Depicter\\Controllers\\Web\\',
			],
		],
		'admin' => [
			'definitions' => __DIR__ . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'admin.php',
			'attributes'  => [
				'namespace' => 'Depicter\\Controllers\\Admin\\',
			],
		],
		'ajax'  => [
			'definitions' => __DIR__ . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'ajax.php',
			'attributes'  => [
				'namespace' => 'Depicter\\Controllers\\Ajax\\',
			],
		],
	],

	/**
	 * View Composers settings.
	 */
	'view_composers'      => [
		'namespace' => 'Depicter\\ViewComposers\\',
	],

	/**
	 * Register middleware class aliases.
	 * Use fully qualified middleware class names.
	 *
	 * Internal aliases that you should avoid overriding:
	 * - 'flash'
	 * - 'old_input'
	 * - 'csrf'
	 * - 'user.logged_in'
	 * - 'user.logged_out'
	 * - 'user.can'
	 */
	'middleware'          => [
		'cache'    => \Depicter\Middleware\CacheMiddleware::class,
		'cors'     => \Depicter\Middleware\CORSMiddleware::class,
		'csrf-api' => \Depicter\Middleware\CsrfAPIMiddleware::class,
		'nonce'    => \Depicter\Middleware\NonceFieldMiddleware::class,
		'userCan'  => \Depicter\Middleware\CapabilityMiddleware::class
	],

	/**
	 * Register middleware groups.
	 * Use fully qualified middleware class names or registered aliases.
	 * There are a couple built-in groups that you may override:
	 * - 'web'      - Automatically applied to web routes.
	 * - 'admin'    - Automatically applied to admin routes.
	 * - 'ajax'     - Automatically applied to ajax routes.
	 * - 'global'   - Automatically applied to all of the above.
	 * - 'wpemerge' - Internal group applied the same way 'global' is.
	 *
	 * Warning: The 'wpemerge' group contains some internal WP Emerge
	 * middleware which you should avoid overriding.
	 */
	'middleware_groups'   => [
		'global' => [],
		'web'    => [],
		'ajax'   => [], //['csrf:_masterCsrfToken'],
		'admin'  => [],
	],

	/**
	 * Optionally specify middleware execution order.
	 * Use fully qualified middleware class names.
	 */
	'middleware_priority' => [
		// phpcs:ignore
		// \Depicter\Middleware\MyMiddlewareThatShouldRunFirst::class,
		// \Depicter\Middleware\MyMiddlewareThatShouldRunSecond::class,
	],

	/**
	 * Custom directories to search for views.
	 * Use absolute paths or leave blank to disable.
	 * Applies only to the default PhpViewEngine.
	 */
	'views'               => [ dirname( __DIR__ ) . DIRECTORY_SEPARATOR . 'views' ],

	/**
	 * App Core configuration.
	 */
	'app_core'            => [
		'path' => dirname( __DIR__ ),
		'url'  => plugin_dir_url( DEPICTER_PLUGIN_FILE ),
	],

	/**
     * Debug settings.
     */
    'debug' => [
        // Enable debug mode. Defaults to the value of WP_DEBUG.
        'enable' => true,
        // Enable the use of filp/whoops for an enhanced error interface. Defaults to true.
        'pretty_errors' => false
    ],

	/**
	 * Other config goes after this comment.
	 */

];
