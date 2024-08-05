<?php

$prefix = 'tribe_events_archive_';

$options = [
	'tribe_events_archive_options' => [
		'type' => 'ct-options',
		'inner-options' => [
			blocksy_get_options('general/page-title', [
				'prefix' => 'tribe_events_archive',
				'is_single' => true,
				'is_page' => true,
				'enabled_label' => sprintf(
					__('%s Title', 'blocksy'),
					'Events Calendar Archive'
				)
			]),

			blocksy_rand_md5() => [
				'type' => 'ct-title',
				'label' => __( 'Events Calendar Archive Structure', 'blocksy' ),
			],

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [
					blocksy_get_options('single-elements/structure', [
						'default_structure' => 'type-4',
						'prefix' => 'tribe_events_archive',
					]),
				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					blocksy_get_options('single-elements/structure-design', [
						'prefix' => 'tribe_events_archive',
					])

				],
			],

		]
	]
];


