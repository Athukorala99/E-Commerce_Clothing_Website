<?php

function blocksy_has_css_in_files() {
	return apply_filters('blocksy:dynamic-css:has_files_cache', false);
}

function blocksy_get_all_dynamic_styles_for($args = []) {
	$args = wp_parse_args(
		$args,
		[
			'context' => null,
			'fonts_manager' => null
		]
	);

	$css = new Blocksy_Css_Injector([
		'fonts_manager' => $args['fonts_manager']
	]);
	$mobile_css = new Blocksy_Css_Injector([
		'fonts_manager' => $args['fonts_manager']
	]);
	$tablet_css = new Blocksy_Css_Injector([
		'fonts_manager' => $args['fonts_manager']
	]);

	blocksy_theme_get_dynamic_styles([
		'name' => 'global',
		'css' => $css,
		'mobile_css' => $mobile_css,
		'tablet_css' => $tablet_css,
		'context' => $args['context'],
		'chunk' => 'global',
		'forced_call' => true
	]);

	do_action(
		'blocksy:global-dynamic-css:enqueue',
		[
			'context' => $args['context'],
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css
		]
	);

	return [
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css
	];
}

function blocksy_get_dynamic_css_file_content($args = []) {
	$args = wp_parse_args(
		$args,
		[
			'context' => null,
		]
	);

	$css_output = blocksy_get_all_dynamic_styles_for([
		'context' => $args['context']
	]);

	$css = $css_output['css'];
	$tablet_css = $css_output['tablet_css'];
	$mobile_css = $css_output['mobile_css'];

	// $content = "/* Desktop CSS */";
	$content = '';
	$content .= trim($css->build_css_structure());

	// $content .= "\n\n/* Tablet CSS */\n";
	$content .= "@media (max-width: 999.98px) {";
	$content .= "  " . trim($tablet_css->build_css_structure());
	$content .= "}";

	// $content .= "\n\n/* Mobile CSS */\n";
	$content .= "@media (max-width: 689.98px) {";
	$content .= trim($mobile_css->build_css_structure());
	$content .= "}";

	return $content;
}

function blocksy_dynamic_styles_should_call($args = []) {
	$args = wp_parse_args(
		$args,
		[
			'context' => null,
			'chunk' => null,
			'forced_call' => false
		]
	);

	if (! $args['context']) {
		throw new Error('$context not provided. This is required!');
	}

	if (! $args['chunk']) {
		throw new Error('$chunk not provided. This is required!');
	}

	if (!$args['forced_call'] && blocksy_has_css_in_files()) {
		if ($args['context'] === 'inline') {
			if ($args['chunk'] === 'global' || $args['chunk'] === 'woocommerce') {
				return false;
			}
		}

		if ($args['context'] === 'files:global') {
			if ($args['chunk'] === 'woocommerce') {
				if (! class_exists('WooCommerce')) {
					return false;
				}
			} else {
				if ($args['chunk'] !== 'global') {
					return false;
				}
			}
		}
	}

	return true;
}

/**
 * Evaluate a file with dynamic styles.
 *
 * @param string $name Name of dynamic CSS file.
 * @param array $variables list of data to pass in file.
 * @throws Error When $css not provided.
 */
function blocksy_theme_get_dynamic_styles($args = []) {
	$args = wp_parse_args(
		$args,
		[
			'path' => null,
			'name' => '',
			'css' => null,

			'context' => null,
			'chunk' => null,
			'forced_call' => false,
			'prefixes' => null
		]
	);

	if (! isset($args['css'])) {
		throw new Error('$css instance not provided. This is required!');
	}

	if (! blocksy_dynamic_styles_should_call($args)) {
		return;
	}

	if (! $args['path']) {
		$args['path'] = get_template_directory() . '/inc/dynamic-styles/' . $args['name'] . '.php';
	}

	if (! $args['prefixes']) {
		blocksy_get_variables_from_file($args['path'], [], $args);
	} else {
		foreach ($args['prefixes'] as $prefix) {
			blocksy_get_variables_from_file(
				$args['path'],
				[],
				array_merge($args, [
					'prefix' => $prefix
				])
			);
		}
	}
}

