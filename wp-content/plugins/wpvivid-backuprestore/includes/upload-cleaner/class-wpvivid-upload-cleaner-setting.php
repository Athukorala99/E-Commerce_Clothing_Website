<?php
if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

if ( ! class_exists( 'WP_List_Table' ) )
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPvivid_Exclude_Files_List extends WP_List_Table
{
    public $list;
    public $type;
    public $page_num;
    public $parent;

    public function __construct( $args = array() )
    {
        global $wpdb;
        parent::__construct(
            array(
                'plural' => 'upload_files',
                'screen' => 'upload_files',
            )
        );
    }

    public function set_parent($parent)
    {
        $this->parent=$parent;
    }

    public function set_list($list,$page_num=1)
    {
        $this->list=$list;
        $this->page_num=$page_num;
    }

    protected function get_table_classes()
    {
        return array( 'widefat striped' );
    }

    public function get_columns()
    {
        $sites_columns = array(
            'cb'          => ' ',
            'file_regex'    => __( 'File Regex', 'wpvivid-backuprestore' )
        );

        return $sites_columns;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function column_cb( $item )
    {
        echo '<input type="checkbox" name="regex_list" />';

    }

    public function column_file_regex( $item )
    {
        echo esc_html($item);
    }

    public function has_items()
    {
        return !empty($this->list);
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items =sizeof($this->list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function display_rows()
    {
        $this->_display_rows( $this->list );
    }

    private function _display_rows( $list )
    {
        $page=$this->get_pagenum();

        $page_list=$list;
        $temp_page_list=array();

        $count=0;
        while ( $count<$page )
        {
            $temp_page_list = array_splice( $page_list, 0, 10);
            $count++;
        }

        foreach ( $temp_page_list as $key=>$item)
        {
            $this->single_row($item);
        }
    }

    public function single_row($item)
    {
        ?>
        <tr file_regex="<?php echo esc_attr($item)?>">
            <?php $this->single_row_columns( $item ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items, 'wpvivid-backuprestore' ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page', 'wpvivid-backuprestore' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page', 'wpvivid-backuprestore' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page', 'wpvivid-backuprestore' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page'  type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label  class="screen-reader-text">' . __( 'Current Page', 'wpvivid-backuprestore' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging', 'wpvivid-backuprestore' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page', 'wpvivid-backuprestore' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page', 'wpvivid-backuprestore' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php echo esc_attr($css_type); ?>">
                <div class="alignleft actions bulkactions">
                    <label for="wpvivid_uc_exclude_regex_bulk_action" class="screen-reader-text">Select bulk action</label>
                    <select name="action" id="wpvivid_uc_exclude_regex_bulk_action">
                        <option value="remove_exclude_regex">Remove</option>
                    </select>
                    <input type="submit" class="button action" value="Apply">
                </div>
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>
                <br class="clear" />
            </div>
            <?php
        }
        else
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php echo esc_attr($css_type); ?>">
                <div class="alignleft actions bulkactions">
                    <label for="wpvivid_uc_exclude_regex_bulk_action" class="screen-reader-text">Select bulk action</label>
                    <select name="action" id="wpvivid_uc_exclude_regex_bulk_action">
                        <option value="remove_exclude_regex">Remove</option>
                    </select>
                    <input type="submit" class="button action" value="Apply">
                </div>
                <br class="clear" />
            </div>
            <?php
        }
    }

    public function display()
    {
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo esc_attr(implode( ' ', $this->get_table_classes() )); ?>" >
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"
                <?php
                if ( $singular ) {
                    echo esc_attr(" data-wp-lists='list:$singular'");
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

            <tfoot>
            <tr>
                <?php $this->print_column_headers( false ); ?>
            </tr>
            </tfoot>

        </table>
        <?php
    }
}

class WPvivid_Post_Type_List extends WP_List_Table
{
    public $list;
    public $type;
    public $page_num;
    public $parent;

    public function __construct( $args = array() )
    {
        global $wpdb;
        parent::__construct(
            array(
                'plural' => 'upload_files',
                'screen' => 'upload_files',
            )
        );
    }

    public function set_parent($parent)
    {
        $this->parent=$parent;
    }

    public function set_list($list,$page_num=1)
    {
        $this->list=$list;
        $this->page_num=$page_num;
    }

    protected function get_table_classes()
    {
        return array( 'widefat striped' );
    }

    public function get_columns()
    {
        $sites_columns = array(
            'cb'          => ' ',
            'post_type'    => __( 'Post Type', 'wpvivid-backuprestore' )
        );

        return $sites_columns;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function column_cb( $item )
    {
        echo '<input type="checkbox" name="post_type" />';

    }

    public function column_post_type( $item )
    {
        echo esc_attr($item);
    }

    public function has_items()
    {
        return !empty($this->list);
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items =sizeof($this->list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function display_rows()
    {
        $this->_display_rows( $this->list );
    }

    private function _display_rows( $list )
    {
        $page=$this->get_pagenum();

        $page_list=$list;
        $temp_page_list=array();

        $count=0;
        while ( $count<$page )
        {
            $temp_page_list = array_splice( $page_list, 0, 10);
            $count++;
        }

        foreach ( $temp_page_list as $key=>$item)
        {
            $this->single_row($item);
        }
    }

    public function single_row($item)
    {
        ?>
        <tr post_type="<?php echo esc_attr($item)?>">
            <?php $this->single_row_columns( $item ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items, 'wpvivid-backuprestore' ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page', 'wpvivid-backuprestore' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page', 'wpvivid-backuprestore' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page', 'wpvivid-backuprestore' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page'  type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label  class="screen-reader-text">' . __( 'Current Page', 'wpvivid-backuprestore' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging', 'wpvivid-backuprestore' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page', 'wpvivid-backuprestore' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page', 'wpvivid-backuprestore' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php echo esc_attr($css_type); ?>">
                <div class="alignleft actions bulkactions">
                    <label for="wpvivid_uc_post_type_bulk_action" class="screen-reader-text">Select bulk action</label>
                    <select name="action" id="wpvivid_uc_post_type_bulk_action">
                        <option value="remove_post_type">Remove</option>
                    </select>
                    <input type="submit" class="button action" value="Apply">
                </div>
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>
                <br class="clear" />
            </div>
            <?php
        }
        else
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php echo esc_attr($css_type); ?>">
                <div class="alignleft actions bulkactions">
                    <label for="wpvivid_uc_post_type_bulk_action" class="screen-reader-text">Select bulk action</label>
                    <select name="action" id="wpvivid_uc_post_type_bulk_action">
                        <option value="remove_post_type">Remove</option>
                    </select>
                    <input type="submit" class="button action" value="Apply">
                </div>
                <br class="clear" />
            </div>
            <?php
        }
    }

    public function display()
    {
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo esc_attr(implode( ' ', $this->get_table_classes() )); ?>" >
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"
                <?php
                if ( $singular ) {
                    echo esc_attr(" data-wp-lists='list:$singular'");
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

            <tfoot>
            <tr>
                <?php $this->print_column_headers( false ); ?>
            </tr>
            </tfoot>

        </table>
        <?php
    }
}

class WPvivid_Uploads_Cleaner_Setting
{
    public function __construct()
    {
        add_filter('wpvivid_add_setting_tab_page', array($this, 'add_setting_tab_page'), 10);
        add_action('wpvivid_setting_add_uc_cell',array($this, 'add_uc_cell'),13);
        add_filter('wpvivid_set_general_setting', array($this, 'set_general_setting'), 11, 3);

        add_filter('wpvivid_pro_setting_tab', array($this, 'setting_tab'), 13);

        add_action('wp_ajax_wpvivid_get_exclude_files_list',array($this, 'get_exclude_files_list'));
        add_action('wp_ajax_wpvivid_delete_exclude_files',array($this, 'delete_exclude_files'));

        add_action('wp_ajax_wpvivid_get_post_type_list',array($this, 'get_post_type_list'));
        add_action('wp_ajax_wpvivid_delete_post_type',array($this, 'delete_post_type'));
    }

    public function setting_tab($tabs)
    {
        if(current_user_can('manage_options'))
        {
            $tab['title']='Media Cleaner Settings';
            $tab['slug']='upload_cleaner';
            $tab['callback']= array($this, 'output_setting');
            $args['is_parent_tab']=0;
            $args['transparency']=1;
            $tab['args']=$args;
            $tabs[]=$tab;
        }
        return $tabs;
    }

    public function set_general_setting($setting_data, $setting, $options)
    {
        if(isset($setting['wpvivid_uc_scan_limit']))
            $setting_data['wpvivid_uc_scan_limit'] = intval($setting['wpvivid_uc_scan_limit']);

        if(isset($setting['wpvivid_uc_files_limit']))
            $setting_data['wpvivid_uc_files_limit'] = intval($setting['wpvivid_uc_files_limit']);

        if(isset($setting['wpvivid_uc_scan_file_types'])&&is_array($setting['wpvivid_uc_scan_file_types']))
            $setting_data['wpvivid_uc_scan_file_types'] = $setting['wpvivid_uc_scan_file_types'];

        if(isset($setting['wpvivid_uc_post_types'])&&is_array($setting['wpvivid_uc_post_types']))
            $setting_data['wpvivid_uc_post_types'] = $setting['wpvivid_uc_post_types'];

        if(isset($setting['wpvivid_uc_quick_scan']))
            $setting_data['wpvivid_uc_quick_scan'] = boolval($setting['wpvivid_uc_quick_scan']);

        if(isset($setting['wpvivid_uc_delete_media_when_delete_file']))
            $setting_data['wpvivid_uc_delete_media_when_delete_file'] = boolval($setting['wpvivid_uc_delete_media_when_delete_file']);

        if(isset($setting['wpvivid_uc_exclude_files_regex']))
            $setting_data['wpvivid_uc_exclude_files_regex'] = $setting['wpvivid_uc_exclude_files_regex'];

        return $setting_data;
    }

    public function add_setting_tab_page($setting_array)
    {
        $setting_array['uc_setting'] = array('index' => '3', 'tab_func' =>  array($this, 'wpvivid_settingpage_add_tab_uc'), 'page_func' => array($this, 'wpvivid_settingpage_add_page_uc'));
        return $setting_array;
    }

    public function wpvivid_settingpage_add_tab_uc()
    {
        ?>
        <a href="#" id="wpvivid_tab_uc_setting" class="nav-tab setting-nav-tab" onclick="switchsettingTabs(event,'page-uc-setting')"><?php esc_html_e('Media Cleaner Settings', 'wpvivid-backuprestore'); ?></a>
        <?php
    }

    public function wpvivid_settingpage_add_page_uc()
    {
        ?>
        <div class="setting-tab-content wpvivid_tab_uc_setting" id="page-uc-setting" style="margin-top: 10px; display: none;">
            <?php do_action('wpvivid_setting_add_uc_cell'); ?>
        </div>
        <?php
    }

    public function output_setting()
    {
        ?>
        <div style="margin-top: 10px;">
            <?php
            $this->add_uc_cell();
            ?>
            <div><input class="button-primary wpvivid_setting_general_save" type="submit" value="<?php esc_attr_e( 'Save Changes', 'wpvivid-backuprestore' ); ?>" /></div>
        </div>
        <?php
    }

    public function add_uc_cell()
    {
        $scan_limit=get_option('wpvivid_uc_scan_limit',20);
        $files_limit=get_option('wpvivid_uc_files_limit',100);

        $default_file_types=array();
        $default_file_types[]='png';
        $default_file_types[]='jpg';
        $default_file_types[]='jpeg';
        $scan_file_types=get_option('wpvivid_uc_scan_file_types',$default_file_types);

        $quick_scan=get_option('wpvivid_uc_quick_scan',false);

        if($quick_scan)
        {
            $quick_scan='checked';
        }
        else
        {
            $quick_scan='';
        }

        //$default_post_types=array();
        //$default_post_types[]='attachment';
        //$default_post_types[]='revision';
        //$default_post_types[]='auto-draft';
        //$default_post_types[]='nav_menu_item';
        //$default_post_types[]='shop_order';
        //$default_post_types[]='shop_order_refund';
        //$default_post_types[]='oembed_cache';
        //$post_types=get_option('wpvivid_uc_post_types',$default_post_types);

        $delete_media_when_delete_file=get_option('wpvivid_uc_delete_media_when_delete_file',false);

        if($delete_media_when_delete_file)
        {
            $delete_media_when_delete_file='checked';
        }
        else
        {
            $delete_media_when_delete_file='';
        }

        $exclude_path=get_option('wpvivid_uc_exclude_files_regex', '');
        ?>
        <div class="postbox schedule-tab-block setting-page-content">
            <div class="wpvivid-element-space-bottom">
                <label for="wpvivid_uc_scan_file_types">
                    <input style="margin: 4px;" id="wpvivid_uc_quick_scan" type="checkbox" option="setting" name="wpvivid_uc_quick_scan" <?php echo esc_attr($quick_scan); ?> />
                    <span><strong><?php esc_html_e('Enable Quick Scan', 'wpvivid-backuprestore'); ?></strong></span>
                </label>
            </div>
            <div class="wpvivid-element-space-bottom">
                <span><?php esc_html_e('Checking this option will speed up your scans but may produce lower accuracy.', 'wpvivid-backuprestore'); ?></span>
            </div>
            <div class="wpvivid-element-space-bottom">
                <label for="wpvivid_uc_delete_media_when_delete_file">
                    <input style="margin: 4px;" id="wpvivid_uc_delete_media_when_delete_file" style="margin-right: 4px;" type="checkbox" option="setting" name="wpvivid_uc_delete_media_when_delete_file" <?php echo esc_attr($delete_media_when_delete_file); ?> />
                    <span><strong><?php esc_html_e('Delete Image URL', 'wpvivid-backuprestore'); ?></strong></span>
                </label>
            </div>
            <div class="wpvivid-element-space-bottom">
                <span><?php esc_html_e('With this option checked, when the image is deleted, the corresponding image url in the database that is not used anywhere on your website will also be deleted.', 'wpvivid-backuprestore'); ?></span>
            </div>
        </div>

        <div class="postbox schedule-tab-block setting-page-content">
            <div class="wpvivid-element-space-bottom"><strong><?php esc_html_e('Posts Quantity Processed Per Request', 'wpvivid-backuprestore'); ?></strong></div>
            <div class="wpvivid-element-space-bottom">
                <input style="margin: 0px;" type="text" placeholder="20" option="setting" name="wpvivid_uc_scan_limit" id="wpvivid_uc_scan_limit" class="all-options" value="<?php echo esc_attr($scan_limit); ?>" onkeyup="value=value.replace(/\D/g,'')" />
            </div>
            <div class="wpvivid-element-space-bottom">
                <?php esc_html_e( 'Set how many posts to process per request. The value should be set depending on your server performance and the recommended value is 20.', 'wpvivid-backuprestore' ); ?>
            </div>
            <div class="wpvivid-element-space-bottom"><strong><?php esc_html_e('Media Files Quantity Processed Per Request', 'wpvivid-backuprestore'); ?></strong></div>
            <div class="wpvivid-element-space-bottom">
                <input style="margin: 0px;" type="text" placeholder="100" option="setting" name="wpvivid_uc_files_limit" id="wpvivid_uc_files_limit" class="all-options" value="<?php echo esc_attr($files_limit); ?>" onkeyup="value=value.replace(/\D/g,'')" />
            </div>
            <div class="wpvivid-element-space-bottom">
                <?php esc_html_e( 'Set how many media files to process per request. The value should be set depending on your server performance and the recommended value is 100.', 'wpvivid-backuprestore' ); ?>
            </div>

            <div class="wpvivid-element-space-bottom"><strong><?php esc_html_e('Exclude images by folder path', 'wpvivid-backuprestore'); ?></strong></div>
            <div class="wpvivid-element-space-bottom">
                <textarea placeholder="Example:&#10;/wp-content/uploads/19/03/&#10;/wp-content/uploads/19/04/" option="setting" name="wpvivid_uc_exclude_files_regex" style="width:100%; height:200px; overflow-x:auto;"><?php echo esc_html($exclude_path); ?></textarea>
            </div>
        </div>
        <?php
    }

    public function get_exclude_files_list()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        try
        {
            $file_exclude=array_map( 'sanitize_text_field', $_POST['file_exclude']);
            if(isset($file_exclude)&&!empty($file_exclude))
            {
                $white_list=get_option('wpvivid_uc_exclude_files_regex',array());
                $white_list[]=$file_exclude;
                update_option('wpvivid_uc_exclude_files_regex',$white_list);
            }

            $white_list=get_option('wpvivid_uc_exclude_files_regex',array());
            $list=new WPvivid_Exclude_Files_List();

            if(isset($_POST['page']))
            {
                $list->set_list($white_list,sanitize_text_field($_POST['page']));
            }
            else
            {
                $list->set_list($white_list);
            }

            $list->prepare_items();
            ob_start();
            $list->display();
            $html = ob_get_clean();

            $ret['result']='success';
            $ret['html']=$html;
            echo wp_json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_exclude_files()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        try
        {
            $json = sanitize_text_field($_POST['selected']);
            $json = stripslashes($json);
            $json = json_decode($json, true);

            $files=$json['selected'];

            $white_list=get_option('wpvivid_uc_exclude_files_regex',array());
            $white_list = array_diff($white_list, $files);

            update_option('wpvivid_uc_exclude_files_regex',$white_list);

            $white_list=get_option('wpvivid_uc_exclude_files_regex',array());
            $list=new WPvivid_Exclude_Files_List();

            if(isset($_POST['page']))
            {
                $list->set_list($white_list,sanitize_key($_POST['page']));
            }
            else
            {
                $list->set_list($white_list);
            }

            $list->prepare_items();
            ob_start();
            $list->display();
            $html = ob_get_clean();

            $ret['result']='success';
            $ret['html']=$html;
            echo wp_json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_post_type_list()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        try
        {
            $default_post_types=array();
            $default_post_types[]='attachment';
            $default_post_types[]='revision';
            $default_post_types[]='auto-draft';
            $default_post_types[]='nav_menu_item';
            $default_post_types[]='shop_order';
            $default_post_types[]='shop_order_refund';
            $default_post_types[]='oembed_cache';
            $post_type=sanitize_text_field($_POST['post_type']);
            if(isset($post_type)&&!empty($post_type))
            {
                $file_exclude=$post_type;

                $post_types=get_option('wpvivid_uc_post_types',$default_post_types);
                $post_types[]=$file_exclude;
                update_option('wpvivid_uc_post_types',$post_types);
            }

            $post_types=get_option('wpvivid_uc_post_types',array());
            $list=new WPvivid_Post_Type_List();

            if(isset($_POST['page']))
            {
                $list->set_list($post_types,sanitize_key($_POST['page']));
            }
            else
            {
                $list->set_list($post_types);
            }

            $list->prepare_items();
            ob_start();
            $list->display();
            $html = ob_get_clean();

            $ret['result']='success';
            $ret['html']=$html;
            echo wp_json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_post_type()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        try
        {
            $default_post_types=array();
            $default_post_types[]='attachment';
            $default_post_types[]='revision';
            $default_post_types[]='auto-draft';
            $default_post_types[]='nav_menu_item';
            $default_post_types[]='shop_order';
            $default_post_types[]='shop_order_refund';
            $default_post_types[]='oembed_cache';

            $json = sanitize_text_field($_POST['selected']);
            $json = stripslashes($json);
            $json = json_decode($json, true);

            $files=$json['selected'];

            $post_types=get_option('wpvivid_uc_post_types',$default_post_types);
            $post_types = array_diff($post_types, $files);

            update_option('wpvivid_uc_post_types',$post_types);

            $post_types=get_option('wpvivid_uc_post_types',$default_post_types);
            $list=new WPvivid_Post_Type_List();

            if(isset($_POST['page']))
            {
                $list->set_list($post_types,sanitize_key($_POST['page']));
            }
            else
            {
                $list->set_list($post_types);
            }

            $list->prepare_items();
            ob_start();
            $list->display();
            $html = ob_get_clean();

            $ret['result']='success';
            $ret['html']=$html;
            echo wp_json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }
}