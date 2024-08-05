<?php

if (! isset($selector)) {
	$selector = ':root';
}

$max_site_width = blocksy_get_theme_mod( 'maxSiteWidth', 1290 );
$css->put(
	':root',
	'--theme-normal-container-max-width: ' . $max_site_width . 'px'
);

$narrowContainerWidth = blocksy_get_theme_mod( 'narrowContainerWidth', 750 );
$css->put(
	':root',
	'--theme-narrow-container-max-width: ' . $narrowContainerWidth . 'px'
);

$wideOffset = blocksy_get_theme_mod( 'wideOffset', 130 );
$css->put(
	':root',
	'--theme-wide-offset: ' . $wideOffset . 'px'
);

$contentSpacingMap = [
	'none' => '0px',
	'compact' => '0.8em',
	'comfortable' => '1.5em',
	'spacious' => '2em',
];

$contentSpacing = blocksy_get_theme_mod('contentSpacing', 'comfortable');

$contentSpacingResult = isset(
	$contentSpacingMap[$contentSpacing]
) ? $contentSpacingMap[$contentSpacing] : $contentSpacingMap['comfortable'];

$css->put(':root', '--theme-content-spacing: ' . $contentSpacingResult);

if ($contentSpacing === 'none') {
	$css->put(':root', '--has-theme-content-spacing: 0');
}


blocksy_theme_get_dynamic_styles([
	'name' => 'admin/colors',
	'css' => $css,
	'mobile_css' => $mobile_css,
	'tablet_css' => $tablet_css,
	'context' => $context,
	'chunk' => 'admin',
	'selector' => $selector
]);

if (
	function_exists('get_current_screen')
	&&
	get_current_screen()
	&&
	get_current_screen()->is_block_editor()
) {
	if (get_current_screen()->base === 'post') {
		blocksy_theme_get_dynamic_styles([
			'name' => 'admin/editor',
			'css' => $css,
			'mobile_css' => $mobile_css,
			'tablet_css' => $tablet_css,
			'context' => $context,
			'chunk' => 'admin'
		]);
	}

	blocksy_theme_get_dynamic_styles([
		'name' => 'global/typography',
		'css' => $css,
		'mobile_css' => $mobile_css,
		'tablet_css' => $tablet_css,
		'context' => 'inline',
		'chunk' => 'admin'
	]);

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => ':root',
		'variableName' => 'theme-button-min-height',
		'value' => blocksy_get_theme_mod('buttonMinHeight', 40)
	]);

	blocksy_output_border([
		'css' => $css,
		'selector' => ':root',
		'variableName' => 'button-border',
		'secondColorVariableName' => 'button-border-hover-color',
		'value' => get_theme_mod('buttonBorder'),
		'default' => [
			'width' => 1,
			'style' => 'none',
			'color' => [
				'color' => 'rgba(224, 229, 235, 0.5)',
			],
			'secondColor' => [
				'color' => 'rgba(224, 229, 235, 0.7)',
			]
		]
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => ':root',
		'property' => 'theme-button-border-radius',
		'value' => blocksy_get_theme_mod( 'buttonRadius',
			blocksy_spacing_value([
				'top' => '3px',
				'left' => '3px',
				'right' => '3px',
				'bottom' => '3px',
			])
		)
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => ':root',
		'property' => 'theme-button-padding',
		'value' => blocksy_get_theme_mod( 'buttonPadding',
			blocksy_spacing_value([
				'top' => '5px',
				'left' => '20px',
				'right' => '20px',
				'bottom' => '5px',
			])
		)
	]);
}

$post_id = null;

if (isset($_GET['post']) && $_GET['post']) {
	$post_id = $_GET['post'];
}

if ($post_id) {
	$post_atts = blocksy_get_post_options($post_id);

	$template_type = get_post_meta($post_id, 'template_type', true);
	$template_subtype = blocksy_akg('template_subtype', $post_atts, 'card');

	if ($template_type === 'archive' && $template_subtype === 'card') {
		$source = [
			'strategy' => $post_atts
		];

		$template_editor_width_source = blocksy_akg_or_customizer(
			'template_editor_width_source',
			$source,
			'small'
		);

		$template_editor_width = blocksy_akg_or_customizer(
			'template_editor_width',
			$source,
			'1290'
		);

		if ($template_editor_width_source === 'small') {
			$template_editor_width = 500;
		}

		if ($template_editor_width_source === 'medium') {
			$template_editor_width = 900;
		}

		$css->put(
			':root',
			'--theme-block-max-width: ' . $template_editor_width . 'px !important'
		);
	}
}
