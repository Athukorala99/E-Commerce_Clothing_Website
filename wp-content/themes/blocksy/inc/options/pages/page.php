<?php

/**
 * Page options.
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$page_title_options = blocksy_get_options('general/page-title', [
	'prefix' => 'single_page',
	'is_single' => true,
	'is_page' => true
]);

$page_structure_options = [
	blocksy_rand_md5() => [
		'label' => __( 'Page Structure', 'blocksy' ),
		'type' => 'ct-title',
	],

	blocksy_rand_md5() => [
		'title' => __( 'General', 'blocksy' ),
		'type' => 'tab',
		'options' => [
			blocksy_get_options('single-elements/structure', [
				'default_structure' => 'type-4',
				'prefix' => 'single_page',
			]),
		],
	],

	blocksy_rand_md5() => [
		'title' => __( 'Design', 'blocksy' ),
		'type' => 'tab',
		'options' => [
			blocksy_get_options('single-elements/structure-design', [
				'prefix' => 'single_page',
			])
		],
	]
];

$maybe_taxonomy = blocksy_maybe_get_matching_taxonomy('page', false);

$page_elements_options = [
	[
		[
			blocksy_rand_md5() => [
				'type' => 'ct-title',
				'label' => __( 'Page Elements', 'blocksy' ),
			],
		],

		blocksy_get_options('single-elements/featured-image', [
			'prefix' => 'single_page',
		]),

		blocksy_get_options('single-elements/post-share-box', [
			'prefix' => 'single_page'
		]),
	],

	$maybe_taxonomy ? [
		'single_page_has_post_tags' => [
			'label' => sprintf(
				__('Page %s', 'blocksy'),
				get_taxonomy($maybe_taxonomy)->label
			),
			'type' => 'ct-switch',
			'value' => 'no',
			'sync' => blocksy_sync_single_post_container([
				'prefix' => 'single_page'
			]),
		],
	] : [],

	[
		blocksy_get_options('general/comments-single', [
			'prefix' => 'single_page',
		]),

		apply_filters(
			'blocksy_single_posts_end_customizer_options',
			[],
			'single_page'
		),
	]
];


$inner_options = [
	[
		blocksy_manager()->get_prefix_title_actions([
			'prefix' => 'single_page',
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
					'id' => 'page_structure',
					'title' => __('Page Structure', 'blocksy'),
					'options' => $page_structure_options,
					'sources' => blocksy_manager()
							->screen
							->get_single_prefixes_with_human_labels()
				],

				[
					'title' => __('Page Elements', 'blocksy'),
					'options' => $page_elements_options,
					'sources' => blocksy_manager()
							->screen
							->get_single_prefixes_with_human_labels()
				]
			]
		]),

		$page_title_options,
		$page_structure_options,
		$page_elements_options
	]
];

if (
	function_exists('blc_get_content_block_that_matches')
	&&
	blc_get_content_block_that_matches([
		'template_type' => 'single',
		'template_subtype' => 'canvas',
		'match_conditions_strategy' => 'single_page'
	])
) {
	$inner_options = [
		blocksy_rand_md5() => [
			'type' => 'ct-notification',
			'attr' => [ 'data-type' => 'background:white' ],
			'text' => sprintf(
				__('This single page is overrided by a custom template, to edit it please access %sthis page%s.', 'blocksy'),
				'<a href="' . get_edit_post_link(blc_get_content_block_that_matches([
					'template_type' => 'single',
					'template_subtype' => 'canvas',
					'match_conditions_strategy' => 'single_page'
				])) . '" target="_blank">',
				'</a>'
			)
		],
	];
}

$options = [
	'page_section_options' => [
		'type' => 'ct-options',
		'setting' => [ 'transport' => 'postMessage' ],
		'inner-options' => $inner_options
	],
];
