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
<div class="ff_addon_wrapper ff_block">
    <div class="ff_card text-center mb-4">
        <img class="mb-6" style="width: 100px; height: 100px;" src="<?php echo esc_url($logo); ?>"/>
        <h1 class="mb-4">Making Email Deliverability Easy and Fast for Your WordPress</h1>
        <p>
            <a target="_blank" href="https://wordpress.org/plugins/fluent-smtp/" rel="nofollow">FluentSMTP</a> is the ultimate WP Mail Plugin that connects with your Email Service Provider natively and makes
            sure your emails are delivered 💯.
            Our goal is to send your WordPress emails delivery fast, secure, and make sure your WordPress emails reach
            the email inbox.
            Made by Fluent Forms team for you.
        </p>
        <?php if (!$is_installed): ?>
            <div class="ff_addon_btn_wrapper mb-6 mt-6">
                <button class="ff_addon_btn intstall_fluentsmtp">Install and Activate FluentSMTP Plugin</button>
                <p style="display: none;" class="ff_addon_installing">Installing FluentSMTP Plugin. Please wait...</p>
            </div>
        <?php else: ?>
            <div class="ff_alert ff_alert_s2 success-soft mt-5">
                <h3 class="mb-4">FluentSMTP plugin has been activated and running.</h3>
                <a href="<?php echo esc_url($setup_url); ?>" class="el-button el-button--large el-button--success">View FluentSMTP Settings</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$is_installed): ?>
        <div class="ff_card">
            <img class="ff_addon_banner mb-5" src="<?php echo esc_url($banner_image); ?>"/>
            <div class="ff_card_head">
                <h4 class="title">FluentSMTP - The ultimate SMTP & Email Service Connection Plugin for WordPress</h4>
                <p class="text">
                    For FluentCRM users, we built a well-optimized SMTP/Amazon SES plugin. It will help you manage all your
                    WordPress website emails, including FluentCRM emails. 
                    <a target="_blank" rel="noopener" href="https://fluentsmtp.com/">Learn more about FluentSMTP Plugin</a>.
                </p>
            </div>
            <div class="el-row mt-5" style="margin-left: -12px; margin-right: -12px;">
                <div class="el-col el-col-12" style="padding-left: 12px; padding-right: 12px;">
                    <h5 class="mb-4">Dedicated API and SMTP connections</h5>
                    <ul class="fs-15">
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> Amazon SES</li>
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> Mailgun</li>
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> SendGrid</li>
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> SendInBlue</li>
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> PepiPost</li>
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> SparkPost</li>
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> + Any SMTP Provider</li>
                    </ul>
                </div>
                <div class="el-col el-col-12" style="padding-left: 12px; padding-right: 12px;">
                    <h5 class="mb-4">Features of Fluent SMTP Plugin</h5>
                    <ul class="fs-15">
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> Optimized API connection with Mail Service Providers</li>
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> Email Logging for better visibility</li>
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> Email Routing based on the sender email address</li>
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> Real-Time Email Delivery</li>
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> Resend Any Emails</li>
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> In Details Reporting</li>
                        <li class="mb-3"><i class="el-icon el-icon-success text-success mr-2"></i> Super fast UI powered by VueJS</li>
                    </ul>
                </div>
            </div><!-- .el-row -->
            <div class="ff_addon_footer text-center mt-6 mb-4">
                <button class="ff_addon_btn ff_invert intstall_fluentsmtp">Install and Activate FluentSMTP Plugin</button>
                <p style="display: none;" class="ff_addon_installing">Installing FluentSMTP Plugin. Please wait...</p>
            </div>
        </div>
    <?php endif; ?>
</div>
