<?php

use FluentForm\App\Modules\Acl\Acl;

$page = sanitize_text_field($_GET['page']);
?>
<div class="ff_header">
    <div class="ff_header_group">
        <span class="plugin-name">
            <img src="<?php echo esc_url($logo); ?>"/>
        </span>
        <span class="ff_menu_toggle">
            <i class="ff-icon ff-icon-menu"></i>
        </span>
    </div>
    <ul class="ff_menu">
        <li class="<?php echo ($page == 'fluent_forms') ? 'active' : '' ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms')); ?>" class="ff_menu_link">
                <?php _e('Forms', 'fluentform'); ?>
            </a>
        </li>
        <li class="<?php echo ($page == 'fluent_forms_all_entries') ? 'active' : '' ?>">
            <?php if (Acl::hasPermission('fluentform_entries_viewer')): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_all_entries'));?>" class="ff_menu_link">
                    <?php _e('Entries', 'fluentform'); ?>
                </a>
            <?php endif; ?>
        </li>
        <li class="<?php echo ($page == 'fluent_forms_payment_entries') ? 'active' : '' ?>">
            <?php if ($show_payment_entries && Acl::hasPermission('fluentform_view_payments')): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_payment_entries')); ?>" class="ff_menu_link">
                    <?php _e('Payments', 'fluentform'); ?>
                </a>
            <?php endif; ?>
        </li>
        <?php if (Acl::hasPermission('fluentform_settings_manager')): ?>
            <li class="<?php echo ($page == 'fluent_forms_settings') ? 'active' : '' ?>">
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_settings')); ?>" class="ff_menu_link">
                    <?php _e('Global Settings', 'fluentform'); ?>
                </a>
            </li>
            <li class="<?php echo ($page == 'fluent_forms_transfer') ? 'active' : '' ?>">
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_transfer')); ?>" class="ff_menu_link">
                    <?php _e('Tools', 'fluentform'); ?>
                </a>
            </li>
            <li class="<?php echo ($page == 'fluent_forms_add_ons') ? 'active' : '' ?>">
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_add_ons')); ?>" class="ff_menu_link">
                    <?php _e('Integrations', 'fluentform'); ?>
                </a>
            </li>
        <?php endif; ?>
        <li>
            <?php if ($show_payment && Acl::hasPermission('fluentform_view_payments')): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_settings&component=payment_settings')); ?>" class="ff_menu_link">
                    <?php _e('Payments', 'fluentform'); ?><span class="ff_new_badge">new</span>
                </a>
            <?php endif; ?>
        </li>
        <li class="<?php echo ($page == 'fluent_forms_smtp') ? 'active' : '' ?>">
            <?php if(!defined('FLUENT_MAIL') && !defined('FLUENTFORMPRO')): ?>
                <a class="ff_menu_link" href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_smtp')); ?>">SMTP</a>
            <?php endif; ?>
        </li>
        <li class="<?php echo ($page == 'fluent_forms_docs') ? 'active' : '' ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms_docs')); ?>" class="ff_menu_link">
                <?php _e('Support', 'fluentform'); ?>
            </a>
        </li>
        <?php if(!defined('FLUENTFORMPRO')): ?>
            <li>
                <a target="_blank" rel="noopener" href="<?php echo esc_url(fluentform_upgrade_url()); ?>" class="ff_menu_link ff_menu_link_buy">
                    <?php _e('Upgrade to Pro', 'fluentform'); ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <?php
        $globalSearchActive = apply_filters('fluentform/global_search_active', 'yes');
        $globalSearchButtonClickScript = "";
        if ($globalSearchActive == 'yes') {
            $globalSearchButtonClickScript = "jQuery('.global-search-menu-button').on('click', function() {
                    document.dispatchEvent(new CustomEvent('global-search-menu-button-click'));
                });";
            $user_agent = getenv("HTTP_USER_AGENT");
            if (!empty($user_agent) && strpos($user_agent, "Win") !== FALSE) {
                $key = "Ctrl ";
            } else {
                $key = "⌘";
            }
        }
    ?>
    <?php if($globalSearchActive == 'yes'):?>
        <button class="global-search-menu-button">
            <span class="el-icon-search"></span> <span><?php _e('Search','fluentform') ?></span> <span class="shortcut"><?php echo esc_html($key)?>K </span>
        </button>
    <?php endif; ?>
    <?php

    do_action_deprecated(
        'fluentform_after_global_menu',
        [
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/after_global_menu',
        'Use fluentform/after_global_menu instead of fluentform_after_global_menu.'
    );
    do_action('fluentform/after_global_menu');
    
    wp_add_inline_script('fluent_forms_global', "
                //for mobile nav
                let targetElem = jQuery('.ff_menu');
                jQuery('.ff_menu_toggle').on('click', function() {
                    targetElem.toggleClass('ff_menu_active');
                });
                $globalSearchButtonClickScript
           ");
    ?>
</div>
<?php
do_action('fluentform/after_global_menu_render',$page);
?>
