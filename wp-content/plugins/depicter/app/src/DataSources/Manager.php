<?php
namespace Depicter\DataSources;

use Depicter\DataSources\Tags\Manager as TagsManager;

/**
 * Chained api to access all data sources
 */
class Manager {

	/**
	 * Returns the instance of posts class
	 *
	 * @return Posts
	 */
	public function posts(): Posts {
		return \Depicter::resolve('depicter.dataSources.posts');
	}

	/**
	 * Returns the instance of products class
	 *
	 * @return Products
	 */
	public function products(): Products {
		return \Depicter::resolve('depicter.dataSources.products');
	}

	/**
	 * Returns the instance of handpicked products class
	 *
	 * @return HandPickedProducts
	 */
	public function handPickedProducts(): HandPickedProducts {
		return \Depicter::resolve('depicter.dataSources.handPickedProducts');
	}

	/**
	 * Returns the instance of tags Manager class
	 *
	 * @return TagsManager
	 */
	public function tagsManager(): TagsManager {
		return \Depicter::resolve('depicter.dataSources.tags.manager');
	}

	/**
	 * Get DataSource instance by type
	 *
	 * @param string $type
	 *
	 * @return DataSourceInterface
	 */
	public function getByType( string $type ){
		$result = $this->posts();

		switch ( $type ) {

			case 'wpPost':
			case 'wpPostHandpicked':
			case 'wpPage':
			case 'wpPageHandpicked':
			case 'wpCPT':
				$result = $this->posts();
				break;

			case 'wooProduct':
			case 'wooProducts':
				$result = $this->products();
				break;

			case 'wooHandpicked':
				$result = $this->handPickedProducts();
				break;

			case 'catalogs':
				$result = \Depicter::resolve('depicter.dataSources.catalogs');
				break;

			default:
				break;
		}

		return $result;
	}

	/**
	 * Get post type by DataSource type
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function getPostTypeByType( string $type ){
		$result = '';

		switch ( $type ) {

			case 'wpPost':
			case 'wpPostHandpicked':
			case 'wpCPT':
				$result = 'post';
				break;

			case 'wpPage':
			case 'wpPageHandpicked':
				$result = 'page';
				break;

			case 'wooProduct':
			case 'wooProducts':
			case 'wooHandpicked':
				$result = 'product';
				break;

			default:
				break;
		}

		return $result;
	}

}
