<?php

$options = [
	blocksy_rand_md5() => [
		'title' => __( 'General', 'blocksy' ),
		'type' => 'tab',
		'options' => [

			'footer_socials' => [
				'label' => false,
				'type' => 'ct-layers',
				'manageable' => true,
				'setting' => [ 'transport' => 'postMessage' ],
				'value' => [
					[
						'id' => 'facebook',
						'enabled' => true,
					],

					[
						'id' => 'twitter',
						'enabled' => true,
					],

					[
						'id' => 'instagram',
						'enabled' => true,
					],
				],

				'settings' => apply_filters(
					'blocksy:socials:options:icon',
					blocksy_get_social_networks_list()
				),
				'desc' => sprintf(
					// translators: placeholder here means the actual URL.
					__( 'Configure the social links in General ➝ %sSocial Network Accounts%s.', 'blocksy' ),
					sprintf(
						'<a data-trigger-section="general:social_section_options" href="%s">',
						admin_url('/customize.php?autofocus[section]=general&ct_autofocus=general:social_section_options')
					),
					'</a>'
				),
			],

			blocksy_rand_md5() => [
				'type' => 'ct-divider',
			],

			'link_target' => [
				'type'  => 'ct-switch',
				'label' => __( 'Open links in new tab', 'blocksy' ),
				'value' => 'no',
				'disableRevertButton' => true,
			],

			'link_nofollow' => [
				'type'  => 'ct-switch',
				'label' => __( 'Set links to nofollow', 'blocksy' ),
				'value' => 'no',
			],

			blocksy_rand_md5() => [
				'type' => 'ct-divider',
			],

			'socialsIconSize' => [
				'label' => __( 'Icons Size', 'blocksy' ),
				'type' => 'ct-slider',
				'min' => 5,
				'max' => 50,
				'value' => 15,
				'responsive' => true,
				'setting' => [ 'transport' => 'postMessage' ],
			],

			'socialsIconSpacing' => [
				'label' => __( 'Icons Spacing', 'blocksy' ),
				'type' => 'ct-slider',
				'min' => 0,
				'max' => 50,
				'value' => 15,
				'responsive' => true,
				'divider' => 'bottom',
				'setting' => [ 'transport' => 'postMessage' ],
			],

			'footerSocialsColor' => [
				'label' => __('Icons Color', 'blocksy'),
				'type' => 'ct-radio',
				'value' => 'custom',
				'view' => 'text',
				'design' => 'block',
				'setting' => [ 'transport' => 'postMessage' ],
				'choices' => [
					'custom' => __( 'Custom', 'blocksy' ),
					'official' => __( 'Official', 'blocksy' ),
				],
			],

			'socialsType' => [
				'label' => __('Icons Shape Type', 'blocksy'),
				'type' => 'ct-radio',
				'value' => 'simple',
				'view' => 'text',
				'design' => 'block',
				'setting' => [ 'transport' => 'postMessage' ],
				'choices' => [
					'simple' => __( 'None', 'blocksy' ),
					'rounded' => __( 'Rounded', 'blocksy' ),
					'square' => __( 'Square', 'blocksy' ),
				],
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => [ 'socialsType' => '!simple' ],
				'options' => [

					'socialsFillType' => [
						'label' => __('Shape Fill Type', 'blocksy'),
						'type' => 'ct-radio',
						'value' => 'solid',
						'view' => 'text',
						'design' => 'block',
						'setting' => [ 'transport' => 'postMessage' ],
						'choices' => [
							'solid' => __( 'Solid', 'blocksy' ),
							'outline' => __( 'Outline', 'blocksy' ),
						],
					],

				],
			],

			'footer_socials_direction' => [
				'type' => 'ct-radio',
				'label' => __( 'Items Direction', 'blocksy' ),
				'view' => 'text',
				'design' => 'block',
				'divider' => 'top:full',
				'setting' => [ 'transport' => 'postMessage' ],
				'value' => 'row',
				'choices' => [
					'row' => __( 'Horizontal', 'blocksy' ),
					'column' => __( 'Vertical', 'blocksy' ),
				],
			],

			'footerSocialsAlignment' => [
				'type' => 'ct-radio',
				'label' => __( 'Horizontal Alignment', 'blocksy' ),
				'view' => 'text',
				'design' => 'block',
				'divider' => 'top:full',
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

			'footerSocialsVerticalAlignment' => [
				'type' => 'ct-radio',
				'label' => __( 'Vertical Alignment', 'blocksy' ),
				'view' => 'text',
				'design' => 'block',
				'divider' => 'top',
				'responsive' => true,
				'attr' => [ 'data-type' => 'vertical-alignment' ],
				'setting' => [ 'transport' => 'postMessage' ],
				'value' => 'CT_CSS_SKIP_RULE',
				'choices' => [
					'flex-start' => '',
					'center' => '',
					'flex-end' => '',
				],
			],

			'socialsLabelVisibility' => [
				'label' => __('Label Visibility', 'blocksy'),
				'type' => 'ct-visibility',
				'design' => 'block',
				'divider' => 'top:full',
				'allow_empty' => true,
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

			'footer_socials_visibility' => [
				'label' => __( 'Element Visibility', 'blocksy' ),
				'type' => 'ct-visibility',
				'design' => 'block',
				'divider' => 'top:full',
				'setting' => [ 'transport' => 'postMessage' ],
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

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => [ 'footerSocialsColor' => 'custom' ],
				'options' => [

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [
							'any' => [
								'socialsLabelVisibility/desktop' => true,
								'socialsLabelVisibility/tablet' => true,
								'socialsLabelVisibility/mobile' => true,
							]
						],
						'options' => [
							'footer_socials_label_font' => [
								'type' => 'ct-typography',
								'label' => __( 'Label Font', 'blocksy' ),
								'value' => blocksy_typography_default_values([
									'size' => '12px',
									'variation' => 'n6',
									'text-transform' => 'uppercase',
								]),
								'setting' => [ 'transport' => 'postMessage' ],
							],

							'footer_socials_font_color' => [
								'label' => __( 'Font Color', 'blocksy' ),
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
										'inherit' => 'var(--theme-text-color)'
									],

									[
										'title' => __( 'Hover', 'blocksy' ),
										'id' => 'hover',
										'inherit' => 'var(--theme-link-hover-color)'
									],
								],
							],

							blocksy_rand_md5() => [
								'type' => 'ct-divider',
							],
						],
					],

					'footerSocialsIconColor' => [
						'label' => __( 'Icons Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'block:right',
						'divider' => 'bottom',
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
								'inherit' => 'var(--theme-text-color)'
							],

							[
								'title' => __( 'Hover', 'blocksy' ),
								'id' => 'hover',
								'inherit' => 'var(--theme-palette-color-2)'
							],
						],
					],

				],
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => [ 'socialsType' => '!simple' ],
				'options' => [

					'footerSocialsIconBackground' => [
						'label' => [
							__('Icons Background Color', 'blocksy') => [
								'socialsFillType' => 'solid'
							],

							__('Icons Border Color', 'blocksy') => [
								'socialsFillType' => 'outline'
							]
						],
						'type'  => 'ct-color-picker',
						'design' => 'block:right',
						'divider' => 'bottom',
						'responsive' => true,
						'setting' => [ 'transport' => 'postMessage' ],

						'value' => [
							'default' => [
								'color' => 'rgba(218, 222, 228, 0.3)',
							],

							'hover' => [
								'color' => 'var(--theme-palette-color-1)',
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

			'footerSocialsMargin' => [
				'label' => __( 'Margin', 'blocksy' ),
				'type' => 'ct-spacing',
				'setting' => [ 'transport' => 'postMessage' ],
				'value' => blocksy_spacing_value(),
				'responsive' => true
			],

		],
	],
];

