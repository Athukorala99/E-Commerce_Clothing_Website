<?php

if (!defined('WPVIVID_PLUGIN_DIR')){
    die;
}

require_once WPVIVID_PLUGIN_DIR . '/includes/customclass/class-wpvivid-remote.php';

if(!defined('WPVIVID_REMOTE_ONEDRIVE'))
{
    define('WPVIVID_REMOTE_ONEDRIVE','onedrive');
}

if(!defined('WPVIVID_ONEDRIVE_DEFAULT_FOLDER'))
{
    define('WPVIVID_ONEDRIVE_DEFAULT_FOLDER','wpvivid_backup');
}

if(!defined('WPVIVID_ONEDRIVE_UPLOAD_SIZE'))
{
    define('WPVIVID_ONEDRIVE_UPLOAD_SIZE',1024*1024*2);
}

if(!defined('WPVIVID_ONEDRIVE_DOWNLOAD_SIZE'))
{
    define('WPVIVID_ONEDRIVE_DOWNLOAD_SIZE',1024*1024*2);
}

if(!defined('WPVIVID_ONEDRIVE_RETRY_TIMES'))
{
    define('WPVIVID_ONEDRIVE_RETRY_TIMES','3');
}

class WPvivid_one_drive extends WPvivid_Remote
{
    public $options;
    public $callback;
    public $add_remote;
    public function __construct($options=array())
    {
        if(empty($options))
        {
            if(!defined('WPVIVID_INIT_STORAGE_TAB_ONE_DRIVE'))
            {
                add_action('init', array($this, 'handle_auth_actions'));
                //wpvivid_one_drive_add_remote
                add_action('wp_ajax_wpvivid_one_drive_add_remote',array( $this,'finish_add_remote'));

                add_action('wpvivid_add_storage_tab',array($this,'wpvivid_add_storage_tab_one_drive'), 12);
                add_action('wpvivid_add_storage_page',array($this,'wpvivid_add_storage_page_one_drive'), 12);
                add_action('wpvivid_edit_remote_page',array($this,'wpvivid_edit_storage_page_one_drive'), 12);
                add_filter('wpvivid_remote_pic',array($this,'wpvivid_remote_pic_one_drive'),10);
                add_filter('wpvivid_get_out_of_date_remote',array($this,'wpvivid_get_out_of_date_one_drive'),10,2);
                add_filter('wpvivid_storage_provider_tran',array($this,'wpvivid_storage_provider_one_drive'),10);
                add_filter('wpvivid_get_root_path',array($this,'wpvivid_get_root_path_one_drive'),10);
                add_filter('wpvivid_pre_add_remote',array($this, 'pre_add_remote'),10,2);
                define('WPVIVID_INIT_STORAGE_TAB_ONE_DRIVE',1);
            }
        }
        else
        {
            $this->options=$options;
        }
        $this->add_remote=false;
    }

    public function pre_add_remote($remote,$id)
    {
        if($remote['type']==WPVIVID_REMOTE_ONEDRIVE)
        {
            $remote['id']=$id;
        }

        return $remote;
    }

    public function handle_auth_actions()
    {
        if (isset($_GET['action']) && isset($_GET['page']))
        {
            if($_GET['page'] === 'WPvivid')
            {
                if($_GET['action']=='wpvivid_one_drive_auth')
                {
                    try {
                        $auth_id = uniqid('wpvivid-auth-');
                        $remote_options['auth_id']=$auth_id;
                        update_option('wpvivid_tmp_remote_options',$remote_options);
                        $url = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize'
                            . '?client_id=' . urlencode('37668be9-b55f-458f-b6a3-97e6f8aa10c9')
                            . '&scope=' . urlencode('offline_access files.readwrite')
                            . '&response_type=code'
                            . '&redirect_uri=' . urlencode('https://auth.wpvivid.com/onedrive_v2/')
                            . '&state=' . urlencode(admin_url() . 'admin.php?page=WPvivid' . '&action=wpvivid_one_drive_finish_auth&main_tab=storage&sub_tab=one_drive&sub_page=storage_account_one_drive&auth_id='.$auth_id)
                            . '&display=popup'
                            . '&locale=en';
                        header('Location: ' . esc_url_raw($url));
                    }
                    catch (Exception $e){
                        echo '<div class="notice notice-error"><p>'.esc_html($e->getMessage()).'</p></div>';
                    }
                }
                else if($_GET['action']=='wpvivid_one_drive_finish_auth')
                {
                    try
                    {
                        if (isset($_GET['auth_error']))
                        {
                            $error = urldecode($_GET['auth_error']);
                            header('Location: ' . admin_url() . 'admin.php?page=' . WPVIVID_PLUGIN_SLUG . '&action=wpvivid_one_drive&result=error&resp_msg=' . $error);
                            return;
                        }

                        $remoteslist = WPvivid_Setting::get_all_remote_options();
                        foreach ($remoteslist as $key => $value)
                        {
                            if (isset($value['auth_id']) && isset($_GET['auth_id']) && $value['auth_id'] == sanitize_text_field($_GET['auth_id']))
                            {
                                echo '<div class="notice notice-success is-dismissible"><p>';
                                esc_html_e('You have authenticated the Microsoft OneDrive account as your remote storage.', 'wpvivid-backuprestore');
                                echo '</p></div>';
                                return;
                            }
                        }

                        $tmp_options=get_option('wpvivid_tmp_remote_options',false);
                        if($tmp_options===false)
                        {
                            return;
                        }
                        else
                        {
                            if($tmp_options['auth_id']===$_GET['auth_id'])
                            {
                                if(empty($_POST['refresh_token']))
                                {
                                    if(empty($tmp_options['token']['refresh_token']))
                                    {
                                        $err = 'No refresh token was received from OneDrive, which means that you entered client secret incorrectly, or that you did not re-authenticated yet after you corrected it. Please authenticate again.';
                                        header('Location: ' . admin_url() . 'admin.php?page=' . WPVIVID_PLUGIN_SLUG . '&action=wpvivid_one_drive&result=error&resp_msg='.$err);

                                        return;
                                    }
                                }
                                else
                                {
                                    $tmp_options['type'] = WPVIVID_REMOTE_ONEDRIVE;
                                    $tmp_options['token']['access_token']=base64_encode(sanitize_text_field($_POST['access_token']));
                                    $tmp_options['token']['refresh_token']=base64_encode(sanitize_text_field($_POST['refresh_token']));
                                    $tmp_options['token']['expires']=time()+$_POST['expires_in'];
                                    $tmp_options['is_encrypt'] = 1;
                                    update_option('wpvivid_tmp_remote_options',$tmp_options);
                                }
                                $this->add_remote=true;
                            }
                            else
                            {
                                return;
                            }
                        }
                    }
                    catch (Exception $e){
                        echo '<div class="notice notice-error"><p>'.esc_html($e->getMessage()).'</p></div>';
                    }
                }
                else if($_GET['action']=='wpvivid_one_drive')
                {
                    try {
                        if (isset($_GET['result'])) {
                            if ($_GET['result'] == 'success') {
                                add_action('show_notice', array($this, 'wpvivid_show_notice_add_onedrive_success'));
                            } else if ($_GET['result'] == 'error') {
                                add_action('show_notice', array($this, 'wpvivid_show_notice_add_onedrive_error'));
                            }
                        }
                    }
                    catch (Exception $e){
                        echo '<div class="notice notice-error"><p>'.esc_html($e->getMessage()).'</p></div>';
                    }
                }
            }
        }
    }
    public function wpvivid_show_notice_add_onedrive_success(){
        echo '<div class="notice notice-success is-dismissible"><p>';
        esc_html_e('You have authenticated the Microsoft OneDrive account as your remote storage.', 'wpvivid-backuprestore');
        echo '</p></div>';
    }
    public function wpvivid_show_notice_add_onedrive_error(){
        global $wpvivid_plugin;
        $wpvivid_plugin->wpvivid_handle_remote_storage_error($_GET['resp_msg'], 'Add OneDrive Remote');
        echo '<div class="notice notice-error"><p>'.esc_html($_GET['resp_msg']).'</p></div>';
    }

