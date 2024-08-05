<?php
/**
 * Search page
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$page_title_options = blocksy_get_options('general/page-title', [
	'prefix' => 'search',
	'is_search' => true
]);

$posts_listing_options = blocksy_get_options('general/posts-listing', [
	'prefix' => 'search',
	'title' => __('Search Results', 'blocksy')
]);

$inner_options = [
	blocksy_manager()->get_prefix_title_actions([
		'prefix' => 'search',
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
			'label' => __( 'Page Elements', 'blocksy' ),
		],
	],

	blocksy_get_options('general/sidebar-particular', [
		'prefix' => 'search'
	]),

	[
		blocksy_rand_md5() => [
			'type' => 'ct-title',
			'label' => __( 'Functionality Options', 'blocksy' ),
		],

		'search_enable_live_results' => [
			'label' => __( 'Live results', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'yes',
		],

		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => [ 'search_enable_live_results' => 'yes' ],
			'options' => function_exists('is_shop') ? [

				'searchProductPrice' => [
					'label' => __( 'Live Results Product Price', 'blocksy' ),
					'type' => 'ct-switch',
					'value' => 'no',
					'divider' => 'top',
				],

				'searchProductStatus' => [
					'label' => __( 'Live Results Product Status', 'blocksy' ),
					'type' => 'ct-switch',
					'value' => 'no',
					'divider' => 'top',
				],

			] : []
		],

	]
];

if (
	function_exists('blc_get_content_block_that_matches')
	&&
	blc_get_content_block_that_matches([
		'template_type' => 'archive',
		'template_subtype' => 'canvas',
		'match_conditions_strategy' => 'search'
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
					'match_conditions_strategy' => 'search'
				])) . '" target="_blank">',
				'</a>'
			)
		],
	];
}

$options = [
	'search_section_options' => [
		'type' => 'ct-options',
		'inner-options' => $inner_options
	]
];
