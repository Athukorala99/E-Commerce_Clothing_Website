<?php

$structure = blocksy_get_theme_mod($prefix . '_structure', 'grid');

if ($structure === 'grid') {
	$grid_columns = blocksy_expand_responsive_value(blocksy_get_theme_mod(
		$prefix . '_columns',
		[
			'desktop' => 3,
			'tablet' => 2,
			'mobile' => 1
		]
	));

	$columns_for_output = [
		'desktop' => 'repeat(' . $grid_columns['desktop'] . ', minmax(0, 1fr))',
		'tablet' => 'repeat(' . $grid_columns['tablet'] . ', minmax(0, 1fr))',
		'mobile' => 'repeat(' . $grid_columns['mobile'] . ', minmax(0, 1fr))'
	];

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.entries', $prefix),
		'variableName' => 'grid-template-columns',
		'value' => $columns_for_output,
		'unit' => ''
	]);
}

$card_type = blocksy_get_listing_card_type([
	'prefix' => $prefix
]);

blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod(
		$prefix . '_cardTitleFont',
		blocksy_typography_default_values([
			'size' => [
				'desktop' => '20px',
				'tablet'  => '20px',
				'mobile'  => '18px'
			],
			'line-height' => '1.3'
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_prefix_selector('.entry-card .entry-title', $prefix)
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod($prefix . '_cardTitleColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => blocksy_prefix_selector('.entry-card .entry-title', $prefix),
			'variable' => 'theme-heading-color'
		],
		'hover' => [
			'selector' => blocksy_prefix_selector('.entry-card .entry-title', $prefix),
			'variable' => 'theme-link-hover-color'
		],
	],
]);

blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod(
		$prefix . '_cardExcerptFont',
		blocksy_typography_default_values([])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_prefix_selector('.entry-excerpt', $prefix)
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod($prefix . '_cardExcerptColor'),
	'default' => [
		'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')]
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => blocksy_prefix_selector('.entry-excerpt', $prefix),
			'variable' => 'theme-text-color'
		]
	],
]);

blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod(
		$prefix . '_cardMetaFont',
		blocksy_typography_default_values([
			'size' => [
				'desktop' => '12px',
				'tablet'  => '12px',
				'mobile'  => '12px'
			],
			'variation' => 'n6',
			'text-transform' => 'uppercase',
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_prefix_selector('.entry-card .entry-meta', $prefix)
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod($prefix . '_cardMetaColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => blocksy_prefix_selector('.entry-card .entry-meta', $prefix),
			'variable' => 'theme-text-color'
		],

		'hover' => [
			'selector' => blocksy_prefix_selector('.entry-card .entry-meta', $prefix),
			'variable' => 'theme-link-hover-color'
		],
	],
]);


blocksy_output_colors([
	'value' => blocksy_get_theme_mod($prefix . '_card_meta_button_type_font_colors'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => blocksy_prefix_selector('.entry-card [data-type="pill"]', $prefix),
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => blocksy_prefix_selector('.entry-card [data-type="pill"]', $prefix),
			'variable' => 'theme-button-text-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod($prefix . '_card_meta_button_type_background_colors'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => blocksy_prefix_selector('.entry-card [data-type="pill"]', $prefix),
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => blocksy_prefix_selector('.entry-card [data-type="pill"]', $prefix),
			'variable' => 'theme-button-background-hover-color'
		],
	],
]);



// simple card
if ($card_type === 'simple') {
	blocksy_output_border([
		'css' => $css,
		'selector' => blocksy_prefix_selector('[data-cards="simple"] .entry-card', $prefix),
		'variableName' => 'card-border',
		'value' => blocksy_get_theme_mod($prefix . '_cardDivider'),
		'default' => [
			'width' => 1,
			'style' => 'dashed',
			'color' => [
				'color' => 'rgba(224, 229, 235, 0.8)',
			],
		],
	]);
}

