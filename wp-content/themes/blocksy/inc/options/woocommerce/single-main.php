<?php

$page_title_options = blocksy_get_options('general/page-title', [
	'prefix' => 'product',
	'is_single' => true,
	'enabled_label' => __('Product Title', 'blocksy')
]);

$options = [
	'woo_single_section_options' => [
		'type' => 'ct-options',
		'setting' => [ 'transport' => 'postMessage' ],
		'inner-options' => [
			blocksy_manager()->get_prefix_title_actions([
				'prefix' => 'product',
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
					]
				]
			]),

			$page_title_options,

			[
				blocksy_rand_md5() => [
					'type'  => 'ct-title',
					'label' => __( 'Page Structure', 'blocksy' ),
				],

				blocksy_rand_md5() => [
					'title' => __( 'General', 'blocksy' ),
					'type' => 'tab',
					'options' => [
						blocksy_get_options('single-elements/structure', [
							'prefix' => 'product',
							'default_structure' => 'type-4',
							'has_v_spacing' => true
						]),
					],
				],

				blocksy_rand_md5() => [
					'title' => __( 'Design', 'blocksy' ),
					'type' => 'tab',
					'options' => [
						blocksy_get_options('single-elements/structure-design', [
							'prefix' => 'product',
						]),
					],
				],
			],

			apply_filters(
				'blocksy:options:single_product:product-elements:end',
				[]
			),

			[
				blocksy_rand_md5() => [
					'type'  => 'ct-title',
					'label' => __( 'Page Elements', 'blocksy' ),
				],
			],

			blocksy_get_options('woocommerce/single-product-gallery'),

			blocksy_get_options('woocommerce/single-product-elements'),

			apply_filters(
				'blocksy_single_product_floating_cart',
				[]
			),

			blocksy_get_options('woocommerce/single-product-tabs'),

			blocksy_get_options('woocommerce/related-upsells'),

			[
				blocksy_rand_md5() => [
					'type'  => 'ct-title',
					'label' => __( 'Functionality Options', 'blocksy' ),
				],

				'has_ajax_add_to_cart' => [
					'label' => __('AJAX Add To Cart', 'blocksy'),
					'type' => 'ct-switch',
					'value' => 'no',
				],
			],
		],
	],
];
