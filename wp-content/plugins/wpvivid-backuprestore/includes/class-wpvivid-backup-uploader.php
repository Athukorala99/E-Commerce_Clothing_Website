<?php

if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

class Wpvivid_BackupUploader
{
    public function __construct()
    {
        add_action('wp_ajax_wpvivid_cancel_upload_backup_free', array($this, 'cancel_upload_backup_free'));
        add_action('wp_ajax_wpvivid_is_backup_file_free', array($this, 'is_backup_file_free'));
        add_action('wp_ajax_wpvivid_upload_files_finish_free', array($this, 'upload_files_finish_free'));
        add_action('wp_ajax_wpvivid_get_file_id',array($this,'get_file_id'));
        add_action('wp_ajax_wpvivid_upload_files',array($this,'upload_files'));
        add_action('wp_ajax_wpvivid_upload_files_finish',array($this,'upload_files_finish'));
        add_action('wp_ajax_wpvivid_delete_upload_incomplete_backup_free', array($this, 'delete_upload_incomplete_backup'));

        add_action('wp_ajax_wpvivid_rescan_local_folder',array($this,'rescan_local_folder_set_backup'));
        add_action('wp_ajax_wpvivid_get_backup_count',array($this,'get_backup_count'));
        add_action('wpvivid_rebuild_backup_list', array($this, 'wpvivid_rebuild_backup_list'), 10);
    }

    function cancel_upload_backup_free()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try{
            $path=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir().DIRECTORY_SEPARATOR;
            if(is_dir($path))
            {
                $handler = opendir($path);
                if($handler!==false)
                {
                    while (($filename = readdir($handler)) !== false)
                    {
                        if ($filename != "." && $filename != "..")
                        {
                            if (is_dir($path  . $filename))
                            {
                                continue;
                            }
                            else
                            {
                                if (preg_match('/.*\.tmp$/', $filename))
                                {
                                    @wp_delete_file($path  . $filename);
                                }

                                if (preg_match('/.*\.part$/', $filename))
                                {
                                    @wp_delete_file($path  . $filename);
                                }
                            }
                        }
                    }
                    if($handler)
                        @closedir($handler);
                }
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function is_wpvivid_backup($file_name)
    {
        if (preg_match('/wpvivid-.*_.*_.*\.zip$/', $file_name))
        {
            return true;
        }
        else {
            return false;
        }
    }

    function is_backup_file_free()
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
            $file_name=sanitize_text_field($_POST['file_name']);
            if (isset($file_name))
            {
                if ($this->is_wpvivid_backup($file_name))
                {
                    $ret['result'] = WPVIVID_SUCCESS;

                    $backupdir=WPvivid_Setting::get_backupdir();
                    $filePath = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backupdir.DIRECTORY_SEPARATOR.$file_name;
                    if(file_exists($filePath))
                    {
                        $ret['is_exists']=true;
                    }
                    else
                    {
                        $ret['is_exists']=false;
                    }
                }
                else
                {
                    $ret['result'] = WPVIVID_FAILED;
                    $ret['error'] = $file_name . ' is not created by WPvivid backup plugin.';
                }
            }
            else
            {
                $ret['result'] = WPVIVID_FAILED;
                $ret['error'] = 'Failed to post file name.';
            }

            echo wp_json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }

        die();
    }

