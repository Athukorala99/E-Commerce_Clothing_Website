<?php

namespace Depicter\Rules\Conditions;

use WP_Post_Type;
use WP_Taxonomy;

class ListConditions
{

	public function wordpressConditions(): array{
		return [
			'label' => __( 'WordPress', 'depicter' ),
			'items' => [
				[
					'id'    => 'wp_post_all',
					'label' => __( 'A WP Post', 'depicter' ),
					'slug'	=> 'post_id',
					'control' => 'remoteMultiSelect',
					'tier'  => 'free-user',
					'controlOptions' => [
						'defaultValue' => ['all'],
						'query' => 'wp:post:all',
					]
				],
				[
					'id' => 'wp_page_all',
					'label' => __( 'A WP Page', 'depicter' ),
					'slug'	=> 'post_id',
					'control' => 'remoteMultiSelect',
					'tier' => 'free-user',
					'controlOptions' => [
						'defaultValue' => ['all'],
						'query' => 'wp:page:all'
					]
				],
				[
					'id' => 'wp_is_single',
					'label' => __( 'Any WP Post', 'depicter' ),
					'slug'	=> 'wp:is_single',
					'description' => __( 'When any single Post is being displayed. This condition is false if you are on a page.', 'depicter' ),
					'control' => 'dropdown',
					'tier' => 'free-user',
					'controlOptions' => [
						'options' => [
							[
								'label' => __( 'Any WP Post', 'depicter' ),
								'value' => true
							]
						],
						'defaultValue' => ['all']
					]
				],
				[
					'id' => 'wp_is_page',
					'label' => __( 'Any WP Page', 'depicter' ),
					'slug'	=> 'wp:is_page',
					'description' => __( 'When any page is being displayed, not a blog post nor a generic page from any other custom post type.', 'depicter' ),
					'control' => 'dropdown',
					'tier' => 'free-user',
					'controlOptions' => [
						'options' => [
							[
								'label' => __( 'Any WP Page', 'depicter' ),
								'value' => true
							]
						],
						'defaultValue' => ['all']
					]
				],
				[
					'id' => 'wp_is_archive',
					'label' => __( 'WP Archive Page', 'depicter' ),
					'slug'	=> 'wp:is_archive',
					'description' => __( 'When any type of Archive page is being displayed. Category, Tag, Author and Date based pages are all types of Archives.', 'depicter' ),
					'control' => 'dropdown',
					'controlOptions' => [
						'options' => [
							[
								'label' => __( 'WP Archive', 'depicter' ),
								'value' => true
							]
						],
						'defaultValue' => ['all']
					]
				],
				[
					'id' => 'wp_static',
					'label' => __( 'WP Static Pages', 'depicter' ),
					'slug'	=> 'wp:static',
					'description' => '',
					'control' => 'multiSelect',
					'controlOptions' => [
						'options' => [
							[
								'label' => __( 'Home Page', 'depicter' ),
								'value' => 'is_home'
							],
							[
								'label' => __( '404 Page', 'depicter' ),
								'value' => 'is_404'
							],
							[
								'label' => __( 'Search Page', 'depicter' ),
								'value' => 'is_search'
							],
							[
								'label' => __( 'Blog Page', 'depicter' ),
								'value' => 'is_blog'
							],
							[
								'label' => __( 'Privacy Policy page', 'depicter' ),
								'value' => 'is_privacy_policy'
							]
						],
						'defaultValue' => ['all']
					]
				],
				[
					'id' => 'wp_category_all',
					'label' => __( 'WP Category Page', 'depicter' ),
					'slug'	=> 'wp:is_category',
					'description' => __( 'When a Category archive page is being displayed.', 'depicter' ),
					'control' => 'remoteMultiSelect',
					'controlOptions' => [
						'defaultValue' => ['all'],
						'query' => 'wp:category:all'
					]
				],
				[
					'id' => 'wp_tag_all',
					'label' => __( 'WP Tag Page', 'depicter' ),
					'slug'	=> 'wp:is_tag',
					'description' => __( 'When any Tag archive page is being displayed.', 'depicter' ),
					'control' => 'remoteMultiSelect',
					'controlOptions' => [
						'defaultValue' => ['all'],
						'query' => 'wp:tag:all'
					]
				],
				[
					'id' => 'wp_category_term_list',
					'label' => __( 'In WP Category', 'depicter' ),
					'slug'	=> 'wp:in_category',
					'description' => __( 'If displayed post is in the specified category.', 'depicter' ),
					'control' => 'remoteMultiSelect',
					'controlOptions' => [
						'defaultValue' => ['all'],
						'query' => 'wp:category:term_list'
					]
				],
				[
					'id' => 'wp_post_tag_term_list',
					'label' => __( 'Has WP Tag', 'depicter' ),
					'slug'	=> 'wp:has_tag',
					'description' => __( 'If displayed post has a specified tag.', 'depicter' ),
					'control' => 'remoteMultiSelect',
					'controlOptions' => [
						'defaultValue' => ['all'],
						'query' => 'wp:post_tag:term_list'
					]
				],
				[
					'id' => 'wp_authors_all',
					'label' => __( 'WP Author Page', 'depicter' ),
					'slug'	=> 'wp:is_author',
					'description' => __( 'When any Author page is being displayed.', 'depicter' ),
					'control' => 'remoteMultiSelect',
					'controlOptions' => [
						'defaultValue' => ['all'],
						'query' => 'wp:authors:all'
					]
				],
			]
		];
	}

