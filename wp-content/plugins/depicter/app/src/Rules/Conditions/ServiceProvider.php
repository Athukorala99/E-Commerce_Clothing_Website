<?php
namespace Depicter\Rules\Conditions;

use Depicter\Rules\Conditions\woocommerce\Category as ProductCategory;
use Depicter\Rules\Conditions\woocommerce\Shop;
use Depicter\Rules\Conditions\woocommerce\Tag as ProductTag;
use \Depicter\Rules\Conditions\woocommerce\Singular as Product;
use Depicter\Rules\Conditions\wp\Archive;
use Depicter\Rules\Conditions\wp\ConditionsValues;
use \Depicter\Rules\Conditions\woocommerce\ConditionsValues as WooConditionsValues;
use Depicter\Rules\Conditions\wp\General;
use Depicter\Rules\Conditions\wp\Singular;
use Depicter\Rules\Conditions\wp\Taxonomy;
use WPEmerge\ServiceProviders\ServiceProviderInterface;
use const WPEMERGE_APPLICATION_KEY;

class ServiceProvider implements ServiceProviderInterface
{

    /**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		$app = $container[ WPEMERGE_APPLICATION_KEY ];

        // register conditions manager
		$container[ 'depicter.conditions.manager' ] = function () {
			return new Manager();
		};
		$app->alias( 'conditionsManager', 'depicter.conditions.manager' );

        // register archive condition
		$container[ 'depicter.conditions.wp-archive' ] = function () {
			return new Archive();
		};

        // register general condition
		$container[ 'depicter.conditions.wp-general' ] = function () {
			return new General();
		};

        // register singular condition
		$container[ 'depicter.conditions.wp-singular' ] = function () {
			return new Singular();
		};

		// register taxonomy condition
		$container[ 'depicter.conditions.wp-taxonomy' ] = function () {
			return new Taxonomy();
		};

		// register woocommerce shop condition
		$container[ 'depicter.conditions.wc-shop' ] = function () {
			return new Shop();
		};

		// register woocommerce shop condition
		$container[ 'depicter.conditions.wc-category' ] = function () {
			return new ProductCategory();
		};

		// register woocommerce shop condition
		$container[ 'depicter.conditions.wc-tag' ] = function () {
			return new ProductTag();
		};

		// register woocommerce shop condition
		$container[ 'depicter.conditions.wc-singular' ] = function () {
			return new Product();
		};

		// register woocommerce shop condition
		$container[ 'depicter.conditions.list' ] = function () {
			return new ListConditions();
		};

		// register woocommerce shop condition
		$container[ 'depicter.conditions.wp.query.values' ] = function () {
			return new ConditionsValues();
		};

		// register woocommerce shop condition
		$container[ 'depicter.conditions.wc.query.values' ] = function () {
			return new WooConditionsValues();
		};

    }

    /**
	 * {@inheritDoc}
	 */
    public function bootstrap($container)
    {

    }
}
