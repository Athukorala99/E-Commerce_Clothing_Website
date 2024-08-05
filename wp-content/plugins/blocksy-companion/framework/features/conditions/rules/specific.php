<?php

$options = [];

$specific_ids = [];

if ($filter === 'all' || $filter === 'singular') {
	$specific_ids[] = [
		'id' => 'post_ids',
		'title' => __('Post ID', 'blocksy-companion')
	];

	$specific_ids[] = [
		'id' => 'page_ids',
		'title' => __('Page ID', 'blocksy-companion')
	];

	$specific_ids[] = [
		'id' => 'custom_post_type_ids',
		'title' => __('Custom Post Type ID', 'blocksy-companion')
	];

	$specific_ids[] = [
		'id' => 'post_with_taxonomy_ids',
		'title' => __('Post with Taxonomy ID', 'blocksy-companion'),
		'post_type' => 'product'
	];

	$specific_ids[] = [
		'id' => 'user_post_author_id',
		'title' => __('Post with Author ID', 'blocksy-companion')
	];
}

if ($filter === 'all' || $filter === 'archive') {
	$specific_ids[] = [
		'id' => 'taxonomy_ids',
		'title' => __('Taxonomy ID', 'blocksy-companion')
	];
}

if ($filter === 'product_tabs') {
	$specific_ids = [];
}

if (! empty($specific_ids)) {
	$options[] = [
		'title' => __('Specific', 'blocksy-companion'),
		'rules' => $specific_ids
	];
}

