<?php

$options = [
	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['field' => 'wp:excerpt'],
		'options' => [
			'excerpt_length' => [
				'label' => __('Length', 'blocksy'),
				'type' => 'ct-number',
				'design' => 'inline',
				'value' => 40,
				'min' => 1,
				'max' => 300,
			],
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['field' => 'wp:date'],
		'options' => [
			'date_type' => [
				'label' => __('Date type', 'blocksy'),
				'type' => 'ct-select',
				'value' => 'published',
				'design' => 'inline',
				'purpose' => 'default',
				'choices' => blocksy_ordered_keys(
					[
						'published' => __('Published Date', 'blocksy'),
						'modified' => __('Modified Date', 'blocksy'),
					]
				),
			],

			'default_format' => [
				'type'  => 'ct-switch',
				'label' => __('Default format', 'blocksy'),
				'value' => 'yes',
				'desc' => __('Example: January 24, 2022', 'blocksy'),
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => ['default_format' => 'no'],
				'options' => [
					'date_format' => [
						'label' => __('Date type', 'blocksy'),
						'type' => 'ct-select',
						'value' => 'F j, Y',
						'design' => 'inline',
						'purpose' => 'default',
						'choices' => blocksy_ordered_keys(
							[
								'F j, Y' => date_i18n('F j, Y'),
								'Y-m-d' => date_i18n('Y-m-d'),
								'm/d/Y' => date_i18n('m/d/Y'),
								'd/m/Y' => date_i18n('d/m/Y'),
								'd.m.Y' => date_i18n('d.m.Y'),
								'd-m-Y' => date_i18n('d-m-Y'),
								'd.m.Y.' => date_i18n('d.m.Y.'),
								'd-m-Y' => date_i18n('d-m-Y'),
								'custom' => __('Custom', 'blocksy'),
							]
						),
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => ['date_format' => 'custom'],
						'options' => [
							'custom_date_format' => [
								'type' => 'text',
								'label' => __('Custom date format', 'blocksy'),
								'value' => 'F j, Y',
								'desc' => sprintf(
									'%s <a href="%s" target="_blank">format string</a>',
									__('Enter a date or time', 'blocksy'),
									'https://wordpress.org/documentation/article/customize-date-and-time-format/'
								),
							],
						]
					]
				]
			],
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['field' => 'wp:comments'],
		'options' => [
			'zero_text' => [
				'type' => 'text',
				'label' => __('No comments', 'blocksy'),
				'value' => __('No comments', 'blocksy'),
			],

			'single_text' => [
				'type' => 'text',
				'label' => __('One comment', 'blocksy'),
				'value' => __('One comment', 'blocksy'),
			],

			'multiple_text' => [
				'type' => 'text',
				'label' => __('Multiple comments', 'blocksy'),
				'value' => __('% comments', 'blocksy'),
			]
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['field' => 'wp:terms'],
		'options' => [
			'separator' => [
				'type' => 'text',
				'label' => __('Separator', 'blocksy'),
				'value' => ', ',
			],
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['field' => 'wp:author'],
		'options' => [
			'author_field' => [
				'type' => 'ct-select',
				'label' => __('Author Field', 'blocksy'),
				'value' => 'email',
				'design' => 'inline',
				'purpose' => 'default',
				'choices' => blocksy_ordered_keys(
					[
						'email' => __('Email', 'blocksy'),
						'nicename' => __('Nicename', 'blocksy'),
						'display_name' => __('Display Name', 'blocksy'),
						'first_name' => __('First Name', 'blocksy'),
						'last_name' => __('Last Name', 'blocksy'),
						'description' => __('Description', 'blocksy')
					]
				),
			]
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [
			'field' => implode('|', [
				'wp:title',
				'wp:date',
				'wp:author',
				'wp:terms',
				'wp:comments',

				'wp:author_avatar',
				'wp:featured_image',
			])
		],
		'options' => [
			'has_field_link' => [
				'type'  => 'ct-switch',
				'label' => [
					__('Link to post', 'blocksy') => [
						'field' => 'wp:title'
					],

					__('Link to post', 'blocksy') => [
						'field' => 'wp:date'
					],

					__('Link to author page', 'blocksy') => [
						'field' => 'wp:author'
					],

					__('Link to user page', 'blocksy') => [
						'field' => 'wp:author_avatar'
					],

					__('Link to term page', 'blocksy') => [
						'field' => 'wp:terms'
					],

					__('Link to post', 'blocksy') => [
						'field' => 'wp:comments'
					]
				],
				'value' => 'no',
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => ['has_field_link' => 'yes'],
				'options' => [
					'has_field_link_new_tab' => [
						'type'  => 'ct-switch',
						'label' => __('Open in new tab', 'blocksy'),
						'value' => 'no',
					],

					'has_field_link_rel' => [
						'type' => 'text',
						'label' => __('Link Rel', 'blocksy'),
						'value' => '',
					],
				]
			]
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [
			'field' => 'wp:terms',
			'has_taxonomies_customization' => 'yes'
		],
		'options' => [
			'termAccentColor' => [
				'type'  => 'ct-switch',
				'label' => __('Terms accent color', 'blocksy'),
				'divider' => 'top:full',
				'value' => 'yes',
			]
		]
	],
];

