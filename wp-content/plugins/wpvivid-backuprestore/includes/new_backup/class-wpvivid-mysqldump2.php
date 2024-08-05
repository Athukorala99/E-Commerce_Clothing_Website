<?php

if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

use Exception as Exception;

class CompressTest_2
{
    private $fileHandler = null;

    /**
     * @param string $filename
     */
    public function open($filename)
    {
        $this->fileHandler = fopen($filename, "wb");
        if (false === $this->fileHandler) {
            throw new Exception("Output file is not writable");
        }

        return true;
    }

    public function write($str)
    {
        if (false === ($bytesWritten = fwrite($this->fileHandler, $str))) {
            throw new Exception("Writting to file failed! Probably, there is no more free space left?");
        }
        return $bytesWritten;
    }

    public function close()
    {
        return fclose($this->fileHandler);
    }

    public function get_size()
    {
        $fstat = fstat($this->fileHandler);
        return $fstat['size'];
    }
}

class WPvivid_Mysqldump2
{

    // Same as mysqldump
    const MAXLINESIZE = 1000000;

    // Available compression methods as constants
    const GZIP = 'Gzip';
    const BZIP2 = 'Bzip2';
    const NONE = 'None';

    // Available connection strings
    const UTF8 = 'utf8';
    const UTF8MB4 = 'utf8mb4';

    /**
     * Database username
     * @var string
     */
    public $user;
    /**
     * Database password
     * @var string
     */
    public $pass;
    /**
     * Destination filename, defaults to stdout
     * @var string
     */
    public $fileName = 'php://output';

    // Internal stuff
    private $tables = array();
    //private $dbHandler = null;
    private $dbType;
    private $compressManager;
    private $typeAdapter;
    private $dumpSettings = array();
    private $version;
    private $tableColumnTypes = array();
    public $log=false;
    public $task_id='';
    /**
     * database name, parsed from dsn
     * @var string
     */
    private $dbName;
    /**
     * host name, parsed from dsn
     * @var string
     */
    private $host;

    public $last_query_string='';

    public $task = false;
    public $file_index=1;
    public $tmp_file_name='';
    public $current_size=0;
    public $files=array();
    public $backup_tables;
    public $find_zero_date=false;

    public function __construct($task,$dump_setting)
    {
        if(is_a($task, 'WPvivid_Backup_Task_2'))
        {
            $this->task=$task;
        }
        else
        {
            throw new Exception('not as wpvivid task type');
        }

        $dumpSettingsDefault = array(
            'include-tables' => array(),
            'exclude-tables' => array(),
            'compress' => WPvivid_Mysqldump2::NONE,
            'init_commands' => array(),
            'no-data' => array(),
            'reset-auto-increment' => false,
            'add-drop-database' => false,
            'add-drop-table' => true,
            'add-drop-trigger' => true,
            'add-locks' => true,
            'complete-insert' => false,
            'default-character-set' => WPvivid_Mysqldump2::UTF8,
            'disable-keys' => true,
            'extended-insert' => false,
            'events' => false,
            'hex-blob' => true, /* faster than escaped content */
            'net_buffer_length' => self::MAXLINESIZE,
            'no-autocommit' => false,
            'no-create-info' => false,
            'lock-tables' => false,
            'routines' => false,
            'single-transaction' => true,
            'skip-triggers' => false,
            'skip-tz-utc' => false,
            'skip-comments' => false,
            'skip-dump-date' => false,
            'where' => '',

        );

        if(defined('DB_CHARSET'))
        {
            $dumpSettingsDefault['default-character-set']=DB_CHARSET;
        }

        $this->dumpSettings = $this->array_replace_recursive($dumpSettingsDefault, $dump_setting);

        $this->dumpSettings['init_commands'][] = "SET NAMES " . WPvivid_Mysqldump2::UTF8MB4;

        if (false === $this->dumpSettings['skip-tz-utc'])
        {
            $this->dumpSettings['init_commands'][] = "SET TIME_ZONE='+00:00'";
        }

        // Create a new compressManager to manage compressed output
        $this->compressManager = new CompressTest_2();
        $this->backup_tables=0;
    }

