<?php

function blocksy_template_loop_product_thumbnail($attr) {
	global $product;

	echo '<figure>';

	do_action('blocksy:woocommerce:product-card:thumbnail:start');

	$badges = [];

	if ($product->is_in_stock()) {
		$has_sale_badge = blocksy_get_theme_mod('has_sale_badge', [
			'single' => true,
			'archive' => true
		]);

		if ($has_sale_badge['archive']) {
			ob_start();
			woocommerce_show_product_loop_sale_flash();
			$badges[] = ob_get_clean();
		}
	} else {
		$maybe_stock_badge = blocksy_get_woo_out_of_stock_badge([
			'location' => 'archive'
		]);

		if ($maybe_stock_badge) {
			$badges[] = $maybe_stock_badge;
		}
	}

	echo implode(
		'',
		apply_filters('blocksy:woocommerce:product-card:badges', $badges)
	);

	echo blocksy_output_product_toolbar();

	$gallery_images = apply_filters(
		'blocksy:woocommerce:product-card:thumbnail:gallery-images',
		blocksy_product_get_gallery_images($product)
	);

	if ($product->get_type() === 'variation') {
		$variation_main_image = $product->get_image_id();

		if ($variation_main_image) {
			if (! in_array($variation_main_image, $gallery_images)) {
				$gallery_images[0] = $variation_main_image;
			}
	
			$gallery_images = array_merge(
				[$variation_main_image],
				array_diff($gallery_images, [$variation_main_image])
			);
		}
	}
	
	$hover_value = blocksy_akg('product_image_hover', $attr, 'none');

	$has_archive_video_thumbnail = blocksy_akg(
		'has_archive_video_thumbnail',
		$attr,
		'no'
	);

	$has_lazy_load_shop_card_image = blocksy_get_theme_mod('has_lazy_load_shop_card_image', 'yes');

	$html_atts = [
		'href' => apply_filters(
			'woocommerce_loop_product_link',
			get_permalink($product->get_id()),
			$product
		),
		'aria-label' => strip_tags($product->get_name()),
	];

	if (
		blocksy_get_theme_mod('woo_archive_affiliate_image_link', 'no') === 'yes'
		&&
		$product->is_type('external')
	) {
		$open_in_new_tab = blocksy_get_theme_mod(
			'woo_archive_affiliate_image_link_new_tab',
			'no'
		) === 'yes' ? '_blank' : '_self';

		$html_atts['href'] = $product->get_product_url();
		$html_atts['target'] = $open_in_new_tab;
	}

	$maybe_other_images = [];

	if ($hover_value === 'swap') {
		if (count($gallery_images) > 1) {
			$maybe_other_images = array_slice($gallery_images, 1, 1);
		}
	}

	$image = blocksy_media([
		'no_image_type' => 'woo',
		'attachment_id' => $gallery_images[0],
		'post_id' => $product->get_id(),
		'other_images' => $maybe_other_images,
		'size' => 'woocommerce_archive_thumbnail',
		'ratio' => blocksy_get_woocommerce_ratio([
			'key' => 'archive_thumbnail',
			'cropping' => blocksy_akg(
				'blocksy_woocommerce_archive_thumbnail_cropping',
				$attr,
				'predefined'
			)
		]),
		'tag_name' => 'a',
		'html_atts' => $html_atts,
		'display_video' => $has_archive_video_thumbnail === 'yes',
		'lazyload' => $has_lazy_load_shop_card_image === 'yes',
		'class' => $hover_value !== 'none' ? 'has-hover-effect' : '',
	]);

	echo apply_filters(
		'woocommerce_product_get_image',
		$image,
		$product,
		'woocommerce_archive_thumbnail',
		[],
		'',
		$image
	);

	do_action('blocksy:woocommerce:product-card:thumbnail:end');

	echo '</figure>';
}

