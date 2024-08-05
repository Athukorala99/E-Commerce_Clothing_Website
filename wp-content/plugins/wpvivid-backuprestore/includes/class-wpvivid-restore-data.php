<?php

if (!defined('WPVIVID_PLUGIN_DIR')){
    die;
}
class WPvivid_restore_data
{
    public $restore_data_file;
    public $restore_log_file;
    public $restore_log=false;
    public $restore_cache=false;


    public function __construct()
    {
        $dir=WPvivid_Setting::get_backupdir();
        $this->restore_data_file= WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.'wpvivid_restoredata';
        $this->restore_log_file= WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.'wpvivid_restore_log.txt';
    }

    public function write_log($message,$type)
    {
        if($this->restore_log===false)
        {
            $this->restore_log=new WPvivid_Log();
            $this->restore_log->OpenLogFile($this->restore_log_file,'has_folder');
        }

        clearstatcache();
        if(filesize($this->restore_log_file)>4*1024*1024)
        {
            $this->restore_log->CloseFile();
            wp_delete_file($this->restore_log_file);
            $this->restore_log=null;
            $this->restore_log=new WPvivid_Log();
            $this->restore_log->OpenLogFile($this->restore_log_file,'has_folder');
        }
        $this->restore_log->WriteLog($message,$type);
    }

    public function get_log_content()
    {
        $file =fopen($this->restore_log_file,'r');

        if(!$file)
        {
            return '';
        }

        $buffer='';
        while(!feof($file))
        {
            $buffer .= fread($file,1024);
        }
        fclose($file);

        return $buffer;
    }
}