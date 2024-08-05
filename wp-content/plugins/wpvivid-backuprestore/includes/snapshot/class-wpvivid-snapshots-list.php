<?php
if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

if ( ! class_exists( 'WP_List_Table' ) )
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'WPvivid_Snapshots_List_Ex' ) )
{
    class WPvivid_Snapshots_List_Ex extends WP_List_Table
    {
        public $page_num;
        public $Snapshots_list;

        public function __construct( $args = array() )
        {
            parent::__construct(
                array(
                    'plural' => 'snapshots',
                    'screen' => 'snapshots'
                )
            );
        }

        protected function get_table_classes()
        {
            return array( 'widefat striped' );
        }

        public function get_columns()
        {
            $columns = array();
            $columns['cb'] = __( 'cb', 'wpvivid-snapshot-database' );
            $columns['wpvivid_time'] = __( 'Time', 'wpvivid-snapshot-database' );
            $columns['wpvivid_type'] = __( 'Type', 'wpvivid-snapshot-database' );
            $columns['wpvivid_prefix'] = __( 'Prefix', 'wpvivid-snapshot-database' );
            $columns['wpvivid_comment'] = __( 'Comment', 'wpvivid-snapshot-database' );
            $columns['wpvivid_actions'] = __( 'Actions', 'wpvivid-snapshot-database' );
            return $columns;
        }

        public function column_cb( $data )
        {
            echo '<input type="checkbox"/>';
        }

        public function _column_wpvivid_time( $data )
        {
            $time = gmdate('M-d-Y H:i', $data['time']);
            echo '<td>' . esc_html( $time ) . '</td>';
        }

        public function _column_wpvivid_type( $data )
        {
            echo '<td>' . esc_html( $data['type'] ) . '</td>';
        }

        public function _column_wpvivid_prefix( $data )
        {
            echo '<td>' . esc_html( $data['id'] ) . '</td>';
        }

        public function _column_wpvivid_comment( $data )
        {
            echo '<td>' . esc_html($data['comment'] ) . '</td>';
        }

        public function _column_wpvivid_actions( $data )
        {
            echo '<td>
                    <div style="cursor:pointer;float:left;">
                        <span class="dashicons dashicons-update wpvivid-dashicons-green wpvivid-snapshot-restore"></span>
                        <span class="wpvivid-snapshot-restore">Restore</span>
                        <span style="width:1rem;">|</span>
                        <span class="dashicons dashicons-trash wpvivid-dashicons-grey wpvivid-snapshot-delete"></span>
                        <span class="wpvivid-snapshot-delete">Delete</span>
                    </div>
                </td>';
        }

        public function set_list($Snapshots_list,$page_num=1)
        {
            $this->Snapshots_list=$Snapshots_list;
            $this->page_num=$page_num;
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

        public function prepare_items()
        {
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = array();
            $this->_column_headers = array($columns, $hidden, $sortable);

            $total_items =sizeof($this->Snapshots_list);

            $this->set_pagination_args(
                array(
                    'total_items' => $total_items,
                    'per_page'    => 10,
                )
            );
        }

        public function has_items()
        {
            return !empty($this->Snapshots_list);
        }

        public function display_rows()
        {
            $this->_display_rows($this->Snapshots_list);
        }

        private function _display_rows($Snapshots_list)
        {
            $page=$this->get_pagenum();

            $page_file_list=array();
            $count=0;
            while ( $count<$page )
            {
                $page_file_list = array_splice( $Snapshots_list, 0, 10);
                $count++;
            }
            foreach ( $page_file_list as $data)
            {
                $this->single_row($data);
            }
        }

        public function single_row($data)
        {
            ?>
            <tr  class='wpvivid-snapshot-row' slug="<?php echo esc_attr($data['id']);?>">
                <?php $this->single_row_columns( $data ); ?>
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

            $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

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
                    __( 'First page' ),
                    '&laquo;'
                );
            }

            if ( $disable_prev ) {
                $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
            } else {
                $page_links[] = sprintf(
                    "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                    $current,
                    __( 'Previous page' ),
                    '&lsaquo;'
                );
            }

            if ( 'bottom' === $which ) {
                $html_current_page  = $current;
                $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
            } else {
                $html_current_page = sprintf(
                    "%s<input class='current-page' id='current-page-selector-filelist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                    '<label for="current-page-selector-filelist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                    $current,
                    strlen( $total_pages )
                );
            }
            $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
            $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

            if ( $disable_next ) {
                $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
            } else {
                $page_links[] = sprintf(
                    "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                    $current,
                    __( 'Next page' ),
                    '&rsaquo;'
                );
            }

            if ( $disable_last ) {
                $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
            } else {
                $page_links[] = sprintf(
                    "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                    __( 'Last page' ),
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
            if ( $total_pages >1 && 'top' === $which)
            {
                ?>
                <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php echo esc_attr($css_type); ?>">
                    <input type="submit" id="wpvivid_delete_snapshots_action" class="button action" value="Delete the selected snapshots">
                    <?php
                    $this->extra_tablenav( $which );
                    $this->pagination( $which );
                    ?>

                    <br class="clear" />
                </div>
                <?php
            }
            else if($total_pages >1)
            {
                ?>
                <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php echo esc_attr($css_type); ?>">
                    <?php
                    $this->extra_tablenav( $which );
                    $this->pagination( $which );
                    ?>

                    <br class="clear" />
                </div>
                <?php
            }
            else if($total_pages <=1 && 'top' === $which)
            {
                ?>
                <input type="submit" id="wpvivid_delete_snapshots_action" class="button action" value="Delete the selected snapshots">
                <p></p>
                <?php
            }
        }

        public function display()
        {
            $singular = $this->_args['singular'];

            $this->display_tablenav( 'top' );

            $this->screen->render_screen_reader_content( 'heading_list' );
            ?>
            <table class="wp-list-table <?php echo esc_attr(implode( ' ', $this->get_table_classes() )); ?>">
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

            </table>
            <?php
            $this->display_tablenav( 'bottom' );
        }
    }
}

