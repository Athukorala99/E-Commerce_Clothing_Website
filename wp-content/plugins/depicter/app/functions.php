<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Retrieves or prints slider markup
 *
 * @param int|string  $documentID  Slider ID
 * @param array       $args        Slider params
 *
 * @return string|void
 * @throws Exception
 */
function depicter( $documentID = 0, $args = [] ) {
	// return markup if 'echo' arg was set to false
	if( isset( $args['echo'] ) && ! $args['echo'] ) {
		return \Depicter::front()->render()->document( $documentID, $args );
	}
	\Depicter::front()->render()->document( $documentID, $args );
}