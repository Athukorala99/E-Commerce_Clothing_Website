<?php

if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

class WPvivid_Restore_DB_2
{
    public $log;
    public $db_method;

    public $support_engines;
    public $support_charsets;
    public $support_collates;

    public $default_engines;
    public $default_charsets;
    public $default_collates;
    public $old_prefix;
    public $old_base_prefix;
    public $new_prefix;
    public $temp_new_prefix;

    public $old_site_url;
    public $old_home_url;
    public $old_content_url;
    public $old_upload_url;
    public $old_mu_single_site_upload_url;
    public $old_mu_single_home_upload_url;

    public $new_site_url;
    public $new_home_url;
    public $new_content_url;
    public $new_upload_url;

    public $is_migrate;

    public $replacing_table;

    public $is_mu;
    public $skip_table;

    public $sum;
    public $offset;

    public $replace_table_character_set;

    public function __construct($log=false)
    {
        $this->log=$log;
    }

    public function restore($sub_task,$backup_id)
    {
        add_filter('wpvivid_restore_db_skip_replace_tables', array($this, 'skip_tables'),10,2);
        add_filter('wpvivid_restore_db_skip_replace_rows', array($this, 'skip_rows'),10,3);

        add_filter('wpvivid_restore_db_skip_create_tables', array($this, 'skip_create_tables'),10,3);

        $files=$sub_task['unzip_file']['files'];
        $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
        $backup_item = new WPvivid_Backup_Item($backup);
        $local_path=$backup_item->get_local_path();

        if($sub_task['unzip_file']['unzip_finished']==0)
        {
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

                if(isset($file['has_child']))
                {
                    if(!file_exists($local_path))
                    {
                        @mkdir($local_path);
                    }
                    $this->log->WriteLog('Extracting file:'.$file['parent_file'],'notice');

                    $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file['parent_file'].'</span>';
                    $sub_task['unzip_file']['last_action']='Unzipping';
                    $sub_task['unzip_file']['last_unzip_file']=$file['parent_file'];
                    $this->update_sub_task($sub_task);

                    $extract_files[]=$file['file_name'];
                    $ret=$this->extract_ex($local_path.$file['parent_file'],$extract_files,untrailingslashit($local_path));
                    if($ret['result']!='success')
                    {
                        return $ret;
                    }
                    $this->log->WriteLog('Extracting file:'.$file['parent_file'].' finished.','notice');
                    $file_name=$local_path.$file['file_name'];
                }
                else
                {
                    $file_name=$local_path.$file['file_name'];
                }

                $root_path = $this->transfer_path(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . WPvivid_Setting::get_backupdir());

                $root_path = rtrim($root_path, '/');
                $root_path = rtrim($root_path, DIRECTORY_SEPARATOR);
                $this->log->WriteLog('Extracting file:'.$file_name,'notice');
                $sub_task['last_msg']='<span><strong>Extracting file:</strong></span><span>'.$file_name.'</span>';
                $sub_task['unzip_file']['last_action']='Unzipping';
                $sub_task['unzip_file']['last_unzip_file']=$file['file_name'];
                $this->update_sub_task($sub_task);

                $ret=$this->extract($file_name,untrailingslashit($root_path));
                if($ret['result']!='success')
                {
                    return $ret;
                }
                $this->log->WriteLog('Extracting file:'.$file_name.' finished.','notice');
                $sub_task['unzip_file']['files'][$index]['finished']=1;
            }

            $is_crypt = false;
            if($files[0]['options']['dump_db'] == 1 && $files[0]['options']['file_type'] == 'databases')
            {
                $temp_sql_file=$backup_item->get_backup_path($files[0]['file_name']);
                $sql_files=$this->get_sql_file($temp_sql_file);
                foreach ($sql_files as $tmp_sql_file)
                {
                    $sql_file_name = $tmp_sql_file['file_name'];
                    if(preg_match('/.*\.crypt$/', $sql_file_name))
                    {
                        $is_crypt = true;
                        break;
                    }
                }
            }

            if(isset($files[0]['options']['is_crypt'])&&$files[0]['options']['is_crypt']=='1' || $is_crypt === true)
            {
                $ret['result']='failed';
                $ret['error']='The free version of WPvivid Backup Plugin does not support restoration of encrypted database backups. You can consider upgrading to the pro version as needed.';
                return $ret;
            }
            else
            {
                foreach ($files as $file)
                {
                    $sql_file_path=$backup_item->get_backup_path($file['file_name']);
                    $sql_files=$this->get_sql_file($sql_file_path);
                    foreach ($sql_files as $tmp_sql_file)
                    {
                        $sql_file['sql_file_name']=$tmp_sql_file['file_name'];
                        $sql_file['sql_file_size']=filesize($local_path.$tmp_sql_file['file_name']);
                        $sql_file['sql_offset']=0;
                        $sql_file['finished']=0;
                        $sub_task['exec_sql']['sql_files'][$sql_file['sql_file_name']]= $sql_file;
                    }
                }
            }
            $sub_task['unzip_file']['unzip_finished']=1;

            $ret['result']='success';
            $ret['sub_task']=$sub_task;
            $this->update_sub_task($sub_task);
            return $ret;
        }

        if(!empty($sub_task['exec_sql']['sql_files']))
        {
            $sql_files=$sub_task['exec_sql']['sql_files'];
        }
        else
        {
            foreach ($files as $file)
            {
                $sql_file_path=$backup_item->get_backup_path($file['file_name']);
                $sql_files=$this->get_sql_file($sql_file_path);
                foreach ($sql_files as $tmp_sql_file)
                {
                    $sql_file['sql_file_name']=$tmp_sql_file['file_name'];
                    $sql_file['sql_file_size']=filesize($local_path.$tmp_sql_file['file_name']);
                    $sql_file['sql_offset']=0;
                    $sql_file['finished']=0;
                    $sub_task['exec_sql']['sql_files'][$sql_file['sql_file_name']]= $sql_file;
                }
            }
            $this->update_sub_task($sub_task);
            $sql_files=$sub_task['exec_sql']['sql_files'];
        }

        if(empty($sql_files))
        {
            $ret['result']='failed';
            $ret['error']='Sql file not found.';
            return $ret;
        }

        ksort($sql_files);
        $ret=$this->restore_db($sql_files,$local_path,$sub_task);

