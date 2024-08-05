<?php

if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

class WPvivid_Restore_DB_WPDB_Method_2
{
    public $max_allow_packet;
    public $skip_query=0;
    public $last_error='';
    public $last_log='';

    public function connect_db()
    {
        global $wpdb;
        $wpdb->get_results('SET NAMES utf8mb4', ARRAY_A);
        $ret['result']=WPVIVID_SUCCESS;
        return $ret;
    }

    public function get_last_error()
    {
        return $this->last_error;
    }

    public function get_last_log()
    {
        return $this->last_log;
    }

    public function test_db()
    {
        global $wpdb;

        $test_table_new=uniqid('wpvivid_test_tables_');
        $columns='(test_id int primary key)';
        $test_table = $wpdb->get_results("CREATE TABLE IF NOT EXISTS $test_table_new $columns",ARRAY_A);

        if ($test_table!==false)
        {
            $this->last_log='The test to create table succeeds.';
            $test_table = $wpdb->get_results("INSERT INTO $test_table_new (`test_id`) VALUES ('123')",ARRAY_A);
            if($test_table!==false)
            {
                $this->last_log='The test to insert into table succeeds.';
                $test_table = $wpdb->get_results("DROP TABLE IF EXISTS $test_table_new",ARRAY_A);
                if($test_table!==false)
                {
                    $this->last_log='The test to drop table succeeds.';
                    return true;
                }
                else
                {
                    $this->last_error='Unable to drop table. The reason is '.$wpdb->last_error;
                    return false;
                }
            }
            else
            {
                $this->last_error='Unable to insert into table. The reason is '.$wpdb->last_error;
                return false;
            }
        }
        else {
            $this->last_error='Unable to create table. The reason is '.$wpdb->last_error;
            return false;
        }
    }

    public function check_max_allow_packet($log)
    {
        $restore_task=get_option('wpvivid_restore_task',array());
        $restore_detail_options=$restore_task['restore_detail_options'];
        $max_allowed_packet=$restore_detail_options['max_allowed_packet'];
        $set_max_allowed_packet=$max_allowed_packet*1024*1024;

        global $wpdb;
        $max_allowed_packet =$wpdb->get_var("SELECT @@session.max_allowed_packet");

        if($max_allowed_packet!==null)
        {
            if($max_allowed_packet<$set_max_allowed_packet)
            {
                $query='set global max_allowed_packet='.$set_max_allowed_packet;
                $test=$wpdb->get_results($query);
                var_dump($test);
                $wpdb->db_connect();
                $max_allowed_packet =$wpdb->get_var("SELECT @@session.max_allowed_packet");
                $this->max_allow_packet=$max_allowed_packet;
            }
            else
            {
                $this->max_allow_packet=$max_allowed_packet;
            }
        }
        else
        {
            $this->last_log='get max_allowed_packet failed.';
            $this->max_allow_packet=1048576;
        }
    }

    public function get_max_allow_packet()
    {
        return $this->max_allow_packet;
    }

    public function init_sql_mode()
    {
        global $wpdb;
        $res = $wpdb->get_var('SELECT @@SESSION.sql_mode');
        if($res===null)
        {
            $this->last_error='get sql_mode failed';
            return false;
        }
        else
        {
            $sql_mod = $res;
            $temp_sql_mode = str_replace('NO_ENGINE_SUBSTITUTION','',$sql_mod);
            $temp_sql_mode = 'ALLOW_INVALID_DATES,NO_AUTO_VALUE_ON_ZERO,'.$temp_sql_mode;
            $wpdb->get_results('SET SESSION sql_mode = "'.$temp_sql_mode.'"',ARRAY_A);
            return true;
        }
    }

    public function set_skip_query($count)
    {
        $this->skip_query=$count;
    }

