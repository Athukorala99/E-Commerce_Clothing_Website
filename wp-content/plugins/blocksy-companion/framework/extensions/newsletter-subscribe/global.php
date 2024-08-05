<?php

$forms_type =  blocksy_get_theme_mod('forms_type', 'classic-forms');

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('newsletter_subscribe_title_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.ct-newsletter-subscribe-container',
			'variable' => 'theme-heading-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('newsletter_subscribe_content'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.ct-newsletter-subscribe-container',
			'variable' => 'text-color'
		],

		'hover' => [
			'selector' => '.ct-newsletter-subscribe-container',
			'variable' => 'theme-link-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('newsletter_subscribe_button'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-1)' ],
		'hover' => [ 'color' => 'var(--theme-palette-color-2)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.ct-newsletter-subscribe-container',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '.ct-newsletter-subscribe-container',
			'variable' => 'theme-button-background-hover-color'
		]
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('newsletter_subscribe_input_font_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'focus' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.ct-newsletter-subscribe-container',
			'variable' => 'theme-form-text-initial-color'
		],

		'focus' => [
			'selector' => '.ct-newsletter-subscribe-container',
			'variable' => 'theme-form-text-focus-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('newsletter_subscribe_border_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'focus' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.ct-newsletter-subscribe-container',
			'variable' => 'theme-form-field-border-initial-color'
		],

		'focus' => [
			'selector' => '.ct-newsletter-subscribe-container',
			'variable' => 'theme-form-field-border-focus-color'
		],
	],
]);

if ($forms_type !== 'classic-forms' || is_customize_preview()) {
	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('newsletter_subscribe_input_background'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
			'focus' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-newsletter-subscribe-container',
				'variable' => 'theme-form-field-background-initial-color'
			],

			'focus' => [
				'selector' => '.ct-newsletter-subscribe-container',
				'variable' => 'theme-form-field-background-focus-color'
			],
		],
	]);
}

blocksy_output_background_css([
	'selector' => '.ct-newsletter-subscribe-container',
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'value' => blocksy_get_theme_mod(
		'newsletter_subscribe_container_background',
		blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => 'var(--theme-palette-color-8)'
				],
			],
		])
	),
	'responsive' => true,
]);

blocksy_output_border([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-newsletter-subscribe-container',
	'variableName' => 'newsletter-container-border',
	'value' => blocksy_get_theme_mod('newsletter_subscribe_container_border'),
	'skip_none' => true,
	'default' => [
		'width' => 1,
		'style' => 'none',
		'color' => [
			'color' => 'var(--theme-palette-color-5)',
		],
	],
	'responsive' => true,
	'skip_none' => true
]);

blocksy_output_box_shadow([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-newsletter-subscribe-container',
	'value' => blocksy_get_theme_mod(
		'newsletter_subscribe_shadow',
		blocksy_box_shadow_value([
			'enable' => true,
			'h_offset' => 0,
			'v_offset' => 50,
			'blur' => 90,
			'spread' => 0,
			'inset' => false,
			'color' => [
				'color' => 'rgba(210, 213, 218, 0.4)',
			],
		])
	),
	'responsive' => true
]);

blocksy_output_spacing([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-newsletter-subscribe-container',
	'property' => 'padding',
	'value' => blocksy_get_theme_mod(
		'newsletter_subscribe_container_spacing',
		blocksy_spacing_value([
			'top' => '30px',
			'left' => '30px',
			'right' => '30px',
			'bottom' => '30px',
		])
	)
]);

blocksy_output_spacing([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-newsletter-subscribe-container',
	'property' => 'theme-border-radius',
	'value' => blocksy_get_theme_mod(
		'newsletter_subscribe_container_border_radius',
		blocksy_spacing_value()
	)
]);
