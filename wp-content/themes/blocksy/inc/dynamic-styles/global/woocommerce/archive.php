<?php

$shop_cards_type = blocksy_get_theme_mod('shop_cards_type', 'type-1');

$card_layout_default = [];

if (function_exists('blocksy_get_woo_archive_layout_defaults')) {
	$card_layout_default = blocksy_get_woo_archive_layout_defaults();
}

$woo_card_layout = blocksy_get_theme_mod('woo_card_layout', $card_layout_default);

$woo_card_layout = blocksy_normalize_layout(
	$woo_card_layout,
	$card_layout_default
);

foreach ($woo_card_layout as $layer) {
	if ( $layer['enabled'] ) {
		$selectors_map = [
			'product_image' => '[data-products] .product figure',
			'product_title' => '[data-products] .product .woocommerce-loop-product__title',
			'product_price' => '[data-products] .product .price',
			'product_rating' => '[data-products] .product .star-rating',
			'product_meta' => '[data-products] .product .entry-meta',
			'product_desc' => '[data-products] .product .entry-excerpt',
			'product_add_to_cart' => '[data-products] .product .ct-woo-card-actions',
			'product_add_to_cart_and_price' => '[data-products] .product .ct-woo-card-actions',

			// companion
			'product_brands' => '[data-products] .product .ct-product-brands',
			'product_swatches' => '[data-products] .product .ct-card-variation-swatches',
			'content-block' => '[data-products] .product .ct-product-content-block',
			'product_sku' => '[data-products] .product .ct-product-sku',
		];

		if ($shop_cards_type === 'type-1') {
			if ($layer['id'] === 'product_add_to_cart_and_price') {
				continue;
			}
		}

		if ($shop_cards_type === 'type-2') {
			if (
				$layer['id'] === 'product_add_to_cart'
				||
				$layer['id'] === 'product_price'
			) {
				continue;
			}
		}

		if ($shop_cards_type === 'type-3') {
			if ($layer['id'] === 'product_add_to_cart') {
				continue;
			}
		}

		$spacing_default = 10;

		if (
			$layer['id'] === 'product_image'
			||
			$layer['id'] === 'product_desc'
		) {
			$spacing_default = 25;
		}

		$spacing = blocksy_akg('spacing', $layer, $spacing_default);

		if (
			intval($spacing) !== $spacing_default
			||
			$spacing_default === 25
		) {
			blocksy_output_responsive([
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'selector' => $selectors_map[$layer['id']],
				'variableName' => 'product-element-spacing',
				'value' => $spacing
			]);
		}

		if ($layer['id'] === 'product_brands') {
			$brand_logo_size = blocksy_akg('brand_logo_size', $layer, 100);

			if ($brand_logo_size !== 100) {
				blocksy_output_responsive([
					'css' => $css,
					'tablet_css' => $tablet_css,
					'mobile_css' => $mobile_css,
					'selector' => $selectors_map[$layer['id']],
					'variableName' => 'product-brand-logo-size',
					'value' => $brand_logo_size,
				]);
			}

			$brand_logo_gap = blocksy_akg('brand_logo_gap', $layer, 10);

			if ($brand_logo_gap !== 10) {
				blocksy_output_responsive([
					'css' => $css,
					'tablet_css' => $tablet_css,
					'mobile_css' => $mobile_css,
					'selector' => $selectors_map[$layer['id']],
					'variableName' => 'product-brands-gap',
					'value' => $brand_logo_gap,
				]);
			}
		}

		if ($layer['id'] === 'content-block') {
			$id = isset($layer["__id"]) ? $layer["__id"] : 'default';

			blocksy_output_responsive([
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'selector' => '[data-products] .product .ct-product-content-block[data-id="' . $id . '"]',
				'variableName' => 'product-element-spacing',
				'value' => $spacing,
				'unit' => 'px'
			]);
		}
	}
}


// archive columns
$shop_columns = blocksy_get_theme_mod('blocksy_woo_columns', [
	'desktop' => 4,
	'tablet' => 3,
	'mobile' => 1,
]);

$shop_columns['desktop'] = get_option('woocommerce_catalog_columns', 4);
$shop_columns['desktop'] = 'CT_CSS_SKIP_RULE';
$shop_columns['tablet'] = 'repeat(' . $shop_columns['tablet'] . ', minmax(0, 1fr))';
$shop_columns['mobile'] = 'repeat(' . $shop_columns['mobile'] . ', minmax(0, 1fr))';

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '[data-products]',
	'variableName' => 'shop-columns',
	'value' => $shop_columns,
	'unit' => ''
]);


// archive columns & rows gap
$shop_columns_gap = blocksy_get_theme_mod('shopCardsGap', 30);

if ($shop_columns_gap !== 30) {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '[data-products]',
		'variableName' => 'grid-columns-gap',
		'value' => $shop_columns_gap,
		'unit' => '',
		'previousUnit' => 'px'
	]);
}

$shop_rows_gap = blocksy_get_theme_mod('shopCardsRowGap', 30);

if ($shop_rows_gap !== 30) {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '[data-products]',
		'variableName' => 'grid-rows-gap',
		'value' => $shop_rows_gap,
		'unit' => '',
		'previousUnit' => 'px'
	]);
}


// border radius
blocksy_output_spacing([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '[data-products] .product',
	'property' => 'theme-border-radius',
	'value' => blocksy_get_theme_mod( 'cardProductRadius',
		blocksy_spacing_value([
			'top' => '3px',
			'left' => '3px',
			'right' => '3px',
			'bottom' => '3px',
		])
	)
]);


