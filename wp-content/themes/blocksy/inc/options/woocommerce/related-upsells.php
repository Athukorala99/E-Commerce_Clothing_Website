<?php

$options = [
	'woo_has_related_upsells' => [
		'label' => __( 'Related & Upsells', 'blocksy' ),
		'type' => 'ct-panel',
		'switch' => true,
		'value' => 'yes',
		'sync' => blocksy_sync_whole_page([
			'prefix' => 'product',
			'loader_selector' => '.type-product'
		]),
		'inner-options' => [

			apply_filters(
				'blocksy_customizer_options:woocommerce:related:before',
				[]
			),

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => [
					'woocommerce_related_products_slideshow' => '!slider',
				],
				'options' => [

					'woo_product_related_cards_columns' => [
						'label' => __('Columns & Rows', 'blocksy'),
						'type' => 'ct-woocommerce-columns-and-rows',
						'value' => [
							'desktop' => 4,
							'tablet' => 3,
							'mobile' => 1
						],
						'min' => 1,
						'max' => 6,
						'responsive' => true,
						'sync' => blocksy_sync_whole_page([
							'prefix' => 'product',
							'loader_selector' => '[class*="post"] .products'
						]),
						'columns_id' => 'woo_product_related_cards_columns',
						'rows_id' => 'woo_product_related_cards_rows'
					],

					'woo_product_related_cards_rows' => [
						'type' => 'hidden',
						'value' => 1,
						'sync' => blocksy_sync_whole_page([
							'prefix' => 'product',
							'loader_selector' => '[class*="post"] .products'
						]),
					],

					blocksy_rand_md5() => [
						'type' => 'ct-divider',
					],

				]
			],

			'related_products_visibility' => [
				'label' => __('Related Products Visibility', 'blocksy'),
				'type' => 'ct-visibility',
				'design' => 'block',
				'setting' => ['transport' => 'postMessage'],
				'allow_empty' => true,

				'value' => [
					'desktop' => true,
					'tablet' => false,
					'mobile' => false,
				],

				'choices' => blocksy_ordered_keys([
					'desktop' => __( 'Desktop', 'blocksy' ),
					'tablet' => __( 'Tablet', 'blocksy' ),
					'mobile' => __( 'Mobile', 'blocksy' ),
				]),
			],

			blocksy_rand_md5() => [
				'type' => 'ct-divider',
			],

			'upsell_products_visibility' => [
				'label' => __('Upsell Products Visibility', 'blocksy'),
				'type' => 'ct-visibility',
				'design' => 'block',
				'setting' => ['transport' => 'postMessage'],
				'allow_empty' => true,

				'value' => [
					'desktop' => true,
					'tablet' => false,
					'mobile' => false,
				],

				'choices' => blocksy_ordered_keys([
					'desktop' => __( 'Desktop', 'blocksy' ),
					'tablet' => __( 'Tablet', 'blocksy' ),
					'mobile' => __( 'Mobile', 'blocksy' ),
				]),
			],

		],
	],
];
