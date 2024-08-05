<?php

namespace Blocksy\Blocks;

class BreadCrumbs {
	public function __construct() {
		add_action('init', [$this, 'blocksy_breadcrumbs_block']);
	}

	public function blocksy_breadcrumbs_block() {
		call_user_func(
			'register_' . 'block_type',
			'blocksy/breadcrumbs',
			[
				'api_version' => 3,
				'render_callback' => function ($attributes, $content) {
					$attributes = wp_parse_args(
						$attributes,
						[
							'className' => '',
							'style' => []
						]
					);

					$colors = isset($attributes['style']['color']) ? $attributes['style']['color'] : [];

					if (isset($attributes['linkColor'])) {
						$var = $attributes['linkColor'];
						$colors['--theme-link-initial-color'] = "var(--wp--preset--color--$var)";
					}

					if (isset($attributes['customLinkColor'])) {
						$colors['--theme-link-initial-color'] = $attributes['customLinkColor'];
					}

					if (isset($attributes['textColor'])) {
						$var = $attributes['textColor'];
						$colors['--theme-text-color'] = "var(--wp--preset--color--$var)";
					}

					if (isset($attributes['customTextColor'])) {
						$colors['--theme-text-color'] = $attributes['customTextColor'];
					}

					if (isset($attributes['linkHoverColor'])) {
						$var = $attributes['linkHoverColor'];
						$colors['--theme-link-hover-color'] = "var(--wp--preset--color--$var)";
					}

					if (isset($attributes['customLinkHoverColor'])) {
						$colors['--theme-link-hover-color'] = $attributes['customLinkHoverColor'];
					}

					$colors_css = '';

					foreach ($colors as $key => $value) {
						if (empty($value)) {
							continue;
						}

						$colors_css .= $key . ':' . $value . ';';
					}

					$breadcrumbs_builder = new \Blocksy\BreadcrumbsBuilder();

					$wp_styles = wp_style_engine_get_styles(
						$attributes['style']
					);

					$wp_styles_css = isset($wp_styles['css']) ? $wp_styles['css'] : '';

					return $breadcrumbs_builder->render(
						array_merge(
							[
								'class' => $attributes['className'],
							],
							! empty($wp_styles_css) || ! empty($colors_css) ? [
								'style' => $wp_styles_css . $colors_css
							] : []
						)
					);
				},
			]);
	}
}
