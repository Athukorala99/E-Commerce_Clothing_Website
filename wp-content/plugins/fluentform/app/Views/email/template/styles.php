<?php
/**
 * Email Styles
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$colors = array(
    'background_color'      => '#f6f6f6',
    'body_background_color' => '#ffffff',
    'base_color'            => '#444444',
    'text_color'            => '#444444'
);
$settings =  apply_filters('fluentform/email_template_colors', $colors);

// Load colours
$bg              = $settings['background_color'];
$body            = $settings['body_background_color'];
$base            = $settings['base_color'];
$base_text       =  '#202020';
$text            = $settings['text_color'];

$text_lighter_20 = '#555555';

// !important; is a gmail hack to prevent styles being stripped if it doesn't like something.
?>
* {-webkit-text-size-adjust: none;}
body {
background-color: <?php echo esc_attr( $bg ); ?>;
-webkit-text-size-adjust: none;
font-family: sans-serif;
-webkit-font-smoothing: antialiased;
font-size: 14px;
line-height: 1.4;
margin: 0;
padding: 0;
-ms-text-size-adjust: 100%;
-webkit-text-size-adjust: 100%;
width: 100%;
}
#wrapper {
margin: 0;
background-color: <?php echo esc_attr( $bg ); ?>;
padding: 40px 0 0 0;
-webkit-text-size-adjust: none !important;
width: 100%;
}

.button {
background-color:#4CAF50;
border-radius:3px;
color:#ffffff;
display:inline-block;
font-family:sans-serif;
font-size:13px;
font-weight:bold;
line-height: 150%;
text-align:center;
text-decoration:none;
-webkit-text-size-adjust:none;
padding: 8px 20px;
}

.button.sm {
padding: 5px 10px;
}

.button.green {
background-color: #4CAF50;
}

.button.orange {
background-color: #FF9800;
}

.button.blue {
background-color: #2196F3;
}

#template_container {
background-color: <?php echo esc_attr( $body ); ?>;
border: 1px solid #eee;
margin-bottom: 25px;
}

#template_header {
color: #444;
border-bottom: 0;
font-weight: bold;
line-height: 100%;
vertical-align: middle;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

#template_footer td {
padding: 0;
}

#template_footer #credit {
border:0;
color: #999;
font-family: Arial;
font-size:12px;
line-height:125%;
text-align:center;
padding: 0 48px 48px 48px;
}

#body_content {
    background-color: <?php echo esc_attr( $body ); ?>;
}

#body_content table td {
padding: 20px 20px;
}

#body_content table td td {
padding: 12px;
}

#body_content table td th {
padding: 12px;
}

#body_content p {
margin: 0 0 20px;
}

#body_content_inner {
color: <?php echo esc_attr( $text_lighter_20 ); ?>;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: 14px;
line-height: 170%;
text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

.td {
color: <?php echo esc_attr( $text_lighter_20 ); ?>;
border: none;
}

.text {
color: <?php echo esc_attr( $text ); ?>;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

#body_content_inner tr.field-label th {
padding: 6px 12px;
background-color: #f8f8f8;
}

#body_content_inner tr.field-value td {
padding: 6px 12px 12px 12px;
white-space: pre-line;
}

.link {
color: <?php echo esc_attr( $base ); ?>;
}

#header_wrapper {
padding: 36px 48px 0;
display: block;
}

h1 {
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: 30px;
font-weight: 300;
line-height: 150%;
margin: 0;
text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
-webkit-font-smoothing: antialiased;
}

h2 {
color: <?php echo esc_attr( $base ); ?>;
display: block;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: 18px;
font-weight: bold;
line-height: 130%;
margin: .5em 0;
text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h3 {
color: <?php echo esc_attr( $base ); ?>;
display: block;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: 16px;
font-weight: bold;
line-height: 130%;
margin: .5em 0;
text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

#body_content_inner h1 {
margin: 0 0 .5em 0;
}

#body_content_inner p + h1,
#body_content_inner p + h2,
#body_content_inner p + h3,
#body_content_inner p + h4 {
margin-top: 2em;
}

a {
color: <?php echo esc_attr( $base ); ?>;
font-weight: normal;
text-decoration: underline;
}

img {
border: none;
display: inline;
font-size: 14px;
font-weight: bold;
height: auto;
line-height: 100%;
outline: none;
text-decoration: none;
text-transform: capitalize;
}
.fluent_credit span a {
text-decoration: none;
}
.aligncenter {
text-align: center !important;
}
.alignright {
text-align: right !important;
}

img.aligncenter {
text-align: center !important;
display: block !important;
margin: 0 auto !important;
}

/* CSS styles for iOS devices - compensates for overly small font size on ios devices */
@media screen and (max-device-width: 768px) and (-webkit-min-device-pixel-ratio: 2) {
*{font-size:28px;}
*{line-height:1.3em;}
}
