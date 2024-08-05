<?php

namespace Blocksy;

use Automattic\WooCommerce\Utilities\NumberUtil;

class WooCommerceImageSizes {
	public function __construct() {
		add_action(
			'after_setup_theme',
			[$this, 'add_image_sizes']
		);

		if (! is_multisite()) {
			add_action(
				'customize_save_after',
				[$this, 'maybe_regenerate_images'],
				// Run after Woo's regeneration logic
				50
			);
		}

		add_filter(
			'woocommerce_get_image_size_archive_thumbnail',
			function ($size) {
				return $this->get_image_size('archive_thumbnail', 500);
			}
		);

		add_filter(
			'woocommerce_image_sizes_to_resize',
			function ($sizes) {
				$sizes[] = 'woocommerce_archive_thumbnail';
				return $sizes;
			}
		);

		add_filter(
			'woocommerce_regenerate_images_intermediate_image_sizes',
			function ($sizes) {
				$sizes[] = 'woocommerce_archive_thumbnail';
				return $sizes;
			}
		);
	}

	public function add_image_sizes() {
		$sizes = [
			[
				'name' => 'archive_thumbnail',
				'default_width' => 500
			]
		];

		foreach ($sizes as $size) {
			$size_info = $this->get_image_size(
				$size['name'],
				$size['default_width']
			);

			add_image_size(
				'woocommerce_' . $size['name'],
				$size_info['width'],
				$size_info['height'],
				$size_info['crop']
			);
		}
	}

	public function maybe_regenerate_images() {
		$size_hash = md5(wp_json_encode([
			$this->get_image_size('archive_thumbnail', 500),
			blocksy_get_theme_mod('gallery_thumbnail_image_width', 100)
		]));

		$did_update = update_option(
			'blocksy_woocommerce_maybe_regenerate_images_hash',
			$size_hash
		);

		if ($did_update) {
			\WC_Regenerate_Images::queue_image_regeneration();
		}
	}

	public function get_image_size($size_name, $default_width = 300) {
		$size = [];

		$size['width'] = absint(
			get_option(
				'woocommerce_' . $size_name . '_image_width',
				$default_width
			)
		);

		$cropping = get_option('woocommerce_' . $size_name . '_cropping', '1:1');

		if ('uncropped' === $cropping) {
			$size['height'] = '';
			$size['crop']   = 0;
		} elseif ('custom' === $cropping) {
			$width = max(
				1,
				get_option(
					'woocommerce_' . $size_name . '_cropping_custom_width',
					'4'
				)
			);
			$height = max(
				1,
				get_option(
					'woocommerce_' . $size_name . '_cropping_custom_height',
					'3'
				)
			);

			$size['height'] = absint(
				NumberUtil::round(($size['width'] / $width) * $height)
			);

			$size['crop'] = 1;
		} else {
			$cropping_split = explode(':', $cropping);

			$width = max(1, current($cropping_split));

			$height = max(1, end($cropping_split));

			if (intval($width) === 0) {
				$width = 1;
			}

			$size['height'] = absint(
				NumberUtil::round(
					(intval($size['width']) / intval($width)) * intval($height)
				)
			);

			$size['crop'] = 1;
		}

		return $size;
	}
}
