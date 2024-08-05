<?php
/**
 * Newsletter Subscribe widget
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$options = [

	'newsletter_subscribe_text' => [
		'label' => __('Text', 'blocksy-companion'),
		'type' => 'hidden',
		'value' => __(
			'Enter your email address below and subscribe to our newsletter',
			'blocksy-companion'
		),
		'desc' => __(
			'You can add here some arbitrary HTML code.',
			'blocksy-companion'
		),
		'disableRevertButton' => true,
		'setting' => ['transport' => 'postMessage'],

		'mediaButtons' => false,
		'tinymce' => [
			'toolbar1' => 'bold,italic,link,undo,redo',
		],
	],

	'newsletter_subscribe_list_id_source' => [
		'type' => 'ct-radio',
		'label' => __('List Source', 'blocksy-companion'),
		'value' => 'default',
		'view' => 'radio',
		'inline' => true,
		'disableRevertButton' => true,
		'choices' => [
			'default' => __('Default', 'blocksy-companion'),
			'custom' => __('Custom', 'blocksy-companion'),
		],
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['newsletter_subscribe_list_id_source' => 'custom'],
		'options' => [
			'newsletter_subscribe_list_id' => [
				'label' => __('List ID', 'blocksy-companion'),
				'type' => 'blocksy-newsletter-subscribe',
				'value' => '',
				'disableRevertButton' => true,
			],
		],
	],

	'newsletter_subscribe_view_type' => [
		'type' => 'ct-radio',
		'label' => __('Form Style', 'blocksy-companion'),
		'value' => 'inline',
		'view' => 'radio',
		'inline' => true,
		'divider' => 'top:full',
		'disableRevertButton' => true,
		'choices' => [
			'inline' => __('Inline', 'blocksy-companion'),
			'stacked' => __('Stacked', 'blocksy-companion'),
		],
	],

	'newsletter_subscribe_height' => [
		'label' => __( 'Input Height', 'blocksy' ),
		'type' => 'ct-slider',
		'min' => 40,
		'max' => 80,
		'value' => '',
		'responsive' => false,
		'divider' => 'top:full',
		'setting' => [ 'transport' => 'postMessage' ],
	],

	'newsletter_subscribe_gap' => [
		'label' => __( 'Fields Gap', 'blocksy' ),
		'type' => 'ct-slider',
		'min' => 0,
		'max' => 50,
		'value' => '',
		'responsive' => false,
		'setting' => [ 'transport' => 'postMessage' ],
	],

	'has_newsletter_subscribe_name' => [
		'type' => 'ct-switch',
		'label' => __('Name Field', 'blocksy-companion'),
		'value' => 'no',
		'divider' => 'top:full',
		'disableRevertButton' => true,
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => ['has_newsletter_subscribe_name' => 'yes'],
		'options' => [
			'newsletter_subscribe_name_label' => [
				'type' => 'hidden',
				'label' => __('Name Label', 'blocksy-companion'),
				'value' => __('Your name', 'blocksy-companion'),
			],
		],
	],

	'newsletter_subscribe_mail_label' => [
		'type' => 'hidden',
		'label' => __('Mail Label', 'blocksy-companion'),
		'value' => __('Your email *', 'blocksy-companion'),
	],

	'newsletter_subscribe_button_text' => [
		'type' => 'hidden',
		'label' => __('Button Label', 'blocksy-companion'),
		'value' => __('Subscribe', 'blocksy-companion'),
	],
];
