<?php

$search_through = blocksy_akg('search_through', $atts, [
	'post' => true,
	'page' => true,
	'product' => true
]);

$all_cpts = blocksy_manager()->post_types->get_supported_post_types();

if (function_exists('is_bbpress')) {
	$all_cpts[] = 'forum';
	$all_cpts[] = 'topic';
	$all_cpts[] = 'reply';
}

foreach ($all_cpts as $single_cpt) {
	if (! isset($search_through[$single_cpt])) {
		$search_through[$single_cpt] = true;
	}
}

$post_type = [];

foreach ($search_through as $single_post_type => $enabled) {
	if (
		! $enabled
		||
		! get_post_type_object($single_post_type)
	) {
		continue;
	}

	if (
		$single_post_type !== 'post'
		&&
		$single_post_type !== 'page'
		&&
		$single_post_type !== 'product'
		&&
		! in_array($single_post_type, $all_cpts)
	) {
		continue;
	}

	$post_type[] = $single_post_type;
}

if (count(array_keys($search_through)) === count($post_type)) {
	$post_type = [];
}

$class = 'ct-search-box';

$icon = '<svg class="ct-icon ct-search-button-content" aria-hidden="true" width="15" height="15" viewBox="0 0 15 15"><path d="M14.8,13.7L12,11c0.9-1.2,1.5-2.6,1.5-4.2c0-3.7-3-6.8-6.8-6.8S0,3,0,6.8s3,6.8,6.8,6.8c1.6,0,3.1-0.6,4.2-1.5l2.8,2.8c0.1,0.1,0.3,0.2,0.5,0.2s0.4-0.1,0.5-0.2C15.1,14.5,15.1,14,14.8,13.7z M1.5,6.8c0-2.9,2.4-5.2,5.2-5.2S12,3.9,12,6.8S9.6,12,6.8,12S1.5,9.6,1.5,6.8z"/></svg>';

if (function_exists('blc_get_icon') && isset($atts['icon'])) {
	$icon = blc_get_icon([
		'icon_descriptor' => blocksy_akg('icon', $atts, [
			'icon' => 'blc blc-search'
		]),
		'icon_container' => true,
		'icon_html_atts' => [
			'class' => 'ct-icon ct-search-button-content',
		]
	]);
}

$taxonomy_filter_visibility = blocksy_visibility_classes(
	blocksy_akg(
		'taxonomy_filter_visibility',
		$atts,
		[
			'desktop' => true,
			'tablet' => true,
			'mobile' => false,
		]
	)
);

$colors = [
	'--theme-form-text-initial-color' => blocksy_default_akg('customInputFontColor', $atts, ''),
	'--theme-form-text-focus-color' => blocksy_default_akg('customInputFontFocusColor', $atts, ''),
	'--theme-form-field-border-initial-color' => blocksy_default_akg('customInputBorderColor', $atts, ''),
	'--theme-form-field-border-focus-color' => blocksy_default_akg('customInputBorderColorFocus', $atts, ''),
	'--theme-form-field-background-initial-color' => blocksy_default_akg('customInputBackgroundColor', $atts, ''),
	'--theme-form-field-background-focus-color' => blocksy_default_akg('customInputBackgroundColorFocus', $atts, ''),
];

$buttonUseText = blocksy_akg('buttonUseText', $atts, 'no') === 'yes';
$buttonPosition = blocksy_akg('buttonPosition', $atts, 'inside');
$search_box_button_text = blocksy_default_akg('search_box_button_text', $atts, __('Search', 'blocksy'));
$has_live_results = blocksy_akg('enable_live_results', $atts, 'no');

