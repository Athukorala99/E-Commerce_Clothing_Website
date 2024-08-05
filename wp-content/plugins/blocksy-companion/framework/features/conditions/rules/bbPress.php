<?php

$options = [];

if (
	function_exists('bbp_is_single_user_profile')
	&&
	$filter === 'all'
) {
	$options[] = [
		'title' => __('bbPress', 'blocksy-companion'),
		'rules' => [
			[
				'id' => 'bbpress_profile',
				'title' => __('Profile', 'blocksy-companion')
			]
		]
	];
}

