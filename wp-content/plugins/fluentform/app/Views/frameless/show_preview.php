<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="Imagetoolbar" content="No"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php esc_html_e('Preview Form', 'fluentform') ?></title>
    <?php
    wp_head();
    ?>
    <style type="text/css">

    </style>
</head>
<body>
<div class="ff_preview_text">
    Design Mode
</div>
<div id="ff_preview_top">
    <div id="ff_preview_header">
        <div class="ff_form_name">
            <?php echo intval($form->id) .' - '. esc_attr($form->title);  ?>
        </div>
        <ul class="ff_preview_menu">
            <li>
                <a class="ff_preview_menu_link" href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms&form_id=' . intval($form_id) . '&route=editor')) ?>">Edit Fields</a>
            </li>
            <li>
                <a class="ff_preview_menu_link" href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms&form_id=' . intval($form_id) . '&route=settings&sub_route=form_settings#/basic_settings' )) ?>">Settings & Integrations</a>
            </li>
            <li>
                <a class="ff_preview_menu_link" href="<?php echo esc_url(admin_url('admin.php?page=fluent_forms&form_id=' . intval($form_id) . '&route=entries#/' )) ?>">Entries</a>
            </li>
        </ul>
        <div class="ff_preview_only_label_wrap">
            <label for="ff_preview_only"><input id="ff_preview_only" type="checkbox" /> Preview Only</label>
        </div>
        <div class="ff_preview_action" id="copy-toggle">
            <i class="el-icon el-icon-document-copy mr-1"></i>
            <span id="copy">
                [fluentform id="<?php echo intval($form_id); ?>"]
            </span>
        </div>
    </div>
    <div class="ff_preview_body">
        <div class="ff_form_preview_wrapper" style="width:100%">
            <div class="ff_form_preview_header">
                <div class="ff_dot_wrap">
                    <span class="dot dot-red"></span>
                    <span class="dot dot-yellow"></span>
                    <span class="dot dot-success"></span>
                </div>
                <div class="ff_device_control_wrap">
                    <span class="ff_device_control monitor" data-type="monitor">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1024 1024">
                            <path d="M853.35 597.335c0 23.506-19.113 42.665-42.665 42.665h-597.336c-23.552 0-42.666-19.159-42.666-42.665v-341.335c0-23.51 19.114-42.666 42.666-42.666h597.336c23.552 0 42.665 19.157 42.665 42.666v341.335zM810.685 128h-597.336c-70.57 0-128 57.43-128 128v341.335c0 70.569 57.43 128 128 128h256v85.33h-170.666c-23.466 0-42.666 19.2-42.666 42.67 0 23.465 19.2 42.665 42.666 42.665h426.667c23.465 0 42.665-19.2 42.665-42.665 0-23.47-19.2-42.67-42.665-42.67h-170.665v-85.33h256c70.569 0 128-57.431 128-128v-341.335c0-70.57-57.431-128-128-128v0z"/>
                        </svg>
                        <span class="ff_tooltip">Desktop</span>
                    </span>
                    <span class="ff_device_control tablet" data-type="tablet">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1024 1024">
                            <path d="M768 170.683c23.562 0 42.665 19.103 42.665 42.666v597.336c0 23.562-19.103 42.665-42.665 42.665h-512c-23.564 0-42.666-19.103-42.666-42.665v-597.336c0-23.564 19.102-42.666 42.666-42.666h512zM256 85.35c-70.692 0-128 57.308-128 128v597.336c0 70.692 57.308 128 128 128h512c70.692 0 128-57.308 128-128v-597.336c0-70.692-57.308-128-128-128h-512z"/>
                            <path d="M554.685 768.015c0 23.567-19.103 42.67-42.67 42.67-23.563 0-42.665-19.103-42.665-42.67 0-23.562 19.103-42.665 42.665-42.665 23.567 0 42.67 19.103 42.67 42.665z"/>
                        </svg>
                        <span class="ff_tooltip">Tablet</span>
                    </span>
                    <span class="ff_device_control mobile"  data-type="mobile">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1024 1024">
                            <path d="M384.017 170.683h-42.667c-23.564 0-42.666 19.103-42.666 42.666v597.336c0 23.562 19.103 42.665 42.666 42.665h341.336c23.562 0 42.665-19.103 42.665-42.665v-597.336c0-23.564-19.103-42.666-42.665-42.666h-42.67c0 35.346-28.652 64-64 64h-127.998c-35.346 0-64-28.654-64-64zM682.685 85.35c70.692 0 128 57.308 128 128v597.336c0 70.692-57.308 128-128 128h-341.336c-70.692 0-128-57.308-128-128v-597.336c0-70.692 57.308-128 128-128h341.336z"/>
                        </svg>
                        <span class="ff_tooltip">Mobile</span>
                    </span>
                </div>
            </div>
            <div class="ff_form_preview">
                <?php echo do_shortcode('[fluentform id="' . intval($form_id) . '"]'); ?>
            </div>
        </div>
        <div class="ff_form_styler_wrapper">
            <?php
            $isShowPreview = true;

            if(defined('FLUENTFORMPRO')): ?>
            <?php
                do_action_deprecated(
                    'fluentform_form_styler',
                    [
                        $form_id
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/form_styler',
                    'Use fluentform/form_styler instead of fluentform_form_styler.'
                );
                do_action('fluentform/form_styler', $form_id);
            ?>
            <?php
                $isShowPreview = apply_filters_deprecated(
                    'fluentform_show_preview_promo',
                    [
                        true
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/show_preview_promo',
                    'Use fluentform/show_preview_promo instead of fluentform_show_preview_promo.'
                );
                elseif(apply_filters('fluentform/show_preview_promo', $isShowPreview)):
            ?>
                <div class="ff_styler_promo">
                    <div class="ff_promo_header">
                        Advanced Form Styler (Pro)
                    </div>
                    <div class="ff_promo_body">
                        <p><a target="_blank" href="https://wpmanageninja.com/docs/fluent-form/fluent-forms-styles/">Advanced Form styler</a> is available in Pro version of Fluent Forms.
                            You can style every element of the forms including input fields, form container, success / error messages and many more.</p>
                        <h5>Other Features</h5>
                        <ul class="ff_feature_list">
                            <li><i class="el-icon el-icon-check"></i> Stripe & PayPal with 6 Payment Integration</li>
                            <li><i class="el-icon el-icon-check"></i> Style Export & Import</li>
                            <li><i class="el-icon el-icon-check"></i> Conditional Email Routing</li>
                            <li><i class="el-icon el-icon-check"></i> Advanced Form Fields</li>
                            <li><i class="el-icon el-icon-check"></i> Quiz & Survey Forms</li>
                            <li><i class="el-icon el-icon-check"></i> Inventory Module</li>
                            <li><i class="el-icon el-icon-check"></i> 40+ Integrations</li>
                            <li><i class="el-icon el-icon-check"></i> Calculated Fields</li>
                            <li><i class="el-icon el-icon-check"></i> Multi-Step Forms</li>
                            <li><i class="el-icon el-icon-check"></i> File Upload Feature</li>
                            <li><i class="el-icon el-icon-check"></i> SMS Notifications</li>
                            <li><i class="el-icon el-icon-check"></i> Visual Data Reporting</li>
                        </ul>
                        <div style="text-align: center; margin-top: 40px; margin-bottom: 40px;">
                            <a target="_blank" rel="nofollow" class="el-button el-button--primary" href="<?php echo esc_url(fluentform_upgrade_url()); ?>">Upgrade to Pro</a>
                        </div>
                        <?php $addOns = (new \FluentForm\App\Modules\AddOnModule())->getPremiumAddOns(); ?>

                        <h5>Integrations available in Fluent Forms Pro</h5>
                        <ul class="ff_addons">
                            <?php foreach ($addOns as $addOn): ?>
                                <li><img title="<?php echo esc_attr($addOn['title']); ?>" src="<?php echo esc_attr($addOn['logo']); ?>" /></li>
                            <?php endforeach; ?>
                        </ul>

                        <div style="text-align: center; margin-top: 40px; margin-bottom: 30px;">
                            <a target="_blank" rel="nofollow" class="el-button el-button--primary" href="<?php echo esc_url(fluentform_upgrade_url()); ?>">Upgrade to Pro</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="ff_notice">
    <div class="ff_notice_inner">
        <i class="el-icon el-icon-warning"></i>
        <p>You are seeing preview version of Fluent Forms. This form is only accessible for Admin users. Other users
        may not access this page. To use this for in a page please use the following shortcode:
        <strong>[fluentform id='<?php echo intval($form_id) ?>']</strong>
        </p>
    </div>
</div>
<?php
wp_footer();
?>
</body>
</html>
