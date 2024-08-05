<?php

if (! isset($only_background)) {
	$only_background = false;
}

$post_type = get_current_screen()->post_type;

$post_id = null;

if (isset($_GET['post']) && $_GET['post']) {
	$post_id = $_GET['post'];
}

$prefix = blocksy_manager()->screen->get_admin_prefix($post_type);

$post_atts = blocksy_get_post_options($post_id);

$page_structure = blocksy_default_akg(
	'page_structure_type',
	$post_atts,
	'default'
);

if ($page_structure === 'default') {
	$page_structure = blocksy_get_theme_mod(
		$prefix . '_structure',
		($prefix === 'single_blog_post') ? 'type-3' : 'type-4'
	);
}

if ($post_type === 'ct_content_block') {
	$page_structure = blocksy_default_akg(
		'content_block_structure',
		$post_atts,
		'type-4'
	);
}

if (! $only_background) {
	if ($page_structure === 'type-4') {
		$css->put(
			':root',
			'--theme-block-max-width: var(--theme-normal-container-max-width)'
		);

		$css->put(
			':root',
			'--theme-block-wide-max-width: calc(var(--theme-normal-container-max-width) + var(--theme-wide-offset) * 2)'
		);
	} else {
		$css->put(
			':root',
			'--theme-block-max-width: var(--theme-narrow-container-max-width)'
		);

		$css->put(
			':root',
			'--theme-block-wide-max-width: calc(var(--theme-narrow-container-max-width) + var(--theme-wide-offset) * 2)'
		);
	}
}

$source = [
	'strategy' => $post_atts
];

if (blocksy_default_akg(
	'content_style_source',
	$post_atts,
	'inherit'
) === 'inherit' && $post_type !== 'ct_content_block') {
	$source = [
		'prefix' => $prefix,
		'strategy' => 'customizer'
	];
}

$has_boxed = blocksy_akg_or_customizer(
	'content_style',
	$source,
	blocksy_get_content_style_default($prefix)
);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => ":root",
	'variableName' => 'has-boxed',
	'value' => blocksy_map_values([
		'value' => $has_boxed,
		'map' => [
			'boxed' => 'var(--true)',
			'wide' => 'var(--false)'
		]
	]),
	'unit' => ''
]);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => ":root",
	'variableName' => 'has-wide',
	'value' => blocksy_map_values([
		'value' => $has_boxed,
		'map' => [
			'wide' => 'var(--true)',
			'boxed' => 'var(--false)'
		]
	]),
	'unit' => ''
]);

if ($post_type === 'ct_content_block') {
	$template_type = get_post_meta($post_id, 'template_type', true);

	$default_content_block_structure = 'yes';

	if ($template_type === 'hook' || $template_type === 'popup') {
		$default_content_block_structure = 'no';
	}

	$has_content_block_structure = blocksy_akg(
		'has_content_block_structure',
		$post_atts,
		$default_content_block_structure
	);

	if ($has_content_block_structure !== 'yes') {
		return;
	}
}

$background_source = blocksy_default_akg(
	'background',
	$post_atts,
	blocksy_background_default_value([
		'backgroundColor' => [
			'default' => [
				'color' => Blocksy_Css_Injector::get_skip_rule_keyword()
			],
		],
	])
);

if ($post_type === 'ct_content_block') {
	$template_type = get_post_meta($post_id, 'template_type', true);

	if ($template_type === 'popup') {
		$background_source = blocksy_default_akg(
			'popup_background',
			$post_atts,
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => '#ffffff'
					],
				],
			])
		);
	}
}

