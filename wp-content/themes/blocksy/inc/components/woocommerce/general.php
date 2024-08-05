<?php

add_action(
	'blocksy:content:top',
	function () {
		if (
			! is_shop()
			&&
			! is_woocommerce()
			&&
			! is_cart()
			&&
			! is_checkout()
			&&
			! is_account_page()
		) {
			global $blocksy_messages_content;
			ob_start();
			echo '<div class="blocksy-woo-messages-default woocommerce-notices-wrapper">';
			echo do_shortcode('[woocommerce_messages]');
			echo '</div>';
			$blocksy_messages_content = ob_get_clean();
		}
	}
);

add_action(
	'blocksy:single:top',
	function () {
		global $blocksy_messages_content;

		if (! empty($blocksy_messages_content)) {
			echo $blocksy_messages_content;
		}
	}
);

add_filter(
	'woocommerce_format_sale_price',
	function ($price, $regular_price, $sale_price) {
		return '<span class="sale-price">' . $price . '</span>';
	},
	10,
	3
);

add_filter('woocommerce_quantity_input_args', function ($args, $product) {
	global $blocksy_quantity_args;
	$blocksy_quantity_args = $args;

	$blocksy_quantity_args['min_value'] = max($blocksy_quantity_args['min_value'], 0);
	$blocksy_quantity_args['max_value'] = 0 < $blocksy_quantity_args['max_value'] ? $blocksy_quantity_args['max_value'] : '';

	if ('' !== $blocksy_quantity_args['max_value'] && $blocksy_quantity_args['max_value'] < $blocksy_quantity_args['min_value']) {
		$blocksy_quantity_args['max_value'] = $blocksy_quantity_args['min_value'];
	}

	return $args;
}, 10, 2);

add_action(
	'woocommerce_before_quantity_input_field',
	function () {
		if (blocksy_get_theme_mod('has_custom_quantity', 'yes') !== 'yes') {
			return;
		}

		echo '<span class="ct-increase"></span>';
		echo '<span class="ct-decrease"></span>';
	}
);

add_action(
	'woocommerce_before_main_content',
	function () {
		$prefix = blocksy_manager()->screen->get_prefix();

		if (
			function_exists('blc_get_content_block_that_matches')
			&&
			blc_get_content_block_that_matches([
				'template_type' => 'nothing_found',
				'match_conditions' => false
			])
			&&
			is_search()
			&&
			! have_posts()
		) {
			echo blc_render_content_block(
				blc_get_content_block_that_matches([
					'template_type' => 'nothing_found',
					'match_conditions' => false
				])
			);
			ob_start();
			return;
		}

		if ($prefix === 'woo_categories' || $prefix === 'search') {
			/**
			 * Note to code reviewers: This line doesn't need to be escaped.
			 * Function blocksy_output_hero_section() used here escapes the value properly.
			 */
			echo blocksy_output_hero_section([
				'type' => 'type-2'
			]);
		}

		$attr = [
			'class' => 'ct-container'
		];

		if (blocksy_get_page_structure() === 'narrow') {
			$attr['class'] = 'ct-container-narrow';
		}

		if ($prefix === 'product') {
			if (blocksy_sidebar_position() === 'none') {
				$attr['class'] = 'ct-container-full';

				$attr['data-content'] = 'normal';

				if (blocksy_get_page_structure() === 'narrow') {
					$attr['data-content'] = 'narrow';
				}
			}

			echo blocksy_output_hero_section([
				'type' => 'type-2'
			]);
		}


		echo '<div ' . blocksy_attr_to_html($attr) . ' ' . wp_kses(blocksy_sidebar_position_attr(), []) . ' ' . blocksy_get_v_spacing() . '>';

		if (is_product()) {
			echo '<article class="post-' . get_the_ID() . '">';
		} else {
			echo '<section>';
		}

		if (
			$prefix === 'woo_categories'
			||
			$prefix === 'search'
			||
			$prefix === 'product'
		) {
			/**
			 * Note to code reviewers: This line doesn't need to be escaped.
			 * Function blocksy_output_hero_section() used here escapes the value properly.
			 */
			echo blocksy_output_hero_section([
				'type' => 'type-1'
			]);
		}
	}
);

add_action(
	'woocommerce_after_main_content',
	function () {
		if (
			function_exists('blc_get_content_block_that_matches')
			&&
			blc_get_content_block_that_matches([
				'template_type' => 'nothing_found',
				'match_conditions' => false
			])
			&&
			is_search()
			&&
			! have_posts()
		) {
			ob_get_clean();
			return;
		}

		if (is_product()) {
			echo '</article>';
		} else {
			echo '</section>';
		}

		get_sidebar();
		echo '</div>';
	}
);

add_action(
	'woocommerce_before_template_part',
	function ($template_name, $template_path, $located, $args) {
		global $blocksy_is_offcanvas_cart;

		if ($template_name === 'global/quantity-input.php') {
			ob_start();
		}

		if ($template_name === 'single-product/up-sells.php') {
			ob_start();
		}

		if ($template_name === 'single-product/related.php') {
			ob_start();
		}

		if (
			$template_name === 'loop/loop-start.php'
			&&
			is_archive()
		) {
			ob_start();
		}
	},
	10,
	4
);

