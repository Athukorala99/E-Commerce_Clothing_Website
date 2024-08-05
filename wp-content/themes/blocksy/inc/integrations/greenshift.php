<?php

add_filter( 'greenshift_hide_landscape_breakpoint', '__return_true' );

add_filter('greenshift_responsive_breakpoints', function($array){
	return array(
		'mobile'  => 690,
		'tablet'  => 690,
		'desktop' => 1000
	);
});