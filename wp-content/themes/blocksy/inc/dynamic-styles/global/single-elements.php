<?php

if (blocksy_get_theme_mod($prefix . '_has_share_box', 'no') === 'yes') {
	$share_box_icon_size = blocksy_get_theme_mod($prefix . '_share_box_icon_size', '15px');
	$share_box_type = blocksy_get_theme_mod($prefix. '_share_box_type', 'type-1');

	if ($share_box_icon_size !== '15px') {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.ct-share-box', $prefix),
			'variableName' => 'theme-icon-size',
			'value' => $share_box_icon_size,
			'unit' => '',
		]);
	}

	$share_box_icons_spacing = blocksy_get_theme_mod($prefix . '_share_box_icons_spacing', '15px');

	if ($share_box_icons_spacing !== '15px' && $share_box_type !== 'type-1') {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.ct-share-box', $prefix),
			'variableName' => 'items-spacing',
			'value' => $share_box_icons_spacing,
			'unit' => '',
		]);
	}

	$top_share_box_spacing = blocksy_get_theme_mod($prefix . '_top_share_box_spacing', '50px');
	if ($top_share_box_spacing !== '50px') {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.ct-share-box[data-location="top"]', $prefix),
			'variableName' => 'margin',
			'value' => $top_share_box_spacing,
			'unit' => ''
		]);
	}

	$bottom_share_box_spacing = blocksy_get_theme_mod($prefix . '_bottom_share_box_spacing', '50px');
	if ($bottom_share_box_spacing !== '50px') {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.ct-share-box[data-location="bottom"]', $prefix),
			'variableName' => 'margin',
			'value' => $bottom_share_box_spacing,
			'unit' => ''
		]);
	}


	if ($share_box_type === 'type-1') {
		blocksy_output_colors([
			'value' => blocksy_get_theme_mod($prefix . '_share_items_icon_color', []),
			'default' => [
				'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			],
			'css' => $css,
			'variables' => [
				'default' => [
					'selector' => blocksy_prefix_selector('.ct-share-box[data-type="type-1"]', $prefix),
					'variable' => 'theme-icon-color'
				],

				'hover' => [
					'selector' => blocksy_prefix_selector('.ct-share-box[data-type="type-1"]', $prefix),
					'variable' => 'theme-icon-hover-color'
				],
			],
		]);

		blocksy_output_border([
			'css' => $css,
			'selector' => blocksy_prefix_selector('.ct-share-box[data-type="type-1"]', $prefix),
			'variableName' => 'theme-border',
			'value' => blocksy_get_theme_mod($prefix . '_share_items_border'),
			'default' => [
				'width' => 1,
				'style' => 'solid',
				'color' => [
					'color' => 'var(--theme-border-color)',
				],
			]
		]);
	}


	if ($share_box_type === 'type-2') {

		$share_box_alignment = blocksy_get_theme_mod($prefix . '_share_box_alignment', 'CT_CSS_SKIP_RULE');

		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.ct-share-box[data-type="type-2"]', $prefix),
			'variableName' => 'horizontal-alignment',
			'value' => $share_box_alignment,
			'unit' => '',
		]);
	}


	$share_box2_colors = blocksy_get_theme_mod($prefix. '_share_box2_colors', 'custom');

	if ($share_box_type === 'type-2' && $share_box2_colors === 'custom') {
		blocksy_output_colors([
			'value' => blocksy_get_theme_mod(
				$prefix . '_share_items_icon',
				[]
			),
			'default' => [
				'default' => [ 'color' => '#ffffff' ],
				'hover' => [ 'color' => '#ffffff' ],
			],
			'css' => $css,
			'variables' => [
				'default' => [
					'selector' => blocksy_prefix_selector('.ct-share-box[data-type="type-2"]', $prefix),
					'variable' => 'theme-icon-color'
				],

				'hover' => [
					'selector' => blocksy_prefix_selector('.ct-share-box[data-type="type-2"]', $prefix),
					'variable' => 'theme-icon-hover-color'
				],
			],
		]);

		blocksy_output_colors([
			'value' => blocksy_get_theme_mod($prefix . '_share_items_background', []),
			'default' => [
				'default' => [ 'color' => 'var(--theme-palette-color-1)' ],
				'hover' => [ 'color' => 'var(--theme-palette-color-2)' ],
			],
			'css' => $css,
			'variables' => [
				'default' => [
					'selector' => blocksy_prefix_selector('.ct-share-box[data-type="type-2"]', $prefix),
					'variable' => 'background-color'
				],

				'hover' => [
					'selector' => blocksy_prefix_selector('.ct-share-box[data-type="type-2"]', $prefix),
					'variable' => 'background-hover-color'
				]
			],
		]);
	}
}


// featured image
if (blocksy_get_theme_mod($prefix . '_has_featured_image', 'no') === 'yes') {

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.ct-featured-image', $prefix),
		'property' => 'theme-border-radius',
		'value' => blocksy_get_theme_mod(
			$prefix . '_featured_image_border_radius',
			blocksy_spacing_value()
		)
	]);
}


