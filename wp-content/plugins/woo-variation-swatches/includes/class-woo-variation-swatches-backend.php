<?php
    defined( 'ABSPATH' ) || exit;
    
    if ( ! class_exists( 'Woo_Variation_Swatches_Backend' ) ) {
        class Woo_Variation_Swatches_Backend {
            
            protected static $_instance = null;
            
            protected $admin_menu;
            
            protected function __construct() {
                $this->includes();
                $this->hooks();
                $this->init();
                do_action( 'woo_variation_swatches_backend_loaded', $this );
            }
            
            public static function instance() {
                if ( is_null( self::$_instance ) ) {
                    self::$_instance = new self();
                }
                
                return self::$_instance;
            }
            
            protected function includes() {
                require_once dirname( __FILE__ ) . '/class-woo-variation-swatches-term-meta.php';
                require_once dirname( __FILE__ ) . '/class-woo-variation-swatches-export-import.php';
                
                require_once dirname( __FILE__ ) . '/getwooplugins/class-getwooplugins-plugin-deactivate-feedback.php';
                require_once dirname( __FILE__ ) . '/getwooplugins/class-getwooplugins-admin-menus.php';
                
                require_once dirname( __FILE__ ) . '/class-woo-variation-swatches-deactivate-feedback.php';
                require_once dirname( __FILE__ ) . '/class-woo-variation-swatches-product-edit-panel.php';
                
                require_once dirname( __FILE__ ) . '/class-woo-variation-swatches-wc-api-response.php';
            }
            
            protected function hooks() {
                
                add_filter( 'getwooplugins_get_settings_pages', array( $this, 'init_settings' ) );
                add_filter( 'product_attributes_type_selector', array( $this, 'attribute_types' ) );
                
                add_action( 'admin_init', array( $this, 'add_attribute_meta' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
                add_action( 'woocommerce_product_option_terms', array( $this, 'product_option_terms' ), 10, 3 );
                
                add_filter( 'plugin_action_links_' . plugin_basename( WOO_VARIATION_SWATCHES_PLUGIN_FILE ), array(
                    $this,
                    'plugin_action_links'
                ) );
                add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
            }
            
            protected function init() {
                $this->get_admin_menu();
                $this->get_deactivate_feedback();
                $this->get_export_import();
                $this->get_edit_panel();
                $this->wc_api_response();
            }
            
            // Start
            public function wc_api_response() {
                return Woo_Variation_Swatches_WC_API_Response::instance();
            }
            
            public function get_edit_panel() {
                return Woo_Variation_Swatches_Product_Edit_Panel::instance();
            }
            
            public function get_admin_menu() {
                return GetWooPlugins_Admin_Menus::instance();
            }
            
            public function get_deactivate_feedback() {
                return Woo_Variation_Swatches_Deactivate_Feedback::instance();
            }
            
            public function get_export_import() {
                return Woo_Variation_Swatches_Export_Import::instance();
            }
            
            public function plugin_row_meta( $links, $file ) {
                if ( plugin_basename( WOO_VARIATION_SWATCHES_PLUGIN_FILE ) !== $file ) {
                    return $links;
                }
                
                $row_meta = apply_filters( 'woo_variation_swatches_plugin_row_meta', array(
                    'docs'    => '<a target="_blank" href="' . esc_url( 'https://getwooplugins.com/documentation/woocommerce-variation-swatches/' ) . '" aria-label="' . esc_attr__( 'View documentation', 'woo-variation-swatches' ) . '">' . esc_html__( 'Documentation', 'woo-variation-swatches' ) . '</a>',
                    'videos'  => '<a target="_blank" href="' . esc_url( 'https://www.youtube.com/channel/UC6F21JXiLUPO7sm-AYlA3Ig/videos' ) . '" aria-label="' . esc_attr__( 'Video Tutorials', 'woo-variation-swatches' ) . '">' . esc_html__( 'Video Tutorials', 'woo-variation-swatches' ) . '</a>',
                    'support' => '<a target="_blank" href="' . esc_url( 'https://getwooplugins.com/tickets/' ) . '" aria-label="' . esc_attr__( 'Help & Support', 'woo-variation-swatches' ) . '">' . esc_html__( 'Help & Support', 'woo-variation-swatches' ) . '</a>',
                ) );
                
                return array_merge( $links, $row_meta );
            }
            
            public function plugin_action_links( $links ) {
                $action_links = array(
                    'settings' => '<a href="' . esc_url( $this->get_admin_menu()->get_settings_link( 'woo_variation_swatches' ) ) . '" aria-label="' . esc_attr__( 'View Swatches settings', 'woo-variation-swatches' ) . '">' . esc_html__( 'Settings', 'woo-variation-swatches' ) . '</a>',
                );
                
                
                $pro_links = array(
                    'gwp-go-pro-action-link' => '<a target="_blank" href="' . esc_url( $this->get_pro_link() ) . '" aria-label="' . esc_attr__( 'Go Pro', 'woo-variation-swatches' ) . '">' . esc_html__( 'Go Pro', 'woo-variation-swatches' ) . '</a>',
                );
                
                if ( woo_variation_swatches()->is_pro() ) {
                    $pro_links = array();
                }
                
                return array_merge( $action_links, $links, $pro_links );
            }
            
            public function product_option_terms( $attribute_taxonomy, $i, $attribute ) {
                
                if ( 'select' !== $attribute_taxonomy->attribute_type && in_array( $attribute_taxonomy->attribute_type, array_keys( $this->attribute_types() ) ) ) {
                    
                    $name = sprintf( 'attribute_values[%s][]', esc_attr( $i ) );
                    ?>
                    <select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'woo-variation-swatches' ); ?>" class="multiselect attribute_values wc-enhanced-select" name="<?php echo esc_attr( $name ) ?>">
                        <?php
                            $args = array(
                                'orderby'    => ! empty( $attribute_taxonomy->attribute_orderby ) ? $attribute_taxonomy->attribute_orderby : 'name',
                                'hide_empty' => 0,
                            );
                            
                            $all_terms = get_terms( $attribute->get_taxonomy(), apply_filters( 'woocommerce_product_attribute_terms', $args ) );
                            if ( $all_terms ) {
                                foreach ( $all_terms as $term ) {
                                    $options = $attribute->get_options();
                                    $options = ! empty( $options ) ? $options : array();
                                    echo '<option value="' . esc_attr( $term->term_id ) . '"' . wc_selected( $term->term_id, $options ) . '>' . esc_html( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
                                }
                            }
                        ?>
                    </select>
                    <button class="button plus select_all_attributes"><?php esc_html_e( 'Select all', 'woo-variation-swatches' ); ?></button>
                    <button class="button minus select_no_attributes"><?php esc_html_e( 'Select none', 'woo-variation-swatches' ); ?></button>
                    <button class="button fr plus add_new_attribute"><?php esc_html_e( 'Add new', 'woo-variation-swatches' ); ?></button>
                    
                    <?php
                }
                
            }
            
            public function admin_scripts() {
                $suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
                $screen    = get_current_screen();
                $screen_id = $screen ? $screen->id : '';
                
                wp_enqueue_style( 'wp-color-picker' );
                wp_enqueue_style( 'woo-variation-swatches-admin', woo_variation_swatches()->assets_url( "/css/admin{$suffix}.css" ), array(), woo_variation_swatches()->assets_version( "/css/admin{$suffix}.css" ) );
                
                if ( in_array( $screen_id, array( 'product' ) ) ) {
                    wp_deregister_script( 'serializejson' );
                    wp_register_script( 'serializejson', woo_variation_swatches()->assets_url( "/js/jquery.serializejson{$suffix}.js" ), array( 'jquery' ), '3.2.1' );
                }
                
                
                wp_enqueue_script( 'wp-color-picker-alpha', woo_variation_swatches()->assets_url( "/js/wp-color-picker-alpha{$suffix}.js" ), array(
                    'jquery',
                    'wp-color-picker'
                ),                 woo_variation_swatches()->assets_version( "/js/wp-color-picker-alpha{$suffix}.js" ), true );
                
                wp_enqueue_script( 'gwp-form-field-dependency', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/getwooplugins/js/getwooplugins-form-field-dependency.js', array( 'jquery' ), filemtime( untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/getwooplugins/js/getwooplugins-form-field-dependency.js' ), true );
                
                wp_enqueue_script( 'woo-variation-swatches-admin', woo_variation_swatches()->assets_url( "/js/admin{$suffix}.js" ), array(
                    'jquery',
                    'wp-color-picker-alpha',
                    'wc-enhanced-select',
                    'serializejson'
                ),                 woo_variation_swatches()->assets_version( "/js/admin{$suffix}.js" ), true );
                
                
                wp_localize_script( 'woo-variation-swatches-admin', 'woo_variation_swatches_admin', array(
                    'media_title'    => esc_html__( 'Choose an Image', 'woo-variation-swatches' ),
                    'dialog_title'   => esc_html__( 'Add Attribute', 'woo-variation-swatches' ),
                    'dialog_save'    => esc_html__( 'Add', 'woo-variation-swatches' ),
                    'dialog_cancel'  => esc_html__( 'Cancel', 'woo-variation-swatches' ),
                    'button_title'   => esc_html__( 'Use Image', 'woo-variation-swatches' ),
                    'add_media'      => esc_html__( 'Add Media', 'woo-variation-swatches' ),
                    'ajaxurl'        => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
                    'wc_ajax_url'    => WC_AJAX::get_endpoint( '%%endpoint%%' ),
                    'settings_url'   => esc_url( $this->get_admin_menu()->get_settings_link( 'woo_variation_swatches' ) ),
                    'settings_title' => esc_html__( 'Variation Swatches Settings', 'woo-variation-swatches' ),
                    'nonce'          => wp_create_nonce( 'woo_variation_swatches_admin' ),
                    'reset_notice'   => esc_html__( 'Are you sure you want to reset it to default setting?', 'woo-variation-swatches' ),
                    'nav_warning'    => esc_html__( 'Please save changed first.', 'woo-variation-swatches' ),
                ) );
            }
            
            public function attribute_types() {
                
                return array(
                    'select' => esc_html__( 'Select', 'woo-variation-swatches' ),
                    'color'  => esc_html__( 'Color', 'woo-variation-swatches' ),
                    'image'  => esc_html__( 'Image', 'woo-variation-swatches' ),
                    'button' => esc_html__( 'Button', 'woo-variation-swatches' ),
                    'radio'  => esc_html__( 'Radio', 'woo-variation-swatches' ),
                );
            }
            
            public function extended_attribute_types() {
                $attribute_types = $this->attribute_types();
                
                // $attribute_types[ 'custom' ] = esc_html__( 'Custom', 'woo-variation-swatches' );
                $attribute_types[ 'mixed' ] = esc_html__( 'Mixed', 'woo-variation-swatches' );
                
                return $attribute_types;
            }
            
            public function filtered_attribute_types() {
                
                $attribute_types = $this->attribute_types();
                unset( $attribute_types[ 'select' ], $attribute_types[ 'radio' ] );
                
                return $attribute_types;
            }
            
            public function load_settings() {
                include_once dirname( __FILE__ ) . '/class-woo-variation-swatches-settings.php';
                
                return new Woo_Variation_Swatches_Settings();
            }
            
            public function init_settings( $settings ) {
                
                $settings[] = $this->load_settings();
                
                return $settings;
            }
            
            public function add_attribute_meta() {
                
                $fields               = $this->attribute_meta_fields();
                $attribute_taxonomies = wc_get_attribute_taxonomies();
                
                if ( $attribute_taxonomies ) {
                    foreach ( $attribute_taxonomies as $taxonomy ) {
                        $attribute_name = wc_attribute_taxonomy_name( $taxonomy->attribute_name );
                        $attribute_type = $taxonomy->attribute_type;
                        if ( in_array( $attribute_type, array_keys( $fields ) ) ) {
                            new Woo_Variation_Swatches_Term_Meta( $attribute_name, 'product', $fields[ $attribute_type ] );
                        }
                    }
                }
            }
            
            public function attribute_meta_fields() {
                
                $fields = array();
                
                $fields[ 'color' ] = array(
                    array(
                        'label' => esc_html__( 'Color', 'woo-variation-swatches' ), // <label>
                        'desc'  => esc_html__( 'Choose a color', 'woo-variation-swatches' ), // description
                        'id'    => 'product_attribute_color', // name of field
                        'type'  => 'color'
                    )
                );
                
                $fields[ 'image' ] = array(
                    array(
                        'label' => esc_html__( 'Image', 'woo-variation-swatches' ), // <label>
                        'desc'  => esc_html__( 'Choose an Image', 'woo-variation-swatches' ), // description
                        'id'    => 'product_attribute_image', // name of field
                        'type'  => 'image'
                    )
                );
                
                return $fields;
                
            }
            
            public function get_attribute_taxonomy( $attribute_name ) {
                
                $taxonomy_attributes = wc_get_attribute_taxonomies();
                
                // $attribute_name = str_ireplace( 'pa_', '', wc_sanitize_taxonomy_name( $attribute_name ) );
                if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
                    $attribute_name = str_replace( 'pa_', '', wc_sanitize_taxonomy_name( $attribute_name ) );
                }
                
                foreach ( $taxonomy_attributes as $attribute ) {
                    
                    // Skip taxonomy attributes that didn't match the query.
                    /*if ( false === stripos( $attribute->attribute_name, $attribute_name ) ) {
                        continue;
                    }*/
                    
                    if ( $attribute->attribute_name !== $attribute_name ) {
                        continue;
                    }
                    
                    return $attribute;
                }
                
                return false;
            }
            
            public function get_pro_link() {
                
                $affiliate_id = apply_filters( 'gwp_affiliate_id', 0 );
                
                $link_args = array();
                
                if ( ! empty( $affiliate_id ) ) {
                    $link_args[ 'ref' ] = esc_html( $affiliate_id );
                }
                
                $link_args = apply_filters( 'woo_variation_swatches_get_pro_link_args', $link_args );
                
                return add_query_arg( $link_args, 'https://getwooplugins.com/plugins/woocommerce-variation-swatches/' );
            }
        }
    }