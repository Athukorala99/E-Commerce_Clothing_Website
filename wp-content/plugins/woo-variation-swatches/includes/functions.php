<?php
defined( 'ABSPATH' ) || exit;

/**
 * Show swatches on archive page
 *
 * @deprecated 2.0.0 - Use "woo_variation_swatches()->show_archive_page_swatches()" instead.
 */
if ( ! function_exists( 'wvs_pro_archive_variation_template' ) ) {
	function wvs_pro_archive_variation_template() {
		wc_deprecated_function( __FUNCTION__, '2.0.0', 'woo_variation_swatches()->show_archive_page_swatches()' );
		woo_variation_swatches()->show_archive_page_swatches();
	}
}