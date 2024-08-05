<?php

blocksy_add_early_inline_style_in_gutenberg(function () {
	$m = new \Blocksy\FontsManager();
	$maybe_google_fonts_url = $m->load_editor_fonts();

	if (! empty($maybe_google_fonts_url)) {
		return "@import url('" . $maybe_google_fonts_url . "');\n";
	}

	return '';
});

add_action(
	'enqueue_block_editor_assets',
	function () {

		if (get_current_screen()->base === 'widgets') {
			return;
		}

		$theme = blocksy_get_wp_parent_theme();
		global $post;

		$options = blocksy_get_options('meta/' . get_post_type($post));

		if (
			$post
			&&
			intval(get_option('page_for_posts')) === intval($post->ID)
		) {
			$options = blocksy_get_options('meta/blog');
		}

		if (
			$post
			&&
			intval(get_option('woocommerce_shop_page_id')) === $post->ID
		) {
			$options = blocksy_get_options('meta/blog');
		}

		if (blocksy_manager()->post_types->is_supported_post_type()) {
			$options = blocksy_get_options('meta/default', [
				'post_type' => get_post_type_object(get_post_type($post))
			]);
		}

		$options = apply_filters(
			'blocksy:editor:post_meta_options',
			$options,
			get_post_type($post)
		);

		wp_enqueue_style(
			'ct-main-editor-styles',
			get_template_directory_uri() . '/static/bundle/editor.min.css',
			[],
			$theme->get('Version')
		);

		if (get_current_screen()->base === 'post') {
			wp_enqueue_style(
				'ct-main-editor-iframe-styles',
				get_template_directory_uri() . '/static/bundle/editor-iframe.min.css',
				[],
				$theme->get('Version')
			);

			wp_add_inline_style(
				'ct-main-editor-styles',
				blocksy_manager()->dynamic_css->load_backend_dynamic_css([
					'echo' => false,
					'filename' => 'admin/editor-top-level'
				])
			);
		}

		if (is_rtl()) {
			wp_enqueue_style(
				'ct-main-editor-rtl-styles',
				get_template_directory_uri() . '/static/bundle/editor-rtl.min.css',
				['ct-main-editor-styles'],
				$theme->get('Version')
			);
		}

		wp_enqueue_script(
			'ct-main-editor-scripts',
			get_template_directory_uri() . '/static/bundle/editor.js',
			['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-hooks', 'ct-options-scripts'],
			$theme->get('Version'),
			true
		);

		$post_type = get_current_screen()->post_type;
		$maybe_cpt = blocksy_manager()
			->post_types
			->is_supported_post_type();

		if ($maybe_cpt) {
			$post_type = $maybe_cpt;
		}

		$prefix = blocksy_manager()->screen->get_admin_prefix($post_type);

		$page_structure = blocksy_get_theme_mod(
			$prefix . '_structure',
			($prefix === 'single_blog_post') ? 'type-3' : 'type-4'
		);

		$background_source = blocksy_get_theme_mod(
			$prefix . '_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword()
					],
				],
			])
		);

		if (
			isset($background_source['background_type'])
			&&
			$background_source['background_type'] === 'color'
			&&
			isset($background_source['backgroundColor']['default']['color'])
			&&
			$background_source['backgroundColor']['default']['color'] === Blocksy_Css_Injector::get_skip_rule_keyword()
		) {
			$background_source = blocksy_get_theme_mod(
				'site_background',
				blocksy_background_default_value([
					'backgroundColor' => [
						'default' => [
							'color' => '#f8f9fb'
						],
					],
				])
			);
		}

		$localize = [
			'post_options' => $options,
			'default_page_structure' => $page_structure,

			'default_background' => $background_source,
			'default_content_style' => blocksy_get_theme_mod(
				$prefix . '_content_style',
				blocksy_get_content_style_default($prefix)
			),

			'default_content_background' => blocksy_get_theme_mod(
				$prefix . '_content_background',
				blocksy_background_default_value([
					'backgroundColor' => [
						'default' => [
							'color' => '#ffffff'
						],
					],
				])
			),

			'default_boxed_content_spacing' => blocksy_get_theme_mod(
				$prefix . '_boxed_content_spacing',
				[
					'desktop' => blocksy_spacing_value([
						'top' => '40px',
						'left' => '40px',
						'right' => '40px',
						'bottom' => '40px',
					]),
					'tablet' => blocksy_spacing_value([
						'top' => '35px',
						'left' => '35px',
						'right' => '35px',
						'bottom' => '35px',
					]),
					'mobile'=> blocksy_spacing_value([
						'top' => '20px',
						'left' => '20px',
						'right' => '20px',
						'bottom' => '20px',
					]),
				]
			),

			'default_content_boxed_radius' => blocksy_get_theme_mod(
				$prefix . '_content_boxed_radius',
				blocksy_spacing_value([
					'top' => '3px',
					'left' => '3px',
					'right' => '3px',
					'bottom' => '3px',
				])
			),

			'default_content_boxed_border' => blocksy_get_theme_mod(
				$prefix . '_content_boxed_border',
				[
					'width' => 1,
					'style' => 'none',
					'color' => [
						'color' => 'rgba(44,62,80,0.2)',
					],
				]
			),

			'default_content_boxed_shadow' => blocksy_get_theme_mod(
				$prefix . '_content_boxed_shadow',
				blocksy_box_shadow_value([
					'enable' => true,
					'h_offset' => 0,
					'v_offset' => 12,
					'blur' => 18,
					'spread' => -6,
					'inset' => false,
					'color' => [
						'color' => 'rgba(34, 56, 101, 0.04)',
					],
				])
			),

			'options_panel_svg' => apply_filters(
				'blocksy:editor:options:icon',
				'<svg width="20" height="20" viewBox="0 0 50 50">
					<path d="M31.2,30.2c0,0.9-0.7,1.6-1.6,1.6h-5l-1.3-3.1h6.4C30.5,28.7,31.2,29.4,31.2,30.2z M29.7,19h-6.4l1.3,3.1h5c0.8,0,1.6-0.7,1.6-1.6C31.2,19.7,30.5,19,29.7,19z M50,25c0,13.8-11.2,25-25,25C11.2,50,0,38.8,0,25C0,11.2,11.2,0,25,0C38.8,0,50,11.2,50,25z M36.1,25.4c1-1.4,1.6-3,1.6-4.9c0-1.8-0.6-3.4-1.6-4.8c-1.4-2-3.7-3.3-6.4-3.4c-0.1,0-0.1,0-0.2,0v0H14.3c-0.4,0-0.7,0.4-0.5,0.8l3.7,8.9h-3.2c-0.4,0-0.7,0.4-0.5,0.8l6.4,15.5h9.4c4.5,0,8.1-3.7,8.1-8.1C37.7,28.4,37.2,26.8,36.1,25.4C36.2,25.4,36.2,25.4,36.1,25.4z"/>
				</svg>'
			)
		];

		wp_localize_script(
			'ct-main-editor-scripts',
			'ct_editor_localizations',
			$localize
		);
	},
	5
);

add_filter('tiny_mce_before_init', function ($mceInit) {
	if (! isset($mceInit['content_css'])) {
		return $mceInit;
	}

	$parsed = explode(',', $mceInit['content_css']);

	$result = [];

	foreach ($parsed as $file) {
		if (strpos($file, 'blocksy') !== false) {
			continue;
		}

		$result[] = $file;
	}

	$mceInit['content_css'] = implode(',', $result);

	return $mceInit;
});

add_action(
	'block_editor_settings_all',
	function($settings) {
		$settings['styles'][] = array(
			'css' => blocksy_manager()->dynamic_css->load_backend_dynamic_css([
				'echo' => false
			]),
			'__unstableType' => 'theme',
			'source' => 'blocksy'
		);

		return $settings;
	}
);

