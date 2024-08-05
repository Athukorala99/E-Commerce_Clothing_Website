<?php

$page_title_options = blocksy_get_options('general/page-title', [
    'prefix' => 'categories',
    'is_archive' => true
]);

$posts_listing_options = blocksy_get_options('general/posts-listing', [
	'prefix' => 'categories',
	'title' => __('Categories', 'blocksy')
]);

$inner_options = [
	blocksy_manager()->get_prefix_title_actions([
		'prefix' => 'categories',
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
				'options' => [],
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
			'type'  => 'ct-title',
			'label' => __( 'Categories Elements', 'blocksy' ),
		],
	],

	blocksy_get_options('general/sidebar-particular', [
		'prefix' => 'categories',
	])
];

if (
	function_exists('blc_get_content_block_that_matches')
	&&
	blc_get_content_block_that_matches([
		'template_type' => 'archive',
		'template_subtype' => 'canvas',
		'match_conditions_strategy' => 'categories'
	])
) {
	$inner_options = [
		blocksy_rand_md5() => [
			'type' => 'ct-notification',
			'attr' => [ 'data-type' => 'background:white' ],
			'text' => sprintf(
				__('This archive page is overrided by a custom template, to edit it please access %sthis page%s.', 'blocksy'),
				'<a href="' . get_edit_post_link(blc_get_content_block_that_matches([
					'template_type' => 'archive',
					'template_subtype' => 'canvas',
					'match_conditions_strategy' => 'categories'
				])) . '" target="_blank">',
				'</a>'
			)
		],
	];
}

$options = [
	'single_categories_section_options' => [
		'type' => 'ct-options',
		'setting' => [ 'transport' => 'postMessage' ],
		'inner-options' => $inner_options
	],
];
