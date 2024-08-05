<?php

    defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'GetWooPlugins_Admin_Settings', false ) ) :

	class GetWooPlugins_Admin_Settings {

		/**
		 * Setting pages.
		 *
		 * @var array
		 */
		private static $settings = array();

		/**
		 * Error messages.
		 *
		 * @var array
		 */
		private static $errors = array();

		/**
		 * Update messages.
		 *
		 * @var array
		 */
		private static $messages = array();

		/**
		 * Notice messages.
		 *
		 * @var array
		 */
		private static $notices = array();

		/**
		 * Include the settings page classes.
		 */
		public static function get_settings_pages() {
			if ( empty( self::$settings ) ) {
				$settings = array();

				include_once dirname( __FILE__ ) . '/class-getwooplugins-settings-page.php';
    
				self::$settings = apply_filters( 'getwooplugins_get_settings_pages', $settings );
			}

			return self::$settings;
		}

		/**
		 * Save the settings.
		 */
		public static function save() {
			global $current_tab;
   
			check_admin_referer( 'getwooplugins-settings' );

			// Trigger actions.
			do_action( 'getwooplugins_settings_save', $current_tab );
			do_action( 'getwooplugins_update_options', $current_tab );
			do_action( 'getwooplugins_update_options' );

			self::add_message( esc_html__( 'Your settings have been saved.', 'woo-variation-swatches' ) );

			do_action( 'getwooplugins_settings_saved' );
		}
  
		public static function action($current_tab, $current_section, $current_action ) {
			// Trigger actions.
			do_action( 'getwooplugins_settings_action', $current_tab, $current_section, $current_action );
		}

		/**
		 * Add a message.
		 *
		 * @param string $text Message.
		 */
		public static function add_message( $text ) {
			self::$messages[] = $text;
		}

		/**
		 * Add an error.
		 *
		 * @param string $text Message.
		 */
		public static function add_error( $text ) {
			self::$errors[] = $text;
		}

		public static function add_notice( $text ) {
			self::$notices[] = $text;
		}


		/**
		 * Output messages + errors.
		 */
		public static function show_messages() {

			if ( count( self::$notices ) > 0 ) {
				foreach ( self::$notices as $notice ) {
					echo '<div class="notice inline"><p><strong>' . esc_html( $notice ) . '</strong></p></div>';
				}
			}

			if ( count( self::$errors ) > 0 ) {
				foreach ( self::$errors as $error ) {
					echo '<div class="error inline"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
				}
			} elseif ( count( self::$messages ) > 0 ) {
				foreach ( self::$messages as $message ) {
					echo '<div class="updated inline"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
				}
			}
		}

		/**
		 * Settings page.
		 *
		 * Handles the display of the main settings page in admin.
		 */
		public static function output() {
			global $current_section, $current_tab;

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			do_action( 'getwooplugins_settings_start' );

			// Get tabs for the settings page.
			$tabs = apply_filters( 'getwooplugins_settings_tabs_array', array() );

			include dirname( __FILE__ ) . '/html/settings-page.php';
		}

		/**
		 * Get a setting from the settings API.
		 *
		 * @param string $option_name Option name.
		 * @param mixed  $default     Default value.
		 *
		 * @return mixed
		 */
		public static function get_option( $option_name, $default = '' ) {
			if ( ! $option_name ) {
				return $default;
			}

			// Array value.
			if ( strstr( $option_name, '[' ) ) {

				parse_str( $option_name, $option_array );

				// Option name is first key.
				$option_name = current( array_keys( $option_array ) );

				// Get value.
				$option_values = get_option( $option_name, '' );

				$key = key( $option_array[ $option_name ] );

				if ( isset( $option_values[ $key ] ) ) {
					$option_value = $option_values[ $key ];
				} else {
					$option_value = null;
				}
			} else {
				// Single value.
				$option_value = get_option( $option_name, null );
			}

			if ( is_array( $option_value ) ) {
				$option_value = wp_unslash( $option_value );
			} elseif ( ! is_null( $option_value ) ) {
				$option_value = stripslashes( $option_value );
			}

			return ( null === $option_value ) ? $default : $option_value;
		}

		/**
		 * Output admin fields.
		 *
		 * Loops through the woocommerce options array and outputs each field.
		 *
		 * @param array[] $options Opens array to output.
		 */
        
        public static function normalize_id($id){
            return  str_ireplace(array('[', ']'),array('_', ''),$id);
        }
        
        /**
        * Escape JSON for use on HTML or attribute text nodes.
        *
        * @param string $json JSON to escape.
        * @param bool   $html True if escaping for HTML text node, false for attributes. Determines how quotes are handled.
        *
        * @return string Escaped JSON.
        */
        public static function esc_json( $json, $html = false ) {
            return _wp_specialchars(
                $json,
                $html ? ENT_NOQUOTES : ENT_QUOTES, // Escape quotes in attribute nodes only.
                'UTF-8',                           // json_encode() outputs UTF-8 (really just ASCII), not the blog's charset.
                true                               // Double escape entities: `&amp;` -> `&amp;amp;`.
            );
        }
        
        public static function popup_template_links($value){
            
            if( $value['is_pro'] ){
                return sprintf('<a data-template="%s" data-tip="%s" class="getwooplugins-help-tip pro-modal" href="#"></a>', esc_attr(self::normalize_id( $value['id'] )), esc_html__('Check how this feature works', 'woo-variation-swatches'));
            }
            
            if($value['help_preview'] ){
                return sprintf('<a data-template="%s" data-tip="%s" class="getwooplugins-help-tip help-modal" href="#"></a>', esc_attr(self::normalize_id( $value['id'] )), esc_html__('See how this feature works', 'woo-variation-swatches'));
            }
        }
        
        public static function dependency_attribute($value){
            if($value && isset( $value['require'] )){
                   return sprintf('data-gwp_dependency="%s"', self::esc_json(wp_json_encode( $value['require'] )));
            }
        }
        
		public static function output_fields( $options ) {
			foreach ( $options as $value ) {
				if ( ! isset( $value['type'] ) ) {
					continue;
				}
				if ( ! isset( $value['id'] ) ) {
					$value['id'] = '';
				}
    
				if ( ! isset( $value['title'] ) ) {
					$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
				}
				if ( ! isset( $value['class'] ) ) {
					$value['class'] = '';
				}
				if ( ! isset( $value['css'] ) ) {
					$value['css'] = '';
				}
				if ( ! isset( $value['default'] ) ) {
					$value['default'] = '';
				}
				if ( ! isset( $value['desc'] ) ) {
					$value['desc'] = '';
				}
				if ( ! isset( $value['desc_tip'] ) ) {
					$value['desc_tip'] = false;
				}
				if ( ! isset( $value['placeholder'] ) ) {
					$value['placeholder'] = '';
				}
				if ( ! isset( $value['suffix'] ) ) {
					$value['suffix'] = '';
				}
				if ( ! isset( $value['prefix'] ) ) {
					$value['prefix'] = '';
				}

				if ( ! isset( $value['suffix-icon'] ) ) {
					$value['suffix-icon'] = '';
				}
				if ( ! isset( $value['prefix-icon'] ) ) {
					$value['prefix-icon'] = '';
				}

				if ( ! isset( $value['value'] ) ) {
					$value['value'] = self::get_option( $value['id'], $value['default'] );
				}
                if ( ! isset( $value['is_pro'] ) ) {
					$value['is_pro'] = false;
				}
                
                if ( ! isset( $value['is_new'] ) ) {
					$value['is_new'] = false;
				}
                
                if ( ! isset( $value['is_classic'] ) ) {
					$value['is_classic'] = false;
				}
                
                if ( ! isset( $value['help_preview'] ) ) {
					$value['help_preview'] = false;
				}
    
				// Custom attribute handling.
				$custom_attributes = array();
    
				if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
					foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				}

				// Description handling.
				$field_description = self::get_field_description( $value );
				$description       = $field_description['description'];
				$tooltip_html      = $field_description['tooltip_html'];
                
                $classes = array();
                
                if( $value['is_pro'] ){
                    $classes[] = 'is-pro';
                }
                
                if( $value['is_new'] ){
                    $classes[] = 'is-new';
                }
                
                if( $value['is_classic'] ){
                    $classes[] = 'is-classic';
                }
                
                if( $value['help_preview'] ){
                    $classes[] = 'help-preview';
                }
                
                if( $tooltip_html ){
                   // $classes[] = 'has-tooltip-html';
                }
                
                $class =  implode(' ', array_values( array_unique($classes) ) );

				// Switch based on type.
				switch ( $value['type'] ) {

					// Section Titles.
					case 'title':
                    case 'sectionstart':
						if ( ! empty( $value['title'] ) ) {
							echo '<h2>' . esc_html( $value['title'] ) . '</h2>';
						}
						if ( ! empty( $value['desc'] ) ) {
							echo '<div id="' . esc_attr( sanitize_title( self::normalize_id( $value['id'] ) ) ) . '-description">';
							echo wp_kses_post( wpautop( wptexturize( $value['desc'] ) ) );
							echo '</div>';
						}
      
						echo '<table class="form-table getwooplugins-admin-form-table">' . "\n\n";
						if ( ! empty( $value['id'] ) ) {
							do_action( 'getwooplugins_setting', sanitize_title( $value['id'] ) );
						}
						break;

					// Section Ends.
					case 'sectionend':
                    case 'titleend':
						if ( ! empty( $value['id'] ) ) {
							do_action( 'getwooplugins_setting_end', sanitize_title( $value['id'] ) );
						}
						echo '</table>';
						if ( ! empty( $value['id'] ) ) {
							do_action( 'getwooplugins_setting_after', sanitize_title( $value['id'] )  );
						}
						break;

					// Standard text inputs and subtypes like 'number'.
					case 'text':
					case 'password':
					case 'datetime':
					case 'datetime-local':
					case 'date':
					case 'month':
					case 'time':
					case 'week':
					case 'number':
					case 'email':
					case 'url':
					case 'tel':
						$option_value = $value['value'];

						?><tr class="<?php echo esc_attr($class) ?>" <?php echo self::dependency_attribute($value); ?>>
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( self::normalize_id( $value['id'] ) ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tooltip_html); ?></label>
							    <?php echo self::popup_template_links($value); // WPCS: XSS ok. ?>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">

<?php
 $input_wrapper_class = '';
 $input_wrapper_class .= ($value['suffix'] || $value['suffix-icon']) ?' has-suffix':'';
 $input_wrapper_class .= ($value['prefix'] || $value['prefix-icon'])?' has-prefix':'';
?>
<div class="input-wrapper <?php echo esc_attr($input_wrapper_class) ?>">
								<span class="gwp-setting-input-prefix">
								<?php if($value['prefix-icon']){ ?>
									<span class="icon-holder <?php echo esc_attr($value['prefix-icon']) ?>"></span>
								<?php }?>
								<?php echo esc_html( $value['prefix'] ); ?>
								</span>
								<input
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( self::normalize_id( $value['id'] ) ); ?>"
									type="<?php echo esc_attr( $value['type'] ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									value="<?php echo esc_attr( $option_value ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
									<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
									/><span class="gwp-setting-input-suffix">
									<?php if($value['suffix-icon']){ ?>
									<span class="icon-holder <?php echo esc_attr($value['suffix-icon']) ?>"></span>
								<?php }?>
									<?php echo esc_html( $value['suffix'] ); ?>
									</span>
</div>
									<?php echo wp_kses_post( $description); ?>
							</td>
						</tr>
						<?php
						break;

					// Color picker.
					case 'color':
						$option_value = $value['value'];
						?>
						<tr class="<?php echo esc_attr($class) ?>" <?php echo self::dependency_attribute($value); ?>>
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
                                <?php echo self::popup_template_links($value); // WPCS: XSS ok. ?>
                            </th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
							<input
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"
									type="text"
									dir="ltr"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									value="<?php echo esc_attr( $option_value ); ?>"
									class="color-picker-alpha <?php echo esc_attr( $value['class'] ); ?>"
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
									<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>/>&lrm; <?php echo wp_kses_post( $description); ?></td></tr>
						<?php
						break;

					// Textarea.
					case 'textarea':
						$option_value = $value['value'];

						?>
						<tr class="<?php echo esc_attr($class) ?>" <?php echo self::dependency_attribute($value); ?>>
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
 							    <?php echo self::popup_template_links($value); // WPCS: XSS ok. ?>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
								

								<textarea
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
									<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
									><?php echo esc_textarea( $option_value ); // WPCS: XSS ok. ?></textarea>
									
									<?php echo wp_kses_post($description); ?>
							</td>
						</tr>
						<?php
						break;

					// Select boxes.
					case 'select':
					case 'multiselect':
						$option_value = $value['value'];
						?>
						<tr class="<?php echo esc_attr($class) ?>" <?php echo self::dependency_attribute($value); ?>>
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); // WPCS: XSS ok. ?></label>
							    <?php echo self::popup_template_links($value); // WPCS: XSS ok. ?>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
								<select
									name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
									id="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									class="wc-enhanced-select <?php echo esc_attr( $value['class'] ); ?>"
									<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
									<?php echo 'multiselect' === $value['type'] ? 'multiple="multiple"' : ''; ?>>
									<?php foreach ( $value['options'] as $key => $val ) { ?>
										<option value="<?php echo esc_attr( $key ); ?>"
											<?php

											if ( is_array( $option_value ) ) {
												selected( in_array( (string) $key, $option_value, true ), true );
											} else {
												selected( $option_value, (string) $key );
											}

											?>
										><?php echo esc_html( $val ); ?></option>
									<?php } ?>
								</select> <?php echo wp_kses_post( $description); // WPCS: XSS ok. ?>
							</td>
						</tr>
						<?php
						break;

					// Radio inputs.
					case 'radio':
						$option_value = $value['value'];
						?>
						<tr class="<?php echo esc_attr($class) ?>" <?php echo self::dependency_attribute($value); ?>>
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); // WPCS: XSS ok. ?></label>
								<?php echo self::popup_template_links($value); // WPCS: XSS ok. ?>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
								<fieldset>
									
									<ul>
									<?php
									foreach ( $value['options'] as $key => $val ) {
										?>
										<li>
											<label><input
												name="<?php echo esc_attr( $value['id'] ); ?>"
												value="<?php echo esc_attr( $key ); ?>"
												type="radio"
												style="<?php echo esc_attr( $value['css'] ); ?>"
												class="<?php echo esc_attr( $value['class'] ); ?>"
												<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
												<?php checked( $key, $option_value ); ?>
												/> <?php echo esc_html( $val ); ?></label>
										</li>
										<?php
									}
									?>
									</ul>
									
									<?php echo wp_kses_post($description); ?>
								</fieldset>
							</td>
						</tr>
						<?php
						break;

					// Checkbox input.
					case 'checkbox':
						$option_value     = $value['value'];
						$visibility_class = array($class);

						if ( ! isset( $value['hide_if_checked'] ) ) {
							$value['hide_if_checked'] = false;
						}
						if ( ! isset( $value['show_if_checked'] ) ) {
							$value['show_if_checked'] = false;
						}
						if ( 'yes' === $value['hide_if_checked'] || 'yes' === $value['show_if_checked'] ) {
							$visibility_class[] = 'hidden_option';
						}
						if ( 'option' === $value['hide_if_checked'] ) {
							$visibility_class[] = 'hide_options_if_checked';
						}
						if ( 'option' === $value['show_if_checked'] ) {
							$visibility_class[] = 'show_options_if_checked';
						}

						if ( ! isset( $value['checkboxgroup'] ) || 'start' === $value['checkboxgroup'] ) {
							?>
								<tr class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>" <?php echo self::dependency_attribute($value); ?>>
									<th scope="row" class="titledesc">
									<label for="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"><?php echo esc_html( $value['title'] ); ?><?php echo wp_kses_post( $tooltip_html); ?></label>
									<?php echo self::popup_template_links($value); // WPCS: XSS ok. ?>
									</th>
									<td class="forminp forminp-checkbox">
										<fieldset>
							<?php
						} else {
							?>
								<fieldset class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
							<?php
						}

						if ( ! empty( $value['title'] ) ) {
							?>
								<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span></legend>
							<?php
						}

						?>
							<label for="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>">
								<input
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr(self::normalize_id( $value['id'] )  ); ?>"
									type="checkbox"
									class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
									value="1"
									<?php checked( $option_value, 'yes' ); ?>
									<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
								/> <?php echo wp_kses_post($description); ?>
							</label> <?php echo wp_kses_post( $tooltip_html); ?>
						<?php

						if ( ! isset( $value['checkboxgroup'] ) || 'end' === $value['checkboxgroup'] ) {
							?>
										</fieldset>
									</td>
								</tr>
							<?php
						} else {
							?>
								</fieldset>
							<?php
						}
						break;

					// Single page selects.
					case 'single_select_page':
						$args = array(
							'name'             => $value['id'],
							'id'               => $value['id'],
							'sort_column'      => 'menu_order',
							'sort_order'       => 'ASC',
							'show_option_none' => ' ',
							'class'            => $value['class'],
							'echo'             => false,
							'selected'         => absint( $value['value'] ),
							'post_status'      => 'publish,private,draft',
						);

						if ( isset( $value['args'] ) ) {
							$args = wp_parse_args( $value['args'], $args );
						}

						?>
						<tr class="<?php echo esc_attr($class) ?> single_select_page" <?php echo self::dependency_attribute($value); ?>>
							<th scope="row" class="titledesc">
								<label><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
							    <?php echo self::popup_template_links($value); // WPCS: XSS ok. ?>
							</th>
							<td class="forminp">
								<?php echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'woo-variation-swatches' ) . "' style='" . esc_attr($value['css']) . "' class='" . esc_attr($value['class']) . "' id=", wp_dropdown_pages( $args ) ); // WPCS: XSS ok. ?> <?php echo wp_kses_post($description); ?>
							</td>
						</tr>
						<?php
						break;

					case 'single_select_page_with_search':
						$option_value = $value['value'];
						$page         = get_post( $option_value );

						if ( ! is_null( $page ) ) {
							$page                = get_post( $option_value );
							$option_display_name = sprintf(
								/* translators: 1: page name 2: page ID */
								__( '%1$s (ID: %2$s)', 'woo-variation-swatches' ),
								$page->post_title,
								$option_value
							);
						}
						?>
						<tr class="<?php echo esc_attr($class) ?> single_select_page" <?php echo self::dependency_attribute($value); ?>>
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( self::normalize_id( $value['id'] ) ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
								<?php echo self::popup_template_links($value); // WPCS: XSS ok. ?>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
								<select
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									<?php echo implode( ' ', $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									data-placeholder="<?php esc_attr_e( 'Search for a page&hellip;', 'woo-variation-swatches' ); ?>"
									data-allow_clear="true"
									data-exclude="<?php echo self::esc_json( wp_json_encode( $value['args']['exclude'] ) ); ?>"
									>
									<option value=""></option>
									<?php if ( ! is_null( $page ) ) { ?>
										<option value="<?php echo esc_attr( $option_value ); ?>" selected="selected">
										<?php echo wp_strip_all_tags( $option_display_name ); ?>
										</option>
									<?php } ?>
								</select> <?php echo wp_kses_post($description); ?>
							</td>
						</tr>
						<?php
						break;

					// Single country selects.
					case 'single_select_country':
						$country_setting = (string) $value['value'];

						if ( strstr( $country_setting, ':' ) ) {
							$country_setting = explode( ':', $country_setting );
							$country         = current( $country_setting );
							$state           = end( $country_setting );
						} else {
							$country = $country_setting;
							$state   = '*';
						}
						?>
						<tr class="<?php echo esc_attr($class) ?>" <?php echo self::dependency_attribute($value); ?>>
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
								<?php echo self::popup_template_links($value); // WPCS: XSS ok. ?>
							</th>
							<td class="forminp"><select name="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" data-placeholder="<?php esc_attr_e( 'Choose a country / region&hellip;', 'woo-variation-swatches' ); ?>" aria-label="<?php esc_attr_e( 'Country / Region', 'woo-variation-swatches' ); ?>" class="wc-enhanced-select">
								<?php WC()->countries->country_dropdown_options( $country, $state ); ?>
							</select> <?php echo wp_kses_post($description); ?>
							</td>
						</tr>
						<?php
						break;

					// Country multiselects.
					case 'multi_select_countries':
						$selections = (array) $value['value'];

						if ( ! empty( $value['options'] ) ) {
							$countries = $value['options'];
						} else {
							$countries = WC()->countries->countries;
						}

						asort( $countries );
						?>
						<tr class="<?php echo esc_attr($class) ?>" <?php echo self::dependency_attribute($value); ?>>
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); // WPCS: XSS ok. ?></label>
								<?php echo self::popup_template_links($value); // WPCS: XSS ok. ?>
							</th>
							<td class="forminp">
								<select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" style="width:350px" data-placeholder="<?php esc_attr_e( 'Choose countries / regions&hellip;', 'woo-variation-swatches' ); ?>" aria-label="<?php esc_attr_e( 'Country / Region', 'woo-variation-swatches' ); ?>" class="wc-enhanced-select">
									<?php
									if ( ! empty( $countries ) ) {
										foreach ( $countries as $key => $val ) {
											echo '<option value="' . esc_attr( $key ) . '"' . wc_selected( $key, $selections ) . '>' . esc_html( $val ) . '</option>'; // WPCS: XSS ok.
										}
									}
									?>
								</select> <?php echo wp_kses_post( $description); ?> <br /><a class="select_all button" href="#"><?php esc_html_e( 'Select all', 'woo-variation-swatches' ); ?></a> <a class="select_none button" href="#"><?php esc_html_e( 'Select none', 'woo-variation-swatches' ); ?></a>
							</td>
						</tr>
						<?php
						break;

					// Days/months/years selector.
					case 'relative_date_selector':
						$periods      = array(
							'days'   => __( 'Day(s)', 'woo-variation-swatches' ),
							'weeks'  => __( 'Week(s)', 'woo-variation-swatches' ),
							'months' => __( 'Month(s)', 'woo-variation-swatches' ),
							'years'  => __( 'Year(s)', 'woo-variation-swatches' ),
						);
						$option_value = wc_parse_relative_date_option( $value['value'] );
						?>
						<tr class="<?php echo esc_attr($class) ?>" <?php echo self::dependency_attribute($value); ?>>
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html);  ?></label>
								<?php echo self::popup_template_links($value); // WPCS: XSS ok. ?>
							</th>
							<td class="forminp">
							<input
									name="<?php echo esc_attr( $value['id'] ); ?>[number]"
									id="<?php echo esc_attr( self::normalize_id( $value['id'] )  ); ?>"
									type="number"
									style="width: 80px;"
									value="<?php echo esc_attr( $option_value['number'] ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
									step="1"
									min="1"
									<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
								/>&nbsp;
								<select name="<?php echo esc_attr( $value['id'] ); ?>[unit]" style="width: auto;">
									<?php
									foreach ( $periods as $value => $label ) {
										echo '<option value="' . esc_attr( $value ) . '"' . selected( $option_value['unit'], $value, false ) . '>' . esc_html( $label ) . '</option>';
									}
									?>
								</select> <?php echo wp_kses_post( $description ); // WPCS: XSS ok. ?>
							</td>
						</tr>
						<?php
						break;

					// Default: run an action.
					default:
						do_action( 'getwooplugins_admin_field', sanitize_text_field($value['type']), $value );
						break;
				}
			}
		}

		/**
		 * Helper function to get the formatted description and tip HTML for a
		 * given form field. Plugins can call this when implementing their own custom
		 * settings types.
		 *
		 * @param  array $value The form field value array.
		 *
		 * @return array The description and tip as a 2 element array.
		 */
		public static function get_field_description( $value ) {
			$description  = '';
			$tooltip_html = '';

			if ( true === $value['desc_tip'] ) {
				$tooltip_html = $value['desc'];
			} elseif ( ! empty( $value['desc_tip'] ) ) {
				$description  = $value['desc'];
				$tooltip_html = $value['desc_tip'];
			} elseif ( ! empty( $value['desc'] ) ) {
				$description = $value['desc'];
			}

			if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ), true ) ) {
				$description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
			} elseif ( $description && in_array( $value['type'], array( 'checkbox' ), true ) ) {
				$description = wp_kses_post( $description );
			} elseif ( $description ) {
				$description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
			}

			if ( $tooltip_html && in_array( $value['type'], array( 'checkbox' ), true ) ) {
				$tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
			} elseif ( $tooltip_html ) {
				// $tooltip_html = wc_help_tip( $tooltip_html );
				$tooltip_html = sprintf('<span class="getwooplugins-help-tip" data-tip="%s"></span>', esc_attr($tooltip_html));
			}

			return array(
				'description'  => $description,
				'tooltip_html' => $tooltip_html,
			);
		}

		/**
		 * Save admin fields.
		 *
		 * Loops through the woocommerce options array and outputs each field.
		 *
		 * @param array $options Options array to output.
		 * @param array $data    Optional. Data to use for saving. Defaults to $_POST.
		 *
		 * @return bool
		 */
		public static function save_fields( $options, $data = null ) {
			if ( is_null( $data ) ) {
				$data = $_POST; // WPCS: input var okay, CSRF ok.
			}
			if ( empty( $data ) ) {
				return false;
			}

			// Options to update will be stored here and saved later.
			$update_options   = array();
			$autoload_options = array();

			// Loop options and get values to save.
			foreach ( $options as $option ) {
				if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) || ( isset( $option['is_option'] ) && false === $option['is_option'] ) ) {
					continue;
				}

				// Get posted value.
				if ( strstr( $option['id'], '[' ) ) {
					parse_str( $option['id'], $option_name_array );
					$option_name  = current( array_keys( $option_name_array ) );
					$setting_name = key( $option_name_array[ $option_name ] );
					$raw_value    = isset( $data[ $option_name ][ $setting_name ] ) ? wp_unslash( $data[ $option_name ][ $setting_name ] ) : null;
				} else {
					$option_name  = $option['id'];
					$setting_name = '';
					$raw_value    = isset( $data[ $option['id'] ] ) ? wp_unslash( $data[ $option['id'] ] ) : null;
				}

				// Format the value based on option type.
				switch ( $option['type'] ) {
					case 'checkbox':
						$value = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
						break;
					case 'textarea':
						$value = wp_kses_post( trim( $raw_value ) );
						break;
					case 'multiselect':
					case 'multi_select_countries':
						$value = array_filter( array_map( 'sanitize_text_field', (array) $raw_value ) );
						break;
					case 'image_width':
						$value = array();
						if ( isset( $raw_value['width'] ) ) {
							$value['width']  = sanitize_text_field( $raw_value['width'] );
							$value['height'] = sanitize_text_field( $raw_value['height'] );
							$value['crop']   = isset( $raw_value['crop'] ) ? 1 : 0;
						} else {
							$value['width']  = $option['default']['width'];
							$value['height'] = $option['default']['height'];
							$value['crop']   = $option['default']['crop'];
						}
						break;
					case 'select':
						$allowed_values = empty( $option['options'] ) ? array() : array_map( 'strval', array_keys( $option['options'] ) );
						if ( empty( $option['default'] ) && empty( $allowed_values ) ) {
							$value = null;
							break;
						}
						$default = ( empty( $option['default'] ) ? $allowed_values[0] : $option['default'] );
						$value   = in_array( $raw_value, $allowed_values, true ) ? $raw_value : $default;
						break;
					case 'relative_date_selector':
						$value = wc_parse_relative_date_option( $raw_value );
						break;
					default:
						$value = sanitize_text_field( $raw_value );
						break;
				}

				/**
				 * Sanitize the value of an option.
				 *
				 * @since 2.4.0
				 */
				$value = apply_filters( 'getwooplugins_admin_settings_sanitize_option', $value, $option, $raw_value );

				/**
				 * Sanitize the value of an option by option name.
				 *
				 * @since 2.4.0
				 */
				$value = apply_filters( "getwooplugins_admin_settings_sanitize_option_$option_name", $value, $option, $raw_value );

				if ( is_null( $value ) ) {
					continue;
				}

				// Check if option is an array and handle that differently to single values.
				if ( $option_name && $setting_name ) {
					if ( ! isset( $update_options[ $option_name ] ) ) {
						$update_options[ $option_name ] = get_option( $option_name, array() );
					}
					if ( ! is_array( $update_options[ $option_name ] ) ) {
						$update_options[ $option_name ] = array();
					}
					$update_options[ $option_name ][ $setting_name ] = $value;
				} else {
					$update_options[ $option_name ] = $value;
				}

				$autoload_options[ $option_name ] = isset( $option['autoload'] ) ? (bool) $option['autoload'] : true;
    
			}

			// Save all options in our array.
			foreach ( $update_options as $name => $value ) {
				update_option( $name, $value, $autoload_options[ $name ] ? 'yes' : 'no' );
			}

			return true;
		}
	}

endif;
