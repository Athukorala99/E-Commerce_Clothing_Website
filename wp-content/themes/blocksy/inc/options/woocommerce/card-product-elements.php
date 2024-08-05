<?php

$card_additional_actions = apply_filters(
	'blocksy_woo_card_options:additional_actions',
	[
		// ['id' => '...', 'label' => '...']
	]
);

$card_additional_actions_options = [];
$card_additional_actions_design_options = [];

if (! empty($card_additional_actions)) {
	$card_additional_actions_options = [
		[
			blocksy_rand_md5() => [
				'type' => 'ct-title',
				'label' => __( 'Additional Actions', 'blocksy' ),
			],
		],
	];

	$card_actions_condition = [];

	foreach ($card_additional_actions as $single_action) {
		$card_actions_condition[$single_action['id']] = 'yes';

		$card_additional_actions_options[$single_action['id']] = [
			'label' => $single_action['label'],
			'type' => 'ct-switch',
			'value' => 'yes',
			'sync' => blocksy_sync_whole_page([
				'loader_selector' => '[data-products]'
			]),
		];
	}

	$card_additional_actions_design_options[blocksy_rand_md5()] = [
		'type' => 'ct-condition',
		'condition' => [
			'any' => $card_actions_condition
		],
		'options' => [
			blocksy_rand_md5() => [
				'type' => 'ct-title',
				'label' => __( 'Additional Actions', 'blocksy' ),
			],

			'additional_actions_button_icon_color' => [
				'label' => __( 'Icon Color', 'blocksy' ),
				'type'  => 'ct-color-picker',
				'design' => 'block:right',
				'responsive' => true,
				'setting' => [ 'transport' => 'postMessage' ],
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
						'inherit' => 'var(--theme-text-color)',
					],

					[
						'title' => __( 'Hover/Active', 'blocksy' ),
						'id' => 'hover',
						'inherit' => '#ffffff',
					],
				],
			],

			'additional_actions_button_background_color' => [
				'label' => __( 'Background Color', 'blocksy' ),
				'type'  => 'ct-color-picker',
				'design' => 'block:right',
				// 'divider' => 'top',
				'responsive' => true,
				'setting' => [ 'transport' => 'postMessage' ],
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
						'inherit' => '#ffffff',
					],

					[
						'title' => __( 'Hover/Active', 'blocksy' ),
						'id' => 'hover',
						'inherit' => 'var(--theme-palette-color-1)',
					],
				],
			],
		]
	];
}


