<?php

if ( ! class_exists( 'WP_List_Table' ) )
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPvivid_Files_List extends WP_List_Table
{
    public $page_num;
    public $file_list;
    public $backup_id;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'files',
                'screen' => 'files'
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
        $columns['wpvivid_file'] = __( 'File', 'wpvivid-backuprestore' );
        return $columns;
    }

    public function _column_wpvivid_file( $file )
    {
        echo '<td class="tablelistcolumn">
                    <div style="padding:0 0 10px 0;">
                        <span>'. esc_html($file['key']).'</span>
                    </div>
                    <div class="wpvivid-download-status" style="padding:0;">';
        if($file['status']=='completed')
        {
            echo '<span>'.esc_html__('File Size: ', 'wpvivid-backuprestore').'</span><span class="wpvivid-element-space-right wpvivid-download-file-size">'.esc_html($file['size']).'</span><span class="wpvivid-element-space-right">|</span><span class=" wpvivid-element-space-right wpvivid-ready-download"><a style="cursor: pointer;">'. esc_html__('Download', 'wpvivid-backuprestore').'</a></span>';
        }
        else if($file['status']=='file_not_found')
        {
            echo '<span>' . esc_html__('File not found', 'wpvivid-backuprestore') . '</span>';
        }
        else if($file['status']=='need_download')
        {
            echo '<span>'.esc_html__('File Size: ', 'wpvivid-backuprestore').'</span><span class="wpvivid-element-space-right wpvivid-download-file-size">'.esc_html($file['size']).'</span><span class="wpvivid-element-space-right">|</span><span class="wpvivid-element-space-right"><a class="wpvivid-download" style="cursor: pointer;">'. esc_html__('Prepare to Download', 'wpvivid-backuprestore').'</a></span>';
        }
        else if($file['status']=='running')
        {
            echo '<div class="wpvivid-element-space-bottom">
                        <span class="wpvivid-element-space-right">' . esc_html__('Retriving (remote storage to web server)', 'wpvivid-backuprestore') . '</span><span class="wpvivid-element-space-right">|</span><span>' . esc_html__('File Size: ', 'wpvivid-backuprestore') . '</span><span class="wpvivid-element-space-right wpvivid-download-file-size">'.esc_html($file['size']).'</span><span class="wpvivid-element-space-right">|</span><span>'. esc_html__('Downloaded Size: ', 'wpvivid-backuprestore').'</span><span>'.esc_html($file['downloaded_size']).'</span>
                    </div>
                    <div style="width:100%;height:10px; background-color:#dcdcdc;">
                        <div style="background-color:#0085ba; float:left;width:'.esc_attr($file['progress_text']).'%;height:10px;"></div>
                    </div>';
        }
        else if($file['status']=='timeout')
        {
            echo '<div class="wpvivid-element-space-bottom">
                        <span>Download timeout, please retry.</span>
                    </div>
                    <div>
                        <span>'.esc_html__('File Size: ', 'wpvivid-backuprestore').'</span><span class="wpvivid-element-space-right wpvivid-download-file-size">'.esc_html($file['size']).'</span><span class="wpvivid-element-space-right">|</span><span class="wpvivid-element-space-right"><a class="wpvivid-download" style="cursor: pointer;">'. esc_html__('Prepare to Download', 'wpvivid-backuprestore').'</a></span>
                    </div>';
        }
        else if($file['status']=='error')
        {
            echo '<div class="wpvivid-element-space-bottom">
                        <span>'.esc_html($file['error']).'</span>
                    </div>
                    <div>
                        <span>'.esc_html__('File Size: ', 'wpvivid-backuprestore').'</span><span class="wpvivid-element-space-right wpvivid-download-file-size">'.esc_html($file['size']).'</span><span class="wpvivid-element-space-right">|</span><span class="wpvivid-element-space-right"><a class="wpvivid-download" style="cursor: pointer;">'. esc_html__('Prepare to Download', 'wpvivid-backuprestore').'</a></span>
                    </div>';
        }

        echo '</div></td>';
        //size
    }

    public function set_files_list($file_list,$backup_id,$page_num=1)
    {
        $this->file_list=$file_list;
        $this->backup_id=$backup_id;
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

        $total_items =sizeof($this->file_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->file_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->file_list);
    }

    private function _display_rows($file_list)
    {
        $page=$this->get_pagenum();

        $page_file_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_file_list = array_splice( $file_list, 0, 10);
            $count++;
        }
        foreach ( $page_file_list as $key=>$file)
        {
            $file['key']=$key;
            $this->single_row($file);
        }
    }

    public function single_row($file)
    {
        ?>
        <tr slug="<?php echo esc_attr($file['key'])?>">
            <?php $this->single_row_columns( $file ); ?>
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
                "%s<input class='current-page' id='current-page-selector-filelist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-filelist" class="screen-reader-text">' . __( 'Current Page', 'wpvivid-backuprestore' ) . '</label>',
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
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

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


function wpvivid_add_backup_type($type_name)
{
    ?>
    <label>
        <input type="radio" option="backup" name="<?php echo esc_attr($type_name); ?>" value="files+db" checked />
        <span><?php esc_html_e( 'Database + Files (WordPress Files)', 'wpvivid-backuprestore' ); ?></span>
    </label><br>
    <label>
        <input type="radio" option="backup" name="<?php echo esc_attr($type_name); ?>" value="files" />
        <span><?php esc_html_e( 'WordPress Files (Exclude Database)', 'wpvivid-backuprestore' ); ?></span>
    </label><br>
    <label>
        <input type="radio" option="backup" name="<?php echo esc_attr($type_name); ?>" value="db" />
        <span><?php esc_html_e( 'Only Database', 'wpvivid-backuprestore' ); ?></span>
    </label><br>
    <label>
        <div style="float: left;">
            <input type="radio" disabled />
            <span class="wpvivid-element-space-right" style="color: #ddd;"><?php esc_html_e('Custom', 'wpvivid-backuprestore'); ?></span>
        </div>
        <span class="wpvivid-feature-pro">
            <a href="https://docs.wpvivid.com/wpvivid-backup-pro-overview.html" style="text-decoration: none;"><?php esc_html_e('Pro feature: learn more', 'wpvivid-backuprestore'); ?></a>
        </span>
    </label>
    <br>
    <?php
}

function wpvivid_backup_do_js()
{
    global $wpvivid_plugin;
    $backup_task=$wpvivid_plugin->backup2->_list_tasks_ex();
    $general_setting=WPvivid_Setting::get_setting(true, "");

    if( (isset($general_setting['options']['wpvivid_common_setting']['estimate_backup'])) &&($general_setting['options']['wpvivid_common_setting']['estimate_backup'] == 0) )
    {
        ?>
        jQuery('#wpvivid_estimate_backup_info').hide();
        <?php
    }

    if($backup_task['progress_html']===false)
    {
        ?>
        jQuery('#wpvivid_postbox_backup_percent').hide();
        jQuery('#wpvivid_backup_cancel_btn').css({'pointer-events': 'auto', 'opacity': '1'});
        jQuery('#wpvivid_quickbackup_btn').css({'pointer-events': 'auto', 'opacity': '1'});
        <?php
    }
    else
    {
        ?>
        jQuery('#wpvivid_postbox_backup_percent').show();
        <?php
    }
}

function wpvivid_download_backup_descript($html){
    $html = '<p><strong>'.__('About backup download', 'wpvivid-backuprestore').'</strong></p>';
    $html .= '<ul>';
    $html .= '<li>'.__('->If backups are stored in remote storage, our plugin will retrieve the backup to your web server first. This may take a little time depending on the size of backup files. Please be patient. Then you can download them to your PC.', 'wpvivid-backuprestore').'</li>';
    $html .= '<li>'.__('->If backups are stored in web server, the plugin will list all relevant files immediately.', 'wpvivid-backuprestore').'</li>';
    $html .= '</ul>';
    return $html;
}

function wpvivid_restore_website_descript($html){
    $html = '<p><a href="#" id="wpvivid_how_to_restore_backup_describe" onclick="wpvivid_click_how_to_restore_backup();" style="text-decoration: none;">'.__('How to restore your website from a backup(scheduled, manual, uploaded and received backup)', 'wpvivid-backuprestore').'</a></p>';
    $html .= '<div id="wpvivid_how_to_restore_backup"></div>';
    return $html;
}

function wpvivid_backuppage_load_backuplist($backuplist_array){
    $backuplist_array['list_backup'] = array('index' => '1', 'tab_func' => 'wpvivid_backuppage_add_tab_backup', 'page_func' => 'wpvivid_backuppage_add_page_backup');
    $backuplist_array['list_log'] = array('index' => '3', 'tab_func' => 'wpvivid_backuppage_add_tab_log', 'page_func' => 'wpvivid_backuppage_add_page_log');
    $backuplist_array['list_restore'] = array('index' => '4', 'tab_func' => 'wpvivid_backuppage_add_tab_restore', 'page_func' => 'wpvivid_backuppage_add_page_restore');
    $backuplist_array['list_download'] = array('index' => '5', 'tab_func' => 'wpvivid_backuppage_add_tab_downlaod', 'page_func' => 'wpvivid_backuppage_add_page_downlaod');
    return $backuplist_array;
}

function wpvivid_backuppage_add_tab_backup(){
    ?>
    <a href="#" id="wpvivid_tab_backup" class="nav-tab backup-nav-tab nav-tab-active" onclick="switchrestoreTabs(event,'page-backups')"><?php esc_html_e('Backups', 'wpvivid-backuprestore'); ?></a>
    <?php
}

function wpvivid_backuppage_add_tab_log(){
    ?>
    <a href="#" id="wpvivid_tab_backup_log" class="nav-tab backup-nav-tab delete" onclick="switchrestoreTabs(event,'page-log')" style="display: none;">
        <div style="margin-right: 15px;"><?php esc_html_e('Log', 'wpvivid-backuprestore'); ?></div>
        <div class="nav-tab-delete-img">
            <img src="<?php echo esc_url(plugins_url( 'images/delete-tab.png', __FILE__ )); ?>" style="vertical-align:middle; cursor:pointer;" onclick="wpvivid_close_tab(event, 'wpvivid_tab_backup_log', 'backup', 'wpvivid_tab_backup');" />
        </div>
    </a>
    <?php
}

function wpvivid_backuppage_add_tab_restore(){
    ?>
    <a href="#" id="wpvivid_tab_restore" class="nav-tab backup-nav-tab delete" onclick="switchrestoreTabs(event,'page-restore')" style="display: none;">
        <div style="margin-right: 15px;"><?php esc_html_e('Restore', 'wpvivid-backuprestore'); ?></div>
        <div class="nav-tab-delete-img">
            <img src="<?php echo esc_url(plugins_url( 'images/delete-tab.png', __FILE__ )); ?>" style="vertical-align:middle; cursor:pointer;" onclick="wpvivid_close_tab(event, 'wpvivid_tab_restore', 'backup', 'wpvivid_tab_backup');" />
        </div>
    </a>
    <?php
}

function wpvivid_backuppage_add_tab_downlaod(){
    ?>
    <a href="#" id="wpvivid_tab_download" class="nav-tab backup-nav-tab delete" onclick="switchrestoreTabs(event,'page-download')" style="display: none;">
        <div style="margin-right: 15px;"><?php esc_html_e('Download', 'wpvivid-backuprestore'); ?></div>
        <div class="nav-tab-delete-img">
            <img src="<?php echo esc_url(plugins_url( 'images/delete-tab.png', __FILE__ )); ?>" style="vertical-align:middle; cursor:pointer;" onclick="wpvivid_close_tab(event, 'wpvivid_tab_download', 'backup', 'wpvivid_tab_backup');" />
        </div>
    </a>
    <?php
}

function wpvivid_backuppage_add_page_backup(){
    $backuplist=WPvivid_Backuplist::get_backuplist();
    $display_backup_count = WPvivid_Setting::get_max_backup_count();
    ?>
    <div class="backup-tab-content wpvivid_tab_backup" id="page-backups">
        <div style="margin-top:10px; margin-bottom:10px;">
            <?php
            echo '<p><strong>'.esc_html__('About backup download', 'wpvivid-backuprestore').'</strong></p>';
            echo '<ul>';
            echo '<li>'.esc_html__('->If backups are stored in remote storage, our plugin will retrieve the backup to your web server first. This may take a little time depending on the size of backup files. Please be patient. Then you can download them to your PC.', 'wpvivid-backuprestore').'</li>';
            echo '<li>'.esc_html__('->If backups are stored in web server, the plugin will list all relevant files immediately.', 'wpvivid-backuprestore').'</li>';
            echo '</ul>';
            ?>
        </div>
        <div style="margin-bottom:10px;">
            <?php
            echo '<p><a href="#" id="wpvivid_how_to_restore_backup_describe" onclick="wpvivid_click_how_to_restore_backup();" style="text-decoration: none;">'.esc_html__('How to restore your website from a backup(scheduled, manual, uploaded and received backup)', 'wpvivid-backuprestore').'</a></p>';
            echo '<div id="wpvivid_how_to_restore_backup"></div>';
            ?>
        </div>
        <div style="clear:both;"></div>
        <?php
        do_action('wpvivid_rescan_backup_list');
        ?>
        <table class="wp-list-table widefat plugins" id="wpvivid_backuplist_table" style="border-collapse: collapse;">
            <thead>
            <tr class="backup-list-head" style="border-bottom: 0;">
                <td></td>
                <th><?php esc_html_e( 'Backup','wpvivid-backuprestore'); ?></th>
                <th><?php esc_html_e( 'Storage','wpvivid-backuprestore'); ?></th>
                <th><?php esc_html_e( 'Download','wpvivid-backuprestore'); ?></th>
                <th><?php esc_html_e( 'Restore', 'wpvivid-backuprestore'); ?></th>
                <th><?php esc_html_e( 'Delete','wpvivid-backuprestore'); ?></th>
            </tr>
            </thead>
            <tbody class="wpvivid-backuplist" id="wpvivid_backup_list">
            <?php
            do_action('wpvivid_add_backup_list_output');
            ?>
            </tbody>
            <tfoot>
            <tr>
                <th><input name="" type="checkbox" id="backup_list_all_check" value="1" /></th>
                <th class="row-title" colspan="5"><a onclick="wpvivid_delete_backups_inbatches();" style="cursor: pointer;"><?php esc_html_e('Delete the selected backups', 'wpvivid-backuprestore'); ?></a></th>
            </tr>
            </tfoot>
        </table>
    </div>
    <script>
        function wpvivid_retrieve_backup_list(){
            var ajax_data = {
                'action': 'wpvivid_get_backup_list'
            };
            wpvivid_post_request(ajax_data, function(data){
                try {
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success') {
                        jQuery('#wpvivid_backup_list').html('');
                        jQuery('#wpvivid_backup_list').append(jsonarray.html);
                    }
                }
                catch(err){
                    alert(err);
                }
            },function(XMLHttpRequest, textStatus, errorThrown) {
                setTimeout(function () {
                    wpvivid_retrieve_backup_list();
                }, 3000);
            });
        }

        function wpvivid_handle_backup_data(data){
            try {
                var jsonarray = jQuery.parseJSON(data);
                if (jsonarray.result === 'success') {
                    jQuery('#wpvivid_backup_list').html('');
                    jQuery('#wpvivid_backup_list').append(jsonarray.html);
                }
                else if(jsonarray.result === 'failed'){
                    alert(jsonarray.error);
                }
            }
            catch(err){
                alert(err);
            }
        }

        function wpvivid_click_check_backup(backup_id, list_name){
            var name = "";
            var all_check = true;
            jQuery('#'+list_name+' tr').each(function (i) {
                jQuery(this).children('th').each(function (j) {
                    if(j === 0) {
                        var id = jQuery(this).find("input[type=checkbox]").attr("id");
                        if (id === backup_id) {
                            name = jQuery(this).parent().children('td').eq(0).find("img").attr("name");
                            if (name === "unlock") {
                                if (jQuery(this).find("input[type=checkbox]").prop('checked') === false) {
                                    all_check = false;
                                }
                            }
                            else {
                                jQuery(this).find("input[type=checkbox]").prop('checked', false);
                                all_check = false;
                            }
                        }
                        else {
                            if (jQuery(this).find("input[type=checkbox]").prop('checked') === false) {
                                all_check = false;
                            }
                        }
                    }
                });
            });
            if(all_check === true){
                jQuery('#backup_list_all_check').prop('checked', true);
            }
            else{
                jQuery('#backup_list_all_check').prop('checked', false);
            }
        }

        function wpvivid_set_backup_lock(backup_id, lock_status){
            //var check_status = true;
            if(lock_status === "lock"){
                var lock=0;
            }
            else{
                var lock=1;
            }
            //if(check_status)
            //{
                var ajax_data = {
                    'action': 'wpvivid_set_security_lock',
                    'backup_id': backup_id,
                    'lock': lock
                };
                wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            jQuery('#wpvivid_lock_' + backup_id).html(jsonarray.html);
                        }
                    }
                    catch (err) {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = wpvivid_output_ajaxerror('setting up a lock for the backup', textStatus, errorThrown);
                    alert(error_message);
                });
            //}
        }

        function wpvivid_read_log(action, param, log_type){
            var tab_id = '';
            var content_id = '';
            var ajax_data = '';
            var show_page = '';
            if(typeof param === 'undefined')    param = '';
            if(typeof log_type === 'undefined')    log_type = '';
            switch(action){
                case 'wpvivid_view_backup_task_log':
                    ajax_data = {
                        'action':action,
                        'id':running_backup_taskid
                    };
                    tab_id = 'wpvivid_tab_backup_log';
                    content_id = 'wpvivid_display_log_content';
                    show_page = 'backup_page';
                    break;
                case 'wpvivid_read_last_backup_log':
                    var ajax_data = {
                        'action': action,
                        'log_file_name': param
                    };
                    tab_id = 'wpvivid_tab_backup_log';
                    content_id = 'wpvivid_display_log_content';
                    show_page = 'backup_page';
                    break;
                case 'wpvivid_view_backup_log':
                    var ajax_data={
                        'action':action,
                        'id':param
                    };
                    tab_id = 'wpvivid_tab_backup_log';
                    content_id = 'wpvivid_display_log_content';
                    show_page = 'backup_page';
                    break;
                case 'wpvivid_view_log':
                    var ajax_data={
                        'action':action,
                        'id':param,
                        'log_type':log_type
                    };
                    tab_id = 'wpvivid_tab_read_log';
                    content_id = 'wpvivid_read_log_content';
                    show_page = 'log_page';
                    break;
                default:
                    break;
            }
            jQuery('#'+tab_id).show();
            jQuery('#'+content_id).html("");
            if(show_page === 'backup_page'){
                //wpvivid_click_switch_backup_page(tab_id);
                wpvivid_click_switch_page('backup', tab_id, true);
            }
            else if(show_page === 'log_page') {
                wpvivid_click_switch_page('wrap', tab_id, true);
            }
            wpvivid_post_request(ajax_data, function(data){
                wpvivid_show_log(data, content_id);
            }, function(XMLHttpRequest, textStatus, errorThrown) {
                var div = 'Reading the log failed. Please try again.';
                jQuery('#wpvivid_display_log_content').html(div);
            });
        }

        /*function wpvivid_initialize_download(backup_id, list_name){
            wpvivid_reset_backup_list(list_name);
            jQuery('#wpvivid_download_loading_'+backup_id).addClass('is-active');
            tmp_current_click_backupid = backup_id;
            var ajax_data = {
                'action':'wpvivid_init_download_page',
                'backup_id':backup_id
            };
            wpvivid_post_request(ajax_data, function(data){
                try {
                    var jsonarray = jQuery.parseJSON(data);
                    jQuery('#wpvivid_download_loading_'+backup_id).removeClass('is-active');
                    if (jsonarray.result === 'success') {
                        jQuery('#wpvivid_file_part_' + backup_id).html("");
                        var i = 0;
                        var file_not_found = false;
                        var file_name = '';
                        jQuery.each(jsonarray.files, function (index, value) {
                            i++;
                            file_name = index;
                            if (value.status === 'need_download') {
                                jQuery('#wpvivid_file_part_' + backup_id).append(value.html);
                                //tmp_current_click_backupid = '';
                            }
                            else if (value.status === 'running') {
                                if (m_downloading_file_name === file_name) {
                                    wpvivid_lock_download(tmp_current_click_backupid);
                                }
                                jQuery('#wpvivid_file_part_' + backup_id).append(value.html);
                            }
                            else if (value.status === 'completed') {
                                if (m_downloading_file_name === file_name) {
                                    wpvivid_unlock_download(tmp_current_click_backupid);
                                    m_downloading_id = '';
                                    m_downloading_file_name = '';
                                }
                                jQuery('#wpvivid_file_part_' + backup_id).append(value.html);
                                //tmp_current_click_backupid = '';
                            }
                            else if (value.status === 'timeout') {
                                if (m_downloading_file_name === file_name) {
                                    wpvivid_unlock_download(tmp_current_click_backupid);
                                    m_downloading_id = '';
                                    m_downloading_file_name = '';
                                }
                                jQuery('#wpvivid_file_part_' + backup_id).append(value.html);
                                //tmp_current_click_backupid = '';
                            }
                            else if (value.status === 'file_not_found') {
                                wpvivid_unlock_download(tmp_current_click_backupid);
                                wpvivid_reset_backup_list(list_name);
                                file_not_found = true;
                                alert("Download failed, file not found. The file might has been moved, renamed or deleted. Please verify the file exists and try again.");
                                //tmp_current_click_backupid = '';
                                return false;
                            }
                        });
                        if (file_not_found === false) {
                            jQuery('#wpvivid_file_part_' + backup_id).append(jsonarray.place_html);
                        }
                    }
                }
                catch(err){
                    alert(err);
                    jQuery('#wpvivid_download_loading_'+backup_id).removeClass('is-active');
                }
            },function(XMLHttpRequest, textStatus, errorThrown){
                jQuery('#wpvivid_download_loading_'+backup_id).removeClass('is-active');
                var error_message = wpvivid_output_ajaxerror('initializing download information', textStatus, errorThrown);
                alert(error_message);
            });
        }*/

        function wpvivid_reset_backup_list(list_name){
            jQuery('#'+list_name+' tr').each(function(i){
                jQuery(this).children('td').each(function (j) {
                    if (j == 2) {
                        var backup_id = jQuery(this).parent().children('th').find("input[type=checkbox]").attr("id");
                        var download_btn = '<div id="wpvivid_file_part_' + backup_id + '" style="float:left;padding:10px 10px 10px 0px;">' +
                            '<div style="cursor:pointer;" onclick="wpvivid_initialize_download(\'' + backup_id + '\', \''+list_name+'\');" title="<?php esc_html_e('Prepare to download the backup', 'wpvivid-backuprestore'); ?>">' +
                            '<img id="wpvivid_download_btn_' + backup_id + '" src="' + wpvivid_plugurl + '/admin/partials/images/download.png" style="vertical-align:middle;" />Download' +
                            '<div class="spinner" id="wpvivid_download_loading_' + backup_id + '" style="float:right;width:auto;height:auto;padding:10px 180px 10px 0;background-position:0 0;"></div>' +
                            '</div>' +
                            '</div>';
                        jQuery(this).html(download_btn);
                    }
                });
            });
        }

        function wpvivid_lock_download(backup_id){
            jQuery('#wpvivid_backup_list tr').each(function(i){
                jQuery(this).children('td').each(function (j) {
                    if (j == 2) {
                        jQuery(this).css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                });
            });
        }

        function wpvivid_unlock_download(backup_id){
            jQuery('#wpvivid_backup_list tr').each(function(i){
                jQuery(this).children('td').each(function (j) {
                    if (j == 2) {
                        jQuery(this).css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                });
            });
        }

        /**
         * Start downloading backup
         *
         * @param part_num  - The part number for the download object
         * @param backup_id - The unique ID for the backup
         * @param file_name - File name
         */
        function wpvivid_prepare_download(part_num, backup_id, file_name){
            var ajax_data = {
                'action': 'wpvivid_prepare_download_backup',
                'backup_id':backup_id,
                'file_name':file_name
            };
            var progress = '0%';
            jQuery("#"+backup_id+"-text-part-"+part_num).html("<a>Retriving(remote storage to web server)</a>");
            jQuery("#"+backup_id+"-progress-part-"+part_num).css('width', progress);
            task_retry_times = 0;
            m_need_update_2 = true;
            wpvivid_lock_download(backup_id);
            m_downloading_id = backup_id;
            tmp_current_click_backupid = backup_id;
            m_downloading_file_name = file_name;
            wpvivid_post_request(ajax_data, function(data)
            {
            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
            }, 0);
        }

        /**
         * Download backups to user's computer.
         *
         * @param backup_id     - The unique ID for the backup
         * @param backup_type   - The types of the backup
         * @param file_name     - File name
         */
        function wpvivid_download(backup_id, backup_type, file_name){
            wpvivid_location_href=true;
            location.href =ajaxurl+'?_wpnonce='+wpvivid_ajax_object.ajax_nonce+'&action=wpvivid_download_backup&backup_id='+backup_id+'&download_type='+backup_type+'&file_name='+file_name;
        }

        function wpvivid_initialize_restore(backup_id, backup_time, backup_type, restore_type='backup'){
            var time_type = 'backup';
            var log_type = '';
            var tab_type = '';
            var page_type = 'backup';
            if(restore_type == 'backup'){
                time_type = 'backup';
                log_type = '';
                tab_type = '';
                page_type = 'backup';
            }
            else if(restore_type == 'transfer'){
                time_type = 'transfer';
                log_type = 'transfer_';
                tab_type = 'add_';
                page_type = 'migrate';
            }
            wpvivid_restore_backup_type = backup_type;
            jQuery('#wpvivid_restore_'+time_type+'_time').html(backup_time);
            m_restore_backup_id = backup_id;
            jQuery('#wpvivid_restore_'+log_type+'log').html("");
            jQuery('#wpvivid_'+tab_type+'tab_restore').show();
            wpvivid_click_switch_page(page_type, 'wpvivid_'+tab_type+'tab_restore', true);
            wpvivid_init_restore_data(restore_type);
        }

        function click_dismiss_restore_check_notice(obj){
            wpvivid_display_restore_check = false;
            jQuery(obj).parent().remove();
        }

        /**
         * This function will initialize restore information
         *
         * @param backup_id - The unique ID for the backup
         */
        function wpvivid_init_restore_data(restore_type)
        {
            wpvivid_resotre_is_migrate=0;
            var restore_method = '';
            if(restore_type == 'backup'){
                restore_method = '';
            }
            else if(restore_type == 'transfer'){
                restore_method = 'transfer_';
            }
            jQuery('#wpvivid_replace_domain').prop('checked', false);
            jQuery('#wpvivid_keep_domain').prop('checked', false);
            jQuery('#wpvivid_restore_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
            jQuery('#wpvivid_clean_'+restore_method+'restore').css({'pointer-events': 'none', 'opacity': '0.4'});
            jQuery('#wpvivid_rollback_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
            jQuery('#wpvivid_download_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
            jQuery('#wpvivid_restore_'+restore_method+'part').show();
            jQuery('#wpvivid_clean_'+restore_method+'part').hide();
            jQuery('#wpvivid_rollback_'+restore_method+'part').hide();
            jQuery('#wpvivid_download_'+restore_method+'part').hide();

            jQuery('#wpvivid_init_restore_data').addClass('is-active');

            jQuery('#wpvivid_restore_progress').hide();
            jQuery('#wpvivid_restore_log').show();
            jQuery('#wpvivid_restore_box').show();
            jQuery('#wpvivid_restore_success').hide();
            jQuery('#wpvivid_restore_failed').hide();
            var ajax_data = {
                'action':'wpvivid_init_restore_page',
                'backup_id':m_restore_backup_id
            };
            wpvivid_post_request(ajax_data, function(data)
            {
                try
                {
                    var jsonarray = jQuery.parseJSON(data);
                    var init_status = false;
                    if(typeof jsonarray.has_zero_date !== 'undefined')
                    {
                        jQuery('#wpvivid_restore_warning_info').show();
                    }
                    else
                    {
                        jQuery('#wpvivid_restore_warning_info').hide();
                    }
                    if(jsonarray.result === 'success')
                    {
                        jQuery('#wpvivid_restore_'+restore_method+'btn').css({'pointer-events': 'auto', 'opacity': '1'});
                        jQuery('#wpvivid_download_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                        jQuery('#wpvivid_restore_'+restore_method+'part').show();
                        jQuery('#wpvivid_download_'+restore_method+'part').hide();
                        wpvivid_restore_need_download = false;
                        init_status = true;
                    }
                    else if (jsonarray.result === "need_download")
                    {
                        init_status = true;
                        wpvivid_restore_download_array = new Array();
                        var download_num = 0;
                        jQuery.each(jsonarray.files, function (index, value)
                        {
                            if (value.status === "need_download")
                            {
                                wpvivid_restore_download_array[download_num] = new Array('file_name', 'size', 'md5');
                                wpvivid_restore_download_array[download_num]['file_name'] = index;
                                wpvivid_restore_download_array[download_num]['size'] = value.size;
                                wpvivid_restore_download_array[download_num]['md5'] = value.md5;
                                download_num++;
                            }
                        });
                        wpvivid_restore_download_index=0;
                        wpvivid_restore_need_download = true;
                        jQuery('#wpvivid_restore_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                        jQuery('#wpvivid_download_'+restore_method+'btn').css({'pointer-events': 'auto', 'opacity': '1'});
                        jQuery('#wpvivid_restore_'+restore_method+'part').hide();
                        jQuery('#wpvivid_download_'+restore_method+'part').show();
                    }
                    else if (jsonarray.result === "failed") {
                        jQuery('#wpvivid_init_restore_data').removeClass('is-active');
                        wpvivid_display_restore_msg(jsonarray.error, restore_type);
                    }

                    if(init_status){
                        if(jsonarray.max_allow_packet_warning != false || jsonarray.memory_limit_warning != false) {
                            if(!wpvivid_display_restore_check) {
                                wpvivid_display_restore_check = true;
                                var output = '';
                                if(jsonarray.max_allow_packet_warning != false){
                                    output += "<p>" + jsonarray.max_allow_packet_warning + "</p>";
                                }
                                if(jsonarray.memory_limit_warning != false){
                                    output += "<p>" + jsonarray.memory_limit_warning + "</p>";
                                }
                                var div = "<div class='notice notice-warning is-dismissible inline'>" +
                                    output +
                                    "<button type='button' class='notice-dismiss' onclick='click_dismiss_restore_check_notice(this);'>" +
                                    "<span class='screen-reader-text'>Dismiss this notice.</span>" +
                                    "</button>" +
                                    "</div>";
                                jQuery('#wpvivid_restore_check').append(div);
                            }
                        }
                        jQuery('#wpvivid_init_restore_data').removeClass('is-active');
                        if(wpvivid_restore_need_download == false) {
                            jQuery('#wpvivid_restore_' + restore_method + 'btn').css({'pointer-events': 'auto', 'opacity': '1'});
                            jQuery('#wpvivid_clean_' + restore_method + 'restore').css({'pointer-events': 'none', 'opacity': '0.4'});
                            jQuery('#wpvivid_rollback_' + restore_method + 'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                            jQuery('#wpvivid_restore_' + restore_method + 'part').show();
                            jQuery('#wpvivid_clean_' + restore_method + 'part').hide();
                            jQuery('#wpvivid_rollback_' + restore_method + 'part').hide();
                            jQuery('#wpvivid_restore_is_migrate').css({'pointer-events': 'auto', 'opacity': '1'});

                            jQuery('#wpvivid_restore_is_migrate').hide();
                            jQuery('#wpvivid_restore_' + restore_method + 'btn').css({'pointer-events': 'auto', 'opacity': '1'});

                            wpvivid_resotre_is_migrate = jsonarray.is_migrate;

                            if (jsonarray.is_migrate_ui === 1) {
                                jQuery('#wpvivid_restore_is_migrate').show();
                                jQuery('#wpvivid_replace_domain').prop('checked', false);
                                jQuery('#wpvivid_keep_domain').prop('checked', false);
                            }
                            else {
                                jQuery('#wpvivid_restore_is_migrate').hide();
                                jQuery('#wpvivid_restore_' + restore_method + 'btn').css({'pointer-events': 'auto', 'opacity': '1'});
                            }

                            wpvivid_interface_flow_control();
                        }
                    }
                }
                catch(err){
                    alert(err);
                }
            }, function(XMLHttpRequest, textStatus, errorThrown) {
                jQuery('#wpvivid_init_restore_data').removeClass('is-active');
                var error_message = wpvivid_output_ajaxerror('initializing restore information', textStatus, errorThrown);
                wpvivid_display_restore_msg(error_message, restore_type);
            });
        }

        function wpvivid_delete_selected_backup(backup_id, list_name){
            var name = '';
            jQuery('#wpvivid_backup_list tr').each(function(i){
                jQuery(this).children('td').each(function (j) {
                    if (j == 0) {
                        var id = jQuery(this).parent().children('th').find("input[type=checkbox]").attr("id");
                        if(id === backup_id){
                            name = jQuery(this).parent().children('td').eq(0).find('img').attr('name');
                        }
                    }
                });
            });
            var descript = '';
            var force_del = 0;
            var bdownloading = false;
            if(name === 'lock') {
                descript = '<?php esc_html_e('This backup is locked, are you sure to remove it? This backup will be deleted permanently from your hosting (localhost) and remote storages.', 'wpvivid-backuprestore'); ?>';
                force_del = 1;
            }
            else{
                descript = '<?php esc_html_e('Are you sure to remove this backup? This backup will be deleted permanently.', 'wpvivid-backuprestore'); ?>';
                force_del = 0;
            }
            if(m_downloading_id === backup_id){
                bdownloading = true;
                descript = '<?php esc_html_e('This request will delete the backup being downloaded, are you sure you want to continue?', 'wpvivid-backuprestore'); ?>';
                force_del = 1;
            }
            var ret = confirm(descript);
            if(ret === true){
                var ajax_data={
                    'action': 'wpvivid_delete_backup',
                    'backup_id': backup_id,
                    'force': force_del
                };
                wpvivid_post_request(ajax_data, function(data){
                    wpvivid_handle_backup_data(data);
                    if(bdownloading){
                        m_downloading_id = '';
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        }
        function wpvivid_delete_backups_inbatches(){
            var delete_backup_array = new Array();
            var count = 0;
            var bdownloading = false;
            jQuery('#wpvivid_backup_list tr').each(function (i) {
                jQuery(this).children('th').each(function (j) {
                    if (j == 0) {
                        if(jQuery(this).find('input[type=checkbox]').prop('checked')){
                            delete_backup_array[count] = jQuery(this).find('input[type=checkbox]').attr('id');
                            if(m_downloading_id === jQuery(this).find('input[type=checkbox]').attr('id')){
                                bdownloading = true;
                            }
                            count++;
                        }
                    }
                });
            });
            if( count === 0 ){
                alert('<?php esc_html_e('Please select at least one item.','wpvivid-backuprestore'); ?>');
            }
            else {
                var descript = '';
                if(bdownloading) {
                    descript = '<?php esc_html_e('This request might delete the backup being downloaded, are you sure you want to continue?', 'wpvivid-backuprestore'); ?>';
                }
                else{
                    descript = '<?php esc_html_e('Are you sure to remove the selected backups? These backups will be deleted permanently.', 'wpvivid-backuprestore'); ?>';
                }
                var ret = confirm(descript);
                if (ret === true) {
                    var ajax_data = {
                        'action': 'wpvivid_delete_backup_array',
                        'backup_id': delete_backup_array
                    };
                    wpvivid_post_request(ajax_data, function (data) {
                        wpvivid_handle_backup_data(data);
                        jQuery('#backup_list_all_check').prop('checked', false);
                        if(bdownloading){
                            m_downloading_id = '';
                        }
                    }, function (XMLHttpRequest, textStatus, errorThrown) {
                        var error_message = wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                        alert(error_message);
                    });
                }
            }
        }

        jQuery('#backup_list_all_check').click(function(){
            var name = '';
            if(jQuery('#backup_list_all_check').prop('checked')) {
                jQuery('#wpvivid_backup_list tr').each(function (i) {
                    jQuery(this).children('th').each(function (j) {
                        if (j == 0) {
                            name = jQuery(this).parent().children('td').eq(0).find("img").attr("name");
                            if(name === 'unlock') {
                                jQuery(this).find("input[type=checkbox]").prop('checked', true);
                            }
                            else{
                                jQuery(this).find("input[type=checkbox]").prop('checked', false);
                            }
                        }
                    });
                });
            }
            else{
                jQuery('#wpvivid_backup_list tr').each(function (i) {
                    jQuery(this).children('th').each(function (j) {
                        if (j == 0) {
                            jQuery(this).find("input[type=checkbox]").prop('checked', false);
                        }
                    });
                });
            }
        });

        function click_dismiss_restore_notice(obj){
            wpvivid_display_restore_backup = false;
            jQuery(obj).parent().remove();
        }

        function wpvivid_click_how_to_restore_backup(){
            if(!wpvivid_display_restore_backup){
                wpvivid_display_restore_backup = true;
                var top = jQuery('#wpvivid_how_to_restore_backup_describe').offset().top-jQuery('#wpvivid_how_to_restore_backup_describe').height();
                jQuery('html, body').animate({scrollTop:top}, 'slow');
                var div = "<div class='notice notice-info is-dismissible inline'>" +
                    "<p>" + wpvividlion.restore_step1 + "</p>" +
                    "<p>" + wpvividlion.restore_step2 + "</p>" +
                    "<p>" + wpvividlion.restore_step3 + "</p>" +
                    "<button type='button' class='notice-dismiss' onclick='click_dismiss_restore_notice(this);'>" +
                    "<span class='screen-reader-text'>Dismiss this notice.</span>" +
                    "</button>" +
                    "</div>";
                jQuery('#wpvivid_how_to_restore_backup').append(div);
            }
        }
    </script>
    <?php
}

function wpvivid_backuppage_add_page_log(){
    ?>
    <div class="backup-tab-content wpvivid_tab_backup_log" id="page-log" style="display:none;">
        <div class="postbox restore_log" id="wpvivid_display_log_content">
            <div></div>
        </div>
    </div>
    <?php
}

function wpvivid_backuppage_add_page_restore()
{
    $general_setting=WPvivid_Setting::get_setting(true, "");
    if(isset($general_setting['options']['wpvivid_common_setting']['restore_max_execution_time'])){
        $restore_max_execution_time = intval($general_setting['options']['wpvivid_common_setting']['restore_max_execution_time']);
    }
    else{
        $restore_max_execution_time = WPVIVID_RESTORE_MAX_EXECUTION_TIME;
    }
    ?>
    <div class="backup-tab-content wpvivid_tab_restore" id="page-restore" style="display:none;">
        <div id="wpvivid_restore_warning_info" class="wpvivid-one-coloum" style="border:2px solid orange; margin-bottom:1em; border-radius:0.3em; display: none;">
            <span style="color:orange;">Warning: We have detected that the backup contains zero dates '0000-00-00', while NO_ZERO_DATE which forbids zero dates is enabled on the current server, which may cause a restore failure. It is recommended to temporarily disable NO_ZERO_DATE on the server.</span></span>
        </div>
        <div id="wpvivid_restore_box">
            <h3><?php esc_html_e('Restore backup from:', 'wpvivid-backuprestore'); ?><span id="wpvivid_restore_backup_time"></span></h3>
            <p><strong><?php esc_html_e('Please do not close the page or switch to other pages when a restore task is running, as it could trigger some unexpected errors.', 'wpvivid-backuprestore'); ?></strong></p>
            <p><?php esc_html_e('Restore function will replace the current site\'s themes, plugins, uploads, database and/or other content directories with the existing equivalents in the selected backup.', 'wpvivid-backuprestore'); ?></p>
            <div id="wpvivid_restore_check"></div>
            <div class="restore-button-position" id="wpvivid_restore_part"><input class="button-primary" id="wpvivid_restore_btn" type="submit" name="restore" value="<?php esc_attr_e( 'Restore', 'wpvivid-backuprestore' ); ?>" onclick="wpvivid_start_restore();" /></div>
            <div class="restore-button-position" id="wpvivid_download_part">
                <input class="button-primary" id="wpvivid_download_btn" type="submit" name="download" value="<?php esc_attr_e( 'Retrieve the backup to localhost', 'wpvivid-backuprestore' ); ?>" />
                <span><?php esc_html_e('The backup is stored on the remote storage, click on the button to download it to localhost.', 'wpvivid-backuprestore'); ?></span>
            </div>
            <div class="spinner" id="wpvivid_init_restore_data" style="float:left;width:auto;height:auto;padding:10px 20px 20px 0;background-position:0 10px;"></div>
        </div>
        <div style="clear:both;"></div>
        <div id="wpvivid_restore_progress" style="margin-bottom:1em;margin-top:1em;display: none">
            <div style="padding-top:1em;border-top:1px solid #cccccc;">
                <span class="dashicons dashicons-update wpvivid-dashicons-blue"></span>
                <span>Restoring: overall progress</span>
            </div>
            <div style="padding: 1em 0;">
                    <span class="wpvivid-span-progress" id="wpvivid_main_progress">
                        <span class="action-progress-bar-percent wpvivid-span-processed-percent-progress" style="width: 0%; display:block; height:1.5em; border-radius:0; padding-left:0.5em;">0% completed</span>
                    </span>
            </div>
        </div>
        <div class="postbox restore_log" id="wpvivid_restore_log"></div>
        <div id="wpvivid_restore_success" style="display: none">
            <div style="font-size:4em; font-weight:900; color:#8bc34a; text-align:center;padding-top:1.5em;">
                <span>Congratulations !!!</span>
            </div>
            <div id="wpvivid_restore_finished_msg" style="width:600px; text-align:center; margin: 5em auto; border-top:5px solid #eaf1fe;border-bottom:5px solid #eaf1fe;">
            </div>
        </div>
        <div id="wpvivid_restore_failed" style="display: none">
            <div style="font-size:2em; font-weight:900; color:orange; text-align:center;padding-top:1.5em;">
                <span>Oops, The restoration seems to have encountered a problem:(</span>
            </div>
            <div id="wpvivid_restore_failed_msg" style="width:600px; text-align:center; margin: 5em auto; border-top:5px solid #eaf1fe;border-bottom:5px solid #eaf1fe;">
            </div>
        </div>
    </div>
    <script>
        var restore_max_exection_time = '<?php echo esc_attr($restore_max_execution_time); ?>';
        restore_max_exection_time = restore_max_exection_time * 1000;
        var m_restore_type='';
        jQuery('#wpvivid_download_btn').click(function()
        {
            wpvivid_download_restore_file('backup');
        });

        function wpvivid_download_restore_file(restore_type)
        {
            var restore_method = '';
            if(restore_type == 'backup'){
                restore_method = '';
            }
            else if(restore_type == 'transfer'){
                restore_method = 'transfer_';
            }

            jQuery('#wpvivid_download_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
            wpvivid_restore_lock();
            if(wpvivid_restore_download_array.length===0)
            {
                wpvivid_display_restore_msg("Downloading backup file failed. Backup file might be deleted or network doesn't work properly. Please verify the file and confirm the network connection and try again later.", restore_type);
                wpvivid_restore_unlock();
                return false;
            }

            if(wpvivid_restore_download_index+1>wpvivid_restore_download_array.length)
            {
                var ajax_data = {
                    'action': 'wpvivid_check_download_has_zero_date',
                    'backup_id': m_restore_backup_id
                }
                wpvivid_post_request(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if(jsonarray.result === 'success')
                        {
                            if(typeof jsonarray.has_zero_date !== 'undefined')
                            {
                                jQuery('#wpvivid_restore_warning_info').show();
                            }
                            else
                            {
                                jQuery('#wpvivid_restore_warning_info').hide();
                            }
                            wpvivid_download_display_restore_msg(restore_type, restore_method);
                        }
                        else
                        {
                            wpvivid_download_display_restore_msg(restore_type, restore_method);
                        }
                    }
                    catch(err){
                        wpvivid_download_display_restore_msg(restore_type, restore_method);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown) {
                    wpvivid_download_display_restore_msg(restore_type, restore_method);
                });
            }
            else
            {
                wpvivid_display_restore_msg("Downloading backup file " +  wpvivid_restore_download_array[wpvivid_restore_download_index]['file_name'], restore_type);
                wpvivid_display_restore_msg('', restore_type, wpvivid_restore_download_index);
                var ajax_data = {
                    'action': 'wpvivid_download_restore',
                    'backup_id': m_restore_backup_id,
                    'file_name': wpvivid_restore_download_array[wpvivid_restore_download_index]['file_name'],
                    'size': wpvivid_restore_download_array[wpvivid_restore_download_index]['size'],
                    'md5': wpvivid_restore_download_array[wpvivid_restore_download_index]['md5']
                }
                wpvivid_get_download_restore_progress_retry=0;
                wpvivid_monitor_download_restore_task(restore_type);
                wpvivid_post_request(ajax_data, function (data) {
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                }, 0);
            }
        }

        function wpvivid_monitor_download_restore_task(restore_type)
        {
            var restore_method = '';
            if(restore_type == 'backup'){
                restore_method = '';
            }
            else if(restore_type == 'transfer'){
                restore_method = 'transfer_';
            }

            var ajax_data={
                'action':'wpvivid_get_download_restore_progress',
                'file_name': wpvivid_restore_download_array[wpvivid_restore_download_index]['file_name'],
                'size': wpvivid_restore_download_array[wpvivid_restore_download_index]['size'],
                'md5': wpvivid_restore_download_array[wpvivid_restore_download_index]['md5']
            };

            wpvivid_post_request(ajax_data, function(data)
            {
                try
                {
                    var jsonarray = jQuery.parseJSON(data);
                    if(typeof jsonarray ==='object')
                    {
                        if(jsonarray.result === "success")
                        {
                            if(jsonarray.status==='completed')
                            {
                                wpvivid_display_restore_msg(wpvivid_restore_download_array[wpvivid_restore_download_index]['file_name'] + ' download succeeded.', restore_type, wpvivid_restore_download_index, false);
                                wpvivid_restore_download_index++;
                                wpvivid_download_restore_file(restore_type);
                                wpvivid_restore_unlock();
                            }
                            else if(jsonarray.status==='error')
                            {
                                jQuery('#wpvivid_restore_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                                jQuery('#wpvivid_clean_'+restore_method+'restore').css({'pointer-events': 'none', 'opacity': '0.4'});
                                jQuery('#wpvivid_rollback_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                                jQuery('#wpvivid_download_'+restore_method+'btn').css({'pointer-events': 'auto', 'opacity': '1'});
                                jQuery('#wpvivid_restore_'+restore_method+'part').hide();
                                jQuery('#wpvivid_clean_'+restore_method+'part').hide();
                                jQuery('#wpvivid_rollback_'+restore_method+'part').hide();
                                jQuery('#wpvivid_download_'+restore_method+'part').show();
                                var error_message = jsonarray.error;
                                wpvivid_display_restore_msg(error_message,restore_type,wpvivid_restore_download_array[wpvivid_restore_download_index]['file_name'],false);
                                wpvivid_restore_unlock();
                            }
                            else if(jsonarray.status==='running')
                            {
                                jQuery('#wpvivid_download_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                                wpvivid_display_restore_msg(jsonarray.log, restore_type, wpvivid_restore_download_index, false);
                                setTimeout(function()
                                {
                                    wpvivid_monitor_download_restore_task(restore_type);
                                }, 3000);
                                wpvivid_restore_lock();
                            }
                            else if(jsonarray.status==='timeout')
                            {
                                wpvivid_get_download_restore_progress_retry++;
                                if(wpvivid_get_download_restore_progress_retry>10)
                                {
                                    jQuery('#wpvivid_restore_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                                    jQuery('#wpvivid_clean_'+restore_method+'restore').css({'pointer-events': 'none', 'opacity': '0.4'});
                                    jQuery('#wpvivid_rollback_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                                    jQuery('#wpvivid_download_'+restore_method+'btn').css({'pointer-events': 'auto', 'opacity': '1'});
                                    jQuery('#wpvivid_restore_'+restore_method+'part').hide();
                                    jQuery('#wpvivid_clean_'+restore_method+'part').hide();
                                    jQuery('#wpvivid_rollback_'+restore_method+'part').hide();
                                    jQuery('#wpvivid_download_'+restore_method+'part').show();
                                    var error_message = jsonarray.error;
                                    wpvivid_display_restore_msg(error_message, restore_type);
                                    wpvivid_restore_unlock();
                                }
                                else
                                {
                                    setTimeout(function()
                                    {
                                        wpvivid_monitor_download_restore_task(restore_type);
                                    }, 3000);
                                }
                            }
                            else
                            {
                                setTimeout(function()
                                {
                                    wpvivid_monitor_download_restore_task(restore_type);
                                }, 3000);
                            }
                        }
                        else
                        {
                            wpvivid_get_download_restore_progress_retry++;
                            if(wpvivid_get_download_restore_progress_retry>10)
                            {
                                jQuery('#wpvivid_restore_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                                jQuery('#wpvivid_clean_'+restore_method+'restore').css({'pointer-events': 'none', 'opacity': '0.4'});
                                jQuery('#wpvivid_rollback_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                                jQuery('#wpvivid_download_'+restore_method+'btn').css({'pointer-events': 'auto', 'opacity': '1'});
                                jQuery('#wpvivid_restore_'+restore_method+'part').hide();
                                jQuery('#wpvivid_clean_'+restore_method+'part').hide();
                                jQuery('#wpvivid_rollback_'+restore_method+'part').hide();
                                jQuery('#wpvivid_download_'+restore_method+'part').show();
                                var error_message = jsonarray.error;
                                wpvivid_display_restore_msg(error_message, restore_type);
                                wpvivid_restore_unlock();
                            }
                            else
                            {
                                setTimeout(function()
                                {
                                    wpvivid_monitor_download_restore_task(restore_type);
                                }, 3000);
                            }
                        }
                    }
                    else
                    {
                        setTimeout(function()
                        {
                            wpvivid_monitor_download_restore_task(restore_type);
                        }, 3000);
                    }
                }
                catch(err){
                    setTimeout(function()
                    {
                        wpvivid_monitor_download_restore_task(restore_type);
                    }, 3000);
                }
            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                setTimeout(function()
                {
                    wpvivid_monitor_download_restore_task(restore_type);
                }, 1000);
            });
        }

        function wpvivid_download_display_restore_msg(restore_type, restore_method)
        {
            wpvivid_display_restore_msg("Download succeeded.", restore_type);
            wpvivid_restore_need_download = false;
            jQuery('#wpvivid_restore_' + restore_method + 'btn').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_clean_' + restore_method + 'restore').css({'pointer-events': 'none', 'opacity': '0.4'});
            jQuery('#wpvivid_rollback_' + restore_method + 'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
            jQuery('#wpvivid_download_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
            jQuery('#wpvivid_restore_' + restore_method + 'part').show();
            jQuery('#wpvivid_clean_' + restore_method + 'part').hide();
            jQuery('#wpvivid_rollback_' + restore_method + 'part').hide();
            jQuery('#wpvivid_download_'+restore_method+'part').hide();
        }

        /**
         * This function will start the process of restoring a backup
         */
        function wpvivid_start_restore(restore_type = 'backup')
        {
            if(!wpvivid_restore_sure){
                var descript = '<?php esc_html_e('Are you sure to continue?', 'wpvivid-backuprestore'); ?>';
                var ret = confirm(descript);
            }
            else{
                ret = true;
            }
            if (ret === true) {
                wpvivid_restore_sure = true;
                var restore_method = '';
                if (restore_type == 'backup') {
                    restore_method = '';
                }
                else if (restore_type == 'transfer') {
                    restore_method = 'transfer_';
                }
                m_restore_type=restore_method;
                jQuery('#wpvivid_restore_' + restore_method + 'log').html("");
                jQuery('#wpvivid_restore_' + restore_method + 'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#wpvivid_restore_' + restore_method + 'part').show();
                jQuery('#wpvivid_restore_progress').show();
                jQuery('#wpvivid_main_progress').html('<span class="action-progress-bar-percent wpvivid-span-processed-percent-progress" style="width: 0%; display:block; height:1.5em; border-radius:0; padding-left:0.5em;">0%</span>');
                wpvivid_restore_lock();
                wpvivid_restoring = true;
                if (wpvivid_restore_need_download)
                {
                    wpvivid_download_restore_file(restore_type);
                }
                else
                {
                    //wpvivid_monitor_restore_task(restore_type);
                    //if(wpvivid_resotre_is_migrate==0)
                    //{
                    //    jQuery('input:radio[option=restore]').each(function()
                    //    {
                    //       if(jQuery(this).prop('checked'))
                    //        {
                    //            var value = jQuery(this).prop('value');
                    //            if(value == '1')
                    //            {
                    //                wpvivid_resotre_is_migrate = '1';
                    //            }
                    //        }
                    //    });
                    //}
                    //wpvivid_restore(restore_type);

                    wpvivid_init_restore();
                }
            }
        }

        function wpvivid_init_restore()
        {
            var ajax_data = {
                'action':'wpvivid_init_restore_task_2',
                'backup_id': m_restore_backup_id,
            };

            wpvivid_post_request(ajax_data, function(data)
            {
                try
                {
                    var jsonarray = jQuery.parseJSON(data);

                    if (jsonarray.result === 'success')
                    {
                        wpvivid_do_restore();
                    }
                    else
                    {
                        jQuery('#wpvivid_restore_'+m_restore_type+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                        jQuery('#wpvivid_download_'+m_restore_type+'btn').css({'pointer-events': 'auto', 'opacity': '1'});
                        jQuery('#wpvivid_restore_'+m_restore_type+'part').hide();
                        jQuery('#wpvivid_download_'+m_restore_type+'part').show();
                        var error_message = jsonarray.error;
                        wpvivid_restore_unlock();
                        wpvivid_restoring = false;
                        alert("<?php esc_html_e('Restore failed.', 'wpvivid-backuprestore'); ?>");
                        wpvivid_display_restore_msg(error_message,m_restore_type,wpvivid_restore_download_array[wpvivid_restore_download_index]['file_name'],false);

                    }
                }
                catch (err)
                {
                    alert(err);
                }
            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                var error_message = wpvivid_output_ajaxerror('init restore task', textStatus, errorThrown);
                alert(error_message);
            });
        }

        function wpvivid_do_restore()
        {
            var ajax_data = {
                'action':'wpvivid_do_restore_2',
                'wpvivid_restore':'1'
            };
            wpvivid_post_request(ajax_data, function(data)
            {
                setTimeout(function(){
                    wpvivid_get_restore_progress();
                }, 1000);
            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                wpvivid_get_restore_progress();
            });
        }

        function wpvivid_get_restore_progress()
        {
            var ajax_data = {
                'action':'wpvivid_get_restore_progress_2',
                'wpvivid_restore':'1'
            };
            wpvivid_post_request(ajax_data, function(data)
            {
                try
                {
                    var jsonarray = jQuery.parseJSON(data);

                    if (jsonarray.result === 'success')
                    {
                        wpvivid_output_progress(jsonarray);

                        if(jsonarray.status=='ready')
                        {
                            wpvivid_do_restore();
                        }
                        else if(jsonarray.status=='sub task finished')
                        {
                            wpvivid_do_restore();
                        }
                        else if(jsonarray.status=='task finished')
                        {
                            wpvivid_finish_restore();
                        }
                        else if(jsonarray.status=='doing sub task')
                        {
                            setTimeout(function(){
                                wpvivid_get_restore_progress();
                            }, 2000);
                        }
                        else if(jsonarray.status=='no response')
                        {
                            setTimeout(function(){
                                wpvivid_get_restore_progress();
                            }, 2000);
                        }
                    }
                    else {
                        wpvivid_restore_failed();
                    }
                }
                catch (err)
                {
                    setTimeout(function(){
                        wpvivid_get_restore_progress();
                    }, 2000);
                }
            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                setTimeout(function(){
                    wpvivid_get_restore_progress();
                }, 2000);
            });
        }

        function wpvivid_finish_restore()
        {
            var ajax_data = {
                'action':'wpvivid_finish_restore_2',
                'wpvivid_restore':'1'
            };
            wpvivid_post_request(ajax_data, function(data)
            {
                wpvivid_restore_unlock();
                alert("<?php esc_html_e('Restore completed successfully.', 'wpvivid-backuprestore'); ?>");
                location.reload();

                //jQuery('#wpvivid_restore_progress').hide();
                //jQuery('#wpvivid_restore_log').hide();
                //jQuery('#wpvivid_restore_box').hide();
                //jQuery('#wpvivid_restore_success').show();
                //jQuery('#wpvivid_restore_failed').hide();
                //jQuery('#wpvivid_restore_finished_msg').html(data);
                //wpvivid_restoring = false;
            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                wpvivid_restore_unlock();
                alert("<?php esc_html_e('Restore completed successfully.', 'wpvivid-backuprestore'); ?>");
                location.reload();

                //jQuery('#wpvivid_restore_progress').hide();
                //jQuery('#wpvivid_restore_log').hide();
                //jQuery('#wpvivid_restore_success').show();
                //jQuery('#wpvivid_restore_failed').hide();
                //
                //jQuery('#wpvivid_restore_finished_msg').html(XMLHttpRequest.responseText);
                //wpvivid_restoring = false;
                //wpvivid_restore_unlock();
                //alert("<?php esc_html_e('Restore completed successfully.', 'wpvivid-backuprestore'); ?>");
                //location.reload();
            });
        }

        function wpvivid_restore_failed()
        {
            var ajax_data = {
                'action':'wpvivid_restore_failed_2',
                'wpvivid_restore':'1'
            };

            wpvivid_post_request(ajax_data, function(data)
            {
                jQuery('#wpvivid_restore_progress').hide();
                //jQuery('#wpvivid_restore_log').hide();
                //jQuery('#wpvivid_restore_box').hide();
                //jQuery('#wpvivid_restore_success').hide();
                //jQuery('#wpvivid_restore_failed').show();
                //jQuery('#wpvivid_restore_failed_msg').html(data);

                wpvivid_restore_unlock();
                wpvivid_restoring = false;
                jQuery('#wpvivid_restore_' + m_restore_type + 'btn').css({'pointer-events': 'auto', 'opacity': '1'});
                alert(data);

            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                jQuery('#wpvivid_restore_progress').hide();
                //jQuery('#wpvivid_restore_log').hide();
                //jQuery('#wpvivid_restore_box').hide();
                //jQuery('#wpvivid_restore_success').hide();
                //jQuery('#wpvivid_restore_failed').show();
                //jQuery('#wpvivid_restore_failed_msg').html(XMLHttpRequest.responseText);

                wpvivid_restore_unlock();
                wpvivid_restoring = false;
                jQuery('#wpvivid_restore_' + m_restore_type + 'btn').css({'pointer-events': 'auto', 'opacity': '1'});
                alert("<?php esc_html_e('Restore failed.', 'wpvivid-backuprestore'); ?>");
            });
        }

        function wpvivid_output_progress(jsonarray)
        {
            jQuery('#wpvivid_main_progress').html(jsonarray.main_progress);

            jQuery('#wpvivid_restore_' + m_restore_type + 'log').html("");
            while (jsonarray.log.indexOf('\n') >= 0)
            {
                var iLength = jsonarray.log.indexOf('\n');
                var log = jsonarray.log.substring(0, iLength);
                jsonarray.log = jsonarray.log.substring(iLength + 1);
                var insert_log = "<div style=\"clear:both;\">" + log + "</div>";
                jQuery('#wpvivid_restore_' + m_restore_type + 'log').append(insert_log);
                var div = jQuery('#wpvivid_restore_' + m_restore_type + 'log');
                div[0].scrollTop = div[0].scrollHeight;
            }
        }



        /**
         * Monitor restore task.
         */
        /*
        function wpvivid_monitor_restore_task(restore_type){
            var restore_method = '';
            if(restore_type == 'backup'){
                restore_method = '';
            }
            else if(restore_type == 'transfer'){
                restore_method = 'transfer_';
            }

            var ajax_data={
                'action':'wpvivid_get_restore_progress',
                'wpvivid_restore' : '1',
                'backup_id':m_restore_backup_id,
            };

            if(wpvivid_restore_timeout){
                jQuery('#wpvivid_restore_'+restore_method+'btn').css({'pointer-events': 'auto', 'opacity': '1'});
                jQuery('#wpvivid_clean_'+restore_method+'restore').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#wpvivid_rollback_'+restore_method+'btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#wpvivid_restore_'+restore_method+'part').show();
                jQuery('#wpvivid_clean_'+restore_method+'part').hide();
                jQuery('#wpvivid_rollback_'+restore_method+'part').hide();
                wpvivid_restore_unlock();
                wpvivid_restoring = false;
                wpvivid_display_restore_msg("Website restore times out.", restore_type);
            }
            else {
                wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (typeof jsonarray === 'object') {
                            if (jsonarray.result === "success") {
                                jQuery('#wpvivid_restore_' + restore_method + 'log').html("");
                                while (jsonarray.log.indexOf('\n') >= 0) {
                                    var iLength = jsonarray.log.indexOf('\n');
                                    var log = jsonarray.log.substring(0, iLength);
                                    jsonarray.log = jsonarray.log.substring(iLength + 1);
                                    var insert_log = "<div style=\"clear:both;\">" + log + "</div>";
                                    jQuery('#wpvivid_restore_' + restore_method + 'log').append(insert_log);
                                    var div = jQuery('#wpvivid_restore_' + restore_method + 'log');
                                    div[0].scrollTop = div[0].scrollHeight;
                                }

                                if (jsonarray.status === 'wait') {
                                    wpvivid_restoring = true;
                                    jQuery('#wpvivid_restore_' + restore_method + 'btn').css({
                                        'pointer-events': 'none',
                                        'opacity': '0.4'
                                    });
                                    jQuery('#wpvivid_clean_' + restore_method + 'restore').css({
                                        'pointer-events': 'none',
                                        'opacity': '0.4'
                                    });
                                    jQuery('#wpvivid_rollback_' + restore_method + 'btn').css({
                                        'pointer-events': 'none',
                                        'opacity': '0.4'
                                    });
                                    jQuery('#wpvivid_restore_' + restore_method + 'part').show();
                                    jQuery('#wpvivid_clean_' + restore_method + 'part').hide();
                                    jQuery('#wpvivid_rollback_' + restore_method + 'part').hide();
                                    wpvivid_restore(restore_type);
                                    setTimeout(function () {
                                        wpvivid_monitor_restore_task(restore_type);
                                    }, 1000);
                                }
                                else if (jsonarray.status === 'completed') {
                                    wpvivid_restoring = false;
                                    wpvivid_restore(restore_type);
                                    wpvivid_restore_unlock();
                                    alert(" //esc_html_e('Restore completed successfully.', 'wpvivid-backuprestore'); ");
                                    location.reload();
                                }
                                else if (jsonarray.status === 'error') {
                                    wpvivid_restore_unlock();
                                    wpvivid_restoring = false;
                                    jQuery('#wpvivid_restore_' + restore_method + 'btn').css({'pointer-events': 'auto', 'opacity': '1'});
                                    alert(" //esc_html_e('Restore failed.', 'wpvivid-backuprestore'); ");
                                }
                                else {
                                    setTimeout(function () {
                                        wpvivid_monitor_restore_task(restore_type);
                                    }, 1000);
                                }
                            }
                            else {
                                setTimeout(function () {
                                    wpvivid_monitor_restore_task(restore_type);
                                }, 1000);
                            }
                        }
                        else {
                            setTimeout(function () {
                                wpvivid_monitor_restore_task(restore_type);
                            }, 1000);
                        }
                    }
                    catch (err) {
                        setTimeout(function () {
                            wpvivid_monitor_restore_task(restore_type);
                        }, 1000);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    setTimeout(function () {
                        wpvivid_monitor_restore_task(restore_type);
                    }, 1000);
                });
            }
        }

        function wpvivid_restore(restore_type){
            var restore_method = '';
            if(restore_type == 'backup'){
                restore_method = '';
            }
            else if(restore_type == 'transfer'){
                restore_method = 'transfer_';
            }

            var skip_old_site = '1';
            var extend_option = {
                'skip_backup_old_site':skip_old_site,
                'skip_backup_old_database':skip_old_site
            };

            var migrate_option = {
                'is_migrate':wpvivid_resotre_is_migrate,
            };
            jQuery.extend(extend_option, migrate_option);

            var restore_options = {
                0:'backup_db',
                1:'backup_themes',
                2:'backup_plugin',
                3:'backup_uploads',
                4:'backup_content',
                5:'backup_core'
            };
            jQuery.extend(restore_options, extend_option);
            var json = JSON.stringify(restore_options);
            var ajax_data={
                'action':'wpvivid_restore',
                'wpvivid_restore':'1',
                'backup_id':m_restore_backup_id,
                'restore_options':json
            };
            setTimeout(function () {
                wpvivid_restore_timeout = true;
            }, restore_max_exection_time);
            wpvivid_post_request(ajax_data, function(data) {
            }, function(XMLHttpRequest, textStatus, errorThrown) {
            });
        }
        */

        function wpvivid_display_restore_msg(msg, restore_type, div_id, append = true){
            var restore_method = '';
            if(restore_type == 'backup'){
                restore_method = '';
            }
            else if(restore_type == 'transfer'){
                restore_method = 'transfer_';
            }

            if(typeof div_id == 'undefined') {
                var restore_msg = "<div style=\"clear:both;\">" + msg + "</div>";
            }
            else{
                var restore_msg = "<div id=\"restore_file_"+div_id+"\"  style=\"clear:both;\">" + msg + "</div>";
            }
            if(append == true) {
                jQuery('#wpvivid_restore_'+restore_method+'log').append(restore_msg);
            }
            else{
                if(jQuery('#restore_file_'+div_id).length )
                {
                    jQuery('#restore_file_'+div_id).html(msg);
                }
                else
                {
                    jQuery('#wpvivid_restore_'+restore_method+'log').append(restore_msg);
                }
            }
            var div = jQuery('#wpvivid_restore_' + restore_method + 'log');
            div[0].scrollTop = div[0].scrollHeight;
        }

        /**
         * Lock certain operations while a restore task is running.
         */
        function wpvivid_restore_lock(){
            jQuery('#wpvivid_postbox_backup_percent').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_postbox_backup').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_postbox_backup_schedule').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_quickbackup_btn').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_tab_backup').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_tab_upload').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_tab_backup_log').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_tab_restore').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#page-backups').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#storage-page').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#settings-page').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#debug-page').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#logs-page').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_tab_migrate').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_add_tab_migrate').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_add_tab_import').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_add_tab_key').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_add_tab_log').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_add_tab_restore').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_restore_is_migrate').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_replace_domain').css({'pointer-events': 'none', 'opacity': '1'});
            jQuery('#wpvivid_keep_domain').css({'pointer-events': 'none', 'opacity': '1'});
        }

        /**
         * Unlock the operations once restore task completed.
         */
        function wpvivid_restore_unlock(){
            jQuery('#wpvivid_postbox_backup_percent').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_postbox_backup').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_postbox_backup_schedule').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_quickbackup_btn').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_tab_backup').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_tab_upload').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_tab_backup_log').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_tab_restore').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#page-backups').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#storage-page').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#settings-page').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#debug-page').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#logs-page').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_tab_migrate').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_add_tab_migrate').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_add_tab_import').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_add_tab_key').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_add_tab_log').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_add_tab_restore').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_restore_is_migrate').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_replace_domain').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#wpvivid_keep_domain').css({'pointer-events': 'auto', 'opacity': '1'});
        }
    </script>
    <?php
}

function wpvivid_backuppage_add_page_downlaod(){
    ?>
    <div class="backup-tab-content wpvivid_tab_download" id="page-download" style="padding-top: 1em; display:none;">
        <div id="wpvivid_init_download_info">
            <div style="float: left; height: 20px; line-height: 20px; margin-top: 4px;">Initializing the download info</div>
            <div class="spinner" style="float: left;"></div>
            <div style="clear: both;"></div>
        </div>
        <div class="wpvivid-element-space-bottom" id="wpvivid_files_list">
        </div>
    </div>

    <script>
        var wpvivid_download_files_list = wpvivid_download_files_list || {};
        wpvivid_download_files_list.backup_id='';
        wpvivid_download_files_list.wpvivid_download_file_array = Array();
        wpvivid_download_files_list.wpvivid_download_lock_array = Array();
        wpvivid_download_files_list.init=function(backup_id) {
            wpvivid_download_files_list.backup_id=backup_id;
            wpvivid_download_files_list.wpvivid_download_file_array.splice(0, wpvivid_download_files_list.wpvivid_download_file_array.length);
        };

        wpvivid_download_files_list.add_download_queue=function(filename) {
            var download_file_size = jQuery("[slug='"+filename+"']").find('.wpvivid-download-status').find('.wpvivid-download-file-size').html();
            var tmp_html = '<div class="wpvivid-element-space-bottom">' +
                '<span class="wpvivid-element-space-right">Retriving (remote storage to web server)</span><span class="wpvivid-element-space-right">|</span><span>File Size: </span><span class="wpvivid-element-space-right">'+download_file_size+'</span><span class="wpvivid-element-space-right">|</span><span>Downloaded Size: </span><span>0</span>' +
                '</div>' +
                '<div style="width:100%;height:10px; background-color:#dcdcdc;">' +
                '<div style="background-color:#0085ba; float:left;width:0%;height:10px;"></div>' +
                '</div>';
            jQuery("[slug='"+filename+"']").find('.wpvivid-download-status').html(tmp_html);
            if(jQuery.inArray(filename, wpvivid_download_files_list.wpvivid_download_file_array) === -1) {
                wpvivid_download_files_list.wpvivid_download_file_array.push(filename);
            }
            var ajax_data = {
                'action': 'wpvivid_prepare_download_backup',
                'backup_id':wpvivid_download_files_list.backup_id,
                'file_name':filename
            };
            wpvivid_post_request(ajax_data, function(data)
            {
            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
            }, 0);

            wpvivid_download_files_list.check_queue();
        };

        wpvivid_download_files_list.check_queue=function() {
            if(jQuery.inArray(wpvivid_download_files_list.backup_id, wpvivid_download_files_list.wpvivid_download_lock_array) !== -1){
                return;
            }
            var ajax_data = {
                'action': 'wpvivid_get_download_progress',
                'backup_id':wpvivid_download_files_list.backup_id,
            };
            wpvivid_download_files_list.wpvivid_download_lock_array.push(wpvivid_download_files_list.backup_id);
            wpvivid_post_request(ajax_data, function(data)
            {
                wpvivid_download_files_list.wpvivid_download_lock_array.splice(jQuery.inArray(wpvivid_download_files_list.backup_id, wpvivid_download_files_list.wpvivid_download_file_array),1);
                var jsonarray = jQuery.parseJSON(data);
                if (jsonarray.result === 'success')
                {
                    jQuery.each(jsonarray.files,function (index, value)
                    {
                        if(jQuery.inArray(index, wpvivid_download_files_list.wpvivid_download_file_array) !== -1) {
                            if(value.status === 'timeout' || value.status === 'completed' || value.status === 'error'){
                                wpvivid_download_files_list.wpvivid_download_file_array.splice(jQuery.inArray(index, wpvivid_download_files_list.wpvivid_download_file_array),1);
                            }
                            wpvivid_download_files_list.update_item(index, value);
                        }
                    });

                    //if(jsonarray.need_update)
                    if(wpvivid_download_files_list.wpvivid_download_file_array.length > 0)
                    {
                        setTimeout(function()
                        {
                            wpvivid_download_files_list.check_queue();
                        }, 3000);
                    }
                }
            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                wpvivid_download_files_list.wpvivid_download_lock_array.splice(jQuery.inArray(wpvivid_download_files_list.backup_id, wpvivid_download_files_list.wpvivid_download_file_array),1);
                setTimeout(function()
                {
                    wpvivid_download_files_list.check_queue();
                }, 3000);
            }, 0);
        };

        wpvivid_download_files_list.update_item=function(index,file) {
            jQuery("[slug='"+index+"']").find('.wpvivid-download-status').html(file.html);
        };

        wpvivid_download_files_list.download_now=function(filename) {
            wpvivid_location_href=true;
            location.href =ajaxurl+'?_wpnonce='+wpvivid_ajax_object.ajax_nonce+'&action=wpvivid_download_backup&backup_id='+wpvivid_download_files_list.backup_id+'&file_name='+filename;
        };

        function wpvivid_initialize_download(backup_id, list_name){
            jQuery('#wpvivid_tab_download').show();
            wpvivid_click_switch_page('backup', 'wpvivid_tab_download', true);
            wpvivid_init_download_page(backup_id);


            /*wpvivid_reset_backup_list(list_name);
            jQuery('#wpvivid_download_loading_'+backup_id).addClass('is-active');
            tmp_current_click_backupid = backup_id;
            var ajax_data = {
                'action':'wpvivid_init_download_page',
                'backup_id':backup_id
            };
            wpvivid_post_request(ajax_data, function(data){
                try {
                    var jsonarray = jQuery.parseJSON(data);
                    jQuery('#wpvivid_download_loading_'+backup_id).removeClass('is-active');
                    if (jsonarray.result === 'success') {
                        jQuery('#wpvivid_file_part_' + backup_id).html("");
                        var i = 0;
                        var file_not_found = false;
                        var file_name = '';
                        jQuery.each(jsonarray.files, function (index, value) {
                            i++;
                            file_name = index;
                            if (value.status === 'need_download') {
                                jQuery('#wpvivid_file_part_' + backup_id).append(value.html);
                                //tmp_current_click_backupid = '';
                            }
                            else if (value.status === 'running') {
                                if (m_downloading_file_name === file_name) {
                                    wpvivid_lock_download(tmp_current_click_backupid);
                                }
                                jQuery('#wpvivid_file_part_' + backup_id).append(value.html);
                            }
                            else if (value.status === 'completed') {
                                if (m_downloading_file_name === file_name) {
                                    wpvivid_unlock_download(tmp_current_click_backupid);
                                    m_downloading_id = '';
                                    m_downloading_file_name = '';
                                }
                                jQuery('#wpvivid_file_part_' + backup_id).append(value.html);
                                //tmp_current_click_backupid = '';
                            }
                            else if (value.status === 'timeout') {
                                if (m_downloading_file_name === file_name) {
                                    wpvivid_unlock_download(tmp_current_click_backupid);
                                    m_downloading_id = '';
                                    m_downloading_file_name = '';
                                }
                                jQuery('#wpvivid_file_part_' + backup_id).append(value.html);
                                //tmp_current_click_backupid = '';
                            }
                            else if (value.status === 'file_not_found') {
                                wpvivid_unlock_download(tmp_current_click_backupid);
                                wpvivid_reset_backup_list(list_name);
                                file_not_found = true;
                                alert("Download failed, file not found. The file might has been moved, renamed or deleted. Please verify the file exists and try again.");
                                //tmp_current_click_backupid = '';
                                return false;
                            }
                        });
                        if (file_not_found === false) {
                            jQuery('#wpvivid_file_part_' + backup_id).append(jsonarray.place_html);
                        }
                    }
                }
                catch(err){
                    alert(err);
                    jQuery('#wpvivid_download_loading_'+backup_id).removeClass('is-active');
                }
            },function(XMLHttpRequest, textStatus, errorThrown){
                jQuery('#wpvivid_download_loading_'+backup_id).removeClass('is-active');
                var error_message = wpvivid_output_ajaxerror('initializing download information', textStatus, errorThrown);
                alert(error_message);
            });*/
        }

        function wpvivid_init_download_page(backup_id){
            jQuery('#wpvivid_files_list').html('');
            jQuery('#wpvivid_init_download_info').show();
            jQuery('#wpvivid_init_download_info').find('.spinner').addClass('is-active');
            var ajax_data = {
                'action':'wpvivid_init_download_page',
                'backup_id':backup_id
            };
            var retry = '<input type="button" class="button button-primary" value="Retry the initialization" onclick="wpvivid_init_download_page(\''+backup_id+'\');" />';

            wpvivid_post_request(ajax_data, function(data)
            {
                jQuery('#wpvivid_init_download_info').hide();
                jQuery('#wpvivid_init_download_info').find('.spinner').removeClass('is-active');
                try
                {
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success')
                    {
                        wpvivid_download_files_list.init(backup_id);
                        var need_check_queue = false;
                        jQuery.each(jsonarray.files,function (index, value)
                        {
                            if(value.status === 'running'){
                                if(jQuery.inArray(index, wpvivid_download_files_list.wpvivid_download_file_array) === -1) {
                                    wpvivid_download_files_list.wpvivid_download_file_array.push(index);
                                    need_check_queue = true;
                                }
                            }
                        });
                        if(need_check_queue) {
                            wpvivid_download_files_list.check_queue();
                        }
                        jQuery('#wpvivid_files_list').html(jsonarray.html);
                    }
                    else{
                        alert(jsonarray.error);
                        jQuery('#wpvivid_files_list').html(retry);
                    }
                }
                catch(err)
                {
                    alert(err);
                    jQuery('#wpvivid_files_list').html(retry);
                }
            },function(XMLHttpRequest, textStatus, errorThrown)
            {
                jQuery('#wpvivid_init_download_info').hide();
                jQuery('#wpvivid_init_download_info').find('.spinner').removeClass('is-active');
                var error_message = wpvivid_output_ajaxerror('initializing download information', textStatus, errorThrown);
                alert(error_message);
                jQuery('#wpvivid_files_list').html(retry);
            });
        }

        function wpvivid_download_change_page(page)
        {
            var backup_id=wpvivid_download_files_list.backup_id;

            var ajax_data = {
                'action':'wpvivid_get_download_page_ex',
                'backup_id':backup_id,
                'page':page
            };

            jQuery('#wpvivid_files_list').html('');
            jQuery('#wpvivid_init_download_info').show();
            jQuery('#wpvivid_init_download_info').find('.spinner').addClass('is-active');

            wpvivid_post_request(ajax_data, function(data)
            {
                jQuery('#wpvivid_init_download_info').hide();
                jQuery('#wpvivid_init_download_info').find('.spinner').removeClass('is-active');
                try
                {
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success')
                    {
                        jQuery('#wpvivid_files_list').html(jsonarray.html);
                    }
                    else{
                        alert(jsonarray.error);
                    }
                }
                catch(err)
                {
                    alert(err);
                }
            },function(XMLHttpRequest, textStatus, errorThrown)
            {
                jQuery('#wpvivid_init_download_info').hide();
                jQuery('#wpvivid_init_download_info').find('.spinner').removeClass('is-active');
                var error_message = wpvivid_output_ajaxerror('initializing download information', textStatus, errorThrown);
                alert(error_message);
            });
        }

        jQuery('#wpvivid_files_list').on("click",'.wpvivid-download',function()
        {
            var Obj=jQuery(this);
            var file_name=Obj.closest('tr').attr('slug');
            wpvivid_download_files_list.add_download_queue(file_name);
        });
        jQuery('#wpvivid_files_list').on("click",'.wpvivid-ready-download',function()
        {
            var Obj=jQuery(this);
            var file_name=Obj.closest('tr').attr('slug');
            wpvivid_download_files_list.download_now(file_name);
        });

        jQuery('#wpvivid_files_list').on("click",'.first-page',function() {
            wpvivid_download_change_page('first');
        });

        jQuery('#wpvivid_files_list').on("click",'.prev-page',function() {
            var page=parseInt(jQuery(this).attr('value'));
            wpvivid_download_change_page(page-1);
        });

        jQuery('#wpvivid_files_list').on("click",'.next-page',function() {
            var page=parseInt(jQuery(this).attr('value'));
            wpvivid_download_change_page(page+1);
        });

        jQuery('#wpvivid_files_list').on("click",'.last-page',function() {
            wpvivid_download_change_page('last');
        });

        jQuery('#wpvivid_files_list').on("keypress", '.current-page', function(){
            if(event.keyCode === 13){
                var page = jQuery(this).val();
                wpvivid_download_change_page(page);
            }
        });
    </script>
    <?php
}

function wpvivid_backuppage_add_progress_module(){
    ?>
    <div class="postbox" id="wpvivid_postbox_backup_percent" style="display: none;">
        <div class="action-progress-bar" id="wpvivid_action_progress_bar">
            <div class="action-progress-bar-percent" id="wpvivid_action_progress_bar_percent" style="height:24px;width:0;"></div>
        </div>
        <div id="wpvivid_estimate_upload_info" style="float: left;">
            <div class="backup-basic-info"><span class="wpvivid-element-space-right"><?php esc_html_e('Total Size:', 'wpvivid-backuprestore'); ?></span><span>N/A</span></div>
            <div class="backup-basic-info"><span class="wpvivid-element-space-right"><?php esc_html_e('Uploaded:', 'wpvivid-backuprestore'); ?></span><span>N/A</span></div>
            <div class="backup-basic-info"><span class="wpvivid-element-space-right"><?php esc_html_e('Speed:', 'wpvivid-backuprestore'); ?></span><span>N/A</span></div>
        </div>
        <div style="float: left;">
            <div class="backup-basic-info"><span class="wpvivid-element-space-right"><?php esc_html_e('Network Connection:', 'wpvivid-backuprestore'); ?></span><span>N/A</span></div>
        </div>
        <div style="clear:both;"></div>
        <div style="margin-left:10px; float: left; width:100%;"><p id="wpvivid_current_doing"></p></div>
        <div style="clear: both;"></div>
        <div>
            <div id="wpvivid_backup_cancel" class="backup-log-btn"><input class="button-primary" id="wpvivid_backup_cancel_btn" type="submit" value="<?php esc_attr_e( 'Cancel', 'wpvivid-backuprestore' ); ?>"  /></div>
            <div id="wpvivid_backup_log" class="backup-log-btn"><input class="button-primary" id="wpvivid_backup_log_btn" type="submit" value="<?php esc_attr_e( 'Log', 'wpvivid-backuprestore' ); ?>" /></div>
        </div>
        <div style="clear: both;"></div>
    </div>
    <script>
        jQuery('#wpvivid_postbox_backup_percent').on("click", "input", function(){
            if(jQuery(this).attr('id') === 'wpvivid_backup_cancel_btn'){
                wpvivid_cancel_backup();
            }
            if(jQuery(this).attr('id') === 'wpvivid_backup_log_btn'){
                wpvivid_read_log('wpvivid_view_backup_task_log');
            }
        });
            
        function wpvivid_cancel_backup()
        {
            var ajax_data= {
                'action': 'wpvivid_backup_cancel'
                //'task_id': running_backup_taskid
            };
            jQuery('#wpvivid_backup_cancel_btn').css({'pointer-events': 'none', 'opacity': '0.4'});
            wpvivid_post_request(ajax_data, function(data)
            {
                try
                {
                    var jsonarray = jQuery.parseJSON(data);
                    if(jsonarray.no_response)
                    {
                        var ret = confirm(jsonarray.msg);
                        if(ret === true)
                        {
                            wpvivid_termination_backup_task(jsonarray.task_id);
                        }
                    }
                    else
                    {
                        jQuery('#wpvivid_current_doing').html(jsonarray.msg);
                    }
                }
                catch(err)
                {
                    alert(err);
                }
            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                jQuery('#wpvivid_backup_cancel_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                var error_message = wpvivid_output_ajaxerror('cancelling the backup', textStatus, errorThrown);
                wpvivid_add_notice('Backup', 'Error', error_message);
            });
        }

        function wpvivid_termination_backup_task(task_id)
        {
            var ajax_data= {
                'action': 'wpvivid_shutdown_backup',
                'task_id': task_id
            };
            wpvivid_post_request(ajax_data, function(data)
            {
                try
                {
                }
                catch(err)
                {
                    alert(err);
                }
            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                var error_message = wpvivid_output_ajaxerror('terminationing the backup', textStatus, errorThrown);
                wpvivid_add_notice('Backup', 'Error', error_message);
            });
        }
    </script>
    <?php
}

function wpvivid_backuppage_add_backup_module(){
    ?>
    <div class="postbox quickbackup" id="wpvivid_postbox_backup">
        <?php
        do_action('wpvivid_backup_module_add_sub');
        ?>
    </div>
   <?php
}

function wpvivid_backup_module_add_descript(){
    $backupdir=WPvivid_Setting::get_backupdir();
    ?>
    <div style="font-size: 14px; padding: 8px 12px; margin: 0; line-height: 1.4; font-weight: 600;">
        <span style="margin-right: 5px;"><?php esc_html_e( 'Back Up Manually','wpvivid-backuprestore'); ?></span>
        <span style="margin-right: 5px;">|</span>
        <span style="margin-right: 0;"><a href="<?php echo esc_url('https://wordpress.org/plugins/wpvivid-imgoptim/'); ?>" style="text-decoration: none;"><?php esc_html_e('Compress images with our image optimization plugin, it\'s free', 'wpvivid-backuprestore'); ?></a></span>
    </div>
    <div class="quickstart-storage-setting">
        <span class="list-top-chip backup" name="ismerge" value="1" style="margin: 10px 10px 10px 0;"><?php esc_html_e('Local Storage Directory:', 'wpvivid-backuprestore'); ?></span>
        <span class="list-top-chip" id="wpvivid_local_storage_path" style="margin: 10px 10px 10px 0;"><?php echo esc_html(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backupdir); ?></span>
        <span class="list-top-chip" style="margin: 10px 10px 10px 0;"><a href="#" onclick="wpvivid_click_switch_page('wrap', 'wpvivid_tab_setting', true);" style="text-decoration: none;"><?php esc_html_e('rename directory', 'wpvivid-backuprestore'); ?></a></span>
    </div>
    <?php
}

function wpvivid_backup_module_add_backup_type(){
    $type_name = 'backup_files';
    ?>
    <div class="quickstart-archive-block">
        <fieldset>
            <legend class="screen-reader-text"><span>input type="radio"</span></legend>
            <?php do_action('wpvivid_add_backup_type', $type_name); ?>
            <label style="display: none;">
                <input type="checkbox" option="backup" name="ismerge" value="1" checked />
            </label><br>
        </fieldset>
    </div>
    <?php
}

function wpvivid_backup_module_add_send_remote(){
    $pic='';
    ?>
    <div class="quickstart-storage-block">
        <fieldset>
            <legend class="screen-reader-text"><span>input type="checkbox"</span></legend>
            <label>
                <input type="radio" id="wpvivid_backup_local" option="backup_ex" name="local_remote" value="local" checked />
                <span><?php esc_html_e( 'Save Backups to Local', 'wpvivid-backuprestore' ); ?></span>
            </label><br>
            <label>
                <input type="radio" id="wpvivid_backup_remote" option="backup_ex" name="local_remote" value="remote" />
                <span><?php esc_html_e( 'Send Backup to Remote Storage:', 'wpvivid-backuprestore' ); ?></span>
            </label><br>
            <div id="upload_storage" style="cursor:pointer;" title="Highlighted icon illuminates that you have choosed a remote storage to store backups">
                <?php
                $remoteslist=WPvivid_Setting::get_all_remote_options();
                $default_remote_storage=array();
                foreach ($remoteslist['remote_selected'] as $value) {
                    $default_remote_storage[]=$value;
                }
                $remote_storage_type=array();
                foreach ($remoteslist as $key=>$value)
                {
                    if(in_array($key, $default_remote_storage))
                    {
                        $remote_storage_type[]=$value['type'];
                    }
                }

                $remote=array();
                $remote=apply_filters('wpvivid_remote_pic', $remote);
                if(is_array($remote))
                {
                    foreach ($remote as $key => $value) {
                        $title = $value['title'];
                        if (in_array($key, $remote_storage_type)) {
                            $pic = $value['selected_pic'];
                        } else {
                            $pic = $value['default_pic'];
                        }
                        $url = apply_filters('wpvivid_get_wpvivid_pro_url', WPVIVID_PLUGIN_URL, $key);
                        echo '<img  src="' . esc_url($url . $pic) . '" style="vertical-align:middle; " title="' . esc_attr($title) . '"/>';
                    }
                    echo '<img onclick="wpvivid_click_switch_page(\'wrap\', \'wpvivid_tab_remote_storage\', true);" src="'.esc_url(WPVIVID_PLUGIN_URL.'/admin/partials/images/add-storages.png').'" style="vertical-align:middle;" title="'.esc_attr__('Add a storage', 'wpvivid-backuprestore').'"/>';
                }
                ?>
            </div>
        </fieldset>
    </div>
    <?php
}

function wpvivid_backup_module_add_exec(){
    ?>
    <div class="quickstart-btn" style="padding-top:20px;">
        <input class="button-primary quickbackup-btn" id="wpvivid_quickbackup_btn" type="submit" value="<?php esc_attr_e( 'Backup Now', 'wpvivid-backuprestore'); ?>" />
        <div class="schedule-tab-block" style="text-align:center;">
            <fieldset>
                <label>
                    <input type="checkbox" id="wpvivid_backup_lock" option="backup" name="lock" />
                    <span><?php esc_html_e( 'This backup can only be deleted manually', 'wpvivid-backuprestore' ); ?></span>
                </label>
            </fieldset>
        </div>
    </div>
    <script>

    var m_need_update_2=false;
    jQuery(document).ready(function ()
    {
        wpvivid_activate_cron_2();
        m_need_update_2=true;
        wpvivid_manage_task_2();
    });

    jQuery(document).ready(function($)
    {
        jQuery(document).on('wpvivid_update_local_backup', function(event)
        {
            wpvivid_retrieve_backup_list();
        });

        jQuery(document).on('wpvivid_update_log_list', function(event)
        {
            wpvivid_retrieve_log_list();
        });
    });

    function wpvivid_activate_cron_2()
    {
        var next_get_time = 3 * 60 * 1000;
        wpvivid_cron_task();
        setTimeout("wpvivid_activate_cron_2()", next_get_time);
        setTimeout(function(){
            m_need_update_2=true;
        }, 10000);
    }

    function wpvivid_manage_task_2()
    {
        if(m_need_update_2 === true)
        {
            m_need_update_2 = false;
            wpvivid_check_runningtask_2();
        }
        else{
            setTimeout(function()
            {
                wpvivid_manage_task_2();
            }, 3000);
        }
    }

    function wpvivid_check_runningtask_2()
    {
        var ajax_data = {
            'action': 'wpvivid_list_tasks_2'
        };

        wpvivid_post_request(ajax_data, function (data)
        {
            setTimeout(function ()
            {
                wpvivid_manage_task_2();
            }, 3000);
            try
            {
                var jsonarray = jQuery.parseJSON(data);
                wpvivid_list_task_2_data(jsonarray);
            }
            catch(err)
            {
                alert(err);
            }
        }, function (XMLHttpRequest, textStatus, errorThrown)
        {
            setTimeout(function ()
            {
                m_need_update_2 = true;
                wpvivid_manage_task_2();
            }, 3000);
        });
    }

    function wpvivid_list_task_2_data(data)
    {
        var b_has_data = false;

        if(data.progress_html!==false)
        {
            jQuery('#wpvivid_postbox_backup_percent').show();
            jQuery('#wpvivid_postbox_backup_percent').html(data.progress_html);
        }
        else
        {
            if(!wpvivid_prepare_backup)
                jQuery('#wpvivid_postbox_backup_percent').hide();
        }

        if(data.upload_progress_html!==false)
        {
            jQuery('#wpvivid_upload_backup_percent').show();
            jQuery('#wpvivid_upload_backup_percent').html(data.upload_progress_html);
        }
        else
        {
            jQuery('#wpvivid_upload_backup_percent').hide();
        }


        var update_backup=false;
        if (data.success_notice_html !== false)
        {
            jQuery('#wpvivid_backup_notice').show();
            jQuery('#wpvivid_backup_notice').html(data.success_notice_html);
            update_backup=true;
        }
        if(data.error_notice_html !== false)
        {
            jQuery('#wpvivid_backup_notice').show();
            jQuery('#wpvivid_backup_notice').html(data.error_notice_html);
            update_backup=true;
        }

        if(update_backup)
        {
            jQuery( document ).trigger( 'wpvivid_update_local_backup');
            jQuery( document ).trigger( 'wpvivid_update_log_list');
        }

        if(data.last_msg_html !== false)
        {
            jQuery('#wpvivid_last_backup_msg').html(data.last_msg_html);
        }

        if(data.need_update)
        {
            m_need_update_2 = true;
        }

        if(data.task_no_response)
        {
            //jQuery('#wpvivid_current_doing').html('Task no response');
            jQuery('#wpvivid_backup_cancel_btn').css({'pointer-events': 'auto', 'opacity': '1'});
        }

        if(data.running_backup_taskid!== '')
        {
            b_has_data = true;
            task_retry_times = 0;
            running_backup_taskid = data.running_backup_taskid;
            wpvivid_control_backup_lock();
            if(data.wait_resume)
            {
                if (data.next_resume_time !== 'get next resume time failed.')
                {
                    wpvivid_resume_backup2(running_backup_taskid, data.next_resume_time);
                }
                else {
                    wpvivid_delete_backup_task(running_backup_taskid);
                }
            }
        }
        else
        {
            if(!wpvivid_prepare_backup)
            {
                jQuery('#wpvivid_backup_cancel_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                wpvivid_control_backup_unlock();
            }
            running_backup_taskid='';
        }
        if (!b_has_data)
        {
            task_retry_times++;
            if (task_retry_times < 5)
            {
                m_need_update_2 = true;
            }
        }
    }

    function wpvivid_resume_backup2(backup_id, next_resume_time)
    {
        if(next_resume_time < 0){
            next_resume_time = 0;
        }
        next_resume_time = next_resume_time * 1000;
        setTimeout("wpvivid_cron_task()", next_resume_time);
        setTimeout(function(){
            task_retry_times = 0;
            m_need_update_2=true;
        }, next_resume_time);
    }

    jQuery('#wpvivid_quickbackup_btn').click(function(){
        wpvivid_clear_notice('wpvivid_backup_notice');
        wpvivid_start_backup();
    });
    
    function wpvivid_start_backup(){
        var bcheck=true;
        var bdownloading=false;
        if(m_downloading_id !== '') {
            var descript = '<?php esc_html_e('This request might delete the backup being downloaded, are you sure you want to continue?', 'wpvivid-backuprestore'); ?>';
            var ret = confirm(descript);
            if (ret === true) {
                bcheck=true;
                bdownloading=true;
            }
            else{
                bcheck=false;
            }
        }
        if(bcheck) {
            var backup_data = wpvivid_ajax_data_transfer('backup');
            backup_data = JSON.parse(backup_data);
            jQuery('input:radio[option=backup_ex]').each(function() {
                if(jQuery(this).prop('checked'))
                {
                    var key = jQuery(this).prop('name');
                    var value = jQuery(this).prop('value');
                    var json = new Array();
                    if(value == 'local'){
                        json['local']='1';
                        json['remote']='0';
                    }
                    else if(value == 'remote'){
                        json['local']='0';
                        json['remote']='1';
                    }
                }
                jQuery.extend(backup_data, json);
            });
            backup_data = JSON.stringify(backup_data);
            var ajax_data = {
                'action': 'wpvivid_prepare_backup_2',
                'backup': backup_data
            };
            wpvivid_control_backup_lock();
            jQuery('#wpvivid_backup_cancel_btn').css({'pointer-events': 'none', 'opacity': '0.4'});
            jQuery('#wpvivid_backup_log_btn').css({'pointer-events': 'none', 'opacity': '0.4'});

            jQuery('#wpvivid_postbox_backup_percent').html('<div class="action-progress-bar" id="wpvivid_action_progress_bar">\n' +
                '                                                <div class="action-progress-bar-percent" id="wpvivid_action_progress_bar_percent" style="height:24px;width:0%"></div>\n' +
                '                                             </div>\n' +
                '                                             <div id="wpvivid_estimate_upload_info" style="float: left;"> \n' +
                '                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">Total Size:</span><span>N/A</span></div>\n' +
                '                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">Uploaded:</span><span>N/A</span></div>\n' +
                '                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">Speed:</span><span>N/A</span></div>\n' +
                '                                             </div>\n' +
                '                                             <div style="float: left;">\n' +
                '                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">Network Connection:</span><span>N/A</span></div>\n' +
                '                                             </div>\n' +
                '                                             <div style="clear:both;"></div>\n' +
                '                                             <div style="margin-left:10px; float: left; width:100%;"><p id="wpvivid_current_doing">Ready to backup. Progress: 0%, running time: 0second.</p></div>\n' +
                '                                             <div style="clear: both;"></div>\n' +
                '                                             <div>\n' +
                '                                                <div id="wpvivid_backup_cancel" class="backup-log-btn"><input class="button-primary" id="wpvivid_backup_cancel_btn" type="submit" value="Cancel" style="pointer-events: none; opacity: 0.4;" /></div>\n' +
                '                                             </div>\n' +
                '                                             <div style="clear: both;"></div>');
            jQuery('#wpvivid_postbox_backup_percent').show();

            wpvivid_completed_backup = 1;
            wpvivid_prepare_backup = true;
            wpvivid_post_request(ajax_data, function (data)
            {
                wpvivid_prepare_backup = false;
                try {
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'failed') {
                        wpvivid_delete_ready_task(jsonarray.error);
                    }
                    else if (jsonarray.result === 'success') {
                        if(bdownloading) {
                            m_downloading_id = '';
                        }
                        m_backup_task_id = jsonarray.task_id;

                        jQuery('#wpvivid_backup_list').html('');
                        jQuery('#wpvivid_backup_list').append(jsonarray.html);
                        wpvivid_backup_now(m_backup_task_id);
                        /*
                         var descript = '';
                        if (jsonarray.check.alert_db === true || jsonarray.check.alter_files === true) {
                            descript = 'The database (the dumping SQL file) might be too large, backing up the database may run out of server memory and result in a backup failure.\n' +
                                'One or more files might be too large, backing up the file(s) may run out of server memory and result in a backup failure.\n' +
                                'Click OK button and continue to back up.';
                            var ret = confirm(descript);
                            if (ret === true) {
                                jQuery('#wpvivid_backup_list').html('');
                                jQuery('#wpvivid_backup_list').append(jsonarray.html);
                                wpvivid_backup_now(m_backup_task_id);
                            }
                            else {
                                jQuery('#wpvivid_backup_cancel_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                                jQuery('#wpvivid_backup_log_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                                wpvivid_control_backup_unlock();
                                jQuery('#wpvivid_postbox_backup_percent').hide();
                            }
                        }
                        else{
                            jQuery('#wpvivid_backup_list').html('');
                            jQuery('#wpvivid_backup_list').append(jsonarray.html);
                            wpvivid_backup_now(jsonarray.task_id);
                        } */
                    }
                }
                catch (err) {
                    wpvivid_delete_ready_task(err);
                }
            }, function (XMLHttpRequest, textStatus, errorThrown) {
                //var error_message = wpvivid_output_ajaxerror('preparing the backup', textStatus, errorThrown);
                var error_message=wpvividlion.backup_calc_timeout;//'Calculating the size of files, folder and database timed out. If you continue to receive this error, please go to the plugin settings, uncheck \'Calculate the size of files, folder and database before backing up\', save changes, then try again.';
                wpvivid_delete_ready_task(error_message);
            });
        }
    }
    
    function wpvivid_backup_now(task_id){
        var ajax_data = {
            'action': 'wpvivid_backup_now_2',
            'task_id': task_id
        };
        task_retry_times = 0;
        m_need_update_2=true;
        wpvivid_post_request(ajax_data, function(data){
        }, function(XMLHttpRequest, textStatus, errorThrown) {
        });
    }
    
    function wpvivid_delete_backup_task(task_id){
        var ajax_data = {
            'action': 'wpvivid_delete_task_2',
            'task_id': task_id
        };
        wpvivid_post_request(ajax_data, function(data){}, function(XMLHttpRequest, textStatus, errorThrown) {
        });
    }
    
    function wpvivid_control_backup_lock(){
        jQuery('#wpvivid_quickbackup_btn').css({'pointer-events': 'none', 'opacity': '0.4'});
        jQuery('#wpvivid_transfer_btn').css({'pointer-events': 'none', 'opacity': '0.4'});
    }
    
    function wpvivid_control_backup_unlock(){
        jQuery('#wpvivid_quickbackup_btn').css({'pointer-events': 'auto', 'opacity': '1'});
        jQuery('#wpvivid_transfer_btn').css({'pointer-events': 'auto', 'opacity': '1'});
    }
    
    function wpvivid_delete_ready_task(error){
        var ajax_data={
            'action': 'wpvivid_delete_ready_task_2'
        };
        wpvivid_post_request(ajax_data, function (data) {
            try {
                var jsonarray = jQuery.parseJSON(data);
                if (jsonarray.result === 'success') {
                    wpvivid_add_notice('Backup', 'Error', error);
                    wpvivid_control_backup_unlock();
                    jQuery('#wpvivid_postbox_backup_percent').hide();
                }
            }
            catch(err){
                wpvivid_add_notice('Backup', 'Error', err);
                wpvivid_control_backup_unlock();
                jQuery('#wpvivid_postbox_backup_percent').hide();
            }
        }, function (XMLHttpRequest, textStatus, errorThrown) {
            setTimeout(function () {
                wpvivid_delete_ready_task(error);
            }, 3000);
        });
    }
    </script>
    <?php
}

function wpvivid_backup_module_add_tips(){
    ?>
    <div class="custom-info" style="float:left; width:100%;">
        <strong><?php esc_html_e('Tip:', 'wpvivid-backuprestore'); ?></strong>&nbsp<?php esc_html_e('The settings are only for manual backup, which won\'t affect schedule settings.', 'wpvivid-backuprestore'); ?>
    </div>
    <?php
}

function wpvivid_backuppage_add_schedule_module(){
    $schedule=WPvivid_Schedule::get_schedule();
    if($schedule['enable']){
        $schedule_status='Enabled';
        $next_backup_time=gmdate("l, F-d-Y H:i", $schedule['next_start']);
    }
    else{
        $schedule_status='Disabled';
        $next_backup_time='N/A';
    }
    ?>
    <div class="postbox qucikbackup-schedule" id="wpvivid_postbox_backup_schedule">
        <h2><span><?php esc_html_e( 'Backup Schedule','wpvivid-backuprestore'); ?></span></h2>
        <div class="schedule-block">
            <p id="wpvivid_schedule_status"><strong><?php esc_html_e('Schedule Status: ', 'wpvivid-backuprestore'); ?></strong><?php echo esc_html($schedule_status); ?></p>
            <div id="wpvivid_schedule_info">
                <p><strong><?php esc_html_e('Server Time: ', 'wpvivid-backuprestore'); ?></strong><?php echo esc_html(gmdate("F-d-Y H:i",time())); ?></p>
                <p><span id="wpvivid_last_backup_msg"><?php do_action('wpvivid_get_last_backup_message_output'); ?></span></p>
                <p id="wpvivid_next_backup"><strong><?php esc_html_e('Next Backup: ', 'wpvivid-backuprestore'); ?></strong><?php echo esc_html($next_backup_time); ?></p>
            </div>
        </div>
    </div>
    <div style="clear:both;"></div>
    <?php
}


add_action('wpvivid_add_backup_type', 'wpvivid_add_backup_type', 11);
add_action('wpvivid_backup_do_js', 'wpvivid_backup_do_js', 10);
add_filter('wpvivid_download_backup_descript', 'wpvivid_download_backup_descript', 10);
add_filter('wpvivid_restore_website_descript', 'wpvivid_restore_website_descript', 10);

add_filter('wpvivid_backuppage_load_backuplist', 'wpvivid_backuppage_load_backuplist', 10);

add_action('wpvivid_backuppage_add_module', 'wpvivid_backuppage_add_progress_module', 10);
add_action('wpvivid_backuppage_add_module', 'wpvivid_backuppage_add_backup_module', 11);
add_action('wpvivid_backuppage_add_module', 'wpvivid_backuppage_add_schedule_module', 12);

add_action('wpvivid_backup_module_add_sub', 'wpvivid_backup_module_add_descript');
add_action('wpvivid_backup_module_add_sub', 'wpvivid_backup_module_add_backup_type');
add_action('wpvivid_backup_module_add_sub', 'wpvivid_backup_module_add_send_remote');
add_action('wpvivid_backup_module_add_sub', 'wpvivid_backup_module_add_exec');
add_action('wpvivid_backup_module_add_sub', 'wpvivid_backup_module_add_tips');

?>