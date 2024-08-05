<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'GetWooPlugins_Settings_Page', false ) ) :

	abstract class GetWooPlugins_Settings_Page {

		public function __construct() {
			add_filter( 'getwooplugins_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'getwooplugins_sections', array( $this, 'output_sections_as_tab' ) );
			add_action( 'getwooplugins_settings', array( $this, 'output' ) );
			add_action( 'getwooplugins_settings_save', array( $this, 'save' ) );
			add_action( 'getwooplugins_settings_action', array( $this, 'action' ), 10, 3 );
		}

		abstract public function get_id();

		abstract public function get_label();

		abstract public function get_title();

		abstract public function get_menu_name();

		public function is_current_tab() {
			return isset( $_GET['tab'] ) && ( $_GET['tab'] === $this->get_id() );
		}

		public function array_insert_after( array $array, $key, array $new ) {
			$keys  = array_keys( $array );
			$index = array_search( $key, $keys );
			$pos   = false === $index ? count( $array ) : $index + 1;

			return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
		}

		public function array_insert_before( array $array, $key, array $new ) {
			$keys  = array_keys( $array );
			$index = array_search( $key, $keys );
			$pos   = false === $index ? count( $array ) - 1 : $index;

			return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
		}

		public function normalize_required_attribute( $require = array() ) {

			$r = array();
			//   array( '#woo_variation_swatches_show_variation_label' => array( 'type' => '==', 'value' => '1' ) ),
			foreach ( $require as $id => $value ) {
				$r[ sprintf( '#%s_%s', $this->get_id(), $id ) ] = $value;
			}

			return array( $r );
		}

		public function add_settings_page( $pages ) {
			$pages[ $this->get_id() ] = array(
				'label' => $this->get_label(),
				'title' => $this->get_title(),
			);

			return $pages;
		}

		public function get_settings() {
			$section_id = 0 === func_num_args() ? '' : func_get_arg( 0 );

			return $this->get_settings_for_section( $section_id );
		}

		final public function get_settings_for_section( $section_id ) {
			if ( '' === $section_id ) {
				$method_name = 'get_settings_for_default_section';
			} else {
				$method_name = "get_settings_for_{$section_id}_section";
			}

			if ( method_exists( $this, $method_name ) ) {
				$settings = $this->$method_name();
			} else {
				$settings = $this->get_settings_for_section_core( $section_id );
			}

			$settings = array_map( array( $this, 'generate_id' ), $settings );

			return apply_filters( 'getwooplugins_get_settings_' . $this->get_id(), $settings, $section_id );
		}

		protected function generate_id( $setting ) {

			if ( isset( $setting['standalone'] ) && $setting['standalone'] === true ) {
				$setting['id'] = sprintf( '%s_%s', $this->get_id(), $setting['id'] );
			} else {
				$setting['id'] = ( in_array( $setting['type'], array(
					'sectionend',
					'title'
				) ) ) ? $setting['id'] : sprintf( '%s[%s]', $this->get_id(), $setting['id'] );
			}


			return $setting;
		}

		protected function get_settings_for_section_core( $section_id ) {
			return array();
		}

		public function get_sections() {
			$sections = $this->get_own_sections();

			return apply_filters( 'getwooplugins_get_sections', $sections, $this->get_id() );
		}

		/**
		 * Get own sections for this page.
		 * Derived classes should override this method if they define sections.
		 * There should always be one default section with an empty string as identifier.
		 *
		 * Example:
		 * return array(
		 *   ''        => __( 'General', 'woo' ),
		 *   'foobars' => __( 'Foos & Bars', 'woo' ),
		 * );
		 *
		 * @return array An associative array where keys are section identifiers and the values are translated section names.
		 */
		protected function get_own_sections() {
			return array( '' => esc_html__( 'General', 'woo-variation-swatches' ) );
		}

		/**
		 * Output sections.
		 */
		public function output_sections( $current_tab ) {
			global $current_section;

			if ( $current_tab !== $this->get_id() ) {
				return;
			}

			$sections = $this->get_sections();

			if ( empty( $sections ) || 1 === count( $sections ) ) {
				return;
			}

			echo '<ul class="subsubsub getwooplugins-settings-sections">';

			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				$url       = admin_url( 'admin.php?page=getwooplugins-settings&tab=' . $this->get_id() . '&section=' . sanitize_title( $id ) );
				$class     = ( $current_section === $id ? 'current' : '' );
				$separator = ( end( $array_keys ) === $id ? '' : '|' );
				$text      = $label;
				echo sprintf( '<li><a href="%s" class="%s">%s</a> %s </li>', esc_url( $url ), esc_attr( $class ), esc_html( $text ), esc_html( $separator ) );
			}

			echo '</ul><br class="clear" />';
		}

		public function output_sections_as_tab( $current_tab ) {
			global $current_section;

			if ( $current_tab !== $this->get_id() ) {
				return;
			}

			$sections = $this->get_sections();

			if ( empty( $sections ) || 1 === count( $sections ) ) {
				return;
			}

			echo '<nav class="nav-tab-wrapper woo-nav-tab-wrapper getwooplugins-nav-tab-wrapper">';

			foreach ( $sections as $id => $label ) {

				$name   = $label;
				$target = '_self';

				if ( empty( $id ) ) {
					$original_url = $url = admin_url( 'admin.php?page=getwooplugins-settings&tab=' . $this->get_id() );
				} else {
					$original_url = $url = admin_url( 'admin.php?page=getwooplugins-settings&tab=' . $this->get_id() . '&section=' . sanitize_title( $id ) );
				}

				if ( is_array( $label ) ) {
					$url    = $label['url'];
					$name   = $label['name'];
					$target = isset( $label['target'] ) ? $label['target'] : '_self';
				}

				$url = apply_filters( 'getwooplugins_get_section_url', $url, $this->get_id(), empty( $id ) ? '' : $id );

				if ( false === $url ) {
					continue;
				}

				if ( true === $url ) {
					$url = $original_url;
				}

				$class = ( $current_section === $id ? 'nav-tab-active' : '' );
				$text  = wp_kses( $name, array(
					'em'     => array(),
					'span'   => array(
						'class' => array(),
						'id'    => array()
					),
					'strong' => array()
				) );

				printf( '<a target="%s" href="%s" class="nav-tab %s">%s</a>', esc_attr( $target ), esc_url( $url ), $class, $text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped


				// echo "<a target='$target' href='$url' class='nav-tab $class'>$text</a>";
			}

			echo '</nav>';
		}

		/**
		 * Output the HTML for the settings.
		 */
		public function output( $current_tab ) {
			global $current_section;

			if ( $current_tab !== $this->get_id() ) {
				return;
			}

			// We can't use "get_settings_for_section" here
			// for compatibility with derived classes overriding "get_settings".
			$settings = $this->get_settings( $current_section );

			GetWooPlugins_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings and trigger the 'getwooplugins_update_options_'.id action.
		 */
		public function save( $current_tab ) {

			if ( $current_tab !== $this->get_id() ) {
				return;
			}

			$this->save_settings_for_current_section();
		}

		public function action( $current_tab, $current_section, $current_action ) {
			if ( $current_tab !== $this->get_id() ) {
				return;
			}

			if ( $current_action === 'reset' ) {
				check_admin_referer( 'getwooplugins-settings' );
				do_action( 'getwooplugins_before_delete_options', $this->get_id() );
				delete_option( $this->get_id() );
				do_action( 'getwooplugins_after_delete_options', $this->get_id() );
				$current_section_url = $current_section ? '&section=' . $current_section : '';
				wp_safe_redirect( admin_url( 'admin.php?page=getwooplugins-settings&tab=' . $this->get_id() . $current_section_url . '&' . $current_action . '=1' ) );
				exit();
			}
		}

		/**
		 * Save settings for current section.
		 */
		protected function save_settings_for_current_section() {
			global $current_section;

			// We can't use "get_settings_for_section" here
			// for compatibility with derived classes overriding "get_settings".
			$settings = $this->get_settings( $current_section );
			GetWooPlugins_Admin_Settings::save_fields( $settings );
		}

		/**
		 * $links = array('button_url'=>'', 'button_text'=>'', 'button_class'=>'', 'link_url'=>'', 'link_text'=>'');
		 */

		public function modal_template_id( $template_id ) {
			return sprintf( '%s_%s', $this->get_id(), $template_id );
		}

		public function modal_dialog( $id, $title, $body, $links = array() ) {
			$template_id = $this->modal_template_id( $id );
			include dirname( __FILE__ ) . '/html/dialog.php';
		}
	}

endif;
