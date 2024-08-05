<?php

if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

class WPvivid_Snapshot_Function_Ex
{
    public $options;

    public function __construct()
    {
        $this->options=new WPvivid_Snapshot_Option_Ex();
    }

    public function check_manual_snapshot()
    {
        $snapshots=$this->options->get_option('wpvivid_snapshot');
        $manual_snapshots=array();
        if($snapshots !== false)
        {
            foreach ($snapshots as $snapshot)
            {
                if($snapshot['type']=='manual')
                {
                    $manual_snapshots[]=$snapshot;
                }
            }
        }
        $setting=$this->options->get_option('wpvivid_snapshot_setting');
        $count=isset($setting['snapshot_retention'])?$setting['snapshot_retention']:6;
        if(empty($count))
        {
            $count=6;
        }

        usort($manual_snapshots, function ($a, $b)
        {
            if ($a['time'] == $b['time'])
                return 0;

            if ($a['time'] > $b['time'])
                return 1;
            else
                return -1;
        });

        while(count($manual_snapshots)>=$count)
        {
            $manual_snapshot=array_shift($manual_snapshots);
            $this->remove_snapshot($manual_snapshot['id']);
        }
    }

    public function create_merge_snapshot($comment='')
    {
        global $wpdb;

        $snapshot_id = 'wp'.$this->create_snapshot_uid();
        $tables = $wpdb->get_results('SHOW TABLE STATUS');
        $exclude_tables[]=$wpdb->prefix.'wpvivid_log';
        $exclude_tables[]=$wpdb->base_prefix."wpvivid_increment_big_ids";
        $exclude_tables[]=$wpdb->prefix.'wpvivid_merge_db';
        $exclude_tables[]=$wpdb->prefix.'wpvivid_options';
        $exclude_tables[]=$wpdb->base_prefix."wpvivid_record_task";
        $exclude_tables=apply_filters('wpvivid_create_snapshot_exclude_tables',$exclude_tables);

        $snapshot_tables=array();

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

            if(in_array($table->Name,$exclude_tables))
            {
                continue;
            }

            $snapshot_table['Name']=$table->Name;
            $snapshot_table['finished']=0;
            $snapshot_tables[$table->Name]=$snapshot_table;
        }

        if (!empty($snapshot_tables))
        {
            foreach ($snapshot_tables as $table_name=>$snapshot_table)
            {
                $new_table=$this->str_replace_first($wpdb->prefix,$snapshot_id,$table_name);

                $wpdb->query("OPTIMIZE TABLE {$table_name}");
                $wpdb->query("CREATE TABLE `{$new_table}` LIKE `{$table_name}`");
                $wpdb->query("INSERT `{$new_table}` SELECT * FROM `{$table_name}`");
            }
        }
        else
        {
            $ret['result']='failed';
            $ret['error']='create snapshot failed';
            return $ret;
        }

        $this->update_snapshot($snapshot_id,'merge',$comment);
        $ret['result']='success';
        $ret['finished']=1;
        $ret['snapshot_id']=$snapshot_id;

