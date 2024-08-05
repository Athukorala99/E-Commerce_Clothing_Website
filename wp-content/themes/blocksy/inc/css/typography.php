<?php

if (! function_exists('blocksy_output_font_css')) {
	function blocksy_output_font_css($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'css' => null,
				'tablet_css' => null,
				'mobile_css' => null,
				'font_value' => null,
				'selector' => ':root',
				'prefix' => ''
			]
		);

		if (! $args['css']) {
			throw new Error('css missing in args!');
		}

		if (! $args['tablet_css']) {
			throw new Error('tablet_css missing in args!');
		}

		if (! $args['mobile_css']) {
			throw new Error('mobile_css missing in args!');
		}

		$args['css']->process_matching_typography($args['font_value']);

		if ($args['font_value']['family'] === 'System Default') {
			$args['font_value']['family'] = "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'";
		} else {
			$fonts_manager = new \Blocksy\FontsManager();

			if (! in_array(
				$args['font_value']['family'],
				$fonts_manager->get_system_fonts(true)
			) && $args['font_value']['family'] !== 'Default') {
				if (
					$args['font_value']['family'] !== 'Default'
					&&
					strpos($args['font_value']['family'], ' ') !== false
				) {
					$args['font_value']['family'] = "'" . $args['font_value']['family'] . "'";
				}

				$font_family_fallback = blocksy_get_theme_mod(
					'font_family_fallback',
					'Sans-Serif'
				);

				if (! empty($font_family_fallback)) {
					$args['font_value']['family'] .= ", " . $font_family_fallback;
				}
			}
		}

		$args['font_value']['family'] = apply_filters(
			'blocksy:css:typography:output:family',
			$args['font_value']['family'],
			$args['font_value']
		);

		if ($args['font_value']['family'] === 'Default') {
			$args['font_value']['family'] = 'CT_CSS_SKIP_RULE';
		}

		$correct_font_family = str_replace(
			'ct_typekit_',
			'',
			$args['font_value']['family']
		);

		$args['css']->put(
			$args['selector'],
			"--" . blocksy_prefix_theme_variable('theme-font-family', $args['prefix']) . ": {$correct_font_family}"
		);

		$weight_and_style = blocksy_get_css_for_variation(
			$args['font_value']['variation']
		);

		$args['css']->put(
			$args['selector'],
			"--" . blocksy_prefix_theme_variable(
				'theme-font-weight',
				$args['prefix']
			) . ": {$weight_and_style['weight']}"
		);

		if ($weight_and_style['style'] !== 'normal') {
			$args['css']->put(
				$args['selector'],
				"--" . blocksy_prefix_theme_variable(
					'theme-font-style',
					$args['prefix']
				) . ": {$weight_and_style['style']}"
			);
		}

		$args['css']->put(
			$args['selector'],
			"--" . blocksy_prefix_theme_variable(
				'theme-text-transform',
				$args['prefix']
			) . ": {$args['font_value']['text-transform']}"
		);

		$args['css']->put(
			$args['selector'],
			"--" . blocksy_prefix_theme_variable(
				'theme-text-decoration',
				$args['prefix']
			) . ": {$args['font_value']['text-decoration']}"
		);

		blocksy_output_responsive([
			'css' => $args['css'],
			'tablet_css' => $args['tablet_css'],
			'mobile_css' => $args['mobile_css'],
			'selector' => $args['selector'],
			'variableName' => blocksy_prefix_theme_variable(
				'theme-font-size',
				$args['prefix']
			),
			'unit' => '',
			'value' => $args['font_value']['size']
		]);

		blocksy_output_responsive([
			'css' => $args['css'],
			'tablet_css' => $args['tablet_css'],
			'mobile_css' => $args['mobile_css'],
			'selector' => $args['selector'],
			'variableName' => blocksy_prefix_theme_variable(
				'theme-line-height',
				$args['prefix']
			),
			'unit' => '',
			'value' => $args['font_value']['line-height']
		]);

		blocksy_output_responsive([
			'css' => $args['css'],
			'tablet_css' => $args['tablet_css'],
			'mobile_css' => $args['mobile_css'],
			'selector' => $args['selector'],
			'variableName' => blocksy_prefix_theme_variable(
				'theme-letter-spacing',
				$args['prefix']
			),
			'unit' => '',
			'value' => $args['font_value']['letter-spacing']
		]);
	}
}

if (! function_exists('blocksy_get_css_for_variation')) {
	function blocksy_get_css_for_variation($variation, $should_output_normals = true) {
		$weight_and_style = [
			'style' => '',
			'weight' => '',
		];

		if ($variation === 'Default') {
			return [
				'style' => 'CT_CSS_SKIP_RULE',
				'weight' => 'CT_CSS_SKIP_RULE'
			];
		}

		if (preg_match(
			"#(n|i)(\d+?)$#",
			$variation,
			$matches
		)) {
			if ('i' === $matches[1]) {
				$weight_and_style['style'] = 'italic';
			} else {
				$weight_and_style['style'] = 'normal';
			}

			$weight_and_style['weight'] = (int) $matches[2] . '00';
		}

		return $weight_and_style;
	}
}

if (! function_exists('blocksy_typography_default_values')) {
	function blocksy_typography_default_values($values = []) {
		return array_merge([
			'family' => 'Default',
			'variation' => 'Default',

			'size' => '17px',
			'line-height' => '1.65',
			'letter-spacing' => '0em',
			'text-transform' => 'none',
			'text-decoration' => 'none',

			'size' => 'CT_CSS_SKIP_RULE',
			'line-height' => 'CT_CSS_SKIP_RULE',
			'letter-spacing' => 'CT_CSS_SKIP_RULE',
			'text-transform' => 'CT_CSS_SKIP_RULE',
			'text-decoration' => 'CT_CSS_SKIP_RULE',
		], $values);
	}
}

add_action('wp_ajax_blocksy_get_fonts_list', function () {
	if (! current_user_can('edit_theme_options')) {
		wp_send_json_error();
	}

	$m = new \Blocksy\FontsManager();

	wp_send_json_success([
		'fonts' => $m->get_all_fonts()
	]);
});

