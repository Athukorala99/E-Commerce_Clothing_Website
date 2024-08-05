<?php

if (! isset($selector)) {
	$selector = ':root';
}

blocksy_theme_get_dynamic_styles([
	'name' => 'admin/colors',
	'css' => $css,
	'mobile_css' => $mobile_css,
	'tablet_css' => $tablet_css,
	'context' => $context,
	'chunk' => 'admin',
	'selector' => $selector,
	'only_palette' => true
]);

if (get_current_screen()->base === 'post') {
	blocksy_theme_get_dynamic_styles([
		'name' => 'admin/editor',
		'css' => $css,
		'mobile_css' => $mobile_css,
		'tablet_css' => $tablet_css,
		'context' => $context,
		'chunk' => 'admin',
		'only_background' => true
	]);
}
