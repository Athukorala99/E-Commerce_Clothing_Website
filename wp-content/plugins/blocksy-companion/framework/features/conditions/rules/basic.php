<?php

$everywhere_title = __('Entire Website', 'blocksy-companion');

if ($filter === 'product_tabs') {
	$everywhere_title = __('All Products', 'blocksy-companion');
}

$options = [];

if ($filter !== 'archive' && $filter !== 'singular') {
	$options[] = [
		'title' => '',
		'rules' => [
			[
				'id' => 'everywhere',
				'title' => $everywhere_title
			]
		]
	];
}

$basic_rules = [];

if ($filter === 'all' || $filter === 'singular') {
	$basic_rules[] = [
		'id' => 'singulars',
		'title' => __('All Singulars', 'blocksy-companion')
	];
}

if ($filter === 'all' || $filter === 'archive') {
	$basic_rules[] = [
		'id' => $filter === 'archive' ? 'everywhere' : 'archives',
		'title' => __('All Archives', 'blocksy-companion')
	];
}

if (! empty($basic_rules)) {
	$options[] = [
		'title' => __('Basic', 'blocksy-companion'),
		'rules' => $basic_rules
	];
}

