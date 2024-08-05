<?php

namespace Blocksy\DbVersioning;

class V200 {
	public function migrate() {
		$this->migrate_sale_badge();
		$this->migrate_woocommerce_extra_features();
		$this->migrate_post_types_extra_features();
		$this->migrate_single_product();
		$this->migrate_archive_card();
		$this->migrate_image_sizes();

		new \Blocksy\LegacyWidgetsTransformer();

		$values_cleaner = new \Blocksy\DbVersioning\DefaultValuesCleaner();

		$values_cleaner->clean_header();
		$values_cleaner->clean_footer();

		$this->cleanup_color_palette_theme_mod();

		$should_migrate_color_palette = apply_filters(
			'blocksy:db-versioning:v2:should-migrate-color-palette',
			true
		);

		if ($should_migrate_color_palette) {
			$did_migrate = $this->migrate_color_palette();

			wp_cache_flush();

			if ($did_migrate) {
				return 'RETRY';
			}

			$r = new \Blocksy\Database\SearchReplace();

			$result = $r->invoke([
				'old' => 'paletteColor',
				'new' => 'theme-palette-color-'
			]);

			if ($result && isset($result['total']) && $result['total'] > 0) {
				return 'RETRY';
			}
		}
	}

	public function migrate_post_types_extra_features() {
		$blocksy_ext_post_types_extra_settings = get_option(
			'blocksy_ext_post_types_extra_settings',
			'__empty__'
		);

		if ($blocksy_ext_post_types_extra_settings !== '__empty__') {
			return;
		}

		if (
			in_array(
				'post-types-extra',
				get_option('blocksy_active_extensions', [])
			)
		) {
			update_option(
				'blocksy_ext_post_types_extra_settings',
				[
					'features' => [
						'read-time' => true,
						'dynamic-data' => true,
						'filtering' => true,
						'taxonomies-customization' => true
					]
				]
			);
		}
	}

	public function migrate_woocommerce_extra_features() {
		$blocksy_ext_woocommerce_extra_settings = get_option(
			'blocksy_ext_woocommerce_extra_settings',
			'__empty__'
		);

		if ($blocksy_ext_woocommerce_extra_settings !== '__empty__') {
			return;
		}

		$should_update = false;

		$woo_extra_settings = [
			'features' => [
				'floating-cart' => false,
				'quick-view' => false,
				'filters' => false,
				'wishlist' => false,
				'single-product-share-box' => false,
				'advanced-gallery' => false,
				'search-by-sku' => false,
				'free-shipping' => false,
				'variation-swatches' => false,
				'product-brands' => false,
				'product-affiliates' => false
			],

			'product-brands-slug' => 'brand'
		];

		if (
			in_array(
				'woocommerce-extra',
				get_option('blocksy_active_extensions', [])
			)
		) {
			$woo_extra_settings['advanced-gallery'] = true;

			if (
				get_theme_mod('has_archive_wishlist', 'no') === 'yes'
				||
				get_theme_mod('has_single_wishlist', 'no') === 'yes'
				||
				get_theme_mod('has_quick_view_wishlist', 'no') === 'yes'
			) {
				$woo_extra_settings['wishlist'] = true;
			}
		}

		$has_floating_bar = get_theme_mod('has_floating_bar', '__empty__');

		if ($has_floating_bar !== '__empty__') {
			$woo_extra_settings['features'][
				'floating-cart'
			] = $has_floating_bar === 'yes';

			$should_update = true;
		}

		$woocommerce_quickview_enabled = get_theme_mod(
			'woocommerce_quickview_enabled',
			'__empty__'
		);
		if ($woocommerce_quickview_enabled !== '__empty__') {
			$woo_extra_settings['features'][
				'quick-view'
			] = $woocommerce_quickview_enabled === 'yes';

			$should_update = true;
		}

		if ($should_update) {
			update_option(
				'blocksy_ext_woocommerce_extra_settings',
				$woo_extra_settings
			);
		}
	}

	public function migrate_sale_badge() {
		$sale_badge_custom_value = get_theme_mod(
			'sale_badge_custom_value',
			'__empty__'
		);

		if ($sale_badge_custom_value !== '__empty__') {
			set_theme_mod(
				'sale_badge_custom_value',
				str_replace('[value]', '{value}', $sale_badge_custom_value)
			);
		}

		$has_sale_badge = get_theme_mod('has_sale_badge', '__empty__');
		$has_product_single_onsale = get_option(
			'has_product_single_onsale',
			'__empty__'
		);

		if ($has_sale_badge === '__empty__' || is_array($has_sale_badge)) {
			return;
		}

		$future_value = [
			'archive' => true,
			'single' => true
		];

		$future_value['archive'] = $has_sale_badge === 'yes';

		if ($has_product_single_onsale !== '__empty__') {
			$future_value['single'] = $has_product_single_onsale === 'yes';
		}

		set_theme_mod('has_sale_badge', $future_value);
	}