    public function wpvivid_add_storage_tab_one_drive()
    {
        ?>
        <div class="storage-providers" remote_type="one_drive" onclick="select_remote_storage(event, 'storage_account_one_drive');">
            <img src="<?php echo esc_url(WPVIVID_PLUGIN_URL.'/admin/partials/images/storage-microsoft-onedrive.png'); ?>" style="vertical-align:middle;"/><?php esc_html_e('Microsoft OneDrive', 'wpvivid-backuprestore'); ?>
        </div>
        <?php
    }

    public function wpvivid_add_storage_page_one_drive()
    {
        global $wpvivid_plugin;
        $root_path=apply_filters('wpvivid_get_root_path', WPVIVID_REMOTE_ONEDRIVE);
        if($this->add_remote)
        {
            ?>
            <div id="storage_account_one_drive" class="storage-account-page" style="display:none;">
                <div style="background-color:#f1f1f1; padding: 10px;">
                    Please read <a target="_blank" href="https://wpvivid.com/privacy-policy" style="text-decoration: none;">this privacy policy</a> for use of our Microsoft OneDrive authorization app (none of your backup data is sent to us).
                </div>
                <div style="color:#8bc34a; padding: 10px 10px 10px 0;">
                    <strong><?php esc_html_e('Authentication is done, please continue to enter the storage information, then click \'Add Now\' button to save it.', 'wpvivid-backuprestore'); ?></strong>
                </div>
                <div style="padding: 10px 10px 10px 0;">
                    <strong><?php esc_html_e('Enter Your Microsoft OneDrive Information', 'wpvivid-backuprestore'); ?></strong>
                </div>
                <table class="wp-list-table widefat plugins" style="width:100%;">
                    <tbody>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input type="text" class="regular-text" autocomplete="off" option="one_drive" name="name" placeholder="<?php esc_attr_e('Enter a unique alias: e.g. OneDrive-001', 'wpvivid-backuprestore'); ?>" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_]/g,'')" />
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i><?php esc_html_e('A name to help you identify the storage if you have multiple remote storage connected.', 'wpvivid-backuprestore'); ?></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input type="text" class="regular-text" autocomplete="off" option="one_drive" name="path" value="<?php echo esc_attr($root_path.WPVIVID_ONEDRIVE_DEFAULT_FOLDER); ?>" readonly="readonly" />
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i><?php esc_html_e('All backups will be uploaded to this directory.', 'wpvivid-backuprestore'); ?></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input type="text" class="regular-text" autocomplete="off" value="mywebsite01" readonly="readonly" />
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <a href="https://docs.wpvivid.com/wpvivid-backup-pro-microsoft-onedrive-custom-folder-name.html"><?php esc_html_e('Pro feature: Create a directory for storing the backups of the site', 'wpvivid-backuprestore'); ?></a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-select">
                                <label>
                                    <input type="checkbox" option="one_drive" name="default" checked /><?php esc_html_e('Set as the default remote storage.', 'wpvivid-backuprestore'); ?>
                                </label>
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i><?php esc_html_e('Once checked, all this sites backups sent to a remote storage destination will be uploaded to this storage by default.', 'wpvivid-backuprestore'); ?></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input id="wpvivid_one_drive_auth" class="button-primary" type="submit" value="<?php esc_attr_e('Add Now', 'wpvivid-backuprestore'); ?>">
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i><?php esc_html_e('Click the button to add the storage.', 'wpvivid-backuprestore'); ?></i>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <script>
                function wpvivid_check_onedrive_storage_alias(storage_alias)
                {
                    var find = 1;
                    jQuery('#wpvivid_remote_storage_list tr').each(function (i) {
                        jQuery(this).children('td').each(function (j) {
                            if (j == 3) {
                                if (jQuery(this).text() == storage_alias) {
                                    find = -1;
                                    return false;
                                }
                            }
                        });
                    });
                    return find;
                }

                jQuery('#wpvivid_one_drive_auth').click(function()
                {
                    wpvivid_one_drive_auth();
                });

