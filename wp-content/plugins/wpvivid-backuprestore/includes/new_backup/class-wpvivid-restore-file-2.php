<?php

if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

class WPvivid_Restore_File_2
{
    public $log;

    public function __construct($log=false)
    {
        $this->log=$log;
    }

    public function restore($sub_task,$backup_id)
    {
        if($sub_task['type']=='wp-core')
        {
            return $this->restore_core($sub_task,$backup_id);
        }

        $files=$sub_task['unzip_file']['files'];
        $GLOBALS['wpvivid_restore_addon_type'] =$sub_task['type'];
        //restore_reset

        foreach ($files as $index=>$file)
        {
            if($file['finished']==1)
            {
                continue;
            }

            $sub_task['unzip_file']['last_action']='Unzipping';
            $sub_task['unzip_file']['last_unzip_file']=$file['file_name'];
            $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file['file_name'].'</span>';
            $this->update_sub_task($sub_task);

            $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
            $backup_item = new WPvivid_Backup_Item($backup);

            $extract_child_finished=isset($file['extract_child_finished'])?$file['extract_child_finished']:0;

            if(isset($file['has_child'])&&$extract_child_finished==0)
            {
                $sub_task['unzip_file']['last_action']='Unzipping';
                $sub_task['unzip_file']['last_unzip_file']=$file['parent_file'];
                $sub_task['unzip_file']['last_unzip_file_index']=0;
                $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file['parent_file'].'</span>';
                $this->update_sub_task($sub_task);

                $root_path=$backup_item->get_local_path();

                if(!file_exists($root_path))
                {
                    @mkdir($root_path);
                }
                $this->log->WriteLog('Extracting file:'.$file['parent_file'],'notice');
                $extract_files[]=$file['file_name'];
                $ret=$this->extract_ex($root_path.$file['parent_file'],$extract_files,untrailingslashit($root_path),$sub_task['options']);
                if($ret['result']!='success')
                {
                    return $ret;
                }
                $this->log->WriteLog('Extracting file:'.$file['parent_file'].' succeeded.','notice');
                $file_name=$root_path.$file['file_name'];
                $sub_task['unzip_file']['files'][$index]['extract_child_finished']=1;
                $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file['parent_file'].' completed.</span>';
                $this->update_sub_task($sub_task);
            }
            else
            {
                $root_path=$backup_item->get_local_path();
                $file_name=$root_path.$file['file_name'];
            }


            $root_path = '';
            if (isset($file['options']['root']))
            {
                $root_path = $this->transfer_path(get_home_path() . $file['options']['root']);
            }
            else if (isset($file['options']['root_flag']))
            {
                if ($file['options']['root_flag'] == WPVIVID_BACKUP_ROOT_WP_CONTENT)
                {
                    $root_path = $this->transfer_path(WP_CONTENT_DIR);
                }
                else if ($file['options']['root_flag'] == WPVIVID_BACKUP_ROOT_CUSTOM)
                {
                    $root_path = $this->transfer_path(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . WPvivid_Setting::get_backupdir());
                }
                else if ($file['options']['root_flag'] == WPVIVID_BACKUP_ROOT_WP_ROOT)
                {
                    $root_path = $this->transfer_path(ABSPATH);
                }
                else if($file['options']['root_flag'] == WPVIVID_BACKUP_ROOT_WP_UPLOADS)
                {
                    $upload_dir = wp_upload_dir();
                    $upload_path = $upload_dir['basedir'];

                    $root_path = $this->transfer_path($upload_path);
                }
            }

            if($sub_task['restore_reset'])
            {
                if($sub_task['restore_reset_finished']===false)
                {
                    $sub_task['unzip_file']['last_action']='Unzipping';
                    $sub_task['last_msg']='<span>Cleaning folder:</span><span>'.$sub_task['type'].'</span>';
                    $this->update_sub_task($sub_task);
                    $this->log->WriteLog('Cleaning folder:'.$sub_task['type'],'notice');
                    $this->reset_restore($sub_task['type']);
                    $sub_task['restore_reset_finished']=true;
                    $sub_task['unzip_file']['last_action']='Unzipping';
                    $sub_task['last_msg']='<span>Cleaning folder:</span><span>'.$sub_task['type'].' completed.</span>';
                    $this->update_sub_task($sub_task);
                }
            }

            $root_path = rtrim($root_path, '/');
            $root_path = rtrim($root_path, DIRECTORY_SEPARATOR);

            $restore_task=get_option('wpvivid_restore_task',array());
            $restore_detail_options=$restore_task['restore_detail_options'];
            $unzip_files_pre_request=$restore_detail_options['unzip_files_pre_request'];
            $use_index=$restore_detail_options['use_index'];
            if($use_index==false)
            {
                $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file['file_name'].'</span>';
                $sub_task['unzip_file']['last_action']='Unzipping';
                $sub_task['unzip_file']['last_unzip_file']=$file['file_name'];
                $sub_task['unzip_file']['last_unzip_file_index']=0;

                $this->update_sub_task($sub_task);
                $this->log->WriteLog('Extracting file:'.$file_name,'notice');
                $ret=$this->extract($file_name,untrailingslashit($root_path),$sub_task['options']);
                if($ret['result']!='success')
                {
                    return $ret;
                }
                $this->log->WriteLog('Extracting file:'.$file_name.' succeeded','notice');
                $sub_task['unzip_file']['files'][$index]['finished']=1;
                $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file['file_name'].' completed.</span>';
            }
            else
            {
                $sum=$this->get_zip_file_count($file_name);

                $start=$file['index'];
                $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file['file_name'].' '.$start.'/'.$sum.'</span>';
                $sub_task['unzip_file']['sum']=$sum;
                $sub_task['unzip_file']['start']=$start;
                $sub_task['unzip_file']['last_action']='Unzipping';
                $sub_task['unzip_file']['last_unzip_file']=$file['file_name'];
                $sub_task['unzip_file']['last_unzip_file_index']=$start;
                $this->update_sub_task($sub_task);
                $this->log->WriteLog('Extracting file:'.basename($file_name).' index:'.$start,'notice');
                $ret=$this->extract_by_index($file_name,untrailingslashit($root_path),$start,$start+$unzip_files_pre_request,$sub_task['options']);
                if($ret['result']!='success')
                {
                    return $ret;
                }
                $this->log->WriteLog('Extracting file:'.basename($file_name).' index:'.$start.' finished.','notice');
                $sub_task['unzip_file']['files'][$index]['index']=$start+$unzip_files_pre_request;
                $sub_task['unzip_file']['last_action']='Unzipping';
                if($start+$unzip_files_pre_request>=$sum)
                {
                    $sub_task['unzip_file']['files'][$index]['finished']=1;
                    $sub_task['unzip_file']['sum']=0;
                    $sub_task['unzip_file']['start']=0;
                    $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file['file_name'].' completed.</span>';
                }
                else
                {
                    $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file['file_name'].' '.$sub_task['unzip_file']['files'][$index]['index'].'/'.$sum.'</span>';
                }
            }

            break;
        }

        if($this->check_restore_finished($sub_task))
        {
            $sub_task['finished']=1;
            $sub_task['unzip_file']['unzip_finished']=1;
            $sub_task['unzip_file']['sum']=0;
            $sub_task['unzip_file']['start']=0;
        }

        $ret['result']='success';
        $ret['sub_task']=$sub_task;
        return $ret;
    }

