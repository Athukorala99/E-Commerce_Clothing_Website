<?php

namespace Blocksy;

class LegacyWidgetsNewsletterSubscribeTransformer {
	private $data = [];

	public function __construct($data) {
		$this->data = $data;
	}

	public function get_block() {
		$options = blocksy_akg('ct_options', $this->data, []);

		$options = array_merge(
			[
				'title' => __('Newsletter', 'blocksy'),
				'newsletter_subscribe_text' => __(
					'Enter your email address below to subscribe to our newsletter',
					'blocksy'
				),
				'newsletter_subscribe_view_type' => 'stacked'
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
					'heading' => 'Newsletter',
					'block' => 'blocksy/newsletter',
					'hasDescription' => true,
					"description" => "Enter your email address below to subscribe to our newsletter"
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
							'placeholder' => 'Description'
						],
						'innerBlocks' => [],
						'innerHTML' => $options['newsletter_subscribe_text'],
						'innerContent' => [
							$options['newsletter_subscribe_text'],
						]
					],

					[
						'blockName' => 'blocksy/newsletter',
						'attrs' => array_merge(
							[
								'lock' => [
									'remove' => true
								]
							],
							$options
						),
						'innerBlocks' => [],
						'innerHTML' => '<div>Blocksy: Newsletter</div>',
						'innerContent' => [
							'<div>Blocksy: Newsletter</div>'
						]
					]
				],
				'innerHTML' => '',
				'innerContent' => [null, null, null]
			]
		];

		return serialize_blocks($blocks);
	}
}