if (
	isset($background_source['background_type'])
	&&
	$background_source['background_type'] === 'color'
	&&
	isset($background_source['backgroundColor']['default']['color'])
	&&
	$background_source['backgroundColor']['default']['color'] === Blocksy_Css_Injector::get_skip_rule_keyword()
) {
	$background_source = blocksy_get_theme_mod(
		$prefix . '_background',
		blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => Blocksy_Css_Injector::get_skip_rule_keyword()
				],
			],
		])
	);

	if (
		isset($background_source['background_type'])
		&&
		$background_source['background_type'] === 'color'
		&&
		isset($background_source['backgroundColor']['default']['color'])
		&&
		$background_source['backgroundColor']['default']['color'] === Blocksy_Css_Injector::get_skip_rule_keyword()
	) {
		$background_source = blocksy_get_theme_mod(
			'site_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'var(--theme-palette-color-7)'
					],
				],
			])
		);
	}
}

$background_source = blocksy_expand_responsive_value($background_source);

blocksy_output_background_css([
	'selector' => '.edit-post-visual-editor__content-area > .is-desktop-preview',
	'css' => $css,
	'value' => $background_source['desktop'],
	'responsive' => false,
	'important' => true
]);

blocksy_output_background_css([
	'selector' => '.edit-post-visual-editor__content-area > .is-tablet-preview',
	'css' => $css,
	'value' => $background_source['tablet'],
	'responsive' => false,
	'important' => true
]);

blocksy_output_background_css([
	'selector' => '.edit-post-visual-editor__content-area > .is-mobile-preview',
	'css' => $css,
	'value' => $background_source['mobile'],
	'responsive' => false,
	'important' => true
]);

if ($only_background) {
	return;
}


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



if (blocksy_some_device($has_boxed, 'boxed')) {
	blocksy_output_background_css([
		'selector' => ':root',
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'value' => blocksy_akg_or_customizer(
			'content_background',
			$source,
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'var(--theme-palette-color-8)'
					],
				],
			])
		),
		'responsive' => true,
		'conditional_var' => '--has-boxed'
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => ':root',
		'property' => 'theme-boxed-content-border-radius',
		'value' => blocksy_akg_or_customizer(
			'content_boxed_radius',
			$source,
			blocksy_spacing_value([
				'top' => '3px',
				'left' => '3px',
				'right' => '3px',
				'bottom' => '3px',
			])
		)
	]);

	blocksy_output_border([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => ':root',
		'variableName' => 'theme-boxed-content-border',
		'value' => blocksy_akg_or_customizer(
			'content_boxed_border',
			$source,
			[
				'width' => 1,
				'style' => 'none',
				'color' => [
					'color' => 'rgba(44,62,80,0.2)',
				],
			]
		),
		'default' => [
			'width' => 1,
			'style' => 'none',
			'color' => [
				'color' => 'rgba(44,62,80,0.2)',
			],
		],
		'responsive' => true,
		'skip_none' => true
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => ':root',
		'property' => 'theme-boxed-content-spacing',
		'value' => blocksy_akg_or_customizer(
			'boxed_content_spacing',
			$source,
			[
				'desktop' => blocksy_spacing_value([
					'top' => '40px',
					'left' => '40px',
					'right' => '40px',
					'bottom' => '40px',
				]),
				'tablet' => blocksy_spacing_value([
					'top' => '35px',
					'left' => '35px',
					'right' => '35px',
					'bottom' => '35px',
				]),
				'mobile'=> blocksy_spacing_value([
					'top' => '20px',
					'left' => '20px',
					'right' => '20px',
					'bottom' => '20px',
				]),
			]
		)
	]);

	blocksy_output_box_shadow([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => ':root',
		'variableName' => 'theme-boxed-content-box-shadow',
		'value' => blocksy_akg_or_customizer(
			'content_boxed_shadow',
			$source,
			blocksy_box_shadow_value([
				'enable' => true,
				'h_offset' => 0,
				'v_offset' => 12,
				'blur' => 18,
				'spread' => -6,
				'inset' => false,
				'color' => [
					'color' => 'rgba(34, 56, 101, 0.04)',
				],
			])
		),
		'responsive' => true
	]);
} else {
	$css->put(
		':root',
		'background-color: transparent'
	);
}

