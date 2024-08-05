<?php
/**
 * Email Header
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$status = apply_filters_deprecated(
    'fluentform_email_template_email_heading',
    [
        false,
        $form,
        $notification
    ],
    FLUENTFORM_FRAMEWORK_UPGRADE,
    'fluentform/email_template_email_heading',
    'Use fluentform/email_template_email_heading instead of fluentform_email_template_email_heading.'
);
$email_heading = apply_filters('fluentform/email_template_email_heading', $status, $form, $notification);
$hasHeaderImage = apply_filters_deprecated(
    'fluentform_email_template_header_image',
    [
        false,
        $form,
        $notification
    ],
    FLUENTFORM_FRAMEWORK_UPGRADE,
    'fluentform/email_template_header_image',
    'Use fluentform/email_template_header_image instead of fluentform_email_template_header_image.'
);
$headerImage = apply_filters('fluentform/email_template_header_image', $hasHeaderImage, $form, $notification);
$contentType = apply_filters_deprecated(
    'fluentform_email_content_type_header',
    [
        'text/html; charset=UTF-8'
    ],
    FLUENTFORM_FRAMEWORK_UPGRADE,
    'fluentform/email_content_type_header',
    'Use fluentform/email_content_type_header instead of fluentform_email_content_type_header.'
);
$contentType = apply_filters('fluentform/email_content_type_header', $contentType);
?>
<!DOCTYPE html>
<html dir="<?php echo is_rtl() ? 'rtl' : 'ltr'?>">
    <head>
        <meta http-equiv="Content-Type" content="<?php echo esc_attr($contentType); ?>" />
        <meta name="x-apple-disable-message-reformatting" />
        <title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
    </head>
    <body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
        <div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'?>">
            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
                <tr><td align="center" valign="top">
                        <div id="template_header_image">
                            <?php
                                if ( $headerImage ) {
                                    echo '<p style="margin-top:0;"><img src="' . esc_url( $headerImage ) . '" alt="' . get_bloginfo( 'name', 'display' ) . '" /></p>';
                                }
                            ?>
                        </div>
                        <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
                            <?php if ( $email_heading ) { ?>
                                <tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header"><tr><td id="header_wrapper"><h1><?php echo esc_attr($email_heading); ?></h1></td></tr></table></td></tr>
                            <?php } ?>
                            <tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body"><tr><td valign="top" id="body_content"><table border="0" cellpadding="20" cellspacing="0" width="100%"><tr><td valign="top"><div id="body_content_inner">
