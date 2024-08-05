<?php

if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

class WPvivid_Backup_Task_2
{
    public $task;
    public $task_id;
    public $current_job;
    public $current_db;

    public function __construct($task_id=false,$task=array())
    {
        $this->task_id=false;
        $this->current_job=false;

        if(empty($task))
        {
            if(!empty($task_id))
            {
                $default = array();
                $options = get_option('wpvivid_task_list', $default);
                if(isset($options[$task_id]))
                {
                    $this->task=$options[$task_id];
                    $this->task_id=$task_id;
                }
            }
        }
        else
        {
            $this->task_id=$task_id;
            $this->task=$task;
        }

    }

    public function new_backup_task($options,$settings)
    {
        $this->task=array();
        $id=uniqid('wpvivid-');
        $this->task['id']=$id;
        $this->task['type']=isset($options['type'])?$options['type']:'';

        if(isset($options['lock']))
        {
            $this->task['options']['lock']=$options['lock'];
        }
        else
        {
            $this->task['options']['lock']=0;
        }

        $this->task['status']['task_start_time']=time();
        $this->task['status']['task_end_time']=time();
        $this->task['status']['start_time']=time();
        $this->task['status']['run_time']=time();
        $this->task['status']['timeout']=time();
        $this->task['status']['str']='ready';
        $this->task['status']['resume_count']=0;

        $options['save_local']=isset($settings['save_local'])?$settings['save_local']:false;
        $this->set_backup_option($options);

        if(isset($options['remote']))
        {
            if($options['remote']=='1')
            {
                if(isset($options['remote_options']))
                {
                    $this->task['options']['remote_options']=$options['remote_options'];
                }
                else
                {
                    $this->task['options']['remote_options']=WPvivid_Setting::get_remote_options();
                }

            }
            else {
                $this->task['options']['remote_options']=false;
            }
        }
        else
        {
            $this->task['options']['remote_options']=false;
        }

        $this->task['setting']=$settings;

        $this->task['data']['doing']='backup';
        $this->task['data']['backup']['doing']='';
        $this->task['data']['backup']['progress']=0;
        $this->task['data']['backup']['sub_job']=array();
        $this->task['data']['upload']['doing']='';
        $this->task['data']['upload']['finished']=0;
        $this->task['data']['upload']['progress']=0;
        $this->task['data']['upload']['job_data']=array();
        $this->task['data']['upload']['sub_job']=array();



        $this->init_backup_job($options['backup_files']);
        $this->task['options']['backup_files']=$options['backup_files'];
        delete_option('wpvivid_task_list');
        WPvivid_Setting::update_task($id,$this->task);

        $ret['result']='success';
        $ret['task']=$this->task;
        $ret['task_id']=$this->task['id'];

        return $ret;
    }

    public function get_start_time()
    {
        return $this->task['status']['task_start_time'];
    }

    public function get_end_time()
    {
        return $this->task['status']['task_end_time'];
    }

    public function update_end_time()
    {
        $this->task['status']['task_end_time']=time();
        $this->update_task();
    }

    public function set_backup_option($options)
    {
        $offset=get_option('gmt_offset');
        $this->task['options']=$options;

        $general_setting=WPvivid_Setting::get_setting(true, "");

        if(isset($options['backup_prefix']) && !empty($options['backup_prefix']))
        {
            $this->task['options']['backup_prefix']=$options['backup_prefix'];
        }
        else
        {
            if(isset($general_setting['options']['wpvivid_common_setting']['domain_include'])&&$general_setting['options']['wpvivid_common_setting']['domain_include'])
            {
                $check_addon = apply_filters('wpvivid_check_setting_addon', 'not_addon');
                if (isset($general_setting['options']['wpvivid_common_setting']['backup_prefix']) && $check_addon == 'addon')
                {
                    $this->task['options']['backup_prefix'] = $general_setting['options']['wpvivid_common_setting']['backup_prefix'];
                }
                else {
                    $home_url_prefix = get_home_url();
                    $home_url_prefix = $this->parse_url_all($home_url_prefix);
                    $this->task['options']['backup_prefix'] = $home_url_prefix;
                }
            }
            else
            {
                $this->task['options']['backup_prefix']='';
            }
        }

        if(empty($this->task['options']['backup_prefix']))
        {
            $this->task['options']['file_prefix'] = $this->task['id'] . '_' . gmdate('Y-m-d-H-i', time()+$offset*60*60);
        }
        else
        {
            $this->task['options']['file_prefix'] =  $this->task['options']['backup_prefix'] . '_' . $this->task['id'] . '_' . gmdate('Y-m-d-H-i', time()+$offset*60*60);
        }
        $this->task['options']['file_prefix'] = apply_filters('wpvivid_backup_file_prefix',$this->task['options']['file_prefix'],$this->task['options']['backup_prefix'],$this->task['id'],$this->task['status']['start_time']);

        $this->task['options']['log_file_name']=$this->task['id'].'_backup';
        $log=new WPvivid_Log();
        $log->CreateLogFile($this->task['options']['log_file_name'],'no_folder','backup');
        $this->task['options']['log_file_path']=$log->log_file;
        $this->task['options']['prefix']=$this->task['options']['file_prefix'];
        $this->task['options']['dir']=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir();
        $this->task['options']['backup_dir']=WPvivid_Setting::get_backupdir();

        $exclude_files=isset($options['exclude_files'])?$options['exclude_files']:array();
        $exclude_files=apply_filters('wpvivid_default_exclude_folders',$exclude_files);
        $this->task['options']['exclude-tables']=isset($options['exclude-tables'])?$options['exclude-tables']:array();
        $this->task['options']['exclude-tables']=$this->default_exclude_table($this->task['options']['exclude-tables']);

        $this->task['options']['include-tables']=isset($options['include-tables'])?$options['include-tables']:array();

        $this->task['options']['exclude_files']=$this->get_exclude_files($exclude_files);
        $this->task['options']['include_files']=$this->get_include_files();

        $this->task['options']['include_plugins']=isset($options['include_plugins'])?$options['include_plugins']:array();
        $this->task['options']['include_themes']=isset($options['include_themes'])?$options['include_themes']:array();

        if(isset($options['local']))
        {
            if($options['local']=='1')
            {
                $this->task['options']['save_local']=1;
            }
            else
            {
                //$this->task['options']['save_local']=0;
                $this->task['options']['save_local'] =isset($options['save_local'])?$options['save_local']:false;
            }
        }
        else
        {
            $this->task['options']['save_local']=1;
        }
        $this->task['options']['backup_options']['compress']['compress_type']='zip';
        $log->CloseFile();

        //$this->task['options']['remote_options'] = apply_filters('wpvivid_set_remote_options', $this->task['options']['remote_options'],$this->task['options']);
    }

    public function default_exclude_table($exclude_tables)
    {
        global $wpdb;
        $exclude_tables[]=$wpdb->base_prefix."wpvivid_log";
        $exclude_tables[]=$wpdb->base_prefix."wpvivid_increment_big_ids";
        $exclude_tables[]=$wpdb->base_prefix."wpvivid_options";
        $exclude_tables[]=$wpdb->base_prefix."wpvivid_record_task";
        $exclude_tables[]=$wpdb->base_prefix."wpvivid_merge_db";
        $exclude_tables[]=$wpdb->base_prefix."wpvivid_merge_ids";
        return $exclude_tables;
    }

    public function parse_url_all($url)
    {
        $parse = wp_parse_url($url);
        //$path=str_replace('/','_',$parse['path']);
        $path = '';
        if(isset($parse['path'])) {
            $parse['path'] = str_replace('/', '_', $parse['path']);
            $path = $parse['path'];
        }
        return $parse['host'].$path;
    }

    public function init_backup_job($backup_content)
    {
        $index=0;
        $this->task['jobs']=array();
        if($backup_content==='files')
        {
            $this->task['jobs'][$index]['backup_type']='backup_themes';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['index']=0;
            $index++;

            $this->task['jobs'][$index]['backup_type']='backup_plugin';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['index']=0;
            $index++;

            $this->task['jobs'][$index]['backup_type']='backup_uploads';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['index']=0;
            $index++;

            $this->task['jobs'][$index]['backup_type']='backup_content';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['index']=0;
            $index++;

            $this->task['jobs'][$index]['backup_type']='backup_core';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['index']=0;
            $index++;
        }
        else if($backup_content==='files+db')
        {
            $this->task['jobs'][$index]['backup_type']='backup_db';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['mysql_file_index']=1;
            $index++;

            $this->task['jobs'][$index]['backup_type']='backup_themes';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['index']=0;
            $index++;

            $this->task['jobs'][$index]['backup_type']='backup_plugin';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['index']=0;
            $index++;

            $this->task['jobs'][$index]['backup_type']='backup_uploads';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['index']=0;
            $index++;

            $this->task['jobs'][$index]['backup_type']='backup_content';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['index']=0;
            $index++;

            $this->task['jobs'][$index]['backup_type']='backup_core';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['index']=0;
            $index++;
        }
        else if($backup_content==='db')
        {
            $this->task['jobs'][$index]['backup_type']='backup_db';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['mysql_file_index']=1;
            $index++;
        }

        $is_merge=$this->task['setting']['is_merge'];
        if(count($this->task['jobs'])==1)
        {
            $is_merge=false;
        }

        if($is_merge)
        {
            $this->task['jobs'][$index]['backup_type']='backup_merge';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $this->task['jobs'][$index]['file_index']=1;
            $this->task['jobs'][$index]['child_file']=array();
            $this->task['jobs'][$index]['index']=0;
        }
    }

    public function get_exclude_files($exclude_files=array())
    {
        $exclude_plugins=array();
        $exclude_plugins=apply_filters('wpvivid_exclude_plugins',$exclude_plugins);
        $exclude_regex=array();
        foreach ($exclude_plugins as $exclude_plugin)
        {
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path(WP_PLUGIN_DIR.'/'.$exclude_plugin), '/').'#';
        }
        foreach ($exclude_files as $exclude_file)
        {
            if($exclude_file['type']=='file'||$exclude_file['type']=='folder')
            {
                if(file_exists($exclude_file['path']))
                {
                    $exclude_regex[]='#^'.preg_quote($this -> transfer_path($exclude_file['path']), '/').'#';
                }
                else
                {
                    $path=WP_CONTENT_DIR.'/'.$exclude_file['path'];
                    if(file_exists($path))
                    {
                        $exclude_regex[]='#^'.preg_quote($this -> transfer_path($path), '/').'#';
                    }
                }
            }
            else if($exclude_file['type']=='ext')
            {
                $exclude_regex[]='#^.*\.'.$exclude_file['path'].'$#';
            }
        }

        $exclude_regex[]='#^'.preg_quote($this -> transfer_path(WP_CONTENT_DIR).'/'.'wpvivid', '/').'#';
        $exclude_regex[]='#^'.preg_quote($this -> transfer_path(WP_CONTENT_DIR).'/'.WPvivid_Setting::get_backupdir(), '/').'#';

