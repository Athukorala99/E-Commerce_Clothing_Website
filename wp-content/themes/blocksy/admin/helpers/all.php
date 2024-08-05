<?php

require get_template_directory() . '/admin/helpers/options.php';
require get_template_directory() . '/admin/helpers/meta-boxes.php';
require get_template_directory() . '/admin/helpers/options-logic.php';
require get_template_directory() . '/admin/helpers/inline-svgs.php';

// Temporary work-around until this issue is fixed:
// https://github.com/WordPress/gutenberg/issues/53509
function blocksy_add_early_inline_style_in_gutenberg($cb) {
	add_action(
		'block_editor_settings_all',
		function ($settings) use ($cb) {
			$css = $cb();

			if (empty($css)) {
				return $settings;
			}

			$settings['__unstableResolvedAssets']['styles'] .= blocksy_html_tag(
				'style',
				[],
				$cb()
			);

			return $settings;
		}
	);

	add_action(
		'admin_print_styles',
		function () use ($cb) {
			if (
				! get_current_screen()
				||
				! get_current_screen()->is_block_editor
			) {
				return;
			}

			$css = $cb();

			if (empty($css)) {
				return;
			}

			echo blocksy_html_tag('style', [], $css);
		},
		25
	);
}
