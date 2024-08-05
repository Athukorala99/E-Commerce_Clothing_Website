<?php

if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

class WPvivid_Restore_2
{
    public $log;
    public $end_shutdown_function;

    public function __construct()
    {
        $this->log=false;
        $this->end_shutdown_function=false;

        add_action('wp_ajax_wpvivid_init_restore_task_2',array($this,'init_restore_task'));

        add_action('wp_ajax_wpvivid_do_restore_2',array($this,'do_restore'));
        add_action('wp_ajax_nopriv_wpvivid_do_restore_2',array( $this,'do_restore'));

        add_action('wp_ajax_wpvivid_get_restore_progress_2',array( $this,'get_restore_progress'));
        add_action('wp_ajax_nopriv_wpvivid_get_restore_progress_2',array( $this,'get_restore_progress'));

        add_action('wp_ajax_wpvivid_finish_restore_2',array( $this,'finish_restore'));
        add_action('wp_ajax_nopriv_wpvivid_finish_restore_2',array( $this,'finish_restore'));

        add_action('wp_ajax_wpvivid_restore_failed_2',array( $this,'restore_failed'));
        add_action('wp_ajax_nopriv_wpvivid_restore_failed_2',array( $this,'restore_failed'));

        include_once WPVIVID_PLUGIN_DIR . '/includes/new_backup/class-wpvivid-restore-file-2.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/new_backup/class-wpvivid-restore-db-2.php';
    }

    public function init_restore_task()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        if(!isset($_POST['backup_id'])||empty($_POST['backup_id'])||!is_string($_POST['backup_id']))
        {
            die();
        }

        $backup_id=sanitize_key($_POST['backup_id']);

        $restore_options=array();
        if(isset($_POST['restore_options']))
        {
            foreach ($_POST['restore_options'] as $key=>$option)
            {
                $restore_options[$key]=sanitize_text_field($option);
            }
        }

        if(isset($restore_options['restore_version']))
        {
            $restore_version=$restore_options['restore_version'];
        }
        else
        {
            $restore_version=0;
        }

        $restore_options['restore_detail_options']=array();

        $ret=$this->create_restore_task($backup_id,$restore_options,$restore_version);

        $this->write_litespeed_rule();
        $this->deactivate_plugins();

        if(!file_exists(WPMU_PLUGIN_DIR.'/a-wpvivid-restore-mu-plugin-check.php'))
        {
            if(file_exists(WPMU_PLUGIN_DIR))
                copy(WPVIVID_PLUGIN_DIR . '/includes/mu-plugins/a-wpvivid-restore-mu-plugin-check.php',WPMU_PLUGIN_DIR.'/a-wpvivid-restore-mu-plugin-check.php');
        }

