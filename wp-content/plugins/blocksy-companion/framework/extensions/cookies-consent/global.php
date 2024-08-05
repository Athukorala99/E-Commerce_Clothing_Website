<?php

// Content color
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('cookieContentColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.cookie-notification',
			'variable' => 'theme-text-color'
		],

		'hover' => [
			'selector' => '.cookie-notification',
			'variable' => 'theme-link-hover-color'
		],
	],
]);

// Accept button color
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('cookieButtonText'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.cookie-notification .ct-cookies-accept-button',
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => '.cookie-notification .ct-cookies-accept-button',
			'variable' => 'theme-button-text-hover-color'
		]
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('cookieButtonBackground'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.cookie-notification .ct-cookies-accept-button',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '.cookie-notification .ct-cookies-accept-button',
			'variable' => 'theme-button-background-hover-color'
		]
	],
]);

// Decline button color
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('cookieDeclineButtonText'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-3)' ],
		'hover' => [ 'color' => 'var(--theme-palette-color-3)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.cookie-notification .ct-cookies-decline-button',
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => '.cookie-notification .ct-cookies-decline-button',
			'variable' => 'theme-button-text-hover-color'
		]
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('cookieDeclineButtonBackground'),
	'default' => [
		'default' => [ 'color' => 'rgba(224, 229, 235, 0.6)' ],
		'hover' => [ 'color' => 'rgba(224, 229, 235, 1)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.cookie-notification .ct-cookies-decline-button',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '.cookie-notification .ct-cookies-decline-button',
			'variable' => 'theme-button-background-hover-color'
		]
	],
]);


// Background color
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('cookieBackground'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-8)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.cookie-notification',
			'variable' => 'backgroundColor'
		],
	],
]);

$cookieMaxWidth = blocksy_get_theme_mod( 'cookieMaxWidth', 400 );
$css->put(
	'.cookie-notification',
	'--maxWidth: ' . $cookieMaxWidth . 'px'
);

