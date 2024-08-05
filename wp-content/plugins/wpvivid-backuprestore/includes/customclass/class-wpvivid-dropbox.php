<?php

if (!defined('WPVIVID_PLUGIN_DIR')){
    die;
}
if(!defined('WPVIVID_REMOTE_DROPBOX')){
    define('WPVIVID_REMOTE_DROPBOX','dropbox');
}
if(!defined('WPVIVID_DROPBOX_DEFAULT_FOLDER'))
    define('WPVIVID_DROPBOX_DEFAULT_FOLDER','/');
require_once WPVIVID_PLUGIN_DIR . '/includes/customclass/class-wpvivid-base-dropbox.php';
require_once WPVIVID_PLUGIN_DIR . '/includes/customclass/class-wpvivid-remote.php';
class WPvivid_Dropbox extends WPvivid_Remote
{

    private $options;
    private $upload_chunk_size = 2097152;
    private $download_chunk_size = 2097152;
    private $redirect_url = 'https://auth.wpvivid.com/dropbox_v3/';
    public $add_remote;
    public function __construct($options = array())
    {
        if(empty($options))
        {
            if(!defined('WPVIVID_INIT_STORAGE_TAB_DROPBOX'))
            {
                add_action('init', array($this, 'handle_auth_actions'));
                //wpvivid_dropbox_add_remote
                add_action('wp_ajax_wpvivid_dropbox_add_remote',array( $this,'finish_add_remote'));

                add_action('wpvivid_delete_remote_token',array($this,'revoke'));

                add_filter('wpvivid_remote_register', array($this, 'init_remotes'),10);
                add_action('wpvivid_add_storage_tab',array($this,'wpvivid_add_storage_tab_dropbox'), 11);
                add_action('wpvivid_add_storage_page',array($this,'wpvivid_add_storage_page_dropbox'), 11);
                add_action('wpvivid_edit_remote_page',array($this,'wpvivid_edit_storage_page_dropbox'), 11);
                add_filter('wpvivid_remote_pic',array($this,'wpvivid_remote_pic_dropbox'),10);
                add_filter('wpvivid_get_out_of_date_remote',array($this,'wpvivid_get_out_of_date_dropbox'),10,2);
                add_filter('wpvivid_storage_provider_tran',array($this,'wpvivid_storage_provider_dropbox'),10);
                add_filter('wpvivid_get_root_path',array($this,'wpvivid_get_root_path_dropbox'),10);
                add_filter('wpvivid_pre_add_remote',array($this, 'pre_add_remote'),10,2);
                define('WPVIVID_INIT_STORAGE_TAB_DROPBOX',1);
            }
        }else{
            $this -> options = $options;
        }
        $this->add_remote=false;
    }

    public function pre_add_remote($remote,$id)
    {
        if($remote['type']==WPVIVID_REMOTE_DROPBOX)
        {
            $remote['id']=$id;
        }

        return $remote;
    }