if (isset($atts['inputFontColor'])) {
	$var = $atts['inputFontColor'];
	$colors['--theme-form-text-initial-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['inputFontColorFocus'])) {
	$var = $atts['inputFontColorFocus'];
	$colors['--theme-form-text-focus-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['inputBorderColor'])) {
	$var = $atts['inputBorderColor'];
	$colors['--theme-form-field-border-initial-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['inputBorderColorFocus'])) {
	$var = $atts['inputBorderColorFocus'];
	$colors['--theme-form-field-border-focus-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['inputBackgroundColor'])) {
	$var = $atts['inputBackgroundColor'];
	$colors['--theme-form-field-background-initial-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['inputBackgroundColorFocus'])) {
	$var = $atts['inputBackgroundColorFocus'];
	$colors['--theme-form-field-background-focus-color'] = "var(--wp--preset--color--$var)";
}

if ($has_live_results === 'yes') {
	$colors = array_merge(
		$colors,
		[
			'--theme-link-initial-color' => blocksy_default_akg('customDropdownTextInitialColor', $atts, ''),
			'--theme-link-hover-color' => blocksy_default_akg('customDropdownTextHoverColor', $atts, ''),
			'--search-dropdown-background' => blocksy_default_akg('customDropdownBackgroundColor', $atts, ''),
			'--search-dropdown-box-shadow-color' => blocksy_default_akg('customShadowColor', $atts, ''),
		]
	);

	if (isset($atts['dropdownTextInitialColor'])) {
		$var = $atts['dropdownTextInitialColor'];
		$colors['--theme-link-initial-color'] = "var(--wp--preset--color--$var)";
	}

	if (isset($atts['dropdownTextHoverColor'])) {
		$var = $atts['dropdownTextHoverColor'];
		$colors['--theme-link-hover-color'] = "var(--wp--preset--color--$var)";
	}

	if (isset($atts['dropdownBackgroundColor'])) {
		$var = $atts['dropdownBackgroundColor'];
		$colors['--search-dropdown-background'] = "var(--wp--preset--color--$var)";
	}

	if (isset($atts['shadowColor'])) {
		$var = $atts['shadowColor'];
		$colors['--search-dropdown-box-shadow-color'] = "var(--wp--preset--color--$var)";
	}
}

$colors_css = '';

foreach ($colors as $key => $value) {
	if (empty($value)) {
		continue;
	}
	$colors_css .= $key . ':' . $value . ';';
}

$style = '';

$search_box_height = blocksy_default_akg('searchBoxHeight', $atts, '');

if (! empty($search_box_height)) {
	$style .= '--theme-form-field-height:' . $search_box_height . 'px;';
}

if (isset($atts['style']['border']['radius'])) {
	if (
		gettype($atts['style']['border']['radius']) === 'string'
		&&
		! empty(gettype($atts['style']['border']['radius']))
	) {
		$style .= '--theme-form-field-border-radius:' . $atts['style']['border']['radius'] . ';';
	} else if (
		gettype($atts['style']['border']['radius']) === 'array'
		&&
		! empty($atts['style']['border']['radius'])
	) {
		$style .= '--theme-form-field-border-radius:' . $atts['style']['border']['radius']['topLeft'] . $atts['style']['border']['radius']['topRight'] . $atts['style']['border']['radius']['bottomLeft'] . $atts['style']['border']['radius']['bottomRight'] . ';';
	}

	unset($atts['style']['border']);
}

$wp_styles = wp_style_engine_get_styles(
	blocksy_default_akg('style', $atts, [])
);

$wp_styles_css = isset($wp_styles['css']) ? $wp_styles['css'] : '';

$button_colors = [];

$button_colors = array_merge(
	$button_colors,
	[
		'--theme-button-text-initial-color' => blocksy_default_akg('customInputIconColor', $atts, ''),
		'--theme-button-text-hover-color' => blocksy_default_akg('customInputIconColorFocus', $atts, ''),
	]
);

if (isset($atts['inputIconColor'])) {
	$var = $atts['inputIconColor'];
	$button_colors['--theme-button-text-initial-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['inputIconColorFocus'])) {
	$var = $atts['inputIconColorFocus'];
	$button_colors['--theme-button-text-hover-color'] = "var(--wp--preset--color--$var)";
}

if ($buttonPosition === 'outside') {
	$button_colors = array_merge(
		$button_colors,
		[
			'--theme-button-background-initial-color' => blocksy_default_akg('customButtonBackgroundColor', $atts, ''),
			'--theme-button-background-hover-color' => blocksy_default_akg('customButtonBackgroundColorHover', $atts, ''),
		]
	);

	if (isset($atts['buttonBackgroundColor'])) {
		$var = $atts['buttonBackgroundColor'];
		$button_colors['--theme-button-background-initial-color'] = "var(--wp--preset--color--$var)";
	}

	if (isset($atts['buttonBackgroundColorHover'])) {
		$var = $atts['buttonBackgroundColorHover'];
		$button_colors['--theme-button-background-hover-color'] = "var(--wp--preset--color--$var)";
	}
}

$button_colors_css = '';

foreach ($button_colors as $key => $value) {
	if (empty($value)) {
		continue;
	}
	$button_colors_css .= $key . ':' . $value . ';';
}

?>

<div class="<?php echo esc_attr($class) ?>">
	<?php
		if (function_exists('blocksy_isolated_get_search_form')) {
			blocksy_isolated_get_search_form([
				'ct_post_type' => $post_type,
				'search_live_results' => blocksy_akg('enable_live_results', $atts, 'no'),
				'live_results_attr' => blocksy_akg(
					'live_results_images',
					$atts,
					'yes'
				) === 'yes' ? 'thumbs' : '',
				'ct_product_price' => blocksy_akg(
					'searchProductPrice',
					$atts,
					'no'
				) === 'yes',
				'ct_product_status' => blocksy_akg(
					'searchProductStatus',
					$atts,
					'no'
				) === 'yes',
				'search_placeholder' => blocksy_default_akg(
					'search_box_placeholder',
					$atts,
					__('Search', 'blocksy')
				),
				'has_taxonomy_filter' => blocksy_akg('has_taxonomy_filter', $atts, 'no') === 'yes',
				'has_taxonomy_children' => blocksy_akg('has_taxonomy_children', $atts, 'no') === 'yes',
				'taxonomy_filter_label' => blocksy_akg('taxonomy_filter_label', $atts, __('Select Category', 'blocksy')),
				'taxonomy_filter_visibility' => blocksy_akg('taxonomy_filter_visibility', $atts, [
					'desktop' => true,
					'tablet' => true,
					'mobile' => false,
				]),
				'icon' => $buttonUseText ? blocksy_html_tag(
					'span',
					[
						'class' => 'ct-search-button-content'
					],
					$search_box_button_text): $icon ,
				'html_atts' => [
					'data-form-controls' => $buttonPosition,
					'data-taxonomy-filter' => blocksy_akg('has_taxonomy_filter', $atts, 'no') === 'yes' ? 'true' : 'false',
					'data-submit-button' => $buttonUseText ? 'text' : 'icon',
					'style' => $style . $colors_css . $wp_styles_css
				],
				'button_html_atts' => !empty($button_colors_css) ? [
					'style' => $button_colors_css
				] : []
			]);
		}
	?>
</div>
