<?php
    
    defined( 'ABSPATH' ) || exit;
    
    if ( ! class_exists( 'Woo_Variation_Swatches_Manage_Cache' ) ) {
        class Woo_Variation_Swatches_Manage_Cache {
            
            protected static $_instance = null;
            
            protected function __construct() {
                $this->includes();
                $this->hooks();
                $this->init();
                do_action( 'woo_variation_swatches_manage_cache_loaded', $this );
            }
            
            public static function instance() {
                if ( is_null( self::$_instance ) ) {
                    self::$_instance = new self();
                }
                
                return self::$_instance;
            }
            
            protected function includes() {
            }
            
            protected function hooks() {
                
                // Attributes
                add_action( 'woocommerce_attribute_added', array( $this, 'clear_cache_on_attribute_added' ), 10, 2 );
                add_action( 'woocommerce_attribute_updated', array( $this, 'clear_cache_on_attribute_updated' ), 10, 3 );
                add_action( 'woocommerce_attribute_deleted', array( $this, 'clear_cache_on_attribute_deleted' ), 10, 3 );
                
                // Products
                add_action( 'woocommerce_save_product_variation', array( $this, 'clear_cache_on_product_modify' ) );
                add_action( 'woocommerce_update_product_variation', array( $this, 'clear_cache_on_product_modify' ) );
                add_action( 'woocommerce_before_delete_product_variation', array(
                    $this,
                    'clear_cache_on_product_modify'
                ) );
                add_action( 'woocommerce_trash_product_variation', array( $this, 'clear_cache_on_product_modify' ) );
                
                // WooCommerce -> Status -> Tools -> Clear transients
                add_action( 'woocommerce_delete_product_transients', array(
                    $this,
                    'clear_cache_on_delete_product_transients'
                ) );
                
                // Options
                add_action( 'getwooplugins_settings_saved', array( $this, 'clear_cache_on_settings_modify' ) );
                add_action( 'getwooplugins_after_delete_options', array( $this, 'clear_cache_on_settings_modify' ) );
                
                // Product label settings
                
                add_action( 'woo_variation_swatches_product_settings_update', array(
                    $this,
                    'clear_cache_on_product_settings_modify'
                ) );
                add_action( 'woo_variation_swatches_product_settings_delete', array(
                    $this,
                    'clear_cache_on_product_settings_modify'
                ) );
            }
            
            protected function init() {
                if ( function_exists( 'wp_cache_add_global_groups' ) ) {
                    wp_cache_add_global_groups( array( 'woo_variation_swatches' ) );
                }
            }
            
            // Start
            
            public function get_key_with_language_suffix( $key ) {
                // wc_deprecated_function( 'Woo_Variation_Swatches_Manage_Cache::get_key_with_language_suffix', '2.0.18', 'Woo_Variation_Swatches_Manage_Cache::get_cache_key' );
                
                return $this->get_cache_key( $key );
            }
            
            private function is_polylang() {
                return function_exists( 'pll_default_language' );
            }
            
            public function get_cache_key( $key ) {
                
                $suffix = '';
                
                // Language
                $default_language = apply_filters( 'wpml_default_language', null );
                $current_language = apply_filters( 'wpml_current_language', null );
                
                if ( $this->is_polylang() ) {
                    $default_language = pll_default_language( 'locale' );
                    $current_language = pll_current_language( 'locale' );
                }
                
                if ( $current_language !== $default_language ) {
                    $suffix .= sprintf( '_%s', $current_language );
                }
                
                // Currency
                $default_currency = get_option( 'woocommerce_currency' );
                $current_currency = get_woocommerce_currency();
                
                if ( $current_currency !== $default_currency ) {
                    $suffix .= sprintf( '_%s', $current_currency );
                }
                
                return apply_filters( 'woo_variation_swatches_get_cache_key', $key . $suffix, $key, $suffix );
            }
            
            
            // Clear Settings Cache
            public function clear_cache_on_settings_modify() {
                
                do_action( 'litespeed_purge_all', 'Woo Variation Swatches: purge all' );
                wp_cache_delete( 'global_settings', 'woo_variation_swatches' );
                
                wp_cache_flush();
                
                $this->delete_last_changed();
                $this->clear_cache_by_group();
            }
            
            // Clear transients Cache
            public function clear_cache_on_delete_product_transients( $post_id ) {
                if ( $post_id > 0 ) {
                    
                    $cache_group = 'woo_variation_swatches';
                    
                    $cache_keys = array(
                        'variation_images_of__%s',
                        'product_settings_of__%s',
                        'variation_attributes_of__%s',
                        'available_variations__%s'
                    );
                    
                    foreach ( $cache_keys as $key_template ) {
                        $cache_key = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( $key_template, $post_id ) );
                        wp_cache_delete( $cache_key, $cache_group );
                    }
                    
                    $cache_key = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'available_preview_variation__%s', $post_id ) );
                    wp_cache_delete( $cache_key, $cache_group );
                }
                
                $this->delete_last_changed();
                $this->clear_cache_by_group();
            }
            
            // Clear Attributes Cache
            public function clear_cache_on_attribute_added( $id, $data ) {
                
                $transient_key       = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'woo_variation_swatches_cache_attribute_taxonomy__%s', wc_attribute_taxonomy_name( $data[ 'attribute_name' ] ) ) );
                $transient_key_by_id = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'woo_variation_swatches_cache_attribute_taxonomy_id__%s', $id ) );
                
                delete_transient( $transient_key );
                delete_transient( $transient_key_by_id );
                $this->delete_last_changed();
                $this->clear_cache_by_group();
            }
            
            public function clear_cache_on_attribute_updated( $id, $data, $old_slug ) {
                
                $transient_key       = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'woo_variation_swatches_cache_attribute_taxonomy__%s', wc_attribute_taxonomy_name( $data[ 'attribute_name' ] ) ) );
                $transient_key_old   = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'woo_variation_swatches_cache_attribute_taxonomy__%s', wc_attribute_taxonomy_name( $old_slug ) ) );
                $transient_key_by_id = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'woo_variation_swatches_cache_attribute_taxonomy_id__%s', $id ) );
                
                delete_transient( $transient_key );
                delete_transient( $transient_key_old );
                delete_transient( $transient_key_by_id );
                
                $this->delete_last_changed();
                $this->clear_cache_by_group();
            }
            
            public function clear_cache_on_attribute_deleted( $id, $name, $taxonomy ) {
                
                $transient_key       = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'woo_variation_swatches_cache_attribute_taxonomy__%s', wc_attribute_taxonomy_name( $name ) ) );
                $transient_key_by_id = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'woo_variation_swatches_cache_attribute_taxonomy_id__%s', $id ) );
                
                delete_transient( $transient_key );
                delete_transient( $transient_key_by_id );
                $this->delete_last_changed();
                $this->clear_cache_by_group();
            }
            
            // Clear Product Cache
            
            public function clear_cache_on_product_modify( $variation_id ) {
                
                $variation_product = wc_get_product( $variation_id );
                
                if ( ! $variation_product ) {
                    return false;
                }
                
                $product_id  = $variation_product->get_parent_id();
                $cache_group = 'woo_variation_swatches';
                
                $cache_keys = array(
                    'variation_images_of__%s',
                    'product_settings_of__%s',
                    'variation_attributes_of__%s',
                    'available_variations__%s'
                );
                
                foreach ( $cache_keys as $key_template ) {
                    $cache_key = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( $key_template, $product_id ) );
                    wp_cache_delete( $cache_key, $cache_group );
                }
                
                $cache_key_1 = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'available_preview_variation__%s', $variation_id ) );
                wp_cache_delete( $cache_key_1, $cache_group );
                
                $this->delete_last_changed();
                $this->clear_cache_by_group();
            }
            
            public function clear_cache_on_product_settings_modify( $product_id ) {
                
                $cache_key   = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'product_settings_of__%s', $product_id ) );
                $cache_group = 'woo_variation_swatches';
                
                wp_cache_delete( $cache_key, $cache_group );
                
                $cache_key_2 = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'available_variations__%s', $product_id ) );
                wp_cache_delete( $cache_key_2, $cache_group );
                
                $cache_key_3 = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'available_preview_variation__%s', $product_id ) );
                wp_cache_delete( $cache_key_3, $cache_group );
                
                $this->delete_last_changed();
                $this->clear_cache_by_group();
            }
            
            public function get_last_changed() {
                return wp_cache_get_last_changed( 'woo_variation_swatches' );
            }
            
            public function delete_last_changed() {
                wp_cache_delete( 'last_changed', 'woo_variation_swatches' );
            }
            
            public function update_last_changed() {
                wp_cache_set( 'last_changed', microtime(), 'woo_variation_swatches' );
            }
            
            public function clear_cache_by_group() {
                if ( function_exists( 'wp_cache_flush_group' ) && method_exists( 'WP_Object_Cache', 'flush_group' ) ) {
                    if ( wp_cache_supports( 'flush_group' ) ) {
                        wp_cache_flush_group( 'woo_variation_swatches' );
                    }
                }
            }
        }
    }
	