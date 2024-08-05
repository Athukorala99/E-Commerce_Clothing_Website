<?php

namespace Blocksy;

require get_template_directory() . '/inc/components/woocommerce/general.php';

require get_template_directory() . '/inc/components/woocommerce/common/layer-defaults.php';
require get_template_directory() . '/inc/components/woocommerce/common/rest-api.php';
require get_template_directory() . '/inc/components/woocommerce/common/checkout.php';
require get_template_directory() . '/inc/components/woocommerce/common/cart.php';
require get_template_directory() . '/inc/components/woocommerce/common/account.php';
require get_template_directory() . '/inc/components/woocommerce/common/store-notice.php';
require get_template_directory() . '/inc/components/woocommerce/common/mini-cart.php';
require get_template_directory() . '/inc/components/woocommerce/common/sale-flash.php';
require get_template_directory() . '/inc/components/woocommerce/common/stock-badge.php';

require get_template_directory() . '/inc/components/woocommerce/archive/helpers.php';
require get_template_directory() . '/inc/components/woocommerce/archive/index.php';
require get_template_directory() . '/inc/components/woocommerce/archive/product-card.php';
require get_template_directory() . '/inc/components/woocommerce/archive/loop.php';
require get_template_directory() . '/inc/components/woocommerce/archive/loop-elements.php';
require get_template_directory() . '/inc/components/woocommerce/archive/pagination.php';

require get_template_directory() . '/inc/components/woocommerce/single/helpers.php';
require get_template_directory() . '/inc/components/woocommerce/single/review-form.php';
require get_template_directory() . '/inc/components/woocommerce/single/single-modifications.php';
require get_template_directory() . '/inc/components/woocommerce/single/add-to-cart.php';
require get_template_directory() . '/inc/components/woocommerce/single/woo-gallery.php';
require get_template_directory() . '/inc/components/woocommerce/single/tabs.php';

// if (class_exists('WC_Additional_Variation_Images_Frontend')) {
	require get_template_directory() . '/inc/components/woocommerce/integrations/woocommerce-additional-variation-images.php';
// }

if (class_exists('Custom_Related_Products')) {
	require get_template_directory() . '/inc/components/woocommerce/integrations/wt-woocommerce-related-products.php';

}

add_filter(
	'blocksy_theme_autoloader_classes_map',
	function ($classes) {
		$prefix = 'inc/components/woocommerce/';

		$classes['WooCommerceBoot'] = $prefix . 'boot.php';
		$classes['WooCommerceImageSizes'] = $prefix . 'common/image-sizes.php';

		$classes['WooCommerceSingle'] = $prefix . 'single/single.php';
		$classes['WooCommerceAddToCart'] = $prefix . 'single/add-to-cart.php';
		$classes['SingleProductAdditionalActions'] = $prefix . 'single/additional-actions-layer.php';

		return $classes;
	}
);

class WooCommerce {
	public $single = null;

	public function __construct() {
		new WooCommerceBoot();
		new WooCommerceImageSizes();

		$this->single = new WooCommerceSingle();
	}
}

