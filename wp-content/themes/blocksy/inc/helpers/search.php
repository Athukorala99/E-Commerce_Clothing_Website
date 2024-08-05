<?php

function blocksy_isolated_get_search_form($args) {
	if (class_exists('IS_Admin_Public')) {
		remove_filter(
			'get_search_form',
			[\IS_Admin_Public::getInstance(), 'get_search_form'],
			9999999
		);
	}

	get_search_form($args);

	if (class_exists('IS_Admin_Public')) {
		add_filter(
			'get_search_form',
			[\IS_Admin_Public::getInstance(), 'get_search_form'],
			9999999
		);
	}
}

function blocksy_reqursive_taxonomy($tax, $parent_term_id, $level, $selected_cat) {
	if (! $parent_term_id) {
		return [];
	}

	$terms = get_terms([
		'taxonomy' => $tax,
		'hide_empty' => true,
		'hierarchical' => false,
		'parent' => $parent_term_id,
	]);

	if (!count($terms)) {
		return [];
	}

	$els = [];

	foreach ($terms as $term) {
		$selected_attr = $selected_cat == $term->term_id ? 'selected' : '';

		$prefix = '&nbsp;&nbsp;&nbsp;';

		for ($i=0; $i < $level; $i++) {
			$prefix .= '&nbsp;&nbsp;&nbsp;';
		}

		$els[] = blocksy_html_tag(
			'option',
			[
				'value' => $tax . ':' . $term->term_id,
				$selected_attr => $selected_attr
			],
			$prefix . $term->name
		);

		$children = get_terms([
			'taxonomy' => $tax,
			'hide_empty' => true,
			'hierarchical' => false,
			'parent' => $term->term_id,
		]);

		if (count($children)) {
			$els = array_merge(
				$els,
				blocksy_reqursive_taxonomy(
					$tax,
					$term->term_id,
					$level + 1,
					$selected_cat
				)
			);
		}
	}

	return $els;
}
