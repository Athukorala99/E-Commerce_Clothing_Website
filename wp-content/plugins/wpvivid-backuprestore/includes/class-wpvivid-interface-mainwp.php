<?php

class WPvivid_Interface_MainWP
{
    public function __construct(){
        $this->load_wpvivid_mainwp_backup_filter();
        $this->load_wpvivid_mainwp_side_bar_filter();
        $this->load_wpvivid_mainwp_backup_list_filter();
        $this->load_wpvivid_mainwp_schedule_filter();
        $this->load_wpvivid_mainwp_setting_filter();
        $this->load_wpvivid_mainwp_remote_filter();
    }

    public function load_wpvivid_mainwp_backup_filter(){
        add_filter('wpvivid_get_status_mainwp', array($this, 'wpvivid_get_status_mainwp'));
        add_filter('wpvivid_get_backup_list_mainwp', array($this, 'wpvivid_get_backup_list_mainwp'));
        add_filter('wpvivid_get_backup_schedule_mainwp', array($this, 'wpvivid_get_backup_schedule_mainwp'));
        add_filter('wpvivid_get_default_remote_mainwp', array($this, 'wpvivid_get_default_remote_mainwp'));
        add_filter('wpvivid_prepare_backup_mainwp', array($this, 'wpvivid_prepare_backup_mainwp'));
        add_filter('wpvivid_backup_now_mainwp', array($this, 'wpvivid_backup_now_mainwp'));
        add_filter('wpvivid_view_backup_task_log_mainwp', array($this, 'wpvivid_view_backup_task_log_mainwp'));
        add_filter('wpvivid_backup_cancel_mainwp', array($this, 'wpvivid_backup_cancel_mainwp'));
        add_filter('wpvivid_set_backup_report_addon_mainwp', array($this, 'wpvivid_set_backup_report_addon_mainwp'));
    }

    public function load_wpvivid_mainwp_side_bar_filter(){
        add_filter('wpvivid_read_last_backup_log_mainwp', array($this, 'wpvivid_read_last_backup_log_mainwp'));
    }

    public function load_wpvivid_mainwp_backup_list_filter(){
        add_filter('wpvivid_set_security_lock_mainwp', array($this, 'wpvivid_set_security_lock_mainwp'));
        add_filter('wpvivid_view_log_mainwp', array($this, 'wpvivid_view_log_mainwp'));
        add_filter('wpvivid_init_download_page_mainwp', array($this, 'wpvivid_init_download_page_mainwp'));
        add_filter('wpvivid_prepare_download_backup_mainwp', array($this, 'wpvivid_prepare_download_backup_mainwp'));
        add_filter('wpvivid_get_download_task_mainwp', array($this, 'wpvivid_get_download_task_mainwp'));
        add_filter('wpvivid_download_backup_mainwp', array($this, 'wpvivid_download_backup_mainwp'));
        add_filter('wpvivid_delete_backup_mainwp', array($this, 'wpvivid_delete_backup_mainwp'));
        add_filter('wpvivid_delete_backup_array_mainwp', array($this, 'wpvivid_delete_backup_array_mainwp'));
    }

    public function load_wpvivid_mainwp_schedule_filter(){
        add_filter('wpvivid_set_schedule_mainwp', array($this, 'wpvivid_set_schedule_mainwp'));
    }

    public function load_wpvivid_mainwp_setting_filter(){
        add_filter('wpvivid_set_general_setting_mainwp', array($this, 'wpvivid_set_general_setting_mainwp'));
    }

    public function load_wpvivid_mainwp_remote_filter(){
        add_filter('wpvivid_set_remote_mainwp', array($this, 'wpvivid_set_remote_mainwp'));
    }

