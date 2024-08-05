<?php

$prefix = 'tribe_events_single_';

$options = [
	'tribe_events_single_options' => [
		'type' => 'ct-options',
		'inner-options' => [
			blocksy_get_options('general/page-title', [
				'prefix' => 'tribe_events_single',
				'is_single' => true,
				'is_page' => true,
				'enabled_default' => 'no',
				'enabled_label' => sprintf(
					__('%s Title', 'blocksy'),
					'Events Calendar Single'
				)
			]),

			[
				blocksy_rand_md5() => [
					'type' => 'ct-title',
					'label' => __( 'Events Calendar Single Structure', 'blocksy' ),
				],

				blocksy_rand_md5() => [
					'title' => __( 'General', 'blocksy' ),
					'type' => 'tab',
					'options' => [
						blocksy_get_options('single-elements/structure', [
							'default_structure' => 'type-4',
							'prefix' => 'tribe_events_single',
						]),
					],
				],

				blocksy_rand_md5() => [
					'title' => __( 'Design', 'blocksy' ),
					'type' => 'tab',
					'options' => [

						blocksy_get_options('single-elements/structure-design', [
							'prefix' => 'tribe_events_single',
						])

					],
				],
			],

		]
	]
];


