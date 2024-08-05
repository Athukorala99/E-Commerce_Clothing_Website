<?php

if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

class WPvivid_Snapshot_Ex
{
    public $options;
    public $main_tab;

    public function __construct()
    {
        include_once WPVIVID_PLUGIN_DIR . '/includes/snapshot/class-wpvivid-snapshot-function.php';
        include_once WPVIVID_PLUGIN_DIR . '/includes/snapshot/class-wpvivid-snapshot-options.php';
        if(is_admin())
        {
            include_once WPVIVID_PLUGIN_DIR . '/includes/snapshot/class-wpvivid-snapshots-list.php';

            add_filter('wpvivid_get_dashboard_menu', array($this, 'get_dashboard_menu'), 20, 2);
            add_filter('wpvivid_get_dashboard_screens', array($this, 'get_dashboard_screens'), 20);

            add_filter('wpvivid_snapshot_get_main_admin_menus',array($this,'get_main_admin_menus'),9999);

            $this->options=new WPvivid_Snapshot_Option_Ex();

            /*
            if (is_multisite())
            {
                add_action('network_admin_menu',array( $this,'add_admin_menu'));
            }
            else
            {
                add_action('admin_menu',array( $this,'add_admin_menu'));
            }
            add_filter('wpvivid_snapshot_get_screen_ids', array($this,'get_screen_ids'), 9999);
            */

            add_filter('wpvivid_get_admin_menus',array($this,'get_admin_menus'),22);
            add_filter('wpvivid_get_screen_ids',array($this,'get_screen_ids'),12);

            add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'), 11);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'), 11);


            add_action('wp_ajax_wpvivid_create_snapshot',array( $this,'create_snapshot'));
            add_action('wp_ajax_wpvivid_get_snapshot_progress',array( $this,'get_snapshot_progress'));
            //
            add_action('wp_ajax_wpvivid_resume_create_snapshot',array( $this,'resume_create_snapshot'));
            add_action('wp_ajax_wpvivid_restore_snapshot',array( $this,'restore_snapshot'));
            add_action('wp_ajax_wpvivid_get_restore_snapshot_status',array( $this,'get_restore_snapshot_status'));
            add_action('wp_ajax_wpvivid_delete_snapshot',array( $this,'delete_snapshot'));

            add_filter('wpvivid_check_create_snapshot',array($this,'check_create_snapshot'));
            add_action('wpvivid_create_snapshot',array($this,'create_snapshot_ex'),10,1);

            add_action('wp_ajax_wpvivid_set_snapshot_setting',array( $this,'set_setting'));
            //
            add_action('wpvivid_snapshot_add_sidebar',array( $this,'add_sidebar'));
            add_action('wpvivid_snapshot_add_sidebar_free', array( $this, 'add_sidebar_free' ));

            $snapshot_setting=$this->options->get_option('wpvivid_snapshot_setting');

            $quick_snapshot=isset($snapshot_setting['quick_snapshot'])?$snapshot_setting['quick_snapshot']:false;

