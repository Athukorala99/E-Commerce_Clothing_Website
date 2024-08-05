<?php

if (! isset($prefix)) {
	$prefix = '';
} else {
	$prefix = $prefix . '_';
}

if (! isset($enabled)) {
	$enabled = 'no';
}

if (! isset($post_type)) {
	$post_type = 'post';
}

$options = [
	$prefix . 'has_post_nav' => [
		'label' => __( 'Posts Navigation', 'blocksy' ),
		'type' => 'ct-panel',
		'switch' => true,
		'value' => $enabled,
		'sync' => blocksy_sync_single_post_container([
			'prefix' => $prefix
		]),
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					$prefix . 'post_nav_criteria' => [
						'label' => __( 'Navigation Criteria', 'blocksy' ),
						'type' => 'ct-radio',
						'value' => 'default',
						'view' => 'text',
						'design' => 'block',
						'setting' => [ 'transport' => 'postMessage' ],
						'choices' => [
							'default' => __( 'Default', 'blocksy' ),
							'taxonomy' => __( 'Taxonomy', 'blocksy' ),
						],
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [ $prefix . 'post_nav_criteria' => 'taxonomy' ],
						'options' => [

							$prefix . 'post_nav_taxonomy' => [
								'label' => __( 'Taxonomy', 'blocksy' ),
								'desc' => __( 'Navigate through posts that are from the same taxonomy.', 'blocksy' ),
								'type' => 'ct-select',
								'value' => array_keys(blocksy_get_taxonomies_for_cpt(
									$post_type
								))[0],
								'view' => 'text',
								'design' => 'inline',
								'choices' => blocksy_ordered_keys(
									blocksy_get_taxonomies_for_cpt($post_type)
								),
								'sync' => [
									'prefix' => $prefix,
									'selector' => '.post-navigation',
									'render' => function () {
										echo blocksy_post_navigation();
									}
								]
							],

						],
					],

					$prefix . 'post_nav_spacing' => [
						'label' => __( 'Container Spacing', 'blocksy' ),
						'type' => 'ct-slider',
						'value' => '50px',
						'units' => blocksy_units_config([
							[ 'unit' => 'px', 'min' => 0, 'max' => 200],
							['unit' => '', 'type' => 'custom'],
						]),
						'responsive' => true,
						'sync' => 'live',
						'divider' => 'top:bottom',
					],

					$prefix . 'post_nav_thumb_size' => [
						'label' => __('Image Size', 'blocksy'),
						'type' => 'ct-select',
						'value' => 'medium',
						'view' => 'text',
						'design' => 'inline',
						'divider' => 'bottom',
						'choices' => blocksy_ordered_keys(
							blocksy_get_all_image_sizes()
						),
						'sync' => [
							'prefix' => $prefix,
							'selector' => '.post-navigation',
							'render' => function () {
								echo blocksy_post_navigation();
							}
						]
					],

					$prefix . 'post_nav_thumb_visibility' => [
						'label' => __( 'Image Visibility', 'blocksy' ),
						'type' => 'ct-visibility',
						'design' => 'block',
						'sync' => 'live',
						'divider' => 'bottom',
						'allow_empty' => true,
						'value' => [
							'desktop' => true,
							'tablet' => true,
							'mobile' => true,
						],

						'choices' => blocksy_ordered_keys([
							'desktop' => __( 'Desktop', 'blocksy' ),
							'tablet' => __( 'Tablet', 'blocksy' ),
							'mobile' => __( 'Mobile', 'blocksy' ),
						]),
					],

					$prefix . 'post_nav_title_visibility' => [
						'label' => __( 'Title Visibility', 'blocksy' ),
						'type' => 'ct-visibility',
						'design' => 'block',
						'sync' => 'live',
						'divider' => 'bottom',
						'allow_empty' => true,
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

					$prefix . 'post_nav_visibility' => [
						'label' => __( 'Module Visibility', 'blocksy' ),
						'type' => 'ct-visibility',
						'design' => 'block',
						'sync' => 'live',
						'value' => [
							'desktop' => true,
							'tablet' => true,
							'mobile' => true,
						],

						'choices' => blocksy_ordered_keys([
							'desktop' => __( 'Desktop', 'blocksy' ),
							'tablet' => __( 'Tablet', 'blocksy' ),
							'mobile' => __( 'Mobile', 'blocksy' ),
						]),
					],

				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					$prefix . 'posts_nav_font_color' => [
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

					$prefix . 'posts_nav_image_overlay_color' => [
						'label' => __( 'Thumbnail Overlay Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
						'sync' => 'live',
						'divider' => 'top',
						'value' => [
							'hover' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],
						],
						'pickers' => [
							[
								'title' => __( 'Hover', 'blocksy' ),
								'id' => 'hover',
								'inherit' => 'var(--theme-palette-color-1)'
							],
						],
					],

					$prefix . 'posts_nav_image_border_radius' => [
						'label' => __( 'Thumbnail Border Radius', 'blocksy' ),
						'type' => 'ct-spacing',
						'divider' => 'top',
						'value' => blocksy_spacing_value(),
						'inputAttr' => [
							'placeholder' => '100'
						],
						'sync' => 'live',
					],

				],
			],

		],
	],

];

