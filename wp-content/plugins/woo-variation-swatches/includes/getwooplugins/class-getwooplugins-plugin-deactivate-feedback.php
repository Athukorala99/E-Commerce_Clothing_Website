<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'GetWooPlugins_Plugin_Deactivate_Feedback', false ) ) :

	/**
	 * GetWooPlugins_Plugin_Deactivate_Feedback Class.
	 */
	abstract class GetWooPlugins_Plugin_Deactivate_Feedback {

		public function __construct() {
			add_action( 'admin_footer', array( $this, 'dialog' ) );
			// add_filter( 'wp_ajax_gwp_deactivate_feedback_by_{PLUGIN_SLUG}', array( $this, 'send' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
		}

		abstract public function slug();

		abstract public function version();

		abstract public function reasons();

		abstract public function options();

		public function enqueue_scripts() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : ''; // plugins
			if ( 'plugins' === $screen_id ) {

				wp_enqueue_style( 'wp-color-picker' );

				wp_enqueue_style( 'getwooplugins_settings', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'css/getwooplugins-settings.css', array( 'dashicons' ), filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'css/getwooplugins-settings.css' ) );

				wp_enqueue_script( 'jquery-tiptip', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/js/jquery.tipTip.js', array( 'jquery' ), filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'js/jquery.tipTip.js' ), true );

				wp_enqueue_script( 'gwp-form-field-dependency', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/js/getwooplugins-form-field-dependency.js', array( 'jquery' ), filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'js/getwooplugins-form-field-dependency.js' ), true );

				wp_enqueue_script( 'wp-color-picker-alpha', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/js/wp-color-picker-alpha.js', array(
					'jquery',
					'wp-color-picker'
				), filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'js/wp-color-picker-alpha.js' ), true );

				$dep = array(
					'jquery',
					'underscore',
					'backbone',
					'wp-util',
					'jquery-tiptip',
					'iris'
				);

				if ( class_exists( 'WooCommerce' ) ) {
					$dep[] = 'wc-enhanced-select';
				}

				wp_enqueue_script( 'getwooplugins_settings', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/getwooplugins-settings.js', $dep, filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'js/getwooplugins-settings.js' ), true );

				wp_localize_script( 'getwooplugins_settings', 'getwooplugins_settings_params', array(
					'i18n_nav_warning' => esc_html__( 'The changes you made will be lost if you navigate away from this page.', 'woo-variation-swatches' ),
				) );

				wp_add_inline_script( 'getwooplugins_settings', sprintf( 'try{GWPAdminHelper.DeactivatePopup("%s")}catch(e){}', $this->slug() ) );
			}
		}

		public function dialog() {

			if ( in_array( get_current_screen()->id, array( 'plugins', 'plugins-network' ), true ) ) {

				$deactivate_reasons = $this->reasons();
				$slug               = $this->slug();
				$version            = $this->version();

				include dirname( __FILE__ ) . '/html/deactive-feedback-dialog.php';
			}
		}

		public function send() {

			$api_url = 'https://stats.storepress.com/wp-json/storepress/deactivation';

			$deactivate_reasons = $this->reasons();

			$plugin         = sanitize_title( $_POST['plugin'] );
			$reason_id      = sanitize_title( $_POST['reason_type'] );
			$reason_title   = wp_kses_post( $deactivate_reasons[ $reason_id ]['title'] );
			$reason_text    = ( isset( $_POST['reason_text'] ) ? sanitize_text_field( $_POST['reason_text'] ) : '' );
			$plugin_version = sanitize_text_field( $_POST['version'] );

			if ( 'temporary_deactivation' === $reason_id ) {
				wp_send_json_success( true );

				return;
			}

			$theme = array(
				'is_child_theme'   => is_child_theme() ? 'yes' : 'no',
				'parent_theme'     => $this->get_parent_theme_name(),
				'theme_name'       => $this->get_theme_name(),
				'theme_version'    => $this->get_theme_version(),
				'theme_uri'        => esc_url( wp_get_theme( get_template() )->get( 'ThemeURI' ) ),
				'theme_author'     => esc_html( wp_get_theme( get_template() )->get( 'Author' ) ),
				'theme_author_uri' => esc_url( wp_get_theme( get_template() )->get( 'AuthorURI' ) ),
			);

			$database_version = wc_get_server_database_version();
			$active_plugins   = (array) get_option( 'active_plugins', array() );
			$plugins          = array();

			if ( is_multisite() ) {
				$network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
				$active_plugins            = array_merge( $active_plugins, $network_activated_plugins );
			}

			foreach ( $active_plugins as $active_plugin ) {

				if ( in_array( $active_plugin, $this->plugin_ignore_list() ) ) {
					continue;
				}

				$plugins[ $active_plugin ] = get_plugin_data( WP_PLUGIN_DIR . '/' . $active_plugin, false, false );
			}

			$environment = array(
				'is_multisite'         => is_multisite() ? 'yes' : 'no',
				'site_url'             => esc_url( get_option( 'siteurl' ) ),
				'home_url'             => esc_url( get_option( 'home' ) ),
				'php_version'          => phpversion(),
				'mysql_version'        => $database_version['number'],
				'mysql_version_string' => $database_version['string'],
				'wc_version'           => WC()->version,
				'wp_version'           => get_bloginfo( 'version' ),
				'server_info'          => isset( $_SERVER['SERVER_SOFTWARE'] ) ? wc_clean( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
			);

			$request_body = array(
				'plugin'       => $plugin,
				'version'      => $plugin_version,
				'reason_id'    => $reason_id,
				'reason_title' => $reason_title,
				'reason_text'  => $reason_text,
				'settings'     => $this->options(),
				'theme'        => $theme,
				'plugins'      => $plugins,
				'environment'  => $environment
			);

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$logger  = wc_get_logger();
				$context = array( 'source' => $this->slug() );
				$logger->info( sprintf( 'Deactivate log: %s', print_r( $request_body, true ) ), $context );
			}

			$response = wp_remote_post( esc_url_raw( $api_url ), array(
				'sslverify' => false,
				'timeout'   => 30,
				'body'      => $request_body
			) );

			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
				wp_send_json_success( wp_remote_retrieve_body( $response ) );
			} else {
				wp_send_json_error( wp_remote_retrieve_response_message( $response ) );
			}
		}

		public function plugin_ignore_list() {
			return array(
				'woo-variation-gallery/woo-variation-gallery.php',
				'woo-variation-gallery-pro/woo-variation-gallery-pro.php',
				'woo-variation-swatches/woo-variation-swatches.php',
				'woo-variation-swatches-pro/woo-variation-swatches-pro.php',
				'woocommerce/woocommerce.php',
			);
		}

		public function get_theme_name() {
			return wp_get_theme()->get( 'Name' );
		}

		public function get_theme_version() {
			return wp_get_theme()->get( 'Version' );
		}

		public function get_parent_theme_dir() {
			return strtolower( basename( get_template_directory() ) );
		}

		public function get_parent_theme_name() {
			return wp_get_theme( get_template() )->get( 'Name' );
		}

		public function get_theme_dir() {
			return strtolower( basename( get_stylesheet_directory() ) );
		}
	}

endif;