    public function restore_core($sub_task,$backup_id)
    {
        $files=$sub_task['unzip_file']['files'];
        $GLOBALS['wpvivid_restore_addon_type'] =$sub_task['type'];
        //restore_reset
        foreach ($files as $index=>$file)
        {
            if($file['finished']==1)
            {
                continue;
            }

            $sub_task['unzip_file']['last_action']='Unzipping';
            $sub_task['unzip_file']['last_unzip_file']=$file['file_name'];
            $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file['file_name'].'</span>';
            $this->update_sub_task($sub_task);

            $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
            $backup_item = new WPvivid_Backup_Item($backup);
            $extract_child_finished=isset($file['extract_child_finished'])?$file['extract_child_finished']:0;
            if(isset($file['has_child'])&&$extract_child_finished==0)
            {
                $sub_task['unzip_file']['last_action']='Unzipping';
                $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file['parent_file'].'</span>';
                $this->update_sub_task($sub_task);

                $root_path=$backup_item->get_local_path();

                if(!file_exists($root_path))
                {
                    @mkdir($root_path);
                }
                $this->log->WriteLog('Extracting file:'.$file['parent_file'],'notice');

                $extract_files[]=$file['file_name'];
                $ret=$this->extract_ex($root_path.$file['parent_file'],$extract_files,untrailingslashit($root_path),$sub_task['options']);
                if($ret['result']!='success')
                {
                    return $ret;
                }
                $this->log->WriteLog('Extracting file:'.$file['parent_file'].' succeeded.','notice');
                $file_name=$root_path.$file['file_name'];
                $sub_task['unzip_file']['files'][$index]['extract_child_finished']=1;
                $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file['parent_file'].' completed.</span>';
                $this->update_sub_task($sub_task);
            }
            else
            {
                $root_path=$backup_item->get_local_path();
                $file_name=$root_path.$file['file_name'];
            }

            if($sub_task['restore_reset'])
            {
                if($sub_task['restore_reset_finished']===false)
                {
                    $sub_task['unzip_file']['last_action']='Unzipping';
                    $sub_task['last_msg']='<span>Cleaning folder:</span><span>'.$sub_task['type'].'</span>';
                    $this->update_sub_task($sub_task);
                    $this->log->WriteLog('Cleaning folder:'.$sub_task['type'],'notice');
                    $this->reset_restore($sub_task['type']);

                    $sub_task['restore_reset_finished']=true;
                    $sub_task['unzip_file']['last_action']='Unzipping';
                    $sub_task['last_msg']='<span>Cleaning folder:</span><span>'.$sub_task['type'].' completed.</span>';
                    $this->update_sub_task($sub_task);
                }
            }

            $root_path = $this->transfer_path(ABSPATH);

            $root_path = rtrim($root_path, '/');
            $root_path = rtrim($root_path, DIRECTORY_SEPARATOR);

            $sub_task['last_msg']='<span><strong>Extracting Files:</strong></span><span>'.$file['file_name'].'</span>';
            $sub_task['unzip_file']['last_action']='Unzipping';
            $this->update_sub_task($sub_task);
            $this->log->WriteLog('Extracting file:'.basename($file_name),'notice');

            $ret=$this->extract($file_name,untrailingslashit($root_path),$sub_task['options']);
            if($ret['result']!='success')
            {
                return $ret;
            }
            $this->log->WriteLog('Extracting file:'.basename($file_name).' succeeded.','notice');
            $sub_task['unzip_file']['files'][$index]['finished']=1;
            $sub_task['last_msg']='<span><strong>Extracting Files:</strong></span><span>'.$file['file_name'].' finished</span>';
            $this->update_sub_task($sub_task);
        }

        if($this->check_restore_finished($sub_task))
        {
            $sub_task['finished']=1;
            $sub_task['unzip_file']['unzip_finished']=1;
        }

        $ret['result']='success';
        $ret['sub_task']=$sub_task;
        return $ret;
    }

