<?php

namespace Blocksy;

class LegacyWidgetsContactInfoTransformer {
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

		$old_size = blocksy_akg('contacts_icons_size', $options, 'medium');

		if (isset($sizes[$old_size])) {
			$options['contacts_icons_size'] = $sizes[$old_size];
		}

		$options = array_merge(
			[
				'title' => __('Contact Info', 'blocksy'),
				'contact_text' => '',
				'contact_information' => [
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
				'contact_link_target' => 'no',
				'link_icons' => 'no',
				'contacts_icons_size' => 20,
				'contacts_items_spacing' => '',
				'contacts_icon_shape' => 'rounded',
				'contacts_icon_fill_type' => 'outline',
				'contacts_items_direction' => 'column',
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
					'heading' => 'Contact Info',
					'block' => 'blocksy/contact-info',
					'hasDescription' => true,
					'description' => $options['contact_text'],
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
						'blockName' => 'core/paragraph',
						'attrs' => [
							'placeholder' => 'Description',
						],
						'innerBlocks' => [],
						'innerHTML' => '<p>' . $options['contact_text'] . '</p>',
						'innerContent' => [
							'<p>',
							$options['contact_text'],
							'</p>'
						]
					],

					[
						'blockName' => 'blocksy/contact-info',
						'attrs' => array_merge(
							[
								'lock' => [
									'remove' => true
								]
							],
							$options
						),
						'innerBlocks' => [],
						'innerHTML' => '<div>Blocksy: Contact Info</div>',
						'innerContent' => [
							'<div>Blocksy: Contact Info</div>'
						]
					]
				],
				'innerHTML' => '',
				'innerContent' => [null, null, null],
				'firstLevelBlock' => true
			]
		];

		return serialize_blocks($blocks);
	}
}

