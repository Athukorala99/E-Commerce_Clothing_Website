<?php
    defined( 'ABSPATH' ) || exit;
    
    if ( ! class_exists( 'Woo_Variation_Swatches_Blocks' ) ) {
        class Woo_Variation_Swatches_Blocks {
            
            protected static $_instance = null;
            
            protected function __construct() {
                $this->includes();
                $this->hooks();
                $this->init();
                do_action( 'woo_variation_swatches_blocks_loaded', $this );
            }
            
            public static function instance() {
                if ( is_null( self::$_instance ) ) {
                    self::$_instance = new self();
                }
                
                return self::$_instance;
            }
            
            protected function hooks() {
            }
            
            protected function includes() {
            }
            
            protected function init() {
            }
        }
    }