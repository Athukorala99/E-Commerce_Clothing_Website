<?php

// Site background
blocksy_output_background_css([
	'selector' => 'body',
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'value' => blocksy_get_theme_mod(
		'site_background',
		blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => 'var(--theme-palette-color-7)'
				],
			],
		])
	),
	'responsive' => true,
	'forced_background_image' => true
]);