        return $ret;
    }

    public function get_sql_file($path)
    {
        if(!class_exists('WPvivid_ZipClass'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-zipclass.php';
        $zip=new WPvivid_ZipClass();
        return $zip->list_file($path);
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

    public function extract($file_name,$root_path)
    {
        if (!class_exists('WPvivid_PclZip'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/zip/class-wpvivid-pclzip.php';

        if(!class_exists('WPvivid_ZipClass'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-zipclass.php';

        if(!defined('PCLZIP_TEMPORARY_DIR'))
            define(PCLZIP_TEMPORARY_DIR,dirname($root_path));

        $archive = new WPvivid_PclZip($file_name);
        $zip_ret = $archive->extract(WPVIVID_PCLZIP_OPT_PATH, $root_path,WPVIVID_PCLZIP_OPT_REPLACE_NEWER,WPVIVID_PCLZIP_CB_PRE_EXTRACT,'wpvivid_function_pre_extract_callback',WPVIVID_PCLZIP_OPT_TEMP_FILE_THRESHOLD,16);
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

    public function extract_ex($file_name,$extract_files,$root_path)
    {
        if (!class_exists('WPvivid_PclZip'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/zip/class-wpvivid-pclzip.php';

        if(!class_exists('WPvivid_ZipClass'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-zipclass.php';

        if(!defined('PCLZIP_TEMPORARY_DIR'))
            define(PCLZIP_TEMPORARY_DIR,dirname($root_path));

        $archive = new WPvivid_PclZip($file_name);
        $zip_ret = $archive->extract(WPVIVID_PCLZIP_OPT_BY_NAME,$extract_files,WPVIVID_PCLZIP_OPT_PATH, $root_path,WPVIVID_PCLZIP_OPT_REPLACE_NEWER,WPVIVID_PCLZIP_CB_PRE_EXTRACT,'wpvivid_function_pre_extract_callback',WPVIVID_PCLZIP_OPT_TEMP_FILE_THRESHOLD,16);
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

    private function transfer_path($path)
    {
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode(DIRECTORY_SEPARATOR,$values);
    }

    public function restore_db($sql_files,$local_path,$sub_task)
    {
        $ret['result']='success';
        $ret['sub_task']=$sub_task;

        foreach ($sql_files as $sql_file_name=>$sql_file)
        {
            if($sql_file['finished']==1)
            {
                continue;
            }
            $this->log->WriteLog('Start restoring sql file offset:'.$sql_file['sql_offset'],'notice');
            $sub_task['exec_sql']['last_action']='Importing';
            $this->sum=filesize($local_path.$sql_file_name);
            $this->offset=($sql_file['sql_offset']);

            $ret=$this->exec_sql($sql_file_name,$local_path,$sub_task);
            if($ret['result']=='success')
            {
                $sub_task=$ret['sub_task'];
                break;
            }
            else
            {
                $this->log->WriteLog('Restoring sql failed:'.$ret['error'],'notice');
                $this->remove_tmp_table($sub_task);
                return $ret;
            }
        }

        $exec_sql_finished=true;
        foreach ($sub_task['exec_sql']['sql_files'] as $sql_file_name=>$sql_file)
        {
            if($sql_file['finished']==0)
            {
                $exec_sql_finished=false;
                break;
            }
        }

        $sub_task['exec_sql']['exec_sql_finished']=$exec_sql_finished;
        if($sub_task['exec_sql']['exec_sql_finished']==1)
        {
            $ret=$this->replace_tables_rows($sub_task);
            if($ret['result']=='success')
            {
                $sub_task=$ret['sub_task'];
            }
            else
            {
                $this->log->WriteLog('Restoring failed:'.$ret['error'],'notice');
                $this->remove_tmp_table($sub_task);
                return $ret;
            }
        }

        if($sub_task['exec_sql']['replace_rows_finished']==1)
        {
            $ret=$this->finish_restore_db($sql_files,$local_path,$sub_task);
            if($ret['result']!='success')
            {
                return $ret;
            }
            $this->log->WriteLog('Restore db success','notice');
        }

        return $ret;
    }

    public function str_replace_first($from, $to, $content)
    {
        $from = '/'.preg_quote($from, '/').'/';

        return preg_replace($from, $to, $content, 1);
    }

    public function remove_tmp_table($sub_task)
    {
        global $wpdb;
        $temp_new_prefix='tmp'.$sub_task['exec_sql']['db_id'].'_';
        $tables = $wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', array($temp_new_prefix . '%')));
        foreach ($tables as $table)
        {
            $wpdb->query('DROP TABLE IF EXISTS `' . $table.'`');
        }
        $ret['result']='success';
        return $ret;
    }

    public function exec_sql($sql_file_name,$local_path,$sub_task)
    {
        include_once WPVIVID_PLUGIN_DIR . '/includes/new_backup/class-wpvivid-restore-db-method-2.php';

        $this->db_method=new WPvivid_Restore_DB_Method_2();
        $this->db_method->set_skip_query(0);
        $this->skip_table=false;

        $sql_file=$local_path.$sql_file_name;
        if(!file_exists($sql_file))
        {
            return array('result'=>'failed','error'=>'Database\'s .sql file not found. Please switch the database access method from WPDB to PDO in the plugin\'s Advanced Settings and try it again.');
        }

        $ret=$this->db_method->connect_db();
        if($ret['result']=='failed')
        {
            return $ret;
        }

        if($this->db_method->test_db()===false)
        {
            $this->log->WriteLog($this->db_method->get_last_error(),'notice');
            $ret['result']='failed';
            $ret['error']=$this->db_method->get_last_error();
            return $ret;
        }

        $this->db_method->check_max_allow_packet($this->log);

        $this->log->WriteLog($this->db_method->get_last_log(),'notice');

        $this->db_method->init_sql_mode();

        global $wpdb;
        $wpdb->query('SET FOREIGN_KEY_CHECKS=0;');

        $this->is_mu=false;
        if(isset($sub_task['options']['is_mu']))
        {
            $this->is_mu=true;
        }
        else
        {
            $this->is_mu=false;
        }

        if($sub_task['exec_sql']['init_sql_finished']!=1)
        {
            $ret=$this->init_restore_db($sql_file,$sub_task);
            $sub_task=$ret['sub_task'];
            $sub_task['exec_sql']['init_sql_finished']=1;
            $this->update_sub_task($sub_task);
        }
        else
        {
            $this->default_engines= $sub_task['db_info']['default_engine'];
            $this->default_charsets=$sub_task['db_info']['default_charsets'];
            $this->default_collates=$sub_task['db_info']['default_collates'];
            $this->old_base_prefix=$sub_task['db_info']['base_prefix'];
            $this->new_prefix=$sub_task['db_info']['new_prefix'];
            $this->temp_new_prefix=$sub_task['db_info']['temp_new_prefix'];

            $this->new_site_url= $sub_task['db_info']['new_site_url'];
            $this->new_home_url=$sub_task['db_info']['new_home_url'];
            $this->new_content_url=$sub_task['db_info']['new_content_url'];
            $this->new_upload_url=$sub_task['db_info']['new_upload_url'];
            $this->is_migrate=$sub_task['db_info']['is_migrate'];
            $this->old_site_url = $sub_task['db_info']['old_site_url'];
            $this->old_home_url =$sub_task['db_info']['old_home_url'];
            $this->old_content_url =$sub_task['db_info']['old_content_url'];
            $this->old_upload_url = $sub_task['db_info']['old_upload_url'];
            $this->old_prefix =$sub_task['db_info']['old_prefix'];
            if(isset($sub_task['db_info']['old_mu_single_site_upload_url']))
            {
                $this->old_mu_single_site_upload_url=$sub_task['db_info']['old_mu_single_site_upload_url'];
            }
            else
            {
                $this->old_mu_single_site_upload_url='';
            }

            if(isset($sub_task['db_info']['old_mu_single_home_upload_url']))
            {
                $this->old_mu_single_home_upload_url=$sub_task['db_info']['old_mu_single_home_upload_url'];
            }
            else
            {
                $this->old_mu_single_home_upload_url='';
            }


            $result = $wpdb->get_results("SHOW ENGINES", OBJECT_K);
            foreach ($result as $key=>$value)
            {
                $this->support_engines[]=$key;
            }

            $result = $wpdb->get_results("SHOW CHARACTER SET", OBJECT_K);
            foreach ($result as $key=>$value)
            {
                $this->support_charsets[]=$key;
            }

            $result = $wpdb->get_results("SHOW COLLATION", OBJECT_K);
            foreach ($result as $key=>$value)
            {
                $this->support_collates[$key]=$value;
            }
        }
        $sql_handle = fopen($sql_file,'r');
        if($sql_handle===false)
        {
            $ret['result']=WPVIVID_FAILED;
            $ret['error']='file not found. file name:'.$sql_file;
            return $ret;
        }

        fseek($sql_handle,$sub_task['exec_sql']['sql_files'][$sql_file_name]['sql_offset']);

        $line_num = 0;
        $query='';

        $restore_task=get_option('wpvivid_restore_task',array());
        $restore_detail_options=$restore_task['restore_detail_options'];
        $sql_file_buffer_pre_request=$restore_detail_options['sql_file_buffer_pre_request'];
        $max_buffer_size=$sql_file_buffer_pre_request*1024*1024;
        $this->replace_table_character_set=isset($restore_detail_options['replace_table_character_set'])?$restore_detail_options['replace_table_character_set']:false;

        $current_offset=$sub_task['exec_sql']['sql_files'][$sql_file_name]['sql_offset'];

        if(!empty($sub_task['exec_sql']['current_table']))
        {
            if(apply_filters('wpvivid_restore_db_skip_create_tables',false,$sub_task['exec_sql']['current_table'],$sub_task['options']))
            {
                $this->log->WriteLog('Skipping table '.$sub_task['exec_sql']['current_table'],'notice');
                $this->skip_table=$sub_task['exec_sql']['current_table'];
            }

            /*if($sub_task['exec_sql']['current_need_replace_table'])
            {
                $this->execute_sql('START TRANSACTION',$sub_task);

                $ret_replace_row=$this->do_replace_row($sql_file_name,$sub_task);
                $sub_task=$ret_replace_row['sub_task'];
                if(!$ret_replace_row['finished'])
                {
                    $this->execute_sql('COMMIT',$sub_task);
                    $sub_task['exec_sql']['sql_files'][$sql_file_name]['finished']=0;
                    $ret['result']='success';
                    $ret['sub_task']=$ret_replace_row['sub_task'];
                    //sub_task
                    $this->update_sub_task($sub_task);
                    return $ret;
                }
            }*/

            $sub_task['last_msg']='Importing sql file:'.$sql_file_name.' table: '.$sub_task['exec_sql']['current_table'].' '.size_format($this->offset,2).'/'.size_format($this->sum,2);
            $this->update_sub_task($sub_task);
        }
        else
        {
            $sub_task['last_msg']='Importing sql file:'.$sql_file_name.size_format($this->offset,2).'/'.size_format($this->sum,2);
            $this->update_sub_task($sub_task);
        }

        $this->execute_sql('START TRANSACTION',$sub_task);

        $progress_offset=$current_offset;

        while(!feof($sql_handle))
        {
            if(empty($query))
            {
                $sub_task['exec_sql']['sql_files'][$sql_file_name]['sql_offset']=ftell($sql_handle);
                $sub_task['exec_sql']['last_action']='Importing';

                if(!empty($sub_task['exec_sql']['current_table']))
                {
                    $sub_task['last_msg']='Importing sql file:'.$sql_file_name.' table: '.$sub_task['exec_sql']['current_table'].' '.size_format($sub_task['exec_sql']['sql_files'][$sql_file_name]['sql_offset'],2).'/'.size_format($this->sum,2);
                }
                else
                {
                    $sub_task['last_msg']='Importing sql file:'.$sql_file_name.size_format($sub_task['exec_sql']['sql_files'][$sql_file_name]['sql_offset'],2).'/'.size_format($this->sum,2);
                }
                $read_offset=$sub_task['exec_sql']['sql_files'][$sql_file_name]['sql_offset']-$current_offset;

                if($read_offset>$max_buffer_size)
                {
                    fclose($sql_handle);
                    $this->execute_sql('COMMIT',$sub_task);
                    $this->log->WriteLog('Reading sql file completed offset:'.$sub_task['exec_sql']['sql_files'][$sql_file_name]['sql_offset'],'notice');
                    $sub_task['exec_sql']['sql_files'][$sql_file_name]['finished']=0;
                    $ret['result']='success';
                    $ret['sub_task']=$sub_task;

                    return $ret;
                }
                else
                {
                    if($sub_task['exec_sql']['sql_files'][$sql_file_name]['sql_offset']-$progress_offset>1024*100)
                    {
                        $progress_offset=$sub_task['exec_sql']['sql_files'][$sql_file_name]['sql_offset'];
                        $this->update_sub_task($sub_task);
                    }
                }
            }

            $line = fgets($sql_handle);
            $line_num ++;
            $startWith = substr(trim($line), 0 ,2);
            $startWithEx = substr(trim($line), 0 ,3);
            $endWith = substr(trim($line), -1 ,1);
            $line = rtrim($line);

            if (empty($line) || $startWith == '--' || ($startWith == '/*'&&$startWithEx!='/*!') || $startWith == '//')
            {
                continue;
            }

            $query = $query . $line;
            if ($endWith == ';')
            {
                if (preg_match('#^\\s*CREATE TABLE#', $query))
                {
                    /*if (preg_match('/TYPE=/', $query))
                    {
                        $query = str_replace('TYPE=', 'ENGINE=', $query);
                    }*/
                    $this->skip_table=false;

                    $sub_task['exec_sql']['current_table']=$this->create_table($query,$sub_task['exec_sql']['current_old_table'],$sub_task);

                    if(apply_filters('wpvivid_restore_db_skip_create_tables',false,$sub_task['exec_sql']['current_table'],$sub_task['options']))
                    {
                        $this->log->WriteLog('Skipping table '.$sub_task['exec_sql']['current_table'],'notice');
                        $this->skip_table=$sub_task['exec_sql']['current_table'];
                    }
                    else
                    {
                        $this->log->WriteLog('Creating table '.$sub_task['exec_sql']['current_table'],'notice');
                    }
                    //$sub_task['exec_sql']['current_replace_table_finish']=false;
                    //$sub_task['exec_sql']['current_need_replace_table']=false;
                    $sub_task['exec_sql']['current_replace_row']=0;
                    $this->update_sub_task($sub_task);
                }
                else if(preg_match('#^\\s*LOCK TABLES#',$query))
                {
                    //$this->lock_table($query);
                }
                else if(preg_match('#^\\s*INSERT INTO#', $query))
                {
                    if($this->skip_table!==false&&$this->skip_table==$sub_task['exec_sql']['current_table'])
                    {
                        $query = '';
                        continue;
                    }

                    $this->insert($query,$sub_task);
                }
                else if(preg_match('#^\\s*DROP TABLE #', $query))
                {
                    if($this->skip_table!==false&&$this->skip_table==$sub_task['exec_sql']['current_table'])
                    {

                    }
                    else
                    {
                        if($this->is_migrate&&!empty($sub_task['exec_sql']['current_table']))
                        {
                            $replace_tables['current_table']=$sub_task['exec_sql']['current_table'];
                            $replace_tables['current_old_table']=$sub_task['exec_sql']['current_old_table'];
                            $replace_tables['finished']=0;
                            $replace_tables['offset']=0;
                            $sub_task['exec_sql']['replace_tables'][$sub_task['exec_sql']['current_table']]=$replace_tables;
                            $this->update_sub_task($sub_task);
                            /*if($this->is_og_table($sub_task['exec_sql']['current_old_table']))
                            {
                                $sub_task['exec_sql']['current_need_replace_table']=true;

                                $ret_replace_row=$this->do_replace_row($sql_file_name,$sub_task);
                                $sub_task=$ret_replace_row['sub_task'];
                                if(!$ret_replace_row['finished'])
                                {
                                    $this->execute_sql('COMMIT',$sub_task);
                                    $sub_task['exec_sql']['sql_files'][$sql_file_name]['finished']=0;
                                    $ret['result']='success';
                                    $ret['sub_task']=$sub_task;
                                    $this->update_sub_task($sub_task);
                                    return $ret;
                                }
                            }*/
                        }
                    }


                    $this->drop_table($query,$sub_task);
                }
                else if(preg_match('#\/*!#', $query))
                {
                    if($this->skip_table!==false&&$this->skip_table==$sub_task['exec_sql']['current_table'])
                    {
                        $query = '';
                        continue;
                    }

                    if ($this->replace_table_execute_sql($query,$sub_task['exec_sql']['current_old_table'],$sub_task)===false)
                    {
                        $query = '';
                        continue;
                    }
                }
                else
                {
                    if($this->skip_table!==false&&$this->skip_table==$sub_task['exec_sql']['current_table'])
                    {
                        $query = '';
                        continue;
                    }

                    if ( $this->execute_sql($query,$sub_task)===false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                        $query = '';
                        continue;
                    }
                }
                $query = '';
            }
        }

        $this->execute_sql('COMMIT',$sub_task);

        $sub_task['exec_sql']['sql_files'][$sql_file_name]['sql_offset']=ftell($sql_handle);

        $sub_task['exec_sql']['sql_files'][$sql_file_name]['finished']=1;

        $replace_tables['current_table']=$sub_task['exec_sql']['current_table'];
        $replace_tables['current_old_table']=$sub_task['exec_sql']['current_old_table'];
        $replace_tables['finished']=0;
        $replace_tables['offset']=0;
        $sub_task['exec_sql']['replace_tables'][$sub_task['exec_sql']['current_table']]=$replace_tables;
        $this->update_sub_task($sub_task);

        /*if($this->skip_table!==false&&$this->skip_table==$sub_task['exec_sql']['current_table'])
        {
            $sub_task['exec_sql']['sql_files'][$sql_file_name]['finished']=1;
            $sub_task['exec_sql']['current_replace_table_finish']=false;
        }
        else
        {
            $ret_replace_row=$this->do_replace_row($sql_file_name,$sub_task);
            $sub_task=$ret_replace_row['sub_task'];
            if($ret_replace_row['finished'])
            {
                $sub_task['exec_sql']['sql_files'][$sql_file_name]['finished']=1;
                $sub_task['exec_sql']['current_replace_table_finish']=false;
            }
            else
            {
                $sub_task['exec_sql']['sql_files'][$sql_file_name]['finished']=0;
            }
        }*/

        fclose($sql_handle);
        $sub_task['exec_sql']['last_action']='Importing';
        $this->update_sub_task($sub_task);
        $ret['result']='success';
        $ret['sub_task']=$sub_task;

        return $ret;
    }

    private function init_db($sub_task)
    {
        include_once WPVIVID_PLUGIN_DIR . '/includes/new_backup/class-wpvivid-restore-db-method-2.php';

        $this->db_method=new WPvivid_Restore_DB_Method_2();
        $this->db_method->set_skip_query(0);
        $this->skip_table=false;

        $ret=$this->db_method->connect_db();
        if($ret['result']=='failed')
        {
            return $ret;
        }

        $this->db_method->check_max_allow_packet($this->log);

        $this->log->WriteLog($this->db_method->get_last_log(),'notice');

        $this->db_method->init_sql_mode();

        $this->default_engines= $sub_task['db_info']['default_engine'];
        $this->default_charsets=$sub_task['db_info']['default_charsets'];
        $this->default_collates=$sub_task['db_info']['default_collates'];
        $this->old_base_prefix=$sub_task['db_info']['base_prefix'];
        $this->new_prefix=$sub_task['db_info']['new_prefix'];
        $this->temp_new_prefix=$sub_task['db_info']['temp_new_prefix'];

        $this->new_site_url= $sub_task['db_info']['new_site_url'];
        $this->new_home_url=$sub_task['db_info']['new_home_url'];
        $this->new_content_url=$sub_task['db_info']['new_content_url'];
        $this->new_upload_url=$sub_task['db_info']['new_upload_url'];
        $this->is_migrate=$sub_task['db_info']['is_migrate'];
        $this->old_site_url = $sub_task['db_info']['old_site_url'];
        $this->old_home_url =$sub_task['db_info']['old_home_url'];
        $this->old_content_url =$sub_task['db_info']['old_content_url'];
        $this->old_upload_url = $sub_task['db_info']['old_upload_url'];
        $this->old_prefix =$sub_task['db_info']['old_prefix'];
        if(isset($sub_task['db_info']['old_mu_single_site_upload_url']))
        {
            $this->old_mu_single_site_upload_url=$sub_task['db_info']['old_mu_single_site_upload_url'];
        }
        else
        {
            $this->old_mu_single_site_upload_url='';
        }

        if(isset($sub_task['db_info']['old_mu_single_home_upload_url']))
        {
            $this->old_mu_single_home_upload_url=$sub_task['db_info']['old_mu_single_home_upload_url'];
        }
        else
        {
            $this->old_mu_single_home_upload_url='';
        }

        $ret['result']='success';
        return $ret;
    }

    public function do_replace_row($sql_file_name,$sub_task)
    {
        if($this->is_migrate&&!empty($sub_task['exec_sql']['current_table']))
        {
            if($this->is_og_table($sub_task['exec_sql']['current_old_table']))
            {
                if($sub_task['exec_sql']['current_replace_table_finish'])
                {
                    $ret['finished']=1;
                }
                else
                {
                    $sub_task['exec_sql']['last_action']='Importing';
                    $this->update_sub_task($sub_task);

                    $restore_task=get_option('wpvivid_restore_task',array());
                    $restore_detail_options=$restore_task['restore_detail_options'];
                    $replace_rows_pre_request=$restore_detail_options['replace_rows_pre_request'];


                    $ret=$this->replace_row($sql_file_name,$sub_task,$replace_rows_pre_request);
                    $sub_task['exec_sql']['current_replace_row']=$ret['replace_row'];
                    $sub_task['exec_sql']['current_replace_table_finish']=$ret['current_replace_table_finish'];
                    if($ret['current_replace_table_finish']==false)
                    {
                        $ret['finished']=0;
                    }
                    else
                    {
                        $ret['finished']=1;
                    }
                }
            }
            else
            {
                $ret['finished']=1;
            }

        }
        else
        {
            $ret['finished']=1;
        }

        $ret['result']='success';
        $ret['sub_task']=$sub_task;
        return $ret;
    }

    public function do_replace_row_ex($table_name,$replace_table_data,$sub_task)
    {
        $this->init_db($sub_task);

        if($this->is_migrate&&!empty($replace_table_data['current_table']))
        {
            $tmp_option=$sub_task['options'];
            if($this->is_mu&&isset($tmp_option['site_id']))
            {
                if($this->is_mu_single_og_table($replace_table_data['current_old_table']))
                {
                    $sub_task['exec_sql']['last_action']='Importing';
                    $this->update_sub_task($sub_task);

                    $restore_task=get_option('wpvivid_restore_task',array());

                    $restore_detail_options=$restore_task['restore_detail_options'];
                    $replace_rows_pre_request=$restore_detail_options['replace_rows_pre_request'];

                    $this->execute_sql('START TRANSACTION',$sub_task);
                    $ret=$this->replace_row_ex($table_name,$replace_table_data,$sub_task,$replace_rows_pre_request);
                    $this->execute_sql('COMMIT',$sub_task);

                    $sub_task['exec_sql']['replace_tables'][$table_name]['offset']=$ret['replace_row'];
                    $sub_task['exec_sql']['replace_tables'][$table_name]['finished']=$ret['current_replace_table_finish'];
                    if($ret['current_replace_table_finish']==false)
                    {
                        $ret['finished']=0;
                    }
                    else
                    {
                        $ret['finished']=1;
                    }
                }
            }
            else if($this->is_og_table($replace_table_data['current_old_table']))
            {
                $sub_task['exec_sql']['last_action']='Importing';
                $this->update_sub_task($sub_task);

                $restore_task=get_option('wpvivid_restore_task',array());

                $restore_detail_options=$restore_task['restore_detail_options'];
                $replace_rows_pre_request=$restore_detail_options['replace_rows_pre_request'];

                $this->execute_sql('START TRANSACTION',$sub_task);
                $ret=$this->replace_row_ex($table_name,$replace_table_data,$sub_task,$replace_rows_pre_request);
                $this->execute_sql('COMMIT',$sub_task);

                $sub_task['exec_sql']['replace_tables'][$table_name]['offset']=$ret['replace_row'];
                $sub_task['exec_sql']['replace_tables'][$table_name]['finished']=$ret['current_replace_table_finish'];
                $sub_task['last_msg']=$ret['last_msg'];
                if($ret['current_replace_table_finish']==false)
                {
                    $ret['finished']=0;
                }
                else
                {
                    $ret['finished']=1;
                }
            }
            else
            {
                $sub_task['exec_sql']['replace_tables'][$table_name]['finished']=1;
                $ret['finished']=1;
            }

        }
        else
        {
            $sub_task['exec_sql']['replace_tables'][$table_name]['finished']=1;
            $ret['finished']=1;
        }

        $ret['result']='success';
        $ret['sub_task']=$sub_task;
        return $ret;
    }

    public function replace_tables_rows($sub_task)
    {
        $this->is_mu=false;
        if(isset($sub_task['options']['is_mu']))
        {
            $this->is_mu=true;
        }
        else
        {
            $this->is_mu=false;
        }

        if(empty($sub_task['exec_sql']['replace_tables']))
        {
            $sub_task['exec_sql']['replace_rows_finished']=1;
            $this->update_sub_task($sub_task);
            $ret['result']='success';
            $ret['sub_task']=$sub_task;
            return $ret;
        }
        else
        {
            foreach ($sub_task['exec_sql']['replace_tables'] as $table_name=>$replace_table_data)
            {
                if($replace_table_data['finished']==1)
                {
                    continue;
                }
                $this->log->WriteLog('Start replacing table '.$table_name.' offset:'.$replace_table_data['offset'],'notice');
                $sub_task['exec_sql']['last_action']='Importing';
                $ret=$this->do_replace_row_ex($table_name,$replace_table_data,$sub_task);
                if($ret['result']=='success')
                {
                    $sub_task=$ret['sub_task'];
                    break;
                }
                else
                {
                    $this->log->WriteLog('Restoring failed:'.$ret['error'],'notice');
                    $this->remove_tmp_table($sub_task);
                    return $ret;
                }
            }

            $replace_rows_finished=true;
            foreach ($sub_task['exec_sql']['replace_tables'] as $table_name=>$replace_table_data)
            {
                if($replace_table_data['finished']==0)
                {
                    $replace_rows_finished=false;
                    break;
                }
            }

            $sub_task['exec_sql']['replace_rows_finished']=$replace_rows_finished;
            $this->update_sub_task($sub_task);
            $ret['result']='success';
            $ret['sub_task']=$sub_task;

            return $ret;
        }
    }

    public function init_restore_db($sql_file,$sub_task)
    {
        global $wpdb;

        $option=$sub_task['options'];

        $this->support_engines=array();
        $this->support_charsets=array();
        $this->support_collates=array();
        $this->default_engines=array();
        $this->default_charsets=array();
        $this->default_collates=array();

        if(isset($option['default_engine']))
        {
            $sub_task['db_info']['default_engine']=$this->default_engines=$option['default_engine'];
        }
        else
        {
            $sub_task['db_info']['default_engine'][]=$this->default_engines[]='MyISAM';
        }

        if(isset($option['default_charsets']))
        {
            $sub_task['db_info']['default_charsets']=$this->default_charsets=$option['default_charsets'];
        }
        else
        {
            $sub_task['db_info']['default_charsets'][]=$this->default_charsets[]=DB_CHARSET;
        }

        if(isset($option['default_collations']))
        {
            $sub_task['db_info']['default_collates']=$this->default_collates=$option['default_collations'];
        }
        else
        {
            $sub_task['db_info']['default_collates'][]=$this->default_collates[]=DB_COLLATE;
        }


        if($this->is_mu&&isset($option['site_id']))
        {
            $sub_task['db_info']['base_prefix']=$this->old_base_prefix=$option['base_prefix'];
            $sub_task['db_info']['old_prefix']=$this->old_prefix=$option['blog_prefix'];
            $sub_task['db_info']['site_url']=$this->old_site_url=$option['site_url'];
            $sub_task['db_info']['home_url']=$this->old_home_url=$option['home_url'];
            $this->old_content_url='';
            $this->old_upload_url='';
            $sub_task['db_info']['old_mu_single_site_upload_url']=$this->old_mu_single_site_upload_url=trailingslashit($option['site_url']).'wp-content/uploads/sites/'.$option['site_id'];
            $sub_task['db_info']['old_mu_single_home_upload_url']=$this->old_mu_single_home_upload_url=trailingslashit($option['home_url']).'wp-content/uploads/sites/'.$option['site_id'];

            if($option['overwrite'])
            {
                $sub_task['db_info']['new_prefix']=$this->new_prefix=$wpdb->get_blog_prefix($option['overwrite_site']);
                $sub_task['db_info']['new_site_url']=$this->new_site_url= untrailingslashit(get_site_url($option['overwrite_site']));
                $sub_task['db_info']['new_home_url']=$this->new_home_url=untrailingslashit(get_home_url($option['overwrite_site']));
                $sub_task['db_info']['new_content_url']=$this->new_content_url=untrailingslashit(content_url());
                $upload_dir  = wp_upload_dir();
                $sub_task['db_info']['new_upload_url']=$this->new_upload_url=untrailingslashit($upload_dir['baseurl']);
            }
            else
            {
                $sub_task['db_info']['new_prefix']=$this->new_prefix=$wpdb->get_blog_prefix($option['site_id']);
                $sub_task['db_info']['new_site_url']=$this->new_site_url= untrailingslashit(get_site_url($option['site_id']));
                $sub_task['db_info']['new_home_url']=$this->new_home_url=untrailingslashit(get_home_url($option['site_id']));
                $sub_task['db_info']['new_content_url']=$this->new_content_url=untrailingslashit(content_url());
                $upload_dir  = wp_upload_dir();
                $sub_task['db_info']['new_upload_url']=$this->new_upload_url=untrailingslashit($upload_dir['baseurl']);
            }
            $sub_task['db_info']['temp_new_prefix']=$this->temp_new_prefix='tmp'.$sub_task['exec_sql']['db_id'].'_';
        }
        else
        {
            $this->old_prefix='';
            $this->old_base_prefix='';
            $this->old_mu_single_site_upload_url='';
            $this->old_mu_single_home_upload_url='';
            if(isset($option['mu_migrate']))
            {
                $sub_task['db_info']['base_prefix']=$this->old_base_prefix=$option['base_prefix'];
            }
            else
            {
                $sub_task['db_info']['base_prefix']=$this->old_base_prefix;
            }

            $sub_task['db_info']['new_prefix']=$this->new_prefix=$wpdb->base_prefix;
            $sub_task['db_info']['temp_new_prefix']=$this->temp_new_prefix='tmp'.$sub_task['exec_sql']['db_id'].'_';

            $sub_task['db_info']['new_site_url']= $this->new_site_url= untrailingslashit(site_url());
            $sub_task['db_info']['new_home_url']=$this->new_home_url=untrailingslashit(home_url());
            $sub_task['db_info']['new_content_url']=$this->new_content_url=untrailingslashit(content_url());

            $upload_dir  = wp_upload_dir();
            $sub_task['db_info']['new_upload_url']=$this->new_upload_url=untrailingslashit($upload_dir['baseurl']);
        }


        $wpdb->query('SET FOREIGN_KEY_CHECKS=0;');
        $result = $wpdb->get_results("SHOW ENGINES", OBJECT_K);
        foreach ($result as $key=>$value)
        {
            $this->support_engines[]=$key;
        }

        $result = $wpdb->get_results("SHOW CHARACTER SET", OBJECT_K);
        foreach ($result as $key=>$value)
        {
            $this->support_charsets[]=$key;
        }

        $result = $wpdb->get_results("SHOW COLLATION", OBJECT_K);
        foreach ($result as $key=>$value)
        {
            $this->support_collates[$key]=$value;
        }

        $sql_handle = fopen($sql_file,'r');
        if($sql_handle===false)
        {
            $ret['result']=WPVIVID_FAILED;
            $ret['error']='file not found. file name:'.$sql_file;
            return $ret;
        }

        $this->old_site_url='';
        $this->old_home_url='';
        $this->old_content_url='';
        $this->old_upload_url='';

        $line_num = 0;

        while(!feof($sql_handle))
        {
            if($line_num>50)
                break;
            $line = fgets($sql_handle);
            $line_num ++;
            $startWith = substr(trim($line), 0 ,2);
            $startWithEx = substr(trim($line), 0 ,3);
            $endWith = substr(trim($line), -1 ,1);
            $line = rtrim($line);
            if (empty($line) || $startWith == '--' || ($startWith == '/*'&&$startWithEx!='/*!') || $startWith == '//')
            {
                if ($endWith == ';' && preg_match('- # -',$line))
                {
                    $matcher = array();
                    if(empty($this -> site_url) && preg_match('# site_url: (.*?) #',$line,$matcher))
                    {
                        if(empty( $this->old_site_url))
                        {
                            $sub_task['db_info']['old_site_url']=$this->old_site_url = $matcher[1];
                        }

                    }
                    if(empty($this -> home_url) && preg_match('# home_url: (.*?) #',$line,$matcher))
                    {
                        if(empty( $this->old_home_url))
                        {
                            $sub_task['db_info']['old_home_url']=$this->old_home_url = $matcher[1];
                        }
                    }
                    if(empty($this -> content_url) && preg_match('# content_url: (.*?) #',$line,$matcher))
                    {
                        if(empty( $this->old_content_url))
                        {
                            $sub_task['db_info']['old_content_url']= $this->old_content_url = $matcher[1];
                        }
                    }
                    if(empty($this -> upload_url) && preg_match('# upload_url: (.*?) #',$line,$matcher))
                    {
                        if(empty( $this->old_upload_url))
                        {
                            $sub_task['db_info']['old_upload_url']=$this->old_upload_url = $matcher[1];
                        }

                    }
                    if(empty($this -> table_prefix) && preg_match('# table_prefix: (.*?) #',$line,$matcher))
                    {
                        if(empty( $this->old_prefix))
                        {
                            $sub_task['db_info']['old_prefix']= $this->old_prefix = $matcher[1];
                        }
                    }
                }
                continue;
            }
        }

        if($this->old_prefix!=$this->new_prefix||(!empty($this->old_site_url)&&$this->old_site_url!=$this->new_site_url))
        {
            $sub_task['db_info']['is_migrate']=$this->is_migrate=true;
        }
        else
        {
            $sub_task['db_info']['is_migrate']=false;
        }

        fclose($sql_handle);
        $ret['result']='success';
        $ret['sub_task']=$sub_task;
        return $ret;
    }

    private function create_table($query,&$current_old_table,$sub_task)
    {
        $table_name='';
        if (preg_match('/^\s*CREATE TABLE +\`?([^\`]*)\`?/i', $query, $matches))
        {
            $table_name = $matches[1];
            $current_old_table=$table_name;
        }

        $tmp_option=$sub_task['options'];
        if($this->is_mu&&isset($tmp_option['site_id']))
        {
            if($this->is_mu_single_og_table($table_name))
            {
                if(substr($table_name,strlen($this->old_base_prefix))=='users'||substr($table_name,strlen($this->old_base_prefix))=='usermeta')
                {
                    $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_base_prefix));
                }
                else
                {
                    $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_prefix));
                }
            }
            else
            {
                $new_table_name=$current_old_table;
            }
        }
        else if($this->is_og_table($table_name))
        {
            if(!empty($this->old_base_prefix)&&(substr($table_name,strlen($this->old_base_prefix))=='users'||substr($table_name,strlen($this->old_base_prefix))=='usermeta'))
            {
                $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_base_prefix));
            }
            else
            {
                $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_prefix));
            }
        }
        else
        {
            $new_table_name=$current_old_table;
        }

        if($this->old_prefix !== '')
        {
            $query=str_replace($table_name,$new_table_name,$query);
        }
        else
        {
            $query=preg_replace('/'.$table_name.'/',$new_table_name,$query, 1);
        }

        $table_name=$new_table_name;

        if(apply_filters('wpvivid_restore_db_skip_create_tables',false,$table_name,$sub_task['options']))
        {
            return $table_name;
        }

        if($this->replace_table_character_set)
        {
            if (preg_match('/ENGINE=([^\s;]+)/', $query, $matches))
            {
                $engine = $matches[1];
                $replace_engine=true;
                foreach ($this->support_engines as $support_engine)
                {
                    if(strtolower($engine)==strtolower($support_engine))
                    {
                        $replace_engine=false;
                        break;
                    }
                }

                if($replace_engine!==false)
                {
                    if(!empty($this->default_engines))
                        $replace_engine=$this->default_engines[0];
                }

                if($replace_engine!==false)
                {
                    $this->log->WriteLog('Create table replace engine:'.$engine.' to :'.$replace_engine,'notice');
                    $query=str_replace("ENGINE=$engine", "ENGINE=$replace_engine", $query);
                }
            }

            if (preg_match('/CHARSET ([^\s;]+)/', $query, $matches)||preg_match('/CHARSET=([^\s;]+)/', $query, $matches))
            {
                $charset = $matches[1];
                $replace_charset=true;
                foreach ($this->support_charsets as $support_charset)
                {
                    if(strtolower($charset)==strtolower($support_charset))
                    {
                        $replace_charset=false;
                        break;
                    }
                }

                if($replace_charset)
                {
                    $replace_charset=$this->default_charsets[0];
                }

                if($replace_charset!==false)
                {
                    $this->log->WriteLog('Create table replace charset:'.$charset.' to :'.$replace_charset,'notice');
                    $query=str_replace("CHARSET=$charset", "CHARSET=$replace_charset", $query);
                    $query=str_replace("CHARSET $charset", "CHARSET=$replace_charset", $query);
                    $charset=$replace_charset;
                }

                $collate='';

                if (preg_match('/ COLLATE ([a-zA-Z0-9._-]+)/i', $query, $matches))
                {
                    $collate = $matches[1];
                }
                else if(preg_match('/ COLLATE=([a-zA-Z0-9._-]+)/i', $query, $matches))
                {
                    $collate = $matches[1];
                }

                if(!empty($collate))
                {
                    $replace_collate=true;
                    foreach ($this->support_collates as $key=>$support_collate)
                    {
                        if(strtolower($charset)==strtolower($support_collate->Charset)&&strtolower($collate)==strtolower($key))
                        {
                            $replace_collate=false;
                            break;
                        }
                    }

                    if($replace_collate)
                    {
                        $replace_collate=false;
                        foreach ($this->support_collates as $key=>$support_collate)
                        {
                            if(strtolower($charset)==strtolower($support_collate->Charset))
                            {
                                if($support_collate->Default=='Yes')
                                {
                                    $replace_collate=$key;
                                }
                            }
                        }

                        if($replace_collate==false)
                        {
                            foreach ($this->support_collates as $key=>$support_collate)
                            {
                                if(strtolower($charset)==strtolower($support_collate->Charset))
                                {
                                    $replace_collate=$key;
                                    break;
                                }
                            }
                        }
                    }

                    if($replace_collate!==false)
                    {
                        $this->log->WriteLog('Create table replace collate:'.$collate.' to :'.$replace_collate.' '.$charset,'notice');
                        $query=str_replace("COLLATE $collate", "COLLATE $replace_collate", $query);
                        $query=str_replace("COLLATE=$collate", "COLLATE=$replace_collate", $query);
                    }
                }
            }
            else
            {
                if (preg_match('/ COLLATE ([a-zA-Z0-9._-]+)/i', $query, $matches))
                {
                    $collate = $matches[1];
                }
                else if(preg_match('/ COLLATE=([a-zA-Z0-9._-]+)/i', $query, $matches))
                {
                    $collate = $matches[1];
                }

                if(!empty($collate))
                {
                    $replace_collate=true;
                    foreach ($this->support_collates as $key=>$support_collate)
                    {
                        if(strtolower($collate)==strtolower($key))
                        {
                            $replace_collate=false;
                            break;
                        }
                    }

                    if($replace_collate)
                    {
                        $replace_collate=false;
                        foreach ($this->support_collates as $key=>$support_collate)
                        {
                            if(strtolower($this->default_charsets[0])==strtolower($support_collate->Charset))
                            {
                                if($support_collate->Default=='Yes')
                                {
                                    $replace_collate=$key;
                                }
                            }
                        }

                        if($replace_collate==false)
                        {
                            foreach ($this->support_collates as $key=>$support_collate)
                            {
                                if(strtolower($this->default_charsets[0])==strtolower($support_collate->Charset))
                                {
                                    $replace_collate=$key;
                                    break;
                                }
                            }
                        }
                    }

                    if($replace_collate!==false)
                    {
                        $this->log->WriteLog('Create table replace collate:'.$collate.' to :'.$replace_collate,'notice');
                        $query=str_replace("COLLATE $collate", "COLLATE $replace_collate", $query);
                        $query=str_replace("COLLATE=$collate", "COLLATE=$replace_collate", $query);
                    }
                }
            }

            if(preg_match('/\/\*!.*\*\//', $query, $matches))
            {
                $annotation_content = $matches[0];
                $query = str_replace($annotation_content, '', $query);
            }
        }


        $constraints = array();
        if (preg_match_all('/CONSTRAINT ([\a-zA-Z0-9_\']+) FOREIGN KEY \([a-zA-z0-9_\', ]+\) REFERENCES \'?([a-zA-z0-9_]+)\'? /i', $query, $constraint_matches))
        {
            $constraints = $constraint_matches;
        }
        else if (preg_match_all('/ FOREIGN KEY \([a-zA-z0-9_\', ]+\) REFERENCES \'?([a-zA-z0-9_]+)\'? /i', $query, $constraint_matches))
        {
            $constraints = $constraint_matches;
        }
        if (!empty($constraints) && $this->old_prefix != $this->new_prefix)
        {
            foreach ($constraints[0] as $constraint)
            {
                $updated_constraint = str_replace($this->old_prefix, $this->new_prefix, $constraint);
                $query = str_replace($constraint, $updated_constraint, $query);
            }
            $this->log->WriteLog('replace foreign key.','notice');
        }


        if($this->execute_sql($query,$sub_task)===false)
        {
            $this->log->WriteLog($this->db_method->get_last_error(),'notice');
        }

        return $table_name;
    }

    private function lock_table($query,$sub_task)
    {
        if (preg_match('/^\s*LOCK TABLES +\`?([^\`]*)\`?/i', $query, $matches))
        {
            $table_name = $matches[1];

            $tmp_option=$sub_task['options'];
            if($this->is_mu&&isset($tmp_option['site_id']))
            {
                if($this->is_mu_single_og_table($table_name))
                {
                    if(substr($table_name,strlen($this->old_base_prefix))=='users'||substr($table_name,strlen($this->old_base_prefix))=='usermeta')
                    {
                        $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_base_prefix));
                    }
                    else
                    {
                        $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_prefix));
                    }
                }
                else
                {
                    $new_table_name=$table_name;
                }
            }
            else if($this->is_og_table($table_name))
            {
                if(!empty($this->old_base_prefix)&&(substr($table_name,strlen($this->old_base_prefix))=='users'||substr($table_name,strlen($this->old_base_prefix))=='usermeta'))
                {
                    $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_base_prefix));
                }
                else
                {
                    $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_prefix));
                }
            }
            else
            {
                $new_table_name=$table_name;
            }

            $this->log->WriteLog('lock replace table:'.$table_name.' to :'.$new_table_name,'notice');
            $query=str_replace($table_name,$new_table_name,$query);
        }
        if($this->execute_sql($query,$sub_task)===false)
        {
            $this->log->WriteLog($this->db_method->get_last_error(),'notice');
        }
    }

    private function replace_table_execute_sql($query,$table_name,$sub_task)
    {
        $tmp_option=$sub_task['options'];
        if($this->is_mu&&isset($tmp_option['site_id']))
        {
            if($this->is_mu_single_og_table($table_name))
            {
                if(!empty($table_name))
                {
                    if(substr($table_name,strlen($this->old_base_prefix))=='users'||substr($table_name,strlen($this->old_base_prefix))=='usermeta')
                    {
                        $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_base_prefix));
                    }
                    else
                    {
                        $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_prefix));
                    }
                    if($this->old_prefix !== '')
                    {
                        $query=str_replace($table_name,$new_table_name,$query);
                    }
                    else
                    {
                        $query=preg_replace('/'.$table_name.'/',$new_table_name,$query, 1);
                    }
                }
            }
            else
            {
                $new_table_name=$table_name;
                $query=str_replace($table_name,$new_table_name,$query);
            }
        }
        else if($this->is_og_table($table_name))
        {
            if(!empty($table_name))
            {
                $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_prefix));
                $query=str_replace($table_name,$new_table_name,$query);
            }
        }
        else
        {
            $new_table_name=$table_name;
            $query=str_replace($table_name,$new_table_name,$query);
        }

        if($this->execute_sql($query,$sub_task)===false)
        {
            $this->log->WriteLog($this->db_method->get_last_error(),'notice');
        }
    }

    private function insert($query,$sub_task)
    {
        if (preg_match('/^\s*INSERT INTO +\`?([^\`]*)\`?/i', $query, $matches))
        {
            $table_name = $matches[1];

            $tmp_option=$sub_task['options'];
            if($this->is_mu&&isset($tmp_option['site_id']))
            {
                if($this->is_mu_single_og_table($table_name))
                {
                    if(substr($table_name,strlen($this->old_base_prefix))=='users'||substr($table_name,strlen($this->old_base_prefix))=='usermeta')
                    {
                        $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_base_prefix));
                    }
                    else
                    {
                        $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_prefix));
                    }
                }
                else
                {
                    $new_table_name=$table_name;
                }
            }
            else if($this->is_og_table($table_name))
            {
                if(!empty($this->old_base_prefix)&&(substr($table_name,strlen($this->old_base_prefix))=='users'||substr($table_name,strlen($this->old_base_prefix))=='usermeta'))
                {
                    $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_base_prefix));
                }
                else
                {
                    $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_prefix));
                }
            }
            else
            {
                $new_table_name=$table_name;
            }

            if($this->old_prefix !== '')
            {
                $query=str_replace($table_name,$new_table_name,$query);
            }
            else
            {
                $query=preg_replace('/'.$table_name.'/',$new_table_name,$query, 1);
            }
        }

        if($this->execute_sql($query,$sub_task)===false)
        {
            $this->log->WriteLog($this->db_method->get_last_error(),'notice');
        }

        /*
        $pos=strpos($query,'mainwp_child_');
        if($pos!==false)
        {
            $this->log->WriteLog('skip insert item: '.$query,'notice');
        }
        else{
            if($this->execute_sql($query,$sub_task)===false)
            {
                $this->log->WriteLog($this->db_method->get_last_error(),'notice');
            }
        }*/
    }

    private function drop_table($query,$sub_task)
    {
        if (preg_match('/^\s*DROP TABLE IF EXISTS +\`?([^\`]*)\`?\s*;/i', $query, $matches))
        {
            $table_name = $matches[1];

            $tmp_option=$sub_task['options'];
            if($this->is_mu&&isset($tmp_option['site_id']))
            {
                if($this->is_mu_single_og_table($table_name))
                {
                    if(substr($table_name,strlen($this->old_base_prefix))=='users'||substr($table_name,strlen($this->old_base_prefix))=='usermeta')
                    {
                        $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_base_prefix));
                    }
                    else
                    {
                        $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_prefix));
                    }
                }
                else
                {
                    $new_table_name=$table_name;
                }
            }
            else if($this->is_og_table($table_name))
            {
                if(!empty($this->old_base_prefix)&&(substr($table_name,strlen($this->old_base_prefix))=='users'||substr($table_name,strlen($this->old_base_prefix))=='usermeta'))
                {
                    $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_base_prefix));
                }
                else
                {
                    $new_table_name=$this->temp_new_prefix.substr($table_name,strlen($this->old_prefix));
                }
            }
            else
            {
                $new_table_name=$table_name;
            }

            $query=str_replace($table_name,$new_table_name,$query);
            $this->log->WriteLog('Drop table if exist '.$new_table_name,'notice');
        }
        if($this->execute_sql($query,$sub_task)===false)
        {
            $this->log->WriteLog($this->db_method->get_last_error(),'notice');
        }
    }

    private function replace_row_ex($table_name,$replace_table_data,$sub_task,$max_replace_row=100000)
    {
        global $wpdb;

        $max_replace_row=max(100,$max_replace_row);

        $row=$replace_table_data['offset'];
        $this->replacing_table=$table_name;
        $replace_current_table_finish=false;
        if(substr($table_name, strlen($this->temp_new_prefix))=='options')
        {
            if($this->old_prefix!=$this->new_prefix)
            {
                $update_query ='UPDATE '.$table_name.' SET option_name="'.$this->new_prefix.'user_roles" WHERE option_name="'.$this->old_prefix.'user_roles";';

                if($this->execute_sql($update_query,$sub_task)===false)
                {
                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                }
            }
        }

        if(substr($table_name, strlen($this->temp_new_prefix))=='usermeta')
        {
            $tmp_option=$sub_task['options'];
            if($this->is_mu&&isset($tmp_option['site_id']))
            {
                $update_query ='UPDATE '.$table_name.' SET meta_key=REPLACE(meta_key,"'.$this->old_prefix.'","'.$this->new_prefix.'") WHERE meta_key LIKE "'.str_replace('_','\_',$this->old_prefix).'%";';
                if($this->execute_sql($update_query,$sub_task)===false)
                {
                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                }

                $select_query='SELECT * FROM '.$table_name.' WHERE meta_key LIKE "%capabilities%";';
                $results = $wpdb->get_results($select_query,ARRAY_A);
                foreach ($results as $item)
                {
                    $update_query='UPDATE '.$table_name.' SET meta_key=\''.$this->new_prefix.'capabilities\' WHERE meta_key=\''.$item['meta_key'].'\';';
                    if($this->execute_sql($update_query,$sub_task)===false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                    }
                }

                $select_query='SELECT * FROM '.$table_name.' WHERE meta_key LIKE "%user_level%";';
                $results = $wpdb->get_results($select_query,ARRAY_A);
                foreach ($results as $item)
                {
                    $update_query='UPDATE '.$table_name.' SET meta_key=\''.$this->new_prefix.'user_level\' WHERE meta_key=\''.$item['meta_key'].'\';';
                    if($this->execute_sql($update_query,$sub_task)===false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                    }
                }
                $ret['result']='success';
                $ret['replace_row']=0;
                $ret['current_replace_table_finish']=true;
                $ret['last_msg']=$sub_task['last_msg'];
                return $ret;
            }
            else
            {
                if($this->old_prefix!=$this->new_prefix)
                {
                    if($this->old_prefix!=='')
                    {
                        $update_query ='UPDATE '.$table_name.' SET meta_key=REPLACE(meta_key,"'.$this->old_prefix.'","'.$this->new_prefix.'") WHERE meta_key LIKE "'.str_replace('_','\_',$this->old_prefix).'%";';

                        if($this->execute_sql($update_query,$sub_task)===false)
                        {
                            $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                        }
                    }
                    else
                    {
                        if(is_multisite())
                        {
                            $select_query='SELECT * FROM '.$table_name.' WHERE meta_key LIKE "%_capabilities%";';
                            $results = $wpdb->get_results($select_query,ARRAY_A);
                            foreach ($results as $item)
                            {
                                $update_query='UPDATE '.$table_name.' SET meta_key=\''.$this->new_prefix.$item['meta_key'].'\' WHERE meta_key=\''.$item['meta_key'].'\';';
                                if($this->execute_sql($update_query,$sub_task)===false)
                                {
                                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                                }
                            }

                            $select_query='SELECT * FROM '.$table_name.' WHERE meta_key LIKE "%_user_level%";';
                            $results = $wpdb->get_results($select_query,ARRAY_A);
                            foreach ($results as $item)
                            {
                                $update_query='UPDATE '.$table_name.' SET meta_key=\''.$this->new_prefix.$item['meta_key'].'\' WHERE meta_key=\''.$item['meta_key'].'\';';
                                if($this->execute_sql($update_query,$sub_task)===false)
                                {
                                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                                }
                            }
                        }

                        $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'capabilities" WHERE meta_key="' . $this->old_prefix . 'capabilities";';
                        if ($this->execute_sql($update_query, $sub_task) === false)
                        {
                            $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                        }

                        $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'user_level" WHERE meta_key="' . $this->old_prefix . 'user_level";';
                        if ($this->execute_sql($update_query, $sub_task) === false)
                        {
                            $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                        }

                        $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'user-settings" WHERE meta_key="' . $this->old_prefix . 'user-settings";';
                        if ($this->execute_sql($update_query, $sub_task) === false)
                        {
                            $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                        }

                        $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'user-settings-time" WHERE meta_key="' . $this->old_prefix . 'user-settings-time";';
                        if ($this->execute_sql($update_query, $sub_task) === false)
                        {
                            $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                        }

                        $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'dashboard_quick_press_last_post_id" WHERE meta_key="' . $this->old_prefix . 'dashboard_quick_press_last_post_id";';
                        if ($this->execute_sql($update_query, $sub_task) === false)
                        {
                            $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                        }
                    }
                    $ret['result']='success';
                    $ret['replace_row']=0;
                    $ret['current_replace_table_finish']=true;
                    $ret['last_msg']=$sub_task['last_msg'];
                    return $ret;
                }
            }
        }

        if(!empty($this->old_base_prefix)&&substr($table_name,strlen($this->temp_new_prefix))=='usermeta')
        {
            $tmp_option=$sub_task['options'];
            if($this->is_mu&&isset($tmp_option['site_id']))
            {
                $update_query ='UPDATE '.$table_name.' SET meta_key=REPLACE(meta_key,"'.$this->old_base_prefix.'","'.$this->new_prefix.'") WHERE meta_key LIKE "'.str_replace('_','\_',$this->old_base_prefix).'%";';
                if($this->execute_sql($update_query,$sub_task)===false)
                {
                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                }

                $select_query='SELECT * FROM '.$table_name.' WHERE meta_key LIKE "%_capabilities%";';
                $results = $wpdb->get_results($select_query,ARRAY_A);
                foreach ($results as $item)
                {
                    $update_query='UPDATE '.$table_name.' SET meta_key=\''.$this->new_prefix.'capabilities\' WHERE meta_key=\''.$item['meta_key'].'\';';
                    if($this->execute_sql($update_query,$sub_task)===false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                    }
                }

                $select_query='SELECT * FROM '.$table_name.' WHERE meta_key LIKE "%_user_level%";';
                $results = $wpdb->get_results($select_query,ARRAY_A);
                foreach ($results as $item)
                {
                    $update_query='UPDATE '.$table_name.' SET meta_key=\''.$this->new_prefix.'user_level\' WHERE meta_key=\''.$item['meta_key'].'\';';
                    if($this->execute_sql($update_query,$sub_task)===false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                    }
                }
                $ret['result']='success';
                $ret['replace_row']=0;
                $ret['current_replace_table_finish']=true;
                $ret['last_msg']=$sub_task['last_msg'];
                return $ret;
            }
            else
            {
                if($this->old_base_prefix!=$this->new_prefix)
                {
                    if($this->old_base_prefix!=='')
                    {
                        $update_query ='UPDATE '.$table_name.' SET meta_key=REPLACE(meta_key,"'.$this->old_base_prefix.'","'.$this->new_prefix.'") WHERE meta_key LIKE "'.str_replace('_','\_',$this->old_base_prefix).'%";';

                        if($this->execute_sql($update_query,$sub_task)===false)
                        {
                            $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                        }
                    }
                    else
                    {
                        if(is_multisite())
                        {
                            $select_query='SELECT * FROM '.$table_name.' WHERE meta_key LIKE "%_capabilities%";';
                            $results = $wpdb->get_results($select_query,ARRAY_A);
                            foreach ($results as $item)
                            {
                                $update_query='UPDATE '.$table_name.' SET meta_key=\''.$this->new_prefix.$item['meta_key'].'\' WHERE meta_key=\''.$item['meta_key'].'\';';
                                if($this->execute_sql($update_query,$sub_task)===false)
                                {
                                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                                }
                            }

                            $select_query='SELECT * FROM '.$table_name.' WHERE meta_key LIKE "%_user_level%";';
                            $results = $wpdb->get_results($select_query,ARRAY_A);
                            foreach ($results as $item)
                            {
                                $update_query='UPDATE '.$table_name.' SET meta_key=\''.$this->new_prefix.$item['meta_key'].'\' WHERE meta_key=\''.$item['meta_key'].'\';';
                                if($this->execute_sql($update_query,$sub_task)===false)
                                {
                                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                                }
                            }
                        }

                        $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'capabilities" WHERE meta_key="' . $this->old_base_prefix . 'capabilities";';
                        if ($this->execute_sql($update_query, $sub_task) === false)
                        {
                            $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                        }

                        $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'user_level" WHERE meta_key="' . $this->old_base_prefix . 'user_level";';
                        if ($this->execute_sql($update_query, $sub_task) === false)
                        {
                            $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                        }

                        $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'user-settings" WHERE meta_key="' . $this->old_base_prefix . 'user-settings";';
                        if ($this->execute_sql($update_query, $sub_task) === false)
                        {
                            $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                        }

                        $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'user-settings-time" WHERE meta_key="' . $this->old_base_prefix . 'user-settings-time";';
                        if ($this->execute_sql($update_query, $sub_task) === false)
                        {
                            $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                        }

                        $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'dashboard_quick_press_last_post_id" WHERE meta_key="' . $this->old_base_prefix . 'dashboard_quick_press_last_post_id";';
                        if ($this->execute_sql($update_query, $sub_task) === false)
                        {
                            $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                        }
                    }

                    $ret['result']='success';
                    $ret['replace_row']=0;
                    $ret['current_replace_table_finish']=true;
                    $ret['last_msg']=$sub_task['last_msg'];
                    return $ret;
                }
            }
        }

        if($this->old_site_url==$this->new_site_url)
        {
            $ret['result']='success';
            $ret['replace_row']=0;
            $ret['current_replace_table_finish']=true;
            $ret['last_msg']=$sub_task['last_msg'];
            return $ret;
        }

        if($this->is_mu)
        {
            if(substr($table_name, strlen($this->temp_new_prefix))=='blogs')
            {
                $this->log->WriteLog('Update mu blogs table', 'notice');

                if((preg_match('#^https?://([^/]+)#i', $this->new_home_url, $matches) || preg_match('#^https?://([^/]+)#i', $this->new_site_url, $matches)) && (preg_match('#^https?://([^/]+)#i', $this->old_home_url, $old_matches) || preg_match('#^https?://([^/]+)#i', $this->old_site_url, $old_matches)))
                {
                    $new_string = strtolower($matches[1]);
                    $old_string = strtolower($old_matches[1]);
                    $new_path='';
                    $old_path='';

                    if(defined( 'PATH_CURRENT_SITE' ))
                    {
                        $new_path=PATH_CURRENT_SITE;
                    }

                    $query = 'SELECT * FROM `'.$table_name.'`';
                    $result=$this->db_method->query($query,ARRAY_A);
                    if($result && sizeof($result)>0)
                    {
                        $rows = $result;
                        foreach ($rows as $row)
                        {
                            $update=array();
                            $where=array();

                            if($row['blog_id']==1)
                            {
                                $old_path=$row['path'];
                            }

                            $old_domain_data = $row['domain'];
                            $new_domain_data=str_replace($old_string,$new_string,$old_domain_data);

                            $temp_where='`blog_id` = "' . $row['blog_id'] . '"';
                            if (is_callable(array($wpdb, 'remove_placeholder_escape')))
                                $temp_where = $wpdb->remove_placeholder_escape($temp_where);
                            $where[] = $temp_where;
                            $update[] = '`domain` = "' . $new_domain_data . '"';

                            if(!empty($old_path)&&!empty($new_path))
                            {
                                $old_path_data= $row['path'];
                                $new_path_data=$this->str_replace_first($old_path,$new_path,$old_path_data);
                                $update[] = '`path` = "' . $new_path_data . '"';
                            }

                            if(!empty($update)&&!empty($where))
                            {
                                $update_query = 'UPDATE `'.$table_name.'` SET '.implode(', ', $update).' WHERE '.implode(' AND ', array_filter($where)).';';
                                if($this->execute_sql($update_query,$sub_task)===false)
                                {
                                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                                }
                            }
                        }
                    }
                }
            }
        }


        $skip_table=false;
        if(apply_filters('wpvivid_restore_db_skip_replace_tables',$skip_table,$table_name))
        {
            $this->log->WriteLog('Skipping table '.$table_name, 'Warning');
            $ret['result']='success';
            $ret['replace_row']=0;
            $ret['current_replace_table_finish']=true;
            $ret['last_msg']=$sub_task['last_msg'];
            return $ret;
        }

        $query = 'SELECT COUNT(*) FROM `'.$table_name.'`';

        $current_row=0;

        $result=$this->db_method->query($query,ARRAY_N);
        if($result && sizeof($result)>0)
        {
            $count=$result[0][0];
            $this->log->WriteLog('Counting of rows in '.$table_name.': '.$count, 'notice');
            if($count==0)
            {
                $ret['result']='success';
                $ret['replace_row']=0;
                $ret['current_replace_table_finish']=true;
                $ret['last_msg']=$sub_task['last_msg'];
                return $ret;
            }

            $query='DESCRIBE `'.$table_name.'`';
            $result=$this->db_method->query($query,ARRAY_A);
            $columns=array();
            foreach ($result as $data)
            {
                $column['Field']=$data['Field'];
                if($data['Key']=='PRI')
                    $column['PRI']=1;
                else
                    $column['PRI']=0;

                if($data['Type']=='mediumblob')
                {
                    $column['skip']=1;
                }
                $columns[]=$column;
            }

            $page=min(5000,$max_replace_row);

            $update_query='';

            $start_row=$row;
            $replace_row=0;

            for ($current_row = $start_row; $current_row < $count; $current_row += $page)
            {
                $this->log->WriteLog('Replacing the row in '.$current_row. ' line.', 'notice');
                $sub_task['last_msg']='Importing sql file table: '.$table_name.' Replaced row: '.$current_row.'/'.$count.';';

                $this->update_sub_task($sub_task);
                $query = 'SELECT * FROM `'.$table_name.'` LIMIT '.$current_row.', '.$page;
                $replace_row+=$page;
                $result=$this->db_method->query($query,ARRAY_A);
                if($result && sizeof($result)>0)
                {
                    $rows = $result;
                    foreach ($rows as $row)
                    {
                        $update=array();
                        $where=array();
                        foreach ($columns as $column)
                        {
                            if(isset($column['skip']))
                            {
                                //$this->log->WriteLog('Skipping mediumblob type data', 'notice');
                                continue;
                            }

                            $old_data = $row[$column['Field']];
                            if($column['PRI']==1)
                            {
                                $wpdb->escape_by_ref($old_data);
                                $temp_where='`'.$column['Field'].'` = "' . $old_data . '"';
                                if (is_callable(array($wpdb, 'remove_placeholder_escape')))
                                    $temp_where = $wpdb->remove_placeholder_escape($temp_where);
                                $where[] = $temp_where;
                            }

                            $skip_row=false;
                            if(apply_filters('wpvivid_restore_db_skip_replace_rows',$skip_row,$table_name,$column['Field']))
                            {
                                continue;
                            }
                            $new_data=$this->replace_row_data($old_data);
                            if($new_data==$old_data)
                                continue;

                            $wpdb->escape_by_ref($new_data);
                            if (is_callable(array($wpdb, 'remove_placeholder_escape')))
                                $new_data = $wpdb->remove_placeholder_escape($new_data);
                            $update[] = '`'.$column['Field'].'` = "' . $new_data . '"';
                        }

                        if(!empty($update)&&!empty($where))
                        {
                            $temp_query = 'UPDATE `'.$table_name.'` SET '.implode(', ', $update).' WHERE '.implode(' AND ', array_filter($where)).';';
                            $type=$this->db_method->get_type();

                            if($type=='pdo_mysql')
                            {
                                if($update_query=='')
                                {
                                    $update_query=$temp_query;
                                    if(strlen($update_query)>$this->db_method->get_max_allow_packet())
                                    {
                                        if($this->execute_sql($update_query,$sub_task)===false)
                                        {
                                            $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                                        }

                                        $update_query='';
                                    }
                                }
                                else if(strlen($temp_query)+strlen($update_query)>$this->db_method->get_max_allow_packet())
                                {
                                    if($this->execute_sql($update_query,$sub_task)===false)
                                    {
                                        $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                                    }
                                    $update_query=$temp_query;
                                }
                                else
                                {
                                    $update_query.=$temp_query;
                                }
                            }
                            else
                            {
                                $update_query=$temp_query;
                                if($this->execute_sql($update_query,$sub_task)===false)
                                {
                                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                                }
                                $update_query='';
                            }

                        }
                        //return;
                    }
                }

                if($replace_row>$max_replace_row)
                {
                    $current_row+= $page;
                    break;
                }
            }

            if(!empty($update_query))
            {
                if($this->execute_sql($update_query,$sub_task)===false)
                {
                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                }
            }

            if($current_row >= $count)
            {
                $replace_current_table_finish=true;
            }
        }
        else
        {
            $replace_current_table_finish=true;
            $current_row=0;
        }

        $this->log->WriteLog('Replacing row completed. Current row:'.$current_row, 'notice');

        $ret['result']='success';
        $ret['replace_row']=$current_row;
        $ret['max_replace_row']=$max_replace_row;
        $ret['current_replace_table_finish']=$replace_current_table_finish;
        $ret['last_msg']=$sub_task['last_msg'];
        $this->log->WriteLog(json_encode($ret), 'notice');
        return $ret;
    }

    private function replace_row($sql_file_name,$sub_task,$max_replace_row=100000)
    {
        global $wpdb;

        $max_replace_row=max(100,$max_replace_row);

        $table_name=$sub_task['exec_sql']['current_table'];
        $row=$sub_task['exec_sql']['current_replace_row'];

        $this->replacing_table=$table_name;
        $replace_current_table_finish=false;
        $this->log->WriteLog('Dumping table '.$table_name.' is complete. Start replacing row(s).', 'notice');

        if(substr($table_name, strlen($this->temp_new_prefix))=='options')
        {
            if($this->old_prefix!=$this->new_prefix)
            {
                $update_query ='UPDATE '.$table_name.' SET option_name="'.$this->new_prefix.'user_roles" WHERE option_name="'.$this->old_prefix.'user_roles";';

                if($this->execute_sql($update_query,$sub_task)===false)
                {
                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                }
            }
        }

        if(substr($table_name, strlen($this->temp_new_prefix))=='usermeta')
        {
            if($this->old_prefix!=$this->new_prefix)
            {
                if($this->old_prefix!=='')
                {
                    $update_query ='UPDATE '.$table_name.' SET meta_key=REPLACE(meta_key,"'.$this->old_prefix.'","'.$this->new_prefix.'") WHERE meta_key LIKE "'.str_replace('_','\_',$this->old_prefix).'%";';

                    if($this->execute_sql($update_query,$sub_task)===false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                    }
                }
                else
                {
                    if(is_multisite())
                    {
                        $select_query='SELECT * FROM '.$table_name.' WHERE meta_key LIKE "%_capabilities%";';
                        $results = $wpdb->get_results($select_query,ARRAY_A);
                        foreach ($results as $item)
                        {
                            $update_query='UPDATE '.$table_name.' SET meta_key=\''.$this->new_prefix.$item['meta_key'].'\' WHERE meta_key=\''.$item['meta_key'].'\';';
                            if($this->execute_sql($update_query,$sub_task)===false)
                            {
                                $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                            }
                        }

                        $select_query='SELECT * FROM '.$table_name.' WHERE meta_key LIKE "%_user_level%";';
                        $results = $wpdb->get_results($select_query,ARRAY_A);
                        foreach ($results as $item)
                        {
                            $update_query='UPDATE '.$table_name.' SET meta_key=\''.$this->new_prefix.$item['meta_key'].'\' WHERE meta_key=\''.$item['meta_key'].'\';';
                            if($this->execute_sql($update_query,$sub_task)===false)
                            {
                                $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                            }
                        }
                    }

                    $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'capabilities" WHERE meta_key="' . $this->old_prefix . 'capabilities";';
                    if ($this->execute_sql($update_query, $sub_task) === false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                    }

                    $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'user_level" WHERE meta_key="' . $this->old_prefix . 'user_level";';
                    if ($this->execute_sql($update_query, $sub_task) === false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                    }

                    $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'user-settings" WHERE meta_key="' . $this->old_prefix . 'user-settings";';
                    if ($this->execute_sql($update_query, $sub_task) === false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                    }

                    $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'user-settings-time" WHERE meta_key="' . $this->old_prefix . 'user-settings-time";';
                    if ($this->execute_sql($update_query, $sub_task) === false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                    }

                    $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'dashboard_quick_press_last_post_id" WHERE meta_key="' . $this->old_prefix . 'dashboard_quick_press_last_post_id";';
                    if ($this->execute_sql($update_query, $sub_task) === false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                    }
                }
                $ret['result']='success';
                $ret['replace_row']=0;
                $ret['current_replace_table_finish']=true;
                return $ret;
            }
        }

        if(!empty($this->old_base_prefix)&&substr($table_name,strlen($this->temp_new_prefix))=='usermeta')
        {
            if($this->old_base_prefix!=$this->new_prefix)
            {
                if($this->old_base_prefix!=='')
                {
                    $update_query ='UPDATE '.$table_name.' SET meta_key=REPLACE(meta_key,"'.$this->old_base_prefix.'","'.$this->new_prefix.'") WHERE meta_key LIKE "'.str_replace('_','\_',$this->old_base_prefix).'%";';

                    if($this->execute_sql($update_query,$sub_task)===false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                    }
                }
                else
                {
                    if(is_multisite())
                    {
                        $select_query='SELECT * FROM '.$table_name.' WHERE meta_key LIKE "%_capabilities%";';
                        $results = $wpdb->get_results($select_query,ARRAY_A);
                        foreach ($results as $item)
                        {
                            $update_query='UPDATE '.$table_name.' SET meta_key=\''.$this->new_prefix.$item['meta_key'].'\' WHERE meta_key=\''.$item['meta_key'].'\';';
                            if($this->execute_sql($update_query,$sub_task)===false)
                            {
                                $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                            }
                        }

                        $select_query='SELECT * FROM '.$table_name.' WHERE meta_key LIKE "%_user_level%";';
                        $results = $wpdb->get_results($select_query,ARRAY_A);
                        foreach ($results as $item)
                        {
                            $update_query='UPDATE '.$table_name.' SET meta_key=\''.$this->new_prefix.$item['meta_key'].'\' WHERE meta_key=\''.$item['meta_key'].'\';';
                            if($this->execute_sql($update_query,$sub_task)===false)
                            {
                                $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                            }
                        }
                    }

                    $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'capabilities" WHERE meta_key="' . $this->old_base_prefix . 'capabilities";';
                    if ($this->execute_sql($update_query, $sub_task) === false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                    }

                    $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'user_level" WHERE meta_key="' . $this->old_base_prefix . 'user_level";';
                    if ($this->execute_sql($update_query, $sub_task) === false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                    }

                    $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'user-settings" WHERE meta_key="' . $this->old_base_prefix . 'user-settings";';
                    if ($this->execute_sql($update_query, $sub_task) === false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                    }

                    $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'user-settings-time" WHERE meta_key="' . $this->old_base_prefix . 'user-settings-time";';
                    if ($this->execute_sql($update_query, $sub_task) === false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                    }

                    $update_query = 'UPDATE ' . $table_name . ' SET meta_key="' . $this->new_prefix . 'dashboard_quick_press_last_post_id" WHERE meta_key="' . $this->old_base_prefix . 'dashboard_quick_press_last_post_id";';
                    if ($this->execute_sql($update_query, $sub_task) === false)
                    {
                        $this->log->WriteLog($this->db_method->get_last_error(), 'notice');
                    }
                }

                $ret['result']='success';
                $ret['replace_row']=0;
                $ret['current_replace_table_finish']=true;
                return $ret;
            }
        }

        if($this->old_site_url==$this->new_site_url)
        {
            $ret['result']='success';
            $ret['replace_row']=0;
            $ret['current_replace_table_finish']=true;
            return $ret;
        }

        if($this->is_mu)
        {
            if(substr($table_name, strlen($this->temp_new_prefix))=='blogs')
            {
                $this->log->WriteLog('Update mu blogs table', 'notice');

                if((preg_match('#^https?://([^/]+)#i', $this->new_home_url, $matches) || preg_match('#^https?://([^/]+)#i', $this->new_site_url, $matches)) && (preg_match('#^https?://([^/]+)#i', $this->old_home_url, $old_matches) || preg_match('#^https?://([^/]+)#i', $this->old_site_url, $old_matches)))
                {
                    $new_string = strtolower($matches[1]);
                    $old_string = strtolower($old_matches[1]);
                    $new_path='';
                    $old_path='';

                    if(defined( 'PATH_CURRENT_SITE' ))
                    {
                        $new_path=PATH_CURRENT_SITE;
                    }

                    $query = 'SELECT * FROM `'.$table_name.'`';
                    $result=$this->db_method->query($query,ARRAY_A);
                    if($result && sizeof($result)>0)
                    {
                        $rows = $result;
                        foreach ($rows as $row)
                        {
                            $update=array();
                            $where=array();

                            if($row['blog_id']==1)
                            {
                                $old_path=$row['path'];
                            }

                            $old_domain_data = $row['domain'];
                            $new_domain_data=str_replace($old_string,$new_string,$old_domain_data);

                            $temp_where='`blog_id` = "' . $row['blog_id'] . '"';
                            if (is_callable(array($wpdb, 'remove_placeholder_escape')))
                                $temp_where = $wpdb->remove_placeholder_escape($temp_where);
                            $where[] = $temp_where;
                            $update[] = '`domain` = "' . $new_domain_data . '"';

                            if(!empty($old_path)&&!empty($new_path))
                            {
                                $old_path_data= $row['path'];
                                $new_path_data=$this->str_replace_first($old_path,$new_path,$old_path_data);
                                $update[] = '`path` = "' . $new_path_data . '"';
                            }

                            if(!empty($update)&&!empty($where))
                            {
                                $update_query = 'UPDATE `'.$table_name.'` SET '.implode(', ', $update).' WHERE '.implode(' AND ', array_filter($where)).';';
                                if($this->execute_sql($update_query,$sub_task)===false)
                                {
                                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                                }
                            }
                        }
                    }
                }
            }
        }


        $skip_table=false;
        if(apply_filters('wpvivid_restore_db_skip_replace_tables',$skip_table,$table_name))
        {
            $this->log->WriteLog('Skipping table '.$table_name, 'Warning');
            $ret['result']='success';
            $ret['replace_row']=0;
            $ret['current_replace_table_finish']=true;
            return $ret;
        }

        $query = 'SELECT COUNT(*) FROM `'.$table_name.'`';

        $current_row=0;

        $result=$this->db_method->query($query,ARRAY_N);
        if($result && sizeof($result)>0)
        {
            $count=$result[0][0];
            $this->log->WriteLog('Counting of rows in '.$table_name.': '.$count, 'notice');
            if($count==0)
            {
                $ret['result']='success';
                $ret['replace_row']=0;
                $ret['current_replace_table_finish']=true;
                return $ret;
            }

            $query='DESCRIBE `'.$table_name.'`';
            $result=$this->db_method->query($query,ARRAY_A);
            $columns=array();
            foreach ($result as $data)
            {
                $column['Field']=$data['Field'];
                if($data['Key']=='PRI')
                    $column['PRI']=1;
                else
                    $column['PRI']=0;

                if($data['Type']=='mediumblob')
                {
                    $column['skip']=1;
                }
                $columns[]=$column;
            }

            $page=min(5000,$max_replace_row);

            $update_query='';

            $start_row=$row;
            $replace_row=0;

            for ($current_row = $start_row; $current_row < $count; $current_row += $page)
            {
                $this->log->WriteLog('Replacing the row in '.$current_row. ' line.', 'notice');
                $sub_task['last_msg']='Importing sql file:'.$sql_file_name.' table: '.$table_name.' Replaced row: '.$current_row.'/'.$count.'; '.size_format($sub_task['exec_sql']['sql_files'][$sql_file_name]['sql_offset'],2).'/'.size_format($this->sum,2);

                $this->update_sub_task($sub_task);
                $query = 'SELECT * FROM `'.$table_name.'` LIMIT '.$current_row.', '.$page;
                $replace_row+=$page;
                $result=$this->db_method->query($query,ARRAY_A);
                if($result && sizeof($result)>0)
                {
                    $rows = $result;
                    foreach ($rows as $row)
                    {
                        $update=array();
                        $where=array();
                        foreach ($columns as $column)
                        {
                            if(isset($column['skip']))
                            {
                                //$this->log->WriteLog('Skipping mediumblob type data', 'notice');
                                continue;
                            }

                            $old_data = $row[$column['Field']];
                            if($column['PRI']==1)
                            {
                                $wpdb->escape_by_ref($old_data);
                                $temp_where='`'.$column['Field'].'` = "' . $old_data . '"';
                                if (is_callable(array($wpdb, 'remove_placeholder_escape')))
                                    $temp_where = $wpdb->remove_placeholder_escape($temp_where);
                                $where[] = $temp_where;
                            }

                            $skip_row=false;
                            if(apply_filters('wpvivid_restore_db_skip_replace_rows',$skip_row,$table_name,$column['Field']))
                            {
                                continue;
                            }
                            $new_data=$this->replace_row_data($old_data);
                            if($new_data==$old_data)
                                continue;

                            $wpdb->escape_by_ref($new_data);
                            if (is_callable(array($wpdb, 'remove_placeholder_escape')))
                                $new_data = $wpdb->remove_placeholder_escape($new_data);
                            $update[] = '`'.$column['Field'].'` = "' . $new_data . '"';
                        }

                        if(!empty($update)&&!empty($where))
                        {
                            $temp_query = 'UPDATE `'.$table_name.'` SET '.implode(', ', $update).' WHERE '.implode(' AND ', array_filter($where)).';';
                            $type=$this->db_method->get_type();

                            if($type=='pdo_mysql')
                            {
                                if($update_query=='')
                                {
                                    $update_query=$temp_query;
                                    if(strlen($update_query)>$this->db_method->get_max_allow_packet())
                                    {
                                        if($this->execute_sql($update_query,$sub_task)===false)
                                        {
                                            $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                                        }

                                        $update_query='';
                                    }
                                }
                                else if(strlen($temp_query)+strlen($update_query)>$this->db_method->get_max_allow_packet())
                                {
                                    if($this->execute_sql($update_query,$sub_task)===false)
                                    {
                                        $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                                    }
                                    $update_query=$temp_query;
                                }
                                else
                                {
                                    $update_query.=$temp_query;
                                }
                            }
                            else
                            {
                                $update_query=$temp_query;
                                if($this->execute_sql($update_query,$sub_task)===false)
                                {
                                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                                }
                                $update_query='';
                            }

                        }
                        //return;
                    }
                }

                if($replace_row>$max_replace_row)
                {
                    $current_row+= $page;
                    break;
                }
            }

            if(!empty($update_query))
            {
                if($this->execute_sql($update_query,$sub_task)===false)
                {
                    $this->log->WriteLog($this->db_method->get_last_error(),'notice');
                }
            }

            if($current_row >= $count)
            {
                $replace_current_table_finish=true;
            }
        }
        else
        {
            $replace_current_table_finish=true;
            $current_row=0;
        }

        $this->log->WriteLog('Replacing row completed. Current row:'.$current_row, 'notice');

        $ret['result']='success';
        $ret['replace_row']=$current_row;
        $ret['max_replace_row']=$max_replace_row;
        $ret['current_replace_table_finish']=$replace_current_table_finish;
        $this->log->WriteLog(wp_json_encode($ret), 'notice');
        return $ret;
    }

    private function replace_row_data($old_data)
    {
        try{
            $unserialize_data = @unserialize($old_data);
            if($unserialize_data===false)
            {
                $old_data=$this->replace_string_v2($old_data);
            }
            else
            {
                $old_data=$this->replace_serialize_data($unserialize_data);
                $old_data=serialize($old_data);
            }
        }
        catch (Error $error)
        {
            $old_data=$this->replace_string_v2($old_data);
        }

        return $old_data;
    }

    private function replace_serialize_data($data)
    {
        if(is_string($data))
        {
            $serialize_data =@unserialize($data);
            if($serialize_data===false)
            {
                $data=$this->replace_string_v2($data);
            }
            else
            {
                $data=serialize($this->replace_serialize_data($serialize_data));
            }
        }
        else if(is_array($data))
        {
            foreach ($data as $key => $value)
            {
                if(is_string($value))
                {
                    $data[$key]=$this->replace_string_v2($value);
                }
                else if(is_array($value))
                {
                    $data[$key]=$this->replace_serialize_data($value);
                }
                else if(is_object($value))
                {
                    if (is_a($value, '__PHP_Incomplete_Class'))
                    {
                        //
                    }
                    else
                    {
                        $data[$key]=$this->replace_serialize_data($value);
                    }
                }
            }
        }
        else if(is_object($data))
        {
            $temp = $data; // new $data_class();
            if (is_a($data, '__PHP_Incomplete_Class'))
            {

            }
            else
            {
                $props = get_object_vars($data);
                foreach ($props as $key => $value)
                {
                    if (strpos($key, "\0")===0)
                        continue;
                    if(is_string($value))
                    {
                        $temp->$key =$this->replace_string_v2($value);
                    }
                    else if(is_array($value))
                    {
                        $temp->$key=$this->replace_serialize_data($value);
                    }
                    else if(is_object($value))
                    {
                        $temp->$key=$this->replace_serialize_data($value);
                    }
                }
            }
            $data = $temp;
            unset($temp);
        }

        return $data;
    }

    private function get_remove_http_link($url)
    {
        if (0 === stripos($url, 'https://'))
        {
            $mix_link = '//'.substr($url, 8);
        } elseif (0 === stripos($url, 'http://')) {
            $mix_link = '//'.substr($url, 7);
        }
        else
        {
            $mix_link=false;
        }
        return $mix_link;
    }

    private function get_remove_http_link_ex($url)
    {
        if (0 === stripos($url, 'https://'))
        {
            $mix_link = '\/\/'.substr($url, 8);
        } elseif (0 === stripos($url, 'http://')) {
            $mix_link = '\/\/'.substr($url, 7);
        }
        else
        {
            $mix_link=false;
        }
        return $mix_link;
    }

    private function get_http_link_at_quote($url)
    {
        return str_replace('/','\/',$url);
    }

    public function replace_string_v2($old_string)
    {
        if(!is_string($old_string))
        {
            return $old_string;
        }

        $from=array();
        $to=array();

        $new_url_use_https=false;
        if (0 === stripos($this->new_site_url, 'https://')|| stripos($this->new_site_url, 'https:\/\/'))
        {
            $new_url_use_https=true;
        }
        else if (0 === stripos($this->new_site_url, 'http://')|| stripos($this->new_site_url, 'http:\/\/'))
        {
            $new_url_use_https=false;
        }

        if(isset($this->old_mu_single_site_upload_url) && !empty($this->old_mu_single_site_upload_url))
        {
            $upload_dir = wp_upload_dir();
            $tmp_upload_url=untrailingslashit($upload_dir['baseurl']);

            $from[]=$this->old_mu_single_site_upload_url;
            $to[]=$tmp_upload_url;

            if(isset($this->old_mu_single_home_upload_url) && !empty($this->old_mu_single_home_upload_url))
            {
                if($this->old_mu_single_site_upload_url !== $this->old_mu_single_home_upload_url)
                {
                    $from[]=$this->old_mu_single_home_upload_url;
                    $to[]=$tmp_upload_url;
                }
            }
        }

        if($this->old_site_url!=$this->new_site_url)
        {
            if(substr($this->replacing_table, strlen($this->new_prefix))=='posts'||substr($this->replacing_table, strlen($this->new_prefix))=='postmeta'||substr($this->replacing_table, strlen($this->new_prefix))=='options')
            {
                $remove_http_link=$this->get_remove_http_link($this->old_site_url);
                if($remove_http_link!==false)
                {
                    $new_remove_http_link=$this->get_remove_http_link($this->new_site_url);
                    $from[]=$remove_http_link;
                    $to[]=$new_remove_http_link;

                    if($new_url_use_https)
                    {
                        $from[]='http:'.$new_remove_http_link;
                        $to[]='https:'.$new_remove_http_link;
                    }
                    else
                    {
                        $from[]='https:'.$new_remove_http_link;
                        $to[]='http:'.$new_remove_http_link;
                    }

                    $quote_old_site_url=$this->get_http_link_at_quote($remove_http_link);
                    $quote_new_site_url=$this->get_http_link_at_quote($new_remove_http_link);
                    $from[]=$quote_old_site_url;
                    $to[]=$quote_new_site_url;
                    if($new_url_use_https)
                    {
                        $from[]='http:'.$quote_new_site_url;
                        $to[]='https:'.$quote_new_site_url;
                    }
                    else
                    {
                        $from[]='https:'.$quote_new_site_url;
                        $to[]='http:'.$quote_new_site_url;
                    }
                }
                else
                {
                    $remove_http_link=$this->get_remove_http_link_ex($this->old_site_url);
                    if($remove_http_link!==false)
                    {
                        $new_remove_http_link=$this->get_remove_http_link_ex($this->new_site_url);
                        $from[]=$remove_http_link;
                        $to[]=$new_remove_http_link;

                        if($new_url_use_https)
                        {
                            $from[]='http:'.$new_remove_http_link;
                            $to[]='https:'.$new_remove_http_link;
                        }
                        else
                        {
                            $from[]='https:'.$new_remove_http_link;
                            $to[]='http:'.$new_remove_http_link;
                        }
                    }
                }

                $tmp_old_site_url = str_replace(':', '%3A', $this->old_site_url);
                $tmp_old_site_url = str_replace('/', '%2F', $tmp_old_site_url);

                $tmp_new_site_url = str_replace(':', '%3A', $this->new_site_url);
                $tmp_new_site_url = str_replace('/', '%2F', $tmp_new_site_url);

                $from[]=$tmp_old_site_url;
                $to[]=$tmp_new_site_url;
            }
            else
            {
                $from[]=$this->old_site_url;
                $to[]=$this->new_site_url;

                $from[]=str_replace('/', '\/', $this->old_site_url);
                $to[]=str_replace('/', '\/', $this->new_site_url);

                $tmp_old_site_url = str_replace(':', '%3A', $this->old_site_url);
                $tmp_old_site_url = str_replace('/', '%2F', $tmp_old_site_url);

                $tmp_new_site_url = str_replace(':', '%3A', $this->new_site_url);
                $tmp_new_site_url = str_replace('/', '%2F', $tmp_new_site_url);

                $from[]=$tmp_old_site_url;
                $to[]=$tmp_new_site_url;
            }
        }


        if($this->old_home_url!=$this->old_site_url&&$this->old_home_url!=$this->new_home_url)
        {
            if(substr($this->replacing_table, strlen($this->new_prefix))=='posts'||substr($this->replacing_table, strlen($this->new_prefix))=='postmeta'||substr($this->replacing_table, strlen($this->new_prefix))=='options')
            {
                $remove_http_link=$this->get_remove_http_link($this->old_home_url);
                if($remove_http_link!==false)
                {
                    $new_remove_http_link=$this->get_remove_http_link($this->new_home_url);
                    $from[]=$remove_http_link;
                    $to[]=$new_remove_http_link;

                    if($new_url_use_https)
                    {
                        $from[]='http:'.$new_remove_http_link;
                        $to[]='https:'.$new_remove_http_link;
                    }
                    else
                    {
                        $from[]='https:'.$new_remove_http_link;
                        $to[]='http:'.$new_remove_http_link;
                    }

                    $quote_old_site_url=$this->get_http_link_at_quote($remove_http_link);
                    $quote_new_site_url=$this->get_http_link_at_quote($new_remove_http_link);
                    $from[]=$quote_old_site_url;
                    $to[]=$quote_new_site_url;
                    if($new_url_use_https)
                    {
                        $from[]='http:'.$quote_new_site_url;
                        $to[]='https:'.$quote_new_site_url;
                    }
                    else
                    {
                        $from[]='https:'.$quote_new_site_url;
                        $to[]='http:'.$quote_new_site_url;
                    }
                }
                else
                {
                    $remove_http_link=$this->get_remove_http_link_ex($this->old_home_url);
                    if($remove_http_link!==false)
                    {
                        $new_remove_http_link=$this->get_remove_http_link_ex($this->new_home_url);
                        $from[]=$remove_http_link;
                        $to[]=$new_remove_http_link;

                        if($new_url_use_https)
                        {
                            $from[]='http:'.$new_remove_http_link;
                            $to[]='https:'.$new_remove_http_link;
                        }
                        else
                        {
                            $from[]='https:'.$new_remove_http_link;
                            $to[]='http:'.$new_remove_http_link;
                        }
                    }
                }
            }
            else
            {
                $from[]=$this->old_home_url;
                $to[]=$this->new_home_url;
            }
        }


        if(!empty($from)&&!empty($to))
        {
            $old_string=str_replace($from,$to,$old_string);
        }

        return $old_string;
    }

    public function skip_tables($skip_table,$table_name)
    {
        $skip_tables[]='adrotate_stats';
        $skip_tables[]='login_security_solution_fail';
        $skip_tables[]='icl_strings';
        $skip_tables[]='icl_string_positions';
        $skip_tables[]='icl_string_translations';
        $skip_tables[]='icl_languages_translations';
        $skip_tables[]='slim_stats';
        $skip_tables[]='slim_stats_archive';
        $skip_tables[]='es_online';
        $skip_tables[]='ahm_download_stats';
        $skip_tables[]='woocommerce_order_items';
        $skip_tables[]='woocommerce_sessions';
        $skip_tables[]='redirection_404';
        $skip_tables[]='redirection_logs';
        $skip_tables[]='wbz404_logs';
        $skip_tables[]='wbz404_redirects';
        $skip_tables[]='Counterize';
        $skip_tables[]='Counterize_UserAgents';
        $skip_tables[]='Counterize_Referers';
        $skip_tables[]='et_bloom_stats';
        $skip_tables[]='term_relationships';
        $skip_tables[]='lbakut_activity_log';
        $skip_tables[]='simple_feed_stats';
        $skip_tables[]='svisitor_stat';
        $skip_tables[]='itsec_log';
        $skip_tables[]='relevanssi_log';
        $skip_tables[]='wysija_email_user_stat';
        $skip_tables[]='wponlinebackup_generations';
        $skip_tables[]='blc_instances';
        $skip_tables[]='wp_rp_tags';
        $skip_tables[]='statpress';
        $skip_tables[]='wfHits';
        $skip_tables[]='wp_wfFileMods';
        $skip_tables[]='tts_trafficstats';
        $skip_tables[]='tts_referrer_stats';
        $skip_tables[]='dmsguestbook';
        $skip_tables[]='relevanssi';
        $skip_tables[]='wfFileMods';
        $skip_tables[]='learnpress_sessions';
        $skip_tables[]='icl_string_pages';
        $skip_tables[]='webarx_event_log';
        $skip_tables[]='duplicator_packages';
        $skip_tables[]='wsal_metadata';
        $skip_tables[]='wsal_occurrences';
        $skip_tables[]='simple_history_contexts';
        $skip_tables[]='simple_history';
        $skip_tables[]='wffilemods';
        $skip_tables[]='statpress';
        //
        if(in_array(substr($table_name, strlen($this->temp_new_prefix)),$skip_tables))
        {
            $skip_table=true;
        }
        else
        {
            $skip_table=false;
        }

        return $skip_table;
    }

    public function skip_rows($skip_rows,$table_name,$column_name)
    {
        $row['table_name']='posts';
        $row['column_name']='guid';
        $rows[]=$row;

        foreach ($rows as $row)
        {
            if($column_name==$row['column_name']&&$table_name==$this->temp_new_prefix.$row['table_name'])
            {
                $skip_rows=true;
                break;
            }
        }

        return $skip_rows;
    }

    public function skip_create_tables($skip_table,$table_name,$option)
    {
        if(isset($option['exclude_tables']))
        {
            $table_name=$this->old_prefix.substr($table_name,strlen($this->temp_new_prefix));
            if(array_key_exists($table_name,$option['exclude_tables']))
            {
                $skip_table=true;
            }
        }
        return $skip_table;
    }

    public function check_max_allow_packet_ex()
    {
        $max_all_packet_warning=false;
        include_once WPVIVID_PLUGIN_DIR . '/includes/new_backup/class-wpvivid-restore-db-method-2.php';
        $this->db_method=new WPvivid_Restore_DB_Method_2();

        $this->db_method->set_skip_query(0);

        $ret=$this->db_method->connect_db();
        if($ret['result']==WPVIVID_SUCCESS)
        {
            $max_allowed_packet = $this->db_method->query("SELECT @@session.max_allowed_packet;",ARRAY_N);
            if($max_allowed_packet)
            {
                if(is_array($max_allowed_packet)&&isset($max_allowed_packet[0])&&isset($max_allowed_packet[0][0]))
                {
                    if($max_allowed_packet[0][0]<16777216){
                        $max_all_packet_warning = 'max_allowed_packet = '.size_format($max_allowed_packet[0][0]).' is too small. The recommended value is 16M or higher. Too small value could lead to a failure when importing a larger database.';
                    }
                }
            }
        }
        return $max_all_packet_warning;
    }

    private function execute_sql($query,$sub_task)
    {
        $sub_task['exec_sql']['last_query']=$query;
        //$this->update_sub_task($sub_task);
        return $this->db_method->execute_sql($query);
    }

    public function finish_restore_db($sql_files,$local_path,$sub_task)
    {
        $this->init_db($sub_task);

        $option_table = $this->temp_new_prefix.'options';

        global $wpdb;

        $db_siteurl = false;
        $siteurl_sql = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $option_table WHERE option_name = %s", 'siteurl' ) );
        foreach ( $siteurl_sql as $siteurl )
        {
            $db_siteurl = untrailingslashit($siteurl->option_value);
        }
        if($db_siteurl !== false)
        {
            $update_query ='UPDATE '.$option_table.' SET option_value="'.$this->new_site_url.'" WHERE option_name="siteurl";';
            $this->log->WriteLog($update_query, 'notice');
            $this->log->WriteLog('update query len:'.strlen($update_query), 'notice');
            $this->execute_sql($update_query,$sub_task);
        }
        else
        {
            $insert_query = $wpdb->prepare("INSERT INTO {$option_table} (option_name,option_value) VALUES ('siteurl',%s)", $this->new_site_url);
            $this->log->WriteLog('siteurl not found, insert: '.$insert_query, 'notice');
            if ($wpdb->get_results($insert_query) === false) {
                $error = $wpdb->last_error;
                $this->log->WriteLog('insert siteurl failed: '.$error, 'notice');
            }
            else
            {
                $this->log->WriteLog('insert siteurl success', 'notice');
            }
        }


        $db_home = false;
        $home_sql = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $option_table WHERE option_name = %s", 'home' ) );
        foreach ( $home_sql as $home )
        {
            $db_home = untrailingslashit($home->option_value);
        }
        if($db_home !== false)
        {
            $update_query ='UPDATE '.$option_table.' SET option_value="'.$this->new_home_url.'" WHERE option_name="home";';
            $this->log->WriteLog($update_query, 'notice');
            $this->log->WriteLog('update query len:'.strlen($update_query), 'notice');
            $this->execute_sql($update_query,$sub_task);
        }
        else
        {
            $insert_query = $wpdb->prepare("INSERT INTO {$option_table} (option_name,option_value) VALUES ('home',%s)", $this->new_home_url);
            $this->log->WriteLog('home not found, insert: '.$insert_query, 'notice');
            if ($wpdb->get_results($insert_query) === false) {
                $error = $wpdb->last_error;
                $this->log->WriteLog('insert home failed: '.$error, 'notice');
            }
            else
            {
                $this->log->WriteLog('insert home success', 'notice');
            }
        }

        $sub_task['finished']=1;

        foreach ($sql_files as $sql_file_name=>$sql_file)
        {
            $tmp_sql_file=$local_path.$sql_file_name;
            if(file_exists($tmp_sql_file))
            {
                @wp_delete_file($tmp_sql_file);
            }
        }

        $ret['result']='success';
        $ret['sub_task']=$sub_task;
        return $ret;
    }

    public function rename_db($sub_task)
    {
        global $wpdb;

        //restore_db_reset
        $wpdb->query('SET FOREIGN_KEY_CHECKS=0;');

        $restore_task=get_option('wpvivid_restore_task',array());
        $restore_detail_options=$restore_task['restore_detail_options'];
        $restore_db_reset = $restore_detail_options['restore_db_reset'];

        $this->log->WriteLog('Restore db success,now start rename temp table name prefix to new site prefix.','notice');

        $temp_new_prefix='tmp'.$sub_task['exec_sql']['db_id'].'_';

        $tables = $wpdb->get_results('SHOW TABLE STATUS');
        $new_tables = array();
        if (is_array($tables))
        {
            foreach ($tables as $table)
            {
                if (0 !== stripos($table->Name, $temp_new_prefix))
                {
                    continue;
                }
                if (empty($table->Engine))
                {
                    continue;
                }
                $new_tables[] = $table->Name;
            }
        }
        else
        {
            $ret['result']='failed';
            $ret['error']='Getting temp tables failed.';
            return $ret;
        }

        if($restore_db_reset)
        {
            foreach ($tables as $table)
            {
                if (0 !== stripos($table->Name, $wpdb->prefix))
                {
                    continue;
                }
                if (empty($table->Engine))
                {
                    continue;
                }

                $wpdb->query('DROP TABLE IF EXISTS ' . $table->Name);
                $this->log->WriteLog('DROP TABLE IF EXISTS ' . $table->Name,'notice');
            }
        }
        else
        {
            foreach ($new_tables as $table)
            {
                $new_table=$this->str_replace_first($temp_new_prefix,$wpdb->prefix,$table);

                if($wpdb->query('DROP TABLE IF EXISTS ' . $new_table)===false)
                {
                    $error='Failed to drop table. Error:'.$wpdb->last_error;
                    $this->log->WriteLog($error,'error');
                    $ret['result']='failed';
                    $ret['error']=$error;
                    return $ret;
                }
                else
                {
                    $this->log->WriteLog('DROP TABLE IF EXISTS ' . $new_table,'notice');
                }
            }
        }

        foreach ($new_tables as $table)
        {
            $new_table=$this->str_replace_first($temp_new_prefix,$wpdb->prefix,$table);

            if($wpdb->query("RENAME TABLE {$table} TO {$new_table}")===false)
            {
                $error='Failed to rename table. Error:'.$wpdb->last_error;
                $this->log->WriteLog($error,'error');
                $ret['result']='failed';
                $ret['error']=$error;
                return $ret;
            }
            else
            {
                $this->log->WriteLog("RENAME TABLE {$table} TO {$new_table}",'notice');
            }
        }


        wp_cache_flush();
        update_option('wpvivid_restore_task',$restore_task);

        $this->log->WriteLog('Replacing table prefix succeeded.','notice');

        //$ret=$this->test_access();
        $ret['result']='success';
        return $ret;
    }

    public function test_access()
    {
        $url=get_home_url();
        $options=array();
        $options['timeout']=15;
        $request=wp_remote_request($url,$options);
        if(!is_wp_error($request) && ($request['response']['code'] == 200))
        {
            $ret['result']='success';
        }
        else
        {
            $ret['result']='failed';
            if ( is_wp_error( $request ) )
            {
                $error_message = $request->get_error_message();
                $ret['error']="Sorry, something went wrong: $error_message. Please try again later or contact us.";
            }
            else if($request['response']['code'] != 200)
            {
                $ret['error']=$request['response']['message'];
            }
            else {
                $ret['error']=$request;
            }
        }

        return $ret;
    }

    public function is_og_table($table_name)
    {
        $table_prefix=substr($table_name,0,strlen($this->old_prefix));

        if($table_prefix==$this->old_prefix)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function is_mu_single_og_table($table_name)
    {
        $table_prefix=substr($table_name,0,strlen($this->old_prefix));

        if($table_prefix==$this->old_prefix)
        {
            return true;
        }
        else
        {
            $table_prefix=substr($table_name,0,strlen($this->old_base_prefix));
            if($table_prefix==$this->old_base_prefix)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
}