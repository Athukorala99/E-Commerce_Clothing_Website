<?php

blocksy_get_variables_from_file(
	get_template_directory() . '/inc/panel-builder/header/middle-row/dynamic-styles.php',
	[],
	[
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'atts' => $atts,
		'root_selector' => $root_selector,

		'default_height' => [
			'mobile' => 80,
			'tablet' => 80,
			'desktop' => 80,
		],

		'default_background' => blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => 'var(--theme-palette-color-8)',
				],
			],
		]),

		'has_transparent_header' => isset($has_transparent_header) ? $has_transparent_header : false,
		'has_sticky_header' => isset($has_sticky_header) ? $has_sticky_header : false
	]
);
