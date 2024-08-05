<?php

$options = [];

if (
	function_exists('blocksy_get_current_language')
	&&
	blocksy_get_current_language() !== '__NOT_KNOWN__'
) {
	$options[] = [
		'title' => __('Languages', 'blocksy-companion'),
		'rules' => [
			[
				'id' => 'current_language',
				'title' => __('Current Language', 'blocksy-companion')
			]
		]
	];
}