add_action(
	'woocommerce_after_template_part',
	function ($template_name, $template_path, $located, $args) {
		global $blocksy_is_offcanvas_cart;

		if ($template_name === 'global/quantity-input.php') {
			$quantity = ob_get_clean();

			$final_quantity_look = 'class="quantity"';

			global $blocksy_quantity_args;

			$args = $blocksy_quantity_args;

			if ($args['max_value'] && $args['min_value'] === $args['max_value']) {
				$final_quantity_look = 'class="quantity hidden"';
			} else {
				if (blocksy_get_theme_mod('has_custom_quantity', 'yes') === 'yes') {
					$final_quantity_look .= ' data-type="' . blocksy_get_theme_mod('quantity_type', 'type-2') . '"';
				}
			}

			echo str_replace(
				'class="quantity"',
				$final_quantity_look,
				$quantity
			);
		}

		if (
			$template_name === 'loop/loop-start.php'
			&&
			is_archive()
		) {
			$loop_start = ob_get_clean();

			$other_attr = [];

			if ( is_customize_preview() ) {
				$other_attr['data-shortcut'] = 'border:outside';
				$other_attr['data-shortcut-location'] = blocksy_first_level_deep_link('woo_categories');
			}

			echo str_replace(
				'class="products',
				blocksy_attr_to_html($other_attr) . 'class="products ',
				$loop_start
			);
		}

		if ($template_name === 'single-product/up-sells.php') {
			$upsells = ob_get_clean();

			$other_attr = [];

			if ( is_customize_preview() ) {
				$other_attr['data-shortcut'] = 'border:outside';
				$other_attr['data-shortcut-location'] = blocksy_first_level_deep_link('woo_categories');

				if ( is_single() ) {
					$prefix = blocksy_manager()->screen->get_prefix();

					$other_attr['data-shortcut-location'] = blocksy_first_level_deep_link($prefix) . ':woo_has_related_upsells';
				}
			}

			echo str_replace(
				'class="up-sells upsells products"',
				blocksy_attr_to_html($other_attr) . 'class="up-sells upsells products ' . trim(
					blocksy_visibility_classes(
						blocksy_get_theme_mod(
							'upsell_products_visibility',
							[
								'desktop' => true,
								'tablet' => false,
								'mobile' => false,
							]
						)
					)
				) . '"',
				$upsells
			);
		}

		if ($template_name === 'single-product/related.php') {
			$related = ob_get_clean();

			$other_attr = [];

			if ( is_customize_preview() ) {
				$other_attr['data-shortcut'] = 'border:outside';
				$other_attr['data-shortcut-location'] = blocksy_first_level_deep_link('woo_categories');

				if ( is_single() ) {
					$prefix = blocksy_manager()->screen->get_prefix();

					$other_attr['data-shortcut-location'] = blocksy_first_level_deep_link($prefix) . ':woo_has_related_upsells';
				}
			}

			echo str_replace(
				'class="related products"',
				blocksy_attr_to_html($other_attr) . 'class="related products ' . trim(
					blocksy_visibility_classes(
						blocksy_get_theme_mod(
							'related_products_visibility',
							[
								'desktop' => true,
								'tablet' => false,
								'mobile' => false,
							]
						)
					)
				) . '"',
				$related
			);
		}
	},
	4,
	4
);

if (! function_exists('blocksy_product_get_gallery_images')) {
    function blocksy_product_get_gallery_images($product, $args = []) {
		$args = wp_parse_args(
			$args,
			[
				'enforce_first_image_replace' => false
			]
		);

		$root_product = $product;

		if ($product->post_type === 'product_variation') {
			$root_product = wc_get_product($product->get_parent_id());
		}

		$thumb_id = apply_filters(
			'woocommerce_product_get_image_id',
			get_post_thumbnail_id($root_product->get_id()),
			$root_product
		);

		$thumb_id = get_post_thumbnail_id($root_product->get_id());

		$gallery_images = $root_product->get_gallery_image_ids();

		if ($thumb_id) {
			array_unshift($gallery_images, intval($thumb_id));
		} else {
			$gallery_images = [null];
		}

		if ($product->post_type === 'product_variation') {
			$variation_main_image = $product->get_image_id();

			$variation_values = get_post_meta(
				$product->get_id(),
				'blocksy_post_meta_options'
			);

			if (empty($variation_values)) {
				$variation_values = [[]];
			}

			$variation_values = $variation_values[0];

			$variation_gallery_images = blocksy_akg('images', $variation_values, []);
			$gallery_source = blocksy_akg('gallery_source', $variation_values, 'default');

			if ($gallery_source === 'default') {
				if (
					! in_array($variation_main_image, $gallery_images)
				) {
					$gallery_images[0] = $variation_main_image;
				} else {
					if ($args['enforce_first_image_replace']) {
						array_unshift($gallery_images, $variation_main_image);
						$gallery_images = array_unique($gallery_images);
					}
				}
			} else {
				$gallery_images = [$variation_main_image];

				foreach ($variation_gallery_images as $variation_gallery_image) {
					$gallery_images[] = $variation_gallery_image['attachment_id'];
				}
			}
		}

		return $gallery_images;
	}
}