// boxed card
if ($card_type === 'boxed' || $card_type === 'cover') {
	$card_spacing = blocksy_get_theme_mod($prefix . '_card_spacing', '30px');
	$card_spacing_expanded = blocksy_expand_responsive_value($card_spacing);

	if ($card_spacing !== '30px') {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.entry-card', $prefix),
			'variableName' => 'card-inner-spacing',
			'value' => $card_spacing,
			'unit' => '',
			'previousUnit' => 'px'
		]);
	}

	blocksy_output_background_css([
		'selector' => blocksy_prefix_selector('.entry-card', $prefix),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'value' => blocksy_get_theme_mod(
			$prefix . '_cardBackground',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'var(--theme-palette-color-8)'
					],
				],
			])
		),
		'responsive' => true,
	]);

	$cardBorder = blocksy_expand_responsive_value(
		blocksy_get_theme_mod($prefix . '_cardBorder')
	);

	blocksy_output_border([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.entry-card', $prefix),
		'variableName' => 'card-border',
		'value' => $cardBorder,
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

	// Border radius
	$cardRadius = blocksy_expand_responsive_value(blocksy_get_theme_mod(
		$prefix . '_cardRadius',
		blocksy_spacing_value()
	));

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.entry-card', $prefix),
		'property' => 'theme-border-radius',
		'value' => $cardRadius
	]);

	$archive_order = apply_filters(
		'blocksy:posts-listing:archive-order',
		blocksy_get_theme_mod(
			$prefix . '_archive_order',
			[]
		)
	);

	$featured_image_settings = null;

	foreach (array_reverse($archive_order) as $index => $value) {
		if ($value['id'] === 'featured_image') {
			$featured_image_settings = $value;
		}
	}

	$devices = ['desktop', 'tablet', 'mobile'];
	$should_apply = false;
	$is_boundles = blocksy_default_akg(
		'is_boundless',
		$featured_image_settings,
		'yes'
	);


	$image_border_radius = [];

	foreach ($devices as $device) {
		$image_border_radius[$device] = blocksy_spacing_prepare_for_device(
			$cardRadius[$device],
			[
				'format' => 'array'
			]
		);

		$maybeWidth = 0;

		if (
			$cardBorder[$device]
			&&
			$cardBorder[$device]['style'] !== 'none'
			&&
			$cardBorder[$device]['width'] > 0
		) {
			$maybeWidth = $cardBorder[$device]['width'] . 'px';
		}

		if ($card_type === 'boxed' && $is_boundles !== 'yes') {
			$maybeWidth = $card_spacing_expanded[$device];
		}

		if ($maybeWidth !== 0) {
			$result = [];

			foreach ($image_border_radius[$device] as $value) {
				$result[] = 'calc(' . $value . ' - ' . $maybeWidth . ')';
			}

			$image_border_radius[$device] = $result;
			$should_apply = true;
		}

		if ($image_border_radius[$device] === 'CT_CSS_SKIP_RULE') {
			$image_border_radius[$device] = 'CT_CSS_SKIP_RULE';
		} else {
			$image_border_radius[$device] = implode(
				' ',
				$image_border_radius[$device]
			);
		}

	}

	if ($should_apply) {
		blocksy_output_css_vars([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.entry-card', $prefix),
			'variableName' => 'theme-image-border-radius',
			'value' => $image_border_radius,
			'responsive' => true
		]);
	}

	// Box shadow
	blocksy_output_box_shadow([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.entry-card', $prefix),
		'value' => blocksy_get_theme_mod($prefix . '_cardShadow', blocksy_box_shadow_value([
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

// cover card
if ($card_type === 'cover') {
	$card_min_height = blocksy_get_theme_mod($prefix. '_card_min_height', 400);

	if ($card_min_height !== 400) {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.entries', $prefix),
			'variableName' => 'card-min-height',
			'value' => $card_min_height
		]);
	}

	blocksy_output_background_css([
		'selector' => blocksy_prefix_selector('.entry-card .ct-media-container:after', $prefix),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'value' => blocksy_get_theme_mod(
			$prefix . '_card_overlay_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'rgba(0,0,0,0.5)'
					],
				],
			])
		),
		'responsive' => true,
	]);
}

