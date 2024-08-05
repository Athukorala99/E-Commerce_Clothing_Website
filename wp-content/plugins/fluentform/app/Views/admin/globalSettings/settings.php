<?php
    do_action_deprecated(
        'fluentform_before_global_settings_option_render',
        [
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/before_global_settings_option_render',
        'Use fluentform/before_global_settings_option_render instead of fluentform_before_global_settings_option_render.'
    );
    do_action('fluentform/before_global_settings_option_render');
?>

<div class="ff_global_settings_option" id="ff_global_settings_option_app">
    <component
        :settings_key="settings_key"
        :is="component"
        :current_component="component"
        :app="App"
    ></component>
    <global-search></global-search>
</div>

<?php
    do_action_deprecated(
        'fluentform_after_global_settings_option_render',
        [
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/after_global_settings_option_render',
        'Use fluentform/after_global_settings_option_render instead of fluentform_after_global_settings_option_render.'
    );
    do_action('fluentform/after_global_settings_option_render');
?>
