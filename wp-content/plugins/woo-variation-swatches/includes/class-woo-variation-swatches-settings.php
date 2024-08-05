<?php
    
    defined( 'ABSPATH' ) || exit;
    
    if ( ! class_exists( 'Woo_Variation_Swatches_Settings' ) ):
        
        class Woo_Variation_Swatches_Settings extends GetWooPlugins_Settings_Page {
            
            /**
             * Constructor.
             */
            public function __construct() {
                
                $this->notices();
                $this->hooks();
                parent::__construct();
                do_action( 'woo_variation_swatches_settings_loaded', $this );
            }
            
            public function get_id() {
                return 'woo_variation_swatches';
            }
            
            public function get_label() {
                return esc_html__( 'Variation Swatches', 'woo-variation-swatches' );
            }
            
            public function get_menu_name() {
                return esc_html__( 'Swatches Settings', 'woo-variation-swatches' );
            }
            
            public function get_title() {
                return esc_html__( 'Variation Swatches for WooCommerce Settings', 'woo-variation-swatches' );
            }
            
            protected function hooks() {
                add_action( 'admin_footer', array( $this, 'modal_templates' ) );
                add_action( 'getwooplugins_sidebar', array( $this, 'sidebar' ) );
                add_filter( 'show_getwooplugins_save_button', array( $this, 'save_button' ), 10, 3 );
                add_filter( 'show_getwooplugins_sidebar', array( $this, 'save_button' ), 10, 3 );
            }
            
            public function save_button( $default, $current_tab, $current_section ) {
                if ( $current_tab === $this->get_id() && in_array( $current_section, array(
                        'tutorial',
                        'plugins',
                        'group'
                    ) ) ) {
                    return false;
                }
                
                return $default;
            }
            
            public function sidebar( $current_tab ) {
                if ( $current_tab === $this->get_id() ) {
                    include_once dirname( __FILE__ ) . '/html-settings-sidebar.php';
                }
            }
            
            public function modal_templates() {
                $this->template_shape_style();
                $this->template_default_to_button();
                $this->template_default_to_image();
                $this->template_clear_on_reselect();
                $this->template_hide_out_of_stock_variation();
                $this->template_clickable_out_of_stock_variation();
                $this->template_show_variation_stock_info();
                $this->template_display_limit();
                $this->template_archive_show_availability();
                $this->template_archive_swatches_position();
                $this->template_show_swatches_on_filter_widget();
                $this->template_enable_catalog_mode();
                $this->template_enable_single_variation_preview();
                $this->template_enable_large_size();
                $this->template_archive_align();
                $this->template_attribute_behavior();
                $this->template_enable_linkable_variation_url();
                $this->template_license();
                $this->template_show_on_archive();
                $this->template_archive_default_selected();
            }
            
            public function modal_support_links() {
                $links = array(
                    'button_url'  => 'https://getwooplugins.com/documentation/woocommerce-variation-swatches/',
                    'button_text' => esc_html__( 'See Documentation', 'woo-variation-swatches' ),
                    'link_url'    => 'https://getwooplugins.com/tickets/',
                    'link_text'   => esc_html__( 'Help &amp; Support', 'woo-variation-swatches' )
                );
                
                return $links;
            }
            
            public function modal_buy_links() {
                
                if ( woo_variation_swatches()->is_pro() ) {
                    return $this->modal_support_links();
                }
                
                $links = array(
                    'button_url'   => 'https://getwooplugins.com/plugins/woocommerce-variation-swatches/',
                    'button_text'  => esc_html__( 'Buy Now', 'woo-variation-swatches' ),
                    'button_class' => 'button-danger',
                    'link_url'     => 'https://getwooplugins.com/documentation/woocommerce-variation-swatches/',
                    'link_text'    => esc_html__( 'See Documentation', 'woo-variation-swatches' )
                );
                
                return $links;
            }
            
            public function template_shape_style() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-01.webm' ) ) );
                $this->modal_dialog( 'shape_style', esc_html__( 'Swatches Shape Style', 'woo-variation-swatches' ), $body, $this->modal_support_links() );
            }
            
            public function template_default_to_button() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-02.webm' ) ) );
                $this->modal_dialog( 'default_to_button', esc_html__( 'Swatches Default To Button', 'woo-variation-swatches' ), $body, $this->modal_support_links() );
            }
            
            public function template_default_to_image() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-03.webm' ) ) );
                $this->modal_dialog( 'default_to_image', esc_html__( 'Swatches Default To Image', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_clear_on_reselect() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-04.webm' ) ) );
                $this->modal_dialog( 'clear_on_reselect', esc_html__( 'Swatches Clear on Reselect', 'woo-variation-swatches' ), $body, $this->modal_support_links() );
            }
            
            public function template_hide_out_of_stock_variation() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-05.webm' ) ) );
                $this->modal_dialog( 'hide_out_of_stock_variation', esc_html__( 'Swatches Hide Out Of Stock', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_clickable_out_of_stock_variation() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-06.webm' ) ) );
                $this->modal_dialog( 'clickable_out_of_stock_variation', esc_html__( 'Swatches Clickable Out Of Stock', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_show_variation_stock_info() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-07.webm' ) ) );
                $this->modal_dialog( 'show_variation_stock_info', esc_html__( 'Swatches Show variation stock info.', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_display_limit() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-08.webm' ) ) );
                $this->modal_dialog( 'display_limit', esc_html__( 'Swatches Attribute Display Limit', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_archive_show_availability() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-09.webm' ) ) );
                $this->modal_dialog( 'archive_show_availability', esc_html__( 'Swatches Show Product Availability', 'woo-variation-swatches' ), $body, $this->modal_support_links() );
            }
            
            public function template_archive_swatches_position() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-10.webm' ) ) );
                $this->modal_dialog( 'archive_swatches_position', esc_html__( 'Swatches Display Position', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_show_swatches_on_filter_widget() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-11.webm' ) ) );
                $this->modal_dialog( 'show_swatches_on_filter_widget', esc_html__( 'Swatches Display On Widget', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_enable_catalog_mode() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-12.webm' ) ) );
                $this->modal_dialog( 'enable_catalog_mode', esc_html__( 'Swatches Show as catalog mode', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_enable_single_variation_preview() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-13.webm' ) ) );
                $this->modal_dialog( 'enable_single_variation_preview', esc_html__( 'Swatches Show variation preview', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_enable_large_size() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-14.webm' ) ) );
                $this->modal_dialog( 'enable_large_size', esc_html__( 'Swatches Show variation preview', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_archive_align() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-15.webm' ) ) );
                $this->modal_dialog( 'archive_align', esc_html__( 'Swatches Show variation preview', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_attribute_behavior() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-16.webm' ) ) );
                $this->modal_dialog( 'attribute_behavior', esc_html__( 'Swatches Disabled Attribute style', 'woo-variation-swatches' ), $body, $this->modal_support_links() );
            }
            
            public function template_enable_linkable_variation_url() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-17.webm' ) ) );
                $this->modal_dialog( 'enable_linkable_variation_url', esc_html__( 'Swatches Generate Sharable URL', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_license() {
                
                $links = array(
                    'button_url'  => 'https://getwooplugins.com/my-account/downloads/',
                    'button_text' => esc_html__( 'Get license', 'woo-variation-swatches' ),
                    'link_url'    => 'https://getwooplugins.com/tickets/',
                    'link_text'   => esc_html__( 'Help &amp; Support', 'woo-variation-swatches' )
                );
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-18.webm' ) ) );
                $this->modal_dialog( 'license', esc_html__( 'Swatches License', 'woo-variation-swatches' ), $body, $links );
            }
            
            public function template_show_on_archive() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-19.webm' ) ) );
                $this->modal_dialog( 'show_on_archive', esc_html__( 'Swatches On Archive Page', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            public function template_archive_default_selected() {
                
                $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-20.webm' ) ) );
                $this->modal_dialog( 'archive_default_selected', esc_html__( 'Swatches Archive Default Selected', 'woo-variation-swatches' ), $body, $this->modal_buy_links() );
            }
            
            
            protected function notices() {
                // phpcs:disable WordPress.Security.NonceVerification.Recommended
                if ( $this->is_current_tab() && isset( $_GET[ 'reset' ] ) ) { // WPCS: input var okay, CSRF ok.
                    GetWooPlugins_Admin_Settings::add_message( __( 'Swatches Settings Reset.', 'woo-variation-swatches' ) );
                }
                // phpcs:enable
            }
            
            public function output( $current_tab ) {
                global $current_section;
                
                if ( $current_tab === $this->get_id() && 'tutorial' === $current_section ) {
                    $this->tutorial_section( $current_section );
                } elseif ( $current_tab === $this->get_id() && 'group' === $current_section ) {
                    $this->group_section( $current_section );
                } else {
                    parent::output( $current_tab );
                }
            }
            
            public function get_all_image_sizes() {
                
                $image_subsizes = wp_get_registered_image_subsizes();
                
                return apply_filters( 'woo_variation_swatches_get_all_image_sizes', array_reduce( array_keys( $image_subsizes ), function ( $carry, $item ) use ( $image_subsizes ) {
                    
                    $title  = ucwords( str_ireplace( array( '-', '_' ), ' ', $item ) );
                    $width  = $image_subsizes[ $item ][ 'width' ];
                    $height = $image_subsizes[ $item ][ 'height' ];
                    
                    $carry[ $item ] = sprintf( '%s (%d &times; %d)', $title, $width, $height );
                    
                    return $carry;
                },                                                                                array() ) );
            }
            
            public function get_product_categories() {
                
                $args = array(
                    'orderby'    => 'name',
                    'order'      => 'asc',
                    'hide_empty' => true,
                );
                
                $categories = get_terms( 'product_cat', $args );
                
                $ids = wp_list_pluck( $categories, 'name', 'term_id' );
                
                return apply_filters( 'woo_variation_swatches_get_product_categories', $ids, $categories, $args );
            }
            
            public function plugins_tab( $label ) {
                return sprintf( '<span class="getwooplugins-recommended-plugins-tab dashicons dashicons-admin-plugins"></span> <span>%s</span>', $label );
            }
            
            protected function get_own_sections() {
                $sections = array(
                    ''         => esc_html__( 'General', 'woo-variation-swatches' ),
                    'advanced' => esc_html__( 'Advanced', 'woo-variation-swatches' ),
                    'style'    => esc_html__( 'Styling', 'woo-variation-swatches' ),
                    'single'   => esc_html__( 'Product Page', 'woo-variation-swatches' ),
                    'archive'  => esc_html__( 'Archive / Shop', 'woo-variation-swatches' ),
                    'special'  => esc_html__( 'Special Attributes', 'woo-variation-swatches' ),
                    'group'    => esc_html__( 'Group', 'woo-variation-swatches' ),
                    'license'  => array(
                        'name' => esc_html__( 'License', 'woo-variation-swatches' ),
                        'url'  => false
                    ),
                    'tutorial' => esc_html__( 'Tutorial', 'woo-variation-swatches' ),
                );
                
                if ( current_user_can( 'install_plugins' ) ) {
                    $sections[ 'plugins' ] = array(
                        'name' => $this->plugins_tab( esc_html__( 'Useful Free Plugins', 'woo-variation-swatches' ) ),
                        'url'  => self_admin_url( 'plugin-install.php?s=getwooplugins&tab=search&type=author' ),
                    );
                }
                
                return $sections;
            }
            
            public function tutorial_section( $current_section ) {
                ob_start();
                $settings = $this->get_settings( $current_section );
                include_once dirname( __FILE__ ) . '/html-settings-tutorial.php';
                echo ob_get_clean();
            }
            
            public function group_section( $current_section ) {
                ob_start();
                $settings = $this->get_settings( $current_section );
                include_once dirname( __FILE__ ) . '/html-settings-group.php';
                echo ob_get_clean();
            }
            
            protected function get_settings_for_default_section() {
                
                $settings = array(
                    
                    array(
                        'id'    => 'general_options',
                        'type'  => 'title',
                        'title' => esc_html__( 'General options', 'woo-variation-swatches' ),
                        'desc'  => '',
                    ),
                    
                    array(
                        'id'      => 'enable_stylesheet',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Enable Stylesheet', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Enable default stylesheet.', 'woo-variation-swatches' ),
                        'default' => 'yes'
                    ),
                    
                    array(
                        'id'      => 'enable_tooltip',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Enable Tooltip', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Enable tooltip on each product attribute.', 'woo-variation-swatches' ),
                        'default' => 'yes',
                        'require' => $this->normalize_required_attribute( array( 'enable_stylesheet' => array( 'type' => '!empty' ) ) ),
                    ),
                    
                    array(
                        'id'           => 'shape_style',
                        'title'        => esc_html__( 'Shape Style', 'woo-variation-swatches' ),
                        'type'         => 'radio',
                        'desc'         => esc_html__( 'This controls which shape style used by default.', 'woo-variation-swatches' ),
                        'desc_tip'     => true,
                        'default'      => 'squared',
                        'options'      => array(
                            'rounded' => esc_html__( 'Rounded Shape', 'woo-variation-swatches' ),
                            'squared' => esc_html__( 'Squared Shape', 'woo-variation-swatches' ),
                        ),
                        'help_preview' => true,
                    ),
                    
                    array(
                        'id'           => 'default_to_button',
                        'title'        => esc_html__( 'Dropdowns to Button', 'woo-variation-swatches' ),
                        'desc'         => esc_html__( 'Convert default dropdowns to button.', 'woo-variation-swatches' ),
                        'default'      => 'yes',
                        'type'         => 'checkbox',
                        'help_preview' => true,
                    ),
                    
                    array(
                        'id'      => 'default_to_image',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Dropdowns to Image', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Convert default dropdowns to image type if variation has an image.', 'woo-variation-swatches' ),
                        'default' => 'yes',
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'general_options',
                    ),
                );
                
                return $settings;
            }
            
            protected function get_settings_for_advanced_section() {
                $settings = array(
                    
                    array(
                        'id'    => 'advanced_options',
                        'type'  => 'title',
                        'title' => esc_html__( 'Advanced options', 'woo-variation-swatches' ),
                        'desc'  => '',
                    ),
                    
                    array(
                        'id'           => 'clear_on_reselect',
                        'type'         => 'checkbox',
                        'title'        => esc_html__( 'Clear on Reselect', 'woo-variation-swatches' ),
                        'desc'         => esc_html__( 'Clear selected attribute on select again.', 'woo-variation-swatches' ),
                        'default'      => 'no',
                        'help_preview' => true,
                    ),
                    
                    array(
                        'id'      => 'hide_out_of_stock_variation',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Disable Out of Stock', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Disable Out Of Stock item.', 'woo-variation-swatches' ),
                        'default' => 'yes',
                        // 'help_preview' => true,
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'id'      => 'clickable_out_of_stock_variation',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Clickable Out Of Stock', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Clickable Out Of Stock item.', 'woo-variation-swatches' ),
                        'default' => 'no',
                        //'require' => $this->normalize_required_attribute( array( 'hide_out_of_stock_variation' => array( 'type' => 'empty' ) ) ),
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'id'           => 'attribute_behavior',
                        'type'         => 'radio',
                        'title'        => esc_html__( 'Disabled Attribute style', 'woo-variation-swatches' ),
                        'desc'         => esc_html__( 'Disabled / Out Of Stock attribute will be hide / blur / crossed.', 'woo-variation-swatches' ),
                        'desc_tip'     => true,
                        'options'      => array(
                            'blur'          => esc_html__( 'Blur with cross', 'woo-variation-swatches' ),
                            'blur-no-cross' => esc_html__( 'Blur without cross', 'woo-variation-swatches' ),
                            'hide'          => esc_html__( 'Hide', 'woo-variation-swatches' ),
                        ),
                        'default'      => 'blur',
                        'help_preview' => true,
                    ),
                    
                    array(
                        'id'      => 'attribute_image_size',
                        'type'    => 'select',
                        'title'   => esc_html__( 'Attribute image size', 'woo-variation-swatches' ),
                        'desc'    => has_filter( 'woo_variation_swatches_global_product_attribute_image_size' ) ? __( '<span style="color: red">Attribute image size can be changed by <code>woo_variation_swatches_global_product_attribute_image_size</code> filter hook. So this option will not apply any effect.</span>', 'woo-variation-swatches' ) : sprintf( __( 'Choose attribute image size. <a target="_blank" href="%s">Media Settings</a> or use <strong>Regenerate Thumbnails</strong> plugin.', 'woo-variation-swatches' ), esc_url( admin_url( 'options-media.php' ) ) ),
                        'options' => self::get_all_image_sizes(),
                        'default' => 'variation_swatches_image_size'
                    ),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'advanced_options',
                    ),
                );
                
                return $settings;
            }
            
            protected function get_settings_for_style_section() {
                
                $settings = array(
                    
                    // Start swatches tick and cross coloring
                    array(
                        'id'    => 'style_icons_options',
                        'type'  => 'title',
                        'title' => esc_html__( 'Swatches indicator', 'woo-variation-swatches' ),
                        'desc'  => esc_html__( 'Change swatches indicator color', 'woo-variation-swatches' ),
                    ),
                    
                    array(
                        'id'                => 'tick_color',
                        'type'              => 'color',
                        'title'             => esc_html__( 'Tick Color', 'woo-variation-swatches' ),
                        'desc'              => esc_html__( 'Swatches Selected tick color. Default is: #ffffff', 'woo-variation-swatches' ),
                        'css'               => 'width: 6em;',
                        'default'           => '#ffffff',
                        //'is_new'            => true,
                        'custom_attributes' => array(//    'data-alpha-enabled' => 'true'
                        )
                    ),
                    
                    array(
                        'id'                => 'cross_color',
                        'type'              => 'color',
                        'title'             => esc_html__( 'Cross Color', 'woo-variation-swatches' ),
                        'desc'              => esc_html__( 'Swatches cross color. Default is: #ff0000', 'woo-variation-swatches' ),
                        'css'               => 'width: 6em;',
                        'default'           => '#ff0000',
                        //'is_new'            => true,
                        'custom_attributes' => array(//    'data-alpha-enabled' => 'true'
                        )
                    ),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'style_icons_options',
                    ),
                    
                    // Start single page swatches style
                    array(
                        'id'    => 'single_style_options',
                        'type'  => 'title',
                        'title' => esc_html__( 'Product Page Swatches Size', 'woo-variation-swatches' ),
                        'desc'  => esc_html__( 'Change swatches style on product page', 'woo-variation-swatches' ),
                    ),
                    
                    array(
                        'id'                => 'width',
                        'type'              => 'number',
                        'title'             => esc_html__( 'Width', 'woo-variation-swatches' ),
                        'desc'              => esc_html__( 'Single product variation item width. Default is: 30', 'woo-variation-swatches' ),
                        'css'               => 'width: 50px;',
                        'default'           => '30',
                        'suffix'            => 'px',
                        'custom_attributes' => array(
                            'min'  => 10,
                            'max'  => 200,
                            'step' => 5,
                        ),
                    ),
                    
                    array(
                        'id'                => 'height',
                        'type'              => 'number',
                        'title'             => esc_html__( 'Height', 'woo-variation-swatches' ),
                        'desc'              => esc_html__( 'Single product variation item height. Default is: 30', 'woo-variation-swatches' ),
                        'css'               => 'width: 50px;',
                        'default'           => 30,
                        'suffix'            => 'px',
                        'custom_attributes' => array(
                            'min'  => 10,
                            'max'  => 200,
                            'step' => 5,
                        ),
                    ),
                    
                    array(
                        'id'                => 'single_font_size',
                        'type'              => 'number',
                        'title'             => esc_html__( 'Font Size', 'woo-variation-swatches' ),
                        'desc'              => esc_html__( 'Single product variation item font size. Default is: 16', 'woo-variation-swatches' ),
                        'css'               => 'width: 50px;',
                        'default'           => 16,
                        'suffix'            => 'px',
                        'custom_attributes' => array(
                            'min'  => 8,
                            'max'  => 48,
                            'step' => 2,
                        ),
                    ),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'single_style_options',
                    ),
                
                );
                
                return $settings;
            }
            
            protected function get_settings_for_single_section() {
                $settings = array(
                    array(
                        'id'    => 'single_page_options',
                        'type'  => 'title',
                        'title' => esc_html__( 'Single Product Page', 'woo-variation-swatches' ),
                        'desc'  => esc_html__( 'Settings for single product page', 'woo-variation-swatches' ),
                    ),
                    
                    array(
                        'id'      => 'show_variation_label',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Show selected attribute', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Show selected attribute variation name beside the title.', 'woo-variation-swatches' ),
                        'default' => 'yes',
                        // 'is_new'  => true,
                    ),
                    
                    array(
                        'id'       => 'variation_label_separator',
                        'type'     => 'text',
                        'title'    => esc_html__( 'Variation label separator', 'woo-variation-swatches' ),
                        'desc'     => sprintf( __( 'Variation label separator. Default: %s.', 'woo-variation-swatches' ), '<code>:</code>' ),
                        'desc_tip' => true,
                        'default'  => ':',
                        'css'      => 'width: 30px;',
                        'require'  => $this->normalize_required_attribute( array(
                                                                               'show_variation_label' => array(
                                                                                   'type'  => '==',
                                                                                   'value' => '1'
                                                                               )
                                                                           ) ),
                        // 'is_new'   => true,
                    ),
                    
                    array(
                        'id'      => 'enable_single_preloader',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Enable Preloader', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Enable single product page swatches preloader.', 'woo-variation-swatches' ),
                        'default' => 'yes',
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'id'      => 'enable_linkable_variation_url',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Generate variation url', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Generate sharable url based on selected variation attributes.', 'woo-variation-swatches' ),
                        'default' => 'no',
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'id'      => 'show_variation_stock_info',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Variation stock info', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Show variation product stock info.', 'woo-variation-swatches' ),
                        'default' => 'no',
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'id'                => 'display_limit',
                        'type'              => 'number',
                        // 'size'    => 'tiny',
                        'title'             => esc_html__( 'Attribute display limit', 'woo-variation-swatches' ),
                        'desc'              => esc_html__( 'Single Product page attribute display limit. Default is 0. Means no limit.', 'woo-variation-swatches' ),
                        'desc_tip'          => true,
                        'custom_attributes' => array( 'min' => 0 ),
                        'css'               => 'width: 80px;',
                        'default'           => '0',
                        'is_pro'            => true,
                    ),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'single_page_options',
                    ),
                );
                
                return $settings;
            }
            
            protected function get_settings_for_archive_section() {
                $settings = array(
                    
                    array(
                        'id'    => 'archive_options',
                        'type'  => 'title',
                        'title' => esc_html__( 'Visual Section', 'woo-variation-swatches' ),
                        'desc'  => esc_html__( 'Advanced change some visual styles on shop / archive page', 'woo-variation-swatches' ),
                    ),
                    
                    array(
                        'id'      => 'show_on_archive',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Enable Swatches', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Show swatches on archive / shop page.', 'woo-variation-swatches' ),
                        'default' => 'yes',
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'id'      => 'enable_archive_preloader',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Enable Preloader', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Enable archive page swatches preloader.', 'woo-variation-swatches' ),
                        'default' => 'yes',
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'id'      => 'archive_show_availability',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Show Product Availability', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Show Product availability stock info.', 'woo-variation-swatches' ),
                        'default' => 'no',
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'id'      => 'archive_default_selected',
                        'type'    => 'checkbox',
                        //'is_pro' => true,
                        //'is_new' => true,
                        //'help_preview' => true,
                        'title'   => esc_html__( 'Show default selected', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Show default selected attribute swatches on archive / shop page.', 'woo-variation-swatches' ),
                        'default' => 'yes',
                        'is_pro'  => true
                    ),
                    
                    
                    array(
                        'id'      => 'archive_swatches_position',
                        'type'    => 'radio',
                        'title'   => esc_html__( 'Display position', 'woo-variation-swatches' ),
                        'desc'    => sprintf( __( 'Show archive swatches position. <span style="color: red">Note:</span> Some theme remove default woocommerce hooks that why it\'s may not work as expected. For theme compatibility <a target="_blank" href="%s">please open a ticket</a>.', 'woo-variation-swatches' ), 'https://getwooplugins.com/tickets/' ),
                        //'desc_tip' => true,
                        'default' => 'after',
                        'options' => array(
                            'before' => esc_html__( 'Before add to cart button', 'woo-variation-swatches' ),
                            'after'  => esc_html__( 'After add to cart button', 'woo-variation-swatches' )
                        ),
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'id'       => 'archive_align',
                        'type'     => 'select',
                        'size'     => 'tiny',
                        'title'    => esc_html__( 'Swatches align', 'woo-variation-swatches' ),
                        'desc'     => esc_html__( 'Swatches align on archive page', 'woo-variation-swatches' ),
                        'desc_tip' => true,
                        'css'      => 'width: 100px;',
                        'default'  => 'flex-start',
                        'options'  => array(
                            'flex-start' => esc_html__( 'Left', 'woo-variation-swatches' ),
                            'center'     => esc_html__( 'Center', 'woo-variation-swatches' ),
                            'flex-end'   => esc_html__( 'Right', 'woo-variation-swatches' )
                        ),
                        'is_pro'   => true,
                    ),
                    
                    array(
                        'id'      => 'show_swatches_on_filter_widget',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Show on filter widget', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Show variation swatches on filter widget.', 'woo-variation-swatches' ),
                        'default' => 'yes',
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'archive_options',
                    ),
                );
                
                return $settings;
            }
            
            protected function get_settings_for_special_section() {
                $settings = array(
                    
                    // Catalog mode
                    array(
                        'id'    => 'catalog_mode_options',
                        'type'  => 'title',
                        'title' => esc_html__( 'Catalog mode', 'woo-variation-swatches' ),
                        'desc'  => esc_html__( 'Show single attribute as catalog mode on shop / archive pages. Catalog mode only change image based on selected variation.', 'woo-variation-swatches' ),
                    ),
                    
                    array(
                        'id'      => 'enable_catalog_mode',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Show Single Attribute', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Show Single Attribute taxonomies on archive page.', 'woo-variation-swatches' ),
                        'default' => 'no',
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'catalog_mode_options',
                    ),
                    
                    array(
                        'id'    => 'single_variation_image_preview_options',
                        'type'  => 'title',
                        'title' => esc_html__( 'Single Variation Image Preview', 'woo-variation-swatches' ),
                        'desc'  => esc_html__( 'Switch variation image when single attribute selected on product page.', 'woo-variation-swatches' ),
                    ),
                    
                    array(
                        'id'      => 'enable_single_variation_preview',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Variation Image Preview', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Show single attribute variation image based on first attribute select on product page.', 'woo-variation-swatches' ),
                        'default' => 'no',
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'single_variation_image_preview_options',
                    ),
                    
                    // Attribute large size
                    array(
                        'id'    => 'attr_large_size_options',
                        'type'  => 'title',
                        'title' => esc_html__( 'Large Size Attribute Section', 'woo-variation-swatches' ),
                        'desc'  => esc_html__( 'Make a attribute taxonomies size large on single product', 'woo-variation-swatches' ),
                    ),
                    
                    array(
                        'id'      => 'enable_large_size',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Show First Attribute In Large Size', 'woo-variation-swatches' ),
                        'desc'    => esc_html__( 'Show Attribute taxonomies in large size.', 'woo-variation-swatches' ),
                        'default' => 'no',
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'attr_large_size_options',
                    ),
                );
                
                return $settings;
            }
            
        }
    endif;