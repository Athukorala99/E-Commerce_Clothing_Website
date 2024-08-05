<?php
namespace Depicter\DataSources;

use Depicter\DataSources\Tags\ACF;
use Depicter\DataSources\Tags\Catalog;
use Depicter\DataSources\Tags\Legacy;
use Depicter\DataSources\Tags\Post;
use Depicter\DataSources\Tags\MetaBoxIO;
use Depicter\DataSources\Tags\MetaFields;
use Depicter\DataSources\Tags\Product;
use Depicter\DataSources\Tags\Taxonomy;
use WPEmerge\ServiceProviders\ServiceProviderInterface;


class ServiceProvider implements ServiceProviderInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		$app = $container[ WPEMERGE_APPLICATION_KEY ];

		// register dataSource manager
		$container[ 'depicter.dataSources.dataSource' ] = function () {
			return new Manager();
		};
		$app->alias( 'dataSource', 'depicter.dataSources.dataSource' );


		// register posts dataSource
		$container[ 'depicter.dataSources.posts' ] = function () {
			return new Posts();
		};

		// register products dataSource
		$container[ 'depicter.dataSources.products' ] = function () {
			return new Products();
		};

		// register handpicked products dataSource
		$container[ 'depicter.dataSources.handPickedProducts' ] = function () {
			return new HandPickedProducts();
		};

		// register catalog dataSource
		$container[ 'depicter.dataSources.catalogs' ] = function () {
			return new Catalogs();
		};

		// dynamic tag modules
		$container[ 'depicter.dataSources.tags.manager' ] = function () {
			return new \Depicter\DataSources\Tags\Manager();
		};

		$container[ 'depicter.dataSources.tags.legacy' ] = function () {
			return new Legacy();
		};

		$container[ 'depicter.dataSources.tags.post' ] = function () {
			return new Post();
		};

		$container[ 'depicter.dataSources.tags.taxonomy' ] = function () {
			return new Taxonomy();
		};

		$container[ 'depicter.dataSources.tags.product' ] = function () {
			return new Product();
		};

		$container[ 'depicter.dataSources.tags.acf' ] = function () {
			return new ACF();
		};

		$container[ 'depicter.dataSources.tags.metaboxio' ] = function () {
			return new MetaBoxIO();
		};

		$container[ 'depicter.dataSources.tags.metafield' ] = function () {
			return new MetaFields();
		};

		$container[ 'depicter.dataSources.tags.catalog' ] = function () {
			return new Catalog();
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {}
}
