<?php

$cpts = [];

$custom_post_types = array_diff(
	get_post_types(['public' => true]),
	[
		'post',
		'page',
		'attachment',
		'documentation',
		'ct_content_block',
		'product'
	]
);

foreach ($custom_post_types as $custom_post_type) {
	$post_type_object = get_post_type_object($custom_post_type);

	if ($filter === 'all' || $filter === 'singular') {
		$cpts[] = [
			'id' => 'post_type_single_' . $custom_post_type,
			'title' => sprintf(
				__('%s Single', 'blocksy-companion'),
				$post_type_object->labels->singular_name
			)
		];
	}

	if ($filter === 'all' || $filter === 'archive') {
		$cpts[] = [
			'id' => 'post_type_archive_' . $custom_post_type,
			'title' => sprintf(
				__('%s Archive', 'blocksy-companion'),
				$post_type_object->labels->singular_name
			)
		];
	}

	$taxonomies = get_object_taxonomies($custom_post_type);

	if ($filter === 'all' || $filter === 'archive') {
		foreach ($taxonomies as $single_taxonomy) {
			$cpts[] = [
				'id' => 'post_type_taxonomy_' . $single_taxonomy,
				'title' => sprintf(
					__('%s %s Taxonomy', 'blocksy-companion'),
					$post_type_object->labels->singular_name,
					get_taxonomy($single_taxonomy)->label
				)
			];
		}
	}
}

$options = [];

if (count($cpts) > 0) {
	$options[] = [
		'title' => __('Custom Post Types', 'blocksy-companion'),
		'rules' => $cpts
	];
}

