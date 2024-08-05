<style>
    #no_permission_page {
        background: #fff;
        border: 1px solid #ccd0d4;
        color: #444;
        margin: 2em auto;
        padding: 1em 2em;
        max-width: 700px;
        box-shadow: 0 1px 1px rgb(0 0 0 / 4%);
    }
</style>

<?php
do_action_deprecated(
    'fluentform_before_no_permission',
    [
    ],
    FLUENTFORM_FRAMEWORK_UPGRADE,
    'fluentform/before_no_permission',
    'Use fluentform/before_no_permission instead of fluentform_before_no_permission.'
);
    do_action('fluentform/before_no_permission');
?>

<div id="no_permission_page">
    <div class="wp-die-message">
        <?php echo __('Sorry, you are not allowed to access this page.', 'fluentform'); ?>
    </div>
</div>

<?php
    do_action_deprecated(
        'fluentform_after_no_permission',
        [
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/after_no_permission',
        'Use fluentform/after_no_permission instead of fluentform_after_no_permission.'
    );
    do_action('fluentform/after_no_permission');
?>
