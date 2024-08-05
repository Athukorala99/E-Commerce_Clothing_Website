<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="Imagetoolbar" content="No"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html($meta['title']); ?></title>
    <meta name="description" content="<?php echo strip_tags($meta['description']) ?>">

    <?php if (!empty($meta['featured_image'])): ?>
        <meta property="og:image" content="<?php echo esc_url($meta['featured_image']); ?>"/>
    <?php endif; ?>

    <meta property="og:title" content="<?php echo esc_html($meta['title']); ?>"/>

    <meta property="og:description" content="<?php echo strip_tags($meta['description']) ?>"/>

    <?php if (!empty($meta['google_font_href'])): ?>

        <link rel="preconnect" href="https://fonts.gstatic.com/">
        <link id="ffc_google_font" href='<?php echo $meta['google_font_href']; ?>' rel="stylesheet" type="text/css">
    <?php endif; ?>

    <?php wp_site_icon(); ?>

    <style type="text/css">
        body {
            height: 100%;
            width: 100%;
            overflow: auto;
            margin: 0;
            padding: 0;
            max-width: 100vw;
            font-family: sans-serif;
        }

        a, abbr, acronym, address, applet, article, aside, audio, b, big, blockquote, body, canvas, caption, center, cite, code, dd, del, details, dfn, div, dl, dt, em, embed, fieldset, figcaption, figure, footer, form, h1, h2, h3, h4, h5, h6, header, hgroup, html, i, iframe, img, ins, kbd, label, legend, li, mark, menu, nav, object, ol, output, p, pre, q, ruby, s, samp, section, small, span, strike, strong, sub, summary, sup, table, tbody, td, tfoot, th, thead, time, tr, tt, u, ul, var, video {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline
        }

        article, aside, details, figcaption, figure, footer, header, hgroup, menu, nav, section {
            display: block
        }

        body {
            line-height: 1
        }

        body * {
            box-sizing: border-box
        }

        ol, ul {
            list-style: none
        }

        blockquote, q {
            quotes: none
        }

        blockquote:after, blockquote:before, q:after, q:before {
            content: '';
            content: none
        }

        .ff_conv_app {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
        }

        .ff_conv_app .ffc_power {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
        }
    </style>

    <?php
        do_action_deprecated(
            'fluentform_conversational_frame_head',
            [
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/conversational_frame_head',
            'Use fluentform/conversational_frame_head instead of fluentform_conversational_frame_head.'
        );
        do_action('fluentform/conversational_frame_head');
    ?>
    <style id="ffc_generated_css" type="text/css">
        <?php echo $generated_css; ?>
    </style>

    <style id="ffc_font_css" type="text/css">
        <?php echo $meta['font_css']; ?>
    </style>

    <style type="text/css">
        <?php echo $submit_css; ?>
        .ffc_loading_screen {
            height: 100vh;
            width: 100%;
            display: flex;
            flex-wrap: nowrap;
            align-content: center;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .ffc_loading_screen h2 {
            font-size: 30px;
            margin-top: 20px;
        }
    </style>

    <?php foreach ($form->image_preloads as $imgSrc): ?>
        <link rel="preload" href="<?php echo esc_url($imgSrc); ?>" as="image">
    <?php endforeach; ?>

</head>
<body class="ff_conversation_page_body  ff_conversation_page_<?php echo $form_id; ?>">
<div class="ffc_conv_wrapper">
    <div class="frm-fluent-form ff_conv_app ff_conv_app_frame fluent_form_<?php echo $form_id; ?> ff_conv_app_<?php echo $form_id; ?> ffc_media_hide_mob_<?php echo $design['hide_media_on_mobile']; ?>" data-form_id="<?php echo $form_id ?>">
        <div data-var_name="fluent_forms_global_var" class="ffc_conv_form" style="width: 100%" id="ffc_app_landing">
            <div class="ffc_loading_screen">
                <h2><?php _e('Loading...', 'fluentform'); ?></h2>
            </div>
        </div>
    </div>
</div>
<?php
do_action_deprecated(
    'fluentform_conversational_frame_footer',
    [],
    FLUENTFORM_FRAMEWORK_UPGRADE,
    'fluentform/conversational_frame_footer',
    'Use fluentform/conversational_frame_footer instead of fluentform_conversational_frame_footer.'
);
do_action('fluentform/conversational_frame_footer');
?>
</body>
</html>

