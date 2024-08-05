<?php
    do_action_deprecated(
        'fluentform_before_form_settings_app',
        [
            $form_id
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/before_form_settings_app',
        'Use fluentform/before_form_settings_app instead of fluentform_before_form_settings_app.'
    );
    do_action('fluentform/before_form_settings_app', $form_id);
?>
<div id="ff_form_settings_app"></div>
<?php
    do_action_deprecated(
        'fluentform_after_form_settings_app',
        [
            $form_id
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/after_form_settings_app',
        'Use fluentform/after_form_settings_app instead of fluentform_after_form_settings_app.'
    );
    do_action('fluentform/after_form_settings_app', $form_id);
?>
