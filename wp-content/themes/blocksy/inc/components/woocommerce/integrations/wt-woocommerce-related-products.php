<?php

add_filter(
	'wt_rp_alter_slider_carousal_ul_tag',
	function ($ul_tag) {
		$attr = [
			'data-products' => blocksy_get_theme_mod('shop_cards_type', 'type-1')
		];

		$hover_value = 'none';

		$render_layout_config = blocksy_get_theme_mod(
			'woo_card_layout',
			[
				[
					'id' => 'product_image',
					'enabled' => true,
				],
			]
		);

		foreach ($render_layout_config as $layout) {
			if ($layout['id'] === 'product_image') {
				$hover_value = blocksy_akg(
					'product_image_hover',
					$layout,
					'none'
				);
			}
		}

		if ($hover_value !== 'none') {
			$attr['data-hover'] = $hover_value;
		}

		return str_replace(
			'">',
			'" ' . blocksy_attr_to_html($attr) . '>',
			$ul_tag
		);
	}
);
