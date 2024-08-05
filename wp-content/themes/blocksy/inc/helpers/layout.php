<?php

function blocksy_normalize_layout($render_layout = [], $default_render_layout = []) {
	$render_ids = array_column($render_layout, 'id');

	foreach ($default_render_layout as $item) {
		if (! in_array($item['id'], $render_ids)) {
			$render_layout[] = $item;
		}
	}

	return $render_layout;
}
