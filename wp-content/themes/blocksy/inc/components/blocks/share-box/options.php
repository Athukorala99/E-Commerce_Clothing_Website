<?php
/**
 * Options for shares widget.
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package Blocksy
 */

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$options = [
	'title' => [
		'type' => 'hidden',
		'label' => __('Title', 'blocksy'),
		'value' => __('Share Icons', 'blocksy'),
	],
	
	'share_facebook' => [
		'label' => __( 'Facebook', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'yes',
	],

	'share_twitter' => [
		'label' => __( 'X (Twitter)', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'yes',
	],

	'share_pinterest' => [
		'label' => __( 'Pinterest', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'yes',
	],

	'share_linkedin' => [
		'label' => __( 'LinkedIn', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'yes',
	],

	'share_reddit' => [
		'label' => __( 'Reddit', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'no',
	],

	'share_hacker_news' => [
		'label' => __( 'Hacker News', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'no',
	],

	'share_vk' => [
		'label' => __( 'VKontakte', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'no',
	],

	'share_ok' => [
		'label' => __( 'Odnoklassniki', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'no',
	],

	'share_telegram' => [
		'label' => __( 'Telegram', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'no',
	],

	'share_viber' => [
		'label' => __( 'Viber', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'no',
	],

	'share_whatsapp' => [
		'label' => __( 'WhatsApp', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'no',
	],

	'share_flipboard' => [
		'label' => __( 'Flipboard', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'no',
	],

	'share_email' => [
		'label' => __( 'Email', 'blocksy' ),
		'type' => 'ct-switch',
		'value' => 'no',
	],

	'link_nofollow' => [
		'type' => 'ct-switch',
		'label' => __('Set links to nofollow', 'blocksy'),
		'value' => 'no',
	],

	'share_icons_size' => [
		'label' => __( 'Icons Size', 'blocksy' ),
		'type' => 'ct-slider',
		'min' => 5,
		'max' => 50,
		'value' => '',
		'responsive' => false,
		'divider' => 'top:full',
	],

	'items_spacing' => [
		'label' => __( 'Icons Spacing', 'blocksy' ),
		'type' => 'ct-slider',
		'min' => 5,
		'max' => 50,
		'value' => '',
		'responsive' => false,
	],

	'share_icons_color' => [
		'label' => __('Icons Color', 'blocksy'),
		'type' => 'ct-radio',
		'value' => 'default',
		'view' => 'text',
		'divider' => 'top:full',
		'setting' => ['transport' => 'postMessage'],
		'choices' => [
			'default' => __('Custom', 'blocksy'),
			'official' => __('Official', 'blocksy'),
		],
	],

	'share_type' => [
		'label' => __('Icons Shape Type', 'blocksy'),
		'type' => 'ct-radio',
		'value' => 'simple',
		'view' => 'text',
		'setting' => ['transport' => 'postMessage'],
		'choices' => [
			'simple' => __('None', 'blocksy'),
			'rounded' => __('Rounded', 'blocksy'),
			'square' => __('Square', 'blocksy'),
		],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['share_type' => '!simple'],
		'options' => [
			'share_icons_fill' => [
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
];
