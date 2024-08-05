<?php

if (! isset($is_general_cpt)) {
    $is_general_cpt = false;
}

if (! isset($is_bbpress)) {
	$is_bbpress = false;
}

$page_title_options = blocksy_get_options(
	'general/page-title',
	apply_filters(
		'blocksy:options:cpt:page-title-args',
		[
			'prefix' => $post_type->name . '_single',
			'is_single' => true,
			'is_bbpress' => $is_bbpress,
			'is_cpt' => true,
			'enabled_label' => sprintf(
				__('%s Title', 'blocksy'),
				$post_type->labels->singular_name
			),
		],
		$post_type->name
	)
);

$page_structure_options = [
	blocksy_rand_md5() => [
		'type' => 'ct-title',
		'label' => sprintf(
			__('%s Structure', 'blocksy'),
			$post_type->labels->singular_name
		),
	],

	blocksy_rand_md5() => [
		'title' => __( 'General', 'blocksy' ),
		'type' => 'tab',
		'options' => array_merge([
			blocksy_get_options('single-elements/structure', [
				'prefix' => $post_type->name . '_single',
				'default_structure' => 'type-4',
				'default_content_style' => $is_bbpress ? 'boxed' : 'wide'
			])
		]),
	],

	blocksy_rand_md5() => [
		'title' => __( 'Design', 'blocksy' ),
		'type' => 'tab',
		'options' => [
			blocksy_get_options('single-elements/structure-design', [
				'prefix' => $post_type->name . '_single',
			])
		],
	]
];

$maybe_taxonomy = blocksy_maybe_get_matching_taxonomy($post_type->name, false);


$areas = [
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
	]
];

$page_elements_options = [];

if ($is_general_cpt) {
	$page_elements_options = [
		apply_filters(
			'blocksy_single_posts_post_elements_start',
			[],
			$post_type->name . '_single'
		),

		blocksy_get_options('single-elements/featured-image', [
			'prefix' => $post_type->name . '_single',
		]),

		$maybe_taxonomy ? [
			$post_type->name . '_single_has_post_tags' => [
				'label' => sprintf(
					__('%s %s', 'blocksy'),
					$post_type->labels->singular_name,
					get_taxonomy($maybe_taxonomy)->label
				),
				'type' => 'ct-switch',
				'value' => 'no',
				'sync' => blocksy_sync_single_post_container([
					'prefix' => $post_type->name . '_single'
				]),
			],
		] : [],

		blocksy_get_options('single-elements/post-share-box', [
			'prefix' => $post_type->name . '_single',
			'has_share_box' => 'no',
		]),

		blocksy_get_options('single-elements/author-box', [
			'prefix' => $post_type->name . '_single',
		]),

		blocksy_get_options('single-elements/post-nav', [
			'prefix' => $post_type->name . '_single',
			'enabled' => 'no',
			'post_type' => $post_type->name
		]),

		apply_filters(
			'blocksy_single_posts_post_elements_end',
			[],
			$post_type->name . '_single'
		),

		[
			blocksy_rand_md5() => [
				'type' => 'ct-title',
				'label' => __( 'Page Elements', 'blocksy' ),
			],
		],

		blocksy_get_options('single-elements/related-posts', [
			'prefix' => $post_type->name . '_single',
			'enabled' => 'no',
			'post_type' => $post_type->name
		]),

		blocksy_get_options('general/comments-single', [
			'prefix' => $post_type->name . '_single',
		]),

		apply_filters(
			'blocksy_single_posts_end_customizer_options',
			[],
			$post_type->name . '_single'
		)
	];

	$areas[] = [
		'title' => __('Page Elements', 'blocksy'),
		'options' => $page_elements_options,
		'sources' => blocksy_manager()
			->screen
			->get_single_prefixes_with_human_labels()
	];
}

$inner_options = array_merge(
	[
		blocksy_manager()->get_prefix_title_actions([
			'prefix' => $post_type->name . '_single',
			'areas' => $areas
		]),

		array_merge(
			$page_title_options,
			$page_structure_options,
			$is_general_cpt ? [
				blocksy_rand_md5() => [
					'type' => 'ct-title',
					'label' => sprintf(
						__('%s Elements', 'blocksy'),
						$post_type->labels->singular_name
					),
				],
			] : []
		)
	],

	$page_elements_options
);

if (
	function_exists('blc_get_content_block_that_matches')
	&&
	blc_get_content_block_that_matches([
		'template_type' => 'single',
		'template_subtype' => 'canvas',
		'match_conditions_strategy' => $post_type->name . '_single'
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
					'match_conditions_strategy' => $post_type->name . '_single'
				])) . '" target="_blank">',
				'</a>'
			)
		],
	];
}

$options = [
	$post_type->name . '_single_section_options' => [
		'type' => 'ct-options',
		'inner-options' => $inner_options
	],
];