	public function customPostTypeConditions(): array{
		/**
		 * @var WP_Post_Type[] $postTypes
		 */
		$postTypes = get_post_types([
			                            'public' => true,
			                            '_builtin' => false
		                            ], 'object');


		$items = [];
		$taxonomyItems = [];

		$singlePageValue = [
			[
				'label' => __( 'All Single Pages', 'depicter' ),
				'value' => 'all'
			]
		];
		$archivePageValue = [];
		$taxonomyPageValue = [
			[
				'label' => __( 'Any', 'depicter' ),
				'value' => ""
			]
		];

		foreach ( $postTypes as $postType ) {

			if ( $postType->name == 'product' ) {
				continue;
			}

			$singlePageValue[] = [
				'label' => sprintf( __( "%s Single Page", 'depicter' ), $postType->labels->singular_name  ),
				'value' => $postType->name
			];

			$items[] = [
				'id' => "wp_" . $postType->name . "_all",
				'label' => sprintf( __( "%s Page", 'depicter' ), $postType->labels->singular_name  ),
				'slug' => "wp:post_id",
				"control" => "remoteMultiSelect",
				'controlOptions' => [
					'defaultValue' => ['all'],
					"query" => "wp:" . $postType->name . ":all"
				],
			];

			$archivePageValue[] = [
				'label' => sprintf( __( '%s Archive', 'depicter' ), $postType->labels->singular_name ),
				'value' => $postType->name
			];

			/**
			 * @var WP_Taxonomy[] $taxonomies
			 */
			$taxonomies = get_taxonomies([
				                             'object_type' => [ $postType->name ],
				                             'public'      => true,
				                             'show_ui'     => true,
			                             ], 'object');

			if ( ! empty( $taxonomies ) ) {
				foreach( $taxonomies as $taxonomy ) {
					$taxonomyPageValue[] = [
						'label' => $taxonomy->label,
						"value" => $taxonomy->name
					];

					$taxonomyItems[] = [
						'id' => "wp_" . $taxonomy->name . "_term_list",
						"label" => $taxonomy->label,
						"slug" => "wp:term_id",
						"control" => "remoteMultiSelect",
						'controlOptions' => [
							'defaultValue' => ['all'],
							"query" => "wp:" . $taxonomy->name . ":term_list"
						]
					];
				}
			}
		}

		array_unshift( $items, [
			'id' => 'wp_cpt_is_singular',
			"label" => __( "Single Page", 'depicter'),
			"slug" => "wp:is_singular",
			"control" => "multiSelect",
			'controlOptions' => [
				'options' => $singlePageValue,
				'defaultValue' => ['all']
			],
		]);


		$items[] = [
			'id' => 'wp_cpt_is_post_type_archive',
			'label' => __( "Archive Page", 'depicter' ),
			'slug' => "wp:is_post_type_archive",
			"control" => "multiSelect",
			'controlOptions' => [
				'options' => $archivePageValue,
				'defaultValue' => ['all'],
			],
		];

		if ( !empty( $taxonomyItems ) ) {
			$items[] = [
				'id' => 'wp_cpt_is_tax',
				'label' => __( "Taxonomy Page", 'depicter' ),
				'slug' => "wp:is_tax",
				"description" => __( "When a Taxonomy archive page for specific taxonomy is being displayed.", 'depicter' ),
				"control" => "multiSelect",
				'controlOptions' => [
					'options' => $taxonomyPageValue,
					'defaultValue' => ['all'],
				],
			];

			$items = array_merge( $items, $taxonomyItems );
		}

		return [
			"label" => __( "Custom Post Types", 'depicter' ),
			"items" => $items
		];
	}

	public function woocommerceConditions() {

	}
	public function list(): array{

		$conditions = [];

		$conditions['wordpress'] = $this->wordpressConditions();

		$conditions['customPostTypeConditions'] = $this->customPostTypeConditions();

		return $conditions;;
	}
}
