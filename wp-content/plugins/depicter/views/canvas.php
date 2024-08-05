<?php
/**
 * Blank canvas.
 *
 * @package Depicter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// $view_args is required to be passed

$view_defaults = [
	'title'   => 'Depicter',
	'head'    => '',
	'body_classes' => [],
	'content' => '',
	'footer'  => ''
];

$view_args = array_merge( $view_defaults, $view_args );

global $wp_version;
extract( $view_args );

$body_classes[] = 'depicter-canvas';
$body_classes[] = 'wp-version-' . str_replace( '.', '-', $wp_version );

if ( is_rtl() ) {
	$body_classes[] = 'rtl';
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo esc_html( $title ); ?></title>
	<?php echo \Depicter\Utility\Sanitize::html( $head ); ?>
	<script>var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';</script>
</head>
<body class="<?php echo Depicter\Utility\Sanitize::attribute( implode( ' ', $body_classes ) ); ?>">
<?php echo \Depicter\Utility\Sanitize::html( $content, null, 'depicter/output' ); ?>
<?php echo \Depicter\Utility\Sanitize::html( $footer ); ?>
</body>
</html>
