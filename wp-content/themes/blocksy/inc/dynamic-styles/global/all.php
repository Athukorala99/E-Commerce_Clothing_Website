<?php

// Color palette
$paletteDefaults = [];
$paletteVariables = [];

$palette = blocksy_manager()->colors->get_color_palette();

foreach ($palette as $paletteKey => $paletteValue) {
	$paletteDefaults[$paletteKey] = [
		'color' => $paletteValue['color'],
	];

	$paletteVariables[$paletteKey] = [
		'variable' => $paletteValue['variable']
	];

	if (class_exists('\Elementor\Plugin')) {
		$key = 'blocksy_palette_' . str_replace('color', '', $paletteKey);

		$css->put(
			':root',
			'--e-global-color-' . $key . ': var(--' . $paletteValue['variable'] . ')'
		);
	}
}

blocksy_output_colors([
	'value' => $palette,
	'default' => $paletteDefaults,
	'css' => $css,
	'variables' => $paletteVariables
]);

// Colors
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('fontColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-3)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => ['variable' => 'theme-text-color'],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('linkColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-1)' ],
		'hover' => [ 'color' => 'var(--theme-palette-color-2)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => ['variable' => 'theme-link-initial-color'],
		'hover' => ['variable' => 'theme-link-hover-color'],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('selectionColor'),
	'default' => [
		'default' => [ 'color' => '#ffffff' ],
		'hover' => [ 'color' => 'var(--theme-palette-color-1)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => ['variable' => 'theme-selection-text-color'],
		'hover' => ['variable' => 'theme-selection-background-color'],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('border_color'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-5)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => ['variable' => 'theme-border-color'],
	],
]);


// Headings
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('headingColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-4)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => ':root',
			'variable' => 'theme-headings-color'
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
			'selector' => ':root',
			'variable' => 'theme-heading-1-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('heading_2_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => ':root',
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
			'selector' => ':root',
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
			'selector' => ':root',
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
			'selector' => ':root',
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
			'selector' => ':root',
			'variable' => 'theme-heading-6-color'
		],
	],
]);


// Content spacing
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

// Buttons
blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => ':root',
	'variableName' => 'theme-button-min-height',
	'value' => blocksy_get_theme_mod('buttonMinHeight', 40)
]);

if (blocksy_get_theme_mod('buttonHoverEffect', 'no') !== 'yes') {
	$css->put(':root', '--theme-button-shadow: none');
	$css->put(':root', '--theme-button-transform: none');
}

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('buttonTextColor'),
	'default' => [
		'default' => [ 'color' => '#ffffff' ],
		'hover' => [ 'color' => '#ffffff' ],
	],
	'css' => $css,
	'variables' => [
		'default' => ['variable' => 'theme-button-text-initial-color'],
		'hover' => ['variable' => 'theme-button-text-hover-color'],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('buttonColor'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-1)' ],
		'hover' => [ 'color' => 'var(--theme-palette-color-2)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => ['variable' => 'theme-button-background-initial-color'],
		'hover' => ['variable' => 'theme-button-background-hover-color'],
	],
]);

blocksy_output_border([
	'css' => $css,
	'selector' => ':root',
	'variableName' => 'theme-button-border',
	'secondColorVariableName' => 'theme-button-border-hover-color',
	'value' => blocksy_get_theme_mod('buttonBorder'),
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
	'value' => blocksy_get_theme_mod(
		'buttonPadding',
		blocksy_spacing_value([
			'top' => '5px',
			'left' => '20px',
			'right' => '20px',
			'bottom' => '5px',
		])
	)
]);


// Layout
$max_site_width = blocksy_get_theme_mod( 'maxSiteWidth', 1290 );
$css->put(
	':root',
	'--theme-normal-container-max-width: ' . $max_site_width . 'px'
);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => ':root',
	'variableName' => 'theme-content-vertical-spacing',
	'unit' => '',
	'value' => blocksy_get_theme_mod('contentAreaSpacing', [
		'desktop' => '60px',
		'tablet' => '60px',
		'mobile' => '50px',
	])
]);

$contentEdgeSpacing = blocksy_get_theme_mod('contentEdgeSpacing', [
	'desktop' => 5,
	'tablet' => 5,
	'mobile' => 6,
]);

