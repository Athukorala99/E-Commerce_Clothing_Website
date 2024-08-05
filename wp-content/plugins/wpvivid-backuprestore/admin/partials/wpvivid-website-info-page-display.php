<?php
?>
<script>
    function wpvivid_get_ini_memory_limit() {
        var ajax_data = {
            'action': 'wpvivid_get_ini_memory_limit'
        };
        wpvivid_post_request(ajax_data, function (data) {
            try {
                jQuery('#wpvivid_websiteinfo_list tr').each(function (i) {
                    jQuery(this).children('td').each(function (j) {
                        if (j == 0) {
                            if (jQuery(this).html().indexOf('memory_limit') >= 0) {
                                jQuery(this).next().html(data);
                            }
                        }
                    });
                });
            }
            catch (err) {
                setTimeout(function ()
                {
                    wpvivid_get_ini_memory_limit();
                }, 3000);
            }
        }, function (XMLHttpRequest, textStatus, errorThrown) {
            setTimeout(function ()
            {
                wpvivid_get_ini_memory_limit();
            }, 3000);
        });
    }

    //
    jQuery('#wpvivid_tab_debug').click(function()
    {
        wpvivid_get_ini_memory_limit();
    });

    jQuery(document).ready(function ()
    {
        jQuery(document).on('wpvivid-switch-tabs', function(event,contentName)
        {
            if(contentName=='debug-page')
            {
                wpvivid_get_ini_memory_limit();
            }

            if(contentName=='settings-page')
            {
                wpvivid_calculate_diskspaceused();
            }
        });

        //wpvivid_get_ini_memory_limit();
    });
</script>
<?php
?>
