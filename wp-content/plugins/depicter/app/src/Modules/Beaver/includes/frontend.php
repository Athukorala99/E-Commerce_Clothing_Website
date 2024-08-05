
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

echo \Depicter::front()->render()->document( ltrim( $settings->document_id, '#' ) );