    public function connect()
    {
        $dbType=$this->dumpSettings['db_connect_method'];
        $host=$this->dumpSettings['host'];
        $user=$this->dumpSettings['user'];
        $pass=$this->dumpSettings['pass'];
        $database=$this->dumpSettings['database'];

        $this->typeAdapter = WPvividTypeAdapterFactory::create($dbType, null);
        $this->typeAdapter->connect($host,$database,$user,$pass,$this->dumpSettings['init_commands']);

    }

    public function write_header()
    {
        // Write some basic info to output file
        $upload_dir  = wp_upload_dir();

        $site_url=$this->dumpSettings['site_url'];
        $home_url=$this->dumpSettings['home_url'];
        $content_url=$this->dumpSettings['content_url'];
        $upload_url=$upload_dir['baseurl'];

        $this->compressManager->write('/* # site_url: '.$site_url.' */;'.PHP_EOL);
        $this->compressManager->write('/* # home_url: '.$home_url.' */;'.PHP_EOL);
        $this->compressManager->write('/* # content_url: '.$content_url.' */;'.PHP_EOL);
        $this->compressManager->write('/* # upload_url: '.$upload_url.' */;'.PHP_EOL);
        if(isset($this->dumpSettings['prefix']))
        {
            $table_prefix=$this->dumpSettings['prefix'];
            $this->compressManager->write('/* # table_prefix: '.$table_prefix.' */;'.PHP_EOL.PHP_EOL.PHP_EOL);
        }


        // Store server settings and use sanner defaults to dump
        $this->compressManager->write(
            $this->typeAdapter->backup_parameters($this->dumpSettings)
        );
    }

    public function write_footer()
    {
        // Restore saved parameters
        $this->compressManager->write(
            $this->typeAdapter->restore_parameters($this->dumpSettings)
        );
    }

    public function init_job()
    {
        $tables=$this->list_tables();

        if(empty($tables))
        {
            return false;
        }

        usort($tables, function ($a, $b)
        {
            if ($a['size'] == $b['size'])
                return 0;

            if ($a['size'] > $b['size'])
                return 1;
            else
                return -1;
        });

        $jobs=array();

        foreach ($tables as $table)
        {
            $jobs[$table['name']]['index']=0;
            $jobs[$table['name']]['finished']=0;
            $jobs[$table['name']]['created']=0;
            $jobs[$table['name']]['name']=$table['name'];
            $jobs[$table['name']]['size']=$table['size'];
            $jobs[$table['name']]['rows']=$table['rows'];
        }

        $this->task->update_current_sub_job($jobs);
        return $jobs;
    }

    public function start_jobs()
    {
        $this->tables= $this->task->get_current_sub_job();
        return $this->exportTables();
    }

