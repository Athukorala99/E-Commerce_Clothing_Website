<?php

namespace Blocksy;

class WooCommerceAddToCart {
	use WordPressActionsManager;

	private $actions = [
		[
			'action' => 'woocommerce_before_add_to_cart_form',
			'priority' => 10
		],

		[
			'action' => 'woocommerce_before_add_to_cart_quantity',
			'priority' => PHP_INT_MAX
		],

		[
			'action' => 'woocommerce_before_add_to_cart_button',
			'priority' => PHP_INT_MAX
		],

		[
			'action' => 'woocommerce_after_add_to_cart_button',
			'priority' => 100
		],

		[
			'action' => 'woocommerce_post_class',
			'priority' => 10
		]
	];

	public function __construct() {
		$this->attach_hooks([
			'exclude' => [
				'woocommerce_after_add_to_cart_button'
			]
		]);
	}

	private function output_cart_action_open() {
		if (
			(is_product() || wp_doing_ajax())
			&&
			! blocksy_manager()->screen->uses_woo_default_template()
		) {
			return;
		}

		$attr = apply_filters('blocksy:woocommerce:cart-actions:attr', [
			'class' => 'ct-cart-actions'
		]);

		echo '<div ' . blocksy_attr_to_html($attr) . '>';

		$this->attach_hooks([
			'only' => [
				'woocommerce_after_add_to_cart_button'
			]
		]);
	}

	public function woocommerce_before_add_to_cart_form() {
		global $product;
		global $root_product;

		$root_product = $product;
	}

	public function woocommerce_before_add_to_cart_quantity() {
		global $product;
		global $root_product;

		if (! $root_product) {
			return;
		}

		if (
			! $root_product->is_type('simple')
			&&
			! $root_product->is_type('variation')
			&&
			! $root_product->is_type('variable')
			&&
			! $root_product->is_type('subscription')
			&&
			! $root_product->is_type('variable-subscription')
		) {
			return;
		}

		$this->output_cart_action_open();
	}

	public function woocommerce_before_add_to_cart_button() {
		global $product;
		global $root_product;

		if (! $root_product) {
			return;
		}

		if (
			! $root_product->is_type('grouped')
			&&
			! $root_product->is_type('external')
		) {
			return;
		}

		$this->output_cart_action_open();
	}

	public function woocommerce_after_add_to_cart_button() {
		global $product;

		if (! $product) {
			return;
		}

		if (
			! $product->is_type('simple')
			&&
			! $product->is_type('variable')
			&&
			! $product->is_type('subscription')
			&&
			! $product->is_type('variable-subscription')
			&&
			! $product->is_type('grouped')
			&&
			! $product->is_type('external')
		) {
			return;
		}

		if (
			(
				$product->is_type('simple')
				||
				$product->is_type('variable')
				||
				$product->is_type('subscription')
				||
				$product->is_type('variable-subscription')
			)
			&&
			! did_action('woocommerce_before_add_to_cart_quantity')
		) {
			return;
		}

		echo '</div>';

		$this->detach_hooks();
	}

	public function woocommerce_post_class($classes) {
		global $product;
		global $woocommerce_loop;

		$default_product_layout = blocksy_get_woo_single_layout_defaults();

		$layout = blocksy_get_theme_mod(
			'woo_single_layout',
			blocksy_get_woo_single_layout_defaults()
		);

		$layout = blocksy_normalize_layout(
			$layout,
			$default_product_layout
		);

		$product_view_type = blocksy_get_product_view_type();

		if (
			$product_view_type === 'top-gallery'
			||
			$product_view_type === 'columns-top-gallery'
		) {
			$woo_single_split_layout = blocksy_get_theme_mod(
				'woo_single_split_layout',
				[
					'left' => blocksy_get_woo_single_layout_defaults('left'),
					'right' => blocksy_get_woo_single_layout_defaults('right')
				]
			);

			$layout = array_merge(
				$woo_single_split_layout['left'],
				$woo_single_split_layout['right']
			);
		}

		$add_to_cart_layer = array_values(array_filter($layout, function($k) {
			return $k['id'] === 'product_add_to_cart';
		}));

		if (
			empty($add_to_cart_layer)
			||
			! $product
			||
			$product->is_type('external')
			||
			$woocommerce_loop['name'] === 'related'
			||
			(
				! is_product()
				&&
				! wp_doing_ajax()
			)
		) {
			return $classes;
		}

		$has_ajax_add_to_cart = blocksy_get_theme_mod(
			'has_ajax_add_to_cart',
			'no'
		);

		if (
			$has_ajax_add_to_cart === 'yes'
			&&
			get_option('woocommerce_cart_redirect_after_add', 'no') === 'no'
		) {
			$classes[] = 'ct-ajax-add-to-cart';
		}

		return $classes;
	}
}


