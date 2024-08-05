<?php

function wpvivid_schedule_settings()
{
    ?>
    <tr>
        <td class="row-title wpvivid-backup-settings-table tablelistcolumn"><label for="tablecell"><?php esc_html_e('Schedule Settings', 'wpvivid-backuprestore'); ?></label></td>
        <td class="tablelistcolumn">
            <div id="storage-brand-3">
                <div>
                    <div>
                        <div class="postbox schedule-tab-block">
                            <label for="wpvivid_schedule_enable">
                                <input option="schedule" name="enable" type="checkbox" id="wpvivid_schedule_enable" />
                                <span><?php esc_html_e( 'Enable backup schedule', 'wpvivid-backuprestore' ); ?></span>
                            </label><br>
                            <label>
                                <div style="float: left;">
                                    <input type="checkbox" disabled />
                                    <span class="wpvivid-element-space-right" style="color: #ddd;"><?php esc_html_e('Enable Incremental Backup', 'wpvivid-backuprestore'); ?></span>
                                </div>
                                <div style="float: left; height: 32px; line-height: 32px;">
                                    <span class="wpvivid-feature-pro">
                                        <a href="https://docs.wpvivid.com/wpvivid-backup-pro-incremental-backups.html"><?php esc_html_e('Pro feature: learn more', 'wpvivid-backuprestore'); ?></a>
                                    </span>
                                </div>
                                <div style="clear: both;"></div>
                            </label>
                            <label>
                                <div style="float: left;">
                                    <input type="checkbox" disabled />
                                    <span class="wpvivid-element-space-right" style="color: #ddd;"><?php esc_html_e('Advanced Schedule', 'wpvivid-backuprestore'); ?></span>
                                </div>
                                <div style="float: left; height: 32px; line-height: 32px;">
                                    <span class="wpvivid-feature-pro">
                                        <a href="https://docs.wpvivid.com/wpvivid-backup-pro-schedule-overview.html"><?php esc_html_e('Pro feature: learn more', 'wpvivid-backuprestore'); ?></a>
                                    </span>
                                </div>
                                <div style="clear: both;"></div>
                            </label>
                            <div style="clear: both;"></div>
                            <div>
                                <?php
                                $time = '00:00:00';
                                $utime = strtotime($time);
                                echo '<p>1) '.'Scheduled job will start at <strong>UTC</strong> time:'.'&nbsp'.esc_html(gmdate('H:i:s', $utime)).'</p>';
                                echo '<p>2) ';
                                esc_html_e('Being subjected to mechanisms of PHP, a scheduled backup task for your site will be triggered only when the site receives at least a visit at any page.', 'wpvivid-backuprestore');
                                echo '</p>';
                                ?>
                            </div>
                        </div>
                        <div class="postbox schedule-tab-block">
                            <fieldset>
                                <legend class="screen-reader-text"><span>input type="radio"</span></legend>
                                <?php
                                $display_array = array("12Hours", "Daily", "Weekly", "Fortnightly", "Monthly");
                                foreach($display_array as $display)
                                {
                                    $schedule_check = wpvivid_check_schedule_type($display);
                                    if($schedule_check['result'])
                                    {
                                        echo ' <label><input type="radio" option="schedule" name="recurrence" value="'.esc_attr($schedule_check['type']).'" />';
                                        if($display === '12Hours'){
                                            echo '<span>'.esc_html__('12Hours', 'wpvivid-backuprestore').'</span></label><br>';
                                        }
                                        if($display === 'Daily'){
                                            echo '<span>'.esc_html__('Daily', 'wpvivid-backuprestore').'</span></label><br>';
                                        }
                                        if($display === 'Weekly'){
                                            echo '<span>'.esc_html__('Weekly', 'wpvivid-backuprestore').'</span></label><br>';
                                        }
                                        if($display === 'Fortnightly'){
                                            echo '<span>'.esc_html__('Fortnightly', 'wpvivid-backuprestore').'</span></label><br>';
                                        }
                                        if($display === 'Monthly'){
                                            echo '<span>'.esc_html__('Monthly', 'wpvivid-backuprestore').'</span></label><br>';
                                        }
                                    }
                                    else{
                                        echo '<p>Warning: Unable to set '.esc_html($display).' backup schedule</p>';
                                    }
                                }
                                echo '<label>';
                                echo '<div style="float: left;">';
                                echo '<input type="radio" disabled />';
                                echo '<span class="wpvivid-element-space-right" style="color: #ddd;">';esc_html_e('Custom', 'wpvivid-backuprestore');echo '</span>';
                                echo '</div>';
                                echo '<div style="float: left; height: 32px; line-height: 32px;">';
                                echo '<span class="wpvivid-feature-pro">';
                                echo '<a href="https://docs.wpvivid.com/wpvivid-backup-pro-customize-start-time.html" style="text-decoration: none; margin-top: 10px;">';esc_html_e('Pro feature: learn more', 'wpvivid-backuprestore');echo '</a>';
                                echo '</span>';
                                echo '</div>';
                                echo '</label><br>';
                                ?>
                            </fieldset>
                        </div>
                    </div>
                </div>
                <div class="postbox schedule-tab-block" id="wpvivid_schedule_backup_type">
                    <div>
                        <div>
                            <fieldset>
                                <legend class="screen-reader-text"><span>input type="radio"</span></legend>
                                <?php
                                echo '<label>';
                                echo '<input type="radio" option="schedule" name="backup_type" value="files+db"/>';
                                echo '<span>'.esc_html__('Database + Files (WordPress Files)', 'wpvivid-backuprestore').'</span>';
                                echo '</label><br>';

                                echo '<label>';
                                echo '<input type="radio" option="schedule" name="backup_type" value="files"/>';
                                echo '<span>'.esc_html__('WordPress Files (Exclude Database)', 'wpvivid-backuprestore').'</span>';
                                echo '</label><br>';

                                echo '<label>';
                                echo '<input type="radio" option="schedule" name="backup_type" value="db"/>';
                                echo '<span>'.esc_html__('Only Database', 'wpvivid-backuprestore').'</span>';
                                echo '</label><br>';

                                echo '<label>';
                                echo '<div style="float: left;">';
                                echo '<input type="radio" disabled />';
                                echo '<span class="wpvivid-element-space-right" style="color: #ddd;">'.esc_html__('Custom', 'wpvivid-backuprestore').'</span>';
                                echo '</div>';
                                echo '<div style="float: left; height: 32px; line-height: 32px;">';
                                echo '<span class="wpvivid-feature-pro">';
                                echo '<a href="https://docs.wpvivid.com/wpvivid-backup-pro-customize-what-to-backup-for-schedule.html" style="text-decoration: none;">'.esc_html__('Pro feature: learn more', 'wpvivid-backuprestore').'</a>';
                                echo '</span>';
                                echo '</div>';
                                echo '</label><br>';
                                ?>
                            </fieldset>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                </div>
                <div class="postbox schedule-tab-block" id="wpvivid_schedule_remote_storage">
                    <div id="wpvivid_schedule_backup_local_remote">
                        <?php
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
                        echo '<fieldset>
                   <label title="">
                        <input type="radio" option="schedule" name="save_local_remote" value="local" '.esc_attr($backup_local).' />
                        <span>'.esc_html__( 'Save backups on localhost (web server)', 'wpvivid-backuprestore' ).'</span>
                   </label><br>
                   <label title="">
                        <input type="radio" option="schedule" name="save_local_remote" value="remote" '.esc_attr($backup_remote).' />
                        <span>'.esc_html__( 'Send backups to remote storage (You can choose whether to keep the backup in localhost after it is uploaded to cloud storage in Settings.)', 'wpvivid-backuprestore' ).'</span>
                   </label>
                   <label style="display: none;">
                        <input type="checkbox" option="schedule" name="lock" value="0" />
                   </label>
                   </fieldset>';
                        ?>
                    </div>
                    <div id="schedule_upload_storage" style="cursor:pointer;" title="<?php esc_html_e('Highlighted icon illuminates that you have choosed a remote storage to store backups', 'wpvivid-backuprestore'); ?>">
                        <?php
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
                        if(is_array($remote))
                        {
                            foreach ($remote as $key => $value) {
                                $title = $value['title'];
                                if (in_array($key, $remote_storage_type)) {
                                    $pic = $value['selected_pic'];
                                } else {
                                    $pic = $value['default_pic'];
                                }
                                $url = apply_filters('wpvivid_get_wpvivid_pro_url', WPVIVID_PLUGIN_URL, $key);
                                echo '<img  src="' . esc_url($url . $pic) . '" style="vertical-align:middle; " title="' . esc_attr($title) . '"/>';
                            }
                            echo '<img onclick="wpvivid_click_switch_page(\'wrap\', \'wpvivid_tab_remote_storage\', true);" src="'.esc_url(WPVIVID_PLUGIN_URL.'/admin/partials/images/add-storages.png').'" style="vertical-align:middle;" title="'.esc_attr__('Add a storage', 'wpvivid-backuprestore').'"/>';
                        }
                        ?>
                    </div>
                </div>
                <div class="postbox schedule-tab-block">
                    <div style="float:left; color: #ddd; margin-right: 10px;">
                        <?php esc_html_e('+ Add another schedule', 'wpvivid-backuprestore'); ?>
                    </div>
                    <span class="wpvivid-feature-pro">
                        <a href="https://docs.wpvivid.com/wpvivid-backup-pro-creating-schedules.html"><?php esc_html_e('Pro feature: learn more', 'wpvivid-backuprestore'); ?></a>
                    </span>
                </div>
            </div>
        </td>
    </tr>
    <script>
        <?php
        do_action('wpvivid_schedule_do_js');
        ?>
    </script>
    <?php
}

