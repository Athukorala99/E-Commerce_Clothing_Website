<?php

namespace Blocksy\DbVersioning;

class DefaultValuesCleaner {
	public function clean_header() {
		$header_placements = blocksy_get_theme_mod(
			'header_placements',
			'__empty__'
		);

		if ($header_placements === '__empty__') {
			return;
		}

		$has_changed = false;

		$new_header_placements = $header_placements;

		foreach ($header_placements['sections'] as $section_index => $section) {
			if (empty($section['items'])) {
				continue;
			}

			foreach ($section['items'] as $item_index => $item) {
				if (empty($item['id']) || empty($item['values'])) {
					continue;
				}

				$cleaned_item_payload = $this->cleanup_item($item);

				if (! $cleaned_item_payload['item_changed']) {
					continue;
				}

				$has_changed = true;

				$new_header_placements['sections'][$section_index]['items'][
					$item_index
				]['values'] = $cleaned_item_payload['new_item'];
			}
		}

		if ($has_changed) {
			set_theme_mod('header_placements', $new_header_placements);
		}
	}

	public function clean_footer() {
		$footer_placements = blocksy_get_theme_mod(
			'footer_placements',
			'__empty__'
		);

		if ($footer_placements === '__empty__') {
			return;
		}

		$has_changed = false;

		$new_footer_placements = $footer_placements;

		foreach ($footer_placements['sections'] as $section_index => $section) {
			if (empty($section['items'])) {
				continue;
			}

			foreach ($section['items'] as $item_index => $item) {
				if (empty($item['id']) || empty($item['values'])) {
					continue;
				}

				$cleaned_item_payload = $this->cleanup_item($item, 'footer');

				if (! $cleaned_item_payload['item_changed']) {
					continue;
				}

				$has_changed = true;

				$new_footer_placements['sections'][$section_index]['items'][
					$item_index
				]['values'] = $cleaned_item_payload['new_item'];
			}
		}

		if ($has_changed) {
			set_theme_mod('footer_placements', $new_footer_placements);
		}
	}

	// new_item = []
	// item_changed = false
	public function cleanup_item($item, $panel_type = 'header') {
		$item_changed = false;

		$registered_items = blocksy_manager()->builder->get_registered_items_by(
			$panel_type
		);

		$item_data = null;

		foreach ($registered_items as $registered_item) {
			if ($registered_item['id'] === $item['id']) {
				$item_data = $registered_item;
				break;
			}
		}

		if (! $item_data) {
			return [
				'item_changed' => false
			];
		}

		$options = blocksy_manager()->builder->get_options_for(
			$panel_type,
			$item_data
		);

		$collected = [];

		blocksy_collect_options(
			$collected,
			$options,
			[
				'limit_option_types' => false,
				'limit_level' => 0,
				'include_container_types' => false,
				'info_wrapper' => false,
			]
		);

		$new_item = [];

		foreach ($item['values'] as $item_key => $item_value) {
			if (! isset($collected[$item_key])) {
				continue;
			}

			if ($item_value != $collected[$item_key]['value']) {
				$new_item[$item_key] = $item_value;
				$item_changed = true;
			}
		}

		if (count($new_item) === 0) {
			return [
				'item_changed' => true,
				'new_item' => []
			];
		}

		return [
			'item_changed' => $item_changed,
			'new_item' => $new_item
		];
	}
}
