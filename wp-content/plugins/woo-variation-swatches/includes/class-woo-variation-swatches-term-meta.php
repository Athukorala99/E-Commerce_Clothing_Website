<?php

defined( 'ABSPATH' ) or die( 'Keep Silent' );

if ( ! class_exists( 'Woo_Variation_Swatches_Term_Meta' ) ):
	class Woo_Variation_Swatches_Term_Meta {

		private $taxonomy;
		private $post_type;
		private $fields = array();

		public function __construct( $taxonomy, $post_type, $fields = array() ) {

			$this->taxonomy  = $taxonomy;
			$this->post_type = $post_type;
			$this->fields    = $fields;

			// Category/term ordering
			// add_action( 'create_term', array( $this, 'create_term' ), 5, 3 );

			add_action( 'delete_term', array( $this, 'delete_term' ), 5, 4 );

			// Add form
			add_action( "{$this->taxonomy}_add_form_fields", array( $this, 'add' ) );
			add_action( "{$this->taxonomy}_edit_form_fields", array( $this, 'edit' ), 10 );
			add_action( "created_term", array( $this, 'save' ), 10, 3 );
			add_action( "edit_term", array( $this, 'save' ), 10, 3 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Add columns
			add_filter( "manage_edit-{$this->taxonomy}_columns", array( $this, 'taxonomy_columns' ) );
			add_filter( "manage_{$this->taxonomy}_custom_column", array( $this, 'taxonomy_column_preview' ), 10, 3 );
			add_filter( "manage_{$this->taxonomy}_custom_column", array( $this, 'taxonomy_column_group' ), 10, 3 );

			do_action( 'woo_variation_swatches_term_meta_loaded', $this );
		}

		public function preview( $attribute_type, $term_id, $fields ) {

			$meta_key = $fields[0]['id']; // take first key for preview

			$this->color_preview( $attribute_type, $term_id, $meta_key );
			$this->image_preview( $attribute_type, $term_id, $meta_key );

		}

		public function color_preview( $attribute_type, $term_id, $key ) {

			if ( 'color' === $attribute_type ) {
				$primary_color = sanitize_hex_color( get_term_meta( $term_id, $key, true ) );

				$is_dual_color   = wc_string_to_bool( get_term_meta( $term_id, 'is_dual_color', true ) );
				$secondary_color = sanitize_hex_color( get_term_meta( $term_id, 'secondary_color', true ) );

				if ( $is_dual_color && woo_variation_swatches()->is_pro() ) {
					$angle = woo_variation_swatches()->get_frontend()->get_dual_color_gradient_angle();
					printf( '<div class="wvs-preview wvs-color-preview wvs-dual-color-preview" style="background: linear-gradient(%3$s, %1$s 0%%, %1$s 50%%, %2$s 50%%, %2$s 100%%);"></div>', esc_attr( $secondary_color ), esc_attr( $primary_color ), esc_attr( $angle ) );
				} else {
					printf( '<div class="wvs-preview wvs-color-preview" style="background-color:%s;"></div>', esc_attr( $primary_color ) );
				}
			}
		}

		public function group_name( $attribute_type, $term_id ) {

			if ( ! woo_variation_swatches()->is_pro() ) {
				return '';
			}

			$group = sanitize_text_field( get_term_meta( $term_id, 'group_name', true ) );
			if ( $group ) {
				return sanitize_text_field( woo_variation_swatches()->get_backend()->get_group()->get( $group ) );
			}

			return '';
		}

		public function image_preview( $attribute_type, $term_id, $key ) {
			if ( 'image' == $attribute_type ) {
				$attachment_id = absint( get_term_meta( $term_id, $key, true ) );
				$image         = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );

				if ( is_array( $image ) ) {
					printf( '<img src="%s" alt="" width="%d" height="%d" class="wvs-preview wvs-image-preview" />', esc_url( $image[0] ), $image[1], $image[2] );
				}
			}
		}

		public function taxonomy_columns( $columns ) {
			$new_columns = array();

			if ( isset( $columns['cb'] ) ) {
				$new_columns['cb'] = $columns['cb'];
			}

			$new_columns['wvs-meta-preview'] = '';

			if ( isset( $columns['cb'] ) ) {
				unset( $columns['cb'] );
			}

			if ( woo_variation_swatches()->is_pro() ) {
				$columns['wvs-meta-group'] = esc_html__( 'Group', 'woo-variation-swatches' );
			}

			return array_merge( $new_columns, $columns );
		}

		public function taxonomy_column_preview( $columns, $column, $term_id ) {

			if ( 'wvs-meta-preview' !== $column ) {
				return $columns;
			}

			$attribute      = woo_variation_swatches()->get_backend()->get_attribute_taxonomy( $this->taxonomy );
			$attribute_type = $attribute->attribute_type;
			$this->preview( $attribute_type, $term_id, $this->fields );

			return $columns;
		}

		public function taxonomy_column_group( $columns, $column, $term_id ) {

			if ( 'wvs-meta-group' !== $column ) {
				return $columns;
			}

			$attribute = woo_variation_swatches()->get_backend()->get_attribute_taxonomy( $this->taxonomy );

			$attribute_type = $attribute->attribute_type;

			echo $this->group_name( $attribute_type, $term_id );

			return $columns;
		}

		public function delete_term( $term_id, $tt_id, $taxonomy, $deleted_term ) {
			global $wpdb;

			$term_id = absint( $term_id );
			if ( $term_id and $taxonomy == $this->taxonomy ) {
				$wpdb->delete( $wpdb->termmeta, array( 'term_id' => $term_id ), array( '%d' ) );
			}
		}

		public function enqueue_scripts() {
			wp_enqueue_media();
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}

		public function save( $term_id, $tt_id = '', $taxonomy = '' ) {

			if ( $taxonomy == $this->taxonomy ) {
				foreach ( $this->fields as $field ) {
					foreach ( $_POST as $post_key => $post_value ) {
						if ( $field['id'] == $post_key ) {
							switch ( $field['type'] ) {
								case 'text':
								case 'color':
									$post_value = esc_html( $post_value );
									break;
								case 'url':
									$post_value = esc_url( $post_value );
									break;
								case 'image':
									$post_value = absint( $post_value );
									break;
								case 'textarea':
									$post_value = esc_textarea( $post_value );
									break;
								case 'editor':
									$post_value = wp_kses_post( $post_value );
									break;
								case 'select':
								case 'select2':
									$post_value = sanitize_key( $post_value );
									break;
								case 'checkbox':
									$post_value = sanitize_key( $post_value );
									break;
								default:
									do_action( 'woo_variation_swatches_save_term_meta', $term_id, $field, $post_value, $taxonomy );
									break;
							}
							update_term_meta( $term_id, $field['id'], $post_value );
						}
					}
				}
				do_action( 'woo_variation_swatches_after_term_meta_saved', $term_id, $taxonomy );
			}
		}

		public function add() {
			$this->generate_fields();
		}

		private function generate_fields( $term = false ) {

			$screen = get_current_screen();

			if ( ( $screen->post_type == $this->post_type ) and ( $screen->taxonomy == $this->taxonomy ) ) {
				self::generate_form_fields( $this->fields, $term );
			}
		}

		public static function generate_form_fields( $fields, $term ) {

			$fields = apply_filters( 'woo_variation_swatches_term_meta_fields', $fields, $term );

			if ( empty( $fields ) ) {
				return;
			}

			foreach ( $fields as $field ) {

				$field = apply_filters( 'woo_variation_swatches_term_meta_field', $field, $term );

				$field['id'] = esc_html( $field['id'] );

				if ( ! $term ) {
					$field['value'] = isset( $field['default'] ) ? $field['default'] : '';
				} else {
					$field['value'] = get_term_meta( $term->term_id, $field['id'], true );
				}


				$field['size']        = isset( $field['size'] ) ? $field['size'] : '40';
				$field['required']    = ( isset( $field['required'] ) and $field['required'] == true ) ? ' aria-required="true"' : '';
				$field['placeholder'] = ( isset( $field['placeholder'] ) ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '" data-placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
				$field['desc']        = ( isset( $field['desc'] ) ) ? $field['desc'] : '';

				$field['dependency']       = ( isset( $field['dependency'] ) ) ? $field['dependency'] : array();

				self::field_start( $field, $term );
				switch ( $field['type'] ) {
					case 'text':
					case 'url':
						ob_start();
						?>
						<input name="<?php echo esc_attr( $field['id'] ) ?>" id="<?php echo esc_attr( $field['id'] ) ?>"
							   type="<?php echo esc_attr( $field['type'] ) ?>"
							   value="<?php echo esc_attr( $field['value'] ) ?>"
							   size="<?php echo esc_attr( $field['size'] ) ?>" <?php echo $field['required'] . $field['placeholder'] ?>>
						<?php
						echo ob_get_clean();
						break;
					case 'color':
						ob_start();
						?>
						<input name="<?php echo esc_attr( $field['id'] ) ?>" id="<?php echo esc_attr( $field['id'] ) ?>" type="text"
							   class="wvs-color-picker" value="<?php echo esc_attr( $field['value'] ) ?>"
							   data-default-color="<?php echo esc_attr( $field['value'] ) ?>"
							   size="<?php echo esc_attr( $field['size'] ) ?>" <?php echo $field['required'] . $field['placeholder'] ?>>
						<?php
						echo ob_get_clean();
						break;
					case 'textarea':
						ob_start();
						?>
						<textarea name="<?php echo esc_attr( $field['id'] ) ?>" id="<?php echo esc_attr( $field['id'] ) ?>" rows="5"
								  cols="<?php echo esc_attr( $field['size'] ) ?>" <?php echo $field['required'] . $field['placeholder'] ?>><?php echo esc_textarea( $field['value'] ) ?></textarea>
						<?php
						echo ob_get_clean();
						break;
					case 'editor':
						$field['settings'] = isset( $field['settings'] )
							? $field['settings']
							: array(
								'textarea_rows' => 8,
								'quicktags'     => false,
								'media_buttons' => false
							);
						ob_start();
						wp_editor( $field['value'], $field['id'], $field['settings'] );
						echo ob_get_clean();
						break;
					case 'select':
					case 'select2':

						$field['options'] = isset( $field['options'] ) ? $field['options'] : array();
						$field['multiple'] = isset( $field['multiple'] ) ? ' multiple="multiple"' : '';
						$css_class         = ( $field['type'] == 'select2' ) ? 'wc-enhanced-select' : '';

						ob_start();
						?>
						<select name="<?php echo esc_attr( $field['id'] ) ?>" id="<?php echo esc_attr( $field['id'] ) ?>"
								class="<?php echo esc_attr( $css_class ) ?>" <?php echo $field['multiple'] ?>>
							<?php
							foreach ( $field['options'] as $key => $option ) {
								echo '<option' . selected( $field['value'], $key, false ) . ' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
							}
							?>
						</select>
						<?php
						echo ob_get_clean();
						break;
					case 'image':
						ob_start();
						?>
						<div class="meta-image-field-wrapper">
							<div class="image-preview">
								<img data-placeholder="<?php echo esc_url( self::placeholder_img_src() ); ?>"
									 src="<?php echo esc_url( self::get_img_src( $field['value'] ) ); ?>"
									 width="60px" height="60px" />
							</div>
							<div class="button-wrapper">
								<input type="hidden" id="<?php echo esc_attr( $field['id'] ) ?>"
									   name="<?php echo esc_attr( $field['id'] ) ?>"
									   value="<?php echo esc_attr( $field['value'] ) ?>" />
								<button type="button"
										class="wvs_upload_image_button button button-primary button-small"><?php esc_html_e( 'Upload / Add image', 'woo-variation-swatches' ); ?></button>
								<button type="button"
										style="<?php echo( empty( $field['value'] ) ? 'display:none' : '' ) ?>"
										class="wvs_remove_image_button button button-danger button-small"><?php esc_html_e( 'Remove image', 'woo-variation-swatches' ); ?></button>
							</div>
						</div>
						<?php
						echo ob_get_clean();
						break;
					case 'checkbox':

						ob_start();
						?>
						<label for="<?php echo esc_attr( $field['id'] ) ?>">

							<input name="<?php echo esc_attr( $field['id'] ) ?>" id="<?php echo esc_attr( $field['id'] ) ?>"
								<?php checked( $field['value'], 'yes' ) ?>
								   type="<?php echo esc_attr( $field['type'] ) ?>"
								   value="yes" <?php echo $field['required'] . $field['placeholder'] ?>>

							<?php echo esc_html( $field['label'] ) ?></label>
						<?php
						echo ob_get_clean();
						break;
					default:
						do_action( 'woo_variation_swatches_term_meta_field', $field, $term );
						break;

				}
				self::field_end( $field, $term );

			}
		}

		private static function field_start( $field, $term ) {
			// Example:
			// http://emranahmed.github.io/Form-Field-Dependency/
			/*'dependency' => array(
				array( '#show_tooltip' => array( 'type' => 'equal', 'value' => 'yes' ) )
			)*/

			$depends = empty( $field['dependency'] ) ? '' : "data-gwp_dependency='" . wc_esc_json( wp_json_encode( $field['dependency'] ) ) . "'";

			ob_start();
			if ( ! $term ) {
				?>
				<div <?php echo $depends ?> class="form-field <?php echo esc_attr( $field['id'] ) ?> <?php echo empty( $field['required'] ) ? '' : 'form-required' ?>">
				<?php if ( $field['type'] !== 'checkbox' ) { ?>
					<label for="<?php echo esc_attr( $field['id'] ) ?>"><?php echo $field['label'] ?></label>
					<?php
				}
			} else {
				?>
				<tr <?php echo $depends ?> class="form-field  <?php echo esc_attr( $field['id'] ) ?> <?php echo empty( $field['required'] ) ? '' : 'form-required' ?>">
				<th scope="row">
					<label for="<?php echo esc_attr( $field['id'] ) ?>"><?php echo $field['label'] ?></label>
				</th>
				<td>
				<?php
			}
			echo ob_get_clean();
		}

		private static function get_img_src( $thumbnail_id = false ) {
			if ( ! empty( $thumbnail_id ) ) {
				$image = wp_get_attachment_thumb_url( $thumbnail_id );
			} else {
				$image = self::placeholder_img_src();
			}

			return $image;
		}

		public static function placeholder_img_src() {
			return woo_variation_swatches()->images_url( '/placeholder.png' );
		}

		private static function field_end( $field, $term ) {

			ob_start();
			if ( ! $term ) {
				?>
				<p><?php echo wp_kses_post( $field['desc'] ) ?></p>
				</div>
				<?php
			} else {
				?>
				<p class="description"><?php echo wp_kses_post( $field['desc'] ) ?></p></td>
				</tr>
				<?php
			}
			echo ob_get_clean();
		}

		public function edit( $term ) {
			$this->generate_fields( $term );
		}
	}
endif;