// author box
if (
	blocksy_get_theme_mod($prefix . '_has_author_box', 'no') === 'yes'
	&&
	$prefix !== 'single_page'
) {

	$author_box_spacing = blocksy_get_theme_mod($prefix. '_single_author_box_spacing', '40px');

	if ($author_box_spacing !== '40px') {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.author-box', $prefix),
			'variableName' => 'spacing',
			'value' => $author_box_spacing,
			'unit' => ''
		]);
	}

	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			$prefix . '_single_author_box_name_font',
			blocksy_typography_default_values([])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.author-box .author-box-name', $prefix),
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod($prefix . '_single_author_box_name_color'),
		'default' => [
			'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,

		'variables' => [
			'default' => [
				'selector' => blocksy_prefix_selector('.author-box .author-box-name', $prefix),
				'variable' => 'theme-heading-color'
			],
		],

		'responsive' => true
	]);

	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			$prefix . '_single_author_box_font',
			blocksy_typography_default_values([])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.author-box .author-box-bio', $prefix),
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod($prefix . '_single_author_box_font_color', []),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'initial' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,

		'variables' => [
			'default' => [
				'selector' => blocksy_prefix_selector('.author-box .author-box-bio', $prefix),
				'variable' => 'theme-text-color'
			],

			'initial' => [
				'selector' => blocksy_prefix_selector('.author-box .author-box-bio', $prefix),
				'variable' => 'theme-link-initial-color'
			],

			'hover' => [
				'selector' => blocksy_prefix_selector('.author-box .author-box-bio', $prefix),
				'variable' => 'theme-link-hover-color'
			],
		],

		'responsive' => true
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod($prefix . '_single_author_box_social_icons_color', []),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,

		'variables' => [
			'default' => [
				'selector' => blocksy_prefix_selector('.author-box .author-box-socials', $prefix),
				'variable' => 'theme-icon-color'
			],

			'hover' => [
				'selector' => blocksy_prefix_selector('.author-box .author-box-socials', $prefix),
				'variable' => 'theme-icon-hover-color'
			]
		],

		'responsive' => true
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod($prefix . '_single_author_box_social_icons_background', []),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,

		'variables' => [
			'default' => [
				'selector' => blocksy_prefix_selector('.author-box .author-box-socials', $prefix),
				'variable' => 'background-color'
			],

			'hover' => [
				'selector' => blocksy_prefix_selector('.author-box .author-box-socials', $prefix),
				'variable' => 'background-hover-color'
			]
		],

		'responsive' => true
	]);


	$author_box_type = blocksy_get_theme_mod($prefix. '_single_author_box_type', 'type-2');

	if ($author_box_type === 'type-1') {

		blocksy_output_background_css([
			'selector' => blocksy_prefix_selector('.author-box[data-type="type-1"]', $prefix),
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'value' => blocksy_get_theme_mod(
				$prefix . '_single_author_box_container_background',
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

		blocksy_output_box_shadow([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.author-box[data-type="type-1"]', $prefix),
			'value' => blocksy_get_theme_mod(
				$prefix . '_single_author_box_shadow',
				blocksy_box_shadow_value([
					'enable' => true,
					'h_offset' => 0,
					'v_offset' => 50,
					'blur' => 90,
					'spread' => 0,
					'inset' => false,
					'color' => [
						'color' => 'rgba(210, 213, 218, 0.4)',
					],
				])
			),
			'responsive' => true
		]);

		blocksy_output_border([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.author-box[data-type="type-1"]', $prefix),
			'variableName' => 'theme-border',
			'value' => blocksy_get_theme_mod($prefix . '_single_author_box_container_border'),
			'default' => [
				'width' => 1,
				'style' => 'none',
				'color' => [
					'color' => 'rgba(44,62,80,0.2)',
				],
			],
			'responsive' => true,
			// 'skip_none' => true
		]);

		blocksy_output_spacing([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.author-box[data-type="type-1"]', $prefix),
			'property' => 'theme-border-radius',
			'value' => blocksy_get_theme_mod(
				$prefix . '_single_author_box_border_radius',
				blocksy_spacing_value()
			)
		]);
	}

	if ($author_box_type === 'type-2') {
		blocksy_output_colors([
			'value' => blocksy_get_theme_mod($prefix . '_single_author_box_border', []),
			'default' => [
				'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			],
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,

			'variables' => [
				'default' => [
					'selector' => blocksy_prefix_selector('.author-box[data-type="type-2"]', $prefix),
					'variable' => 'theme-border-color'
				],
			],

			'responsive' => true,
		]);
	}
}

// posts navigation
if (
	blocksy_get_theme_mod($prefix . '_has_post_nav', 'no') === 'yes'
	&&
	$prefix !== 'single_page'
) {

	$post_nav_spacing = blocksy_get_theme_mod($prefix . '_post_nav_spacing', '50px');

	if ($post_nav_spacing !== '50px') {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.post-navigation', $prefix),
			'variableName' => 'margin',
			'value' => $post_nav_spacing,
			'unit' => ''
		]);
	}

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod($prefix . '_posts_nav_font_color', []),
		'default' => [
			'default' => [ 'color' => 'var(--theme-text-color)' ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => blocksy_prefix_selector('.post-navigation', $prefix),
				'variable' => 'theme-link-initial-color'
			],

			'hover' => [
				'selector' => blocksy_prefix_selector('.post-navigation', $prefix),
				'variable' => 'theme-link-hover-color'
			],
		],
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod($prefix . '_posts_nav_image_overlay_color', []),
		'default' => [
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'hover' => [
				'selector' => blocksy_prefix_selector('.post-navigation', $prefix),
				'variable' => 'image-overlay-color'
			],
		],
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.post-navigation figure', $prefix),
		'property' => 'theme-border-radius',
		'value' => blocksy_get_theme_mod(
			$prefix . '_posts_nav_image_border_radius',
			blocksy_spacing_value()
		)
	]);
}


