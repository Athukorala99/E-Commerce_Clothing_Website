<?php

$pages_rules = [];

if ($filter === 'all' || $filter === 'singular') {
	$pages_rules[] = [
		'id' => 'single_page',
		'title' => __('Single Page', 'blocksy-companion')
	];
}

if ($filter === 'all') {
	$pages_rules[] = [
		'id' => '404',
		'title' => __('404', 'blocksy-companion')
	];
}

if ($filter === 'all' || $filter === 'archive') {
	$pages_rules[] = [
		'id' => 'search',
		'title' => __('Search', 'blocksy-companion')
	];

	$pages_rules[] = [
		'id' => 'blog',
		'title' => __('Blog', 'blocksy-companion')
	];
}

$pages_rules[] = [
	'id' => 'front_page',
	'title' => __('Front Page', 'blocksy-companion')
];

if ($filter === 'all') {
	$pages_rules[] = [
		'id' => 'privacy_policy_page',
		'title' => __('Privacy Policy Page', 'blocksy-companion')
	];
}

if ($filter === 'all' || $filter === 'archive') {
	$pages_rules[] = [
		'id' => 'author',
		'title' => __('Author Archives', 'blocksy-companion')
	];
}

$options = [
	[
		'title' => __('Pages', 'blocksy-companion'),
		'rules' => $pages_rules
	]
];
