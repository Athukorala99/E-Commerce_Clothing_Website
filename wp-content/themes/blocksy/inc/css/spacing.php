<?php

function blocksy_spacing_value($args = []) {
	$args = wp_parse_args(
		$args,
		[
			// array | 10px
			'top' => ['value' => '', 'unit' => ''],
			// array | 10px
			'right' => ['value' => '', 'unit' => ''],
			// array | 10px
			'bottom' => ['value' => '', 'unit' => ''],
			// array | 10px
			'left' => ['value' => '', 'unit' => ''],

			'custom' => '',
		]
	);

	/*
		$old_format = [
			'top' => '',
			'bottom' => '',
			'left' => '',
			'right' => '',
			'linked' => true
		];
	*/

	// Still allow providing defaults in the old format
	foreach (['top', 'right', 'bottom', 'left'] as $side) {
		if (! is_string($args[$side])) {
			continue;
		}

		$proper_value = intval($args[$side]);

		if ($args[$side] === 'auto') {
			$proper_value = 'auto';
		}

		$args[$side] = [
			'value' => $proper_value,
			'unit' => str_replace($proper_value, '', $args[$side])
		];
	}

	$initial_state = 2;

	$non_auto_values = [];

	if ($args['top']['value'] !== 'auto') {
		$non_auto_values[] = $args['top']['value'] . $args['top']['unit'];
	}

	if ($args['right']['value'] !== 'auto') {
		$non_auto_values[] = $args['right']['value'] . $args['right']['unit'];
	}

	if ($args['bottom']['value'] !== 'auto') {
		$non_auto_values[] = $args['bottom']['value'] . $args['bottom']['unit'];
	}

	if ($args['left']['value'] !== 'auto') {
		$non_auto_values[] = $args['left']['value'] . $args['left']['unit'];
	}

	if (count(array_unique($non_auto_values)) === 1) {
		$initial_state = 1;
	}

	return [
		// 1 - linked
		// 2 - unlinked
		// 3 - custom
		'state' => $initial_state,

		'values' => [
			$args['top'],
			$args['right'],
			$args['bottom'],
			$args['left']
		],

		'custom' => ''
	];
}

function blocksy_output_spacing($args = []) {
	$args = wp_parse_args(
		$args,
		[
			'css' => null,
			'tablet_css' => null,
			'mobile_css' => null,

			'selector' => null,
			'property' => 'margin',

			'important' => false,
			'responsive' => true,

			'empty_value' => 0,

			'value' => null,
		]
	);

	$value = blocksy_expand_responsive_value($args['value']);

	$spacing_value = [
		'desktop' => blocksy_spacing_prepare_for_device(
			$value['desktop'],
			$args
		),
		'tablet' => blocksy_spacing_prepare_for_device(
			$value['tablet'],
			$args
		),
		'mobile' => blocksy_spacing_prepare_for_device(
			$value['mobile'],
			$args
		),
	];

	$args['value'] = $spacing_value;
	$args['variableName'] = $args['property'];

	if ($args['important']) {
		$args['value_suffix'] = ' !important';
	}

	blocksy_output_css_vars($args);
}

function blocksy_spacing_prepare_for_device($value, $args = []) {
	$args = wp_parse_args($args, [
		'empty_value' => 0,

		// string | array
		'format' => 'string'
	]);

	// Keep temporarily for backwards compatibility
	if (! isset($value['values'])) {
		return blocksy_legacy_spacing_prepare_for_device(
			$value,
			$args
		);
	}

	if ($value['state'] === 3) {
		if (empty(trim($value['custom']))) {
			return 'CT_CSS_SKIP_RULE';
		}

		return trim($value['custom']);
	}

	$result = array_map(
		function ($side) use ($args) {
			if ($side['value'] === '' || $side['value'] === 'auto') {
				$side['value'] = $args['empty_value'];
			}

			return $side;
		},
		$value['values']
	);

	$should_skip = true;
	$unit = '';

	foreach ($result as $side) {
		if ($side['value'] !== $args['empty_value']) {
			$should_skip = false;
		}

		if ($side['unit'] !== '') {
			$unit = $side['unit'];
		}
	}

	// Normalize units
	if ($unit) {
		foreach ($result as $index => $side) {
			if ($side['unit'] === '') {
				$result[$index]['unit'] = $unit;
			}
		}
	}

	if ($should_skip) {
		if ($args['format'] === 'array') {
			return ['CT_CSS_SKIP_RULE'];
		}

		return 'CT_CSS_SKIP_RULE';
	}

	$result = array_map(
		function ($side) {
			return $side['value'] . $side['unit'];
		},
		$result
	);

	if (
		$result[0] === $result[1]
		&&
		$result[0] === $result[2]
		&&
		$result[0] === $result[3]
	) {
		if ($args['format'] === 'array') {
			return [$result[0]];
		}

		return $result[0];
	}

	if (
		$result[0] === $result[2]
		&&
		$result[1] === $result[3]
	) {
		if ($args['format'] === 'array') {
			return [$result[0], $result[3]];
		}

		return $result[0] . ' ' . $result[3];
	}

	if ($args['format'] === 'array') {
		return $result;
	}

	return implode(' ', $result);
}

function blocksy_legacy_spacing_prepare_for_device($value, $args = []) {
	$args = wp_parse_args($args, [
		'empty_value' => 0,

		// string | array
		'format' => 'string'
	]);

	$result = [];

	$is_value_compact = true;

	foreach ([
		$value['top'],
		$value['right'],
		$value['bottom'],
		$value['left']
	] as $val) {
	if (
		$val !== 'auto'
		&&
		trim($val) !== ''
	) {
		$is_value_compact = false;
		break;
	}
	}

	if ($is_value_compact) {
		return 'CT_CSS_SKIP_RULE';
	}

	if ($args['empty_value'] !== 0) {
		$unit = '';

		foreach ($value as $side => $side_value) {
			if (
				$side_value
				&&
				$side_value !== floatval($side_value)
			) {
				$unit = str_replace(
					floatval($side_value),
					'',
					$side_value
				);
			}
		}

		$args['empty_value'] .= $unit;
	}

	if (
		$value['top'] === 'auto'
		||
		$value['top'] === ''
		||
		strval($value['top']) === '0'
	) {
		$result[] = $args['empty_value'];
	} else {
		$result[] = $value['top'];
	}

	if (
		$value['right'] === 'auto'
		||
		$value['right'] === ''
		||
		strval($value['right']) === '0'
	) {
		$result[] = $args['empty_value'];
	} else {
		$result[] = $value['right'];
	}

	if (
		$value['bottom'] === 'auto'
		||
		$value['bottom'] === ''
		||
		strval($value['bottom']) === '0'
	) {
		$result[] = $args['empty_value'];
	} else {
		$result[] = $value['bottom'];
	}

	if (
		$value['left'] === 'auto'
		||
		$value['left'] === ''
		||
		strval($value['left']) === '0'
	) {
		$result[] = $args['empty_value'];
	} else {
		$result[] = $value['left'];
	}

	if (
		$result[0] === $result[1]
		&&
		$result[0] === $result[2]
		&&
		$result[0] === $result[3]
	) {
		if ($args['format'] === 'array') {
			return [ $result[0] ];
		}

		return $result[0];
	}

	if (
		$result[0] === $result[2]
		&&
		$result[1] === $result[3]
	) {
		if ($args['format'] === 'array') {
			return [$result[0], $result[3]];
		}

		return $result[0] . ' ' . $result[3];
	}

	if ($args['format'] === 'array') {
		return $result;
	}

	return implode(' ', $result);
}