            if($quick_snapshot)
            {
                add_action('admin_bar_menu',array( $this,'add_toolbar_items'),100);
                add_action('admin_footer',array( $this,'quick_snapshot'));
            }
        }

    }

    public function get_admin_menus($submenus)
    {
        $submenu['parent_slug']=apply_filters('wpvivid_white_label_slug', WPVIVID_PLUGIN_SLUG);
        $submenu['page_title']= apply_filters('wpvivid_white_label_display', 'WPvivid Backup');
        $submenu['menu_title']=__('Database Snapshots', 'wpvivid-backuprestore');
        $submenu['capability']='administrator';
        $submenu['menu_slug']=strtolower(sprintf('%s-snapshot-ex', apply_filters('wpvivid_white_label_slug', 'wpvivid')));
        $submenu['index']=2;
        $submenu['function']=array($this, 'init_page');
        $submenus[$submenu['menu_slug']]=$submenu;
        return $submenus;
    }

    public function get_screen_ids($screen_ids)
    {
        $screen_ids[]=apply_filters('wpvivid_white_label_screen_id', 'wpvivid-backup_page_wpvivid-snapshot-ex');
        return $screen_ids;
    }

    public function add_toolbar_items($wp_admin_bar)
    {
        $wp_admin_bar->add_menu(array(
            'id' => 'wpvivid_snapshot_admin_menu',
            'title' => '<span class="dashicons-camera-alt ab-icon"></span>'.'Quick Snapshot',
            'meta' =>array(
                'class' => 'wpvivid-quick-create-snapshot',
            )
        ));
    }

    public function add_admin_menu()
    {
        $page_title=apply_filters('wpvivid_white_label_display', 'WPvivid Snapshot');
        $menu_title=apply_filters('wpvivid_white_label_display', 'WPvivid Snapshot');

        $capability = 'administrator';

        $menu_slug ='wpvivid-snapshot';

        $function=array($this, 'init_page');
        $icon_url='dashicons-camera-alt';
        $position=100;

        $menu['page_title']= $page_title;
        $menu['menu_title']= $menu_title;
        $menu['capability']='administrator';
        $menu['menu_slug']=$menu_slug;
        $menu['function']=array($this, 'init_page');
        $menu['icon_url']=$icon_url;
        $menu['position']=100;

        $menu=apply_filters('wpvivid_snapshot_get_main_admin_menus', $menu);

        if($menu!=false)
            add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
    }

    public function get_dashboard_menu($submenus,$parent_slug)
    {
        $display = apply_filters('wpvivid_get_menu_capability_addon', 'menu_database_snapshot');
        if($display)
        {
            $submenu['menu_slug'] = strtolower(sprintf('%s-snapshot', apply_filters('wpvivid_white_label_slug', 'wpvivid')));
            if(isset($submenus[$submenu['menu_slug']]))
            {
                unset($submenus[$submenu['menu_slug']]);
            }
            $submenu['parent_slug'] = $parent_slug;
            $submenu['page_title'] = apply_filters('wpvivid_white_label_display', 'Database Snapshots');
            $submenu['menu_title'] = 'Database Snapshots';
            $submenu['capability'] = 'administrator';
            $submenu['index'] = 11;//10;
            $submenu['function'] = array($this, 'init_page_pro');
            $submenus[$submenu['menu_slug']] = $submenu;
        }

        return $submenus;
    }

    public function get_dashboard_screens($screens)
    {
        $screen['menu_slug']='wpvivid-snapshot';
        $screen['screen_id']='wpvivid-plugin_page_wpvivid-snapshot';
        $screen['is_top']=false;
        $screens[]=$screen;
        return $screens;
    }

    public function get_main_admin_menus($menu)
    {
        if(class_exists('WPvivid_backup_pro'))
            return false;
        else
            return $menu;
    }

    /*
    public function get_screen_ids($screen_ids)
    {
        $screen_ids=array();
        $screen['menu_slug']='wpvivid-snapshot';
        $screen['screen_id']='toplevel_page_wpvivid-snapshot';
        $screen['is_top']=true;
        $screens[]=$screen;

        foreach ($screens as $screen)
        {
            $screen_ids[]=$screen['screen_id'];
            if(is_multisite())
            {
                if(substr($screen['screen_id'],-8)=='-network')
                    continue;
                $screen_ids[]=$screen['screen_id'].'-network';
            }
            else
            {
                $screen_ids[]=$screen['screen_id'];
            }
        }
        return $screen_ids;
    }
    */

    public function enqueue_styles()
    {
        $screen_ids=array();
        $screen_ids=apply_filters('wpvivid_get_screen_ids',$screen_ids);
        if(in_array(get_current_screen()->id,$screen_ids))
        {
            wp_enqueue_style('wpvivid_snapshot_ex', WPVIVID_PLUGIN_DIR_URL . 'css/wpvivid-snapshot-style.css', array(), WPVIVID_PLUGIN_VERSION, 'all');
        }
    }

    public function enqueue_scripts()
    {

        $snapshot_setting=$this->options->get_option('wpvivid_snapshot_setting');

        $quick_snapshot=isset($snapshot_setting['quick_snapshot'])?$snapshot_setting['quick_snapshot']:false;

        if($quick_snapshot)
        {
            wp_enqueue_style('wpvivid_quick_snapshot_ex', WPVIVID_PLUGIN_DIR_URL . 'css/wpvivid-quick-snapshot-style.css', array(), WPVIVID_PLUGIN_VERSION, 'all');
            wp_enqueue_style (  'wp-jquery-ui-dialog');
            wp_enqueue_script( 'jquery-ui-dialog' );
            wp_enqueue_script (  'wpvivid_qucick_snapshot_ex_js' ,       // handle
                WPVIVID_PLUGIN_DIR_URL . 'js/wpvivid-quick-snapshot.js'  ,       // source
                array('jquery-ui-dialog'),
                WPVIVID_PLUGIN_VERSION, false
            );
            wp_localize_script('wpvivid_qucick_snapshot_ex_js', 'wpvivid_quick_snapshot_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'),'ajax_nonce'=>wp_create_nonce('wpvivid_ajax')));
        }
    }

    public function added_quick_snapshot($added)
    {
        return true;
    }

    public function quick_snapshot()
    {
        if(apply_filters('wpvivid_added_quick_snapshot',false))
        {
            return;
        }
        add_filter('wpvivid_added_quick_snapshot',array( $this,'added_quick_snapshot'));
        ?>
        <div id="wpvivid_quick_snapshot_dialog">
            <span id="wpvivid_quick_snapshot_close" class="dashicons dashicons-no" style="float:right;cursor: pointer"></span>
            <div id="wpvivid_quick_snapshot_message_box" style="padding:20px 0;">
                <p style="text-align:center;font-size:24px;">
                    <span id="wpvivid_quick_snapshot_message">Are you sure you want to create a snapshot now?</span>
                    <span id="wpvivid_quick_snapshot_loading"><img src="<?php echo esc_url(admin_url()).'/images/loading.gif'; ?>"></span>
                </p>
                <p style="text-align:center;" id="wpvivid_quick_create_snapshot_comment_box">
                    <span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-green" style="margin-top:0.2em;"></span>
                    <span><strong>Comment the snapshot</strong>(optional): </span>
                    <input  id="wpvivid_quick_create_snapshot_comment" type="text" placeholder="e.g. mysnapshot">
                </p>
            </div>
            <div id="wpvivid_quick_snapshot_progress" style="display: none">
                <p>
                    <span class="wpvivid-span-progress">
                        <span class="wpvivid-span-processed-progress">0% completed</span>
                    </span>
                </p>
                <p><span>Action: </span><span></span><span></span></p>
            </div>
            <div style="padding:0 0 10px 0">
                <p style="text-align:center;">
                    <input class="button-primary"  style="width: 150px; height: 40px; font-size: 16px; margin-bottom: 10px; pointer-events: auto; opacity: 1;" id="wpvivid_quick_create_snapshot" type="submit" value="Create Now">
                </p>
            </div>
        </div>
        <script>
            var b_quick_end_create_progress=false;
            var b_quick_need_update=false;
            jQuery('.wpvivid-quick-create-snapshot').click(function()
            {
                jQuery("#wpvivid_quick_snapshot_message_box").show();
                //
                jQuery("#wpvivid_quick_snapshot_loading").hide();
                jQuery("#wpvivid_quick_create_snapshot_comment_box").show();
                jQuery("#wpvivid_quick_snapshot_progress").hide();
                //
                jQuery("#wpvivid_quick_snapshot_message").html("Are you sure you want to create a snapshot now?");
                jQuery("#wpvivid_quick_snapshot_dialog").dialog("widget").find(".ui-dialog-titlebar").hide();
                jQuery("#wpvivid_quick_snapshot_dialog").dialog("open");

                return false;
            });

            //wpvivid_quick_snapshot_close
            jQuery('#wpvivid_quick_snapshot_close').click(function()
            {
                jQuery("#wpvivid_quick_snapshot_dialog").dialog('close');
            });

            jQuery('#wpvivid_quick_create_snapshot').click(function()
            {
                wpvivid_quick_create_snapshot();
            });

            function wpvivid_quick_simulate_create_progress()
            {
                var MaxProgess = 30,
                    currentProgess = 0,
                    steps = 1,
                    time_steps=1000;

                var timer = setInterval(function ()
                {
                    if(currentProgess>100)
                    {
                        currentProgess=100;
                    }
                    else
                    {
                        currentProgess += steps;
                    }

                    if(b_quick_end_create_progress)
                    {
                        clearInterval(timer);
                        return;
                    }
                    var progress_html='<p><span class="wpvivid-span-progress">' +
                        '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width: '+currentProgess+'%">' +
                        currentProgess+'% completed</span></span></p><p>' +
                        '<span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span>' +
                        '<span>Creating the snapshot.</span></p>';

                    jQuery("#wpvivid_quick_snapshot_progress").html(progress_html);
                    if (currentProgess >= MaxProgess)
                    {
                        clearInterval(timer);
                    }
                }, time_steps);
            }

            function wpvivid_quick_create_snapshot()
            {
                var comment=jQuery('#wpvivid_quick_create_snapshot_comment').val();
                var ajax_data= {
                    'action': 'wpvivid_create_snapshot',
                    'comment':comment,
                };
                var default_progress='<p><span class="wpvivid-span-progress"><span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width: 0%">0% completed</span></span></p><p><span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span><span>Creating a snapshot.</span></p>';
                jQuery('#wpvivid_quick_snapshot_progress').show();
                jQuery('#wpvivid_quick_snapshot_progress').html(default_progress);
                jQuery("#wpvivid_quick_snapshot_loading").show();
                jQuery("#wpvivid_quick_create_snapshot_comment_box").hide();
                jQuery("#wpvivid_quick_snapshot_message").html("Creating the snapshot...");
                b_quick_need_update=true;
                b_quick_end_create_progress=false;
                wpvivid_quick_simulate_create_progress();

                setTimeout(function(){
                    wpvivid_quick_get_snapshot_progress();
                }, 3000);

                jQuery('#wpvivid_quick_create_snapshot').css({'pointer-events': 'none', 'opacity': '0.4'});
                wpvivid_post_request_quick(ajax_data, function(data)
                {
                    b_quick_end_create_progress=true;
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success')
                    {
                        jQuery('#wpvivid_quick_snapshot_progress').html(jsonarray.progress);
                        if(jsonarray.finished==1)
                        {
                            jQuery("#wpvivid_quick_snapshot_dialog").dialog('close');
                            b_quick_need_update=false;
                            jQuery('#wpvivid_quick_snapshot_progress').hide();
                            jQuery('#wpvivid_quick_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});

                            jQuery("#wpvivid_quick_snapshot_loading").hide();
                            jQuery("#wpvivid_quick_create_snapshot_comment_box").show();
                            jQuery("#wpvivid_quick_snapshot_message").html("Are you sure you want to create a snapshot now?");

                            alert("Creating a snapshot completed successfully.");
                        }
                        else
                        {
                            wpvivid_quick_resume_create_snapshot();
                        }
                    }
                    else
                    {
                        alert(jsonarray.error);
                        b_quick_need_update=false;
                        jQuery('#wpvivid_quick_snapshot_progress').hide();
                        jQuery('#wpvivid_quick_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});

                        jQuery("#wpvivid_quick_snapshot_loading").hide();
                        jQuery("#wpvivid_quick_create_snapshot_comment_box").show();
                        jQuery("#wpvivid_quick_snapshot_message").html("Are you sure you want to create a snapshot now?");
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    setTimeout(function(){
                        wpvivid_quick_resume_create_snapshot(0);
                    }, 15000);
                });
            }

            function wpvivid_quick_get_snapshot_progress()
            {
                var ajax_data= {
                    'action': 'wpvivid_get_snapshot_progress',
                };

                wpvivid_post_request_quick(ajax_data, function(data)
                {
                    var jsonarray = jQuery.parseJSON(data);
                    b_quick_end_create_progress=true;
                    jQuery('#wpvivid_quick_snapshot_progress').html(jsonarray.progress);

                    if(b_quick_need_update)
                    {
                        setTimeout(function(){
                            wpvivid_quick_get_snapshot_progress();
                        }, 1000);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    if(b_quick_need_update)
                    {
                        setTimeout(function(){
                            wpvivid_quick_get_snapshot_progress();
                        }, 1000);
                    }
                });
            }

            function wpvivid_quick_resume_create_snapshot(resume)
            {
                if(resume>6)
                {
                    alert('Creating the snapshot timed out.');
                    b_quick_need_update=false;
                    jQuery("#wpvivid_quick_snapshot_message_box").show();
                    jQuery('#wpvivid_quick_snapshot_progress').hide();
                    jQuery('#wpvivid_quick_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});

                    jQuery("#wpvivid_quick_snapshot_loading").hide();
                    jQuery("#wpvivid_quick_create_snapshot_comment_box").show();
                    jQuery("#wpvivid_quick_snapshot_message").html("Are you sure you want to create a snapshot now?");
                    return;
                }
                var ajax_data= {
                    'action': 'wpvivid_resume_create_snapshot'
                };

                wpvivid_post_request_quick(ajax_data, function(data)
                {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#wpvivid_quick_snapshot_progress').html(jsonarray.progress);

                            if(jsonarray.finished==1)
                            {
                                b_quick_need_update=false;
                                jQuery("#wpvivid_quick_snapshot_dialog").dialog('close');

                                jQuery("#wpvivid_quick_snapshot_message_box").show();
                                jQuery('#wpvivid_quick_snapshot_progress').hide();
                                jQuery('#wpvivid_quick_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});

                                jQuery("#wpvivid_quick_snapshot_loading").hide();
                                jQuery("#wpvivid_quick_create_snapshot_comment_box").show();
                                jQuery("#wpvivid_quick_snapshot_message").html("Are you sure you want to create a snapshot now?");
                                alert("Creating a snapshot completed successfully.");
                            }
                            else
                            {
                                wpvivid_quick_resume_create_snapshot();
                            }
                        }
                        else
                        {
                            b_quick_need_update=false;
                            jQuery("#wpvivid_quick_snapshot_message_box").show();
                            alert(jsonarray.error);
                            jQuery('#wpvivid_quick_snapshot_progress').hide();
                            jQuery('#wpvivid_quick_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});

                            jQuery("#wpvivid_quick_snapshot_loading").hide();
                            jQuery("#wpvivid_quick_create_snapshot_comment_box").show();
                            jQuery("#wpvivid_quick_snapshot_message").html("Are you sure you want to create a snapshot now?");
                        }
                    }
                    catch (e)
                    {
                        resume+=1;
                        setTimeout(function(){
                            wpvivid_quick_resume_create_snapshot(resume);
                        }, 15000);
                    }

                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    resume+=1;
                    setTimeout(function(){
                        wpvivid_quick_resume_create_snapshot(resume);
                    }, 15000);
                });
            }

            jQuery(document).ready(function ()
            {
                jQuery(function($)
                {
                    jQuery("#wpvivid_quick_snapshot_dialog").dialog({
                        'dialogClass'   : 'noTitleStuff',
                        'modal'         : true,
                        'autoOpen'      : false,
                        'closeOnEscape' : true,
                        'width': '600px',
                        'minWidth' : "260px"
                    });
                });
            });
        </script>
        <?php
    }

    public function init_page()
    {
        $this->options->check_tables();
        ?>
        <div class="wrap" style="max-width:1720px;">
            <h1><?php echo esc_html( apply_filters('wpvivid_white_label_display', 'WPvivid').' Plugins - Snapshots'); ?></h1>

            <?php
            if(!class_exists('WPvivid_Tab_Page_Container'))
                include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-tab-page-container.php';

            $args['is_parent_tab']=1;
            $this->main_tab=new WPvivid_Tab_Page_Container();
            $this->main_tab->add_tab('Snapshots','snapshots',array($this, 'output_snapshots'), $args);
            $this->main_tab->add_tab('Setting','snapshots_setting',array($this, 'output_snapshots_setting'), $args);
            $this->main_tab->display();
            ?>

        </div>
        <?php
    }

    public function init_page_pro()
    {
        $this->options->check_tables();
        ?>
        <div class="wrap wpvivid-canvas">
            <div class="icon32"></div>
            <h1><?php echo esc_html( apply_filters('wpvivid_white_label_display', 'WPvivid').' Plugins - Snapshots' ); ?></h1>
            <div id="wpvivid_backup_notice"></div>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <div class="wpvivid-backup">
                                <?php $this->welcome_bar();?>
                                <div class="wpvivid-canvas wpvivid-clear-float">
                                    <!---  backup progress --->
                                    <?php

                                    if(!class_exists('WPvivid_Tab_Page_Container_Ex'))
                                        include_once WPVIVID_PLUGIN_DIR . '/includes/snapshot/class-wpvivid-tab-page-container-ex.php';
                                    $this->main_tab=new WPvivid_Tab_Page_Container_Ex();

                                    $args['is_parent_tab']=0;
                                    $args['div_style']='padding-top:0;display:block;';
                                    $args['span_class']='dashicons dashicons-camera';
                                    $args['span_style']='color:#007cba; padding-right:0.5em;margin-top:0.2em;';
                                    //
                                    $tabs['merge']['title']='Snapshots';
                                    $tabs['merge']['slug']='snapshots';
                                    $tabs['merge']['callback']=array($this, 'output_snapshots');
                                    $tabs['merge']['args']=$args;

                                    $args['div_style']='padding-top:0;';
                                    $args['span_class']='dashicons  dashicons-admin-generic';
                                    $args['span_style']='color:grey;padding-right:0.5em;margin-top:0.1em;';
                                    $tabs['snapshot']['title']='Setting';
                                    $tabs['snapshot']['slug']='snapshots_setting';
                                    $tabs['snapshot']['callback']=array($this, 'output_snapshots_setting');
                                    $tabs['snapshot']['args']=$args;

                                    foreach ($tabs as $key=>$tab)
                                    {
                                        $this->main_tab->add_tab($tab['title'],$tab['slug'],$tab['callback'], $tab['args']);
                                    }

                                    $this->main_tab->display();
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    do_action( 'wpvivid_snapshot_add_sidebar');
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function welcome_bar()
    {
        ?>
        <div class="wpvivid-welcome-bar wpvivid-clear-float">
            <div class="wpvivid-welcome-bar-left">
                <p><span class="dashicons dashicons-camera-alt wpvivid-dashicons-large wpvivid-dashicons-green"></span><span class="wpvivid-page-title">Database Snapshots</span></p>
                <p><span class="about-description">Create snapshots of the website database and restore the database from a snapshot.</span></p>
            </div>
            <div class="wpvivid-welcome-bar-right">
                <p></p>
                <div style="float:right;">
                    <span>Local Time:</span>
                    <span>
                        <a href="<?php echo esc_attr(apply_filters('wpvivid_get_admin_url', '').'options-general.php'); ?>">
                            <?php
                            $offset=get_option('gmt_offset');
                            echo esc_html(gmdate("l, F-d-Y H:i",time()+$offset*60*60));
                            ?>
                        </a>
                    </span>
                    <span class="dashicons dashicons-editor-help wpvivid-dashicons-editor-help wpvivid-tooltip">
                        <div class="wpvivid-left">
                            <p>Clicking the date and time will redirect you to the WordPress General Settings page where you can change your timezone settings.</p>
                            <i></i> <!-- do not delete this line -->
                        </div>
                    </span>
                </div>
            </div>
        </div>
        <?php
    }

    public function output_snapshots()
    {
        $snapshot=new WPvivid_Snapshot_Function_Ex();
        $snapshot_data=$snapshot->get_snapshots();

        ?>
        <div class="postbox quicksnapshot">
            <div id="wpvivid_snapshot_progress" style="display: none">
                <p>
                    <span class="wpvivid-span-progress">
                        <span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress">0% completed</span>
                    </span>
                </p>
                <p><span>Action: </span><span></span><span class="wpvivid-animate-flicker"></span></p>
            </div>
            <div>
                <input class="button-primary" style="width: 200px; height: 50px; font-size: 20px; margin-bottom: 10px; pointer-events: auto; opacity: 1;" id="wpvivid_create_snapshot" type="submit" value="Create a snapshot">
            </div>
            <div>
                <p>
                    <span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-green" style="margin-top:0.2em;"></span>
                    <span><strong>Comment the snapshot</strong>(optional): </span>
                    <input id="wpvivid_create_snapshot_comment" type="text" placeholder="e.g. mysnapshot">
                </p>
            </div>

            <div id="wpvivid_snapshots_list">
                <?php
                $Snapshots_list = new WPvivid_Snapshots_List_Ex();
                $Snapshots_list->set_list($snapshot_data);
                $Snapshots_list->prepare_items();
                $Snapshots_list->display();
                ?>
            </div>
        </div>
        <script>
            var b_need_update=false;
            var b_restore_finished=false;
            var b_end_create_progress=false;
            jQuery('#wpvivid_create_snapshot').click(function()
            {
                wpvivid_create_snapshot();
            });

            function wpvivid_simulate_restore_progress()
            {
                var MaxProgess = 95,
                    currentProgess = 0,
                    steps = 1,
                    time_steps=1000;

                var timer = setInterval(function ()
                {
                    if(b_restore_finished)
                    {
                        currentProgess=100;
                    }
                    else
                    {
                        currentProgess += steps;
                    }


                    var progress_html='<p><span class="wpvivid-span-progress">' +
                        '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width: '+currentProgess+'%">' +
                        currentProgess+'% completed</span></span></p><p>' +
                        '<span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span>' +
                        '<span>Restoring the snapshot.</span></p>';

                    jQuery("#wpvivid_snapshot_progress").html(progress_html);
                    if (currentProgess >= MaxProgess)
                    {
                        clearInterval(timer);
                    }
                }, time_steps);
            }

            function wpvivid_simulate_create_progress()
            {
                var MaxProgess = 30,
                    currentProgess = 0,
                    steps = 1,
                    time_steps=1000;

                var timer = setInterval(function ()
                {
                    if(currentProgess>100)
                    {
                        currentProgess=100;
                    }
                    else
                    {
                        currentProgess += steps;
                    }

                    if(b_end_create_progress)
                    {
                        clearInterval(timer);
                        return;
                    }
                    var progress_html='<p><span class="wpvivid-span-progress">' +
                        '<span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width: '+currentProgess+'%">' +
                        currentProgess+'% completed</span></span></p><p>' +
                        '<span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span>' +
                        '<span>Creating the snapshot.</span></p>';

                    jQuery("#wpvivid_snapshot_progress").html(progress_html);
                    if (currentProgess >= MaxProgess)
                    {
                        clearInterval(timer);
                    }
                }, time_steps);
            }

            function wpvivid_create_snapshot()
            {
                var comment=jQuery('#wpvivid_create_snapshot_comment').val();
                var ajax_data= {
                    'action': 'wpvivid_create_snapshot',
                    'comment':comment,
                };
                var default_progress='<p><span class="wpvivid-span-progress"><span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width: 0%">0% completed</span></span></p><p><span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span><span>Creating a snapshot.</span></p>';
                jQuery('#wpvivid_snapshot_progress').show();
                jQuery('#wpvivid_snapshot_progress').html(default_progress);

                b_need_update=true;
                b_end_create_progress=false;
                wpvivid_simulate_create_progress();

                setTimeout(function(){
                    wpvivid_get_snapshot_progress();
                }, 3000);

                jQuery('#wpvivid_create_snapshot').css({'pointer-events': 'none', 'opacity': '0.4'});
                wpvivid_post_request(ajax_data, function(data)
                {
                    b_end_create_progress=true;
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success')
                    {
                        jQuery('#wpvivid_snapshot_progress').html(jsonarray.progress);
                        if(jsonarray.finished==1)
                        {
                            b_need_update=false;
                            alert('Creating a snapshot completed successfully.');
                            location.reload();
                        }
                        else
                        {
                            wpvivid_resume_create_snapshot();
                        }
                    }
                    else
                    {
                        b_need_update=false;
                        alert(jsonarray.error);
                        jQuery('#wpvivid_snapshot_progress').hide();
                        jQuery('#wpvivid_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    setTimeout(function(){
                        wpvivid_resume_create_snapshot(0);
                    }, 15000);
                });
            }

            function wpvivid_get_snapshot_progress()
            {
                var ajax_data= {
                    'action': 'wpvivid_get_snapshot_progress',
                };

                wpvivid_post_request(ajax_data, function(data)
                {
                    var jsonarray = jQuery.parseJSON(data);
                    b_end_create_progress=true;
                    jQuery('#wpvivid_snapshot_progress').html(jsonarray.progress);

                    if(b_need_update)
                    {
                        setTimeout(function(){
                            wpvivid_get_snapshot_progress();
                        }, 1000);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    if(b_need_update)
                    {
                        setTimeout(function(){
                            wpvivid_get_snapshot_progress();
                        }, 1000);
                    }
                });
            }

            function wpvivid_resume_create_snapshot(resume)
            {
                if(resume>6)
                {
                    b_need_update=false;
                    alert('Creating the snapshot timed out.');
                    jQuery('#wpvivid_snapshot_progress').hide();
                    jQuery('#wpvivid_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});
                    return;
                }
                var ajax_data= {
                    'action': 'wpvivid_resume_create_snapshot'
                };

                wpvivid_post_request(ajax_data, function(data)
                {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#wpvivid_snapshot_progress').html(jsonarray.progress);

                            if(jsonarray.finished==1)
                            {
                                b_need_update=false;

                                alert('Creating a snapshot completed successfully.');
                                location.reload();
                            }
                            else
                            {
                                wpvivid_resume_create_snapshot();
                            }
                        }
                        else
                        {
                            b_need_update=false;
                            alert(jsonarray.error);
                            jQuery('#wpvivid_snapshot_progress').hide();
                            jQuery('#wpvivid_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});
                        }
                    }
                    catch (e)
                    {
                        resume+=1;
                        setTimeout(function(){
                            wpvivid_resume_create_snapshot(resume);
                        }, 15000);
                    }

                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    resume+=1;
                    setTimeout(function(){
                        wpvivid_resume_create_snapshot(resume);
                    }, 15000);
                });
            }

            jQuery('#wpvivid_snapshots_list').on("click",'.wpvivid-snapshot-restore',function()
            {
                var Obj=jQuery(this);
                var snapshot_id=Obj.closest('tr').attr('slug');

                var descript = '<?php esc_html_e('Are you sure you want to restore this snapshot?', 'wpvivid'); ?>';
                var ret = confirm(descript);
                if (ret === true)
                {
                    var ajax_data= {
                        'action': 'wpvivid_restore_snapshot',
                        'id':snapshot_id
                    };
                    jQuery('#wpvivid_create_snapshot').css({'pointer-events': 'none', 'opacity': '0.4'});
                    var default_progress='<p><span class="wpvivid-span-progress"><span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width: 0%">0% completed</span></span></p><p><span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span><span>Restoring the snapshot.</span></p>';
                    jQuery('#wpvivid_snapshot_progress').show();
                    jQuery('#wpvivid_snapshot_progress').html(default_progress);
                    b_restore_finished=false;
                    wpvivid_simulate_restore_progress();

                    wpvivid_post_request(ajax_data, function(data)
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            b_restore_finished=true;
                            jQuery('#wpvivid_snapshot_progress').html(jsonarray.progress);
                            alert('Restoring the snapshot completed successfully.');
                            location.reload();                        }
                        else
                        {
                            b_restore_finished=true;
                            jQuery('#wpvivid_snapshot_progress').hide();
                            alert(jsonarray.error);
                            jQuery('#wpvivid_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});
                        }
                    }, function(XMLHttpRequest, textStatus, errorThrown)
                    {
                        setTimeout(function(){
                            wpvivid_get_restore_snapshot_status();
                        }, 1000);
                    });
                }
            });

            function wpvivid_get_restore_snapshot_status()
            {
                var ajax_data= {
                    'action': 'wpvivid_get_restore_snapshot_status',
                };

                wpvivid_post_request(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            if(jsonarray.finished==1)
                            {
                                jQuery('#wpvivid_snapshot_progress').html(jsonarray.progress);
                                b_restore_finished=true;
                                alert('Restoring the snapshot completed successfully.');
                                location.reload();
                            }
                            else
                            {
                                setTimeout(function(){
                                    wpvivid_get_restore_snapshot_status();
                                }, 1000);
                            }
                        }
                        else
                        {
                            b_restore_finished=true;
                            jQuery('#wpvivid_snapshot_progress').hide();
                            alert(jsonarray.error);
                            jQuery('#wpvivid_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});
                        }
                    }
                    catch (err)
                    {
                        setTimeout(function(){
                            wpvivid_get_restore_snapshot_status();
                        }, 1000);
                    }

                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    setTimeout(function(){
                        wpvivid_get_restore_snapshot_status();
                    }, 1000);
                });
            }

            jQuery('#wpvivid_snapshots_list').on("click",'.wpvivid-snapshot-delete',function()
            {
                var Obj=jQuery(this);
                var snapshot_id=Obj.closest('tr').attr('slug');

                var descript = '<?php esc_html_e('Are you sure you want to delete this snapshot?', 'wpvivid'); ?>';
                var ret = confirm(descript);
                if (ret === true)
                {
                    var ajax_data= {
                        'action': 'wpvivid_delete_snapshot',
                        'id':snapshot_id
                    };
                    jQuery('#wpvivid_create_snapshot').css({'pointer-events': 'none', 'opacity': '0.4'});
                    wpvivid_post_request(ajax_data, function(data)
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            alert('The snapshot has been deleted successfully.');
                            jQuery('#wpvivid_snapshots_list').html(jsonarray.html);
                            jQuery('#wpvivid_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});
                        }
                        else
                        {
                            alert(jsonarray.error);
                            jQuery('#wpvivid_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});
                        }
                    }, function(XMLHttpRequest, textStatus, errorThrown)
                    {
                        jQuery('#wpvivid_create_snapshot').css({'pointer-events': 'auto', 'opacity': '1'});
                        alert("Deleting the snapshot(s) failed.");
                    });
                }
            });

            jQuery('#wpvivid_snapshots_list').on("click",'#wpvivid_delete_snapshots_action',function()
            {
                var delete_snapshots_array = new Array();
                var count = 0;

                jQuery('#wpvivid_snapshots_list .wpvivid-snapshot-row input').each(function (i)
                {
                    if(jQuery(this).prop('checked'))
                    {
                        delete_snapshots_array[count] =jQuery(this).closest('tr').attr('slug');
                        count++;
                    }
                });
                if( count === 0 )
                {
                    alert('<?php esc_html_e('Please select at least one item.','wpvivid'); ?>');
                }
                else
                {
                    var descript = '<?php esc_html_e('Are you sure to delete the selected snapshots? These snapshots will be deleted permanently.', 'wpvivid'); ?>';

                    var ret = confirm(descript);
                    if (ret === true)
                    {
                        jQuery('#wpvivid_delete_snapshots_action').css({'pointer-events': 'none', 'opacity': '0.4'});
                        wpvivid_delete_snapshot_array(delete_snapshots_array,0);
                    }
                }
            });

            function wpvivid_delete_snapshot_array(delete_snapshots_array,index)
            {
                if(index >= delete_snapshots_array.length)
                {
                    alert('The snapshot has been deleted successfully.');
                    jQuery('#wpvivid_delete_snapshots_action').css({'pointer-events': 'auto', 'opacity': '1'});
                    return;
                }
                const snapshot_id = delete_snapshots_array[index];
                var ajax_data= {
                    'action': 'wpvivid_delete_snapshot',
                    'id':snapshot_id
                };
                wpvivid_post_request(ajax_data, function(data)
                {
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success')
                    {
                        jQuery('#wpvivid_snapshots_list').html(jsonarray.html);
                        index++;
                        wpvivid_delete_snapshot_array(delete_snapshots_array,index);
                    }
                    else
                    {
                        alert(jsonarray.error);
                        jQuery('#wpvivid_delete_snapshots_action').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    alert('Deleting the snapshot(s) failed.');
                    jQuery('#wpvivid_delete_snapshots_action').css({'pointer-events': 'auto', 'opacity': '1'});
                });
            }
        </script>
        <?php
    }

    public function output_snapshots_setting()
    {

        $setting=$this->options->get_option('wpvivid_snapshot_setting');
        if(empty($setting))
        {
            $setting=array();
        }

        $snapshot_retention=isset($setting['snapshot_retention'])?$setting['snapshot_retention']:6;
        $quick_snapshot=isset($setting['quick_snapshot'])?$setting['quick_snapshot']:false;
        if($quick_snapshot)
        {
            $quick_snapshot='checked';
        }
        else
        {
            $quick_snapshot='';
        }
        ?>
        <div class="postbox quicksnapshot">
            <table class="widefat" style="border-left:none;border-top:none;border-right:none;">
                <tr>
                    <td class="row-title" style="min-width:200px;">
                        <label for="tablecell">Snapshot Retention</label>
                    </td>
                    <td>
                        <p>
                            <span>Up to </span>
                            <span>
                                <select id="wpvivid_snapshot_retention" option="setting" name="snapshot_retention">
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6" selected>6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                </select>
                            </span>
                            <span>snapshots retained</span></p>
                        <p>It is not recommended to create too many snapshots.</p>
                    </td>
                </tr>
                <tr>
                    <td class="row-title" style="min-width:200px;">
                        <label for="tablecell">Quick Snapshot</label>
                    </td>
                    <td>
                        <p>
                            <label class="wpvivid-checkbox">
                                <span>Enable Quick Snapshot</span>
                                <input type="checkbox" option="setting" name="quick_snapshot" <?php echo esc_attr($quick_snapshot); ?> />
                                <span class="wpvivid-checkbox-checkmark"></span>
                            </label>
                        </p>
                        <p><code>Show a menu in top admin bar for quickly creating a snapshot.</code></p>
                    </td>
                </tr>
            </table>
            <div style="padding:1em 1em 0 0;"><input class="button-primary wpvivid-snapshot-setting-save" type="submit" value="Save Changes"></div>
        </div>
        <script>
            jQuery('.wpvivid-snapshot-setting-save').click(function()
            {
                wpvivid_snapshot_setting_save();
            });

            function wpvivid_ajax_snapshot_data_transfer(data_type){
                var json = {};
                jQuery('input:checkbox[option='+data_type+']').each(function() {
                    var value = '0';
                    var key = jQuery(this).prop('name');
                    if(jQuery(this).prop('checked')) {
                        value = '1';
                    }
                    else {
                        value = '0';
                    }
                    json[key]=value;
                });
                jQuery('input:radio[option='+data_type+']').each(function() {
                    if(jQuery(this).prop('checked'))
                    {
                        var key = jQuery(this).prop('name');
                        var value = jQuery(this).prop('value');
                        json[key]=value;
                    }
                });
                jQuery('input:text[option='+data_type+']').each(function(){
                    var obj = {};
                    var key = jQuery(this).prop('name');
                    var value = jQuery(this).val();
                    json[key]=value;
                });
                jQuery('input:password[option='+data_type+']').each(function(){
                    var obj = {};
                    var key = jQuery(this).prop('name');
                    var value = jQuery(this).val();
                    json[key]=value;
                });
                jQuery('select[option='+data_type+']').each(function(){
                    var obj = {};
                    var key = jQuery(this).prop('name');
                    var value = jQuery(this).val();
                    json[key]=value;
                });
                return JSON.stringify(json);
            }

            function wpvivid_snapshot_setting_save()
            {
                var setting_data = wpvivid_ajax_snapshot_data_transfer('setting');
                var json = JSON.parse(setting_data);
                setting_data=JSON.stringify(json);

                var ajax_data = {
                    'action': 'wpvivid_set_snapshot_setting',
                    'setting': setting_data,
                };
                jQuery('.wpvivid-snapshot-setting-save').css({'pointer-events': 'none', 'opacity': '0.4'});
                wpvivid_post_request(ajax_data, function (data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);

                        jQuery('.wpvivid-snapshot-setting-save').css({'pointer-events': 'auto', 'opacity': '1'});
                        if (jsonarray.result === 'success')
                        {
                            location.reload();
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err)
                    {
                        alert(err);
                        jQuery('.wpvivid-snapshot-setting-save').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                },function (XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_msg = "request: "+ textStatus + "(" + errorThrown + "): an error occurred when changing snapshot settings. " +
                        "This error may be request not reaching or server not responding. Please try again later.";
                    alert(error_msg);
                });
            }

            jQuery(document).ready(function ()
            {
                jQuery('#wpvivid_snapshot_retention').val("<?php echo esc_attr($snapshot_retention)?>").change();
            });
        </script>
        <?php
    }

    public function create_snapshot()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        if(isset($_POST['comment'])&&!empty($_POST['comment']))
        {
            $comment=sanitize_text_field($_POST['comment']);
        }
        else
        {
            $comment='';
        }

        set_time_limit(300);
        $snapshot=new WPvivid_Snapshot_Function_Ex();
        $snapshot->check_manual_snapshot();
        $ret=$snapshot->create_snapshot('manual',$comment);
        if($ret['result']=='success')
        {
            if($ret['finished']==1)
            {
                $ret['progress'] = '<p><span class="wpvivid-span-progress"><span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width:100%">100% completed</span></span></p>         
                                     <p><span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span><span>Create snapshot completed.</span></p>';
            }
            else
            {
                $progress=$snapshot->get_progress();
                $ret['progress'] = '<p><span class="wpvivid-span-progress"><span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width:'.$progress['main_percent'].'">'.$progress['main_percent'].' completed</span></span></p>         
                                     <p><span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span><span>'.$progress['doing'].'</span></p>';
            }
        }

        echo wp_json_encode($ret);
        die();
    }

    public function get_snapshot_progress()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        set_time_limit(300);
        $snapshot=new WPvivid_Snapshot_Function_Ex();

        $progress=$snapshot->get_progress();
        $ret['progress'] = '<p><span class="wpvivid-span-progress"><span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width:'.$progress['main_percent'].'">'.$progress['main_percent'].' completed</span></span></p>         
                                     <p><span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span><span>'.$progress['doing'].'</span></p>';

        echo wp_json_encode($ret);
        die();
    }

    public function resume_create_snapshot()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        set_time_limit(300);
        $snapshot=new WPvivid_Snapshot_Function_Ex();
        $ret=$snapshot->resume_create_snapshot();

        if($ret['result']=='success')
        {
            if($ret['finished']==1)
            {
                $ret['progress'] = '<p><span class="wpvivid-span-progress"><span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width:100%">100% completed</span></span></p>         
                                     <p><span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span><span>Create snapshot completed.</span></p>';
            }
            else
            {
                $progress=$snapshot->get_progress();
                $ret['progress'] = '<p><span class="wpvivid-span-progress"><span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width:'.$progress['main_percent'].'">'.$progress['main_percent'].' completed</span></span></p>         
                                     <p><span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span><span>'.$progress['doing'].'</span></p>';
            }
        }

        echo wp_json_encode($ret);
        die();
    }

    public function restore_snapshot()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        if(isset($_POST['id']))
        {
            $snapshot_id=sanitize_text_field($_POST['id']);

            set_time_limit(300);
            $snapshot=new WPvivid_Snapshot_Function_Ex();
            $ret=$snapshot->restore_snapshot($snapshot_id);
            if($ret['result']=='success')
            {
                $ret['progress'] = '<p><span class="wpvivid-span-progress"><span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width:100%">100% completed</span></span></p>         
                                     <p><span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span><span>Restoring the snapshot completed.</span></p>';
            }
            echo wp_json_encode($ret);
        }

        die();
    }

    public function get_restore_snapshot_status()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        set_time_limit(300);
        $snapshot=new WPvivid_Snapshot_Function_Ex();
        $ret=$snapshot->get_restore_task_data();

        if($ret['result']!='failed')
        {
            $finished=true;
            $i_sum=count($ret['snapshot_tables']);
            $i_finished=0;
            foreach ($ret['snapshot_tables'] as $table)
            {
                if($table['finished']==0)
                {
                    $finished=false;
                }
                else
                {
                    $i_finished++;
                }
            }

            $i_progress=intval(($i_finished/$i_sum)*100);
            $progress['main_percent']=$i_progress.'%';
            $progress['doing']="Restoring the snapshot.";
            $ret['progress'] = '<p><span class="wpvivid-span-progress"><span class="wpvivid-span-processed-progress wpvivid-span-processed-percent-progress" style="width:'.$progress['main_percent'].'">'.$progress['main_percent'].' completed</span></span></p>         
                                     <p><span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey"></span><span>Action:</span><span>'.$progress['doing'].'</span></p>';

            $ret['finished']=$finished;
        }

        echo wp_json_encode($ret);
        die();
    }

    public function delete_snapshot()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        if(isset($_POST['id']))
        {
            $snapshot_id=sanitize_text_field($_POST['id']);

            set_time_limit(300);
            $snapshot=new WPvivid_Snapshot_Function_Ex();
            $ret=$snapshot->remove_snapshot($snapshot_id);
            if($ret['result']=='success')
            {
                $snapshot_data=$snapshot->get_snapshots();
                $Snapshots_list = new WPvivid_Snapshots_List_Ex();
                $Snapshots_list->set_list($snapshot_data);
                $Snapshots_list->prepare_items();
                ob_start();
                $Snapshots_list->display();
                $html = ob_get_clean();
                $ret['html']=$html;
            }

            echo wp_json_encode($ret);
        }
        die();
    }

    public function check_create_snapshot($check)
    {
        return true;
    }

    public function create_snapshot_ex($comment)
    {
        set_time_limit(300);
        $snapshot=new WPvivid_Snapshot_Function_Ex();
        $snapshot->create_snapshot('manual',$comment);
    }

    public function set_setting()
    {
        check_ajax_referer( 'wpvivid_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        $check=apply_filters('wpvivid_ajax_check_security',$check);
        if(!$check)
        {
            die();
        }

        if(isset($_POST['setting'])&&!empty($_POST['setting']))
        {
            $json_setting = sanitize_text_field($_POST['setting']);
            $json_setting = stripslashes($json_setting);
            $setting = json_decode($json_setting, true);
            if (is_null($setting))
            {
                $ret['result']='failed';
                $ret['error']='json decode failed';
                echo wp_json_encode($ret);
                die();
            }

            $old_setting=$this->options->get_option('wpvivid_snapshot_setting');
            if(empty($setting))
            {
                $setting=array();
            }

            if(isset($setting['snapshot_retention']))
            {
                $old_setting['snapshot_retention']=intval($setting['snapshot_retention']);
            }

            if(isset($setting['quick_snapshot']))
            {
                $old_setting['quick_snapshot']=intval($setting['quick_snapshot']);
            }

            $this->options->update_option('wpvivid_snapshot_setting',$old_setting);
        }
        $ret['result']='success';
        echo wp_json_encode($ret);
        die();
    }

    public function add_sidebar_free()
    {
        if(defined('WPVIVID_SNAPSHOT_VERSION'))
        {
            $wpvivid_snapshot_version = WPVIVID_SNAPSHOT_VERSION;
        }
        else
        {
            $wpvivid_snapshot_version = WPVIVID_PLUGIN_VERSION;
        }

        ?>
        <div class="postbox">
            <h2>
                <div style="float: left; margin-right: 5px;"><span style="margin: 0; padding: 0"><?php esc_html_e('Current Version: ', 'wpvivid-backuprestore'); ?><?php echo esc_html($wpvivid_snapshot_version); ?></span></div>
                <div style="float: left; margin-right: 5px;"><span style="margin: 0; padding: 0">|</span></div>
                <div style="float: left; margin-left: 0;">
                    <span style="margin: 0; padding: 0"><a href="https://wordpress.org/plugins/wpvivid-snapshot-database/#developers" target="_blank" style="text-decoration: none;"><?php esc_html_e('ChangeLog', 'wpvivid-backuprestore'); ?></a></span>
                </div>
                <div style="clear: both;"></div>
            </h2>
        </div>
        <div id="wpvivid_backup_schedule_part"></div>
        <div class="postbox">
            <h2><span><?php esc_html_e('Troubleshooting', 'wpvivid-backuprestore'); ?></span></h2>
            <div class="inside">
                <table class="widefat" cellpadding="0">
                    <tbody>
                    <tr class="alternate">
                        <td class="row-title"><a href="https://docs.wpvivid.com/wpvivid-database-snapshots-create-database-snapshots-wordpress.html" target="_blank"><?php esc_html_e('Create Database Snapshots', 'wpvivid-backuprestore'); ?></a></td>
                    </tr>
                    <tr>
                        <td class="row-title"><a href="https://docs.wpvivid.com/wpvivid-database-snapshots-restore-database-snapshots-wordpress.html" target="_blank"><?php esc_html_e('Restore Database Snapshots', 'wpvivid-backuprestore'); ?></a></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="postbox">
            <h2><span><?php esc_html_e('Support', 'wpvivid-backuprestore'); ?></span></h2>
            <div class="inside">
                <table class="widefat" cellpadding="0">
                    <tbody>
                    <tr class="alternate"><td class="row-title"><a href="https://wordpress.org/support/plugin/wpvivid-snapshot-database" target="_blank"><?php esc_html_e('Get Support on Forum', 'wpvivid-backuprestore'); ?></a></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public function add_sidebar()
    {
        if(apply_filters('wpvivid_show_sidebar',true))
        {
            ?>
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox  wpvivid-sidebar">
                        <h2 style="margin-top:0.5em;">
                            <span class="dashicons dashicons-book-alt wpvivid-dashicons-orange" ></span>
                            <span><?php esc_attr_e(
                                    'Documentation', 'WpAdminStyle'
                                ); ?></span></h2>
                        <div class="inside" style="padding-top:0;">
                            <ul class="" >
                                <li>
                                    <span class="dashicons dashicons-camera-alt wpvivid-dashicons-grey"></span>
                                    <a href="https://docs.wpvivid.com/wpvivid-database-snapshots-create-database-snapshots-wordpress.html"><b><?php esc_html_e('Create Database Snapshots', 'wpvivid'); ?></b></a>
                                    <small><span style="float: right;"><a href="#" style="text-decoration: none;"><span class="dashicons dashicons-migrate wpvivid-dashicons-grey"></span></a></span></small><br>
                                </li>
                                <li>
                                    <span class="dashicons dashicons-camera-alt wpvivid-dashicons-grey"></span>
                                    <a href="https://docs.wpvivid.com/wpvivid-database-snapshots-restore-database-snapshots-wordpress.html"><b><?php esc_html_e('Restore Database Snapshots', 'wpvivid'); ?></b></a>
                                    <small><span style="float: right;"><a href="#" style="text-decoration: none;"><span class="dashicons dashicons-migrate wpvivid-dashicons-grey"></span></a></span></small><br>
                                </li>
                            </ul>
                        </div>
                        <h2><span class="dashicons dashicons-businesswoman wpvivid-dashicons-green"></span>
                            <span><?php esc_attr_e(
                                    'Support', 'WpAdminStyle'
                                ); ?></span></h2>
                        <div class="inside">
                            <ul class="">
                                <li><span class="dashicons dashicons-admin-comments wpvivid-dashicons-green"></span>
                                    <a href="https://wordpress.org/support/plugin/snapshot-database/"><b><?php esc_html_e('Get Support on Forum', 'wpvivid'); ?></b></a>
                                    <br>
                                    <?php esc_html_e('If you need any help with our plugin, start a thread on the plugin support forum and we will respond shortly.', 'wpvivid'); ?>
                                </li>
                            </ul>

                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}