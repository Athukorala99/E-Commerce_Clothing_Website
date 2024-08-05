<?php

if (! isset($skip_sync_id)) {
	$skip_sync_id = null;
}

if (! isset($prefix)) {
	$prefix = '';
} else {
	$prefix = $prefix . '_';
}

if (! isset($sync_prefix)) {
	$sync_prefix = $prefix;
}

if (! isset($has_module_title)) {
	$has_module_title = true;
}

if (! isset($has_share_box_type)) {
	$has_share_box_type = true;
}

if (! isset($has_share_box_location1)) {
	$has_share_box_location1 = true;
}

if (! isset($has_share_box)) {
	$has_share_box = 'no';
}

if (! isset($general_tab_end)) {
	$general_tab_end = [];
}

if (! isset($has_bottom_share_box_spacing)) {
	$has_bottom_share_box_spacing = true;
}

if (! isset($has_share_items_border)) {
	$has_share_items_border = true;
}

if (! isset($has_share_items_visibility)) {
	$has_share_items_visibility = true;
}

if (! isset($has_forced_icons_spacing)) {
	$has_forced_icons_spacing = false;
}

if (! isset($display_style)) {
	$display_style = 'panel';
}

$general_tab_options = array_merge(
	[
		$prefix . 'share_box_type' => [
			'label' => false,
			'type' => $has_share_box_type ? 'ct-image-picker' : 'hidden',
			'value' => 'type-1',
			'attr' => [ 'data-type' => 'background' ],
			'switchDeviceOnChange' => 'desktop',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix
			]),
			'choices' => [
				'type-1' => [
					'src'   => blocksy_image_picker_url( 'share-box-type-1.svg' ),
					'title' => __( 'Type 1', 'blocksy' ),
				],

				'type-2' => [
					'src'   => blocksy_image_picker_url( 'share-box-type-2.svg' ),
					'title' => __( 'Type 2', 'blocksy' ),
				],
			],
		],

		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => [
				$prefix . 'share_box_type' => 'type-2'
			],
			'options' => [

				$prefix . 'share_box2_colors' => [
					'label' => __( 'Icons Color', 'blocksy' ),
					'type' => 'ct-radio',
					'value' => 'custom',
					'view' => 'text',
					'design' => 'block',
					'divider' => 'top',
					'choices' => [
						'custom' => __( 'Custom', 'blocksy' ),
						'official' => __( 'Official', 'blocksy' ),
					],
					'sync' => blocksy_sync_whole_page([
						'prefix' => $sync_prefix,
						'loader_selector' => '.ct-share-box'
					]),
				],

			]
		],

		$prefix . 'share_box1_location' => [
			'label' => __( 'Box Location', 'blocksy' ),
			'type' => $has_share_box_location1 ? 'ct-checkboxes' : 'hidden',
			'design' => $has_share_box_location1 ? 'block' : 'none',
			'view' => 'text',
			'value' => [
				'top' => false,
				'bottom' => true,
			],
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix
			]),

			'divider' => 'top:bottom',

			'choices' => blocksy_ordered_keys([
				'top' => __( 'Top', 'blocksy' ),
				'bottom' => __( 'Bottom', 'blocksy' ),
			]),
		],

		$prefix . 'has_share_box_title' => [
			'label' => __( 'Module Title', 'blocksy' ),
			'type' => $has_module_title ? 'ct-switch' : 'hidden',
			'value' => 'no',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => [ $prefix . 'has_share_box_title' => 'yes' ],
			'options' => [

				$prefix . 'share_box_title' => array_merge([
					'label' => false,
					'type' => 'text',
					'design' => 'block',
					'value' => __( 'Share your love', 'blocksy' ),
					'disableRevertButton' => true,
				], $skip_sync_id ? [
					'sync' => $skip_sync_id,
					'setting' => [ 'transport' => 'postMessage' ],
				] : [
					'sync' => 'live'
				]),

			],
		],

		blocksy_rand_md5() => [
			'type' => 'ct-title',
			'label' => __( 'Share Networks', 'blocksy' ),
		],

		$prefix . 'share_facebook' => [
			'label' => __( 'Facebook', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'yes',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		$prefix . 'share_twitter' => [
			'label' => __( 'X (Twitter)', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'yes',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		$prefix . 'share_pinterest' => [
			'label' => __( 'Pinterest', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'yes',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		$prefix . 'share_linkedin' => [
			'label' => __( 'LinkedIn', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'yes',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		$prefix . 'share_reddit' => [
			'label' => __( 'Reddit', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'no',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			])
		],

		$prefix . 'share_hacker_news' => [
			'label' => __( 'Hacker News', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'no',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		$prefix . 'share_vk' => [
			'label' => __( 'VKontakte', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'no',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		$prefix . 'share_ok' => [
			'label' => __( 'Odnoklassniki', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'no',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		$prefix . 'share_telegram' => [
			'label' => __( 'Telegram', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'no',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		$prefix . 'share_viber' => [
			'label' => __( 'Viber', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'no',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		$prefix . 'share_whatsapp' => [
			'label' => __( 'WhatsApp', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'no',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		$prefix . 'share_flipboard' => [
			'label' => __( 'Flipboard', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'no',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		$prefix . 'share_email' => [
			'label' => __( 'Email', 'blocksy' ),
			'type' => 'ct-switch',
			'value' => 'no',
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix,
				'loader_selector' => '.ct-share-box'
			]),
		],

		blocksy_rand_md5() => [
			'type' => 'ct-divider',
		],

		$prefix . 'share_links_nofollow' => [
			'type'  => 'ct-switch',
			'label' => __( 'Set links to nofollow', 'blocksy' ),
			'value' => 'yes',
		],

		blocksy_rand_md5() => [
			'type' => 'ct-divider',
		],

		$prefix . 'share_box_icon_size' => array_merge([
			'label' => __( 'Icon Size', 'blocksy' ),
			'type' => 'ct-slider',
			'value' => '15px',
			'units' => blocksy_units_config([
				[ 'unit' => 'px', 'min' => 0, 'max' => 100 ],
				['unit' => '', 'type' => 'custom'],
			]),
			'responsive' => true,
			'setting' => [ 'transport' => 'postMessage' ],
		], $skip_sync_id ? [
			'sync' => $skip_sync_id
		] : []),

		blocksy_rand_md5() => [
			'type' => 'ct-divider',
		],

		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => $has_forced_icons_spacing ? [
				$prefix . 'share_box_type' => 'type-1'
			] : [
				$prefix . 'share_box_type' => '!type-1'
			],
			'options' => [

				$prefix . 'share_box_icons_spacing' => array_merge([
					'label' => __( 'Icons Spacing', 'blocksy' ),
					'type' => 'ct-slider',
					'value' => '15px',
					'units' => blocksy_units_config([
						[ 'unit' => 'px', 'min' => 0, 'max' => 100 ],
						['unit' => '', 'type' => 'custom'],
					]),
					'responsive' => true,
					'setting' => [ 'transport' => 'postMessage' ],
				], $skip_sync_id ? [
					'sync' => $skip_sync_id
				] : []),

				blocksy_rand_md5() => [
					'type' => 'ct-divider',
				],

			],
		],

		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => [
				$prefix . 'share_box1_location/top' => true
			],
			'options' => [

				$prefix . 'top_share_box_spacing' => [
					'label' => __( 'Top Box Spacing', 'blocksy' ),
					'type' => 'ct-slider',
					'value' => '50px',
					'units' => blocksy_units_config([
						[ 'unit' => 'px', 'min' => 0, 'max' => 100],
						['unit' => '', 'type' => 'custom'],
					]),
					'responsive' => true,
					'sync' => 'live',
					'divider' => 'bottom',
				],

			],
		],

		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => [
				$prefix . 'share_box1_location/bottom' => true
			],
			'options' => [
				$has_bottom_share_box_spacing ? [
					$prefix . 'bottom_share_box_spacing' => [
						'label' => __( 'Bottom Box Spacing', 'blocksy' ),
						'type' => 'ct-slider',
						'value' => '50px',
						'units' => blocksy_units_config([
							[ 'unit' => 'px', 'min' => 0, 'max' => 100],
							['unit' => '', 'type' => 'custom'],
						]),
						'responsive' => true,
						'sync' => 'live',
					],

					blocksy_rand_md5() => [
						'type' => 'ct-divider',
					],
				] : []
			],
		],

		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => [ $prefix . 'share_box_type' => 'type-2' ],
			'options' => [

				$prefix . 'share_box_alignment' => [
					'type' => 'ct-radio',
					'label' => __( 'Content Alignment', 'blocksy' ),
					'view' => 'text',
					'design' => 'block',
					'divider' => 'bottom:full',
					'responsive' => true,
					'attr' => [ 'data-type' => 'alignment' ],
					'setting' => [ 'transport' => 'postMessage' ],
					'value' => 'CT_CSS_SKIP_RULE',
					'choices' => [
						'flex-start' => '',
						'center' => '',
						'flex-end' => '',
					],
				],

			],
		],

	],

	[
		$prefix . 'share_box_visibility' => [
			'label' => __( 'Visibility', 'blocksy' ),
			'type' => $has_share_items_visibility ? 'ct-visibility' : 'hidden',
			'design' => 'block',
			'sync' => 'live',

			'value' => [
				'desktop' => true,
				'tablet' => true,
				'mobile' => false,
			],

			'choices' => blocksy_ordered_keys([
				'desktop' => __( 'Desktop', 'blocksy' ),
				'tablet' => __( 'Tablet', 'blocksy' ),
				'mobile' => __( 'Mobile', 'blocksy' ),
			]),
		],

	],

	$general_tab_end
);

$design_tab_options = [
	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => $display_style === 'design_only' ? [
			$prefix . 'share_box_type' => '! type-1'
		] : [
			$prefix . 'share_box_type' => 'type-1'
		],
		'options' => [

			[
				$prefix . 'share_items_icon_color' => [
					'label' => __( 'Icons Color', 'blocksy' ),
					'type'  => 'ct-color-picker',
					'design' => 'inline',

					'sync' => 'live',

					'value' => [
						'default' => [
							'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
						],

						'hover' => [
							'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
						],
					],

					'pickers' => [
						[
							'title' => __( 'Initial', 'blocksy' ),
							'id' => 'default',
							'inherit' => 'var(--theme-text-color)'
						],

						[
							'title' => __( 'Hover', 'blocksy' ),
							'id' => 'hover',
							'inherit' => 'var(--theme-palette-color-2)'
						],
					],
				],
			],

			$has_share_items_border ? [
				$prefix . 'share_items_border' => [
					'label' => __( 'Border', 'blocksy' ),
					'type' => 'ct-border',
					'design' => 'inline',
					'divider' => 'top',
					'sync' => 'live',
					'value' => [
						'width' => 1,
						'style' => 'solid',
						'color' => [
							'color' => 'var(--theme-border-color)',
						],
					]
				],
			] : []
		],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [
			$prefix . 'share_box_type' => 'type-2',
			$prefix . 'share_box2_colors' => 'custom'
		],
		'options' => [

			$prefix . 'share_items_icon' => [
				'label' => __( 'Icons Color', 'blocksy' ),
				'type'  => 'ct-color-picker',
				'design' => 'inline',
				'sync' => 'live',

				'value' => [
					'default' => [
						'color' => '#ffffff',
					],

					'hover' => [
						'color' => '#ffffff',
					],
				],

				'pickers' => [
					[
						'title' => __( 'Initial', 'blocksy' ),
						'id' => 'default',
					],

					[
						'title' => __( 'Hover', 'blocksy' ),
						'id' => 'hover',
					],
				],
			],

			$prefix . 'share_items_background' => [
				'label' => __( 'Background Color', 'blocksy' ),
				'type'  => 'ct-color-picker',
				'design' => 'inline',
				'sync' => 'live',

				'value' => [
					'default' => [
						'color' => 'var(--theme-palette-color-1)',
					],

					'hover' => [
						'color' => 'var(--theme-palette-color-2)',
					],
				],

				'pickers' => [
					[
						'title' => __( 'Initial', 'blocksy' ),
						'id' => 'default',
					],

					[
						'title' => __( 'Hover', 'blocksy' ),
						'id' => 'hover',
					],
				],
			],
		],
	],
];

$inner_options = [
	blocksy_rand_md5() => [
		'title' => __('General', 'blocksy'),
		'type' => 'tab',
		'options' => $general_tab_options
	],

	blocksy_rand_md5() => [
		'title' => __('Design', 'blocksy'),
		'type' => 'tab',
		'options' => $design_tab_options
	]
];


if ($display_style === 'panel') {
	$options = [
		$prefix . 'has_share_box' => [
			'label' => __( 'Share Box', 'blocksy' ),
			'type' => 'ct-panel',
			'switch' => true,
			'value' => $has_share_box,
			'sync' => blocksy_sync_whole_page([
				'prefix' => $sync_prefix
			]),
			'inner-options' => $inner_options
		],
	];
}

if ($display_style === 'switch') {
	$options = [
		[
			$prefix . 'has_share_box' => [
				'label' => __( 'Share Box', 'blocksy' ),
				'type' => 'ct-switch',
				'divider' => 'top:full',
				'value' => $has_share_box,
				'sync' => blocksy_sync_whole_page([
					'prefix' => $sync_prefix
				])
			],
		],

		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => [
				$prefix . 'has_share_box' => 'yes'
			],
			'options' => $inner_options
		]
	];
}

if ($display_style === 'general_only') {
	$options = $general_tab_options;
}

if ($display_style === 'design_only') {
	$options = $design_tab_options;
}

