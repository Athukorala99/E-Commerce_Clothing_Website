<div>
	<?php
        do_action_deprecated(
            'fluentform_before_editor_start',
            [
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/before_editor_start',
            'Use fluentform/before_editor_start instead of fluentform_before_editor_start.'
        );
        do_action('fluentform/before_editor_start');

    ?>
	<div id="ff_form_editor_app">
		<ff_form_editor v-if="!loading" :form="form" :form_saving="form_saving" :save_form="saveForm"></ff_form_editor>
        <global-search></global-search>
	</div>
	<?php
        do_action_deprecated(
            'fluentform_after_editor_start',
            [
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/after_editor_start',
            'Use fluentform/after_editor_start instead of fluentform_after_editor_start.'
        );
        do_action('fluentform/after_editor_start');
    ?>
</div>