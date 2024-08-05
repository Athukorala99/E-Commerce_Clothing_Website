<?php

namespace Blocksy\Blocks;

class Query {
	public function __construct() {
		add_action('wp_ajax_blocksy_get_posts_block_data', function () {
			if (! current_user_can('edit_posts')) {
				wp_send_json_error();
			}

			$body = json_decode(file_get_contents('php://input'), true);

			if (! isset($body['attributes'])) {
				wp_send_json_error();
			}

			$all_post_types = [
				'post' => __('Posts', 'blocksy')
			];

			$post_types = blocksy_manager()->post_types->get_supported_post_types();

			foreach ($post_types as $single_post_type) {
				$post_type_object = get_post_type_object($single_post_type);

				if (! $post_type_object) {
					continue;
				}

				$all_post_types[
					$single_post_type
				] = $post_type_object->labels->singular_name;
			}

			if (! isset($body['previewedPostId'])) {
				$body['previewedPostId'] = 0;
			}

			$context = [
				'post_id' => $body['previewedPostId']
			];

			$query = $this->get_query_for(
				$body['attributes'],
				$context
			);
			$prefix = $this->get_prefix_for($body['attributes']);

			wp_send_json_success([
				'taxonomies' => blocksy_get_taxonomies_for_cpt(
					get_post_type($body['previewedPostId']),
					['return_empty' => true]
				),
				'all_posts' => $query->get_posts(),
				'post_types' => $all_post_types,
				'block' => $this->render_block($body['attributes'], $context),
				'dynamic_styles' => $this->get_dynamic_styles_for(
					$body['attributes']
				),
				'pagination_output' => blocksy_display_posts_pagination([
					'query' => $query,
					'prefix' => $prefix
				])
			]);
		});

		add_action('wp_ajax_blocksy_get_posts_block_patterns', function () {
			if (! current_user_can('manage_options')) {
				wp_send_json_error();
			}

			$all_patterns = \WP_Block_Patterns_Registry::get_instance()
				->get_all_registered();

			$result = [];

			foreach ($all_patterns as $single_pattern) {
				if (
					isset($single_pattern['blockTypes'])
					&&
					is_array($single_pattern['blockTypes'])
					&&
					in_array(
						'blocksy/query',
						$single_pattern['blockTypes']
					)
				) {
					$result[] = $single_pattern;
				}
			}

			wp_send_json_success([
				'patterns' => $result
			]);
		});

		call_user_func(
			'register_' . 'block_type',
			get_template_directory() . '/static/js/editor/blocks/query/block.json',
			[
				'render_callback' => function ($attributes, $content, $block) {
					$prefix = $this->get_prefix_for($attributes);

					if (
						empty($block->inner_blocks)
						&&
						isset($attributes['design'])
						&&
						$attributes['design'] === 'default'
					) {
						$content = $this->render_block($attributes);

						$wrapper_attr = [];

						$border_result = get_block_core_post_featured_image_border_attributes(
							$attributes
						);

						if (! empty($border_result['class'])) {
							$wrapper_attr['class'] = $border_result['class'];
						}

						if (! empty($border_result['style'])) {
							$wrapper_attr['style'] = $border_result['style'];
						}

						$wrapper_attributes = get_block_wrapper_attributes($wrapper_attr);

						if (! empty($content)) {
							return blocksy_html_tag(
								'div',
								$wrapper_attributes,
								$this->render_block($attributes)
							);
						}

						return '';
					}

					return $content;
				}
			]
		);

		call_user_func(
			'register_' . 'block_type',
			get_template_directory() . '/static/js/editor/blocks/post-template/block.json',
			[
				'render_callback' => function ($attributes, $content, $block) {
					$query = $this->get_query_for($block->context);

					if (! $query->have_posts()) {
						return '';
					}

					$classnames = 'entries';

					if (isset($attributes['style']['elements']['link']['color']['text'])) {
						$classnames .= ' has-link-color';
					}

					// Ensure backwards compatibility by flagging the number of columns via classname when using grid layout.
					if (
						isset($attributes['layout']['type'])
						&&
						'grid' === $attributes['layout']['type']
						&&
						! empty($attributes['layout']['columnCount'])
					) {
						$classnames .= ' ' . sanitize_title(
							'columns-' . $attributes['layout']['columnCount']
						);
					}

					$wrapper_attributes = get_block_wrapper_attributes([
						'class' => trim($classnames)
					]);

					$content = '';

					while ($query->have_posts()) {
						$query->the_post();

						$block_instance = $block->parsed_block;

						// Set the block name to one that does not correspond to an existing registered block.
						// This ensures that for the inner instances of the Post Template block, we do not render any block supports.
						$block_instance['blockName'] = 'core/null';

						$post_id = get_the_ID();
						$post_type = get_post_type();

						$filter_block_context = static function ($context) use ($post_id, $post_type) {
							$context['postType'] = $post_type;
							$context['postId'] = $post_id;

							return $context;
						};

						// Use an early priority to so that other 'render_block_context' filters have access to the values.
						add_filter('render_block_context', $filter_block_context, 1);

						// Render the inner blocks of the Post Template block with `dynamic` set to `false` to prevent calling
						// `render_callback` and ensure that no wrapper markup is included.
						$block_content = (new \WP_Block($block_instance))->render(
							['dynamic' => false]
						);
						remove_filter('render_block_context', $filter_block_context, 1);

						// Wrap the render inner blocks in a `li` element with the appropriate post classes.
						$post_classes = implode(' ', get_post_class('wp-block-post'));

						$content .= '<article' . ' class="' . esc_attr($post_classes) . '">' . $block_content . '</article>';
					}

					/*
					 * Use this function to restore the context of the template tags
					 * from a secondary query loop back to the main query loop.
					 * Since we use two custom loops, it's safest to always restore.
					 */
					wp_reset_postdata();

					$result = sprintf(
						'<div %1$s>%2$s</div>',
						$wrapper_attributes,
						$content
					);

					if (blocksy_akg('has_pagination', $block->context, 'no') === 'yes') {
						$prefix = $this->get_prefix_for($block->context);

						$result .= blocksy_display_posts_pagination([
							'query' => $query,
							'prefix' => $prefix
						]);
					}

					return $result;
				},
			]
		);

		add_action(
			'init',
			function () {
				$posts_block_patterns = [
					'posts-layout-1',
					'posts-layout-2',
					'posts-layout-3',
					'posts-layout-4',
				];

				foreach ($posts_block_patterns as $posts_block_pattern) {
					$pattern_data = blocksy_get_variables_from_file(
						__DIR__ . '/block-patterns/' . $posts_block_pattern . '.php',
						['pattern' => []]
					);

					register_block_pattern(
						'blocksy/' . $posts_block_pattern,
						$pattern_data['pattern']
					);
				}
			}
		);
	}

