<?php

function blocksy_get_woo_archive_layout_defaults() {
	return apply_filters(
		'blocksy_woo_card_options_layers:defaults',
		[
			[
				'id' => 'product_image',
				'enabled' => true,
			],
			[
				'id' => 'product_title',
				'enabled' => true,
			],
			[
				'id' => 'product_price',
				'enabled' => true,
			],
			[
				'id' => 'product_rating',
				'enabled' => true,
			],
			[
				'id' => 'product_meta',
				'enabled' => true,
			],
			[
				'id' => 'product_desc',
				'enabled' => false,
			],
			[
				'id' => 'product_add_to_cart',
				'enabled' => true,
			],
			[
				'id' => 'product_add_to_cart_and_price',
				'enabled' => true
			]
		]
	);
}

function blocksy_get_woo_single_layout_defaults($layout = 'main') {
	$additional_actions = blocksy_manager()
		->woocommerce
		->single
		->additional_actions
		->get_actions();

	$actions_layer = [];

	if (! empty($additional_actions)) {
		$actions_layer[] = [
			'id' => 'product_actions',
			'enabled' => true,
		];
	}

	if ($layout === 'left') {
		return apply_filters(
			'blocksy_woo_single_left_options_layers:defaults',
			[
				[
					'id' => 'product_title',
					'enabled' => true,
				],

				[
					'id' => 'product_rating',
					'enabled' => true,
				],

				[
					'id' => 'product_price',
					'enabled' => true,
				],

				[
					'id' => 'product_desc',
					'enabled' => true,
				]
			]
		);
	}

	if ($layout === 'right') {
		return apply_filters(
			'blocksy_woo_single_right_options_layers:defaults',
			array_merge(
				[
					[
						'id' => 'product_add_to_cart',
						'enabled' => true,
					],
				],

				$actions_layer,

				[
					[
						'id' => 'divider',
						'enabled' => true,
					],
					[
						'id' => 'product_meta',
						'enabled' => true,
					],
					[
						'id' => 'divider',
						'enabled' => true,
					],
					[
						'id' => 'product_payment_methods',
						'enabled' => false,
					],

					[
						'id' => 'additional_info',
						'enabled' => false,
					]
				]
			)
		);
	}

	return apply_filters(
		'blocksy_woo_single_options_layers:defaults',
		array_merge(
			[
				[
					'id' => 'product_title',
					'enabled' => true,
				],

				[
					'id' => 'product_rating',
					'enabled' => true,
				],

				[
					'id' => 'product_price',
					'enabled' => true,
				],

				[
					'id' => 'product_desc',
					'enabled' => true,
				],

				[
					'id' => 'divider',
					'enabled' => true,
					'__id' => 'divider_1'
				],

				[
					'id' => 'product_add_to_cart',
					'enabled' => true,
				],
			],

			$actions_layer,

			[
				[
					'id' => 'divider',
					'enabled' => true,
					'__id' => 'divider_2'
				],

				[
					'id' => 'product_meta',
					'enabled' => true,
				],
				[
					'id' => 'product_payment_methods',
					'enabled' => false,
				],

				[
					'id' => 'additional_info',
					'enabled' => false,
				]
			]
		)
	);
}
