<?php
namespace Depicter\WordPress;

use Depicter\Dynamic\ContentTypes\Post;
use Depicter\Dynamic\ContentTypes\Product;
use Depicter\Dynamic\ContentTypes\Types;
use Pimple\Container;
use WPEmerge\ServiceProviders\ServiceProviderInterface;

class RestApiServiceProvider implements ServiceProviderInterface
{

	/**
	 * Register all dependencies in the IoC container.
	 *
	 * @param Container $container
	 *
	 * @return void
	 */
	public function register($container)
	{
		$container[ Types::class ] = function () {
			return new Types();
		};

		$container[ Post::class ] = function () {
			return new Post();
		};

		$container[ Product::class ] = function () {
			return new Product();
		};
	}

	/**
	 * Bootstrap any services if needed.
	 *
	 * @param Container $container
	 *
	 * @return void
	 */
	public function bootstrap($container)
	{
		add_action( 'rest_api_init', [ $this, 'registerRoutes' ]);
	}

	/**
	 * Register routes
	 *
	 */
	public function registerRoutes()
	{
		register_rest_route( 'depicter/v1', '/dynamic/content-types', [
			'methods'  => 'GET',
			'callback' => \Depicter::closure()->method( Types::class, 'index' ),
			'permission_callback' => '__return_true'
		]);

		register_rest_route( 'depicter/v1', '/dynamic/content-types/post', [
			'methods'  => 'GET',
			'callback' => \Depicter::closure()->method( Post::class, 'index' ),
			'permission_callback' => '__return_true'
		]);

		register_rest_route( 'depicter/v1', '/dynamic/content-types/product', [
			'methods'  => 'GET',
			'callback' => \Depicter::closure()->method( Product::class, 'index' ),
			'permission_callback' => '__return_true'
		]);
	}

}