	public function render_block($attributes, $context = []) {
		$attributes = wp_parse_args(
			$attributes,
			[
				'post_type' => 'post',
				'limit' => 5,

				'offset' => 0,

				// post_date | comment_count
				'orderby' => 'post_date',
				'order' => 'DESC',

				// yes | no
				'has_pagination' => 'no',

				// include | exclude | only
				'sticky_posts' => 'include',

				// 404 | skip
				'no_results' => '404',

				'class' => ''
			]
		);

		$block_class = 'ct-posts-block';

		if (! empty($attributes['class'])) {
			$block_class .= ' ' . esc_attr($attributes['class']);
		}

		$query = $this->get_query_for($attributes, $context);

		if (! $query->have_posts() && $attributes['no_results'] === 'skip') {
			return;
		}

		$prefix = $this->get_prefix_for($attributes);

		$result = '<div class="' . $block_class . '" data-prefix="' . $prefix . '">';

		global $wp_query;

		$prev_query = $wp_query;

		if (wp_doing_ajax()) {
			$wp_query = $query;
		}

		ob_start();

		blocksy_render_archive_cards([
			'prefix' => $prefix,
			'query' => $query,
			'has_pagination' => $attributes['has_pagination'] === 'yes'
		]);

		$result .= ob_get_clean();

		wp_reset_postdata();

		if (wp_doing_ajax()) {
			$wp_query = $prev_query;
		}

		$result .= '</div>';

		return $result;
	}