    public function list_tables()
    {
        $tables=array();
        $views=array();

        global $wpdb;
        $resultSet=$wpdb->get_results('SHOW TABLE STATUS', ARRAY_A);

        $resultViews=$wpdb->get_results('SHOW FULL TABLES WHERE table_type = \'VIEW\'', ARRAY_A);
        if(!is_null($resultViews))
        {
            foreach ($resultViews as $view)
            {
                $name = 'Tables_in_'.DB_NAME;
                $views[] = $view[$name];
            }
        }

        if (is_null($resultSet))
        {
           return $tables;
        }

        if(isset($this->dumpSettings['prefix'])&&!empty($this->dumpSettings['prefix']))
        {
            $exclude = array('/^(?!' . $this->dumpSettings['prefix'] . ')/i');
        }
        else
        {
            $exclude=array();
        }
        foreach ($resultSet as $row)
        {
            if(isset($row['Comment']) && $row['Comment'] === 'VIEW')
            {
                continue;
            }

            if ( $this->matches($row['Name'], $this->dumpSettings['include-tables']) )
            {
                $table['name']=$row['Name'];
                $table['size']= ($row["Data_length"] + $row["Index_length"]);
                $table['rows']=$row['Rows'];
                $tables[]=$table;
                continue;
            }

            if(!empty($exclude))
            {
                if ( $this->matches($row['Name'], $exclude) )
                {
                    continue;
                }
            }

            if ( $this->matches($row['Name'], $this->dumpSettings['exclude-tables']) )
            {
                continue;
            }

            if(!empty($views))
            {
                if ( $this->matches($row['Name'], $views) )
                {
                    continue;
                }
            }

            $table['name']=$row['Name'];
            $table['size']= ($row["Data_length"] + $row["Index_length"]);
            $table['rows']=$row['Rows'];
            $tables[]=$table;
        }

        return $tables;
    }
    /**
     * Compare if $table name matches with a definition inside $arr
     * @param $table string
     * @param $arr array with strings or patterns
     * @return bool
     */
    private function matches($table, $arr) {
        $match = false;

        if(empty($arr))
        {
            return false;
        }

        foreach ($arr as $pattern) {
            if ( '/' != $pattern[0] ) {
                continue;
            }
            if ( 1 == preg_match($pattern, $table) ) {
                $match = true;
            }
        }

        return in_array($table, $arr) || $match;
    }

    public function check_tmp_file()
    {
        $max_file_size=$this->dumpSettings['max_file_size'];
        $max_backup_tables=5000;
        if($max_file_size==0)
            return;
        $this->current_size=$this->compressManager->get_size();
        $path=$this->dumpSettings['path'];

        if( $this->current_size>$max_file_size||$this->backup_tables>=$max_backup_tables)
        {
            $this->current_size=0;
            $this->backup_tables=0;
            $this->close_tmp_file();
            $name_file_name=$this->dumpSettings['file_prefix'].'.part'.sprintf('%03d',($this->file_index)).'.sql';
            $this->file_index++;
            rename($this->tmp_file_name,$path.DIRECTORY_SEPARATOR.$name_file_name);
            $this->task->update_current_sub_job($this->tables);
            $this->task->add_mysql_dump_files($name_file_name);
            $this->open_tmp_file();
        }
    }

    public function open_tmp_file($b_delete=false)
    {
        if($b_delete)
            @wp_delete_file( $this->tmp_file_name);
        $this->compressManager->open($this->tmp_file_name);
    }

    public function close_tmp_file()
    {
        $this->compressManager->close();
    }

