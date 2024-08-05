<?php

if (! function_exists('blocksy_get_woocommerce_ratio')) {
	function blocksy_get_woocommerce_ratio($args = []) {
		$args = wp_parse_args($args, [
			'key' => 'archive_thumbnail',
			'cropping' => 'predefined',

			'default_width' => 3,
			'default_height' => 4
		]);

		if ($args['cropping'] === 'uncropped') {
			return 'original';
		}

		if ($args['cropping'] === '1:1') {
			return '1/1';
		}

		if ($args['cropping'] === 'custom' || $args['cropping'] === 'predefined') {
			$width = get_option(
				'woocommerce_' . $args['key'] . '_cropping_custom_width',
				3
			);

			$height = get_option(
				'woocommerce_' . $args['key'] . '_cropping_custom_height',
				4
			);

			return $width . '/' . $height;
		}

		return '1/1';
	}
}

