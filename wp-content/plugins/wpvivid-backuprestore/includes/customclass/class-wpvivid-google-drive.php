<?php

if (!defined('WPVIVID_PLUGIN_DIR')){
    die;
}

require_once WPVIVID_PLUGIN_DIR . '/includes/customclass/class-wpvivid-remote.php';

if(!defined('WPVIVID_REMOTE_GOOGLEDRIVE'))
    define('WPVIVID_REMOTE_GOOGLEDRIVE','googledrive');
if(!defined('WPVIVID_GOOGLEDRIVE_DEFAULT_FOLDER'))
    define('WPVIVID_GOOGLEDRIVE_DEFAULT_FOLDER','wpvivid_backup');
if(!defined('WPVIVID_GOOGLEDRIVE_UPLOAD_SIZE'))
    define('WPVIVID_GOOGLEDRIVE_UPLOAD_SIZE',1024*1024*2);
if(!defined('WPVIVID_GOOGLE_NEED_PHP_VERSION'))
    define('WPVIVID_GOOGLE_NEED_PHP_VERSION','5.5');
class Wpvivid_Google_drive extends WPvivid_Remote
{
    public $options;

    public $google_drive_secrets;

    public $add_remote;

    public function __construct($options=array())
    {
        if(empty($options))
        {
            if(!defined('WPVIVID_INIT_STORAGE_TAB_GOOGLE_DRIVE'))
            {
                add_action('init', array($this, 'handle_auth_actions'));
                //wpvivid_google_drive_add_remote
                add_action('wp_ajax_wpvivid_google_drive_add_remote',array( $this,'finish_add_remote'));

                add_action('wpvivid_add_storage_tab',array($this,'wpvivid_add_storage_tab_google_drive'), 10);
                add_action('wpvivid_add_storage_page',array($this,'wpvivid_add_storage_page_google_drive'), 10);
                add_filter('wpvivid_pre_add_remote',array($this, 'pre_add_remote'),10,2);
                add_action('wpvivid_edit_remote_page',array($this,'wpvivid_edit_storage_page_google_drive'), 10);
                add_filter('wpvivid_remote_pic',array($this,'wpvivid_remote_pic_google_drive'),10);
                add_filter('wpvivid_get_out_of_date_remote',array($this,'wpvivid_get_out_of_date_google_drive'),10,2);
                add_filter('wpvivid_storage_provider_tran',array($this,'wpvivid_storage_provider_google_drive'),10);
                add_filter('wpvivid_get_root_path',array($this,'wpvivid_get_root_path_google_drive'),10);
                define('WPVIVID_INIT_STORAGE_TAB_GOOGLE_DRIVE',1);
            }

        }
        else
        {
            $this->options=$options;
        }
        $this->add_remote=false;
        $this->google_drive_secrets = array("web"=>array(
            "client_id"=>"134809148507-32crusepgace4h6g47ota99jjrvf4j1u.apps.googleusercontent.com",
            "project_id"=>"wpvivid-auth",
            "auth_uri"=>"https://accounts.google.com/o/oauth2/auth",
            "token_uri"=>"https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url"=>"https://www.googleapis.com/oauth2/v1/certs",
            "client_secret"=>"",
            "redirect_uris"=>array("https://auth.wpvivid.com/google_drive_v2/")
        ));
    }

    public function pre_add_remote($remote,$id)
    {
        if($remote['type']==WPVIVID_REMOTE_GOOGLEDRIVE)
        {
            $remote['id']=$id;
        }

        return $remote;
    }

