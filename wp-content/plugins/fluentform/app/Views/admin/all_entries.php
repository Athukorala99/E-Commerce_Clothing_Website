<?php
    do_action_deprecated(
        'fluentform_global_menu',
        [
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/global_menu',
        'Use fluentform/global_menu instead of fluentform_global_menu.'
    );
    do_action('fluentform/global_menu');
?>
<div class="ff_form_wrap">
    <div class="ff_form_wrap_area">
        <?php
            do_action_deprecated(
                'fluentform_before_all_entries_render',
                [
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/before_all_entries_render',
                'Use fluentform/before_all_entries_render instead of fluentform_before_all_entries_render.'
            );
            do_action('fluentform/before_all_entries_render');
        ?>
        <div class="ff_all_forms" id="ff_all_forms_app">
            <div id="ff_all_entries"><ff-all-entries></ff-all-entries><global-search></global-search></div>
        </div>
        <?php
            do_action_deprecated(
                'fluentform_after_all_entries_render',
                [
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/after_all_entries_render',
                'Use fluentform/after_all_entries_render instead of fluentform_after_all_entries_render.'
            );
            do_action('fluentform/after_all_entries_render');
        ?>
    </div>
</div>
<?php
    $dashboard_notices = apply_filters_deprecated(
        'fluentform_dashboard_notices',
        [
            []
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/dashboard_notices',
        'Use fluentform/dashboard_notices instead of fluentform_dashboard_notices.'
    );
$notices = apply_filters('fluentform/dashboard_notices', $dashboard_notices);

if ($notices) {
    echo '<div class="ff_global_notices">';
    foreach ($notices as $noticeKey => $notice) :
        ?>
        <div class="ff_global_notice ff_notice_<?php echo esc_attr($notice['type']); ?>">
            <?php echo esc_html($notice['message']); ?>
        </div>
    <?php
    endforeach;
    echo '</div>';
}