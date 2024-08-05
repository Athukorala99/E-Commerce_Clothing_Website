<?php

if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

class WPvivid_Backup_2
{
    public $end_shutdown_function;
    public $current_task_id;
    public $task;
    public function __construct()
    {
        include_once WPVIVID_PLUGIN_DIR . '/includes/new_backup/class-wpvivid-backup-task_2.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/new_backup/class-wpvivid-mysqldump2.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/new_backup/class-wpvivid-zip.php';

        add_action('wp_ajax_wpvivid_prepare_backup_2',array( $this,'prepare_backup_2'));
        add_action('wp_ajax_wpvivid_delete_ready_task_2',array($this,'delete_ready_task_2'));

        add_action('wp_ajax_wpvivid_backup_now_2',array( $this,'backup_now_2'));
        add_action('wp_ajax_wpvivid_list_tasks_2',array( $this,'list_tasks'));

        add_action('wp_ajax_wpvivid_shutdown_backup',array( $this,'shutdown_backup'));
        add_action('wp_ajax_wpvivid_delete_task_2',array( $this,'delete_task'));
        add_action('wpvivid_task_monitor_event_2',array( $this,'task_monitor'));
        add_action('wpvivid_backup_2_schedule_event',array( $this,'backup_schedule'));
        //
        add_action('wpvivid_handle_backup_2_succeed',array($this,'handle_backup_succeed'),10);
        add_action('wpvivid_handle_backup_2_failed',array($this,'handle_backup_failed'),10);
        //
        add_action('wpvivid_clean_backup_2_data_event',array($this,'clean_backup_data_event'));
        //
        add_action(WPVIVID_MAIN_SCHEDULE_EVENT,array( $this,'main_schedule'));
        //
        add_filter('wpvivid_exclude_plugins',array($this,'exclude_plugins'),10);
        //migrate
        add_action('wp_ajax_wpvivid_send_backup_to_site_2',array( $this,'send_backup_to_site'));
        add_action('wp_ajax_wpvivid_migrate_now_2',array( $this,'migrate_now'));
        //
        add_filter('wpvivid_default_exclude_folders' ,array($this, 'default_exclude_folders'));
    }

    public function exclude_plugins($exclude_plugins)
    {
        $exclude_plugins[]='wpvivid-backuprestore';
        $exclude_plugins[]='wp-cerber';
        $exclude_plugins[]='.';
        $exclude_plugins[]='wpvivid-backup-pro';
        $exclude_plugins[]='wpvividdashboard';
        //$exclude_plugins[]='wpvivid-staging';
        return $exclude_plugins;
    }

    public function prepare_backup_2()
    {
        global $wpvivid_plugin;
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        try
        {
            if(isset($_POST['backup'])&&!empty($_POST['backup']))
            {
                $json = sanitize_text_field($_POST['backup']);
                $json = stripslashes($json);
                $backup_options = json_decode($json, true);
                if (is_null($backup_options))
                {
                    die();
                }

                if(!isset($backup_options['type']))
                {
                    $backup_options['type']='Manual';
                }

                if(!isset($backup_options['backup_files'])||empty($backup_options['backup_files']))
                {
                    $ret['result']='failed';
                    $ret['error']=__('A backup type is required.', 'wpvivid-backuprestore');
                    echo wp_json_encode($ret);
                    die();
                }

                if(!isset($backup_options['local'])||!isset($backup_options['remote']))
                {
                    $ret['result']='failed';
                    $ret['error']=__('Choose at least one storage location for backups.', 'wpvivid-backuprestore');
                    echo wp_json_encode($ret);
                    die();
                }

                if(empty($backup_options['local']) && empty($backup_options['remote']))
                {
                    $ret['result']='failed';
                    $ret['error']=__('Choose at least one storage location for backups.', 'wpvivid-backuprestore');
                    echo wp_json_encode($ret);
                    die();
                }

                if ($backup_options['remote'] === '1')
                {
                    $remote_storage = WPvivid_Setting::get_remote_options();
                    if ($remote_storage == false)
                    {
                        $ret['result']='failed';
                        $ret['error'] = __('There is no default remote storage configured. Please set it up first.', 'wpvivid-backuprestore');
                        echo wp_json_encode($ret);
                        die();
                    }
                }

                if(apply_filters('wpvivid_need_clean_oldest_backup',true,$backup_options))
                {
                    $wpvivid_plugin->clean_oldest_backup();
                }
                do_action('wpvivid_clean_oldest_backup',$backup_options);

                if($this->is_tasks_backup_running())
                {
                    $ret['result']='failed';
                    $ret['error']=__('A task is already running. Please wait until the running task is complete, and try again.', 'wpvivid-backuprestore');
                    echo wp_json_encode($ret);
                    die();
                }

                $settings=$this->get_backup_settings($backup_options);

                $backup=new WPvivid_Backup_Task_2();
                $ret=$backup->new_backup_task($backup_options,$settings);

                if($ret['result']=='success')
                {
                    $html = '';
                    $html = apply_filters('wpvivid_add_backup_list', $html);
                    $ret['html'] = $html;
                }

                echo wp_json_encode($ret);
                die();
            }
        }
        catch (Exception $error)
        {
            $ret['result']='failed';
            $message = 'An exception has occurred. class:'.get_class($error).';msg:'.$error->getMessage().';code:'.$error->getCode().';line:'.$error->getLine().';in_file:'.$error->getFile().';';
            $ret['error'] = $message;
            $id=uniqid('wpvivid-');
            $log_file_name=$id.'_backup';
            $log=new WPvivid_Log();
            $log->CreateLogFile($log_file_name,'no_folder','backup');
            $log->WriteLog($message,'notice');
            $log->CloseFile();
            WPvivid_error_log::create_error_log($log->log_file);
            error_log($message);
            echo wp_json_encode($ret);
            die();
        }
    }

    public function delete_ready_task_2()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        $tasks = get_option('wpvivid_task_list', array());
        $delete_ids=array();
        foreach ($tasks as $task)
        {
            if($task['status']['str']=='ready')
            {
                $delete_ids[]=$task['id'];
            }
        }

        if(!empty($delete_ids))
        {
            foreach ($delete_ids as $id)
            {
                unset($tasks[$id]);
            }
            update_option('wpvivid_task_list',$tasks);
        }