$contentEdgeSpacing['desktop'] = 100 - intval($contentEdgeSpacing['desktop']) * 2;
$contentEdgeSpacing['tablet'] = 100 - intval($contentEdgeSpacing['tablet']) * 2;
$contentEdgeSpacing['mobile'] = 100 - intval($contentEdgeSpacing['mobile']) * 2;

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => ':root',
	'variableName' => 'theme-container-edge-spacing',
	'unit' => 'vw',
	'value' => $contentEdgeSpacing
]);

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

// sidebars
$sidebar_type = blocksy_get_theme_mod('sidebar_type', 'type-1');

// sidebar width
$sidebar_width = blocksy_get_theme_mod( 'sidebarWidth', 27 );
if ($sidebar_width !== 27) {
	$css->put(
		'[data-sidebar]',
		'--sidebar-width: ' . $sidebar_width . '%'
	);

	$css->put(
		'[data-sidebar]',
		'--sidebar-width-no-unit: ' . intval($sidebar_width)
	);
}

// sidebar gap
$sidebarGap = blocksy_get_with_percentage('sidebarGap', '4%');
if ($sidebarGap !== '4%') {
	$css->put(
		'[data-sidebar]',
		'--sidebar-gap: ' . $sidebarGap
	);
}


// sticky sidebar offset
$sidebarOffset = blocksy_get_theme_mod('sidebarOffset', 50);
if ($sidebarOffset !== 50) {
	$css->put(
		'[data-sidebar]',
		'--sidebar-offset: ' . $sidebarOffset . 'px'
	);
}

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('sidebarWidgetsTitleColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables' => [
		'default' => [
			'selector' => '.ct-sidebar .widget-title',
			'variable' => 'theme-heading-color'
		],
	],
	'responsive' => true
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('sidebarWidgetsFontColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'link_initial' => [ 'color' => 'var(--theme-text-color)' ],
		'link_hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables' => [
		'default' => [
			'selector' => '.ct-sidebar > *',
			'variable' => 'theme-text-color'
		],

		'link_initial' => [
			'selector' => '.ct-sidebar',
			'variable' => 'theme-link-initial-color'
		],

		'link_hover' => [
			'selector' => '.ct-sidebar',
			'variable' => 'theme-link-hover-color'
		],
	],
	'responsive' => true
]);

if (
	$sidebar_type === 'type-2'
	||
	$sidebar_type === 'type-4'
	||
	is_customize_preview()
) {
	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('sidebarBackgroundColor'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'variables' => [
			'default' => [
				'selector' => '[data-sidebar] > aside',
				'variable' => 'sidebar-background-color'
			],
		],
		'responsive' => true
	]);
}

// Sidebar border
if ($sidebar_type === 'type-2') {
	blocksy_output_border([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => 'aside[data-type="type-2"]',
		'variableName' => 'theme-border',
		'value' => blocksy_get_theme_mod('sidebarBorder'),
		'default' => [
			'width' => 1,
			'style' => 'none',
			'color' => [
				'color' => 'rgba(224, 229, 235, 0.8)',
			],
		],
		'responsive' => true
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => 'aside[data-type="type-2"]',
		'property' => 'theme-border-radius',
		'value' => blocksy_get_theme_mod(
			'sidebarRadius',
			blocksy_spacing_value()
		)
	]);

	// Sidebar shadow
	blocksy_output_box_shadow([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => 'aside[data-type="type-2"]',
		'value' => blocksy_get_theme_mod('sidebarShadow', blocksy_box_shadow_value([
			'enable' => true,
			'h_offset' => 0,
			'v_offset' => 12,
			'blur' => 18,
			'spread' => -6,
			'inset' => false,
			'color' => [
				'color' => 'rgba(34, 56, 101, 0.04)',
			],
		])),
		'responsive' => true
	]);
}

if ($sidebar_type === 'type-3') {
	blocksy_output_border([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => 'aside[data-type="type-3"]',
		'variableName' => 'theme-border',
		'value' => blocksy_get_theme_mod('sidebarDivider'),
		'default' => [
			'width' => 1,
			'style' => 'solid',
			'color' => [
				'color' => 'rgba(224, 229, 235, 0.8)',
			],
		],
		'responsive' => true
	]);
}

$sidebarWidgetsSpacing = blocksy_get_theme_mod('sidebarWidgetsSpacing', 40);

