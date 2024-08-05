<?php

add_action('rest_api_init', function () {
	if (! function_exists('is_shop')) {
		return;
	}

	if (
		! isset($_GET['post_type'])
		||
		(
			! str_contains($_GET['post_type'], 'product')
			&&
			$_GET['post_type'] !== 'ct_forced_any'
		)
	) {
		return;
	}

	register_rest_field('post', 'placeholder_image', array(
		'get_callback' => function ($post, $field_name, $request) {
			if ($post['type'] !== 'product') {
				return null;
			}

			return wc_placeholder_img_src('thumbnail');
		}
	));

	if (
		isset($_GET['product_price'])
		&&
		$_GET['product_price'] === 'true'
	) {
		register_rest_field('post', 'product_price', array(
			'get_callback' => function ($post, $field_name, $request) {
				if ($post['type'] !== 'product') {
					return 0;
				}

				$product = wc_get_product($post['id']);

				$price = $product->get_regular_price();
				$sale = $product->get_price();

				if (
					! $product->is_type('simple')
					&&
					! $product->is_type('external')
				) {
					return $product->get_price_html();
				}

				if ($product->is_taxable()) {
					if (defined('WC_ABSPATH')) {
						// WC 3.6+ - Cart and other frontend functions are not included for REST requests.
						include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
						include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
						include_once WC_ABSPATH . 'includes/wc-template-hooks.php';
					}

					if (null === WC()->session) {
						$session_class = apply_filters(
							'woocommerce_session_handler',
							'WC_Session_Handler'
						);

						WC()->session = new $session_class();
						WC()->session->init();
					}

					if (null === WC()->customer) {
						WC()->customer = new WC_Customer(
							get_current_user_id(),
							true
						);
					}

					$tax_display_mode = get_option('woocommerce_tax_display_shop');

					if ($tax_display_mode === 'incl') {
						$price = wc_get_price_including_tax($product, ['price' => $price]);
						$sale = wc_get_price_including_tax($product, ['price' => $sale]);
					} else {
						$price = wc_get_price_excluding_tax($product, ['price' => $price]);
						$sale = wc_get_price_excluding_tax($product, ['price' => $sale]);
					}
				}

				if ($sale && $product->is_on_sale()) {
					$sale_html = $sale ? blocksy_html_tag(
						'ins',
						[
							'aria-hidden' => 'true'
						],
						wc_price($sale)
					) : '';

					$price_html = blocksy_html_tag(
						'del',
						[],
						wc_price($price)
					);

					return $price ? blocksy_html_tag(
						'span',
						[
							'class' => 'sale-price'
						],
						$price_html . $sale_html
					) : 0;
				}

				return $price ? wc_price($price) : 0;
			},
			'update_callback' => null,
			'schema' => [
				'description' => __('Product Price', 'blocksy'),
				'type' => 'string'
			],
		));
	}

	if (
		isset($_GET['product_status'])
		&&
		$_GET['product_status'] === 'true'
	) {
		register_rest_field('post', 'product_status', array(
			'get_callback' => function ($post, $field_name, $request) {
				if ($post['type'] !== 'product') {
					return null;
				}

				$product = wc_get_product($post['id']);

				return $product->get_stock_status() === 'instock' ?
					__('In Stock', 'blocksy') :
					__('Out of Stock', 'blocksy');
			},
			'update_callback' => null,
			'schema' => [
				'description' => __('Product Status', 'blocksy'),
				'type' => 'string'
			],
		));
	}
});
