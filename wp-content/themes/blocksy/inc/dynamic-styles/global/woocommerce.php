<?php

if (! function_exists('is_woocommerce')) {
	return;
}

blocksy_theme_get_dynamic_styles([
	'name' => 'global/woocommerce/general',
	'css' => $css,
	'mobile_css' => $mobile_css,
	'tablet_css' => $tablet_css,
	'context' => $context,
	'chunk' => 'global'
]);

blocksy_theme_get_dynamic_styles([
	'name' => 'global/woocommerce/archive',
	'css' => $css,
	'mobile_css' => $mobile_css,
	'tablet_css' => $tablet_css,
	'context' => $context,
	'chunk' => 'global'
]);

blocksy_theme_get_dynamic_styles([
	'name' => 'global/woocommerce/single-product-gallery',
	'css' => $css,
	'mobile_css' => $mobile_css,
	'tablet_css' => $tablet_css,
	'context' => $context,
	'chunk' => 'global'
]);

blocksy_theme_get_dynamic_styles([
	'name' => 'global/woocommerce/single-product-layers',
	'css' => $css,
	'mobile_css' => $mobile_css,
	'tablet_css' => $tablet_css,
	'context' => $context,
	'chunk' => 'global'
]);