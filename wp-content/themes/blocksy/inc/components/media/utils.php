<?php

add_filter('wp_lazy_loading_enabled', function ($enabled) {
	return blocksy_get_theme_mod('has_lazy_load', 'yes') === 'yes';
});

if (! function_exists('blocksy_get_all_wp_image_sizes')) {
	function blocksy_get_all_wp_image_sizes() {
		global $_wp_additional_image_sizes;

		$default_image_sizes = get_intermediate_image_sizes();

		$image_sizes = [];

		foreach ($default_image_sizes as $size) {
			$image_sizes[$size] = [];

			$image_sizes[$size]['width'] = intval(get_option("{$size}_size_w"));
			$image_sizes[$size]['height'] = intval(get_option("{$size}_size_h"));

			$image_sizes[$size]['crop'] = get_option("{$size}_crop")
				? get_option("{$size}_crop")
				: false;
		}

		if (
			isset($_wp_additional_image_sizes)
			&&
			count($_wp_additional_image_sizes)
		) {
			$image_sizes = array_merge(
				$image_sizes,
				$_wp_additional_image_sizes
			);
		}

		return $image_sizes;
	}
}

if (! function_exists('blocksy_generate_ratio')) {
	function blocksy_generate_ratio($ratio, $attachment_id = null, $size = null) {
		if ('original' === $ratio) {
			$result = '1/1';

			if ($attachment_id) {
				$all_sizes = blocksy_get_all_wp_image_sizes();

				if (
					$size
					&&
					$size === 'woocommerce_gallery_thumbnail'
					&&
					isset($all_sizes[$size])
					&&
					$all_sizes[$size]['width']
					&&
					$all_sizes[$size]['height']
				) {
					$info = $all_sizes[$size];
				} else {
					$info = wp_get_attachment_metadata($attachment_id);
				}

				if (
					$info
					&&
					isset($info['width'])
					&&
					intval($info['width']) !== 0
				) {
					$g = blocksy_gcd((int) $info['width'], (int) $info['height']);

					$ratio = ((int) $info['width'] / $g) . '/' . ((int) $info['height'] / $g);
				}
			}
		} else {
			if (strpos($ratio, ':') !== false) {
				$info = explode(':', $ratio);
				$ratio = $info[0] . '/' . $info[1];
			}
		}

		return 'aspect-ratio: ' . $ratio . ';';
	}
}

function blocksy_gcd($a, $b) {
	$a = abs($a); $b = abs($b);

	if ($a < $b) list($b,$a) = Array($a,$b);
	if ($b == 0) return $a;

	$r = $a % $b;

	while ($r > 0) {
		$a = $b;
		$b = $r;
		$r = $a % $b;
	}

	return $b;
}
