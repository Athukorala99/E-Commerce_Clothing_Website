<?php

add_filter('post_class', function ($classes) {
	if (function_exists('is_bbpress') && (
		get_post_type() === 'forum'
		||
		get_post_type() === 'topic'
		||
		get_post_type() === 'reply'
	)) {
		$classes[] = 'bbpress';
	}

	return $classes;
});