    private function exportTables()
    {
        global $wpvivid_plugin;
        //tmp_file_name
        $path=$this->dumpSettings['path'];
        $this->tmp_file_name=$path.DIRECTORY_SEPARATOR.$this->dumpSettings['file_prefix'].'_tmp.sql';

        $this->open_tmp_file();
        $this->write_header();
        /*
        if(file_exists($this->tmp_file_name))
        {
            $this->open_tmp_file();
        }
        else
        {
            $this->open_tmp_file();
            $this->write_header();
        }*/

        // Exporting tables one by one
        $this->file_index=$this->task->get_current_mysql_file_index();
        $this->current_size=0;
        $tables=$this->tables;
        $i=0;
        $i_step=0;
        $this->backup_tables=0;
        if($this->task->task_id!=='')
        {
            $size = $this->task->get_backup_jobs();
            if(sizeof($size) > 0)
            {
                $i_step = intval(1 / (sizeof($size)) * 100);
            }
        }
        foreach ($tables as $name=>$table)
        {
            if($this->task->check_cancel_backup())
            {
                die();
            }

            if($table['finished']==1)
            {
                continue;
            }
            $index=$table['index'];
            $table_name=$table['name'];

            $message='Preparing to dump table '.$table_name;
            $wpvivid_plugin->wpvivid_log->WriteLog($message,'notice');

            $this->task->update_sub_task_progress($message);

            if($table['created']==0)
            {
                $this->getTableStructure($table_name);
                $this->tables[$name]['created']=1;
                //$this->task->update_current_sub_job($this->tables);
            }

            $this->tableColumnTypes[$table_name] = $this->getTableColumnTypes($table_name);
            if($this->tableColumnTypes[$table_name]===false)
            {
                continue;
            }

            $this->listValues($table_name,$index);
            $this->check_tmp_file();

            $this->tables[$name]['finished']=1;
            $this->backup_tables++;
            //$this->task->update_current_sub_job($this->tables);
            $i++;
            if($this->task->task_id!=='')
            {
                $i_progress=intval($i/sizeof($this->tables)*$i_step);
                $this->task->update_database_progress($i_progress);
            }
        }

        $this->current_size=$this->compressManager->get_size();
        if($this->current_size>0)
        {
            $this->close_tmp_file();
            $name_file_name=$this->dumpSettings['file_prefix'].'.part'.sprintf('%03d',($this->file_index)).'.sql';
            $this->file_index++;
            rename($this->tmp_file_name,$path.DIRECTORY_SEPARATOR.$name_file_name);
            $this->task->add_mysql_dump_files($name_file_name);
        }
        else
        {
            $this->close_tmp_file();
        }

        $ret['result']='success';
        return $ret;
    }
    /**
     * Table structure extractor
     *
     * @param string $tableName  Name of table to export
     * @return null
     */
    private function getTableStructure($tableName)
    {
        if (!$this->dumpSettings['no-create-info']) {
            $ret = '';
            if (!$this->dumpSettings['skip-comments']) {
                $ret = "--" . PHP_EOL .
                    "-- Table structure for table `$tableName`" . PHP_EOL .
                    "--" . PHP_EOL . PHP_EOL;
            }
            $stmt = $this->typeAdapter->show_create_table($tableName);

            foreach ($this->query($stmt) as $r)
            {
                $this->compressManager->write($ret);
                if ($this->dumpSettings['add-drop-table']) {
                    $this->compressManager->write(
                        $this->typeAdapter->drop_table($tableName)
                    );
                }

                $this->compressManager->write(
                    $this->typeAdapter->create_table($r, $this->dumpSettings)
                );
                break;
            }
        }

        return;
    }

    /**
     * Store column types to create data dumps and for Stand-In tables
     *
     * @param string $tableName  Name of table to export
     * @return array type column types detailed
     */

    private function getTableColumnTypes($tableName) {
        $columnTypes = array();
        $columns = $this->query(
            $this->typeAdapter->show_columns($tableName)
        );
        if($columns===false)
        {
            $error=$this->typeAdapter->errorInfo();
            if(isset($error[2])){
                $error = 'Error: '.$error[2];
            }
            else{
                $error = '';
            }
            $columns = $this->query(
                'DESCRIBE '.$tableName
            );
            if($columns===false)
            {
                $error=$this->typeAdapter->errorInfo();
                if(isset($error[2])){
                    $error = 'Error: '.$error[2];
                }
                else{
                    $error = '';
                }
                return false;
            }
        }

        foreach($columns as $key => $col) {
            $types = $this->typeAdapter->parseColumnType($col);
            $columnTypes[$col['Field']] = array(
                'is_numeric'=> $types['is_numeric'],
                'is_blob' => $types['is_blob'],
                'type' => $types['type'],
                'type_sql' => $col['Type'],
                'is_virtual' => $types['is_virtual']
            );
        }

        return $columnTypes;
    }

    /**
     * Escape values with quotes when needed
     *
     * @param string $tableName Name of table which contains rows
     * @param array $row Associative array of column names and values to be quoted
     *
     * @return array
     */
    private function escape($tableName, $row)
    {
        $ret = array();
        $columnTypes = $this->tableColumnTypes[$tableName];
        foreach ($row as $colName => $colValue) {
            if (is_null($colValue)) {
                $ret[] = "NULL";
            } elseif ($this->dumpSettings['hex-blob'] && $columnTypes[$colName]['is_blob']) {
                if ($columnTypes[$colName]['type'] == 'bit' || !empty($colValue)) {
                    $ret[] = "0x{$colValue}";
                } else {
                    $ret[] = "''";
                }
            } elseif ($columnTypes[$colName]['is_numeric']) {
                $ret[] = $colValue;
            } else {
                $ret[] = $this->typeAdapter->quote($colValue);
            }
        }
        return $ret;
    }


