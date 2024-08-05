<?php
defined( 'ABSPATH' ) or die( 'Keep Quit' );

if ( ! function_exists( 'wvs_woo_layout_injector_script_override' ) ):
	function wvs_woo_layout_injector_script_override() {
		if ( function_exists( 'sb_et_woo_li_enqueue' ) ) :
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_dequeue_script( 'sb_et_woo_li_js' );
			wp_enqueue_script( 'sb_et_woo_li_js_override', woo_variation_swatches()->assets_url( "/js/divi_woo_layout_injector{$suffix}.js" ), array( 'jquery' ), woo_variation_swatches()->version(), true );
		endif;
	}

	// add_action( 'wp_enqueue_scripts', 'wvs_woo_layout_injector_script_override', 99999 );
endif;


// ==========================================================
// WOODMART Theme
// ==========================================================
if ( ! function_exists( 'woodmart_has_swatches' ) ) {
	function woodmart_has_swatches( $id, $attr_name, $options, $available_variations, $swatches_use_variation_images = false ) {
		return array();
	}
}