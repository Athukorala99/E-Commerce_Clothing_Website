<?php

$options = [
	'woo_product_gallery' => [
		'label' => __( 'Product Gallery', 'blocksy' ),
		'type' => 'ct-panel',
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					apply_filters(
						'blocksy:options:single_product:product-general-tab:start',
						[
							'product_view_type' => [
								'type' => 'hidden',
								'value' => 'default-gallery'
							]
						]
					),

					[
						'has_product_single_lightbox' => [
							'label' => __( 'Lightbox', 'blocksy' ),
							'type' => 'ct-switch',
							'value' => 'no',
							'divider' => 'top:full',
							'sync' => blocksy_sync_whole_page([
								'prefix' => 'product',
								'loader_selector' => '.woocommerce-product-gallery'
							]),
						],

						'has_product_single_zoom' => [
							'label' => __( 'Zoom Effect', 'blocksy' ),
							'type' => 'ct-switch',
							'value' => 'yes',
							'sync' => blocksy_sync_whole_page([
								'prefix' => 'product',
								'loader_selector' => '.woocommerce-product-gallery'
							]),
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								'product_view_type' => 'default-gallery|stacked-gallery'
							],
							'options' => [
								'has_product_sticky_gallery' => [
									'label' => __('Sticky Gallery', 'blocksy'),
									'type' => 'ct-switch',
									'value' => 'no',
									'sync' => 'live'
								],
							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-divider',
						],


					],

					// apply_filters(
					// 	'blocksy:options:single_product:gallery-options:start',
					// 	[]
					// ),

					[
						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								'product_view_type' => 'default-gallery|stacked-gallery',
							],
							'options' => [

								'productGalleryWidth' => [
									'label' => __( 'Gallery Container Width', 'blocksy' ),
									'type' => 'ct-slider',
									'defaultUnit' => '%',
									'value' => 50,
									'min' => 20,
									'max' => 70,
									'divider' => 'bottom',
									'setting' => [ 'transport' => 'postMessage' ],
								],

							],
						],

						// blocksy_rand_md5() => [
						// 	'type' => 'ct-divider',
						// ],

						'product_gallery_ratio' => [
							'label' => [
								__( 'Main Image Ratio', 'blocksy' ) => [
									'product_view_type' => 'default-gallery|top-gallery'
								],
								__( 'Image Ratio', 'blocksy' ) => [
									'product_view_type' => 'stacked-gallery|columns-top-gallery'
								],
							],
							'type' => 'ct-ratio',
							'value' => '3/4',
							'design' => 'block',
							'attr' => [ 'data-type' => 'compact' ],
							'setting' => [ 'transport' => 'postMessage' ],
							'preview_width_key' => 'woocommerce_single_image_width',
							'view' => 'inline',
							'inner-options' => [

								'woocommerce_single_image_width' => [
									'type' => 'text',
									'label' => __('Image Size', 'blocksy'),
									'desc' => __('Image size used for the main image on single product pages.', 'blocksy'),
									'value' => 600,
									'design' => 'inline',
									'setting' => [
										'type' => 'option',
										'capability' => 'manage_woocommerce',
									]
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-divider',
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'product_view_type' => 'default-gallery' ],
							'options' => [

								'gallery_style' => [
									'label' => __('Thumbnails Position', 'blocksy'),
									'type' => 'ct-radio',
									'value' => 'horizontal',
									'view' => 'text',
									'design' => 'block',
									'divider' => 'bottom',
									'choices' => [
										'horizontal' => __( 'Horizontal', 'blocksy' ),
										'vertical' => __( 'Vertical', 'blocksy' ),
									],

									'sync' => blocksy_sync_whole_page([
										'loader_selector' => '.woocommerce-product-gallery',
										'prefix' => 'product'
									])
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								'product_view_type' => 'default-gallery|top-gallery',
							],
							'options' => [

								'gallery_thumbnail_image_width' => [
									'type' => 'text',
									'label' => __('Thumbnails Size', 'blocksy'),
									'desc' => __('Image size used for the gallery thumbnails on single product pages.', 'blocksy'),
									'value' => 100,
									'design' => 'block',
									'divider' => 'bottom',
								],

							],
						],

					],

					apply_filters(
						'blocksy:options:single_product:gallery-options:start',
						[]
					),

					[
						'product_thumbs_spacing' => [
							'label' => [
								__( 'Thumbnails Spacing', 'blocksy' ) => [
									'product_view_type' => '!columns-top-gallery|!stacked-gallery'
								],
								__( 'Columns Spacing', 'blocksy' ) => [
									'product_view_type' => 'columns-top-gallery|stacked-gallery'
								],
							],
							'type' => 'ct-slider',
							'value' => '15px',
							'units' => blocksy_units_config([
								[ 'unit' => 'px', 'min' => 0, 'max' => 100 ],
							]),
							'responsive' => true,
							'setting' => [ 'transport' => 'postMessage' ],
						],

					],

					apply_filters(
						'blocksy:options:single_product:gallery-options:end',
						[]
					),

				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					'slider_nav_arrow_color' => [
						'label' => __( 'Prev/Next Arrow', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
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
								'inherit' => 'var(--theme-text-color)'
							],

							[
								'title' => __( 'Hover', 'blocksy' ),
								'id' => 'hover',
								'inherit' => '#ffffff'
							],
						],
					],

					'slider_nav_background_color' => [
						'label' => __( 'Prev/Next Background', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
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
								'inherit' => '#ffffff'
							],

							[
								'title' => __( 'Hover', 'blocksy' ),
								'id' => 'hover',
								'inherit' => 'var(--theme-palette-color-1)'
							],
						],
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [ 'has_product_single_lightbox' => 'yes' ],
						'options' => [

							'lightbox_button_icon_color' => [
								'label' => __( 'Lightbox Button Icon Color', 'blocksy' ),
								'type'  => 'ct-color-picker',
								'design' => 'inline',
								'divider' => 'top',
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
										'inherit' => 'var(--theme-text-color)'
									],

									[
										'title' => __( 'Hover', 'blocksy' ),
										'id' => 'hover',
										'inherit' => '#ffffff'
									],
								],
							],

							'lightbox_button_background_color' => [
								'label' => __( 'Lightbox Button Background', 'blocksy' ),
								'type'  => 'ct-color-picker',
								'design' => 'inline',
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
										'inherit' => '#ffffff'
									],

									[
										'title' => __( 'Hover', 'blocksy' ),
										'id' => 'hover',
										'inherit' => 'var(--theme-palette-color-1)'
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