	public function get_query_for($attributes, $context = []) {
		$attributes = wp_parse_args(
			$attributes,
			[
				'post_type' => 'post',
				'limit' => 5,
				'offset' => 0,

				// post_date | comment_count
				'orderby' => 'post_date',
				'order' => 'DESC',

				// yes | no
				'has_pagination' => 'no',

				'sticky_posts' => 'include',

				// 404 | skip
				'no_results' => '404',

				'include_term_ids' => [],
				'exclude_term_ids' => [],

				'class' => ''
			]
		);

		$context = wp_parse_args($context, [
			'post_id' => get_the_ID()
		]);

		$query_args = [
			'order' => $attributes['order'],
			'post_type' => explode(',', $attributes['post_type']),
			'orderby' => $attributes['orderby'],
			'posts_per_page' => $attributes['limit'],
			'post_status' => 'publish'
		];

		if ($attributes['offset'] !== 0) {
			$query_args['offset'] = $attributes['offset'];
		}

		if ($attributes['sticky_posts'] === 'exclude') {
			// $query_args['ignore_sticky_posts'] = true;
			$query_args['post__not_in'] = get_option('sticky_posts');
		}

		if ($attributes['sticky_posts'] === 'only') {
			$sticky_posts = get_option('sticky_posts');
			$query_args['ignore_sticky_posts'] = true;

			if (! empty($sticky_posts)) {
				$query_args['post__in'] = $sticky_posts;
			}
		}

		if ($attributes['has_pagination'] === 'yes') {
			if (get_query_var('paged')) {
				$query_args['paged'] = get_query_var('paged');
			} elseif (get_query_var('page')) {
				$query_args['paged'] = get_query_var('page');
			} else {
				$query_args['paged'] = 1;
			}
		}

		$to_include = [
			'relation' => 'OR'
		];

		$to_exclude = [
			'relation' => 'AND'
		];

		foreach ($attributes['include_term_ids'] as $term_slug => $term_descriptor) {
			if ($term_descriptor['strategy'] === 'all') {
				continue;
			}

			if (
				$term_descriptor['strategy'] === 'specific'
				&&
				! empty($term_descriptor['terms'])
			) {
				$terms_to_pass = [];

				foreach ($term_descriptor['terms'] as $internal_term_slug) {
					if (function_exists('pll_get_term')) {
						$all_terms = get_terms(array(
							'taxonomy' => $term_slug,
							'slug' => esc_attr($internal_term_slug),
							'lang' => ''
						));

						if (empty($all_terms)) {
							continue;
						}

						$term = $all_terms[0];

						if (! $term) {
							continue;
						}

						$current_lang = blocksy_get_current_language();
						$current_term_id = pll_get_term($term->term_id, $current_lang);

						$term = get_term_by('id', $current_term_id, $term_slug);

						$internal_term_slug = $term->slug;
					}

					$terms_to_pass[] = $internal_term_slug;
				}

				$to_include[] = [
					'field' => 'slug',
					'taxonomy' => $term_slug,
					'terms' => $terms_to_pass
				];
			}

			if (
				$term_descriptor['strategy'] === 'related'
				&&
				$context['post_id']
			) {
				$post = get_post($context['post_id']);

				$current_post_type = get_post_type($post);

				if ($current_post_type !== $attributes['post_type']) {
					continue;
				}

				$all_taxonomies = get_the_terms($post->ID, $term_slug);

				if (! $all_taxonomies || is_wp_error($all_taxonomies)) {
					continue;
				}

				$all_taxonomy_ids = [];

				foreach ($all_taxonomies as $current_taxonomy) {
					if (! isset($current_taxonomy->term_id)) {
						continue;
					}

					$current_term_id = $current_taxonomy->term_id;

					if (function_exists('pll_get_term')) {
						$current_lang = blocksy_get_current_language();
						$current_term_id = pll_get_term($current_term_id, $current_lang);
					}

					if (! $current_term_id) {
						continue;
					}

					$all_taxonomy_ids[] = $current_term_id;
				}

				$query_args['post__not_in'] = [$post->ID];

				if (! empty($all_taxonomy_ids)) {
					$to_include[] = [
						'field' => 'term_id',
						'taxonomy' => $term_slug,
						'terms' => $all_taxonomy_ids
					];
				}
			}
		}

		foreach ($attributes['exclude_term_ids'] as $term_slug => $term_descriptor) {
			if ($term_descriptor['strategy'] === 'all') {
				continue;
			}

			if (
				$term_descriptor['strategy'] === 'specific'
				&&
				! empty($term_descriptor['terms'])
			) {
				$terms_to_pass = [];

				foreach ($term_descriptor['terms'] as $internal_term_slug) {
					if (function_exists('pll_get_term')) {
						$all_terms = get_terms(array(
							'taxonomy' => $term_slug,
							'slug' => esc_attr($internal_term_slug),
							'lang' => ''
						));

						if (empty($all_terms)) {
							continue;
						}

						$term = $all_terms[0];

						if (! $term) {
							continue;
						}

						$current_lang = blocksy_get_current_language();
						$current_term_id = pll_get_term($term->term_id, $current_lang);

						$term = get_term_by('id', $current_term_id, $term_slug);

						$internal_term_slug = $term->slug;
					}

					$terms_to_pass[] = $internal_term_slug;
				}

				$to_exclude[] = [
					'field' => 'slug',
					'taxonomy' => $term_slug,
					'terms' => $terms_to_pass,
					'operator' => 'NOT IN'
				];
			}
		}

		$tax_query = [];

		if (count($to_include) > 1) {
			$tax_query = array_merge($to_include, $tax_query);
		}

		if (count($to_exclude) > 1) {
			$tax_query = array_merge($to_exclude, $tax_query);
		}

		if (count($to_include) > 1 && count($to_exclude) > 1) {
			$tax_query['relation'] = 'AND';
		}

		$query_args['tax_query'] = $tax_query;

		$q = new \WP_Query();

		if (
			empty($query_args['tax_query'])
			&&
			$attributes['sticky_posts'] === 'include'
		) {
			add_action('pre_get_posts', [$this, 'pre_get_posts']);
		}

		$q->query(apply_filters(
			'blocksy:general:blocks:query:args',
			$query_args,
			$attributes
		));

		if (
			empty($query_args['tax_query'])
			&&
			$attributes['sticky_posts'] === 'include'
		) {
			remove_action('pre_get_posts', [$this, 'pre_get_posts']);
		}

		return $q;
	}