if ($sidebarWidgetsSpacing !== 40) {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-sidebar',
		'variableName' => 'sidebar-widgets-spacing',
		'value' => $sidebarWidgetsSpacing
	]);
}

$sidebarInnerSpacing = blocksy_get_theme_mod('sidebarInnerSpacing', 35);

if ($sidebarInnerSpacing !== 35) {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => "[data-sidebar] > aside",
		'variableName' => 'sidebar-inner-spacing',
		'value' => $sidebarInnerSpacing,
	]);
}


// Mobile sidebar position
$sidebar_position = blocksy_get_theme_mod('mobile_sidebar_position', 'bottom');

if ($sidebar_position === 'top') {
	$mobile_css->put(
		':root',
		'--sidebar-order: -1'
	);

	$tablet_css->put(
		':root',
		'--sidebar-order: -1'
	);
}


// To top button
$has_back_top = blocksy_get_theme_mod('has_back_top', 'no');

if ($has_back_top === 'yes') {

	$topButtonSize = blocksy_get_theme_mod('topButtonSize', 12);

	if ($topButtonSize !== 12) {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '.ct-back-to-top .ct-icon',
			'variableName' => 'theme-icon-size',
			'value' => $topButtonSize
		]);
	}

	$topButtonOffset = blocksy_get_theme_mod('topButtonOffset', 25);

	if ($topButtonOffset !== 25) {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '.ct-back-to-top',
			'variableName' => 'back-top-bottom-offset',
			'value' => $topButtonOffset
		]);
	}

	$sideButtonOffset = blocksy_get_theme_mod('sideButtonOffset', 25);

	if ($sideButtonOffset !== 25) {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '.ct-back-to-top',
			'variableName' => 'back-top-side-offset',
			'value' => $sideButtonOffset
		]);
	}

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('topButtonIconColor'),
		'default' => [
			'default' => [ 'color' => '#ffffff' ],
			'hover' => [ 'color' => '#ffffff' ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-back-to-top',
				'variable' => 'theme-icon-color'
			],

			'hover' => [
				'selector' => '.ct-back-to-top',
				'variable' => 'theme-icon-hover-color'
			]
		],
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('topButtonShapeBackground'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-back-to-top',
				'variable' => 'top-button-background-color'
			],

			'hover' => [
				'selector' => '.ct-back-to-top',
				'variable' => 'top-button-background-hover-color'
			]
		],
	]);

	$topButtonSgape = blocksy_get_theme_mod('top_button_shape', 'square');

	if($topButtonSgape === 'square') {
		blocksy_output_spacing([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '.ct-back-to-top',
			'property' => 'theme-border-radius',
			'value' => blocksy_get_theme_mod(
				'topButtonRadius',
				blocksy_spacing_value([
					'top' => '2px',
					'left' => '2px',
					'right' => '2px',
					'bottom' => '2px',
				])
			)
		]);
	}

	blocksy_output_box_shadow([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-back-to-top',
		'value' => blocksy_get_theme_mod('topButtonShadow', blocksy_box_shadow_value([
			'enable' => false,
			'h_offset' => 0,
			'v_offset' => 5,
			'blur' => 20,
			'spread' => 0,
			'inset' => false,
			'color' => [
				'color' => 'rgba(210, 213, 218, 0.2)',
			],
		])),
		'responsive' => true
	]);
}

// Passepartout
$has_passepartout = blocksy_get_theme_mod('has_passepartout', 'no');

if ($has_passepartout !== 'no') {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => ':root',
		'variableName' => 'theme-frame-size',
		'value' => blocksy_get_theme_mod('passepartoutSize', 10)
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('passepartoutColor'),
		'default' => [
			'default' => [ 'color' => 'var(--theme-palette-color-1)' ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => ':root',
				'variable' => 'theme-frame-color'
			],
		],
	]);
}


// breadcrumbs
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('breadcrumbsFontColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'initial' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.ct-breadcrumbs',
			'variable' => 'theme-text-color'
		],

		'initial' => [
			'selector' => '.ct-breadcrumbs',
			'variable' => 'theme-link-initial-color'
		],

		'hover' => [
			'selector' => '.ct-breadcrumbs',
			'variable' => 'theme-link-hover-color'
		],
	],
]);