function blocksy_output_product_toolbar() {
	$shop_cards_type = blocksy_get_theme_mod('shop_cards_type', 'type-1');

	$components = apply_filters(
		'blocksy:options:woocommerce:archive:card-type:output_product_toolbar',
		[]
	);

	if (function_exists('blocksy_output_add_to_wish_list')) {
		$maybe_wish_list = blocksy_output_add_to_wish_list('archive');

		if (! empty($maybe_wish_list)) {
			$components[] = $maybe_wish_list;
		}
	}

	if (function_exists('blocksy_output_add_to_compare')) {
		$maybe_compare = blocksy_output_add_to_compare('archive');

		if (! empty($maybe_compare)) {
			$components[] = $maybe_compare;
		}
	}

	if (function_exists('blocksy_output_quick_view_link')) {
		$maybe_quick_view = blocksy_output_quick_view_link();

		if (! empty($maybe_quick_view)) {
			$components[] = $maybe_quick_view;
		}
	}

	if (! empty($components)) {
		return blocksy_html_tag(
			'div',
			[
				'class' => 'ct-woo-card-extra',
				'data-type' => $shop_cards_type === 'type-3' ? 'type-2' : 'type-1'
			],
			implode(' ', $components)
		);
	}

	return '';
}

$action_to_hook = 'wp';

if (wp_doing_ajax()) {
	$action_to_hook = 'init';
}