    public function handle_auth_actions()
    {
        if(isset($_GET['action']) && isset($_GET['page']))
        {
            if($_GET['page'] === 'WPvivid')
            {
                if($_GET['action']=='wpvivid_google_drive_auth')
                {
                    $auth_id = uniqid('wpvivid-auth-');
                    $res = $this -> compare_php_version();
                    if($res['result'] == WPVIVID_FAILED){
                        echo '<div class="notice notice-warning is-dismissible"><p>'.esc_html($res['error']).'</p></div>';
                        return ;
                    }
                    try {
                        include_once WPVIVID_PLUGIN_DIR . '/vendor/autoload.php';
                        $client = new WPvivid_Google_Client();
                        $client->setAuthConfig($this->google_drive_secrets);
                        $client->setApprovalPrompt('force');
                        $client->addScope(WPvivid_Google_Service_Drive::DRIVE_FILE);
                        $client->setAccessType('offline');
                        $client->setState(admin_url() . 'admin.php?page=WPvivid' . '&action=wpvivid_google_drive_finish_auth&main_tab=storage&sub_tab=googledrive&sub_page=storage_account_google_drive&auth_id='.$auth_id);
                        $auth_url = $client->createAuthUrl();
                        $remote_options['auth_id']=$auth_id;
                        update_option('wpvivid_tmp_remote_options',$remote_options);
                        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
                    }
                    catch (Exception $e){
                        if($e->getMessage() === 'file does not exist'){
                            $error_msg = __('Authentication failed, the client_secrets.json file is missing. Please make sure the client_secrets.json file is in wpvivid-backuprestore\includes\customclass directory.', 'wpvivid-backuprestore');
                            echo '<div class="notice notice-error"><p>'.esc_html($error_msg).'</p></div>';
                        }
                        else if($e->getMessage() === 'invalid json for auth config'){
                            $error_msg = __('Authentication failed, the format of the client_secrets.json file is incorrect. Please delete and re-install the plugin to recreate the file.', 'wpvivid-backuprestore');
                            echo '<div class="notice notice-error"><p>'.esc_html($error_msg).'</p></div>';
                        }
                        else{
                            echo '<div class="notice notice-error"><p>'.esc_html($e->getMessage()).'</p></div>';
                        }
                    }
                }
                else if($_GET['action']=='wpvivid_google_drive_finish_auth')
                {
                    try
                    {
                        if(isset($_GET['error']))
                        {
                            header('Location: '.admin_url().'admin.php?page='.WPVIVID_PLUGIN_SLUG.'&action=wpvivid_google_drive&main_tab=storage&sub_tab=googledrive&sub_page=storage_account_google_drive&result=error&resp_msg='.sanitize_text_field($_GET['error']));
                            return;
                        }

                        $remoteslist = WPvivid_Setting::get_all_remote_options();
                        foreach ($remoteslist as $key => $value)
                        {
                            if (isset($value['auth_id']) && isset($_GET['auth_id']) && $value['auth_id'] == sanitize_text_field($_GET['auth_id']))
                            {
                                echo '<div class="notice notice-success is-dismissible"><p>';
                                esc_html_e('You have authenticated the Google Drive account as your remote storage.', 'wpvivid-backuprestore');
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
                                        $err = 'No refresh token was received from Google, which means that you entered client secret incorrectly, or that you did not re-authenticated yet after you corrected it. Please authenticate again.';
                                        header('Location: '.admin_url().'admin.php?page='.WPVIVID_PLUGIN_SLUG.'&action=wpvivid_google_drive&main_tab=storage&sub_tab=googledrive&sub_page=storage_account_google_drive&result=error&resp_msg='.$err);

                                        return;
                                    }
                                }
                                else
                                {
                                    $tmp_options['type'] = WPVIVID_REMOTE_GOOGLEDRIVE;
                                    $tmp_options['token']['access_token'] = base64_encode(sanitize_text_field($_POST['access_token']));
                                    $tmp_options['token']['expires_in'] = sanitize_text_field($_POST['expires_in']);
                                    $tmp_options['token']['refresh_token'] = base64_encode(sanitize_text_field($_POST['refresh_token']));
                                    $tmp_options['token']['scope'] = sanitize_text_field($_POST['scope']);
                                    $tmp_options['token']['token_type'] = sanitize_text_field($_POST['token_type']);
                                    $tmp_options['token']['created'] = sanitize_text_field($_POST['created']);
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
                else if($_GET['action']=='wpvivid_google_drive')
                {
                    try {
                        if (isset($_GET['result'])) {
                            if ($_GET['result'] == 'success') {
                                add_action('show_notice', array($this, 'wpvivid_show_notice_add_google_drive_success'));
                            } else if ($_GET['result'] == 'error') {
                                add_action('show_notice', array($this, 'wpvivid_show_notice_add_google_drive_error'));
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

    public function wpvivid_show_notice_add_google_drive_success(){
        echo '<div class="notice notice-success is-dismissible"><p>';
        esc_html_e('You have authenticated the Google Drive account as your remote storage.', 'wpvivid-backuprestore');
        echo '</p></div>';
    }
    public function wpvivid_show_notice_add_google_drive_error(){
        global $wpvivid_plugin;
        $wpvivid_plugin->wpvivid_handle_remote_storage_error($_GET['resp_msg'], 'Add Google Drive Remote');
        echo '<div class="notice notice-error"><p>'.esc_html($_GET['resp_msg']).'</p></div>';
    }

    public function wpvivid_add_storage_tab_google_drive()
    {
        ?>
        <div class="storage-providers storage-providers-active" remote_type="googledrive" onclick="select_remote_storage(event, 'storage_account_google_drive');">
            <img src="<?php echo esc_url(WPVIVID_PLUGIN_URL.'/admin/partials/images/stroage-google-drive.png'); ?>" style="vertical-align:middle;"/><?php esc_html_e('Google Drive', 'wpvivid-backuprestore'); ?>
        </div>
        <?php
    }

    public function wpvivid_add_storage_page_google_drive()
    {
        global $wpvivid_plugin;
        $root_path=apply_filters('wpvivid_get_root_path', WPVIVID_REMOTE_GOOGLEDRIVE);
        if($this->add_remote)
        {
            ?>
            <div id="storage_account_google_drive" class="storage-account-page">
                <div style="background-color:#f1f1f1; padding: 10px;">
                    Please read <a target="_blank" href="https://wpvivid.com/privacy-policy" style="text-decoration: none;">this privacy policy</a> for use of our Google Drive authorization app (none of your backup data is sent to us).
                </div>
                <div style="color:#8bc34a; padding: 10px 10px 10px 0;">
                    <strong>Authentication is done, please continue to enter the storage information, then click 'Add Now' button to save it.</strong>
                </div>
                <div style="padding: 10px 10px 10px 0;">
                    <strong><?php esc_html_e('Enter Your Google Drive Information', 'wpvivid-backuprestore'); ?></strong>
                </div>
                <table class="wp-list-table widefat plugins" style="width:100%;">
                    <tbody>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input type="text" class="regular-text" autocomplete="off" option="googledrive" name="name" placeholder="<?php esc_attr_e('Enter a unique alias: e.g. Google Drive-001', 'wpvivid-backuprestore'); ?>" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_]/g,'')" />
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
                                <input type="text" class="regular-text" autocomplete="off" name="path" value="<?php echo esc_attr($root_path.WPVIVID_GOOGLEDRIVE_DEFAULT_FOLDER); ?>" readonly="readonly" />
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
                                <a href="https://docs.wpvivid.com/wpvivid-backup-pro-google-drive-custom-folder-name.html"><?php esc_html_e('Pro feature: Create a directory for storing the backups of the site', 'wpvivid-backuprestore'); ?></a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-select">
                                <label>
                                    <input type="checkbox" option="googledrive" name="default" checked /><?php esc_html_e('Set as the default remote storage.', 'wpvivid-backuprestore'); ?>
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
                                <input id="wpvivid_google_drive_auth" class="button-primary" type="submit" value="<?php esc_attr_e('Add Now', 'wpvivid-backuprestore'); ?>" />
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
                function wpvivid_check_google_drive_storage_alias(storage_alias){
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
                jQuery('#wpvivid_google_drive_auth').click(function()
                {
                    wpvivid_google_drive_auth();
                });

                function wpvivid_google_drive_auth()
                {
                    wpvivid_settings_changed = false;
                    var name='';
                    var path='';
                    jQuery('input:text[option=googledrive]').each(function()
                    {
                        var key = jQuery(this).prop('name');
                        if(key==='name')
                        {
                            name = jQuery(this).val();
                        }
                    });

                    var remote_default='0';

                    jQuery('input:checkbox[option=googledrive]').each(function()
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
                    else if(wpvivid_check_google_drive_storage_alias(name) === -1)
                    {
                        alert(wpvividlion.remoteexist);
                    }
                    else
                    {
                        var ajax_data;
                        var remote_from = wpvivid_ajax_data_transfer('googledrive');
                        ajax_data = {
                            'action': 'wpvivid_google_drive_add_remote',
                            'remote': remote_from
                        };
                        jQuery('#wpvivid_google_drive_auth').css({'pointer-events': 'none', 'opacity': '0.4'});
                        jQuery('#wpvivid_remote_notice').html('');
                        wpvivid_post_request(ajax_data, function (data)
                        {
                            try
                            {
                                var jsonarray = jQuery.parseJSON(data);
                                if (jsonarray.result === 'success')
                                {
                                    jQuery('#wpvivid_google_drive_auth').css({'pointer-events': 'auto', 'opacity': '1'});
                                    jQuery('input:text[option=googledrive]').each(function(){
                                        jQuery(this).val('');
                                    });
                                    jQuery('input:password[option=googledrive]').each(function(){
                                        jQuery(this).val('');
                                    });
                                    wpvivid_handle_remote_storage_data(data);
                                    location.href='admin.php?page=WPvivid&action=wpvivid_google_drive&main_tab=storage&sub_tab=googledrive&sub_page=storage_account_google_drive&result=success';
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
                            jQuery('#wpvivid_google_drive_auth').css({'pointer-events': 'auto', 'opacity': '1'});
                        });
                    }
                }
            </script>
            <?php
        }
        else
        {
            ?>
            <div id="storage_account_google_drive" class="storage-account-page">
                <div style="background-color:#f1f1f1; padding: 10px;">
                    Please read <a target="_blank" href="https://wpvivid.com/privacy-policy" style="text-decoration: none;">this privacy policy</a> for use of our Google Drive authorization app (none of your backup data is sent to us).
                </div>
                <div style="padding: 10px 10px 10px 0;">
                    <strong><?php esc_html_e('To add Google Drive, please get Google authentication first. Once authenticated, you will be redirected to this page, then you can add storage information and save it', 'wpvivid-backuprestore'); ?></strong>
                </div>
                <table class="wp-list-table widefat plugins" style="width:100%;">
                    <tbody>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input onclick="wpvivid_google_drive_auth();" class="button-primary" type="submit" value="<?php esc_attr_e('Authenticate with Google Drive', 'wpvivid-backuprestore'); ?>" />
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i><?php esc_html_e('Click to get Google authentication.', 'wpvivid-backuprestore'); ?></i>
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
                function wpvivid_google_drive_auth()
                {
                    location.href = '<?php echo esc_url(admin_url()) . 'admin.php?page=WPvivid' . '&action=wpvivid_google_drive_auth'?>';
                }
            </script>
            <?php
        }

    }

    public function wpvivid_edit_storage_page_google_drive()
    {
        ?>
        <div id="remote_storage_edit_googledrive" class="postbox storage-account-block remote-storage-edit" style="display:none;">
            <div style="padding: 0 10px 10px 0;">
                <strong><?php esc_html_e('Enter Your Google Drive Information', 'wpvivid-backuprestore'); ?></strong>
            </div>
            <table class="wp-list-table widefat plugins" style="width:100%;">
                <tbody>
                <tr>
                    <td class="plugin-title column-primary">
                        <div class="wpvivid-storage-form">
                            <input type="text" class="regular-text" autocomplete="off" option="edit-googledrive" name="name" placeholder="<?php esc_attr_e('Enter a unique alias: e.g. Google Drive-001', 'wpvivid-backuprestore'); ?>" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_]/g,'')" />
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
                            <input class="button-primary" type="submit" option="edit-remote" value="<?php esc_attr_e('Save Changes', 'wpvivid-backuprestore'); ?>" />
                        </div>
                    </td>
                    <td class="column-description desc">
                        <div class="wpvivid-storage-form-desc">
                            <i><?php esc_html_e('Click the button to save the changes.', 'wpvivid-backuprestore');?></i>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <script>
            function wpvivid_google_drive_update_auth()
            {
                var name='';
                jQuery('input:text[option=edit-googledrive]').each(function()
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
                    location.href = '<?php echo esc_url(admin_url()) . 'admin.php?page=WPvivid' . '&action=wpvivid_google_drive_update_auth&name='?>' + name + '&id=' + wpvivid_editing_storage_id;
                }
            }
        </script>
        <?php
    }

    public function wpvivid_remote_pic_google_drive($remote)
    {
        $remote['googledrive']['default_pic'] = '/admin/partials/images/stroage-google-drive(gray).png';
        $remote['googledrive']['selected_pic'] = '/admin/partials/images/stroage-google-drive.png';
        $remote['googledrive']['title'] = 'Google Drive';
        return $remote;
    }

    public function sanitize_options($skip_name='')
    {
        $ret['result']=WPVIVID_SUCCESS;

        if(!isset($this->options['name']))
        {
            $ret['error']="Warning: An alias for remote storage is required.";
            return $ret;
        }

        $this->options['name']=sanitize_text_field($this->options['name']);

        if(empty($this->options['name']))
        {
            $ret['error']="Warning: An alias for remote storage is required.";
            return $ret;
        }

        $remoteslist=WPvivid_Setting::get_all_remote_options();
        foreach ($remoteslist as $key=>$value)
        {
            if(isset($value['name'])&&$value['name'] == $this->options['name']&&$skip_name!=$value['name'])
            {
                $ret['error']="Warning: The alias already exists in storage list.";
                return $ret;
            }
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


        $client=$this->get_client();
        if($client['result'] == WPVIVID_FAILED){
            return $client;
        }
        $client = $client['data'];

        if($client===false)
        {
            return array('result' => WPVIVID_FAILED,'error'=> 'Token refresh failed.');
        }

        $service = new WPvivid_Google_Service_Drive($client);
        $path=$this->options['path'];
        $wpvivid_plugin->wpvivid_log->WriteLog('Check upload folder '.$path,'notice');
        $folder_id=$this->get_folder($service,$path);

        if($folder_id==false)
        {
            return array('result' => WPVIVID_FAILED,'error'=> 'Unable to create the local file. Please make sure the folder is writable and try again.');
        }

        $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_GOOGLEDRIVE);
        if(empty($upload_job))
        {
            $job_data=array();
            foreach ($files as $file)
            {
                $file_data['size']=filesize($file);
                $file_data['uploaded']=0;
                $file_data['resumeUri']=false;
                $file_data['progress']=false;
                $job_data[basename($file)]=$file_data;
            }
            WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_GOOGLEDRIVE,WPVIVID_UPLOAD_UNDO,'Start uploading',$job_data);
            $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_GOOGLEDRIVE);
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
            $result=$this->_upload($task_id, $file,$client,$service,$folder_id, $callback);
            if($result['result'] !==WPVIVID_SUCCESS){
                return $result;
            }
            else
            {
                WPvivid_taskmanager::wpvivid_reset_backup_retry_times($task_id);
            }
            $ref=$this->check_token($client, $service);
            if($ref['result']=!WPVIVID_SUCCESS)
            {
                return $ref;
            }
        }
        return array('result' =>WPVIVID_SUCCESS);
    }

    public function check_token(&$client, &$service)
    {
        if ($client->isAccessTokenExpired())
        {
            // Refresh the token if possible, else fetch a new one.
            global $wpvivid_plugin;
            $wpvivid_plugin->wpvivid_log->WriteLog('Refresh the token.','notice');
            if ($client->getRefreshToken())
            {
                $tmp_refresh_token = $client->getRefreshToken();
                /*
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $token=$client->getAccessToken();
                */

                if(isset($this->options['is_encrypt']) && $this->options['is_encrypt'] == 1) {
                    $tmp_refresh_token = base64_decode($tmp_refresh_token);
                }

                $args = array(
                    'refresh_token' => $tmp_refresh_token
                );

                $result = wp_remote_post("https://auth.wpvivid.com/google_drive_v2/", array(
                    'timeout' => 60,
                    'body' => $args
                ));

                if (is_wp_error($result))
                {
                    return array('result' => WPVIVID_PRO_SUCCESS,'data' => false);
                }
                else
                {
                    $token = wp_remote_retrieve_body($result);
                    $token = json_decode($token, true);
                    if(!is_null($token))
                    {
                        $client->setAccessToken($token);
                    }
                    else
                    {
                        return array('result' => WPVIVID_PRO_SUCCESS,'data' => false);
                    }
                }

                $remote_options=WPvivid_Setting::get_remote_option($this->options['id']);
                $this->options['token']=json_decode(wp_json_encode($token),1);
                $this->options['token']['access_token']=base64_encode($this->options['token']['access_token']);
                $this->options['is_encrypt']=1;
                if($remote_options!==false)
                {
                    if(!isset($this->options['token']['refresh_token'])){
                        $this->options['token']['refresh_token'] = base64_encode($tmp_refresh_token);
                    }
                    else{
                        $this->options['token']['refresh_token']=base64_encode($this->options['token']['refresh_token']);
                    }
                    $remote_options['token']=$this->options['token'];
                    $remote_options['is_encrypt']=1;
                    WPvivid_Setting::update_remote_option($this->options['id'],$remote_options);

                    $client=$this->get_client();
                    if($client['result'] == WPVIVID_FAILED){
                        return $client;
                    }
                    $client = $client['data'];

                    if($client===false)
                    {
                        return array('result' => WPVIVID_FAILED,'error'=> 'Token refresh failed.');
                    }
                    $service = new WPvivid_Google_Service_Drive($client);
                }
                return array('result' => WPVIVID_SUCCESS);
            }
            else
            {
                return array('result' => WPVIVID_FAILED,'error'=>'get refresh token failed');
            }
        }
        else
        {
            return array('result' => WPVIVID_SUCCESS);
        }
    }

    public function _upload($task_id, $file,$client,$service,$folder_id, $callback = '', $retry_times=0)
    {
        global $wpvivid_plugin;
        $wpvivid_plugin->wpvivid_log->WriteLog('Check if the server already has the same name file.','notice');
        try{
            if(!$this->delete_exist_file($folder_id,basename($file),$service))
            {
                return array('result' =>WPVIVID_FAILED,'error'=>'Uploading '.$file.' to Google Drive server failed. '.$file.' might be deleted or network doesn\'t work properly . Please verify the file and confirm the network connection and try again later.');
            }

            $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_GOOGLEDRIVE);
            $this -> current_file_size = filesize($file);
            $this -> current_file_name = basename($file);


            $fileMetadata = new WPvivid_Google_Service_Drive_DriveFile(array(
                'name' => basename($file),
                'parents' => array($folder_id)));
            $chunk_size = 1 * 1024 * 1024;
            $client->setDefer(true);
            $request = $service->files->create($fileMetadata);
            $media = new WPvivid_Google_Http_MediaFileUpload(
                $client,
                $request,
                'text/plain',
                null,
                true,
                $chunk_size
            );
            $media->setFileSize(filesize($file));

            $status = false;
            $handle = fopen($file, "rb");

            if(!empty($upload_job['job_data'][basename($file)]['resumeUri']))
            {
                $media->resume( $upload_job['job_data'][basename($file)]['resumeUri'] );

                $media->setResumeUri($upload_job['job_data'][basename($file)]['resumeUri'] );
                $media->setProgress($upload_job['job_data'][basename($file)]['progress'] );

                $wpvivid_plugin->wpvivid_log->WriteLog('Resume uploading '.basename($file).'.','notice');
                $wpvivid_plugin->wpvivid_log->WriteLog('resumeUri:'.$media->getResumeUri().'.','notice');
                $wpvivid_plugin->wpvivid_log->WriteLog('progress:'.$media->getProgress().'.','notice');

                $offset = $upload_job['job_data'][basename($file)]['progress'];
                fseek($handle, $offset);
                WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_GOOGLEDRIVE,WPVIVID_UPLOAD_UNDO,'Resume uploading '.basename($file).'.',$upload_job['job_data']);
            }
            else
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('Initiate a resumable upload session.','notice');
                $offset=0;
                WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_GOOGLEDRIVE,WPVIVID_UPLOAD_UNDO,'Start uploading '.basename($file).'.',$upload_job['job_data']);
            }


            while (!$status && !feof($handle))
            {
                $chunk = fread($handle, $chunk_size);

                $status = $media->nextChunk($chunk);

                $offset+=strlen($chunk);
                $retry_times=0;

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

                $upload_job['job_data'][basename($file)]['resumeUri']=$media->getResumeUri();
                $upload_job['job_data'][basename($file)]['progress']=$media->getProgress();

                //$wpvivid_plugin->wpvivid_log->WriteLog('resumeUri:'.$media->getResumeUri().'.','notice');
                $wpvivid_plugin->wpvivid_log->WriteLog('progress:'.$media->getProgress().'.','notice');
                WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_GOOGLEDRIVE,WPVIVID_UPLOAD_SUCCESS,'Uploading '.basename($file),$upload_job['job_data']);
            }

            fclose($handle);
            $client->setDefer(false);
            if ($status != false)
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('Finished uploading '.basename($file),'notice');
                $upload_job['job_data'][basename($file)]['uploaded']=1;
                WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_GOOGLEDRIVE,WPVIVID_UPLOAD_SUCCESS,'Uploading '.basename($file).' completed.',$upload_job['job_data']);
                $wpvivid_plugin->wpvivid_log->WriteLog('Upload success.','notice');
                return array('result' =>WPVIVID_SUCCESS);
            }
            else
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('Upload failed.','notice');
                return array('result' =>WPVIVID_FAILED,'error'=>'Uploading '.$file.' to Google Drive server failed. '.$file.' might be deleted or network doesn\'t work properly. Please verify the file and confirm the network connection and try again later.');
            }
        }
        catch (WPvivid_Google_Service_Exception $e)
        {
            $retry_times++;
            fclose($handle);
            $client->setDefer(false);
            $message = 'A exception ('.get_class($e).') occurred '.esc_html($e->getMessage()).' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().') ';
            if($retry_times < 15)
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('Upload Google_Service_Exception, '.$message.', retry times: '.$retry_times,'notice');
                return $this->_upload($task_id, $file,$client,$service,$folder_id, $callback, $retry_times);
            }
            else
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('Upload Google_Service_Exception, retry times: '.$retry_times,'notice');
                return array('result' =>WPVIVID_PRO_FAILED,'error'=>$message);
            }
        }
    }

    public function get_client()
    {
        $res = $this -> compare_php_version();
        if($res['result'] == WPVIVID_FAILED){
            return $res;
        }

        $token=$this->options['token'];
        if(isset($this->options['is_encrypt']) && $this->options['is_encrypt'] == 1) {
            $token['access_token'] = base64_decode($this->options['token']['access_token']);
        }

        include_once WPVIVID_PLUGIN_DIR.'/vendor/autoload.php';
        $client = new WPvivid_Google_Client();
        $client->setConfig('access_type','offline');
        $client->setAuthConfig($this->google_drive_secrets);
        $client->addScope(WPvivid_Google_Service_Drive::DRIVE_FILE);//
        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired())
        {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken())
            {
                $tmp_refresh_token = $client->getRefreshToken();
                /*
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $token=$client->getAccessToken();
                */

                if(isset($this->options['is_encrypt']) && $this->options['is_encrypt'] == 1) {
                    $tmp_refresh_token = base64_decode($tmp_refresh_token);
                }

                $args = array(
                    'refresh_token' => $tmp_refresh_token
                );

                $result = wp_remote_post("https://auth.wpvivid.com/google_drive_v2/", array(
                    'timeout' => 60,
                    'body' => $args
                ));

                if (is_wp_error($result))
                {
                    return array('result' => WPVIVID_PRO_SUCCESS,'data' => false);
                }
                else
                {
                    $token = wp_remote_retrieve_body($result);
                    $token = json_decode($token, true);
                    if(!is_null($token))
                    {
                        $client->setAccessToken($token);
                    }
                    else
                    {
                        return array('result' => WPVIVID_PRO_SUCCESS,'data' => false);
                    }
                }

                $this->options['token']=json_decode(wp_json_encode($token),1);
                $this->options['token']['access_token']=base64_encode($this->options['token']['access_token']);
                $this->options['is_encrypt']=1;
                if(!isset($this->options['token']['refresh_token'])){
                    $this->options['token']['refresh_token'] = base64_encode($tmp_refresh_token);
                }
                else{
                    $this->options['token']['refresh_token']=base64_encode($this->options['token']['refresh_token']);
                }
                WPvivid_Setting::update_remote_option($this->options['id'],$this->options);
                return array('result' => WPVIVID_SUCCESS,'data' => $client);
            }
            else
            {
                return array('result' => WPVIVID_SUCCESS,'data' => false);
            }
        }
        else
        {
            return array('result' => WPVIVID_SUCCESS,'data' => $client);
        }
    }

    private function get_folder($service,$path)
    {
        $response = $service->files->listFiles(array(
            'q' => "name ='".$path."' and 'root' in parents and mimeType = 'application/vnd.google-apps.folder'",
            'fields' => 'nextPageToken, files(id, name,mimeType)',
        ));
        if(sizeof($response->getFiles())==0)
        {
            $fileMetadata = new WPvivid_Google_Service_Drive_DriveFile(array(
                'name' => $path,
                'mimeType' => 'application/vnd.google-apps.folder'));
            $file = $service->files->create($fileMetadata, array(
                'fields' => 'id'));

            return $file->id;
        }
        else
        {
            foreach ($response->getFiles() as $file)
            {
                return $file->getId();
            }
        }

        return false;
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

    public function download( $file, $local_path, $callback = '')
    {
        try
        {
            global $wpvivid_plugin;
            $this -> current_file_name = $file['file_name'];
            $this -> current_file_size = $file['size'];
            $this->set_token();
            $wpvivid_plugin->wpvivid_download_log->WriteLog('Google Drive get client.','notice');
            $client=$this->get_client();
            if($client['result'] == WPVIVID_FAILED) {
                return $client;
            }
            $client = $client['data'];

            if($client===false)
            {
                return array('result' => WPVIVID_FAILED,'error'=> 'Token refresh failed.');
            }

            $service = new WPvivid_Google_Service_Drive($client);

            $path=$this->options['path'];
            $wpvivid_plugin->wpvivid_download_log->WriteLog('Create local file.','notice');
            $folder_id=$this->get_folder($service,$path);

            if($folder_id==false)
            {
                return array('result' => WPVIVID_FAILED,'error'=> 'Unable to create the local file. Please make sure the folder is writable and try again.');
            }

            $response = $service->files->listFiles(array(
                'q' => "name='".$file['file_name']."' and '".$folder_id."' in parents",
                'fields' => 'files(id,size,webContentLink)'
            ));

            if(sizeof($response->getFiles())==0)
            {
                return array('result' => WPVIVID_FAILED,'error'=> 'Downloading file failed. The file might be deleted or network doesn\'t work properly. Please verify the file and confirm the network connection and try again later.');
            }
            else
            {
                $fileSize=$file['size'];
                $file_id='';
                foreach ($response->getFiles() as $file)
                {
                    $file_id=$file->getId();
                    break;
                }
                $wpvivid_plugin->wpvivid_download_log->WriteLog('Get download url.','notice');
                $download_url=$this->get_download_url($client,$file_id);

                if(!empty($file_id)||!empty($download_url))
                {
                    $file_path = trailingslashit($local_path).$this -> current_file_name;

                    if(file_exists($file_path))
                    {
                        $offset = filesize($file_path);
                    }
                    else
                    {
                        $offset=0;
                    }

                    $fh = fopen($file_path, 'a');
                    $upload_size = WPVIVID_GOOGLEDRIVE_UPLOAD_SIZE;
                    $http = $client->authorize();
                    $wpvivid_plugin->wpvivid_download_log->WriteLog('Downloading file ' . $file['file_name'] . ', Size: ' . $file['size'] ,'notice');
                    while ($offset < $fileSize)
                    {
                        $upload_end=min($offset+$upload_size-1,$fileSize-1);

                        if ($offset > 0)
                        {
                            $options['headers']['Range']='bytes='.$offset.'-'.$upload_end;
                        } else {
                            $options['headers']['Range']='bytes=0-'.$upload_end;
                        }
                        $request = new WPvividGuzzleHttp\Psr7\Request('GET', $download_url,$options['headers']);
                        $http_request = $http->send($request);
                        $http_response=$http_request->getStatusCode();
                        if (200 == $http_response || 206 == $http_response)
                        {
                            fwrite($fh, $http_request->getBody()->getContents(),$upload_size);
                            $offset=$upload_end + 1;
                        }
                        else
                        {
                            throw new Exception('Failed to obtain any new data at size: '.$offset.' http code:'.$http_response);
                        }

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
                    }
                    fclose($fh);
                }
                else
                {
                    return array('result' => WPVIVID_FAILED,'error'=> 'Downloading file failed. The file might be deleted or network doesn\'t work properly. Please verify the file and confirm the network connection and try again later.');
                }
            }
        }catch(Exception $e)
        {
            return array('result' => WPVIVID_FAILED,'error' => $e -> getMessage());
        }
        return array('result' => WPVIVID_SUCCESS);
    }

    public function get_download_url($client,$file_id)
    {
        $http = $client->authorize();
        $url='https://www.googleapis.com/drive/v2/files/'.$file_id;
        $request = new WPvividGuzzleHttp\Psr7\Request('GET', $url);
        $http_request = $http->send($request);

        $http_response=$http_request->getStatusCode();
        if (200 == $http_response)
        {
            $json=$http_request->getBody()->getContents();
            $json=json_decode($json,1);
            $download_url=$json['downloadUrl'];
            return $download_url;
        }
        else
        {
            throw new Exception('Failed to use v2 api');
        }
    }

    public function delete_exist_file($folder_id,$file,$service)
    {
        $client=$this->get_client();
        if($client['result'] == WPVIVID_FAILED)
            return false;
        $client = $client['data'];

        if($client===false)
        {
            return false;
        }

        $delete_files = $service->files->listFiles(array(
            'q' => "name='".$file."' and '".$folder_id."' in parents",
            'fields' => 'nextPageToken, files(id, name,mimeType)',
        ));

        if(sizeof($delete_files->getFiles())==0)
        {
            return true;
        }
        else
        {
            foreach ($delete_files->getFiles() as $file_google_drive)
            {
                $file_id=$file_google_drive->getId();
                $service->files->delete($file_id);
                return true;
            }
        }

        return false;
    }

    public function cleanup($files)
    {
        $client=$this->get_client();
        if($client['result'] == WPVIVID_FAILED)
            return $client;
        $client = $client['data'];

        if($client===false)
        {
            return array('result' => WPVIVID_FAILED,'error'=> 'Token refresh failed.');
        }

        $service = new WPvivid_Google_Service_Drive($client);

        $path=$this->options['path'];
        $folder_id=$this->get_folder($service,$path);

        if($folder_id==false)
        {
            return array('result' => WPVIVID_FAILED,'error'=> 'Unable to create the local file. Please make sure the folder is writable and try again.');
        }

        foreach ($files as $file)
        {
            $delete_files = $service->files->listFiles(array(
                'q' => "name='".$file."' and '".$folder_id."' in parents",
                'fields' => 'nextPageToken, files(id, name,mimeType)',
            ));

            if(sizeof($delete_files->getFiles())==0)
            {
                continue;
            }
            else
            {
                foreach ($delete_files->getFiles() as $file_google_drive)
                {
                    $file_id=$file_google_drive->getId();
                    $service->files->delete($file_id);
                }
            }
        }
        return array('result' =>WPVIVID_SUCCESS);
    }

    public function wpvivid_get_out_of_date_google_drive($out_of_date_remote, $remote)
    {
        if($remote['type'] == WPVIVID_REMOTE_GOOGLEDRIVE){
            $root_path=apply_filters('wpvivid_get_root_path', $remote['type']);
            $out_of_date_remote = $root_path.$remote['path'];
        }
        return $out_of_date_remote;
    }

    public function wpvivid_storage_provider_google_drive($storage_type)
    {
        if($storage_type == WPVIVID_REMOTE_GOOGLEDRIVE){
            $storage_type = 'Google Drive';
        }
        return $storage_type;
    }

    public function wpvivid_get_root_path_google_drive($storage_type){
        if($storage_type == WPVIVID_REMOTE_GOOGLEDRIVE){
            $storage_type = 'root/';
        }
        return $storage_type;
    }
    private function compare_php_version(){
        if(version_compare(WPVIVID_GOOGLE_NEED_PHP_VERSION,phpversion()) > 0){
            return array('result' => WPVIVID_FAILED,error => 'The required PHP version is higher than '.WPVIVID_GOOGLE_NEED_PHP_VERSION.'. After updating your PHP version, please try again.');
        }
        return array('result' => WPVIVID_SUCCESS);
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
            if(empty($tmp_remote_options)||$tmp_remote_options['type']!==WPVIVID_REMOTE_GOOGLEDRIVE)
            {
                die();
            }

            $json = sanitize_text_field($_POST['remote']);
            $json = stripslashes($json);
            $remote_options = json_decode($json, true);
            if (is_null($remote_options)) {
                die();
            }

            $remote_options['path'] = WPVIVID_GOOGLEDRIVE_DEFAULT_FOLDER;
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