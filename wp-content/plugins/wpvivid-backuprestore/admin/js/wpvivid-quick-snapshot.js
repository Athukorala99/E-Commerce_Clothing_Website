function wpvivid_post_request_quick(ajax_data, callback, error_callback, time_out){
    if(typeof time_out === 'undefined')    time_out = 30000;
    ajax_data.nonce=wpvivid_quick_snapshot_ajax_object.ajax_nonce;
    jQuery.ajax({
        type: "post",
        url: wpvivid_quick_snapshot_ajax_object.ajax_url,
        data: ajax_data,
        cache:false,
        success: function (data) {
            callback(data);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            error_callback(XMLHttpRequest, textStatus, errorThrown);
        },
        timeout: time_out
    });
}