        echo wp_json_encode($ret);
        die();
    }

    public function create_restore_task($backup_id,$restore_options,$restore_version)
    {
        $restore_task=array();
        $restore_task['backup_id']=$backup_id;
        $restore_task['restore_options']=$restore_options;
        $restore_task['update_time']=time();
        $restore_task['restore_timeout_count']=0;
        $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
        if($backup===false)
        {
            $ret['result']='failed';
            $ret['error']='backup not found';
            return $ret;
        }


        $backup_item = new WPvivid_Backup_Item($backup);
        $backup_file_info=$this->get_restore_files_info($backup_item,$restore_version,true);
        $sub_tasks=array();

        $b_reset_plugin=false;

        foreach ($backup_file_info as $key=>$files_info)
        {
            $task['type']=$key;
            if(isset($restore_options[$key]))
                $task['options']=$restore_options[$key];
            else
                $task['options']=array();

            $task['options']['restore_reset']=true;
            if($key=='themes')
            {
                $task['priority']=1;
                $task['unzip_file']['files']=$files_info['files'];
                $task['unzip_file']['unzip_finished']=0;
                $task['unzip_file']['last_action']='waiting...';
                $task['unzip_file']['last_unzip_file']='';
                $task['unzip_file']['last_unzip_file_index']=0;
            }
            else if($key=='plugin')
            {
                $task['priority']=2;
                $task['unzip_file']['files']=$files_info['files'];
                $task['unzip_file']['unzip_finished']=0;
                $task['unzip_file']['last_action']='waiting...';
                $task['unzip_file']['last_unzip_file']='';
                $task['unzip_file']['last_unzip_file_index']=0;

                $b_reset_plugin=isset($restore_options['restore_detail_options']['restore_reset'])?$restore_options['restore_detail_options']['restore_reset']:false;;
            }
            else if($key=='wp-content')
            {
                $task['priority']=3;
                $task['unzip_file']['files']=$files_info['files'];
                $task['unzip_file']['unzip_finished']=0;
                $task['unzip_file']['last_action']='waiting...';
                $task['unzip_file']['last_unzip_file']='';
                $task['unzip_file']['last_unzip_file_index']=0;
            }
            else if($key=='upload')
            {
                $task['priority']=4;
                $task['unzip_file']['files']=$files_info['files'];
                $task['unzip_file']['unzip_finished']=0;
                $task['unzip_file']['last_action']='waiting...';
                $task['unzip_file']['last_unzip_file']='';
                $task['unzip_file']['last_unzip_file_index']=0;
            }
            else if($key=='wp-core')
            {
                $task['priority']=5;
                $task['unzip_file']['files']=$files_info['files'];
                $task['unzip_file']['unzip_finished']=0;
                $task['unzip_file']['last_action']='waiting...';
                $task['unzip_file']['last_unzip_file']='';
                $task['unzip_file']['last_unzip_file_index']=0;
            }
            else if($key=='custom')
            {
                $task['priority']=6;
                $task['unzip_file']['files']=$files_info['files'];
                $task['unzip_file']['unzip_finished']=0;
                $task['unzip_file']['last_action']='waiting...';
                $task['unzip_file']['last_unzip_file']='';
                $task['unzip_file']['last_unzip_file_index']=0;
            }
            else if($key=='db'||$key=='databases')
            {
                $task['type']='databases';

                $task['unzip_file']['files']=$files_info['files'];

                $task['options']=array_merge($task['options'],$task['unzip_file']['files'][0]['options']);

                $task['unzip_file']['unzip_finished']=0;
                $task['unzip_file']['last_action']='waiting...';
                $task['unzip_file']['last_unzip_file']='';

                $task['exec_sql']['init_sql_finished']=0;
                $task['exec_sql']['create_snapshot_finished']=0;
                $task['exec_sql']['exec_sql_finished']=0;
                $task['exec_sql']['replace_rows_finished']=0;

                $task['exec_sql']['current_table']='';
                $task['exec_sql']['current_old_table']='';
                $task['exec_sql']['replace_tables']=array();
                //$task['exec_sql']['current_replace_table_finish']=false;
                //$task['exec_sql']['current_need_replace_table']=false;
                //$task['exec_sql']['current_replace_row']=0;

                $task['exec_sql']['last_action']='waiting...';
                $task['exec_sql']['last_query']='';

                $uid=$this->create_db_uid();
                if($uid===false)
                {
                    $ret['result']='failed';
                    $ret['error']='create db uid failed';
                    return $ret;
                }
                $task['exec_sql']['db_id']=$uid;
                $task['exec_sql']['sql_files']=array();
                $task['priority']=8;
                $restore_task['restore_db']=1;
            }
            else
            {
                $task['priority']=7;
                $task['unzip_file']['files']=$files_info['files'];
                $task['unzip_file']['unzip_finished']=0;
                $task['unzip_file']['last_action']='waiting...';
                $task['unzip_file']['last_unzip_file']='';
                $task['unzip_file']['last_unzip_file_index']=0;
            }

            $restore_reset=isset($restore_options['restore_detail_options']['restore_reset'])?$restore_options['restore_detail_options']['restore_reset']:false;
            $task['finished']=0;
            $task['last_msg']='waiting...';
            if($restore_reset)
            {
                $task['restore_reset']=true;
                $task['restore_reset_finished']=false;
            }
            else
            {
                $task['restore_reset']=false;
            }

            $restore_htaccess=isset($restore_options['restore_detail_options']['restore_htaccess'])?$restore_options['restore_detail_options']['restore_htaccess']:false;
            if($restore_htaccess)
            {
                $task['options']['restore_htaccess']=true;
            }
            else
            {
                $task['options']['restore_htaccess']=false;
            }

            $sub_tasks[]=$task;
        }
        usort($sub_tasks, function ($a, $b)
        {
            if ($a['priority'] == $b['priority'])
                return 0;

            if ($a['priority'] > $b['priority'])
                return 1;
            else
                return -1;
        });

        $restore_task['is_migrate'] = $backup_item->check_migrate_file();
        $restore_task['sub_tasks']=$sub_tasks;
        $restore_task['do_sub_task']=false;
        $restore_task['restore_detail_options']=$this->get_default_restore_options($restore_options['restore_detail_options']);

        $id=uniqid('wpvivid-');
        $log_file_name=$id.'_restore_log.txt';
        $this->log=new WPvivid_Log();
        $log_file=$this->log->GetSaveLogFolder().$log_file_name;
        $restore_task['log']=$log_file;

        $restore_task['last_log']='Init restore task completed.';
        $this->log->WriteLog($restore_task['last_log'],'notice');
        $restore_task['status']='ready';
        update_option('wpvivid_restore_task',$restore_task);
        $ret['result']='success';
        $ret['reset_plugin']=$b_reset_plugin;
        $ret['task']=$restore_task;
        return $ret;
    }

    public function write_litespeed_rule($open=true)
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

        if($litespeed)
        {
            if (function_exists('insert_with_markers'))
            {
                $home_path     = get_home_path();
                $htaccess_file = $home_path . '.htaccess';

                if ( ( ! file_exists( $htaccess_file ) && is_writable( $home_path ) ) || is_writable( $htaccess_file ) )
                {
                    if ( got_mod_rewrite() )
                    {
                        if($open)
                        {
                            $line=array();
                            $line[]='<IfModule Litespeed>';
                            $line[]='RewriteEngine On';
                            $line[]='RewriteRule .* - [E=noabort:1, E=noconntimeout:1]';
                            $line[]='</IfModule>';
                            insert_with_markers($htaccess_file,'WPvivid_Restore',$line);
                        }
                        else
                        {
                            insert_with_markers($htaccess_file,'WPvivid_Restore','');
                        }

                    }
                }
            }
        }
    }

    public function deactivate_plugins()
    {
        if(is_multisite())
        {
            $current =  get_site_option( 'active_sitewide_plugins' );
            update_option( 'wpvivid_save_active_plugins', $current );

            $wpvivid_backup='wpvivid-backuprestore/wpvivid-backuprestore.php';

            if (array_key_exists($wpvivid_backup, $current) !== false)
            {
                unset($current[$wpvivid_backup]);
            }
            deactivate_plugins($current, true, true);
        }
        else
        {
            $current = get_option( 'active_plugins', array() );
            update_option( 'wpvivid_save_active_plugins', $current );

            $wpvivid_backup='wpvivid-backuprestore/wpvivid-backuprestore.php';

            if (($key = array_search($wpvivid_backup, $current)) !== false)
            {
                unset($current[$key]);
            }
            deactivate_plugins($current, true, false);
        }

    }

    public function get_default_restore_options($restore_detail_options)
    {
        $setting=get_option('wpvivid_common_setting',array());
        $restore_detail_options['max_allowed_packet']=32;
        $restore_detail_options['replace_rows_pre_request']=isset($setting['replace_rows_pre_request'])?$setting['replace_rows_pre_request']:10000;
        $restore_detail_options['restore_max_execution_time']=isset($setting['restore_max_execution_time'])?$setting['restore_max_execution_time']:WPVIVID_RESTORE_MAX_EXECUTION_TIME;
        $restore_detail_options['restore_memory_limit']=isset($setting['restore_memory_limit'])?$setting['restore_memory_limit']:WPVIVID_RESTORE_MEMORY_LIMIT;
        $restore_detail_options['sql_file_buffer_pre_request']=isset($setting['sql_file_buffer_pre_request'])?$setting['sql_file_buffer_pre_request']:'5';
        $restore_detail_options['use_index']=isset($setting['use_index'])?$setting['use_index']:1;
        $restore_detail_options['unzip_files_pre_request']=isset($setting['unzip_files_pre_request'])?$setting['unzip_files_pre_request']:1000;
        $restore_detail_options['db_connect_method']=isset($setting['db_connect_method'])?$setting['db_connect_method']:'wpdb';
        $restore_detail_options['restore_db_reset']=false;

        return $restore_detail_options;
    }

    public function create_db_uid()
    {
        global $wpdb;
        $count = 0;

        do
        {
            $count++;
            $uid = sprintf('%06x', wp_rand(0, 0xFFFFFF));

            $verify_db = $wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', array('%' . $uid . '%')));
        } while (!empty($verify_db) && $count < 10);

        if ($count == 10)
        {
            $uid = false;
        }

        return $uid;
    }

    public function get_restore_files_info($backup_item,$restore_version=0,$use_index=0)
    {
        $files=$backup_item->get_files(false);
        $files_info=array();

        foreach ($files as $file)
        {
            $files_info[$file]=$backup_item->get_file_info($file);
        }

        $info=array();
        $added_files=array();

        foreach ($files_info as $file_name=>$file_info)
        {
            if(isset($file_info['has_child']))
            {
                $info=$this->get_has_child_file_info($info,$file_name,$file_info,$added_files,$restore_version,$use_index);
            }
            else
            {
                if(isset($file_info['file_type']))
                {
                    if(isset($file_info['version']))
                    {
                        if($restore_version===false)
                        {
                            if (!in_array($file_name, $added_files))
                            {
                                $file_data['file_name']=$file_name;
                                $file_data['version']=$file_info['version'];
                                $file_data['has_version']=true;
                                $file_data['finished']=0;
                                if($use_index)
                                {
                                    $file_data['index']=0;
                                }
                                $file_data['options']=$file_info;
                                $info[$file_info['file_type']]['files'][]= $file_data;
                                $added_files[]=$file_name;
                            }
                        }
                        else
                        {
                            $version=$restore_version;
                            if($version>=$file_info['version'])
                            {
                                if (!in_array($file_name, $added_files))
                                {
                                    $file_data['file_name']=$file_name;
                                    $file_data['version']=$version;
                                    $file_data['has_version']=true;
                                    $file_data['finished']=0;
                                    if($use_index)
                                    {
                                        $file_data['index']=0;
                                    }
                                    $file_data['options']=$file_info;
                                    $info[$file_info['file_type']]['files'][]= $file_data;
                                    $added_files[]=$file_name;
                                }
                            }
                        }
                    }
                    else
                    {
                        if (!in_array($file_name, $added_files))
                        {
                            $file_data['file_name']=$file_name;
                            $file_data['version']=0;
                            $file_data['finished']=0;
                            if($use_index)
                            {
                                $file_data['index']=0;
                            }
                            $file_data['options']=$file_info;
                            $info[$file_info['file_type']]['files'][]= $file_data;
                            $added_files[]=$file_name;
                        }
                    }
                }
            }
        }

        return $info;
    }

    public function get_has_child_file_info($info,$file_name,$file_info,&$added_files,$restore_version=0,$use_index=0)
    {
        foreach ($file_info['child_file'] as $child_file_name=>$child_file_info)
        {
            if(isset($child_file_info['file_type']))
            {
                if(isset($child_file_info['version']))
                {
                    $info=$this->get_file_version_info($info,$file_name,$file_info,$child_file_name,$child_file_info,$restore_version,$added_files,$use_index);
                }
                else
                {
                    if (!in_array($child_file_name, $added_files))
                    {
                        $file_data['file_name']=$child_file_name;
                        $file_data['version']=0;
                        $file_data['parent_file']=$file_name;
                        $file_data['has_child']=1;
                        $file_data['extract_child_finished']=0;
                        $file_data['finished']=0;
                        if($use_index)
                        {
                            $file_data['index']=0;
                        }
                        $file_data['options']=$file_info['child_file'][$child_file_name];
                        $info[$child_file_info['file_type']]['files'][]=$file_data;
                        $added_files[]=$child_file_name;
                    }
                }
            }
        }
        return $info;
    }

    public function get_file_version_info($info,$file_name,$file_info,$child_file_name,$child_file_info,$restore_version,&$added_files,$use_index)
    {
        if($restore_version===false||$restore_version>=$child_file_info['version'])
        {
            if (!in_array($child_file_name, $added_files))
            {
                $file_data['file_name']=$child_file_name;
                $file_data['version']=$child_file_info['version'];
                $file_data['has_version']=true;
                $file_data['parent_file']=$file_name;
                $file_data['has_child']=1;
                $file_data['finished']=0;
                if($use_index)
                {
                    $file_data['index']=0;
                }
                $file_data['options']=$file_info['child_file'][$child_file_name];
                $info[$child_file_info['file_type']]['files'][]=$file_data;
                $added_files[]=$child_file_name;
            }
        }

        return $info;
    }

    public function deal_restore_shutdown_error()
    {
        $error = error_get_last();

        if (!is_null($error))
        {
            if(preg_match('/Allowed memory size of.*$/', $error['message']))
            {
                $restore_task=get_option('wpvivid_restore_task',array());

                $restore_detail_options=$restore_task['restore_detail_options'];
                $db_connect_method=$restore_detail_options['db_connect_method'];
                if($db_connect_method === 'wpdb')
                {
                    $key=$restore_task['do_sub_task'];
                    if($key!==false)
                    {
                        if($restore_task['sub_tasks'][$key]['type']==='databases')
                        {
                            global $wpdb;
                            $wpdb->get_results('COMMIT');
                        }
                    }
                }

                $restore_task['status']='error';
                $restore_task['error']=$error['message'];
                $restore_task['error_memory_limit']=true;
                update_option('wpvivid_restore_task',$restore_task);
            }
        }

        die();
    }

    public function do_restore()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        if(!$check)
        {
            die();
        }
        ini_set('display_errors', false);
        error_reporting(-1);
        register_shutdown_function(array($this,'deal_restore_shutdown_error'));

        try
        {
            if($this->check_restore_task()==false)
            {
                $ret['result']='failed';
                $ret['error']='restore task has error';
                echo wp_json_encode($ret);
                $this->end_shutdown_function=true;
                die();
            }

            $this->_enable_maintenance_mode();

            $this->set_restore_environment();
            //$this->flush();

            $ret=$this->_do_restore();

            $this->_disable_maintenance_mode();
            echo wp_json_encode($ret);
        }
        catch (Exception $error)
        {
            $restore_task=get_option('wpvivid_restore_task',array());
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);

            $this->_disable_maintenance_mode();

            $ret['result']='failed';
            $ret['error']=$message;
            $restore_task['status']='error';
            $restore_task['error']=$ret['error'];
            update_option('wpvivid_restore_task',$restore_task);
            echo wp_json_encode($ret);
        }

        die();
    }

    public function _do_restore()
    {
        $ret['result']='success';

        $restore_task=get_option('wpvivid_restore_task',array());
        $this->log=new WPvivid_Log();
        $this->log->OpenLogFile( $restore_task['log'],'has_folder');

        if(empty($restore_task))
        {
            $ret['result']='failed';
            $ret['error']='task empty';
            return $ret;
        }

        $restore_task['do_sub_task']=false;

        foreach ($restore_task['sub_tasks'] as $key=>$sub_task)
        {
            if($sub_task['finished']==1)
            {
                continue;
            }
            else
            {
                $restore_task['do_sub_task']=$key;
                break;
            }
        }

        if($restore_task['do_sub_task']===false)
        {
            $ret['result']='failed';
            $ret['error']='no sub task';
            $restore_task['status']='error';
            $restore_task['error']=$ret['error'];
            update_option('wpvivid_restore_task',$restore_task);
            return $ret;
        }
        else
        {
            $restore_task['status']='doing sub task';
            $restore_task['update_time']=time();
            update_option('wpvivid_restore_task',$restore_task);
            return $this->do_sub_task();
        }
    }

    public function do_sub_task()
    {
        $restore_task=get_option('wpvivid_restore_task',array());

        $key=$restore_task['do_sub_task'];

        $sub_task=$restore_task['sub_tasks'][$key];

        if($sub_task['type']=='databases')
        {
            $this->log->WriteLog('Start restoring '.$sub_task['type'].'.','notice');

            $restore_db=new WPvivid_Restore_DB_2($this->log);
            $ret=$restore_db->restore($sub_task,$restore_task['backup_id']);
            if($ret['result']=='success')
            {
                $this->log->WriteLog('End restore '.$sub_task['type'].'.','notice');
                $restore_task=get_option('wpvivid_restore_task',array());
                $restore_task['sub_tasks'][$key]=$ret['sub_task'];
                $restore_task['status']='sub task finished';
                $restore_task['update_time']=time();
                update_option('wpvivid_restore_task',$restore_task);
            }
            else
            {
                $restore_task=get_option('wpvivid_restore_task',array());
                $restore_task['status']='error';
                $restore_task['error']=$ret['error'];
                wp_cache_flush();
                update_option('wpvivid_restore_task',$restore_task);
            }
        }
        else
        {
            $this->log->WriteLog('Start restoring '.$sub_task['type'].'.','notice');

            $restore_file=new WPvivid_Restore_File_2($this->log);
            $ret=$restore_file->restore($sub_task,$restore_task['backup_id']);
            if($ret['result']=='success')
            {
                $this->log->WriteLog('End restore '.$sub_task['type'].'.','notice');
                $restore_task=get_option('wpvivid_restore_task',array());
                $restore_task['sub_tasks'][$key]=$ret['sub_task'];
                $restore_task['status']='sub task finished';
                $restore_task['update_time']=time();
                update_option('wpvivid_restore_task',$restore_task);
            }
            else
            {
                $restore_task=get_option('wpvivid_restore_task',array());
                $restore_task['status']='error';
                $restore_task['error']=$ret['error'];
                $this->log->WriteLog('End restore '.$sub_task['type'].' error:'.$ret['error'],'notice');
                update_option('wpvivid_restore_task',$restore_task);
            }
        }


        return $ret;
    }

    public function check_restore_task()
    {
        $restore_task=get_option('wpvivid_restore_task',array());

        if(empty($restore_task))
        {
            return false;
        }

        $backup_id=$restore_task['backup_id'];
        $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
        if($backup===false)
        {
            return false;
        }

        if(empty($restore_task['sub_tasks']))
        {
            return false;
        }

        if($restore_task['do_sub_task']===false)
        {
            return true;
        }
        else
        {
            $sub_task_key=$restore_task['do_sub_task'];
            if(isset($restore_task['sub_tasks'][$sub_task_key]))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public function _enable_maintenance_mode()
    {
        //enable maintenance mode by create the .maintenance file.
        //If your wordpress version is greater than 4.6, use the enable_maintenance_mode filter to make our ajax request pass
        $this->init_filesystem();
        global $wp_filesystem;
        $file = $wp_filesystem->abspath() . '.maintenance';
        $maintenance_string = '<?php $upgrading = ' . (time()+1200) . ';';
        $maintenance_string.='global $wp_version;';
        $maintenance_string.='$version_check=version_compare($wp_version,4.6,\'>\' );';
        $maintenance_string.='if($version_check)';
        $maintenance_string.='{';
        $maintenance_string.='if(!function_exists(\'enable_maintenance_mode_filter\'))';
        $maintenance_string.='{';
        $maintenance_string.='function enable_maintenance_mode_filter($enable_checks,$upgrading)';
        $maintenance_string.='{';
        $maintenance_string.='if(is_admin()&&isset($_POST[\'wpvivid_restore\']))';
        $maintenance_string.='{';
        $maintenance_string.='return false;';
        $maintenance_string.='}';
        $maintenance_string.='return $enable_checks;';
        $maintenance_string.='}';
        $maintenance_string.='}';
        $maintenance_string.='add_filter( \'enable_maintenance_mode\',\'enable_maintenance_mode_filter\',10, 2 );';
        $maintenance_string.='}';
        $maintenance_string.='else';
        $maintenance_string.='{';
        $maintenance_string.='if(is_admin()&&isset($_POST[\'wpvivid_restore\']))';
        $maintenance_string.='{';
        $maintenance_string.='global $upgrading;';
        $maintenance_string.='$upgrading=0;';
        $maintenance_string.='return 1;';
        $maintenance_string.='}';
        $maintenance_string.='}';
        if ($wp_filesystem->exists( $file ) )
        {
            $wp_filesystem->delete($file);
        }
        $wp_filesystem->put_contents($file, $maintenance_string, FS_CHMOD_FILE);
    }

    public function _disable_maintenance_mode()
    {
        $this->init_filesystem();
        global $wp_filesystem;
        $file = $wp_filesystem->abspath() . '.maintenance';
        if ($wp_filesystem->exists( $file ))
        {
            $wp_filesystem->delete($file);
        }
    }

    public function init_filesystem()
    {
        $credentials = request_filesystem_credentials(wp_nonce_url(admin_url('admin.php')."?page=WPvivid", 'wpvivid-nonce'));

        if ( ! WP_Filesystem($credentials) )
        {
            return false;
        }
        return true;
    }

    public function set_restore_environment()
    {
        $restore_task=get_option('wpvivid_restore_task',array());

        $restore_detail_options=$restore_task['restore_detail_options'];
        $memory_limit = $restore_detail_options['restore_memory_limit'];
        $restore_max_execution_time= $restore_detail_options['restore_max_execution_time'];

        @set_time_limit($restore_max_execution_time);

        @ini_set('memory_limit', $memory_limit);
    }

    public function flush()
    {
        $ret['result'] = 'success';
        $txt = wp_json_encode($ret);

        if(!headers_sent()){
            header('Content-Length: '.( ( ! empty( $txt ) ) ? strlen( $txt ) : '0' ));
            header('Connection: close');
            header('Content-Encoding: none');
        }
        if (session_id())
            session_write_close();
        echo wp_json_encode($ret);

        if(function_exists('fastcgi_finish_request'))
        {
            fastcgi_finish_request();
        }
        else
        {
            if(ob_get_level()>0)
                ob_flush();
            flush();
        }
    }

    public function get_restore_progress()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        if(!$check)
        {
            die();
        }
        $restore_task=get_option('wpvivid_restore_task',array());

        if($this->check_restore_task()==false)
        {
            $ret['result']='failed';
            $ret['error']='restore task has error';
            $ret['test']=$restore_task;
            echo wp_json_encode($ret);
            die();
        }

        $ret['test']=$restore_task;
        if($restore_task['status']=='error')
        {
            $ret['result']='failed';
            $ret['error']=$restore_task['error'];
            echo wp_json_encode($ret);
            die();
        }

        $key=$restore_task['do_sub_task'];

        if($key===false)
        {
            $ret['result']='success';
            $ret['do_sub_task']=false;
            $ret['status']='ready';
        }
        else
        {
            if(isset($restore_task['sub_tasks'][$key]))
            {
                $sub_task=$restore_task['sub_tasks'][$key];
                $do_sub_task=$sub_task['type'];
                if($sub_task['finished']==1)
                {
                    $ret['result']='success';
                    $ret['do_sub_task']=$do_sub_task;
                    if($this->check_task_finished())
                    {
                        $ret['status']='task finished';
                    }
                    else
                    {
                        $ret['status']='sub task finished';
                    }

                }
                else
                {
                    $ret['result']='success';
                    $ret['do_sub_task']=$do_sub_task;

                    if($restore_task['status']=='sub task finished')
                    {
                        $ret['status']='sub task finished';
                    }
                    else
                    {
                        $common_setting = WPvivid_Setting::get_option('wpvivid_common_setting');

                        if(isset($common_setting['restore_max_execution_time']))
                        {
                            $setting_restore_max_execution_time = intval($common_setting['restore_max_execution_time']);
                        }
                        else{
                            $setting_restore_max_execution_time = WPVIVID_RESTORE_MAX_EXECUTION_TIME;
                        }

                        $restore_detail_options=$restore_task['restore_detail_options'];
                        $restore_max_execution_time= isset($restore_detail_options['restore_max_execution_time'])?$restore_detail_options['restore_max_execution_time']:$setting_restore_max_execution_time;


                        if(time()-$restore_task['update_time']>$restore_max_execution_time)
                        {
                            $restore_task['restore_timeout_count']++;
                            update_option('wpvivid_restore_task',$restore_task);
                            if($restore_task['restore_timeout_count']>6)
                            {
                                $ret['result']='failed';
                                $ret['error']='restore timeout';
                            }
                            else
                            {
                                $ret['status']='sub task finished';
                            }
                        }
                        else if(time()-$restore_task['update_time']>180)
                        {
                            $ret['status']='no response';
                        }
                        else
                        {
                            $ret['status']='doing sub task';
                        }
                    }
                }

                if($ret['result']=='success')
                {
                    $ret['main_msg']='doing restore '.$sub_task['type'];

                    $finished=0;
                    $total=count($restore_task['sub_tasks']);
                    $sub_tasks_progress=array();
                    $sub_tasks_progress_detail=array();
                    if($total==0)
                    {
                        $main_progress=0;
                    }
                    else
                    {
                        $sub_progress=0;
                        foreach ($restore_task['sub_tasks'] as $key=>$sub_task)
                        {
                            if($sub_task['type']=='themes')
                            {
                                $sub_progress_id='wpvivid_restore_themes_progress';
                                $sub_progress_detail_id='wpvivid_restore_themes_progress_detail';
                            }
                            else if($sub_task['type']=='plugin')
                            {
                                $sub_progress_id='wpvivid_restore_plugin_progress';
                                $sub_progress_detail_id='wpvivid_restore_plugin_progress_detail';
                            }
                            else if($sub_task['type']=='wp-content')
                            {
                                $sub_progress_id='wpvivid_restore_wp_content_progress';
                                $sub_progress_detail_id='wpvivid_restore_wp_content_progress_detail';
                            }
                            else if($sub_task['type']=='upload')
                            {
                                $sub_progress_id='wpvivid_restore_upload_progress';
                                $sub_progress_detail_id='wpvivid_restore_upload_progress_detail';
                            }
                            else if($sub_task['type']=='wp-core')
                            {
                                $sub_progress_id='wpvivid_restore_core_progress';
                                $sub_progress_detail_id='wpvivid_restore_core_progress_detail';
                            }
                            else if($sub_task['type']=='custom')
                            {
                                $sub_progress_id='wpvivid_restore_custom_progress';
                                $sub_progress_detail_id='wpvivid_restore_custom_progress_detail';
                            }
                            else if($sub_task['type']=='db'||$sub_task['type']=='databases')
                            {
                                $sub_progress_id='wpvivid_restore_databases_progress';
                                $sub_progress_detail_id='wpvivid_restore_databases_progress_detail';
                            }
                            else if($sub_task['type']=='additional_databases')
                            {
                                $sub_progress_id='wpvivid_restore_additional_db_progress';
                                $sub_progress_detail_id='wpvivid_restore_additional_db_progress_detail';
                            }
                            else
                            {
                                $sub_progress_id='';
                                $sub_progress_detail_id='';
                            }

                            if($sub_task['finished']==1)
                            {
                                $finished++;
                                $sub_progress+=100;
                                $sub_task_progress='Completed - 100%<span class="dashicons dashicons-yes" style="color:#8bc34a;"></span>';
                            }
                            else
                            {
                                if($sub_task['unzip_file']['last_action']=='waiting...')
                                {
                                    $sub_task_progress='waiting...';
                                }
                                else if($sub_task['type']=='databases')
                                {
                                    if($sub_task['unzip_file']['unzip_finished']==0)
                                    {
                                        $sub_task_progress= $sub_task['unzip_file']['last_action'].' - 0%';
                                    }
                                    else
                                    {
                                        if($restore_task['is_migrate'])
                                        {
                                            $file_size=0;
                                            $read_size=0;

                                            foreach ($sub_task['exec_sql']['sql_files'] as $sql_file)
                                            {
                                                $file_size+=$sql_file['sql_file_size'];
                                                $read_size+=$sql_file['sql_offset'];
                                            }

                                            $progress1=intval(($read_size/ $file_size)*50);
                                            $progress2=0;
                                            if(!empty($sub_task['exec_sql']['replace_tables']))
                                            {
                                                $need_replace_table = sizeof($sub_task['exec_sql']['replace_tables']);
                                                $replaced_tables=0;
                                                foreach ($sub_task['exec_sql']['replace_tables'] as $replace_table)
                                                {
                                                    if ($replace_table['finished'] == 1)
                                                    {
                                                        $replaced_tables++;
                                                    }
                                                }
                                                $progress2=intval(($replaced_tables/ $need_replace_table)*50);
                                            }

                                            $progress=$progress1+$progress2;

                                            $sub_progress+=$progress;
                                            $sub_task_progress= $sub_task['exec_sql']['last_action'].' - '.$progress.'%';
                                        }
                                        else
                                        {
                                            $file_size=0;
                                            $read_size=0;

                                            foreach ($sub_task['exec_sql']['sql_files'] as $sql_file)
                                            {
                                                $file_size+=$sql_file['sql_file_size'];
                                                $read_size+=$sql_file['sql_offset'];
                                            }

                                            $progress=intval(($read_size/ $file_size)*100);

                                            $sub_progress+=$progress;
                                            $sub_task_progress= $sub_task['exec_sql']['last_action'].' - '.$progress.'%';
                                        }
                                    }
                                }
                                else
                                {
                                    $files=$sub_task['unzip_file']['files'];
                                    $files_finished=0;
                                    $files_total=count($sub_task['unzip_file']['files']);
                                    foreach ($files as $index=>$file)
                                    {
                                        if ($file['finished'] == 1)
                                        {
                                            $files_finished++;
                                        }
                                    }

                                    if(isset($sub_task['unzip_file']['sum'])&&$sub_task['unzip_file']['start'])
                                    {
                                        $sum=$sub_task['unzip_file']['sum'];
                                        $start=$sub_task['unzip_file']['start'];

                                        if($sum>0)
                                        {
                                            $file_progress=intval((($start/$sum)*100)/$files_total);
                                        }
                                        else
                                        {
                                            $file_progress=0;
                                        }
                                    }
                                    else
                                    {
                                        $file_progress=0;
                                    }
                                    $progress=intval(($files_finished/ $files_total)*100)+$file_progress;
                                    $progress=min(100,$progress);
                                    $sub_progress+=$progress;
                                    $sub_task_progress= $sub_task['unzip_file']['last_action'].' - '.$progress.'%';
                                }
                            }

                            if(!empty($sub_progress_id))
                            {
                                $sub_tasks_progress[$sub_progress_id]=$sub_task_progress;
                            }

                            if(!empty($sub_progress_id))
                            {
                                $sub_tasks_progress_detail[$sub_progress_detail_id]['html']=$sub_task['last_msg'];
                                if($do_sub_task==$sub_task['type'])
                                {
                                    $sub_tasks_progress_detail[$sub_progress_detail_id]['show']=true;
                                }
                                else
                                {
                                    $sub_tasks_progress_detail[$sub_progress_detail_id]['show']=false;
                                }
                            }
                        }
                        $main_progress=intval($sub_progress/$total);
                        //$main_progress=intval(($finished/$total)*100);
                        $main_progress=min($main_progress,100);
                    }
                    $ret['sub_tasks_progress']=$sub_tasks_progress;
                    $ret['sub_tasks_progress_detail']=$sub_tasks_progress_detail;

                    $ret['main_task_progress_total']=$total;
                    $ret['main_task_progress_finished']=$finished;
                    $ret['main_progress']='<span class="action-progress-bar-percent wpvivid-span-processed-restore-percent-progress" style="width: '.$main_progress.'%; display:block; height:1.5em; border-radius:0; padding-left:0.5em;">'.$main_progress.'% completed</span>';

                    $buffer = '';
                    if(file_exists($restore_task['log']))
                    {
                        $file = fopen($restore_task['log'], 'r');

                        if ($file)
                        {
                            while (!feof($file)) {
                                $buffer .= fread($file, 1024);
                            }
                            fclose($file);
                        }
                    }
                    $ret['log'] = $buffer;

                    if(strlen($ret['log']) > 100*1024)
                    {
                        $ret['log']=substr($ret['log'], -100*1024);
                    }
                }
            }
            else
            {
                $ret['result']='failed';
                $ret['error']='sub task not found';
            }
        }

        echo wp_json_encode($ret);
        die();
    }

    public function check_task_finished()
    {
        $restore_task=get_option('wpvivid_restore_task',array());

        $finished=false;

        foreach ($restore_task['sub_tasks'] as $sub_task)
        {
            if($sub_task['finished']==1)
            {
                $finished=true;
            }
            else
            {
                $finished=false;
                break;
            }
        }
        return $finished;
    }

    public function finish_restore()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        if(!$check)
        {
            die();
        }
        register_shutdown_function(array($this,'deal_restore_finish_shutdown_error'));
        ini_set('display_errors', 0);

        echo '<p style="font-size:1.5em;"><span>The restoration has been successfully completed.</span></p>';

        $this->_disable_maintenance_mode();
        $this->write_litespeed_rule(false);

        if(file_exists(WPMU_PLUGIN_DIR.'/a-wpvivid-restore-mu-plugin-check.php'))
        {
            @wp_delete_file(WPMU_PLUGIN_DIR.'/a-wpvivid-restore-mu-plugin-check.php');
        }

        $plugins= get_option( 'wpvivid_save_active_plugins', array() );

        $ret=$this->check_restore_db();

        $this->delete_temp_files();
        delete_transient( 'wp_core_block_css_files' );

        $restore_task=get_option('wpvivid_restore_task',array());

        if($restore_task['is_migrate'])
        {
            $this->check_force_ssl();
            $this->check_admin_plugins();
            $this->flush_elementor_cache();
            $this->regenerate_css_files();
            if(!is_multisite())
            {
                if (function_exists('save_mod_rewrite_rules'))
                {
                    if(isset($restore_task['restore_options']['restore_detail_options']['restore_htaccess'])&&$restore_task['restore_options']['restore_detail_options']['restore_htaccess'])
                    {
                        //
                    }
                    else
                    {
                        if (file_exists(get_home_path() . '.htaccess'))
                        {
                            $htaccess_data = file_get_contents(get_home_path() . '.htaccess');
                            $line = '';
                            if (preg_match('#AddHandler application/x-httpd-php.*#', $htaccess_data, $matcher))
                            {
                                $line = PHP_EOL . $matcher[0];

                                if (preg_match('#<IfModule mod_suphp.c>#', $htaccess_data, $matcher)) {
                                    $line .= PHP_EOL . '<IfModule mod_suphp.c>';
                                    if (preg_match('#suPHP_ConfigPath .*#', $htaccess_data, $matcher)) {
                                        $line .= PHP_EOL . $matcher[0];
                                    }
                                    $line .= PHP_EOL . '</IfModule>';
                                }
                            }
                            else if (preg_match('#AddHandler application/x-httpd-ea-php.*#', $htaccess_data, $matcher))
                            {
                                $line_temp = PHP_EOL . $matcher[0];

                                if (preg_match('#<IfModule mime_module>#', $htaccess_data, $matcher))
                                {
                                    $line .= PHP_EOL . '<IfModule mime_module>';
                                    $line .= $line_temp.PHP_EOL;
                                    $line .= PHP_EOL . '</IfModule>';
                                }
                            }
                            @rename(get_home_path() . '.htaccess', get_home_path() . '.htaccess_old');
                            save_mod_rewrite_rules();
                            if (!empty($line))
                                file_put_contents(get_home_path() . '.htaccess', $line, FILE_APPEND);
                        }
                        else
                        {
                            save_mod_rewrite_rules();
                        }
                    }

                    if(file_exists(get_home_path() . '.user.ini'))
                    {
                        @rename(get_home_path() . '.user.ini', get_home_path() . '.user.ini_old');
                        save_mod_rewrite_rules();
                    }
                }

            }
        }

        if($ret['has_db'])
        {
            $this->active_plugins();
        }
        else
        {
            $this->active_plugins($plugins);
        }


        if($restore_task['is_migrate'])
        {
            //$html.='<p style="font-size:1.5em;"><span>Save permalinks structure:</span><span><a href="'.admin_url('options-permalink.php').'" target="_blank">click here</a></span></p>';
            if($this->check_oxygen())
            {
                echo '<p style="font-size:1.5em;"><span>The restoration is almost complete, but there is a little bit job to do.</span></p>';
                echo '<p style="font-size:1.5em;"><span>We found that your website is using the Oxygen page builder. In order to restore this backup perfectly, please follow</span><span><a href="https://oxygenbuilder.com/documentation/other/importing-exporting/#resigning" target="_blank"> the guide </a>to regenerate the css.</span></p>';
            }

            if($this->check_divi())
            {
                $this->clean_divi_cache();
                echo '<p style="font-size:1.5em;"><span>The restoration is almost complete, but there is a little bit job to do.</span></p>';
                echo '<p style="font-size:1.5em;"><span>We found that your website is using the Divi theme. In order to restore this backup perfectly,</span><span>please follow<a href="https://divitheme.net/clear-divi-cache/" target="_blank"> the guide </a>to clean up the Divi cache</span></p>';
            }
        }

        if(isset( $restore_task['restore_options']['delete_local'])&& $restore_task['restore_options']['delete_local'])
        {
            $backup_id=$restore_task['backup_id'];
            $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
            if($backup!==false)
            {
                $backup_item = new WPvivid_Backup_Item($backup);
                if($backup_item->get_remote()!==false)
                {
                    $files=$backup_item->get_files(true);
                    foreach ($files as $file)
                    {
                        @wp_delete_file($file);
                    }
                }
            }
        }

        $siteurl = get_option( 'siteurl' );
        echo '<p style="font-size:1.5em;"><span><a href="'.esc_url($siteurl).'" target="_blank">Visit Site</a></span></p>';

        delete_option('wpvivid_restore_task');

        wp_cache_flush();
        die();
    }

    public function check_restore_db()
    {
        $has_db=false;

        $restore_task=get_option('wpvivid_restore_task',array());
        $this->log=new WPvivid_Log();
        $this->log->OpenLogFile( $restore_task['log'],'has_folder');
        foreach ($restore_task['sub_tasks'] as $sub_task)
        {
            if($sub_task['type']=='databases')
            {
                $has_db=true;

                $restore_db=new WPvivid_Restore_DB_2($this->log);
                $current_setting = WPvivid_Setting::export_setting_to_json();

                $ret=$restore_db->rename_db($sub_task);
                WPvivid_Setting::import_json_to_setting($current_setting);
                do_action('wpvivid_reset_schedule');
                do_action('wpvivid_do_after_restore_db');

                if($restore_task['is_migrate'] == '1')
                {
                    $option_name = 'wpvivid_staging_task_list';
                    global $wpdb;
                    $result = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name = %s", $option_name));
                    if(!$result)
                    {
                        $this->log->WriteLog('Delete migration option failed.', 'notice');
                    }
                }

                if($ret['result']!='success')
                {
                    $this->log->WriteLog('Restore database failed:'.$ret['error'],'notice');
                    $restore_db->remove_tmp_table($sub_task);
                    return $ret;
                }
                break;
            }
        }


        $ret['result']='success';
        $ret['has_db']=$has_db;
        return $ret;
    }

    public function delete_temp_files()
    {
        $restore_task=get_option('wpvivid_restore_task',array());
        $this->log=new WPvivid_Log();
        $this->log->OpenLogFile( $restore_task['log'],'has_folder');
        $this->log->WriteLog('Deleting temp files.','notice');
        $backup_id=$restore_task['backup_id'];
        $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
        $backup_item=new WPvivid_Backup_Item($backup);
        foreach($restore_task['sub_tasks'] as $key => $task)
        {
            foreach ($task['unzip_file']['files'] as $file)
            {
                if(isset($file['has_child']))
                {
                    $path= $backup_item->get_local_path().$file['file_name'];
                    //$this->log->WriteLog('clean file:'.$path,'notice');
                    if(file_exists($path))
                    {
                        @wp_delete_file($path);
                    }
                }
            }
        }
    }

    public function restore_failed()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        if(!$check)
        {
            die();
        }
        register_shutdown_function(array($this,'deal_restore_finish_shutdown_error'));

        //echo '<p style="font-size:1.5em;"><span>Please adjust the advanced settings before restoring and retry.</span></p>';

        $this->_disable_maintenance_mode();
        $this->write_litespeed_rule(false);
        if(file_exists(WPMU_PLUGIN_DIR.'/a-wpvivid-restore-mu-plugin-check.php'))
        {
            @wp_delete_file(WPMU_PLUGIN_DIR.'/a-wpvivid-restore-mu-plugin-check.php');
        }
        $plugins= get_option( 'wpvivid_save_active_plugins', array() );

        $this->delete_temp_tables();
        $this->delete_temp_files();

        $this->active_plugins($plugins);

        $restore_task=get_option('wpvivid_restore_task',array());

        //$restore_detail_options=$restore_task['restore_detail_options'];
        //$unzip_files_pre_request=$restore_detail_options['unzip_files_pre_request'];
        echo 'Restore failed. ';
        if($restore_task['status']=='error')
        {
            echo 'Error:'.esc_html($restore_task['error']).' ';
            if(isset($restore_task['error_memory_limit']))
            {
                echo 'Memory exhausted during restoring..';
            }
            else if(isset($restore_task['error_mu_require_file']))
            {
                echo 'Restore must-use plugin '.esc_html($restore_task['error_mu_require_file']).' error.Plugin require file not found..';
            }
        }
        else
        {
            $key=$restore_task['do_sub_task'];

            if($key!==false)
            {
                if(isset($restore_task['sub_tasks'][$key]))
                {
                    //$error_msg='restore sub task '.$restore_task['sub_tasks'][$key]['type'].' timeout.';
                    if($restore_task['sub_tasks'][$key]['type']==='databases'||$restore_task['sub_tasks'][$key]['type']==='additional_databases')
                    {
                        echo 'Sql file importing time out.';
                        //$error_msg.='<p style="font-size:1.5em;">Pleases try to increase your max_allowed_packet(recommend 32M)</p>';
                        //$error_msg.='<p style="font-size:1.5em;">or reduce SQL buffer will be processed every PHP request(recommend 5M)</p>';
                        //$error_msg.='<p style="font-size:1.5em;">or reduce maximum rows of data in MYSQL table will be imported every time when restoring(recommend 10000)</p>';
                    }
                    else
                    {
                        echo 'File extracting time out.';
                        //$error_msg.='<p style="font-size:1.5em;">Pleases try to check user unzip files using index,and set files are unzipped every PHP request(recommend 1000)</p>';
                        //$error_msg.='<p style="font-size:1.5em;">and increase your PHP - max execution time(900s)</p>';
                    }
                }
                else
                {
                    //$error_msg='';
                    echo 'Restoring time out.';
                }
            }
        }

        delete_option('wpvivid_restore_task');
        wp_cache_flush();

        die();
    }

    public function deal_restore_finish_shutdown_error()
    {
        $error = error_get_last();
        if (!is_null($error))
        {
            if (empty($error) || !in_array($error['type'], array(E_ERROR,E_RECOVERABLE_ERROR,E_CORE_ERROR,E_COMPILE_ERROR), true))
            {
                $error = false;
            }

            if ($error !== false)
            {
                $message = 'type: '. $error['type'] . ', ' . $error['message'];
                echo '<p style="font-size:1.5em;">Error Info:'.esc_html($message).'</p>';;
            }
        }

        die();
    }

    public function delete_temp_tables()
    {
        $restore_task=get_option('wpvivid_restore_task',array());
        $this->log=new WPvivid_Log();
        $this->log->OpenLogFile( $restore_task['log'],'has_folder');
        foreach ($restore_task['sub_tasks'] as $sub_task)
        {
            if($sub_task['type']=='databases')
            {
                $restore_db=new WPvivid_Restore_DB_2($this->log);
                $restore_db->remove_tmp_table($sub_task);
            }
        }

        $ret['result']='success';
        return $ret;
    }

    public function check_force_ssl()
    {
        $plugins=array();
        if ( ! is_ssl() )
        {
            $plugins[]='really-simple-ssl/rlrsssl-really-simple-ssl.php';
            $plugins[]='wordpress-https/wordpress-https.php';
            $plugins[]='wp-force-ssl/wp-force-ssl.php';
            $plugins[]='force-https-littlebizzy/force-https.php';

            $current = get_option( 'active_plugins', array() );

            foreach ( $plugins as $plugin )
            {
                if ( ( $key = array_search( $plugin, $current ) ) !== false )
                {
                    unset( $current[ $key ] );
                }
            }

            update_option( 'active_plugins', $current );

            if ( get_option( 'woocommerce_force_ssl_checkout' ) )
            {
                update_option( 'woocommerce_force_ssl_checkout', 'no' );
            }
        }

    }

    public function check_admin_plugins()
    {
        $plugins=array();
        $plugins[]='wps-hide-login/wps-hide-login.php';
        $plugins[]='lockdown-wp-admin/lockdown-wp-admin.php';
        $plugins[]='rename-wp-login/rename-wp-login.php';
        $plugins[]='change-wp-admin-login/change-wp-admin-login.php';
        $plugins[]='hide-my-wp/index.php';
        $plugins[]='hide-login-page/hide-login-page.php';
        $plugins[]='wp-hide-security-enhancer/wp-hide.php';
        //
        $current = get_option( 'active_plugins', array() );

        foreach ( $plugins as $plugin )
        {
            if ( ( $key = array_search( $plugin, $current ) ) !== false )
            {
                unset( $current[ $key ] );
            }
        }

        update_option( 'active_plugins', $current );
    }

    public function flush_elementor_cache()
    {
        $wp_upload_dir=wp_upload_dir( null, false );
        $path =  $wp_upload_dir['basedir'] . '/elementor/css/' . '*';

        foreach ( glob( $path ) as $file_path ) {
            wp_delete_file( $file_path );
        }
        delete_post_meta_by_key( '_elementor_css' );
        delete_option( '_elementor_global_css' );
        delete_option( 'elementor-custom-breakpoints-files' );

        delete_option( '_elementor_assets_data' );
        delete_post_meta_by_key( '_elementor_inline_svg' );
    }

    public function regenerate_css_files()
    {
        delete_option( 'generateblocks_dynamic_css_posts' );
    }

    public function active_plugins($plugins=array())
    {
        wp_cache_flush();

        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $current = get_option( 'active_plugins', array() );
        $plugin_list=array();
        $plugin_list[]='wpvivid-backuprestore/wpvivid-backuprestore.php';
        $plugin_list=apply_filters('wpvivid_enable_plugins_list',$plugin_list);

        $current=array_merge($plugin_list,$current);
        // Add plugins
        if(!empty($plugins))
        {
            foreach ( $plugins as $plugin )
            {
                if ( ! in_array( $plugin, $current ) && ! is_wp_error( validate_plugin( $plugin ) ) ) {
                    $current[] = $plugin;
                }
            }
        }
        activate_plugins($current,'',false,true);
    }

    public function check_oxygen()
    {
        if (!function_exists('get_plugins'))
        {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( ( $plugins = get_plugins() ) )
        {
            foreach ( $plugins as $key => $plugin )
            {
                if ( $key === 'oxygen/functions.php' )
                {
                    return true;
                }
            }
        }
        return false;
    }

    public function check_divi()
    {
        $themes=wp_get_themes();
        foreach ($themes as $key=>$theme)
        {
            if ( $key === 'Divi' )
            {
                return true;
            }
        }
        return false;
    }

    public function clean_divi_cache()
    {
        $_post_id = '*';
        $_owner   = '*';
        $_slug    = '*';

        $cache_dir= WP_CONTENT_DIR.DIRECTORY_SEPARATOR.'et-cache';

        $files = array_merge(
        // Remove any CSS files missing a parent folder.
            (array) glob( "{$cache_dir}/et-{$_owner}-*" ),
            // Remove CSS files for individual posts or all posts if $post_id set to 'all'.
            (array) glob( "{$cache_dir}/{$_post_id}/et-{$_owner}-{$_slug}*" ),
            // Remove CSS files that contain theme builder template CSS.
            // Multiple directories need to be searched through since * doesn't match / in the glob pattern.
            (array) glob( "{$cache_dir}/*/et-{$_owner}-{$_slug}-*tb-{$_post_id}*" ),
            (array) glob( "{$cache_dir}/*/*/et-{$_owner}-{$_slug}-*tb-{$_post_id}*" ),
            (array) glob( "{$cache_dir}/*/*/*/et-{$_owner}-{$_slug}-*tb-{$_post_id}*" ),
            (array) glob( "{$cache_dir}/*/et-{$_owner}-{$_slug}-*tb-for-{$_post_id}*" ),
            (array) glob( "{$cache_dir}/*/*/et-{$_owner}-{$_slug}-*tb-for-{$_post_id}*" ),
            (array) glob( "{$cache_dir}/*/*/*/et-{$_owner}-{$_slug}-*tb-for-{$_post_id}*" ),
            // Remove Dynamic CSS files for categories, tags, authors, archives, homepage post feed and search results.
            (array) glob( "{$cache_dir}/taxonomy/*/*/et-{$_owner}-dynamic*" ),
            (array) glob( "{$cache_dir}/author/*/et-{$_owner}-dynamic*" ),
            (array) glob( "{$cache_dir}/archive/et-{$_owner}-dynamic*" ),
            (array) glob( "{$cache_dir}/search/et-{$_owner}-dynamic*" ),
            (array) glob( "{$cache_dir}/notfound/et-{$_owner}-dynamic*" ),
            (array) glob( "{$cache_dir}/home/et-{$_owner}-dynamic*" )
        );

        $this->_remove_files_in_directory( $files, $cache_dir );

        $this->remove_empty_directories($cache_dir );

        delete_option( '_et_builder_global_feature_cache' );

        $post_meta_caches = array(
            'et_enqueued_post_fonts',
            '_et_dynamic_cached_shortcodes',
            '_et_dynamic_cached_attributes',
            '_et_builder_module_features_cache',
        );

        // Clear post meta caches.
        foreach ( $post_meta_caches as $post_meta_cache ) {
            if ( ! empty( $post_id ) ) {
                delete_post_meta( $post_id, $post_meta_cache );
            } else {
                delete_post_meta_by_key( $post_meta_cache );
            }
        }
    }

    public function remove_empty_directories( $path ) {
        $path = realpath( $path );

        if ( empty( $path ) ) {
            // $path doesn't exist
            return;
        }

        $path        = $this->normalize_path( $path );
        $content_dir = $this->normalize_path( WP_CONTENT_DIR );

        if ( 0 !== strpos( $path, $content_dir ) || $content_dir === $path ) {
            return;
        }

        $this->_remove_empty_directories($path);
    }

    public function _remove_empty_directories($path)
    {
        if ( ! is_dir( $path ) ) {
            return false;
        }

        $empty              = true;
        $directory_contents = glob( untrailingslashit( $path ) . '/*' );

        foreach ( (array) $directory_contents as $item ) {
            if ( ! $this->_remove_empty_directories( $item ) ) {
                $empty = false;
            }
        }

        return $empty ? @rmdir( $path ) : false;
    }

    public function _remove_files_in_directory( $files, $cache_dir )
    {
        $cache_dir=$this->normalize_path( $cache_dir );

        foreach ( $files as $file )
        {
            $file =$this->normalize_path( $file );

            if ( ! $this->starts_with( $file, $cache_dir ) ) {
                // File is not located inside cache directory so skip it.
                continue;
            }

            if ( is_file( $file ) )
            {
                @wp_delete_file($file);
            }
        }
    }

    public function starts_with( $string, $substring ) {
        return 0 === strpos( $string, $substring );
    }

    public function normalize_path( $path = '' )
    {
        $path = (string) $path;
        $path = str_replace( '..', '', $path );

        if ( function_exists( 'wp_normalize_path' ) ) {
            return wp_normalize_path( $path );
        }

        return str_replace( '\\', '/', $path );
    }

}