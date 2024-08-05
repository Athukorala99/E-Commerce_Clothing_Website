<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wpvivid.com
 * @since      0.9.1
 *
 * @package    wpvivid
 * @subpackage wpvivid/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.9.1
 * @package    wpvivid
 * @subpackage wpvivid/includes
 * @author     wpvivid team
 */
if (!defined('WPVIVID_PLUGIN_DIR')){
    die;
}

class WPvivid
{

	protected $plugin_name;

	protected $version;

    public $wpvivid_log;
    public $wpvivid_download_log;

    public $current_task;

    public $updater;

    public $remote_collection;

    public $function_realize;

    public $end_shutdown_function;

    public $restore_data;

    public $migrate;
    public $backup_uploader;

    public $admin;

    public $interface_mainwp;

    public $staging;

    public $backup2;

	public function __construct()
    {
        $this->version = WPVIVID_PLUGIN_VERSION;

		$this->plugin_name = WPVIVID_PLUGIN_SLUG;

		$this->end_shutdown_function = false;

		$this->restore_data=false;
		$this->remote_collection=false;
        add_action('plugins_loaded', array($this, 'load_remote_storage'),10);

        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-setting.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-tools.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-schedule.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-taskmanager.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-backuplist.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/staging/class-wpvivid-staging.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-public-interface.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-crypt.php';

        if(!$this->wpvivid_check_staging_pro_active())
        {
            $this->staging=new WPvivid_Staging_Free();
        }

        $this->init_cron();

        include_once WPVIVID_PLUGIN_DIR . '/includes/new_backup/class-wpvivid-backup2.php';
        $this->backup2=new WPvivid_Backup_2();

        include_once WPVIVID_PLUGIN_DIR . '/includes/new_backup/class-wpvivid-restore2.php';
        new WPvivid_Restore_2();

        include_once WPVIVID_PLUGIN_DIR . '/includes/customclass/class-wpvivid-send-to-site.php';
        new WPvivid_Send_to_site();

        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-log.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-error-log.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-zipclass.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-backup.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-upload.php';

        $this->wpvivid_log=new WPvivid_Log();
        $this->wpvivid_download_log=new WPvivid_Log();

        add_action('init', array($this, 'init_pclzip_tmp_folder'));

		if(is_admin())
        {
            $this->load_admin();
            $this->set_locale();
        }

        add_filter('wpvivid_get_oldest_backup_ids', array($this, 'get_oldest_backup_ids'), 10, 2);
        add_filter('wpvivid_check_backup_completeness', array($this, 'check_backup_completeness'), 10, 2);

		/*
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-log.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-error-log.php';

        $this->load_dependencies();

        $this->wpvivid_log=new WPvivid_Log();
        $this->wpvivid_download_log=new WPvivid_Log();
		//Load dependent files


        //A flag to determine whether plugin had been initialized
		$init=get_option('wpvivid_init', 'not init');
		if($init=='not init')
        {
            //Initialization settings
            WPvivid_Setting::init_option();
            WPvivid_Setting::update_option('wpvivid_init','init');
        }

        $wpvivid_remote_init=get_option('wpvivid_remote_init', 'not init');
		if($wpvivid_remote_init=='not init'){
            $this->init_remote_option();
            WPvivid_Setting::update_option('wpvivid_remote_init','init');
        }
		*/

	}

	public function load_admin()
    {
        require_once WPVIVID_PLUGIN_DIR . '/admin/class-wpvivid-admin.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-tab-page-container.php' ;
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-downloader.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-mail-report.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-function-realize.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-backup-uploader.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-migrate.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-db-method.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-additional-db-method.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-export-import.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-exporter.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-importer.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-interface-mainwp.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/upload-cleaner/class-wpvivid-uploads-cleaner.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/snapshot/class-wpvivid-snapshot.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-restore-data.php';

        $this->function_realize=new WPvivid_Function_Realize();
        $this->migrate=new WPvivid_Migrate();
        $this->backup_uploader=new Wpvivid_BackupUploader();
        $this->interface_mainwp = new WPvivid_Interface_MainWP();

        $export_import = new WPvivid_Export_Import();
        $cleaner=new WPvivid_Uploads_Cleaner();

        new WPvivid_Snapshot_Ex();

        $this->admin = new WPvivid_Admin($this->get_plugin_name(), $this->get_version());
        add_filter('wpvivid_pre_add_remote',array($this, 'pre_add_remote'),10,2);
        add_filter('wpvivid_get_log_list', array( $this, 'wpvivid_get_log_list' ), 10);
        add_action('wpvivid_get_log_list_output', array( $this, 'wpvivid_get_log_list_output' ));
        //add_action('plugins_loaded', array($this, 'load_remote_storage'),10);

        $this->load_ajax_hook_for_admin();

        add_filter('wpvivid_add_backup_list', array( $this, 'wpvivid_add_backup_list' ), 10, 3);
        add_action('wpvivid_add_backup_list_output', array( $this, 'wpvivid_add_backup_list_output' ), 10);
        add_filter('wpvivid_add_remote_storage_list', array( $this, 'wpvivid_add_remote_storage_list' ), 10);
        add_filter('wpvivid_schedule_add_remote_pic', array( $this, 'wpvivid_schedule_add_remote_pic' ), 10);
        add_filter('wpvivid_get_remote_directory', array( $this, 'wpvivid_get_remote_directory' ), 10);

        add_filter('wpvivid_get_last_backup_message', array( $this, 'wpvivid_get_last_backup_message' ), 10);
        add_action('wpvivid_get_last_backup_message_output', array( $this, 'wpvivid_get_last_backup_message_output' ), 10);
        add_filter('wpvivid_schedule_local_remote', array( $this, 'wpvivid_schedule_local_remote' ), 10);
        add_filter('wpvivid_remote_storage', array( $this, 'wpvivid_remote_storage'), 10);
        add_filter('wpvivid_add_remote_notice', array($this, 'wpvivid_add_remote_notice'), 10, 2);
        add_filter('wpvivid_set_general_setting', array($this, 'wpvivid_set_general_setting'), 10, 3);

        add_action('wpvivid_handle_backup_succeed',array($this,'wpvivid_handle_backup_succeed'),10);
        add_action('wpvivid_handle_upload_succeed',array($this,'wpvivid_handle_backup_succeed'),10);

        add_action('wpvivid_handle_upload_succeed',array($this,'wpvivid_mark_task'),20);
        add_action('wpvivid_handle_backup_succeed',array($this,'wpvivid_mark_task'),20);

        add_action('wpvivid_handle_backup_failed',array($this,'wpvivid_handle_backup_failed'),9, 2);

        add_action('wpvivid_handle_upload_succeed',array($this,'wpvivid_deal_upload_succeed'),9);

        add_action('wpvivid_handle_backup_failed',array($this,'wpvivid_mark_task'),20);

        add_action('wpvivid_before_setup_page',array($this,'clean_cache'));
        add_filter('wpvivid_check_type_database', array($this, 'wpvivid_check_type_database'), 10, 2);
        add_filter('wpvivid_set_mail_subject', array($this, 'set_mail_subject'), 10, 2);
        add_filter('wpvivid_set_mail_body', array($this, 'set_mail_body'), 10, 2);

        add_filter('wpvivid_get_mainwp_sync_data', array($this, 'get_mainwp_sync_data'), 10);
        //
        add_filter('wpvivid_get_zip_object_class_ex',array($this, 'get_zip_object_class'));
    }

	public function init_cron()
    {
        $schedule=new WPvivid_Schedule();
        //add_action(WPVIVID_MAIN_SCHEDULE_EVENT,array( $this,'main_schedule'));
        add_action(WPVIVID_RESUME_SCHEDULE_EVENT,array( $this,'resume_schedule'));
        add_action(WPVIVID_CLEAN_BACKING_UP_DATA_EVENT,array($this,'clean_backing_up_data_event'));
        add_action(WPVIVID_CLEAN_BACKUP_RECORD_EVENT,array($this,'clean_backup_record_event'));
        //add_clean_event
        add_action(WPVIVID_TASK_MONITOR_EVENT,array( $this,'task_monitor'));
        add_filter('cron_schedules',array( $schedule,'wpvivid_cron_schedules'),99);
        add_filter('wpvivid_schedule_time', array($schedule, 'output'));
    }

	private function load_dependencies()
    {

        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-taskmanager.php';

        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-downloader.php';

        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-mail-report.php';


        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-function-realize.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-upload.php';

        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-backup-uploader.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-crypt.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-migrate.php';

        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-db-method.php';

        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-public-interface.php';

        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-additional-db-method.php';

        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-export-import.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-exporter.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-importer.php';

        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-interface-mainwp.php';

        include_once WPVIVID_PLUGIN_DIR . '/includes/upload-cleaner/class-wpvivid-uploads-cleaner.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/staging/class-wpvivid-staging.php';

        include_once WPVIVID_PLUGIN_DIR . '/includes/snapshot/class-wpvivid-snapshot.php';

        $this->function_realize=new WPvivid_Function_Realize();
        $this->migrate=new WPvivid_Migrate();
        $this->backup_uploader=new Wpvivid_BackupUploader();
        $this->interface_mainwp = new WPvivid_Interface_MainWP();
        $send_to_site=new WPvivid_Send_to_site();
        $export_import = new WPvivid_Export_Import();
        $cleaner=new WPvivid_Uploads_Cleaner();

        if(!$this->wpvivid_check_staging_pro_active())
        {
            $this->staging=new WPvivid_Staging_Free();
        }

        new WPvivid_Snapshot_Ex();

        include_once WPVIVID_PLUGIN_DIR . '/includes/new_backup/class-wpvivid-backup2.php';
        $this->backup2=new WPvivid_Backup_2();

        include_once WPVIVID_PLUGIN_DIR . '/includes/new_backup/class-wpvivid-restore2.php';
        new WPvivid_Restore_2();
	}

	public function wpvivid_check_staging_pro_active()
    {
        if ( ! function_exists( 'is_plugin_active' ) )
        {
            include_once(ABSPATH.'wp-admin/includes/plugin.php');
        }
        if(is_plugin_active('wpvivid-staging/wpvivid-staging.php'))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

	public function init_pclzip_tmp_folder()
    {
        if (!defined('PCLZIP_TEMPORARY_DIR')) {
            $backupdir=WPvivid_Setting::get_backupdir();
            define( 'PCLZIP_TEMPORARY_DIR', WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backupdir.DIRECTORY_SEPARATOR );
        }
    }

    public function load_remote_storage()
    {
        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-remote-collection.php';
        $this->remote_collection=new WPvivid_Remote_collection();
    }

	private function set_locale()
    {
        require_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-i18n.php';
		$plugin_i18n = new WPvivid_i18n();
        add_action('plugins_loaded',array( $plugin_i18n,'load_plugin_textdomain'));
	}

    public function pre_add_remote($remote,$id)
    {
        unset($remote['default']);
        return $remote;
    }

    public function wpjam_pre_update_option_cache($value, $option)
    {
        wp_cache_delete('notoptions', 'options');
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete($option, 'options');
        return $value;
    }

    public function load_ajax_hook_for_admin()
    {
        //Add remote storage
        add_action('wp_ajax_wpvivid_add_remote',array( $this,'add_remote'));
        //Delete remote storage
        add_action('wp_ajax_wpvivid_delete_remote',array( $this,'delete_remote'));
        //Retrieve remote storage
        add_action('wp_ajax_wpvivid_retrieve_remote',array( $this,'retrieve_remote'));
        //Edit remote storage
        add_action('wp_ajax_wpvivid_edit_remote',array( $this,'edit_remote'));
        //List exist remote
        add_action('wp_ajax_wpvivid_list_remote',array( $this,'list_remote'));
        //Test remote connection
        add_action('wp_ajax_wpvivid_test_remote_connection',array( $this,'test_remote_connection'));
        //Start backup
        add_action('wp_ajax_wpvivid_prepare_backup',array( $this,'prepare_backup'));
        add_action('wp_ajax_wpvivid_delete_ready_task',array($this,'delete_ready_task'));
        add_action('wp_ajax_wpvivid_backup_now',array( $this,'backup_now'));
        //Cancel backup
        add_action('wp_ajax_wpvivid_backup_cancel',array( $this,'backup_cancel'));
        //List backup record
        add_action('wp_ajax_wpvivid_get_backup_list',array( $this,'get_backup_list'));
        //View backup record log file
        add_action('wp_ajax_wpvivid_view_backup_log',array( $this,'view_backup_log'));
        //View log file of the backup task
        add_action('wp_ajax_wpvivid_view_backup_task_log',array( $this,'view_backup_task_log'));
        //List all logs
        add_action('wp_ajax_wpvivid_get_log_list',array( $this,'get_log_list'));
        //View logs
        add_action('wp_ajax_wpvivid_view_log',array( $this,'view_log'));
        //Prepare download backup files
        add_action('wp_ajax_wpvivid_prepare_download_backup',array( $this,'prepare_download_backup'));
        //Get download progress
        add_action('wp_ajax_wpvivid_get_download_progress',array($this,'get_download_progress'));
        //Download backup from site
        add_action('wp_ajax_wpvivid_download_backup',array( $this,'download_backup'));
        //Delete backup record
        add_action('wp_ajax_wpvivid_delete_backup',array( $this,'delete_backup'));
        //Delete backup records
        add_action('wp_ajax_wpvivid_delete_backup_array',array( $this,'delete_backup_array'));
        //
        add_action('wp_ajax_wpvivid_init_download_page',array( $this,'init_download_page'));
        //Download backuplist change page
        add_action('wp_ajax_wpvivid_get_download_page_ex',array($this,'get_download_page_ex'));
        //Set security lock for backup record
        add_action('wp_ajax_wpvivid_set_security_lock',array( $this,'set_security_lock'));
        //Delete task
        add_action('wp_ajax_wpvivid_delete_task',array( $this,'delete_task'));
        //Get backup schedule data
        add_action('wp_ajax_wpvivid_get_schedule',array( $this,'get_schedule'));
        //Get last backup information
        add_action('wp_ajax_wpvivid_get_last_backup',array( $this,'get_last_backup'));
        //Get settings
        add_action('wp_ajax_wpvivid_get_setting',array( $this,'get_setting'));
        add_action('wp_ajax_wpvivid_get_general_setting',array( $this,'get_general_setting'));
        //Update settings
        add_action('wp_ajax_wpvivid_update_setting',array( $this,'update_setting'));
        add_action('wp_ajax_wpvivid_set_general_setting',array( $this,'set_general_setting'));
        add_action('wp_ajax_wpvivid_set_schedule',array( $this,'set_schedule' ));
        //Export settings
        add_action('wp_ajax_wpvivid_export_setting',array( $this,'export_setting'));
        //Import settings
        add_action('wp_ajax_wpvivid_import_setting',array( $this,'import_setting'));
        //Send test mail
        add_action('wp_ajax_wpvivid_test_send_mail',array( $this,'test_send_mail'));
        //Send debug mail
        add_action('wp_ajax_wpvivid_create_debug_package',array( $this,'create_debug_package'));
        //Get backup local storage path
        add_action('wp_ajax_wpvivid_get_dir',array( $this,'get_dir'));
        //Get Web-server disk space in use
        add_action('wp_ajax_wpvivid_junk_files_info',array( $this,'junk_files_info'));
        add_action('wp_ajax_wpvivid_clean_local_storage',array( $this,'clean_local_storage'));
        add_action('wp_ajax_wpvivid_get_out_of_date_info',array($this,'get_out_of_date_info'));
        add_action('wp_ajax_wpvivid_clean_out_of_date_backup',array($this,'clean_out_of_date_backup'));
        //Prepare backup files for restore
        add_action('wp_ajax_wpvivid_prepare_restore',array( $this,'prepare_restore'));
        //Download backup files for restore
        add_action('wp_ajax_wpvivid_download_restore',array( $this,'download_restore_file'));
        //
        add_action('wp_ajax_wpvivid_init_restore_page',array( $this,'init_restore_page'));
        //
        add_action('wp_ajax_wpvivid_delete_last_restore_data',array( $this,'delete_last_restore_data'));
        //
        //start restore
        add_action('wp_ajax_wpvivid_restore',array( $this,'restore'));
        add_action('wp_ajax_wpvivid_get_restore_progress',array( $this,'get_restore_progress'));
        add_action('wp_ajax_wpvivid_get_download_restore_progress',array( $this,'download_restore_progress'));
        add_action('wp_ajax_wpvivid_check_download_has_zero_date', array( $this, 'download_check_has_zero_date' ));
        //When restoring the database use wp_ajax_nopriv_
        add_action('wp_ajax_nopriv_wpvivid_restore',array( $this,'restore'));
        add_action('wp_ajax_nopriv_wpvivid_get_restore_progress',array( $this,'get_restore_progress'));
        add_action('wp_ajax_wpvivid_list_tasks',array( $this,'list_tasks'));
        //View last backup record log
        add_action('wp_ajax_wpvivid_read_last_backup_log',array( $this,'read_last_backup_log'));
        //Set default remote storage when backing up
        add_action('wp_ajax_wpvivid_set_default_remote_storage',array( $this,'set_default_remote_storage'));
        //Get default remote storage when backing up
        add_action('wp_ajax_wpvivid_get_default_remote_storage',array( $this,'get_default_remote_storage'));
        add_action('wp_ajax_wpvivid_need_review',array( $this,'need_review'));
        add_action('wp_ajax_wpvivid_send_debug_info',array($this,'wpvivid_send_debug_info'));
        add_action('wp_ajax_wpvivid_get_ini_memory_limit',array($this,'get_ini_memory_limit'));
        add_action('wp_ajax_wpvivid_get_restore_file_is_migrate', array($this, 'get_restore_file_is_migrate'));

        add_action('wp_ajax_wpvivid_check_remote_alias_exist', array($this, 'check_remote_alias_exist'));
        add_action('wp_ajax_wpvivid_task_monitor', array($this, 'task_monitor_ex'));
        add_action('wp_ajax_wpvivid_amazons3_notice', array($this, 'amazons3_notice'));

        add_action('wp_ajax_wpvivid_hide_mainwp_tab_page', array($this, 'hide_mainwp_tab_page'));
        add_action('wp_ajax_wpvivid_hide_wp_cron_notice', array($this, 'hide_wp_cron_notice'));
        //wpvivid_task_monitor

        //download backup by mainwp
        add_action('wp_ajax_wpvivid_download_backup_mainwp', array($this, 'download_backup_mainwp'));
    }

	public function get_plugin_name()
    {
		return $this->plugin_name;
	}

	public function get_version()
    {
        return $this->version;
    }

    /**
     * Prepare backup include what you want to backup,where you want to store.
     *
     *When prepare backup finished,you can use backup_now start a backup task.
     *
     * @since 0.9.1
     */
    public function prepare_backup()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        $this->end_shutdown_function=false;
        register_shutdown_function(array($this,'deal_prepare_shutdown_error'));
        try
        {
            if(isset($_POST['backup'])&&!empty($_POST['backup']))
            {
                $json = sanitize_text_field($_POST['backup']);
                $json = stripslashes($json);
                $backup_options = json_decode($json, true);
                if (is_null($backup_options))
                {
                    $this->end_shutdown_function=true;
                    die();
                }

                $backup_options = apply_filters('wpvivid_custom_backup_options', $backup_options);

                if(!isset($backup_options['type']))
                {
                    $backup_options['type']='Manual';
                    $backup_options['action']='backup';
                }

                $ret = $this->check_backup_option($backup_options, $backup_options['type']);
                if($ret['result']!=WPVIVID_SUCCESS)
                {
                    $this->end_shutdown_function=true;
                    echo wp_json_encode($ret);
                    die();
                }

                $ret=$this->pre_backup($backup_options);
                if($ret['result']=='success')
                {
                    //Check the website data to be backed up
                    /*
                    $ret['check']=$this->check_backup($ret['task_id'],$backup_options);
                    if(isset($ret['check']['result']) && $ret['check']['result'] == WPVIVID_FAILED)
                    {
                        $this->end_shutdown_function=true;
                        echo wp_json_encode(array('result' => WPVIVID_FAILED,'error' => $ret['check']['error']));
                        die();
                    }*/

                    $html = '';
                    $html = apply_filters('wpvivid_add_backup_list', $html);
                    $ret['html'] = $html;
                }
                $this->end_shutdown_function=true;
                echo wp_json_encode($ret);
                die();
            }
        }
        catch (Exception $error)
        {
            $this->end_shutdown_function=true;
            $ret['result']='failed';
            $message = 'An exception has occurred. class:'.get_class($error).';msg:'.$error->getMessage().';code:'.$error->getCode().';line:'.$error->getLine().';in_file:'.$error->getFile().';';
            $ret['error'] = $message;
            $id=uniqid('wpvivid-');
            $log_file_name=$id.'_backup';

            if(!class_exists('WPvivid_Log'))
            {
                include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-log.php';
            }
            $log=new WPvivid_Log();
            $log->CreateLogFile($log_file_name,'no_folder','backup');
            $log->WriteLog($message,'notice');
            $log->CloseFile();
            WPvivid_error_log::create_error_log($log->log_file);
            error_log($message);
            echo wp_json_encode($ret);
            die();
        }
    }

    public function deal_prepare_shutdown_error()
    {
        if($this->end_shutdown_function==false)
        {
            $last_error = error_get_last();
            if (!empty($last_error) && !in_array($last_error['type'], array(E_NOTICE,E_WARNING,E_USER_NOTICE,E_USER_WARNING,E_DEPRECATED), true)) {
                $error = $last_error;
            } else {
                $error = false;
            }
            $ret['result'] = 'failed';
            if ($error === false) {
                $ret['error'] = 'unknown Error';
            } else {
                $ret['error'] = 'type: '. $error['type'] . ', ' . $error['message'] . ' file:' . $error['file'] . ' line:' . $error['line'];
                error_log($ret['error']);
            }
            $id = uniqid('wpvivid-');
            $log_file_name = $id . '_backup';
            if(!class_exists('WPvivid_Log'))
            {
                include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-log.php';
            }
            $log = new WPvivid_Log();
            $log->CreateLogFile($log_file_name, 'no_folder', 'backup');
            $log->WriteLog($ret['error'], 'notice');
            $log->CloseFile();
            WPvivid_error_log::create_error_log($log->log_file);
            echo wp_json_encode($ret);
            die();
        }
    }

    public function check_backup_option($data, $backup_method = 'Manual')
    {
        $ret['result']=WPVIVID_SUCCESS;
        add_filter('wpvivid_check_backup_options_valid',array($this, 'check_backup_options_valid'),10,3);
        $ret=apply_filters('wpvivid_check_backup_options_valid',$ret,$data,$backup_method);
        return $ret;
    }

    public function check_backup_options_valid($ret,$data,$backup_method)
    {
        $ret['result']=WPVIVID_FAILED;
        if(!isset($data['backup_files']))
        {
            $ret['error']=__('A backup type is required.', 'wpvivid-backuprestore');
            return $ret;
        }

        $data['backup_files']=sanitize_text_field($data['backup_files']);

        if(empty($data['backup_files']))
        {
            $ret['error']=__('A backup type is required.', 'wpvivid-backuprestore');
            return $ret;
        }

        if(!isset($data['local']) && !isset($data['remote']))
        {
            $ret['error']=__('Choose at least one storage location for backups.', 'wpvivid-backuprestore');
            return $ret;
        }

        $data['local']=sanitize_text_field($data['local']);
        $data['remote']=sanitize_text_field($data['remote']);

        if(empty($data['local']) && empty($data['remote']))
        {
            $ret['error']=__('Choose at least one storage location for backups.', 'wpvivid-backuprestore');
            return $ret;
        }

        if($backup_method == 'Manual')
        {
            if ($data['remote'] === '1')
            {
                $remote_storage = WPvivid_Setting::get_remote_options();
                if ($remote_storage == false)
                {
                    $ret['error'] = __('There is no default remote storage configured. Please set it up first.', 'wpvivid-backuprestore');
                    return $ret;
                }
            }
        }
        $ret['result']=WPVIVID_SUCCESS;
        return $ret;
    }

