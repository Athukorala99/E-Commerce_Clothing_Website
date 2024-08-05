<?php

$forms_type = blocksy_get_theme_mod('forms_type', 'classic-forms');

if ($forms_type === 'classic-forms') {
	$css->put(
		':root',
		'--has-classic-forms: var(--true)'
	);

	$css->put(
		':root',
		'--has-modern-forms: var(--false)'
	);
} else {
	$css->put(
		':root',
		'--has-classic-forms: var(--false)'
	);

	$css->put(
		':root',
		'--has-modern-forms: var(--true)'
	);
}

// general
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('formTextColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'focus' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => ':root',
			'variable' => 'theme-form-text-initial-color'
		],

		'focus' => [
			'selector' => ':root',
			'variable' => 'theme-form-text-focus-color'
		],
	],
]);

$formFontSize = blocksy_get_theme_mod('formFontSize', 16);

if ($formFontSize !== 16) {
	$css->put(':root', '--theme-form-font-size: ' . $formFontSize . 'px');
}

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('formBackgroundColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
		'focus' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => ':root',
			'variable' => 'theme-form-field-background-initial-color'
		],

		'focus' => [
			'selector' => ':root',
			'variable' => 'theme-form-field-background-focus-color'
		],
	],
]);

$formInputHeight = blocksy_get_theme_mod( 'formInputHeight', 40 );

if ($formInputHeight !== 40) {
	$css->put( ':root', '--theme-form-field-height: ' . $formInputHeight . 'px' );
}


$formTextAreaHeight = blocksy_get_theme_mod( 'formTextAreaHeight', 170 );
$css->put( 'form textarea', '--theme-form-field-height: ' . $formTextAreaHeight . 'px' );


$formFieldBorderRadius = blocksy_get_theme_mod( 'formFieldBorderRadius', 3 );

if ($formFieldBorderRadius !== 3) {
	$css->put( ':root', '--theme-form-field-border-radius: ' . $formFieldBorderRadius . 'px' );
}


blocksy_output_colors([
	'value' => blocksy_get_theme_mod('formBorderColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-border-color)' ],
		'focus' => [ 'color' => 'var(--theme-palette-color-1)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => ':root',
			'variable' => 'theme-form-field-border-initial-color'
		],

		'focus' => [
			'selector' => ':root',
			'variable' => 'theme-form-field-border-focus-color'
		],
	],
]);

$formBorderSize = blocksy_get_theme_mod( 'formBorderSize', 1 );


if ($forms_type === 'classic-forms') {
	if($formBorderSize !== 1) {
		$css->put(
			':root',
			'--theme-form-field-border-width: ' . $formBorderSize . 'px'
		);
	}
} else {
	$css->put(
		':root',
		'--theme-form-field-border-width: 0 0 ' . $formBorderSize . 'px 0'
	);

	$css->put(
		':root',
		'--form-selection-control-border-width: ' . $formBorderSize . 'px'
	);
}

// dropdown select
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('formSelectFontColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'active' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => ':root',
			'variable' => 'form-field-select-initial-color'
		],

		'active' => [
			'selector' => ':root',
			'variable' => 'form-field-select-active-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('formSelectBackgroundColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
		'active' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => ':root',
			'variable' => 'theme-form-select-background-initial-color'
		],

		'active' => [
			'selector' => ':root',
			'variable' => 'theme-form-select-background-active-color'
		],
	],
]);

// radio & checkbox
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('radioCheckboxColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-border-color)' ],
		'accent' => [ 'color' => 'var(--theme-palette-color-1)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => ':root',
			'variable' => 'theme-form-selection-field-initial-color'
		],

		'accent' => [
			'selector' => ':root',
			'variable' => 'theme-form-selection-field-active-color'
		],
	],
]);

$checkboxBorderRadius = blocksy_get_theme_mod('checkboxBorderRadius', 3);

if ($checkboxBorderRadius !== 3) {
	$css->put( ':root', '--theme-form-checkbox-border-radius: ' . $checkboxBorderRadius . 'px' );
}