        $ret['result'] = 'success';
        echo wp_json_encode($ret);
        die();
    }

    public function is_tasks_backup_running($task_id='')
    {
        $tasks = get_option('wpvivid_task_list', array());

        if(empty($task_id))
        {
            foreach ($tasks as $task)
            {
                if ($task['status']['str']=='running'||$task['status']['str']=='no_responds')
                {
                    return true;
                }
            }
            return false;
        }
        else
        {
            if(isset($tasks[$task_id]))
            {
                $task=$tasks[$task_id];
                if ($task['status']['str']=='running'||$task['status']['str']=='no_responds')
                {
                    return true;
                }
            }
            return false;
        }
    }

    public function get_backup_settings($backup_options)
    {
        $common_setting=get_option('wpvivid_common_setting',array());
        $settings['db_connect_method']=isset($common_setting['db_connect_method'])?$common_setting['db_connect_method']:'wpdb';
        $settings['memory_limit']=isset($common_setting['memory_limit'])?$common_setting['memory_limit']:'256M';
        $settings['max_execution_time']=isset($common_setting['max_execution_time'])?$common_setting['max_execution_time']:900;
        $settings['compress_file_use_cache']=isset($common_setting['compress_file_use_cache'])?$common_setting['compress_file_use_cache']:false;
        $settings['compress_file_count']=isset($common_setting['compress_file_count'])?$common_setting['compress_file_count']:500;
        $settings['max_file_size']=isset($common_setting['max_file_size'])?$common_setting['max_file_size']:200;
        $settings['max_sql_file_size']=isset($common_setting['max_sql_file_size'])?$common_setting['max_sql_file_size']:200;
        $settings['exclude_file_size']=isset($common_setting['exclude_file_size'])?$common_setting['exclude_file_size']:0;
        $settings['max_resume_count']=isset($common_setting['max_resume_count'])?$common_setting['max_resume_count']:6;
        $settings['zip_method']=isset($common_setting['zip_method'])?$common_setting['zip_method']:6;
        $settings['is_merge']=isset($common_setting['ismerge'])?$common_setting['ismerge']:true;
        $settings['save_local']=isset($common_setting['retain_local'])?$common_setting['retain_local']:false;

        if(isset($common_setting['zip_method']))
        {
            if($common_setting['zip_method'] === 'ziparchive')
            {
                $settings['zip_method']= 'ziparchive';
            }
            else{
                $settings['zip_method']= 'pclzip';
            }
        }
        else
        {
            if(class_exists('ZipArchive'))
            {
                if(method_exists('ZipArchive', 'addFile'))
                {
                    $settings['zip_method']= 'ziparchive';
                }
                else
                {
                    $settings['zip_method']= 'pclzip';
                }
            }
            else
            {
                $settings['zip_method']= 'pclzip';
            }
        }

        return $settings;
    }

    public function main_schedule($schedule_id='')
    {
        global $wpvivid_plugin;

        do_action('wpvivid_set_current_schedule_id', $schedule_id);

        $schedule_options=WPvivid_Schedule::get_schedule($schedule_id);
        if(empty($schedule_options))
        {
            die();
        }

        $schedule_options['backup']['local'] = strval($schedule_options['backup']['local']);
        $schedule_options['backup']['remote'] = strval($schedule_options['backup']['remote']);
        $schedule_options['backup']['ismerge'] = strval($schedule_options['backup']['ismerge']);
        $schedule_options['backup']['lock'] = strval($schedule_options['backup']['lock']);

        if(!isset($schedule_options['backup']['type']))
        {
            $schedule_options['backup']['type']='Cron';
            $schedule_options['backup']['action']='backup';
        }


        $ret = $this->pre_new_backup($schedule_options['backup']);
        if ($ret['result'] == 'success')
        {
            $wpvivid_plugin->flush($ret['task_id']);
            //start backup task.
            $task_msg = WPvivid_taskmanager::get_task($ret['task_id']);
            $wpvivid_plugin->update_last_backup_time($task_msg);

            $this->backup_schedule($ret['task_id']);
        }
        $this->end_shutdown_function=true;
        die();
    }

    public function pre_new_backup($backup_options)
    {
        global $wpvivid_plugin;

        if(apply_filters('wpvivid_need_clean_oldest_backup',true,$backup_options))
        {
            $wpvivid_plugin->clean_oldest_backup();
        }
        do_action('wpvivid_clean_oldest_backup',$backup_options);

        if($this->is_tasks_backup_running())
        {
            $ret['result']='failed';
            $ret['error']=__('We detected that there is already a running backup task. Please wait until it completes then try again.', 'wpvivid');
            return $ret;
        }

        $settings=$this->get_backup_settings($backup_options);

        $backup=new WPvivid_Backup_Task_2();
        $ret=$backup->new_backup_task($backup_options,$settings);

        return $ret;
    }

    public function backup_now_2()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        register_shutdown_function(array($this,'deal_backup_shutdown_error'));
        $this->end_shutdown_function=false;

        $task_id = sanitize_key($_POST['task_id']);
        $this->current_task_id=$task_id;
        global $wpvivid_plugin;

        if ($this->is_tasks_backup_running($task_id))
        {
            $ret['result'] = 'failed';
            $ret['error'] = __('We detected that there is already a running backup task. Please wait until it completes then try again.', 'wpvivid-backuprestore');
            echo wp_json_encode($ret);
            die();
        }

        try
        {
            $this->update_backup_task_status($task_id,true,'running');
            $wpvivid_plugin->flush($task_id);
            $this->add_monitor_event($task_id);
            $this->task=new WPvivid_Backup_Task_2($task_id);
            $this->task->set_memory_limit();
            $this->task->set_time_limit();

            $wpvivid_plugin->wpvivid_log->OpenLogFile($this->task->task['options']['log_file_name']);
            $wpvivid_plugin->wpvivid_log->WriteLog('Start backing up.','notice');
            $wpvivid_plugin->wpvivid_log->WriteLogHander();

            if(!$this->task->is_backup_finished())
            {
                $ret=$this->backup();
                $this->task->clear_cache();
                if($ret['result']!='success')
                {
                    $wpvivid_plugin->wpvivid_log->WriteLog('Backup the file ends with an error '. $ret['error'],'error');
                    $this->task->update_backup_task_status(false,'error',false,false,$ret['error']);
                    do_action('wpvivid_handle_backup_2_failed', $task_id);
                    $this->end_shutdown_function=true;
                    $this->clear_monitor_schedule($task_id);
                    die();
                }
            }

            if($this->task->need_upload())
            {
                $ret=$this->upload($task_id);
                if($ret['result'] == WPVIVID_SUCCESS)
                {
                    do_action('wpvivid_handle_backup_2_succeed',$task_id);
                    $this->update_backup_task_status($task_id,false,'completed');
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
                $this->update_backup_task_status($task_id,false,'completed');
            }
            $this->clear_monitor_schedule($task_id);
        }
        catch (Exception $error)
        {
            //catch error and stop task recording history
            $message = 'An exception has occurred. class:'.get_class($error).';msg:'.$error->getMessage().';code:'.$error->getCode().';line:'.$error->getLine().';in_file:'.$error->getFile().';';
            error_log($message);
            WPvivid_taskmanager::update_backup_task_status($task_id,false,'error',false,false,$message);
            $wpvivid_plugin->wpvivid_log->WriteLog($message,'error');
            do_action('wpvivid_handle_backup_2_failed',$task_id);
            $this->end_shutdown_function=true;
            die();
        }


        $this->end_shutdown_function=true;

        die();
    }

    public function backup_schedule($task_id)
    {
        $this->current_task_id=$task_id;
        if(empty($task_id))
        {
            die();
        }

        if ($this->is_tasks_backup_running($task_id))
        {
            $ret['result'] = 'failed';
            $ret['error'] = __('We detected that there is already a running backup task. Please wait until it completes then try again.', 'wpvivid-backuprestore');
            echo wp_json_encode($ret);
            die();
        }
        $this->end_shutdown_function=false;
        register_shutdown_function(array($this,'deal_backup_shutdown_error'));
        global $wpvivid_plugin;
        try
        {
            WPvivid_taskmanager::update_backup_task_status($task_id,true,'running');
            $wpvivid_plugin->flush($task_id);
            $this->add_monitor_event($task_id);
            $this->task=new WPvivid_Backup_Task_2($task_id);
            $this->task->set_memory_limit();
            $this->task->set_time_limit();

            $this->task->update_schedule_last_backup_time();

            $wpvivid_plugin->wpvivid_log->OpenLogFile(WPvivid_taskmanager::get_task_options($task_id,'log_file_name'));
            $wpvivid_plugin->wpvivid_log->WriteLog('Start backing up.','notice');
            $wpvivid_plugin->wpvivid_log->WriteLogHander();

            if(!$this->task->is_backup_finished())
            {
                $ret=$this->backup();
                $this->task->clear_cache();
                if($ret['result']!='success')
                {
                    $wpvivid_plugin->wpvivid_log->WriteLog('Backup the file ends with an error '. $ret['error'],'error');
                    $this->task->update_backup_task_status(false,'error',false,false,$ret['error']);
                    do_action('wpvivid_handle_backup_2_failed', $task_id);
                    $this->end_shutdown_function=true;
                    $this->clear_monitor_schedule($task_id);
                    die();
                }
            }

            if($this->task->need_upload())
            {
                $ret=$this->upload($task_id);
                if($ret['result'] == WPVIVID_SUCCESS)
                {
                    do_action('wpvivid_handle_backup_2_succeed',$task_id);
                    WPvivid_taskmanager::update_backup_task_status($task_id,false,'completed');
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
                WPvivid_taskmanager::update_backup_task_status($task_id,false,'completed');
            }
            $this->clear_monitor_schedule($task_id);
        }
        catch (Exception $error)
        {
            //catch error and stop task recording history
            $message = 'An exception has occurred. class:'.get_class($error).';msg:'.$error->getMessage().';code:'.$error->getCode().';line:'.$error->getLine().';in_file:'.$error->getFile().';';
            error_log($message);
            WPvivid_taskmanager::update_backup_task_status($task_id,false,'error',false,false,$message);
            $wpvivid_plugin->wpvivid_log->WriteLog($message,'error');
            do_action('wpvivid_handle_backup_2_failed',$task_id);
            $this->end_shutdown_function=true;
            die();
        }

        $this->end_shutdown_function=true;

        die();
    }

    public function backup()
    {
        $ret['result']='success';

        $this->task->wpvivid_check_add_litespeed_server();

        while (!$this->task->is_backup_finished())
        {
            if($this->task->check_cancel_backup())
            {
                $this->end_shutdown_function=true;
                die();
            }

            $job=$this->task->get_next_job();

            if($job===false)
                break;

            $this->task->set_time_limit();
            $ret=$this->task->do_backup_job($job);
            if($ret['result']!='success')
            {
                break;
            }
        }

        if($ret['result']==='success')
        {
            $check_res = apply_filters('wpvivid_check_backup_completeness', true, $this->task->task_id);
            if(!$check_res){
                $ret['result'] = 'failed';
                $ret['error'] = 'We have detected that this backup is either corrupted or incomplete. Please make sure your server disk space is sufficient then create a new backup. In order to successfully back up/restore a website, the amount of free server disk space needs to be at least twice the size of the website';
            }
        }

        return $ret;
    }

    public function upload($task_id)
    {
        global $wpvivid_plugin;

        $files=$this->task->get_backup_files();
        $wpvivid_plugin->wpvivid_log->WriteLog('files: '.wp_json_encode($files),'notice');
        $remote_options=$this->task->get_remote_options();

        $remote_option=array_shift($remote_options);

        if(!class_exists('WPvivid_Remote_collection'))
        {
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-remote-collection.php';
            $wpvivid_plugin->remote_collection=new WPvivid_Remote_collection();
        }
        $remote=$wpvivid_plugin->remote_collection->get_remote($remote_option);

        try
        {
            $result=$remote->upload($task_id,$files,array($this,'upload_callback'));
            if($result['result']=='success')
            {
                $this->update_backup_task_status($task_id,false,'running',false,0);
                $wpvivid_plugin->wpvivid_log->WriteLog('Finish upload to '.$remote_option['type'],'notice');
                if($remote_option['type']!=='send_to_site')
                {
                    WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',$remote_option['id'],WPVIVID_UPLOAD_SUCCESS,'Finish upload to'.$remote_option['type']);
                }
                WPvivid_taskmanager::update_backup_main_task_progress($task_id,'upload',100,1);
                WPvivid_taskmanager::update_backup_task_status($task_id,false,'completed');
                return array('result' => 'success');
            }
            else
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('Finish upload to '.$remote_option['type'].' error:'.$result['error'],'notice');
                if($remote_option['type']!=='send_to_site')
                {
                    WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',$remote_option['id'],WPVIVID_UPLOAD_FAILED,'Finish upload to'.$remote_option['type']);
                }
                $remote ->cleanup($files);
                $last_error=$result['error'];
                WPvivid_taskmanager::update_backup_task_status($task_id,false,'error',false,false,$last_error);
                return array('result' => 'failed' , 'error' => $last_error);
            }
        }
        catch (Exception $e)
        {
            //catch error and stop task recording history
            $wpvivid_plugin->wpvivid_log->WriteLog('Finish upload to '.$remote_option['type'].' error:'.$e->getMessage(),'notice');
            WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',$remote_option['id'],WPVIVID_UPLOAD_FAILED,'Finish upload to'.$remote_option['type']);
            $last_error=$e->getMessage();

            WPvivid_taskmanager::update_backup_task_status($task_id,false,'error',false,false,$last_error);
            return array('result' => 'failed' , 'error' => $last_error);
        }

    }

    public function upload_callback($offset,$current_name,$current_size,$last_time,$last_size)
    {
        $job_data=array();
        $upload_data=array();
        $upload_data['offset']=$offset;
        $upload_data['current_name']=$current_name;
        $upload_data['current_size']=$current_size;
        $upload_data['last_time']=$last_time;
        $upload_data['last_size']=$last_size;
        $upload_data['descript']='Uploading '.$current_name;
        $v =( $offset - $last_size ) / (time() - $last_time);
        $v /= 1000;
        $v=round($v,2);

        global $wpvivid_plugin;
        $this->task->check_cancel_backup();

        $message='Uploading '.$current_name.' Total size: '.size_format($current_size,2).' Uploaded: '.size_format($offset,2).' speed:'.$v.'kb/s';
        $wpvivid_plugin->wpvivid_log->WriteLog($message,'notice');
        $progress=intval(($offset/$current_size)*100);
        WPvivid_taskmanager::update_backup_main_task_progress($this->current_task_id,'upload',$progress,0);
        WPvivid_taskmanager::update_backup_sub_task_progress($this->current_task_id,'upload','',WPVIVID_UPLOAD_UNDO,$message, $job_data, $upload_data);
    }

    public function handle_backup_succeed($task_id)
    {
        $task= new WPvivid_Backup_Task_2($task_id);
        $task->update_end_time();
        if($task->task['type']=='Migrate')
        {
            $backup_success_count = WPvivid_Setting::get_option('wpvivid_transfer_success_count');
            if (empty($backup_success_count))
            {
                $backup_success_count = 0;
            }
            $backup_success_count++;
            WPvivid_Setting::update_option('wpvivid_transfer_success_count', $backup_success_count);

            global $wpvivid_plugin;
            $wpvivid_plugin->wpvivid_log->WriteLog('Upload finished. Delete task '.$task->task['id'], 'notice');
            $task->clean_local_files();

            $task->wpvivid_check_clear_litespeed_rule();
            $this->clear_monitor_schedule($task_id);
        }
        else
        {
            $backup=WPvivid_Backuplist::get_backup_by_id($task_id);
            if($backup!==false)
            {
                $task->add_exist_backup($task_id);
            }
            else
            {
                $task->add_new_backup();
            }

            if($task->need_upload())
            {
                if(!$task->is_save_local())
                {
                    $task->clean_local_files();
                }

                $remote_options=$this->task->get_remote_options();

                WPvivid_Backuplist::update_backup($task_id,'remote', $remote_options);
            }

            set_time_limit(120);
            $backup_ids=array();
            $backup_ids=apply_filters('wpvivid_get_oldest_backup_ids',$backup_ids,true);
            global $wpvivid_plugin;
            if(!empty($backup_ids))
            {
                foreach ($backup_ids as $backup_id)
                {
                    $wpvivid_plugin->delete_backup_by_id($backup_id);
                }
            }

            $backup_success_count = WPvivid_Setting::get_option('wpvivid_backup_success_count');
            if (empty($backup_success_count))
            {
                $backup_success_count = 0;
            }
            $backup_success_count++;
            WPvivid_Setting::update_option('wpvivid_backup_success_count', $backup_success_count);

            $wpvivid_plugin->wpvivid_analysis_backup($task->task);
            $task_msg = WPvivid_taskmanager::get_task($task_id);
            $wpvivid_plugin->update_last_backup_task($task_msg);

            $task_msg = WPvivid_taskmanager::get_task($task_id);
            update_option('wpvivid_last_msg',$task_msg);

            $this->clear_monitor_schedule($task_id);

            WPvivid_taskmanager::mark_task($task_id);

            if(!class_exists('WPvivid_mail_report'))
                include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-mail-report.php';
            WPvivid_mail_report::send_report_mail_ex($task_id);

            $task->wpvivid_check_clear_litespeed_rule();
        }
    }

    public function handle_backup_failed($task_id)
    {
        global $wpvivid_plugin;

        $task = WPvivid_taskmanager::get_task($task_id);

        if($task['type']=='Migrate')
        {
            $backup_error_array = WPvivid_Setting::get_option('wpvivid_transfer_error_array');
            if (empty($backup_error_array)) {
                $backup_error_array = array();
            }
            if (!array_key_exists($task['id'], $backup_error_array['bu_error']))
            {
                $backup_error_array['bu_error']['task_id'] = $task['id'];
                $backup_error_array['bu_error']['error_msg'] = $task['status']['error'];
                WPvivid_Setting::update_option('wpvivid_transfer_error_array', $backup_error_array);
            }

            $new_task= new WPvivid_Backup_Task_2($task_id);
            $new_task->update_end_time();
            $new_task->clean_backup();
            $wpvivid_plugin->wpvivid_log->WriteLog('Upload failed. Delete task '.$task['id'], 'notice');
            $this->clear_monitor_schedule($task_id);
            $new_task->wpvivid_check_clear_litespeed_rule();
        }
        else
        {
            $backup_error_array = WPvivid_Setting::get_option('wpvivid_backup_error_array');
            if (!isset($backup_error_array) || empty($backup_error_array))
            {
                $backup_error_array = array();
                $backup_error_array['bu_error']['task_id'] = '';
                $backup_error_array['bu_error']['error_msg'] = '';
            }
            if (!array_key_exists($task_id, $backup_error_array['bu_error']))
            {
                $backup_error_array['bu_error']['task_id'] = $task_id;
                $backup_error_array['bu_error']['error_msg'] = 'Unknown error.';

                $general_setting=WPvivid_Setting::get_setting(true, "");
                $need_notice = false;
                if(!isset($general_setting['options']['wpvivid_compress_setting']['subpackage_plugin_upload'])){
                    $need_notice = true;
                }
                else{
                    if($general_setting['options']['wpvivid_compress_setting']['subpackage_plugin_upload']){
                        $need_notice = false;
                    }
                    else{
                        $need_notice = true;
                    }
                }
                if($need_notice)
                {
                    $notice_msg = 'Backup error: '.$task['status']['error'].', task id: '.$task['id'];
                    $backup_error_array['bu_error']['error_msg']='<div class="notice notice-error inline"><p>'.$notice_msg.', Please switch to <a href="#" onclick="wpvivid_click_switch_page(\'wrap\', \'wpvivid_tab_debug\', true);">Website Info</a> page to send us the debug information. </p></div>';
                }
                else{
                    $notice_msg = 'Backup error: ' . $task['status']['error'] . ', task id: ' . $task['id'];
                    $backup_error_array['bu_error']['error_msg'] = '<div class="notice notice-error inline"><p>' . $notice_msg . ', Please switch to <a href="#" onclick="wpvivid_click_switch_page(\'wrap\', \'wpvivid_tab_debug\', true);">Website Info</a> page to send us the debug information. </p></div>';
                }
            }

            WPvivid_Setting::update_option('wpvivid_backup_error_array', $backup_error_array);
            $task_msg = WPvivid_taskmanager::get_task($task_id);
            $wpvivid_plugin->update_last_backup_task($task_msg);

            $task= new WPvivid_Backup_Task_2($task_id);
            $task->update_end_time();
            $this->add_clean_backup_data_event($task_id);

            $task_msg = WPvivid_taskmanager::get_task($task_id);
            update_option('wpvivid_last_msg',$task_msg);

            global $wpvivid_plugin;
            if($wpvivid_plugin->wpvivid_log)
            {
                $wpvivid_plugin->wpvivid_log->WriteLog($task_msg['status']['error'],'error');
                $wpvivid_plugin->wpvivid_log->CloseFile();
                WPvivid_error_log::create_error_log($wpvivid_plugin->wpvivid_log->log_file);
            }

            $this->clear_monitor_schedule($task_id);
            if(!class_exists('WPvivid_mail_report'))
                include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-mail-report.php';
            WPvivid_mail_report::send_report_mail_ex($task_id);

            WPvivid_taskmanager::mark_task($task_id);

            $task->wpvivid_check_clear_litespeed_rule();
        }
    }

    public function deal_backup_shutdown_error()
    {
        if($this->end_shutdown_function===false)
        {
            global $wpvivid_plugin;
            $options = get_option('wpvivid_task_list',array());
            if(!isset($options[$this->current_task_id]))
            {
                die();
            }

            $error = error_get_last();
            $resume_backup=false;
            $memory_limit=false;
            $max_execution_time=false;

            if (!is_null($error))
            {
                if (empty($error) || !in_array($error['type'], array(E_ERROR,E_RECOVERABLE_ERROR,E_CORE_ERROR,E_COMPILE_ERROR), true))
                {
                    $wpvivid_plugin->wpvivid_log->WriteLog('In shutdown function last message type:'.$error['type'].' str:'.$error['message'],'notice');
                }

                if(preg_match('/Allowed memory size of.*$/', $error['message']))
                {
                    $resume_backup=true;
                    $memory_limit=true;
                }
                else if(preg_match('/Maximum execution time of.*$/', $error['message']))
                {
                    $resume_backup=true;
                    $max_execution_time=true;
                }
            }

            $task= new WPvivid_Backup_Task_2($this->current_task_id);
            $status=$task->get_status();
            if($memory_limit===true)
            {
                if(!$task->check_memory_limit())
                {
                    $wpvivid_plugin->wpvivid_log->WriteLog('Backup the file ends with an error '. $error['message'],'error');
                    $task->update_backup_task_status(false,'error',false,$status['resume_count'],$error['message']);
                    do_action('wpvivid_handle_backup_2_failed', $this->current_task_id);
                    $resume_backup=false;
                }
            }

            if($max_execution_time===true)
            {
                $task->check_execution_time();
            }

            if($status['str']!='completed')
            {
                $max_resume_count=$task->get_max_resume_count();
                $status=$task->get_status();
                $status['resume_count']++;
                if($status['resume_count']>$max_resume_count)
                {
                    $message=__('Too many resumption attempts.', 'wpvivid-backuprestore');
                    $wpvivid_plugin->wpvivid_log->WriteLog('Backup the file ends with an error '. $message,'error');
                    $task->update_backup_task_status(false,'error',false,$status['resume_count'],$message);
                    if($resume_backup)
                        $task->check_timeout_backup_failed();
                    do_action('wpvivid_handle_backup_2_failed', $this->current_task_id);
                }
                else
                {
                    $message=__('Task timed out.', 'wpvivid-backuprestore');
                    $wpvivid_plugin->wpvivid_log->WriteLog('Task timed out.','error');
                    $timestamp = wp_next_scheduled('wpvivid_backup_2_schedule_event',array($this->current_task_id));
                    if($timestamp===false)
                    {
                        $task->update_backup_task_status(false,'wait_resume',false,$status['resume_count']);
                        if($this->add_resume_event($this->current_task_id)===false)
                        {
                            $task->update_backup_task_status(false,'error',false,$status['resume_count'],$message);
                            $wpvivid_plugin->wpvivid_log->WriteLog('Backup the file ends with an error '. $message,'error');
                            if($resume_backup)
                                $task->check_timeout_backup_failed();
                            do_action('wpvivid_handle_backup_2_failed', $this->current_task_id);
                        }
                    }
                }
            }
        }

        die();
    }

    public function list_tasks()
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
            $ret = $this->_list_tasks();

            echo wp_json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }

        die();
    }

    public function _list_tasks_ex()
    {
        if($this->wpvivid_check_litespeed_server() && $this->wpvivid_check_litespeed_cache_plugin())
        {
            wp_cache_delete('wpvivid_task_list', 'options');
        }

        $tasks = get_option('wpvivid_task_list', array());
        $ret['result']='success';
        $ret['progress_html']=false;

        foreach ($tasks as $task)
        {
            if(!isset($task['id']))
            {
                continue;
            }

            $ret['task_id']=$task['id'];
            $ret['need_update']=true;
            if(isset($task['options']['export']))
            {
                $ret['export'] =$task['options']['export'];
            }
            else
            {
                $ret['export'] ='';
            }
            $backup_task=new WPvivid_Backup_Task_2($task['id']);
            $info=$backup_task->get_backup_task_info();

            if($info['status']['str']=='ready'||$info['status']['str']=='running'||$info['status']['str']=='wait_resume'||$info['status']['str']=='no_responds')
            {
                $ret['running_backup_taskid']=$task['id'];

                if($info['status']['str']=='wait_resume')
                {
                    $ret['wait_resume']=true;
                    $ret['next_resume_time']=$info['data']['next_resume_time'];
                }

                if($info['status']['str']=='no_responds')
                {
                    $ret['task_no_response']=true;
                }

                $ret['progress_html'] = '<div class="action-progress-bar" id="wpvivid_action_progress_bar">
                                                <div class="action-progress-bar-percent" id="wpvivid_action_progress_bar_percent" style="height:24px;width:'.$info['task_info']['backup_percent'].'"></div>
                                             </div>
                                             <div id="wpvivid_estimate_upload_info" style="float: left;"> 
                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Total Size:', 'wpvivid-backuprestore') . '</span><span>'.$info['task_info']['total'].'</span></div>
                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Uploaded:', 'wpvivid-backuprestore') . '</span><span>'.$info['task_info']['upload'].'</span></div>
                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Speed:', 'wpvivid-backuprestore') . '</span><span>'.$info['task_info']['speed'].'</span></div>
                                             </div>
                                             <div style="float: left;">
                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Network Connection:', 'wpvivid-backuprestore') . '</span><span>'.$info['task_info']['network_connection'].'</span></div>
                                             </div>
                                             <div style="clear:both;"></div>
                                             <div style="margin-left:10px; float: left; width:100%;"><p id="wpvivid_current_doing">'.$info['task_info']['descript'].'</p></div>
                                             <div style="clear: both;"></div>
                                             <div>
                                                <div id="wpvivid_backup_cancel" class="backup-log-btn"><input class="button-primary" id="wpvivid_backup_cancel_btn" type="submit" value="' . esc_attr('Cancel', 'wpvivid-backuprestore') . '" style="'.$info['task_info']['css_btn_cancel'].'" /></div>
                                             </div>
                                             <div style="clear: both;"></div>';
            }
        }

        return $ret;
    }

    public function _list_tasks()
    {
        if($this->wpvivid_check_litespeed_server() && $this->wpvivid_check_litespeed_cache_plugin())
        {
            wp_cache_delete('wpvivid_task_list', 'options');
        }

        $tasks = get_option('wpvivid_task_list', array());
        $ret['result']='success';
        $ret['progress_html']=false;
        $ret['upload_progress_html']=false;
        $ret['success_notice_html'] =false;
        $ret['error_notice_html'] =false;
        $ret['need_update']=false;
        $ret['last_msg_html']=false;
        $ret['running_backup_taskid']='';
        $ret['wait_resume']=false;
        $ret['next_resume_time']=false;
        $ret['need_refresh_remote']=false;
        $ret['backup_finish_info']=false;
        $ret['task_no_response']=false;

        $finished_tasks=array();
        $backup_success_count=0;
        $backup_failed_count=0;
        $success_log_file_name = '';
        $ret['test']=$tasks;
        foreach ($tasks as $task)
        {
            if(!isset($task['id']))
            {
                continue;
            }

            $ret['task_id']=$task['id'];
            $ret['need_update']=true;
            if(isset($task['options']['export']))
            {
                $ret['export'] =$task['options']['export'];
            }
            else
            {
                $ret['export'] ='';
            }
            $backup_task=new WPvivid_Backup_Task_2($task['id']);
            $info=$backup_task->get_backup_task_info();
            $ret['need_next_schedule']=$info['task_info']['need_next_schedule'];
            if($info['task_info']['need_next_schedule']===true)
            {
                $timestamp = wp_next_scheduled('wpvivid_task_monitor_event_2',array($task['id']));
                if($timestamp===false)
                {
                    $this->add_monitor_event($task['id'],20);
                }
            }
            if($info['status']['str']=='ready'||$info['status']['str']=='running'||$info['status']['str']=='wait_resume'||$info['status']['str']=='no_responds')
            {
                $ret['running_backup_taskid']=$task['id'];

                if($info['status']['str']=='wait_resume')
                {
                    $ret['wait_resume']=true;
                    $ret['next_resume_time']=$info['data']['next_resume_time'];
                }

                if($info['status']['str']=='no_responds')
                {
                    $ret['task_no_response']=true;
                }

                $ret['progress_html'] = '<div class="action-progress-bar" id="wpvivid_action_progress_bar">
                                                <div class="action-progress-bar-percent" id="wpvivid_action_progress_bar_percent" style="height:24px;width:'.$info['task_info']['backup_percent'].'"></div>
                                             </div>
                                             <div id="wpvivid_estimate_upload_info" style="float: left;"> 
                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Total Size:', 'wpvivid-backuprestore') . '</span><span>'.$info['task_info']['total'].'</span></div>
                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Uploaded:', 'wpvivid-backuprestore') . '</span><span>'.$info['task_info']['upload'].'</span></div>
                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Speed:', 'wpvivid-backuprestore') . '</span><span>'.$info['task_info']['speed'].'</span></div>
                                             </div>
                                             <div style="float: left;">
                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Network Connection:', 'wpvivid-backuprestore') . '</span><span>'.$info['task_info']['network_connection'].'</span></div>
                                             </div>
                                             <div style="clear:both;"></div>
                                             <div style="margin-left:10px; float: left; width:100%;"><p id="wpvivid_current_doing">'.$info['task_info']['descript'].'</p></div>
                                             <div style="clear: both;"></div>
                                             <div>
                                                <div id="wpvivid_backup_cancel" class="backup-log-btn"><input class="button-primary" id="wpvivid_backup_cancel_btn" type="submit" value="' . esc_attr('Cancel', 'wpvivid-backuprestore') . '" style="'.$info['task_info']['css_btn_cancel'].'" /></div>
                                             </div>
                                             <div style="clear: both;"></div>';
            }

            if($info['status']['str']=='completed')
            {
                $finished_tasks[$task['id']]=$task;
                $backup_success_count++;
                $success_log_file_name = $task['id'].'_backup_log.txt';
            }
            else if($info['status']['str']=='error')
            {
                $finished_tasks[$task['id']]=$task;
                $backup_failed_count++;
            }

            if(isset($task['options']['export'])&&$task['options']['export']=='auto_migrate')
            {
                $ret['upload_progress_html']=$ret['progress_html'];
                $ret['progress_html']=false;
            }
        }

        if(!empty($ret['running_backup_taskid']))
        {
            $timestamp = wp_next_scheduled('wpvivid_task_monitor_event_2',array($ret['running_backup_taskid']));
            if($timestamp===false)
            {
                $this->add_monitor_event($ret['running_backup_taskid'],20);
            }
        }

        if($backup_success_count>0)
        {
            $notice_msg = $backup_success_count.' backup task(s) finished. Please switch to <a href="#" onclick="wpvivid_click_switch_page(\'wrap\', \'wpvivid_tab_log\', true);">Log</a> page to check the details.';
            $ret['success_notice_html'] ='<div class="notice notice-success is-dismissible inline" style="margin-bottom: 5px;"><p>'.$notice_msg.'</p>
                                    <button type="button" class="notice-dismiss" onclick="click_dismiss_notice(this);">
                                    <span class="screen-reader-text">Dismiss this notice.</span>
                                    </button>
                                    </div>';
        }

        //<a href="#" onclick="wpvivid_click_switch_page('wrap', 'wpvivid_tab_log', true);">Log</a>
        if($backup_failed_count>0)
        {
            $admin_url = apply_filters('wpvivid_get_admin_url', '');
            $notice_msg = $backup_failed_count.' backup task(s) have been failed. Please switch to <a href="#" onclick="wpvivid_click_switch_page(\'wrap\', \'wpvivid_tab_debug\', true);">Log</a> page to send us the debug information.';
            $ret['error_notice_html'] ='<div class="notice notice-error inline" style="margin-bottom: 5px;"><p>'.$notice_msg.'</p></div>';
        }

        $delete_ids=array();

        foreach ($tasks as $task)
        {
            if(array_key_exists($task['id'],$finished_tasks))
            {
                $delete_ids[]=$task['id'];
            }
        }
        foreach ($delete_ids as $id)
        {
            unset($tasks[$id]);
        }
        WPvivid_Setting::update_option('wpvivid_task_list',$tasks);

        return $ret;
    }

    public function shutdown_backup()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        $task_id = sanitize_key($_POST['task_id']);
        $backup_task=new WPvivid_Backup_Task_2($task_id);
        if($backup_task->check_cancel_backup())
        {
            $ret['result'] = 'success';
        }
        else
        {
            $ret['result'] = 'failed';
        }

        echo wp_json_encode($ret);
        die();
    }

    public function delete_task()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        if (isset($_POST['task_id']) && !empty($_POST['task_id']) && is_string($_POST['task_id']))
        {
            $task_id = sanitize_key($_POST['task_id']);

            $options = get_option('wpvivid_task_list', array());
            unset($options[$task_id]);

            update_option('wpvivid_task_list',$options);
            $json['result'] = 'success';
            echo wp_json_encode($json);
        }

        die();
    }

    public function update_backup_task_status($task_id,$reset_start_time=false,$status='',$reset_timeout=false,$resume_count=false,$error='')
    {
        $tasks=get_option('wpvivid_task_list', array());
        if(array_key_exists ($task_id,$tasks))
        {
            $task = $tasks[$task_id];
            $task['status']['run_time']=time();
            if($reset_start_time)
                $task['status']['start_time']=time();
            if(!empty($status))
            {
                $task['status']['str']=$status;
            }
            if($reset_timeout)
                $task['status']['timeout']=time();
            if($resume_count!==false)
            {
                $task['status']['resume_count']=$resume_count;
            }

            if(!empty($error))
            {
                $task['status']['error']=$error;
            }

            $options = get_option('wpvivid_task_list', array());
            $options[$task_id]=$task;
            update_option('wpvivid_task_list',$options);

            return true;
        }
        else
        {
            return false;
        }
    }

    public function task_monitor($task_id)
    {
        if(WPvivid_taskmanager::get_task($task_id)!==false)
        {
            $task=new WPvivid_Backup_Task_2($task_id);

            $status=$task->get_status();

            if($task->is_task_canceled())
            {
                $limit=$task->get_time_limit();

                $last_active_time=time()-$status['run_time'];
                if($last_active_time>180)
                {
                    if($task->check_cancel_backup())
                    {
                        $this->end_shutdown_function=true;
                        die();
                    }
                }
            }
            global $wpvivid_plugin;
            $wpvivid_plugin->wpvivid_log->OpenLogFile(WPvivid_taskmanager::get_task_options($task_id,'log_file_name'));

            if($status['str']=='running'||$status['str']=='error'||$status['str']=='no_responds')
            {
                $limit=$task->get_time_limit();

                $time_spend=time()-$status['timeout'];
                $last_active_time=time()-$status['run_time'];
                if($time_spend>$limit&&$last_active_time>180)
                {
                    //time out
                    $max_resume_count=$task->get_max_resume_count();
                    $task->check_timeout();
                    $status['resume_count']++;
                    if($status['resume_count']>$max_resume_count)
                    {
                        $message=__('Too many resumption attempts.', 'wpvivid-backuprestore');
                        $wpvivid_plugin->wpvivid_log->WriteLog('Backup the file ends with an error '. $message,'error');
                        $task->update_backup_task_status(false,'error',false,$status['resume_count'],$message);
                        $task->check_timeout_backup_failed();
                        do_action('wpvivid_handle_backup_2_failed', $task_id);
                    }
                    else
                    {
                        $message=__('Task timed out.', 'wpvivid-backuprestore');
                        $task->update_backup_task_status(false,'wait_resume',false,$status['resume_count']);
                        if($this->add_resume_event($task_id)===false)
                        {
                            $wpvivid_plugin->wpvivid_log->WriteLog('Backup the file ends with an error '. $message,'error');
                            $task->update_backup_task_status(false,'error',false,$status['resume_count'],$message);
                            $task->check_timeout_backup_failed();
                            do_action('wpvivid_handle_backup_2_failed', $task_id);
                        }
                    }
                }
                else
                {
                    $time_spend=time()-$status['run_time'];
                    if($time_spend>180)
                    {
                        $task->update_backup_task_status(false,'no_responds',false,$status['resume_count']);
                        $this->add_monitor_event($task_id);
                    }
                    else {
                        $this->add_monitor_event($task_id);
                    }
                }
            }
            else if($status['str']=='wait_resume')
            {
                $timestamp = wp_next_scheduled(WPVIVID_RESUME_SCHEDULE_EVENT,array($task_id));
                if($timestamp===false)
                {
                    $message = 'Task timed out (WebHosting).';
                    $task->update_backup_task_status(false, 'wait_resume', false, $status['resume_count']);
                    if ($this->add_resume_event($task_id)===false)
                    {
                        $wpvivid_plugin->wpvivid_log->WriteLog('Backup the file ends with an error '. $message,'error');
                        $task->update_backup_task_status(false, 'error', false, $status['resume_count'], $message);
                        $task->check_timeout_backup_failed();
                        do_action('wpvivid_handle_backup_2_failed', $task_id);
                    }
                }
            }
        }
    }

    private function add_resume_event($task_id)
    {
        $resume_time=time()+10;

        $b=wp_schedule_single_event($resume_time,'wpvivid_backup_2_schedule_event',array($task_id));

        if($b===false)
        {
            $timestamp = wp_next_scheduled('wpvivid_backup_2_schedule_event',array($task_id));

            if($timestamp===false)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        return true;
    }

    public function add_monitor_event($task_id,$next_time=120)
    {
        $resume_time=time()+$next_time;

        $timestamp = wp_next_scheduled('wpvivid_task_monitor_event_2',array($task_id));

        if($timestamp===false)
        {
            $b = wp_schedule_single_event($resume_time, 'wpvivid_task_monitor_event_2', array($task_id));
            if ($b === false)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        return true;
    }

    public function clear_monitor_schedule($id)
    {
        $timestamp =wp_next_scheduled('wpvivid_task_monitor_event_2',array($id));
        if($timestamp!==false)
        {
            wp_unschedule_event($timestamp,'wpvivid_task_monitor_event_2',array($id));
        }
    }

    public function add_clean_backup_data_event($task_id)
    {
        $task=WPvivid_taskmanager::get_task($task_id);
        $tasks=WPvivid_Setting::get_option('wpvivid_clean_task_2');
        $tasks[$task_id]=$task;
        WPvivid_Setting::update_option('wpvivid_clean_task_2',$tasks);

        $resume_time=time()+60;

        $b=wp_schedule_single_event($resume_time,'wpvivid_clean_backup_2_data_event',array($task_id));

        if($b===false)
        {
            $timestamp = wp_next_scheduled('wpvivid_clean_backup_2_data_event',array($task_id));

            if($timestamp!==false)
            {
                $resume_time=max($resume_time,$timestamp+10*60+10);

                $b=wp_schedule_single_event($resume_time,'wpvivid_clean_backup_2_data_event',array($task_id));

                if($b===false)
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        return true;
    }

    public function clean_backup_data_event($task_id)
    {
        $tasks=get_option('wpvivid_clean_task_2',array());
        if(isset($tasks[$task_id]))
        {
            $task_data=$tasks[$task_id];
            unset($tasks[$task_id]);
        }
        update_option('wpvivid_clean_task_2',$tasks);

        if(!empty($task_data))
        {
            $task= new WPvivid_Backup_Task_2($task_id,$task_data);
            $task->clean_backup();

            $files=array();

            if($task->need_upload())
            {
                $backup_files=$task->get_backup_files();
                foreach ($backup_files as $file)
                {
                    $files[]=basename($file);
                }
                if(!empty($files))
                {
                    if(!class_exists('WPvivid_Upload'))
                        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-upload.php';
                    $upload=new WPvivid_Upload();
                    $upload->clean_remote_backup($task->get_remote_options(),$files);
                }
            }
            //clean upload
        }
    }

    public function wpvivid_check_litespeed_server()
    {
        $litespeed=false;
        if ( isset( $_SERVER['HTTP_X_LSCACHE'] ) && $_SERVER['HTTP_X_LSCACHE'] )
        {
            $litespeed=true;
        }
        elseif ( isset( $_SERVER['LSWS_EDITION'] ) && strpos( $_SERVER['LSWS_EDITION'], 'Openlitespeed' ) === 0 ) {
            $litespeed=true;
        }
        elseif ( isset( $_SERVER['SERVER_SOFTWARE'] ) && $_SERVER['SERVER_SOFTWARE'] == 'LiteSpeed' ) {
            $litespeed=true;
        }

        return $litespeed;
    }

    public function wpvivid_check_litespeed_cache_plugin()
    {
        $litespeed_cache_plugin=false;
        if(!function_exists('get_plugins'))
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $litespeed_cache_slug='litespeed-cache/litespeed-cache.php';
        if (is_multisite())
        {
            $active_plugins = array();
            //network active
            $mu_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
            if(!empty($mu_active_plugins)){
                foreach ($mu_active_plugins as $plugin_name => $data){
                    $active_plugins[] = $plugin_name;
                }
            }
            $plugins=get_mu_plugins();
            if(count($plugins) == 0 || !isset($plugins[$litespeed_cache_slug])){
                $plugins=get_plugins();
            }
        }
        else
        {
            $active_plugins = get_option('active_plugins');
            $plugins=get_plugins();
        }

        if(!empty($plugins))
        {
            if(isset($plugins[$litespeed_cache_slug]))
            {
                if(in_array($litespeed_cache_slug, $active_plugins))
                {
                    $litespeed_cache_plugin=true;
                }
                else
                {
                    $litespeed_cache_plugin=false;
                }
            }
            else
            {
                $litespeed_cache_plugin=false;
            }
        }
        else
        {
            $litespeed_cache_plugin=false;
        }

        return $litespeed_cache_plugin;
    }

    public function send_backup_to_site()
    {
        try
        {
            check_ajax_referer( 'wpvivid_ajax', 'nonce' );
            $check=current_user_can('manage_options');
            $check=apply_filters('wpvivid_ajax_check_security',$check);
            if(!$check)
            {
                die();
            }

            $options = WPvivid_Setting::get_option('wpvivid_saved_api_token');

            if (empty($options))
            {
                $ret['result'] = 'failed';
                $ret['error'] = __('A key is required.', 'wpvivid-backuprestore');
                echo wp_json_encode($ret);
                die();
            }

            $url = '';
            foreach ($options as $key => $value)
            {
                $url = $value['url'];
            }

            if ($url === '')
            {
                $ret['result'] = 'failed';
                $ret['error'] = __('The key is invalid.', 'wpvivid-backuprestore');
                echo wp_json_encode($ret);
                die();
            }

            if ($options[$url]['expires'] != 0 && $options[$url]['expires'] < time())
            {
                $ret['result'] = 'failed';
                $ret['error'] =  __('The key has expired.', 'wpvivid-backuprestore');
                echo wp_json_encode($ret);
                die();
            }

            $json['test_connect']=1;
            $json=wp_json_encode($json);
            $crypt=new WPvivid_crypt(base64_decode($options[$url]['token']));
            $data=$crypt->encrypt_message($json);
            $data=base64_encode($data);
            $args['body']=array('wpvivid_content'=>$data,'wpvivid_action'=>'send_to_site_connect');
            $response=wp_remote_post($url,$args);

            if ( is_wp_error( $response ) )
            {
                $ret['result']=WPVIVID_FAILED;
                $ret['error']= $response->get_error_message();
                echo wp_json_encode($ret);
                die();
            }
            else
            {
                if($response['response']['code']==200)
                {
                    $res=json_decode($response['body'],1);
                    if($res!=null) {
                        if($res['result']==WPVIVID_SUCCESS)
                        {

                        }
                        else
                        {
                            $ret['result']=WPVIVID_FAILED;
                            $ret['error']= $res['error'];
                            echo wp_json_encode($ret);
                            die();
                        }
                    }
                    else {
                        $ret['result']=WPVIVID_FAILED;
                        $ret['error']= 'failed to parse returned data, unable to establish connection with the target site.';
                        $ret['response']=$response;
                        echo wp_json_encode($ret);
                        die();
                    }
                }
                else {
                    $ret['result']=WPVIVID_FAILED;
                    $ret['error']= 'upload error '.$response['response']['code'].' '.$response['body'];
                    echo wp_json_encode($ret);
                    die();
                }
            }

            if (WPvivid_taskmanager::is_tasks_backup_running())
            {
                $ret['result'] = 'failed';
                $ret['error'] = __('A task is already running. Please wait until the running task is complete, and try again.', 'wpvivid-backuprestore');
                echo wp_json_encode($ret);
                die();
            }

            $remote_option['url'] = $options[$url]['url'];
            $remote_option['token'] = $options[$url]['token'];
            $remote_option['type'] = WPVIVID_REMOTE_SEND_TO_SITE;
            $remote_options['temp'] = $remote_option;

            $backup_options = stripslashes(sanitize_text_field($_POST['backup_options']));
            $backup_options = json_decode($backup_options, true);
            $backup['backup_files'] = $backup_options['transfer_type'];
            $backup['local'] = 0;
            $backup['remote'] = 1;
            $backup['ismerge'] = 1;
            $backup['lock'] = 0;
            $backup['remote_options'] = $remote_options;
            $backup['type']='Migrate';
            $backup['export']='auto_migrate';

            /*
            $backup_task = new WPvivid_Backup_Task();
            $ret = $backup_task->new_backup_task($backup, 'Manual', 'transfer');
            $task_id = $ret['task_id'];
            global $wpvivid_plugin;
            $wpvivid_plugin->check_backup($task_id, $backup);
            echo wp_json_encode($ret);
            die();
            */

            $settings=$this->get_backup_settings($backup);
            $task=new WPvivid_Backup_Task_2();
            $ret=$task->new_backup_task($backup,$settings);

            echo wp_json_encode($ret);
            die();
        }
        catch (Exception $e){
            $ret['result'] = 'failed';
            $ret['error'] = $e->getMessage();
            echo wp_json_encode($ret);
            die();
        }
    }

    public function migrate_now()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        register_shutdown_function(array($this,'deal_backup_shutdown_error'));
        $this->end_shutdown_function=false;

        $task_id = sanitize_key($_POST['task_id']);
        $this->current_task_id=$task_id;
        global $wpvivid_plugin;

        if ($this->is_tasks_backup_running($task_id))
        {
            $ret['result'] = 'failed';
            $ret['error'] = __('We detected that there is already a running backup task. Please wait until it completes then try again.', 'wpvivid-backuprestore');
            echo wp_json_encode($ret);
            die();
        }

        try
        {
            $this->update_backup_task_status($task_id,true,'running');
            $wpvivid_plugin->flush($task_id);
            $this->add_monitor_event($task_id);
            $this->task=new WPvivid_Backup_Task_2($task_id);
            $this->task->set_memory_limit();
            $this->task->set_time_limit();

            $wpvivid_plugin->wpvivid_log->OpenLogFile($this->task->task['options']['log_file_name']);
            $wpvivid_plugin->wpvivid_log->WriteLog('Start backing up.','notice');
            $wpvivid_plugin->wpvivid_log->WriteLogHander();

            if(!$this->task->is_backup_finished())
            {
                $ret=$this->backup();
                $this->task->clear_cache();
                if($ret['result']!='success')
                {
                    $wpvivid_plugin->wpvivid_log->WriteLog('Backup the file ends with an error '. $ret['error'],'error');
                    $this->task->update_backup_task_status(false,'error',false,false,$ret['error']);
                    do_action('wpvivid_handle_backup_2_failed', $task_id);
                    $this->end_shutdown_function=true;
                    $this->clear_monitor_schedule($task_id);
                    die();
                }
            }

            if($this->task->need_upload())
            {
                $ret=$this->upload($task_id);
                if($ret['result'] == WPVIVID_SUCCESS)
                {
                    do_action('wpvivid_handle_backup_2_succeed',$task_id);
                    $this->update_backup_task_status($task_id,false,'completed');
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
                $this->update_backup_task_status($task_id,false,'completed');
            }
            $this->clear_monitor_schedule($task_id);
        }
        catch (Exception $error)
        {
            //catch error and stop task recording history
            $message = 'An exception has occurred. class:'.get_class($error).';msg:'.$error->getMessage().';code:'.$error->getCode().';line:'.$error->getLine().';in_file:'.$error->getFile().';';
            error_log($message);
            WPvivid_taskmanager::update_backup_task_status($task_id,false,'error',false,false,$message);
            $wpvivid_plugin->wpvivid_log->WriteLog($message,'error');
            do_action('wpvivid_handle_backup_2_failed',$task_id);
            $this->end_shutdown_function=true;
            die();
        }


        $this->end_shutdown_function=true;

        die();
    }

    public function default_exclude_folders($folders)
    {
        $upload_dir = wp_upload_dir();
        $exclude_default = array();
        $exclude_default[0]['type'] = 'folder';
        $exclude_default[0]['path'] = $upload_dir['basedir'].'/'.'backwpup';    // BackWPup backup directory
        $exclude_default[1]['type'] = 'folder';
        $exclude_default[1]['path'] = $upload_dir['basedir'].'/'.'ShortpixelBackups';   //ShortpixelBackups
        $exclude_default[2]['type'] = 'folder';
        $exclude_default[2]['path'] = $upload_dir['basedir'].'/'.'backup';
        $exclude_default[3]['type'] = 'folder';
        $exclude_default[3]['path'] = $upload_dir['basedir'].'/'.'backwpup';    // BackWPup backup directory
        $exclude_default[4]['type'] = 'folder';
        $exclude_default[4]['path'] = $upload_dir['basedir'].'/'.'backup-guard';    // Wordpress Backup and Migrate Plugin backup directory
        $exclude_default[5]['type'] = 'folder';
        $exclude_default[5]['path'] = WP_CONTENT_DIR.'/'.'updraft';     // Updraft Plus backup directory
        $exclude_default[6]['type'] = 'folder';
        $exclude_default[6]['path'] = WP_CONTENT_DIR.'/'.'ai1wm-backups';   // All-in-one WP migration backup directory
        $exclude_default[7]['type'] = 'folder';
        $exclude_default[7]['path'] = WP_CONTENT_DIR.'/'.'backups';     // Xcloner backup directory
        $exclude_default[8]['type'] = 'folder';
        $exclude_default[8]['path'] = WP_CONTENT_DIR.'/'.'upgrade';
        $exclude_default[10]['type'] = 'folder';
        $exclude_default[10]['path'] = WP_CONTENT_DIR.'/'.'cache';
        $exclude_default[11]['type'] = 'folder';
        $exclude_default[11]['path'] = WP_CONTENT_DIR.'/'.'wphb-cache';
        $exclude_default[12]['type'] = 'folder';
        $exclude_default[12]['path'] = WP_CONTENT_DIR.'/'.'backup';
        $exclude_default[13]['type'] = 'folder';
        $exclude_default[13]['path'] = WP_CONTENT_DIR.'/'.'Dropbox_Backup';
        //$exclude_default[14]['type'] = 'folder';
        //$exclude_default[14]['path'] = WP_CONTENT_DIR.'/'.'mu-plugins';
        $exclude_default[15]['type'] = 'file';
        $exclude_default[15]['path'] = WP_CONTENT_DIR.'/'.'mysql.sql';  //mysql

        //
        $exclude_default[16]['type'] = 'folder';
        $exclude_default[16]['path'] = WP_CONTENT_DIR.'/'.'cache';

        $exclude_default[17]['type'] = 'folder';
        $exclude_default[17]['path'] = WP_CONTENT_DIR.'/'.'wpvivid_uploads';

        $exclude_default[18]['type'] = 'folder';
        $exclude_default[18]['path'] = WP_CONTENT_DIR.'/'.'WPvivid_Uploads';

        $exclude_default[19]['type'] = 'folder';
        $exclude_default[19]['path'] = WP_CONTENT_DIR.'/'.'backups-dup-pro';    // duplicator backup directory

        $exclude_default[20]['type'] = 'folder';
        $exclude_default[20]['path'] = WP_CONTENT_DIR.'/'.'backup-migration';

        $exclude_default[21]['type'] = 'folder';
        $exclude_default[21]['path'] = WP_CONTENT_DIR.'/'.'backups-dup-lite';

        if(!empty($exclude_default))
        {
            foreach ($exclude_default as $index => $value)
            {
                $folders[$index]=$value;
            }
        }
        return $folders;
    }
}