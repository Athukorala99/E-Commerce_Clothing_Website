<?php

namespace Blocksy;

class WooCommerceSingle {
	public $additional_actions = null;

	public function __construct() {
		$this->additional_actions = new SingleProductAdditionalActions();

		new WooCommerceAddToCart();
	}

	public function render_layout($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'layout' => null,
				'defaults' => null,
				'exclude' => null
			]
		);

		if (! $args['defaults']) {
			throw new Error('No defaults provided');
		}

		if (! $args['layout']) {
			$args['layout'] = blocksy_get_theme_mod(
				'woo_single_layout',
				blocksy_get_woo_single_layout_defaults()
			);
		}

		if ($args['defaults']) {
			$args['layout'] = blocksy_normalize_layout(
				$args['layout'],
				$args['defaults']
			);
		}

		// TODO: maybe to refactor
		if ($args['exclude']) {
			$exclude_column_ids = array_column($args['exclude'], 'id');

			$args['layout'] = array_filter($args['layout'], function($layer) use ($exclude_column_ids) {
				return ! in_array($layer['id'], $exclude_column_ids);
			});
		}

		$args['layout'] = apply_filters(
			'blocksy:woocommerce:product-single:layout',
			$args['layout']
		);

		foreach ($args['layout'] as $layer) {
			if (! $layer['enabled']) {
				continue;
			}

			if (method_exists($this, $layer['id'])) {
				$this->{$layer['id']}($layer);
				continue;
			}

			do_action('blocksy:woocommerce:product:custom:layer', $layer);
		}
	}

	public function product_title($layer) {
		do_action('blocksy:woocommerce:product-single:title:before');
		woocommerce_template_single_title();
		do_action('blocksy:woocommerce:product-single:title:after');
	}

	public function product_rating($layer) {
		do_action('blocksy:woocommerce:product-single:rating:before');
		woocommerce_template_single_rating();
		do_action('blocksy:woocommerce:product-single:rating:after');
	}

	public function product_price($layer) {
		do_action('blocksy:woocommerce:product-single:price:before');
		woocommerce_template_single_price();
		do_action('blocksy:woocommerce:product-single:price:after');
	}

	public function product_desc($layer) {
		do_action('blocksy:woocommerce:product-single:excerpt:before');
		woocommerce_template_single_excerpt();
		do_action('blocksy:woocommerce:product-single:excerpt:after');
	}

	public function divider($layer) {
		echo blocksy_html_tag(
			'span',
			[
				'class' => 'ct-product-divider',
				'data-id' => blocksy_akg('__id', $layer, 'default')
			],
			''
		);
	}

	public function product_add_to_cart($layer) {
		do_action('blocksy:woocommerce:product-single:add_to_cart:before');

		$section_title = blocksy_akg('add_to_cart_layer_title', $layer, '');

		ob_start();
		woocommerce_template_single_add_to_cart();
		$single_add_to_cart = ob_get_clean();

		echo blocksy_html_tag(
			'div',
			[
				'class' => 'ct-product-add-to-cart',
			],
			(
				! empty($section_title) || is_customize_preview() ?
				blocksy_html_tag(
					'span',
					[
						'class' => 'ct-module-title',
					],
					$section_title
				) : ''
			) .
			$single_add_to_cart
		);

		do_action('blocksy:woocommerce:product-single:add_to_cart:after');
	}

	public function product_meta($layer) {
		do_action('blocksy:woocommerce:product-single:meta:before');
		woocommerce_template_single_meta();
		do_action('blocksy:woocommerce:product-single:meta:after');
	}

	public function product_payment_methods($layer) {
		$items = [
			[
				'id' =>  'item_mastercard',
				'enabled' =>  true,
				'label' =>  'Mastercard'
			],
			[
				'id' =>  'item_visa',
				'enabled' =>  true,
				'label' =>  'Visa'
			],
			[
				'id' =>  'item_amex',
				'enabled' =>  true,
				'label' =>  'Amex'
			],
			[
				'id' =>  'item_discover',
				'enabled' =>  true,
				'label' =>  'Discover'
			],
		];

		if (isset($layer['payment_items'])) {
			$items = $layer['payment_items'];
		}

		if (! count($items)) {
			return;
		}

		$data_color = blocksy_akg('payment_icons_color', $layer, 'default');
		$section_title = blocksy_akg('payment_methods_title', $layer, __('Guaranteed Safe Checkout', 'blocksy'));

		$out = '<fieldset class="ct-payment-methods" data-color="' . $data_color . '">';
		$out .= '<legend>' . $section_title . '</legend>';

		$icons = [
			'item_mastercard' => '<svg class="ct-icon" width="35" height="35" viewBox="0 0 35 35">
				<path fill="var(--theme-icon-color, #F2F0EB)" d="M2.92,5.83h29.17c1.61,0,2.92,1.31,2.92,2.92v17.5c0,1.61-1.31,2.92-2.92,2.92H2.92C1.31,29.17,0,27.86,0,26.25V8.75C0,7.14,1.31,5.83,2.92,5.83z"/>
				<path fill="var(--theme-icon-color-2, #E82128)" d="M15.18,17.5c-0.01-1.89,0.85-3.68,2.33-4.85c-2.54-1.99-6.17-1.7-8.36,0.66s-2.19,6.02,0,8.39s5.83,2.65,8.36,0.66C16.02,21.18,15.17,19.39,15.18,17.5z"/>
				<path fill="var(--theme-icon-color-2, #F49D20)" d="M27.5,17.5c0,2.36-1.35,4.52-3.48,5.55c-2.13,1.04-4.66,0.76-6.52-0.7c2.68-2.11,3.14-5.99,1.04-8.67c0,0,0,0,0,0c-0.3-0.39-0.65-0.74-1.04-1.05c1.86-1.46,4.39-1.73,6.52-0.7S27.5,15.13,27.5,17.5z"/>
				<path fill="var(--theme-icon-color-2, #F16223)" d="M18.54,13.68c-0.3-0.39-0.65-0.74-1.04-1.05c-1.48,1.17-2.34,2.96-2.33,4.85c-0.01,1.89,0.85,3.68,2.33,4.85C20.18,20.24,20.65,16.36,18.54,13.68z" stroke="var(--theme-icon-color, transparent)" stroke-width="1.5"/>
			</svg>',

			'item_visa' => '<svg class="ct-icon" width="35" height="35" viewBox="0 0 35 35">
				<path fill="var(--theme-icon-color, #2A2C6B)" d="M2.92 5.83h29.17c1.61 0 2.92 1.31 2.92 2.92v17.5c0 1.61-1.31 2.92-2.92 2.92H2.92C1.31 29.17 0 27.86 0 26.25V8.75c0-1.61 1.31-2.92 2.92-2.92z"/>
				<path fill="#FFF" d="m17.4 14.14-1.46 6.74h-1.75l1.46-6.74h1.75zm7.33 4.37.92-2.53.53 2.53h-1.45zm1.95 2.4h1.62l-1.41-6.74h-1.46c-.32 0-.61.2-.73.5l-2.62 6.25h1.84l.36-1.01h2.19l.21 1zm-4.55-2.19c.01-1.82-2.44-1.95-2.44-2.68 0-.24.23-.5.73-.56.59-.06 1.18.04 1.72.3l.31-1.46c-.52-.2-1.08-.3-1.63-.3-1.72 0-2.92.92-2.92 2.23 0 .97.87 1.51 1.52 1.83.66.32.91.55.9.84 0 .45-.54.66-1.04.66-.62.01-1.23-.14-1.78-.44l-.31 1.46c.62.24 1.28.36 1.94.36 1.83 0 3.03-.9 3.04-2.3m-7.23-4.54-2.83 6.74h-1.9l-1.39-5.39a.707.707 0 0 0-.42-.59 7.55 7.55 0 0 0-1.72-.57l.04-.2h2.97c.4 0 .74.29.81.69l.73 3.9 1.82-4.59h1.89z"/>
			</svg>',

			'item_amex' => '<svg class="ct-icon" width="35" height="35" viewBox="0 0 35 35">
				<path fill="var(--theme-icon-color, #1F72CD)" d="M2.92 5.83h29.17c1.61 0 2.92 1.31 2.92 2.92v17.5c0 1.61-1.31 2.92-2.92 2.92H2.92C1.31 29.17 0 27.86 0 26.25V8.75c0-1.61 1.31-2.92 2.92-2.92z"/>
				<path fill="#FFF" fill-rule="evenodd" d="m6.5 13.9-3.2 7.2h3.8l.5-1.2h1.1l.5 1.2h4.2v-.9l.4.9H16l.4-.9v.9h8.7l1.1-1.1 1 1.1h4.5l-3.2-3.6 3.2-3.6h-4.4l-1 1.1-1-1.1h-9.5l-1 1.9-.8-1.9h-3.8v.9l-.5-.9H6.5zm13 1h5l1.5 1.7 1.6-1.7h1.5l-2.3 2.6 2.3 2.6h-1.6L26 18.4l-1.6 1.7h-4.9v-5.2zm1.2 2.1v-.9h3.1l1.4 1.5-1.4 1.4h-3.1v-1h2.7v-1.1h-2.7v.1zM7.2 14.9h1.9l2.1 4.9v-4.9h2l1.6 3.5 1.5-3.5h2v5.2h-1.2V16l-1.8 4.1h-1.1L12.4 16v4.1H9.9l-.5-1.2H6.8l-.5 1.2H5l2.2-5.2zm.1 3 .9-2.1.8 2.1H7.3z" clip-rule="evenodd"/>
			</svg>',

			'item_discover' => '<svg class="ct-icon" width="35" height="35" viewBox="0 0 35 35">
				<path fill="var(--theme-icon-color, #4D4D4D)" d="M35 8.75v17.5c0 1.61-1.31 2.92-2.92 2.92H2.92c-.34 0-.67-.06-.99-.18A2.912 2.912 0 0 1 0 26.25V8.75c0-1.61 1.31-2.92 2.92-2.92h29.17c1.6 0 2.91 1.31 2.91 2.92z"/>
				<path fill="var(--theme-icon-color-2, #FD6020)" d="M35 17.5v8.72c0 1.63-1.3 2.94-2.91 2.94H2.99c-.34.01-.67-.04-.99-.16 2.44-.35 4.8-.8 7.12-1.3.61-.12 1.21-.26 1.8-.4 9.62-2.22 17.94-5.49 22.63-8.69.52-.37 1.01-.74 1.45-1.11zm-14.15-1.58c0-1.37-1.11-2.48-2.49-2.49a2.49 2.49 0 1 0 2.49 2.49z"/>
				<path fill="var(--theme-icon-color, #FD6020)" d="m11.19 28.76-.55.12c-.42.1-.86.2-1.3.28h-.15 22.9c1.61 0 2.92-1.31 2.92-2.92v-6.78c-.19.15-.41.29-.63.45-4.97 3.35-13.63 6.66-23.19 8.85z"/>
				<path fill="#FFF" d="M4.24 13.56v.03H2.92v4.64h1.33c.6.03 1.2-.15 1.68-.52.78-.65 1.05-1.74.67-2.68a2.374 2.374 0 0 0-2.36-1.47zm1.08 3.53c-.35.3-.8.45-1.25.41h-.23v-3.12h.23c.45-.05.9.09 1.25.38.33.3.52.72.51 1.17.01.44-.18.86-.51 1.16zm1.86-3.51h.91v4.68h-.91v-4.68zm4.49 3.26c.01.42-.16.83-.46 1.12-.31.29-.72.44-1.14.41-.68.04-1.32-.31-1.66-.89l.59-.57c.18.39.57.63.99.63.19.02.37-.05.51-.17s.22-.3.22-.49c0-.2-.11-.38-.28-.48-.2-.11-.42-.2-.63-.27-.85-.31-1.14-.63-1.14-1.28.01-.37.17-.73.44-.98.28-.25.64-.38 1.02-.36.51 0 1 .18 1.37.52l-.47.62a.948.948 0 0 0-.73-.38c-.39 0-.68.26-.68.52s.18.39.73.59c1.02.38 1.32.72 1.32 1.46zm2.79-3.37c.39 0 .78.1 1.12.29v1.07c-.29-.33-.71-.52-1.14-.52-.42.01-.81.18-1.1.48-.29.3-.44.71-.43 1.12-.02.43.13.85.43 1.15s.71.48 1.14.47c.42 0 .83-.19 1.1-.51v1.07c-.35.18-.75.27-1.14.27a2.427 2.427 0 0 1-2.47-2.44c0-.66.27-1.28.74-1.74.46-.46 1.09-.71 1.75-.71zm9.62.11h.99l-2.03 4.8h-.49l-1.98-4.8h.99l1.25 3.14 1.27-3.14zm1.4 0h2.6v.79H26.4v1.04h1.62v.79H26.4v1.26h1.68v.79h-2.6v-4.67zm5.14 2.72c.65-.1 1.11-.69 1.06-1.34 0-.88-.6-1.37-1.66-1.37h-1.34v4.64h.92v-1.85h.1l1.27 1.85h1.11l-1.46-1.93zm-.72-.56h-.3v-1.41h.29c.55 0 .86.23.86.73.01.49-.31.68-.85.68z"/>
			</svg>',

			'item_paypal' => '<svg class="ct-icon" width="35" height="35" viewBox="0 0 35 35">
				<path fill="var(--theme-icon-color, #003087)" d="M2.92 5.83h29.17c1.61 0 2.92 1.31 2.92 2.92v17.5c0 1.61-1.31 2.92-2.92 2.92H2.92C1.31 29.17 0 27.86 0 26.25V8.75c0-1.61 1.31-2.92 2.92-2.92z"/>
				<path fill="#FFF" d="M13.6 15.9h-1c-.1 0-.2.1-.2.1v.3l-.1-.1c-.2-.3-.7-.4-1.2-.4-1.1 0-2 .8-2.2 2-.1.6 0 1.1.4 1.5.3.4.7.5 1.2.5.9 0 1.4-.6 1.4-.6v.3c0 .1.1.2.2.2h.9c.1 0 .3-.1.3-.2l.5-3.3c0-.2-.1-.3-.2-.3zm-2.4 2.8c-.3 0-.5-.1-.7-.3-.1-.2-.2-.4-.2-.7.1-.6.5-.9 1.1-.9.3 0 .5.1.7.3.1.2.2.4.2.7-.1.6-.6.9-1.1.9zM9 14.5c-.3-.3-.8-.5-1.5-.5h-2c-.1 0-.3.1-.3.2l-.8 5.2c0 .1.1.2.2.2h1c.1 0 .3-.1.3-.2L6 18c0-.1.1-.2.3-.2h.6c1.3 0 2.1-.7 2.3-1.9.2-.6.1-1.1-.2-1.4zm-1.2 1.4c-.1.7-.7.7-1.2.7h-.4l.2-1.4c0-.1.1-.1.2-.1h.1c.4 0 .7 0 .9.2.2.2.2.4.2.6zm11.2.3-3.3 4.7c0 .1-.1.1-.2.1h-1c-.1 0-.2-.2-.1-.3l1-1.4-1.1-3.2c0-.1 0-.2.2-.2h1c.1 0 .2.1.3.2l.6 1.9 1.4-2c.1-.1.1-.1.2-.1h1s.1.1 0 .3zm11.6-2-.8 5.2c0 .1-.1.2-.3.2h-.8c-.1 0-.2-.1-.2-.2l.8-5.3c0-.1.1-.1.2-.1h.9c.2 0 .2.1.2.2zm-2.4 1.7h-1c-.1 0-.2.1-.2.1v.3l-.1-.1c-.2-.3-.7-.4-1.2-.4-1.1 0-2 .8-2.2 2-.1.6 0 1.1.4 1.5.3.4.7.5 1.2.5.9 0 1.4-.6 1.4-.6v.3c0 .1.1.2.2.2h.9c.1 0 .3-.1.3-.2l.5-3.3c0-.2-.1-.3-.2-.3zm-2.5 2.8c-.3 0-.5-.1-.7-.3-.1-.2-.2-.4-.2-.7.1-.6.5-.9 1.1-.9.3 0 .5.1.7.3.1.2.2.4.2.7-.1.6-.5.9-1.1.9zm-2.1-4.2c-.3-.3-.8-.5-1.5-.5h-2c-.1 0-.3.1-.3.2l-.8 5.2c0 .1.1.2.2.2h1c.1 0 .2-.1.2-.2l.2-1.5c0-.1.1-.2.3-.2h.6c1.3 0 2.1-.7 2.3-1.9.1-.5 0-1-.2-1.3zm-1.3 1.4c-.1.7-.7.7-1.2.7h-.3l.2-1.4c0-.1.1-.1.2-.1h.1c.4 0 .7 0 .9.2.1.2.2.4.1.6z"/>
			</svg>',

			'item_apple_pay' => '<svg class="ct-icon" width="35" height="35" viewBox="0 0 35 35">
				<path fill="var(--theme-icon-color, #000)" d="M2.92 5.83h29.17c1.61 0 2.92 1.31 2.92 2.92v17.5c0 1.61-1.31 2.92-2.92 2.92H2.92C1.31 29.17 0 27.86 0 26.25V8.75c0-1.61 1.31-2.92 2.92-2.92z"/>
				<path fill="#FFF" fill-rule="evenodd" d="M8.32 14.4c.5 0 .9-.2 1.2-.6.3-.4.5-.8.4-1.3-.4 0-.9.3-1.2.6-.2.4-.4.9-.4 1.3zm5.6 6.4v-7.7h2.9c1.5 0 2.5 1 2.5 2.5s-1.1 2.5-2.6 2.5h-1.6v2.6h-1.2v.1zm-3.9-6.3c-.4 0-.8.1-1.1.2-.2.1-.4.1-.5.1s-.3-.1-.5-.1c-.3-.1-.5-.2-.9-.2-.7 0-1.4.4-1.7 1.1-.7 1.3-.2 3.2.5 4.2.3.5.8 1.1 1.3 1.1.2 0 .4-.1.6-.2s.4-.2.8-.2c.3 0 .5.1.7.2s.4.2.6.2c.6 0 .9-.5 1.3-1 .4-.6.5-1.1.6-1.2-.1-.1-1.1-.5-1.1-1.7 0-1 .8-1.5.9-1.6-.5-.8-1.2-.9-1.5-.9zm11.5 6.4c.7 0 1.4-.4 1.7-1v.9h1.1V17c0-1.1-.9-1.8-2.2-1.8-1.3 0-2.2.7-2.2 1.7h1.1c.1-.5.5-.8 1.1-.8.7 0 1.1.3 1.1 1v.4l-1.5.1c-1.4.1-2.1.7-2.1 1.6 0 1 .8 1.7 1.9 1.7zm.3-.9c-.6 0-1-.3-1-.8s.4-.8 1.1-.8l1.3-.1v.4c0 .7-.6 1.3-1.4 1.3zm6.2 1.1c-.5 1.4-1 1.8-2.2 1.8h-.5V22h.3c.5 0 .8-.2 1-.8l.1-.3-2-5.6h1.3l1.4 4.6 1.4-4.6h1.2l-2 5.8zm-12.9-7h1.4c1 0 1.6.6 1.6 1.5 0 1-.6 1.5-1.6 1.5h-1.4v-3z" clip-rule="evenodd"/>
			</svg>',

			'item_google_pay' => '<svg class="ct-icon" width="35" height="35" viewBox="0 0 35 35">
				<path fill="var(--theme-icon-color, #241F20)" d="M2.92 5.83h29.17c1.61 0 2.92 1.31 2.92 2.92v17.5c0 1.61-1.31 2.92-2.92 2.92H2.92C1.31 29.17 0 27.86 0 26.25V8.75c0-1.61 1.31-2.92 2.92-2.92z"/>
				<path fill="#FFF" fill-rule="evenodd" d="M16.5 21.3v-3h1.6c.6 0 1.2-.2 1.6-.6l.1-.1c.8-.9.8-2.2-.1-3-.4-.4-1-.7-1.6-.6h-2.5v7.5h.9v-.2zm0-4v-2.6h1.6c.3 0 .7.1.9.4.5.5.5 1.3 0 1.8-.2.3-.6.4-.9.4h-1.6zm7.7-.7c-.4-.4-1-.6-1.7-.6-.9 0-1.6.3-2 1l.8.5c.3-.5.7-.7 1.2-.7.3 0 .7.1.9.4.2.2.4.5.4.9v.2c-.4-.2-.8-.3-1.4-.3-.7 0-1.2.2-1.6.5s-.6.7-.6 1.3c0 .5.2.9.6 1.2.4.3.8.5 1.4.5.7 0 1.2-.3 1.6-.9v.7h.9v-3.1c.1-.7-.1-1.3-.5-1.6zm-2.5 3.7c-.2-.1-.3-.4-.3-.6 0-.3.1-.5.4-.7s.6-.3 1-.3c.5 0 .9.1 1.2.4 0 .4-.2.8-.5 1.1-.3.3-.7.4-1.1.4-.3 0-.5-.1-.7-.3zm5.1 3.2 3.2-7.3h-1l-1.5 3.7-1.5-3.7h-1l2.1 4.8-1.2 2.6h.9v-.1z" clip-rule="evenodd"/>
				<path fill="var(--theme-icon-color-2, #4285F4)" d="M13.3 17.6c0-.3 0-.6-.1-.9h-4v1.6h2.3c-.1.5-.4 1-.8 1.3v1.1H12c.8-.7 1.3-1.8 1.3-3.1z"/>
				<path fill="var(--theme-icon-color-2, #34A853)" d="M9.2 21.8c1.1 0 2.1-.4 2.8-1l-1.4-1.1c-.4.3-.9.4-1.4.4-1.1 0-2-.8-2.4-1.8H5.5v1.1c.7 1.5 2.1 2.4 3.7 2.4z"/>
				<path fill="var(--theme-icon-color-2, #FBBC04)" d="M6.9 18.3c-.2-.5-.2-1.1 0-1.6v-1.1H5.5c-.6 1.2-.6 2.6 0 3.8l1.4-1.1z"/>
				<path fill="var(--theme-icon-color-2, #EA4335)" d="M9.2 14.9c.6 0 1.2.2 1.6.6l1.2-1.2c-.8-.7-1.8-1.1-2.8-1.1-1.6 0-3.1.9-3.8 2.4l1.4 1.1c.4-1 1.3-1.8 2.4-1.8z"/>
			</svg>',

			'custom_link' => '<svg class="ct-icon" width="12" height="12" viewBox="0 0 10 10"><path d="M5.9 9.5h-.2l-1.8-.9c-.2-.1-.3-.2-.3-.4V5.4L.1 1.2C0 1.1 0 .9 0 .7.1.5.2.4.4.4h9.1c.2 0 .3.1.4.3s0 .3-.1.5L6.4 5.4v3.7c0 .2-.1.3-.2.4h-.3z"/></svg>',
		];

		foreach ($items as $key => $item) {
			if ($item['enabled']) {

				$icon = blocksy_html_tag(
					'span',
					[
						'class' => 'ct-icon-container'
					],
					$icons[$item['id']]
				);

				if (
					isset($item['icon_source'])
					&&
					$item['icon_source'] === 'custom'
					&&
					function_exists('blc_get_icon')
				) {
					$icon = blc_get_icon(
						[
							'icon_descriptor' => blocksy_akg('icon', $item, [
								'icon' => "blc blc-user" // TODO: get defaults from item ID
							])
						]
					);
				}

				$out .= $icon;
			}
		}

		$out .= '</fieldset>';

		echo $out;
	}

	public function additional_info($layer) {
		$items = [
			[
				'id' => 'additional_info_item',
				'enabled' => true,
				'item_title' => __('Premium Quality', 'blocksy'),
			],
			[
				'id' => 'additional_info_item',
				'enabled' => true,
				'item_title' => __('Secure Payments', 'blocksy'),
			],
			[
				'id' => 'additional_info_item',
				'enabled' => true,
				'item_title' => __('Satisfaction Guarantee', 'blocksy')
			],
			[
				'id' => 'additional_info_item',
				'enabled' => true,
				'item_title' => __('Worldwide Shipping', 'blocksy')
			],
			[
				'id' => 'additional_info_item',
				'enabled' => true,
				'item_title' => __('Money Back Guarantee', 'blocksy')
			],
		];

		if (isset($layer['additional_info_items'])) {
			$items = $layer['additional_info_items'];
		}

		if (! count($items)) {
			return;
		}

		$section_title = __('Extra Features', 'blocksy');

		if (isset($layer['product_additional_info_title'])) {
			$section_title = $layer['product_additional_info_title'];
		}

		$out = '<div class="ct-product-additional-info">';
		$out .= '<span class="ct-module-title">' . $section_title . '</span>';
		$out .= '<ul>';

		foreach ($items as $key => $item) {
			$icon = '<svg width="15" height="15" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.6 0 12 0zm6.2 9.5-7.6 7.6c-.4.4-1.1.4-1.5 0l-3.3-3.3c-.4-.4-.4-1.1 0-1.5.4-.4 1.1-.4 1.5 0l2.5 2.5L16.7 8c.4-.4 1.1-.4 1.5 0 .4.4.4 1.1 0 1.5z"/></svg>';

			if ($item['enabled']) {
				$out .= '<li>';

				$icon = blocksy_html_tag(
					'span',
					[
						'class' => 'ct-icon-container'
					],
					$icon
				);

				if (
					function_exists('blc_get_icon')
					&&
					isset($item['icon_source'])
					&&
					$item['icon_source'] === 'custom'
				) {
					$icon = blc_get_icon(
						[
							'icon_descriptor' => blocksy_akg('icon', $item, [
								'icon' => "blc blc-user"
							])
						]
					);
				}

				$out .= $icon;

				$out .= blocksy_html_tag(
					'span',
					[
						'class' => 'ct-label'
					],
					$item['item_title']
				);
				$out .= '</li>';
			}
		}

		$out .= '</ul>';
		$out .= '</div>';

		echo $out;
	}
}
