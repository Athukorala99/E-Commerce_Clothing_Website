<?php

if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

class WPvivid_Zip
{
    public $zip_object;

    public function __construct($zip_method='')
    {
        $this->check_available_zip_object($zip_method);
    }

    public function add_files($zip_file,$root_path,$files,$create=false,$json=false)
    {
        if($create)
        {
            if(file_exists($zip_file))
                @wp_delete_file($zip_file);
        }

        if($json!==false)
        {
            $this->add_json_file($zip_file,$json,$create);
        }

        if(file_exists($zip_file))
        {
            $this->zip_object->open($zip_file);
            clearstatcache();
        }
        else
        {
            $create_code = (version_compare(PHP_VERSION, '5.2.12', '>') && defined('ZIPARCHIVE::CREATE')) ? ZIPARCHIVE::CREATE : 1;
            $this->zip_object->open($zip_file, $create_code);
        }

        if(is_a($this->zip_object,'WPvivid_PclZip_2'))
            $this->zip_object->set_replace_path($root_path);

        foreach ($files as $file)
        {
            $new_file=str_replace($root_path,'',$file);
            if(file_exists($file))
            {
                $this->zip_object->addFile($file,$new_file);
            }
        }

        if($this->zip_object->close()===false)
        {
            $ret['result']='failed';
            $ret['error']='Failed to add zip files.';
            if(is_a($this->zip_object,'WPvivid_PclZip_2'))
            {
                $ret['error'].=' last error:'.$this->zip_object->last_error;
            }
            else if(is_a($this->zip_object,'ZipArchive'))
            {
                $ret['error'].=' status string:'.$this->zip_object->getStatusString();
            }
            return $ret;
        }

        $ret['result']='success';
        return $ret;
    }

    public function add_file($zip_file,$file,$add_as,$replace_path)
    {
        global $wpvivid_plugin;
        $wpvivid_plugin->wpvivid_log->WriteLog('Prepare to zip file. file: '.basename($file),'notice');

        if(file_exists($zip_file))
        {
            $this->zip_object->open($zip_file);
            clearstatcache();
        }
        else
        {
            $create_code = (version_compare(PHP_VERSION, '5.2.12', '>') && defined('ZIPARCHIVE::CREATE')) ? ZIPARCHIVE::CREATE : 1;
            $this->zip_object->open($zip_file, $create_code);
        }

        if(is_a($this->zip_object,'WPvivid_PclZip_2'))
            $this->zip_object->set_replace_path($replace_path);

        if($this->zip_object->addFile($file,$add_as)===false)
        {
            $ret['result']='failed';
            $ret['error']='Failed to add zip file '.$file;
            if(is_a($this->zip_object,'WPvivid_PclZip_2'))
            {
                $ret['error'].=' last error:'.$this->zip_object->last_error;
            }
            else if(is_a($this->zip_object,'ZipArchive'))
            {
                $ret['error'].=' status string:'.$this->zip_object->getStatusString();
            }
            return $ret;
        }

        if($this->zip_object->close()===false)
        {
            $ret['result']='failed';
            $ret['error']='Failed to add zip files.';
            if(is_a($this->zip_object,'WPvivid_PclZip_2'))
            {
                $ret['error'].=' last error:'.$this->zip_object->last_error;
            }
            else if(is_a($this->zip_object,'ZipArchive'))
            {
                $ret['error'].=' status string:'.$this->zip_object->getStatusString();
            }
            return $ret;
        }

        $ret['result']='success';
        $wpvivid_plugin->wpvivid_log->WriteLog('Adding zip files completed.'.basename($zip_file).', filesize: '.size_format(filesize($zip_file),2),'notice');

        return $ret;
    }

    public function add_json_file($zip_file,$json,$create=false)
    {
        if($create)
        {
            if(file_exists($zip_file))
                @wp_delete_file($zip_file);
        }
        $json['file']=basename($zip_file);
        $string=wp_json_encode($json);

        if(file_exists($zip_file))
        {
            $this->zip_object->open($zip_file);
            clearstatcache();
        }
        else
        {
            $create_code = (version_compare(PHP_VERSION, '5.2.12', '>') && defined('ZIPARCHIVE::CREATE')) ? ZIPARCHIVE::CREATE : 1;
            $this->zip_object->open($zip_file, $create_code);
        }

        if($this->zip_object->addFromString('wpvivid_package_info.json',$string)===false)
        {
            $ret['result']='failed';
            $ret['error']='Failed to add zip file';
            return $ret;
        }

        if(is_a($this->zip_object,'WPvivid_PclZip_2'))
        {

        }
        else
        {
            if($this->zip_object->close()===false)
            {
                $ret['result']='failed';
                $ret['error']='Failed to add zip file';
                return $ret;
            }
        }

        $ret['result']='success';
        return $ret;
    }

