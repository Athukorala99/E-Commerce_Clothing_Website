<?php

function blocksy_get_taxonomies_for_cpt($post_type, $args = []) {
	$args = wp_parse_args($args, [
		'return_empty' => false
	]);

	if ($post_type === 'post') {
		return [
			'category' => __('Category', 'blocksy'),
			'post_tag' => __('Tag', 'blocksy')
		];
	}

	if ($post_type === 'product') {
		return [
			'product_cat' => __('Category', 'blocksy'),
			'product_tag' => __('Tag', 'blocksy')
		];
	}

	$result = [];

	$taxonomies = array_values(array_diff(
		get_object_taxonomies($post_type),
		['post_format']
	));

	if (count($taxonomies) === 0) {
		if ($args['return_empty']) {
			return [];
		}

		return [
			'default' => __('Default', 'blocksy')
		];
	}

	foreach ($taxonomies as $single_taxonomy) {
		$taxonomy_object = get_taxonomy($single_taxonomy);
		$result[$single_taxonomy] = $taxonomy_object->label;
	}

	return $result;
}
