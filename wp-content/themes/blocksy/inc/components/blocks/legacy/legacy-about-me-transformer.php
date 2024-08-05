<?php

namespace Blocksy;

class LegacyWidgetsAboutMeTransformer {
	private $data = [];

	public function __construct($data) {
		$this->data = $data;
	}

	public function get_block() {
		$options = blocksy_akg('ct_options', $this->data, []);

		$options = array_merge(
			[
				'title' => __('About me', 'blocksy'),
				'about_type' => 'bordered',
				'about_source' => 'from_wp',
				'wp_user' => 1,
				'about_avatar' => [
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
				'about_name' => 'John Doe',
				'about_text' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua tincidunt tortor aliquam.',
				'about_avatar_size' => 'small',
				'avatar_shape' => 'rounded',
				'about_alignment' => 'center',
				'about_socials' => [],
				'about_social_icons_size' => '',
				'about_items_spacing' => '',
				'about_social_type' => 'rounded',
				'about_social_icons_fill' => 'outline',
			],
			$options
		);

		$sizes = [
			'small' => 15,
			'medium' => 20,
			'large' => 25,
		];

		$old_size = blocksy_akg('about_social_icons_size', $options, 'medium');

		if (isset($sizes[$old_size])) {
			$options['about_social_icons_size'] = $sizes[$old_size];
		}

		$innerContent = [
			'<h3 class="wp-block-heading" style="font-size:18px">',
			$options['title'],
			'</h3>'
		];

		$blocks = [
			[
				'blockName' => 'blocksy/widgets-wrapper',
				'attrs' => [
					'heading' => 'About Me',
					'block' => 'blocksy/about-me'
				],
				'innerBlocks' => [
					[
						'blockName' => 'core/heading',
						'attrs' => [
							'level' => 3,
							'fontSize' => 'medium',
							'className' => 'widget-title',
							'style' => [
								'typography' => [
									'fontSize' => '18px'
								]
							]
						],
						'innerBlocks' => [],
						'innerHTML' => join('', $innerContent),
						'innerContent' => $innerContent
					],
					[
						'blockName' => 'blocksy/about-me',
						'attrs' => array_merge(
							[
								'lock' => [
									'remove' => true
								]
							],
							$options
						),
						'innerBlocks' => [],
						'innerHTML' => '<div>Blocksy: About Me</div>',
						'innerContent' => [
							'<div>Blocksy: About Me</div>'
						]
					]
				],
				'innerHTML' => '',
				'innerContent' => ['', null, '', null, ''],
				'firstLevelBlock' => true,
			]
		];

		return serialize_blocks($blocks);
	}
}

