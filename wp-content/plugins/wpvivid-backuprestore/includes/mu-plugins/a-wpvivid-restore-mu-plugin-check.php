<?php

/**
 * Plugin Name:       WPvivid Restore Must use plugin checker
 * Plugin URI:        https://wpvivid.com/
 * Description:       
 * Author:            WPvivid
 */

// If this file is called directly, abort.
if ( ! defined( "WPINC" ) ) die;

// Load and include
register_shutdown_function('wpvivid_deal_restore_shut_down_error');
// Run

function wpvivid_transfer_path($path)
{
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode(DIRECTORY_SEPARATOR,$values);
}

function wpvivid_deal_restore_shut_down_error()
{
    $error = error_get_last();
    if (!is_null($error)&&($error['type']==E_ERROR||$error['type']==E_COMPILE_ERROR))
    {
        if(preg_match('/Failed opening required.*$/', $error['message']))
        {
            $error_file_path=$error['file'];
            $error_file_path=wpvivid_transfer_path($error_file_path);

            $mu_path = wpvivid_transfer_path(WPMU_PLUGIN_DIR);
            if(strpos($error_file_path,$mu_path)!==false)
            {
                @wp_delete_file($error_file_path);
                $restore_task=get_option('wpvivid_restore_task',array());

                $restore_task['status']='error';
                $restore_task['error']=$error['message'];
                $restore_task['error_mu_require_file']=$error['file'];
                update_option('wpvivid_restore_task',$restore_task);
            }
        }
    }

    die();
}

