<?php

$page_title_options = blocksy_get_options('general/page-title', [
	'prefix' => 'single_blog_post',
	'is_single' => true,
	'enabled_label' => __('Post Title', 'blocksy')
]);

$page_structure_options = [
	blocksy_rand_md5() => [
		'type' => 'ct-title',
		'label' => __( 'Post Structure', 'blocksy' ),
	],

	blocksy_rand_md5() => [
		'title' => __( 'General', 'blocksy' ),
		'type' => 'tab',
		'options' => [
			blocksy_get_options('single-elements/structure', [
				'prefix' => 'single_blog_post',
			]),
		],
	],

	blocksy_rand_md5() => [
		'title' => __( 'Design', 'blocksy' ),
		'type' => 'tab',
		'options' => [
			blocksy_get_options('single-elements/structure-design', [
				'prefix' => 'single_blog_post',
			])
		],
	]
];

$page_elements_options = [
	[
		blocksy_rand_md5() => [
			'type' => 'ct-title',
			'label' => __( 'Post Elements', 'blocksy' ),
		],
	],

	blocksy_get_options('single-elements/featured-image', [
		'prefix' => 'single_blog_post',
	]),

	[
		'single_blog_post_has_post_tags' => [
			'label' => __( 'Post Tags', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'no',
			'sync' => blocksy_sync_single_post_container([
				'prefix' => 'single_blog_post'
			]),
		],
	],

	blocksy_get_options('single-elements/post-share-box', [
		'prefix' => 'single_blog_post'
	]),

	blocksy_get_options('single-elements/author-box', [
		'prefix' => 'single_blog_post'
	]),

	blocksy_get_options('single-elements/post-nav', [
		'prefix' => 'single_blog_post'
	]),

	[
		blocksy_rand_md5() => [
			'type' => 'ct-title',
			'label' => __( 'Page Elements', 'blocksy' ),
		],
	],

	blocksy_get_options('single-elements/related-posts', [
		'prefix' => 'single_blog_post'
	]),

	blocksy_get_options('general/comments-single', [
		'prefix' => 'single_blog_post',
	]),

	apply_filters(
		'blocksy_single_posts_end_customizer_options',
		[],
		'single_blog_post'
	)
];

$inner_options = [
	blocksy_manager()->get_prefix_title_actions([
		'prefix' => 'single_blog_post',
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
								'has_woocommerce' => true,
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
];

if (
	function_exists('blc_get_content_block_that_matches')
	&&
	blc_get_content_block_that_matches([
		'template_type' => 'single',
		'template_subtype' => 'canvas',
		'match_conditions_strategy' => 'single_blog_post'
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
					'match_conditions_strategy' => 'single_blog_post'
				])) . '" target="_blank">',
				'</a>'
			)
		],
	];
}

$options = [
	'single_section_options' => [
		'type' => 'ct-options',
		'setting' => [ 'transport' => 'postMessage' ],
		'inner-options' => $inner_options
	],
];
