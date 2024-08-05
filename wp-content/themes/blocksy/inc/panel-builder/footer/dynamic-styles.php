<?php

if (! function_exists('blocksy_assemble_selector')) {
	return;
}

// Box shadow
$has_reveal_effect = blocksy_akg('has_reveal_effect', $atts,  [
	'desktop' => false,
	'tablet' => false,
	'mobile' => false,
]);

if (function_exists('blocksy_output_responsive_switch')) {
	blocksy_output_responsive_switch([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector(
			blocksy_mutate_selector([
				'selector' => blocksy_mutate_selector([
					'selector' => $root_selector,
					'operation' => 'suffix',
					'to_add' => '.ct-footer'
				]),
				'operation' => 'container-suffix',
				'to_add' => '[data-footer*="reveal"]'
			])
		),
		'variable' => 'position',
		'on' => 'sticky',
		'off' => 'static',
		'value' => $has_reveal_effect,
		'skip_when' => 'all_disabled'
	]);
}

if (
	(
		function_exists('blocksy_some_device')
		&&
		blocksy_some_device($has_reveal_effect)
	)
	||
	is_customize_preview()
) {
	blocksy_output_box_shadow([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector(
			blocksy_mutate_selector([
				'selector' => blocksy_mutate_selector([
					'selector' => $root_selector,
					'operation' => 'suffix',
					'to_add' => '.site-main'
				]),
				'operation' => 'container-suffix',
				'to_add' => '[data-footer*="reveal"]'
			])
		),
		'value' => blocksy_akg('footerShadow', $atts, blocksy_box_shadow_value([
			'enable' => true,
			'h_offset' => 0,
			'v_offset' => 30,
			'blur' => 50,
			'spread' => 0,
			'inset' => false,
			'color' => [
				'color' => 'rgba(0, 0, 0, 0.1)',
			],
		])),
		'variableName' => 'footer-box-shadow',
		'responsive' => $has_reveal_effect,
		'should_skip_output' => false
	]);
}

blocksy_output_background_css([
	'selector' => blocksy_assemble_selector(
		blocksy_mutate_selector([
			'selector' => $root_selector,
			'operation' => 'suffix',
			'to_add' => '.ct-footer'
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'value' => blocksy_akg(
		'footerBackground',
		$atts,
		blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => 'var(--theme-palette-color-6)'
				],
			],
		])
	),
	'responsive' => true,
]);

$footer_container_structure = blocksy_akg('footer_container_structure', $atts, 'fixed');

if ($footer_container_structure !== 'boxed' || is_customize_preview()) {
	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector(
			blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '.ct-footer'
			])
		),
		'property' => 'footer-container-padding',
		'value' => blocksy_akg('footer_spacing', $atts, blocksy_spacing_value())
	]);
}

if ($footer_container_structure === 'boxed' || is_customize_preview()) {

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector(
			blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => 'footer.ct-container'
			])
		),
		'variableName' => 'footer-container-bottom-offset',
		'value' => blocksy_akg('footer_boxed_offset', $atts, 50)
	]);
	
	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector(
			blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => 'footer.ct-container'
			])
		),
		'property' => 'footer-container-padding',
		'value' => blocksy_akg('footer_boxed_spacing', $atts,
			[
				'desktop' => blocksy_spacing_value([
					'top' => '0px',
					'left' => '35px',
					'right' => '35px',
					'bottom' => '0px',
				]),
				'tablet' => blocksy_spacing_value([
					'top' => '0vw',
					'left' => '4vw',
					'right' => '4vw',
					'bottom' => '0vw',
				]),
				'mobile'=> blocksy_spacing_value([
					'top' => '0vw',
					'left' => '5vw',
					'right' => '5vw',
					'bottom' => '0vw',
				]),
			]
		)
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector(
			blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => 'footer.ct-container'
			])
		),
		'property' => 'footer-container-border-radius',
		'value' => blocksy_akg('footer_container_border_radius', $atts, blocksy_spacing_value())
	]);
}