	public function pre_get_posts($q) {
		$q->is_home = true;
	}

	public function get_dynamic_styles_for($attributes) {
		$prefix = $this->get_prefix_for($attributes);

		$styles = [
			'desktop' => '',
			'tablet' => '',
			'mobile' => ''
		];

		$css = new \Blocksy_Css_Injector();
		$tablet_css = new \Blocksy_Css_Injector();
		$mobile_css = new \Blocksy_Css_Injector();

		blocksy_theme_get_dynamic_styles([
			'name' => 'global/posts-listing',
			'css' => $css,
			'mobile_css' => $mobile_css,
			'tablet_css' => $tablet_css,
			'context' => 'global',
			'chunk' => 'global',
			'prefixes' => [ $prefix ]
		]);

		$styles['desktop'] .= $css->build_css_structure();
		$styles['tablet'] .= $tablet_css->build_css_structure();
		$styles['mobile'] .= $mobile_css->build_css_structure();

		$final_css = '';

		if (! empty($styles['desktop'])) {
			$final_css .= $styles['desktop'];
		}

		if (! empty(trim($styles['tablet']))) {
			$final_css .= '@media (max-width: 999.98px) {' . $styles['tablet'] . '}';
		}

		if (! empty(trim($styles['mobile']))) {
			$final_css .= '@media (max-width: 689.98px) {' . $styles['mobile'] . '}';
		}

		return $final_css;
	}

	public function get_prefix_for($attributes) {
		$attributes = wp_parse_args(
			$attributes,
			[
				'post_type' => 'post',
				'limit' => 5,

				// post_date | comment_count
				'orderby' => 'post_date',
				'order' => 'DESC',

				// yes | no
				'has_pagination' => 'no',

				'sticky_posts' => 'include',

				// 404 | skip
				'no_results' => '404',

				'class' => ''
			]
		);

		$prefix = 'blog';

		$custom_post_types = blocksy_manager()->post_types->get_supported_post_types();

		$preferred_post_type = explode(',', $attributes['post_type'])[0];

		foreach ($custom_post_types as $cpt) {
			if ($cpt === $preferred_post_type) {
				$prefix = $cpt . '_archive';
			}
		}

		return $prefix;
	}
}

