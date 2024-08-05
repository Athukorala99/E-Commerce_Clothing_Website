<?php

namespace Blocksy;

class WooCommerceBoot {
	public function __construct() {
		add_filter('blocksy:general:ct-scripts-localizations', function ($data) {
			if (
				blocksy_get_theme_mod('has_product_single_lightbox', 'no') === 'yes'
				||
				is_customize_preview()
			) {
				$data['has_product_single_lightbox'] = true;
			}

			return $data;
		});

		add_action('after_setup_theme', function () {
			add_theme_support(
				'woocommerce',
				[
					'gallery_thumbnail_image_width' => blocksy_get_theme_mod(
						'gallery_thumbnail_image_width',
						100
					)
				]
			);

			if (
				blocksy_get_theme_mod('has_product_single_lightbox', 'no') === 'yes'
				||
				is_customize_preview()
			) {
				add_theme_support('wc-product-gallery-lightbox');
			}

			if (
				blocksy_get_theme_mod('has_product_single_zoom', 'yes') === 'yes'
				||
				is_customize_preview()
			) {
				add_theme_support('wc-product-gallery-zoom');
			}
		});

		if (! wp_doing_ajax()) {
			add_filter('template_include', function ($template) {
				if (
					! blocksy_manager()->screen->uses_woo_default_template()
					||
					! blocksy_woocommerce_has_flexy_view()
				) {
					add_theme_support('wc-product-gallery-slider');
				}

				return $template;
			}, 900000009);
		} else {
			add_action('init', function () {
				if (
					! blocksy_manager()->screen->uses_woo_default_template()
					||
					! blocksy_woocommerce_has_flexy_view()
				) {
					add_theme_support('wc-product-gallery-slider');
				}
			});
		}

		add_filter('woocommerce_enqueue_styles', '__return_empty_array');

		add_action('wp_enqueue_scripts', function () {
			if (
				blocksy_manager()->screen->uses_woo_default_template()
				&&
				blocksy_woocommerce_has_flexy_view()
			) {
				wp_deregister_script('yith_wapo_color_label_frontend');
			}
		}, 100);

		add_action('wp_enqueue_scripts', function () {
			$render = new \Blocksy_Header_Builder_Render();

			if ($render->contains_item('cart') || is_customize_preview()) {
				wp_enqueue_script('wc-cart-fragments');
			}

			if (! function_exists('is_shop')) return;

			$theme = blocksy_get_wp_parent_theme();

			wp_enqueue_style(
				'ct-woocommerce-styles',
				get_template_directory_uri() . '/static/bundle/woocommerce.min.css',
				[],
				$theme->get('Version')
			);

			// wp_dequeue_style( 'wc-block-style' );
		});

		add_action(
			'blocksy:widgets_init',
			function ($sidebar_title_tag) {
				register_sidebar(
					[
						'name' => esc_html__('WooCommerce Sidebar', 'blocksy'),
						'id' => 'sidebar-woocommerce',
						'description' => esc_html__('Add widgets here.', 'blocksy'),
						'before_widget' => '<div class="ct-widget %2$s" id="%1$s">',
						'after_widget' => '</div>',
						'before_title' => '<' . $sidebar_title_tag . ' class="widget-title">',
						'after_title' => '</' . $sidebar_title_tag . '>',
					]
				);
			}
		);
	}
}