// related posts
if (
	blocksy_get_theme_mod($prefix . '_has_related_posts', 'no') === 'yes'
	&&
	$prefix !== 'single_page'
) {

	$related_posts_container_spacing = blocksy_get_theme_mod($prefix . '_related_posts_container_spacing', '50px');

	if ($related_posts_container_spacing !== '50px') {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_prefix_selector('.ct-related-posts-container', $prefix),
			'variableName' => 'padding',
			'value' => $related_posts_container_spacing,
			'unit' => ''
		]);
	}

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.ct-related-posts .ct-block-title', $prefix),
		'variableName' => 'horizontal-alignment',
		'value' => blocksy_get_theme_mod($prefix . '_related_label_alignment', 'CT_CSS_SKIP_RULE'),
		'unit' => '',
	]);

	blocksy_output_background_css([
		'selector' => blocksy_prefix_selector('.ct-related-posts-container', $prefix),
		'css' => $css,
		'value' => blocksy_get_theme_mod(
			$prefix . '_related_posts_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'var(--theme-palette-color-6)'
					],
				],
			])
		)
	]);

	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			$prefix . '_related_posts_label_font',
			blocksy_typography_default_values([])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.ct-related-posts-container .ct-block-title', $prefix),
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod($prefix . '_related_posts_label_color'),
		'default' => [
			'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => blocksy_prefix_selector('.ct-related-posts-container .ct-block-title', $prefix),
				'variable' => 'theme-heading-color'
			],
		],
	]);

	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			$prefix . '_related_posts_link_font',
			blocksy_typography_default_values([
				'size' => '16px'
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.related-entry-title', $prefix),
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod($prefix . '_related_posts_link_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => blocksy_prefix_selector('.related-entry-title', $prefix),
				'variable' => 'theme-heading-color'
			],

			'hover' => [
				'selector' => blocksy_prefix_selector('.related-entry-title', $prefix),
				'variable' => 'theme-link-hover-color'
			],
		],
	]);

	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			$prefix . '_related_posts_meta_font',
			blocksy_typography_default_values([
				'size' => '14px'
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.ct-related-posts .entry-meta', $prefix),
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod($prefix . '_related_posts_meta_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => blocksy_prefix_selector('.ct-related-posts .entry-meta', $prefix),
				'variable' => 'theme-text-color'
			],

			'hover' => [
				'selector' => blocksy_prefix_selector('.ct-related-posts .entry-meta', $prefix),
				'variable' => 'theme-link-hover-color'
			],
		],
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector('.ct-related-posts .ct-media-container', $prefix),
		'property' => 'theme-border-radius',
		'value' => blocksy_get_theme_mod(
			$prefix . '_related_thumb_radius',
			blocksy_spacing_value()
		),
		'empty_value' => 5
	]);


	$relatedNarrowWidth = blocksy_get_theme_mod($prefix . '_related_narrow_width', 750 );

	if ($relatedNarrowWidth !== 750) {
		$css->put(
			blocksy_prefix_selector('.ct-related-posts-container', $prefix),
			'--theme-narrow-container-max-width: ' . $relatedNarrowWidth . 'px'
		);
	}

	$grid_columns = blocksy_expand_responsive_value(blocksy_get_theme_mod(
		$prefix . '_related_posts_columns',
		[
			'desktop' => 3,
			'tablet' => 2,
			'mobile' => 1
		]
	));

	$columns_for_output = [
		'desktop' => 'repeat(' . $grid_columns['desktop'] . ', 1fr)',
		'tablet' => 'repeat(' . $grid_columns['tablet'] . ', 1fr)',
		'mobile' => 'repeat(' . $grid_columns['mobile'] . ', 1fr)'
	];

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_prefix_selector(
			'.ct-related-posts',
			$prefix
		),
		'variableName' => 'grid-template-columns',
		'value' => $columns_for_output,
		'unit' => ''
	]);
}