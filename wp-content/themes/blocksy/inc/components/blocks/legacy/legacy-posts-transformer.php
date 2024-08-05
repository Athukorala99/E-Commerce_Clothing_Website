<?php

namespace Blocksy;

class LegacyWidgetsPostsTransformer {
	private $data = [];

	public function __construct($data) {
		$this->data = $data;
	}

	public function get_block() {
		$atts = $this->data['ct_options'];

		$query_attrs = [
			'type' => 'post',
			'post_type' => 'post',
			'limit' => 5,
			'term_ids' => [],
			'order' => 'desc',

			'limit' => blocksy_akg('posts_number', $atts, 5),
			'post_type' => blocksy_akg('post_type_source', $atts, 'post'),
		];

		$orderby_map = [
			'default' => 'post_date',
			'random' => 'rand',
			'recent' => 'post_date',
			'commented' => 'comment_count'
		];

		$proper_orderby = 'post_date';

		$current_type = blocksy_akg('type', $atts, 'default');

		if (isset($orderby_map[$current_type])) {
			$query_attrs['orderby'] = $orderby_map[$current_type];
		}

		$post_template_blocks = [
			[
				"blockName" => "blocksy/dynamic-data",
				"attrs" => [
					"tagName" => "h2",
					"field" => "wp:title",
					'has_field_link' => 'yes',
					"style" => [
						"spacing" => [
							"margin" => [
								"bottom" => "var:preset|spacing|20"
							]
						],

						'typography' => [
							'fontSize' => '15px',
							'fontWeight' => '500'
						]
					]
				],
				"innerBlocks" => [],
				"innerHTML" => "",
				"innerContent" => []
			],
		];

		if (blocksy_akg('display_excerpt', $atts, 'no') === 'yes') {
			$post_template_blocks[] = [
				"blockName" => "blocksy/dynamic-data",
				"attrs" => [
					"field" => "wp:excerpt",
					'excerpt_length' => blocksy_akg('excerpt_lenght', $atts, 10),
					'style' => [
						'typography' => [
							'fontSize' => '13px'
						],
						'spacing' => [
							'margin' => [
								'bottom' => 'var:preset|spacing|30'
							]
						]
					]
				],
				"innerBlocks" => [],
				"innerHTML" => "",
				"innerContent" => []
			];
		}

		if (blocksy_akg('display_date', $atts, 'no') === 'yes') {
			$post_template_blocks[] = [
				"blockName" => "blocksy/dynamic-data",
				"attrs" => [
					"field" => "wp:date",
					'style' => [
						'typography' => ['fontSize' => '13px']
					]
				],
				"innerBlocks" => [],
				"innerHTML" => "",
				"innerContent" => []
			];
		}

		$posts_type = blocksy_akg('posts_type', $atts, 'small-thumbs');

		if (
			$posts_type === 'small-thumbs'
			||
			$posts_type === 'large-thumbs'
			||
			$posts_type === 'large-small'
			||
			$posts_type === 'rounded'
		) {
			$featured_image_block = [
				"blockName" => "blocksy/dynamic-data",
				"attrs" => [
					"field" => "wp:featured_image",
					"aspectRatio" => "1",
					'has_field_link' => 'yes'
				],
				"innerBlocks" => [],
				"innerHTML" => "",
				"innerContent" => []
			];

			$sizeSlug = 'thumbnail';

			if (blocksy_akg('post_widget_thumb_size', $atts, 'default') !== 'default') {
				$sizeSlug = blocksy_akg('post_widget_thumb_size', $atts, 'default');
			}

			$featured_image_block['attrs']['sizeSlug'] = $sizeSlug;

			if ($posts_type === 'rounded') {
				$featured_image_block['attrs']['style'] = [
					'border' => [
						'radius' => '100%'
					]
				];
			}

			$columns_layout = [
				"blockName" => "core/columns",
				"attrs" => [
					'style' => [
						'spacing' => [
							'blockGap' => [
								'left' => '20px'
							]
						]
					]
				],
				"innerBlocks" => [
					[
						"blockName" => "core/column",
						"attrs" => [
							"width" => "25%"
						],
						"innerBlocks" => [
							$featured_image_block
						],
						"innerHTML" => '<div class="wp-block-column" style="flex-basis:25%"></div>',
						"innerContent" => [
							'<div class="wp-block-column" style="flex-basis:25%">',
							null,
							"</div>"
						]
					],

					[
						"blockName" => "core/column",
						"attrs" => [
							"verticalAlignment" => "center",
							"width" => "75%"
						],
						"innerBlocks" => $post_template_blocks,
						"innerHTML" => '<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:75%"></div>',
						"innerContent" => []
					]
				],
				"innerHTML" => '<div class="wp-block-columns"></div>',
				"innerContent" => [
					'<div class="wp-block-columns">',
					null,
					"",
					null,
					"</div>"
				]
			];

			$columns_layout['innerBlocks'][1]['innerContent'] = array_merge(
				[
					'<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:75%">',
				],

				array_fill(
					0,
					count($columns_layout['innerBlocks'][1]['innerBlocks']),
					null
				),

				[
					'</div>'
				]
			);

			$post_template_blocks = [
				$columns_layout
			];
		}

		$post_template_block = [
			"blockName" => "blocksy/post-template",
			"attrs" => [
				"layout" => [
					"type" => "default",
					"columnCount" => 3
				]
			],
			"innerBlocks" => $post_template_blocks,
			"innerHTML" => "",
			"innerContent" => []
		];

		$post_template_block['innerContent'] = array_fill(
			0,
			count($post_template_block['innerBlocks']),
			null
		);

		$innerContent = [
			'<h3 class="wp-block-heading" style="font-size:18px">',
			blocksy_akg('title', $atts, ''),
			'</h3>'
		];

		$b = [
			[
				'blockName' => 'core/group',
				'attrs' => [
					'layout' => [
						'type' => 'constrained'
					]
				],

				'innerBlocks' => [
					[
						'blockName' => 'core/heading',
						'attrs' => [
							'level' => 3,
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
						"blockName" => "blocksy/query",
						"attrs" => $query_attrs,
						"innerBlocks" => [$post_template_block],
						"innerHTML" => '<div class="wp-block-blocksy-query"></div>',
						"innerContent" => [
							'<div class="wp-block-blocksy-query">',
							null,
							"</div>"
						],
					]
				],

				'innerHTML' => '<div class="wp-block-group"></div>',
				'innerContent' => array(
					'<div class="wp-block-group">',
					null,
					null,
					'</div>'
				)
			]
		];

		return serialize_blocks($b);
	}
}