    /**
     * Delete tasks had [ready] status.
     *
     *When prepare backup go wrong,may retain some task we don't need.Delete them.
     *
     * @since 0.9.3
     */
    public function delete_ready_task()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            WPvivid_taskmanager::delete_ready_task();
            $ret['result'] = 'success';
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        echo wp_json_encode($ret);
        die();
    }
    /**
     * Start a backup task init by prepare_backup.
     *
     * @since 0.9.1
     */
    public function backup_now()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (!isset($_POST['task_id']) || empty($_POST['task_id']) || !is_string($_POST['task_id'])) {
                $ret['result'] = 'failed';
                $ret['error'] = __('Error occurred while parsing the request data. Please try to run backup again.', 'wpvivid-backuprestore');
                echo wp_json_encode($ret);
                die();
            }
            $task_id = sanitize_key($_POST['task_id']);

            //Start backup site
            if (WPvivid_taskmanager::is_tasks_backup_running()) {
                $ret['result'] = 'failed';
                $ret['error'] = __('A task is already running. Please wait until the running task is complete, and try again.', 'wpvivid-backuprestore');
                echo wp_json_encode($ret);
                die();
            }
            //flush buffer
            $this->flush($task_id);

            $task_msg = WPvivid_taskmanager::get_task($task_id);
            $this->update_last_backup_time($task_msg);

            $this->backup($task_id);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }
    /**
     * View backup record logs.
     *
     * @since 0.9.1
     */
    public function view_backup_log()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try
        {
            if (isset($_POST['id']) && !empty($_POST['id']) && is_string($_POST['id']))
            {
                $backup_id = sanitize_key($_POST['id']);
                $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
                if (!$backup)
                {
                    $json['result'] = 'failed';
                    $json['error'] = __('Retrieving the backup information failed while showing log. Please try again later.', 'wpvivid-backuprestore');
                    echo wp_json_encode($json);
                    die();
                }

                if (!file_exists($backup['log']))
                {
                    $json['result'] = 'failed';
                    $json['error'] = __('The log not found.', 'wpvivid-backuprestore');
                    echo wp_json_encode($json);
                    die();
                }

                $file = fopen($backup['log'], 'r');

                if (!$file)
                {
                    $json['result'] = 'failed';
                    $json['error'] = __('Unable to open the log file.', 'wpvivid-backuprestore');
                    echo wp_json_encode($json);
                    die();
                }

                $buffer = '';
                while (!feof($file))
                {
                    $buffer .= fread($file, 1024);
                }
                fclose($file);

                $json['result'] = 'success';
                $json['data'] = $buffer;
                echo wp_json_encode($json);
            } else {
                $json['result'] = 'failed';
                $json['error'] = __('Reading the log failed. Please try again.', 'wpvivid-backuprestore');
                echo wp_json_encode($json);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }
    /**
     * View last backup record logs.
     *
     * @since 0.9.1
     */
    public function read_last_backup_log()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (!isset($_POST['log_file_name']) || empty($_POST['log_file_name']) || !is_string($_POST['log_file_name'])) {
                $json['result'] = 'failed';
                $json['error'] = __('Reading the log failed. Please try again.', 'wpvivid-backuprestore');
                echo wp_json_encode($json);
                die();
            }
            $option = sanitize_text_field($_POST['log_file_name']);
            $log_file_name = $this->wpvivid_log->GetSaveLogFolder() . $option . '_log.txt';

            if (!file_exists($log_file_name)) {
                $json['result'] = 'failed';
                $json['error'] = __('The log not found.', 'wpvivid-backuprestore');
                echo wp_json_encode($json);
                die();
            }

            $file = fopen($log_file_name, 'r');

            if (!$file) {
                $json['result'] = 'failed';
                $json['error'] = __('Unable to open the log file.', 'wpvivid-backuprestore');
                echo wp_json_encode($json);
                die();
            }

            $buffer = '';
            while (!feof($file)) {
                $buffer .= fread($file, 1024);
            }
            fclose($file);

            $json['result'] = 'success';
            $json['data'] = $buffer;
            echo wp_json_encode($json);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }
    /**
     * View logs of the backup task.
     *
     * @since 0.9.1
     */
    public function view_backup_task_log()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (isset($_POST['id']) && !empty($_POST['id']) && is_string($_POST['id'])) {
                $backup_task_id = sanitize_key($_POST['id']);
                $option = WPvivid_taskmanager::get_task_options($backup_task_id, 'log_file_name');
                if (!$option) {
                    $json['result'] = 'failed';
                    $json['error'] = __('Retrieving the backup information failed while showing log. Please try again later.', 'wpvivid-backuprestore');
                    echo wp_json_encode($json);
                    die();
                }

                $log_file_name = $this->wpvivid_log->GetSaveLogFolder() . $option . '_log.txt';

                if (!file_exists($log_file_name)) {
                    $json['result'] = 'failed';
                    $json['error'] = __('The log not found.', 'wpvivid-backuprestore');
                    echo wp_json_encode($json);
                    die();
                }

                $file = fopen($log_file_name, 'r');

                if (!$file) {
                    $json['result'] = 'failed';
                    $json['error'] = __('Unable to open the log file.', 'wpvivid-backuprestore');
                    echo wp_json_encode($json);
                    die();
                }

                $buffer = '';
                while (!feof($file)) {
                    $buffer .= fread($file, 1024);
                }
                fclose($file);

                $json['result'] = 'success';
                $json['data'] = $buffer;
                echo wp_json_encode($json);
            } else {
                $json['result'] = 'failed';
                $json['error'] = __('Reading the log failed. Please try again.', 'wpvivid-backuprestore');
                echo wp_json_encode($json);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }
    /**
     * Cancel a backup task.
     *
     * @since 0.9.1
     */
    public function backup_cancel()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            /*if (isset($_POST['task_id']) && !empty($_POST['task_id']) && is_string($_POST['task_id'])) {
                $task_id = sanitize_key($_POST['task_id']);
                $json = $this->function_realize->_backup_cancel($task_id);
                echo wp_json_encode($json);
            }*/
            $json = $this->function_realize->_backup_cancel();
            echo wp_json_encode($json);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function main_schedule($schedule_id='')
    {
        //get backup options
        do_action('wpvivid_set_current_schedule_id', $schedule_id);
        $this->end_shutdown_function=false;
        register_shutdown_function(array($this,'deal_prepare_shutdown_error'));
        $schedule_options=WPvivid_Schedule::get_schedule($schedule_id);
        if(empty($schedule_options))
        {
            $this->end_shutdown_function=true;
            die();
        }
        try
        {
            $schedule_options['backup']['local'] = strval($schedule_options['backup']['local']);
            $schedule_options['backup']['remote'] = strval($schedule_options['backup']['remote']);
            $schedule_options['backup']['ismerge'] = strval($schedule_options['backup']['ismerge']);
            $schedule_options['backup']['lock'] = strval($schedule_options['backup']['lock']);
            $ret = $this->check_backup_option($schedule_options['backup'], 'Cron');
            if ($ret['result'] != WPVIVID_SUCCESS)
            {
                $this->end_shutdown_function=true;
                //echo wp_json_encode($ret);
                die();
            }

            if(!isset($schedule_options['backup']['type']))
            {
                $schedule_options['backup']['type']='Cron';
                $schedule_options['backup']['action']='backup';
            }

            $ret = $this->pre_backup($schedule_options['backup']);
            if ($ret['result'] == 'success') {
                //Check the website data to be backed up.
                //$this->check_backup($ret['task_id'], $schedule_options['backup']);
                //flush buffer
                $this->flush($ret['task_id']);
                //start backup task.

                $task_msg = WPvivid_taskmanager::get_task($ret['task_id']);
                $this->update_last_backup_time($task_msg);

                $this->backup($ret['task_id']);
            }
            $this->end_shutdown_function=true;
            die();
        }
        catch (Exception $error)
        {
            $this->end_shutdown_function=true;
            $ret['result']='failed';
            $message = 'An exception has occurred. class:'.get_class($error).';msg:'.$error->getMessage().';code:'.$error->getCode().';line:'.$error->getLine().';in_file:'.$error->getFile().';';
            $ret['error'] = $message;
            $id=uniqid('wpvivid-');
            $log_file_name=$id.'_backup';
            if(!class_exists('WPvivid_Log'))
            {
                include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-log.php';
            }
            $log=new WPvivid_Log();
            $log->CreateLogFile($log_file_name,'no_folder','backup');
            $log->WriteLog($message,'notice');
            $log->CloseFile();
            WPvivid_error_log::create_error_log($log->log_file);
            error_log($message);
            //echo wp_json_encode($ret);
            die();
        }
    }
    /**
     * Resume backup schedule.
     *
     * Resume a backup task.
     *
     * @var string $task_id backup task id
     *
     * @since 0.9.1
     */
    public function resume_schedule($task_id='0')
    {
        if($task_id=='0')
        {
            die();
        }

        $task=WPvivid_taskmanager::get_task($task_id);

        if(!$task)
        {
            die();
        }

        if (WPvivid_taskmanager::is_tasks_backup_running())
        {
            $ret['result'] = 'failed';
            $ret['error'] = __('A task is already running. Please wait until the running task is complete, and try again.', 'wpvivid-backuprestore');
            echo wp_json_encode($ret);
            die();
        }

        $doing=WPvivid_taskmanager::get_backup_main_task_progress($task_id);
        if($doing=='backup')
        {
            //flush buffer
            $this->flush($task_id);
            $this->backup($task_id);
        }
        else if($doing=='upload')
        {
            //flush buffer
            $this->flush($task_id);
            $this->upload($task_id);
        }
        //resume backup

        die();
    }

    /**
     * Clean backing up data schedule.
     *
     * @var string $task_id backup task id
     *
     * @since 0.9.1
     */
    public function clean_backing_up_data_event($task_id)
    {
        $tasks=WPvivid_Setting::get_option('clean_task');
        if(isset($tasks[$task_id])){
            $task=$tasks[$task_id];
            unset($tasks[$task_id]);
        }
        WPvivid_Setting::update_option('clean_task',$tasks);

        if(!empty($task))
        {
            $backup=new WPvivid_Backup(false,$task);
            $backup->clean_backup();

            $files=array();

            if($task['options']['remote_options']!==false)
            {
                $backup_files=$backup->task->get_need_cleanup_files(true);
                foreach ($backup_files as $file)
                {
                    $files[]=basename($file);
                }
                if(!empty($files))
                {
                    $upload=new WPvivid_Upload();
                    $upload->clean_remote_backup($task['options']['remote_options'],$files);
                }
            }
            //clean upload
        }
    }
    /**
     * Clean backup record schedule.
     *
     * @var string $task_id backup task id
     *
     * @since 0.9.1
     */
    public function clean_backup_record_event($backup_id)
    {
        $tasks=WPvivid_Setting::get_option('clean_task');
        $backup=$tasks[$backup_id];
        unset($tasks[$backup_id]);
        WPvivid_Setting::update_option('clean_task',$tasks);

        if(!empty($backup))
        {
            $backup_item=new WPvivid_Backup_Item($backup);
            $backup_item->cleanup_local_backup();
            $backup_item->cleanup_remote_backup();
        }
    }
    /**
     * Clean oldest backup record.
     *
     * @var string $task_id backup task id
     *
     * @since 0.9.1
     */
    public function clean_oldest_backup()
    {
        $oldest_ids=array();
        $oldest_ids=apply_filters('wpvivid_get_oldest_backup_ids',$oldest_ids,false);
        if($oldest_ids!==false)
        {
            foreach ($oldest_ids as $oldest_id)
            {
                $this->add_clean_backup_record_event($oldest_id);
                WPvivid_Backuplist::delete_backup($oldest_id);
            }
        }
    }

    public function get_oldest_backup_ids($oldest_ids,$multiple)
    {
        if($multiple)
        {
            $count=WPvivid_Setting::get_max_backup_count();

            $oldest_ids = WPvivid_Backuplist::get_out_of_date_backuplist($count);

            return $oldest_ids;
        }
        else
        {
            $count=WPvivid_Setting::get_max_backup_count();
            $oldest_id=WPvivid_Backuplist::check_backuplist_limit($count);
            $oldest_ids=array();
            $oldest_ids[]=$oldest_id;
            return $oldest_ids;
        }
    }

    public function check_backup_completeness($check_res, $task_id){
        $task=WPvivid_taskmanager::get_task($task_id);
        if(isset($task['setting']['is_merge']) && $task['setting']['is_merge'] == '1')
        {
            foreach ($task['jobs'] as $job_info)
            {
                if($job_info['backup_type'] === 'backup_merge')
                {
                    if(isset($job_info['zip_file']) && !empty($job_info['zip_file']))
                    {
                        foreach ($job_info['zip_file'] as $zip_file_name => $zip_file_info)
                        {
                            if(!$this->check_backup_file_json($zip_file_name)){
                                $check_res = false;
                            }
                        }
                    }
                }
            }
        }
        else
        {
            foreach ($task['jobs'] as $job_info)
            {
                if(isset($job_info['zip_file']) && !empty($job_info['zip_file']))
                {
                    foreach ($job_info['zip_file'] as $zip_file_name => $zip_file_info)
                    {
                        if(!$this->check_backup_file_json($zip_file_name)){
                            $check_res = false;
                        }
                    }
                }
            }
        }
        return $check_res;
    }

    public function get_mainwp_sync_data($information)
    {
        $data['setting']['wpvivid_compress_setting']=get_option('wpvivid_compress_setting');
        $data['setting']['wpvivid_local_setting']=get_option('wpvivid_local_setting');
        $data['setting']['wpvivid_common_setting']=get_option('wpvivid_common_setting');
        $data['setting']['wpvivid_email_setting']=get_option('wpvivid_email_setting');
        $data['setting']['cron_backup_count']=get_option('cron_backup_count');
        $data['schedule']=get_option('wpvivid_schedule_setting');
        $data['remote']['upload']=get_option('wpvivid_upload_setting');
        $data['remote']['history']=get_option('wpvivid_user_history');

        $data['setting_addon'] = $data['setting'];
        $data['setting_addon']['wpvivid_staging_options']=array();
        $data['backup_custom_setting']=array();
        $data['menu_capability']=array();
        $data['white_label_setting']=array();
        $data['incremental_backup_setting']=array();
        $data['last_backup_report']=array();
        $data['schedule_addon']=array();
        $data['time_zone']=false;
        $data['is_pro']=false;
        $data['is_install']=false;
        $data['is_login']=false;
        $data['latest_version']='';
        $data['current_version']='';
        $data['dashboard_version'] = '';
        $data['addons_info'] = array();
        $data=apply_filters('wpvivid_get_wpvivid_info_addon_mainwp', $data);

        $information['syncWPvividSetting']=$data;
        return $information;
    }

    public function check_backup_file_json($file_name){
        if(!class_exists('WPvivid_ZipClass'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-zipclass.php';
        $zip=new WPvivid_ZipClass();

        $general_setting=WPvivid_Setting::get_setting(true, "");
        $backup_folder = $general_setting['options']['wpvivid_local_setting']['path'];
        $backup_path=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backup_folder.DIRECTORY_SEPARATOR;
        $file_path=$backup_path.$file_name;

        $ret=$zip->get_json_data($file_path);
        if($ret['result'] === WPVIVID_SUCCESS) {
            $json=$ret['json_data'];
            $json = json_decode($json, 1);
            if (is_null($json)) {
                return false;
            } else {
                return $json;
            }
        }
        elseif($ret['result'] === WPVIVID_FAILED){
            return false;
        }
    }

    /**
     * Initialization backup task.
     *
     * @var array $backup_options
     * @var string $type
     * @var int $lock
     *
     * @return array
     *
     * @since 0.9.1
     */
    public function pre_backup($backup_options)
    {
        if(apply_filters('wpvivid_need_clean_oldest_backup',true,$backup_options))
        {
            $this->clean_oldest_backup();
        }
        do_action('wpvivid_clean_oldest_backup',$backup_options);

        if(WPvivid_taskmanager::is_tasks_backup_running())
        {
            $ret['result']='failed';
            $ret['error']=__('A task is already running. Please wait until the running task is complete, and try again.', 'wpvivid-backuprestore');
            return $ret;
        }

        $backup=new WPvivid_Backup_Task();
        $ret=$backup->new_backup_task($backup_options,$backup_options['type'],$backup_options['action']);
        return $ret;
    }

    /**
     * start or resume a backup task.
     *
     * @var string $task_id
     *
     * @since 0.9.1
     */
    public function backup($task_id)
    {
        //register shutdown function to catch php fatal error such as script time out and memory limit

        $common_setting = WPvivid_Setting::get_option('wpvivid_common_setting');
        if(isset($common_setting['memory_limit']) && !empty($common_setting['memory_limit'])){
            $memory_limit = $common_setting['memory_limit'];
        }
        else{
            $memory_limit = WPVIVID_MEMORY_LIMIT;
        }
        @ini_set('memory_limit', $memory_limit);
        $this->end_shutdown_function=false;
        register_shutdown_function(array($this,'deal_shutdown_error'),$task_id);
        @ignore_user_abort(true);
        WPvivid_taskmanager::update_backup_task_status($task_id,true,'running');
        $this->current_task=WPvivid_taskmanager::get_task($task_id);
        //start a watch task event
        $this->add_monitor_event($task_id);
        //flush buffer
        //$this->flush($task_id);
        $this->wpvivid_log->OpenLogFile(WPvivid_taskmanager::get_task_options($task_id,'log_file_name'));
        $this->wpvivid_log->WriteLog('Start backing up.','notice');
        $this->wpvivid_log->WriteLogHander();
        //start backup
        try
        {
            $this->wpvivid_check_add_litespeed_server($task_id);
            $backup=new WPvivid_Backup();

            //$backup->clearcache();
            $backup_ret=$backup->backup($task_id);
            $backup->clearcache();

            if($backup_ret['result'] != WPVIVID_SUCCESS)
            {
                $this->wpvivid_log->WriteLog('Backup ends with an error '. $backup_ret['error'], 'error');
            }
            else {
                $this->wpvivid_log->WriteLog('Backup completed.','notice');
            }

            if(!$this->finish_backup_task($task_id))
            {
                $this->end_shutdown_function=true;
                die();
            }
            if(WPvivid_taskmanager::get_task_options($task_id,'remote_options')!=false)
            {
                $this->upload($task_id,false);
            }
        }
        catch (Exception $e)
        {
            //catch error and stop task recording history
            $this->deal_task_error($task_id,'exception',$e);
            $this->wpvivid_log->CloseFile();
            $this->end_shutdown_function=true;
            die();
        }

        $this->end_shutdown_function=true;
        die();
    }

    /**
     * recording finished backup task.
     *
     * @var string $task_id
     *
     * @var array $backup_ret return data of backup
     *
     * @return boolean
     *
     * @since 0.9.1
     */
    private function finish_backup_task($task_id)
    {
        $status=WPvivid_taskmanager::get_backup_task_status($task_id);
        if($status['str']=='running')
        {
            $this->wpvivid_log->WriteLog('Backup succeeded.','notice');

            $check_res = apply_filters('wpvivid_check_backup_completeness', true, $task_id);
            if(!$check_res){
                $task=WPvivid_taskmanager::get_task($task_id);
                $task['status']['error'] = 'We have detected that this backup is either corrupted or incomplete. Please make sure your server disk space is sufficient then create a new backup. In order to successfully back up/restore a website, the amount of free server disk space needs to be at least twice the size of the website';
                do_action('wpvivid_handle_backup_failed',$task, false);
                return false;
            }

            $remote_options=WPvivid_taskmanager::get_task_options($task_id,'remote_options');
            if($remote_options===false)
            {
                $task=WPvivid_taskmanager::update_backup_task_status($task_id,false,'completed');
                do_action('wpvivid_handle_backup_succeed',$task);
            }
            return true;
        }
        else
        {
            $task=WPvivid_taskmanager::get_task($task_id);
            do_action('wpvivid_handle_backup_failed',$task, false);
            return false;
        }
    }

    public function wpvivid_analysis_backup($task)
    {
        if($task['type'] == 'Cron')
        {
            $cron_backup_count = WPvivid_Setting::get_option('cron_backup_count');
            if(empty($cron_backup_count)){
                $cron_backup_count = 0;
            }
            $cron_backup_count++;
            WPvivid_Setting::update_option('cron_backup_count', $cron_backup_count);
            $common_setting = WPvivid_Setting::get_option('wpvivid_common_setting');
            $max_backup_count = $common_setting['max_backup_count'];
            if($cron_backup_count >= $max_backup_count){
                $need_review=WPvivid_Setting::get_option('wpvivid_need_review');
                if($need_review=='not')
                {
                    WPvivid_Setting::update_option('wpvivid_need_review','show');
                    $msg = 'Cheers! The schedule feature of WPvivid Backup plugin seems to be running well. If you found WPvivid Backup plugin helpful, a 5-star rating will motivate us to keep improving the plugin quality.';
                    WPvivid_Setting::update_option('wpvivid_review_msg',$msg);
                }
            }
        }
    }
    /**
     * start upload files to remote.
     *
     * @var string $task_id
     * @var bool $restart
     * @since 0.9.10
     */
    public function upload($task_id,$restart=true)
    {
        $this->end_shutdown_function=false;
        register_shutdown_function(array($this,'deal_shutdown_error'),$task_id);
        @ignore_user_abort(true);
        WPvivid_taskmanager::update_backup_task_status($task_id,$restart,'running',false,0);
        $this->current_task=WPvivid_taskmanager::get_task($task_id);
        //start a watch task event
        $this->add_monitor_event($task_id);
        //flush buffer
        //$this->flush($task_id);
        $this->wpvivid_log->OpenLogFile(WPvivid_taskmanager::get_task_options($task_id,'log_file_name'));
        $this->wpvivid_log->WriteLog('Start upload.','notice');

        $this->set_time_limit($task_id);

        $upload=new WPvivid_Upload();
        $ret=$upload->upload($task_id);
        if($ret['result'] == WPVIVID_SUCCESS)
        {
            $task=WPvivid_taskmanager::update_backup_task_status($task_id,false,'completed');
            do_action('wpvivid_handle_upload_succeed',$task);
        }
        else
        {
            $backup = WPvivid_Backuplist::get_backup_by_id($task_id);
            if($backup!==false)
            {
                $backup['save_local']=1;
                WPvivid_Backuplist::update_backup_option($task_id, $backup);
            }

            $this->wpvivid_log->WriteLog('Uploading the file ends with an error '. $ret['error'], 'error');
            $task=WPvivid_taskmanager::get_task($task_id);
            do_action('wpvivid_handle_backup_failed',$task, false);
        }
        $this->end_shutdown_function=true;
        die();
    }

    function wpvivid_deal_upload_succeed($task)
    {
        $save_local=$task['options']['save_local'];
        if($save_local==0)
        {
            $retain_local = WPvivid_Setting::get_retain_local_status();
            if(!$retain_local) {
                $this->wpvivid_log->WriteLog('Cleaned up local files after uploading to remote storages.', 'notice');
                $backup = new WPvivid_Backup($task['id']);
                $backup->clean_backup();
            }
        }
        $this->wpvivid_log->WriteLog('Upload succeeded.','notice');
        $remote_options=$task['options']['remote_options'];
        $remote_options=apply_filters('wpvivid_set_backup_remote_options',$remote_options,$task['id']);
        WPvivid_Backuplist::update_backup($task['id'],'remote',$remote_options);
    }

    function wpvivid_handle_backup_succeed($task)
    {
        if($task['action'] === 'backup')
        {
            $backup_task=new WPvivid_Backup_Task($task['id']);
            $backup_task->add_new_backup();

            $remote_options = WPvivid_taskmanager::get_task_options($task['id'], 'remote_options');
            if($remote_options != false){
                WPvivid_Backuplist::update_backup($task['id'],'remote', $remote_options);
            }

            $backup_success_count = WPvivid_Setting::get_option('wpvivid_backup_success_count');
            if (empty($backup_success_count))
            {
                $backup_success_count = 0;
            }
            $backup_success_count++;
            WPvivid_Setting::update_option('wpvivid_backup_success_count', $backup_success_count);
            $this->wpvivid_analysis_backup($task);
            $task_msg = WPvivid_taskmanager::get_task($task['id']);
            $this->update_last_backup_task($task_msg);
        }
        WPvivid_Schedule::clear_monitor_schedule($task['id']);

        $backup_task=new WPvivid_Backup_Task($task['id']);
        $res=$backup_task->get_backup_result();
        if(!empty($res['files']))
        {
            if(!class_exists('WPvivid_mail_report'))
                include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-mail-report.php';
            WPvivid_mail_report::send_report_mail($task);
        }
        $this->wpvivid_check_clear_litespeed_rule($task['id']);
    }

    function wpvivid_mark_task($task)
    {
        WPvivid_taskmanager::mark_task($task['id']);
    }

    function wpvivid_handle_backup_failed($task, $need_set_low_resource_mode)
    {
        if($task['action'] === 'backup')
        {
            $backup_error_array = WPvivid_Setting::get_option('wpvivid_backup_error_array');
            if (!isset($backup_error_array) || empty($backup_error_array))
            {
                $backup_error_array = array();
                $backup_error_array['bu_error']['task_id'] = '';
                $backup_error_array['bu_error']['error_msg'] = '';
            }
            if (!array_key_exists($task['id'], $backup_error_array['bu_error']))
            {
                $backup_error_array['bu_error']['task_id'] = $task['id'];
                $backup_error_array['bu_error']['error_msg'] = 'Unknown error.';

                $general_setting=WPvivid_Setting::get_setting(true, "");
                $need_notice = false;
                if(!isset($general_setting['options']['wpvivid_compress_setting']['subpackage_plugin_upload'])){
                    $need_notice = true;
                }
                else{
                    if($general_setting['options']['wpvivid_compress_setting']['subpackage_plugin_upload']){
                        $need_notice = false;
                    }
                    else{
                        $need_notice = true;
                    }
                }
                if($need_notice) {
                    if($need_set_low_resource_mode) {
                        $notice_msg1 = 'Backup failed, it seems due to insufficient server resource or hitting server limit. Please navigate to Settings > Advanced > ';
                        $notice_msg2 = 'optimization mode for web hosting/shared hosting';
                        $notice_msg3 = ' to enable it and try again';
                        $backup_error_array['bu_error']['error_msg']='<div class="notice notice-error inline"><p>'.$notice_msg1.'<strong>'.$notice_msg2.'</strong>'.$notice_msg3.'</p></div>';
                    }
                    else{
                        $notice_msg = 'Backup error: '.$task['status']['error'].', task id: '.$task['id'];
                        $backup_error_array['bu_error']['error_msg']='<div class="notice notice-error inline"><p>'.$notice_msg.', Please switch to <a href="#" onclick="wpvivid_click_switch_page(\'wrap\', \'wpvivid_tab_debug\', true);">Website Info</a> page to send us the debug information. </p></div>';
                    }
                }
                else{
                    if($need_set_low_resource_mode) {
                        $notice_msg = 'Backup failed, it seems due to insufficient server resource or hitting server limit.';
                        $backup_error_array['bu_error']['error_msg'] = '<div class="notice notice-error inline"><p>' . $notice_msg . ', Please switch to <a href="#" onclick="wpvivid_click_switch_page(\'wrap\', \'wpvivid_tab_debug\', true);">Website Info</a> page to send us the debug information. </p></div>';
                    }
                    else {
                        $notice_msg = 'Backup error: ' . $task['status']['error'] . ', task id: ' . $task['id'];
                        $backup_error_array['bu_error']['error_msg'] = '<div class="notice notice-error inline"><p>' . $notice_msg . ', Please switch to <a href="#" onclick="wpvivid_click_switch_page(\'wrap\', \'wpvivid_tab_debug\', true);">Website Info</a> page to send us the debug information. </p></div>';
                    }
                }
            }
            WPvivid_Setting::update_option('wpvivid_backup_error_array', $backup_error_array);
            $task_msg = WPvivid_taskmanager::get_task($task['id']);
            $this->update_last_backup_task($task_msg);
        }
        $this->wpvivid_log->WriteLog($task['status']['error'],'error');
        $this->wpvivid_log->CloseFile();
        WPvivid_error_log::create_error_log($this->wpvivid_log->log_file);
        WPvivid_Schedule::clear_monitor_schedule($task['id']);
        $this->add_clean_backing_up_data_event($task['id']);
        if(!class_exists('WPvivid_mail_report'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-mail-report.php';
        WPvivid_mail_report::send_report_mail($task);
        $this->wpvivid_check_clear_litespeed_rule($task['id']);
    }

    public function deal_shutdown_error($task_id)
    {
        if($this->end_shutdown_function===false)
        {
            $last_error = error_get_last();
            if (!empty($last_error) && !in_array($last_error['type'], array(E_NOTICE,E_WARNING,E_USER_NOTICE,E_USER_WARNING,E_DEPRECATED), true))
            {
                $error = $last_error;
            } else {
                $error = false;
            }
            //$this->task_monitor($task_id,$error);
            if (WPvivid_taskmanager::get_task($task_id) !== false)
            {
                if ($this->wpvivid_log->log_file_handle == false)
                {
                    $this->wpvivid_log->OpenLogFile(WPvivid_taskmanager::get_task_options($task_id, 'log_file_name'));
                }

                $status = WPvivid_taskmanager::get_backup_task_status($task_id);

                if ($status['str'] == 'running' || $status['str'] == 'error' || $status['str'] == 'no_responds')
                {
                    $options=WPvivid_Setting::get_option('wpvivid_common_setting');
                    if(isset($options['max_execution_time']))
                    {
                        $limit=$options['max_execution_time'];
                    }
                    else
                    {
                        $limit=WPVIVID_MAX_EXECUTION_TIME;
                    }

                    if(isset($options['max_resume_count']))
                    {
                        $max_resume_count=$options['max_resume_count'];
                    }
                    else
                    {
                        $max_resume_count=WPVIVID_RESUME_RETRY_TIMES;
                    }
                    $time_spend = time() - $status['timeout'];
                    $time_start = time() - $status['start_time'];
                    $time_min=min($limit, 120);
                    if ($time_spend >= $limit)
                    {
                        //time out
                        $status['resume_count']++;

                        if ($status['resume_count'] > $max_resume_count)
                        {
                            $message = __('Too many resumption attempts.', 'wpvivid-backuprestore');
                            $task = WPvivid_taskmanager::update_backup_task_status($task_id, false, 'error', false, $status['resume_count'], $message);
                            do_action('wpvivid_handle_backup_failed', $task, true);
                        } else {
                            $this->check_cancel_backup($task_id);
                            $message = 'Task timed out.';
                            if ($this->add_resume_event($task_id))
                            {
                                WPvivid_taskmanager::update_backup_task_status($task_id, false, 'wait_resume', false, $status['resume_count']);
                            } else {
                                $task = WPvivid_taskmanager::update_backup_task_status($task_id, false, 'error', false, $status['resume_count'], $message);
                                do_action('wpvivid_handle_backup_failed', $task, true);
                            }
                        }
                        if ($this->wpvivid_log)
                            $this->wpvivid_log->WriteLog($message, 'error');
                    }
                    else if($time_start>=$time_min)
                    {
                        $status['resume_count']++;
                        if ($status['resume_count'] > $max_resume_count)
                        {
                            $message = __('Too many resumption attempts.', 'wpvivid-backuprestore');
                            if ($error !== false)
                            {
                                $message.= 'type: '. $error['type'] . ', ' . $error['message'] . ' file:' . $error['file'] . ' line:' . $error['line'];
                            }
                            $task = WPvivid_taskmanager::update_backup_task_status($task_id, false, 'error', false, $status['resume_count'], $message);
                            do_action('wpvivid_handle_backup_failed', $task ,true);
                        } else {
                            $this->check_cancel_backup($task_id);
                            $message = 'Task timed out (WebHosting).';
                            if ($this->add_resume_event($task_id))
                            {
                                WPvivid_taskmanager::update_backup_task_status($task_id, false, 'wait_resume', false, $status['resume_count']);
                            } else {
                                $task = WPvivid_taskmanager::update_backup_task_status($task_id, false, 'error', false, $status['resume_count'], $message);
                                do_action('wpvivid_handle_backup_failed', $task, true);
                            }
                        }
                        if ($this->wpvivid_log)
                            $this->wpvivid_log->WriteLog($message, 'error');
                    }
                    else
                    {
                        $status['resume_count']++;
                        if ($status['resume_count'] > $max_resume_count)
                        {
                            $message = __('Too many resumption attempts.', 'wpvivid-backuprestore');
                            if ($error !== false)
                            {
                                $message.= 'type: '. $error['type'] . ', ' . $error['message'] . ' file:' . $error['file'] . ' line:' . $error['line'];
                            }
                            $task = WPvivid_taskmanager::update_backup_task_status($task_id, false, 'error', false, $status['resume_count'], $message);
                            do_action('wpvivid_handle_backup_failed', $task ,true);
                        } else {
                            $this->check_cancel_backup($task_id);
                            $message = 'Task timed out (WebHosting).';
                            if ($this->add_resume_event($task_id))
                            {
                                WPvivid_taskmanager::update_backup_task_status($task_id, false, 'wait_resume', false, $status['resume_count']);
                            } else {
                                $task = WPvivid_taskmanager::update_backup_task_status($task_id, false, 'error', false, $status['resume_count'], $message);
                                do_action('wpvivid_handle_backup_failed', $task, true);
                            }
                        }
                        if ($this->wpvivid_log)
                            $this->wpvivid_log->WriteLog($message, 'error');
                    }
                    /*
                    else
                    {
                        if ($status['str'] != 'error')
                        {
                            if ($error !== false)
                            {
                                $message = 'type: '. $error['type'] . ', ' . $error['message'] . ' file:' . $error['file'] . ' line:' . $error['line'];
                            } else {
                                $message = __('Backup timed out. Please set the value of PHP script execution timeout to '.$time_start.' in plugin settings.', 'wpvivid-backuprestore');
                            }
                            WPvivid_taskmanager::update_backup_task_status($task_id, false, 'error', false, $status['resume_count'], $message);
                        }
                        $task = WPvivid_taskmanager::get_task($task_id);
                        do_action('wpvivid_handle_backup_failed', $task, false);
                    }
                    */
                }
            }
            die();
        }
    }
    public function deal_task_error($task_id,$error_type,$error)
    {
        $message = 'An '.$error_type.' has occurred. class:'.get_class($error).';msg:'.$error->getMessage().';code:'.$error->getCode().';line:'.$error->getLine().';in_file:'.$error->getFile().';';
        error_log($message);
        $task=WPvivid_taskmanager::update_backup_task_status($task_id,false,'error',false,false,$message);
        $this->wpvivid_log->WriteLog($message,'error');

        do_action('wpvivid_handle_backup_failed',$task, false);
    }
    /**
     * update time limit.
     *
     * @var string $task_id
     *
     * @var int $second
     *
     * @since 0.9.1
     */
    public function set_time_limit($task_id,$second=0)
    {
        if($second==0)
        {
            $options=WPvivid_Setting::get_option('wpvivid_common_setting');
            if(isset($options['max_execution_time']))
            {
                $second=$options['max_execution_time'];
            }
            else
            {
                $second=WPVIVID_MAX_EXECUTION_TIME;
            }
        }
        WPvivid_taskmanager::update_backup_task_status($task_id,false,'',true);
        @set_time_limit($second);
    }
    /**
     * Watch task status.
     *
     * @var string $task_id
     *
     * @var array|false $error
     *
     * @since 0.9.1
     */
    public function task_monitor($task_id)
    {
        if(WPvivid_taskmanager::get_task($task_id)!==false)
        {
            if($this->wpvivid_log->log_file_handle==false)
            {
                $this->wpvivid_log->OpenLogFile(WPvivid_taskmanager::get_task_options($task_id,'log_file_name'));
            }

            $status=WPvivid_taskmanager::get_backup_task_status($task_id);

            if($status['str']=='running'||$status['str']=='error'||$status['str']=='no_responds')
            {
                $options=WPvivid_Setting::get_option('wpvivid_common_setting');
                if(isset($options['max_execution_time']))
                {
                    $limit=$options['max_execution_time'];
                }
                else
                {
                    $limit=WPVIVID_MAX_EXECUTION_TIME;
                }
                $time_spend=time()-$status['timeout'];
                $last_active_time=time()-$status['run_time'];
                if($time_spend>=$limit&&$last_active_time>min(180,$limit))
                {
                    //time out
                    if(isset($options['max_resume_count']))
                    {
                        $max_resume_count=$options['max_resume_count'];
                    }
                    else
                    {
                        $max_resume_count=WPVIVID_RESUME_RETRY_TIMES;
                    }
                    $status['resume_count']++;
                    if($status['resume_count']>$max_resume_count)
                    {
                        $message=__('Too many resumption attempts.', 'wpvivid-backuprestore');
                        $task=WPvivid_taskmanager::update_backup_task_status($task_id,false,'error',false,$status['resume_count'],$message);
                        //WPvivid_error_log::create_error_log($this->wpvivid_log->log_file);
                        do_action('wpvivid_handle_backup_failed',$task, true);
                    }
                    else
                    {
                        $this->check_cancel_backup($task_id);
                        $message=__('Task timed out.', 'wpvivid-backuprestore');
                        if($this->add_resume_event($task_id))
                        {
                            WPvivid_taskmanager::update_backup_task_status($task_id,false,'wait_resume',false,$status['resume_count']);
                        }
                        else
                        {
                            $task=WPvivid_taskmanager::update_backup_task_status($task_id,false,'error',false,$status['resume_count'],$message);
                            do_action('wpvivid_handle_backup_failed',$task, true);
                        }
                    }
                    if($this->wpvivid_log)
                        $this->wpvivid_log->WriteLog($message,'error');
                }
                else {
                    $time_spend=time()-$status['run_time'];
                    if($time_spend>180)
                    {
                        $this->check_cancel_backup($task_id);
                        $this->wpvivid_log->WriteLog('Not responding for a long time.','notice');
                        WPvivid_taskmanager::update_backup_task_status($task_id,false,'no_responds',false,$status['resume_count']);
                        $this->add_monitor_event($task_id);
                    }
                    else {
                        $this->add_monitor_event($task_id);
                    }
                }
            }
            else if($status['str']=='wait_resume')
            {
                $timestamp = wp_next_scheduled(WPVIVID_RESUME_SCHEDULE_EVENT,array($task_id));
                if($timestamp===false)
                {
                    if($this->wpvivid_log)
                        $this->wpvivid_log->WriteLog('Missing resume task,so we create new one.','error');
                    $message = 'Task timed out (WebHosting).';
                    if ($this->add_resume_event($task_id))
                    {
                        WPvivid_taskmanager::update_backup_task_status($task_id, false, 'wait_resume', false, $status['resume_count']);
                    } else {
                        $task = WPvivid_taskmanager::update_backup_task_status($task_id, false, 'error', false, $status['resume_count'], $message);
                        do_action('wpvivid_handle_backup_failed', $task, true);
                    }
                }
            }
        }
    }

    public function task_monitor_ex()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        $tasks=WPvivid_Setting::get_tasks();
        $task_id='';
        foreach ($tasks as $task)
        {
            if($task['action']=='backup')
            {
                $status=WPvivid_taskmanager::get_backup_tasks_status($task['id']);
                if($status['str']=='completed'||$status['str']=='error')
                {
                    continue;
                }
                else
                {
                    $task_id=$task['id'];
                    break;
                }
            }
        }

        if(empty($task_id))
        {
            die();
        }

        if(WPvivid_taskmanager::get_task($task_id)!==false)
        {
            if($this->wpvivid_log->log_file_handle==false)
            {
                $this->wpvivid_log->OpenLogFile(WPvivid_taskmanager::get_task_options($task_id,'log_file_name'));
            }

            $status=WPvivid_taskmanager::get_backup_task_status($task_id);

            if($status['str']=='running'||$status['str']=='error'||$status['str']=='no_responds')
            {
                $options=WPvivid_Setting::get_option('wpvivid_common_setting');
                if(isset($options['max_execution_time']))
                {
                    $limit=$options['max_execution_time'];
                }
                else
                {
                    $limit=WPVIVID_MAX_EXECUTION_TIME;
                }
                $time_spend=time()-$status['timeout'];
                $last_active_time=time()-$status['run_time'];
                if($time_spend>=$limit&&$last_active_time>min(180,$limit))
                {
                    //time out
                    if(isset($options['max_resume_count']))
                    {
                        $max_resume_count=$options['max_resume_count'];
                    }
                    else
                    {
                        $max_resume_count=WPVIVID_RESUME_RETRY_TIMES;
                    }
                    $status['resume_count']++;
                    if($status['resume_count']>$max_resume_count)
                    {
                        $message=__('Too many resumption attempts.', 'wpvivid-backuprestore');
                        $task=WPvivid_taskmanager::update_backup_task_status($task_id,false,'error',false,$status['resume_count'],$message);
                        //WPvivid_error_log::create_error_log($this->wpvivid_log->log_file);
                        do_action('wpvivid_handle_backup_failed',$task, true);
                    }
                    else
                    {
                        $this->check_cancel_backup($task_id);
                        $message=__('Task timed out.', 'wpvivid-backuprestore');
                        if($this->add_resume_event($task_id))
                        {
                            WPvivid_taskmanager::update_backup_task_status($task_id,false,'wait_resume',false,$status['resume_count']);
                        }
                        else
                        {
                            $task=WPvivid_taskmanager::update_backup_task_status($task_id,false,'error',false,$status['resume_count'],$message);
                            do_action('wpvivid_handle_backup_failed',$task, true);
                        }
                    }
                    if($this->wpvivid_log)
                        $this->wpvivid_log->WriteLog($message,'error');
                }
                else {
                    $time_spend=time()-$status['run_time'];
                    if($time_spend>180)
                    {
                        $this->check_cancel_backup($task_id);
                        $this->wpvivid_log->WriteLog('Not responding for a long time.','notice');
                        WPvivid_taskmanager::update_backup_task_status($task_id,false,'no_responds',false,$status['resume_count']);
                        $this->add_monitor_event($task_id);
                    }
                    else{
                        $this->add_monitor_event($task_id);
                    }
                }
            }
            else if($status['str']=='wait_resume')
            {
                $timestamp = wp_next_scheduled(WPVIVID_RESUME_SCHEDULE_EVENT,array($task_id));
                if($timestamp===false)
                {
                    if($this->wpvivid_log)
                        $this->wpvivid_log->WriteLog('Missing resume task,so we create new one.','error');
                    $message = 'Task timed out (WebHosting).';
                    if ($this->add_resume_event($task_id))
                    {
                        WPvivid_taskmanager::update_backup_task_status($task_id, false, 'wait_resume', false, $status['resume_count']);
                    } else {
                        $task = WPvivid_taskmanager::update_backup_task_status($task_id, false, 'error', false, $status['resume_count'], $message);
                        do_action('wpvivid_handle_backup_failed', $task, true);
                    }
                }
            }
        }
    }
    /**
     * Estimate the size of files, folder, database and backup time before backing up.
     *
     * @var string $task_id
     *
     * @var string $backup_files
     *
     * @var array $backup_option
     *
     * @return array
     *
     * @since 0.9.1
     */
    public function check_backup($task_id,$backup_option)
    {
        @set_time_limit(180);
        $options=WPvivid_Setting::get_option('wpvivid_common_setting');
        if(isset($options['estimate_backup']))
        {
            if($options['estimate_backup'] == false)
            {
                $ret['alert_db']=false;
                $ret['alter_files']=false;
                $ret['alter_fcgi']=false;
                $ret['alter_big_file']=false;
                return $ret;
            }
        }

        $file_size=false;

        $check['check_file']=false;
        $check['check_db']=false;
        add_filter('wpvivid_check_backup_size',array($this, 'check_backup_size'),10,2);
        $check = apply_filters('wpvivid_check_backup_size', $check,$backup_option);
        $check_file=$check['check_file'];
        $check_db=$check['check_db'];

        $sapi_type=php_sapi_name();

        if($sapi_type=='cgi-fcgi'||$sapi_type=='fpm-fcgi')
        {
            $alter_fcgi=true;
        }
        else
        {
            $alter_fcgi=false;
        }
        if($check_db)
        {
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-db-method.php';
            $db_method=new WPvivid_DB_Method();
            $ret=$db_method->check_db($alter_fcgi);
            if($ret['result']==WPVIVID_FAILED)
            {
                return $ret;
            }
        }
        else
        {
            $ret['alert_db']=false;
            $ret['db_size']=false;
        }

        $ret['alter_files']=false;
        $ret['alter_big_file']=false;
        $ret['alter_fcgi']=false;

        if($check_file)
        {
            include_once WPVIVID_PLUGIN_DIR .'/includes/class-wpvivid-backup.php';
            $task=new WPvivid_Backup_Task($task_id);

            $file_size=$task->get_file_info();
            $sum_size=$file_size['sum_size'];
            $sum_count=$file_size['sum_count'];
            if($alter_fcgi)
            {
                $alter_sum_size=1024*1024*1024;
                $alter_sum_count=20000;
            }
            else
            {
                $alter_sum_size=4*1024*1024*1024;
                $alter_sum_count=8*10000;
            }

            if($sum_size>$alter_sum_size||$sum_count>$alter_sum_count)
            {
                $ret['alter_files']=true;
                $ret['sum_size']=$this->formatBytes($sum_size);
                $ret['sum_count']=$sum_count;
                $ret['file_size']=$file_size;
                if($alter_fcgi)
                    $ret['alter_fcgi']=true;
            }
            else{
                $ret['sum_count']=$sum_count;
            }
            $file_size['sum']=$this->formatBytes($sum_size);
        }

        $ret['file_size']=$file_size;
        if($task_id!==false)
        {
            $task=new WPvivid_Backup_Task($task_id);
            $task->set_file_and_db_info($ret['db_size'],$file_size);
        }
        return $ret;
    }

    public function check_backup_size($check,$backup_option)
    {
        if(isset($backup_option['backup_files']))
        {
            if($backup_option['backup_files']=='files+db')
            {
                $check['check_file']=true;
                $check['check_db']=true;
            }
            else if($backup_option['backup_files']=='files')
            {
                $check['check_file']=true;
            }
            else if($backup_option['backup_files']=='db')
            {
                $check['check_db']=true;
            }
        }
        return $check;
    }

    /**
     * Add a backup task resume schedule.
     *
     * @var string $task_id
     *
     * @return boolean
     *
     * @since 0.9.1
     */
    private function add_resume_event($task_id)
    {
        $resume_time=time()+WPVIVID_RESUME_INTERVAL;

        $b=wp_schedule_single_event($resume_time,WPVIVID_RESUME_SCHEDULE_EVENT,array($task_id));

        if($b===false)
        {
            $timestamp = wp_next_scheduled(WPVIVID_RESUME_SCHEDULE_EVENT,array($task_id));

            if($timestamp!==false)
            {
                $resume_time=max($resume_time,$timestamp+10*60+10);

                $b=wp_schedule_single_event($resume_time,WPVIVID_RESUME_SCHEDULE_EVENT,array($task_id));

                if($b===false)
                {
                    $this->wpvivid_log->WriteLog('Add and retry resume event failed.','notice');
                    return false;
                }
                $this->wpvivid_log->WriteLog('Retry resume event succeeded.','notice');
            }
            else
            {
                $this->wpvivid_log->WriteLog('Add resume event failed.','notice');
                return false;
            }
        }
        $this->wpvivid_log->WriteLog('Add resume event succeeded.. arg1:'.$resume_time.' arg2:'.WPVIVID_RESUME_SCHEDULE_EVENT.' arg3:'.$task_id,'notice');
        return true;
    }
    /**
     * Add a scheduled task to clear backup data.
     *
     * @var string $task_id
     *
     * @return boolean
     *
     * @since 0.9.1
     */
    public function add_clean_backing_up_data_event($task_id)
    {
        $task=WPvivid_taskmanager::get_task($task_id);
        $tasks=WPvivid_Setting::get_option('clean_task');
        $tasks[$task_id]=$task;
        WPvivid_Setting::update_option('clean_task',$tasks);

        $resume_time=time()+60;

        $b=wp_schedule_single_event($resume_time,WPVIVID_CLEAN_BACKING_UP_DATA_EVENT,array($task_id));

        if($b===false)
        {
            $timestamp = wp_next_scheduled(WPVIVID_CLEAN_BACKING_UP_DATA_EVENT,array($task_id));

            if($timestamp!==false)
            {
                $resume_time=max($resume_time,$timestamp+10*60+10);

                $b=wp_schedule_single_event($resume_time,WPVIVID_CLEAN_BACKING_UP_DATA_EVENT,array($task_id));

                if($b===false)
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        return true;
    }
    /**
     * Add a scheduled task to clear backup record.
     *
     * @var string $task_id
     *
     * @return boolean
     *
     * @since 0.9.1
     */
    private function add_clean_backup_record_event($backup_id)
    {
        $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
        $tasks=WPvivid_Setting::get_option('clean_task');
        $tasks[$backup_id]=$backup;
        WPvivid_Setting::update_option('clean_task',$tasks);
        $resume_time=time()+60;

        $b=wp_schedule_single_event($resume_time,WPVIVID_CLEAN_BACKUP_RECORD_EVENT,array($backup_id));

        if($b===false)
        {
            $timestamp = wp_next_scheduled(WPVIVID_CLEAN_BACKUP_RECORD_EVENT,array($backup_id));

            if($timestamp!==false)
            {
                $resume_time=max($resume_time,$timestamp+10*60+10);

                $b=wp_schedule_single_event($resume_time,WPVIVID_CLEAN_BACKUP_RECORD_EVENT,array($backup_id));

                if($b===false)
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        return true;
    }
    /**
     * Add a watch task scheduled event.
     *
     * @var string $task_id
     *
     * @var int $next_time
     *
     * @return boolean
     *
     * @since 0.9.1
     */
    public function add_monitor_event($task_id,$next_time=120)
    {
        $resume_time=time()+$next_time;

        $timestamp = wp_next_scheduled(WPVIVID_TASK_MONITOR_EVENT,array($task_id));

        if($timestamp===false)
        {
            $b = wp_schedule_single_event($resume_time, WPVIVID_TASK_MONITOR_EVENT, array($task_id));
            if ($b === false)
            {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    public function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . '' . $units[$pow];
    }
    /**
     * check backup task canceled or not
     *
     * @var string $task_id
     *
     * @since 0.9.1
     */
    public function check_cancel_backup($task_id)
    {
        if(WPvivid_taskmanager::get_task($task_id)!==false)
        {
            $task=new WPvivid_Backup_Task($task_id);

            if($task->is_cancel_file_exist())
            {
                if($this->wpvivid_log->log_file_handle==false)
                {
                    $this->wpvivid_log->OpenLogFile(WPvivid_taskmanager::get_task_options($task_id,'log_file_name'));
                }
                $this->wpvivid_log->WriteLog('Backup cancelled.','notice');

                $task->update_status('cancel');
                //WPvivid_taskmanager::update_backup_task_status($task_id,false,'cancel',false);
                $this->add_clean_backing_up_data_event($task_id);
                WPvivid_Schedule::clear_monitor_schedule($task_id);
                WPvivid_taskmanager::delete_task($task_id);
                die();
            }
        }
    }
    public function flush($txt, $from_mainwp=false) {
        if(!$from_mainwp) {
            $ret['result'] = 'success';
            $ret['task_id'] = $txt;
            $txt = wp_json_encode($ret);
        }
        else{
            $ret['result']='success';
            $txt = '<mainwp>' . base64_encode( serialize( $ret ) ) . '</mainwp>';
        }
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

    /**
     * return initialization download page data
     *
     * @var string $task_id
     *
     * @since 0.9.48
     */
    public function init_download_page()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id'])) {
                $backup_id = sanitize_key($_POST['backup_id']);
                $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
                if ($backup === false) {
                    $ret['result'] = WPVIVID_FAILED;
                    $ret['error'] = 'backup id not found';
                    echo wp_json_encode($ret);
                    die();
                }

                $backup_item = new WPvivid_Backup_Item($backup);

                $backup_files = $backup_item->get_download_backup_files($backup_id);

                if ($backup_files['result'] == WPVIVID_SUCCESS) {
                    $ret['result'] = WPVIVID_SUCCESS;

                    $remote = $backup_item->get_remote();

                    foreach ($backup_files['files'] as $file) {
                        $path = $this->get_backup_path($backup_item, $file['file_name']);
                        //$path = $backup_item->get_local_path() . $file['file_name'];

                        if (file_exists($path)) {
                            if (filesize($path) == $file['size']) {
                                if (WPvivid_taskmanager::get_download_task_v2($file['file_name']))
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                $ret['files'][$file['file_name']]['status'] = 'completed';
                                $ret['files'][$file['file_name']]['size'] = size_format(filesize($path), 2);
                                $ret['files'][$file['file_name']]['download_path'] = $path;
                                $download_url = $this->get_backup_url($backup_item, $file['file_name']);
                                $ret['files'][$file['file_name']]['download_url'] = $download_url;

                                continue;
                            }
                        }
                        $ret['files'][$file['file_name']]['size'] = size_format($file['size'], 2);

                        if (empty($remote)) {
                            $ret['files'][$file['file_name']]['status'] = 'file_not_found';
                        } else {
                            $task = WPvivid_taskmanager::get_download_task_v2($file['file_name']);
                            if ($task === false) {
                                $ret['files'][$file['file_name']]['status'] = 'need_download';
                            } else {
                                $ret['result'] = WPVIVID_SUCCESS;
                                if ($task['status'] === 'running') {
                                    $ret['files'][$file['file_name']]['status'] = 'running';
                                    $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                                    if (file_exists($path)) {
                                        $ret['files'][$file['file_name']]['downloaded_size'] = size_format(filesize($path), 2);
                                    } else {
                                        $ret['files'][$file['file_name']]['downloaded_size'] = '0';
                                    }
                                } elseif ($task['status'] === 'timeout') {
                                    $ret['files'][$file['file_name']]['status'] = 'timeout';
                                    $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                } elseif ($task['status'] === 'completed') {
                                    $ret['files'][$file['file_name']]['status'] = 'completed';
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                } elseif ($task['status'] === 'error') {
                                    $ret['files'][$file['file_name']]['status'] = 'error';
                                    $ret['files'][$file['file_name']]['error'] = $task['error'];
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                }
                            }
                        }
                    }
                } else {
                    $ret = $backup_files;
                }


                if (!class_exists('WPvivid_Files_List'))
                    include_once WPVIVID_PLUGIN_DIR .'/admin/partials/wpvivid-backup-restore-page-display.php';

                $files_list = new WPvivid_Files_List();

                $files_list->set_files_list($ret['files'], $backup_id);
                $files_list->prepare_items();
                ob_start();
                $files_list->display();
                $ret['html'] = ob_get_clean();

                echo wp_json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_download_page_ex()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id'])) {
                if (isset($_POST['page'])) {
                    $page = sanitize_key($_POST['page']);
                } else {
                    $page = 1;
                }

                $backup_id = sanitize_key($_POST['backup_id']);
                $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
                if ($backup === false) {
                    $ret['result'] = WPVIVID_FAILED;
                    $ret['error'] = 'backup id not found';
                    echo wp_json_encode($ret);
                    die();
                }

                $backup_item = new WPvivid_Backup_Item($backup);

                $backup_files = $backup_item->get_download_backup_files($backup_id);

                if ($backup_files['result'] == WPVIVID_SUCCESS) {
                    $ret['result'] = WPVIVID_SUCCESS;

                    $remote = $backup_item->get_remote();

                    foreach ($backup_files['files'] as $file) {
                        $path = $this->get_backup_path($backup_item, $file['file_name']);
                        //$path = $backup_item->get_local_path() . $file['file_name'];
                        if (file_exists($path)) {
                            if (filesize($path) == $file['size']) {
                                if (WPvivid_taskmanager::get_download_task_v2($file['file_name']))
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                $ret['files'][$file['file_name']]['status'] = 'completed';
                                $ret['files'][$file['file_name']]['size'] = size_format(filesize($path), 2);
                                $ret['files'][$file['file_name']]['download_path'] = $path;
                                $download_url = $this->get_backup_url($backup_item, $file['file_name']);
                                $ret['files'][$file['file_name']]['download_url'] = $download_url;

                                continue;
                            }
                        }
                        $ret['files'][$file['file_name']]['size'] = size_format($file['size'], 2);

                        if (empty($remote)) {
                            $ret['files'][$file['file_name']]['status'] = 'file_not_found';
                        } else {
                            $task = WPvivid_taskmanager::get_download_task_v2($file['file_name']);
                            if ($task === false) {
                                $ret['files'][$file['file_name']]['status'] = 'need_download';
                            } else {
                                $ret['result'] = WPVIVID_SUCCESS;
                                if ($task['status'] === 'running') {
                                    $ret['files'][$file['file_name']]['status'] = 'running';
                                    $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                                    if (file_exists($path)) {
                                        $ret['files'][$file['file_name']]['downloaded_size'] = size_format(filesize($path), 2);
                                    } else {
                                        $ret['files'][$file['file_name']]['downloaded_size'] = '0';
                                    }
                                } elseif ($task['status'] === 'timeout') {
                                    $ret['files'][$file['file_name']]['status'] = 'timeout';
                                    $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                } elseif ($task['status'] === 'completed') {
                                    $ret['files'][$file['file_name']]['status'] = 'completed';
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                } elseif ($task['status'] === 'error') {
                                    $ret['files'][$file['file_name']]['status'] = 'error';
                                    $ret['files'][$file['file_name']]['error'] = $task['error'];
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                }
                            }
                        }
                    }
                } else {
                    $ret = $backup_files;
                }

                if (!class_exists('WPvivid_Files_List'))
                    include_once WPVIVID_PLUGIN_DIR .'/admin/partials/wpvivid-backup-restore-page-display.php';

                $files_list = new WPvivid_Files_List();

                $files_list->set_files_list($ret['files'], $backup_id, $page);
                $files_list->prepare_items();
                ob_start();
                $files_list->display();
                $ret['html'] = ob_get_clean();

                echo wp_json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    /**
     * prepare download backup
     *
     * Retrieve files from the server
     *
     * @var string $task_id
     *
     * @since 0.9.1
     */
    public function prepare_download_backup()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        $this->end_shutdown_function=false;
        register_shutdown_function(array($this,'deal_prepare_download_shutdown_error'));
        $id=uniqid('wpvivid-');
        $log_file_name=$id.'_download';
        $this->wpvivid_download_log->OpenLogFile($log_file_name);
        $this->wpvivid_download_log->WriteLog('Prepare download backup.','notice');
        $this->wpvivid_download_log->WriteLogHander();
        try {
            if (!isset($_POST['backup_id']) || empty($_POST['backup_id']) || !is_string($_POST['backup_id']) || !isset($_POST['file_name']) || empty($_POST['file_name']) || !is_string($_POST['file_name'])) {
                $this->end_shutdown_function=true;
                die();
            }
            $download_info = array();
            $download_info['backup_id'] = sanitize_key($_POST['backup_id']);
            $download_info['file_name'] = sanitize_file_name($_POST['file_name']);
            @set_time_limit(600);
            if (session_id())
                session_write_close();

            $downloader = new WPvivid_downloader();
            $downloader->ready_download($download_info);

            $ret['result'] = 'success';
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            if($this->wpvivid_download_log){
                $this->wpvivid_download_log->WriteLog($message ,'error');
                $this->wpvivid_download_log->CloseFile();
                WPvivid_error_log::create_error_log($this->wpvivid_download_log->log_file);
            }
            else {
                $id = uniqid('wpvivid-');
                $log_file_name = $id . '_download';
                if(!class_exists('WPvivid_Log'))
                {
                    include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-log.php';
                }
                $log = new WPvivid_Log();
                $log->CreateLogFile($log_file_name, 'no_folder', 'download');
                $log->WriteLog($message, 'error');
                $log->CloseFile();
                WPvivid_error_log::create_error_log($log->log_file);
            }
            error_log($message);
            $this->end_shutdown_function=true;
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        $this->wpvivid_download_log->CloseFile();
        $this->end_shutdown_function=true;
        die();
    }

    public function get_download_progress()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (isset($_POST['backup_id'])) {
                $backup_id = sanitize_key($_POST['backup_id']);
                $ret['result'] = WPVIVID_SUCCESS;
                $ret['files'] = array();
                $ret['need_update'] = false;

                $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
                if ($backup === false) {
                    $ret['result'] = WPVIVID_FAILED;
                    $ret['error'] = 'backup id not found';
                    return $ret;
                }

                $backup_item = new WPvivid_Backup_Item($backup);

                $backup_files = $backup_item->get_download_backup_files($backup_id);

                foreach ($backup_files['files'] as $file) {
                    $path = $this->get_backup_path($backup_item, $file['file_name']);
                    //$path = $backup_item->get_local_path() . $file['file_name'];
                    if (file_exists($path)) {
                        $downloaded_size = size_format(filesize($path), 2);
                    } else {
                        $downloaded_size = '0';
                    }
                    $file['size'] = size_format($file['size'], 2);

                    $task = WPvivid_taskmanager::get_download_task_v2($file['file_name']);
                    if ($task === false) {
                        $ret['files'][$file['file_name']]['status'] = 'need_download';
                        $ret['files'][$file['file_name']]['html'] = '<div class="wpvivid-element-space-bottom">
                                                                        <span class="wpvivid-element-space-right">Retriving (remote storage to web server)</span><span class="wpvivid-element-space-right">|</span><span>File Size: </span><span class="wpvivid-element-space-right">' . $file['size'] . '</span><span class="wpvivid-element-space-right">|</span><span>Downloaded Size: </span><span>0</span>
                                                                   </div>
                                                                   <div style="width:100%;height:10px; background-color:#dcdcdc;">
                                                                        <div style="background-color:#0085ba; float:left;width:0%;height:10px;"></div>
                                                                   </div>';
                        $ret['need_update'] = true;
                    } else {
                        if ($task['status'] === 'running') {
                            $ret['files'][$file['file_name']]['status'] = 'running';
                            $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                            $ret['files'][$file['file_name']]['html'] = '<div class="wpvivid-element-space-bottom">
                                                                            <span class="wpvivid-element-space-right">Retriving (remote storage to web server)</span><span class="wpvivid-element-space-right">|</span><span>File Size: </span><span class="wpvivid-element-space-right">' . $file['size'] . '</span><span class="wpvivid-element-space-right">|</span><span>Downloaded Size: </span><span>' . $downloaded_size . '</span>
                                                                        </div>
                                                                        <div style="width:100%;height:10px; background-color:#dcdcdc;">
                                                                            <div style="background-color:#0085ba; float:left;width:' . $task['progress_text'] . '%;height:10px;"></div>
                                                                        </div>';
                            $ret['need_update'] = true;
                        } elseif ($task['status'] === 'timeout') {
                            $ret['files'][$file['file_name']]['status'] = 'timeout';
                            $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                            $ret['files'][$file['file_name']]['html'] = '<div class="wpvivid-element-space-bottom">
                                                                            <span>Download timeout, please retry.</span>
                                                                         </div>
                                                                         <div>
                                                                            <span>' . __('File Size: ', 'wpvivid-backuprestore') . '</span><span class="wpvivid-element-space-right">' . $file['size'] . '</span><span class="wpvivid-element-space-right">|</span><span class="wpvivid-element-space-right"><a class="wpvivid-download" style="cursor: pointer;">'. __('Prepare to Download', 'wpvivid-backuprestore').'</a></span>
                                                                        </div>';
                            WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                        } elseif ($task['status'] === 'completed') {
                            $ret['files'][$file['file_name']]['status'] = 'completed';
                            $ret['files'][$file['file_name']]['html'] = '<span>' . __('File Size: ', 'wpvivid-backuprestore') . '</span><span class="wpvivid-element-space-right">' . $file['size'] . '</span><span class="wpvivid-element-space-right">|</span><span class="wpvivid-element-space-right wpvivid-ready-download"><a style="cursor: pointer;">'. __('Download', 'wpvivid-backuprestore').'</a></span>';
                            WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                        } elseif ($task['status'] === 'error') {
                            $ret['files'][$file['file_name']]['status'] = 'error';
                            $ret['files'][$file['file_name']]['error'] = $task['error'];
                            $ret['files'][$file['file_name']]['html'] = '<div class="wpvivid-element-space-bottom">
                                                                            <span>' . $task['error'] . '</span>
                                                                         </div>
                                                                         <div>
                                                                            <span>' . __('File Size: ', 'wpvivid-backuprestore') . '</span><span class="wpvivid-element-space-right">' . $file['size'] . '</span><span class="wpvivid-element-space-right">|</span><span class="wpvivid-element-space-right"><a class="wpvivid-download" style="cursor: pointer;">'. __('Prepare to Download', 'wpvivid-backuprestore').'</a></span>
                                                                         </div>';
                            WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                        }
                    }
                }
                echo wp_json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function deal_prepare_download_shutdown_error()
    {
        if($this->end_shutdown_function==false) {
            $last_error = error_get_last();
            if (!empty($last_error) && !in_array($last_error['type'], array(E_NOTICE,E_WARNING,E_USER_NOTICE,E_USER_WARNING,E_DEPRECATED), true)) {
                $error = $last_error;
            } else {
                $error = false;
            }
            $ret['result'] = 'failed';
            if ($error === false) {
                $ret['error'] = 'unknown Error';
            } else {
                $ret['error'] = 'type: '. $error['type'] . ', ' . $error['message'] . ' file:' . $error['file'] . ' line:' . $error['line'];
                error_log($ret['error']);
            }
            if($this->wpvivid_download_log){
                $this->wpvivid_download_log->WriteLog($ret['error'] ,'error');
                $this->wpvivid_download_log->CloseFile();
                WPvivid_error_log::create_error_log($this->wpvivid_download_log->log_file);
            }
            else {
                $id = uniqid('wpvivid-');
                $log_file_name = $id . '_download';
                if(!class_exists('WPvivid_Log'))
                {
                    include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-log.php';
                }
                $log = new WPvivid_Log();
                $log->CreateLogFile($log_file_name, 'no_folder', 'download');
                $log->WriteLog($ret['error'], 'notice');
                $log->CloseFile();
                WPvivid_error_log::create_error_log($log->log_file);
            }
            echo wp_json_encode($ret);
            die();
        }
    }

    public function init_download($backup_id)
    {
        if(empty($backup_id))
        {
            $ret['result']=WPVIVID_SUCCESS;
            $ret['data']=array();
            return $ret;
        }
        $ret['result']=WPVIVID_SUCCESS;
        $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);

        if($backup===false)
        {
            $ret['result']=WPVIVID_FAILED;
            $ret['error']='backup id not found';
            return $ret;
        }

        $backup_item=new WPvivid_Backup_Item($backup);
        $ret=$backup_item->get_download_backup_files($backup_id);
        if($ret['result']==WPVIVID_SUCCESS){
            $ret=$backup_item->get_download_progress($backup_id, $ret['files']);
            WPvivid_taskmanager::update_download_cache($backup_id,$ret);
        }
        return $ret;
    }

    /**
     * download backup file
     *
     * @since 0.9.1
     */
    public function download_backup()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (isset($_REQUEST['backup_id']) && isset($_REQUEST['file_name'])) {
                if (!empty($_REQUEST['backup_id']) && is_string($_REQUEST['backup_id'])) {
                    $backup_id = sanitize_key($_REQUEST['backup_id']);
                } else {
                    die();
                }

                if (!empty($_REQUEST['file_name']) && is_string($_REQUEST['file_name'])) {
                    //$file_name=sanitize_file_name($_REQUEST['file_name']);
                    $file_name = sanitize_text_field($_REQUEST['file_name']);
                } else {
                    die();
                }

                $cache = WPvivid_taskmanager::get_download_cache($backup_id);
                if ($cache === false) {
                    $this->init_download($backup_id);
                    $cache = WPvivid_taskmanager::get_download_cache($backup_id);
                }
                $path = false;
                if (array_key_exists($file_name, $cache['files'])) {
                    if ($cache['files'][$file_name]['status'] == 'completed') {
                        $path = $cache['files'][$file_name]['download_path'];
                    }
                }
                if ($path !== false) {
                    if (file_exists($path)) {
                        if (session_id())
                            session_write_close();

                        @ini_set( 'memory_limit', '1024M' );

                        $size = filesize($path);
                        if (!headers_sent()) {
                            header('Content-Description: File Transfer');
                            header('Content-Type: application/zip');
                            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                            header('Cache-Control: must-revalidate');
                            header('Content-Length: ' . $size);
                            header('Content-Transfer-Encoding: binary');
                        }

                        if ($size < 1024 * 1024 * 60) {
                            ob_end_clean();
                            readfile($path);
                            exit;
                        } else {
                            ob_end_clean();
                            $download_rate = 1024 * 10;
                            $file = fopen($path, "r");
                            while (!feof($file)) {
                                @set_time_limit(20);
                                // send the current file part to the browser
                                print fread($file, round($download_rate * 1024));
                                // flush the content to the browser
                                ob_flush();
                                flush();
                                // sleep one second
                                sleep(1);
                            }
                            fclose($file);
                            exit;
                        }
                    }
                }
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        $admin_url = admin_url();
        echo '<a href="'.esc_url($admin_url).'admin.php?page=WPvivid">file not found. please retry again.</a>';
        die();
    }

    /**
     * List backup record
     *
     * @since 0.9.1
     */
    public function get_backup_list()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try
        {
            $json['result'] = 'success';
            $html = '';
            $html = apply_filters('wpvivid_add_backup_list', $html);
            $json['html'] = $html;
            echo wp_json_encode($json);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }
    /**
     * Delete backup record
     *
     * @since 0.9.1
     */
    public function delete_backup()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try
        {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id']) && isset($_POST['force']))
            {
                if ($_POST['force'] == 0 || $_POST['force'] == 1)
                {
                    $force_del = sanitize_key($_POST['force']);
                } else {
                    $force_del = 0;
                }
                $backup_id = sanitize_key($_POST['backup_id']);
                $ret = $this->delete_backup_by_id($backup_id, $force_del);
                echo wp_json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }
    /**
     * Delete backup records
     *
     * @since 0.9.1
     */
    public function delete_backup_array()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try
        {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_array($_POST['backup_id']))
            {
                $backup_ids = $_POST['backup_id'];
                $ret = array();
                foreach ($backup_ids as $backup_id)
                {
                    $backup_id = sanitize_key($backup_id);
                    $ret = $this->delete_backup_by_id($backup_id);
                }
                $html = '';
                $html = apply_filters('wpvivid_add_backup_list', $html);
                $ret['html'] = $html;
                echo wp_json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function delete_backup_by_id($backup_id,$force=0)
    {
        $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
        if(!$backup)
        {
            $ret['result']='failed';
            $ret['error']=__('Retrieving the backup(s) information failed while deleting the selected backup(s). Please try again later.', 'wpvivid-backuprestore');
            return $ret;
        }

        $backup_item=new WPvivid_Backup_Item($backup);
        if($backup_item->is_lock())
        {
            if($force==0)
            {
                $ret['result']='failed';
                $ret['error']=__('Unable to delete the locked backup. Please unlock it first and try again.', 'wpvivid-backuprestore');
                return $ret;
            }
        }
        WPvivid_Backuplist::delete_backup($backup_id);
        $backup_item->cleanup_local_backup();
        $backup_item->cleanup_remote_backup();

        $html = '';
        $html = apply_filters('wpvivid_add_backup_list', $html);
        $ret['html'] = $html;
        $ret['result']='success';
        return $ret;
    }

    public function delete_local_backup($backup_ids)
    {
        foreach ($backup_ids as $backup_id)
        {
            $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
            if(!$backup)
            {
               continue;
            }

            if(array_key_exists('lock',$backup))
            {
               continue;
            }

            $backup_item = new WPvivid_Backup_Item($backup);
            $backup_item->cleanup_local_backup();
        }
    }

    public function delete_task()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try {
            if (isset($_POST['task_id']) && !empty($_POST['task_id']) && is_string($_POST['task_id'])) {
                $task_id = sanitize_key($_POST['task_id']);
                WPvivid_taskmanager::delete_task($task_id);

                $json['result'] = 'success';
                echo wp_json_encode($json);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }
    /**
     * Add remote storage
     *
     * @since 0.9.1
     */
    public function add_remote()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (empty($_POST) || !isset($_POST['remote']) || !is_string($_POST['remote']) || !isset($_POST['type']) || !is_string($_POST['type'])) {
                die();
            }
            $json = $_POST['remote'];
            $json = stripslashes($json);
            $remote_options = json_decode($json, true);
            if (is_null($remote_options)) {
                die();
            }

            $remote_options['type'] = sanitize_text_field($_POST['type']);
            if ($remote_options['type'] == 'amazons3')
            {
                if(isset($remote_options['s3Path']))
                    $remote_options['s3Path'] = rtrim($remote_options['s3Path'], "/");
            }
            $ret = $this->remote_collection->add_remote($remote_options);

            if ($ret['result'] == 'success') {
                $html = '';
                $html = apply_filters('wpvivid_add_remote_storage_list', $html);
                $ret['html'] = $html;
                $pic = '';
                $pic = apply_filters('wpvivid_schedule_add_remote_pic', $pic);
                $ret['pic'] = $pic;
                $dir = '';
                $dir = apply_filters('wpvivid_get_remote_directory', $dir);
                $ret['dir'] = $dir;
                $schedule_local_remote = '';
                $schedule_local_remote = apply_filters('wpvivid_schedule_local_remote', $schedule_local_remote);
                $ret['local_remote'] = $schedule_local_remote;
                $remote_storage = '';
                $remote_storage = apply_filters('wpvivid_remote_storage', $remote_storage);
                $ret['remote_storage'] = $remote_storage;
                $remote_select_part = '';
                $remote_select_part = apply_filters('wpvivid_remote_storage_select_part', $remote_select_part);
                $ret['remote_select_part'] = $remote_select_part;
                $default = array();
                $remote_array = apply_filters('wpvivid_archieve_remote_array', $default);
                $ret['remote_array'] = $remote_array;
                $success_msg = __('You have successfully added a remote storage.', 'wpvivid-backuprestore');
                $ret['notice'] = apply_filters('wpvivid_add_remote_notice', true, $success_msg);
            }
            else{
                $ret['notice'] = apply_filters('wpvivid_add_remote_notice', false, $ret['error']);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        echo wp_json_encode($ret);
        die();
    }
    /**
     * Delete remote storage
     *
     * @since 0.9.1
     */
    public function delete_remote()
    {
        try {
            check_ajax_referer( 'wpvivid_ajax', 'nonce' );
            $check=current_user_can('manage_options');
            $check=apply_filters('wpvivid_ajax_check_security',$check);
            if(!$check)
            {
                die();
            };
            if (empty($_POST) || !isset($_POST['remote_id']) || !is_string($_POST['remote_id'])) {
                die();
            }
            $id = sanitize_key($_POST['remote_id']);
            if (WPvivid_Setting::delete_remote_option($id)) {
                $remote_selected = WPvivid_Setting::get_user_history('remote_selected');
                if (in_array($id, $remote_selected)) {
                    WPvivid_Setting::update_user_history('remote_selected', array());
                }
                $ret['result'] = 'success';
                $html = '';
                $html = apply_filters('wpvivid_add_remote_storage_list', $html);
                $ret['html'] = $html;
                $pic = '';
                $pic = apply_filters('wpvivid_schedule_add_remote_pic', $pic);
                $ret['pic'] = $pic;
                $dir = '';
                $dir = apply_filters('wpvivid_get_remote_directory', $dir);
                $ret['dir'] = $dir;
                $schedule_local_remote = '';
                $schedule_local_remote = apply_filters('wpvivid_schedule_local_remote', $schedule_local_remote);
                $ret['local_remote'] = $schedule_local_remote;
                $remote_storage = '';
                $remote_storage = apply_filters('wpvivid_remote_storage', $remote_storage);
                $ret['remote_storage'] = $remote_storage;
            } else {
                $ret['result'] = 'failed';
                $ret['error'] = __('Failed to delete the remote storage, can not retrieve the storage infomation. Please try again.', 'wpvivid-backuprestore');
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        echo wp_json_encode($ret);
        die();
    }

    /**
     * Retrieve remote storage
     *
     * @since 0.9.8
     */
    public function retrieve_remote()
    {
        try {
            check_ajax_referer( 'wpvivid_ajax', 'nonce' );
            $check=current_user_can('manage_options');
            $check=apply_filters('wpvivid_ajax_check_security',$check);
            if(!$check)
            {
                die();
            }
            if (empty($_POST) || !isset($_POST['remote_id']) || !is_string($_POST['remote_id'])) {
                die();
            }
            $id = sanitize_key($_POST['remote_id']);
            $remoteslist = WPvivid_Setting::get_all_remote_options();
            $ret['result'] = WPVIVID_FAILED;
            $ret['error'] = __('Failed to get the remote storage information. Please try again later.', 'wpvivid-backuprestore');
            foreach ($remoteslist as $key => $value) {
                if ($key == $id) {
                    if ($key === 'remote_selected') {
                        continue;
                    }
                    $value = apply_filters('wpvivid_encrypt_remote_password', $value);
                    $ret = $value;
                    $ret['result'] = WPVIVID_SUCCESS;
                    break;
                }
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        echo wp_json_encode($ret);
        die();
    }
    /**
     * Edit remote storage
     *
     * @since 0.9.8
     */
    public function edit_remote()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (empty($_POST) || !isset($_POST['remote']) || !is_string($_POST['remote']) || !isset($_POST['id']) || !is_string($_POST['id']) || !isset($_POST['type']) || !is_string($_POST['type'])) {
                die();
            }
            $json = sanitize_text_field($_POST['remote']);
            $json = stripslashes($json);
            $remote_options = json_decode($json, true);
            if (is_null($remote_options)) {
                die();
            }
            $remote_options['type'] = sanitize_text_field($_POST['type']);
            if ($remote_options['type'] == 'amazons3')
            {
                if(isset($remote_options['s3Path']))
                    $remote_options['s3Path'] = rtrim($remote_options['s3Path'], "/");
            }
            $id=sanitize_key($_POST['id']);
            $old_remote=WPvivid_Setting::get_remote_option($id);
            foreach ($old_remote as $key=>$value)
            {
                if(isset($remote_options[$key]))
                    $old_remote[$key]=$remote_options[$key];
            }

            $ret = $this->remote_collection->update_remote($id, $old_remote);

            if ($ret['result'] == 'success') {
                $ret['result'] = WPVIVID_SUCCESS;
                $html = '';
                $html = apply_filters('wpvivid_add_remote_storage_list', $html);
                $ret['html'] = $html;
                $pic = '';
                $pic = apply_filters('wpvivid_schedule_add_remote_pic', $pic);
                $ret['pic'] = $pic;
                $dir = '';
                $dir = apply_filters('wpvivid_get_remote_directory', $dir);
                $ret['dir'] = $dir;
                $schedule_local_remote = '';
                $schedule_local_remote = apply_filters('wpvivid_schedule_local_remote', $schedule_local_remote);
                $ret['local_remote'] = $schedule_local_remote;
                $remote_storage = '';
                $remote_storage = apply_filters('wpvivid_remote_storage', $remote_storage);
                $ret['remote_storage'] = $remote_storage;
                $remote_select_part = '';
                $remote_select_part = apply_filters('wpvivid_remote_storage_select_part', $remote_select_part);
                $ret['remote_select_part'] = $remote_select_part;
                $default = array();
                $remote_array = apply_filters('wpvivid_archieve_remote_array', $default);
                $ret['remote_array'] = $remote_array;
                $success_msg = 'You have successfully updated the account information of your remote storage.';
                $ret['notice'] = apply_filters('wpvivid_add_remote_notice', true, $success_msg);
            }
            else{
                $ret['notice'] = apply_filters('wpvivid_add_remote_notice', false, $ret['error']);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        echo wp_json_encode($ret);
        die();
    }
    /**
     * List exist remote
     *
     * @since 0.9.1
     */
    public function list_remote()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try {
            $ret['result'] = 'success';
            $html = '';
            $html = apply_filters('wpvivid_add_remote_storage_list', $html);
            $ret['html'] = $html;
            $pic = '';
            $pic = apply_filters('wpvivid_schedule_add_remote_pic', $pic);
            $ret['pic'] = $pic;
            $dir = '';
            $dir = apply_filters('wpvivid_get_remote_directory', $dir);
            $ret['dir'] = $dir;
            $schedule_local_remote = '';
            $schedule_local_remote = apply_filters('wpvivid_schedule_local_remote', $schedule_local_remote);
            $ret['local_remote'] = $schedule_local_remote;
            $remote_storage = '';
            $remote_storage = apply_filters('wpvivid_remote_storage', $remote_storage);
            $ret['remote_storage'] = $remote_storage;
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        echo wp_json_encode($ret);
        die();
    }
    /**
     * Test remote connection
     *
     * @since 0.9.1
     */
    public function test_remote_connection()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (empty($_POST) || !isset($_POST['remote']) || !is_string($_POST['remote']) || !isset($_POST['type']) || !is_string($_POST['type'])) {
                die();
            }
            $json = sanitize_text_field($_POST['remote']);
            $json = stripslashes($json);
            $remote_options = json_decode($json, true);
            if (is_null($remote_options)) {
                die();
            }

            $remote_options['type'] = sanitize_text_field($_POST['type']);
            $remote = $this->remote_collection->get_remote($remote_options);
            $ret = $remote->test_connect();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        echo wp_json_encode($ret);
        die();
    }
    /**
     * Get backup schedule data
     *
     * @since 0.9.1
     */
    public function get_schedule()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try {
            $schedule = WPvivid_Schedule::get_schedule();
            $schedule['next_start'] = gmdate("l, F d, Y H:i", $schedule['next_start']);
            $ret['result'] = 'success';
            $ret['data'] = $schedule;
            $ret['user_history'] = WPvivid_Setting::get_user_history('remote_selected');
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function scan_last_restore()
    {
        try
        {
            $dir=WPvivid_Setting::get_backupdir();
            $restore_data_file=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.'wpvivid_restoredata';
            if(file_exists($restore_data_file))
            {
                $ret['has_exist_restore']=1;
            }
            else
            {
                $ret['has_exist_restore']=0;
            }

            $ret['has_old_files']=0;
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';

            $ret['has_exist_restore']=1;
            $ret['restore_error']=$message;
        }


        return $ret;
    }

    public function check_max_allow_packet_ex()
    {
        $max_all_packet_warning=false;
        global $wpdb;
        $wpdb->get_results('SET NAMES utf8mb4', ARRAY_A);
        $max_allowed_packet = $wpdb->get_results('SELECT @@session.max_allowed_packet;',ARRAY_N);
        if($max_allowed_packet)
        {
            if(is_array($max_allowed_packet)&&isset($max_allowed_packet[0])&&isset($max_allowed_packet[0][0]))
            {
                if($max_allowed_packet[0][0]<16777216){
                    $max_all_packet_warning = 'max_allowed_packet = '.size_format($max_allowed_packet[0][0]).' is too small. The recommended value is 16M or higher. Too small value could lead to a failure when importing a larger database.';
                }
            }
        }
        return $max_all_packet_warning;
    }

    public function init_restore_page()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try
        {
            if (!isset($_POST['backup_id']) || empty($_POST['backup_id']) || !is_string($_POST['backup_id']))
            {
                die();
            }

            //$this->restore_data = new WPvivid_restore_data();
            $ret_scan_last_restore = $this->scan_last_restore();

            $backup_id = sanitize_key($_POST['backup_id']);

            $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);

            $backup_item = new WPvivid_Backup_Item($backup);

            $ret = $backup_item->check_backup_files();

            $ret['is_migrate'] = $backup_item->check_migrate_file();

            if ($backup_item->get_backup_type() == 'Upload' || $backup_item->get_backup_type() == 'Migration')
            {
                $is_display = $backup_item->is_display_migrate_option();
                if($is_display === true)
                {
                    $ret['is_migrate_ui'] = 1;
                }
                else {
                    $ret['is_migrate_ui'] = 0;
                }
            } else {
                $ret['is_migrate_ui'] = 0;
            }

            $ret['skip_backup_old_site'] = 1;
            $ret['skip_backup_old_database'] = 1;

            $ret = array_merge($ret, $ret_scan_last_restore);

            $ret['max_allow_packet_warning'] = $this->check_max_allow_packet_ex();

            $common_setting = WPvivid_Setting::get_option('wpvivid_common_setting');
            if(isset($common_setting['restore_memory_limit']) && !empty($common_setting['restore_memory_limit'])){
                $memory_limit = $common_setting['restore_memory_limit'];
            }
            else{
                $memory_limit = WPVIVID_RESTORE_MEMORY_LIMIT;
            }

            @ini_set('memory_limit', $memory_limit);

            $memory_limit = ini_get('memory_limit');
            $unit = strtoupper(substr($memory_limit, -1));
            if ($unit == 'K')
            {
                $memory_limit_tmp = intval($memory_limit) * 1024;
            }
            else if ($unit == 'M')
            {
                $memory_limit_tmp = intval($memory_limit) * 1024 * 1024;
            }
            else if ($unit == 'G')
            {
                $memory_limit_tmp = intval($memory_limit) * 1024 * 1024 * 1024;
            }
            else{
                $memory_limit_tmp = intval($memory_limit);
            }
            if ($memory_limit_tmp < 256 * 1024 * 1024)
            {
                $ret['memory_limit_warning'] = 'memory_limit = ' . $memory_limit . ' is too small. The recommended value is 256M or higher. Too small value could result in a failure of website restore.';
            } else {
                $ret['memory_limit_warning'] = false;
            }

            if ($ret['result'] == WPVIVID_FAILED)
            {
                //$this->wpvivid_handle_restore_error($ret['error'], 'Init restore page');
            }

            $has_zero_date=$backup_item->check_has_zero_date();
            if($has_zero_date)
            {
                include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-db-method.php';
                $db_method = new WPvivid_DB_Method();
                $ret_sql_mode = $db_method->get_sql_mode();
                if(preg_match('/NO_ZERO_DATE/', $ret_sql_mode['mysql_mode']))
                {
                    $ret['has_zero_date']=1;
                }
            }

            echo wp_json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function get_restore_file_is_migrate()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (!isset($_POST['backup_id']) || empty($_POST['backup_id']) || !is_string($_POST['backup_id'])) {
                die();
            }

            $backup_id = sanitize_key($_POST['backup_id']);

            $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);

            $backup_item = new WPvivid_Backup_Item($backup);

            $ret = $backup_item->check_backup_files();

            $ret['is_migrate'] =  $backup_item->check_migrate_file();

            if ($backup_item->get_backup_type() == 'Upload' || $backup_item->get_backup_type() == 'Migration')
            {
                $is_display = $backup_item->is_display_migrate_option();
                if($is_display === true){
                    $ret['is_migrate_ui'] = 1;
                }
                else{
                    $ret['is_migrate_ui'] = 0;
                }
                /*if( $ret['is_migrate']==0)
                    $ret['is_migrate_ui'] = 1;
                else
                    $ret['is_migrate_ui'] = 0;*/
            } else {
                $ret['is_migrate_ui'] = 0;
            }

            if ($ret['result'] == WPVIVID_FAILED) {
                //$this->wpvivid_handle_restore_error($ret['error'], 'Init restore page');
            }

            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function delete_last_restore_data()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            $this->restore_data = new WPvivid_restore_data();
            $this->restore_data->clean_restore_data();
            $ret['result'] = 'success';
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    /**
     * Prepare backup files for restore
     *
     * @since 0.9.1
     */
    public function prepare_restore()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (!isset($_POST['backup_id']) || empty($_POST['backup_id']) || !is_string($_POST['backup_id'])) {
                die();
            }

            $backup_id = sanitize_key($_POST['backup_id']);

            $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);

            $backup_item = new WPvivid_Backup_Item($backup);

            $ret = $backup_item->check_backup_files();

            if ($backup_item->get_backup_type() == 'Upload')
            {
                $ret['is_migrate'] = 1;
            } else {
                $ret['is_migrate'] = 0;
            }

            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }
    /**
     * Download backup files from remote server for restore
     *
     * @since 0.9.1
     */
    public function download_restore_file()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (!isset($_POST['backup_id']) || empty($_POST['backup_id']) || !is_string($_POST['backup_id'])
                || !isset($_POST['file_name']) || empty($_POST['file_name']) || !is_string($_POST['file_name'])) {
                die();
            }

            @set_time_limit(600);

            $backup_id = sanitize_key($_POST['backup_id']);
            //$file_name=sanitize_file_name($_POST['file_name']);
            $file_name = sanitize_text_field($_POST['file_name']);

            $file['file_name'] = $file_name;
            $file['size'] = sanitize_key($_POST['size']);

            if(isset($_POST['md5']))
            {
                $file['md5'] = sanitize_key($_POST['md5']);
            }

            $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
            if (!$backup) {
                echo wp_json_encode(array('result' => WPVIVID_FAILED, 'error' => 'backup not found'));
                die();
            }

            $backup_item = new WPvivid_Backup_Item($backup);

            $remote_option = $backup_item->get_remote();

            if ($remote_option === false) {
                echo wp_json_encode(array('result' => WPVIVID_FAILED, 'error' => 'Retrieving the cloud storage information failed while downloading backups. Please try again later.'));
                die();
            }

            //$downloader = new WPvivid_downloader();
            //$ret = $downloader->download($file, $local_path, $remote_option);
            $download_info = array();
            $download_info['backup_id'] = sanitize_key($_POST['backup_id']);
            //$download_info['file_name']=sanitize_file_name($_POST['file_name']);
            $download_info['file_name'] = sanitize_text_field($_POST['file_name']);
            //set_time_limit(600);
            if (session_id())
                session_write_close();

            $downloader = new WPvivid_downloader();
            $downloader->ready_download($download_info);

            $ret['result'] = 'success';
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function download_restore_progress()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try
        {
            if (!isset($_POST['file_name'])) {
                die();
            }

            $file_name=sanitize_text_field($_POST['file_name']);

            $file_size = sanitize_key($_POST['size']);

            $task = WPvivid_taskmanager::get_download_task_v2($file_name);

            if ($task === false)
            {
                $check_status = false;
                $backupdir=WPvivid_Setting::get_backupdir();
                $local_storage_dir = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backupdir;
                $local_file=$local_storage_dir.DIRECTORY_SEPARATOR.$file_name;
                if(file_exists($local_file))
                {
                    if(filesize($local_file)==$file_size)
                    {
                        $check_status = true;
                    }
                }

                if($check_status){
                    $ret['result'] = WPVIVID_SUCCESS;
                    $ret['status'] = 'completed';
                }
                else {
                    $ret['result'] = WPVIVID_FAILED;
                    $ret['error'] = 'not found download file';
                    //$this->wpvivid_handle_restore_error($ret['error'], 'Downloading backup file');
                }
                echo wp_json_encode($ret);
            } else {
                $ret['result'] = WPVIVID_SUCCESS;
                $ret['status'] = $task['status'];
                if(isset($task['download_descript']))
                {
                    $ret['log'] = $task['download_descript'];
                }
                else
                {
                    $ret['log'] = '';
                }
                $ret['error'] = $task['error'];
                echo wp_json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function download_check_has_zero_date()
    {
        try
        {
            check_ajax_referer( 'wpvivid_ajax', 'nonce' );
            $check=current_user_can('manage_options');
            $check=apply_filters('wpvivid_ajax_check_security',$check);
            if(!$check)
            {
                die();
            }
            if (!isset($_POST['backup_id'])) {
                die();
            }

            $backup_id = sanitize_key($_POST['backup_id']);

            $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);

            $backup_item = new WPvivid_Backup_Item($backup);

            $has_zero_date=$backup_item->check_has_zero_date();
            if($has_zero_date)
            {
                include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-db-method.php';
                $db_method = new WPvivid_DB_Method();
                $ret_sql_mode = $db_method->get_sql_mode();
                if(preg_match('/NO_ZERO_DATE/', $ret_sql_mode['mysql_mode']))
                {
                    $ret['has_zero_date']=1;
                }
            }

            $ret['result']=WPVIVID_SUCCESS;
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function wpvivid_handle_restore_error($error_message, $error_type)
    {
        $this->restore_data=new WPvivid_restore_data();
        $this->restore_data->delete_restore_log();
        $this->restore_data->write_log($error_type, 'error');
        $this->restore_data->write_log($error_message, 'error');
        $this->restore_data->save_error_log_to_debug();
    }

    public function wpvivid_handle_remote_storage_error($error_message, $error_type)
    {
        $id = uniqid('wpvivid-');
        $log_file_name = $id . '_add_remote';
        $log = new WPvivid_Log();
        $log->CreateLogFile($log_file_name, 'no_folder', 'Add Remote Test Connection');
        $log->WriteLog('Remote Type: '.$error_type, 'notice');
        if(isset($ret['error'])) {
            $log->WriteLog($error_message, 'notice');
        }
        $log->CloseFile();
        WPvivid_error_log::create_error_log($log->log_file);
    }

    /**
     * Start restore
     *
     * @since 0.9.1
     */
    public function restore()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        if(!$check)
        {
            die();
        }
        $this->end_shutdown_function=false;
        register_shutdown_function(array($this,'deal_restore_shutdown_error'));
        if(!isset($_POST['backup_id'])||empty($_POST['backup_id'])||!is_string($_POST['backup_id']))
        {
            $this->end_shutdown_function=true;
            die();
        }

        $backup_id=sanitize_key($_POST['backup_id']);
        $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
        if($backup===false)
        {
            die();
        }

        $this->restore_data=new WPvivid_restore_data();

        $restore_options=array();
        if(isset($_POST['restore_options']))
        {
            $json=sanitize_text_field($_POST['restore_options']);
            $json = stripslashes($json);
            $restore_options = json_decode($json, 1);
            if(is_null($restore_options))
            {
                $restore_options=array();
            }
        }
        try
        {
            if ($this->restore_data->has_restore())
            {
                $status = $this->restore_data->get_restore_status();

                if ($status === WPVIVID_RESTORE_ERROR)
                {
                    $ret['result'] =WPVIVID_FAILED;
                    $ret['error'] = $this->restore_data->get_restore_error();
                    $this->restore_data->save_error_log_to_debug();
                    $this->restore_data->delete_temp_files();
                    $this->_disable_maintenance_mode();
                    echo wp_json_encode($ret);
                    $this->end_shutdown_function=true;
                    die();
                }
                else if ($status === WPVIVID_RESTORE_COMPLETED)
                {
                    $this->write_litespeed_rule(false);
                    $this->restore_data->write_log('disable maintenance mode', 'notice');
                    $this->restore_data->delete_temp_files();
                    $this->_disable_maintenance_mode();
                    echo wp_json_encode(array('result' => 'finished'));
                    $this->end_shutdown_function=true;
                    die();
                }
            }
            else {
                $this->restore_data->init_restore_data($backup_id,$restore_options);
                $this->restore_data->write_log('init restore data', 'notice');
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            echo esc_html($message);
            $this->end_shutdown_function=true;
            die();
        }

        try
        {
            $this->_enable_maintenance_mode();
            $this->write_litespeed_rule();
            $restore=new WPvivid_Restore();
            $common_setting = WPvivid_Setting::get_option('wpvivid_common_setting');
            if(isset($common_setting['restore_memory_limit']) && !empty($common_setting['restore_memory_limit'])){
                $memory_limit = $common_setting['restore_memory_limit'];
            }
            else{
                $memory_limit = WPVIVID_RESTORE_MEMORY_LIMIT;
            }

            @ini_set('memory_limit', $memory_limit);
            $ret=$restore->restore();
            if($ret['result']==WPVIVID_FAILED&&$ret['error']=='A restore task is already running.')
            {
                echo wp_json_encode(array('result'=> WPVIVID_SUCCESS));
                $this->end_shutdown_function=true;
                die();
            }
            $this->_disable_maintenance_mode();
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            $this->restore_data->delete_temp_files();
            $this->restore_data->update_error($message);
            $this->restore_data->write_log($message,'error');
            $this->restore_data->save_error_log_to_debug();
            $this->_disable_maintenance_mode();
            echo wp_json_encode(array('result'=>WPVIVID_FAILED,'error'=>$message));
            $this->end_shutdown_function=true;
            die();
        }

        if($ret['result']==WPVIVID_FAILED)
        {
            $this->restore_data->delete_temp_files();
            $this->_disable_maintenance_mode();
        }

        echo wp_json_encode($ret);
        $this->end_shutdown_function=true;
        die();
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

    public function deal_restore_shutdown_error()
    {
        if($this->end_shutdown_function===false)
        {
            $last_error = error_get_last();
            if (!empty($last_error) && !in_array($last_error['type'], array(E_NOTICE,E_WARNING,E_USER_NOTICE,E_USER_WARNING,E_DEPRECATED), true))
            {
                $error = $last_error;
            } else {
                $error = false;
            }
            //$this->task_monitor($task_id,$error);

            if ($error !== false)
            {
                $message = 'type: '. $error['type'] . ', ' . $error['message'] . ' file:' . $error['file'] . ' line:' . $error['line'];
                $this->restore_data->delete_temp_files();
                $this->restore_data->update_error($message);
                $this->restore_data->write_log($message,'error');
                $this->restore_data->save_error_log_to_debug();
                //save_error_log_to_debug
                $this->_disable_maintenance_mode();
                echo wp_json_encode(array('result'=>WPVIVID_FAILED,'error'=>$message));
            }
            else {
                $message = __('restore failed error unknown', 'wpvivid-backuprestore');
                $this->restore_data->delete_temp_files();
                $this->restore_data->update_error($message);
                $this->restore_data->write_log($message,'error');
                $this->restore_data->save_error_log_to_debug();
                $this->_disable_maintenance_mode();
                echo wp_json_encode(array('result'=>WPVIVID_FAILED,'error'=>$message));
            }

            die();
        }
    }
    /**
     * Get restore progress
     *
     * @since 0.9.1
     */
    public function get_restore_progress()
    {
        try
        {
            check_ajax_referer( 'wpvivid_ajax', 'nonce' );
            $check=current_user_can('manage_options');
            if(!$check)
            {
                die();
            }
            if(!isset($_POST['backup_id'])||empty($_POST['backup_id'])||!is_string($_POST['backup_id']))
            {
                $this->end_shutdown_function=true;
                die();
            }

            $backup_id=sanitize_key($_POST['backup_id']);
            $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
            if($backup===false)
            {
                die();
            }

            $this->restore_data = new WPvivid_restore_data();

            if ($this->restore_data->has_restore())
            {
                $ret['result'] = 'success';
                $ret['status'] = $this->restore_data->get_restore_status();
                if ($ret['status'] == WPVIVID_RESTORE_ERROR) {
                    $this->restore_data->save_error_log_to_debug();
                }
                $ret['log'] = $this->restore_data->get_log_content();
                echo wp_json_encode($ret);
                die();
            } else {
                $ret['result'] = 'failed';
                $ret['error'] = __('The restore file not found. Please verify the file exists.', 'wpvivid-backuprestore');
                echo wp_json_encode($ret);
                die();
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
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

    public function deal_restore_error($error_type,$error)
    {
        $message = 'A '.$error_type.' has occurred. class:'.get_class($error).';msg:'.$error->getMessage().';code:'.$error->getCode().';line:'.$error->getLine().';in_file:'.$error->getFile().';';
        error_log($message);
        echo esc_html($message);
    }

    public function update_last_backup_time($task)
    {
        WPvivid_Setting::update_option('wpvivid_last_msg',$task);
    }

    public function update_last_backup_task($task)
    {
        apply_filters('wpvivid_set_backup_report_addon_mainwp', $task);
    }
    /**
     * Get last backup information
     *
     * @since 0.9.1
     */
    public function get_last_backup()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            $html = '';
            $html = apply_filters('wpvivid_get_last_backup_message', $html);
            $ret['data'] = $html;
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function wpvivid_get_last_backup_message($html)
    {
        $html='';
        $message=WPvivid_Setting::get_last_backup_message('wpvivid_last_msg');
        if(empty($message)){
            $last_message=__('The last backup message not found.', 'wpvivid-backuprestore');
        }
        else{
            if(isset($message['status']['start_time']))
            {
                $message['status']['start_time'] = gmdate("l, F-d-Y H:i", strtotime($message['status']['start_time']));
                $last_message=$message['status']['start_time'];
            }
            else
            {
                $last_message=__('The last backup message not found.', 'wpvivid-backuprestore');
            }
        }
        $html .= '<strong>'.__('Last Backup: ', 'wpvivid-backuprestore').'</strong>'.$last_message;
        return $html;
    }

    public function wpvivid_get_last_backup_message_output()
    {
        $message=WPvivid_Setting::get_last_backup_message('wpvivid_last_msg');
        if(empty($message)){
            ?>
            <strong><?php esc_html_e('Last Backup: ', 'wpvivid-backuprestore'); ?></strong><?php esc_html_e('The last backup message not found.', 'wpvivid-backuprestore'); ?>
            <?php
        }
        else{
            if(isset($message['status']['start_time']))
            {
                if($message['status']['str'] == 'completed'){
                    $last_backup_status='Succeeded';
                    $span_style='color: #81d742;';
                }
                elseif($message['status']['str'] == 'error'){
                    $last_backup_status='Failed';
                    $span_style='color: #ff0000;';
                }
                elseif($message['status']['str'] == 'cancel'){
                    $last_backup_status='Failed';
                    $span_style='color: #ff0000;';
                }
                else{
                    $last_backup_status='Succeeded';
                    $span_style='color: #81d742;';
                }
                ?>
                <strong><?php esc_html_e('Last Backup: ', 'wpvivid-backuprestore'); ?></strong>
                <span style="<?php echo esc_attr($span_style); ?>">
                    <?php
                    echo esc_html(gmdate("F-d-Y H:i", strtotime($message['status']['start_time'])).' ('.$last_backup_status.')');
                    ?>
                </span>
                <?php
            }
            else
            {
                ?>
                <strong><?php esc_html_e('Last Backup: ', 'wpvivid-backuprestore'); ?></strong><?php esc_html_e('The last backup message not found.', 'wpvivid-backuprestore'); ?>
                <?php
            }
        }
    }

    public function list_tasks()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try
        {
            if (isset($_POST['backup_id']))
            {
                $backup_id=sanitize_key($_POST['backup_id']);
            }
            else{
                $backup_id=false;
            }
            $ret = $this->_list_tasks($backup_id);
            $backup_success_count=WPvivid_Setting::get_option('wpvivid_backup_success_count');
            if(!empty($backup_success_count)){
                WPvivid_Setting::delete_option('wpvivid_backup_success_count');
            }

            $backup_error_array=WPvivid_Setting::get_option('wpvivid_backup_error_array');
            if(!empty($backup_error_array)){
                WPvivid_Setting::delete_option('wpvivid_backup_error_array');
            }

            echo wp_json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function _list_tasks($backup_id)
    {
        $tasks=WPvivid_Setting::get_tasks();
        $ret=array();
        $list_tasks=array();
        foreach ($tasks as $task)
        {
            if($task['action']=='backup')
            {
                $backup=new WPvivid_Backup_Task($task['id']);
                $list_tasks[$task['id']]=$backup->get_backup_task_info($task['id']);
                if($list_tasks[$task['id']]['task_info']['need_next_schedule']===true){
                    $timestamp = wp_next_scheduled(WPVIVID_TASK_MONITOR_EVENT,array($task['id']));

                    if($timestamp===false)
                    {
                        $this->add_monitor_event($task['id'],20);
                    }
                }
                /*if($list_tasks[$task['id']]['task_info']['need_update_last_task']===true){
                    $task_msg = WPvivid_taskmanager::get_task($task['id']);
                    $this->update_last_backup_task($task_msg);
                    if($task['type'] === 'Cron') {
                        //update last backup time
                        //do_action('wpvivid_update_schedule_last_time_addon');
                    }
                }*/
                //<div id="wpvivid_estimate_backup_info" style="float:left; ' . $list_tasks[$task['id']]['task_info']['display_estimate_backup'] . '">
                //                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Database Size:', 'wpvivid-backuprestore') . '</span><span id="wpvivid_backup_database_size">' . $list_tasks[$task['id']]['task_info']['db_size'] . '</span></div>
                //                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('File Size:', 'wpvivid-backuprestore') . '</span><span id="wpvivid_backup_file_size">' . $list_tasks[$task['id']]['task_info']['file_size'] . '</span></div>
                //                                             </div>
                $list_tasks[$task['id']]['progress_html'] = '<div class="action-progress-bar" id="wpvivid_action_progress_bar">
                                                <div class="action-progress-bar-percent" id="wpvivid_action_progress_bar_percent" style="height:24px;width:' . $list_tasks[$task['id']]['task_info']['backup_percent'] . '"></div>
                                             </div>
                                             <div id="wpvivid_estimate_upload_info" style="float: left;"> 
                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Total Size:', 'wpvivid-backuprestore') . '</span><span>' . $list_tasks[$task['id']]['task_info']['total'] . '</span></div>
                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Uploaded:', 'wpvivid-backuprestore') . '</span><span>' . $list_tasks[$task['id']]['task_info']['upload'] . '</span></div>
                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Speed:', 'wpvivid-backuprestore') . '</span><span>' . $list_tasks[$task['id']]['task_info']['speed'] . '</span></div>
                                             </div>
                                             <div style="float: left;">
                                                <div class="backup-basic-info"><span class="wpvivid-element-space-right">' . __('Network Connection:', 'wpvivid-backuprestore') . '</span><span>' . $list_tasks[$task['id']]['task_info']['network_connection'] . '</span></div>
                                             </div>
                                             <div style="clear:both;"></div>
                                             <div style="margin-left:10px; float: left; width:100%;"><p id="wpvivid_current_doing">' . $list_tasks[$task['id']]['task_info']['descript'] . '</p></div>
                                             <div style="clear: both;"></div>
                                             <div>
                                                <div id="wpvivid_backup_cancel" class="backup-log-btn"><input class="button-primary" id="wpvivid_backup_cancel_btn" type="submit" value="' . esc_attr('Cancel', 'wpvivid-backuprestore') . '" style="' . $list_tasks[$task['id']]['task_info']['css_btn_cancel'] . '" /></div>
                                                <div id="wpvivid_backup_log" class="backup-log-btn"><input class="button-primary" id="wpvivid_backup_log_btn" type="submit" value="' . esc_attr('Log', 'wpvivid-backuprestore') . '" style="' . $list_tasks[$task['id']]['task_info']['css_btn_log'] . '" /></div>
                                             </div>
                                             <div style="clear: both;"></div>';
            }
        }
        WPvivid_taskmanager::delete_marked_task();

        $ret['backuplist_html'] = false;
        $backup_success_count=WPvivid_Setting::get_option('wpvivid_backup_success_count');
        if(!empty($backup_success_count)){
            //$notice_msg = $backup_success_count.' backup tasks have been completed. Please switch to <a href="#" onclick="wpvivid_click_switch_page(\'wrap\', \'wpvivid_tab_log\', true);">Log</a> page to check the details.';
            $notice_msg = sprintf('%d backup tasks have been completed. Please switch to <a href="#" onclick="wpvivid_click_switch_page(\'wrap\', \'wpvivid_tab_log\', true);">Log</a> page to check the details.', $backup_success_count);
            $success_notice_html='<div class="notice notice-success is-dismissible inline"><p>'.$notice_msg.'</p>
                                    <button type="button" class="notice-dismiss" onclick="click_dismiss_notice(this);">
                                    <span class="screen-reader-text">Dismiss this notice.</span>
                                    </button>
                                    </div>';
            //WPvivid_Setting::delete_option('wpvivid_backup_success_count');
            $html = '';
            $html = apply_filters('wpvivid_add_backup_list', $html);
            $ret['backuplist_html'] = $html;
        }
        else {
            $success_notice_html = false;
        }
        $ret['success_notice_html'] = $success_notice_html;

        $backup_error_array=WPvivid_Setting::get_option('wpvivid_backup_error_array');
        if(!empty($backup_error_array)){
            $error_notice_html = array();
            foreach ($backup_error_array as $key => $value){
                $error_notice_html['bu_error']['task_id']=$value['task_id'];
                $error_notice_html['bu_error']['error_msg']=$value['error_msg'];
            }
            //WPvivid_Setting::delete_option('wpvivid_backup_error_array');
            $html = '';
            $html = apply_filters('wpvivid_add_backup_list', $html);
            $ret['backuplist_html'] = $html;
        }
        else{
            $error_notice_html = false;
        }
        $ret['error_notice_html'] = $error_notice_html;

        $ret['backup']['result']='success';
        $ret['backup']['data']=$list_tasks;

        $ret['download']=array();
        if($backup_id !== false && !empty($backup_id)) {
            $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
            if($backup===false)
            {
                $ret['result']=WPVIVID_FAILED;
                $ret['error']='backup id not found';
                return $ret;
            }
            $backup_item=new WPvivid_Backup_Item($backup);
            $ret['download']=$backup_item->update_download_page($backup_id);
        }

        $html='';
        $html=apply_filters('wpvivid_get_last_backup_message', $html);
        $ret['last_msg_html']=$html;

        $html='';
        $html=apply_filters('wpvivid_get_log_list', $html);
        $ret['log_html'] = $html['html'];
        $ret['log_count'] = $html['log_count'];

        return $ret;
    }

    public function clean_cache()
    {
        delete_option('wpvivid_download_cache');
        delete_option('wpvivid_download_task');
        WPvivid_taskmanager::delete_out_of_date_finished_task();
        WPvivid_taskmanager::delete_ready_task();
    }
    /**
     * Get backup local storage path
     *
     * @since 0.9.1
     */
    public function get_dir()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try {
            $dir = WPvivid_Setting::get_option('wpvivid_local_setting');

            if (!isset($dir['path'])) {
                $dir = WPvivid_Setting::set_default_local_option();
            }

            if (!is_dir(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $dir['path'])) {
                @mkdir(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $dir['path'], 0777, true);
                @fopen(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $dir['path'] . '/index.html', 'x');
                $tempfile = @fopen(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $dir['path'] . '/.htaccess', 'x');
                if ($tempfile) {
                    //$text = "deny from all";
                    $text="<IfModule mod_rewrite.c>\r\nRewriteEngine On\r\nRewriteRule .* - [F,L]\r\n</IfModule>";
                    fwrite($tempfile, $text);
                    fclose($tempfile);
                } else {
                    $ret['result'] = 'failed';
                    $ret['error'] = __('Getting backup directory failed. Please try again later.', 'wpvivid-backuprestore');
                }

            }

            $ret['result'] = 'success';
            $ret['path'] = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $dir['path'];
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }
    /**
     * Set security lock for backup record
     *
     * @since 0.9.1
     */
    public function set_security_lock()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id']) && isset($_POST['lock'])) {
                $backup_id = sanitize_key($_POST['backup_id']);
                if ($_POST['lock'] == 0 || $_POST['lock'] == 1) {
                    $lock = sanitize_key($_POST['lock']);
                } else {
                    $lock = 0;
                }

                $ret = WPvivid_Backuplist::set_security_lock($backup_id, $lock);
                echo wp_json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }
    /**
     * Get Web-server disk space in use
     *
     * @since 0.9.1
     */
    public function junk_files_info()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            $ret['result'] = 'success';
            $ret['data'] = $this->_junk_files_info_ex();
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function check_is_a_wpvivid_backup($file_name)
    {
        $ret=WPvivid_Backup_Item::get_backup_file_info($file_name);
        if($ret['result'] === WPVIVID_SUCCESS){
            return true;
        }
        elseif($ret['result'] === WPVIVID_FAILED){
            return $ret['error'];
        }
    }

    public function check_file_is_a_wpvivid_backup($file_name,&$backup_id)
    {
        if(preg_match('/wpvivid-.*_.*_.*\.zip$/',$file_name))
        {
            if(preg_match('/wpvivid-(.*?)_/',$file_name,$matches))
            {
                $id= $matches[0];
                $id=substr($id,0,strlen($id)-1);

                $backup_id_list=WPvivid_Backuplist::get_has_remote_backuplist();
                if(in_array($id, $backup_id_list))
                {
                    return false;
                }
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

    public function get_wpvivid_backup_size()
    {
        $path=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir().DIRECTORY_SEPARATOR;
        $backups=array();
        $count = 0;
        $ret_size = 0;
        if(is_dir($path))
        {
            $handler = opendir($path);
            if($handler!==false)
            {
                while (($filename = readdir($handler)) !== false)
                {
                    if ($filename != "." && $filename != "..")
                    {
                        $count++;

                        if (is_dir($path  . $filename))
                        {
                            continue;
                        } else {
                            if($this->check_file_is_a_wpvivid_backup($filename,$backup_id))
                            {
                                if($this->check_is_a_wpvivid_backup($path.$filename) === true)
                                {
                                    $backups[$backup_id]['files'][] = $filename;
                                }
                            }
                        }
                    }
                }
                if($handler)
                    @closedir($handler);
            }
            if(!empty($backups))
            {
                foreach ($backups as $backup_id =>$backup)
                {
                    $backup_data['result']='success';
                    $backup_data['files']=array();
                    if(empty($backup['files']))
                        continue;
                    foreach ($backup['files'] as $file)
                    {
                        $ret_size += filesize($path.$file);
                    }
                }
            }
        }
        else{
            $ret_size = 0;
        }
        return $ret_size;
    }

    public function _junk_files_info_ex()
    {
        try
        {
            $log_dir = WPvivid_tools::GetSaveLogFolder();
            $log_dir_byte = $this->GetDirectorySize($log_dir);
            $ret['log_dir_size'] = $this->formatBytes($log_dir_byte);


            $ret['backup_cache_size'] = 0;
            $path = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir();
            $handler=opendir($path);
            if($handler===false)
            {
                $ret['backup_cache_size'] = 0;
            }
            while(($filename=readdir($handler))!==false)
            {
                if(preg_match('#pclzip-.*\.tmp#', $filename)){
                    $ret['backup_cache_size'] += filesize($path.DIRECTORY_SEPARATOR.$filename);
                }
                if(preg_match('#pclzip-.*\.gz#', $filename)){
                    $ret['backup_cache_size'] += filesize($path.DIRECTORY_SEPARATOR.$filename);
                }
            }
            @closedir($handler);
            $backup_id_list=WPvivid_Backuplist::get_has_remote_backuplist();
            foreach ($backup_id_list as $backup_id)
            {
                $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
                if(!$backup)
                {
                    continue;
                }

                if(array_key_exists('lock',$backup))
                {
                    continue;
                }

                $backup_item = new WPvivid_Backup_Item($backup);
                $file=$backup_item->get_files(false);
                foreach ($file as $filename)
                {
                    if(file_exists($path.DIRECTORY_SEPARATOR.$filename))
                    {
                        $ret['backup_cache_size'] += filesize($path.DIRECTORY_SEPARATOR.$filename);
                    }
                }
            }
            $ret['backup_cache_size'] = $this->formatBytes($ret['backup_cache_size']);


            $ret['junk_size'] = 0;
            $delete_files  = array();
            $delete_folder = array();
            $list=WPvivid_Backuplist::get_backuplist();
            $files=array();
            foreach ($list as $backup_id => $backup_value)
            {
                $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
                if($backup===false)
                {
                    continue;
                }
                $backup_item = new WPvivid_Backup_Item($backup);
                $file=$backup_item->get_files(false);
                foreach ($file as $filename){
                    $files[]=$filename;
                }
            }

            $dir=WPvivid_Setting::get_backupdir();
            $dir=WP_CONTENT_DIR.DIRECTORY_SEPARATOR. $dir;
            $path=str_replace('/',DIRECTORY_SEPARATOR,WPvivid_tools::GetSaveLogFolder());
            if(substr($path, -1) == DIRECTORY_SEPARATOR)
            {
                $path = substr($path, 0, -1);
            }
            $folder[]= $path;
            $except_regex['file'][]='&wpvivid-&';
            $except_regex['file'][]='&wpvivid_temp-&';
            //$except_regex['file'][]='&'.apply_filters('wpvivid_white_label_file_prefix', 'wpvivid').'-&';
            //$except_regex['file'][]='&'.apply_filters('wpvivid_white_label_file_prefix', 'wpvivid').'_temp-&';
            $this -> get_dir_files($delete_files,$delete_folder,$dir,$except_regex,$files,$folder,0,false);

            foreach ($delete_files as $file)
            {
                if(file_exists($file))
                {
                    $ret['junk_size'] += filesize($file);
                }
            }

            foreach ($delete_folder as $folder)
            {
                if(file_exists($folder))
                {
                    $ret['junk_size'] += $this->GetDirectorySize($folder);
                }
            }
            $ret['junk_size'] = $this->formatBytes($ret['junk_size']);


            $backup_dir_byte = $this->GetDirectorySize($dir);

            $ret['backup_size'] = $this->get_wpvivid_backup_size();
            $ret['backup_size'] = $this->formatBytes($ret['backup_size']);

            $ret['sum_size'] = $this->formatBytes($backup_dir_byte + $log_dir_byte);
        }
        catch (Exception $e)
        {
            $ret['log_path'] = $log_dir = WPvivid_tools::GetSaveLogFolder();
            $dir = WPvivid_Setting::get_backupdir();
            $ret['old_files_path'] = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . WPVIVID_DEFAULT_ROLLBACK_DIR;
            $dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $dir;
            $ret['junk_path'] = $dir;
            $ret['sum_size'] = '0';
            $ret['log_dir_size'] = '0';
            $ret['backup_cache_size'] = '0';
            $ret['junk_size'] = '0';
            $ret['backup_size'] = '0';
        }
        return $ret;
    }

    public function get_out_of_date_info()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            $dir = WPvivid_Setting::get_backupdir();
            $dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $dir;
            $ret['web_server'] = $dir;
            $ret['remote_options'] = WPvivid_Setting::get_remote_options();

            $info = WPvivid_Backuplist::get_out_of_date_backuplist_info(WPvivid_Setting::get_max_backup_count());
            $ret['info'] = $info;
            $ret['info']['size'] = $this->formatBytes($info['size']);

            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function _get_out_of_date_info()
    {
        $dir=WPvivid_Setting::get_backupdir();
        $dir=WP_CONTENT_DIR.DIRECTORY_SEPARATOR. $dir;
        $ret['web_server']=$dir;
        $ret['remote_options']=WPvivid_Setting::get_remote_options();

        $info=WPvivid_Backuplist::get_out_of_date_backuplist_info(WPvivid_Setting::get_max_backup_count());
        $ret['info']=$info;
        $ret['info']['size']=$this->formatBytes($info['size']);

        return $ret;
    }

    public function clean_out_of_date_backup()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try
        {
            $backup_ids=array();
            $backup_ids=apply_filters('wpvivid_get_oldest_backup_ids',$backup_ids,true);
            foreach ($backup_ids as $backup_id)
            {
                $this->delete_backup_by_id($backup_id);
            }
            $ret['result'] = 'success';
            $html = '';
            $html = apply_filters('wpvivid_add_backup_list', $html);
            $ret['html'] = $html;

            echo wp_json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    private function GetDirectorySize($path){
        $bytes_total = 0;
        $path = realpath($path);
        if($path!==false && $path!='' && file_exists($path))
        {
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
                $bytes_total += $object->getSize();
            }
        }
        return $bytes_total;
    }

    public function clean_local_storage()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        try
        {
            if(!isset($_POST['options'])||empty($_POST['options'])||!is_string($_POST['options']))
            {
                die();
            }
            $options=sanitize_text_field($_POST['options']);
            $options =stripslashes($options);
            $options=json_decode($options,true);
            if(is_null($options))
            {
                die();
            }
            if($options['log']=='0' && $options['backup_cache']=='0' && $options['junk_files']=='0' && $options['old_files']=='0')
            {
                $ret['result']=WPVIVID_FAILED;
                $ret['msg']=__('Choose at least one type of junk files for deleting.', 'wpvivid-backuprestore');
                echo wp_json_encode($ret);
                die();
            }
            $delete_files = array();
            $delete_folder=array();
            if($options['log']=='1')
            {
                $log_dir=$this->wpvivid_log->GetSaveLogFolder();
                $log_files=array();
                $temp=array();
                $this -> get_dir_files($log_files,$temp,$log_dir,array('file' => '&wpvivid-&'),array(),array(),0,false);

                foreach ($log_files as $file)
                {
                    $file_name=basename($file);
                    $id=substr ($file_name,0,21);
                    if(WPvivid_Backuplist::get_backup_by_id($id)===false)
                    {
                        $delete_files[]=$file;
                    }
                }
            }

            if($options['backup_cache']=='1')
            {
                $backup_id_list=WPvivid_Backuplist::get_has_remote_backuplist();
                $this->delete_local_backup($backup_id_list);
                WPvivid_tools::clean_junk_cache();
            }

            if($options['junk_files']=='1')
            {
                $list=WPvivid_Backuplist::get_backuplist();
                $files=array();
                foreach ($list as $backup_id => $backup_value)
                {
                    $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
                    if($backup===false)
                    {
                        continue;
                    }
                    $backup_item = new WPvivid_Backup_Item($backup);
                    $file=$backup_item->get_files(false);
                    foreach ($file as $filename){
                        $files[]=$filename;
                    }
                }

                $dir=WPvivid_Setting::get_backupdir();
                $dir=WP_CONTENT_DIR.DIRECTORY_SEPARATOR. $dir;
                $path=str_replace('/',DIRECTORY_SEPARATOR,$this->wpvivid_log->GetSaveLogFolder());
                if(substr($path, -1) == DIRECTORY_SEPARATOR) {
                    $path = substr($path, 0, -1);
                }
                $folder[]= $path;
                $except_regex['file'][]='&wpvivid-&';
                $except_regex['file'][]='&wpvivid_temp-&';
                $this -> get_dir_files($delete_files,$delete_folder,$dir,$except_regex,$files,$folder,0,false);
            }

            foreach ($delete_files as $file)
            {
                if(file_exists($file))
                    @wp_delete_file($file);
            }

            foreach ($delete_folder as $folder)
            {
                if(file_exists($folder))
                    WPvivid_tools::deldir($folder,'',true);
            }

            $ret['result']='success';
            $ret['msg']=__('The selected junk files have been deleted.', 'wpvivid-backuprestore');
            $ret['data']=$this->_junk_files_info_ex();
            $html = '';
            $html = apply_filters('wpvivid_get_log_list', $html);
            $ret['html'] = $html['html'];
            $ret['log_count'] = $html['log_count'];
            echo wp_json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }

        die();
    }

    public function get_dir_files(&$files,&$folder,$path,$except_regex,$exclude_files=array(),$exclude_folder=array(),$exclude_file_size=0,$flag = true)
    {
        $handler=opendir($path);
        if($handler===false)
            return;
        while(($filename=readdir($handler))!==false)
        {
            if($filename != "." && $filename != "..")
            {
                $dir=str_replace('/',DIRECTORY_SEPARATOR,$path.DIRECTORY_SEPARATOR.$filename);


                if(in_array($dir,$exclude_folder))
                {
                    continue;
                }
                else if(is_dir($path.DIRECTORY_SEPARATOR.$filename))
                {
                    if($except_regex!==false)
                    {
                        if($this -> regex_match($except_regex['file'],$path.DIRECTORY_SEPARATOR.$filename,$flag)){
                            continue;
                        }
                        $folder[]=$path.DIRECTORY_SEPARATOR.$filename;
                    }else
                    {
                        $folder[]=$path.DIRECTORY_SEPARATOR.$filename;
                    }
                    $this->get_dir_files($files ,$folder, $path.DIRECTORY_SEPARATOR.$filename,$except_regex,$exclude_folder);
                }else {
                    if($except_regex===false||!$this -> regex_match($except_regex['file'] ,$path.DIRECTORY_SEPARATOR.$filename,$flag))
                    {
                        if(in_array($filename,$exclude_files))
                        {
                            continue;
                        }
                        if($exclude_file_size==0)
                        {
                            $files[] = $path.DIRECTORY_SEPARATOR.$filename;
                        }
                        else if(filesize($path.DIRECTORY_SEPARATOR.$filename)<$exclude_file_size*1024*1024)
                        {
                            $files[] = $path.DIRECTORY_SEPARATOR.$filename;
                        }
                    }
                }
            }
        }
        if($handler)
            @closedir($handler);

    }
    private function regex_match($regex_array,$filename,$flag){
        if($flag){
            if(empty($regex_array)){
                return false;
            }
            if(is_array($regex_array)){
                foreach ($regex_array as $regex)
                {
                    if(preg_match($regex,$filename))
                    {
                        return true;
                    }
                }
            }else{
                if(preg_match($regex_array,$filename))
                {
                    return true;
                }
            }
            return false;
        }else{
            if(empty($regex_array)){
                return true;
            }
            if(is_array($regex_array)){
                foreach ($regex_array as $regex)
                {
                    if(preg_match($regex,$filename))
                    {
                        return false;
                    }
                }
            }else{
                if(preg_match($regex_array,$filename))
                {
                    return false;
                }
            }
            return true;
        }
    }

    public function get_setting()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try {
            if (isset($_POST['all']) && is_bool($_POST['all'])) {
                $all = sanitize_key($_POST['all']);
                if (!$all) {
                    if (isset($_POST['options_name']) && is_array($_POST['options_name'])) {
                        $options_name = $_POST['options_name'];
                        $options_name_array=array();
                        foreach ($options_name as $option_name)
                        {
                            $options_name_array[]=sanitize_text_field($option_name);
                        }
                        $ret = WPvivid_Setting::get_setting($all, $options_name_array);
                        echo wp_json_encode($ret);
                    }
                } else {
                    $options_name = array();
                    $ret = WPvivid_Setting::get_setting($all, $options_name);
                    echo wp_json_encode($ret);
                }
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function update_setting()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try {
            if (isset($_POST['options']) && !empty($_POST['options']) && is_string($_POST['options'])) {
                $json = sanitize_text_field($_POST['options']);
                $json = stripslashes($json);
                $options = json_decode($json, true);
                if (is_null($options)) {
                    die();
                }
                $ret = WPvivid_Setting::update_setting($options);
                echo wp_json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    function set_default_remote_storage()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try {
            if (!isset($_POST['remote_storage']) || empty($_POST['remote_storage']) || !is_array($_POST['remote_storage'])) {
                $ret['result'] = WPVIVID_FAILED;
                $ret['error'] = __('Choose one storage from the list to be the default storage.', 'wpvivid-backuprestore');
                echo wp_json_encode($ret);
                die();
            }
            $remote_storage = $_POST['remote_storage'];
            $remote_storage = array_map( 'sanitize_text_field', $remote_storage );
            WPvivid_Setting::update_user_history('remote_selected', $remote_storage);
            $ret['result'] = 'success';
            $html = '';
            $html = apply_filters('wpvivid_add_remote_storage_list', $html);
            $ret['html'] = $html;
            $pic = '';
            $pic = apply_filters('wpvivid_schedule_add_remote_pic', $pic);
            $ret['pic'] = $pic;
            $dir = '';
            $dir = apply_filters('wpvivid_get_remote_directory', $dir);
            $ret['dir'] = $dir;
            $schedule_local_remote = '';
            $schedule_local_remote = apply_filters('wpvivid_schedule_local_remote', $schedule_local_remote);
            $ret['local_remote'] = $schedule_local_remote;
            $remote_storage = '';
            $remote_storage = apply_filters('wpvivid_remote_storage', $remote_storage);
            $ret['remote_storage'] = $remote_storage;
            $remote_select_part = '';
            $remote_select_part = apply_filters('wpvivid_remote_storage_select_part', $remote_select_part);
            $ret['remote_select_part'] = $remote_select_part;
            $default = array();
            $remote_array = apply_filters('wpvivid_archieve_remote_array', $default);
            $ret['remote_array'] = $remote_array;
            $success_msg = __('You have successfully changed your default remote storage.', 'wpvivid-backuprestore');
            $ret['notice'] = apply_filters('wpvivid_add_remote_notice', true, $success_msg);
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function check_remote_alias_exist()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        if (!isset($_POST['remote_alias']))
        {
            $remote_alias=sanitize_text_field($_POST['remote_alias']);
            $remoteslist=WPvivid_Setting::get_all_remote_options();
            foreach ($remoteslist as $key=>$value)
            {
                if(isset($value['name'])&&$value['name'] == $remote_alias)
                {
                    $ret['result']=WPVIVID_FAILED;
                    $ret['error']="Warning: The alias already exists in storage list.";
                    echo wp_json_encode($ret);
                    die();
                }
            }
            $ret['result']=WPVIVID_SUCCESS;
            echo wp_json_encode($ret);
            die();
        }

        die();
    }

    function get_default_remote_storage(){
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try {
            $ret['result'] = 'success';
            $ret['remote_storage'] = WPvivid_Setting::get_user_history('remote_selected');
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    function get_default_remote_storage_ex(){
        $ret['result']='success';
        $ret['remote_storage']=WPvivid_Setting::get_user_history('remote_selected');
        return $ret;
    }

    public function get_general_setting()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try {
            if (isset($_POST['all']) && is_bool($_POST['all'])) {
                $all = sanitize_key($_POST['all']);
                if (!$all) {
                    if (isset($_POST['options_name']) && is_array($_POST['options_name'])) {
                        $options_name = $_POST['options_name'];
                        $options_name_array=array();
                        foreach ($options_name as $option_name)
                        {
                            $options_name_array[]=sanitize_text_field($option_name);
                        }
                        $ret['data']['setting'] = WPvivid_Setting::get_setting($all, $options_name_array);

                        $schedule = WPvivid_Schedule::get_schedule();
                        $schedule['next_start'] = gmdate("l, F d, Y H:i", $schedule['next_start']);
                        $ret['result'] = 'success';
                        $ret['data']['schedule'] = $schedule;
                        $ret['user_history'] = WPvivid_Setting::get_user_history('remote_selected');
                        echo wp_json_encode($ret);
                    }
                } else {
                    $options_name = array();
                    $ret['data']['setting'] = WPvivid_Setting::get_setting($all, $options_name);
                    $schedule = WPvivid_Schedule::get_schedule();
                    $schedule['next_start'] = gmdate("l, F d, Y H:i", $schedule['next_start']);
                    $ret['result'] = 'success';
                    $ret['data']['schedule'] = $schedule;
                    $ret['user_history'] = WPvivid_Setting::get_user_history('remote_selected');
                    echo wp_json_encode($ret);
                }
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function wpvivid_set_general_setting($setting_data, $setting, $options)
    {
        $setting['exclude_file_size'] = intval($setting['exclude_file_size']);
        $setting['max_execution_time'] = intval($setting['max_execution_time']);
        $setting['restore_max_execution_time'] = intval($setting['restore_max_execution_time']);
        $setting['max_backup_count'] = intval($setting['max_backup_count']);
        $setting['max_resume_count'] = intval($setting['max_resume_count']);

        $setting_data['wpvivid_email_setting']['send_to'][] = $setting['send_to'];
        $setting_data['wpvivid_email_setting']['always'] = $setting['always'];
        if(isset($setting['email_enable'])) {
            $setting_data['wpvivid_email_setting']['email_enable'] = $setting['email_enable'];
        }

        $setting_data['wpvivid_common_setting']['max_file_size'] = $setting['max_file_size'];
        $setting_data['wpvivid_common_setting']['exclude_file_size'] = $setting['exclude_file_size'];

        $setting['path']=esc_attr($setting['path']);
        $setting_data['wpvivid_local_setting']['path'] = $setting['path'];

        if($options['options']['wpvivid_local_setting']['path'] !== $setting['path'])
        {
            if(file_exists(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$options['options']['wpvivid_local_setting']['path']))
            {
                @rename(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$options['options']['wpvivid_local_setting']['path'], WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$setting['path']);
            }
        }

        $setting_data['wpvivid_local_setting']['save_local'] = $options['options']['wpvivid_local_setting']['save_local'];

        $setting_data['wpvivid_common_setting']['max_execution_time'] = $setting['max_execution_time'];
        $setting_data['wpvivid_common_setting']['restore_max_execution_time'] = $setting['restore_max_execution_time'];
        $setting_data['wpvivid_common_setting']['log_save_location'] = $setting['path'].'/wpvivid_log';
        $setting_data['wpvivid_common_setting']['max_backup_count'] = $setting['max_backup_count'];
        $setting_data['wpvivid_common_setting']['show_admin_bar'] = $setting['show_admin_bar'];
        //$setting_data['wpvivid_common_setting']['show_tab_menu'] = $setting['show_tab_menu'];
        $setting_data['wpvivid_common_setting']['domain_include'] = $setting['domain_include'];
        $setting_data['wpvivid_common_setting']['estimate_backup'] = $setting['estimate_backup'];
        $setting_data['wpvivid_common_setting']['max_resume_count'] = $setting['max_resume_count'];
        $setting_data['wpvivid_common_setting']['memory_limit'] = $setting['memory_limit'].'M';
        $setting_data['wpvivid_common_setting']['restore_memory_limit'] = $setting['restore_memory_limit'].'M';
        $setting_data['wpvivid_common_setting']['migrate_size'] = $setting['migrate_size'];
        $setting_data['wpvivid_common_setting']['ismerge'] = $setting['ismerge'];
        $setting_data['wpvivid_common_setting']['db_connect_method'] = $setting['db_connect_method'];
        $setting_data['wpvivid_common_setting']['retain_local'] = $setting['retain_local'];
        $setting_data['wpvivid_common_setting']['uninstall_clear_folder'] = $setting['uninstall_clear_folder'];

        //new
        $setting_data['wpvivid_common_setting']['compress_file_count'] = intval($setting['compress_file_count']);
        $setting_data['wpvivid_common_setting']['max_sql_file_size'] = intval($setting['max_sql_file_size']);

        $setting_data['wpvivid_common_setting']['replace_rows_pre_request'] = intval($setting['replace_rows_pre_request']);
        $setting_data['wpvivid_common_setting']['sql_file_buffer_pre_request'] = intval($setting['sql_file_buffer_pre_request']);
        $setting_data['wpvivid_common_setting']['use_index'] = intval($setting['use_index']);
        $setting_data['wpvivid_common_setting']['unzip_files_pre_request'] = intval($setting['unzip_files_pre_request']);

        $setting_data['wpvivid_common_setting']['zip_method'] = $setting['zip_method'];

		return $setting_data;
    }

    public function set_general_setting()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        $ret=array();
        try
        {
            if(isset($_POST['setting'])&&!empty($_POST['setting']))
            {
                $json_setting = sanitize_text_field($_POST['setting']);
                $json_setting = stripslashes($json_setting);
                $setting = json_decode($json_setting, true);
                if (is_null($setting)){
                    die();
                }
                $ret = $this->check_setting_option($setting);
                if($ret['result']!=WPVIVID_SUCCESS)
                {
                    echo wp_json_encode($ret);
                    die();
                }
                $options=WPvivid_Setting::get_setting(true, "");
                $setting_data = array();
                $setting_data= apply_filters('wpvivid_set_general_setting',$setting_data, $setting, $options);
                $ret['setting']=WPvivid_Setting::update_setting($setting_data);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        echo wp_json_encode($ret);
        die();
    }

    public function set_schedule(){
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };

        $ret=array();

        try{
            if(isset($_POST['schedule'])&&!empty($_POST['schedule']))
            {
                $json = sanitize_text_field($_POST['schedule']);
                $json = stripslashes($json);
                $schedule = json_decode($json, true);
                if (is_null($schedule))
                {
                    die();
                }
                $ret = $this->check_schedule_option($schedule);
                if($ret['result']!=WPVIVID_SUCCESS)
                {
                    echo wp_json_encode($ret);
                    die();
                }
                //set_schedule_ex
                $ret=WPvivid_Schedule::set_schedule_ex($schedule);
                if($ret['result']!='success')
                {
                    echo wp_json_encode($ret);
                    die();
                }
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        echo wp_json_encode($ret);
        die();
    }

    public function check_setting_option($data)
    {
        $ret['result']=WPVIVID_FAILED;
        if(!isset($data['max_file_size']))
        {
            $ret['error']=__('The value of \'Compress file every\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        $data['max_file_size']=sanitize_text_field($data['max_file_size']);

        if(empty($data['max_file_size']) && $data['max_file_size'] != '0')
        {
            $ret['error']=__('The value of \'Compress file every\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        if(!isset($data['exclude_file_size']))
        {
            $ret['error']=__('The value of \'Exclude files which are larger than\' can\'t be empty.', 'wpvivid-backuprestore');
        }

        $data['exclude_file_size']=sanitize_text_field($data['exclude_file_size']);

        if(empty($data['exclude_file_size']) && $data['exclude_file_size'] != '0')
        {
            $ret['error']=__('The value of \'Exclude files which are larger than\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        if(!isset($data['max_execution_time']))
        {
            $ret['error']=__('The value of \'PHP scripts execution timeout for backup\' can\'t be empty.', 'wpvivid-backuprestore');
        }

        $data['max_execution_time']=sanitize_text_field($data['max_execution_time']);

        if(empty($data['max_execution_time']) && $data['max_execution_time'] != '0')
        {
            $ret['error']=__('The value of \'PHP scripts execution timeout for backup\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        //
        if(!isset($data['restore_max_execution_time']))
        {
            $ret['error']=__('The value of \'PHP scripts execution timeout for restore\' can\'t be empty.', 'wpvivid-backuprestore');
        }
        $data['restore_max_execution_time']=sanitize_text_field($data['restore_max_execution_time']);
        if(empty($data['restore_max_execution_time']) && $data['restore_max_execution_time'] != '0')
        {
            $ret['error']=__('The value of \'PHP scripts execution timeout for restore\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        if(!isset($data['memory_limit']))
        {
            $ret['error']=__('The value of \'PHP memory limit for backup\' can\'t be empty.', 'wpvivid-backuprestore');
        }
        $data['memory_limit']=sanitize_text_field($data['memory_limit']);
        if(empty($data['memory_limit']) && $data['memory_limit'] != '0')
        {
            $ret['error']=__('The value of \'PHP memory limit for backup\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        if(!isset($data['restore_memory_limit']))
        {
            $ret['error']=__('The value of \'PHP memory limit for restoration\' can\'t be empty.', 'wpvivid-backuprestore');
        }
        $data['restore_memory_limit']=sanitize_text_field($data['restore_memory_limit']);
        if(empty($data['restore_memory_limit']) && $data['restore_memory_limit'] != '0')
        {
            $ret['error']=__('The value of \'PHP memory limit for restoration\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        if(!isset($data['migrate_size']))
        {
            $ret['error']=__('The value of \'Chunk Size\' can\'t be empty.', 'wpvivid-backuprestore');
        }
        $data['migrate_size']=sanitize_text_field($data['migrate_size']);
        if(empty($data['migrate_size']) && $data['migrate_size'] != '0')
        {
            $ret['error']=__('The value of \'Chunk Size\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        $data['compress_file_count']=sanitize_text_field($data['compress_file_count']);
        if(!isset($data['compress_file_count']) || empty($data['compress_file_count']))
        {
            $ret['error']=__('The value of \'The number of files compressed to the backup zip each time\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        $data['max_sql_file_size']=sanitize_text_field($data['max_sql_file_size']);
        if(!isset($data['max_sql_file_size']) || empty($data['max_sql_file_size']))
        {
            $ret['error']=__('The value of \'Split a sql file every this size\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        $data['replace_rows_pre_request']=sanitize_text_field($data['replace_rows_pre_request']);
        if(!isset($data['replace_rows_pre_request']) || empty($data['replace_rows_pre_request']))
        {
            $ret['error']=__('The value of \'Maximum rows of data to be processed per request for restoration\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        $data['sql_file_buffer_pre_request']=sanitize_text_field($data['sql_file_buffer_pre_request']);
        if(!isset($data['sql_file_buffer_pre_request']) || empty($data['sql_file_buffer_pre_request']))
        {
            $ret['error']=__('The value of \'Maximum size of sql file to be imported per request for restoration\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        if(!isset($data['wpvivid_uc_scan_limit']))
        {
            $ret['error']=__('The value of \'Posts Quantity Processed Per Request\' can\'t be empty.', 'wpvivid-backuprestore');
        }
        $data['wpvivid_uc_scan_limit']=sanitize_text_field($data['wpvivid_uc_scan_limit']);
        if(empty($data['wpvivid_uc_scan_limit']) && $data['wpvivid_uc_scan_limit'] != '0')
        {
            $ret['error']=__('The value of \'Posts Quantity Processed Per Request\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        if(!isset($data['wpvivid_uc_files_limit']))
        {
            $ret['error']=__('The value of \'Media Files Quantity Processed Per Request\' can\'t be empty.', 'wpvivid-backuprestore');
        }
        $data['wpvivid_uc_files_limit']=sanitize_text_field($data['wpvivid_uc_files_limit']);
        if(empty($data['wpvivid_uc_files_limit']) && $data['wpvivid_uc_files_limit'] != '0')
        {
            $ret['error']=__('The value of \'Media Files Quantity Processed Per Request\' can\'t be empty.', 'wpvivid-backuprestore');
            return $ret;
        }

        if(!$this->wpvivid_check_staging_pro_active())
        {
            if(!isset($data['staging_db_insert_count']))
            {
                $ret['error']=__('The value of \'DB Copy Count\' can\'t be empty.', 'wpvivid-backuprestore');
            }
            $data['staging_db_insert_count']=sanitize_text_field($data['staging_db_insert_count']);
            if(empty($data['staging_db_insert_count']) && $data['staging_db_insert_count'] != '0')
            {
                $ret['error']=__('The value of \'DB Copy Count\' can\'t be empty.', 'wpvivid-backuprestore');
                return $ret;
            }

            if(!isset($data['staging_db_replace_count']))
            {
                $ret['error']=__('The value of \'DB Replace Count\' can\'t be empty.', 'wpvivid-backuprestore');
            }
            $data['staging_db_replace_count']=sanitize_text_field($data['staging_db_replace_count']);
            if(empty($data['staging_db_replace_count']) && $data['staging_db_replace_count'] != '0')
            {
                $ret['error']=__('The value of \'DB Replace Count\' can\'t be empty.', 'wpvivid-backuprestore');
                return $ret;
            }

            if(!isset($data['staging_file_copy_count']))
            {
                $ret['error']=__('The value of \'File Copy Count\' can\'t be empty.', 'wpvivid-backuprestore');
            }
            $data['staging_file_copy_count']=sanitize_text_field($data['staging_file_copy_count']);
            if(empty($data['staging_file_copy_count']) && $data['staging_file_copy_count'] != '0')
            {
                $ret['error']=__('The value of \'File Copy Count\' can\'t be empty.', 'wpvivid-backuprestore');
                return $ret;
            }

            if(!isset($data['staging_exclude_file_size']))
            {
                $ret['error']=__('The value of \'Max File Size\' can\'t be empty.', 'wpvivid-backuprestore');
            }
            $data['staging_exclude_file_size']=sanitize_text_field($data['staging_exclude_file_size']);
            if(empty($data['staging_exclude_file_size']) && $data['staging_exclude_file_size'] != '0')
            {
                $ret['error']=__('The value of \'Max File Size\' can\'t be empty.', 'wpvivid-backuprestore');
                return $ret;
            }

            if(!isset($data['staging_memory_limit']))
            {
                $ret['error']=__('The value of \'Staging Memory Limit\' can\'t be empty.', 'wpvivid-backuprestore');
            }
            $data['staging_memory_limit']=sanitize_text_field($data['staging_memory_limit']);
            if(empty($data['staging_memory_limit']) && $data['staging_memory_limit'] != '0')
            {
                $ret['error']=__('The value of \'Staging Memory Limit\' can\'t be empty.', 'wpvivid-backuprestore');
                return $ret;
            }

            if(!isset($data['staging_max_execution_time']))
            {
                $ret['error']=__('The value of \'PHP Scripts Execution Timeout\' can\'t be empty.', 'wpvivid-backuprestore');
            }
            $data['staging_max_execution_time']=sanitize_text_field($data['staging_max_execution_time']);
            if(empty($data['staging_max_execution_time']) && $data['staging_max_execution_time'] != '0')
            {
                $ret['error']=__('The value of \'PHP Scripts Execution Timeout\' can\'t be empty.', 'wpvivid-backuprestore');
                return $ret;
            }

            if(!isset($data['staging_request_timeout']))
            {
                $ret['error']=__('The value of \'Delay Between Requests\' can\'t be empty.', 'wpvivid-backuprestore');
            }
            $data['staging_request_timeout']=sanitize_text_field($data['staging_request_timeout']);
            if(empty($data['staging_request_timeout']) && $data['staging_request_timeout'] != '0')
            {
                $ret['error']=__('The value of \'Delay Between Requests\' can\'t be empty.', 'wpvivid-backuprestore');
                return $ret;
            }
        }

        //

        if(!isset($data['path']))
        {
            $ret['error']=__('The local storage path is required.', 'wpvivid-backuprestore');
        }

        $data['path']=sanitize_text_field($data['path']);

        if(empty($data['path']))
        {
            $ret['error']=__('The local storage path is required.', 'wpvivid-backuprestore');
            return $ret;
        }

        $data['email_enable']=sanitize_text_field($data['email_enable']);
        $data['send_to']=sanitize_text_field($data['send_to']);
        if($data['email_enable'] == '1')
        {
            if(empty($data['send_to']))
            {
                $ret['error']=__('An email address is required.', 'wpvivid-backuprestore');
                return $ret;
            }
        }

        if(isset($data['db_connect_method']) && $data['db_connect_method'] === 'pdo') {
            if (class_exists('PDO')) {
                $extensions = get_loaded_extensions();
                if (!array_search('pdo_mysql', $extensions)) {
                    $ret['error'] = __('The pdo_mysql extension is not detected. Please install the extension first or choose wpdb option for Database connection method.', 'wpvivid-backuprestore');
                    return $ret;
                }
            } else {
                $ret['error'] = __('The pdo_mysql extension is not detected. Please install the extension first or choose wpdb option for Database connection method.', 'wpvivid-backuprestore');
                return $ret;
            }
        }

        $ret['result']=WPVIVID_SUCCESS;
        return $ret;
    }

    public function check_schedule_option($data)
    {
        $ret['result']=WPVIVID_FAILED;

        $data['enable']=sanitize_text_field($data['enable']);
        $data['save_local_remote']=sanitize_text_field($data['save_local_remote']);

        if(!empty($data['enable'])){
            if($data['enable'] == '1'){
                if(!empty($data['save_local_remote'])){
                    if($data['save_local_remote'] == 'remote'){
                        $remote_storage=WPvivid_Setting::get_remote_options();
                        if($remote_storage == false) {
                            $ret['error']=__('There is no default remote storage configured. Please set it up first.', 'wpvivid-backuprestore');
                            return $ret;
                        }
                    }
                }
            }
        }

        $ret['result']=WPVIVID_SUCCESS;
        return $ret;
    }

    public function export_setting()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try {
            if (isset($_REQUEST['setting']) && !empty($_REQUEST['setting']) && isset($_REQUEST['history']) && !empty($_REQUEST['history']) && isset($_REQUEST['review'])) {
                $setting = sanitize_text_field($_REQUEST['setting']);
                $history = sanitize_text_field($_REQUEST['history']);
                $review = sanitize_text_field($_REQUEST['review']);

                if ($setting == '1') {
                    $setting = true;
                } else {
                    $setting = false;
                }

                if ($history == '1') {
                    $history = true;
                } else {
                    $history = false;
                }

                if ($review == '1') {
                    $review = true;
                } else {
                    $review = false;
                }

                $backup_list = false;

                $json = WPvivid_Setting::export_setting_to_json($setting, $history, $review, $backup_list);

                $parse = wp_parse_url(home_url());
                $path = '';
                if(isset($parse['path'])) {
                    $parse['path'] = str_replace('/', '_', $parse['path']);
                    $parse['path'] = str_replace('.', '_', $parse['path']);
                    $path = $parse['path'];
                }
                $parse['host'] = str_replace('/', '_', $parse['host']);
                $parse['host'] = str_replace('.', '_', $parse['host']);
                $domain_tran = $parse['host'].$path;
                $offset=get_option('gmt_offset');
                $date_format = gmdate("Ymd",time()+$offset*60*60);
                $time_format = gmdate("His",time()+$offset*60*60);
                $export_file_name = apply_filters('wpvivid_white_label_slug', 'wpvivid').'_setting-'.$domain_tran.'-'.$date_format.'-'.$time_format.'.json';
                if (!headers_sent()) {
                    header('Content-Disposition: attachment; filename='.$export_file_name);
                    //header('Content-type: application/json');
                    header('Content-Type: application/force-download');
                    header('Content-Description: File Transfer');
                    header('Cache-Control: must-revalidate');
                    header('Content-Transfer-Encoding: binary');
                }

                echo wp_json_encode($json);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        exit;
    }

    public function import_setting()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        };
        try {
            if (isset($_POST['data']) && !empty($_POST['data']) && is_string($_POST['data'])) {
                $data = sanitize_text_field($_POST['data']);
                $data = stripslashes($data);
                $json = json_decode($data, true);
                if (is_null($json)) {
                    die();
                }
                if (json_last_error() === JSON_ERROR_NONE && is_array($json) && array_key_exists('plugin', $json) && $json['plugin'] == 'WPvivid') {
                    $json = apply_filters('wpvivid_trim_import_info', $json);
                    WPvivid_Setting::import_json_to_setting($json);
                    //WPvivid_Schedule::reset_schedule();
                    do_action('wpvivid_reset_schedule');
                    $ret['result'] = 'success';
                    $ret['slug'] = apply_filters('wpvivid_white_label_slug', 'WPvivid');
                    echo wp_json_encode($ret);
                } else {
                    $ret['result'] = 'failed';
                    $ret['error'] = __('The selected file is not the setting file for WPvivid. Please upload the right file.', 'wpvivid-backuprestore');
                    echo wp_json_encode($ret);
                }
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function test_send_mail()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (isset($_POST['send_to']) && !empty($_POST['send_to']) && is_string($_POST['send_to'])) {
                $send_to = sanitize_email($_POST['send_to']);
                if (empty($send_to)) {
                    $ret['result'] = 'failed';
                    $ret['error'] = __('Invalid email address', 'wpvivid-backuprestore');
                    echo wp_json_encode($ret);
                } else {
                    $subject = 'WPvivid Test Mail';
                    $body = 'This is a test mail from WPvivid backup plugin';
                    $headers = array('Content-Type: text/html; charset=UTF-8');
                    if (wp_mail($send_to, $subject, $body, $headers) === false) {
                        $ret['result'] = 'failed';
                        $ret['error'] = __('Unable to send email. Please check the configuration of email server.', 'wpvivid-backuprestore');
                    } else {
                        $ret['result'] = 'success';
                    }
                    echo wp_json_encode($ret);
                }
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function create_debug_package()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            $files = WPvivid_error_log::get_error_log();
            $staging_files = WPvivid_error_log::get_staging_error_log();

            if (!class_exists('WPvivid_PclZip'))
                include_once WPVIVID_PLUGIN_DIR . '/includes/zip/class-wpvivid-pclzip.php';

            $backup_path = WPvivid_Setting::get_backupdir();
            $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . 'wpvivid_debug.zip';

            if (file_exists($path)) {
                @wp_delete_file($path);
            }
            $archive = new WPvivid_PclZip($path);

            if (!empty($files)) {
                if (!$archive->add($files, WPVIVID_PCLZIP_OPT_REMOVE_ALL_PATH)) {
                    echo esc_html($archive->errorInfo(true)) . ' <a href="' . esc_url(admin_url()) . 'admin.php?page=WPvivid">retry</a>.';
                    exit;
                }
            }

            if (!empty($staging_files)) {
                if (!$archive->add($staging_files, WPVIVID_PCLZIP_OPT_REMOVE_ALL_PATH)) {
                    echo esc_html($archive->errorInfo(true)) . ' <a href="' . esc_url(admin_url()) . 'admin.php?page=WPvivid">retry</a>.';
                    exit;
                }
            }

            $server_info = wp_json_encode($this->get_website_info());
            $server_file_path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . 'wpvivid_server_info.json';
            if (file_exists($server_file_path)) {
                @wp_delete_file($server_file_path);
            }
            $server_file = fopen($server_file_path, 'x');
            fclose($server_file);
            file_put_contents($server_file_path, $server_info);
            if (!$archive->add($server_file_path, WPVIVID_PCLZIP_OPT_REMOVE_ALL_PATH)) {
                echo esc_html($archive->errorInfo(true)) . ' <a href="' . esc_url(admin_url()) . 'admin.php?page=WPvivid">retry</a>.';
                exit;
            }
            @wp_delete_file($server_file_path);

            if (session_id())
                session_write_close();

            $size = filesize($path);
            if (!headers_sent()) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                header('Cache-Control: must-revalidate');
                header('Content-Length: ' . $size);
                header('Content-Transfer-Encoding: binary');
            }


            ob_end_clean();
            readfile($path);
            @wp_delete_file($path);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        exit;
    }

    public function get_log_list()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            $ret['result'] = 'success';
            $html = '';
            $html = apply_filters('wpvivid_get_log_list', $html);
            $ret['html'] = $html['html'];
            $ret['log_count'] = $html['log_count'];
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function wpvivid_get_log_list($html)
    {
        $loglist=$this->get_log_list_ex();
        $current_num=1;
        $max_log_diaplay=20;
        $log_index=0;
        $pic_log='/admin/partials/images/Log.png';
        if(!empty($loglist['log_list']['file'])) {
            foreach ($loglist['log_list']['file'] as $value) {
                if ($current_num <= $max_log_diaplay) {
                    $log_tr_display = '';
                } else {
                    $log_tr_display = 'display: none;';
                }
                if (empty($value['time'])) {
                    $value['time'] = 'N/A';
                }
                else{
                    $offset=get_option('gmt_offset');
                    $localtime = strtotime($value['time'])/* + $offset * 60 * 60*/;
                    $value['time'] = gmdate('F-d-Y H:i:s',$localtime);
                }
                if (empty($value['des'])) {
                    $value['des'] = 'N/A';
                }
                $value['path'] = str_replace('\\', '/', $value['path']);
                $html .= '<tr style="'.esc_attr($log_tr_display, 'wpvivid-backuprestore').'">
                <td class="row-title"><label for="tablecell">'.$value['time'].'</label>
                </td>
                <td>'.$value['des'].'</td>
                <td>'.$value['file_name'].'</td>
                <td>
                    <a onclick="wpvivid_read_log(\''.'wpvivid_view_log'.'\', \''.$value['id'].'\', \''.'backup'.'\')" style="cursor:pointer;">
                    <img src="'.esc_url(WPVIVID_PLUGIN_URL.$pic_log).'" style="vertical-align:middle;">Log
                    </a>
                </td>
                </tr>';
                $log_index++;
                $current_num++;
            }
        }
        $ret['log_count']=$log_index;
        $ret['html']=$html;
        return $ret;
    }

    public function wpvivid_get_log_list_output()
    {
        $loglist=$this->get_log_list_ex();
        $current_num=1;
        $max_log_diaplay=20;
        $pic_log='/admin/partials/images/Log.png';
        if(!empty($loglist['log_list']['file'])) {
            foreach ($loglist['log_list']['file'] as $value) {
                if ($current_num <= $max_log_diaplay) {
                    $log_tr_display = '';
                } else {
                    $log_tr_display = 'display: none;';
                }
                if (empty($value['time'])) {
                    $value['time'] = 'N/A';
                }
                else{
                    $localtime = strtotime($value['time']);
                    $value['time'] = gmdate('F-d-Y H:i:s',$localtime);
                }
                if (empty($value['des'])) {
                    $value['des'] = 'N/A';
                }
                $value['path'] = str_replace('\\', '/', $value['path']);
                ?>
                <tr style="<?php echo esc_attr($log_tr_display); ?>">
                    <td class="row-title"><label for="tablecell"><?php echo esc_html($value['time']); ?></label>
                    </td>
                    <td><?php echo esc_html($value['des']); ?></td>
                    <td><?php echo esc_html($value['file_name']); ?></td>
                    <td>
                        <a onclick="wpvivid_read_log('wpvivid_view_log', '<?php echo esc_js($value['id']); ?>', 'backup');" style="cursor:pointer;">
                            <img src="<?php echo esc_url(WPVIVID_PLUGIN_URL.$pic_log); ?>" style="vertical-align:middle;">Log
                        </a>
                    </td>
                </tr>
                <?php
                $current_num++;
            }
        }
    }

    public function get_log_list_ex()
    {
        $ret['log_list']['file']=array();

        if(!class_exists('WPvivid_Log'))
        {
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-log.php';
        }

        $log=new WPvivid_Log();
        $dir=$log->GetSaveLogFolder();
        $files=array();
        $handler=opendir($dir);
        $regex='#^wpvivid.*_log.txt#';
        if($handler!==false)
        {
            while(($filename=readdir($handler))!==false)
            {
                if($filename != "." && $filename != "..")
                {
                    if(is_dir($dir.$filename))
                    {
                        continue;
                    }else{
                        if(preg_match($regex,$filename))
                        {
                            $files[$filename] = $dir.$filename;
                        }
                    }
                }
            }
            if($handler)
                @closedir($handler);
        }

        $dir.='error'.DIRECTORY_SEPARATOR;
        if(file_exists($dir))
        {
            $handler=opendir($dir);
            if($handler!==false)
            {
                while(($filename=readdir($handler))!==false)
                {
                    if($filename != "." && $filename != "..")
                    {
                        if(is_dir($dir.$filename))
                        {
                            continue;
                        }else {
                            if(preg_match($regex,$filename))
                            {
                                $files[$filename] = $dir.$filename;
                            }
                        }
                    }
                }
                if($handler)
                    @closedir($handler);
            }
        }


        foreach ($files as $file)
        {
            $handle = @fopen($file, "r");
            if ($handle)
            {
                $log_file['file_name']=basename($file);
                $log_file['id']='';
                if(preg_match('/wpvivid-(.*?)_/', basename($file), $matches))
                {
                    $id= $matches[0];
                    $id=substr($id,0,strlen($id)-1);
                    $log_file['id']=$id;
                }
                $log_file['path']=$file;
                $log_file['des']='';
                $log_file['time']='';
                if(preg_match('/error/', $file))
                {
                    $log_file['result']='failed';
                }
                else
                {
                    $log_file['result']='success';
                }
                $line = fgets($handle);
                if($line!==false)
                {
                    $pos=strpos($line,'Log created: ');
                    if($pos!==false)
                    {
                        $log_file['time']=substr ($line,$pos+strlen('Log created: '));
                    }
                }
                $line = fgets($handle);
                if($line!==false)
                {
                    $pos=strpos($line,'Type: ');
                    if($pos!==false)
                    {
                        $log_file['des']=substr ($line,$pos+strlen('Type: '));
                    }
                }

                fclose($handle);
                $ret['log_list']['file'][basename($file)]=$log_file;
            }
        }

        $ret['log_list']['file'] =$this->sort_list($ret['log_list']['file']);

        return $ret;
    }

    public function sort_list($list)
    {
        uasort ($list,function($a, $b)
        {
            if($a['time']>$b['time'])
            {
                return -1;
            }
            else if($a['time']===$b['time'])
            {
                return 0;
            }
            else
            {
                return 1;
            }
        });

        return $list;
    }

    public function view_log()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (isset($_POST['id']) && !empty($_POST['id']) && is_string($_POST['id'])) {
                $id = sanitize_text_field($_POST['id']);

                $path = '';

                if(isset($_POST['log_type']))
                {
                    $log_type = sanitize_text_field($_POST['log_type']);
                }
                else
                {
                    $log_type = 'backup';
                }
                if($log_type === 'backup')
                {
                    $loglist=$this->get_log_list_ex();
                }
                else
                {
                    $log_page=new WPvivid_Staging_Log_Page_Free();
                    $loglist=$log_page->get_log_list('staging');
                }

                if(!empty($loglist['log_list']['file']))
                {
                    foreach ($loglist['log_list']['file'] as $value)
                    {
                        if($value['id'] === $id)
                        {
                            $path = str_replace('\\', '/', $value['path']);
                            break;
                        }
                    }
                }

                if (!file_exists($path)) {
                    $json['result'] = 'failed';
                    $json['error'] = __('The log not found.', 'wpvivid-backuprestore');
                    echo wp_json_encode($json);
                    die();
                }

                $file = fopen($path, 'r');

                if (!$file) {
                    $json['result'] = 'failed';
                    $json['error'] = __('Unable to open the log file.', 'wpvivid-backuprestore');
                    echo wp_json_encode($json);
                    die();
                }

                $buffer = '';
                while (!feof($file)) {
                    $buffer .= fread($file, 1024);
                }
                fclose($file);

                $json['result'] = 'success';
                $json['data'] = $buffer;
                echo wp_json_encode($json);
            } else {
                $json['result'] = 'failed';
                $json['error'] = __('Reading the log failed. Please try again.', 'wpvivid-backuprestore');
                echo wp_json_encode($json);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function get_website_info()
    {
        try {
            $version = $this->version;
            $version = apply_filters('wpvivid_display_pro_version', $version);
            $ret['result'] = 'success';
            $ret['data']['version'] = $version;
            $ret['data']['home_url'] = get_home_url();
            $ret['data']['abspath'] = ABSPATH;
            $ret['data']['wp_content_path'] = WP_CONTENT_DIR;
            $ret['data']['wp_plugin_path'] = WP_PLUGIN_DIR;
            $ret['data']['active_plugins'] = get_option('active_plugins');

            global $wp_version;
            $ret['wp_version'] = $wp_version;
            if (is_multisite()) {
                $ret['data']['multisite'] = 'enable';
            } else {
                $ret['data']['multisite'] = 'disable';
            }
            $ret['data']['web_server'] = $_SERVER["SERVER_SOFTWARE"];
            $ret['data']['php_version'] = phpversion();
            global $wpdb;
            $ret['data']['mysql_version'] = $wpdb->db_version();
            if (defined('WP_DEBUG')) {
                $ret['data']['wp_debug'] = WP_DEBUG;
            } else {
                $ret['wp_debug'] = false;
            }
            $ret['data']['language'] = get_bloginfo('language');
            $ret['data']['upload_max_filesize'] = ini_get("upload_max_filesize");

            $options = WPvivid_Setting::get_option('wpvivid_common_setting');
            if (isset($options['max_execution_time'])) {
                $limit = $options['max_execution_time'];
            } else {
                $limit = WPVIVID_MAX_EXECUTION_TIME;
            }
            ini_set('max_execution_time', $limit);

            $current_offset = get_option( 'gmt_offset' );
            $timezone       = get_option( 'timezone_string' );

            if ( false !== strpos( $timezone, 'Etc/GMT' ) ) {
                $timezone = '';
            }

            if ( empty( $timezone ) ) {
                if ( 0 == $current_offset ) {
                    $timezone = 'UTC+0';
                } elseif ( $current_offset < 0 ) {
                    $timezone = 'UTC' . $current_offset;
                } else {
                    $timezone = 'UTC+' . $current_offset;
                }
            }

            $ret['data']['max_execution_time'] = ini_get("max_execution_time");
            $ret['data']['max_input_vars'] = ini_get("max_input_vars");
            $ret['data']['max_input_vars'] = ini_get("max_input_vars");
            $ret['data']['timezone'] = $timezone;//date_default_timezone_get();
            if(function_exists('php_uname'))
            {
                $ret['data']['OS'] = php_uname();
            }
            $ret['data']['memory_current'] = $this->formatBytes(memory_get_usage());
            $ret['data']['memory_peak'] = $this->formatBytes(memory_get_peak_usage());
            $ret['data']['memory_limit'] = ini_get('memory_limit');
            $ret['data']['post_max_size'] = ini_get('post_max_size');
            $ret['data']['allow_url_fopen'] = ini_get('allow_url_fopen');
            $ret['data']['safe_mode'] = ini_get('safe_mode');
            $ret['data']['pcre.backtrack_limit'] = ini_get('pcre.backtrack_limit');
            $extensions = get_loaded_extensions();
            if (array_search('exif', $extensions)) {
                $ret['data']['exif'] = 'support';
            } else {
                $ret['data']['exif'] = 'not support';
            }

            if (array_search('xml', $extensions)) {
                $ret['data']['xml'] = 'support';
            } else {
                $ret['data']['xml'] = 'not support';
            }

            if (array_search('suhosin', $extensions)) {
                $ret['data']['suhosin'] = 'support';
            } else {
                $ret['data']['suhosin'] = 'not support';
            }

            if (array_search('gd', $extensions)) {
                $ret['data']['IPTC'] = 'support';
            } else {
                $ret['data']['IPTC'] = 'not support';
            }

            $ret['data']['extensions'] = $extensions;

            if (function_exists('apache_get_modules')) {
                $ret['data']['apache_modules'] = apache_get_modules();
            } else {
                $ret['data']['apache_modules'] = array();
            }

            if (array_search('pdo_mysql', $extensions)) {
                $ret['data']['pdo_mysql'] = 'support';
            } else {
                $ret['data']['pdo_mysql'] = 'not support';
            }

            if ($ret['data']['pdo_mysql'] == 'support') {
                include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-db-method.php';
                $db_method = new WPvivid_DB_Method();
                $ret_sql_mode = $db_method->get_sql_mode();
                if ($ret_sql_mode['result'] == WPVIVID_FAILED) {
                    $ret['data']['mysql_mode'] = '';
                } else {
                    $ret['data']['mysql_mode'] = $ret_sql_mode['mysql_mode'];
                    $ret['mysql_mode'] = $ret_sql_mode['mysql_mode'];
                }
            } else {
                $ret['data']['mysql_mode'] = '';
            }
            if (!class_exists('PclZip')) include_once(ABSPATH . '/wp-admin/includes/class-pclzip.php');
            if (!class_exists('PclZip')) {
                $ret['data']['PclZip'] = 'not support';
            } else {
                $ret['data']['PclZip'] = 'support';
            }

            if (is_multisite() && !defined('MULTISITE')) {
                $prefix = $wpdb->base_prefix;
            } else {
                $prefix = $wpdb->get_blog_prefix(0);
            }

            $ret['data']['wp_prefix'] = $prefix;

            $sapi_type = php_sapi_name();

            if ($sapi_type == 'cgi-fcgi' || $sapi_type == ' fpm-fcgi') {
                $ret['data']['fast_cgi'] = 'On';
            } else {
                $ret['data']['fast_cgi'] = 'Off';
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            return array('result'=>'failed','error'=>$message);
        }
        return $ret;
    }

    public function wpvivid_add_backup_list($html, $list_name = 'wpvivid_backup_list', $tour = false)
    {
        $html = '';
        $backuplist=WPvivid_Backuplist::get_backuplist($list_name);
        $remote=array();
        $remote=apply_filters('wpvivid_remote_pic', $remote);
        $upload_title = '';
        foreach ($backuplist as $key=>$value) {
            if($value['type'] !== 'Rollback') {
                $row_style = '';
                if ($value['type'] == 'Migration' || $value['type'] == 'Upload') {
                    if ($value['type'] == 'Migration') {
                        $upload_title = 'Received Backup: ';
                    } else if ($value['type'] == 'Upload') {
                        $upload_title = __('Uploaded Backup: ', 'wpvivid-backuprestore');
                    }
                    $row_style = 'border: 2px solid #006799; box-sizing:border-box; -moz-box-sizing:border-box; -webkit-box-sizing:border-box;';
                } else if ($value['type'] == 'Manual' || $value['type'] == 'Cron') {
                    $row_style = '';
                    $upload_title = '';
                } else {
                    $upload_title = '';
                }

                if (empty($value['lock'])) {
                    $backup_lock = '/admin/partials/images/unlocked.png';
                    $lock_status = 'unlock';
                } else {
                    if ($value['lock'] == 0) {
                        $backup_lock = '/admin/partials/images/unlocked.png';
                        $lock_status = 'unlock';
                    } else {
                        $backup_lock = '/admin/partials/images/locked.png';
                        $lock_status = 'lock';
                    }
                }

                $backup_size=0;
                $backup_time=$value['create_time'];
                if(isset($value['backup']['files'])){
                    foreach ($value['backup']['files'] as $file_info){
                        $backup_size+=$file_info['size'];
                        if(preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}/',$file_info['file_name'],$matches))
                        {
                            $backup_date=$matches[0];
                        }
                        else
                        {
                            $backup_date=$value['create_time'];
                        }

                        $time_array=explode('-',$backup_date);
                        if(sizeof($time_array)>4){
                            $time=$time_array[0].'-'.$time_array[1].'-'.$time_array[2].' '.$time_array[3].':'.$time_array[4];
                            $backup_time=strtotime($time);
                        }
                        //break;
                    }
                }

                $remote_pic_html = '';
                $save_local_pic_y = '/admin/partials/images/storage-local.png';
                $save_local_pic_n = '/admin/partials/images/storage-local(gray).png';
                $local_title = 'Localhost';
                if ($value['save_local'] == 1 || $value['type'] == 'Migration') {
                    $remote_pic_html .= '<img  src="' . esc_url(WPVIVID_PLUGIN_URL . $save_local_pic_y) . '" style="vertical-align:middle; " title="' . $local_title . '"/>';
                } else {
                    $remote_pic_html .= '<img  src="' . esc_url(WPVIVID_PLUGIN_URL . $save_local_pic_n) . '" style="vertical-align:middle; " title="' . $local_title . '"/>';
                }
                $b_has_remote = false;
                if (is_array($remote)) {
                    foreach ($remote as $key1 => $value1) {
                        foreach ($value['remote'] as $storage_type) {
                            $b_has_remote = true;
                            if ($key1 === $storage_type['type']) {
                                $pic = $value1['selected_pic'];
                            } else {
                                $pic = $value1['default_pic'];
                            }
                        }
                        if (!$b_has_remote) {
                            $pic = $value1['default_pic'];
                        }
                        $title = $value1['title'];
                        $remote_pic_html .= '<img  src="' . esc_url(WPVIVID_PLUGIN_URL . $pic) . '" style="vertical-align:middle; " title="' . $title . '"/>';
                    }
                }
                if ($tour) {
                    $tour = false;
                    $tour_message = '<div class="wpvivid-popuptext" id="wpvivid_popup_tour">' . esc_html__('Click the button to complete website restore or migration', 'wpvivid-backuprestore') . '</div>';
                    $tour_class = 'wpvivid-popup';
                } else {
                    $tour_message = '';
                    $tour_class = '';
                }

                $hide = 'hide';
                $html .= '<tr style="' . $row_style . '">
                <th class="check-column"><input name="check_backup" type="checkbox" id="' . esc_attr($key, 'wpvivid-backuprestore') . '" value="' . esc_attr($key, 'wpvivid-backuprestore') . '" onclick="wpvivid_click_check_backup(\'' . $key . '\', \'' . $list_name . '\');" /></th>
                <td class="tablelistcolumn">
                    <div style="float:left;padding:0 10px 10px 0;">
                        <div class="backuptime"><strong>' . $upload_title . '</strong>' . gmdate('M-d-Y H:i', $backup_time) . '</div>
                        <div class="common-table">
                            <span title="To lock the backup, the backup can only be deleted manually" id="wpvivid_lock_' . $key . '">
                            <img src="' . esc_url(WPVIVID_PLUGIN_URL . $backup_lock) . '" name="' . esc_attr($lock_status, 'wpvivid-backuprestore') . '" onclick="wpvivid_set_backup_lock(\'' . $key . '\', \'' . $lock_status . '\');" style="vertical-align:middle; cursor:pointer;"/>
                            </span>
                            <span style="margin:0;">|</span> <span>' . __('Type:', 'wpvivid-backuprestore') . '</span><span>' . $value['type'] . '</span>
                            <span style="margin:0;">|</span> <span title="Backup log"><a href="#" onclick="wpvivid_read_log(\'' . 'wpvivid_view_backup_log' . '\', \'' . $key . '\');"><img src="' . esc_url(WPVIVID_PLUGIN_URL . '/admin/partials/images/Log.png') . '" style="vertical-align:middle;cursor:pointer;"/><span style="margin:0;">' . __('Log', 'wpvivid-backuprestore') . '</span></a></span>
                        </div>
                    </div>
                </td>
                <td class="tablelistcolumn">
                    <div style="float:left;padding:10px 10px 10px 0;">' . $remote_pic_html . '</div>
                </td>
               
                <td class="tablelistcolumn" style="min-width:100px;">
                    <div id="wpvivid_file_part_' .$key . '" style="float:left;padding:10px 10px 10px 0;">
                        <div style="cursor:pointer;" onclick="wpvivid_initialize_download(\'' . $key . '\', \'' . $list_name . '\');" title="'. esc_html__('Prepare to download the backup', 'wpvivid-backuprestore') .'">
                            <img id="wpvivid_download_btn_' . $key . '" src="' . esc_url(WPVIVID_PLUGIN_URL . '/admin/partials/images/download.png') . '" style="vertical-align:middle;" /><span>' . __('Download', 'wpvivid-backuprestore') . ' (' . esc_html(size_format($backup_size, 2)) . ')' .'</span>
                            <div class="spinner" id="wpvivid_download_loading_' . $key . '" style="float:right;width:auto;height:auto;padding:10px 180px 10px 0;background-position:0 0;"></div>
                        </div>
                    </div>
                </td>
                <td class="tablelistcolumn" style="min-width:100px;">
                    <div class="' . $tour_class . '" onclick="wpvivid_popup_tour(\'' . $hide . '\');">
                        ' . $tour_message . '<div style="cursor:pointer;padding:10px 0 10px 0;" onclick="wpvivid_initialize_restore(\'' . $key . '\',\'' . gmdate('M-d-Y H:i', $backup_time) . '\',\'' . $value['type'] . '\');" style="float:left;padding:10px 10px 10px 0;">
                            <img src="' . esc_url(WPVIVID_PLUGIN_URL . '/admin/partials/images/Restore.png') . '" style="vertical-align:middle;" /><span>' . __('Restore', 'wpvivid-backuprestore') . '</span>
                        </div>
                    </div>
                </td>
                <td class="tablelistcolumn">
                    <div class="backuplist-delete-backup" style="padding:10px 0 10px 0;">
                        <img src="' . esc_url(WPVIVID_PLUGIN_URL . '/admin/partials/images/Delete.png') . '" style="vertical-align:middle; cursor:pointer;" title="'. __('Delete the backup', 'wpvivid-backuprestore') .'" onclick="wpvivid_delete_selected_backup(\'' . $key . '\', \'' . $list_name . '\');"/>
                    </div>
                </td>
            </tr>';
            }
        }
        return $html;
    }

    public function wpvivid_add_backup_list_output()
    {
        $list_name = 'wpvivid_backup_list';
        $backuplist=WPvivid_Backuplist::get_backuplist($list_name);
        $remote=array();
        $remote=apply_filters('wpvivid_remote_pic', $remote);
        $upload_title = '';
        foreach ($backuplist as $key=>$value) {
            if($value['type'] !== 'Rollback') {
                $row_style = '';
                if ($value['type'] == 'Migration' || $value['type'] == 'Upload') {
                    if ($value['type'] == 'Migration') {
                        $upload_title = 'Received Backup: ';
                    } else if ($value['type'] == 'Upload') {
                        $upload_title = __('Uploaded Backup: ', 'wpvivid-backuprestore');
                    }
                    $row_style = 'border: 2px solid #006799; box-sizing:border-box; -moz-box-sizing:border-box; -webkit-box-sizing:border-box;';
                } else if ($value['type'] == 'Manual' || $value['type'] == 'Cron') {
                    $row_style = '';
                    $upload_title = '';
                } else {
                    $upload_title = '';
                }

                if (empty($value['lock'])) {
                    $backup_lock = '/admin/partials/images/unlocked.png';
                    $lock_status = 'unlock';
                } else {
                    if ($value['lock'] == 0) {
                        $backup_lock = '/admin/partials/images/unlocked.png';
                        $lock_status = 'unlock';
                    } else {
                        $backup_lock = '/admin/partials/images/locked.png';
                        $lock_status = 'lock';
                    }
                }

                $backup_size=0;
                $backup_time=$value['create_time'];
                if(isset($value['backup']['files'])){
                    foreach ($value['backup']['files'] as $file_info){
                        $backup_size+=$file_info['size'];
                        if(preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}/',$file_info['file_name'],$matches))
                        {
                            $backup_date=$matches[0];
                        }
                        else
                        {
                            $backup_date=$value['create_time'];
                        }

                        $time_array=explode('-',$backup_date);
                        if(sizeof($time_array)>4){
                            $time=$time_array[0].'-'.$time_array[1].'-'.$time_array[2].' '.$time_array[3].':'.$time_array[4];
                            $backup_time=strtotime($time);
                        }
                        //break;
                    }
                }

                $hide = 'hide';
                ?>
                <tr style="<?php echo esc_attr($row_style); ?>">
                    <th class="check-column"><input name="check_backup" type="checkbox" id="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($key); ?>" onclick="wpvivid_click_check_backup('<?php echo esc_js($key); ?>', '<?php echo esc_js($list_name); ?>');" /></th>
                    <td class="tablelistcolumn">
                        <div style="float:left;padding:0 10px 10px 0;">
                            <div class="backuptime"><strong><?php echo esc_html($upload_title); ?></strong><?php echo esc_html(gmdate('M-d-Y H:i', $backup_time)); ?></div>
                            <div class="common-table">
                            <span title="To lock the backup, the backup can only be deleted manually" id="wpvivid_lock_<?php echo esc_attr($key); ?>">
                            <img src="<?php echo esc_url(WPVIVID_PLUGIN_URL . $backup_lock); ?>" name="<?php echo esc_attr($lock_status); ?>" onclick="wpvivid_set_backup_lock('<?php echo esc_js($key); ?>', '<?php echo esc_js($lock_status); ?>');" style="vertical-align:middle; cursor:pointer;"/>
                            </span>
                                <span style="margin:0;">|</span> <span><?php esc_html_e('Type: ', 'wpvivid-backuprestore'); ?></span><span><?php echo esc_html($value['type']); ?></span>
                                <span style="margin:0;">|</span> <span title="Backup log"><a href="#" onclick="wpvivid_read_log('wpvivid_view_backup_log', '<?php echo esc_js($key); ?>');"><img src="<?php echo esc_url(WPVIVID_PLUGIN_URL.'/admin/partials/images/Log.png'); ?>" style="vertical-align:middle;cursor:pointer;"/><span style="margin:0;"><?php esc_html_e('Log', 'wpvivid-backuprestore'); ?></span></a></span>
                            </div>
                        </div>
                    </td>
                    <td class="tablelistcolumn">
                        <div style="float:left;padding:10px 10px 10px 0;">
                            <?php
                            $remote_pic_html = '';
                            $save_local_pic_y = '/admin/partials/images/storage-local.png';
                            $save_local_pic_n = '/admin/partials/images/storage-local(gray).png';
                            $local_title = 'Localhost';
                            if ($value['save_local'] == 1 || $value['type'] == 'Migration') {
                                echo '<img  src="'.esc_url(WPVIVID_PLUGIN_URL . $save_local_pic_y).'" style="vertical-align:middle; " title="'.esc_attr($local_title).'"/>';
                            } else {
                                echo '<img  src="'.esc_url(WPVIVID_PLUGIN_URL . $save_local_pic_n).'" style="vertical-align:middle; " title="'.esc_attr($local_title).'"/>';
                            }
                            $b_has_remote = false;
                            if (is_array($remote)) {
                                foreach ($remote as $key1 => $value1) {
                                    $pic = $value1['default_pic'];
                                    foreach ($value['remote'] as $storage_type) {
                                        $b_has_remote = true;
                                        if ($key1 === $storage_type['type']) {
                                            $pic = $value1['selected_pic'];
                                        } else {
                                            $pic = $value1['default_pic'];
                                        }
                                    }
                                    if (!$b_has_remote) {
                                        $pic = $value1['default_pic'];
                                    }
                                    $title = $value1['title'];
                                    echo '<img  src="'.esc_url(WPVIVID_PLUGIN_URL . $pic).'" style="vertical-align:middle; " title="'.esc_attr($title).'"/>';
                                }
                            }
                            ?>
                        </div>
                    </td>
                    <td class="tablelistcolumn" style="min-width:100px;">
                        <div id="wpvivid_file_part_<?php echo esc_attr($key); ?>" style="float:left;padding:10px 10px 10px 0;">
                            <div style="cursor:pointer;" onclick="wpvivid_initialize_download('<?php echo esc_js($key); ?>', '<?php echo esc_js($list_name); ?>');" title="<?php esc_attr_e('Prepare to download the backup', 'wpvivid-backuprestore'); ?>">
                                <img id="wpvivid_download_btn_<?php echo esc_attr($key); ?>" src="<?php echo esc_url(WPVIVID_PLUGIN_URL.'/admin/partials/images/download.png'); ?>" style="vertical-align:middle;" /><span><?php esc_html_e('Download', 'wpvivid-backuprestore'); echo ' ('.esc_html(size_format($backup_size, 2)).')'; ?></span>
                                <div class="spinner" id="wpvivid_download_loading_<?php esc_attr($key); ?>" style="float:right;width:auto;height:auto;padding:10px 180px 10px 0;background-position:0 0;"></div>
                            </div>
                        </div>
                    </td>
                    <td class="tablelistcolumn" style="min-width:100px;">
                        <div onclick="wpvivid_popup_tour('<?php echo esc_js($hide); ?>');">
                            <div style="cursor:pointer;padding:10px 0 10px 0;" onclick="wpvivid_initialize_restore('<?php echo esc_js($key); ?>','<?php echo esc_js(gmdate('M-d-Y H:i', $backup_time)); ?>','<?php echo esc_js($value['type']); ?>');" style="float:left;padding:10px 10px 10px 0;">
                                <img src="<?php echo esc_url(WPVIVID_PLUGIN_URL . '/admin/partials/images/Restore.png'); ?>" style="vertical-align:middle;" /><span><?php esc_html_e('Restore', 'wpvivid-backuprestore'); ?></span>
                            </div>
                        </div>
                    </td>
                    <td class="tablelistcolumn">
                        <div class="backuplist-delete-backup" style="padding:10px 0 10px 0;">
                            <img src="<?php echo esc_url(WPVIVID_PLUGIN_URL . '/admin/partials/images/Delete.png'); ?>" style="vertical-align:middle; cursor:pointer;" title="<?php esc_attr_e('Delete the backup', 'wpvivid-backuprestore'); ?>" onclick="wpvivid_delete_selected_backup('<?php echo esc_js($key); ?>', '<?php echo esc_js($list_name); ?>');"/>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }
    }

    public function wpvivid_add_remote_storage_list($html)
    {
        $html = '';
        $remoteslist=WPvivid_Setting::get_all_remote_options();
        $default_remote_storage='';
        foreach ($remoteslist['remote_selected'] as $value) {
            $default_remote_storage=$value;
        }
        $i=1;
        foreach ($remoteslist as $key=>$value)
        {
            if($key === 'remote_selected')
            {
                continue;
            }
            if ($key === $default_remote_storage)
            {
                $check_status = 'checked';
            }
            else
            {
                $check_status='';
            }
            $storage_type = $value['type'];
            $storage_type=apply_filters('wpvivid_storage_provider_tran', $storage_type);
            $html .= '<tr>
                <td>'.$i++.'</td>
                <td><input type="checkbox" name="remote_storage" value="'.esc_attr($key).'" '.esc_attr($check_status).' /></td>
                <td>'.$storage_type.'</td>
                <td class="row-title"><label for="tablecell">'.$value['name'].'</label></td>
                <td>
                    <div style="float: left;"><img src="'.esc_url(WPVIVID_PLUGIN_URL.'/admin/partials/images/Edit.png').'" onclick="click_retrieve_remote_storage(\''.esc_attr($key).'\',\''.esc_attr($value['type']).'\',\''.esc_attr($value['name']).'\'
                    );" style="vertical-align:middle; cursor:pointer;" title="'. esc_html__('Edit the remote storage', 'wpvivid-backuprestore') .'"/></div>
                    <div><img src="'.esc_url(WPVIVID_PLUGIN_URL.'/admin/partials/images/Delete.png').'" onclick="wpvivid_delete_remote_storage(\''.esc_attr($key).'\'
                    );" style="vertical-align:middle; cursor:pointer;" title="'. esc_html__('Remove the remote storage', 'wpvivid-backuprestore') .'"/></div>
                </td>
                </tr>';
        }
        return $html;
    }

    public function wpvivid_remote_storage($remote_storage){
        $remote_id_array = WPvivid_Setting::get_user_history('remote_selected');
        $remote_storage = false;
        foreach ($remote_id_array as $value){
            $remote_storage = true;
        }
        return $remote_storage;
    }

    public function wpvivid_add_remote_notice($notice_type, $message){
        $html = '';
        if($notice_type) {
            $html .= '<div class="notice notice-success is-dismissible inline"><p>'.$message.'</p>
                                    <button type="button" class="notice-dismiss" onclick="click_dismiss_notice(this);">
                                    <span class="screen-reader-text">Dismiss this notice.</span>
                                    </button>
                                    </div>';

        }
        else{
            $html .= '<div class="notice notice-error"><p>' . $message . '</p></div>';
        }
        return $html;
    }

    public function wpvivid_schedule_add_remote_pic($html){
        $html = '';
        $remoteslist=WPvivid_Setting::get_all_remote_options();
        $default_remote_storage=array();
        foreach ($remoteslist['remote_selected'] as $value) {
            $default_remote_storage[]=$value;
        }
        $remote_storage_type=array();
        foreach ($remoteslist as $key=>$value)
        {
            if(in_array($key, $default_remote_storage))
            {
                $remote_storage_type[]=$value['type'];
            }
        }

        $remote=array();
        $remote=apply_filters('wpvivid_remote_pic', $remote);
        if(is_array($remote)) {
            foreach ($remote as $key => $value) {
                $title = $value['title'];
                if (in_array($key, $remote_storage_type)) {
                    $pic = $value['selected_pic'];
                } else {
                    $pic = $value['default_pic'];
                }
                $url = apply_filters('wpvivid_get_wpvivid_pro_url', WPVIVID_PLUGIN_URL, $key);
                $html .= '<img  src="' . esc_url($url . $pic) . '" style="vertical-align:middle; " title="' . $title . '"/>';
            }
            $html.='<img onclick="wpvivid_click_switch_page(\'wrap\', \'wpvivid_tab_remote_storage\', true);" src="'.esc_url(WPVIVID_PLUGIN_URL.'/admin/partials/images/add-storages.png').'" style="vertical-align:middle;" title="'.esc_attr__('Add a storage', 'wpvivid-backuprestore').'"/>';
        }
        return $html;
    }

    public function wpvivid_schedule_local_remote($html){
        $html = '';
        $schedule=WPvivid_Schedule::get_schedule();
        $backup_local = 'checked';
        $backup_remote = '';
        if($schedule['enable'] == true)
        {
            if($schedule['backup']['remote'] === 1)
            {
                $backup_local = '';
                $backup_remote = 'checked';
            }
            else{
                $backup_local = 'checked';
                $backup_remote = '';
            }
        }
        $html .= '<fieldset>
                   <label title="">
                        <input type="radio" option="schedule" name="save_local_remote" value="local" '.$backup_local.' />
                        <span>'.__( 'Save backups on localhost (web server)', 'wpvivid-backuprestore' ).'</span>
                   </label><br>
                   <label title="">
                        <input type="radio" option="schedule" name="save_local_remote" value="remote" '.$backup_remote.' />
                        <span>'.__( 'Send backups to remote storage (You can choose whether to keep the backup in localhost after it is uploaded to cloud storage in Settings.)', 'wpvivid-backuprestore' ).'</span>
                   </label>
                   <label style="display: none;">
                        <input type="checkbox" option="schedule" name="lock" value="0" />
                   </label>
                   </fieldset>';
        return $html;
    }

    public function wpvivid_get_remote_directory($out_of_date_remote){
        $out_of_date=$this->_get_out_of_date_info();
        $out_of_date_remote='There is no path for remote storage, please set it up first.';

        if($out_of_date['remote_options'] !== false)
        {
            $out_of_date_remote_temp = array();
            foreach ($out_of_date['remote_options'] as $value)
            {
                $out_of_date_remote=apply_filters('wpvivid_get_out_of_date_remote',$out_of_date_remote, $value);
                $value['type']=apply_filters('wpvivid_storage_provider_tran', $value['type']);
                $out_of_date_remote_temp[] = $value['type'].': '.$out_of_date_remote;
            }
            $out_of_date_remote = implode(',', $out_of_date_remote_temp);
        }
        return $out_of_date_remote;
    }

    public function init_remote_option()
    {
        $remoteslist=WPvivid_Setting::get_all_remote_options();
        foreach ($remoteslist as $key=>$value)
        {
            if(!array_key_exists('options',$value))
            {
                continue;
            }
            $remote = array();
            if($value['type'] === 'ftp')
            {
                $remote['host']=$value['options']['host'];
                $remote['username']=$value['options']['username'];
                $remote['password']=$value['options']['password'];
                $remote['path']=$value['options']['path'];
                $remote['name']=$value['options']['name'];
                $remote['passive']=$value['options']['passive'];
                $value['type'] = strtolower($value['type']);
                $remote['type']=$value['type'];
                $remoteslist[$key]=$remote;
            }
            elseif ($value['type'] === 'sftp')
            {
                $remote['host']=$value['options']['host'];
                $remote['username']=$value['options']['username'];
                $remote['password']=$value['options']['password'];
                $remote['path']=$value['options']['path'];
                $remote['name']=$value['options']['name'];
                $remote['port']=$value['options']['port'];
                $value['type'] = strtolower($value['type']);
                $remote['type']=$value['type'];
                $remoteslist[$key]=$remote;
            }
            elseif ($value['type'] === 'amazonS3')
            {
                $remote['classMode']='0';
                $remote['sse']='0';
                $remote['name']=$value['options']['name'];
                $remote['access']=$value['options']['access'];
                $remote['secret']=$value['options']['secret'];
                $remote['s3Path']=$value['options']['s3Path'];
                $value['type'] = strtolower($value['type']);
                $remote['type']=$value['type'];
                $remoteslist[$key]=$remote;
            }
        }
        WPvivid_Setting::update_option('wpvivid_upload_setting',$remoteslist);

        $backuplist=WPvivid_Backuplist::get_backuplist();
        foreach ($backuplist as $key=>$value)
        {
            if(is_array($value['remote']))
            {
                foreach ($value['remote'] as $remote_key=>$storage_type)
                {
                    if(!array_key_exists('options',$storage_type))
                    {
                        continue;
                    }
                    $remote = array();
                    if($storage_type['type'] === 'ftp')
                    {
                        $remote['host']=$storage_type['options']['host'];
                        $remote['username']=$storage_type['options']['username'];
                        $remote['password']=$storage_type['options']['password'];
                        $remote['path']=$storage_type['options']['path'];
                        $remote['name']=$storage_type['options']['name'];
                        $remote['passive']=$storage_type['options']['passive'];
                        $storage_type['type'] = strtolower($storage_type['type']);
                        $remote['type']=$storage_type['type'];
                    }
                    elseif ($storage_type['type'] === 'sftp')
                    {
                        $remote['host']=$storage_type['options']['host'];
                        $remote['username']=$storage_type['options']['username'];
                        $remote['password']=$storage_type['options']['password'];
                        $remote['path']=$storage_type['options']['path'];
                        $remote['name']=$storage_type['options']['name'];
                        $remote['port']=$storage_type['options']['port'];
                        $storage_type['type'] = strtolower($storage_type['type']);
                        $remote['type']=$storage_type['type'];
                    }
                    elseif ($storage_type['type'] === 'amazonS3')
                    {
                        $remote['classMode']='0';
                        $remote['sse']='0';
                        $remote['name']=$storage_type['options']['name'];
                        $remote['access']=$storage_type['options']['access'];
                        $remote['secret']=$storage_type['options']['secret'];
                        $remote['s3Path']=$storage_type['options']['s3Path'];
                        $storage_type['type'] = strtolower($storage_type['type']);
                        $remote['type']=$storage_type['type'];
                    }
                    $backuplist[$key]['remote'][$remote_key]=$remote;
                }
            }
        }
        WPvivid_Setting::update_option('wpvivid_backup_list',$backuplist);
    }

    public function need_review()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (isset($_POST['review']) && !empty($_POST['review']) && is_string($_POST['review'])) {
                $review = sanitize_text_field($_POST['review']);
                if ($review == 'rate-now') {
                    $review_option = 'do_not_ask';
                    echo 'https://wordpress.org/support/plugin/wpvivid-backuprestore/reviews/?filter=5';
                } elseif ($review == 'never-ask') {
                    $review_option = 'do_not_ask';
                    echo '';
                } elseif ($review == 'already-done'){
                    $review_option = 'do_not_ask';
                    echo '';
                } elseif ($review == 'ask-later') {
                    $review_option = 'not';
                    WPvivid_Setting::update_option('cron_backup_count', 0);
                    echo '';
                } else {
                    $review_option = 'not';
                    echo '';
                }
                WPvivid_Setting::update_option('wpvivid_need_review', $review_option);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function wpvivid_send_debug_info(){
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (!isset($_POST['user_mail']) || empty($_POST['user_mail'])) {
                $ret['result'] = 'failed';
                $ret['error'] = __('User\'s email address is required.', 'wpvivid-backuprestore');
            } else {
                $pattern = '/^[a-z0-9]+([._-][a-z0-9]+)*@([0-9a-z]+\.[a-z]{2,14}(\.[a-z]{2})?)$/i';
                if (!preg_match($pattern, $_POST['user_mail'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = __('Please enter a valid email address.', 'wpvivid-backuprestore');
                } else {
                    $user_mail=sanitize_email($_POST['user_mail']);
                    $server_type=sanitize_text_field($_POST['server_type']);
                    $host_provider=sanitize_text_field($_POST['host_provider']);
                    $comment=sanitize_text_field($_POST['comment']);
                    if(!class_exists('WPvivid_mail_report'))
                        include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-mail-report.php';
                    $ret = WPvivid_mail_report::wpvivid_send_debug_info($user_mail,$server_type,$host_provider,$comment);
                }
            }
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function get_ini_memory_limit(){
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            $memory_limit = @ini_get('memory_limit');
            echo esc_html($memory_limit);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function wpvivid_switch_domain_to_folder($domain){
        $parse = wp_parse_url($domain);
        $path = '';
        if(isset($parse['path'])) {
            $parse['path'] = str_replace('/', '_', $parse['path']);
            $parse['path'] = str_replace('.', '_', $parse['path']);
            $path = $parse['path'];
        }
        $parse['host'] = str_replace('/', '_', $parse['host']);
        $parse['host'] = str_replace('.', '_', $parse['host']);
        return $parse['host'].$path;
    }

    public function wpvivid_check_zip_valid()
    {
        return true;
    }

    public function amazons3_notice()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            $notice_message = 'init';
            WPvivid_Setting::update_option('wpvivid_amazons3_notice', $notice_message);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function wpvivid_check_type_database($is_type_db, $data){
        if(isset($data['dump_db'])){
            $is_type_db = true;
        }
        return $is_type_db;
    }

    public function hide_mainwp_tab_page(){
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        WPvivid_Setting::update_option('wpvivid_hide_mwp_tab_page_v1', true);
        $ret['result']=WPVIVID_SUCCESS;
        echo wp_json_encode($ret);
        die();
    }

    public function hide_wp_cron_notice(){
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        WPvivid_Setting::update_option('wpvivid_hide_wp_cron_notice', true);
        $ret['result']=WPVIVID_SUCCESS;
        echo wp_json_encode($ret);
        die();
    }

    public function download_backup_mainwp()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }
        try {
            if (isset($_REQUEST['backup_id']) && isset($_REQUEST['file_name'])) {
                if (!empty($_REQUEST['backup_id']) && is_string($_REQUEST['backup_id'])) {
                    $backup_id = sanitize_key($_REQUEST['backup_id']);
                } else {
                    die();
                }

                if (!empty($_REQUEST['file_name']) && is_string($_REQUEST['file_name'])) {
                    //$file_name=sanitize_file_name($_REQUEST['file_name']);
                    $file_name = sanitize_text_field($_REQUEST['file_name']);
                } else {
                    die();
                }

                $cache = WPvivid_taskmanager::get_download_cache($backup_id);
                if ($cache === false) {
                    $this->init_download($backup_id);
                    $cache = WPvivid_taskmanager::get_download_cache($backup_id);
                }
                $path = false;
                if (array_key_exists($file_name, $cache['files'])) {
                    if ($cache['files'][$file_name]['status'] == 'completed') {
                        $path = $cache['files'][$file_name]['download_path'];
                    }
                }
                if ($path !== false) {
                    if (file_exists($path)) {
                        if (session_id())
                            session_write_close();

                        $size = filesize($path);
                        if (!headers_sent()) {
                            header('Content-Description: File Transfer');
                            header('Content-Type: application/zip');
                            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                            header('Cache-Control: must-revalidate');
                            header('Content-Length: ' . $size);
                            header('Content-Transfer-Encoding: binary');
                        }

                        if ($size < 1024 * 1024 * 60) {
                            ob_end_clean();
                            readfile($path);
                            exit;
                        } else {
                            ob_end_clean();
                            $download_rate = 1024 * 10;
                            $file = fopen($path, "r");
                            while (!feof($file)) {
                                @set_time_limit(20);
                                // send the current file part to the browser
                                print fread($file, round($download_rate * 1024));
                                // flush the content to the browser
                                ob_flush();
                                flush();

                                // sleep one second
                                sleep(1);
                            }
                            fclose($file);
                            exit;
                        }
                    }
                }
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        $admin_url = admin_url();
        echo '<a href="'.esc_url($admin_url).'admin.php?page=WPvivid">file not found. please retry again.</a>';
        die();
    }

    public function set_mail_subject($subject, $task){
        if(!class_exists('WPvivid_mail_report'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-mail-report.php';
        $subject=WPvivid_mail_report::create_subject($task);
        return $subject;
    }

    public function set_mail_body($body, $task){
        if(!class_exists('WPvivid_mail_report'))
            include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-mail-report.php';
        $body=WPvivid_mail_report::create_body($task);
        return $body;
    }

    public function wpvivid_handle_mainwp_action($data){
        $public_interface = new WPvivid_Public_Interface();
        $ret = $public_interface->mainwp_data($data);
        return $ret;
    }

    public function get_zip_object_class($class)
    {
        /*if(version_compare(phpversion(),'8.0.0','>='))
        {
            return 'WPvivid_PclZip_Class_Ex';
        }
        return $class;*/
        return 'WPvivid_PclZip_Class_Ex';
    }

    public function get_backup_path($backup_item, $file_name)
    {
        $path = $backup_item->get_local_path() . $file_name;

        if (file_exists($path)) {
            return $path;
        }
        else{
            $local_setting = get_option('wpvivid_local_setting', array());
            if(!empty($local_setting))
            {
                $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $local_setting['path'] . DIRECTORY_SEPARATOR . $file_name;
            }
            else {
                $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wpvividbackups' . DIRECTORY_SEPARATOR . $file_name;
            }
        }
        return $path;
    }

    public function get_backup_folder()
    {
        $local_setting = get_option('wpvivid_local_setting', array());
        if(!empty($local_setting))
        {
            $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $local_setting['path'] . DIRECTORY_SEPARATOR;
        }
        else {
            $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wpvividbackups' . DIRECTORY_SEPARATOR;
        }
        return $path;
    }

    public function get_backup_url($backup_item, $file_name)
    {
        $path = $backup_item->get_local_path() . $file_name;

        if (file_exists($path)) {
            return $backup_item->get_local_url() . $file_name;
        }
        else{
            $local_setting = get_option('wpvivid_local_setting', array());
            if(!empty($local_setting))
            {
                $url = content_url().DIRECTORY_SEPARATOR.$local_setting['path'].DIRECTORY_SEPARATOR.$file_name;
            }
            else {
                $url = content_url().DIRECTORY_SEPARATOR.'wpvividbackups'.DIRECTORY_SEPARATOR.$file_name;
            }
        }
        return $url;
    }

    public function wpvivid_check_add_litespeed_server($task_id)
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
            if($this->wpvivid_log->log_file_handle==false)
            {
                $this->wpvivid_log->OpenLogFile(WPvivid_taskmanager::get_task_options($task_id,'log_file_name'));
            }
            $this->wpvivid_log->WriteLog('LiteSpeed Server.','notice');

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
                        $this->wpvivid_log->WriteLog('Add LiteSpeed Rule','notice');
                    }
                    else
                    {
                        $this->wpvivid_log->WriteLog('mod_rewrite not found.','notice');
                    }
                }
                else
                {
                    $this->wpvivid_log->WriteLog('.htaccess file not exists or not writable.','notice');
                }
            }
            else
            {
                $this->wpvivid_log->WriteLog('insert_with_markers function not exists.','notice');
            }
        }
    }

    public function wpvivid_check_clear_litespeed_rule($task_id)
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
            if($this->wpvivid_log->log_file_handle==false)
            {
                $this->wpvivid_log->OpenLogFile(WPvivid_taskmanager::get_task_options($task_id,'log_file_name'));
            }
            $this->wpvivid_log->WriteLog('LiteSpeed Server.','notice');

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
                        $this->wpvivid_log->WriteLog('Clear LiteSpeed Rule','notice');
                    }
                    else
                    {
                        $this->wpvivid_log->WriteLog('mod_rewrite not found.','notice');
                    }
                }
                else
                {
                    $this->wpvivid_log->WriteLog('.htaccess file not exists or not writable.','notice');
                }
            }
            else
            {
                $this->wpvivid_log->WriteLog('insert_with_markers function not exists.','notice');
            }
        }
    }
}
