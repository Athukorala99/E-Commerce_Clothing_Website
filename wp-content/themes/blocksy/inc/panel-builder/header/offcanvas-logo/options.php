<?php

$options = [

	blocksy_rand_md5() => [
		'title' => __( 'General', 'blocksy' ),
		'type' => 'tab',
		'options' => [
			[
				'custom_logo' => [
					'label' => __( 'Logo', 'blocksy' ),
					'type' => 'ct-image-uploader',
					'value' => blocksy_get_theme_mod('custom_logo', ''),
					'inline_value' => true,
					'responsive' => [
						'tablet' => 'skip'
					],
					'attr' => [ 'data-type' => 'small' ],
				],
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => [
					'any' => [
						'custom_logo:truthy' => 'yes',
						'all' => [
							'builderSettings/has_transparent_header' => 'yes',
							'transparent_logo:truthy' => 'yes',
						]
					]
				],
				'options' => [
					apply_filters(
						'blocksy:panel-builder:offcanvas-logo:options:general',
						[],
						$panel_type
					),
				]
			],

			'off_canvas_logo_max_height' => [
				'label' => __( 'Logo Height', 'blocksy' ),
				'type' => 'ct-slider',
				'divider' => 'top:full',
				'min' => 0,
				'max' => 300,
				'value' => 50,
				'responsive' => true,
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => [
					'has_svg_logo' => 'yes'
				],
				'computed_fields' => ['has_svg_logo'],
				'options' => [
					'inline_svg_logos' => [
						'label' => __('Inline SVG File', 'blocksy'),
						'type' => 'ct-switch',
						'value' => 'no',
						'divider' => 'top:full',
					]
				]
			],
		],
	],

	blocksy_rand_md5() => [
		'title' => __( 'Design', 'blocksy' ),
		'type' => 'tab',
		'options' => [

			'off_canvas_logo_margin' => [
				'label' => __( 'Margin', 'blocksy' ),
				'type' => 'ct-spacing',
				'setting' => [ 'transport' => 'postMessage' ],
				'value' => blocksy_spacing_value(),
				'responsive' => true
			],

		],
	],

];