    public function execute_sql($query)
    {
        if(preg_match('#SET TIME_ZONE=@OLD_TIME_ZONE#', $query))
        {
            return true;
        }
        if(preg_match('#SET SQL_MODE=@OLD_SQL_MODE#', $query))
        {
            return true;
        }
        if(preg_match('#SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS#', $query))
        {
            return true;
        }
        if(preg_match('#SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS#', $query))
        {
            return true;
        }
        if(preg_match('#SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT#', $query))
        {
            return true;
        }
        if(preg_match('#SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS#', $query))
        {
            return true;
        }
        if(preg_match('#SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION#', $query))
        {
            return true;
        }
        if(preg_match('#SET SQL_NOTES=@OLD_SQL_NOTES#', $query))
        {
            return true;
        }

        global $wpdb;
        if ($wpdb->get_results($query)===false)
        {
            $this->last_error=$wpdb->last_error;
            return false;
        }
        else
        {
            return true;
        }
    }

    public function query($sql,$output)
    {
        global $wpdb;
        return $wpdb->get_results($sql,$output);
    }

    public function errorInfo()
    {
        global $wpdb;
        return $wpdb->last_error;
    }
}

class WPvivid_Restore_DB_PDO_Mysql_Method_2
{
    private $db;
    public $max_allow_packet;
    public $skip_query=0;
    public $last_error='';
    public $last_log='';

    public function get_last_error()
    {
        return $this->last_error;
    }

    public function get_last_log()
    {
        return $this->last_log;
    }

    public function connect_db()
    {
        try
        {
            $res = explode(':',DB_HOST);
            $db_host = $res[0];
            $db_port = empty($res[1])?'':$res[1];
            if(!empty($db_port)) {
                $dsn='mysql:host=' . $db_host . ';port=' . $db_port . ';dbname=' . DB_NAME;
            }
            else{
                $dsn='mysql:host=' . $db_host . ';dbname=' . DB_NAME;
            }
            $this->db = null;
            $this->db=new PDO($dsn, DB_USER, DB_PASSWORD);
            $this->db->exec('SET NAMES utf8mb4');
            if(empty($this->db) || !$this->db)
            {
                if(class_exists('PDO'))
                {
                    $extensions=get_loaded_extensions();
                    if(array_search('pdo_mysql',$extensions))
                    {
                        $ret['result']=WPVIVID_FAILED;
                        $ret['error']='The error establishing a database connection. Please check wp-config.php file and make sure the information is correct.';
                    }
                    else{
                        $ret['result']=WPVIVID_FAILED;
                        $ret['error']='The pdo_mysql extension is not detected. Please install the extension first or choose wpdb option for Database connection method.';
                    }
                }
                else{
                    $ret['result']=WPVIVID_FAILED;
                    $ret['error']='The pdo_mysql extension is not detected. Please install the extension first or choose wpdb option for Database connection method.';
                }
            }
            else
            {
                $ret['result']=WPVIVID_SUCCESS;
            }
        }
        catch (Exception $e)
        {
            if(empty($this->db) || !$this->db)
            {
                if(class_exists('PDO'))
                {
                    $extensions=get_loaded_extensions();
                    if(array_search('pdo_mysql',$extensions))
                    {
                        $ret['result']=WPVIVID_FAILED;
                        $ret['error']='The error establishing a database connection. Please check wp-config.php file and make sure the information is correct.';
                    }
                    else{
                        $ret['result']=WPVIVID_FAILED;
                        $ret['error']='The pdo_mysql extension is not detected. Please install the extension first or choose wpdb option for Database connection method.';
                    }
                }
                else{
                    $ret['result']=WPVIVID_FAILED;
                    $ret['error']='The pdo_mysql extension is not detected. Please install the extension first or choose wpdb option for Database connection method.';
                }
            }
            else
            {
                $ret['result']=WPVIVID_FAILED;
                $ret['error']=$e->getMessage();
            }
        }
        return $ret;
    }