                function wpvivid_one_drive_auth()
                {
                    wpvivid_settings_changed = false;
                    var name='';
                    var path='';
                    jQuery('input:text[option=one_drive]').each(function()
                    {
                        var key = jQuery(this).prop('name');
                        if(key==='name')
                        {
                            name = jQuery(this).val();
                        }
                    });

                    var remote_default='0';

                    jQuery('input:checkbox[option=one_drive]').each(function()
                    {
                        if(jQuery(this).prop('checked')) {
                            remote_default='1';
                        }
                        else {
                            remote_default='0';
                        }
                    });
                    if(name == ''){
                        alert(wpvividlion.remotealias);
                    }
                    else if(wpvivid_check_onedrive_storage_alias(name) === -1){
                        alert(wpvividlion.remoteexist);
                    }
                    else {
                        var ajax_data;
                        var remote_from = wpvivid_ajax_data_transfer('one_drive');
                        ajax_data = {
                            'action': 'wpvivid_one_drive_add_remote',
                            'remote': remote_from
                        };
                        jQuery('#wpvivid_one_drive_auth').css({'pointer-events': 'none', 'opacity': '0.4'});
                        jQuery('#wpvivid_remote_notice').html('');
                        wpvivid_post_request(ajax_data, function (data)
                        {
                            try
                            {
                                var jsonarray = jQuery.parseJSON(data);
                                if (jsonarray.result === 'success')
                                {
                                    jQuery('#wpvivid_one_drive_auth').css({'pointer-events': 'auto', 'opacity': '1'});
                                    jQuery('input:text[option=one_drive]').each(function(){
                                        jQuery(this).val('');
                                    });
                                    jQuery('input:password[option=one_drive]').each(function(){
                                        jQuery(this).val('');
                                    });
                                    wpvivid_handle_remote_storage_data(data);
                                    location.href='admin.php?page=WPvivid&action=wpvivid_one_drive&main_tab=storage&sub_tab=one_drive&sub_page=storage_account_one_drive&result=success';
                                }
                                else if (jsonarray.result === 'failed')
                                {
                                    jQuery('#wpvivid_remote_notice').html(jsonarray.notice);
                                    jQuery('input[option=add-remote]').css({'pointer-events': 'auto', 'opacity': '1'});
                                }
                            }
                            catch (err)
                            {
                                alert(err);
                                jQuery('input[option=add-remote]').css({'pointer-events': 'auto', 'opacity': '1'});
                            }

                        }, function (XMLHttpRequest, textStatus, errorThrown)
                        {
                            var error_message = wpvivid_output_ajaxerror('adding the remote storage', textStatus, errorThrown);
                            alert(error_message);
                            jQuery('#wpvivid_one_drive_auth').css({'pointer-events': 'auto', 'opacity': '1'});
                        });
                    }
                }
            </script>
            <?php
        }
        else
        {
            ?>
            <div id="storage_account_one_drive" class="storage-account-page" style="display:none;">
                <div style="background-color:#f1f1f1; padding: 10px;">
                    Please read <a target="_blank" href="https://wpvivid.com/privacy-policy" style="text-decoration: none;">this privacy policy</a> for use of our Microsoft OneDrive authorization app (none of your backup data is sent to us).
                </div>
                <div style="padding: 10px 10px 10px 0;">
                    <strong><?php esc_html_e('To add OneDrive, please get Microsoft authentication first. Once authenticated, you will be redirected to this page, then you can add storage information and save it', 'wpvivid-backuprestore'); ?></strong>
                </div>
                <table class="wp-list-table widefat plugins" style="width:100%;">
                    <tbody>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input onclick="wpvivid_one_drive_auth();" class="button-primary" type="submit" value="<?php esc_attr_e('Authenticate with Microsoft OneDrive', 'wpvivid-backuprestore'); ?>">
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i><?php esc_html_e('Click to get Microsoft authentication.', 'wpvivid-backuprestore'); ?></i>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div style="padding: 10px 0 0 0;">
                    <span>Tip: Get a 404 or 403 error after authorization? Please read this <a href="https://docs.wpvivid.com/http-403-error-authorizing-cloud-storage.html">doc</a>.</span>
                </div>
            </div>
            <script>
                function wpvivid_one_drive_auth()
                {
                    location.href = '<?php echo esc_url(admin_url()) . 'admin.php?page=WPvivid' . '&action=wpvivid_one_drive_auth'?>';
                }
            </script>
            <?php
        }
    }

    public function wpvivid_edit_storage_page_one_drive()
    {
        ?>
        <div id="remote_storage_edit_onedrive" class="postbox storage-account-block remote-storage-edit" style="display:none;">
            <div style="padding: 0 10px 10px 0;">
                <strong><?php esc_html_e('Enter Your Microsoft OneDrive Information', 'wpvivid-backuprestore'); ?></strong>
            </div>
            <table class="wp-list-table widefat plugins" style="width:100%;">
                <tbody>
                <tr>
                    <td class="plugin-title column-primary">
                        <div class="wpvivid-storage-form">
                            <input type="text" class="regular-text" autocomplete="off" option="edit-onedrive" name="name" placeholder="<?php esc_attr_e('Enter a unique alias: e.g. OneDrive-001', 'wpvivid-backuprestore'); ?>" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_]/g,'')" />
                        </div>
                    </td>
                    <td class="column-description desc">
                        <div class="wpvivid-storage-form-desc">
                            <i><?php esc_html_e('A name to help you identify the storage if you have multiple remote storage connected.', 'wpvivid-backuprestore'); ?></i>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="plugin-title column-primary">
                        <div class="wpvivid-storage-form">
                            <input class="button-primary" type="submit" option="edit-remote" value="<?php esc_attr_e('Save Changes', 'wpvivid-backuprestore'); ?>">
                        </div>
                    </td>
                    <td class="column-description desc">
                        <div class="wpvivid-storage-form-desc">
                            <i><?php esc_html_e('Click the button to save the changes.', 'wpvivid-backuprestore'); ?></i>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <script>
            function wpvivid_one_drive_update_auth()
            {
                var name='';
                jQuery('input:text[option=edit-onedrive]').each(function()
                {
                    var key = jQuery(this).prop('name');
                    if(key==='name')
                    {
                        name = jQuery(this).val();
                    }
                });
                if(name == ''){
                    alert(wpvividlion.remotealias);
                }
                else if(wpvivid_check_onedrive_storage_alias(name) === -1){
                    alert(wpvividlion.remoteexist);
                }
                else {
                    location.href = '<?php echo esc_url(admin_url()) . 'admin.php?page=WPvivid' . '&action=wpvivid_one_drive_update_auth&name='?>' + name + '&id=' + wpvivid_editing_storage_id;
                }
            }
        </script>
        <?php
    }

    public function sanitize_options($skip_name='')
    {
        $ret['result']=WPVIVID_SUCCESS;

        if(!isset($this->options['name']))
        {
            $ret['error']="Warning: An alias for remote storage is required.";
            return $ret;
        }

        $ret['options']=$this->options;
        return $ret;
    }

    public function test_connect()
    {
        return array('result' => WPVIVID_SUCCESS);
    }

    public function upload($task_id, $files, $callback = '')
    {
        global $wpvivid_plugin;

        if($this->need_refresh())
        {
            $wpvivid_plugin->wpvivid_log->WriteLog('The token expired and will go to the server to refresh the token.','notice');
            $ret=$this->refresh_token();
            if($ret['result']===WPVIVID_FAILED)
            {
                return $ret;
            }
        }

        $path=$this->options['path'];
        $wpvivid_plugin->wpvivid_log->WriteLog('Check upload folder '.$path,'notice');
        $ret=$this->check_folder($path);

        if($ret['result']===WPVIVID_FAILED)
        {
            return $ret;
        }

        $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_ONEDRIVE);
        if(empty($upload_job))
        {
            $job_data=array();
            foreach ($files as $file)
            {
                $file_data['size']=filesize($file);
                $file_data['uploaded']=0;
                $file_data['uploadUrl']='';
                $job_data[basename($file)]=$file_data;
            }
            WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_ONEDRIVE,WPVIVID_UPLOAD_UNDO,'Start uploading',$job_data);
            $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_ONEDRIVE);
        }

        foreach ($files as $file)
        {
            if(is_array($upload_job['job_data'])&&array_key_exists(basename($file),$upload_job['job_data']))
            {
                if($upload_job['job_data'][basename($file)]['uploaded']==1)
                    continue;
            }

            $this -> last_time = time();
            $this -> last_size = 0;

            if(!file_exists($file))
                return array('result' =>WPVIVID_FAILED,'error' =>$file.' not found. The file might has been moved, renamed or deleted. Please reload the list and verify the file exists.');
            $wpvivid_plugin->wpvivid_log->WriteLog('Start uploading '.basename($file),'notice');
            $wpvivid_plugin->set_time_limit($task_id);
            $result=$this->_upload($task_id, $file,$callback);
            if($result['result'] !==WPVIVID_SUCCESS)
            {
                return $result;
            }
            else
            {
                WPvivid_taskmanager::wpvivid_reset_backup_retry_times($task_id);
            }
            if($this->need_refresh())
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('The token expired and will go to the server to refresh the token.','notice');
                $ret=$this->refresh_token();
                if($ret['result']===WPVIVID_FAILED)
                {
                    return $ret;
                }
            }
        }
        return array('result' =>WPVIVID_SUCCESS);
    }

    public function cleanup($files)
    {
        @set_time_limit(120);

        $path=$this->options['path'];
        if($this->need_refresh())
        {
            $ret=$this->refresh_token();
            if($ret['result']===WPVIVID_FAILED)
            {
                return $ret;
            }
        }

        $ret=$this->get_files_id($files,$path);
        if($ret['result']==WPVIVID_SUCCESS)
        {
            $ids=$ret['ids'];
            foreach ($ids as $id)
            {
                $this->delete_file($id);
            }
        }
        else
        {
            return $ret;
        }

        return array('result' =>WPVIVID_SUCCESS);
    }

    public function set_token()
    {
        $remote_options=WPvivid_Setting::get_remote_option($this->options['id']);
        if($remote_options!==false)
        {
            $this->options['token']=$remote_options['token'];
            if(isset($remote_options['is_encrypt']))
            {
                $this->options['is_encrypt']=$remote_options['is_encrypt'];
            }
        }
    }

    public function download($file, $local_path, $callback = '')
    {
        try {
            $this->current_file_name = $file['file_name'];
            $this->current_file_size = $file['size'];
            $this->callback=$callback;
            global $wpvivid_plugin;
            $wpvivid_plugin->wpvivid_download_log->WriteLog('Remote type: OneDrive.','notice');
            $this->set_token();
            if ($this->need_refresh()) {
                $ret = $this->refresh_token();
                if ($ret['result'] === WPVIVID_FAILED) {
                    return $ret;
                }
            }

            $path = $this->options['path'];
            $ret = $this->check_file($file['file_name'], $path);

            if ($ret['result'] === WPVIVID_FAILED) {
                return $ret;
            }

            $file_path = $local_path . $file['file_name'];
            $wpvivid_plugin->wpvivid_download_log->WriteLog('Create local file.','notice');
            $fh = fopen($file_path, 'a');
            $wpvivid_plugin->wpvivid_download_log->WriteLog('Downloading file ' . $file['file_name'] . ', Size: ' . $file['size'] ,'notice');
            $downloaded_start = filesize($file_path);
            $url = 'https://graph.microsoft.com/v1.0/me/drive/root:/' . $this->options['path'] . '/' . $file['file_name'] . ':/content';
            $download_size = WPVIVID_ONEDRIVE_DOWNLOAD_SIZE;
            $size = $file['size'];
            while ($downloaded_start < $size) {
                $ret = $this->download_loop($url, $downloaded_start, $download_size, $size);
                if ($ret['result'] != WPVIVID_SUCCESS) {
                    return $ret;
                }

                fwrite($fh, $ret['body']);
            }

            fclose($fh);
            return array('result' => WPVIVID_SUCCESS);
        }
        catch (Exception $error){
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            return array('result'=>WPVIVID_FAILED, 'error'=>$message);
        }
    }

    private function download_loop($url,&$downloaded_start,$download_size,$file_size,$retry_count=0)
    {
        global $wpvivid_plugin;

        $downloaded_end=min($downloaded_start+$download_size-1,$file_size-1);
        $headers['Range']="bytes=$downloaded_start-$downloaded_end";
        $response=$this->remote_get($url,$headers,false,30);
        if ((time() - $this->last_time) > 3) {
            if (is_callable($this->callback)) {
                call_user_func_array($this->callback, array($downloaded_start, $this->current_file_name,
                    $this->current_file_size, $this->last_time, $this->last_size));
            }
            $this->last_size = $downloaded_start;
            $this->last_time = time();
        }
        if($response['result']==WPVIVID_SUCCESS)
        {
            $downloaded_start=$downloaded_end+1;
            $ret['result']=WPVIVID_SUCCESS;
            $ret['body']=$response['body'];
            return $ret;
        }
        else
        {
            if($retry_count<WPVIVID_ONEDRIVE_RETRY_TIMES)
            {
                $retry_count++;
                return $this->download_loop($url,$downloaded_start,$download_size,$file_size,$retry_count);
            }
            else
            {
                return $response;
            }
        }
    }

    private function need_refresh()
    {
        if(time()-120> $this->options['token']['expires'])
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private function refresh_token()
    {
        if(isset($this->options['is_encrypt']) && $this->options['is_encrypt'] == 1) {
            $refresh_token=base64_decode($this->options['token']['refresh_token']);
        }
        else{
            $refresh_token=$this->options['token']['refresh_token'];
        }

        $args['method']='POST';
        $args['wpvivid_refresh_token']=1;
        $args['timeout']=15;
        $args['sslverify']=FALSE;
        $args['body']=array( 'wpvivid_refresh_token' => '1', 'refresh_token' => $refresh_token);
        $response=wp_remote_post('https://auth.wpvivid.com/onedrive_v2/',$args);
        if(!is_wp_error($response) && ($response['response']['code'] == 200))
        {
            $json =stripslashes($response['body']);
            $json_ret =json_decode($json,true);
            if($json_ret['result']=='success')
            {
                $remote_options=WPvivid_Setting::get_remote_option($this->options['id']);
                $json_ret['token']['access_token']=base64_encode($json_ret['token']['access_token']);
                $json_ret['token']['refresh_token']=base64_encode($json_ret['token']['refresh_token']);
                $this->options['token']=$json_ret['token'];
                $this->options['is_encrypt']=1;
                $this->options['token']['expires']=time()+ $json_ret['token']['expires_in'];
                if($remote_options!==false)
                {
                    $remote_options['is_encrypt']=1;
                    $remote_options['token']=$json_ret['token'];
                    $remote_options['token']['expires']=time()+ $json_ret['token']['expires_in'];
                    WPvivid_Setting::update_remote_option($this->options['id'],$remote_options);
                }
                $ret['result']=WPVIVID_SUCCESS;
                return $ret;
            }
            else{
                $ret['result']=WPVIVID_FAILED;
                $ret['error']=$json_ret['error'];
                return $ret;
            }
        }
        else
        {
            $ret['result']=WPVIVID_FAILED;
            if ( is_wp_error( $response ) )
            {
                $ret['error']= $response->get_error_message();
            }
            else
            {
                $ret['error']=$response['response']['message'];
            }
            return $ret;
        }
    }

    private function check_folder($folder)
    {
        $url='https://graph.microsoft.com/v1.0/me/drive/root:/'.$folder.'?$select=id,name,folder';
        $response=$this->remote_get($url);
        if($response['result']==WPVIVID_SUCCESS)
        {
            $ret['result']=WPVIVID_SUCCESS;
            return $ret;
        }
        else
        {
            if(isset($response['code'])&&$response['code'] ==404)
            {
                $body=array( 'name' => $folder, 'folder' => array("childCount" => '0'));
                $body=wp_json_encode($body);
                $url='https://graph.microsoft.com/v1.0/me/drive/root/children';

                $response=$this->remote_post($url,array(),$body);
                if($response['result']==WPVIVID_SUCCESS)
                {
                    $ret['result']=WPVIVID_SUCCESS;
                    return $ret;
                }
                else
                {
                    return $response;
                }
            }
            else
            {
                return $response;
            }
        }
    }

    private function check_file($file,$folder)
    {
        $url='https://graph.microsoft.com/v1.0/me/drive/root:/'.$folder.'/'.$file.'?$select=id,name,size';

        $response=$this->remote_get($url);
        if($response['result']==WPVIVID_SUCCESS)
        {
            $ret['result']=WPVIVID_SUCCESS;
            return $ret;
        }
        else
        {
            return $response;
        }
    }

    private function _upload($task_id,$local_file,$callback)
    {
        global $wpvivid_plugin;

        $this -> current_file_size = filesize($local_file);
        $this -> current_file_name = basename($local_file);

        $wpvivid_plugin->wpvivid_log->WriteLog('Check if the server already has the same name file.','notice');

        $this->delete_file_by_name($this->options['path'],basename($local_file));

        $file_size=filesize($local_file);

        //small file
        if($file_size<1024*1024*4)
        {
            $wpvivid_plugin->wpvivid_log->WriteLog('Uploaded files are less than 4M.','notice');
            $ret=$this->upload_small_file($local_file,$task_id);
            return $ret;
        }
        else
        {
            $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_ONEDRIVE);
            if(empty( $upload_job['job_data'][basename($local_file)]['uploadUrl']))
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('Creating upload session.','notice');
                //big file
                $ret=$this->create_upload_session(basename($local_file));

                if($ret['result']===WPVIVID_FAILED)
                {
                    return $ret;
                }

                $upload_job['job_data'][basename($local_file)]['uploadUrl']=$ret['session_url'];
                $session_url=$ret['session_url'];

                WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_ONEDRIVE,WPVIVID_UPLOAD_UNDO,'Created upload session',$upload_job['job_data']);
            }
            else
            {
                $session_url=$upload_job['job_data'][basename($local_file)]['uploadUrl'];
            }

            $wpvivid_plugin->wpvivid_log->WriteLog('Ready to start uploading files.','notice');
            $ret=$this->upload_resume($session_url,$local_file,$task_id,$callback);

            return $ret;
        }
    }

    private function upload_small_file($file,$task_id)
    {
        global $wpvivid_plugin;
        $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_ONEDRIVE);

        if(isset($this->options['is_encrypt']) && $this->options['is_encrypt'] == 1) {
            $access_token=base64_decode($this->options['token']['access_token']);
        }
        else{
            $access_token=$this->options['token']['access_token'];
        }

        $path=$this->options['path'].'/'.basename($file);
        $args['method']='PUT';
        $args['headers']=array( 'Authorization' => 'bearer '.$access_token,'content-type' => 'application/zip');
        $args['timeout']=15;

        $data=file_get_contents($file);
        $args['body']=$data;

        WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_ONEDRIVE,WPVIVID_UPLOAD_UNDO,'Start uploading '.basename($file).'.',$upload_job['job_data']);

        $response=wp_remote_post('https://graph.microsoft.com/v1.0/me/drive/root:/'.$path.':/content',$args);

        if(!is_wp_error($response) && ($response['response']['code'] == 200||$response['response']['code'] == 201))
        {
            $upload_job['job_data'][basename($file)]['uploaded']=1;
            $wpvivid_plugin->wpvivid_log->WriteLog('Finished uploading '.basename($file),'notice');
            WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_ONEDRIVE,WPVIVID_UPLOAD_SUCCESS,'Uploading '.basename($file).' completed.',$upload_job['job_data']);
            return array('result' =>WPVIVID_SUCCESS);
        }
        else
        {
            $ret['result']=WPVIVID_FAILED;
            if ( is_wp_error( $response ) )
            {
                $ret['error']= $response->get_error_message();
            }
            else
            {
                $error=json_decode($response['body'],1);
                $ret['error']=$error['error']['message'];
            }
            return $ret;
        }
    }

    private function upload_resume($session_url,$file,$task_id,$callback)
    {
        global $wpvivid_plugin;
        $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_ONEDRIVE);

        $ret=$this->get_upload_offset($session_url);

        if($ret['result']=='failed')
        {
            return $ret;
        }

        $offset=$ret['offset'];
        $wpvivid_plugin->wpvivid_log->WriteLog('offset '.$offset,'notice');

        WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_ONEDRIVE,WPVIVID_UPLOAD_UNDO,'Start uploading '.basename($file).'.',$upload_job['job_data']);

        $file_size=filesize($file);
        $handle=fopen($file,'rb');
        $upload_size=WPVIVID_ONEDRIVE_UPLOAD_SIZE;
        $upload_end=min($offset+$upload_size-1,$file_size-1);
        while(true)
        {
            $ret=$this->upload_loop($session_url,$handle,$offset,$upload_end,$upload_size,$file_size,$task_id,$callback);

            if($ret['result']==WPVIVID_SUCCESS)
            {
                if((time() - $this -> last_time) >3)
                {
                    if(is_callable($callback))
                    {
                        call_user_func_array($callback,array($offset,$this -> current_file_name,
                            $this->current_file_size,$this -> last_time,$this -> last_size));
                    }
                    $this -> last_size = $offset;
                    $this -> last_time = time();
                }

                if($ret['op']=='continue')
                {
                    continue;
                }
                else
                {
                    break;
                }
            }
            else
            {
                return $ret;
            }
        }

        fclose($handle);
        $upload_job['job_data'][basename($file)]['uploaded']=1;
        $wpvivid_plugin->wpvivid_log->WriteLog('Finished uploading '.basename($file),'notice');
        WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_ONEDRIVE,WPVIVID_UPLOAD_SUCCESS,'Uploading '.basename($file).' completed.',$upload_job['job_data']);
        return array('result' =>WPVIVID_SUCCESS);
    }

    private function get_upload_offset($uploadUrl)
    {
        global $wpvivid_plugin;
        $wpvivid_plugin->wpvivid_log->WriteLog('uploadUrl: '.$uploadUrl,'notice');

        $url=$uploadUrl;
        $response=$this->remote_get($url);
        if($response['result']==WPVIVID_SUCCESS)
        {
            if($response['code']==200)
            {
                $ranges=$response['body']['nextExpectedRanges'];

                if (is_array($ranges))
                {
                    $range = $ranges[0];
                } else {
                    $range=$ranges;
                }

                if (preg_match('/^(\d+)/', $range, $matches))
                {
                    $uploaded = $matches[1];
                    $ret['result']='success';
                    $ret['offset']=$uploaded;
                    return $ret;
                }
                else
                {
                    $ret['result']='failed';
                    $ret['error']='get offset failed';
                    return $ret;
                }
            }
            else
            {
                $ret['result']='failed';
                $ret['error']='get offset failed';
                return $ret;
            }
        }
        else
        {
            return $response;
        }
    }

    private function create_upload_session($file)
    {
        $path=$this->options['path'].'/'.basename($file);
        $url='https://graph.microsoft.com/v1.0/me/drive/root:/'.$path.':/createUploadSession';
        $response=$this->remote_post($url);

        if($response['result']==WPVIVID_SUCCESS)
        {
            $upload_session=$response['body']['uploadUrl'];

            $ret['result']=WPVIVID_SUCCESS;
            $ret['session_url']=$upload_session;
            return $ret;
        }
        else
        {
            return $response;
        }
    }

    private function upload_loop($url,$file_handle,&$uploaded,&$upload_end,$upload_size,$file_size,$task_id,$callback,$retry_count=0)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $upload_size=min($upload_size,$file_size-$uploaded);

        if ($uploaded)
            fseek($file_handle, $uploaded);

        if(isset($this->options['is_encrypt']) && $this->options['is_encrypt'] == 1) {
            $access_token=base64_decode($this->options['token']['access_token']);
        }
        else{
            $access_token=$this->options['token']['access_token'];
        }

        $headers = array(
            "Content-Length: $upload_size",
            "Content-Range: bytes $uploaded-$upload_end/".$file_size,
        );
        $headers[] = 'Authorization: Bearer ' . $access_token;

        $options = array(
            CURLOPT_URL        => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_PUT        => true,
            CURLOPT_INFILE     => $file_handle,
            CURLOPT_INFILESIZE => $upload_size,
            CURLOPT_RETURNTRANSFER=>true,
        );

        curl_setopt_array($curl, $options);

        global $wpvivid_plugin;

        $response=curl_exec($curl);

        $http_code = curl_getinfo($curl,CURLINFO_HTTP_CODE);

        if($response!=false)
        {
            curl_close($curl);
            if($http_code==202)
            {
                $json=json_decode($response,1);
                $ranges=$json['nextExpectedRanges'];

                if (is_array($ranges))
                {
                    $range = $ranges[0];
                } else {
                    $range=$ranges;
                }

                if (preg_match('/^(\d+)/', $range, $matches))
                {
                    $uploaded = $matches[1];
                    $upload_end=min($uploaded+$upload_size-1,$file_size-1);
                }

                $ret['result']=WPVIVID_SUCCESS;
                $ret['op']='continue';
                return $ret;
            }
            else if($http_code==200||$http_code==201)
            {
                $ret['result']=WPVIVID_SUCCESS;
                $ret['op']='finished';
                return $ret;
            }
            else
            {
                if($retry_count<WPVIVID_ONEDRIVE_RETRY_TIMES)
                {
                    $error=json_decode($response,1);
                    $wpvivid_plugin->wpvivid_log->WriteLog('http code is not 200, start retry. http code :'.$http_code.', error: '.wp_json_encode($error),'notice');
                    $ret=$this->get_upload_offset($url);

                    if($ret['result']=='failed')
                    {
                        return $ret;
                    }

                    $uploaded=$ret['offset'];
                    $upload_end=min($uploaded+$upload_size-1,$file_size-1);
                    $wpvivid_plugin->wpvivid_log->WriteLog('offset '.$uploaded,'notice');
                    $retry_count++;
                    return $this->upload_loop($url,$file_handle,$uploaded,$upload_end,$upload_size,$file_size,$task_id,$callback,$retry_count);
                }
                else
                {
                    $ret['result']=WPVIVID_FAILED;
                    $error=json_decode($response,1);
                    $ret['error']=$error['error']['message'];
                    return $ret;
                }
            }
        }
        else
        {
            if($retry_count<WPVIVID_ONEDRIVE_RETRY_TIMES)
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('http no response, start retry. http code :'.$http_code,'notice');
                $ret=$this->get_upload_offset($url);

                if($ret['result']=='failed')
                {
                    return $ret;
                }

                $uploaded=$ret['offset'];
                $upload_end=min($uploaded+$upload_size-1,$file_size-1);
                $wpvivid_plugin->wpvivid_log->WriteLog('offset '.$uploaded,'notice');
                if($http_code === 202)
                {
                    WPvivid_taskmanager::wpvivid_reset_backup_retry_times($task_id);
                    $retry_count=0;
                    if(is_callable($callback))
                    {
                        call_user_func_array($callback,array($uploaded,$this -> current_file_name,
                            $this->current_file_size,$this -> last_time,$this -> last_size));
                    }
                    $this -> last_size = $uploaded;
                    $this -> last_time = time();
                }
                else
                {
                    $retry_count++;
                }
                return $this->upload_loop($url,$file_handle,$uploaded,$upload_end,$upload_size,$file_size,$task_id,$callback,$retry_count);
            }
            else
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('retry times: '.$retry_count.', http code :'.$http_code,'notice');
                $ret['result']=WPVIVID_FAILED;
                $ret['error']=curl_error($curl);
                curl_close($curl);
                return $ret;
            }
        }
    }

    private function get_files_id($files,$path)
    {
        $ret['ids']=array();
        foreach ($files as $file)
        {
            $url='https://graph.microsoft.com/v1.0/me/drive/root:/'.$path.'/'.$file.'?$select=id';
            $response=$this->remote_get($url);
            if($response['result']==WPVIVID_SUCCESS)
            {
                if($response['code']==200)
                {
                    $ret['ids'][]=$response['body']['id'];
                }
            }
            else
            {
                continue;
            }
        }

        if(sizeof($ret['ids'])==0)
        {
            $ret['result']=WPVIVID_FAILED;
            $ret['error']='file not found';
        }
        else
        {
            $ret['result']=WPVIVID_SUCCESS;
        }

        return $ret;
    }

    private function delete_file($id)
    {
        if(isset($this->options['is_encrypt']) && $this->options['is_encrypt'] == 1) {
            $access_token=base64_decode($this->options['token']['access_token']);
        }
        else{
            $access_token=$this->options['token']['access_token'];
        }

        $args['method']='DELETE';
        $args['headers']=array( 'Authorization' => 'bearer '.$access_token);
        $args['timeout']=15;

        $response = wp_remote_request( 'https://graph.microsoft.com/v1.0/me/drive/items/'.$id,$args);

        if(!is_wp_error($response) && ($response['response']['code'] == 204))
        {
            $ret['result']=WPVIVID_SUCCESS;
            return $ret;
        }
        else
        {
            $ret['result']=WPVIVID_FAILED;
            if ( is_wp_error( $response ) )
            {
                $ret['error']= $response->get_error_message();
            }
            else
            {
                $ret['error']= $response['body'];
            }
            return $ret;
        }
    }

    private function remote_get($url,$header=array(),$decode=true,$timeout=15,$except_code=array())
    {
        if(isset($this->options['is_encrypt']) && $this->options['is_encrypt'] == 1) {
            $access_token=base64_decode($this->options['token']['access_token']);
        }
        else{
            $access_token=$this->options['token']['access_token'];
        }

        if(empty($except_code))
        {
            $except_code=array(200,201,202,204,206);
        }
        $args['timeout']=$timeout;
        $args['headers']['Authorization']= 'bearer '.$access_token;
        $args['headers']= $args['headers']+$header;
        $response=wp_remote_get($url,$args);

        if(!is_wp_error($response))
        {
            $ret['code']=$response['response']['code'];
            if(in_array($response['response']['code'],$except_code))
            {
                $ret['result']=WPVIVID_SUCCESS;
                if($decode)
                    $ret['body']=json_decode($response['body'],1);
                else
                    $ret['body']=$response['body'];
            }
            else
            {
                $ret['result']=WPVIVID_FAILED;
                $error=json_decode($response['body'],1);
                $ret['error']=$error['error']['message'].' http code:'.$response['response']['code'];
            }
            return $ret;
        }
        else
        {
            $ret['result']=WPVIVID_FAILED;
            $ret['error']=$response->get_error_message();
            return $ret;
        }
    }

    private function remote_post($url,$header=array(),$body=null,$except_code=array())
    {
        if(isset($this->options['is_encrypt']) && $this->options['is_encrypt'] == 1) {
            $access_token=base64_decode($this->options['token']['access_token']);
        }
        else{
            $access_token=$this->options['token']['access_token'];
        }

        if(empty($except_code))
        {
            $except_code=array(200,201,202,204,206);
        }

        $args['method']='POST';
        $args['headers']=array( 'Authorization' => 'bearer '.$access_token,'content-type' => 'application/json');
        $args['headers']=$args['headers']+$header;
        if(!is_null($body))
        {
            $args['body']=$body;
        }
            $args['timeout']=15;

        $response=wp_remote_post($url,$args);

        if(!is_wp_error($response))
        {
            $ret['code']=$response['response']['code'];
            if(in_array($response['response']['code'],$except_code))
            {
                $ret['result']=WPVIVID_SUCCESS;
                $ret['body']=json_decode($response['body'],1);
            }
            else
            {
                $ret['result']=WPVIVID_FAILED;
                $error=json_decode($response['body'],1);
                $ret['error']=$error['error']['message'];
            }
            return $ret;
        }
        else
        {
            $ret['result']=WPVIVID_FAILED;
            $ret['error']=$response->get_error_message();
            return $ret;
        }
    }

    private function delete_file_by_name($folder,$file_name)
    {
        $files[]=$file_name;
        $ret=$this->get_files_id($files,$folder);

        if($ret['result']==WPVIVID_SUCCESS)
        {
            $ids=$ret['ids'];
            foreach ($ids as $id)
            {
                $ret=$this->delete_file($id);
                if($ret['result']==WPVIVID_FAILED)
                {
                    return $ret;
                }
            }
        }
        else
        {
            return $ret;
        }

        return array('result' =>WPVIVID_SUCCESS);
    }

    public function wpvivid_remote_pic_one_drive($remote)
    {
        $remote['onedrive']['default_pic'] = '/admin/partials/images/storage-microsoft-onedrive(gray).png';
        $remote['onedrive']['selected_pic'] = '/admin/partials/images/storage-microsoft-onedrive.png';
        $remote['onedrive']['title'] = 'Microsoft OneDrive';
        return $remote;
    }

    public function wpvivid_get_out_of_date_one_drive($out_of_date_remote, $remote)
    {
        if($remote['type'] == WPVIVID_REMOTE_ONEDRIVE){
            $root_path=apply_filters('wpvivid_get_root_path', $remote['type']);
            $out_of_date_remote = $root_path.$remote['path'];
        }
        return $out_of_date_remote;
    }

    public function wpvivid_storage_provider_one_drive($storage_type)
    {
        if($storage_type == WPVIVID_REMOTE_ONEDRIVE){
            $storage_type = 'Microsoft OneDrive';
        }
        return $storage_type;
    }
    public function wpvivid_get_root_path_one_drive($storage_type){
        if($storage_type == WPVIVID_REMOTE_ONEDRIVE){
            $storage_type = 'root/';
        }
        return $storage_type;
    }

    public function finish_add_remote()
    {
        global $wpvivid_plugin;
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (empty($_POST) || !isset($_POST['remote']) || !is_string($_POST['remote'])) {
                die();
            }

            $tmp_remote_options =get_option('wpvivid_tmp_remote_options',array());
            delete_option('wpvivid_tmp_remote_options');
            if(empty($tmp_remote_options)||$tmp_remote_options['type']!==WPVIVID_REMOTE_ONEDRIVE)
            {
                die();
            }

            $json = sanitize_text_field($_POST['remote']);
            $json = stripslashes($json);
            $remote_options = json_decode($json, true);
            if (is_null($remote_options)) {
                die();
            }

            $remote_options['path'] = WPVIVID_ONEDRIVE_DEFAULT_FOLDER;
            $remote_options=array_merge($remote_options,$tmp_remote_options);
            if(!class_exists('WPvivid_Remote_collection'))
            {
                include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-remote-collection.php';
                $wpvivid_plugin->remote_collection=new WPvivid_Remote_collection();
            }
            $ret = $wpvivid_plugin->remote_collection->add_remote($remote_options);

            if ($ret['result'] == 'success') {
                $html = '';
                $html = apply_filters('wpvivid_add_remote_storage_list', $html);
                $ret['html'] = $html;
                $pic = '';
                $pic = apply_filters('wpvivid_schedule_add_remote_pic', $pic);
                $ret['pic'] = $pic;
                $dir = '';
                $dir = apply_filters('wpvivid_get_remote_directory', $dir);
                $ret['dir'] = $dir;
                $schedule_local_remote = '';
                $schedule_local_remote = apply_filters('wpvivid_schedule_local_remote', $schedule_local_remote);
                $ret['local_remote'] = $schedule_local_remote;
                $remote_storage = '';
                $remote_storage = apply_filters('wpvivid_remote_storage', $remote_storage);
                $ret['remote_storage'] = $remote_storage;
                $remote_select_part = '';
                $remote_select_part = apply_filters('wpvivid_remote_storage_select_part', $remote_select_part);
                $ret['remote_select_part'] = $remote_select_part;
                $default = array();
                $remote_array = apply_filters('wpvivid_archieve_remote_array', $default);
                $ret['remote_array'] = $remote_array;
                $success_msg = __('You have successfully added a remote storage.', 'wpvivid-backuprestore');
                $ret['notice'] = apply_filters('wpvivid_add_remote_notice', true, $success_msg);
            }
            else{
                $ret['notice'] = apply_filters('wpvivid_add_remote_notice', false, $ret['error']);
            }

        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        echo wp_json_encode($ret);
        die();
    }
}