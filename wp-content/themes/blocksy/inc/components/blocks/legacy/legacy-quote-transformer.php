<?php

namespace Blocksy;

class LegacyWidgetsQuoteTransformer {
	private $data = [];

	public function __construct($data) {
		$this->data = $data;
	}

	public function get_block() {
		$options = blocksy_akg('ct_options', $this->data, []);

		$options = array_merge(
			[
				'quote_text' => '',
				'quote_author' => '',
			],
			$options
		);

		$innerContent = [
			'<figure class="wp-block-pullquote has-palette-color-8-color has-palette-color-1-background-color has-text-color has-background" style="border-style:none;border-width:0px;border-radius:7px">',
			'<blockquote><p>',
			$options['quote_text'],
			'</p><cite>',
			$options['quote_author'],
			'</cite></blockquote></figure>',
		];

		$blocks = [
			[
				'blockName' => 'core/pullquote',
				'attrs' => [
					'value' => trim(strip_tags($options['quote_text'])),
					'citation' => trim(strip_tags($options['quote_author'])),
					'style' => [
						'border' => [
							'radius' => '7px',
							'width' => '0px',
							'style' => 'none'
						],
						'backgroundColor' => 'palette-color-1',
						'textColor' => 'palette-color-8',
					],
				],
				'innerBlocks' => [],
				'innerHTML' => join('', $innerContent),
				'innerContent' => $innerContent,
				'firstLevelBlock' => true,
			]
		];

		return serialize_blocks($blocks);
	}
}