        return $ret;
    }

    public function check_dev_snapshot()
    {
        $snapshots=$this->options->get_option('wpvivid_snapshot');
        $dev_snapshots=array();
        foreach ($snapshots as $snapshot)
        {
            if($snapshot['type']=='dev')
            {
                $dev_snapshots[]=$snapshot;
            }
        }

        $setting=$this->options->get_option('wpvivid_merge_setting');
        $count=isset($setting['snapshot_retention'])?$setting['snapshot_retention']:6;
        if(empty($count))
        {
            $count=6;
        }

        while(count($dev_snapshots)>=$count)
        {
            usort($dev_snapshots, function ($a, $b)
            {
                if ($a['time'] == $b['time'])
                    return 0;

                if ($a['time'] > $b['time'])
                    return 1;
                else
                    return -1;
            });

            $dev_snapshot=array_shift($dev_snapshots);
            $this->remove_snapshot($dev_snapshot['id']);
        }
    }

    public function create_dev_snapshot($task_data)
    {
        global $wpdb;

        $snapshot_id = 'wp'.$this->create_snapshot_uid();
        $tables = $wpdb->get_results('SHOW TABLE STATUS');
        //$exclude_tables[]=$wpdb->prefix.'wpvivid_log';
        //$exclude_tables[]=$wpdb->prefix.'wpvivid_merge_db';
        $exclude_tables[]=$wpdb->prefix.'wpvivid_options';
        $exclude_tables[]=$wpdb->base_prefix."wpvivid_record_task";
        $exclude_tables=apply_filters('wpvivid_create_snapshot_exclude_tables',$exclude_tables);

        $start_time=time();
        $snapshot_tables=array();

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

            if(in_array($table->Name,$exclude_tables))
            {
                continue;
            }

            $snapshot_table['Name']=$table->Name;
            $snapshot_table['finished']=0;
            $snapshot_tables[$table->Name]=$snapshot_table;
        }

        $this->init_task($snapshot_id,$snapshot_tables,'dev',$task_data['comment']);

        if (!empty($snapshot_tables))
        {
            foreach ($snapshot_tables as $table_name=>$snapshot_table)
            {
                if($snapshot_table['finished']==1)
                    continue;

                $new_table=$this->str_replace_first($wpdb->prefix,$snapshot_id,$table_name);

                $wpdb->query("OPTIMIZE TABLE {$table_name}");
                $wpdb->query("CREATE TABLE `{$new_table}` LIKE `{$table_name}`");
                $wpdb->query("INSERT `{$new_table}` SELECT * FROM `{$table_name}`");

                $snapshot_tables[$table_name]['finished']=1;
                $this->update_task($snapshot_id,$snapshot_tables);

                if($this->is_time_limit_exceeded($start_time))
                {
                    $ret['result']='success';
                    $ret['finished']=0;
                    $ret['snapshot_id']=$snapshot_id;
                    $ret['snapshot_tables']=$snapshot_tables;
                    return $ret;
                }
            }
        }
        else
        {
            $ret['result']='failed';
            $ret['error']='create snapshot failed';
            return $ret;
        }

        $this->update_snapshot($snapshot_id,'dev',$task_data['comment']);
        $ret['result']='success';
        $ret['finished']=1;
        $ret['snapshot_id']=$snapshot_id;

        return $ret;
    }

    public function create_snapshot($type='',$comment='')
    {
        global $wpdb;

        $snapshot_id=$this->create_snapshot_uid();
        $snapshot_id = 'wp'.$snapshot_id;

        $tables = $wpdb->get_results('SHOW TABLE STATUS');

        $exclude_tables[]=$wpdb->prefix.'wpvivid_log';
        $exclude_tables[]=$wpdb->base_prefix."wpvivid_increment_big_ids";
        $exclude_tables[]=$wpdb->prefix.'wpvivid_merge_db';
        $exclude_tables[]=$wpdb->prefix.'wpvivid_options';
        $exclude_tables[]=$wpdb->base_prefix."wpvivid_record_task";
        $exclude_tables=apply_filters('wpvivid_create_snapshot_exclude_tables',$exclude_tables);

        $start_time=time();
        $snapshot_tables=array();

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

            if(in_array($table->Name,$exclude_tables))
            {
                continue;
            }

            $snapshot_table['Name']=$table->Name;
            $snapshot_table['finished']=0;
            $snapshot_tables[$table->Name]=$snapshot_table;
        }

        if(empty($type))
        {
            $type='manual';
        }

        $this->init_task($snapshot_id,$snapshot_tables,$type,$comment);

        if (!empty($snapshot_tables))
        {
            foreach ($snapshot_tables as $table_name=>$snapshot_table)
            {
                if($snapshot_table['finished']==1)
                    continue;

                $new_table=$this->str_replace_first($wpdb->prefix,$snapshot_id,$table_name);

                $wpdb->query("OPTIMIZE TABLE {$table_name}");
                $wpdb->query("DROP TABLE IF EXISTS `{$new_table}`");
                $wpdb->query("CREATE TABLE `{$new_table}` LIKE `{$table_name}`");
                $wpdb->query("INSERT `{$new_table}` SELECT * FROM `{$table_name}`");

                $snapshot_tables[$table_name]['finished']=1;
                $this->update_task($snapshot_id,$snapshot_tables);

                if($this->is_time_limit_exceeded($start_time))
                {
                    $ret['result']='success';
                    $ret['finished']=0;
                    $ret['snapshot_id']=$snapshot_id;
                    $ret['snapshot_tables']=$snapshot_tables;
                    return $ret;
                }
            }
        }
        else
        {
            $ret['result']='failed';
            $ret['error']='Creating the snapshot failed.';
            return $ret;
        }

        $this->update_task($snapshot_id,$snapshot_tables);
        $this->update_snapshot($snapshot_id,$type,$comment);
        $ret['result']='success';
        $ret['finished']=1;
        $ret['snapshot_id']=$snapshot_id;
        return $ret;
    }

    public function resume_create_snapshot()
    {
        global $wpdb;

        $start_time=time();

        $ret=$this->get_task_data();
        if($ret['result']=='failed')
        {
            return $ret;
        }

        $snapshot_id=$ret['snapshot_id'];
        $snapshot_tables=$ret['snapshot_tables'];
        $type=$ret['type'];
        $comment=$ret['comment'];

        if (!empty($snapshot_tables))
        {
            foreach ($snapshot_tables as $table_name=>$snapshot_table)
            {
                if($snapshot_table['finished']==1)
                    continue;

                $new_table=$this->str_replace_first($wpdb->prefix,$snapshot_id,$table_name);

                $wpdb->query("OPTIMIZE TABLE {$table_name}");
                $wpdb->query("DROP TABLE IF EXISTS `{$new_table}`");
                $wpdb->query("CREATE TABLE `{$new_table}` LIKE `{$table_name}`");
                $wpdb->query("INSERT `{$new_table}` SELECT * FROM `{$table_name}`");

                $snapshot_tables[$table_name]['finished']=1;
                $this->update_task($snapshot_id,$snapshot_tables);
                if($this->is_time_limit_exceeded($start_time))
                {
                    $ret['result']='success';
                    $ret['finished']=0;
                    $ret['snapshot_id']=$snapshot_id;
                    $ret['snapshot_tables']=$snapshot_tables;
                    $this->update_task($snapshot_id,$snapshot_tables);
                    return $ret;
                }
            }
        }
        else
        {
            $ret['result']='failed';
            $ret['error']='Creating the snapshot failed.';
            return $ret;
        }

        $this->update_task($snapshot_id,$snapshot_tables);
        $this->update_snapshot($snapshot_id,$type,$comment);
        $ret['result']='success';
        $ret['finished']=1;
        $ret['snapshot_id']=$snapshot_id;

        return $ret;
    }

    public function init_task($snapshot_id,$snapshot_tables,$type,$comment)
    {
        $snapshot_task=$this->options->get_option('wpvivid_snapshot_task');
        if(empty($snapshot_task))
        {
            $snapshot_task=array();
        }

        $snapshot_task['snapshot_id']=$snapshot_id;
        $snapshot_task['snapshot_tables']=$snapshot_tables;
        $snapshot_task['type']=$type;
        $snapshot_task['comment']=$comment;

        $this->options->update_option('wpvivid_snapshot_task',$snapshot_task);
    }

    public function get_progress()
    {
        $snapshot_task=$this->options->get_option('wpvivid_snapshot_task');
        if(empty($snapshot_task))
        {
            $progress['main_percent']='0%';
            $progress['doing']="Creating a snapshot.";
        }

        $snapshot_tables=$snapshot_task['snapshot_tables'];
        $i_sum=count($snapshot_tables);
        $i_finished=0;
        $b_finished=true;
        foreach ($snapshot_tables as $table_name=>$snapshot_table)
        {
            if($snapshot_table['finished']==1)
            {
                $i_finished++;
            }
            else
            {
                $b_finished=false;
            }
        }

        $i_progress=intval(($i_finished/$i_sum)*100);
        $progress['main_percent']=$i_progress.'%';
        if($b_finished)
        {
            $progress['doing']="Create snapshot completed.";
        }
        else
        {
            $progress['doing']="Creating a snapshot.";
        }

        return $progress;
    }

    public function update_task($snapshot_id,$snapshot_tables)
    {
        $snapshot_task=$this->options->get_option('wpvivid_snapshot_task');
        if(empty($snapshot_task))
        {
            $snapshot_task=array();
        }

        $snapshot_task['snapshot_id']=$snapshot_id;
        $snapshot_task['snapshot_tables']=$snapshot_tables;

        $this->options->update_option('wpvivid_snapshot_task',$snapshot_task);
    }

    public function get_task_data()
    {
        $snapshot_task=$this->options->get_option('wpvivid_snapshot_task');
        if(empty($snapshot_task))
        {
            $ret['result']='failed';
            $ret['error']='Creating snapshot task not found.';
            return $ret;
        }

        if(empty($snapshot_task['snapshot_id'])||empty($snapshot_task['snapshot_tables']))
        {
            $ret['result']='failed';
            $ret['error']='Creating snapshot task not found.';
            return $ret;
        }

        $ret['result']='success';
        $ret['snapshot_id']=$snapshot_task['snapshot_id'];
        $ret['snapshot_tables']=$snapshot_task['snapshot_tables'];
        $ret['type']=$snapshot_task['type'];
        $ret['comment']=$snapshot_task['comment'];
        return $ret;
    }

    public function get_snapshots($type='')
    {
        $snapshot_data=$this->options->get_option('wpvivid_snapshot');
        if(empty($snapshot_data))
        {
            $snapshot_data=array();
        }

        if(empty($type))
        {
            return $snapshot_data;
        }
        else
        {
            $get_snapshot_data=array();
            foreach ($snapshot_data as $data)
            {
                if($data['type']==$type)
                {
                    $get_snapshot_data[]=$data;
                }
            }
            return $snapshot_data;
        }
    }

    public function restore_snapshot($snapshot_id)
    {
        global $wpdb;

        $tables = $wpdb->get_results('SHOW TABLE STATUS');

        //$exclude_tables[]=$wpdb->prefix.'wpvivid_log';
        //$exclude_tables[]=$wpdb->base_prefix."wpvivid_increment_big_ids";
        //$exclude_tables[]=$wpdb->prefix.'wpvivid_merge_db';
        $exclude_tables[]=$wpdb->prefix.'wpvivid_options';
        $exclude_tables=apply_filters('wpvivid_create_snapshot_exclude_tables',$exclude_tables);

        add_filter('wpvivid_merge_query_lock', array($this,'query_lock'), 9999);

        $new_tables = array();
        if (is_array($tables))
        {
            foreach ($tables as $table)
            {
                if (0 !== stripos($table->Name, $snapshot_id))
                {
                    continue;
                }

                if (empty($table->Engine))
                {
                    continue;
                }

                if(in_array($table->Name,$exclude_tables))
                {
                    continue;
                }

                $new_table['Name']=$table->Name;
                $new_table['finished']=0;
                $new_tables[$table->Name] =$new_table;
            }
        }
        else
        {
            $ret['result']='failed';
            $ret['error']='Failed to retrieve list of database tables.';
            return $ret;
        }

        $this->init_restore_task($snapshot_id,$new_tables);

        /*
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

            if(in_array($table->Name,$exclude_tables))
            {
                continue;
            }

            $wpdb->query('DROP TABLE ' . $table->Name);
        }*/

        foreach ($new_tables as $table_name=>$table)
        {
            $new_table=$this->str_replace_first($snapshot_id,$wpdb->prefix,$table_name);

            $wpdb->query('DROP TABLE ' . $new_table);
            $wpdb->query("CREATE TABLE `{$new_table}` LIKE `{$table_name}`");
            $wpdb->query("INSERT `{$new_table}` SELECT * FROM `{$table_name}`");
            $new_tables[$table_name]['finished']=1;
            $this->update_restore_task($snapshot_id,$new_tables);
        }

        $ret['result']='success';
        return $ret;
    }

    public function init_restore_task($snapshot_id,$snapshot_tables)
    {
        $snapshot_task=$this->options->get_option('wpvivid_restore_snapshot_task');
        if(empty($snapshot_task))
        {
            $snapshot_task=array();
        }

        $snapshot_task['snapshot_id']=$snapshot_id;
        $snapshot_task['snapshot_tables']=$snapshot_tables;

        $this->options->update_option('wpvivid_restore_snapshot_task',$snapshot_task);
    }

    public function update_restore_task($snapshot_id,$snapshot_tables)
    {
        $snapshot_task=$this->options->get_option('wpvivid_restore_snapshot_task');
        if(empty($snapshot_task))
        {
            $snapshot_task=array();
        }

        $snapshot_task['snapshot_id']=$snapshot_id;
        $snapshot_task['snapshot_tables']=$snapshot_tables;

        $this->options->update_option('wpvivid_restore_snapshot_task',$snapshot_task);
    }

    public function get_restore_task_data()
    {
        $snapshot_task=$this->options->get_option('wpvivid_restore_snapshot_task');
        if(empty($snapshot_task))
        {
            $ret['result']='failed';
            $ret['error']='Restoring snapshot task not found.';
            return $ret;
        }

        if(empty($snapshot_task['snapshot_id'])||empty($snapshot_task['snapshot_tables']))
        {
            $ret['result']='failed';
            $ret['error']='Restoring snapshot task not found.';
            return $ret;
        }

        $ret['result']='success';
        $ret['snapshot_id']=$snapshot_task['snapshot_id'];
        $ret['snapshot_tables']=$snapshot_task['snapshot_tables'];
        return $ret;
    }

    public function remove_snapshot($snapshot_id)
    {
        global $wpdb;

        $tables = $wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', array($snapshot_id . '%')));
        foreach ($tables as $table)
        {
            $wpdb->query('DROP TABLE IF EXISTS `' . $table.'`');
        }

        $snapshot_data=$this->options->get_option('wpvivid_snapshot');
        unset($snapshot_data[$snapshot_id]);
        $this->options->update_option('wpvivid_snapshot',$snapshot_data);

        $ret['result']='success';
        return $ret;
    }

    public function str_replace_first($from, $to, $content)
    {
        $from = '/'.preg_quote($from, '/').'/';

        return preg_replace($from, $to, $content, 1);
    }

    public function create_snapshot_uid()
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

    public function update_snapshot($snapshot_id,$type,$comment)
    {
        $snapshot_data=$this->options->get_option('wpvivid_snapshot');
        if(empty($snapshot_data))
        {
            $snapshot_data=array();
        }

        $snapshot_data[$snapshot_id]['id']=$snapshot_id;
        $snapshot_data[$snapshot_id]['type']=$type;
        $snapshot_data[$snapshot_id]['time']=time();
        $snapshot_data[$snapshot_id]['comment']=$comment;

        $this->options->update_option('wpvivid_snapshot',$snapshot_data);
    }

    public function is_time_limit_exceeded($start_time)
    {
        $time_limit =20;
        $time_taken = microtime(true) -$start_time;
        if($time_taken >= $time_limit)
        {
            return true;
        }

        return false;
    }

    public function get_dev_progress()
    {
        $snapshot_task=$this->options->get_option('wpvivid_snapshot_task');
        if(empty($snapshot_task))
        {
            $progress['main_percent']='0%';
            $progress['text']="creating snapshot for dev site.";
        }

        $snapshot_tables=$snapshot_task['snapshot_tables'];
        $i_sum=count($snapshot_tables);
        $i_finished=0;
        $b_finished=true;
        foreach ($snapshot_tables as $table_name=>$snapshot_table)
        {
            if($snapshot_table['finished']==1)
            {
                $i_finished++;
            }
            else
            {
                $b_finished=false;
            }
        }

        $i_progress=intval(($i_finished/$i_sum)*100);
        $progress['main_percent']=$i_progress.'%';
        if($b_finished)
        {
            $progress['text']="creating snapshot for dev site completed.";
        }
        else
        {
            $progress['text']="creating snapshot for dev site - $i_finished/$i_sum tables";
        }

        return $progress;
    }

    public function query_lock($lock)
    {
        return true;
    }
}