	public function migrate_single_product() {
		if (! function_exists('blocksy_get_woo_single_layout_defaults')) {
			return;
		}

		$woo_single_layout = get_theme_mod(
			'woo_single_layout',
			blocksy_get_woo_single_layout_defaults()
		);

		$has_product_single_title = get_theme_mod(
			'has_product_single_title',
			'__empty__'
		);

		$has_product_single_rating = get_theme_mod(
			'has_product_single_rating',
			'__empty__'
		);

		$has_product_single_meta = get_theme_mod(
			'has_product_single_meta',
			'__empty__'
		);

		$touched = false;

		foreach ($woo_single_layout as $index => $layer) {
			if (
				$layer['id'] === 'product_title'
				&&
				$has_product_single_title !== '__empty__'
			) {
				$touched = true;
				$woo_single_layout[$index]['enabled'] =
					$has_product_single_title === 'yes';
			}

			if (
				$layer['id'] === 'product_rating'
				&&
				$has_product_single_rating !== '__empty__'
			) {
				$touched = true;
				$woo_single_layout[$index]['enabled'] =
					$has_product_single_rating === 'yes';
			}

			if (
				$layer['id'] === 'product_meta'
				&&
				$has_product_single_meta !== '__empty__'
			) {
				$touched = true;
				$woo_single_layout[$index]['enabled'] =
					$has_product_single_meta === 'yes';
			}
		}

		if ($touched) {
			set_theme_mod('woo_single_layout', $woo_single_layout);
		}
	}

	public function migrate_archive_card() {
		if (! function_exists('blocksy_get_woo_archive_layout_defaults')) {
			return;
		}

		$woo_card_layout = get_theme_mod(
			'woo_card_layout',
			blocksy_get_woo_archive_layout_defaults()
		);

		$touched = false;

		foreach ($woo_card_layout as $index => $layer) {
			if ($layer['id'] === 'product_image') {
				$blocksy_woocommerce_thumbnail_cropping = get_theme_mod(
					'blocksy_woocommerce_thumbnail_cropping',
					'__empty__'
				);

				if ($blocksy_woocommerce_thumbnail_cropping !== '__empty__') {
					$touched = true;
					$woo_card_layout[$index]['blocksy_woocommerce_archive_thumbnail_cropping'] = $blocksy_woocommerce_thumbnail_cropping;
				}

				$product_image_hover = get_theme_mod(
					'product_image_hover',
					'__empty__'
				);

				if ($product_image_hover !== '__empty__') {
					$touched = true;
					$woo_card_layout[$index]['product_image_hover'] = $product_image_hover;
				}

				$has_archive_video_thumbnail = get_theme_mod(
					'has_archive_video_thumbnail',
					'__empty__'
				);

				if ($has_archive_video_thumbnail !== '__empty__') {
					$touched = true;
					$woo_card_layout[$index]['has_archive_video_thumbnail'] = $has_archive_video_thumbnail;
				}
			}

			if ($layer['id'] === 'product_rating') {
				$has_star_rating = get_theme_mod(
					'has_star_rating',
					'__empty__'
				);

				if ($has_star_rating !== '__empty__') {
					$touched = true;
					$woo_card_layout[$index]['enabled'] = $has_star_rating === 'yes';
				}
			}

			if ($layer['id'] === 'product_meta') {
				$has_product_categories = get_theme_mod(
					'has_product_categories',
					'__empty__'
				);

				if ($has_product_categories !== '__empty__') {
					$touched = true;
					$woo_card_layout[$index]['enabled'] = $has_product_categories === 'yes';
				}
			}

			if ($layer['id'] === 'product_desc') {
				$has_excerpt = get_theme_mod(
					'has_excerpt',
					'__empty__'
				);

				$excerpt_length = get_theme_mod(
					'excerpt_length',
					'__empty__'
				);

				if ($has_excerpt !== '__empty__') {
					$touched = true;
					$woo_card_layout[$index]['enabled'] = $has_excerpt === 'yes';
				}

				if ($excerpt_length !== '__empty__') {
					$touched = true;
					$woo_card_layout[$index]['excerpt_length'] = $excerpt_length;
				}
			}

			if (
				$layer['id'] === 'product_add_to_cart'
				||
				$layer['id'] === 'product_add_to_cart_and_price'
			) {
				$has_product_action_button = get_theme_mod(
					'has_product_action_button',
					'__empty__'
				);

				if ($has_product_action_button !== '__empty__') {
					$touched = true;
					$woo_card_layout[$index]['enabled'] = $has_product_action_button === 'yes';
				}
			}
		}

		if (
			$touched
			&&
			get_theme_mod('woo_card_layout', '__empty__') === '__empty__'
		) {
			set_theme_mod('woo_card_layout', $woo_card_layout);
		}

		blocksy_manager()->db_versioning->migrate_options([
			[
				'old' => 'shop_cards_alignment_1',
				'new' => 'shop_cards_alignment'
			]
		]);
	}

