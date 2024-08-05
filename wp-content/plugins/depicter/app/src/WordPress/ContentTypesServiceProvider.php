<?php

namespace Depicter\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Register widgets and sidebars.
 */
class ContentTypesServiceProvider implements ServiceProviderInterface
{

	/**
	 * post-type slug.
	 */
	const CPT = 'depicter';

	/**
	 * Post type object.
	 *
	 * @access private
	 *
	 * @var \WP_Post_Type
	 */
	private $post_type_object;

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
		add_action( 'init', [ $this, 'registerPostTypes' ] );
		add_action( 'init', [ $this, 'registerTaxonomies'] );
	}

	/**
	 * Register post types.
	 *
	 * @return void
	 */
	public function registerPostTypes() {

		$this->post_type_object = register_post_type(
			'depicter',
			array(
				'labels'              => array(
					'name'               => __( 'Projects', 'depicter' ),
					'singular_name'      => __( 'Project', 'depicter' ),
					'add_new'            => __( 'Add New', 'depicter' ),
					'add_new_item'       => __( 'Add new Project', 'depicter' ),
					'view_item'          => __( 'View Project', 'depicter' ),
					'edit_item'          => __( 'Edit Project', 'depicter' ),
					'new_item'           => __( 'New Project', 'depicter' ),
					'search_items'       => __( 'Search Projects', 'depicter' ),
					'not_found'          => __( 'No Sliders found', 'depicter' ),
					'not_found_in_trash' => __( 'No Sliders found in trash', 'depicter' ),
				),
				'public'              => true,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'query_var'           => true,
				'menu_icon'   		  => 'dashicons-slides',
				'supports'            => [ 'title','editor','thumbnail','page-attributes', 'author', 'revisions' ],
				'rewrite'             => false
			)
		);

	}

	/**
	 * Register taxonomies.
	 *
	 * @return void
	 */
	public function registerTaxonomies() {

		register_taxonomy(
			'depicter-category',
			[ 'depicter' ],
			[
				'labels'            => [
					'name'              => __( 'Categories', 'depicter' ),
					'singular_name'     => __( 'Category', 'depicter' ),
					'search_items'      => __( 'Search Categories', 'depicter' ),
					'all_items'         => __( 'All Categories', 'depicter' ),
					'parent_item'       => __( 'Parent category', 'depicter' ),
					'parent_item_colon' => __( 'Parent category:', 'depicter' ),
					'view_item'         => __( 'View category', 'depicter' ),
					'edit_item'         => __( 'Edit category', 'depicter' ),
					'update_item'       => __( 'Update category', 'depicter' ),
					'add_new_item'      => __( 'Add New category', 'depicter' ),
					'new_item_name'     => __( 'New category Name', 'depicter' ),
					'menu_name'         => __( 'Categories', 'depicter' ),
				],
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => [ 'slug' => 'depicter-category' ]
			]
		);

	}
}
