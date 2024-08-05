<?php

if (!defined('WPVIVID_PLUGIN_DIR'))
{
    die;
}

if(!class_exists('WPvivid_Tab_Page_Container'))
{
    include_once WPVIVID_PLUGIN_DIR . '/includes/class-wpvivid-tab-page-container.php';
}

class WPvivid_Tab_Page_Container_Ex extends WPvivid_Tab_Page_Container
{
    public $is_transparency=0;

    public function add_tab($title,$slug,$callback,$args=array())
    {
        $new_tab['title']=$title;
        $new_tab['slug']=$slug;
        $new_tab['page']=$callback;
        foreach ($args as $key=>$arg)
        {
            $new_tab[$key]=$arg;
            if($key === 'is_parent_tab') {
                $this->is_parent_tab = $arg;
            }
            if($key === 'transparency'){
                $this->is_transparency = $arg;
            }
        }

        $this->tabs[]=$new_tab;
    }

    public function display()
    {
        $class = '';
        if($this->is_transparency){
            $class .= ' wpvivid-intab-addon';
        }
        ?>
        <div class="wpvivid-one-coloum" id="<?php echo esc_attr($this->container_id); ?>">
            <h2 class="nav-tab-wrapper wpvivid-nav-tab-wrapper">
                <?php
                $this->display_tabs();
                ?>
            </h2>
        </div>
        <?php
        if($this->is_parent_tab){
            ?>
            <div style="margin: 10px 0 0 2px;">
                <div id="poststuff" style="padding-top: 0;">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <div class="inside" style="margin-top:0;">
                                <div>
                                    <?php
                                    $this->display_page();
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div id="postbox-container-1" class="postbox-container">
                            <div class="meta-box-sortables">
                            </div>
                        </div>
                    </div>
                    <br class="clear">
                </div>
            </div>
            <?php
        }
        else{
            ?>
            <?php
            $this->display_page();
            ?>
            <?php
        }
        ?>

        <script>
            jQuery('#<?php echo esc_js($this->container_id)?>').on("click",".<?php echo esc_js($this->container_id)?>-tab",function()
            {
                jQuery('#<?php echo esc_js($this->container_id)?>').find( '.<?php echo esc_js($this->container_id)?>-tab' ).each(function()
                {
                    jQuery(this).removeClass( "nav-tab-active wpvivid-nav-tab-active" );
                });

                jQuery('.<?php echo esc_js($this->container_id)?>-content').each(function()
                {
                    jQuery(this).hide();
                });

                var id=jQuery(this).attr('id');
                id= id.substr(12);

                jQuery("#wpvivid_page_"+id).show();
                jQuery(this).addClass( "nav-tab-active wpvivid-nav-tab-active" );
                var top = jQuery(this).offset().top-jQuery(this).height();
                jQuery('html, body').animate({scrollTop:top}, 'slow');
            });

            jQuery('#<?php echo esc_js($this->container_id)?>').on("click",".nav-tab-delete-img-addon",function(event)
            {
                event.stopPropagation();
                var redirect=jQuery(this).attr('redirect');
                jQuery(this).parent().hide();

                jQuery('#<?php echo esc_js($this->container_id)?>').find( '.<?php echo esc_js($this->container_id)?>-tab' ).each(function()
                {
                    jQuery(this).removeClass( "nav-tab-active wpvivid-nav-tab-active" );
                });

                jQuery('.<?php echo esc_js($this->container_id)?>-content').each(function()
                {
                    jQuery(this).hide();
                });

                jQuery("#wpvivid_page_"+redirect).show();
                jQuery("#wpvivid_tab_"+redirect).addClass( "nav-tab-active wpvivid-nav-tab-active" );
                //jQuery(this).addClass( "nav-tab-active wpvivid-nav-tab-active" );
            });

            jQuery(document).ready(function($)
            {
                jQuery(document).on('<?php echo esc_js($this->container_id)?>-show', function(event,id,redirect)
                {
                    jQuery('#<?php echo esc_js($this->container_id)?>').find( '.<?php echo esc_js($this->container_id)?>-tab' ).each(function()
                    {
                        jQuery(this).removeClass( "nav-tab-active wpvivid-nav-tab-active" );
                    });

                    jQuery('.<?php echo esc_js($this->container_id); ?>-content').each(function()
                    {
                        jQuery(this).hide();
                    });

                    jQuery("#wpvivid_page_"+id).show();
                    jQuery("#wpvivid_tab_"+id).show();
                    jQuery("#wpvivid_tab_"+id).find( '.nav-tab-delete-img-addon' ).each(function()
                    {
                        jQuery(this).attr('redirect',redirect);
                    });
                    jQuery("#wpvivid_tab_"+id).addClass( "nav-tab-active wpvivid-nav-tab-active" );
                    var top = jQuery("#wpvivid_tab_"+id).offset().top-jQuery("#wpvivid_tab_"+id).height();
                    jQuery('html, body').animate({scrollTop:top}, 'slow');
                });

                jQuery(document).on('<?php echo esc_js($this->container_id)?>-delete', function(event,id,redirect)
                {
                    jQuery('#<?php echo esc_js($this->container_id)?>').find( '.<?php echo esc_js($this->container_id)?>-tab' ).each(function()
                    {
                        jQuery(this).removeClass( "nav-tab-active wpvivid-nav-tab-active" );
                    });

                    jQuery('#<?php echo esc_js($this->container_id)?>').find( '.<?php echo esc_js($this->container_id)?>-content' ).each(function()
                    {
                        jQuery(this).hide();
                    });

                    jQuery("#wpvivid_page_"+id).hide();
                    jQuery("#wpvivid_tab_"+id).hide();
                    jQuery("#wpvivid_page_"+redirect).show();
                    jQuery("#wpvivid_tab_"+redirect).addClass( "nav-tab-active wpvivid-nav-tab-active" );
                });
            });
        </script>
        <?php
    }