add_action($action_to_hook, function () {
	if (
		blocksy_get_theme_mod('has_star_rating', 'yes') !== 'yes'
		&&
		!is_product()
	) {
		add_filter(
			'woocommerce_product_get_rating_html',
			function ($html) {
				return str_replace(
					'class="star-rating"',
					'class="star-rating" data-customize-hide',
					$html
				);
			}
		);
	}

	remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
	remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
	remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
	remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
	remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
	remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
	remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
	remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);

	// Category cards
	remove_action('woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail');
	remove_action('woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open');
	remove_action('woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title');
	remove_action('woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close');

	blocksy_manager()->get_hooks()->redirect_callbacks([
		'token' => 'product_card_type_2',
		'source' => [
			'woocommerce_before_shop_loop_item_title',
			'woocommerce_shop_loop_item_title'
		],
		'destination' => 'blocksy:woocommerce:product-card:title:before'
	]);

	blocksy_manager()->get_hooks()->redirect_callbacks([
		'token' => 'product_card_type_2',
		'source' => [
			'woocommerce_after_shop_loop_item_title',
		],
		'destination' => 'blocksy:woocommerce:product-card:title:after'
	]);

	add_action(
		'woocommerce_shop_loop',
		function () {
			global $blocksy_rendering_woo_card;
			$blocksy_rendering_woo_card = true;
		}
	);

	add_action(
		'woocommerce_before_shop_loop_item_title',
		function () {
			global $product;
			global $blocksy_rendering_woo_card;
			$default_product_layout = blocksy_get_woo_archive_layout_defaults();

			$shop_cards_type = blocksy_get_theme_mod('shop_cards_type', 'type-1');

			$render_layout_config = blocksy_get_theme_mod(
				'woo_card_layout',
				$default_product_layout
			);

			$render_layout_config = blocksy_normalize_layout(
				$render_layout_config,
				$default_product_layout
			);
			
			foreach ($render_layout_config as $layout) {
				if (! $layout['enabled'] ) {
					continue;
				}

				if ($layout['id'] === 'product_image') {
					blocksy_template_loop_product_thumbnail($layout);
					continue;
				}

				if ($layout['id'] === 'product_title') {
					do_action('blocksy:woocommerce:product-card:title:before');

					$link_attrs = apply_filters(
						'blocksy:woocommerce:product-card:title:link',
						[
							'href' => get_the_permalink(),
							'target' => '_self'
						]
					);

					echo blocksy_html_tag(
						'h2',
						[
							'class' => esc_attr(
								apply_filters(
									'woocommerce_product_loop_title_classes',
									'woocommerce-loop-product__title'
									)
								),
						],
						blocksy_html_tag(
							'a',
							array_merge(
								[
									'class' => 'woocommerce-LoopProduct-link woocommerce-loop-product__link',
								],
								$link_attrs
							),
							get_the_title()
						)
					);

					do_action('blocksy:woocommerce:product-card:title:after');

					continue;
				}

				if (
					$shop_cards_type !== 'type-2'
					&&
					$layout['id'] === 'product_price'
				) {
					do_action('blocksy:woocommerce:product-card:price:before');

					ob_start();
					woocommerce_template_loop_price();
					$default_price = ob_get_clean();

					echo apply_filters(
						'blocksy:woocommerce:product-card:price',
						$default_price
					);

					do_action('blocksy:woocommerce:product-card:price:after');
					continue;
				}

				if ($layout['id'] === 'product_rating') {
					woocommerce_template_loop_rating();

					continue;
				}

				if ($layout['id'] === 'product_meta') {
					$style = isset($layout['style']) ? $layout['style'] : 'simple';

					echo blocksy_post_meta(
						[
							[
								'id' => 'categories',
								'enabled' => true,
								'style' => $style
							],
						]
					);

					continue;
				}

				if ($layout['id'] === 'product_desc') {
					echo blocksy_entry_excerpt([
						'length' => blocksy_akg(
							'excerpt_length',
							$layout,
							'40'
						),
					]);
					continue;
				}

				if (
					$shop_cards_type === 'type-1'
					&&
					$layout['id'] === 'product_add_to_cart'
				) {
					do_action('blocksy:woocommerce:product-card:actions:before');
					echo '<div class="ct-woo-card-actions">';
					woocommerce_template_loop_add_to_cart();
					echo '</div>';
					do_action('blocksy:woocommerce:product-card:actions:after');
					continue;
				}

				if (
					$shop_cards_type === 'type-2'
					&&
					$layout['id'] === 'product_add_to_cart_and_price'
				) {
					do_action('blocksy:woocommerce:product-card:actions:before');
					echo '<div class="ct-woo-card-actions">';
					woocommerce_template_loop_price();
					woocommerce_template_loop_add_to_cart();
					echo '</div>';
					do_action('blocksy:woocommerce:product-card:actions:after');
					continue;
				}

				$blocksy_rendering_woo_card = true;
				do_action('blocksy:woocommerce:product-card:custom:layer', $layout);
			}

			$blocksy_rendering_woo_card = false;
		},
		10
	);

	add_action(
		'woocommerce_before_subcategory_title',
		function ($category) {
			global $blocksy_rendering_woo_card;
			$default_product_layout = blocksy_get_woo_archive_layout_defaults();

			$shop_cards_type = blocksy_get_theme_mod('shop_cards_type', 'type-1');

			$render_layout_config = blocksy_get_theme_mod(
				'woo_card_layout',
				$default_product_layout
			);

			$render_layout_config = blocksy_normalize_layout(
				$render_layout_config,
				$default_product_layout
			);

			foreach ($render_layout_config as $layout) {
				if (! $layout['enabled'] ) {
					continue;
				}

				if ($layout['id'] === 'product_image') {
					$thumbnail_id = get_term_meta(
						$category->term_id,
						'thumbnail_id',
						true
					);

					$hover_value = blocksy_akg('product_image_hover', $layout, 'none');

					$has_lazy_load_shop_card_image = blocksy_get_theme_mod(
						'has_lazy_load_shop_card_image',
						'yes'
					);

					echo blocksy_html_tag(
						'figure',
						[],
						blocksy_media([
							'no_image_type' => 'woo',
							'attachment_id' => $thumbnail_id,
							'size' => 'woocommerce_archive_thumbnail',
							'ratio' => blocksy_get_woocommerce_ratio([
								'key' => 'archive_thumbnail',
								'cropping' => blocksy_akg(
									'blocksy_woocommerce_archive_thumbnail_cropping',
									$layout,
									'predefined'
								)
							]),
							'tag_name' => 'a',
							'html_atts' => [
								'href' => get_term_link( $category, 'product_cat' ),
							],
							'lazyload' => $has_lazy_load_shop_card_image === 'yes',
							'class' => $hover_value !== 'none' ? 'has-hover-effect' : '',
						])
					);

					continue;
				}

				if ($layout['id'] === 'product_title') {
					$category_name = esc_html($category->name);

					if ($category->count > 0) {
						$category_name .= apply_filters(
							'woocommerce_subcategory_count_html',
							' <mark class="count">(' . esc_html($category->count) . ')</mark>',
							$category
						);
					}

					echo blocksy_html_tag(
						'h2',
						[
							'class' => 'woocommerce-loop-category__title',
						],
						blocksy_html_tag(
							'a',
							[
								'href' => esc_url(
									get_term_link($category, 'product_cat')
								),
								'class' => 'woocommerce-loop-product__link',
							],
							$category_name
						)
					);

					continue;
				}

				if ($layout['id'] === 'product_desc') {
					if (! empty($category->category_description) ) {
						echo blocksy_html_tag(
							'div',
							[
								'class' => 'entry-excerpt',
							],
							$category->category_description
						);
					}

					continue;
				}
			}
		}
	);

}, 15000);