    public function wpvivid_get_status_mainwp($data){
        $ret['result']='success';
        $list_tasks=array();
        $tasks=WPvivid_Setting::get_tasks();
        foreach ($tasks as $task)
        {
            $backup = new WPvivid_Backup_Task($task['id']);
            $list_tasks[$task['id']]=$backup->get_backup_task_info($task['id']);
            if($list_tasks[$task['id']]['task_info']['need_update_last_task']===true){
                $task_msg = WPvivid_taskmanager::get_task($task['id']);
                WPvivid_Setting::update_option('wpvivid_last_msg',$task_msg);
                apply_filters('wpvivid_set_backup_report_addon_mainwp', $task_msg);
            }
        }
        $ret['wpvivid']['task']=$list_tasks;
        $backuplist=WPvivid_Backuplist::get_backuplist();
        $schedule=WPvivid_Schedule::get_schedule();
        $ret['wpvivid']['backup_list']=$backuplist;
        $ret['wpvivid']['schedule']=$schedule;
        $ret['wpvivid']['schedule']['last_message']=WPvivid_Setting::get_last_backup_message('wpvivid_last_msg');
        WPvivid_taskmanager::delete_marked_task();
        return $ret;
    }

    public function wpvivid_get_backup_list_mainwp($data){
        $backuplist=WPvivid_Backuplist::get_backuplist();
        $ret['result']='success';
        $ret['wpvivid']['backup_list']=$backuplist;
        return $ret;
    }

    public function wpvivid_get_backup_schedule_mainwp($data){
        $schedule=WPvivid_Schedule::get_schedule();
        $ret['result']='success';
        $ret['wpvivid']['schedule']=$schedule;
        $ret['wpvivid']['schedule']['last_message']=WPvivid_Setting::get_last_backup_message('wpvivid_last_msg');
        return $ret;
    }

    public function wpvivid_get_default_remote_mainwp($data){
        global $wpvivid_plugin;
        $ret['result']='success';
        $ret['remote_storage_type']=$wpvivid_plugin->function_realize->_get_default_remote_storage();
        return $ret;
    }

    public function wpvivid_prepare_backup_mainwp($data){
        $backup_options = $data['backup'];

        global $wpvivid_plugin;
        if(isset($backup_options)&&!empty($backup_options))
        {
            if (is_null($backup_options))
            {
                $ret['error']='Invalid parameter param:'.$backup_options;
                return $ret;
            }

            if(!isset($backup_options['type']))
            {
                $backup_options['type']='Manual';
            }

            if(!isset($backup_options['backup_files'])||empty($backup_options['backup_files']))
            {
                $ret['result']='failed';
                $ret['error']=__('A backup type is required.', 'wpvivid-backuprestore');
                return $ret;
            }

            if(!isset($backup_options['local'])||!isset($backup_options['remote']))
            {
                $ret['result']='failed';
                $ret['error']=__('Choose at least one storage location for backups.', 'wpvivid-backuprestore');
                return $ret;
            }

            if(empty($backup_options['local']) && empty($backup_options['remote']))
            {
                $ret['result']='failed';
                $ret['error']=__('Choose at least one storage location for backups.', 'wpvivid-backuprestore');
                return $ret;
            }

            if ($backup_options['remote'] === '1')
            {
                $remote_storage = WPvivid_Setting::get_remote_options();
                if ($remote_storage == false)
                {
                    $ret['result']='failed';
                    $ret['error'] = __('There is no default remote storage configured. Please set it up first.', 'wpvivid-backuprestore');
                    return $ret;
                }
            }

            if(apply_filters('wpvivid_need_clean_oldest_backup',true,$backup_options))
            {
                $wpvivid_plugin->clean_oldest_backup();
            }
            do_action('wpvivid_clean_oldest_backup',$backup_options);

            if($wpvivid_plugin->backup2->is_tasks_backup_running())
            {
                $ret['result']='failed';
                $ret['error']=__('A task is already running. Please wait until the running task is complete, and try again.', 'wpvivid-backuprestore');
                echo wp_json_encode($ret);
                die();
            }

            $settings=$wpvivid_plugin->backup2->get_backup_settings($backup_options);

            $backup=new WPvivid_Backup_Task_2();
            $ret=$backup->new_backup_task($backup_options,$settings);
        }
        else{
            $ret['error']='Error occurred while parsing the request data. Please try to run backup again.';
            return $ret;
        }
        return $ret;
    }