    private function listValues($tableName,$index)
    {
        global $wpvivid_plugin;
        $this->prepareListValues($tableName);

        $onlyOnce = true;
        $lineSize = 0;

        $colStmt = $this->getColumnStmt($tableName);

        global $wpdb;
        $prefix=$wpdb->base_prefix;
        $dbType=$this->dumpSettings['db_connect_method'];

        $start=$index;
        $limit_count=5000;

        //$sum =$wpdb->get_var("SELECT COUNT(1) FROM `{$tableName}`");
        $sum=0;
        $resultSet = $this->query("SELECT COUNT(1) FROM `{$tableName}`");
        foreach ($resultSet as $row)
        {
            $sum=$row['COUNT(1)'];
        }

        $this->typeAdapter->closeCursor($resultSet);

        if($dbType=='wpdb')
        {
            $b_options=false;
            if(substr($tableName, strlen($prefix))=='options')
            {
               $b_options=true;
            }

            $stmt = "SELECT " . implode(",", $colStmt) . " FROM `$tableName`";

            if ($this->dumpSettings['where']) {
                $stmt .= " WHERE {$this->dumpSettings['where']}";
            }

            $i=0;
            $i_check_cancel=0;
            $count=0;

            while($sum > $start)
            {
                $limit = " LIMIT {$limit_count} OFFSET {$start}";

                $query=$stmt.$limit;
                $resultSet = $this->query($query);

                if($resultSet===false)
                {
                    $error=$this->typeAdapter->errorInfo();
                    if(isset($error[2])){
                        $error = 'Error: '.$error[2];
                    }
                    else{
                        $error = '';
                    }
                    $this->endListValues($tableName);
                    return ;
                }

                foreach ($resultSet as $row)
                {
                    $i++;

                    $skip=false;

                    $vals = $this->escape($tableName, $row);

                    foreach($vals as $key => $value)
                    {
                        if($value === '\'0000-00-00 00:00:00\'')
                        {
                            //$vals[$key] = '\'1999-01-01 00:00:00\'';
                            $this->find_zero_date=true;
                        }

                        if($b_options)
                        {
                            if($value=="'wpvivid_task_list'")
                            {
                                $skip=true;
                            }
                        }
                    }

                    if($skip)
                        continue;
                    if ($onlyOnce || !$this->dumpSettings['extended-insert'])
                    {
                        if ($this->dumpSettings['complete-insert'])
                        {
                            $lineSize += $this->compressManager->write(
                                "INSERT INTO `$tableName` (" .
                                implode(", ", $colStmt) .
                                ") VALUES (" . implode(",", $vals) . ")"
                            );
                        } else {
                            $lineSize += $this->compressManager->write(
                                "INSERT INTO `$tableName` VALUES (" . implode(",", $vals) . ")"
                            );
                        }
                        $onlyOnce = false;
                    }
                    else {
                        $lineSize += $this->compressManager->write(",(" . implode(",", $vals) . ")");
                    }
                    if (($lineSize > $this->dumpSettings['net_buffer_length']) ||
                        !$this->dumpSettings['extended-insert']) {
                        $onlyOnce = true;
                        $lineSize = $this->compressManager->write(";" . PHP_EOL);
                    }

                    if($i>=200000)
                    {
                        $count+=$i;
                        $i=0;
                        if($this->task->task_id!=='')
                        {
                            $i_check_cancel++;
                            if($i_check_cancel>5)
                            {
                                $i_check_cancel=0;
                                $this->task->check_cancel_backup();
                            }
                            $message='Dumping table '.$tableName.', rows dumped: '.$count.' rows.';
                            $this->task->update_sub_task_progress($message);
                        }
                    }
                }

                $this->typeAdapter->closeCursor($resultSet);

                $start += $limit_count;
                $this->tables[$tableName]['index']=$start;
                //$this->task->update_current_sub_job($this->tables);
                $this->check_tmp_file();
            }

            if (!$onlyOnce) {
                $this->compressManager->write(";" . PHP_EOL);
            }

            $this->endListValues($tableName);
        }
        else
        {
            $b_options=false;
            if(substr($tableName, strlen($prefix))=='options')
            {
                $b_options=true;
            }

            $stmt = "SELECT " . implode(",", $colStmt) . " FROM `$tableName`";

            if ($this->dumpSettings['where']) {
                $stmt .= " WHERE {$this->dumpSettings['where']}";
            }

            $i=0;
            $i_check_cancel=0;
            $count=0;

            while($sum > $start)
            {
                $limit = " LIMIT {$limit_count} OFFSET {$start}";

                $query=$stmt.$limit;
                $resultSet = $this->query($query);

                if($resultSet===false)
                {
                    $error=$this->typeAdapter->errorInfo();
                    if(isset($error[2])){
                        $error = 'Error: '.$error[2];
                    }
                    else{
                        $error = '';
                    }
                    $this->endListValues($tableName);
                    return ;
                }

                foreach ($resultSet as $row)
                {
                    $skip=false;
                    $vals = $this->escape($tableName, $row);

                    foreach($vals as $key => $value)
                    {
                        if($value === '\'0000-00-00 00:00:00\'')
                        {
                            //$vals[$key] = '\'1999-01-01 00:00:00\'';
                            $this->find_zero_date=true;
                        }

                        if($b_options)
                        {
                            if($value=="'wpvivid_task_list'")
                            {
                                $skip=true;
                            }
                        }
                    }

                    if($skip)
                        continue;

                    if ($onlyOnce || !$this->dumpSettings['extended-insert'])
                    {
                        if ($this->dumpSettings['complete-insert'])
                        {
                            var_dump('test1');
                            $lineSize += $this->compressManager->write(
                                "INSERT INTO `$tableName` (" .
                                implode(", ", $colStmt) .
                                ") VALUES (" . implode(",", $vals) . ")"
                            );
                        } else {
                            $lineSize += $this->compressManager->write(
                                "INSERT INTO `$tableName` VALUES (" . implode(",", $vals) . ")"
                            );
                        }
                        $onlyOnce = false;
                    }
                    else {
                        $lineSize += $this->compressManager->write(",(" . implode(",", $vals) . ")");
                    }
                    if (($lineSize > $this->dumpSettings['net_buffer_length']) ||
                        !$this->dumpSettings['extended-insert']) {
                        $onlyOnce = true;
                        $lineSize = $this->compressManager->write(";" . PHP_EOL);
                    }

                    if($i>=200000)
                    {
                        $count+=$i;
                        $i=0;
                        if($this->task->task_id!=='')
                        {
                            $i_check_cancel++;
                            if($i_check_cancel>5)
                            {
                                $i_check_cancel=0;
                                $this->task->check_cancel_backup();
                            }
                            $message='Dumping table '.$tableName.', rows dumped: '.$count.' rows.';
                            $this->task->update_sub_task_progress($message);
                        }
                    }
                }

                $this->typeAdapter->closeCursor($resultSet);

                $start += $limit_count;
                $this->tables[$tableName]['index']=$start;
                //$this->task->update_current_sub_job($this->tables);
                $this->check_tmp_file();
            }

            if (!$onlyOnce) {
                $this->compressManager->write(";" . PHP_EOL);
            }

            $this->endListValues($tableName);
        }

        //$this->current_size+=$table['size'];
    }

