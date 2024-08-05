<?php
    /**
     * Plugin Name: Variation Swatches for WooCommerce
     * Plugin URI: https://wordpress.org/plugins/woo-variation-swatches/
     * Description: Beautiful colors, images and buttons variation swatches for woocommerce product attributes. Requires WooCommerce 5.6+
     * Author: Emran Ahmed
     * Version: 2.0.29
     * Domain Path: /languages
     * Requires PHP: 7.4
     * Requires at least: 5.6
     * Tested up to: 6.4
     * WC requires at least: 5.6
     * WC tested up to: 8.3
     * Text Domain: woo-variation-swatches
     * Author URI: https://getwooplugins.com/
     */
    
    defined( 'ABSPATH' ) or die( 'Keep Silent' );
    
    if ( ! defined( 'WOO_VARIATION_SWATCHES_PLUGIN_VERSION' ) ) {
        define( 'WOO_VARIATION_SWATCHES_PLUGIN_VERSION', '2.0.29' );
    }
    
    if ( ! defined( 'WOO_VARIATION_SWATCHES_PLUGIN_FILE' ) ) {
        define( 'WOO_VARIATION_SWATCHES_PLUGIN_FILE', __FILE__ );
    }
    
    // Include the main class.
    if ( ! class_exists( 'Woo_Variation_Swatches', false ) ) {
        require_once dirname( __FILE__ ) . '/includes/class-woo-variation-swatches.php';
    }
    
    // Require woocommerce admin message
    function woo_variation_swatches_wc_requirement_notice() {
        
        if ( ! class_exists( 'WooCommerce' ) ) {
            $text = esc_html__( 'WooCommerce', 'woo-variation-swatches' );
            
            $args = array(
                'tab'       => 'plugin-information',
                'plugin'    => 'woocommerce',
                'TB_iframe' => 'true',
                'width'     => '640',
                'height'    => '500',
            );
            
            $link    = esc_url( add_query_arg( $args, admin_url( 'plugin-install.php' ) ) );
            $message = wp_kses( __( "<strong>Variation Swatches for WooCommerce</strong> is an add-on of ", 'woo-variation-swatches' ), array( 'strong' => array() ) );
            
            printf( '<div class="%1$s"><p>%2$s <a class="thickbox open-plugin-details-modal" href="%3$s"><strong>%4$s</strong></a></p></div>', 'notice notice-error', $message, $link, $text );
        }
    }
    
    add_action( 'admin_notices', 'woo_variation_swatches_wc_requirement_notice' );
    
    /**
     * Returns the main instance.
     */
    
    function woo_variation_swatches() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
        
        if ( ! class_exists( 'WooCommerce', false ) ) {
            return false;
        }
        
        if ( function_exists( 'woo_variation_swatches_pro' ) ) {
            return woo_variation_swatches_pro();
        }
        
        return Woo_Variation_Swatches::instance();
    }
    
    add_action( 'plugins_loaded', 'woo_variation_swatches' );
    
    function is_using_correct_version_of_woo_variation_swatches_pro() {
        return defined( 'WOO_VARIATION_SWATCHES_PRO_PLUGIN_VERSION' ) && ( version_compare( WOO_VARIATION_SWATCHES_PRO_PLUGIN_VERSION, '2.0.26' ) >= 0 );
        // return defined( 'WOO_VARIATION_SWATCHES_PRO_PLUGIN_FILE' );
    }
    
    // Prevent activating pro old version
    function deactivate_woo_variation_swatches_pro() {
        
        if ( is_using_correct_version_of_woo_variation_swatches_pro() ) {
            return;
        }
        
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        if ( is_plugin_active( 'woo-variation-swatches-pro/woo-variation-swatches-pro.php' ) ) {
            // Deactivate the plugin silently, Prevent deactivation hooks from running.
            deactivate_plugins( 'woo-variation-swatches-pro/woo-variation-swatches-pro.php', true );
        }
    }
    
    function prevent_active_woo_variation_swatches_pro() {
        
        if ( is_using_correct_version_of_woo_variation_swatches_pro() ) {
            return;
        }
        
        echo 'You are running older version of "Variation Swatches for WooCommerce - Pro". Please upgrade to 2.0.26 or upper and continue.';
        exit();
    }
    
    function woo_variation_swatches_hpos_compatibility() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }
    
    add_action( 'before_woocommerce_init', 'woo_variation_swatches_hpos_compatibility' );
    add_action( 'plugins_loaded', 'deactivate_woo_variation_swatches_pro', 9 );
    add_action( 'activate_woo-variation-swatches-pro/woo-variation-swatches-pro.php', 'prevent_active_woo_variation_swatches_pro' );