    function upload_files_finish_free()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        try {
            $ret = $this->_rescan_local_folder_set_backup();
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

    function delete_upload_incomplete_backup()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        try {
            if(isset($_POST['incomplete_backup'])&&!empty($_POST['incomplete_backup']))
            {
                $json = sanitize_text_field($_POST['incomplete_backup']);
                $json = stripslashes($json);
                $incomplete_backup = json_decode($json, true);

                if(is_array($incomplete_backup) && !empty($incomplete_backup))
                {
                    $path=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir().DIRECTORY_SEPARATOR;
                    foreach ($incomplete_backup as $backup)
                    {
                        $backup = basename($backup);
                        if (preg_match('/wpvivid-.*_.*_.*\.zip$/', $backup))
                        {
                            @wp_delete_file($path.$backup);
                        }
                        else if(preg_match('/'.apply_filters('wpvivid_white_label_file_prefix', 'wpvivid').'-.*_.*_.*\.zip$/', $backup))
                        {
                            @wp_delete_file($path.$backup);
                        }
                    }
                }

                $ret['result']='success';
                echo wp_json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    function get_file_id()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        $file_name=sanitize_text_field($_POST['file_name']);
        if(isset($file_name))
        {
            if(preg_match('/wpvivid-.*_.*_.*\.zip$/',$file_name))
            {
                if(preg_match('/wpvivid-(.*?)_/',$file_name,$matches))
                {
                    $id= $matches[0];
                    $id=substr($id,0,strlen($id)-1);
                    if(WPvivid_Backuplist::get_backup_by_id($id)===false)
                    {
                        $ret['result']=WPVIVID_SUCCESS;
                        $ret['id']=$id;
                    }
                    else
                    {
                        $ret['result']=WPVIVID_FAILED;
                        $ret['error']='The uploading backup already exists in Backups list.';
                    }
                }
                else
                {
                    $ret['result']=WPVIVID_FAILED;
                    $ret['error']=$file_name . ' is not created by WPvivid backup plugin.';
                }
            }
            else
            {
                $ret['result']=WPVIVID_FAILED;
                $ret['error']=$file_name . ' is not created by WPvivid backup plugin.';
            }
        }
        else
        {
            $ret['result']=WPVIVID_FAILED;
            $ret['error']='Failed to post file name.';
        }

        echo wp_json_encode($ret);
        die();
    }

    function check_file_is_a_wpvivid_backup($file_name,&$backup_id)
    {
        if(preg_match('/wpvivid-.*_.*_.*\.zip$/',$file_name))
        {
            if(preg_match('/wpvivid-(.*?)_/',$file_name,$matches))
            {
                $id= $matches[0];
                $id=substr($id,0,strlen($id)-1);


                if(WPvivid_Backuplist::get_backup_by_id($id)===false)
                {
                    $backup_id=$id;
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    function upload_files()
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
            $chunk = isset($_REQUEST["chunk"]) ? intval(sanitize_key($_REQUEST["chunk"])) : 0;
            $chunks = isset($_REQUEST["chunks"]) ? intval(sanitize_key($_REQUEST["chunks"])) : 0;

            $fileName = isset($_REQUEST["name"]) ? sanitize_text_field($_REQUEST["name"]) : $_FILES["file"]["name"];

            $backupdir=WPvivid_Setting::get_backupdir();
            $filePath = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backupdir.DIRECTORY_SEPARATOR.$fileName;

            $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");

            if ($out)
            {
                // Read binary input stream and append it to temp file
                $options['test_form'] =true;
                $options['action'] ='wpvivid_upload_files';
                $options['test_type'] = false;
                $options['ext'] = 'zip';
                $options['type'] = 'application/zip';

                add_filter('upload_dir', array($this, 'upload_dir'));

                $status = wp_handle_upload($_FILES['async-upload'],$options);

                remove_filter('upload_dir', array($this, 'upload_dir'));

                $in = @fopen($status['file'], "rb");

                if ($in)
                {
                    while ($buff = fread($in, 4096))
                        fwrite($out, $buff);
                }
                else
                {
                    echo wp_json_encode(array('result'=>'failed','error'=>"Failed to open tmp file.path:".$status['file']));
                    die();
                }

                @fclose($in);
                @fclose($out);

                @wp_delete_file($status['file']);
            }
            else
            {
                echo wp_json_encode(array('result'=>'failed','error'=>"Failed to open input stream.path:{$filePath}.part"));
                die();
            }

            if (!$chunks || $chunk == $chunks - 1)
            {
                // Strip the temp .part suffix off
                rename("{$filePath}.part", $filePath);
            }

            echo wp_json_encode(array('result' => WPVIVID_SUCCESS));
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    function upload_files_finish()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        $ret['html']=false;
        if(isset($_POST['files']))
        {
            $files=sanitize_text_field($_POST['files']);
            $files =stripslashes($files);
            $files =json_decode($files,true);
            if(is_null($files))
            {
                die();
            }

            $path=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir().DIRECTORY_SEPARATOR;

            $backup_data['result']='success';
            $backup_data['files']=array();
            if(preg_match('/wpvivid-.*_.*_.*\.zip$/',$files[0]['name']))
            {
                if(preg_match('/wpvivid-(.*?)_/',$files[0]['name'],$matches_id))
                {
                    if(preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}/',$files[0]['name'],$matches))
                    {
                        $backup_time=$matches[0];
                        $time_array=explode('-',$backup_time);
                        if(sizeof($time_array)>4)
                            $time=$time_array[0].'-'.$time_array[1].'-'.$time_array[2].' '.$time_array[3].':'.$time_array[4];
                        else
                            $time=$backup_time;
                        $time=strtotime($time);
                    }
                    else
                    {
                        $time=time();
                    }
                    $id= $matches_id[0];
                    $id=substr($id,0,strlen($id)-1);
                    $unlinked_file = '';
                    $check_result=true;
                    foreach ($files as $file)
                    {
                        $res=$this->check_is_a_wpvivid_backup($path.$file['name']);
                        if($res === true)
                        {
                            $add_file['file_name']=$file['name'];
                            $add_file['size']=filesize($path.$file['name']);
                            $backup_data['files'][]=$add_file;
                        }
                        else
                        {
                            $check_result=false;
                            $unlinked_file .= 'file name: '.$file['name'].', error: '.$res;
                        }
                    }
                    if($check_result === true){
                        WPvivid_Backuplist::add_new_upload_backup($id,$backup_data,$time,'');
                        $html = '';
                        $html = apply_filters('wpvivid_add_backup_list', $html);
                        $ret['result']=WPVIVID_SUCCESS;
                        $ret['html'] = $html;
                    }
                    else{
                        foreach ($files as $file) {
                            $this->clean_tmp_files($path, $file['name']);
                            @wp_delete_file($path . $file['name']);
                        }
                        $ret['result']=WPVIVID_FAILED;
                        $ret['error']='Upload file failed.';
                        $ret['unlinked']=$unlinked_file;
                    }
                }
                else
                {
                    $ret['result']=WPVIVID_FAILED;
                    $ret['error']='The backup is not created by WPvivid backup plugin.';
                }
            }
            else
            {
                $ret['result']=WPVIVID_FAILED;
                $ret['error']='The backup is not created by WPvivid backup plugin.';
            }
        }
        else{
            $ret['result']=WPVIVID_FAILED;
            $ret['error']='Failed to post file name.';
        }
        echo wp_json_encode($ret);
        die();
    }

    function clean_tmp_files($path, $filename){
        $handler=opendir($path);
        if($handler===false)
            return;
        while(($file=readdir($handler))!==false) {
            if (!is_dir($path.$file) && preg_match('/wpvivid-.*_.*_.*\.tmp$/', $file)) {
                $iPos = strrpos($file, '_');
                $file_temp = substr($file, 0, $iPos);
                if($file_temp === $filename) {
                    @wp_delete_file($path.$file);
                }
            }
        }
        @closedir($handler);
    }

    function wpvivid_check_remove_update_backup($path){
        $backup_list = WPvivid_Setting::get_option('wpvivid_backup_list');
        $remove_backup_array = array();
        $update_backup_array = array();
        $tmp_file_array = array();
        $remote_backup_list=WPvivid_Backuplist::get_has_remote_backuplist();
        foreach ($backup_list as $key => $value){
            if(!in_array($key, $remote_backup_list)) {
                $need_remove = true;
                $need_update = false;
                if (is_dir($path)) {
                    $handler = opendir($path);
                    if($handler===false)
                        return true;
                    while (($filename = readdir($handler)) !== false) {
                        if ($filename != "." && $filename != "..") {
                            if (!is_dir($path . $filename)) {
                                if ($this->check_wpvivid_file_info($filename, $backup_id, $need_update)) {
                                    if ($key === $backup_id) {
                                        $need_remove = false;
                                    }
                                    if ($need_update) {
                                        if ($this->check_is_a_wpvivid_backup($path . $filename) === true) {
                                            if (!in_array($filename, $tmp_file_array)) {
                                                $add_file['file_name'] = $filename;
                                                $add_file['size'] = filesize($path . $filename);
                                                $tmp_file_array[] = $filename;
                                                $update_backup_array[$backup_id]['files'][] = $add_file;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($handler) {
                        @closedir($handler);
                    }
                }
                if ($need_remove) {
                    $remove_backup_array[] = $key;
                }
            }
        }
        $this->wpvivid_remove_update_local_backup_list($remove_backup_array, $update_backup_array);
        return true;
    }

    function check_wpvivid_file_info($file_name, &$backup_id, &$need_update=false){
        if(preg_match('/wpvivid-.*_.*_.*\.zip$/',$file_name))
        {
            if(preg_match('/wpvivid-(.*?)_/',$file_name,$matches))
            {
                $id= $matches[0];
                $id=substr($id,0,strlen($id)-1);
                $backup_id=$id;

                if(WPvivid_Backuplist::get_backup_by_id($id)===false)
                {
                    $need_update = false;
                    return true;
                }
                else
                {
                    $need_update = true;
                    return true;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    function wpvivid_remove_update_local_backup_list($remove_backup_array, $update_backup_array){
        $backup_list = WPvivid_Setting::get_option('wpvivid_backup_list');
        foreach ($remove_backup_array as $remove_backup_id){
            unset($backup_list[$remove_backup_id]);
        }
        foreach ($update_backup_array as $update_backup_id => $data){
            $backup_list[$update_backup_id]['backup']['files'] = $data['files'];
        }
        WPvivid_Setting::update_option('wpvivid_backup_list', $backup_list);
    }

    function _rescan_local_folder_set_backup(){
        $path=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir().DIRECTORY_SEPARATOR;

        $this->wpvivid_check_remove_update_backup($path);

        $backups=array();
        $count = 0;
        if(is_dir($path))
        {
            $handler = opendir($path);
            if($handler!==false)
            {
                while (($filename = readdir($handler)) !== false)
                {
                    if ($filename != "." && $filename != "..")
                    {
                        $count++;

                        if (is_dir($path  . $filename))
                        {
                            continue;
                        } else {
                            if($this->check_file_is_a_wpvivid_backup($filename,$backup_id))
                            {
                                if($this->zip_check_sum($path . $filename))
                                {
                                    if($this->check_is_a_wpvivid_backup($path.$filename) === true)
                                    {
                                        $backups[$backup_id]['files'][]=$filename;
                                    }
                                    else
                                    {
                                        $ret['incomplete_backup'][] = $filename;
                                    }
                                }
                            }
                        }
                    }
                }
                if($handler)
                    @closedir($handler);
            }
        }
        else{
            $ret['result']=WPVIVID_FAILED;
            $ret['error']='Failed to get local storage directory.';
        }
        if(!empty($backups))
        {
            foreach ($backups as $backup_id =>$backup)
            {
                $backup_data['result']='success';
                $backup_data['files']=array();
                if(empty($backup['files']))
                    continue;
                $time=false;
                foreach ($backup['files'] as $file)
                {
                    if($time===false)
                    {
                        if(preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}/',$file,$matches))
                        {
                            $backup_time=$matches[0];
                            $time_array=explode('-',$backup_time);
                            if(sizeof($time_array)>4)
                                $time=$time_array[0].'-'.$time_array[1].'-'.$time_array[2].' '.$time_array[3].':'.$time_array[4];
                            else
                                $time=$backup_time;
                            $time=strtotime($time);
                        }
                        else
                        {
                            $time=time();
                        }
                    }

                    $add_file['file_name']=$file;
                    $add_file['size']=filesize($path.$file);
                    $backup_data['files'][]=$add_file;
                }

                WPvivid_Backuplist::add_new_upload_backup($backup_id,$backup_data,$time,'');
            }
        }
        $ret['result']=WPVIVID_SUCCESS;
        $html = '';
        $tour = true;
        $html = apply_filters('wpvivid_add_backup_list', $html, 'wpvivid_backup_list', $tour);
        $ret['html'] = $html;
        return $ret;
    }

    function rescan_local_folder_set_backup()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        $ret = $this->_rescan_local_folder_set_backup();
        echo wp_json_encode($ret);
        die();
    }

    public function wpvivid_rebuild_backup_list(){
        $this->_rescan_local_folder_set_backup();
    }

    static function rescan_local_folder()
    {
        $backupdir=WPvivid_Setting::get_backupdir();
        ?>
        <div style="padding-top: 10px;">
            <span><?php esc_html_e('Tips: Click the button below to scan all uploaded or received backups in directory', 'wpvivid-backuprestore'); ?>&nbsp<?php echo esc_html(WP_CONTENT_DIR.'/'.$backupdir); ?></span>
        </div>
        <div style="padding-top: 10px;">
            <input type="submit" class="button-primary" value="<?php esc_attr_e('Scan uploaded backup or received backup', 'wpvivid-backuprestore'); ?>" onclick="wpvivid_rescan_local_folder();" />
        </div>
        <script type="text/javascript">
            function wpvivid_rescan_local_folder()
            {
                var ajax_data = {
                    'action': 'wpvivid_rescan_local_folder'
                };
                wpvivid_post_request(ajax_data, function (data)
                {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if(typeof jsonarray.incomplete_backup !== 'undefined' && jsonarray.incomplete_backup.length > 0)
                        {
                            var incomplete_count = jsonarray.incomplete_backup.length;
                            alert('Failed to scan '+incomplete_count+' backup zips, the zips can be corrupted during creation or download process. Please check the zips.');
                        }
                        if(jsonarray.html !== false)
                        {
                            jQuery('#wpvivid_backup_list').html('');
                            jQuery('#wpvivid_backup_list').append(jsonarray.html);
                            wpvivid_popup_tour('show');
                        }
                    }
                    catch(err) {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('scanning backup list', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        </script>
        <?php
    }

    function get_backup_count()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        $backuplist=WPvivid_Backuplist::get_backuplist();
        echo esc_attr(sizeof($backuplist));
        die();
    }

    static function upload_meta_box()
    {
        ?>
        <div id="wpvivid_plupload-upload-ui" class="hide-if-no-js" style="margin-bottom: 10px;">
            <div id="drag-drop-area">
                <div class="drag-drop-inside">
                    <p class="drag-drop-info"><?php esc_html_e('Drop files here', 'wpvivid-backuprestore'); ?></p>
                    <p><?php esc_html_x('or', 'Uploader: Drop files here - or - Select Files', 'wpvivid-backuprestore'); ?></p>
                    <p class="drag-drop-buttons"><input id="wpvivid_select_file_button" type="button" value="<?php esc_attr_e('Select Files', 'wpvivid-backuprestore'); ?>" class="button" /></p>
                </div>
            </div>
        </div>
        <div id="wpvivid_uploaded_file_list" class="hide-if-no-js" style="margin-bottom: 10px;"></div>
        <div id="wpvivid_upload_file_list" class="hide-if-no-js" style="margin-bottom: 10px;"></div>
        <div style="margin-bottom: 10px;">
            <input type="submit" class="button-primary" id="wpvivid_upload_submit_btn" value="Upload" onclick="wpvivid_submit_upload();" />
            <input type="submit" class="button-primary" id="wpvivid_stop_upload_btn" value="Cancel" onclick="wpvivid_cancel_upload();" />
        </div>
        <div style="clear: both;"></div>
        <?php
        $chunk_size = min(wp_max_upload_size(), 1048576*2);
        $plupload_init = array(
            'browse_button'       => 'wpvivid_select_file_button',
            'container'           => 'wpvivid_plupload-upload-ui',
            'drop_element'        => 'drag-drop-area',
            'file_data_name'      => 'async-upload',
            'max_retries'		    => 3,
            'multiple_queues'     => true,
            'max_file_size'       => '10Gb',
            'chunk_size'        => $chunk_size.'b',
            'url'                 => admin_url('admin-ajax.php'),
            'multipart'           => true,
            'urlstream_upload'    => true,
            // additional post data to send to our ajax hook
            'multipart_params'    => array(
                '_ajax_nonce' => wp_create_nonce('wpvivid_ajax'),
                'action'      => 'wpvivid_upload_files',            // the ajax action name
            ),
        );

        // we should probably not apply this filter, plugins may expect wp's media uploader...
        $plupload_init = apply_filters('plupload_init', $plupload_init);
        $upload_file_image = includes_url( '/images/media/archive.png' );
        ?>

        <script type="text/javascript">
            var uploader;

            function wpvivid_stop_upload()
            {
                var ajax_data = {
                    'action': 'wpvivid_cancel_upload_backup_free',
                };
                wpvivid_post_request(ajax_data, function (data)
                {
                    jQuery("#wpvivid_select_file_button").prop('disabled', false);
                    jQuery('#wpvivid_upload_file_list').html("");
                    jQuery('#wpvivid_upload_submit_btn').hide();
                    jQuery('#wpvivid_stop_upload_btn').hide();
                    wpvivid_init_upload_list();
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('cancelling upload backups', textStatus, errorThrown);
                    alert(error_message);
                    jQuery('#wpvivid_upload_file_list').html("");
                    jQuery('#wpvivid_upload_submit_btn').hide();
                    jQuery('#wpvivid_stop_upload_btn').hide();
                    wpvivid_init_upload_list();
                });
            }

            function wpvivid_check_plupload_added_files(up,files)
            {
                var repeat_files = '';
                var exist_files = '';
                var file_count=files.length;
                var current_scan=0;
                var exist_count=0;
                plupload.each(files, function(file)
                {
                    var brepeat=false;
                    var file_list = jQuery('#wpvivid_upload_file_list span');
                    file_list.each(function (index, value)
                    {
                        if (value.innerHTML === file.name)
                        {
                            brepeat=true;
                        }
                    });

                    if(!brepeat)
                    {
                        var ajax_data = {
                            'action': 'wpvivid_is_backup_file_free',
                            'file_name':file.name
                        };
                        wpvivid_post_request(ajax_data, function (data)
                        {
                            current_scan++;
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === "success")
                            {
                                if(jsonarray.is_exists==true)
                                {
                                    exist_count++;
                                    if(exist_files === '') {
                                        exist_files += file.name;
                                    }
                                    else {
                                        exist_files += ', ' + file.name;
                                    }
                                    wpvivid_file_uploaded_queued(file);
                                    uploader.removeFile(file);
                                }
                                else
                                {
                                    wpvivid_fileQueued( file );
                                }
                                if(file_count === exist_count)
                                {
                                    alert("The backup already exists in target folder.");
                                    exist_files = '';
                                }
                                else if((file_count === current_scan) && (exist_files !== ''))
                                {
                                    alert(exist_files + " already exist in target folder.");
                                    exist_files = '';
                                }
                            }
                            else if(jsonarray.result === "failed")
                            {
                                uploader.removeFile(file);
                                alert(jsonarray.error);
                            }
                        }, function (XMLHttpRequest, textStatus, errorThrown)
                        {
                            current_scan++;
                            var error_message = wpvivid_output_ajaxerror('uploading backups', textStatus, errorThrown);
                            uploader.removeFile(file);
                            alert(error_message);
                        });
                    }
                    else{
                        current_scan++;
                        if(repeat_files === ''){
                            repeat_files += file.name;
                        }
                        else{
                            repeat_files += ', ' + file.name;
                        }
                    }
                });
                if(repeat_files !== ''){
                    alert(repeat_files + " already exists in upload list.");
                    repeat_files = '';
                }
            }

            function wpvivid_fileQueued(file)
            {
                jQuery('#wpvivid_upload_file_list').append(
                    '<div id="' + file.id + '" style="width: 100%; height: 36px; background: #fff; margin-bottom: 1px;">' +
                    '<img src=" <?php echo esc_attr($upload_file_image); ?> " alt="" style="float: left; margin: 2px 10px 0 3px; max-width: 40px; max-height: 32px;">' +
                    '<div style="line-height: 36px; float: left; margin-left: 5px;"><span>' + file.name + '</span></div>' +
                    '<div class="fileprogress" style="line-height: 36px; float: right; margin-right: 5px;"></div>' +
                    '</div>' +
                    '<div style="clear: both;"></div>'
                );
                jQuery('#wpvivid_upload_submit_btn').show();
                jQuery('#wpvivid_stop_upload_btn').show();
                jQuery("#wpvivid_upload_submit_btn").prop('disabled', false);
            }

            function wpvivid_file_uploaded_queued(file)
            {
                jQuery('#'+file.id).remove();
            }

            function wpvivid_delete_incomplete_backups(incomplete_backup)
            {
                var ajax_data = {
                    'action': 'wpvivid_delete_upload_incomplete_backup_free',
                    'incomplete_backup': incomplete_backup
                };
                wpvivid_post_request(ajax_data, function (data)
                {
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                });
            }

            function wpvivid_init_upload_list()
            {
                uploader = new plupload.Uploader(<?php echo wp_json_encode($plupload_init); ?>);

                // checks if browser supports drag and drop upload, makes some css adjustments if necessary
                uploader.bind('Init', function(up)
                {
                    var uploaddiv = jQuery('#wpvivid_plupload-upload-ui');

                    if(up.features.dragdrop){
                        uploaddiv.addClass('drag-drop');
                        jQuery('#drag-drop-area')
                            .bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
                            .bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); });

                    }else{
                        uploaddiv.removeClass('drag-drop');
                        jQuery('#drag-drop-area').unbind('.wp-uploader');
                    }
                });

                uploader.init();
                // a file was added in the queue

                uploader.bind('FilesAdded', wpvivid_check_plupload_added_files);

                uploader.bind('Error', function(up, error)
                {
                    alert('Upload ' + error.file.name +' error, error code: ' + error.code + ', ' + error.message);
                    wpvivid_stop_upload();
                });

                uploader.bind('FileUploaded', function(up, file, response)
                {
                    var jsonarray = jQuery.parseJSON(response.response);
                    if(jsonarray.result == 'failed')
                    {
                        alert('upload ' + file.name + ' failed, ' + jsonarray.error);

                        uploader.stop();
                        wpvivid_stop_upload();
                    }
                    else
                    {
                        wpvivid_file_uploaded_queued(file);
                    }
                });

                uploader.bind('UploadProgress', function(up, file)
                {
                    jQuery('#' + file.id + " .fileprogress").html(file.percent + "%");
                });

                uploader.bind('UploadComplete',function(up, files)
                {
                    jQuery('#wpvivid_upload_file_list').html("");
                    jQuery('#wpvivid_upload_submit_btn').hide();
                    jQuery('#wpvivid_stop_upload_btn').hide();
                    jQuery("#wpvivid_select_file_button").prop('disabled', false);
                    var ajax_data = {
                        'action': 'wpvivid_upload_files_finish_free'
                    };
                    wpvivid_post_request(ajax_data, function (data)
                    {
                        try
                        {
                            var jsonarray = jQuery.parseJSON(data);
                            if(jsonarray.result === 'success')
                            {
                                if(typeof jsonarray.incomplete_backup !== 'undefined' && jsonarray.incomplete_backup.length > 0)
                                {
                                    var incomplete_count = jsonarray.incomplete_backup.length;
                                    var incomplete_backup = JSON.stringify(jsonarray.incomplete_backup);
                                    wpvivid_delete_incomplete_backups(incomplete_backup);
                                    alert('Failed to scan '+incomplete_count+' backup zips, the zips can be corrupted during creation or download process. Please check the zips.');
                                }
                                else
                                {
                                    alert('The upload has completed.');
                                }
                                jQuery('#wpvivid_backup_list').html('');
                                jQuery('#wpvivid_backup_list').append(jsonarray.html);
                                wpvivid_click_switch_page('backup', 'wpvivid_tab_backup', true);
                                location.href = '<?php echo esc_url(admin_url()) . 'admin.php?page=WPvivid'; ?>';
                            }
                            else
                            {
                                alert(jsonarray.error);
                            }
                        }
                        catch(err)
                        {
                            alert(err);
                        }
                    }, function (XMLHttpRequest, textStatus, errorThrown)
                    {
                        var error_message = wpvivid_output_ajaxerror('refreshing backup list', textStatus, errorThrown);
                        alert(error_message);
                    });
                    plupload.each(files, function(file)
                    {
                        if(typeof file === 'undefined')
                        {

                        }
                        else
                        {
                            uploader.removeFile(file.id);
                        }
                    });
                });

                uploader.bind('Destroy', function(up, file)
                {
                    wpvivid_stop_upload();
                });
            }

            jQuery(document).ready(function($)
            {
                // create the uploader and pass the config from above
                jQuery('#wpvivid_upload_submit_btn').hide();
                jQuery('#wpvivid_stop_upload_btn').hide();
                wpvivid_init_upload_list();
            });

            function wpvivid_submit_upload()
            {
                jQuery("#wpvivid_upload_submit_btn").prop('disabled', true);
                jQuery("#wpvivid_select_file_button").prop('disabled', true);
                uploader.refresh();
                uploader.start();
            }

            function wpvivid_cancel_upload()
            {
                uploader.destroy();
            }
        </script>
        <?php
    }

    public function upload_dir($uploads)
    {
        $uploads['path'] = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir();
        return $uploads;
    }

    private function check_is_a_wpvivid_backup($file_name)
    {
        $ret=WPvivid_Backup_Item::get_backup_file_info($file_name);
        if($ret['result'] === WPVIVID_SUCCESS){
            return true;
        }
        elseif($ret['result'] === WPVIVID_FAILED){
            return $ret['error'];
        }
    }

    private function zip_check_sum($file_name)
    {
        return true;
    }
}