    /**
     * Table rows extractor, append information prior to dump
     *
     * @param string $tableName  Name of table to export
     *
     * @return null
     */
    function prepareListValues($tableName)
    {
        if (!$this->dumpSettings['skip-comments']) {
            $this->compressManager->write(
                "--" . PHP_EOL .
                "-- Dumping data for table `$tableName`" .  PHP_EOL .
                "--" . PHP_EOL . PHP_EOL
            );
        }

        if ($this->dumpSettings['single-transaction']) {
            $this->exec($this->typeAdapter->setup_transaction());
            $this->exec($this->typeAdapter->start_transaction());
        }

        if ($this->dumpSettings['lock-tables'])
        {
            $this->typeAdapter->lock_table($tableName);

            //if($this -> privileges['LOCK TABLES'] == 0)
            //{
            //global $wpvivid_plugin;
            //    $wpvivid_plugin->wpvivid_log->WriteLog('The lack of LOCK TABLES privilege, the backup will skip lock_tables() to continue.','notice');
            //}else{
            //    $this->typeAdapter->lock_table($tableName);
            //}
        }

        if ($this->dumpSettings['add-locks']) {
            $this->compressManager->write(
                $this->typeAdapter->start_add_lock_table($tableName)
            );
        }

        if ($this->dumpSettings['disable-keys']) {
            $this->compressManager->write(
                $this->typeAdapter->start_add_disable_keys($tableName)
            );
        }

        // Disable autocommit for faster reload
        if ($this->dumpSettings['no-autocommit']) {
            $this->compressManager->write(
                $this->typeAdapter->start_disable_autocommit()
            );
        }

        return;
    }