function wpvivid_check_schedule_type($display)
{
    $schedule_type = array(
        'wpvivid_12hours'       =>  '12Hours',
        'twicedaily'             =>  '12Hours',
        'wpvivid_daily'         =>   'Daily',
        'daily'                  =>   'Daily',
        'onceday'                =>   'Daily',
        'wpvivid_weekly'        =>   'Weekly',
        'weekly'                 =>   'Weekly',
        'wpvivid_fortnightly'  =>   'Fortnightly',
        'fortnightly'           =>   'Fortnightly',
        'wpvivid_monthly'      =>   'Monthly',
        'monthly'               =>    'Monthly',
        'montly'                =>    'Monthly'
    );
        $schedules = wp_get_schedules();
        $check_res = false;
        $ret = array();
        foreach ($schedule_type as $key => $value){
            if($value == $display){
                if(isset($schedules[$key])){
                    $check_res = true;
                    $ret['type']=$key;
                    break;
                }
            }
        }
        $ret['result']=$check_res;
        return $ret;
}

function wpvivid_schedule_do_js()
{
    $schedule=WPvivid_Schedule::get_schedule();
    if($schedule['enable'] == true)
    {
        ?>
        jQuery("#wpvivid_schedule_enable").prop('checked', true);
        <?php
        if($schedule['backup']['remote'] === 1)
        {
            $schedule_remote='remote';
        }
        else{
            $schedule_remote='local';
        }
    }
    else{
        $schedule['recurrence']='wpvivid_daily';
        $schedule['backup']['backup_files']='files+db';
        $schedule_remote='local';
    }
    ?>
    jQuery("input:radio[value='<?php echo esc_attr($schedule['recurrence'])?>']").prop('checked', true);
    jQuery("input:radio[value='<?php echo esc_attr($schedule['backup']['backup_files'])?>']").prop('checked', true);
    jQuery("input:radio[name='save_local_remote'][value='remote']").click(function()
    {
    <?php
    $remote_id_array = WPvivid_Setting::get_user_history('remote_selected');
    $remote_id = '';
    foreach ($remote_id_array as $value)
    {
        $remote_id = $value;
    }
    if(empty($remote_id))
    {
        ?>
        alert("<?php esc_html_e('There is no default remote storage configured. Please set it up first.', 'wpvivid-backuprestore'); ?>");
        jQuery("input:radio[name='save_local_remote'][value='local']").prop('checked', true);
        <?php
    }
    ?>
    });
    <?php
}

add_action('wpvivid_schedule_add_cell','wpvivid_schedule_settings',11);
add_action('wpvivid_schedule_do_js','wpvivid_schedule_do_js',10);
?>

