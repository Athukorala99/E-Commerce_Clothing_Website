<?php

namespace Blocksy\DbVersioning;

class V202 {
	public function migrate() {
		$this->migrate_color_palette();
		$this->migrate_offcanvas_filters();
	}

	public function migrate_offcanvas_filters() {
		blocksy_manager()->db_versioning->migrate_options([
			[
				'old' => 'woocommerce_filter_type',
				'new' => 'woocommerce_filter_icon_type'
			]
		]);

		set_theme_mod('woocommerce_filter_type', 'type-1');
	}

	public function migrate_color_palette() {
		$colorPalette = get_theme_mod('colorPalette', '__empty__');

		if ($colorPalette === '__empty__') {
			return;
		}

		$value = [
			'color1' => [
				'color' => '#2872fa',
			],

			'color2' => [
				'color' => '#1559ed',
			],

			'color3' => [
				'color' => '#3A4F66',
			],

			'color4' => [
				'color' => '#192a3d',
			],

			'color5' => [
				'color' => '#e1e8ed',
			],

			'color6' => [
				'color' => '#f2f5f7',
			],

			'color7' => [
				'color' => '#FAFBFC',
			],

			'color8' => [
				'color' => '#ffffff',
			]
		];

		$did_update = false;

		foreach ($value as $key => $item) {
			if (! isset($colorPalette[$key]) || ! $colorPalette[$key]) {
				$did_update = true;
				$colorPalette[$key] = $item;
			}
		}

		if ($did_update) {
			set_theme_mod('colorPalette', $colorPalette);
		}
	}
}
