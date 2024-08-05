<?php

if (! function_exists('blocksy_assemble_selector')) {
	return;
}

$render = new Blocksy_Header_Builder_Render([
	'current_section_id' => $section_id
]);
$header_height = $render->get_header_height();

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_assemble_selector($root_selector),
	'variableName' => 'header-height',
	'value' => $header_height
]);

if (isset($has_sticky_header) && $has_sticky_header) {
	$scroll_margin_top_offset = $header_height;

	$header_sticky_height = $render->get_header_height($has_sticky_header);

	if (! in_array('desktop', $has_sticky_header['devices'])) {
		$header_sticky_height['desktop'] = 0;
	}

	if (! in_array('mobile', $has_sticky_header['devices'])) {
		$header_sticky_height['tablet'] = 0;
		$header_sticky_height['mobile'] = 0;
	}

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector($root_selector),
		'variableName' => 'header-sticky-height',
		'value' => $header_sticky_height
	]);

	$current_section = $render->get_current_section();

	if (! isset($current_section['settings'])) {
		$current_section['settings'] = [];
	}

	$atts = $current_section['settings'];

	$sticky_offset = blocksy_akg('sticky_offset', $atts, '0');

	if ($sticky_offset !== '0') {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => blocksy_assemble_selector($root_selector),
			'variableName' => 'header-sticky-offset',
			'value' => $sticky_offset
		]);
	}
}

// background - initial state
blocksy_output_background_css([
	'selector' => blocksy_assemble_selector(
		blocksy_mutate_selector([
			'selector' => $root_selector,
			'operation' => 'suffix',
			'to_add' => '.ct-header'
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'value' => blocksy_akg(
		'headerBackground',
		$atts,
		blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')
				],
			],
		])
	),
	'responsive' => true,
	'forced_background_image' => true
]);


// background - transparent state
if (isset($has_transparent_header) && $has_transparent_header) {

	blocksy_output_background_css([
		'selector' => blocksy_assemble_selector(
			blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '[data-transparent]'
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'value' => blocksy_akg(
			'transparentHeaderBackground',
			$atts,
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')
					],
				],
			])
		),
		'responsive' => true,
		'forced_background_image' => true
	]);
}

// background - sticky state
if (isset($has_sticky_header) && $has_sticky_header) {

	blocksy_output_background_css([
		'selector' => blocksy_assemble_selector(
			blocksy_mutate_selector([
				'selector' => $root_selector,
				'to_add' => '[data-sticky*="yes"]'
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'value' => blocksy_akg(
			'stickyHeaderBackground',
			$atts,
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')
					],
				],
			])
		),
		'responsive' => true,
		'forced_background_image' => true
	]);
}