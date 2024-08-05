<?php
    defined( 'ABSPATH' ) || exit;
    
    if ( ! class_exists( 'Woo_Variation_Swatches_Frontend' ) ) {
        class Woo_Variation_Swatches_Frontend {
            
            protected static $_instance = null;
            
            protected function __construct() {
                
                $this->includes();
                $this->hooks();
                $this->init();
                do_action( 'woo_variation_swatches_frontend_loaded', $this );
            }
            
            public static function instance() {
                if ( is_null( self::$_instance ) ) {
                    self::$_instance = new self();
                }
                
                return self::$_instance;
            }
            
            protected function includes() {
                require_once dirname( __FILE__ ) . '/class-woo-variation-swatches-compatibility.php';
                require_once dirname( __FILE__ ) . '/class-woo-variation-swatches-product-page.php';
            }
            
            protected function hooks() {
                add_filter( 'body_class', array( $this, 'body_class' ) );
            }
            
            protected function init() {
                $this->get_product_page();
                $this->get_compatibility();
            }
            
            // Start
            
            public function get_product_page() {
                return Woo_Variation_Swatches_Product_Page::instance();
            }
            
            public function get_compatibility() {
                return Woo_Variation_Swatches_Compatibility::instance();
            }
            
            public function get_attribute_taxonomy_by_name( $attribute_name ) {
                
                $transient_key = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'woo_variation_swatches_cache_attribute_taxonomy__%s', $attribute_name ) );
                
                if ( ! taxonomy_exists( $attribute_name ) ) {
                    return false;
                }
                
                if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
                    $attribute_name = str_replace( 'pa_', '', wc_sanitize_taxonomy_name( $attribute_name ) );
                } else {
                    return false;
                }
                
                if ( false === ( $attribute_taxonomy = get_transient( $transient_key ) ) ) {
                    
                    global $wpdb;
                    
                    $attribute_taxonomy = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s", esc_sql( $attribute_name ) ) );
                    
                    set_transient( $transient_key, $attribute_taxonomy );
                }
                
                return apply_filters( 'woo_variation_swatches_get_wc_attribute_taxonomy', $attribute_taxonomy, $attribute_name );
            }
            
            public function get_attribute_taxonomy_by_id( $attribute_id ) {
                
                $transient_key = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'woo_variation_swatches_cache_attribute_taxonomy_id__%s', $attribute_id ) );
                
                if ( false === ( $attribute_taxonomy = get_transient( $transient_key ) ) ) {
                    
                    global $wpdb;
                    
                    $attribute_taxonomy = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id = %d", $attribute_id ) );
                    
                    set_transient( $transient_key, $attribute_taxonomy );
                }
                
                return apply_filters( 'woo_variation_swatches_get_wc_attribute_taxonomy_by_id', $attribute_taxonomy, $attribute_taxonomy );
            }
            
            public function body_class( $classes ) {
                
                $behavior = sprintf( 'wvs-behavior-%s', sanitize_text_field( woo_variation_swatches()->get_option( 'attribute_behavior', 'blur' ) ) );
                
                $classes[] = 'woo-variation-swatches';
                $classes[] = $behavior;
                $classes[] = sprintf( 'wvs-theme-%s', strtolower( basename( get_stylesheet_directory() ) ) );
                $classes[] = ( wp_is_mobile() ? 'wvs-mobile' : '' );
                
                if ( wc_string_to_bool( woo_variation_swatches()->get_option( 'show_variation_label', 'yes' ) ) ) {
                    $classes[] = 'wvs-show-label';
                }
                if ( wc_string_to_bool( woo_variation_swatches()->get_option( 'enable_tooltip', 'yes' ) ) ) {
                    $classes[] = 'wvs-tooltip';
                }
                
                return array_filter( $classes );
            }
            
            public function get_dual_color_gradient_angle() {
                return apply_filters( 'woo_variation_swatches_dual_color_gradient_angle', '-45deg' );
            }
            
            public function is_color_attribute( $attribute ) {
                if ( ! is_object( $attribute ) ) {
                    return false;
                }
                
                return $attribute->attribute_type == 'color';
            }
            
            public function is_image_attribute( $attribute ) {
                if ( ! is_object( $attribute ) ) {
                    return false;
                }
                
                return $attribute->attribute_type == 'image';
            }
            
            public function is_button_attribute( $attribute ) {
                if ( ! is_object( $attribute ) ) {
                    return false;
                }
                
                return $attribute->attribute_type == 'button';
            }
            
            public function is_radio_attribute( $attribute ) {
                if ( ! is_object( $attribute ) ) {
                    return false;
                }
                
                return $attribute->attribute_type == 'radio';
            }
            
            public function get_product_attribute_color( $term, $data = array() ) {
                
                $term_id = 0;
                if ( is_numeric( $term ) ) {
                    $term_id = $term;
                }
                
                if ( is_object( $term ) ) {
                    $term_id = $term->term_id;
                }
                
                return get_term_meta( $term_id, 'product_attribute_color', true );
            }
            
            public function get_product_attribute_image( $term, $data = array() ) {
                
                $term_id = 0;
                if ( is_numeric( $term ) ) {
                    $term_id = $term;
                }
                
                if ( is_object( $term ) ) {
                    $term_id = $term->term_id;
                }
                
                return get_term_meta( $term_id, 'product_attribute_image', true );
            }
            
            public function get_product_children( $product ) {
                
                $variation_ids        = $product->get_children();
                $available_variations = array();
                
                if ( is_callable( '_prime_post_caches' ) ) {
                    _prime_post_caches( $variation_ids );
                }
                
                foreach ( $variation_ids as $variation_id ) {
                    
                    $variation = wc_get_product( $variation_id );
                    
                    if ( ! $variation || ! $variation->exists() ) {
                        continue;
                    }
                    
                    // Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
                    if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $product->get_id(), $variation ) && ! $variation->variation_is_visible() ) {
                        continue;
                    }
                    
                    $available_variations[] = $product->get_id();
                }
                
                return array_values( $available_variations );
                
            }
            
            public function get_product_variations( $product ) {
                
                
                $variation_ids        = $product->get_children();
                $available_variations = array();
                
                foreach ( $variation_ids as $variation_id ) {
                    
                    $variation = wc_get_product( $variation_id );
                    
                    // Hide out of stock variations if 'Hide out of stock items from the catalog' is checked.
                    /*if ( ! $variation || ! $variation->exists() || ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $variation->is_in_stock() ) ) {
                        continue;
                    }*/
                    if ( ! $variation || ! $variation->exists() ) {
                        continue;
                    }
                    
                    // Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
                    if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $product->get_id(), $variation ) && ! $variation->variation_is_visible() ) {
                        continue;
                    }
                    
                    $available_variations[] = $variation;
                    
                }
                
                return array_values( $available_variations );
            }
            
            public function get_product_attachment_props( $attachment_id = null, $product = false ) {
                
                $props      = array(
                    //'title'   => '',
                    //'caption' => '',
                    //'url'    => '',
                    'alt'    => '',
                    'src'    => '',
                    'srcset' => false,
                
                );
                $attachment = get_post( $attachment_id );
                
                if ( $attachment && 'attachment' === $attachment->post_type ) {
                    // $props['alt'] = wp_strip_all_tags( $attachment->post_title );
                    
                    $props[ 'alt' ] = wp_strip_all_tags( get_the_title( $product->get_id() ) );
                    
                    //$props['url'] = wp_get_attachment_url( $attachment_id );
                    
                    // Thumbnail version.
                    $image_size        = apply_filters( 'woocommerce_thumbnail_size', 'woocommerce_thumbnail' );
                    $src               = wp_get_attachment_image_src( $attachment_id, $image_size );
                    $props[ 'src' ]    = $src[ 0 ];
                    $props[ 'srcset' ] = false;
                }
                
                return $props;
            }
            
            public function get_variation_data() {
                ob_start();
                // phpcs:disable WordPress.Security.NonceVerification.Missing
                if ( empty( $_POST[ 'product_id' ] ) ) {
                    wp_die();
                }
                
                $variation = wc_get_product( absint( $_POST[ 'product_id' ] ) );
                
                if ( ! $variation ) {
                    wp_die();
                }
                
                
                $variation_data = array(
                    'id'                => $variation->get_id(),
                    'is_purchasable'    => $variation->is_purchasable(),
                    'is_active'         => $variation->variation_is_active(),
                    'in_stock'          => $variation->is_in_stock(),
                    'max_qty'           => 0 < $variation->get_max_purchase_quantity() ? $variation->get_max_purchase_quantity() : '',
                    'min_qty'           => $variation->get_min_purchase_quantity(),
                    'price_html'        => $variation->get_price_html(),
                    'availability_html' => wc_get_stock_html( $variation ),
                    'image'             => $this->get_product_attachment_props( $variation->get_image_id(), $variation ),
                );
                
                wp_send_json( $variation_data );
                // phpcs:enable
            }
        }
    }