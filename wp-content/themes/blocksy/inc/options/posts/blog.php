<?php
/**
 * Blog options
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$page_title_options = blocksy_get_options('general/page-title', [
	'prefix' => 'blog',
	'is_home' => true,
	'enabled_label' => __('Blog Title', 'blocksy'),
	'enabled_default' => 'no'
]);

$posts_listing_options = blocksy_get_options('general/posts-listing', [
	'prefix' => 'blog',
	'title' => __('Blog', 'blocksy')
]);

$pagination_options = blocksy_get_options('general/pagination', [
	'prefix' => 'blog',
]);

$inner_options = [
	blocksy_manager()->get_prefix_title_actions([
		'prefix' => 'blog',
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
		'prefix' => 'blog',
	]),

	$pagination_options,

	[
		blocksy_rand_md5() => [
			'type' => 'ct-title',
			'label' => __( 'Functionality Options', 'blocksy' ),
		],
	],

	apply_filters(
		'blocksy_posts_home_page_elements_end',
		[],
		'blog',
		'post'
	),

	blocksy_get_options('general/cards-reveal-effect', [
		'prefix' => 'blog',
	]),
];

if (
	function_exists('blc_get_content_block_that_matches')
	&&
	blc_get_content_block_that_matches([
		'template_type' => 'archive',
		'template_subtype' => 'canvas',
		'match_conditions_strategy' => 'blog'
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
					'match_conditions_strategy' => 'blog'
				])) . '" target="_blank">',
				'</a>'
			)
		],
	];
}

$options = [
	'blog_posts_section_options' => [
		'type' => 'ct-options',
		'inner-options' => $inner_options
	],
];
