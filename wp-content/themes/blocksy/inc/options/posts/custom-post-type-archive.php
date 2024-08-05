<?php

$page_title_options = blocksy_get_options('general/page-title', [
	'prefix' => $post_type->name . '_archive',
	'is_cpt' => true,
	'is_archive' => true,
	'enabled_label' => sprintf(
		__('%s Title', 'blocksy'),
		$post_type->labels->name
	),
]);

$posts_listing_options = blocksy_get_options('general/posts-listing', [
	'prefix' => $post_type->name . '_archive',
	'title' => $post_type->labels->name,
	'is_cpt' => true
]);

$pagination_options = blocksy_get_options('general/pagination', [
	'prefix' => $post_type->name . '_archive',
]);

$inner_options = [
	blocksy_manager()->get_prefix_title_actions([
		'prefix' => $post_type->name . '_archive',
		'areas' => [
			[
				'title' => __('Page Title', 'blocksy'),
				'options' => $page_title_options,
				'sources' => array_merge(
					blocksy_manager()
						->screen
						->get_archive_prefixes_with_human_labels([
							'has_categories' => true,
							'has_author' => true,
							'has_search' => true,
							'has_woocommerce' => true
						]),

					blocksy_manager()
						->screen
						->get_single_prefixes_with_human_labels([
							'has_woocommerce' => true
						])
				)
			],

			[
				'id' => 'posts_listing',
				'title' => __('Posts Listing', 'blocksy'),
				'options' => $posts_listing_options,
				'sources' => blocksy_manager()
					->screen
					->get_archive_prefixes_with_human_labels([
						'has_categories' => true,
						'has_author' => true,
						'has_search' => true
					]),
			],

			[
				'title' => __('Pagination', 'blocksy'),
				'options' => $pagination_options,
				'sources' => blocksy_manager()
					->screen
					->get_archive_prefixes_with_human_labels([
						'has_categories' => true,
						'has_author' => true,
						'has_search' => true
					]),
			]
		]
	]),

	$page_title_options,
	$posts_listing_options,

	[
		blocksy_rand_md5() => [
			'type' => 'ct-title',
			'label' => __( 'Page Elements', 'blocksy' ),
		],
	],

	blocksy_get_options('general/sidebar-particular', [
		'prefix' => $post_type->name . '_archive',
	]),

	$pagination_options,

	[
		blocksy_rand_md5() => [
			'type' => 'ct-title',
			'label' => __('Functionality Options', 'blocksy'),
		],
	],

	apply_filters(
		'blocksy_posts_home_page_elements_end',
		[],
		$post_type->name . '_archive',
		$post_type->name
	),

	blocksy_get_options('general/cards-reveal-effect', [
		'prefix' => $post_type->name . '_archive',
	]),
];

$options = [
	$post_type->name . '_section_options' => [
		'type' => 'ct-options',
		'inner-options' => $inner_options
	],
];
