<?php
    
    defined( 'ABSPATH' ) || exit;
    
    if ( ! class_exists( 'Woo_Variation_Swatches_WC_API_Response' ) ) {
        
        class Woo_Variation_Swatches_WC_API_Response {
            
            protected static $_instance = null;
            
            public static function instance() {
                if ( is_null( self::$_instance ) ) {
                    self::$_instance = new self();
                }
                
                return self::$_instance;
            }
            
            protected function __construct() {
                $this->hooks();
                do_action( 'woo_variation_swatches_wc_api_response_loaded', $this );
            }
            
            public function hooks() {
                // Thanks: https://github.com/woocommerce/woocommerce-rest-api/issues/80#issuecomment-574650905
                add_action( 'rest_api_init', array( $this, 'register_woo_variation_swatches_field' ) );
            }
            
            public function register_woo_variation_swatches_field() {
                register_rest_field( 'product_attribute_term', 'woo_variation_swatches', array(
                    'get_callback'    => array( $this, 'get_additional_response' ),
                    'update_callback' => array( $this, 'update_additional_response' ),
                    'schema'          => array( $this, 'additional_response_schema' ),
                ) );
            }
            
            public function get_additional_response( $object, $field_name, $request ) {
                
                $attribute_id = absint( $request->get_param( 'attribute_id' ) );
                $term_id      = absint( $object[ 'id' ] );
                
                $attribute = woo_variation_swatches()->get_frontend()->get_attribute_taxonomy_by_id( $attribute_id );
                
                $attribute_type   = sanitize_text_field( $attribute->attribute_type );
                $primary_color    = sanitize_hex_color( get_term_meta( $term_id, 'product_attribute_color', true ) );
                $secondary_color  = sanitize_hex_color( get_term_meta( $term_id, 'secondary_color', true ) );
                $is_dual_color    = wc_string_to_bool( get_term_meta( $term_id, 'is_dual_color', true ) );
                $dual_color_angle = woo_variation_swatches()->get_frontend()->get_dual_color_gradient_angle();
                
                $image_id = absint( get_term_meta( $term_id, 'product_attribute_image', true ) );
                
                $image_size = sanitize_text_field( woo_variation_swatches()->get_option( 'attribute_image_size', 'variation_swatches_image_size' ) );
                
                $show_tooltip     = sanitize_text_field( get_term_meta( $term_id, 'show_tooltip', true ) );
                $tooltip_text     = sanitize_text_field( get_term_meta( $term_id, 'tooltip_text', true ) );
                $tooltip_image_id = absint( get_term_meta( $term_id, 'tooltip_image_id', true ) );
                
                $group      = '';
                $group_name = '';
                if ( woo_variation_swatches()->is_pro() ) {
                    $group      = sanitize_text_field( get_term_meta( $term_id, 'group_name', true ) );
                    $group_name = sanitize_text_field( woo_variation_swatches_pro()->get_backend()->get_group()->get( $group ) );
                }
                
                
                $data = array(
                    'attribute_type' => $attribute_type,
                    
                    'primary_color'    => $primary_color,
                    'secondary_color'  => $secondary_color,
                    'is_dual_color'    => $is_dual_color,
                    'dual_color_angle' => $dual_color_angle,
                    
                    'image_id'   => $image_id,
                    'image_size' => $image_size,
                    
                    'group'      => $group,
                    'group_name' => $group_name,
                    
                    'tooltip'          => $show_tooltip,
                    'tooltip_text'     => $tooltip_text,
                    'tooltip_image_id' => $tooltip_image_id,
                );
                
                return apply_filters( 'woo_variation_swatches_rest_attribute_term_additional_response', $data, $term_id, $attribute );
            }
            
            
            /**
             *
             * PUT Request: wp-json/wc/v3/products/attributes/<attribute_id>/terms/<id>
             *     DATA: JSON Object
             * {
             * "woo_variation_swatches": {
             *     "product_attribute_color": "" || "primary_color": ""
             *     "secondary_color": ""
             *     "is_dual_color": ""
             *     "product_attribute_image": "" || "image_id": ""
             * }
             * }
             */
            
            public function update_additional_response( $values, $WP_Term_object, $field_id ) {
                
                if ( empty( $values ) || ! is_array( $values ) || 'woo_variation_swatches' !== $field_id ) {
                    return;
                }
                
                
                $term_id = absint( $WP_Term_object->term_id );
                
                
                // Update Primary Color
                if ( array_key_exists( 'product_attribute_color', $values ) || array_key_exists( 'primary_color', $values ) ) {
                    $primary_color = ! empty( $values[ 'product_attribute_color' ] ) ? $values[ 'product_attribute_color' ] : $values[ 'primary_color' ];
                    update_term_meta( $term_id, 'product_attribute_color', sanitize_hex_color( $primary_color ) );
                }
                
                // Update Secondary Color
                if ( array_key_exists( 'secondary_color', $values ) ) {
                    $secondary_color = sanitize_hex_color( $values[ 'secondary_color' ] );
                    update_term_meta( $term_id, 'secondary_color', $secondary_color );
                }
                
                // Update Is Dual Color
                if ( array_key_exists( 'is_dual_color', $values ) ) {
                    $is_dual_color = wc_string_to_bool( $values[ 'is_dual_color' ] );
                    update_term_meta( $term_id, 'is_dual_color', $is_dual_color );
                }
                
                // Update Image ID
                if ( array_key_exists( 'product_attribute_image', $values ) || array_key_exists( 'image_id', $values ) ) {
                    $image_id = ! empty( $values[ 'product_attribute_image' ] ) ? $values[ 'product_attribute_image' ] : $values[ 'image_id' ];
                    update_term_meta( $term_id, 'product_attribute_image', absint( $image_id ) );
                }
                
                
                do_action( 'woo_variation_swatches_rest_attribute_term_additional_request', $values, $term_id, $WP_Term_object );
                
                return null;
            }
            
            public function additional_response_schema() {
                return null;
            }
        }
    }