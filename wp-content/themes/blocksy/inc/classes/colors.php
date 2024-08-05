<?php

namespace Blocksy;

class Colors {
	public function __construct() {
		add_action('wp_ajax_blocksy_get_custom_palettes', function () {
			if (! current_user_can('manage_options')) {
				wp_send_json_error();
			}

			$option = get_option('blocksy_custom_palettes', []);

			if (empty($option)) {
				$option = [];
			}

			if (! isset($option['palettes'])) {
				$option['palettes'] = [];
			}

			wp_send_json_success([
				'palettes' => $option['palettes']
			]);
		});

		add_action('wp_ajax_blocksy_sync_custom_palettes', function () {
			if (! current_user_can('manage_options')) {
				wp_send_json_error();
			}

			$body = json_decode(file_get_contents('php://input'), true);

			if (! isset($body['palettes'])) {
				wp_send_json_error();
			}

			update_option('blocksy_custom_palettes', [
				'palettes' => $body['palettes']
			]);

			wp_send_json_success($body);
		});
	}

	public function get_color_palette($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'id' => 'colorPalette',
				'default' => [
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
				]
			]
		);

		$colorPalette = blocksy_get_theme_mod($args['id'], $args['default']);

		$result = [];

		foreach ($colorPalette as $key => $value) {
			if (strpos($key, 'color') === false) {
				continue;
			}

			$variableName = str_replace('color', 'theme-palette-color-', $key);

			if (isset($value['variable']) && ! empty($value['variable'])) {
				$variableName = $value['variable'];
			}

			if (! $value) {
				$value = $args['default'][$key];
			}

			$result[$key] = [
				'id' => $key,
				'slug' => 'palette-color-' . str_replace('color', '', $key),
				'color' => $value['color'],
				'variable' => $variableName,
				'title' => sprintf(
					__('Palette Color %s', 'blocksy'),
					str_replace('color', '', $key)
				)
			];
		}

		return $result;
	}
}
