<?php

namespace Blocksy;

class LegacyWidgetsSocialsTransformer {
	private $data = [];

	public function __construct($data) {
		$this->data = $data;
	}

	public function get_block() {
		$options = blocksy_akg('ct_options', $this->data, []);

		$sizes = [
			'small' => 15,
			'medium' => 20,
			'large' => 25,
		];

		$old_size = blocksy_akg('social_icons_size', $options, 'medium');

		if (isset($sizes[$old_size])) {
			$options['social_icons_size'] = $sizes[$old_size];
		}

		$options = array_merge(
			[
				'title' => __('Social Icons', 'blocksy'),
				'socials' => [
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
				'link_target' => 'no',
				'link_nofollow' => 'no',
				'social_icons_size' => '',
				'items_spacing' => '',
				'social_type' => 'simple',
				'social_icons_fill' => 'outline',
			],
			$options
		);

		$innerContent = [
			'<h3 class="wp-block-heading" style="font-size:18px">',
			$options['title'],
			'</h3>'
		];

		$blocks = [
			[
				'blockName' => 'blocksy/widgets-wrapper',
				'attrs' => [
					'heading' => 'Socials',
					'block' => 'blocksy/socials'
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
						'blockName' => 'blocksy/socials',
						'attrs' => array_merge(
							[
								'lock' => [
									'remove' => true
								]
							],
							$options
						),
						'innerBlocks' => [],
						'innerHTML' => '<div>Blocksy: Socials</div>',
						'innerContent' => [
							'<div>Blocksy: Socials</div>'
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

