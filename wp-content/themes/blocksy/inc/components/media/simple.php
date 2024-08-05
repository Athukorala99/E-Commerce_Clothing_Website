<?php

/**
 * Generate an image container based on image URL.
 *
 * @param string $image_src URL to the image.
 * @param array $args various params that the function accepts.
 */
if (! function_exists('blocksy_simple_image')) {
	function blocksy_simple_image($image_src, $args = []) {
		$args = wp_parse_args(
			$args,
			[
				'ratio' => '1/1',
				'class' => '',
				'aspect_ratio' => true,
				'tag_name' => 'div',
				'html_atts' => [],
				'img_atts' => [],
				'inner_content' => '',
				'lazyload' => true,
				'size' => 'medium',
				'suffix' => '',
				'has_default_alt' => true,
				'has_default_class' => true,
			]
		);
		$original = '';

		if ($args['has_default_class']) {
			$original = 'ct-media-container';
		}

		if (! empty($args['suffix'])) {
			$original .= '-' . $args['suffix'];
		}

		if ($args['aspect_ratio']) {
			$args['img_atts']['style'] = isset($args['img_atts']['style'])
				? $args['img_atts']['style'] . ';'
				: '';
			$args['img_atts']['style'] .= blocksy_generate_ratio($args['ratio']);
		}

		$other_img_atts = '';

		if (! isset($args['img_atts']['alt']) && $args['has_default_alt']) {
			$args['img_atts']['alt'] = __('Default image', 'blocksy');
		}

		foreach ($args['img_atts'] as $attr => $value) {
			$other_img_atts .= $attr . '="' . $value . '" ';
		}

		if (! isset($args['html_atts']['class'])) {
			$args['html_atts']['class'] = $original;
		} else {
			$args['html_atts']['class'] = join(' ', [$original, $args['html_atts']['class']]);
		}

		$image_content = blocksy_html_tag(
			'img',
			array_merge(
				[
					'src' => $image_src
				],
				$args['img_atts']
			)
		);

		if (
			wp_lazy_loading_enabled('img', 'blocksy_simple_image')
			&&
			false === strpos($image_content, ' loading=')
		) {
			if (function_exists('wp_img_tag_add_loading_optimization_attrs')) {
				$image_content = wp_img_tag_add_loading_optimization_attrs(
					$image_content,
					'blocksy_simple_image'
				);
			} else {
				$image_content = wp_img_tag_add_loading_attr(
					$image_content,
					'blocksy_simple_image'
				);
			}
		}

		return blocksy_html_tag(
			$args['tag_name'],
			$args['html_atts'],
			$image_content . $args['inner_content']
		);
	}
}
