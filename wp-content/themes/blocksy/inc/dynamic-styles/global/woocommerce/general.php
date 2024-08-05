<?php

// quantity input colors
$has_custom_quantity = blocksy_get_theme_mod('has_custom_quantity', 'yes');

if ($has_custom_quantity === 'yes') {
	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('global_quantity_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.quantity',
				'variable' => 'quantity-initial-color'
			],

			'hover' => [
				'selector' => '.quantity',
				'variable' => 'quantity-hover-color'
			],
		],
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('global_quantity_arrows'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'default_type_2' => [ 'color' => 'var(--theme-text-color)' ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.quantity[data-type="type-1"]',
				'variable' => 'quantity-arrows-initial-color'
			],

			'default_type_2' => [
				'selector' => '.quantity[data-type="type-2"]',
				'variable' => 'quantity-arrows-initial-color'
			],

			'hover' => [
				'selector' => '.quantity',
				'variable' => 'quantity-arrows-hover-color'
			],
		],
	]);
}


// sale badge
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('saleBadgeColor'),
	'default' => [
		'text' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'background' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'text' => [
			'selector' => ':root',
			'variable' => 'badge-text-color'
		],

		'background' => [
			'selector' => ':root',
			'variable' => 'badge-background-color'
		],
	],
]);


// out of stock badge
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('outOfStockBadgeColor'),
	'default' => [
		'text' => [ 'color' => '#ffffff' ],
		'background' => [ 'color' => '#24292E' ],
	],
	'css' => $css,
	'variables' => [
		'text' => [
			'selector' => '.out-of-stock-badge',
			'variable' => 'badge-text-color'
		],

		'background' => [
			'selector' => '.out-of-stock-badge',
			'variable' => 'badge-background-color'
		],
	],
]);


// store notice
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('wooNoticeContent'),
	'default' => [
		'default' => ['color' => '#ffffff']
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.demo_store',
			'variable' => 'theme-text-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('wooNoticeBackground'),
	'default' => [
		'default' => ['color' => 'var(--theme-palette-color-1)']
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.demo_store',
			'variable' => 'background-color'
		],
	],
]);


// success message
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('success_message_text_color'),
	'default' => [
		'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
		'hover' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-message',
			'variable' => 'theme-text-color'
		],

		'hover' => [
			'selector' => '.woocommerce-message',
			'variable' => 'theme-link-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('success_message_background_color'),
	'default' => [
		'default' => ['color' => '#F0F1F3'],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-message',
			'variable' => 'background-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('success_message_button_text_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-message',
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-message',
			'variable' => 'theme-button-text-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('success_message_button_background'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-message',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-message',
			'variable' => 'theme-button-background-hover-color'
		],
	],
]);


// info message
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('info_message_text_color'),
	'default' => [
		'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
		'hover' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-info, .woocommerce-thankyou-order-received',
			'variable' => 'theme-text-color'
		],

		'hover' => [
			'selector' => '.woocommerce-info, .woocommerce-thankyou-order-received',
			'variable' => 'theme-link-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('info_message_background_color'),
	'default' => [
		'default' => ['color' => '#F0F1F3'],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-info, .woocommerce-thankyou-order-received',
			'variable' => 'background-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('info_message_button_text_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-info',
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-info',
			'variable' => 'theme-button-text-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('info_message_button_background'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-info',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-info',
			'variable' => 'theme-button-background-hover-color'
		],
	],
]);


// error message
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('error_message_text_color'),
	'default' => [
		'default' => ['color' => '#ffffff'],
		'hover' => ['color' => '#ffffff'],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-error',
			'variable' => 'theme-text-color'
		],

		'hover' => [
			'selector' => '.woocommerce-error',
			'variable' => 'theme-link-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('error_message_background_color'),
	'default' => [
		'default' => ['color' => 'rgba(218, 0, 28, 0.7)'],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-error',
			'variable' => 'background-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('error_message_button_text_color'),
	'default' => [
		'default' => [ 'color' => '#ffffff' ],
		'hover' => [ 'color' => '#ffffff' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-error',
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-error',
			'variable' => 'theme-button-text-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('error_message_button_background'),
	'default' => [
		'default' => [ 'color' => '#b92c3e' ],
		'hover' => [ 'color' => '#9c2131' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-error',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-error',
			'variable' => 'theme-button-background-hover-color'
		],
	],
]);


// account page
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('account_nav_text_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
		'active' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.ct-acount-nav',
			'variable' => 'account-nav-text-initial-color'
		],

		'active' => [
			'selector' => '.ct-acount-nav',
			'variable' => 'account-nav-text-active-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('account_nav_background_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
		'active' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.ct-acount-nav',
			'variable' => 'account-nav-background-initial-color'
		],

		'active' => [
			'selector' => '.ct-acount-nav',
			'variable' => 'account-nav-background-active-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('account_nav_divider_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.ct-acount-nav',
			'variable' => 'account-nav-divider-color'
		],
	],
]);

blocksy_output_box_shadow([
	'css' => $css,
	'selector' => '.ct-acount-nav',
	'value' => blocksy_get_theme_mod('account_nav_shadow', blocksy_box_shadow_value([
		'enable' => false,
		'h_offset' => 0,
		'v_offset' => 10,
		'blur' => 20,
		'spread' => 0,
		'inset' => false,
		'color' => [
			'color' => 'rgba(0, 0, 0, 0.03)',
		],
	])),
]);