    public function extract($file_name,$root_path,$option)
    {
        if (!class_exists('WPvivid_PclZip'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/zip/class-wpvivid-pclzip.php';

        if(!empty($option))
        {
            $GLOBALS['wpvivid_restore_option'] = $option;
        }

        if(!defined('PCLZIP_TEMPORARY_DIR'))
            define(PCLZIP_TEMPORARY_DIR,dirname($root_path));

        $archive = new WPvivid_PclZip($file_name);
        $zip_ret = $archive->extract(WPVIVID_PCLZIP_OPT_PATH, $root_path,WPVIVID_PCLZIP_OPT_REPLACE_NEWER,WPVIVID_PCLZIP_CB_PRE_EXTRACT,'wpvivid_function_pre_extract_callback_2',WPVIVID_PCLZIP_OPT_TEMP_FILE_THRESHOLD,16);
        if(!$zip_ret)
        {
            $ret['result']='failed';
            $ret['error'] = $archive->errorInfo(true);
            $this->log->WriteLog('Extracting failed. Error:'.$archive->errorInfo(true),'notice');
        }
        else
        {
            $ret['result']='success';
        }
        return $ret;
    }

    public function extract_ex($file_name,$extract_files,$root_path,$option)
    {
        if (!class_exists('WPvivid_PclZip'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/zip/class-wpvivid-pclzip.php';

        if(!empty($option))
        {
            $GLOBALS['wpvivid_restore_option'] = $option;
        }

        if(!defined('PCLZIP_TEMPORARY_DIR'))
            define(PCLZIP_TEMPORARY_DIR,dirname($root_path));

        $archive = new WPvivid_PclZip($file_name);
        $zip_ret = $archive->extract(WPVIVID_PCLZIP_OPT_BY_NAME,$extract_files,WPVIVID_PCLZIP_OPT_PATH, $root_path,WPVIVID_PCLZIP_OPT_REPLACE_NEWER,WPVIVID_PCLZIP_CB_PRE_EXTRACT,'wpvivid_function_pre_extract_callback_2',WPVIVID_PCLZIP_OPT_TEMP_FILE_THRESHOLD,16);
        if(!$zip_ret)
        {
            $ret['result']='failed';
            $ret['error'] = $archive->errorInfo(true);
            $this->log->WriteLog('Extracting failed. Error:'.$archive->errorInfo(true),'notice');
        }
        else
        {
            $ret['result']='success';
        }
        return $ret;
    }

    public function extract_by_index($file_name,$root_path,$start,$end,$option)
    {
        $index=$start.'-'.$end;

        if (!class_exists('WPvivid_PclZip'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/zip/class-wpvivid-pclzip.php';

        if(!empty($option))
        {
            $GLOBALS['wpvivid_restore_option'] = $option;
        }

        if(!defined('PCLZIP_TEMPORARY_DIR'))
            define(PCLZIP_TEMPORARY_DIR,dirname($root_path));

        $archive = new WPvivid_PclZip($file_name);
        $zip_ret = $archive->extractByIndex($index,WPVIVID_PCLZIP_OPT_PATH, $root_path,WPVIVID_PCLZIP_OPT_REPLACE_NEWER,WPVIVID_PCLZIP_CB_PRE_EXTRACT,'wpvivid_function_pre_extract_callback_2',WPVIVID_PCLZIP_OPT_TEMP_FILE_THRESHOLD,16);
        if(!$zip_ret)
        {
            $ret['result']='failed';
            $ret['error'] = $archive->errorInfo(true);
            $this->log->WriteLog('Extracting failed. Error:'.$archive->errorInfo(true),'notice');
        }
        else
        {
            $ret['result']='success';
        }
        return $ret;
    }

    public function get_zip_file_count($file_name)
    {
        if (!class_exists('WPvivid_PclZip'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/zip/class-wpvivid-pclzip.php';

        $archive = new WPvivid_PclZip($file_name);
        $properties=$archive->properties();
        return $properties['nb'];
    }

    public function check_restore_finished($sub_task)
    {
        $finished=true;

        $files=$sub_task['unzip_file']['files'];

        foreach ($files as $index=>$file)
        {
            if($file['finished']==1)
            {
                continue;
            }
            else
            {
                $finished=false;
            }
        }

        return $finished;
    }

    private function transfer_path($path)
    {
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode(DIRECTORY_SEPARATOR,$values);
    }

    public function update_sub_task($sub_task=false)
    {
        $restore_task=get_option('wpvivid_restore_task',array());

        if($restore_task['do_sub_task']!==false)
        {
            $key=$restore_task['do_sub_task'];
            $restore_task['update_time']=time();
            if($sub_task!==false)
                $restore_task['sub_tasks'][$key]=$sub_task;
            update_option('wpvivid_restore_task',$restore_task);
        }
    }

    public function reset_restore($type)
    {
        if($type=='themes')
        {
            return $this->delete_themes();
        }
        else if($type=='plugin')
        {
            return $this->delete_plugins();
        }
        else if($type=='upload')
        {
            return $this->delete_uploads();
        }
        else if($type=='wp-content')
        {
            return $this->delete_wp_content();
        }
        //else if($type=='mu_plugins')
        //{
        //    return $this->delete_mu_plugins();
        //}
        else  if($type=='wp-core')
        {
            return $this->delete_core();
        }
        $ret['result']='success';
        return $ret;
    }

    public function delete_themes()
    {
        if (!function_exists('delete_theme'))
        {
            require_once ABSPATH . 'wp-admin/includes/theme.php';
        }

        if (!function_exists('request_filesystem_credentials'))
        {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $all_themes = wp_get_themes(array('errors' => null));

        foreach ($all_themes as $theme_slug => $theme_details)
        {
            delete_theme($theme_slug);
        }

        update_option('template', '');
        update_option('stylesheet', '');
        update_option('current_theme', '');

        $ret['result']=WPVIVID_SUCCESS;
        return $ret;
    }

    public function delete_plugins()
    {
        if (!function_exists('get_plugins'))
        {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (!function_exists('request_filesystem_credentials'))
        {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $wpvivid_backup_pro='wpvivid-backup-pro/wpvivid-backup-pro.php';
        $wpvivid_backup='wpvivid-backuprestore/wpvivid-backuprestore.php';

        $all_plugins = get_plugins();
        unset($all_plugins[$wpvivid_backup_pro]);
        unset($all_plugins[$wpvivid_backup]);

        if (!empty($all_plugins))
        {
            $this->_delete_plugins(array_keys($all_plugins));
        }

        $ret['result']=WPVIVID_SUCCESS;
        return $ret;
    }

    public function _delete_plugins($plugins)
    {
        if ( empty( $plugins ) )
        {
            return false;
        }

        $plugins_dir = trailingslashit( WP_PLUGIN_DIR );

        foreach ( $plugins as $plugin_file )
        {
            $this_plugin_dir = trailingslashit( dirname( $plugins_dir . $plugin_file ) );

            // If plugin is in its own directory, recursively delete the directory.
            if ( strpos( $plugin_file, '/' ) && $this_plugin_dir != $plugins_dir )
            { //base check on if plugin includes directory separator AND that it's not the root plugin folder
                $this->delete_folder($this_plugin_dir,$plugins_dir);
            } else {
               @wp_delete_file($plugins_dir . $plugin_file);
            }
        }

        return true;
    }

    public function delete_uploads()
    {
        $upload_dir = wp_get_upload_dir();

        $this->delete_folder($upload_dir['basedir'], $upload_dir['basedir']);

        $ret['result']=WPVIVID_SUCCESS;
        return $ret;
    }

    public function delete_folder($folder, $base_folder)
    {
        $files = array_diff(scandir($folder), array('.', '..'));

        foreach ($files as $file)
        {
            if (is_dir($folder . DIRECTORY_SEPARATOR . $file))
            {
                $this->delete_folder($folder . DIRECTORY_SEPARATOR . $file, $base_folder);
            } else {
                @wp_delete_file($folder . DIRECTORY_SEPARATOR . $file);
            }
        } // foreach

        if ($folder != $base_folder)
        {
            $tmp = @rmdir($folder);
            return $tmp;
        } else {
            return true;
        }
    }

    public function delete_wp_content()
    {
        global $wpvivid_plugin;

        $wp_content_dir = trailingslashit(WP_CONTENT_DIR);

        $wpvivid_backup=WPvivid_Setting::get_backupdir();

        $whitelisted_folders = array('mu-plugins', 'plugins', 'themes', 'uploads',$wpvivid_backup);

        $dirs = glob($wp_content_dir . '*', GLOB_ONLYDIR);
        foreach ($dirs as $dir)
        {
            if (false == in_array(basename($dir), $whitelisted_folders))
            {
                $this->delete_folder($dir, $dir);
                @rmdir($dir);
            }
        }

        $ret['result']=WPVIVID_SUCCESS;
        return $ret;
    }

    public function delete_mu_plugins()
    {
        $ret['result']=WPVIVID_SUCCESS;

        $mu_plugins = get_mu_plugins();

        if(empty($mu_plugins))
        {
            return $ret;
        }

        $this->delete_folder(WPMU_PLUGIN_DIR, WPMU_PLUGIN_DIR);

        return $ret;
    }

    public function delete_core()
    {
        $ret['result']=WPVIVID_SUCCESS;

        require_once( ABSPATH . 'wp-admin/includes/update-core.php' );

        global $_old_files;

        $wp_dir = ABSPATH;

        foreach ( $_old_files as $old_file )
        {
            $old_file = $wp_dir . $old_file;
            if ( ! file_exists( $old_file ) )
            {
                continue;
            }

            // If the file isn't deleted, try writing an empty string to the file instead.
            @wp_delete_file($old_file);
        }
        return $ret;
    }
}

function wpvivid_function_pre_extract_callback_2($p_event, &$p_header)
{
    $plugins = substr(WP_PLUGIN_DIR, strpos(WP_PLUGIN_DIR, 'wp-content/'));

    if ( isset( $GLOBALS['wpvivid_restore_option'] ) )
    {
        $option = $GLOBALS['wpvivid_restore_option'];
        $type=$GLOBALS['wpvivid_restore_addon_type'];
        if ($type == 'themes')
        {
            if (isset($option['remove_themes']))
            {
                foreach ($option['remove_themes'] as $slug => $themes)
                {
                    if (empty($slug))
                        continue;
                    if(strpos($p_header['filename'],$plugins.DIRECTORY_SEPARATOR.$slug)!==false)
                    {
                        return 0;
                    }
                }
            }
        }
        else if ($type == 'plugin')
        {
            if (isset($option['remove_plugins']))
            {
                foreach ($option['remove_plugins'] as $slug => $plugin)
                {
                    if (empty($slug))
                        continue;
                    if(strpos($p_header['filename'],$plugins.'/'.$slug)!==false)
                    {
                        return 0;
                    }
                }
            }
        }
    }
    else
    {
        $option=array();
    }

    $path = str_replace('\\','/',WP_CONTENT_DIR);
    $content_path = $path.'/';
    if(strpos($p_header['filename'], $content_path.'advanced-cache.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'], $content_path.'db.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'], $content_path.'object-cache.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'],$plugins.'/wpvivid-backuprestore')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'],'wp-config.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'],'wpvivid_package_info.json')!==false)
    {
        return 0;
    }

    if(isset($option['restore_htaccess'])&&$option['restore_htaccess'])
    {

    }
    else
    {
        if(strpos($p_header['filename'],'.htaccess')!==false)
        {
            return 0;
        }
    }

    if(strpos($p_header['filename'],'.user.ini')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'],'wordfence-waf.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'], $content_path.'mu-plugins/endurance-browser-cache.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'], $content_path.'mu-plugins/endurance-page-cache.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'], $content_path.'mu-plugins/endurance-php-edge.php')!==false)
    {
        return 0;
    }

    if(strpos($p_header['filename'], $content_path.'mu-plugins/wp-stack-cache.php')!==false)
    {
        return 0;
    }

    return 1;
}