	public function cleanup_color_palette_theme_mod() {
		$colorPalette = get_theme_mod('colorPalette', '__empty__');

		if ($colorPalette === '__empty__') {
			return;
		}

		$did_update = false;

		if (isset($colorPalette['palettes'])) {
			$did_update = true;
			unset($colorPalette['palettes']);
		}

		if (isset($colorPalette['current_palette'])) {
			$did_update = true;
			unset($colorPalette['current_palette']);
		}

		if ($did_update) {
			set_theme_mod('colorPalette', $colorPalette);
		}
	}

	public function migrate_color_palette() {
		$r = new \Blocksy\Database\SearchReplace();

		$result = $r->invoke([
			'old' => 'paletteColor',
			'new' => 'theme-palette-color-'
		]);

		$did_migrate = false;

		if ($result && isset($result['total']) && $result['total'] > 0) {
			$did_migrate = true;

			$r->invoke([
				'old' => 'var(--color)',
				'new' => 'var(--theme-text-color)',
				'dry_run' => false
			]);

			$r->invoke([
				'old' => 'paletteColor',
				'new' => 'theme-palette-color-',
				'dry_run' => false
			]);

			$r->invoke([
				'old' => 'buttonInitialColor',
				'new' => 'theme-button-background-initial-color',
				'dry_run' => false
			]);
		}

		if (function_exists('gspb_GreenShift_plugin_init')) {
			$greenshift_variables = [
				'--linkInitialColor' => '--theme-link-initial-color',
				'--container-width' => '--theme-container-width',
				'--normal-container-max-width' => '--theme-normal-container-max-width',
				'--narrow-container-max-width' => '--theme-narrow-container-max-width',
				'--buttonFontFamily' => '--theme-button-font-family',
				'--fontFamily' => '--theme-font-family',
				'--buttonFontSize' => '--theme-button-font-size',
				'--buttonFontWeight' => '--theme-button-font-weight',
				'--buttonFontStyle' => '--theme-button-font-style',
				'--buttonLineHeight' => '--theme-button-line-height',
				'--buttonLetterSpacing' => '--theme-button-letter-spacing',
				'--buttonTextTransform' => '--theme-button-text-transform',
				'--buttonTextDecoration' => '--theme-button-text-decoration',
				'--buttonTextInitialColor' => '--theme-button-text-initial-color',
				'--button-border' => '--theme-button-border',
				'--buttonInitialColor' => '--theme-button-background-initial-color',
				'--buttonMinHeight' => '--theme-button-min-height',
				'--buttonBorderRadius' => '--theme-button-border-radius',
				'--button-padding' => '--theme-button-padding',
				'--button-border-hover-color' => '--theme-button-border-hover-color',
				'--buttonTextHoverColor' => '--theme-button-text-hover-color',
				'--buttonHoverColor' => '--theme-button-background-hover-color'
			];

			foreach ($greenshift_variables as $old => $new) {
				$result = $r->invoke([
					'old' => $old,
					'new' => $new,
					'tables' => [
						_get_meta_table('post')
					]
				]);

				if ($result && $result['total'] > 0) {
					$r->invoke([
						'old' => $old,
						'new' => $new,
						'tables' => [
							_get_meta_table('post')
						],
						'dry_run' => false
					]);
				}
			}
		}

		return $did_migrate;
	}

	public function migrate_image_sizes() {
		$archive_thumbnail = get_option(
			'woocommerce_archive_thumbnail_image_width',
			'__empty__'
		);

		$thumbnail = get_option(
			'woocommerce_thumbnail_image_width',
			'__empty__'
		);

		if ($archive_thumbnail !== '__empty__' || $thumbnail === '__empty__') {
			return;
		}

		update_option(
			'woocommerce_archive_thumbnail_image_width',
			$thumbnail
		);

		update_option(
			'woocommerce_archive_thumbnail_cropping',
			get_option('woocommerce_thumbnail_cropping', '1:1')
		);

		update_option(
			'woocommerce_archive_thumbnail_cropping_custom_width',
			get_option('woocommerce_thumbnail_cropping_custom_width', '4')
		);

		update_option(
			'woocommerce_archive_thumbnail_cropping_custom_height',
			get_option('woocommerce_thumbnail_cropping_custom_height', '3')
		);
	}
}
