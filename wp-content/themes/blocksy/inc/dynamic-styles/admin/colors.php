<?php

if (! isset($selector)) {
    $selector = ':root';
}

if (! isset($only_palette)) {
    $only_palette = false;
}

// WP Color palette
$current_color = get_user_option('admin_color');

global $_wp_admin_css_colors;

if (
	$_wp_admin_css_colors
	&&
	$current_color
	&&
	isset($_wp_admin_css_colors[$current_color])
) {
	$colors = $_wp_admin_css_colors[$current_color]->colors;


	if (! empty($colors)) {
		$ui_accent_color = $colors[count($colors) - 1];

		if (count($colors) > 2) {
			$ui_accent_color = $colors[2];
		}

		if ($current_color === 'light') {
			$ui_accent_color = $colors[3];
		}

		if ($current_color === 'modern') {
			$ui_accent_color = $colors[1];
		}

		if ($current_color === 'blue') {
			$ui_accent_color = $colors[1];
		}

		if ($current_color === 'midnight') {
			$ui_accent_color = $colors[3];
		}

		$css->put(
			$selector,
			'--ui-accent-color: ' . $ui_accent_color
		);

		$css->put(
			$selector,
			'--ui-accent-hover-color: ' . blocksy_adjust_color_lightness(
				$ui_accent_color,
				-0.15
			)
		);
	}
}

// Color palette
foreach (blocksy_manager()->colors->get_color_palette() as $paletteKey => $paletteValue) {
	$css->put(
		$selector,
		"--" . $paletteValue['variable'] . ": {$paletteValue['color']}"
	);
}

// body font color
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('fontColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-3)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'variable' => 'theme-text-color',
			'selector' => $selector
		],
	],
]);

// link color
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('linkColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-1)' ],
		'hover' => [ 'color' => 'var(--theme-palette-color-2)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'variable' => 'theme-link-initial-color',
			'selector' => $selector
		],
		'hover' => [
			'variable' => 'theme-link-hover-color',
			'selector' => $selector
		],
	],
]);

if ($only_palette) {
	return;
}



// border color
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('border_color'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-5)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'variable' => 'theme-border-color',
			'selector' => $selector
		],
	],
]);


// headins
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('headingColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-4)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'variable' => 'theme-headings-color',
			'selector' => $selector
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('heading_1_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => $selector,
			'variable' => 'theme-heading-1-color'
		],
	]
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('heading_2_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => $selector,
			'variable' => 'theme-heading-2-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('heading_3_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => $selector,
			'variable' => 'theme-heading-3-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('heading_4_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => $selector,
			'variable' => 'theme-heading-4-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('heading_5_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => $selector,
			'variable' => 'theme-heading-5-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('heading_6_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => $selector,
			'variable' => 'theme-heading-6-color'
		],
	],
]);


// forms
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('formTextColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'focus' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => $selector,
			'variable' => 'theme-form-text-initial-color'
		],

		'focus' => [
			'selector' => $selector,
			'variable' => 'theme-form-text-focus-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('formBorderColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-border-color)' ],
		'focus' => [ 'color' => 'var(--theme-palette-color-1)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => $selector,
			'variable' => 'theme-form-field-border-initial-color'
		],

		'focus' => [
			'selector' => $selector,
			'variable' => 'theme-form-field-border-focus-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('formBackgroundColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
		'focus' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => $selector,
			'variable' => 'theme-form-field-background-initial-color'
		],

		'focus' => [
			'selector' => $selector,
			'variable' => 'theme-form-field-background-focus-color'
		],
	],
]);


// buttons
$buttonTextColor = blocksy_get_colors( blocksy_get_theme_mod('buttonTextColor'),
	[
		'default' => [ 'color' => '#ffffff' ],
		'hover' => [ 'color' => '#ffffff' ],
	]
);

$css->put(
	$selector,
	"--theme-button-text-initial-color: {$buttonTextColor['default']}"
);

$css->put(
	$selector,
	"--theme-button-text-hover-color: {$buttonTextColor['hover']}"
);

$button_color = blocksy_get_colors( blocksy_get_theme_mod('buttonColor'),
	[
		'default' => [ 'color' => 'var(--theme-palette-color-1)' ],
		'hover' => [ 'color' => 'var(--theme-palette-color-2)' ],
	]
);

$css->put(
	$selector,
	"--theme-button-background-initial-color: {$button_color['default']}"
);

$css->put(
	$selector,
	"--theme-button-background-hover-color: {$button_color['hover']}"
);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('global_quantity_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => $selector,
			'variable' => 'quantity-initial-color'
		],

		'hover' => [
			'selector' => $selector,
			'variable' => 'quantity-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('global_quantity_arrows'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => $selector,
			'variable' => 'quantity-arrows-initial-color'
		],

		'hover' => [
			'selector' => $selector,
			'variable' => 'quantity-arrows-hover-color'
		],
	],
]);
