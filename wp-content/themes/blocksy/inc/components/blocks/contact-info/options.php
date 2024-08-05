<?php
/**
 * Contact Info widget
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */
$is_pro = function_exists('blc_fs') && blc_fs()->can_use_premium_code();

$options = [
	'title' => [
		'type' => 'hidden',
		'label' => __('Title', 'blocksy'),
		'value' => __('Contact Info', 'blocksy'),
	],

	'contact_text' => [
		'label' => __('Text', 'blocksy'),
		'type' => 'hidden',
	],

	'contact_information' => [
		'label' => __('Contact Information', 'blocksy'),
		'type' => 'ct-layers',
		'manageable' => true,
		'value' => [
			[
				'id' => 'address',
				'enabled' => true,
				'title' => __('Address:', 'blocksy'),
				'content' => 'Street Name, NY 38954',
				'link' => '',
			],

			[
				'id' => 'phone',
				'enabled' => true,
				'title' => __('Phone:', 'blocksy'),
				'content' => '578-393-4937',
				'link' => 'tel:578-393-4937',
			],

			[
				'id' => 'mobile',
				'enabled' => true,
				'title' => __('Mobile:', 'blocksy'),
				'content' => '578-393-4937',
				'link' => 'tel:578-393-4937',
			],
		],

		'settings' => [
			'address' => [
				'label' => __('Address', 'blocksy'),
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy'),
							'value' => __('Address:', 'blocksy'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy'),
							'value' => 'Street Name, NY 38954',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy'),
							'value' => '',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy'),
									'custom' => __('Custom', 'blocksy'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-map-pin',
										],
									],
								],
							],
						]
						: [],
				],

				'clone' => true,
			],

			'phone' => [
				'label' => __('Phone', 'blocksy'),
				'clone' => true,
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy'),
							'value' => __('Phone:', 'blocksy'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy'),
							'value' => '578-393-4937',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy'),
							'value' => 'tel:578-393-4937',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy'),
									'custom' => __('Custom', 'blocksy'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-phone',
										],
									],
								],
							],
						]
						: [],
				],
			],

			'mobile' => [
				'label' => __('Mobile', 'blocksy'),
				'clone' => true,
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy'),
							'value' => __('Mobile:', 'blocksy'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy'),
							'value' => '578-393-4937',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy'),
							'value' => 'tel:578-393-4937',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy'),
									'custom' => __('Custom', 'blocksy'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-mobile-phone',
										],
									],
								],
							],
						]
						: [],
				],
			],

			'hours' => [
				'label' => __('Work Hours', 'blocksy'),
				'clone' => true,
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy'),
							'value' => __('Opening hours', 'blocksy'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy'),
							'value' => '9AM - 5PM',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy'),
							'value' => '',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy'),
									'custom' => __('Custom', 'blocksy'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-clock',
										],
									],
								],
							],
						]
						: [],
				],
			],

			'fax' => [
				'label' => __('Fax', 'blocksy'),
				'clone' => true,
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy'),
							'value' => __('Fax:', 'blocksy'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy'),
							'value' => '578-393-4937',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy'),
							'value' => 'tel:578-393-4937',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy'),
									'custom' => __('Custom', 'blocksy'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-fax',
										],
									],
								],
							],
						]
						: [],
				],
			],

			'email' => [
				'label' => __('Email', 'blocksy'),
				'clone' => 2,
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy'),
							'value' => __('Email:', 'blocksy'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy'),
							'value' => 'contact@yourwebsite.com',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy'),
							'value' => 'mailto:contact@yourwebsite.com',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy'),
									'custom' => __('Custom', 'blocksy'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-email',
										],
									],
								],
							],
						]
						: [],
				],
			],

			'website' => [
				'label' => __('Website', 'blocksy'),
				'clone' => true,
				'options' => [
					[
						'title' => [
							'type' => 'text',
							'label' => __('Title', 'blocksy'),
							'value' => __('Website:', 'blocksy'),
							'design' => 'block',
						],

						'content' => [
							'type' => 'text',
							'label' => __('Content', 'blocksy'),
							'value' => 'creativethemes.com',
							'design' => 'block',
						],

						'link' => [
							'type' => 'text',
							'label' => __('Link (optional)', 'blocksy'),
							'value' => 'https://creativethemes.com',
							'design' => 'block',
						],
					],

					$is_pro
						? [
							'icon_source' => [
								'label' => __('Icon Source', 'blocksy'),
								'type' => 'ct-radio',
								'value' => 'default',
								'view' => 'text',
								'design' => 'block',
								'setting' => ['transport' => 'postMessage'],
								'choices' => [
									'default' => __('Default', 'blocksy'),
									'custom' => __('Custom', 'blocksy'),
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-condition',
								'condition' => ['icon_source' => 'custom'],
								'options' => [
									'icon' => [
										'type' => 'icon-picker',
										'label' => __('Icon', 'blocksy'),
										'design' => 'block',
										'value' => [
											'icon' => 'blc blc-globe',
										],
									],
								],
							],
						]
						: [],
				],
			],
		],
	],

	'contact_link_target' => [
		'type' => 'ct-switch',
		'label' => __('Open link in new tab', 'blocksy'),
		'value' => 'no',
		'divider' => 'top:full'
	],

	'link_icons' => [
		'type'  => 'ct-switch',
		'label' => __( 'Link Icons', 'blocksy' ),
		'value' => 'no',
	],

	'contacts_icons_size' => [
		'label' => __( 'Icons Size', 'blocksy' ),
		'type' => 'ct-slider',
		'min' => 5,
		'max' => 50,
		'value' => 20,
		'responsive' => false,
		'divider' => 'top:full',
	],

	'contacts_items_spacing' => [
		'label' => __( 'Items Spacing', 'blocksy' ),
		'type' => 'ct-slider',
		'min' => 5,
		'max' => 50,
		'value' => '',
		'responsive' => false,
	],

	'contacts_icon_shape' => [
		'label' => __('Icons Shape Type', 'blocksy'),
		'type' => 'ct-radio',
		'value' => 'rounded',
		'view' => 'text',
		'divider' => 'top:full',
		'setting' => ['transport' => 'postMessage'],
		'choices' => [
			'simple' => __('None', 'blocksy'),
			'rounded' => __('Rounded', 'blocksy'),
			'square' => __('Square', 'blocksy'),
		],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['contacts_icon_shape' => '!simple'],
		'options' => [
			'contacts_icon_fill_type' => [
				'label' => __('Shape Fill Type', 'blocksy'),
				'type' => 'ct-radio',
				'value' => 'outline',
				'view' => 'text',
				'setting' => ['transport' => 'postMessage'],
				'choices' => [
					'outline' => __('Outline', 'blocksy'),
					'solid' => __('Solid', 'blocksy'),
				],
			],
		],
	],

	'contacts_items_direction' => [
		'type' => 'ct-radio',
		'label' => __( 'Items Direction', 'blocksy' ),
		'view' => 'text',
		'design' => 'block',
		'divider' => 'top:full',
		'value' => 'column',
		'choices' => [
			'column' => __( 'Vertical', 'blocksy' ),
			'row' => __( 'Horizontal', 'blocksy' ),
		],
		'setting' => [ 'transport' => 'postMessage' ],
	],
];
