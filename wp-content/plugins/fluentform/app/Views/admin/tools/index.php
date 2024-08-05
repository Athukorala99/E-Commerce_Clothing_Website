<?php

use FluentForm\App\Helpers\Helper;
?>
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

<div class="ff_form_wrap ff_tools_wrap">
    <div class="ff_admin_menu_wrapper ff_layout_section">
        <?php
            do_action_deprecated(
                'fluentform_before_export_import_wrapper',
                [
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/before_tools_wrapper',
                'Use fluentform/before_tools_wrapper instead of fluentform_before_export_import_wrapper.'
            );
            do_action('fluentform/before_tools_wrapper');
        ?>

        <div class="ff_admin_menu_sidebar ff_layout_section_sidebar">
            <ul class="ff_admin_menu_list ff_list_button">
                <li class="ff_list_button_item active">
                    <a class="ff_list_button_link" data-hash="exportforms"
                       href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_transfer', ['hash' => 'exportforms'])); ?>"
                    >
                        <?php echo __('Export Forms', 'fluentform'); ?>
                    </a>
                </li>
                <li class="ff_list_button_item">
                    <a class="ff_list_button_link" data-hash="importforms"
                       href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_transfer', ['hash' => 'importforms'])); ?>"
                    >
                        <?php echo __('Import Forms', 'fluentform'); ?>
                    </a>
                </li>
                <li class="ff_list_button_item">
                    <a class="ff_list_button_link" data-hash="migrator"
                       href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_transfer', ['hash' => 'migrator'])); ?>"
                    >
                        <?php echo __('Migrator', 'fluentform'); ?>
                    </a>
                </li>

                <li class="ff_list_button_item">
                    <a class="ff_list_button_link" data-hash="activitylogs"
                       href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_transfer', ['hash' => 'activitylogs'])); ?>"
                    >
                        <?php echo __('Activity Logs', 'fluentform'); ?>
                    </a>
                </li>
                <li class="ff_list_button_item">
                    <a class="ff_list_button_link" data-hash="apilogs"
                       href="<?php echo esc_url(Helper::makeMenuUrl('fluent_forms_transfer', ['hash' => 'apilogs'])); ?>"
                    >
                        <?php echo __('API Logs', 'fluentform'); ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="ff_admin_menu_container ff_layout_section_container">
            <?php
                do_action_deprecated(
                    'fluentform_before_export_import_container',
                    [
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/before_tools_container',
                    'Use fluentform/before_tools_container instead of fluentform_before_export_import_container.'
                );
                do_action('fluentform/before_tools_container');
            ?>
            <div class="ff_transfer" id="ff_transfer_app">
                <component :is="component" :app="App"></component>
                <global-search></global-search>
            </div>
            <?php
                do_action_deprecated(
                    'fluentform_after_export_import_container',
                    [
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/after_tools_container',
                    'Use fluentform/after_tools_container instead of fluentform_after_export_import_container.'
                );
                do_action('fluentform/after_tools_container');
            ?>
        </div>
        <?php
            do_action_deprecated(
                'fluentform_after_export_import_wrapper',
                [
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/after_tools_wrapper',
                'Use fluentform/after_tools_wrapper instead of fluentform_after_export_import_wrapper.'
            );
            do_action('fluentform/after_tools_wrapper');
        ?>
    </div>
</div>