    /**
     * Table rows extractor, close locks and commits after dump
     *
     * @param string $tableName  Name of table to export
     *
     * @return null
     */
    function endListValues($tableName)
    {
        if ($this->dumpSettings['disable-keys']) {
            $this->compressManager->write(
                $this->typeAdapter->end_add_disable_keys($tableName)
            );
        }

        if ($this->dumpSettings['add-locks']) {
            $this->compressManager->write(
                $this->typeAdapter->end_add_lock_table($tableName)
            );
        }

        if ($this->dumpSettings['single-transaction']) {
            $this->exec($this->typeAdapter->commit_transaction());
        }

        if ($this->dumpSettings['lock-tables']) {
            $this->typeAdapter->unlock_table($tableName);
        }

        // Commit to enable autocommit
        if ($this->dumpSettings['no-autocommit']) {
            $this->compressManager->write(
                $this->typeAdapter->end_disable_autocommit()
            );
        }

        $this->compressManager->write(PHP_EOL);

        return;
    }

    /**
     * Build SQL List of all columns on current table
     *
     * @param string $tableName  Name of table to get columns
     *
     * @return string SQL sentence with columns
     */
    function getColumnStmt($tableName)
    {
        $colStmt = array();
        foreach($this->tableColumnTypes[$tableName] as $colName => $colType) {
            if ($colType['type'] == 'bit' && $this->dumpSettings['hex-blob']) {
                $colStmt[] = "LPAD(HEX(`{$colName}`),2,'0') AS `{$colName}`";
            } else if ($colType['is_blob'] && $this->dumpSettings['hex-blob']) {
                $colStmt[] = "HEX(`{$colName}`) AS `{$colName}`";
            } else if ($colType['is_virtual']) {
                $this->dumpSettings['complete-insert'] = true;
                continue;
            } else {
                $colStmt[] = "`{$colName}`";
            }
        }

        return $colStmt;
    }

    /**
     * Custom array_replace_recursive to be used if PHP < 5.3
     * Replaces elements from passed arrays into the first array recursively
     *
     * @param array $array1 The array in which elements are replaced
     * @param array $array2 The array from which elements will be extracted
     *
     * @return array Returns an array, or NULL if an error occurs.
     */
    public function array_replace_recursive($array1, $array2)
    {
        if (function_exists('array_replace_recursive')) {
            return array_replace_recursive($array1, $array2);
        }

        foreach ($array2 as $key => $value) {
            if (is_array($value)) {
                $array1[$key] = $this->array_replace_recursive($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }
        return $array1;
    }

    public function query($query_string)
    {
        $this->last_query_string=$query_string;
        return  $this->typeAdapter->query($query_string);
    }

    private function exec($query_string)
    {
        $this->last_query_string=$query_string;
        return  $this->typeAdapter->query($query_string);
    }

    public function is_has_zero_date()
    {
        if($this->find_zero_date)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}