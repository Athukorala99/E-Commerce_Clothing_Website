<?php

namespace Blocksy;

class LegacyWidgetsAdvertisementTransformer {
	private $data = [];

	public function __construct($data) {
		$this->data = $data;
	}

	public function get_block() {
		$options = blocksy_akg('ct_options', $this->data, []);

		$options = array_merge(
			[
				'title' => __('Advertisement', 'blocksy'),
				'ad_source' => 'code',
				'ad_code' => __('Insert ad code here', 'blocksy'),
				'ad_image' => ['attachment_id' => null],
				'ad_image_ratio' => 'original',
				'ad_link' => 'https://creativethemes.com',
				'ad_link_target' => 'yes',
			],
			$options
		);

		$block = [];

		if ($options['ad_source'] === 'code') {
			$block = [
				'blockName' => 'core/html',
				'attrs' => [],
				'innerBlocks' => [],
				'innerHTML' => blocksy_akg('ad_code', $options, ''),
				'innerContent' => [
					blocksy_akg('ad_code', $options, '')
				]
			];
		}

		if ($options['ad_source'] === 'upload') {
			$attrs = [
				'id' => blocksy_akg('attachment_id', $options['ad_image'], null),
				'url' => blocksy_akg('url', $options['ad_image'], ''),
				'linkTarget' => blocksy_akg(
					'ad_link_target',
					$options,
					'yes'
				) === 'yes' ? '_blank' : '',
				'href' => blocksy_akg('ad_link', $options, ''),
				'scale' => 'cover'
			];

			$ad_image_ratio = blocksy_akg(
				'ad_image_ratio',
				$options,
				'original'
			);

			if ($ad_image_ratio === 'original') {
				$ad_image_ratio = '';
			}

			$attrs['aspectRatio'] = $ad_image_ratio;

			$img_style = [];

			$img_style = [
				'aspect-ratio: ' .  $ad_image_ratio,
				'object-fit: cover'
			];

			$img = blocksy_html_tag(
				'img',
				[
					'src' => blocksy_akg('url', $options['ad_image'], ''),
					'alt' => '',
					'class' => 'wp-image-' . blocksy_akg(
						'attachment_id',
						$options['ad_image'],
						''
					),
					'style' => join(';', $img_style)
				]
			);

			if ($attrs['href'] !== '') {
				$img = blocksy_html_tag(
					'a',
					[
						'href' => $attrs['href'],
						'target' => $attrs['linkTarget']
					],
					$img
				);
			}

			$innerContent = [
				'<figure class="wp-block-image size-large">',
				$img,
				'</figure>'
			];

			$block = [
				'blockName' => 'core/image',
				'attrs' => $attrs,
				'innerBlocks' => [],
				'innerHTML' => join('', $innerContent),
				'innerContent' => $innerContent
			];
		}

		$innerContent = [
			'<h3 class="wp-block-heading" style="font-size:18px">',
			$options['title'],
			'</h3>'
		];

		$blocks = [
			[
				'blockName' => 'core/group',
				'attrs' => [],
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

					$block,
				],
				'innerHTML' => '<div class="wp-block-group"></div>',
				'innerContent' => [
					'<div class="wp-block-group">',
					null,
					'',
					null,
					'</div>'
				],
				'firstLevelBlock' => true,
			]
		];

		return serialize_blocks($blocks);
	}
}

