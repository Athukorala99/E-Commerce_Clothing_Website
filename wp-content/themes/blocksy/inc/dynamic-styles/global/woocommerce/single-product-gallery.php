<?php

// vertical gallery helper
$product_view_type = blocksy_get_product_view_type();

$gallery_style = blocksy_get_theme_mod('gallery_style', 'horizontal');

if ($product_view_type && $gallery_style === 'vertical') {

	global $_wp_additional_image_sizes;

	if (isset($_wp_additional_image_sizes['woocommerce_gallery_thumbnail'])) {
		$css->put(
			'.product-entry-wrapper',
			'--thumbs-width: ' . $_wp_additional_image_sizes['woocommerce_gallery_thumbnail']['width'] . 'px'
		);
	}
}


// gallery width
$productGalleryWidth = blocksy_get_theme_mod( 'productGalleryWidth', 50 );

if ($productGalleryWidth !== 50) {
	$css->put(
		'.product-entry-wrapper',
		'--product-gallery-width: ' . $productGalleryWidth . '%'
	);
}


// thumbnails spacing
$product_thumbs_spacing = blocksy_get_theme_mod( 'product_thumbs_spacing', '15px' );

if ($product_thumbs_spacing !== '15px') {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.product-entry-wrapper',
		'variableName' => 'thumbs-spacing',
		'unit' => '',
		'value' => $product_thumbs_spacing
	]);
}


// slider arrows
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('slider_nav_arrow_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-product-gallery',
			'variable' => 'flexy-nav-arrow-color'
		],

		'hover' => [
			'selector' => '.woocommerce-product-gallery',
			'variable' => 'flexy-nav-arrow-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('slider_nav_background_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-product-gallery',
			'variable' => 'flexy-nav-background-color'
		],

		'hover' => [
			'selector' => '.woocommerce-product-gallery',
			'variable' => 'flexy-nav-background-hover-color'
		],
	],
]);


// lightbox button
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('lightbox_button_icon_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-product-gallery__trigger',
			'variable' => 'lightbox-button-icon-color'
		],

		'hover' => [
			'selector' => '.woocommerce-product-gallery__trigger',
			'variable' => 'lightbox-button-icon-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('lightbox_button_background_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-product-gallery__trigger',
			'variable' => 'lightbox-button-background-color'
		],

		'hover' => [
			'selector' => '.woocommerce-product-gallery__trigger',
			'variable' => 'lightbox-button-hover-background-color'
		],
	],
]);
