<?php

namespace Blocksy\Blocks;

class BlockWrapper {
	public function __construct() {
		add_action('init', [$this, 'blocksy_block_wrapper_block']);
		add_action('enqueue_block_editor_assets', [$this, 'enqueue_admin']);

		add_filter(
			'block_categories_all',
			function( $categories ) {
				$widgets_category = array_search('widgets', array_column($categories, 'slug'));

				return array_merge(
					array_slice($categories, 0, $widgets_category),
					[
						[
							'slug'  => 'blocksy-blocks',
							'title' => 'Blocksy'
						]
					],
					array_slice($categories, $widgets_category)
				);
			}
		);
	}

	public function blocksy_block_wrapper_block() {
		call_user_func(
			'register_' . 'block_type',
			'blocksy/widgets-wrapper',
			[
				'api_version' => 3,
				'render_callback' => function ($attributes, $content) {
					if (strpos($content, 'class="ct-') === false) {
						return '';
					}

					$class = ['ct-block-wrapper'];

					if (isset($attributes['className'])) {
						$class[] = $attributes['className'];
					}

					$attributes = wp_parse_args(
						$attributes,
						[
							'style' => []
						]
					);

					$wp_styles = wp_style_engine_get_styles(
						$attributes['style']
					);

					$wp_styles_css = isset($wp_styles['css']) ? $wp_styles['css'] : '';

					return blocksy_html_tag(
						'div',
						array_merge(
							[
								'class' => implode(' ', $class)
							],
							(! empty($wp_styles_css) ? [
								'style' => $wp_styles_css,
							] : [])
						),
						$content
					);
				},
			]
		);
	}

	public function enqueue_admin() {
		wp_enqueue_script(
			'blocksy/widgets-wrapper',
			get_template_directory_uri() . '/static/bundle/blocks/widgets-wrapper.js',
			['wp-blocks', 'wp-element', 'wp-block-editor']
		);
	}
}