    public function display_tabs()
    {
        $first=true;

        foreach ($this->tabs as $tab)
        {
            $class='nav-tab wpvivid-nav-tab '.$this->container_id.'-tab';
            $span_class='';
            $span_style='';
            if($first)
            {
                $class.=' nav-tab-active wpvivid-nav-tab-active';
                $first=false;
            }

            $style='cursor:pointer;';

            if(isset($tab['hide']))
            {
                $style.=' display: none';
            }

            if(isset($tab['span_class']))
            {
                $span_class.=$tab['span_class'];
            }
            if(isset($tab['span_style']))
            {
                $span_style.=$tab['span_style'];
            }
            if(isset($tab['can_delete']))
            {
                $class.=' delete';
            }
            if(isset($tab['transparency']))
            {
                $class.=' wpvivid-transparency-tab';
            }

            echo '<a id="wpvivid_tab_'.esc_attr($tab['slug']).'" class="'.esc_attr($class).'" style="'.esc_attr($style).'">';

            if(isset($tab['can_delete']))
            {
                echo '<span class="'.esc_attr($span_class).'" style="'.esc_attr($span_style).'"></span><span style="padding-right: 1em;">'.esc_attr($tab['title']).'</span>';
                if(isset($tab['redirect']))
                {
                    echo '<div class="nav-tab-delete-img-addon" redirect="'.esc_attr($tab['redirect']).'">
                                <span class="dashicons dashicons-no-alt wpvivid-dashicons-grey" style="width: 15px; height: 15px; font-size: 15px; padding-top: 0.4em;">
                       </div>';
                }
                else
                {
                    echo '<div class="nav-tab-delete-img-addon">
                          <span class="dashicons dashicons-no-alt wpvivid-dashicons-grey" style="width: 15px; height: 15px; font-size: 15px; padding-top: 0.4em;">
                       </div>';
                }
            }
            else
            {
                echo '<span class="'.esc_attr($span_class).'" style="'.esc_attr($span_style).'"></span><span>'.esc_attr($tab['title']).'</span>';
            }
            echo '</a>';
        }
    }

    public function display_page()
    {
        $first=true;
        foreach ($this->tabs as $tab)
        {
            //delete
            /*$style='display: none;';
            if($first)
            {
                if(isset($tab['hide']))
                {

                }
                else
                {
                    $style='';
                    $first=false;
                }
            }*/
            if(isset($tab['div_style']))
            {
                $style = $tab['div_style'];
            }
            else{
                $style='display: none;';
            }

            $class='wpvivid-one-coloum wpvivid-tabcontent ';
            $class.=$this->container_id.'-content';
            echo '<div id="wpvivid_page_'.esc_attr($tab['slug']).'" class="'.esc_attr($class).'" style="'.esc_attr($style).'">';
            call_user_func($tab['page']);
            echo '</div>';
        }
    }
}