$options = [
	'product_card_options_panel' => [
		'label' => __( 'Card Options', 'blocksy' ),
		'type' => 'ct-panel',
		'wrapperAttr' => [ 'data-panel' => 'only-arrow' ],
		'setting' => [ 'transport' => 'postMessage' ],
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [
					'woocommerce_archive_thumbnail_image_width' => [
						'type' => 'hidden',
						'label' => __('Image Width', 'blocksy'),
						'desc' => __('Image height will be automatically calculated based on the image ratio.', 'blocksy'),
						'value' => 500,
						'design' => 'inline',
						'setting' => [
							'type' => 'option',
							'capability' => 'manage_woocommerce',
							'transport' => 'postMessage'
						]
					],

					'woocommerce_archive_thumbnail_cropping' => [
						'label' => false,
						'type' => 'hidden',
						'value' => 'predefined',
						'setting' => [
							'type' => 'option',
							'capability' => 'manage_woocommerce',
							'transport' => 'postMessage'
						],
						'disableRevertButton' => true,
						'desc' => __('Width', 'blocksy'),
					],

					'woocommerce_archive_thumbnail_cropping_custom_width' => [
						'label' => false,
						'type' => 'hidden',
						'value' => 3,
						'setting' => [
							'type' => 'option',
							'capability' => 'manage_woocommerce',
							'transport' => 'postMessage'
						],
						'disableRevertButton' => true,
						'desc' => __('Width', 'blocksy'),
					],

					'woocommerce_archive_thumbnail_cropping_custom_height' => [
						'label' => false,
						'type' => 'hidden',
						'value' => 4,
						'setting' => [
							'type' => 'option',
							'capability' => 'manage_woocommerce',
							'transport' => 'postMessage'
						],
						'disableRevertButton' => true,
						'desc' => __('Height', 'blocksy'),
					],

					[
						'woo_card_layout' => [
							'label' => false,
							'type' => 'ct-layers',
							'manageable' => false,
							'divider' => 'bottom:full',
							'sync' => [
								blocksy_sync_whole_page([
									'prefix' => 'woo_categories',
									'loader_selector' => '[data-products] > li'
								]),

								[
									'prefix' => 'woo_categories',
									'id' => 'woo_card_layout_skip',
									'loader_selector' => 'skip',
									'container_inclusive' => false
								],
							],
							'value' => blocksy_get_woo_archive_layout_defaults(),
							'settings' => apply_filters(
								'blocksy_woo_card_options_layers:extra',
								[
									'product_image' => [
										'label' => __('Product Image', 'blocksy'),
										'options' => [

											[
												'blocksy_woocommerce_archive_thumbnail_cropping' => [
													'label' => __('Image Ratio', 'blocksy'),
													'type' => 'ct-woocommerce-ratio',
													/**
													 * Can be
													 * 1:1
													 * custom
													 * predefined
													 */
													'value' => 'predefined',
													'view' => 'inline',
													'design' => 'block',
													'preview_width_key' => 'woocommerce_archive_thumbnail_image_width',
													'inner-options' => [

														'woocommerce_archive_thumbnail_image_width' => [
															'label' => __('Image Size', 'blocksy'),
															'type' => 'text',
															'value' => 500,
															'design' => 'inline',
															'setting' => [
																'type' => 'option',
																'capability' => 'manage_woocommerce',
															],
															'desc' => __('Image height will be automatically calculated based on the image ratio.', 'blocksy'),
														],

													],

													'sync' => [
														'id' => 'woo_card_layout_skip'
													]
												],

												'product_image_hover' => [
													'label' => __( 'Hover Effect', 'blocksy' ),
													'type' => 'ct-select',
													'value' => 'none',
													'view' => 'text',
													'design' => 'inline',
													'setting' => [ 'transport' => 'postMessage' ],
													'choices' => blocksy_ordered_keys(
														[
															'none' => __( 'None', 'blocksy' ),
															'swap' => __( 'Swap Images', 'blocksy' ),
															'zoom-in' => __( 'Zoom In', 'blocksy' ),
															'zoom-out' => __( 'Zoom Out', 'blocksy' ),
														]
													),

													'sync' => blocksy_sync_whole_page([
														'prefix' => 'woo_categories',
														'loader_selector' => '[data-products] > li'
													]),
												],
											],

											(
												function_exists('blc_fs')
												&&
												blc_fs()->can_use_premium_code()
											) ? [
												'has_archive_video_thumbnail' => [
													'label' => __( 'Video Thumbnail', 'blocksy' ),
													'type' => 'ct-switch',
													'value' => 'no',
													'sync' => blocksy_sync_whole_page([
														'prefix' => 'woo_categories',
														'loader_selector' => '[data-products] > li'
													]),
												],
											] : [],

											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 25,
												'responsive' => true,

												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],

										],
									],

									'product_title' => [
										'label' => __('Title', 'blocksy'),
										'options' => [
											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 10,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],
										]
									],

									'product_price' => [
										'label' => __('Price', 'blocksy'),
										'condition' => [
											'shop_cards_type' => '!type-2'
										],
										'options' => [
											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 10,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],
										]
									],

									'product_rating' => [
										'label' => __('Star Rating', 'blocksy'),
										'options' => [
											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 10,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],
										]
									],

									'product_meta' => [
										'label' => __('Categories', 'blocksy'),
										'options' => [

											'style' => [
												'label' => __( 'Style', 'blocksy' ),
												'type' => 'ct-select',
												'value' => 'simple',
												'design' => 'inline',
												'view' => 'text',
												'choices' => blocksy_ordered_keys(
													[
														'simple' => __( 'Default', 'blocksy' ),
														'pill' => __( 'Button', 'blocksy' ),
														'underline' => __( 'Underline', 'blocksy' ),
													]
												),
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],

											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 10,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],

										],
									],

									'product_desc' => [
										'label' => __('Excerpt', 'blocksy'),
										'options' => [
											'excerpt_length' => [
												'label' => __('Length', 'blocksy'),
												'type' => 'ct-number',
												'design' => 'inline',
												'value' => 40,
												'min' => 1,
												'max' => 300,
											],

											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 25,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],
										]
									],

									'product_add_to_cart' => [
										'label' => __('Add to Cart', 'blocksy'),
										'condition' => [
											'shop_cards_type' => 'type-1'
										],
										'options' => [

											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 0,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],

										],
									],

									'product_add_to_cart_and_price' => [
										'label' => __('Add to Cart and Price', 'blocksy'),
										'condition' => [
											'shop_cards_type' => 'type-2'
										],
										'options' => [

											'spacing' => [
												'label' => __( 'Bottom Spacing', 'blocksy' ),
												'type' => 'ct-slider',
												'min' => 0,
												'max' => 100,
												'value' => 0,
												'responsive' => true,
												'sync' => [
													'id' => 'woo_card_layout_skip'
												]
											],

										],
									],
								]
							),
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'shop_cards_type' => '!type-2' ],
							'options' => [

								'shop_cards_alignment' => [
									'type' => 'ct-radio',
									'label' => __( 'Content Alignment', 'blocksy' ),
									'view' => 'text',
									'design' => 'block',
									'divider' => 'bottom',
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

						'shopCardsGap' => [
							'label' => __( 'Columns Gap', 'blocksy' ),
							'type' => 'ct-slider',
							'value' => '30px',
							'units' => blocksy_units_config([
								[ 'unit' => 'px', 'min' => 0, 'max' => 100 ],
								['unit' => '', 'type' => 'custom'],
							]),
							'responsive' => true,
							'setting' => [ 'transport' => 'postMessage' ],
						],

						'shopCardsRowGap' => [
							'label' => __( 'Rows Gap', 'blocksy' ),
							'type' => 'ct-slider',
							'value' => '30px',
							'units' => blocksy_units_config([
								[ 'unit' => 'px', 'min' => 0, 'max' => 100 ],
								['unit' => '', 'type' => 'custom'],
							]),
							'responsive' => true,
							'setting' => [ 'transport' => 'postMessage' ],
						],
					],

					$card_additional_actions_options,

					apply_filters(
						'blocksy_woo_card_options_elements:after',
						[]
					)
				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					[
						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_card_layout:array-ids:product_title:enabled' => '!no' ],
							'options' => [

								'cardProductTitleFont' => [
									'type' => 'ct-typography',
									'label' => __( 'Title Font', 'blocksy' ),
									'value' => blocksy_typography_default_values([
										'size' => '17px',
										'variation' => 'n6',
									]),
									'setting' => [ 'transport' => 'postMessage' ],
								],

								'cardProductTitleColor' => [
									'label' => __( 'Title Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'setting' => [ 'transport' => 'postMessage' ],

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
											'inherit' => 'var(--theme-heading-2-color, var(--theme-headings-color))'
										],

										[
											'title' => __( 'Hover', 'blocksy' ),
											'id' => 'hover',
											'inherit' => 'var(--theme-link-hover-color)'
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_card_layout:array-ids:product_desc:enabled' => '!no' ],
							'options' => [

								'cardProductExcerptFont' => [
									'type' => 'ct-typography',
									'label' => __( 'Short Description Font', 'blocksy' ),
									'value' => blocksy_typography_default_values([]),
									'setting' => [ 'transport' => 'postMessage' ],
									'divider' => 'top:full',
								],

								'cardProductExcerptColor' => [
									'label' => __( 'Short Description Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'setting' => [ 'transport' => 'postMessage' ],

									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
											'inherit' => 'var(--theme-text-color)'
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_card_layout:array-ids:product_price:enabled' => '!no' ],
							'options' => [

								'cardProductPriceFont' => [
									'type' => 'ct-typography',
									'label' => __( 'Price Font', 'blocksy' ),
									'value' => blocksy_typography_default_values([
										'variation' => 'n6',
									]),
									'setting' => [ 'transport' => 'postMessage' ],
									'divider' => 'top:full',
								],

								'cardProductPriceColor' => [
									'label' => __( 'Price Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'setting' => [ 'transport' => 'postMessage' ],

									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
											'inherit' => 'var(--theme-text-color)'
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_card_layout:array-ids:product_meta:enabled' => '!no' ],
							'options' => [

								blocksy_rand_md5() => [
									'type' => 'ct-divider',
								],

								'card_product_categories_font' => [
									'type' => 'ct-typography',
									'label' => __( 'Categories Font', 'blocksy' ),
									'sync' => 'live',
									'value' => blocksy_typography_default_values([
										'size' => [
											'desktop' => '12px',
											'tablet'  => '12px',
											'mobile'  => '12px'
										],
										'variation' => 'n6',
										'text-transform' => 'uppercase',
									]),
								],

								blocksy_rand_md5() => [
									'type' => 'ct-condition',
									'condition' => [ 'woo_card_layout:array-ids:product_meta:style' => '!pill' ],
									'options' => [

										'cardProductCategoriesColor' => [
											'label' => __( 'Categories Font Color', 'blocksy' ),
											'type'  => 'ct-color-picker',
											'design' => 'block:right',
											'responsive' => true,
											'setting' => [ 'transport' => 'postMessage' ],

											'value' => [
												'default' => [
													'color' => 'var(--theme-text-color)',
												],

												'hover' => [
													'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
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
													'inherit' => 'var(--theme-link-hover-color)'
												],
											],
										],

									],
								],


								blocksy_rand_md5() => [
									'type' => 'ct-condition',
									'condition' => [ 'woo_card_layout:array-ids:product_meta:style' => 'pill' ],
									'options' => [

										'card_product_categories_button_type_font_colors' => [
											'label' => __( 'Categories Font Color', 'blocksy' ),
											'type'  => 'ct-color-picker',
											'design' => 'block:right',
											'responsive' => true,
											'noColor' => [ 'background' => 'var(--theme-text-color)'],
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
													'inherit' => 'var(--theme-button-text-initial-color)'
												],

												[
													'title' => __( 'Hover', 'blocksy' ),
													'id' => 'hover',
													'inherit' => 'var(--theme-button-text-hover-color)'
												],
											],
										],

										'card_product_categories_button_type_background_colors' => [
											'label' => __( 'Categories Button Color', 'blocksy' ),
											'type'  => 'ct-color-picker',
											'design' => 'block:right',
											'responsive' => true,
											'noColor' => [ 'background' => 'var(--theme-text-color)'],
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
													'inherit' => 'var(--theme-button-background-initial-color)'
												],

												[
													'title' => __( 'Hover', 'blocksy' ),
													'id' => 'hover',
													'inherit' => 'var(--theme-button-background-hover-color)'
												],
											],
										],

									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_card_layout:array-ids:product_sku:enabled' => '!no' ],
							'options' => [

								'cardProductSkuFont' => [
									'type' => 'ct-typography',
									'label' => __( 'SKU Font', 'blocksy' ),
									'value' => blocksy_typography_default_values([]),
									'setting' => [ 'transport' => 'postMessage' ],
									'divider' => 'top:full',
								],

								'cardProductSkuColor' => [
									'label' => __( 'SKU Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'setting' => [ 'transport' => 'postMessage' ],

									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
											'inherit' => 'var(--theme-text-color)'
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_card_layout:array-ids:product_add_to_cart:enabled' => '!no' ],
							'options' => [

								blocksy_rand_md5() => [
									'type' => 'ct-condition',
									'condition' => [ 'shop_cards_type' => 'type-1' ],
									'options' => [

										'cardProductButton1Text' => [
											'label' => __( 'Button Text Color', 'blocksy' ),
											'type'  => 'ct-color-picker',
											'design' => 'block:right',
											'responsive' => true,
											'divider' => 'top:full',
											'setting' => [ 'transport' => 'postMessage' ],

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
													'inherit' => 'var(--theme-button-text-initial-color)'
												],

												[
													'title' => __( 'Hover', 'blocksy' ),
													'id' => 'hover',
													'inherit' => 'var(--theme-button-text-hover-color)'
												],
											],
										],

										'cardProductButtonBackground' => [
											'label' => __( 'Button Background Color', 'blocksy' ),
											'type'  => 'ct-color-picker',
											'design' => 'block:right',
											'responsive' => true,
											'setting' => [ 'transport' => 'postMessage' ],
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
													'inherit' => 'var(--theme-button-background-initial-color)'
												],

												[
													'title' => __( 'Hover', 'blocksy' ),
													'id' => 'hover',
													'inherit' => 'var(--theme-button-background-hover-color)'
												],
											],
										],

									],
								],

								blocksy_rand_md5() => [
									'type' => 'ct-condition',
									'condition' => [ 'shop_cards_type' => 'type-2' ],
									'options' => [

										'cardProductButton2Text' => [
											'label' => __( 'Button Text Color', 'blocksy' ),
											'type'  => 'ct-color-picker',
											'design' => 'block:right',
											'responsive' => true,
											'divider' => 'top:full',
											'setting' => [ 'transport' => 'postMessage' ],

											'value' => [
												'default' => [
													'color' => 'var(--theme-text-color)',
												],

												'hover' => [
													'color' => 'var(--theme-link-hover-color)',
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

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'shop_cards_type' => 'type-2' ],
							'options' => [

								'cardProductBackground' => [
									'label' => __( 'Card Background Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'block:right',
									'responsive' => true,
									'divider' => 'top:full',
									'setting' => [ 'transport' => 'postMessage' ],
									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Initial', 'blocksy' ),
											'id' => 'default',
											'inherit' => 'var(--theme-palette-color-8)'
										],
									],
								],

								'cardProductShadow' => [
									'label' => __( 'Card Shadow', 'blocksy' ),
									'type' => 'ct-box-shadow',
									'responsive' => true,
									'divider' => 'top',
									'setting' => [ 'transport' => 'postMessage' ],
									'value' => blocksy_box_shadow_value([
										'enable' => true,
										'h_offset' => 0,
										'v_offset' => 12,
										'blur' => 18,
										'spread' => -6,
										'inset' => false,
										'color' => [
											'color' => 'rgba(34, 56, 101, 0.03)',
										],
									])
								],

							],
						],
					],

					$card_additional_actions_design_options,

					[
						'cardProductRadius' => [
							'label' => [
								__('Image Border Radius', 'blocksy') => [
									'shop_cards_type' => 'type-1'
								],

								__('Card Border Radius', 'blocksy') => [
									'shop_cards_type' => 'type-2'
								]
							],
							'type' => 'ct-spacing',
							'divider' => 'top:full',
							'setting' => [ 'transport' => 'postMessage' ],
							'value' => blocksy_spacing_value([
								'top' => '3px',
								'left' => '3px',
								'right' => '3px',
								'bottom' => '3px',
							]),
							'responsive' => true
						],
					]
				],
			],

		],
	],
];