    public function test_connect()
    {
        return array('result' => WPVIVID_SUCCESS);
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

    public function upload($task_id, $files, $callback = '')
    {
        global $wpvivid_plugin;

        $options = $this -> options;
        $dropbox = new Dropbox_Base($options);
        $ret=$dropbox->check_token();
        if($ret['result']=='failed')
        {
            return $ret;
        }
        $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_DROPBOX);
        if(empty($upload_job))
        {
            $job_data=array();
            foreach ($files as $file)
            {
                $file_data['size']=filesize($file);
                $file_data['uploaded']=0;
                $file_data['session_id']='';
                $file_data['offset']=0;
                $job_data[basename($file)]=$file_data;
            }
            WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_DROPBOX,WPVIVID_UPLOAD_UNDO,'Start uploading',$job_data);
            $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_DROPBOX);
        }

        foreach ($files as $file)
        {
            if(is_array($upload_job['job_data']) &&array_key_exists(basename($file),$upload_job['job_data']))
            {
                if($upload_job['job_data'][basename($file)]['uploaded']==1)
                    continue;
            }
            $ret=$dropbox->check_token();
            if($ret['result']=='failed')
            {
                return $ret;
            }

            $this -> last_time = time();
            $this -> last_size = 0;
            $wpvivid_plugin->wpvivid_log->WriteLog('Start uploading '.basename($file),'notice');
            $wpvivid_plugin->set_time_limit($task_id);
            if(!file_exists($file))
                return array('result' =>WPVIVID_FAILED,'error' =>$file.' not found. The file might has been moved, renamed or deleted. Please reload the list and verify the file exists.');
            $result = $this -> _put($task_id,$dropbox,$file,$callback);
            if($result['result'] !==WPVIVID_SUCCESS){
                $wpvivid_plugin->wpvivid_log->WriteLog('Uploading '.basename($file).' failed.','notice');
                return $result;
            }
            else
            {
                WPvivid_taskmanager::wpvivid_reset_backup_retry_times($task_id);
            }
            $wpvivid_plugin->wpvivid_log->WriteLog('Finished uploading '.basename($file),'notice');
            $upload_job['job_data'][basename($file)]['uploaded'] = 1;
            WPvivid_taskmanager::update_backup_sub_task_progress($task_id, 'upload', WPVIVID_REMOTE_DROPBOX, WPVIVID_UPLOAD_SUCCESS, 'Uploading ' . basename($file) . ' completed.', $upload_job['job_data']);
        }
        return array('result' =>WPVIVID_SUCCESS);
    }
    private function _put($task_id,$dropbox,$file,$callback){
        global $wpvivid_plugin;
        $options = $this -> options;
        $path = trailingslashit($options['path']).basename($file);
        $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_DROPBOX);
        $this -> current_file_size = filesize($file);
        $this -> current_file_name = basename($file);

        if($this -> current_file_size > $this -> upload_chunk_size)
        {

            if(empty($upload_job['job_data'][basename($file)]['session_id']))
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('Creating upload session.','notice');
                //WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_DROPBOX,WPVIVID_UPLOAD_UNDO,'Start uploading '.basename($file).'.',$upload_job['job_data']);
                $result = $dropbox -> upload_session_start();
                if(isset($result['error_summary']))
                {
                    return array('result'=>WPVIVID_FAILED,'error'=>$result['error_summary']);
                }

                $upload_job['job_data'][basename($file)]['session_id']= $result['session_id'];
                WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_DROPBOX,WPVIVID_UPLOAD_UNDO,'Start uploading '.basename($file).'.',$upload_job['job_data']);

                $build_id = $result['session_id'];
            }
            else
            {
                $build_id = $upload_job['job_data'][basename($file)]['session_id'];
            }

            $result = $this -> large_file_upload($task_id,$build_id,$file,$dropbox,$callback);
        }else{
            $wpvivid_plugin->wpvivid_log->WriteLog('Uploaded files are less than 2M.','notice');
            $result = $dropbox -> upload($path,$file);
            if(isset($result['error_summary'])){
                WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_DROPBOX,WPVIVID_UPLOAD_FAILED,'Uploading '.basename($file).' failed.',$upload_job['job_data']);
                $result = array('result' => WPVIVID_FAILED,'error' => $result['error_summary']);
            }else{
                WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_DROPBOX,WPVIVID_UPLOAD_SUCCESS,'Uploading '.basename($file).' completed.',$upload_job['job_data']);
                $result = array('result'=> WPVIVID_SUCCESS);
            }
        }
        return $result;
    }

    public function large_file_upload($task_id,$session_id,$file,$dropbox,$callback)
    {
        global $wpvivid_plugin;
        $fh = fopen($file,'rb');

        $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_DROPBOX);

        $offset = $upload_job['job_data'][basename($file)]['offset'];
        $wpvivid_plugin->wpvivid_log->WriteLog('offset:'.size_format($offset,2),'notice');
        if ($offset > 0)
        {
            fseek($fh, $offset);
        }

        while($data =fread($fh,$this -> upload_chunk_size))
        {
            $ret = $this -> _upload_loop($session_id,$offset,$data,$dropbox);
            if($ret['result'] !== WPVIVID_SUCCESS)
            {
                return $ret;
            }

            if((time() - $this -> last_time) >3)
            {
                if(is_callable($callback))
                {
                    call_user_func_array($callback,array(min($offset + $this -> upload_chunk_size,$this -> current_file_size),$this -> current_file_name,
                        $this->current_file_size,$this -> last_time,$this -> last_size));
                }
                $this -> last_size = $offset;
                $this -> last_time = time();
            }

            if(isset($ret['correct_offset']))
            {
                $offset = $ret['correct_offset'];
                fseek($fh, $offset);
                $wpvivid_plugin->wpvivid_log->WriteLog('correct_offset:'.size_format($offset,2),'notice');
            }
            else
            {
                $offset = ftell($fh);
            }

            $upload_job['job_data'][basename($file)]['offset']=$offset;
            $wpvivid_plugin->wpvivid_log->WriteLog('offset:'.size_format($offset,2),'notice');
            WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',WPVIVID_REMOTE_DROPBOX,WPVIVID_UPLOAD_UNDO,'Uploading '.basename($file),$upload_job['job_data']);
        }

        $options = $this -> options;
        $path = trailingslashit($options['path']).basename($file);
        $result = $dropbox -> upload_session_finish($session_id,$offset,$path);
        if(isset($result['error_summary']))
        {
            $wpvivid_plugin->wpvivid_log->WriteLog('offset:'.$offset,'notice');
            $wpvivid_plugin->wpvivid_log->WriteLog('result:'.wp_json_encode($result),'notice');
            $ret = array('result' => WPVIVID_FAILED,'error' => $result['error_summary']);
        }else{
            $ret = array('result'=> WPVIVID_SUCCESS);
        }

        fclose($fh);
        return $ret;
    }
    public function _upload_loop($session_id,$offset,$data,$dropbox)
    {
        $result['result']=WPVIVID_SUCCESS;
        for($i =0;$i <WPVIVID_REMOTE_CONNECT_RETRY_TIMES; $i ++)
        {
            $result = $dropbox -> upload_session_append_v2($session_id,$offset,$data);
            if(isset($result['error_summary']))
            {
                if(strstr($result['error_summary'],'incorrect_offset'))
                {
                    $result['result']=WPVIVID_SUCCESS;
                    $result['correct_offset']=$result['error']['correct_offset'];
                    return $result;
                }
                else
                {
                    $result = array('result' => WPVIVID_FAILED,'error' => 'Uploading '.$this -> current_file_name.' to Dropbox server failed. '.$result['error_summary']);
                }
            }
            else
            {
                return array('result' => WPVIVID_SUCCESS);
            }
        }
        return $result;
    }

    public function download($file, $local_path, $callback = '')
    {
        try {
            global $wpvivid_plugin;
            $wpvivid_plugin->wpvivid_download_log->WriteLog('Remote type: Dropbox.','notice');
            $this->current_file_name = $file['file_name'];
            $this->current_file_size = $file['size'];
            $options = $this->options;
            $dropbox = new Dropbox_Base($options);
            $ret=$dropbox->check_token();
            if($ret['result']=='failed')
            {
                return $ret;
            }
            $file_path = trailingslashit($local_path) . $this->current_file_name;
            $start_offset = file_exists($file_path) ? filesize($file_path) : 0;
            $wpvivid_plugin->wpvivid_download_log->WriteLog('Create local file.','notice');
            $fh = fopen($file_path, 'a');
            $wpvivid_plugin->wpvivid_download_log->WriteLog('Downloading file ' . $file['file_name'] . ', Size: ' . $file['size'] ,'notice');
            while ($start_offset < $this->current_file_size) {
                $last_byte = min($start_offset + $this->download_chunk_size - 1, $this->current_file_size - 1);
                $headers = array("Range: bytes=$start_offset-$last_byte");
                $response = $dropbox->download(trailingslashit($options['path']) . $this->current_file_name, $headers);
                if (isset($response['error_summary'])) {
                    return array('result' => WPVIVID_FAILED, 'error' => 'Downloading ' . trailingslashit($options['path']) . $this->current_file_name . ' failed.' . $response['error_summary']);
                }
                if (!fwrite($fh, $response)) {
                    return array('result' => WPVIVID_FAILED, 'error' => 'Downloading ' . trailingslashit($options['path']) . $this->current_file_name . ' failed.');
                }
                clearstatcache();
                $state = stat($file_path);
                $start_offset = $state['size'];

                if ((time() - $this->last_time) > 3) {
                    if (is_callable($callback)) {
                        call_user_func_array($callback, array($start_offset, $this->current_file_name,
                            $this->current_file_size, $this->last_time, $this->last_size));
                    }
                    $this->last_size = $start_offset;
                    $this->last_time = time();
                }
            }
            @fclose($fh);

            if(filesize($file_path) == $file['size']){
                if($wpvivid_plugin->wpvivid_check_zip_valid()) {
                    $res = TRUE;
                }
                else{
                    $res = FALSE;
                }
            }
            else{
                $res = FALSE;
            }

            if ($res !== TRUE) {
                @wp_delete_file($file_path);
                return array('result' => WPVIVID_FAILED, 'error' => 'Downloading ' . $file['file_name'] . ' failed. ' . $file['file_name'] . ' might be deleted or network doesn\'t work properly. Please verify the file and confirm the network connection and try again later.');
            }
            return array('result' => WPVIVID_SUCCESS);
        }
        catch (Exception $error){
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            return array('result'=>WPVIVID_FAILED, 'error'=>$message);
        }
    }

    public function cleanup($files)
    {
        $options = $this -> options;
        $dropbox = new Dropbox_Base($options);
        $ret=$dropbox->check_token();
        if($ret['result']=='failed')
        {
            return $ret;
        }
        foreach ($files as $file){
            $dropbox -> delete(trailingslashit($options['path']).$file);
        }
        return array('result'=>WPVIVID_SUCCESS);
    }

    public function init_remotes($remote_collection){
        $remote_collection[WPVIVID_REMOTE_DROPBOX] = 'WPvivid_Dropbox';
        return $remote_collection;
    }

    public function handle_auth_actions()
    {
        if(isset($_GET['action']) && isset($_GET['page']))
        {
            if($_GET['page'] === 'WPvivid')
            {
                if($_GET['action'] === 'wpvivid_dropbox_auth')
                {
                    try {
                        $auth_id = uniqid('wpvivid-auth-');
                        $state = admin_url() . 'admin.php?page=WPvivid' . '&action=wpvivid_dropbox_finish_auth&main_tab=storage&sub_tab=dropbox&sub_page=storage_account_dropbox&auth_id='.$auth_id;
                        $remote_options['auth_id']=$auth_id;
                        update_option('wpvivid_tmp_remote_options',$remote_options);
                        $url = Dropbox_Base::getUrl($this->redirect_url, $state);
                        header('Location: ' . filter_var($url, FILTER_SANITIZE_URL));
                    }
                    catch (Exception $e){
                        echo '<div class="notice notice-error"><p>'.esc_html($e->getMessage()).'</p></div>';
                    }
                }
                else if($_GET['action'] === 'wpvivid_dropbox_finish_auth')
                {
                    try {
                        $remoteslist = WPvivid_Setting::get_all_remote_options();
                        foreach ($remoteslist as $key => $value)
                        {
                            if (isset($value['auth_id']) && isset($_GET['auth_id']) && $value['auth_id'] == sanitize_text_field($_GET['auth_id']))
                            {
                                echo '<div class="notice notice-success is-dismissible"><p>';
                                esc_html_e('You have authenticated the Dropbox account as your remote storage.', 'wpvivid-backuprestore');
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
                            if($tmp_options['auth_id']===sanitize_text_field($_GET['auth_id']))
                            {
                                if(empty($_POST['code']))
                                {
                                    if(empty($tmp_options['access_token']))
                                    {
                                        header('Location: ' . admin_url() . 'admin.php?page=' . WPVIVID_PLUGIN_SLUG . '&action=wpvivid_dropbox_drive&result=error&resp_msg=' . 'Get Dropbox token failed.');
                                        return;
                                    }
                                }
                                else
                                {
                                    $tmp_options['type'] = WPVIVID_REMOTE_DROPBOX;
                                    $tmp_options['access_token']= base64_encode(sanitize_text_field($_POST['code']));
                                    $tmp_options['expires_in'] = sanitize_text_field($_POST['expires_in']);
                                    $tmp_options['refresh_token'] = base64_encode(sanitize_text_field($_POST['refresh_token']));
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
                else if($_GET['action']=='wpvivid_dropbox_drive')
                {
                    try {
                        if (isset($_GET['result'])) {
                            if ($_GET['result'] == 'success') {
                                add_action('show_notice', array($this, 'wpvivid_show_notice_add_dropbox_success'));
                            } else if ($_GET['result'] == 'error') {
                                add_action('show_notice', array($this, 'wpvivid_show_notice_add_dropbox_error'));
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
    public function wpvivid_show_notice_add_dropbox_success(){
        echo '<div class="notice notice-success is-dismissible"><p>';
            esc_html_e('You have authenticated the Dropbox account as your remote storage.', 'wpvivid-backuprestore');
            echo '</p></div>';
    }
    public function wpvivid_show_notice_add_dropbox_error(){
        global $wpvivid_plugin;
        $wpvivid_plugin->wpvivid_handle_remote_storage_error($_GET['resp_msg'], 'Add Dropbox Remote');
        echo '<div class="notice notice-error"><p>'.esc_html($_GET['resp_msg']).'</p></div>';
    }

    public function wpvivid_add_storage_tab_dropbox(){
        ?>
        <div class="storage-providers" remote_type="dropbox" onclick="select_remote_storage(event, 'storage_account_dropbox');">
            <img src="<?php echo esc_url(WPVIVID_PLUGIN_URL.'/admin/partials/images/storage-dropbox.png'); ?>" style="vertical-align:middle;"/><?php esc_html_e('Dropbox', 'wpvivid-backuprestore'); ?>
        </div>
        <?php
    }
    public function wpvivid_add_storage_page_dropbox(){
        global $wpvivid_plugin;
        $root_path=apply_filters('wpvivid_get_root_path', WPVIVID_REMOTE_DROPBOX);

        $remote = get_option('wpvivid_upload_setting');
        if($this->add_remote)
        {
            ?>
            <div id="storage_account_dropbox" class="storage-account-page" style="display:none;">
                <div style="background-color:#f1f1f1; padding: 10px;">
                    Please read<a target="_blank" href="https://wpvivid.com/privacy-policy" style="text-decoration: none;">this privacy policy</a> for use of our Dropbox authorization app (none of your backup data is sent to us).
                </div>
                <div style="color:#8bc34a; padding: 10px 10px 10px 0;">
                    <strong><?php esc_html_e('Authentication is done, please continue to enter the storage information, then click \'Add Now\' button to save it.', 'wpvivid-backuprestore'); ?></strong>
                </div>
                <div style="padding: 10px 10px 10px 0;">
                    <strong><?php esc_html_e('Enter Your Dropbox Information', 'wpvivid-backuprestore'); ?></strong>
                </div>
                <table class="wp-list-table widefat plugins" style="width:100%;">
                    <tbody>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input type="text" class="regular-text" autocomplete="off" option="dropbox" name="name" placeholder="<?php esc_attr_e('Enter a unique alias: e.g. Dropbox-001', 'wpvivid-backuprestore'); ?>" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_]/g,'')" />
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
                                <input type="text" class="regular-text" autocomplete="off" option="dropbox" name="path" value="<?php echo esc_attr($root_path.WPVIVID_DROPBOX_DEFAULT_FOLDER); ?>" readonly="readonly" />
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
                                <a href="https://docs.wpvivid.com/wpvivid-backup-pro-dropbox-custom-folder-name.html"><?php esc_html_e('Pro feature: Create a directory for storing the backups of the site', 'wpvivid-backuprestore'); ?></a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-select">
                                <label>
                                    <input type="checkbox" option="dropbox" name="default" checked /><?php esc_html_e('Set as the default remote storage.', 'wpvivid-backuprestore'); ?>
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
                                <input id="wpvivid_dropbox_auth" class="button-primary" type="submit" value="<?php esc_attr_e('Add Now', 'wpvivid-backuprestore'); ?>">
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
                function wpvivid_check_dropbox_storage_alias(storage_alias)
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

                jQuery('#wpvivid_dropbox_auth').click(function()
                {
                    wpvivid_dropbox_auth();
                });

                function wpvivid_dropbox_auth()
                {
                    wpvivid_settings_changed = false;
                    var name='';
                    var path = '';
                    var bdefault = '0';
                    jQuery("input:checkbox[option=dropbox]").each(function(){
                        var key = jQuery(this).prop('name');
                        if(jQuery(this).prop('checked')) {
                            bdefault = '1';
                        }
                        else {
                            bdefault = '0';
                        }
                    });
                    jQuery('input:text[option=dropbox]').each(function()
                    {
                        var type = jQuery(this).prop('name');
                        if(type == 'name'){
                            name = jQuery(this).val();
                        }
                    });
                    if(name == ''){
                        alert(wpvividlion.remotealias);
                    }
                    else if(wpvivid_check_dropbox_storage_alias(name) === -1){
                        alert(wpvividlion.remoteexist);
                    }
                    else{
                        var ajax_data;
                        var remote_from = wpvivid_ajax_data_transfer('dropbox');
                        ajax_data = {
                            'action': 'wpvivid_dropbox_add_remote',
                            'remote': remote_from
                        };
                        jQuery('#wpvivid_dropbox_auth').css({'pointer-events': 'none', 'opacity': '0.4'});
                        jQuery('#wpvivid_remote_notice').html('');
                        wpvivid_post_request(ajax_data, function (data)
                        {
                            try
                            {
                                var jsonarray = jQuery.parseJSON(data);
                                if (jsonarray.result === 'success')
                                {
                                    jQuery('#wpvivid_dropbox_auth').css({'pointer-events': 'auto', 'opacity': '1'});
                                    jQuery('input:text[option=dropbox]').each(function(){
                                        jQuery(this).val('');
                                    });
                                    jQuery('input:password[option=dropbox]').each(function(){
                                        jQuery(this).val('');
                                    });
                                    wpvivid_handle_remote_storage_data(data);
                                    location.href='admin.php?page=WPvivid&action=wpvivid_dropbox_drive&main_tab=storage&sub_tab=dropbox&sub_page=storage_account_dropbox&result=success';
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
                            jQuery('#wpvivid_dropbox_auth').css({'pointer-events': 'auto', 'opacity': '1'});
                        });
                    }
                }
            </script>
            <?php
        }
        else
        {
            ?>
            <div id="storage_account_dropbox" class="storage-account-page" style="display:none;">
                <div style="background-color:#f1f1f1; padding: 10px;">
                    Please read <a target="_blank" href="https://wpvivid.com/privacy-policy" style="text-decoration: none;">this privacy policy</a> for use of our Dropbox authorization app (none of your backup data is sent to us).
                </div>
                <div style="padding: 10px 10px 10px 0;">
                    <strong><?php esc_html_e('To add Dropbox, please get Dropbox authentication first. Once authenticated, you will be redirected to this page, then you can add storage information and save it.', 'wpvivid-backuprestore'); ?></strong>
                </div>
                <table class="wp-list-table widefat plugins" style="width:100%;">
                    <tbody>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input onclick="wpvivid_dropbox_auth();" class="button-primary" type="submit" value="<?php esc_attr_e('Authenticate with Dropbox', 'wpvivid-backuprestore'); ?>">
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i><?php esc_html_e('Click to get Dropbox authentication.', 'wpvivid-backuprestore'); ?></i>
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
                function wpvivid_dropbox_auth()
                {
                    location.href ='<?php echo esc_url(admin_url()).'admin.php?page=WPvivid'.'&action=wpvivid_dropbox_auth'?>';
                }
            </script>
            <?php
        }
    }
    public function wpvivid_edit_storage_page_dropbox()
    {
        do_action('wpvivid_remote_storage_js');
        ?>
        <div id="remote_storage_edit_dropbox" class="postbox storage-account-block remote-storage-edit" style="display:none;">
            <div style="padding: 0 10px 10px 0;">
                <strong><?php esc_html_e('To add Dropbox, please get Dropbox authentication first. Once authenticated, you will be redirected to this page, then you can add storage information and save it', 'wpvivid-backuprestore'); ?></strong>
            </div>
            <table class="wp-list-table widefat plugins" style="width:100%;">
                <tbody>
                <tr>
                    <td class="plugin-title column-primary">
                        <div class="wpvivid-storage-form">
                            <input type="text" class="regular-text" autocomplete="off" option="edit-dropbox" name="name" placeholder="<?php esc_attr_e('Enter a unique alias: e.g. Dropbox-001', 'wpvivid-backuprestore'); ?>" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_]/g,'')" />
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
            function wpvivid_dropbox_update_auth()
            {
                var name='';
                jQuery('input:text[option=edit-dropbox]').each(function()
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
                    location.href = '<?php echo esc_url(admin_url()) . 'admin.php?page=WPvivid' . '&action=wpvivid_dropbox_update_auth&name='?>' + name + '&id=' + wpvivid_editing_storage_id;
                }
            }
        </script>
        <?php
    }
    public function wpvivid_remote_pic_dropbox($remote)
    {
        $remote['dropbox']['default_pic'] = '/admin/partials/images/storage-dropbox(gray).png';
        $remote['dropbox']['selected_pic'] = '/admin/partials/images/storage-dropbox.png';
        $remote['dropbox']['title'] = 'Dropbox';
        return $remote;
    }

    public function revoke($id){
        $upload_options = WPvivid_Setting::get_option('wpvivid_upload_setting');
        if(array_key_exists($id,$upload_options) && $upload_options[$id] == WPVIVID_REMOTE_DROPBOX){
            $dropbox = new Dropbox_Base($upload_options);
            $dropbox -> revoke();
        }
    }

    public function wpvivid_get_out_of_date_dropbox($out_of_date_remote, $remote)
    {
        if($remote['type'] == WPVIVID_REMOTE_DROPBOX){
            $root_path=apply_filters('wpvivid_get_root_path', $remote['type']);
            $out_of_date_remote = $root_path.$remote['path'];
        }
        return $out_of_date_remote;
    }

    public function wpvivid_storage_provider_dropbox($storage_type)
    {
        if($storage_type == WPVIVID_REMOTE_DROPBOX){
            $storage_type = 'Dropbox';
        }
        return $storage_type;
    }

    public function wpvivid_get_root_path_dropbox($storage_type){
        if($storage_type == WPVIVID_REMOTE_DROPBOX){
            $storage_type = 'apps/Wpvivid backup restore';
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
            if(empty($tmp_remote_options)||$tmp_remote_options['type']!==WPVIVID_REMOTE_DROPBOX)
            {
                die();
            }

            $json = sanitize_text_field($_POST['remote']);
            $json = stripslashes($json);
            $remote_options = json_decode($json, true);
            if (is_null($remote_options)) {
                die();
            }

            $remote_options['created']=time();
            $remote_options['path'] = WPVIVID_DROPBOX_DEFAULT_FOLDER;
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