    public function test_db()
    {
        $test_table_new=uniqid('wpvivid_test_tables_');
        $columns='(test_id int primary key)';
        $test_table = $this->db->exec("CREATE TABLE IF NOT EXISTS $test_table_new $columns");

        if ($test_table!==false)
        {
            $this->last_log='The test to create table succeeds.';

            $test_table = $this->db->exec("INSERT INTO $test_table_new (`test_id`) VALUES ('123')");
            if($test_table!==false)
            {
                $this->last_log='The test to insert into table succeeds.';
                $test_table = $this->db->exec("DROP TABLE IF EXISTS $test_table_new");
                if($test_table!==false)
                {
                    $this->last_log='The test to drop table succeeds.';
                    return true;
                }
                else
                {
                    $error=$this->db->errorInfo();

                    $this->last_error='Unable to drop table. The reason is '.$error[2];
                    return false;
                }
            }
            else
            {
                $error=$this->db->errorInfo();
                $this->last_error='Unable to insert into table. The reason is '.$error[2];
                return false;
            }
        }
        else {
            $error=$this->db->errorInfo();
            $this->last_error='Unable to create table. The reason is '.$error[2];
            return false;
        }
    }

    public function check_max_allow_packet($log)
    {
        $restore_task=get_option('wpvivid_restore_task',array());
        $restore_detail_options=$restore_task['restore_detail_options'];
        $max_allowed_packet=$restore_detail_options['max_allowed_packet'];
        $set_max_allowed_packet=$max_allowed_packet*1024*1024;

        try{
            $max_allowed_packet = $this->db->query("SELECT @@session.max_allowed_packet;");
            if($max_allowed_packet)
            {
                $max_allowed_packet = $max_allowed_packet -> fetchAll();

                if(is_array($max_allowed_packet)&&isset($max_allowed_packet[0])&&isset($max_allowed_packet[0][0]))
                {
                    if($max_allowed_packet[0][0]<$set_max_allowed_packet)
                    {
                        $query='set global max_allowed_packet='.$set_max_allowed_packet;
                        $this->db->exec($query);
                        $this->connect_db();
                        $max_allowed_packet = $this->db->query("SELECT @@session.max_allowed_packet;");
                        $max_allowed_packet = $max_allowed_packet -> fetchAll();
                        if(is_array($max_allowed_packet)&&isset($max_allowed_packet[0])&&isset($max_allowed_packet[0][0]))
                        {
                            $this->max_allow_packet=$max_allowed_packet[0][0];
                        }
                        else
                        {
                            $this->max_allow_packet=1048576;
                        }
                    }
                    else
                    {
                        $this->max_allow_packet=$max_allowed_packet[0][0];
                    }

                }
                else
                {
                    $this->last_log='get max_allowed_packet failed.';
                    $this->max_allow_packet=1048576;
                }
            }
            else
            {
                $this->last_log='get max_allowed_packet failed.';
                $this->max_allow_packet=1048576;
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            $log->WriteLog($message, 'warning');
        }
    }

    public function get_max_allow_packet()
    {
        return $this->max_allow_packet;
    }

    public function init_sql_mode()
    {
        $res = $this->db->query('SELECT @@SESSION.sql_mode') -> fetchAll();
        $sql_mod = $res[0][0];

        $modes = explode( ',', $sql_mod );
        $modes = array_change_key_case( $modes, CASE_UPPER );

        $incompatible_modes = array(
            'NO_ZERO_DATE',
            'ONLY_FULL_GROUP_BY',
            'STRICT_TRANS_TABLES',
            'STRICT_ALL_TABLES',
            'TRADITIONAL',
        );

        $incompatible_modes = (array) apply_filters( 'incompatible_sql_modes', $incompatible_modes );

        foreach ( $modes as $i => $mode ) {
            if ( in_array( $mode, $incompatible_modes ) ) {
                unset( $modes[ $i ] );
            }
        }

        $sql_mod = implode( ',', $modes );

        //$temp_sql_mode = str_replace('NO_ENGINE_SUBSTITUTION','',$sql_mod);
        //$temp_sql_mode = 'NO_AUTO_VALUE_ON_ZERO,'.$temp_sql_mode;
        $this->db->query('SET SESSION sql_mode = "'.$sql_mod.'"');
        return true;
    }

    public function set_skip_query($count)
    {
        $this->skip_query=$count;
    }

    public function execute_sql($query)
    {
        if($this->skip_query>10)
        {
            if(strlen($query)>$this->max_allow_packet)
            {
                $this->last_log='skip query size:'.size_format(strlen($query));
                return true;
            }
        }

        if(preg_match('#SET TIME_ZONE=@OLD_TIME_ZONE#', $query))
        {
            return true;
        }
        if(preg_match('#SET SQL_MODE=@OLD_SQL_MODE#', $query))
        {
            return true;
        }
        if(preg_match('#SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS#', $query))
        {
            return true;
        }
        if(preg_match('#SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS#', $query))
        {
            return true;
        }
        if(preg_match('#SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT#', $query))
        {
            return true;
        }
        if(preg_match('#SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS#', $query))
        {
            return true;
        }
        if(preg_match('#SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION#', $query))
        {
            return true;
        }
        if(preg_match('#SET SQL_NOTES=@OLD_SQL_NOTES#', $query))
        {
            return true;
        }

        if ($this->db->exec($query)===false)
        {
            $info=$this->db->errorInfo();

            if($info[1] == 2006)
            {
                if(strlen($query)>$this->max_allow_packet)
                {
                    $this->skip_query++;
                    $this->last_error='max_allow_packet too small:'.size_format($this->max_allow_packet).' query size:'.size_format(strlen($query));
                }
                else
                {
                    $this->last_error='execute sql failed. The reason is '.$info[2];
                }
                $ret=$this->connect_db();
                if($ret['result']==WPVIVID_FAILED)
                {
                    $this->last_log='reconnect failed';

                }
                else {
                    $this->last_log='reconnect succeed';
                }
            }
            else
            {
                $this->last_error='execute sql failed. The reason is '.$info[2];
            }

            return false;
        }
        else
        {
            return true;
        }
    }

    public function query($sql,$output)
    {
        $ret=$this->db->query($sql);
        if($ret===false)
        {
            $error=$this->db->errorInfo();
            $this->last_error=$error[1].' - '.$error[2];
            return false;
        }
        else
        {
            return $ret -> fetchAll();
        }
    }

    public function errorInfo()
    {
        return $this->db->errorInfo();
    }
}

class WPvivid_Restore_DB_Method_2
{
    private $db;
    private $type;