// product title
blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod(
		'cardProductTitleFont',
		blocksy_typography_default_values([
			'size' => '17px',
			'variation' => 'n6',
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '[data-products] .woocommerce-loop-product__title, [data-products] .woocommerce-loop-category__title'
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('cardProductTitleColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '[data-products] .woocommerce-loop-product__title, [data-products] .woocommerce-loop-category__title',
			'variable' => 'theme-heading-color'
		],

		'hover' => [
			'selector' => '[data-products] .woocommerce-loop-product__title, [data-products] .woocommerce-loop-category__title',
			'variable' => 'theme-link-hover-color'
		],
	],
]);


// product excerpt
blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod(
		'cardProductExcerptFont',
		blocksy_typography_default_values([])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '[data-products] .entry-excerpt'
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('cardProductExcerptColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '[data-products] .entry-excerpt',
			'variable' => 'theme-text-color'
		],
	],
]);


// product price
blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod(
		'cardProductPriceFont',
		blocksy_typography_default_values([
			'variation' => 'n6',
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '[data-products] .product .price'
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('cardProductPriceColor'),
	'default' => [
		'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ]
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '[data-products] .product .price',
			'variable' => 'theme-text-color'
		],
	],
]);

// product SKU
blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod(
		'cardProductSkuFont',
		blocksy_typography_default_values([])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '[data-products] .ct-product-sku'
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('cardProductSkuColor'),
	'default' => [
		'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ]
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '[data-products] .ct-product-sku',
			'variable' => 'theme-text-color'
		],
	],
]);

// star rating
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('starRatingColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'inactive' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => ':root',
			'variable' => 'star-rating-initial-color'
		],

		'inactive' => [
			'selector' => ':root',
			'variable' => 'star-rating-inactive-color'
		],
	],
]);


// categories/meta
blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod(
		'card_product_categories_font',
		blocksy_typography_default_values([
			'size' => [
				'desktop' => '12px',
				'tablet'  => '12px',
				'mobile'  => '12px'
			],
			'variation' => 'n6',
			'text-transform' => 'uppercase',
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '[data-products] .entry-meta',
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('cardProductCategoriesColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-text-color)' ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '[data-products] .entry-meta',
			'variable' => 'theme-link-initial-color'
		],

		'hover' => [
			'selector' => '[data-products] .entry-meta',
			'variable' => 'theme-link-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('card_product_categories_button_type_font_colors'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '[data-products] [data-type="pill"]',
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => '[data-products] [data-type="pill"]',
			'variable' => 'theme-button-text-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('card_product_categories_button_type_background_colors'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '[data-products] [data-type="pill"]',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '[data-products] [data-type="pill"]',
			'variable' => 'theme-button-background-hover-color'
		],
	],
]);


// archive background
blocksy_output_background_css([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'selector' => '[data-prefix="woo_categories"]',
	'value' => blocksy_get_theme_mod('shop_archive_background',
		blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => Blocksy_Css_Injector::get_skip_rule_keyword()
				],
			],
		])
	),
	'responsive' => true,
]);


// cards type 1
if ($shop_cards_type === 'type-1') {

	// button color
	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('cardProductButton1Text'),
		'default' => [
			'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
			'hover' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'variables' => [
			'default' => [
				'selector' => '[data-products="type-1"]',
				'variable' => 'theme-button-text-initial-color'
			],

			'hover' => [
				'selector' => '[data-products="type-1"]',
				'variable' => 'theme-button-text-hover-color'
			],
		],
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('cardProductButtonBackground'),
		'default' => [
			'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
			'hover' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'variables' => [
			'default' => [
				'selector' => '[data-products="type-1"]',
				'variable' => 'theme-button-background-initial-color'
			],

			'hover' => [
				'selector' => '[data-products="type-1"]',
				'variable' => 'theme-button-background-hover-color'
			],
		],
	]);
}


// cards type 2
if ($shop_cards_type === 'type-2') {

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('cardProductButton2Text'),
		'default' => [
			'default' => ['color' => 'var(--theme-text-color)'],
			'hover' => ['color' => 'var(--theme-link-hover-color)'],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'variables' => [
			'default' => [
				'selector' => '[data-products="type-2"] .ct-woo-card-actions',
				'variable' => 'theme-button-text-initial-color'
			],

			'hover' => [
				'selector' => '[data-products="type-2"] .ct-woo-card-actions',
				'variable' => 'theme-button-text-hover-color'
			],
		],
	]);

	// card background
	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('cardProductBackground'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'variables' => [
			'default' => [
				'selector' => '[data-products="type-2"]',
				'variable' => 'backgroundColor'
			],
		],
	]);

	// box shadow
	blocksy_output_box_shadow([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '[data-products="type-2"]',
		'value' => blocksy_get_theme_mod('cardProductShadow', blocksy_box_shadow_value([
			'enable' => true,
			'h_offset' => 0,
			'v_offset' => 12,
			'blur' => 18,
			'spread' => -6,
			'inset' => false,
			'color' => [
				'color' => 'rgba(34, 56, 101, 0.03)',
			],
		])),
		'responsive' => true
	]);
}


// csrds type 1 and type 3
if ($shop_cards_type !== 'type-2') {

	// alignment
	$shop_cards_alignment = blocksy_get_theme_mod('shop_cards_alignment', 'CT_CSS_SKIP_RULE');
	$text_shop_cards_alignment = $shop_cards_alignment;

	$text_shop_cards_alignment = blocksy_map_values([
		'value' => $shop_cards_alignment,
		'map' => [
			'flex-start' => 'left',
			'flex-end' => 'right'
		]
	]);

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '[data-products] .product',
		'variableName' => 'horizontal-alignment',
		'value' => $shop_cards_alignment,
		'unit' => '',
	]);

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '[data-products] .product',
		'variableName' => 'text-horizontal-alignment',
		'value' => $text_shop_cards_alignment,
		'unit' => '',
	]);
}
