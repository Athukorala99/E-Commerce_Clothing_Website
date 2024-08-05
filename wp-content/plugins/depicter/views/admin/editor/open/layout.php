<?php
/**
 * Editor open layout.
 *
 * @package Depicter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $wp_version;

$body_classes = [
	'depicter-editor-active',
	'wp-version-' . str_replace( '.', '-', $wp_version ),
];

if ( is_rtl() ) {
	$body_classes[] = 'rtl';
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php wp_title(); ?></title>
	<?php wp_head(); ?>
	<script>var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';</script>
</head>
<body class="<?php echo \Averta\WordPress\Utility\Sanitize::attribute( implode( ' ', $body_classes ) ); ?>">

<?php
 	\Depicter::layoutContent();
	wp_footer();
	do_action( 'admin_print_footer_scripts' );
?>
</body>
</html>
