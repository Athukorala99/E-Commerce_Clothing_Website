<?php
    
    defined( 'ABSPATH' ) || exit;
    
    if ( ! class_exists( 'GetWooPlugins_Admin_Menus', false ) ) :
        
        class GetWooPlugins_Admin_Menus {
            
            protected static $_instance = null;
            
            protected $settings_pages;
            
            
            public function __construct() {
                $this->includes();
                $this->hooks();
                $this->init();
            }
            
            public static function instance() {
                if ( is_null( self::$_instance ) ) {
                    self::$_instance = new self();
                }
                
                return self::$_instance;
            }
            
            public function includes() {
                // Include settings pages.
                require_once dirname( __FILE__ ) . '/class-getwooplugins-admin-settings.php';
            }
            
            public function hooks() {
                // Add menus.
                add_action( 'admin_menu', array( $this, 'menu_sort' ), 60 );
                add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
                add_action( 'admin_menu', array( $this, 'settings_menu' ), 50 );
                add_filter( 'submenu_file', array( $this, 'update_menu_highlight' ), 99, 2 );
                
                // Handle saving settings earlier than load-{page} hook to avoid race conditions in conditional menus.
                add_action( 'wp_loaded', array( $this, 'save_settings' ) );
                
                add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 20 );
            }
            
            public function init() {
            
            }
            
            public function get_settings_pages() {
                
                if ( ! $this->settings_pages ) {
                    $this->settings_pages = GetWooPlugins_Admin_Settings::get_settings_pages();
                }
                
                return $this->settings_pages;
            }
            
            public function update_menu_highlight( $submenu_file, $parent_file ) {
                
                $settings_pages = $this->get_settings_pages();
                
                if ( 'getwooplugins' === $parent_file && ! empty( $settings_pages ) ) {
                    
                    $tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'tab' ] ) ) : '';
                    
                    foreach ( $settings_pages as $index => $page ) {
                        
                        if ( $index === 0 && $page->get_id() === $tab ) {
                            $submenu_file = 'getwooplugins-settings';
                            break;
                        }
                        
                        if ( $page->get_id() === $tab ) {
                            $submenu_file = 'getwooplugins-settings&tab=' . $page->get_id();
                            break;
                        }
                    }
                }
                
                return $submenu_file;
            }
            
            public function load_css( $screen_ids ) {
                
                /*$screen    = get_current_screen();
                $screen_id = $screen ? $screen->id : '';*/
                
                array_push( $screen_ids, 'getwooplugins_page_getwooplugins-settings' );
                
                return $screen_ids;
            }
            
            public function admin_scripts() {
                $screen    = get_current_screen();
                $screen_id = $screen ? $screen->id : '';
                
                if ( 'getwooplugins_page_getwooplugins-settings' === $screen_id ) {
                    
                    wp_enqueue_style( 'woocommerce_admin_styles' );
                    wp_enqueue_style( 'jquery-ui-style' );
                    wp_enqueue_style( 'wp-color-picker' );
                    wp_enqueue_media();
                    
                    wp_enqueue_style( 'getwooplugins_settings', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/css/getwooplugins-settings.css', array(), filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'css/getwooplugins-settings.css' ) );
                    
                    wp_enqueue_script( 'jquery-tiptip', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/js/jquery.tipTip.js', array( 'jquery' ), filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'js/jquery.tipTip.js' ), true );
                    
                    wp_enqueue_script( 'gwp-form-field-dependency', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/js/getwooplugins-form-field-dependency.js', array( 'jquery' ), filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'js/getwooplugins-form-field-dependency.js' ), true );
                    
                    wp_enqueue_script( 'wp-color-picker-alpha', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/js/wp-color-picker-alpha.js', array(
                        'jquery',
                        'wp-color-picker'
                    ),                 filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'js/wp-color-picker-alpha.js' ), true );
                    
                    
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
                    
                    wp_enqueue_script( 'getwooplugins_settings', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/js/getwooplugins-settings.js', $dep, filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'js/getwooplugins-settings.js' ), true );
                    
                    wp_localize_script( 'getwooplugins_settings', 'getwooplugins_settings_params', array(
                        'i18n_nav_warning' => esc_html__( 'The changes you made will be lost if you navigate away from this page.', 'woo-variation-swatches' ),
                    ) );
                }
            }
            
            /**
             * Add menu items.
             */
            public function admin_menu() {
                global $menu;
                
                $pages = $this->get_settings_pages();
                
                if ( empty( $pages ) ) {
                    return;
                }
                
                if ( current_user_can( 'edit_theme_options' ) ) {
                    $menu[ '45.4' ] = array(
                        '',
                        'read',
                        'separator-getwooplugins',
                        '',
                        'wp-menu-separator getwooplugins'
                    ); // WPCS: override ok.
                }
                
                
                add_menu_page( esc_html__( 'GetWooPlugins Settings', 'woo-variation-swatches' ), esc_html__( 'GetWooPlugins', 'woo-variation-swatches' ), 'edit_theme_options', 'getwooplugins', null, 'dashicons-admin-settings', '45.5' );
                
            }
            
            public function get_settings_link( $id, $section = false ) {
                
                $params = array(
                    'page' => 'getwooplugins-settings',
                    'tab'  => esc_html( $id )
                );
                
                if ( $section && is_string( $section ) ) {
                    $params[ 'section' ] = $section;
                }
                
                if ( $section && is_array( $section ) ) {
                    $params = wp_parse_args( $params, $section );
                }
                
                return add_query_arg( $params, admin_url( 'admin.php' ) );
            }
            
            /**
             * Add menu item.
             */
            public function settings_menu() {
                
                global $submenu, $menu;
                
                $settings_page = add_submenu_page( 'getwooplugins', esc_html__( 'GetWooPlugins Settings', 'woo-variation-swatches' ), esc_html__( 'Home', 'woo-variation-swatches' ), 'manage_options', 'getwooplugins-settings', array(
                    $this,
                    'settings_page'
                ) );
                
                $settings_pages = $this->get_settings_pages();
                
                foreach ( $settings_pages as $page ) {
                    add_submenu_page( 'getwooplugins', $page->get_title(), $page->get_menu_name(), 'manage_options', 'getwooplugins-settings&tab=' . $page->get_id(), array(
                        $this,
                        'settings_page'
                    ) );
                }
                
                add_action( 'load-' . $settings_page, array( $this, 'settings_page_init' ) );
            }
            
            public function settings_page_init() {
                
                do_action( 'getwooplugins_settings_page_init' );
            }
            
            /**
             * Handle saving of settings.
             *
             * @return void
             */
            public function save_settings() {
                
                global $current_tab, $current_section;
                
                // We should only save on the settings page.
                if ( ! is_admin() || ! isset( $_GET[ 'page' ] ) || 'getwooplugins-settings' !== $_GET[ 'page' ] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    return;
                }
                
                $settings_pages = $this->get_settings_pages();
                
                if ( empty( $settings_pages ) ) {
                    return;
                }
                
                
                // Get current tab/section.
                $current_tab     = empty( $_GET[ 'tab' ] ) ? $settings_pages[ 0 ]->get_id() : sanitize_title( wp_unslash( $_GET[ 'tab' ] ) ); // WPCS: input var okay, CSRF ok.
                $current_section = empty( $_REQUEST[ 'section' ] ) ? '' : sanitize_title( wp_unslash( $_REQUEST[ 'section' ] ) ); // WPCS: input var okay, CSRF ok.
                $current_action  = empty( $_GET[ 'action' ] ) ? '' : sanitize_title( wp_unslash( $_GET[ 'action' ] ) ); // WPCS: input var okay, CSRF ok.
                
                
                if ( is_admin() && isset( $_GET[ 'page' ] ) && 'getwooplugins-settings' === $_GET[ 'page' ] && ! empty( $current_action ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    GetWooPlugins_Admin_Settings::action( $current_tab, $current_section, $current_action );
                }
                
                
                // Save settings if data has been posted.
                if ( '' !== $current_section && apply_filters( "getwooplugins_save_settings_{$current_tab}_{$current_section}", ! empty( $_POST[ 'save' ] ) ) ) { // WPCS: input var okay, CSRF ok.
                    GetWooPlugins_Admin_Settings::save();
                } elseif ( '' === $current_section && apply_filters( "getwooplugins_save_settings_{$current_tab}", ! empty( $_POST[ 'save' ] ) ) ) { // WPCS: input var okay, CSRF ok.
                    GetWooPlugins_Admin_Settings::save();
                }
            }
            
            /**
             * Adds the order processing count to the menu.
             */
            public function menu_sort() {
                global $submenu, $menu;
                
                if ( ! isset( $submenu[ 'getwooplugins' ] ) ) {
                    return;
                }
                
                if ( isset( $submenu[ 'getwooplugins' ] ) ) {
                    // Remove 'getwooplugins' sub menu item.
                    unset( $submenu[ 'getwooplugins' ][ 0 ] );
                }
                
                $pages = $this->get_settings_pages();
                
                if ( ! empty( $pages ) && count( $pages ) > 0 ) {
                    
                    $submenu[ 'getwooplugins' ][ 1 ][ 0 ] = $submenu[ 'getwooplugins' ][ 2 ][ 0 ];
                    unset( $submenu[ 'getwooplugins' ][ 2 ] );
                }
            }
            
            /**
             * Init the settings page.
             */
            public function settings_page() {
                GetWooPlugins_Admin_Settings::output();
            }
        }
    endif;