    public function wpvivid_backup_now_mainwp($data){
        global $wpvivid_plugin;
        try{
            $task_id = $data['task_id'];
            $task_id=sanitize_key($task_id);
            if (!isset($task_id)||empty($task_id)||!is_string($task_id))
            {
                $ret['result'] = 'failed';
                $ret['error']=__('Error occurred while parsing the request data. Please try to run backup again.', 'wpvivid-backuprestore');
                return $ret;
            }

            if ($wpvivid_plugin->backup2->is_tasks_backup_running($task_id))
            {
                $ret['result'] = 'failed';
                $ret['error'] = __('We detected that there is already a running backup task. Please wait until it completes then try again.', 'wpvivid-backuprestore');
                return $ret;
            }

            $wpvivid_plugin->backup2->update_backup_task_status($task_id,true,'running');
            $wpvivid_plugin->flush($task_id, true);
            $wpvivid_plugin->backup2->add_monitor_event($task_id);
            $wpvivid_plugin->backup2->task=new WPvivid_Backup_Task_2($task_id);
            $wpvivid_plugin->backup2->task->set_memory_limit();
            $wpvivid_plugin->backup2->task->set_time_limit();

            $wpvivid_plugin->wpvivid_log->OpenLogFile($wpvivid_plugin->backup2->task->task['options']['log_file_name']);
            $wpvivid_plugin->wpvivid_log->WriteLog('Start backing up.','notice');
            $wpvivid_plugin->wpvivid_log->WriteLogHander();

            if(!$wpvivid_plugin->backup2->task->is_backup_finished())
            {
                $ret=$wpvivid_plugin->backup2->backup();
                $wpvivid_plugin->backup2->task->clear_cache();
                if($ret['result']!='success')
                {
                    $wpvivid_plugin->wpvivid_log->WriteLog('Backup the file ends with an error '. $ret['error'],'error');
                    $wpvivid_plugin->backup2->task->update_backup_task_status(false,'error',false,false,$ret['error']);
                    do_action('wpvivid_handle_backup_2_failed', $task_id);
                    $wpvivid_plugin->backup2->clear_monitor_schedule($task_id);
                    $ret['result'] = 'failed';
                    $ret['error']='Backup the file ends with an error '. $ret['error'];
                    return $ret;
                }
            }

            if($wpvivid_plugin->backup2->task->need_upload())
            {
                $ret=$wpvivid_plugin->backup2->upload($task_id);
                if($ret['result'] == WPVIVID_SUCCESS)
                {
                    do_action('wpvivid_handle_backup_2_succeed',$task_id);
                    $wpvivid_plugin->backup2->update_backup_task_status($task_id,false,'completed');
                }
                else
                {
                    $wpvivid_plugin->wpvivid_log->WriteLog('Uploading the file ends with an error '. $ret['error'], 'error');
                    do_action('wpvivid_handle_backup_2_failed',$task_id);
                }
            }
            else
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('Backup completed.','notice');
                do_action('wpvivid_handle_backup_2_succeed', $task_id);
                $wpvivid_plugin->backup2->update_backup_task_status($task_id,false,'completed');
            }
            $wpvivid_plugin->backup2->clear_monitor_schedule($task_id);
            $ret['result']='success';
            return $ret;
        }
        catch (Exception $error)
        {
            //catch error and stop task recording history
            $message = 'An exception has occurred. class:'.get_class($error).';msg:'.$error->getMessage().';code:'.$error->getCode().';line:'.$error->getLine().';in_file:'.$error->getFile().';';
            error_log($message);
            WPvivid_taskmanager::update_backup_task_status($task_id,false,'error',false,false,$message);
            $wpvivid_plugin->wpvivid_log->WriteLog($message,'error');
            do_action('wpvivid_handle_backup_2_failed',$task_id);
            $ret['result'] = 'failed';
            $ret['error']=$message;
            return $ret;
        }
    }

    public function wpvivid_view_backup_task_log_mainwp($data){
        $backup_task_id = $data['id'];
        global $wpvivid_plugin;
        if (!isset($backup_task_id)||empty($backup_task_id)||!is_string($backup_task_id)){
            $ret['error']='Reading the log failed. Please try again.';
            return $ret;
        }
        $backup_task_id = sanitize_key($backup_task_id);
        $ret=$wpvivid_plugin->function_realize->_get_log_file('tasklog', $backup_task_id);
        if($ret['result'] == 'success') {
            $file = fopen($ret['log_file'], 'r');
            if (!$file) {
                $ret['result'] = 'failed';
                $ret['error'] = __('Unable to open the log file.', 'wpvivid-backuprestore');
                return $ret;
            }
            $buffer = '';
            while (!feof($file)) {
                $buffer .= fread($file, 1024);
            }
            fclose($file);
            $ret['result'] = 'success';
            $ret['data'] = $buffer;
        }
        else{
            $ret['error']='Unknown error';
        }
        return $ret;
    }

    public function wpvivid_backup_cancel_mainwp($data){
        global $wpvivid_plugin;
        $ret=$wpvivid_plugin->function_realize->_backup_cancel();
        return $ret;
    }

    public function wpvivid_set_backup_report_addon_mainwp($data){
        if(isset($data['id']))
        {
            $task_id = $data['id'];
            $option = array();
            $option[$task_id]['task_id'] = $task_id;
            $option[$task_id]['backup_time'] = $data['status']['start_time'];
            if($data['status']['str'] == 'completed'){
                $option[$task_id]['status'] = 'Succeeded';
            }
            elseif($data['status']['str'] == 'error'){
                $option[$task_id]['status'] = 'Failed, '.$data['status']['error'];
            }
            elseif($data['status']['str'] == 'cancel'){
                $option[$task_id]['status'] = 'Canceled';
            }
            else{
                $option[$task_id]['status'] = 'The last backup message not found.';
            }

            $backup_reports = get_option('wpvivid_backup_reports', array());
            if(!empty($backup_reports)){
                foreach ($option as $key => $value){
                    $backup_reports[$key] = $value;
                    update_option('wpvivid_backup_reports', $backup_reports);
                }
            }
            else{
                update_option('wpvivid_backup_reports', $option);
            }
        }
    }

    public function wpvivid_read_last_backup_log_mainwp($data){
        $log_file_name = $data['log_file_name'];
        global $wpvivid_plugin;
        if(!isset($log_file_name)||empty($log_file_name)||!is_string($log_file_name))
        {
            $ret['result']='failed';
            $ret['error']=__('Reading the log failed. Please try again.', 'wpvivid-backuprestore');
            return $ret;
        }
        $log_file_name=sanitize_text_field($log_file_name);
        $ret=$wpvivid_plugin->function_realize->_get_log_file('lastlog', $log_file_name);
        if($ret['result'] == 'success') {
            $file = fopen($ret['log_file'], 'r');
            if (!$file) {
                $ret['result'] = 'failed';
                $ret['error'] = __('Unable to open the log file.', 'wpvivid-backuprestore');
                return $ret;
            }
            $buffer = '';
            while (!feof($file)) {
                $buffer .= fread($file, 1024);
            }
            fclose($file);
            $ret['result'] = 'success';
            $ret['data'] = $buffer;
        }
        else{
            $ret['error']='Unknown error';
        }
        return $ret;
    }

    public function wpvivid_set_security_lock_mainwp($data){
        $backup_id = $data['backup_id'];
        $lock = $data['lock'];
        if(!isset($backup_id)||empty($backup_id)||!is_string($backup_id)){
            $ret['error']='Backup id not found';
            return $ret;
        }
        if(!isset($lock)){
            $ret['error']='Invalid parameter param: lock';
            return $ret;
        }
        $backup_id=sanitize_key($backup_id);
        if($lock==0||$lock==1) {
        }
        else {
            $lock=0;
        }
        WPvivid_Backuplist::set_security_lock($backup_id,$lock);
        $backuplist=WPvivid_Backuplist::get_backuplist();
        $ret['wpvivid']['backup_list']=$backuplist;
        return $ret;
    }

    public function wpvivid_view_log_mainwp($data){
        $backup_id = $data['id'];
        global $wpvivid_plugin;
        if (!isset($backup_id)||empty($backup_id)||!is_string($backup_id)){
            $ret['error']='Backup id not found';
            return $ret;
        }
        $backup_id=sanitize_key($backup_id);
        $ret=$wpvivid_plugin->function_realize->_get_log_file('backuplist', $backup_id);
        if($ret['result'] == 'success') {
            $file = fopen($ret['log_file'], 'r');
            if (!$file) {
                $ret['result'] = 'failed';
                $ret['error'] = __('Unable to open the log file.', 'wpvivid-backuprestore');
                return $ret;
            }
            $buffer = '';
            while (!feof($file)) {
                $buffer .= fread($file, 1024);
            }
            fclose($file);
            $ret['data'] = $buffer;
        }
        else{
            $ret['error']='Unknown error';
        }
        return $ret;
    }

    public function wpvivid_init_download_page_mainwp($data){
        $backup_id = $data['backup_id'];
        global $wpvivid_plugin;
        if(!isset($backup_id)||empty($backup_id)||!is_string($backup_id)) {
            $ret['error']='Invalid parameter param:'.$backup_id;
            return $ret;
        }
        else {
            $backup_id=sanitize_key($backup_id);
            return $wpvivid_plugin->init_download($backup_id);
        }
    }

    public function wpvivid_prepare_download_backup_mainwp($data){
        $backup_id = $data['backup_id'];
        $file_name = $data['file_name'];
        if(!isset($backup_id)||empty($backup_id)||!is_string($backup_id))
        {
            $ret['error']='Invalid parameter param:'.$backup_id;
            return $ret;
        }
        if(!isset($file_name)||empty($file_name)||!is_string($file_name))
        {
            $ret['error']='Invalid parameter param:'.$file_name;
            return $ret;
        }
        $download_info=array();
        $download_info['backup_id']=sanitize_key($backup_id);
        $download_info['file_name'] = $file_name;

        @set_time_limit(600);
        if (session_id())
            session_write_close();
        try
        {
            $downloader=new WPvivid_downloader();
            $downloader->ready_download($download_info);
        }
        catch (Exception $e)
        {
            $message = 'A exception ('.get_class($e).') occurred '.$e->getMessage().' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().')';
            error_log($message);
            return array('error'=>$message);
        }
        catch (Error $e)
        {
            $message = 'A error ('.get_class($e).') has occurred: '.$e->getMessage().' (Code: '.$e->getCode().', line '.$e->getLine().' in '.$e->getFile().')';
            error_log($message);
            return array('error'=>$message);
        }

        $ret['result']='success';
        return $ret;
    }

    public function wpvivid_get_download_task_mainwp($data){
        $backup_id = $data['backup_id'];
        if(!isset($backup_id)||empty($backup_id)||!is_string($backup_id)) {
            $ret['error']='Invalid parameter param:'.$backup_id;
            return $ret;
        }
        else {
            $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
            if ($backup === false) {
                $ret['result'] = WPVIVID_FAILED;
                $ret['error'] = 'backup id not found';
                return $ret;
            }
            $backup_item = new WPvivid_Backup_Item($backup);
            $ret = $backup_item->update_download_page($backup_id);
            return $ret;
        }
    }

    public function wpvivid_download_backup_mainwp($data){
        $backup_id = $data['backup_id'];
        $file_name = $data['file_name'];
        global $wpvivid_plugin;
        if(!isset($backup_id)||empty($backup_id)||!is_string($backup_id)) {
            $ret['error']='Invalid parameter param: backup_id';
            return $ret;
        }
        if(!isset($file_name)||empty($file_name)||!is_string($file_name)) {
            $ret['error']='Invalid parameter param: file_name';
            return $ret;
        }
        $backup_id=sanitize_key($backup_id);
        $cache=WPvivid_taskmanager::get_download_cache($backup_id);
        if($cache===false) {
            $wpvivid_plugin->init_download($backup_id);
            $cache=WPvivid_taskmanager::get_download_cache($backup_id);
        }
        $path=false;
        if(array_key_exists($file_name,$cache['files'])) {
            if($cache['files'][$file_name]['status']=='completed') {
                $path=$cache['files'][$file_name]['download_path'];
                $download_url = $cache['files'][$file_name]['download_url'];
            }
        }
        if($path!==false) {
            if (file_exists($path)) {
                $ret['download_url'] = $download_url;
                $ret['size'] = filesize($path);
            }
        }
        return $ret;
    }

    public function wpvivid_delete_backup_mainwp($data){
        $backup_id = $data['backup_id'];
        $force_del = $data['force'];
        global $wpvivid_plugin;
        if(!isset($backup_id)||empty($backup_id)||!is_string($backup_id)) {
            $ret['error']='Invalid parameter param: backup_id.';
            return $ret;
        }
        if(!isset($force_del)){
            $ret['error']='Invalid parameter param: force.';
            return $ret;
        }
        if($force_del==0||$force_del==1) {
        }
        else {
            $force_del=0;
        }
        $backup_id=sanitize_key($backup_id);
        $ret=$wpvivid_plugin->delete_backup_by_id($backup_id, $force_del);
        $backuplist=WPvivid_Backuplist::get_backuplist();
        $ret['wpvivid']['backup_list']=$backuplist;
        return $ret;
    }

    public function wpvivid_delete_backup_array_mainwp($data){
        $backup_id_array = $data['backup_id'];
        global $wpvivid_plugin;
        if(!isset($backup_id_array)||empty($backup_id_array)||!is_array($backup_id_array)) {
            $ret['error']='Invalid parameter param: backup_id';
            return $ret;
        }
        $ret=array();
        foreach($backup_id_array as $backup_id)
        {
            $backup_id=sanitize_key($backup_id);
            $ret=$wpvivid_plugin->delete_backup_by_id($backup_id);
        }
        $backuplist=WPvivid_Backuplist::get_backuplist();
        $ret['wpvivid']['backup_list']=$backuplist;
        return $ret;
    }

    public function wpvivid_set_schedule_mainwp($data){
        $schedule = $data['schedule'];
        $ret=array();
        try {
            if(isset($schedule)&&!empty($schedule)) {
                $json = $schedule;
                $json = stripslashes($json);
                $schedule = json_decode($json, true);
                if (is_null($schedule)) {
                    $ret['error']='bad parameter';
                    return $ret;
                }
                $ret=WPvivid_Schedule::set_schedule_ex($schedule);
                if($ret['result']!='success') {
                    return $ret;
                }
            }
            $ret['result']='success';
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            return array('error'=>$message);
        }
        return $ret;
    }

    public function wpvivid_set_general_setting_mainwp($data){
        $setting = $data['setting'];
        $ret=array();
        try {
            if(isset($setting)&&!empty($setting)) {
                $json_setting = $setting;
                $json_setting = stripslashes($json_setting);
                $setting = json_decode($json_setting, true);
                if (is_null($setting)) {
                    $ret['error']='bad parameter';
                    return $ret;
                }

                if(isset($setting['wpvivid_compress_setting']['max_file_size']))
                {
                    $setting['wpvivid_common_setting']['max_file_size'] = str_replace('M', '', $setting['wpvivid_compress_setting']['max_file_size']);
                }

                if(isset($setting['wpvivid_compress_setting']['exclude_file_size']))
                {
                    $setting['wpvivid_common_setting']['exclude_file_size'] = $setting['wpvivid_compress_setting']['exclude_file_size'];
                }

                WPvivid_Setting::update_setting($setting);
            }

            $ret['result']='success';
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            return array('error'=>$message);
        }
        return $ret;
    }

    public function wpvivid_set_remote_mainwp($data){
        $remote = $data['remote'];
        global $wpvivid_plugin;
        $ret=array();
        try {
            if(isset($remote)&&!empty($remote)) {
                $json = $remote;
                $json = stripslashes($json);
                $remote = json_decode($json, true);
                if (is_null($remote)) {
                    $ret['error']='bad parameter';
                    return $ret;
                }
                $wpvivid_plugin->function_realize->_set_remote($remote);
            }
            $ret['result']='success';
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            return array('error'=>$message);
        }
        return $ret;
    }
}