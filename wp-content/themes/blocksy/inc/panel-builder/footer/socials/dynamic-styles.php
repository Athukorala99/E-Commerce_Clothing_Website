<?php

if (! function_exists('blocksy_assemble_selector')) {
	return;
}

// Icon size
$socialsIconSize = blocksy_akg( 'socialsIconSize', $atts, 15 );

if ($socialsIconSize !== 15) {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector($root_selector),
		'variableName' => 'theme-icon-size',
		'value' => $socialsIconSize,
		'responsive' => true
	]);
}

// Icon spacing
$socialsIconSpacing = blocksy_akg( 'socialsIconSpacing', $atts, 15 );

if ($socialsIconSpacing !== 15) {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector($root_selector),
		'variableName' => 'items-spacing',
		'value' => $socialsIconSpacing,
		'responsive' => true
	]);
}


// Horizontal alignment
blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
		'selector' => $root_selector,
		'operation' => 'replace-last',
		'to_add' => $column_selector
	])),
	'variableName' => 'horizontal-alignment',
	'value' => blocksy_akg( 'footerSocialsAlignment', $atts, 'CT_CSS_SKIP_RULE' ),
	'unit' => '',
]);


// Vertical alignment
blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
		'selector' => $root_selector,
		'operation' => 'replace-last',
		'to_add' => $column_selector
	])),
	'variableName' => 'vertical-alignment',
	'value' => blocksy_akg( 'footerSocialsVerticalAlignment', $atts, 'CT_CSS_SKIP_RULE' ),
	'unit' => '',
]);


// label font & color
$has_label = (
	is_customize_preview()
	||
	blocksy_some_device(blocksy_default_akg(
		'socialsLabelVisibility',
		$atts,
		[
			'desktop' => false,
			'tablet' => false,
			'mobile' => false,
		]
	))
);

if ($has_label) {
	blocksy_output_font_css([
		'font_value' => blocksy_akg( 'footer_socials_label_font', $atts,
			blocksy_typography_default_values([
				'size' => '12px',
				'variation' => 'n6',
				'text-transform' => 'uppercase',
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector(
			blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '.ct-label'
			])
		)
	]);

	blocksy_output_colors([
		'value' => blocksy_akg('footer_socials_font_color', $atts),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'variables' => [
			'default' => [
				'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
					'selector' => $root_selector,
					'operation' => 'suffix',
					'to_add' => 'a'
				])),
				'variable' => 'theme-link-initial-color'
			],

			'hover' => [
				'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
					'selector' => $root_selector,
					'operation' => 'suffix',
					'to_add' => 'a'
				])),
				'variable' => 'theme-link-hover-color'
			],
		],
		'responsive' => true
	]);
}


// Icons custom color
blocksy_output_colors([
	'value' => blocksy_akg('footerSocialsIconColor', $atts),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,

	'variables' => [
		'default' => [
			'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '[data-color="custom"]'
			])),
			'variable' => 'theme-icon-color'
		],

		'hover' => [
			'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '[data-color="custom"]'
			])),
			'variable' => 'theme-icon-hover-color'
		]
	],

	'responsive' => true
]);

// Icons custom background
blocksy_output_colors([
	'value' => blocksy_akg('footerSocialsIconBackground', $atts),
	'default' => [
		'default' => [ 'color' => 'rgba(218, 222, 228, 0.3)' ],
		'hover' => [ 'color' => 'var(--theme-palette-color-1)' ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,

	'variables' => [
		'default' => [
			'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '[data-color="custom"]'
			])),
			'variable' => 'background-color'
		],

		'hover' => [
			'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '[data-color="custom"]'
			])),
			'variable' => 'background-hover-color'
		]
	],

	'responsive' => true
]);

// Margin
blocksy_output_spacing([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_assemble_selector($root_selector),
	'important' => true,
	'value' => blocksy_default_akg(
		'footerSocialsMargin',
		$atts,
		blocksy_spacing_value()
	)
]);

if (function_exists('blocksy_output_responsive_switch')) {
	blocksy_output_responsive_switch([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
			'selector' => $root_selector,
			'operation' => 'suffix',
			'to_add' => '.ct-label'
		])),
		'value' => blocksy_default_akg(
			'socialsLabelVisibility',
			$atts,
			[
				'desktop' => false,
				'tablet' => false,
				'mobile' => false,
			]
		),
		'on' => 'block'
	]);
}

$items_direction = blocksy_akg('footer_socials_direction', $atts, 'row');

if ($items_direction === 'column') {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector($root_selector),
		'variableName' => 'items-direction',
		'value' => $items_direction,
		'unit' => '',
	]);
}




