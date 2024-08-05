<?php

$is_pro = function_exists('blc_fs') && blc_fs()->can_use_premium_code();

$payment_method_options = [
	'item_visa' => [
		'label' => __('Visa', 'blocksy'),
	],

	'item_mastercard' => [
		'label' => __('Mastercard', 'blocksy'),
	],

	'item_amex' => [
		'label' => __('Amex', 'blocksy'),
	],

	'item_discover' => [
		'label' => __('Discover', 'blocksy'),
	],

	'item_paypal' => [
		'label' => __('PayPal', 'blocksy'),
	],

	'item_apple_pay' => [
		'label' => __('Apple Pay', 'blocksy'),
	],

	'item_google_pay' => [
		'label' => __('Google Pay', 'blocksy'),
	],
];

$payment_method_options = array_merge(
	$payment_method_options,
	$is_pro ? [
		'custom_link' => [
			'label' => sprintf(
				__('%s', 'blocksy'),
				__('Custom', 'blocksy')
			),
			'clone' => 4,
		]
	] : []
);

$additional_info_options = [
	'additional_info_item' => [
		'label' => sprintf('<%%= item_title || "%s" %%>', __('Item Label', 'blocksy')),
		'clone' => 10,
		'options' => [
			$is_pro ? [
				'icon_source' => [
					'label' => __( 'Icon Source', 'blocksy' ),
					'type' => 'ct-radio',
					'value' => 'default',
					'view' => 'text',
					'design' => 'block',
					'choices' => [
						'default' => __( 'Default', 'blocksy' ),
						'custom' => __( 'Custom', 'blocksy' ),
					]
				],

				blocksy_rand_md5() => [
					'type' => 'ct-condition',
					'condition' => ['icon_source' => 'custom'],
					'options' => [
						'icon' => [
							'type' => 'icon-picker',
							'label' => __('Icon', 'blocksy'),
							'design' => 'inline',
							'value' => [
								'icon' => 'blc blc-user' // change defaults
							]
						]
					]
				],
			]: [],

			'item_title' => [
				'label' => __('Title', 'blocksy'),
				'type' => 'text',
				'design' => 'block',
				'value' => 'Test text',
				'disableRevertButton' => true,
				'sync' => [
					'id' => 'woo_single_layout_skip'
				]
			],
		]
	]
];

if ($is_pro) {
	foreach ($payment_method_options as $key => $method) {
		$payment_method_options[$key]['options'] = [
			'icon_source' => [
				'label' => __( 'Icon Source', 'blocksy' ),
				'type' => 'ct-radio',
				'value' => 'default',
				'view' => 'text',
				'design' => 'block',
				'choices' => [
					'default' => __( 'Default', 'blocksy' ),
					'custom' => __( 'Custom', 'blocksy' ),
				]
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => ['icon_source' => 'custom'],
				'options' => [
					'icon' => [
						'type' => 'icon-picker',
						'label' => __('Icon', 'blocksy'),
						'design' => 'inline',
						'value' => [
							'icon' => 'blc blc-user'
						]
					]
				]
			]
		];
	}
}

