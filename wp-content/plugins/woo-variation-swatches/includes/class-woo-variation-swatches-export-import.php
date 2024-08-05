<?php

defined( 'ABSPATH' ) or die( 'Keep Quit' );

if ( ! class_exists( 'Woo_Variation_Swatches_Export_Import' ) ):

	class Woo_Variation_Swatches_Export_Import {

		private $export_type = 'product';
		private $column_id = 'attributes_type';
		protected static $_instance = null;

		protected function __construct() {
			$this->export_hooks();
			$this->import_hooks();
			do_action( 'woo_variation_swatches_export_import_loaded', $this );
		}

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		public function export_hooks() {
			// EXPORT
			// "woocommerce_{$this->export_type}_export_column_names"
			//add_filter( 'woocommerce_product_export_column_names', 'add_woo_variation_gallery_export_column' );

			add_filter( "woocommerce_product_export_{$this->export_type}_default_columns", array(
				$this,
				'export_column_name'
			) );
			add_filter( "woocommerce_product_export_{$this->export_type}_column_{$this->column_id}", array(
				$this,
				'export_column_data'
			), 10, 3 );

			// add_filter( 'woocommerce_product_export_row_data', array( $this, 'prepare_attributes_for_export' ), 10, 3 );

		}

		public function import_hooks() {
			// IMPORT
			add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'import_column_name' ) );
			add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array(
				$this,
				'default_import_column_name'
			) );
			add_action( 'woocommerce_product_import_inserted_product_object', array(
				$this,
				'process_wc_import'
			), 10, 2 );

		}

		// Export Function
		public function prepare_attributes_for_export( $row, $product ) {
			return $row;
		}

		public function export_column_name( $columns ) {

			// column slug => column name
			$columns[ $this->column_id ] = 'Swatches Attributes';

			return $columns;
		}

		public function export_column_data( $value, $product, $column_id ) {

			$attributes = $product->get_attributes();

			$types = array();

			if ( count( $attributes ) ) {
				foreach ( $attributes as $attribute_name => $attribute ) {

					if ( is_a( $attribute, 'WC_Product_Attribute' ) ) {

						if ( $attribute->is_taxonomy() ) {
							$attr  = wc_get_attribute( $attribute->get_id() );
							$name  = wc_attribute_label( $attribute->get_name(), $product );
							$terms = $attribute->get_terms();

							if ( ! in_array( $name, $types ) && $attr->type !== 'select' ) {
								$types[ $name ]          = array();
								$types[ $name ]['name']  = $name;
								$types[ $name ]['type']  = $attr->type;
								$types[ $name ]['terms'] = array();

								foreach ( $terms as $term ) {
									$types[ $name ]['terms'][ $term->name ]          = array();
									$types[ $name ]['terms'][ $term->name ]['name']  = $term->name;
									$types[ $name ]['terms'][ $term->name ]['color'] = sanitize_hex_color( get_term_meta( $term->term_id, 'product_attribute_color', true ) );

									$term_image_id = get_term_meta( $term->term_id, 'product_attribute_image', true );

									$types[ $name ]['terms'][ $term->name ]['image'] = $term_image_id ? wp_get_attachment_image_url( $term_image_id, 'full' ) : '';


									$types[ $name ]['terms'][ $term->name ]['show_tooltip'] = get_term_meta( $term->term_id, 'show_tooltip', true );
									$types[ $name ]['terms'][ $term->name ]['tooltip_text'] = get_term_meta( $term->term_id, 'tooltip_text', true );

									$tooltip_image_id = get_term_meta( $term->term_id, 'tooltip_image_id', true );

									$types[ $name ]['terms'][ $term->name ]['tooltip_image'] = $tooltip_image_id ? wp_get_attachment_image_url( $tooltip_image_id, 'full' ) : '';
									$types[ $name ]['terms'][ $term->name ]['image_size']    = get_term_meta( $term->term_id, 'product_attribute_image', true );

								}
							}
						}
					}
				}
			}

			return $types ? wp_json_encode( $types ) : '';
		}

		// Import Function
		public function import_column_name( $columns ) {
			// column slug => column name
			$columns[ $this->column_id ] = 'Swatches Attributes';

			return $columns;
		}

		public function default_import_column_name( $columns ) {
			// potential column name => column slug
			$columns[ esc_html__( 'Swatches Attributes', 'woo-variation-swatches' ) ] = $this->column_id;

			return $columns;
		}

		public function process_wc_import( $product, $data ) {

			$product_id = $product->get_id();

			if ( isset( $data[ $this->column_id ] ) && ! empty( $data[ $this->column_id ] ) ) {

				$raw_data = (array) json_decode( $data[ $this->column_id ], true );

				$done_taxonomy = array();
				$done_terms    = array();

				foreach ( $raw_data as $attr_name => $attr ) {
					$id       = wc_attribute_taxonomy_id_by_name( $attr_name );
					$taxonomy = wc_attribute_taxonomy_name( $attr_name );


					if ( in_array( $id, $done_taxonomy ) ) {
						continue;
					}

					if ( $id ) {
						array_push( $done_taxonomy, $id );

						wc_update_attribute( $id, array( 'type' => $attr['type'] ) );

						foreach ( $attr['terms'] as $term_name => $term_data ) {

							$term = get_term_by( 'name', $term_name, $taxonomy );

							if ( in_array( $id, $done_terms ) ) {
								continue;
							}

							if ( $term ) {
								array_push( $done_terms, $term->term_id );

								$color = ! empty( $term_data['color'] ) ? sanitize_hex_color( $term_data['color'] ) : '';
								update_term_meta( $term->term_id, 'product_attribute_color', $color );

								$image_id = ! empty( $term_data['image'] ) ? $this->get_attachment_id_from_url( $term_data['image'], 0 ) : '';
								update_term_meta( $term->term_id, 'product_attribute_image', $image_id );
							}
						}
					}
				}
			}
		}

		public function get_attachment_id_from_url( $url, $product_id ) {
			if ( empty( $url ) ) {
				return 0;
			}

			$id         = 0;
			$upload_dir = wp_upload_dir( null, false );
			$base_url   = $upload_dir['baseurl'] . '/';

			// Check first if attachment is inside the WordPress uploads directory, or we're given a filename only.
			if ( false !== strpos( $url, $base_url ) || false === strpos( $url, '://' ) ) {
				// Search for yyyy/mm/slug.extension or slug.extension - remove the base URL.
				$file = str_replace( $base_url, '', $url );
				$args = array(
					'post_type'   => 'attachment',
					'post_status' => 'any',
					'fields'      => 'ids',
					'meta_query'  => array( // @codingStandardsIgnoreLine.
						'relation' => 'OR',
						array(
							'key'     => '_wp_attached_file',
							'value'   => '^' . $file,
							'compare' => 'REGEXP',
						),
						array(
							'key'     => '_wp_attached_file',
							'value'   => '/' . $file,
							'compare' => 'LIKE',
						),
						array(
							'key'     => '_wc_attachment_source',
							'value'   => '/' . $file,
							'compare' => 'LIKE',
						),
					),
				);
			} else {
				// This is an external URL, so compare to source.
				$args = array(
					'post_type'   => 'attachment',
					'post_status' => 'any',
					'fields'      => 'ids',
					'meta_query'  => array( // @codingStandardsIgnoreLine.
						array(
							'value' => $url,
							'key'   => '_wc_attachment_source',
						),
					),
				);
			}

			$ids = get_posts( $args ); // @codingStandardsIgnoreLine.

			if ( $ids ) {
				$id = current( $ids );
			}

			// Upload if attachment does not exists.
			if ( ! $id && stristr( $url, '://' ) ) {
				$upload = wc_rest_upload_image_from_url( $url );

				if ( is_wp_error( $upload ) ) {
					throw new Exception( $upload->get_error_message(), 400 );
				}

				$id = wc_rest_set_uploaded_image_as_attachment( $upload, $product_id );

				if ( ! wp_attachment_is_image( $id ) ) {
					/* translators: %s: image URL */
					throw new Exception( sprintf( __( 'Not able to attach "%s".', 'woo-variation-swatches' ), $url ), 400 );
				}

				// Save attachment source for future reference.
				update_post_meta( $id, '_wc_attachment_source', $url );
			}

			if ( ! $id ) {
				/* translators: %s: image URL */
				throw new Exception( sprintf( __( 'Unable to use image "%s".', 'woo-variation-swatches' ), $url ), 400 );
			}

			return $id;
		}
	}
endif;