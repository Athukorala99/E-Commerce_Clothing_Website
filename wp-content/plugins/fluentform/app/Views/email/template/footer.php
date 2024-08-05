<?php
/**
 * Email Footer
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$poweredBy = apply_filters_deprecated(
    'fluentform_email_template_footer_credit',
    [
        'Powered by <a href="https://wordpress.org/plugins/fluentform/">FluentForm</a>.',
        $form,
        $notification
    ],
    FLUENTFORM_FRAMEWORK_UPGRADE,
    'fluentform/email_template_footer_credit',
    'Use fluentform/email_template_footer_credit instead of fluentform_email_template_footer_credit.'
);
$poweredBy = apply_filters('fluentform/email_template_footer_credit',$poweredBy, $form, $notification);
if(defined('FLUENTFORMPRO')) {
    $poweredBy = '';
}
?>
</div></td></tr></table></td></tr></table></td></tr></table>
<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer"><tr><td valign="top"><table border="0" cellpadding="10" cellspacing="0" width="100%"><tr><td class="fluent_credit" colspan="2" valign="middle" id="credit">
<span><?php echo wp_kses_post($footerText); ?> <?php echo wp_kses_post($poweredBy); ?></span>
<?php
    do_action_deprecated(
        'fluentform_email_template_after_footer',
        [
            $form,
            $notification
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/email_template_after_footer',
        'Use fluentform/email_template_after_footer instead of fluentform_email_template_after_footer.'
    );
    do_action( 'fluentform/email_template_after_footer', $form, $notification );
?>
</td></tr></table></td></tr></table></td></tr></table></div></body></html>
