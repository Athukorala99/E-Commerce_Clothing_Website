<?php

$options = [
	'woo_has_product_tabs' => [
		'label' => __( 'Product Tabs', 'blocksy' ),
		'type' => 'ct-panel',
		'switch' => true,
		'value' => 'yes',
		'sync' => blocksy_sync_whole_page([
			'prefix' => 'product',
			'loader_selector' => '.type-product'
		]),
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					[
						'woo_tabs_type' => [
							'label' => false,
							'type' => 'ct-image-picker',
							'value' => 'type-1',
							'divider' => 'bottom:full',
							'choices' => [
								'type-1' => [
									'src' => blocksy_image_picker_url('woo-tabs-type-1.svg'),
									'title' => __('Type 1', 'blocksy'),
								],
	
								'type-2' => [
									'src' => blocksy_image_picker_url('woo-tabs-type-2.svg'),
									'title' => __('Type 2', 'blocksy'),
								],
	
								'type-3' => [
									'src' => blocksy_image_picker_url('woo-tabs-type-3.svg'),
									'title' => __('Type 3', 'blocksy'),
								],
	
								'type-4' => [
									'src' => blocksy_image_picker_url('woo-tabs-type-4.svg'),
									'title' => __('Type 4', 'blocksy'),
								],
							],
							'sync' => blocksy_sync_whole_page([
								'prefix' => 'product',
								'loader_selector' => '.woocommerce-tabs'
							]),
						],
	
						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_tabs_type' => 'type-1|type-2' ],
							'options' => [
	
								'woo_tabs_alignment' => [
									'type' => 'ct-radio',
									'label' => __( 'Horizontal Alignment', 'blocksy' ),
									'view' => 'text',
									'design' => 'block',
									'divider' => 'bottom',
									'attr' => [ 'data-type' => 'alignment' ],
									'setting' => [ 'transport' => 'postMessage' ],
									'value' => 'center',
									'choices' => [
										'left' => '',
										'center' => '',
										'right' => '',
									],
								],
	
							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 
								'woo_tabs_type' => 'type-3|type-4',
							],
							'options' => [

								'woo_accordion_in_summary' => [
									'label' => __('Module Placement', 'blocksy'),
									'type' => 'ct-radio',
									'value' => 'default',
									'view' => 'text',
									'divider' => 'bottom',
									'choices' => [
										'default' => __('Default', 'blocksy'),
										'summary' => __('Summary', 'blocksy'),
									],

									'sync' => blocksy_sync_whole_page([
										'prefix' => 'product',
										'loader_selector' => '.product'
									]),
								],
	
							],
						],
	
						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_tabs_type' => 'type-3' ],
							'options' => [
	
								'woo_accordion_closed_by_default' => [
									'label' => __( 'First Tab Expanded', 'blocksy' ),
									'type' => 'ct-switch',
									'switch' => true,
									'value' => 'yes',
									// 'divider' => 'top',
									'sync' => blocksy_sync_whole_page([
										'prefix' => 'product',
										'loader_selector' => '.woocommerce-tabs'
									]),
								],

								'woo_accordion_close_prev' => [
									'label' => __( 'Close Adjacent Tabs', 'blocksy' ),
									'type' => 'ct-switch',
									'switch' => true,
									'value' => 'yes',
									// 'divider' => 'top',
									'sync' => blocksy_sync_whole_page([
										'prefix' => 'product',
										'loader_selector' => '.woocommerce-tabs'
									]),
								],
	
							],
						],
	
						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_tabs_type' => '!type-4' ],
							'options' => [
								'woo_has_product_tabs_description' => [
									'label' => __( 'Description Heading ', 'blocksy' ),
									'type' => 'ct-switch',
									'switch' => true,
									'value' => 'no',
									// 'divider' => 'top',
									'sync' => blocksy_sync_whole_page([
										'prefix' => 'product',
										'loader_selector' => '.woocommerce-tabs'
									]),
								],
							]
						],

					],

					apply_filters('blocksy:options:woo:tabs:general:brands', []),

					[
						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'woo_tabs_type' => 'type-4' ],
							'options' => [
	
								'woo_separated_tabs_spacing' => [
									'label' => __( 'Items Spacing', 'blocksy' ),
									'type' => 'ct-slider',
									'min' => 0,
									'max' => 200,
									'value' => 50,
									'divider' => 'top',
									'responsive' => true,
									'setting' => [ 'transport' => 'postMessage' ],
								],
	
							],
						],
					],
				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					'woo_tabs_font' => [
						'type' => 'ct-typography',
						'label' => __( 'Font', 'blocksy' ),
						'value' => blocksy_typography_default_values([
							'size' => '12px',
							'variation' => 'n6',
							'text-transform' => 'uppercase',
							'line-height' => '1',
						]),
						'setting' => [ 'transport' => 'postMessage' ],
					],

					'woo_tabs_font_color' => [
						'label' => __( 'Font Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
						'sync' => 'live',
						'value' => [
							'default' => [
								'color' => 'var(--theme-text-color)',
							],

							'hover' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],

							'active' => [
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

							[
								'title' => __( 'Active', 'blocksy' ),
								'id' => 'active',
								'inherit' => 'self:hover'
							],
						],
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [ 'woo_tabs_type' => '!type-4' ],
						'options' => [

							'woo_tabs_border_color' => [
								'label' => __( 'Border Color', 'blocksy' ),
								'type'  => 'ct-color-picker',
								'design' => 'inline',
								'divider' => 'top',
								'sync' => 'live',
								'value' => [
									'default' => [
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
									],
								],

								'pickers' => [
									[
										'title' => __( 'Initial', 'blocksy' ),
										'id' => 'default',
										'inherit' => 'var(--theme-border-color)'
									],
								],
							],

						],
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [ 'woo_tabs_type' => 'type-1' ],
						'options' => [

							'woo_actibe_tab_border' => [
								'label' => __( 'Active Tab Border', 'blocksy' ),
								'type'  => 'ct-color-picker',
								'design' => 'inline',
								'divider' => 'top',
								'sync' => 'live',
								'value' => [
									'default' => [
										'color' => 'var(--theme-palette-color-1)',
									],
								],

								'pickers' => [
									[
										'title' => __( 'Active', 'blocksy' ),
										'id' => 'default',
									],
								],
							],

						],
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [ 'woo_tabs_type' => 'type-2' ],
						'options' => [

							'woo_actibe_tab_background' => [
								'label' => __( 'Active Tab Colors', 'blocksy' ),
								'type'  => 'ct-color-picker',
								'design' => 'inline',
								'divider' => 'top',
								'sync' => 'live',
								'value' => [
									'default' => [
										'color' => 'rgba(242, 244, 247, 0.7)',
									],

									'border' => [
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
									],
								],

								'pickers' => [
									[
										'title' => __( 'Background', 'blocksy' ),
										'id' => 'default',
									],

									[
										'title' => __( 'Border', 'blocksy' ),
										'id' => 'border',
										'inherit' => 'var(--theme-border-color)'
									],
								],
							],

						],
					],

				],
			],

		],
	],

];
