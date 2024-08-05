<?php

$options = [

	'footer_container_structure' => [
		'label' => __( 'Container Structure', 'blocksy' ),
		'type' => 'ct-radio',
		'value' => 'fixed',
		'view' => 'text',
		'design' => 'block',
		'choices' => [
			'fixed' => __( 'Default', 'blocksy' ),
			'boxed' => __( 'Boxed', 'blocksy' ),
			'fluid' => __( 'Full Width', 'blocksy' ),
		],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [ 'footer_container_structure' => 'boxed' ],
		'options' => [

			'footer_boxed_offset' => [
				'label' => __( 'Container Bottom Offset', 'blocksy' ),
				'type' => 'ct-slider',
				'min' => 0,
				'max' => 300,
				'value' => 50,
				'responsive' => true,
				'divider' => 'top',
				'setting' => [ 'transport' => 'postMessage' ],
			],

		],
	],

	'has_reveal_effect' => [
		'label' => __( 'Enable reveal effect on', 'blocksy' ),
		'type' => 'ct-visibility',
		'design' => 'block',
		'divider' => 'top:full',
		'allow_empty' => true,
		'desc' => __('Enables a nice reveal effect as you scroll down.', 'blocksy'),
		'setting' => ['transport' => 'postMessage'],

		'value' => [
			'desktop' => false,
			'tablet' => false,
			'mobile' => false,
		],

		'choices' => blocksy_ordered_keys([
			'desktop' => __('Desktop', 'blocksy'),
			'tablet' => __('Tablet', 'blocksy'),
			'mobile' => __('Mobile', 'blocksy'),
		]),
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['has_reveal_effect:visibility' => 'yes'],
		'options' => [

			'footerShadow' => [
				'label' => __( 'Shadow', 'blocksy' ),
				'type' => 'ct-box-shadow',
				'responsive' => true,
				'divider' => 'top',
				'hide_shadow_placement' => true,
				'value' => blocksy_box_shadow_value([
					'enable' => true,
					'h_offset' => 0,
					'v_offset' => 30,
					'blur' => 50,
					'spread' => 0,
					'inset' => false,
					'color' => [
						'color' => 'rgba(0, 0, 0, 0.1)',
					],
				])
			],

		],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-divider',
	],

	'footerBackground' => [
		'label' => __( 'Container Background', 'blocksy' ),
		'type' => 'ct-background',
		'design' => 'block:right',
		'responsive' => true,
		'setting' => [ 'transport' => 'postMessage' ],
		'value' => blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => 'var(--theme-palette-color-6)'
				],
			],
		]),
		'desc' => __( 'Please note, you can also change the background color for each row individually.', 'blocksy' ),
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [ 'footer_container_structure' => '!boxed' ],
		'options' => [

			'footer_spacing' => [
				'label' => __( 'Container Padding', 'blocksy' ),
				'type' => 'ct-spacing',
				'divider' => 'top',
				'setting' => [ 'transport' => 'postMessage' ],
				'value' => blocksy_spacing_value(),
				'responsive' => true
			],

		],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [ 'footer_container_structure' => 'boxed' ],
		'options' => [

			'footer_boxed_spacing' => [
				'label' => __( 'Container Padding', 'blocksy' ),
				'type' => 'ct-spacing',
				'divider' => 'top',
				'setting' => [ 'transport' => 'postMessage' ],
				'value' => [
					'desktop' => blocksy_spacing_value([
						'top' => '0px',
						'left' => '35px',
						'right' => '35px',
						'bottom' => '0px',
					]),
					'tablet' => blocksy_spacing_value([
						'top' => '0vw',
						'left' => '4vw',
						'right' => '4vw',
						'bottom' => '0vw',
					]),
					'mobile'=> blocksy_spacing_value([
						'top' => '0vw',
						'left' => '5vw',
						'right' => '5vw',
						'bottom' => '0vw',
					]),
				],
				'responsive' => true
			],

			'footer_container_border_radius' => [
				'label' => __( 'Container Border Radius', 'blocksy' ),
				'type' => 'ct-spacing',
				'divider' => 'top',
				'setting' => [ 'transport' => 'postMessage' ],
				'value' => blocksy_spacing_value(),
				'responsive' => true
			],

		],
	],

];
