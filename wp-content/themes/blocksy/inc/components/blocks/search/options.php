<?php

$is_pro = function_exists('blc_fs') && blc_fs()->can_use_premium_code();

$cpt_choices = [
	'post' => __('Posts', 'blocksy'),
	'page' => __('Pages', 'blocksy'),
];

$cpt_options = [
	'post' => true,
	'page' => true
];

if (class_exists('WooCommerce')) {
	$cpt_choices['product'] = __('Products', 'blocksy');
	$cpt_options['product'] = true;
}

$all_cpts = blocksy_manager()->post_types->get_supported_post_types();

if (function_exists('is_bbpress')) {
	$all_cpts[] = 'forum';
	$all_cpts[] = 'topic';
	$all_cpts[] = 'reply';
}

foreach ($all_cpts as $single_cpt) {
	if (get_post_type_object($single_cpt)) {
		$cpt_choices[$single_cpt] = get_post_type_labels(
			get_post_type_object($single_cpt)
		)->singular_name;
	} else {
		$cpt_choices[$single_cpt] = ucfirst($single_cpt);
	}

	$cpt_options[$single_cpt] = true;
}

$options = [

	'buttonUseText' => [
		'label' => __( 'Placeholder Text', 'blocksy' ),
		'type' => 'hidden',
		'value' => 'no',
	],

	'buttonPosition' => [
		'label' => __( 'Placeholder Text', 'blocksy' ),
		'type' => 'hidden',
		'value' => 'inside',
	],

	'search_box_button_text' => [
		'label' => __( 'Button Text', 'blocksy' ),
		'type' => 'hidden',
		'value' => __( 'Search', 'blocksy' ),
	],

	'search_box_placeholder' => [
		'label' => __( 'Placeholder Text', 'blocksy' ),
		'type' => 'hidden',
		'value' => __( 'Search', 'blocksy' ),
	],

	'searchBoxHeight' => [
		'label' => __( 'Input Height', 'blocksy' ),
		'type' => 'ct-slider',
		'min' => 40,
		'max' => 80,
		'value' => '',
		'responsive' => false,
		'divider' => 'top:full',
		'setting' => [ 'transport' => 'postMessage' ],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [ 'buttonUseText' => 'no' ],
		'options' => $is_pro ? [
			'icon' => [
				'type' => 'icon-picker',
				'label' => __('Icon', 'blocksy'),
				'design' => 'inline',
				'divider' => 'top:full',
				'value' => [
					'icon' => 'blc blc-search'
				]
			],
		] : []
	],

	'enable_live_results' => [
		'label' => __( 'Live Results', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'no',
		'divider' => 'top:full',
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [ 'enable_live_results' => 'yes' ],
		'options' => [

			'live_results_images' => [
				'label' => __( 'Live Results Images', 'blocksy' ),
				'type' => 'ct-switch',
				'value' => 'yes',
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => [ 'search_through/product' => true ],
				'options' => [
					'searchProductPrice' => [
						'label' => __( 'Live Results Product Price', 'blocksy' ),
						'type' => 'ct-switch',
						'value' => 'no',
						'setting' => [ 'transport' => 'postMessage' ],
					],

					'searchProductStatus' => [
						'label' => __( 'Live Results Product Status', 'blocksy' ),
						'type' => 'ct-switch',
						'value' => 'no',
						'setting' => [ 'transport' => 'postMessage' ],
					],
				]
			],

		],
	],

	'has_taxonomy_filter' => [
		'label' => __( 'Taxonomy Filter', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'no',
		'divider' => 'top:full',
	],

	'taxonomy_filter_label' => [
		'label' => __( 'Placeholder Text', 'blocksy' ),
		'type' => 'hidden',
		'value' => __('Select Category', 'blocksy')
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [ 'has_taxonomy_filter' => 'yes' ],
		'options' => [

			'taxonomy_filter_visibility' => [
				'label' => __( 'Filter Visibility', 'blocksy' ),
				'type' => 'ct-visibility',
				'design' => 'block',
				'allow_empty' => true,
				'setting' => [ 'transport' => 'postMessage' ],
				'value' => [
					'desktop' => true,
					'tablet' => true,
					'mobile' => false,
				],

				'choices' => blocksy_ordered_keys([
					'desktop' => __( 'Desktop', 'blocksy' ),
					'tablet' => __( 'Tablet', 'blocksy' ),
					'mobile' => __( 'Mobile', 'blocksy' ),
				]),
			],

			'has_taxonomy_children' => [
				'label' => __( 'Taxonomy Childrens', 'blocksy' ),
				'type' => 'ct-switch',
				'value' => 'no',
				// 'divider' => 'top',
			],
		],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-title',
		'label' => __('Search Through Criteria', 'blocksy'),
		'attr' => ['class' => 'components-base-control ct-title'],
		'desc' => __(
			'Chose in which post types do you want to perform searches.',
			'blocksy'
		)
	],

	'search_through' => [
		'label' => false,
		'type' => 'ct-checkboxes',
		'attr' => ['data-columns' => '2'],
		'disableRevertButton' => true,
		'choices' => blocksy_ordered_keys($cpt_choices),
		'value' => $cpt_options
	],

];