foreach (blocksy_get_theme_mod($prefix . '_archive_order', []) as $layer) {
	if (! $layer['enabled']) {
		continue;
	}

	// featured image
	if ($layer['id'] === 'featured_image') {
		if ($structure === 'simple') {
			$image_width = blocksy_akg('image_width', $layer, 40);

			if ($image_width !== 40) {
				$css->put(
					blocksy_prefix_selector('.entry-card', $prefix),
					'--card-media-max-width: ' . $image_width . '%'
				);
			}
		}
	}

	// divider
	if ($layer['id'] === 'divider') {
		blocksy_output_border([
			'css' => $css,
			'selector' => blocksy_prefix_selector('.entry-card', $prefix),
			'variableName' => 'entry-divider',
			'value' => blocksy_get_theme_mod($prefix . '_entryDivider'),
			'default' => [
				'width' => 1,
				'style' => 'solid',
				'color' => [
					'color' => 'rgba(224, 229, 235, 0.8)',
				],
			]
		]);
	}

	// entry button
	if ($layer['id'] === 'read_more') {

		$button_type = blocksy_akg('button_type', $layer, 'background');

		if ($button_type === 'simple') {
			blocksy_output_colors([
				'value' => blocksy_get_theme_mod($prefix . '_cardButtonSimpleTextColor'),
				'default' => [
					'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
					'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				],
				'css' => $css,
				'variables' => [
					'default' => [
						'selector' => blocksy_prefix_selector('.entry-button', $prefix),
						'variable' => 'theme-link-initial-color'
					],

					'hover' => [
						'selector' => blocksy_prefix_selector('.entry-button', $prefix),
						'variable' => 'theme-link-hover-color'
					],
				],
			]);
		}

		if ($button_type === 'background') {
			blocksy_output_colors([
				'value' => blocksy_get_theme_mod($prefix . '_cardButtonBackgroundTextColor'),
				'default' => [
					'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
					'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				],
				'css' => $css,
				'variables' => [
					'default' => [
						'selector' => blocksy_prefix_selector('.entry-button.ct-button', $prefix),
						'variable' => 'theme-button-text-initial-color'
					],

					'hover' => [
						'selector' => blocksy_prefix_selector('.entry-button.ct-button', $prefix),
						'variable' => 'theme-button-text-hover-color'
					],
				],
			]);
		}

		if ($button_type === 'outline') {
			blocksy_output_colors([
				'value' => blocksy_get_theme_mod($prefix . '_cardButtonOutlineTextColor'),
				'default' => [
					'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
					'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				],
				'css' => $css,
				'variables' => [
					'default' => [
						'selector' => blocksy_prefix_selector('.entry-button.ct-button-ghost', $prefix),
						'variable' => 'theme-button-text-initial-color'
					],

					'hover' => [
						'selector' => blocksy_prefix_selector('.entry-button.ct-button-ghost', $prefix),
						'variable' => 'theme-button-text-hover-color'
					],
				],
			]);
		}

		if ($button_type !== 'simple') {
			blocksy_output_colors([
				'value' => blocksy_get_theme_mod($prefix . '_cardButtonColor'),
				'default' => [
					'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
					'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				],
				'css' => $css,
				'variables' => [
					'default' => [
						'selector' => blocksy_prefix_selector('.entry-button', $prefix),
						'variable' => 'theme-button-background-initial-color'
					],

					'hover' => [
						'selector' => blocksy_prefix_selector('.entry-button', $prefix),
						'variable' => 'theme-button-background-hover-color'
					],
				],
			]);
		}
	}

	if (isset($layer['typography'])) {
		blocksy_output_font_css([
			'font_value' => blocksy_akg(
				'typography',
				$layer,
				blocksy_typography_default_values([])
			),
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('[data-field*="' . substr(
				$layer['__id'],
				0, 6
			) . '"]', $prefix)
		]);
	}

	if (isset($layer['color'])) {
		blocksy_output_colors([
			'value' => blocksy_akg('color', $layer),
			'default' => [
				'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			],
			'css' => $css,
			'variables' => [
				'default' => [
					'selector' => blocksy_prefix_selector('[data-field*="' . substr(
						$layer['__id'],
						0, 6
					) . '"]', $prefix),
					'variable' => 'theme-text-color'
				],

				'hover' => [
					'selector' => blocksy_prefix_selector('[data-field*="' . substr(
						$layer['__id'],
						0, 6
					) . '"]', $prefix),
					'variable' => 'theme-link-hover-color'
				],
			],
		]);
	}
}

$cards_gap = blocksy_get_theme_mod($prefix. '_cardsGap', '30px');

if ($cards_gap !== '30px') {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.entries', $prefix),
		'variableName' => 'grid-columns-gap',
		'value' => $cards_gap,
		'unit' => '',
		'previousUnit' => 'px'
	]);
}


// content alignment
$horizontal_alignment = blocksy_get_theme_mod(
	$prefix. '_content_horizontal_alignment',
	'CT_CSS_SKIP_RULE'
);

$flex_horizontal_alignment = $horizontal_alignment;

$flex_horizontal_alignment = blocksy_map_values([
	'value' => $horizontal_alignment,
	'map' => [
		'left' => 'flex-start',
		'right' => 'flex-end'
	]
]);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_prefix_selector('.entry-card', $prefix),
	'variableName' => 'text-horizontal-alignment',
	'value' => $horizontal_alignment,
	'unit' => '',
]);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_prefix_selector('.entry-card', $prefix),
	'variableName' => 'horizontal-alignment',
	'value' => $flex_horizontal_alignment,
	'unit' => '',
]);


if ($card_type === 'cover') {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.entry-card', $prefix),
		'variableName' => 'vertical-alignment',
		'value' => blocksy_get_theme_mod($prefix. '_content_vertical_alignment', 'CT_CSS_SKIP_RULE'),
		'unit' => '',
	]);
}

// Featured Image Radius
if ($card_type === 'simple') {
	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.entry-card .ct-media-container', $prefix),
		'property' => 'theme-border-radius',
		'value' => blocksy_get_theme_mod(
			$prefix . '_cardThumbRadius',
			blocksy_spacing_value()
		)
	]);
}

blocksy_output_background_css([
	'selector' => blocksy_prefix_selector('', $prefix),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'value' => blocksy_get_theme_mod(
		$prefix . '_background',
		blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => Blocksy_Css_Injector::get_skip_rule_keyword()
				],
			],
		])
	),
	'responsive' => true,
]);