    public function __construct()
    {
        $restore_task=get_option('wpvivid_restore_task',array());
        $restore_detail_options=$restore_task['restore_detail_options'];
        $db_connect_method=$restore_detail_options['db_connect_method'];
        if($db_connect_method === 'wpdb')
        {
            $this->db =new WPvivid_Restore_DB_WPDB_Method_2();
            $this->type='wpdb';
        }
        else{
            $this->db =new WPvivid_Restore_DB_PDO_Mysql_Method_2();
            $this->type='pdo_mysql';
        }
    }

    public function get_last_error()
    {
        return $this->db->last_error;
    }

    public function get_last_log()
    {
        return $this->db->last_log;
    }

    public function get_type()
    {
        return $this->type;
    }

    public function connect_db()
    {
        return $this->db->connect_db();
    }

    public function test_db()
    {
        return $this->db->test_db();
    }

    public function check_max_allow_packet($log)
    {
        $this->db->check_max_allow_packet($log);
    }

    public function get_max_allow_packet()
    {
        return $this->db->get_max_allow_packet();
    }

    public function init_sql_mode()
    {
        $this->db->init_sql_mode();
    }

    public function set_skip_query($count)
    {
        $this->db->set_skip_query($count);
    }

    public function execute_sql($query)
    {
        return $this->db->execute_sql($query);
    }

    public function query($sql,$output=ARRAY_A)
    {
        return $this->db->query($sql,$output);
    }

    public function errorInfo()
    {
        return $this->db->errorInfo();
    }
}