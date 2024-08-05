<?php
/**
 * Helpers for generating CSS output.
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

function blocksy_output_responsive($args = []) {
	$args = wp_parse_args(
		$args,
		[
			'css' => null,
			'tablet_css' => null,
			'mobile_css' => null,
			'selector' => null,

			'desktop_selector_prefix' => '',
			'tablet_selector_prefix' => '',
			'mobile_selector_prefix' => '',

			'variableName' => null,
			'unit' => 'px',
			'previousUnit' => '',
			'value' => null,
		]
	);

	if (empty($args['unit']) && ! empty($args['previousUnit'])) {
		$value = blocksy_expand_responsive_value($args['value']);

		$all_is_plain = true;

		if (! is_numeric($value['desktop'])) {
			$all_is_plain = false;
		}

		if (! is_numeric($value['tablet'])) {
			$all_is_plain = false;
		}

		if (! is_numeric($value['mobile'])) {
			$all_is_plain = false;
		}

		if ($all_is_plain) {
			$args['unit'] = $args['previousUnit'];
		}
	}

	$args['value_suffix'] = $args['unit'];
	$args['responsive'] = true;

	blocksy_output_css_vars($args);
}

function blocksy_units_config($overrides = []) {
	$units = [
		[
			'unit' => 'px',
			'min' => 0,
			'max' => 40,
		],
		[
			'unit' => 'em',
			'min' => 0,
			'max' => 30,
		],
		[
			'unit' => '%',
			'min' => 0,
			'max' => 100,
		],
		[
			'unit' => 'vw',
			'min' => 0,
			'max' => 100,
		],
		[
			'unit' => 'vh',
			'min' => 0,
			'max' => 100,
		],
		[
			'unit' => 'pt',
			'min' => 0,
			'max' => 100,
		],
		[
			'unit' => 'rem',
			'min' => 0,
			'max' => 30,
		],
	];

	foreach ($overrides as $single_override) {
		$found = false;

		foreach ($units as $key => $single_unit) {
			if ($single_override['unit'] === $single_unit['unit']) {
				$units[$key] = $single_override;
				$found = true;
			}
		}

		if (! $found) {
			$units[] = $single_override;
		}
	}

	return $units;
}

function blocksy_output_border($args = []) {
	$args = wp_parse_args(
		$args,
		[
			'css' => null,
			'tablet_css' => null,
			'mobile_css' => null,

			'selector' => null,

			'desktop_selector_prefix' => '',
			'tablet_selector_prefix' => '',
			'mobile_selector_prefix' => '',

			'value' => null,
			'default' => null,

			'variableName' => null,
			'secondColorVariableName' => null,

			'important' => false,
			'responsive' => false,
			'skip_none' => false
		]
	);

	if (! $args['default']) {
		return;
	}

	if ($args['responsive']) {
		blocksy_assert_args($args, ['tablet_css', 'mobile_css']);
	}

	$value = blocksy_expand_responsive_value($args['value']);
	$default_value = blocksy_expand_responsive_value($args['default']);

	if (
		is_array($value['desktop'])
		&&
		! isset($value['desktop']['style'])
	) {
		$value['desktop']['style'] = 'none';
	}

	if (! $value['desktop']) {
		$value['desktop'] = $default_value['desktop'];
	}

	if (
		isset($default_value['desktop']['secondColor'])
		&&
		! isset($value['desktop']['secondColor'])
	) {
		$value['desktop']['secondColor'] = $default_value['desktop']['secondColor'];
	}

	if (
		is_array($value['tablet'])
		&&
		! isset($value['tablet']['style'])
	) {
		$value['tablet']['style'] = 'none';
	}

	if (! $value['tablet']) {
		$value['tablet'] = $default_value['tablet'];
	}

	if (
		isset($default_value['tablet']['secondColor'])
		&&
		! isset($value['tablet']['secondColor'])
	) {
		$value['tablet']['secondColor'] = $default_value['tablet']['secondColor'];
	}

	if (
		is_array($value['mobile'])
		&&
		! isset($value['mobile']['style'])
	) {
		$value['mobile']['style'] = 'none';
	}

	if (! $value['mobile']) {
		$value['mobile'] = $default_value['mobile'];
	}

	if (
		isset($default_value['mobile']['secondColor'])
		&&
		! isset($value['mobile']['secondColor'])
	) {
		$value['mobile']['secondColor'] = $default_value['mobile']['secondColor'];
	}

	$border_values = [
		'desktop' => '',
		'tablet' => '',
		'mobile' => '',
	];

	if ($value['desktop']['style'] === 'none') {
		$border_values['desktop'] = 'none';

		if (isset($default_value['desktop']['secondColor'])) {
			$value['desktop']['secondColor']['color'] = 'CT_CSS_SKIP_RULE';
		}
	} else {
		$color = blocksy_get_colors([
			'default' => $value['desktop']['color']
		], [
			'default' => $value['desktop']['color']
		]);

		$border_values['desktop'] = $value['desktop']['width'] . 'px ' .
			$value['desktop']['style'] . ' ' . $color['default'];
	}

	if (
		isset($value['desktop']['inherit'])
		&&
		$value['desktop']['inherit']
	) {
		$border_values['desktop'] = 'CT_CSS_SKIP_RULE';
	}

	if ($value['tablet']['style'] === 'none') {
		$border_values['tablet'] = 'none';
		if (isset($default_value['tablet']['secondColor'])) {
			$value['tablet']['secondColor']['color'] = 'CT_CSS_SKIP_RULE';
		}
	} else {
		$color = blocksy_get_colors([
			'default' => $value['tablet']['color']
		], [
			'default' => $value['tablet']['color']
		]);

		$border_values['tablet'] = $value['tablet']['width'] . 'px ' .
			$value['tablet']['style'] . ' ' . $color['default'];
	}

	if (
		isset($value['tablet']['inherit'])
		&&
		$value['tablet']['inherit']
	) {
		$border_values['tablet'] = 'CT_CSS_SKIP_RULE';
	}

	if ($value['mobile']['style'] === 'none') {
		$border_values['mobile'] = 'none';
		if (isset($default_value['mobile']['secondColor'])) {
			$value['mobile']['secondColor']['color'] = 'CT_CSS_SKIP_RULE';
		}
	} else {
		$color = blocksy_get_colors([
			'default' => $value['mobile']['color']
		], [
			'default' => $value['mobile']['color']
		]);

		$border_values['mobile'] = $value['mobile']['width'] . 'px ' .
			$value['mobile']['style'] . ' ' . $color['default'];
	}

	if (
		isset($value['mobile']['inherit'])
		&&
		$value['mobile']['inherit']
	) {
		$border_values['mobile'] = 'CT_CSS_SKIP_RULE';
	}

	$args['value'] = $border_values;

	if ($args['skip_none']) {
		if (
			$border_values['desktop'] === 'none'
			&&
			$border_values['tablet'] === 'none'
			&&
			$border_values['mobile'] === 'none'
		) {
			$args['value'] = [
				'desktop' => 'CT_CSS_SKIP_RULE',
				'tablet' => 'CT_CSS_SKIP_RULE',
				'mobile' => 'CT_CSS_SKIP_RULE'
			];
		}
	}

	if ($args['important']) {
		$args['value_suffix'] = ' !important';
	}

	if (
		isset($value['desktop']['secondColor'])
		&&
		$value['desktop']['secondColor']
	) {
		$secondColorValue = [
			'desktop' => [
				'default' => $value['desktop']['secondColor']
			],
			'tablet' => [
				'default' => $value['tablet']['secondColor']
			],
			'mobile' => [
				'default' => $value['mobile']['secondColor']
			]
		];

		blocksy_output_colors([
			'value' => $secondColorValue,
			'default' => $secondColorValue,

			'css' => $args['css'],
			'tablet_css' => $args['tablet_css'],
			'mobile_css' => $args['mobile_css'],

			'responsive' => $args['responsive'],
			'variables' => [
				'default' => [
					'selector' => $args['selector'],
					'variable' => $args['secondColorVariableName']
				],
			],
		]);
	}

	blocksy_output_css_vars($args);
}

function blocksy_maybe_append_important($value, $has_important = false) {
	if (! $has_important) {
		return $value;
	}

	if (strpos($value, 'CT_CSS_SKIP_RULE') !== false) {
		return $value;
	}

	return $value . ' !important';
}

