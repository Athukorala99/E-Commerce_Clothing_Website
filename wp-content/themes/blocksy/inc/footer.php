<?php

if (! function_exists('blocksy_output_drawer_canvas')) {
	function blocksy_output_drawer_canvas($location = 'start') {
		$default_footer_elements = [];

		global $blocksy_has_default_header;

		if ($location === 'start') {
			$elements = new Blocksy_Header_Builder_Elements();

			if (
				isset($blocksy_has_default_header)
				&&
				$blocksy_has_default_header
			) {
				ob_start();
				$elements->render_search_modal();
				$default_footer_elements[] = ob_get_clean();

				$default_footer_elements[] = $elements->render_offcanvas();
			}

			if (blocksy_get_theme_mod('has_back_top', 'no') === 'yes') {
				ob_start();
				blocksy_output_back_to_top_link();
				$default_footer_elements[] = ob_get_clean();
			}
		}

		$footer_elements = apply_filters(
			'blocksy:footer:offcanvas-drawer',
			$default_footer_elements,
			[
				'blocksy_has_default_header' => $blocksy_has_default_header,
				'location' => $location
			]
		);

		if (! empty($footer_elements)) {
			$attr = [
				'class' => 'ct-drawer-canvas',
				'data-location' => $location
			];

			foreach ($footer_elements as $footer_el) {
				$content = $footer_el;

				if (is_array($footer_el) && isset($footer_el['attr'])) {
					$attr = array_merge($attr, $footer_el['attr']);
				}
			}

			echo '<div ' . blocksy_attr_to_html($attr) . '>';

			if ($location === 'end') {
				echo '<div class="ct-drawer-inner">';
			}

			foreach ($footer_elements as $footer_el) {
				$content = $footer_el;

				if (is_array($footer_el) && isset($footer_el['content'])) {
					$content = $footer_el['content'];
				}

				echo $content;
			}

			if ($location === 'end') {
				echo '</div>';
			}

			echo '</div>';
		}
	}
}

add_action('wp_body_open', function () {
	if (! is_admin()) {
		blocksy_output_drawer_canvas('start');
	}
}, 60);

add_action(
	'wp_footer',
	function () {
		blocksy_output_drawer_canvas('end');
	}
);
