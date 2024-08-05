<?php

$options = [];

$posts_options = [];

if ($filter === 'all' || $filter === 'archive') {
	$posts_options[] = [
		'id' => 'all_post_archives',
		'title' => __('All Post Archives', 'blocksy-companion')
	];

	$posts_options[] = [
		'id' => 'post_categories',
		'title' => __('Post Categories', 'blocksy-companion')
	];

	$posts_options[] = [
		'id' => 'post_tags',
		'title' => __('Post Tags', 'blocksy-companion')
	];
}

if ($filter === 'all' || $filter === 'singular') {
	$posts_options[] = [
		'id' => 'single_post',
		'title' => __('Single Post', 'blocksy-companion')
	];

}

$options[] = [
	'title' => __('Posts', 'blocksy-companion'),
	'rules' => $posts_options
];
