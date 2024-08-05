<?php
/**
 * Options for socials widget.
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
		'value' => __('Social Icons', 'blocksy'),
	],

	'socials' => [
		'label' => __('Social Channels', 'blocksy'),
		'type' => 'ct-layers',
		'manageable' => true,
		'desc' => sprintf(
			// translators: placeholder here means the actual URL.
			__(
				'Configure the social links in Customizer ➝ General ➝ %sSocial Network Accounts%s.',
				'blocksy'
			),
			sprintf(
				'<a href="%s" data-trigger-section="general:social_section_options" target="_blank">',
				admin_url(
					'/customize.php?autofocus[section]=general&ct_autofocus=general:social_section_options'
				)
			),
			'</a>'
		),

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
	],

	'link_target' => [
		'type' => 'ct-switch',
		'label' => __('Open links in new tab', 'blocksy'),
		'value' => 'no',
		'divider' => 'top:full',
	],

	'link_nofollow' => [
		'type' => 'ct-switch',
		'label' => __('Set links to nofollow', 'blocksy'),
		'value' => 'no',
	],

	'social_icons_size' => [
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

	'social_icons_color' => [
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

	'social_type' => [
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
		'condition' => ['social_type' => '!simple'],
		'options' => [
			'social_icons_fill' => [
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
