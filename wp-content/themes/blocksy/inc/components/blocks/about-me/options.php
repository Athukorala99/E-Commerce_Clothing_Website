<?php
/**
 * About me widget
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

require_once dirname(__FILE__) . '/helpers.php';

$options = [
	'title' => [
		'type' => 'hidden',
		'label' => __('Title', 'blocksy'),
		'value' => __('About me', 'blocksy'),
		'disableRevertButton' => true,
	],

	'about_source' => [
		'label' => __('User Source', 'blocksy'),
		'type' => 'ct-radio',
		'value' => 'from_wp',
		'inline' => true,
		'choices' => [
			'from_wp' => __('Dynamic', 'blocksy'),
			'custom' => __('Custom', 'blocksy'),
		],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['about_source' => 'from_wp'],
		'options' => [
			'wp_user' => [
				'type' => 'ct-select',
				'label' => __('User', 'blocksy'),
				'value' => array_keys(blc_get_user_choices())[0],
				'choices' => blocksy_ordered_keys(blc_get_user_choices()),
			],
		],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['about_source' => 'custom'],
		'options' => [
			'about_avatar' => [
				'label' => __('Image', 'blocksy'),
				'type' => 'ct-image-uploader',
				'value' => ['attachment_id' => null],
				'attr' => ['data-type' => 'no-frame'],
				'emptyLabel' => __('Select Image', 'blocksy'),
				'filledLabel' => __('Change Image', 'blocksy'),
			],

			'about_name' => [
				'label' => __('Name', 'blocksy'),
				'type' => 'hidden',
				'value' => 'John Doe',
			],

			'about_text' => [
				'label' => __('Description', 'blocksy'),
				'type' => 'hidden',
				'value' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua tincidunt tortor aliquam.',
				'desc' => __(
					'You can add here some arbitrary HTML code.',
					'blocksy'
				),

				'mediaButtons' => false,
				'tinymce' => [
					'toolbar1' => 'bold,italic,link,undo,redo',
				],
			],
		],
	],

	'about_avatar_size' => [
		'label' => __('Image Size', 'blocksy'),
		'type' => 'ct-select',
		'value' => 'small',
		'choices' => [
			'small' => __('Small', 'blocksy'),
			'medium' => __('Medium', 'blocksy'),
			'large' => __('Large', 'blocksy'),
		],
	],

	'avatar_shape' => [
		'label' => __('Image Shape', 'blocksy'),
		'type' => 'ct-radio',
		'value' => 'rounded',
		'inline' => true,
		'choices' => [
			'rounded' => __('Rounded', 'blocksy'),
			'square' => __('Square', 'blocksy'),
		],
	],

	'about_alignment' => [
		'type' => 'ct-radio',
		'label' => __('Alignment', 'blocksy'),
		'value' => 'center',
		'divider' => 'top:full',
		'attr' => ['data-type' => 'alignment'],
		'choices' => [
			'left' =>
				'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M4 19.8h8.9v-1.5H4v1.5zm8.9-15.6H4v1.5h8.9V4.2zm-8.9 7v1.5h16v-1.5H4z"></path></svg>',
			'center' =>
				'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M16.4 4.2H7.6v1.5h8.9V4.2zM4 11.2v1.5h16v-1.5H4zm3.6 8.6h8.9v-1.5H7.6v1.5z"></path></svg>',
			'right' =>
				'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M11.1 19.8H20v-1.5h-8.9v1.5zm0-15.6v1.5H20V4.2h-8.9zM4 12.8h16v-1.5H4v1.5z"></path></svg>',
		],
	],

	'about_socials' => [
		'label' => __('Social Channels', 'blocksy'),
		'type' => 'ct-layers',
		'divider' => 'top:full',
		'manageable' => true,
		'desc' => sprintf(
			__('You can configure social URLs in %s.', 'blocksy'),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				admin_url('/customize.php?autofocus[section]=social_accounts'),
				__('Customizer', 'blocksy')
			)
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

	'about_social_icons_size' => [
		'label' => __( 'Icons Size', 'blocksy' ),
		'type' => 'ct-slider',
		'min' => 5,
		'max' => 50,
		'value' => '',
		'responsive' => false,
		'divider' => 'top:full',
	],

	'about_items_spacing' => [
		'label' => __( 'Items Spacing', 'blocksy' ),
		'type' => 'ct-slider',
		'min' => 5,
		'max' => 50,
		'value' => '',
		'responsive' => false,
	],

	'about_social_icons_color' => [
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

	'about_social_type' => [
		'label' => __('Icons Shape Type', 'blocksy'),
		'type' => 'ct-radio',
		'value' => 'rounded',
		'choices' => [
			'simple' => __('None', 'blocksy'),
			'rounded' => __('Rounded', 'blocksy'),
			'square' => __('Square', 'blocksy'),
		],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['about_social_type' => '!simple'],
		'options' => [
			'about_social_icons_fill' => [
				'label' => __('Shape Fill Type', 'blocksy'),
				'type' => 'ct-radio',
				'value' => 'outline',
				'choices' => [
					'outline' => __('Outline', 'blocksy'),
					'solid' => __('Solid', 'blocksy'),
				],
			],
		],
	],
];
