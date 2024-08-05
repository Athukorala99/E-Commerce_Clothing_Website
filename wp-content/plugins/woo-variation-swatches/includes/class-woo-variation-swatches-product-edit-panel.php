<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Woo_Variation_Swatches_Product_Edit_Panel' ) ) :
	class Woo_Variation_Swatches_Product_Edit_Panel {
		protected static $_instance = null;

		protected function __construct() {
			$this->hooks();
			do_action( 'woo_variation_swatches_product_edit_panel_loaded', $this );
		}

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		protected function hooks() {
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'product_data_tab' ) );
			add_filter( 'woocommerce_product_data_panels', array( $this, 'product_data_panel' ) );
		}

		public function product_data_tab( $tabs ) {

			if ( apply_filters( 'woo_variation_swatches_product_data_tab', true ) ) {

				$tabs['woo_variation_swatches'] = array(
					'label'    => esc_html__( 'Swatches Settings', 'woo-variation-swatches-pro' ),
					'target'   => 'woo_variation_swatches_variation_product_options',
					'class'    => array( 'show_if_variable', 'variations_tab', 'pro-inactive' ),
					'priority' => 65,
				);
			}

			return $tabs;
		}

		public function product_data_panel() {
			global $post, $wpdb, $product_object;

			$product_id = $product_object->get_id();

			if ( ! $product_object->is_type( 'variable' ) ) {
				return;
			}

			include dirname( __FILE__ ) . '/html-product-settings-panel.php';
		}
	}

endif;