        if(defined('WPVIVID_UPLOADS_ISO_DIR'))
        {
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path(WP_CONTENT_DIR).'/'.WPVIVID_UPLOADS_ISO_DIR, '/').'#';
        }

        return $exclude_regex;
    }

    public function get_backup_type_exclude_files($backup_type)
    {
        $exclude_regex=array();

        if($backup_type=='backup_content')
        {
            $upload_dir = wp_upload_dir();
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path(WP_CONTENT_DIR).'/'.'plugins', '/').'#';
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($upload_dir['basedir']), '/').'$#';
            $exclude_regex[]='#^'.preg_quote($this->transfer_path(get_theme_root()), '/').'#';
        }
        
        return $exclude_regex;
    }

    public function get_include_files()
    {
        $include_regex[]='#^'.preg_quote($this -> transfer_path(ABSPATH.'wp-admin'), '/').'#';
        $include_regex[]='#^'.preg_quote($this->transfer_path(ABSPATH.'wp-includes'), '/').'#';
        $include_regex[]='#^'.preg_quote($this->transfer_path(ABSPATH.'lotties'), '/').'#';

        return $include_regex;
    }

    public function set_memory_limit()
    {
        $memory_limit=isset($this->task['setting']['memory_limit'])?$this->task['setting']['memory_limit']:WPVIVID_MEMORY_LIMIT;
        @ini_set('memory_limit', $memory_limit);
    }

    public function is_backup_finished()
    {
        $finished=true;

        foreach ($this->task['jobs'] as $job)
        {
            if($job['finished']==0)
            {
                $finished=false;
                break;
            }
        }
        return $finished;
    }

    public function update_sub_task_progress($progress)
    {
        $this->task['status']['run_time']=time();
        $this->task['status']['str']='running';
        $this->task['data']['doing']='backup';
        $sub_job_name=$this->task['jobs'][$this->current_job]['backup_type'];
        $this->task['data']['backup']['doing']=$sub_job_name;
        $this->task['data']['backup']['sub_job'][$sub_job_name]['progress']=$progress;
        if(!isset( $this->task['data']['backup']['sub_job'][$sub_job_name]['job_data']))
        {
            $this->task['data']['backup']['sub_job'][$sub_job_name]['job_data']=array();
        }
        $this->update_task();
    }

    public function get_next_job()
    {
        $job_key=false;
        foreach ($this->task['jobs'] as $key=>$job)
        {
            if($job['finished']==0)
            {
                $job_key=$key;
                break;
            }
        }
        return $job_key;
    }

    public function do_backup_job($key)
    {
        if(!isset($this->task['jobs'][$key]))
        {
            $ret['result']='failed';
            $ret['error']='not found job';
            return $ret;
        }

        //backup_type
        $this->current_job=$key;
        $job=$this->task['jobs'][$key];
        global $wpvivid_plugin;
        $wpvivid_plugin->wpvivid_log->WriteLog('Prepare to backup '.$job['backup_type'].' files.','notice');

        $this->update_sub_task_progress(sprintf('Start backing up %s.',$job['backup_type']));

        if($job['backup_type']=='backup_db')
        {
            $ret=$this->do_backup_db();
            if($ret['result']!='success')
            {
                return $ret;
            }
            else
            {
                $this->rename_backup_files($key);
            }
        }
        else if($job['backup_type']=='backup_merge')
        {
            $ret=$this->do_backup_merge();
            if($ret['result']!='success')
            {
                return $ret;
            }
            else
            {
                $this->rename_backup_files($key);
            }
        }
        else
        {
            $ret=$this->do_backup_files($job['backup_type']);
            if($ret['result']!='success')
            {
                return $ret;
            }
            else
            {
                $this->rename_backup_files($key);
            }
        }

        $wpvivid_plugin->wpvivid_log->WriteLog('Backing up '.$job['backup_type'].' completed.','notice');
        $this->task['jobs'][$key]['finished']=1;
        $this->task['status']['resume_count']=0;

        $this->update_sub_task_progress(sprintf('Backing up %s finished.',$job['backup_type']));
        $this->update_main_progress();

        $ret['result']='success';
        return $ret;
    }

    public function rename_backup_files($key)
    {
        if(isset($this->task['jobs'][$key]['zip_file']))
        {
            if(count($this->task['jobs'][$key]['zip_file'])==1)
            {
                $backup_type=$this->task['jobs'][$key]['backup_type'];
                $file_prefix=$this->task['options']['file_prefix'];

                $old_file=array_shift($this->task['jobs'][$key]['zip_file']);

                if($backup_type=='backup_merge')
                {
                    $backup_type='backup_all';
                }
                $filename=$file_prefix.'_'.$backup_type.'.zip';
                $zip['filename']=$filename;
                $zip['finished']=1;
                $this->task['jobs'][$key]['zip_file']=array();
                $this->task['jobs'][$key]['zip_file'][$filename]=$zip;

                $path=$this->task['options']['dir'].'/';

                rename($path.$old_file['filename'],$path.$filename);

                $this->update_task();
            }
        }
    }

    public function update_main_progress()
    {
        $i_finished_backup_count=0;
        $i_sum=count($this->task['jobs']);
        foreach ($this->task['jobs'] as $job)
        {
            if($job['finished']==1)
            {
                $i_finished_backup_count++;
            }
        }
        $i_progress=intval(($i_finished_backup_count/$i_sum)*100);
        $this->task['data']['backup']['progress']=$i_progress;
        $this->update_task();
    }

    public function update_database_progress($i_progress)
    {
        $this->task['data']['backup']['progress']=$i_progress;
        $this->update_task();
    }

    public function delete_canceled_backup_files($task_id)
    {
        $path = $this->task['options']['dir'];
        $handler=opendir($path);
        if($handler!==false)
        {
            while(($filename=readdir($handler))!==false)
            {
                if(preg_match('#'.$task_id.'#',$filename) || preg_match('#'.apply_filters('wpvivid_fix_wpvivid_free', $task_id).'#',$filename))
                {
                    @wp_delete_file($path.'/'.$filename);
                }
            }
            @closedir($handler);
        }
    }

    public function update_task()
    {
        wp_cache_flush();
        $default = array();
        $tasks = get_option('wpvivid_task_list', $default);
        if(array_key_exists ($this->task_id, $tasks))
        {
            $this->task['status']['run_time']=time();
            WPvivid_Setting::update_task($this->task_id,$this->task);
        }
        else
        {
            $this->delete_canceled_backup_files($this->task_id);
        }
    }

    public function do_backup_merge()
    {
        $root_path=$this->get_backup_root('backup_merge');

        $files=$this->get_merge_files($root_path);

        if(empty($files))
        {
            $ret['result']='success';
            return $ret;
        }

        $max_zip_file_size= $this->task['setting']['max_file_size']*1024*1024;


        $path=$this->task['options']['dir'].'/';

        $zip_method=isset($this->task['setting']['zip_method'])?$this->task['setting']['zip_method']:'ziparchive';
        $zip=new WPvivid_Zip($zip_method);

        $zip_file_name=$path.$this->get_zip_file('backup_merge');

        $numItems = count($files);
        $i = 0;
        $index=$this->get_zipped_file_index();
        foreach ($files as $file)
        {
            if($this->check_cancel_backup())
            {
                die();
            }

            if($i<$index)
            {
                $i++;
                continue;
            }

            /*
            $zip->add_file($zip_file_name,$file,basename($file),dirname($file));
            $i++;

            $child_json=$this->get_file_json($file);
            $this->update_merge_zipped_file_index($i,basename($file),$child_json);

            if($i === $numItems)
            {
                continue;
            }

            if($max_zip_file_size !== 0 && (filesize($zip_file_name)>$max_zip_file_size))
            {
                $json=array();
                $json=$this->get_json_info('backup_merge',$json);
                $this->update_zip_file(basename($zip_file_name),1,$json);
                $zip_file_name=$path.$this->add_zip_file('backup_merge');
            }
            */

            if($max_zip_file_size==0)
                $max_zip_file_size = 4 * 1024 * 1024 * 1024;

            if(!file_exists($zip_file_name) || filesize($zip_file_name) == 0)
            {
                $zip->add_file($zip_file_name,$file,basename($file),dirname($file));
                $i++;

                $child_json=$this->get_file_json($file);
                $this->update_merge_zipped_file_index($i,basename($file),$child_json);

                if($i === $numItems)
                {
                    continue;
                }

                if(filesize($zip_file_name)>$max_zip_file_size)
                {
                    $json=array();
                    $json=$this->get_json_info('backup_merge',$json);
                    $this->update_zip_file(basename($zip_file_name),1,$json);
                    $zip_file_name=$path.$this->add_zip_file('backup_merge');
                }
            }
            else if((filesize($zip_file_name) + filesize($file)) < $max_zip_file_size)
            {
                $zip->add_file($zip_file_name,$file,basename($file),dirname($file));
                $i++;

                $child_json=$this->get_file_json($file);
                $this->update_merge_zipped_file_index($i,basename($file),$child_json);

                if($i === $numItems)
                {
                    continue;
                }
            }
            else
            {
                $json=array();
                $json=$this->get_json_info('backup_merge',$json);
                $this->update_zip_file(basename($zip_file_name),1,$json);
                $zip_file_name=$path.$this->add_zip_file('backup_merge');

                $zip->add_file($zip_file_name,$file,basename($file),dirname($file));
                $i++;

                $child_json=$this->get_file_json($file);
                $this->update_merge_zipped_file_index($i,basename($file),$child_json);

                if($i === $numItems)
                {
                    continue;
                }
            }

            /*if(filesize($zip_file_name)>$max_zip_file_size)
            {
                $json=array();
                $json=$this->get_json_info('backup_merge',$json);
                $this->update_zip_file(basename($zip_file_name),1,$json);
                $zip_file_name=$path.$this->add_zip_file('backup_merge');
            }*/
        }

        $json=array();
        $json=$this->get_json_info('backup_merge',$json);
        $this->update_zip_file(basename($zip_file_name),1,$json);
        foreach ($files as $file)
        {
            @wp_delete_file($file);
        }
        $ret['result']='success';
        return $ret;
    }

    public function do_backup_files($backup_type)
    {
        $root_path=$this->get_backup_root($backup_type);
        $exclude_files=$this->get_backup_type_exclude_files($backup_type);

        if($root_path===false)
        {
            $ret['result']='failed';
            $ret['error']='backup type not found';
            return $ret;
        }
        $compress_file_use_cache= $this->task['setting']['compress_file_use_cache'];

        $replace_path=$this->get_replace_path($backup_type);

        if($compress_file_use_cache)
        {
            if(!$this->check_cache_files())
            {
                $this->clean_zip_files();

                if($backup_type=='backup_core')
                {
                    $folders=$this->get_empty_folders($root_path,$replace_path,$exclude_files,$this->task['options']['include_files']);
                }
                else if($backup_type=='backup_plugin')
                {
                    if(!empty($this->task['options']['include_plugins']))
                    {
                        $include_regex=array();
                        foreach ($this->task['options']['include_plugins'] as $plugins)
                        {
                            $include_regex[]='#^'.preg_quote($this -> transfer_path(WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$plugins), '/').'#';
                        }
                        $folders=$this->get_empty_folders($root_path,$replace_path,$exclude_files,$include_regex);
                    }
                    else
                    {
                        $folders=$this->get_empty_folders($root_path,$replace_path,$exclude_files);
                    }
                }
                else if($backup_type=='backup_themes')
                {
                    if(!empty($this->task['options']['include_themes']))
                    {
                        $include_regex=array();
                        foreach ($this->task['options']['include_themes'] as $themes)
                        {
                            $include_regex[]='#^'.preg_quote($this -> transfer_path(get_theme_root().DIRECTORY_SEPARATOR.$themes), '/').'#';
                        }
                        $folders=$this->get_empty_folders($root_path,$replace_path,$exclude_files,$include_regex);
                    }
                    else
                    {
                        $folders=$this->get_empty_folders($root_path,$replace_path,$exclude_files);
                    }
                }
                else
                {
                    $folders=$this->get_empty_folders($root_path,$replace_path,$exclude_files);
                }

                $cache_file_prefix=WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir().'/'.$this->task['options']['file_prefix'].'_'.$backup_type.'_';

                if($backup_type=='backup_core')
                {
                    $ret=$this->create_cache_files($cache_file_prefix,$root_path,$exclude_files,$this->task['options']['include_files']);
                }
                else if($backup_type=='backup_custom_other')
                {
                    $ret=$this->create_custom_other_cache_files($cache_file_prefix,$this->task['options']['custom_other_root'],$exclude_files,$this->task['options']['custom_other_include_files']);
                }
                else if($backup_type=='backup_plugin')
                {
                    if(!empty($this->task['options']['include_plugins']))
                    {
                        $include_regex=array();
                        foreach ($this->task['options']['include_plugins'] as $plugins)
                        {
                            $include_regex[]='#^'.preg_quote($this -> transfer_path(WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$plugins), '/').'#';
                        }
                        $ret=$this->create_cache_files($cache_file_prefix,$root_path,$exclude_files,$include_regex);
                    }
                    else
                    {
                        $ret=$this->create_cache_files($cache_file_prefix,$root_path,$exclude_files);
                    }
                }
                else if($backup_type=='backup_themes')
                {
                    if(!empty($this->task['options']['include_themes']))
                    {
                        $include_regex=array();
                        foreach ($this->task['options']['include_themes'] as $themes)
                        {
                            $include_regex[]='#^'.preg_quote($this -> transfer_path(get_theme_root().DIRECTORY_SEPARATOR.$themes), '/').'#';
                        }
                        $ret=$this->create_cache_files($cache_file_prefix,$root_path,$exclude_files,$include_regex);
                    }
                    else
                    {
                        $ret=$this->create_cache_files($cache_file_prefix,$root_path,$exclude_files);
                    }
                }
                else
                {
                    $ret=$this->create_cache_files($cache_file_prefix,$root_path,$exclude_files);
                }

                if($ret['is_empty']===true)
                {
                    $ret['result']='success';
                    $this->clean_tmp_files();
                    return $ret;
                }

                $ret=$this->_backup_empty_folder($folders,$backup_type);

                if($ret['result']!='success')
                {
                    return $ret;
                }
            }

            $ret=$this->_backup_files_use_cache($backup_type,$replace_path);
        }
        else
        {
            if($backup_type=='backup_core')
            {
                $folders=$this->get_empty_folders($root_path,$replace_path,$exclude_files,$this->task['options']['include_files']);
            }
            else if($backup_type=='backup_plugin')
            {
                if(!empty($this->task['options']['include_plugins']))
                {
                    $include_regex=array();
                    foreach ($this->task['options']['include_plugins'] as $plugins)
                    {
                        $include_regex[]='#^'.preg_quote($this -> transfer_path(WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$plugins), '/').'#';
                    }
                    $folders=$this->get_empty_folders($root_path,$replace_path,$exclude_files,$include_regex);
                }
                else
                {
                    $folders=$this->get_empty_folders($root_path,$replace_path,$exclude_files);
                }
            }
            else if($backup_type=='backup_themes')
            {
                if(!empty($this->task['options']['include_themes']))
                {
                    $include_regex=array();
                    foreach ($this->task['options']['include_themes'] as $themes)
                    {
                        $include_regex[]='#^'.preg_quote($this -> transfer_path(get_theme_root().DIRECTORY_SEPARATOR.$themes), '/').'#';
                    }
                    $folders=$this->get_empty_folders($root_path,$replace_path,$exclude_files,$include_regex);
                }
                else
                {
                    $folders=$this->get_empty_folders($root_path,$replace_path,$exclude_files);
                }
            }
            else
            {
                $folders=$this->get_empty_folders($root_path,$replace_path,$exclude_files);
            }

            if($backup_type=='backup_core')
            {
                $files=$this->get_files($root_path,$exclude_files,$this->task['options']['include_files']);
            }
            else if($backup_type=='backup_plugin')
            {
                if(!empty($this->task['options']['include_plugins']))
                {
                    $include_regex=array();
                    foreach ($this->task['options']['include_plugins'] as $plugins)
                    {
                        $include_regex[]='#^'.preg_quote($this -> transfer_path(WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$plugins), '/').'#';
                    }
                    $files=$this->get_files($root_path,$exclude_files,$include_regex);
                }
                else
                {
                    $files=$this->get_files($root_path,$exclude_files);
                }
            }
            else if($backup_type=='backup_themes')
            {
                if(!empty($this->task['options']['include_themes']))
                {
                    $include_regex=array();
                    foreach ($this->task['options']['include_themes'] as $themes)
                    {
                        $include_regex[]='#^'.preg_quote($this -> transfer_path(get_theme_root().DIRECTORY_SEPARATOR.$themes), '/').'#';
                    }
                    $files=$this->get_files($root_path,$exclude_files,$include_regex);
                }
                else
                {
                    $files=$this->get_files($root_path,$exclude_files);
                }
            }
            else
            {
                $files=$this->get_files($root_path,$exclude_files);
            }

            $replace_path=$this->get_replace_path($backup_type);
            if(empty($files))
            {
                $ret['result']='success';
                return $ret;
            }
            else
            {
                $ret=$this->_backup_empty_folder($folders,$backup_type);

                if($ret['result']!='success')
                {
                    return $ret;
                }

                $ret=$this->_backup_files($files,$replace_path,$backup_type);
            }
        }

        return $ret;
    }

    public function transfer_path($path)
    {
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode('/',$values);
    }

    public function get_backup_root($backup_type)
    {
        if($backup_type=='backup_themes')
        {
            return $this->transfer_path(get_theme_root());
        }
        else if($backup_type=='backup_plugin')
        {
            return $this->transfer_path(WP_PLUGIN_DIR);
        }
        else if($backup_type=='backup_uploads')
        {
            $upload_dir = wp_upload_dir();
            return $this -> transfer_path($upload_dir['basedir']);
        }
        else if($backup_type=='backup_content')
        {
            return $this -> transfer_path(WP_CONTENT_DIR);
        }
        else if($backup_type=='backup_core')
        {
            return $this -> transfer_path(ABSPATH);
        }
        else if($backup_type=='backup_merge')
        {
            return $this -> transfer_path(WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir());
        }
        else
        {
            return false;
        }
    }

    public function get_replace_path($backup_type)
    {
        if($backup_type=='backup_themes')
        {
            return $this->transfer_path(WP_CONTENT_DIR.'/');
        }
        else if($backup_type=='backup_plugin')
        {
            return $this->transfer_path(WP_CONTENT_DIR.'/');
        }
        else if($backup_type=='backup_uploads')
        {
            return $this->transfer_path(WP_CONTENT_DIR.'/');
        }
        else if($backup_type=='backup_content')
        {
            return $this->transfer_path(WP_CONTENT_DIR.'/');
        }
        else if($backup_type=='backup_core')
        {
            return $this -> transfer_path(ABSPATH);
        }
        else
        {
            return false;
        }
    }

    public function check_cache_files()
    {
        if($this->current_job!==false)
        {
            if(isset($this->task['jobs'][$this->current_job]['cache_files']))
            {
                return true;
            }
        }
        return false;
    }

    public function clean_tmp_files()
    {
        if($this->current_job!==false)
        {
            if(isset($this->task['jobs'][$this->current_job]['cache_files']))
            {
                foreach ($this->task['jobs'][$this->current_job]['cache_files'] as $cache_file)
                {
                    @wp_delete_file($cache_file['name']);
                }
            }

            if(isset($this->task['jobs'][$this->current_job]['mysql_dump_files']))
            {
                $files=$this->task['jobs'][$this->current_job]['mysql_dump_files'];
                if(count($files)==1)
                {
                    $path=$this->task['options']['dir'].'/';
                    $new_file=$this->task['options']['file_prefix'].'_backup_db.sql';

                    @wp_delete_file($path.$new_file);
                }
                else
                {
                    $path=$this->task['options']['dir'].'/';
                    foreach ($files as $file)
                    {
                        @wp_delete_file($path.$file);
                    }
                }
            }
        }
    }

    public function get_empty_folders($root_path,$replace_path,$exclude_files=array(),$include_files=array())
    {
        $folder=array();
        $exclude_regex=array_merge($this->task['options']['exclude_files'],$exclude_files);
        $root_path=untrailingslashit($root_path);
        $this->_get_folders($root_path,$replace_path,$folder,$exclude_regex,$include_files);
        return $folder;
    }

    public function _get_folders($path,$replace_path,&$folders,$exclude_regex=array(),$include_regex=array())
    {
        $handler = opendir($path);

        if($handler===false)
            return;

        while (($filename = readdir($handler)) !== false)
        {
            if ($filename != "." && $filename != "..")
            {
                if (is_dir($path . '/' . $filename) && !@is_link($path . '/' . $filename))
                {
                    if($this->regex_match($exclude_regex, $this->transfer_path($path . '/' . $filename), 0))
                    {
                        if ($this->regex_match($include_regex, $path . '/' . $filename, 1))
                        {
                            $folders[]=str_replace($replace_path,'',$this->transfer_path($path . '/' . $filename));
                            $this->_get_folders($path . '/' . $filename,$replace_path,$folders,$exclude_regex,$include_regex);
                        }
                    }
                }
            }
        }
        if($handler)
            @closedir($handler);

        return;
    }

    public function _backup_empty_folder($folders,$backup_type)
    {
        $file_prefix=$this->task['options']['file_prefix'];
        $file_index=$this->get_file_index();
        $zip_file_name=$file_prefix.'_'.$backup_type.'.part'.sprintf('%03d',($file_index)).'.zip';

        $zip_method=isset($this->task['setting']['zip_method'])?$this->task['setting']['zip_method']:'ziparchive';
        $zip=new WPvivid_Zip($zip_method);

        return $zip->addEmptyDir($zip_file_name,$folders);
    }

    public function create_cache_files($file_prefix,$root_path,$exclude_files=array(),$include_files=array())
    {
        $number=1;
        $cache_file_handle=false;
        $max_cache_file_size=16*1024*1024;

        $exclude_regex=array_merge($this->task['options']['exclude_files'],$exclude_files);

        $exclude_file_size=$this->task['setting']['exclude_file_size'];

        $root_path=untrailingslashit($root_path);

        $skip_files_time=0;

        $files=$this->get_file_cache($root_path,$file_prefix,$cache_file_handle,$max_cache_file_size,$number,$exclude_regex,$include_files,$exclude_file_size,$skip_files_time);

        if($this->current_job!==false)
        {
            foreach ($files as $file)
            {
                $file_data['name']=$file;
                $file_data['index']=0;
                $file_data['finished']=0;
                $this->task['jobs'][$this->current_job]['cache_files'][$file_data['name']]=$file_data;
            }

            $this->update_task();
        }

        $ret['result']='success';
        $ret['is_empty']=$this->is_cache_empty($files);
        return $ret;
    }

    public function is_cache_empty($files)
    {
        $empty=true;
        foreach ($files as $file)
        {
            if(filesize($file)>0)
            {
                $empty=false;
                break;
            }
        }
        return $empty;
    }

    public function create_custom_other_cache_files($file_prefix,$custom_other_root,$exclude_files=array(),$include_files=array())
    {
        $number=1;
        $cache_file_handle=false;
        $max_cache_file_size=16*1024*1024;

        $exclude_regex=array_merge($this->task['options']['exclude_files'],$exclude_files);

        $exclude_file_size=$this->task['setting']['exclude_file_size'];

        if(isset($this->task['options']['incremental_options']))
        {
            $skip_files_time=$this->task['options']['incremental_options']['versions']['skip_files_time'];
        }
        else
        {
            $skip_files_time=0;
        }

        $files=array();
        foreach ($custom_other_root as $root_path)
        {
            $files1=$this->get_file_cache($root_path,$file_prefix,$cache_file_handle,$max_cache_file_size,$number,$exclude_regex,$include_files,$exclude_file_size,$skip_files_time);
            $files=array_merge($files,$files1);
        }

        if($this->current_job!==false)
        {
            foreach ($files as $file)
            {
                $file_data['name']=$file;
                $file_data['index']=0;
                $file_data['finished']=0;
                $this->task['jobs'][$this->current_job]['cache_files'][$file_data['name']]=$file_data;
            }

            $this->update_task();
        }

        $ret['result']='success';
        $ret['is_empty']=$this->is_cache_empty($files);
        return $ret;
    }

    public function update_files_cache($file_data)
    {
        if($this->current_job!==false)
        {
            $this->task['jobs'][$this->current_job]['cache_files'][$file_data['name']]=$file_data;
            $this->task['status']['resume_count']=0;
            $this->update_task();
        }
    }

    public function get_files_cache_list()
    {
        if($this->current_job!==false)
        {
            return $this->task['jobs'][$this->current_job]['cache_files'];
        }
        else
        {
            return array();
        }
    }

    public function get_files_from_cache($cache_file,$index,$max_count)
    {
        $files=array();
        $file = new SplFileObject($cache_file);
        $file->seek($index);

        $file->setFlags( \SplFileObject::SKIP_EMPTY | \SplFileObject::READ_AHEAD );

        $count=0;

        while(!$file->eof())
        {
            $src = $file->fgets();

            $src=trim($src,PHP_EOL);

            if(empty($src))
                continue;

            if(!file_exists($src))
            {
                continue;
            }

            $files[$src]=$src;
            $count++;

            if($count>$max_count)
            {
                break;
            }
        }

        $ret['eof']=$file->eof();
        $ret['files']=$files;
        return $ret;
    }

    public function get_files_from_cache_by_size($cache_file,$index,$max_zip_file_size)
    {
        $files=array();
        $file = new SplFileObject($cache_file);
        //$file->seek($index);
        if (version_compare(PHP_VERSION, '8.0.1', '>=') || $index == 0) {
            $file->seek($index);
        } else {
            if( $index == 1 ){
                $file->rewind(); // Ensure to go at first row before exit
                $file->fgets(); // Read line 0. Cursor remains now at line 1
            } else {
                $file->seek($index-1);
            }
        }

        $file->setFlags( \SplFileObject::SKIP_EMPTY | \SplFileObject::READ_AHEAD );

        $current_size=0;
        $current=$index;
        while(!$file->eof())
        {
            $src = $file->fgets();
            $src=trim($src,PHP_EOL);
            if(empty($src))
            {
                continue;
            }

            if(!file_exists($src))
            {
                continue;
            }

            if($max_zip_file_size==0)
                $max_zip_file_size = 4 * 1024 * 1024 * 1024;

            if($current_size > 0)
            {
                $current_size+=filesize($src);
            }

            if($current_size == 0)
            {
                $current++;
                $files[$src]=$src;
                $current_size+=filesize($src);
            }
            else if($current_size>$max_zip_file_size)
            {
                break;
            }
            else
            {
                $current++;
                $files[$src]=$src;
            }

            /*$current_size+=filesize($src);
            $files[$src]=$src;

            if($max_zip_file_size==0)
                continue;

            if($current_size>$max_zip_file_size)
                break;*/
        }

        $ret['eof']=$file->eof();
        $ret['index']=$current;
        $ret['files']=$files;
        return $ret;
    }

    public function get_files_count($files,$index,$add_files_count)
    {
        $add_files = array_slice($files,$index,$add_files_count);

        if($index+$add_files_count>count($files))
        {
            $eof=true;
        }
        else
        {
            $eof=false;
        }
        $ret['eof']=$eof;
        $ret['files']=$add_files;
        return $ret;
    }

    public function get_files_size($files,$index,$max_zip_file_size)
    {
        $current=0;
        $current_size=0;
        $add_files=array();
        foreach ($files as $file)
        {
            if($current<$index)
            {
                $current++;
                continue;
            }

            if($max_zip_file_size==0)
                $max_zip_file_size = 4 * 1024 * 1024 * 1024;

            if($current_size > 0)
            {
                $current_size+=filesize($file);
            }

            if($current_size == 0)
            {
                $current++;
                $add_files[]=$file;
                $current_size+=filesize($file);
            }
            else if($current_size>$max_zip_file_size)
            {
                break;
            }
            else
            {
                $current++;
                $add_files[]=$file;
            }

            /*$current++;
            $current_size+=filesize($file);
            $add_files[]=$file;

            if($max_zip_file_size==0)
                $max_zip_file_size = 4 * 1024 * 1024 * 1024;

            if($current_size>$max_zip_file_size)
                break;*/
        }

        if($current>=count($files))
        {
            $eof=true;
        }
        else
        {
            $eof=false;
        }
        $ret['eof']=$eof;
        $ret['index']=$current;
        $ret['files']=$add_files;
        return $ret;
    }

    public function get_file_cache($path,$cache_prefix,&$cache_file_handle,$max_cache_file_size,&$number,$exclude_files,$include_files,$exclude_file_size,$skip_files_time)
    {
        $files=array();

        if(!$cache_file_handle)
        {
            $cache_file=$cache_prefix.$number.'.cache';
            $cache_file_handle=fopen($cache_file,'a');
            $files[] = $cache_file;
        }
        $handler = opendir($path);

        if($handler===false)
            return $files;

        while (($filename = readdir($handler)) !== false)
        {
            if ($filename != "." && $filename != "..")
            {
                if (is_dir($path . '/' . $filename) && !@is_link($path . '/' . $filename))
                {
                    if($this->regex_match($exclude_files, $this->transfer_path($path . '/' . $filename), 0))
                    {
                        if ($this->regex_match($include_files, $path . '/' . $filename, 1))
                        {
                            $files2=$this->get_file_cache($path . '/' . $filename,$cache_prefix,$cache_file_handle,$max_cache_file_size,$number,$exclude_files,$include_files,$exclude_file_size,$skip_files_time);
                            $files=array_merge($files,$files2);
                        }
                    }
                }
                else
                {
                    if($this->regex_match($exclude_files, $this->transfer_path($path . '/' . $filename), 0))
                    {
                        if(is_readable($path . '/' . $filename) && !@is_link($path . '/' . $filename))
                        {
                            if ($exclude_file_size != 0)
                            {
                                if (filesize($path . '/' . $filename) < $exclude_file_size * 1024 * 1024)
                                {
                                    $add=true;
                                }
                                else
                                {
                                    $add=false;
                                }
                            }
                            else
                            {
                                $add=true;
                            }

                            if($add)
                            {
                                if($skip_files_time>0)
                                {
                                    $file_time=filemtime($path . '/' . $filename);
                                    if($file_time>0&&$file_time>$skip_files_time)
                                    {
                                        $line = $this->transfer_path($path . '/' . $filename).PHP_EOL;
                                        fwrite($cache_file_handle, $line);
                                    }
                                }
                                else
                                {
                                    $line = $this->transfer_path($path . '/' . $filename).PHP_EOL;
                                    fwrite($cache_file_handle, $line);
                                }

                            }
                        }
                    }
                }
            }
        }
        if($handler)
            @closedir($handler);

        return $files;
    }

    public function get_zip_file($backup_type)
    {
        if($this->current_job!==false)
        {
            if(!isset($this->task['jobs'][$this->current_job]['zip_file']))
            {
                $file_prefix=$this->task['options']['file_prefix'];
                if($backup_type=='backup_merge')
                {
                    $backup_type='backup_all';
                }
                $filename=$file_prefix.'_'.$backup_type.'.part'.sprintf('%03d',($this->task['jobs'][$this->current_job]['file_index'])).'.zip';
                $zip['filename']=$filename;
                $zip['finished']=0;
                $this->task['jobs'][$this->current_job]['zip_file'][$filename]=$zip;
                $this->update_task();
                return $filename;
            }
            else
            {
                foreach ($this->task['jobs'][$this->current_job]['zip_file'] as $zip)
                {
                    if( $zip['finished']==0)
                    {
                        return $zip['filename'];
                    }
                }

                return false;
            }

        }
        else
        {
            return false;
        }
    }

    public function add_zip_file($backup_type)
    {
        if($this->current_job!==false)
        {
            $this->task['jobs'][$this->current_job]['file_index']++;
            $file_prefix=$this->task['options']['file_prefix'];
            if($backup_type=='backup_merge')
            {
                $backup_type='backup_all';
                $this->task['jobs'][$this->current_job]['child_file']=array();
            }
            $filename=$file_prefix.'_'.$backup_type.'.part'.sprintf('%03d',($this->task['jobs'][$this->current_job]['file_index'])).'.zip';

            $zip['filename']=$filename;
            $zip['finished']=0;
            $this->task['jobs'][$this->current_job]['zip_file'][$filename]=$zip;
            $this->task['status']['resume_count']=0;
            $this->update_task();
            $this->set_time_limit();
            return $filename;
        }
        else
        {
            return false;
        }
    }

    public function update_zip_file($zip_name,$finished,$json=array())
    {
        if($this->current_job!==false)
        {
            if($json!==false)
                $this->add_json_file($zip_name,$json);
            $this->task['jobs'][$this->current_job]['zip_file'][$zip_name]['finished']=$finished;
            $this->task['jobs'][$this->current_job]['zip_file'][$zip_name]['json']=$json;
            $this->update_task();
        }
    }

    public function add_json_file($zip_name,$json)
    {
        $zip_method=isset($this->task['setting']['zip_method'])?$this->task['setting']['zip_method']:'ziparchive';
        $zip=new WPvivid_Zip($zip_method);

        $path=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir().DIRECTORY_SEPARATOR;
        return $zip->add_json_file($path.$zip_name,$json);
    }

    public function _backup_files_use_cache($backup_type,$replace_path)
    {
        global $wpvivid_plugin;

        $max_zip_file_size= $this->task['setting']['max_file_size']*1024*1024;
        $add_files_count=$this->task['setting']['compress_file_count'];
        $path=$this->task['options']['dir'].'/';
        $files_cache_list=$this->get_files_cache_list();

        $zip_method=isset($this->task['setting']['zip_method'])?$this->task['setting']['zip_method']:'ziparchive';
        $zip=new WPvivid_Zip($zip_method);

        if($zip_method=='ziparchive')
        {
            if($zip->check_ziparchive_available())
            {
                $use_pclzip=false;
            }
            else
            {
                $use_pclzip=true;
            }
        }
        else
        {
            $use_pclzip=true;
        }


        $zip_file_name=$path.$this->get_zip_file($backup_type);
        $json=array();
        $json=$this->get_json_info($backup_type,$json);

        $numItems = count($files_cache_list);
        $i = 0;

        foreach ($files_cache_list as $cache_file)
        {
            $i++;

            if($cache_file['finished']==1)
                continue;
            $eof=false;
            while(!$eof)
            {
                if ($this->check_cancel_backup())
                {
                    die();
                }

                if ($use_pclzip)
                {
                    $files_cache = $this->get_files_from_cache_by_size($cache_file['name'], $cache_file['index'], $max_zip_file_size);
                    $eof = $files_cache['eof'];
                    $files = $files_cache['files'];
                    $cache_file['index'] = $files_cache['index'];

                    $wpvivid_plugin->wpvivid_log->WriteLog('Compressing zip file:' . basename($zip_file_name) . ' index:' . $cache_file['index'], 'notice');
                    $zip->add_files($zip_file_name, $replace_path, $files, true, $json);
                    //$cache_file['index'] += $add_files_count;
                    $wpvivid_plugin->wpvivid_log->WriteLog('Compressing zip file:' . basename($zip_file_name) . ' success. index:' . $cache_file['index'] . ' file size:' . size_format(filesize($zip_file_name), 2), 'notice');

                    $this->update_zip_file(basename($zip_file_name), 1, false);
                    $this->update_files_cache($cache_file);
                    if ($i === $numItems && $eof)
                    {
                        continue;
                    }

                    $zip_file_name = $path . $this->add_zip_file($backup_type);
                }
                else
                {
                    //$files_cache = $this->get_files_from_cache($cache_file['name'], $cache_file['index'], $add_files_count);
                    $files_cache = $this->get_files_from_cache_by_size($cache_file['name'], $cache_file['index'], $max_zip_file_size);
                    $eof = $files_cache['eof'];
                    $files = $files_cache['files'];
                    $cache_file['index'] = $files_cache['index'];
                    $wpvivid_plugin->wpvivid_log->WriteLog('Compressing zip file:' . basename($zip_file_name) . ' index:' . $cache_file['index'], 'notice');
                    $zip->add_files($zip_file_name, $replace_path, $files);
                    //$cache_file['index'] += $add_files_count;
                    $this->update_files_cache($cache_file);
                    $wpvivid_plugin->wpvivid_log->WriteLog('Compressing zip file:' . basename($zip_file_name) . ' success. index:' . $cache_file['index'] . ' file size:' . size_format(filesize($zip_file_name), 2), 'notice');

                    if ($i === $numItems && $eof)
                    {
                        continue;
                    }

                    $this->update_zip_file(basename($zip_file_name), 1, $json);
                    $zip_file_name = $path . $this->add_zip_file($backup_type);

                    /*if ($max_zip_file_size !== 0 && (filesize($zip_file_name) > $max_zip_file_size))
                    {
                        $this->update_zip_file(basename($zip_file_name), 1, $json);
                        $zip_file_name = $path . $this->add_zip_file($backup_type);
                    }*/
                }
            }
            $cache_file['finished']=1;
            $this->update_files_cache($cache_file);
        }

        if(!$use_pclzip)
        {
            $this->update_zip_file(basename($zip_file_name),1,$json);
        }

        $this->clean_tmp_files();

        $ret['result']='success';
        return $ret;
    }

    public function get_json_info($backup_type,$json)
    {
        global $wpdb;
        if($backup_type=='backup_themes')
        {
            $json['file_type']='themes';
            $json['root_flag']='wp-content';
            $json['php_version']=phpversion();
            $json['mysql_version']=$wpdb->db_version();
            $json['wp_version'] = get_bloginfo( 'version' );
            $json['themes']=$this->get_themes_list();
        }
        else if($backup_type=='backup_plugin')
        {
            $json['file_type']='plugin';
            $json['root_flag']='wp-content';
            $json['php_version']=phpversion();
            $json['mysql_version']=$wpdb->db_version();
            $json['wp_version'] = get_bloginfo( 'version' );
            $json['plugin']=$this->get_plugins_list();
        }
        else if($backup_type=='backup_uploads')
        {
            $json['file_type']='upload';
            $json['root_flag']='wp-content';
            $json['php_version']=phpversion();
            $json['mysql_version']=$wpdb->db_version();
            $json['wp_version'] = get_bloginfo( 'version' );
        }
        else if($backup_type=='backup_content')
        {
            $json['file_type']='wp-content';
            $json['root_flag']='wp-content';
            $json['php_version']=phpversion();
            $json['mysql_version']=$wpdb->db_version();
            $json['wp_version'] = get_bloginfo( 'version' );
        }
        else if($backup_type=='backup_core')
        {
            $json['file_type']='wp-core';
            $json['include_path'][]='wp-includes';
            $json['include_path'][]='wp-admin';
            $json['wp_core']=1;
            $json['root_flag']='root';
            $json['home_url']=home_url();
        }
        else if($backup_type=='backup_db')
        {
            global $wpdb;
            $json['dump_db']=1;
            $json['file_type']='databases';
            if(isset($this->task['options']['site_id']))
            {
                $json['site_id']=$this->task['options']['site_id'];
                global $wpdb;
                $site_prefix= $wpdb->get_blog_prefix($this->task['options']['site_id']);
                $json['home_url']=get_home_url($this->task['options']['site_id']);
                $json['site_url']=get_site_url($this->task['options']['site_id']);
                $json['blog_prefix']=$site_prefix;
                $json['mu_migrate']=1;
                $json['base_prefix']=$wpdb->get_blog_prefix(0);
            }
            else
            {
                $json['home_url']=home_url();
            }

            $json['root_flag']='custom';
            $json['php_version']=phpversion();
            $json['mysql_version']=$wpdb->db_version();
            $json['wp_version'] = get_bloginfo( 'version' );
            if(is_multisite())
            {
                $json['is_mu']=1;
            }

            //encrypt_db
            if(isset($this->task['options']['encrypt_db'])&&$this->task['options']['encrypt_db'])
            {
                $json['is_crypt']=1;
            }

        }
        else if($backup_type=='backup_merge')
        {
            $json['has_child']=1;
            $json['home_url']=home_url();
            $json['root_flag']='custom';
            $json['php_version']=phpversion();
            $json['mysql_version']=$wpdb->db_version();
            $json['wp_version'] = get_bloginfo( 'version' );
            $json['child_file']=array();
            foreach ($this->task['jobs'][$this->current_job]['child_file'] as $file=>$child_json)
            {
                $json['child_file'][$file]=$child_json;
            }
        }

        return $json;
    }

    public function get_themes_list()
    {
        $themes_list=array();
        $list=wp_get_themes();
        foreach ($list as $key=>$item)
        {
            $path=$this -> transfer_path(get_theme_root().'/'.$key);

            if($this->regex_match($this->task['options']['exclude_files'],$path, 0))
            {
                $themes_list[$key]['slug']=$key;
            }
        }
        return $themes_list;
    }

    public function get_plugins_list()
    {
        $plugins_list=array();
        if(!function_exists('get_plugins'))
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $list=get_plugins();

        foreach ($list as $key=>$item)
        {
            if(dirname($key)=='.')
                continue;

            $path=$this -> transfer_path(WP_PLUGIN_DIR.'/'.$key);

            if($this->regex_match($this->task['options']['exclude_files'],$path, 0))
            {
                $plugins_list[dirname($key)]['slug']=dirname($key);
            }
        }
        return $plugins_list;
    }

    public function get_files($root_path,$exclude_files=array(),$include_files=array())
    {
        $files=array();
        $exclude_regex=array_merge($this->task['options']['exclude_files'],$exclude_files);
        $exclude_file_size=$this->task['setting']['exclude_file_size'];
        $root_path=untrailingslashit($root_path);

        if(isset($this->task['options']['incremental_options']))
        {
            $skip_files_time=$this->task['options']['incremental_options']['versions']['skip_files_time'];
        }
        else
        {
            $skip_files_time=0;
        }

        $this->_get_files($root_path,$files,$exclude_regex,$include_files,$exclude_file_size,$skip_files_time);

        return $files;
    }

    public function _get_files($path,&$files,$exclude_regex,$include_regex,$exclude_file_size,$skip_files_time)
    {
        $handler = opendir($path);

        if($handler===false)
            return;

        while (($filename = readdir($handler)) !== false)
        {
            if ($filename != "." && $filename != "..")
            {
                if (is_dir($path . '/' . $filename) && !@is_link($path . '/' . $filename))
                {
                    if($this->regex_match($exclude_regex, $this->transfer_path($path . '/' . $filename), 0))
                    {
                        if ($this->regex_match($include_regex, $path . '/' . $filename, 1))
                        {
                            $this->_get_files($path . '/' . $filename,$files,$exclude_regex,$include_regex,$exclude_file_size,$skip_files_time);
                        }
                    }
                }
                else
                {
                    if(is_readable($path . '/' . $filename) && !@is_link($path . '/' . $filename))
                    {
                        if($skip_files_time>0)
                        {
                            $file_time=filemtime($path . '/' . $filename);
                            if($file_time>0&&$file_time>$skip_files_time)
                            {
                                if ($exclude_file_size == 0)
                                {
                                    if($this->regex_match($exclude_regex, $this->transfer_path($path . '/' . $filename), 0))
                                    {
                                        $files[]=$this->transfer_path($path . '/' . $filename);
                                    }
                                }
                                else
                                {
                                    if($this->regex_match($exclude_regex, $this->transfer_path($path . '/' . $filename), 0))
                                    {
                                        if (filesize($path . '/' . $filename) < $exclude_file_size * 1024 * 1024)
                                        {
                                            $files[]=$this->transfer_path($path . '/' . $filename);
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            if ($exclude_file_size == 0)
                            {
                                if($this->regex_match($exclude_regex, $this->transfer_path($path . '/' . $filename), 0))
                                {
                                    $files[]=$this->transfer_path($path . '/' . $filename);
                                }
                            }
                            else
                            {
                                if($this->regex_match($exclude_regex, $this->transfer_path($path . '/' . $filename), 0))
                                {
                                    if (filesize($path . '/' . $filename) < $exclude_file_size * 1024 * 1024)
                                    {
                                        $files[]=$this->transfer_path($path . '/' . $filename);
                                    }
                                }
                            }
                        }

                    }
                }
            }
        }
        if($handler)
            @closedir($handler);

        return;
    }

    public function get_merge_files($root_path)
    {
        $files=array();
        foreach ($this->task['jobs'] as $job)
        {
            if($job['backup_type']=='backup_merge')
                continue;

            if(isset($job['zip_file']))
            {
                foreach ($job['zip_file'] as $zip)
                {
                    if( $zip['finished']!=0)
                    {
                        $files[]=$root_path.'/'.$zip['filename'];
                    }
                }
            }
        }

        return $files;
    }

    private function regex_match($regex_array,$string,$mode)
    {
        if(empty($regex_array))
        {
            return true;
        }

        if($mode==0)
        {
            foreach ($regex_array as $regex)
            {
                if(preg_match($regex,$string))
                {
                    return false;
                }
            }

            return true;
        }

        if($mode==1)
        {
            foreach ($regex_array as $regex)
            {
                if(preg_match($regex,$string))
                {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    public function _backup_files($files,$replace_path,$backup_type)
    {
        global $wpvivid_plugin;
        $max_zip_file_size= $this->task['setting']['max_file_size']*1024*1024;
        $add_files_count=$this->task['setting']['compress_file_count'];
        $path=$this->task['options']['dir'].'/';

        $zip_method=isset($this->task['setting']['zip_method'])?$this->task['setting']['zip_method']:'ziparchive';
        $zip=new WPvivid_Zip($zip_method);

        if($zip_method=='ziparchive')
        {
            if($zip->check_ziparchive_available())
            {
                $use_pclzip=false;
            }
            else
            {
                $use_pclzip=true;
            }
        }
        else
        {
            $use_pclzip=true;
        }

        $zip_file_name=$path.$this->get_zip_file($backup_type);
        $json=array();
        $json=$this->get_json_info($backup_type,$json);
        $eof=false;
        $index=$this->get_zipped_file_index();
        while(!$eof)
        {
            if($this->check_cancel_backup())
            {
                die();
            }

            if($use_pclzip)
            {
                $files_count=$this->get_files_size($files,$index,$max_zip_file_size);
                $eof=$files_count['eof'];
                $index=$files_count['index'];
                $wpvivid_plugin->wpvivid_log->WriteLog('Compressing zip file:'.basename($zip_file_name).' index:'.$index,'notice');
                $ret=$zip->add_files($zip_file_name,$replace_path,$files_count['files'],true,$json);
                $wpvivid_plugin->wpvivid_log->WriteLog('Compressing zip file:'.basename($zip_file_name).' success. index:'.$index.' file size:'.size_format(filesize($zip_file_name),2),'notice');

                if($ret['result']!='success')
                {
                    return $ret;
                }

                $this->update_zip_file(basename($zip_file_name),1,false);
                $this->update_zipped_file_index($index);

                if($eof)
                {
                    continue;
                }

                $zip_file_name=$path.$this->add_zip_file($backup_type);
            }
            else
            {
                //$files_count=$this->get_files_count($files,$index,$add_files_count);
                $files_count=$this->get_files_size($files,$index,$max_zip_file_size);
                $eof=$files_count['eof'];
                //$index+=$add_files_count;
                $index=$files_count['index'];
                $wpvivid_plugin->wpvivid_log->WriteLog('Compressing zip file:'.basename($zip_file_name).' index:'.$index,'notice');
                $ret=$zip->add_files($zip_file_name,$replace_path,$files_count['files']);
                $wpvivid_plugin->wpvivid_log->WriteLog('Compressing zip file:'.basename($zip_file_name).' success. index:'.$index.' file size:'.size_format(filesize($zip_file_name),2),'notice');
                if($ret['result']!='success')
                {
                    return $ret;
                }

                $this->update_zipped_file_index($index);

                if($eof)
                {
                    continue;
                }

                $this->update_zip_file(basename($zip_file_name),1,$json);
                $zip_file_name=$path.$this->add_zip_file($backup_type);

                /*if($max_zip_file_size==0)
                    $max_zip_file_size = 4 * 1024 * 1024 * 1024;
                if(filesize($zip_file_name)>$max_zip_file_size)
                {
                    $this->update_zip_file(basename($zip_file_name),1,$json);
                    $zip_file_name=$path.$this->add_zip_file($backup_type);
                }*/
            }
        }

        if(!$use_pclzip)
            $this->update_zip_file(basename($zip_file_name),1,$json);

        $ret['result']='success';
        return $ret;
    }

    public function do_backup_db()
    {
        if(!class_exists('WPvividTypeAdapterFactory'))
        {
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-mysqldump-method.php';
        }
        $this->task['dump_setting']=$this->init_db_backup_setting();
        $dump = new WPvivid_Mysqldump2($this,$this->task['dump_setting']);
        $dump->connect();
        if(!isset($this->task['jobs'][$this->current_job]['sub_jobs']))
        {
            $ret=$dump->init_job();
            if($ret===false)
            {
                $ret['result']='failed';
                $ret['error']='tables not found.';
                return $ret;
            }
        }

        if($this->check_cancel_backup())
        {
            die();
        }

        global $wpvivid_plugin;
        $wpvivid_plugin->wpvivid_log->WriteLog('Start exporting database.','notice');
        $ret= $dump->start_jobs();
        if($ret['result']=='success')
        {
            $wpvivid_plugin->wpvivid_log->WriteLog('Exporting database finished.','notice');
            $files=$this->get_mysql_dump_files();
            $jobs=$this->get_current_sub_job();
            $tables=array();
            foreach ( $jobs as $job)
            {
                $table['name']=$job['name'];
                $table['size']=$job['size'];
                $table['rows']=$job['rows'];
                $tables[]=$table;
            }
            $wpvivid_plugin->wpvivid_log->WriteLog('Start compressing database','notice');
            $find_zero_date=$dump->is_has_zero_date();
            $ret=$this->zip_mysql_dump_files($files,$tables,$find_zero_date);
            $wpvivid_plugin->wpvivid_log->WriteLog('Compressing database completed','notice');
        }

        $this->clean_tmp_files();

        return $ret;
    }

    public function get_current_mysql_file_index()
    {
        if($this->current_job!==false)
        {
            return $this->task['jobs'][$this->current_job]['mysql_file_index'];
        }
        else
        {
            return 1;
        }
    }

    public function reset_mysql_file_index()
    {
        $this->task['jobs'][$this->current_job]['mysql_file_index']=1;
        $this->update_task();
    }

    public function get_mysql_dump_files()
    {
        if($this->current_job!==false)
        {
            $files=$this->task['jobs'][$this->current_job]['mysql_dump_files'];
            if(count($files)==1)
            {
                $path=$this->task['options']['dir'].'/';

                $file=array_shift($files);
                if($this->task['jobs'][$this->current_job]['backup_type']=='backup_additional_db')
                {
                    $new_file=$this->task['options']['file_prefix'].'_backup_additional_db.sql';
                }
                else
                {
                    $new_file=$this->task['options']['file_prefix'].'_backup_db.sql';
                }

                rename($path.$file,$path.$new_file);

                $dump_files=array();
                $dump_files[]=$path.$new_file;
            }
            else
            {
                $dump_files=array();
                $path=$this->task['options']['dir'].'/';

                foreach ($files as $file)
                {
                    $dump_files[]=$path.$file;
                }
            }

            return $dump_files;
        }
        else
        {
            return array();
        }

    }

    public function add_mysql_dump_files($name_file_name)
    {
        if($this->current_job!==false)
        {
            $this->task['jobs'][$this->current_job]['mysql_dump_files'][]=$name_file_name;
            $this->task['jobs'][$this->current_job]['mysql_file_index']++;
            $this->update_task();
        }
    }

    public function get_file_index()
    {
        if($this->current_job!==false)
        {
            return $this->task['jobs'][$this->current_job]['file_index'];
        }
        else
        {
            return 1;
        }
    }

    public function get_zipped_file_index()
    {
        if($this->current_job!==false)
        {
            return $this->task['jobs'][$this->current_job]['index'];
        }
        else
        {
            return 0;
        }
    }

    public function update_zipped_file_index($index)
    {
        if($this->current_job!==false)
        {
            $this->task['jobs'][$this->current_job]['index']=$index;
            $this->task['status']['resume_count']=0;
            $this->update_task();
        }
    }

    public function update_merge_zipped_file_index($index,$file,$json)
    {
        if($this->current_job!==false)
        {
            $this->task['jobs'][$this->current_job]['index']=$index;
            $this->task['status']['resume_count']=0;
            $this->task['jobs'][$this->current_job]['child_file'][$file]=$json;
            $this->update_task();
        }
    }

    public function zip_mysql_dump_files($files,$tables,$find_zero_date=false)
    {
        foreach ($files as $file)
        {
            $json['files'][]=basename($file);
        }
        $json['tables']=$tables;
        $json=$this->get_json_info('backup_db',$json);
        $max_zip_file_size= $this->task['setting']['max_file_size']*1024*1024;
        $path=$this->task['options']['dir'].'/';

        $zip_method=isset($this->task['setting']['zip_method'])?$this->task['setting']['zip_method']:'ziparchive';
        $zip=new WPvivid_Zip($zip_method);

        $zip_file_name=$path.$this->get_zip_file('backup_db');

        $numItems = count($files);
        $i = 0;

        foreach ($files as $file)
        {
            $ret=$zip->add_file($zip_file_name,$file,basename($file),dirname($file));
            if($ret['result']!='success')
            {
                return $ret;
            }

            if(++$i === $numItems)
            {
                continue;
            }

            if($max_zip_file_size !== 0 && (filesize($zip_file_name)>$max_zip_file_size))
            {
                $this->update_zip_file(basename($zip_file_name),1,$json);
                $zip_file_name=$path.$this->add_zip_file('backup_db');
            }
        }

        if($find_zero_date)
        {
            $json['find_zero_date']=1;
        }

        $this->update_zip_file(basename($zip_file_name),1,$json);
        $ret['result']='success';
        return $ret;
    }

    public function update_current_sub_job($jobs)
    {
        if($this->current_job!==false)
        {
            $this->task['jobs'][$this->current_job]['sub_jobs']=$jobs;
            $this->update_task();
        }
    }

    public function get_current_sub_job()
    {
        if($this->current_job!==false)
        {
            return $this->task['jobs'][$this->current_job]['sub_jobs'];
        }
        else
        {
            return false;
        }
    }

    public function init_db_backup_setting()
    {
        global $wpdb;

        $dump_setting['database'] = DB_NAME;
        $dump_setting['host'] = DB_HOST;
        $dump_setting['user'] = DB_USER;
        $dump_setting['pass'] = DB_PASSWORD;

        $dump_setting['site_url']=get_site_url();
        $dump_setting['home_url']=get_home_url();

        $dump_setting['content_url']=content_url();

        $dump_setting['prefix'] = $wpdb->get_blog_prefix(0);

        $db_connect_method = isset($this->task['setting']['db_connect_method']) ? $this->task['setting']['db_connect_method'] : 'wpdb';
        if ($db_connect_method === 'wpdb')
        {
            $dump_setting['db_connect_method']='wpdb';
        }
        else
        {
            $dump_setting['db_connect_method']='mysql';
        }

        $dump_setting['file_prefix']=$this->task['options']['file_prefix'];
        $dump_setting['path']=$this->task['options']['dir'];
        $dump_setting['max_file_size']=$this->task['setting']['max_sql_file_size']*1024*1024;

        $dump_setting['exclude-tables']=isset($this->task['options']['exclude-tables'])?$this->task['options']['exclude-tables']:array();
        $dump_setting['include-tables']=isset($this->task['options']['include-tables'])?$this->task['options']['include-tables']:array();
        return $dump_setting;
    }

    public function update_status($status)
    {
        $this->task['status']['str']=$status;
        $this->task['status']['run_time']=time();
        WPvivid_Setting::update_task($this->task_id,$this->task);
    }

    public function get_status()
    {
        return $this->task['status'];
    }

    public function set_time_limit()
    {
        //max_execution_time
        @set_time_limit( $this->task['setting']['max_execution_time']);
        $this->task['status']['timeout']=time();
        $this->update_task();
    }

    public function get_time_limit()
    {
        return $this->task['setting']['max_execution_time'];
    }

    public function get_max_resume_count()
    {
        return $this->task['setting']['max_resume_count'];
    }

    public function update_backup_task_status($reset_start_time=false,$status='',$reset_timeout=false,$resume_count=false,$error='')
    {
        $this->task['status']['run_time']=time();
        if($reset_start_time)
            $this->task['status']['start_time']=time();
        if(!empty($status))
        {
            $this->task['status']['str']=$status;
        }
        if($reset_timeout)
            $this->task['status']['timeout']=time();
        if($resume_count!==false)
        {
            $this->task['status']['resume_count']=$resume_count;
        }

        if(!empty($error))
        {
            $this->task['status']['error']=$error;
        }

        $this->update_task();
    }

    public function get_setting()
    {
        return $this->task['setting'];
    }

    public function get_unfinished_job()
    {
        $job=false;
        if(!$this->is_backup_finished())
        {
            $job_key=$this->get_next_job();
            if($job_key!==false)
            {
                $job=$this->task['jobs'][$job_key];
            }
        }

        return $job;
    }

    public function clean_zip_files()
    {
        if($this->current_job!==false)
        {
            if(isset($this->task['jobs'][$this->current_job]['zip_file']))
            {
                $path=$this->task['options']['dir'].'/';

                foreach ($this->task['jobs'][$this->current_job]['zip_file'] as $zip)
                {
                    @wp_delete_file($path.$zip['filename']);
                }

                unset($this->task['jobs'][$this->current_job]['zip_file']);
                $this->task['jobs'][$this->current_job]['file_index']=1;
                $this->update_task();
            }
        }
    }

    public function need_upload()
    {
        //remote_options
        if($this->task['options']['remote_options']===false)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public function is_upload_finished()
    {
        $b_finished=true;

        if(array_key_exists('upload',$this->task['data']))
        {
            foreach ($this->task['data']['upload']['sub_job'] as $upload_job)
            {
                if($upload_job['finished']!=1)
                {
                    $b_finished=false;
                    break;
                }
            }
        }
        else
        {
            $b_finished=false;
        }

        return $b_finished;
    }

    public function get_remote_options()
    {
        return $this->task['options']['remote_options'];
    }

    public function get_backup_files()
    {
        $files=array();
        $root_path=$this -> transfer_path(WP_CONTENT_DIR.'/'.WPvivid_Setting::get_backupdir());
        foreach ($this->task['jobs'] as $job)
        {
            if($job['backup_type']=='backup_merge')
            {
                $files=array();
                if(isset($job['zip_file']))
                {
                    foreach ($job['zip_file'] as $zip)
                    {
                        $files[]=$root_path.'/'.$zip['filename'];
                    }
                }
                break;
            }

            if(isset($job['zip_file']))
            {
                foreach ($job['zip_file'] as $zip)
                {
                    $files[]=$root_path.'/'.$zip['filename'];
                }
            }
        }

        return $files;
    }

    public function update_backup_result()
    {
        $files=$this->get_backup_files();
        $backup_result['result']['result']='success';

        if(!empty($files))
        {
            foreach ($files as $file)
            {
                $file_data['file_name'] = basename($file);
                $file_data['size'] = filesize($file);
                $backup_result['result']['files'][] =$file_data;
            }
        }
        $is_merge=$this->task['setting']['is_merge'];
        if($is_merge==1)
        {
            $backup_result['key']='backup_merge';
        }

        $this->task['options']['backup_options']['ismerge']=$is_merge;
        $this->task['options']['backup_options']['backup'][]=$backup_result;
        $this->update_task();

    }

    public function add_new_backup()
    {
        $files=$this->get_backup_files();

        if(empty($files))
        {
            return;
        }

        $backup_data=array();
        $backup_data['type']=$this->task['type'];
        $backup_data['create_time']=$this->task['status']['start_time'];
        $backup_data['manual_delete']=0;
        $backup_data['local']['path']=$this->task['options']['backup_dir'];
        $backup_data['compress']['compress_type']='zip';
        $backup_data['save_local']=$this->task['options']['save_local'];
        if(isset($this->task['options']['backup_prefix']))
        {
            $backup_data['backup_prefix'] = $this->task['options']['backup_prefix'];
        }

        $backup_data['log']=$this->task['options']['log_file_path'];

        $backup_result['result']='success';

        foreach ($files as $file)
        {
            $file_data['file_name'] = basename($file);
            $file_data['size'] = filesize($file);
            $backup_result['files'][] =$file_data;
        }
        $backup_data['backup']=$backup_result;
        $backup_data['remote']=array();

        if(isset($this->task['options']['lock']))
        {
            $backup_data['lock'] = $this->task['options']['lock'];
        }

        $backup_list='wpvivid_backup_list';

        $backup_list=apply_filters('get_wpvivid_backup_list_name',$backup_list,$this->task['id']);

        $list = WPvivid_Setting::get_option($backup_list);
        $list[$this->task['id']]=$backup_data;
        WPvivid_Setting::update_option($backup_list,$list);
    }

    public function set_remote_lock()
    {
        $backup_lock=get_option('wpvivid_remote_backups_lock');
        $backup_id = $this->task['id'];
        if(isset($this->task['options']['lock']))
        {
            $lock = $this->task['options']['lock'];
        }
        else
        {
            $lock = 0;
        }
        if($lock)
        {
            $backup_lock[$backup_id]=1;
        }
        else {
            unset($backup_lock[$backup_id]);
        }
        update_option('wpvivid_remote_backups_lock',$backup_lock);
    }

    public function add_exist_backup($backup_id,$type='Common')
    {
        $files=$this->get_backup_files();

        if(empty($files))
        {
            return;
        }

        $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
        $backup_result['files']=array();
        foreach ($files as $file)
        {
            $file_data['file_name'] = basename($file);
            $file_data['size'] = filesize($file);
            $backup_result['files'][] =$file_data;
        }
        $backup['backup']['files']=array_merge($backup['backup']['files'],$backup_result['files']);
        WPvivid_Backuplist::update_backup($backup_id,'backup', $backup['backup']);
    }

    public function clean_backup()
    {
        if(empty($this->task_id))
        {
            return;
        }

        $path = $this->task['options']['dir'];
        $handler=opendir($path);
        if($handler!==false)
        {
            while(($filename=readdir($handler))!==false)
            {
                if(preg_match('#'.$this->task_id.'#',$filename) || preg_match('#'.apply_filters('wpvivid_fix_wpvivid_free', $this->task_id).'#',$filename))
                {
                    @wp_delete_file($path.'/'.$filename);
                }
            }
            @closedir($handler);
        }
    }

    public function get_backup_task_info()
    {
        $list_tasks['status']=$this->task['status'];
        $list_tasks['is_canceled']=$this->is_task_canceled();
        $list_tasks['data']=$this->get_backup_tasks_progress();
        //
        $list_tasks['task_info']['need_next_schedule']=false;
        if($list_tasks['status']['str']=='running'||$list_tasks['status']['str']=='no_responds')
        {
            if($list_tasks['data']['running_stamp']>180)
            {
                $list_tasks['task_info']['need_next_schedule'] = true;
            }
            else{
                $list_tasks['task_info']['need_next_schedule'] = false;
            }
        }

        $list_tasks['task_info']['display_estimate_backup'] = '';

        $list_tasks['task_info']['backup_percent']=$list_tasks['data']['progress'].'%';
        //
        $list_tasks['task_info']['db_size']=0;
        $list_tasks['task_info']['file_size']=0;

        $list_tasks['task_info']['descript']='';
        $list_tasks['task_info']['css_btn_cancel']='pointer-events: auto; opacity: 1;';
        $list_tasks['task_info']['css_btn_log']='pointer-events: auto; opacity: 1;';
        $list_tasks['task_info']['total'] = 'N/A';
        $list_tasks['task_info']['upload'] = 'N/A';
        $list_tasks['task_info']['speed'] = 'N/A';
        $list_tasks['task_info']['network_connection'] = 'N/A';

        $list_tasks['task_info']['need_update_last_task']=false;
        if($list_tasks['status']['str']=='ready')
        {
            $list_tasks['task_info']['descript']=__('Ready to backup. Progress: 0%, running time: 0second.','wpvivid-backuprestore');
            $list_tasks['task_info']['css_btn_cancel']='pointer-events: none; opacity: 0.4;';
            $list_tasks['task_info']['css_btn_log']='pointer-events: none; opacity: 0.4;';
        }
        else if($list_tasks['status']['str']=='running')
        {
            if($list_tasks['is_canceled'] == false)
            {
                if($list_tasks['data']['type'] == 'upload')
                {
                    if(isset($list_tasks['data']['upload_data']) && !empty($list_tasks['data']['upload_data']))
                    {
                        $descript = $list_tasks['data']['upload_data']['descript'];
                        $offset = $list_tasks['data']['upload_data']['offset'];
                        $current_size = $list_tasks['data']['upload_data']['current_size'];
                        $last_time = $list_tasks['data']['upload_data']['last_time'];
                        $last_size = $list_tasks['data']['upload_data']['last_size'];
                        $speed = ($offset - $last_size) / (time() - $last_time);
                        $speed /= 1000;
                        $speed = round($speed, 2);
                        $speed .= 'kb/s';
                        if(!empty($current_size)) {
                            $list_tasks['task_info']['total'] = size_format($current_size,2);
                        }
                        if(!empty($offset)) {
                            $list_tasks['task_info']['upload'] = size_format($offset, 2);
                        }
                    }
                    else{
                        $descript = 'Start uploading.';
                        $speed = '0kb/s';
                        $list_tasks['task_info']['total'] = 'N/A';
                        $list_tasks['task_info']['upload'] = 'N/A';
                    }

                    $list_tasks['task_info']['speed'] = $speed;
                    $list_tasks['task_info']['descript'] = $descript.' '.__('Progress: ', 'wpvivid-backuprestore') . $list_tasks['task_info']['backup_percent'] . ', ' . __('running time: ', 'wpvivid-backuprestore') . $list_tasks['data']['running_time'];

                    $time_spend=time()-$list_tasks['status']['run_time'];
                    if($time_spend>30)
                    {
                        $list_tasks['task_info']['network_connection']='Retrying';
                    }
                    else
                    {
                        $list_tasks['task_info']['network_connection']='OK';
                    }
                }
                else {
                    $list_tasks['task_info']['descript'] = $list_tasks['data']['descript'] . ' '. __('Progress: ', 'wpvivid-backuprestore') . $list_tasks['task_info']['backup_percent'] . ', '. __('running time: ', 'wpvivid-backuprestore') . $list_tasks['data']['running_time'];
                }
                $list_tasks['task_info']['css_btn_cancel']='pointer-events: auto; opacity: 1;';
                $list_tasks['task_info']['css_btn_log']='pointer-events: auto; opacity: 1;';
            }
            else{
                $list_tasks['task_info']['descript']=__('The backup will be canceled after backing up the current chunk ends.','wpvivid-backuprestore');
                $list_tasks['task_info']['css_btn_cancel']='pointer-events: none; opacity: 0.4;';
                $list_tasks['task_info']['css_btn_log']='pointer-events: auto; opacity: 1;';
            }
        }
        else if($list_tasks['status']['str']=='wait_resume')
        {
            $list_tasks['task_info']['descript']='Task '.$this->task_id.' timed out, backup task will retry in '.$list_tasks['data']['next_resume_time'].' seconds, retry times: '.$list_tasks['status']['resume_count'].'.';
            $list_tasks['task_info']['css_btn_cancel']='pointer-events: auto; opacity: 1;';
            $list_tasks['task_info']['css_btn_log']='pointer-events: auto; opacity: 1;';
        }
        else if($list_tasks['status']['str']=='no_responds')
        {
            if($list_tasks['is_canceled'] == false)
            {
                $list_tasks['task_info']['descript']='Task , '.$list_tasks['data']['doing'].' is not responding. Progress: '.$list_tasks['task_info']['backup_percent'].', running time: '.$list_tasks['data']['running_time'];
                $list_tasks['task_info']['css_btn_cancel']='pointer-events: auto; opacity: 1;';
                $list_tasks['task_info']['css_btn_log']='pointer-events: auto; opacity: 1;';
            }
            else{
                $list_tasks['task_info']['descript']=__('The backup will be canceled after backing up the current chunk ends.','wpvivid-backuprestore');
                $list_tasks['task_info']['css_btn_cancel']='pointer-events: none; opacity: 0.4;';
                $list_tasks['task_info']['css_btn_log']='pointer-events: auto; opacity: 1;';
            }
        }
        else if($list_tasks['status']['str']=='completed')
        {
            $list_tasks['task_info']['descript']='Task '.$this->task_id.' completed.';
            $list_tasks['task_info']['css_btn_cancel']='pointer-events: auto; opacity: 1;';
            $list_tasks['task_info']['css_btn_log']='pointer-events: auto; opacity: 1;';
            $list_tasks['task_info']['need_update_last_task']=true;
        }
        else if($list_tasks['status']['str']=='error')
        {
            $list_tasks['task_info']['descript']='Backup error: '.$list_tasks['status']['error'];
            $list_tasks['task_info']['css_btn_cancel']='pointer-events: auto; opacity: 1;';
            $list_tasks['task_info']['css_btn_log']='pointer-events: auto; opacity: 1;';
            $list_tasks['task_info']['need_update_last_task']=true;
        }

        return $list_tasks;
    }

    public function is_task_canceled()
    {
        $file_name=$this->task['options']['file_prefix'];

        $file =$this->task['options']['dir'].'/'. $file_name . '_cancel';

        if (file_exists($file))
        {
            return true;
        }
        return false;
    }

    public function check_cancel_backup()
    {
        if($this->is_task_canceled())
        {
            global $wpvivid_plugin;
            $wpvivid_plugin->wpvivid_log->WriteLog('Backup cancelled.','notice');

            $this->update_status('cancel');
            $this->clean_tmp_files();

            $tasks=WPvivid_Setting::get_option('wpvivid_clean_task_2');
            $tasks[$this->task_id]=$this->task;
            WPvivid_Setting::update_option('wpvivid_clean_task_2',$tasks);

            $resume_time=time()+60;

            $b=wp_schedule_single_event($resume_time,'wpvivid_clean_backup_data_event_2',array($this->task_id));

            if($b===false)
            {
                $timestamp = wp_next_scheduled('wpvivid_clean_backup_data_event_2',array($this->task_id));

                if($timestamp!==false)
                {
                    $resume_time=max($resume_time,$timestamp+10*60+10);
                    wp_schedule_single_event($resume_time,'wpvivid_clean_backup_data_event_2',array($this->task_id));
                }
            }

            $timestamp =wp_next_scheduled('wpvivid_task_monitor_event',array($this->task_id));
            if($timestamp!==false)
            {
                wp_unschedule_event($timestamp,'wpvivid_task_monitor_event',array($this->task_id));
            }
            wp_cache_flush();
            WPvivid_taskmanager::delete_task($this->task_id);
            wp_cache_flush();

            $this->wpvivid_check_clear_litespeed_rule();

            return true;
        }
        else
        {
            return false;
        }
    }

    public function get_backup_tasks_progress()
    {
        $current_time=gmdate("Y-m-d H:i:s");
        $create_time=gmdate("Y-m-d H:i:s",$this->task['status']['start_time']);
        $time_diff=strtotime($current_time)-strtotime($create_time);
        $running_time='';
        if(gmdate("G",$time_diff) > 0){
            $running_time .= gmdate("G",$time_diff).' hour(s)';
        }
        if(intval(gmdate("i",$time_diff)) > 0){
            $running_time .= intval(gmdate("i",$time_diff)).' min(s)';
        }
        if(intval(gmdate("s",$time_diff)) > 0){
            $running_time .= intval(gmdate("s",$time_diff)).' second(s)';
        }
        $next_resume_time=$this->get_next_resume_time();

        $ret['type']=$this->task['data']['doing'];
        $ret['progress']=$this->task['data'][$ret['type']]['progress'];
        $ret['doing']=$this->task['data'][$ret['type']]['doing'];
        if(isset($this->task['data'][$ret['type']]['sub_job'][$ret['doing']]['progress']))
        {
            $ret['descript']=$this->task['data'][$ret['type']]['sub_job'][$ret['doing']]['progress'];
        }
        else
        {
            $ret['descript']='';
        }
        if(isset($this->task['data'][$ret['type']]['sub_job'][$ret['doing']]['upload_data']))
            $ret['upload_data']=$this->task['data'][$ret['type']]['sub_job'][$ret['doing']]['upload_data'];
        $this->task['data'][$ret['type']]['sub_job'][$ret['doing']]['upload_data']=false;
        $ret['running_time']=$running_time;
        $ret['running_stamp']=$time_diff;
        $ret['next_resume_time']=$next_resume_time;
        return $ret;
    }

    public function get_next_resume_time()
    {
        $timestamp=wp_next_scheduled(WPVIVID_RESUME_SCHEDULE_EVENT,array($this->task_id));
        if($timestamp!==false)
        {
            return $timestamp-time();
        }
        else
        {
            return false;
        }
    }

    public function clear_cache()
    {
        $path = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir();
        $handler=opendir($path);
        if($handler!==false)
        {
            while(($filename=readdir($handler))!==false)
            {
                if(preg_match('#pclzip-.*\.tmp#', $filename)){
                    @wp_delete_file($path.DIRECTORY_SEPARATOR.$filename);
                }
                if(preg_match('#pclzip-.*\.gz#', $filename)){
                    @wp_delete_file($path.DIRECTORY_SEPARATOR.$filename);
                }
            }
            @closedir($handler);
        }
    }

    public function is_save_local()
    {
        return isset($this->task['options']['save_local'])?$this->task['options']['save_local']:false;
    }

    public function clean_local_files()
    {
        $path = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir();
        $handler=opendir($path);
        if($handler!==false)
        {
            while(($filename=readdir($handler))!==false)
            {
                if(preg_match('#'.$this->task_id.'#',$filename) || preg_match('#'.apply_filters('wpvivid_fix_wpvivid_free', $this->task_id).'#',$filename))
                {
                    @wp_delete_file($path.DIRECTORY_SEPARATOR.$filename);
                }
            }
            @closedir($handler);
        }
    }

    public function get_backup_jobs()
    {
        return $this->task['jobs'];
    }

    public function get_file_json($file)
    {
        if(!class_exists('WPvivid_ZipClass'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-zipclass.php';
        $zip=new WPvivid_ZipClass();

        $ret=$zip->get_json_data($file);
        if($ret['result'] === WPVIVID_SUCCESS)
        {
            $json=$ret['json_data'];
            $json = json_decode($json, 1);
            if (is_null($json))
            {
                return false;
            } else {
                return $json;
            }
        }
        else
        {
            return array();
        }
    }
    //adaptive settings
    public function check_memory_limit()
    {
        $current_memory_limit=$this->task['setting']['memory_limit'];
        $current_memory_int = (int) filter_var($current_memory_limit, FILTER_SANITIZE_NUMBER_INT);
        if($current_memory_int<512)
        {
            $this->task['setting']['memory_limit']='512M';
            $this->update_task();
            return true;
        }
        else if($current_memory_int<1024)
        {
            $this->task['setting']['memory_limit']=($current_memory_int+100).'M';
            $this->update_task();
            return true;
        }
        else
        {
            return false;
        }
    }

    public function check_timeout()
    {
        $job=$this->get_unfinished_job();
        if($job!==false)
        {
            if($job['backup_type']=='backup_db'||$job['backup_type']=='backup_additional_db')
            {
                if($this->task['setting']['max_sql_file_size']>200)
                {
                    $this->task['setting']['max_sql_file_size']=200;
                }
                else
                {
                    $this->task['setting']['max_sql_file_size']=max(10,$this->task['setting']['max_sql_file_size']-50);
                }
                $this->update_task();
            }
            else
            {
                //if($this->task['setting']['compress_file_use_cache']==false)
                //{
                //    $this->task['setting']['compress_file_use_cache']=true;
                //}

                if($this->task['setting']['compress_file_count']>=1000)
                {
                    $this->task['setting']['compress_file_count']=800;
                }
                else if($this->task['setting']['compress_file_count']>=800)
                {
                    $this->task['setting']['compress_file_count']=500;
                }
                else if($this->task['setting']['compress_file_count']>=500)
                {
                    $this->task['setting']['compress_file_count']=300;
                }
                else
                {
                    $this->task['setting']['compress_file_count']=100;
                }

                if($this->task['setting']['max_file_size']>200)
                {
                    $this->task['setting']['max_file_size']=200;
                }

                if($this->task['setting']['exclude_file_size']==0)
                {
                    $this->task['setting']['exclude_file_size']=200;
                }
                $this->update_task();
            }
        }
    }

    public function check_execution_time()
    {
        $this->task['setting']['max_execution_time']=$this->task['setting']['max_execution_time']+120;
        $this->update_task();
    }

    public function check_timeout_backup_failed()
    {
        //$job=$this->get_unfinished_job();
        //if($job!==false)
        //{
            //if($job['backup_type']=='backup_merge')
            //{
                //$this->task['setting']['is_merge']=false;
            //}
        //}

        $max_resume_count=$this->get_max_resume_count();
        $status=$this->get_status();
        $status['resume_count']++;
        if($status['resume_count']>$max_resume_count)
        {
            $this->task['setting']['max_resume_count']=max(20,$this->task['setting']['max_resume_count']+3);
            $this->update_task();
        }
    }

    public function update_schedule_last_backup_time()
    {

    }

    public function wpvivid_check_add_litespeed_server()
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
            global $wpvivid_plugin;
            $wpvivid_plugin->wpvivid_log->WriteLog('LiteSpeed Server.','notice');

            if ( ! function_exists( 'got_mod_rewrite' ) )
            {
                require_once ABSPATH . 'wp-admin/includes/misc.php';
            }

            if(function_exists('insert_with_markers'))
            {
                if(!function_exists('get_home_path'))
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                $home_path     = get_home_path();
                $htaccess_file = $home_path . '.htaccess';

                if ( ( ! file_exists( $htaccess_file ) && is_writable( $home_path ) ) || is_writable( $htaccess_file ) )
                {
                    if ( got_mod_rewrite() )
                    {
                        $line[]='<IfModule Litespeed>';
                        $line[]='RewriteEngine On';
                        $line[]='RewriteRule .* - [E=noabort:1, E=noconntimeout:1]';
                        $line[]='</IfModule>';
                        insert_with_markers($htaccess_file,'WPvivid Rewrite Rule for LiteSpeed',$line);
                        $wpvivid_plugin->wpvivid_log->WriteLog('Add LiteSpeed Rule','notice');
                    }
                    else
                    {
                        $wpvivid_plugin->wpvivid_log->WriteLog('mod_rewrite not found.','notice');
                    }
                }
                else
                {
                    $wpvivid_plugin->wpvivid_log->WriteLog('.htaccess file not exists or not writable.','notice');
                }
            }
            else
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('insert_with_markers function not exists.','notice');
            }
        }
    }

    public function wpvivid_check_clear_litespeed_rule()
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
            global $wpvivid_plugin;
            $wpvivid_plugin->wpvivid_log->WriteLog('LiteSpeed Server.','notice');

            if ( ! function_exists( 'got_mod_rewrite' ) )
            {
                require_once ABSPATH . 'wp-admin/includes/misc.php';
            }

            if(function_exists('insert_with_markers'))
            {
                if(!function_exists('get_home_path'))
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                $home_path     = get_home_path();
                $htaccess_file = $home_path . '.htaccess';

                if ( ( ! file_exists( $htaccess_file ) && is_writable( $home_path ) ) || is_writable( $htaccess_file ) )
                {
                    if ( got_mod_rewrite() )
                    {
                        insert_with_markers($htaccess_file,'WPvivid Rewrite Rule for LiteSpeed','');
                        $wpvivid_plugin->wpvivid_log->WriteLog('Clear LiteSpeed Rule','notice');
                    }
                    else
                    {
                        $wpvivid_plugin->wpvivid_log->WriteLog('mod_rewrite not found.','notice');
                    }
                }
                else
                {
                    $wpvivid_plugin->wpvivid_log->WriteLog('.htaccess file not exists or not writable.','notice');
                }
            }
            else
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('insert_with_markers function not exists.','notice');
            }
        }
    }

    public function wpvivid_disable_litespeed_cache_for_backup()
    {
        if (defined('LSCWP_V'))
        {
            do_action( 'litespeed_disable_all', 'stop for backup' );
        }
    }
}