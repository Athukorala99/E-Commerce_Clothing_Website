<?php
    defined( 'ABSPATH' ) || exit;
    
    if ( ! class_exists( 'Woo_Variation_Swatches_Product_Page' ) ) {
        class Woo_Variation_Swatches_Product_Page {
            
            protected static $_instance = null;
            
            protected function __construct() {
                
                $this->includes();
                $this->hooks();
                $this->init();
                
                do_action( 'woo_variation_swatches_product_page_loaded', $this );
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
                
                add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'dropdown' ), 20, 2 );
                add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
                add_action( 'wc_ajax_woo_get_all_variations', array( $this, 'get_all_variations' ) );
                add_filter( 'woocommerce_get_script_data', array( $this, 'add_to_cart_variation_params' ), 10, 2 );
                add_filter( 'woocommerce_ajax_variation_threshold', array( $this, 'ajax_variation_threshold' ) );
                add_filter( 'woocommerce_variable_children_args', array( $this, 'variable_children_args' ), 10, 3 );
                add_filter( 'woocommerce_variation_is_active', array( $this, 'disable_out_of_stock_item' ), 10, 2 );
                add_filter( 'woocommerce_available_variation', array( $this, 'add_variation_data' ), 10, 3 );
                
                add_action( 'woocommerce_before_variations_form', array( $this, 'before_variations_form' ) );
                add_action( 'woocommerce_after_variations_form', array( $this, 'after_variations_form' ) );
                
                
                // add_action( 'pmxi_before_post_import', $callback);
                
                // add_action( 'woocommerce_after_variations_form', array( $this, 'enqueue_script' ) );
                
                // add_filter( 'nocache_headers', array( $this, 'cache_ajax_response' ), 99 );
                // add_action( 'wp', array( $this, 'stop_prevent_ajax_caching' ), 1 );
                // wp_cache_flush()
            }
            
            protected function init() {
            
            }
            
            // Start
            
            public function before_variations_form() {
                global $product;
                $threshold_min  = apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
                $threshold_max  = $this->get_variation_threshold_max( $product );
                $total_children = count( $product->get_children() );
                $attributes     = apply_filters( 'woo_variation_swatches_single_product_wrapper_attributes', array(
                    'data-product_id'    => absint( $product->get_id() ),
                    'data-threshold_min' => absint( $threshold_min ),
                    'data-threshold_max' => absint( $threshold_max ),
                    'data-total'         => absint( $total_children ),
                ),                               $product );
                
                echo sprintf( '<div %s>', wc_implode_html_attributes( $attributes ) ); // WPCS: XSS ok. );
            }
            
            public function after_variations_form() {
                echo '</div>';
            }
            
            public function stop_prevent_ajax_caching() {
                global $wp_query;
                if ( ! is_ajax() ) {
                    return true;
                }
                
                $action   = isset( $_GET[ 'wc-ajax' ] ) ? sanitize_text_field( $_GET[ 'wc-ajax' ] ) : false;
                $requests = array( 'woo_get_variations', 'woo_get_all_variations' );
                
                if ( $action && in_array( $action, $requests ) ) {
                    wc_maybe_define_constant( 'DONOTCACHEPAGE', false );
                    wc_maybe_define_constant( 'DONOTCACHEOBJECT', false );
                    wc_maybe_define_constant( 'DONOTCACHEDB', false );
                }
                
                return true;
                
            }
            
            public function cache_ajax_response( $headers ) {
                
                if ( ! is_ajax() ) {
                    return $headers;
                }
                
                $action = isset( $_GET[ 'wc-ajax' ] ) ? sanitize_text_field( $_GET[ 'wc-ajax' ] ) : false;
                
                $requests = array( 'woo_get_variations', 'woo_get_all_variations' );
                if ( $action && in_array( $action, $requests ) ) {
                    // ask the browser to cache this response
                    
                    $expires       = HOUR_IN_SECONDS;        // 1 hr
                    $cache_control = sprintf( 'public, s-max-age=%d', $expires );
                    
                    $headers[ 'Pragma' ]                                    = 'cache'; // public / cache. backwards compatibility with HTTP/1.0 caches
                    $headers[ 'Expires' ]                                   = $expires;
                    $headers[ 'Cache-Control' ]                             = $cache_control;
                    $headers[ 'X-Variation-Swatches-Ajax-Header-Modified' ] = true;
                }
                
                return $headers;
            }
            
            public function add_variation_data( $variation_data, $product, $variation ) {
                
                if ( woo_variation_swatches()->is_pro() && wc_string_to_bool( woo_variation_swatches()->get_option( 'enable_linkable_variation_url', 'no' ) ) ) {
                    $variation_data[ 'variation_permalink' ] = $variation->get_permalink();
                }
                
                if ( woo_variation_swatches()->is_pro() && wc_string_to_bool( woo_variation_swatches()->get_option( 'default_to_image', 'yes' ) ) ) {
                    $variation_data[ 'variation_image_id' ] = $variation->get_image_id();
                }
                
                if ( woo_variation_swatches()->is_pro() && wc_string_to_bool( woo_variation_swatches()->get_option( 'show_variation_stock_info', 'no' ) ) ) {
                    $variation_data[ 'variation_stock_left' ] = $variation->managing_stock() ? sprintf( esc_html__( '%s left', 'woo-variation-swatches' ), $variation->get_stock_quantity() ) : '';
                }
                
                return $variation_data;
            }
            
            public function disable_out_of_stock_item( $default, $variation ) {
                if ( woo_variation_swatches()->is_pro() && ! $variation->is_in_stock() && wc_string_to_bool( woo_variation_swatches()->get_option( 'hide_out_of_stock_variation', 'yes' ) ) ) {
                    return false;
                }
                
                return $default;
            }
            
            public function variable_children_args( $all_args, $product, $visible_only ) {
                
                // The issue is: During import it does not save disabled variation.
                if ( function_exists( 'wp_all_import_get_import_id' ) && wp_all_import_get_import_id() !== 'new' ) {
                    return $all_args;
                }
                
                // Show only published variation product.
                if ( ! $visible_only ) {
                    $all_args[ 'post_status' ] = 'publish';
                }
                
                return $all_args;
            }
            
            public function ajax_variation_threshold( $limit ) {
                return $limit;
            }
            
            public function get_variation_threshold_max( $product ) {
                return absint( apply_filters( 'woo_variation_swatches_global_ajax_variation_threshold_max', 100, $product ) );
            }
            
            public function add_to_cart_variation_params( $params, $handle ) {
                
                if ( 'wc-add-to-cart-variation' === $handle ) {
                    if ( is_product() ) {
                        
                        $product = wc_get_product();
                        
                        $params[ 'woo_variation_swatches_ajax_variation_threshold_min' ] = apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
                        $params[ 'woo_variation_swatches_ajax_variation_threshold_max' ] = $this->get_variation_threshold_max( $product );
                        $params[ 'woo_variation_swatches_total_children' ]               = $product ?? count( $product->get_children() );
                    }
                }
                
                return $params;
            }
            
            // ajax return
            public function get_all_variations() {
                ob_start();
                
                // phpcs:disable WordPress.Security.NonceVerification.Missing
                if ( empty( $_POST[ 'product_id' ] ) ) {
                    wp_die();
                }
                
                $product = wc_get_product( absint( $_POST[ 'product_id' ] ) );
                
                if ( ! $product ) {
                    wp_die();
                }
                
                $available_variations = $product->get_available_variations();
                wp_send_json( $available_variations );
                // phpcs:enable
            }
            
            public function enqueue_scripts() {
                
                $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
                
                if ( wc_string_to_bool( woo_variation_swatches()->get_option( 'enable_stylesheet', 'yes' ) ) ) {
                    wp_enqueue_style( 'woo-variation-swatches', woo_variation_swatches()->assets_url( "/css/frontend{$suffix}.css" ), array(), woo_variation_swatches()->assets_version( "/css/frontend{$suffix}.css" ) );
                }
                
                $this->add_inline_style();
                
                // $is_defer = is_wp_version_compatible( '6.3' ) ? array( 'strategy' => 'defer' ) : true;
                
                wp_register_script( 'woo-variation-swatches', woo_variation_swatches()->assets_url( "/js/frontend{$suffix}.js" ), array(
                    'jquery',
                    'wp-util',
                    'underscore',
                    'jquery-blockui',
                    'wp-api-request',
                    'wp-api-fetch',
                    'wp-polyfill',
                    'wp-url'
                ),                  woo_variation_swatches()->assets_version( "/js/frontend{$suffix}.js" ), true );
                
                $extra_params_for_rest_uri = apply_filters( 'woo_variation_swatches_rest_add_extra_params', array() );
                
                if ( $extra_params_for_rest_uri ) {
                    $extra_params_for_rest_uri = map_deep( $extra_params_for_rest_uri, 'sanitize_text_field' );
                    wp_add_inline_script( 'woo-variation-swatches', sprintf( 'wp.apiFetch.use( window.createMiddlewareForExtraQueryParams(%s) )', wp_json_encode( $extra_params_for_rest_uri ) ) );
                }
                
                wp_localize_script( 'woo-variation-swatches', 'woo_variation_swatches_options', $this->js_options() );
                
                // @TODO: we need to load swatches script based on 'wc-add-to-cart-variation' script
                wp_enqueue_script( 'woo-variation-swatches' );
            }
            
            public function inline_svg_encode( $string ) {
                $entities     = array( '<', '>', '#', '"' );
                $replacements = array( '%3C', '%3E', "%23", "'" );
                
                return str_replace( $entities, $replacements, $string );
            }
            
            public function inline_svg( $string ) {
                return sprintf( 'url("data:image/svg+xml;utf8,%s")', $this->inline_svg_encode( $string ) );
            }
            
            public function implode_css_property_value( $raw_properties ) {
                $properties = array();
                foreach ( $raw_properties as $name => $value ) {
                    $properties[] = esc_attr( $name ) . ':' . esc_attr( $value );
                }
                
                return implode( ";\n", $properties );
            }
            
            public function inline_style_declaration() {
                
                $width     = absint( woo_variation_swatches()->get_option( 'width', 30 ) );
                $height    = absint( woo_variation_swatches()->get_option( 'height', 30 ) );
                $font_size = absint( woo_variation_swatches()->get_option( 'single_font_size', 16 ) );
                
                $declaration = array(
                    '--wvs-single-product-item-width'     => sprintf( '%spx', $width ),
                    '--wvs-single-product-item-height'    => sprintf( '%spx', $height ),
                    '--wvs-single-product-item-font-size' => sprintf( '%spx', $font_size ),
                );
                
                return apply_filters( 'woo_variation_swatches_inline_style_declaration', $declaration );
            }
            
            public function add_inline_style() {
                
                if ( apply_filters( 'disable_woo_variation_swatches_inline_style', false ) ) {
                    return;
                }
                
                $tick_color  = sanitize_hex_color( woo_variation_swatches()->get_option( 'tick_color', '#ffffff' ) );
                $cross_color = sanitize_hex_color( woo_variation_swatches()->get_option( 'cross_color', '#ff0000' ) );
                
                $style = "";
                $style .= sprintf( "\n--wvs-tick:%s;\n", $this->inline_svg( sprintf( '<svg filter="drop-shadow(0px 0px 2px rgb(0 0 0 / .8))" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 30 30"><path fill="none" stroke="%s" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M4 16L11 23 27 7"/></svg>', $tick_color ) ) );
                $style .= sprintf( "\n--wvs-cross:%s;\n", $this->inline_svg( sprintf( '<svg filter="drop-shadow(0px 0px 5px rgb(255 255 255 / .6))" xmlns="http://www.w3.org/2000/svg" width="72px" height="72px" viewBox="0 0 24 24"><path fill="none" stroke="%s" stroke-linecap="round" stroke-width="0.6" d="M5 5L19 19M19 5L5 19"/></svg>', $cross_color ) ) );
                
                $style .= $this->implode_css_property_value( $this->inline_style_declaration() );
                
                $style = sprintf( ":root {%s}", $style );
                
                $enable_catalog_mode        = wc_string_to_bool( woo_variation_swatches()->get_option( 'enable_catalog_mode', 'no' ) );
                $catalog_mode_display_limit = absint( woo_variation_swatches()->get_option( 'catalog_mode_display_limit', 0 ) );
                $display_limit              = absint( woo_variation_swatches()->get_option( 'display_limit', 0 ) );
                $archive_display_limit      = absint( woo_variation_swatches()->get_option( 'archive_display_limit', 0 ) );
                
                
                $style = apply_filters( 'woo_variation_swatches_inline_style', $style );
                
                if ( woo_variation_swatches()->is_pro() && $enable_catalog_mode && $catalog_mode_display_limit > 0 ) {
                    $style .= sprintf( ".enabled-catalog-display-limit-mode > li.variable-item:nth-child(n+%d) {display:none !important;}", absint( $catalog_mode_display_limit ) + 1 );
                }
                
                if ( woo_variation_swatches()->is_pro() && $display_limit > 0 ) {
                    $style .= sprintf( ".enabled-display-limit-mode > li.variable-item:nth-child(n+%d) {display:none !important;}", absint( $display_limit ) + 1 );
                }
                if ( woo_variation_swatches()->is_pro() && $archive_display_limit > 0 && ! $enable_catalog_mode ) {
                    $style .= sprintf( ".enabled-archive-display-limit-mode > li.variable-item:nth-child(n+%d) {display:none !important;}", absint( $archive_display_limit ) + 1 );
                }
                
                wp_add_inline_style( 'woo-variation-swatches', $style );
            }
            
            public function js_options() {
                
                return apply_filters( 'woo_variation_swatches_js_options', array(
                    'show_variation_label'      => wc_string_to_bool( woo_variation_swatches()->get_option( 'show_variation_label', 'yes' ) ),
                    'clear_on_reselect'         => wc_string_to_bool( woo_variation_swatches()->get_option( 'clear_on_reselect', 'no' ) ),
                    'variation_label_separator' => sanitize_text_field( woo_variation_swatches()->get_option( 'variation_label_separator', ':' ) ),
                    'is_mobile'                 => wp_is_mobile(),
                    'show_variation_stock'      => woo_variation_swatches()->is_pro() && wc_string_to_bool( woo_variation_swatches()->get_option( 'show_variation_stock_info', 'no' ) ),
                    'stock_label_threshold'     => absint( woo_variation_swatches()->get_option( 'stock_label_display_threshold', '5' ) ),
                    'cart_redirect_after_add'   => get_option( 'woocommerce_cart_redirect_after_add', 'no' ),
                    'enable_ajax_add_to_cart'   => get_option( 'woocommerce_enable_ajax_add_to_cart', 'yes' ),
                    'cart_url'                  => apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null ),
                    'is_cart'                   => is_cart(),
                ) );
            }
            
            public function is_archive( $data ) {
                // $args = isset( $data[ 'args' ] ) ? $data[ 'args' ] : $data;
                
                return isset( $data[ 'is_archive' ] ) && wc_string_to_bool( $data[ 'is_archive' ] );
            }
            
            public function wrapper_class( $args, $attribute, $product, $attribute_type ) {
                
                $classes = array();
                
                $shape     = sprintf( 'wvs-style-%s', woo_variation_swatches()->get_option( 'shape_style', 'squared' ) );
                $classes[] = 'variable-items-wrapper';
                $classes[] = sprintf( '%s-variable-items-wrapper', $attribute_type );
                $classes[] = sanitize_text_field( $shape );
                
                return $classes;
            }
            
            public function wrapper_html_attribute( $args, $attribute, $product, $attribute_type, $options ) {
                
                $raw_html_attributes = array();
                $css_classes         = $this->wrapper_class( $args, $attribute, $product, $attribute_type );
                
                $raw_html_attributes[ 'role' ]                  = 'radiogroup';
                $raw_html_attributes[ 'aria-label' ]            = wc_attribute_label( $attribute, $product );
                $raw_html_attributes[ 'class' ]                 = implode( ' ', array_unique( array_values( $css_classes ) ) );
                $raw_html_attributes[ 'data-attribute_name' ]   = wc_variation_attribute_name( $attribute );
                $raw_html_attributes[ 'data-attribute_values' ] = wc_esc_json( wp_json_encode( array_values( $options ) ) );
                
                return $raw_html_attributes;
            }
            
            public function wrapper_start( $args, $attribute, $product, $attribute_type, $options ) {
                
                $html_attributes = $this->wrapper_html_attribute( $args, $attribute, $product, $attribute_type, $options );
                
                // return sprintf( '<ul role="radiogroup" aria-label="%1$s" class="%2$s" data-attribute_name="%3$s" data-attribute_values="%4$s">', esc_attr( wc_attribute_label( $attribute, $product ) ), implode( ' ', array_unique( array_values( $classes ) ) ), esc_attr( wc_variation_attribute_name( $attribute ) ), wc_esc_json( wp_json_encode( array_values( $options ) ) ) );
                return sprintf( '<ul %s>', wc_implode_html_attributes( $html_attributes ) );
            }
            
            public function wrapper_end() {
                return '</ul>';
            }
            
            public function item_start( $data, $attribute_type, $variation_data = array() ) {
                
                
                $args           = $data[ 'args' ];
                $term_or_option = $data[ 'item' ];
                
                $options     = $args[ 'options' ];
                $product     = $args[ 'product' ];
                $attribute   = $args[ 'attribute' ];
                $is_selected = $data[ 'is_selected' ];
                $option_name = $data[ 'option_name' ];
                $option_slug = $data[ 'option_slug' ];
                $slug        = $data[ 'slug' ];
                
                $is_term = wc_string_to_bool( $data[ 'is_term' ] );
                
                $css_class = implode( ' ', array_unique( array_values( apply_filters( 'woo_variation_swatches_variable_item_css_class', $this->get_item_css_classes( $data, $attribute_type, $variation_data ), $data, $attribute_type, $variation_data ) ) ) );
                
                $html_attributes = array(
                    'aria-checked' => ( $is_selected ? 'true' : 'false' ),
                    'tabindex'     => ( wp_is_mobile() ? '2' : '0' ),
                );
                
                $html_attributes = wp_parse_args( $this->get_item_tooltip_attribute( $data, $attribute_type, $variation_data ), $html_attributes );
                
                $html_attributes = apply_filters( 'woo_variation_swatches_variable_item_custom_attributes', $html_attributes, $data, $attribute_type, $variation_data );
                
                return sprintf( '<li %1$s class="variable-item %2$s-variable-item %2$s-variable-item-%3$s %4$s" title="%5$s" data-title="%5$s" data-value="%6$s" role="radio" tabindex="0"><div class="variable-item-contents">', wc_implode_html_attributes( $html_attributes ), esc_attr( $attribute_type ), esc_attr( $option_slug ), esc_attr( $css_class ), esc_html( $option_name ), esc_attr( $slug ) );
            }
            
            public function get_item_css_classes( $data, $attribute_type, $variation_data = array() ) {
                
                $css_classes = array();
                
                $is_selected = wc_string_to_bool( $data[ 'is_selected' ] );
                
                if ( $is_selected ) {
                    $css_classes[] = 'selected';
                }
                
                return $css_classes;
            }
            
            public function get_item_tooltip_attribute( $data, $attribute_type, $variation_data = array() ) {
                
                $html_attributes = array();
                
                $option_name = $data[ 'option_name' ];
                
                $enable_tooltip = wc_string_to_bool( woo_variation_swatches()->get_option( 'enable_tooltip', 'yes' ) );
                
                if ( $enable_tooltip ) {
                    $tooltip = trim( apply_filters( 'woo_variation_swatches_global_variable_item_tooltip_text', $option_name, $data ) );
                    
                    $html_attributes[ 'data-wvstooltip' ] = esc_attr( $tooltip );
                }
                
                return $html_attributes;
            }
            
            public function item_end() {
                $html = '';
                if ( woo_variation_swatches()->is_pro() && wc_string_to_bool( woo_variation_swatches()->get_option( 'show_variation_stock_info', 'no' ) ) ) {
                    $html .= '<div class="wvs-stock-left-info" data-wvs-stock-info=""></div>';
                }
                $html .= '</div></li>';
                
                return $html;
            }
            
            public function get_available_variation_image( $variation, $product ) {
                if ( is_numeric( $variation ) ) {
                    $variation = wc_get_product( $variation );
                }
                if ( ! $variation instanceof WC_Product_Variation ) {
                    return false;
                }
                
                // $placeholder_image_id = get_option( 'woocommerce_placeholder_image', 0 );
                // $variation_image_id = $variation->get_image_id() ? $variation->get_image_id() : $placeholder_image_id;
                
                $available_variation = array(
                    'attributes'           => $variation->get_variation_attributes(),
                    'image_id'             => $variation->get_image_id(),
                    'is_in_stock'          => $variation->is_in_stock(),
                    'is_purchasable'       => $variation->is_purchasable(),
                    'variation_id'         => $variation->get_id(),
                    'variation_image_id'   => $variation->get_image_id(),
                    'product_id'           => $product->get_id(),
                    'availability_html'    => wc_get_stock_html( $variation ),
                    'price_html'           => '<span class="price">' . $variation->get_price_html() . '</span>',
                    'variation_is_active'  => $variation->variation_is_active(),
                    'variation_is_visible' => $variation->variation_is_visible(),
                );
                
                return apply_filters( 'woo_variation_swatches_get_available_variation_image', $available_variation, $variation, $product );
            }
            
            public function get_available_variation_images( $product ) {
                
                $cache_key   = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'variation_images_of__%s', $product->get_id() ) );
                $cache_group = 'woo_variation_swatches';
                
                $default_to_image_from_parent = wc_string_to_bool( woo_variation_swatches()->get_option( 'default_to_image_from_parent', 'yes' ) );
                
                if ( false === ( $variations = wp_cache_get( $cache_key, $cache_group ) ) ) {
                    
                    $variation_ids        = $product->get_children();
                    $available_variations = array();
                    
                    foreach ( $variation_ids as $variation_id ) {
                        
                        $variation = wc_get_product( $variation_id );
                        
                        // Hide out of stock variations if 'Hide out of stock items from the catalog' is checked.
                        if ( ! $variation || ! $variation->exists() || ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $variation->is_in_stock() ) ) {
                            //	continue;
                        }
                        
                        
                        if ( ! $variation || ! $variation->exists() ) {
                            continue;
                        }
                        
                        // Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
                        if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $product->get_id(), $variation ) && ! $variation->variation_is_visible() ) {
                            continue;
                        }
                        
                        if ( ! $variation->get_image_id( 'edit' ) > 0 && ! $default_to_image_from_parent ) {
                            continue;
                        }
                        
                        $available_variations[] = $this->get_available_variation_image( $variation, $product );
                    }
                    
                    $variations = array_values( array_filter( $available_variations ) );
                    
                    wp_cache_set( $cache_key, $variations, $cache_group );
                }
                
                return $variations;
            }
            
            public function get_variation_by_attribute_name_value( $available_variations, $attribute_name, $attribute_value ) {
                return array_reduce( $available_variations, function ( $item, $variation ) use ( $attribute_name, $attribute_value ) {
                    
                    if ( $variation[ 'attributes' ][ $attribute_name ] === $attribute_value ) {
                        $item = $variation;
                    }
                    
                    return $item;
                },                   array() );
            }
            
            public function get_variation_data_by_attribute_name( $available_variations, $attribute_name ) {
                
                $assigned       = array();
                $attribute_name = wc_variation_attribute_name( $attribute_name );
                
                foreach ( $available_variations as $variation ) {
                    $attrs = $variation[ 'attributes' ];
                    $value = $attrs[ $attribute_name ];
                    
                    if ( ! isset( $assigned[ $attribute_name ][ $value ] ) && ! empty( $value ) ) {
                        $assigned[ $attribute_name ][ $value ] = array(
                            'image_id'     => $variation[ 'variation_image_id' ],
                            'variation_id' => $variation[ 'variation_id' ],
                            'type'         => empty( $variation[ 'variation_image_id' ] ) ? 'button' : 'image',
                        );
                    }
                }
                
                return $assigned;
            }
            
            public function get_image_attribute_id( $data, $attribute_type, $variation_data = array() ) {
                
                if ( 'image' === $attribute_type ) {
                    
                    $term = $data[ 'item' ];
                    
                    // Global
                    return apply_filters( 'woo_variation_swatches_global_product_attribute_image_id', absint( woo_variation_swatches()->get_frontend()->get_product_attribute_image( $term, $data ) ), $data );
                }
                
                return 0;
            }
            
            public function get_image_attribute( $data, $attribute_type, $variation_data = array() ) {
                if ( 'image' === $attribute_type ) {
                    
                    $term = $data[ 'item' ];
                    
                    // Global
                    $attachment_id = apply_filters( 'woo_variation_swatches_global_product_attribute_image_id', absint( woo_variation_swatches()->get_frontend()->get_product_attribute_image( $term, $data ) ), $data );
                    $image_size    = apply_filters( 'woo_variation_swatches_global_product_attribute_image_size', sanitize_text_field( woo_variation_swatches()->get_option( 'attribute_image_size', 'variation_swatches_image_size' ) ), $data );
                    
                    if ( empty( $attachment_id ) && $data[ 'total_attributes' ] === 1 && $data[ 'variation_image_id' ] > 0 ) {
                        $attachment_id = $data[ 'variation_image_id' ];
                    }
                    
                    return wp_get_attachment_image_src( $attachment_id, $image_size );
                }
            }
            
            public function color_attribute( $data, $attribute_type, $variation_data = array() ) {
                // Color
                if ( 'color' === $attribute_type ) {
                    
                    $term = $data[ 'item' ];
                    
                    // Global Color
                    $color = sanitize_hex_color( woo_variation_swatches()->get_frontend()->get_product_attribute_color( $term, $data ) );
                    
                    $template_format = apply_filters( 'woo_variation_swatches_color_attribute_template', '<span class="variable-item-span variable-item-span-color" style="background-color:%s;"></span>', $data, $attribute_type, $variation_data );
                    
                    return sprintf( $template_format, esc_attr( $color ) );
                }
            }
            
            public function image_attribute( $data, $attribute_type, $variation_data = array() ) {
                
                if ( 'image' === $attribute_type ) {
                    
                    $option_name = $data[ 'option_name' ];
                    
                    // Global
                    $image = $this->get_image_attribute( $data, $attribute_type, $variation_data );
                    
                    $template_format = apply_filters( 'woo_variation_swatches_image_attribute_template', '<img class="variable-item-image" aria-hidden="true" alt="%s" src="%s" width="%d" height="%d" />', $data, $attribute_type, $variation_data );
                    
                    return sprintf( $template_format, esc_attr( $option_name ), esc_url( $image[ 0 ] ), esc_attr( $image[ 1 ] ), esc_attr( $image[ 2 ] ) );
                }
            }
            
            public function button_attribute( $data, $attribute_type, $variation_data = array() ) {
                
                if ( 'button' === $attribute_type ) {
                    $option_name = $data[ 'option_name' ];
                    
                    $template_format = apply_filters( 'woo_variation_swatches_button_attribute_template', '<span class="variable-item-span variable-item-span-button">%s</span>', $data, $attribute_type, $variation_data );
                    
                    return sprintf( $template_format, esc_html( $option_name ) );
                }
            }
            
            public function radio_attribute( $data, $attribute_type, $variation_data = array() ) {
                
                if ( 'radio' === $attribute_type ) {
                    
                    $attribute_name = $data[ 'attribute_name' ];
                    $product        = $data[ 'product' ];
                    $product_id     = absint( $product->get_id() );
                    $attributes     = $product->get_variation_attributes();
                    // $attributes  = $this->get_cached_variation_attributes( $product );
                    $slug        = $data[ 'slug' ];
                    $is_selected = wc_string_to_bool( $data[ 'is_selected' ] );
                    $option_name = $data[ 'option_name' ];
                    
                    /*
                     * $get_variations       = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
                     * $available_variations = $get_variations ? $product->get_available_variations() : false;
                    */
                    
                    
                    $name            = sprintf( 'wvs_radio_%s__%d', $attribute_name, $product_id );
                    $attribute_value = $slug;
                    
                    $label          = esc_html( $option_name );
                    $label_template = apply_filters( 'woo_variation_swatches_global_item_radio_label_template', '%image% - %variation% - %price% %stock%', $data );
                    
                    if ( count( array_keys( $attributes ) ) === 1 ) {
                        
                        // $available_variations = $product->get_available_variations();
                        $available_variations = $this->get_available_variation_images( $product );
                        
                        $variation = $this->get_variation_by_attribute_name_value( $available_variations, $attribute_name, $attribute_value );
                        
                        if ( ! empty( $variation ) ) {
                            
                            $image_id = $variation[ 'variation_image_id' ];
                            
                            $image_size = apply_filters( 'woo_variation_swatches_global_product_attribute_image_size', sanitize_text_field( woo_variation_swatches()->get_option( 'attribute_image_size', 'variation_swatches_image_size' ) ), $data );
                            
                            // $image_size = sanitize_text_field( woo_variation_swatches()->get_option( 'attribute_image_size', 'variation_swatches_image_size' ) );
                            
                            $variation_image = $this->get_variation_img_src( $image_id, $image_size );
                            
                            $image = sprintf( '<img src="%1$s" title="%2$s" alt="%2$s" width="%3$s" height="%4$s" />', esc_url( $variation_image[ 'src' ] ), $label, absint( $variation_image[ 'width' ] ), absint( $variation_image[ 'height' ] ) );
                            $stock = wp_kses_post( $variation[ 'availability_html' ] );
                            $price = wp_kses_post( $variation[ 'price_html' ] );
                            $label = str_ireplace( array( '%image%', '%variation%', '%price%', '%stock%' ), array(
                                $image,
                                '<span class="variable-item-radio-value">' . esc_html( $option_name ) . '</span>',
                                $price,
                                $stock
                            ),                     $label_template );
                        }
                    }
                    
                    $template_format = apply_filters( 'woo_variation_swatches_radio_attribute_template', '<label class="variable-item-radio-input-wrapper"><input name="%1$s" class="variable-item-radio-input" %2$s type="radio" value="%3$s" data-value="%3$s" /><span class="variable-item-radio-value-wrapper">%4$s</span></label>', $data, $attribute_type, $variation_data );
                    
                    return sprintf( $template_format, $name, checked( $is_selected, true, false ), esc_attr( $slug ), $label );
                }
            }
            
            public function get_variation_gallery_img_src( $variation ) {
                
                if ( isset( $variation[ 'image' ][ 'gallery_thumbnail_src' ] ) ) {
                    return array(
                        'src'    => $variation[ 'image' ][ 'gallery_thumbnail_src' ],
                        'width'  => $variation[ 'image' ][ 'gallery_thumbnail_src_w' ],
                        'height' => $variation[ 'image' ][ 'gallery_thumbnail_src_h' ],
                    );
                } else {
                    $gallery_thumbnail      = wc_get_image_size( 'gallery_thumbnail' );
                    $gallery_thumbnail_size = apply_filters( 'woocommerce_gallery_thumbnail_size', array(
                        $gallery_thumbnail[ 'width' ],
                        $gallery_thumbnail[ 'height' ]
                    ) );
                    $placeholder_img_src    = wc_placeholder_img_src( $gallery_thumbnail_size );
                    
                    return array(
                        'src'    => $placeholder_img_src,
                        'width'  => '100',
                        'height' => '100',
                    );
                }
            }
            
            public function get_variation_img_src( $image_id, $image_size ) {
                $image = array(
                    'src'     => woo_variation_swatches()->images_url( '/placeholder.png' ),
                    'width'   => 50,
                    'height'  => 50,
                    'resized' => 0,
                );
                
                $image_data = wp_get_attachment_image_src( absint( $image_id ), $image_size );
                
                if ( $image_data ) {
                    $image = array(
                        'src'     => $image_data[ 0 ],
                        'width'   => $image_data[ 1 ],
                        'height'  => $image_data[ 2 ],
                        'resized' => $image_data[ 3 ],
                    );
                }
                
                return $image;
                
            }
            
            public function get_variation_image_id( $variation ) {
                
                $placeholder_image = get_option( 'woocommerce_placeholder_image', 0 );
                
                return empty( $variation[ 'image_id' ] ) ? $placeholder_image : $variation[ 'image_id' ];
            }
            
            // Cached version of $product->get_variation_attributes()
            public function get_cached_variation_attributes( $product ) {
                
                $product_id = $product->get_id();
                
                $cache_key   = woo_variation_swatches()->get_cache()->get_cache_key( sprintf( 'variation_attributes_of__%s', $product_id ) );
                $cache_group = 'woo_variation_swatches';
                
                if ( false === ( $attributes = wp_cache_get( $cache_key, $cache_group ) ) ) {
                    $attributes = $product->get_variation_attributes();
                    
                    wp_cache_set( $cache_key, $attributes, $cache_group );
                }
                
                return $attributes;
                
            }
            
            public function get_swatch_data( $args, $term_or_option ) {
                
                $options          = $args[ 'options' ];
                $product          = $args[ 'product' ];
                $attribute        = $args[ 'attribute' ];
                $attributes       = $product->get_variation_attributes();
                $count_attributes = count( array_keys( $attributes ) );
                
                $is_term = is_object( $term_or_option );
                
                if ( $is_term ) {
                    
                    $term        = $term_or_option;
                    $slug        = $term->slug;
                    $is_selected = ( sanitize_title( $args[ 'selected' ] ) === $term->slug );
                    $option_name = apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product );
                    
                } else {
                    $option      = $slug = $term_or_option;
                    $is_selected = ( sanitize_title( $args[ 'selected' ] ) === $args[ 'selected' ] ) ? ( $args[ 'selected' ] === sanitize_title( $option ) ) : ( $args[ 'selected' ] === $option );
                    $option_name = apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product );
                }
                
                $attribute_name  = wc_variation_attribute_name( $attribute );
                $attribute_value = $slug;
                
                $single_attribute_variation_image_id = 0;
                if ( count( array_keys( $attributes ) ) === 1 ) {
                    $available_variations = $this->get_available_variation_images( $product );
                    
                    $variation = $this->get_variation_by_attribute_name_value( $available_variations, $attribute_name, $attribute_value );
                    
                    $single_attribute_variation_image_id = empty( $variation ) ? 0 : $variation[ 'variation_image_id' ];
                }
                
                $data = array(
                    'is_archive'         => isset( $args[ 'is_archive' ] ) ? $args[ 'is_archive' ] : false,
                    'is_selected'        => $is_selected,
                    'is_term'            => $is_term,
                    'term_id'            => $is_term ? $term->term_id : woo_variation_swatches()->sanitize_name( $option ),
                    'slug'               => $slug,
                    'variation_image_id' => absint( $single_attribute_variation_image_id ),
                    'total_attributes'   => absint( $count_attributes ),
                    'option_slug'        => woo_variation_swatches()->sanitize_name( $slug ),
                    'item'               => $term_or_option,
                    'options'            => $options,
                    'option_name'        => $option_name,
                    'attribute'          => $attribute,
                    'attribute_key'      => sanitize_title( $attribute ),
                    'attribute_name'     => wc_variation_attribute_name( $attribute ),
                    'attribute_label'    => wc_attribute_label( $attribute, $product ),
                    'args'               => $args,
                    'product'            => $product,
                );
                
                return apply_filters( 'woo_variation_swatches_get_swatch_data', $data, $args, $product );
            }
            
            public function dropdown( $html, $args ) {
                
                $args = wp_parse_args( apply_filters( 'woocommerce_dropdown_variation_attribute_options_args', $args ), array(
                    'options'          => false,
                    'attribute'        => false,
                    'product'          => false,
                    'selected'         => false,
                    'name'             => '',
                    'id'               => '',
                    'class'            => '',
                    'show_option_none' => esc_html__( 'Choose an option', 'woo-variation-swatches' ),
                    'is_archive'       => false
                ) );
                
                if ( apply_filters( 'default_woo_variation_swatches_single_product_dropdown_html', false, $args, $html, $this ) ) {
                    return $html;
                }
                
                // Get selected value.
                if ( empty( $args[ 'selected' ] ) && $args[ 'attribute' ] && $args[ 'product' ] instanceof WC_Product ) {
                    $selected_key = wc_variation_attribute_name( $args[ 'attribute' ] );
                    // phpcs:disable WordPress.Security.NonceVerification.Recommended
                    //$args[ 'selected' ] = isset( $_REQUEST[ $selected_key ] ) ? wc_clean( wp_unslash( $_REQUEST[ $selected_key ] ) ) : $args[ 'product' ]->get_variation_default_attribute( $args[ 'attribute' ] );
                    // $args[ 'selected' ] = isset( $_REQUEST[ $selected_key ] ) ? wc_clean( rawurldecode( wp_unslash( $_REQUEST[ $selected_key ] ) ) ) : $args[ 'product' ]->get_variation_default_attribute( $args[ 'attribute' ] );
                    $args[ 'selected' ] = isset( $_REQUEST[ $selected_key ] ) ? woo_variation_swatches()->sanitize_name( $_REQUEST[ $selected_key ] ) : $args[ 'product' ]->get_variation_default_attribute( $args[ 'attribute' ] );
                    // phpcs:enable WordPress.Security.NonceVerification.Recommended
                }
                
                $options          = $args[ 'options' ];
                $product          = $args[ 'product' ];
                $attribute        = $args[ 'attribute' ];
                $name             = $args[ 'name' ] ? $args[ 'name' ] : wc_variation_attribute_name( $attribute );
                $id               = $args[ 'id' ] ? $args[ 'id' ] : sanitize_title( $attribute );
                $class            = $args[ 'class' ];
                $show_option_none = (bool) $args[ 'show_option_none' ];
                // $show_option_none      = true;
                $show_option_none_text = $args[ 'show_option_none' ] ? $args[ 'show_option_none' ] : esc_html__( 'Choose an option', 'woo-variation-swatches' ); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.
                
                if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
                    $attributes = $product->get_variation_attributes();
                    // $attributes = $this->get_cached_variation_attributes( $product );
                    $options = $attributes[ $attribute ];
                }
                
                
                // Default Convert to button
                $global_convert_to_button = wc_string_to_bool( woo_variation_swatches()->get_option( 'default_to_button', 'yes' ) );
                $get_attribute            = woo_variation_swatches()->get_frontend()->get_attribute_taxonomy_by_name( $attribute );
                $attribute_types          = array_keys( woo_variation_swatches()->get_backend()->attribute_types() );
                $attribute_type           = ( $get_attribute ) ? $get_attribute->attribute_type : 'select';
                $swatches_data            = array();
                
                if ( ! in_array( $attribute_type, $attribute_types ) ) {
                    return $html;
                }
                
                $select_inline_style = '';
                
                if ( $global_convert_to_button && $attribute_type === 'select' ) {
                    $attribute_type = 'button';
                }
                
                if ( $attribute_type !== 'select' ) {
                    $select_inline_style = 'style="display:none"';
                    $class               .= ' woo-variation-raw-select';
                }
                
                $html = '<select ' . $select_inline_style . ' id="' . esc_attr( $id ) . '" class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="' . esc_attr( wc_variation_attribute_name( $attribute ) ) . '" data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '">';
                $html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';
                
                
                if ( ! empty( $options ) ) {
                    if ( $product && taxonomy_exists( $attribute ) ) {
                        // Get terms if this is a taxonomy - ordered. We need the names too.
                        $terms = wc_get_product_terms( $product->get_id(), $attribute, array(
                            'fields' => 'all',
                        ) );
                        
                        foreach ( $terms as $term ) {
                            if ( in_array( $term->slug, $options, true ) ) {
                                
                                $swatches_data[] = $this->get_swatch_data( $args, $term );
                                
                                $html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args[ 'selected' ] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product ) ) . '</option>';
                            }
                        }
                    } else {
                        foreach ( $options as $option ) {
                            // This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
                            $selected = sanitize_title( $args[ 'selected' ] ) === $args[ 'selected' ] ? selected( $args[ 'selected' ], sanitize_title( $option ), false ) : selected( $args[ 'selected' ], $option, false );
                            
                            $swatches_data[] = $this->get_swatch_data( $args, $option );
                            
                            $html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product ) ) . '</option>';
                        }
                    }
                }
                
                $html .= '</select>';
                
                if ( $attribute_type === 'select' ) {
                    return $html;
                }
                
                // Start Swatches
                
                $item        = '';
                $wrapper     = '';
                $wrapper_end = '';
                
                if ( ! empty( $options ) && ! empty( $swatches_data ) && $product ) {
                    
                    $wrapper = $this->wrapper_start( $args, $attribute, $product, $attribute_type, $options );
                    
                    $__attribute_type = $attribute_type;
                    
                    foreach ( $swatches_data as $data ) {
                        
                        // If attribute have no image we should convert attribute type image to attribute type button
                        
                        $attribute_type = $__attribute_type;
                        if ( 'image' === $attribute_type && ! is_array( $this->get_image_attribute( $data, $attribute_type ) ) ) {
                            $attribute_type = 'button';
                        }
                        
                        // If 3rd party plugin wants to remove some attribute from list
                        if ( apply_filters( 'woo_variation_swatches_remove_attribute_item', false, $data, $attribute_type ) ) {
                            continue;
                        }
                        
                        $item .= $this->item_start( $data, $attribute_type );
                        
                        $item .= $this->color_attribute( $data, $attribute_type );
                        $item .= $this->image_attribute( $data, $attribute_type );
                        $item .= $this->button_attribute( $data, $attribute_type );
                        $item .= $this->radio_attribute( $data, $attribute_type );
                        
                        $item .= $this->item_end();
                    }
                    
                    $wrapper_end = $this->wrapper_end();
                }
                
                // End Swatches
                $html .= $wrapper . $item . $wrapper_end;
                
                return apply_filters( 'woo_variation_swatches_html', $html, $args, $swatches_data, $this );
            }
        }
    }