$options = apply_filters(
	'blocksy_woo_single_options_layers:extra',
	[
		'product_title' => [
			'label' => __('Title', 'blocksy'),
			'options' => [
				'spacing' => [
					'label' => __( 'Bottom Spacing', 'blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 10,
					'responsive' => true,
					'sync' => [
						'id' => 'woo_single_layout_skip'
					],
				],
			],
		],

		'product_rating' => [
			'label' => __('Star Rating', 'blocksy'),
			'options' => [
				'spacing' => [
					'label' => __( 'Bottom Spacing', 'blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 10,
					'responsive' => true,
					'sync' => [
						'id' => 'woo_single_layout_skip'
					],
				],
			],
		],

		'product_price' => [
			'label' => __('Price', 'blocksy'),
			'options' => apply_filters(
				'blocksy:single-product-layer:options:product_price',
				[
					[
						'spacing' => [
							'label' => __( 'Bottom Spacing', 'blocksy' ),
							'type' => 'ct-slider',
							'min' => 0,
							'max' => 100,
							'value' => 35,
							'responsive' => true,
							'sync' => [
								'id' => 'woo_single_layout_skip'
							],
						],
					]
				],
				'blc blc-feather'
			),
		],

		'product_desc' => [
			'label' => __('Short Description', 'blocksy'),
			'options' => [
				'spacing' => [
					'label' => __( 'Bottom Spacing', 'blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 35,
					'responsive' => true,
					'sync' => [
						'id' => 'woo_single_layout_skip'
					],
				],
			],
		],

		'divider' => [
			'label' => __('Divider', 'blocksy'),
			'clone' => 5,
			'options' => [
				'spacing' => [
					'label' => __( 'Bottom Spacing', 'blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 35,
					'responsive' => true,
					'sync' => [
						'id' => 'woo_single_layout_skip'
					],
				],
			],
		],

		'product_add_to_cart' => [
			'label' => __('Add to Cart', 'blocksy'),
			'options' => [
				
				'add_to_cart_layer_title' => [
					'label' => __('Title', 'blocksy'),
					'type' => 'text',
					'design' => 'block',
					'value' => '',
					'disableRevertButton' => true,
					'sync' => [
						'id' => 'woo_card_layout_skip'
					],
				],

				'add_to_cart_button_width' => [
					'label' => __('Button Width', 'blocksy'),
					'type' => 'ct-slider',
					'value' => '100%',
					'units' => blocksy_units_config([
						['unit' => '%', 'min' => 30, 'max' => 100],
					]),
					'responsive' => true,
					'setting' => ['transport' => 'postMessage'],
					'sync' => [
						'id' => 'woo_single_layout_skip'
					],
				],

				'spacing' => [
					'label' => __( 'Bottom Spacing', 'blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 35,
					'responsive' => true,
					'sync' => [
						'id' => 'woo_single_layout_skip'
					],
				],
			],
		],

		'product_meta' => [
			'label' => __('Meta', 'blocksy'),
			'options' => [
				'spacing' => [
					'label' => __( 'Bottom Spacing', 'blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 10,
					'responsive' => true,
					'sync' => [
						'id' => 'woo_single_layout_skip'
					],
				],
			],
		],

		'product_payment_methods' => [
			'label' => __('Payment Methods', 'blocksy'),
			'sync' => [
				'id' => 'product_payment_methods',
			],
			'options' => [

				'payment_methods_title' => [
					'label' => __('Title', 'blocksy'),
					'type' => 'text',
					'design' => 'block',
					'value' => __('Guaranteed Safe Checkout', 'blocksy'),
					'disableRevertButton' => true,
					'sync' => [
						'id' => 'woo_single_layout_skip'
					],
				],

				'payment_items' => [
					'label' => false,
					'type' => 'ct-layers',
					'itemClass' => 'ct-inner-layer',
					'value' => [
						[
							'id' => 'item_visa',
							'enabled' => true,
							'label' => __('Visa', 'blocksy'),
						],

						[
							'id' => 'item_mastercard',
							'enabled' => true,
							'label' => __('Mastercard', 'blocksy'),
						],

						[
							'id' => 'item_amex',
							'enabled' => true,
							'label' => __('Amex', 'blocksy'),
						],

						[
							'id' => 'item_discover',
							'enabled' => true,
							'label' => __('Discover', 'blocksy'),
						],
					],
					'manageable' => true,
					'sync' => 'live',
					'settings' => $payment_method_options
				],

				'payment_icons_size' => [
					'label' => __( 'Icon Size', 'blocksy' ),
					'type' => 'ct-slider',
					'min' => 5,
					'max' => 100,
					'value' => 40,
					'responsive' => true,
					'sync' => [
						'id' => 'woo_single_layout_skip'
					],
				],

				'payment_icons_color' => [
					'label' => __('Icons Color', 'blocksy'),
					'type' => 'ct-radio',
					'value' => 'default',
					'view' => 'text',
					'design' => 'block',
					'choices' => [
						'default' => __( 'Default', 'blocksy' ),
						'custom' => __( 'Custom', 'blocksy' ),
					],
					'sync' => [
						'id' => 'product_payment_methods',
					],
				],

				'spacing' => [
					'label' => __( 'Bottom Spacing', 'blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 10,
					'responsive' => true,
					'sync' => [
						'id' => 'woo_single_layout_skip'
					],
				],

			],
		],

		'additional_info' => [
			'label' => __('Additional Info', 'blocksy'),
			'options' => [
				'product_additional_info_title' => [
					'label' => __('Title', 'blocksy'),
					'type' => 'text',
					'design' => 'block',
					'value' => __('Extra Features', 'blocksy'),
					'sync' => [
						'id' => 'woo_single_layout_skip'
					],
				],
				'additional_info_items' => [
					'label' => __('Items', 'blocksy'),
					'type' => 'ct-layers',
					'itemClass' => 'ct-inner-layer',
					'manageable' => false,
					'forcedRevertButton' => true,
					'value' => [
						[
							'id' => 'additional_info_item',
							'enabled' => true,
							'item_title' => __('Premium Quality', 'blocksy')
						],
						[
							'id' => 'additional_info_item',
							'enabled' => true,
							'item_title' => __('Secure Payments', 'blocksy')
						],
						[
							'id' => 'additional_info_item',
							'enabled' => true,
							'item_title' => __('Satisfaction Guarantee', 'blocksy')
						],
						[
							'id' => 'additional_info_item',
							'enabled' => true,
							'item_title' => __('Worldwide Shipping', 'blocksy')
						],
						[
							'id' => 'additional_info_item',
							'enabled' => true,
							'item_title' => __('Money Back Guarantee', 'blocksy')
						],
					],
					'settings' => $additional_info_options
				],

				'spacing' => [
					'label' => __( 'Bottom Spacing', 'blocksy' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 10,
					'responsive' => true,
					'sync' => [
						'id' => 'woo_single_layout_skip'
					],
				],
			]
		],
	]
);