    public function check_available_zip_object($zip_method)
    {
        if($zip_method=='ziparchive'||empty($zip_method))
        {
            if($this->check_ziparchive_available())
            {
                $this->zip_object=new ZipArchive();
            }
            else
            {
                $this->zip_object=new WPvivid_PclZip_2();
            }
        }
        else
        {
            $this->zip_object=new WPvivid_PclZip_2();
        }
    }

    public function check_ziparchive_available()
    {
        if(class_exists('ZipArchive'))
        {
            if(method_exists('ZipArchive', 'addFile'))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public function addEmptyDir($zip_file,$folders)
    {
        $path=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir().DIRECTORY_SEPARATOR;
        if(file_exists($path.$zip_file))
        {
            $this->zip_object->open($path.$zip_file);
        }
        else
        {
            $create_code = (version_compare(PHP_VERSION, '5.2.12', '>') && defined('ZIPARCHIVE::CREATE')) ? ZIPARCHIVE::CREATE : 1;
            $this->zip_object->open($path.$zip_file, $create_code);
        }

        foreach ($folders as $folder)
        {
            $this->zip_object->addEmptyDir($folder);
        }

        $this->zip_object->close();

        $ret['result']='success';
        return $ret;
    }
}

class WPvivid_PclZip_2
{
    public $addfiles;

    public $adddirs;

    public $path;

    public $pclzip;

    public $last_error;

    public $replace_path;

    public function __construct()
    {
        $this->addfiles = array();
        $this->adddirs = array();
        if(!defined('PCLZIP_TEMPORARY_DIR'))
        {
            $path=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir().DIRECTORY_SEPARATOR;
            $temp_dir =$path.'wpvivid-pclzip-temp'.DIRECTORY_SEPARATOR;
            define(PCLZIP_TEMPORARY_DIR,$temp_dir);
        }

        if (!class_exists('WPvivid_PclZip'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/zip/class-wpvivid-pclzip.php';
    }

    public function open($path, $flags = 0)
    {
        $ziparchive_create_match = (version_compare(PHP_VERSION, '5.2.12', '>') && defined('ZIPARCHIVE::CREATE')) ? ZIPARCHIVE::CREATE : 1;

        if ($flags == $ziparchive_create_match && file_exists($path))
            @wp_delete_file($path);

        $this->pclzip = new WPvivid_PclZip($path);

        if (empty($this->pclzip))
        {
            return false;
        }

        $this->path = $path;

        return true;

    }

    public function set_replace_path($replace_path)
    {
        $this->replace_path=$replace_path;
    }

    public function addFile($file, $add_as)
    {
        $this->addfiles[] = $file;
        return true;
    }

    public function addEmptyDir($dir)
    {
        $this->adddirs[] = $dir;
    }

    public function close()
    {
        if (empty($this->pclzip))
        {
            return false;
        }

        $path=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir().DIRECTORY_SEPARATOR;

        foreach ($this->adddirs as $dir)
        {
            $ret=$this->pclzip->add($path.'emptydir', WPVIVID_PCLZIP_OPT_REMOVE_PATH, $path.'emptydir', WPVIVID_PCLZIP_OPT_ADD_PATH, $dir);
            if (!$ret)
            {
                $this->last_error = $this->pclzip->errorInfo(true);
                return false;
            }
        }

        if(!class_exists('WPvivid_ZipClass'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-zipclass.php';
        $ret = $this->pclzip -> add($this->addfiles,WPVIVID_PCLZIP_OPT_REMOVE_PATH,$this->replace_path,WPVIVID_PCLZIP_CB_PRE_ADD,'wpvivid_function_per_add_callback',WPVIVID_PCLZIP_OPT_NO_COMPRESSION,WPVIVID_PCLZIP_OPT_TEMP_FILE_THRESHOLD,16);

        if (!$ret)
        {
            $this->last_error = $this->pclzip->errorInfo(true);
            return false;
        }

        $this->pclzip = false;
        $this->addfiles = array();
        $this->adddirs = array();

        clearstatcache();

        return true;
    }

    public function addFromString($file_name,$string)
    {
        $path=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir().DIRECTORY_SEPARATOR;
        $temp_path = $path.$file_name;
        if(file_exists($temp_path))
        {
            @wp_delete_file($temp_path);
        }
        file_put_contents($temp_path,$string);
        $this->pclzip  -> add($temp_path,WPVIVID_PCLZIP_OPT_REMOVE_PATH,dirname($temp_path));
        @wp_delete_file($temp_path);
        return true;
    }
}