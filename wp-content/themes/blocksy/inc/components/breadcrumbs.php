<?php

namespace Blocksy;

class BreadcrumbsBuilder {
	public function mount_shortcode() {
		call_user_func(
			'add_' . 'shortcode',
			'blocksy_breadcrumbs',
			function ($args, $content) {
				return $this->render([
					'class' => 'ct-breadcrumbs-shortcode'
				]);
			}
		);
	}

	/**
	 * Determine the current frontend page location, in creates the breadcrumbs array
	 * @return array
	 */
	private function build_breadcrumbs() {
		if (is_admin()) {
			return [];
		}

		if (did_action('wp') === 0) {
			return [];
		}

		$home_icon = '';

		if (blocksy_get_theme_mod('breadcrumb_home_item', 'text') === 'icon') {
			$home_icon = '<svg class="ct-home-icon" width="15" viewBox="0 0 24 20" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12,0L0.4,10.5h3.2V20h6.3v-6.3h4.2V20h6.3v-9.5h3.2L12,0z"/></svg>';
		}

		$return = [
			0 => [
				'name' => blocksy_get_theme_mod(
					'breadcrumb_home_text',
					__('Home', 'blocksy')
				),
				'url' => esc_url(home_url('/')),
				'type' => 'front_page',
				'icon' => $home_icon
			]
		];

		$has_single = blocksy_get_theme_mod('breadcrumb_page_title', 'yes') === 'yes';
		$has_taxonomy = blocksy_get_theme_mod('breadcrumb_taxonomy_title', 'yes') === 'yes';
		$has_single_taxonomy = blocksy_get_theme_mod('breadcrumb_single_taxonomy_title', 'yes') === 'yes';

		$custom_page = [];

		if (is_array($custom_page) && !empty($custom_page)) {
			$return[] = $custom_page;
			return $return;
		}

		if (is_404()) {
			$page = [];

			$page['type'] = '404';
			$page['name'] = __('404 Not found', 'blocksy');
			$page['url'] = blocksy_current_url();

			$return[] = $page;
		} elseif (is_search()) {
			$search = [];

			$search['type'] = 'search';
			$search['name'] = __('Searching for:', 'blocksy') . ' ' . get_search_query();
			$s = '?s=' . get_search_query();
			$search['url'] = home_url('/') . $s;

			$return[] = $search;
		} elseif (is_front_page()) {
			$return = array_merge(
				$return,
				$this->get_custom_post_type_archive()
			);
		} elseif ($blocksy_is_page = blocksy_is_page()) {
			$return = array_merge(
				$return,
				array_reverse($this->get_page_hierarchy($blocksy_is_page))
			);

			$has_single = blocksy_get_theme_mod(
				'breadcrumb_page_title',
				'yes'
			) === 'yes';

			if (! $has_single) {
				array_pop($return);
			}
		} elseif (is_single()) {
			global $post;

			$taxonomies = get_object_taxonomies($post->post_type, 'objects');

			$primary_taxonomy_hash = [
				'post' => 'category',
				'product' => 'product_cat'
			];

			$slugs = [];

			if (isset($primary_taxonomy_hash[$post->post_type])) {
				foreach ($taxonomies as $key => $tax) {
					if ($tax->name === $primary_taxonomy_hash[$post->post_type]) {
						$slugs[] = $tax->name;
						break;
					}
				}
			}

			$return = array_merge(
				$return,
				$this->get_custom_post_type_archive()
			);

			if ($has_single_taxonomy && ! empty($taxonomies)) {
				if (empty($slugs)) {
					foreach ($taxonomies as $key => $tax) {
						if (
							$tax->show_ui === true
							&&
							$tax->public === true
							&&
							$tax->hierarchical !== false
						) {
							array_push($slugs, $tax->name);
						}
					}
				}

				$slugs = apply_filters(
					'blocksy:breadcrumbs:single:taxonomies:slugs',
					$slugs
				);

				$terms = wp_get_post_terms($post->ID, $slugs);

				if (! empty($terms)) {
					$lowest_term = $this->get_lowest_taxonomy_terms(
						$post, $terms,
						$slugs[0]
					);

					$term = $lowest_term[0];

					$return = array_merge(
						$return,
						array_reverse(
							$this->get_term_hierarchy(
								$term->term_id,
								$term->taxonomy
							)
						)
					);
				}
			}

			$return = array_merge(
				$return,
				array_reverse($this->get_page_hierarchy($post->ID))
			);

			$has_single = blocksy_get_theme_mod(
				'breadcrumb_page_title',
				'yes'
			) === 'yes';

			if (! $has_single) {
				array_pop($return);
			}
		} elseif (is_category()) {
			$term_id = get_query_var('cat');

			$tax_result = array_reverse(
				$this->get_term_hierarchy($term_id, 'category')
			);

			if (! $has_taxonomy) {
				array_pop($tax_result);
			}

			$return = array_merge(
				$return,
				$tax_result
			);
		} elseif (is_tag()) {
			$term_id = get_query_var('tag');
			$term = get_term_by('slug', $term_id, 'post_tag');

			if (empty($term) || is_wp_error($term)) {
				return [];
			}

			if ($has_taxonomy) {
				$tag = [];

				$tag['type'] = 'taxonomy';
				$tag['name'] = $term->name;
				$tag['url'] = get_term_link($term_id, 'post_tag');
				$tag['taxonomy'] = 'post_tag';
				$return[] = $tag;
			}
		} elseif (is_tax()) {
			$term_id = get_queried_object()->term_id;
			$taxonomy = get_queried_object()->taxonomy;

			$tax_result = array_reverse(
				$this->get_term_hierarchy($term_id, $taxonomy)
			);

			if (! $has_taxonomy) {
				array_pop($tax_result);
			}

			$return = array_merge(
				$return,
				$this->get_custom_post_type_archive(),
				$tax_result
			);
		} elseif (is_author()) {
			$author = [];

			$author_data = get_userdata(get_the_author_meta('ID'));

			$author['name'] = $author_data->display_name;
			$author['id'] = get_the_author_meta('ID');
			$author['url'] = get_author_posts_url(
				$author['id'],
				$author_data->user_nicename
			);
			$author['type'] = 'author';

			$return[] = $author;
		} elseif (is_date()) {
			$date = [];

			if (get_option('permalink_structure')) {
				$day = get_query_var('day');
				$month = get_query_var('monthnum');
				$year = get_query_var('year');
			} else {
				$m = get_query_var('m');
				$year = substr($m, 0, 4);
				$month = substr($m, 4, 2);
				$day = substr($m, 6, 2);
			}

			if (is_day()) {
				$date['name'] = mysql2date(
					'd F Y',
					$day . '-' . $month . '-' . $year
				);
				$date['url'] = get_day_link($year, $month, $day);
				$date['date_type'] = 'daily';
				$date['day'] = $day;
				$date['month'] = $month;
				$date['year'] = $year;
			} elseif (is_month()) {
				$date['name'] = mysql2date(
					'F Y',
					'01.' . $month . '.' . $year
				);
				$date['url'] = get_month_link($year, $month);
				$date['date_type'] = 'monthly';
				$date['month'] = $month;
				$date['year'] = $year;
			} else {
				$date['name'] = mysql2date(
					'Y',
					'01.01.' . $year
				);
				$date['url'] = get_year_link($year);
				$date['date_type'] = 'yearly';
				$date['year'] = $year;
			}

			$return[] = $date;
		} elseif (is_archive()) {
			$return = array_merge(
				$return,
				$this->get_custom_post_type_archive()
			);
		}

		foreach ($return as $key => $item) {
			if (empty($item['name'])) {
				$return[$key]['name'] = __('No title', 'blocksy');
			}
		}

		if (function_exists('is_woocommerce') && is_woocommerce()) {
			$permalinks = wc_get_permalink_structure();
			$shop_page_id = apply_filters(
				'wpml_object_id',
				wc_get_page_id('shop'),
				'page'
			);
			$shop_page = get_post($shop_page_id);

			$shop_page_for_matching = $shop_page;

			$product_base = '';

			if (isset($permalinks['product_base'])) {
				$product_base = trim($permalinks['product_base'], '/');
			}

			global $sitepress, $woocommerce_wpml;

			if ($sitepress && $woocommerce_wpml) {
				$product_base = $woocommerce_wpml->url_translation->get_woocommerce_product_base();

				$shop_page_for_matching = get_post(
					apply_filters(
						'translate_object_id',
						$shop_page_id,
						'page',
						true,
						$sitepress->get_default_language()
					)
				);
			}

			if (
				$shop_page_id
				&&
				$shop_page
				&&
				$permalinks['product_base']
				&&
				strstr($product_base, $shop_page_for_matching->post_name)
				&&
				intval(get_option('page_on_front')) !== $shop_page_id
				&&
				intval($shop_page_id) !== intval(blocksy_is_page())
			) {
				array_splice($return, 1, 0, [
					[
						'url' => get_permalink($shop_page),
						'name' => get_the_title($shop_page)
					]
				]);
			}
		}

		return $this->post_process_breadcrumbs($return);
	}

	private function post_process_breadcrumbs($items) {
		if (
			blocksy_get_theme_mod('breadcrumb_shop_item', 'no') === 'yes'
			&&
			function_exists('wc_get_page_id')
			&&
			is_single()
			&&
			get_post_type() === 'product'
		) {
			$shop_page_id = wc_get_page_id('shop');
			$shop_page_url = get_permalink(wc_get_page_id('shop'));

			array_splice($items, 1, 0, [
				[
					'url' => esc_url($shop_page_url),
					'name' => $shop_page_id ? get_the_title($shop_page_id) : __('Shop', 'blocksy'),
				]
			]);
		}

		if (
			is_single()
			&&
			get_post_type() === 'post'
			&&
			blocksy_get_theme_mod('breadcrumb_blog_item', 'no') === 'yes'
		) {
			$page_for_posts = get_option('page_for_posts');

			array_splice($items, 1, 0, [
				[
					'url' => esc_url(get_post_type_archive_link('post')),
					'name' => $page_for_posts ? get_the_title($page_for_posts) : __('Blog', 'blocksy'),
				]
			]);
		}

		return apply_filters('blocksy:breadcrumbs:items-array', $items);
	}

	/**
	 * Determine if the page has parents and in case it has, adds all page parents hierarchy
	 *
	 * @param $id , page id
	 *
	 * @return array
	 */
	private function get_page_hierarchy($id, $has_single_check = true) {
		$page = get_post($id);

		if (empty($page) || is_wp_error($page)) {
			return [];
		}

		$return = [];
		$page_obj = [];

		$page_obj['type'] = 'post';
		$page_obj['post_type'] = $page->post_type;
		$page_obj['name'] = $page->post_title;
		$page_obj['id'] = $id;
		$page_obj['url'] = get_permalink($id);

		$return[] = $page_obj;

		if ($page->post_parent > 0) {
			$return = array_merge(
				$return,
				$this->get_page_hierarchy($page->post_parent)
			);
		}

		return $return;
	}

	/**
	 * Determine if the term has parents and in case it has, adds all term parents hierarchy
	 *
	 * @param $id , term id
	 * @param $taxonomy , term taxonomy name
	 *
	 * @return array
	 */
	private function get_term_hierarchy($id, $taxonomy) {
		$term = get_term($id, $taxonomy);

		if (empty($term) || is_wp_error($term)) {
			return [];
		}

		$return = [];
		$term_obj = [];

		$term_obj['type'] = 'taxonomy';
		$term_obj['name'] = $term->name;
		$term_obj['id'] = $id;
		$term_obj['url'] = get_term_link($id, $taxonomy);
		$term_obj['taxonomy'] = $taxonomy;

		$return[] = $term_obj;

		if ($term->parent > 0) {
			$return = array_merge(
				$return,
				$this->get_term_hierarchy($term->parent, $taxonomy)
			);
		}

		return $return;
	}

	private function get_custom_post_type_archive() {
		$return = [];

		$post_type = get_post_type();
		$post_type_object = get_post_type_object($post_type);

		if (
			$post_type_object
			&&
			$post_type !== 'product'
			&&
			$post_type_object->has_archive
		) {
			// Add support for a non-standard label of 'archive_title' (special use case).
			$label = ! empty(
				$post_type_object->labels->archive_title
			) ? $post_type_object->labels->archive_title : $post_type_object->labels->name;

			// Core filter hook.
			$label = apply_filters(
				'post_type_archive_title',
				$label,
				$post_type_object->name
			);

			$return[] = [
				'name' => $label,
				'url' => get_post_type_archive_link($post_type)
			];
		}

		return $return;
	}


	/**
	 * Returns the lowest hierarchical term
	 * @return array
	 */
	private function get_lowest_taxonomy_terms($post, $terms, $taxonomy) {
		$post_id = $post->ID;

		$primary_term = null;

		if (class_exists('WPSEO_Primary_Term')) {
			$primary_term = new \WPSEO_Primary_Term($taxonomy, $post_id);
			$primary_term = get_term($primary_term->get_primary_term());
		}

		// B. The SEO Framework
		if (
			function_exists('the_seo_framework')
			&&
			method_exists(the_seo_framework(), 'data')
		) {
			$primary_term = the_seo_framework()->data()->plugin()->post()->get_primary_term(
				$post_id,
				$taxonomy
			);
		}

		// C. RankMath
		if (class_exists('RankMath')) {
			$primary_cat_id = get_post_meta($post_id, "rank_math_primary_{$taxonomy}", true);
			$primary_term = (!empty($primary_cat_id)) ? get_term($primary_cat_id, $taxonomy) : '';
		}

		// D. SEOPress
		if (function_exists('seopress_init') && $taxonomy == 'category') {
			$primary_cat_id = get_post_meta($post_id, '_seopress_robots_primary_cat', true);
			$primary_term = (!empty($primary_cat_id)) ? get_term($primary_cat_id, 'category') : '';
		}

		if ($primary_term && ! is_wp_error($primary_term)) {
			return [$primary_term];
		}

		// if terms is not array or its empty don't proceed
		if (! is_array($terms) || empty($terms)) {
			return false;
		}

		return $this->filter_terms($terms);
	}

	private function filter_terms($terms) {
		$return_terms = array();
		$term_ids = array();

		foreach ($terms as $t) {
			$term_ids[] = $t->term_id;
		}

		foreach ($terms as $t) {
			if ($t->parent == false || !in_array($t->parent,$term_ids)) {
				// remove this term
			} else {
				$return_terms[] = $t;
			}
		}

		if (count($return_terms)) {
			return $this->filter_terms($return_terms);
		} else {
			return $terms;
		}
	}

	/**
	 * Returns the breadcrumbs array
	 * @return string
	 */
	public function get_breadcrumbs() {
		$result = $this->build_breadcrumbs();

		if (class_exists('WC_Breadcrumb')) {
			$woo_compatible_breadcrumbs = new \WC_Breadcrumb();

			foreach ($result as $item) {
				$woo_compatible_breadcrumbs->add_crumb($item['name'], $item['url']);
			}

			do_action(
				'woocommerce_breadcrumb',
				$woo_compatible_breadcrumbs,
				apply_filters(
					'woocommerce_breadcrumb_defaults',
					[
						'delimiter'   => '&nbsp;&#47;&nbsp;',
						'wrap_before' => '<nav class="woocommerce-breadcrumb">',
						'wrap_after'  => '</nav>',
						'before'      => '',
						'after'       => '',
						'home'        => _x( 'Home', 'breadcrumb', 'blocksy' ),
					]
				)
			);
		}

		return $result;
	}

	public function render($args = []) {
		$args = wp_parse_args($args, [
			'class' => '',
			'style' => ''
		]);

		$source = blocksy_get_theme_mod('breadcrumbs_source', 'default');

		if (
			function_exists('rank_math_the_breadcrumbs')
			&&
			$source === 'rankmath'
		) {
			ob_start();
			rank_math_the_breadcrumbs();
			$content = ob_get_clean();

			if (! empty($content)) {
				return '<div class="ct-breadcrumbs" data-source="' . $source . '">' . $content . '</div>';
			}
		}

		if (
			function_exists('yoast_breadcrumb')
			&&
			$source === 'yoast'
		) {
			ob_start();
			yoast_breadcrumb('<div class="ct-breadcrumbs" data-source="' . $source . '">', '</div>');
			$content = ob_get_clean();

			if (! empty($content)) {
				return $content;
			}
		}

		if (
			function_exists('seopress_display_breadcrumbs')
			&&
			$source === 'seopress'
		) {
			ob_start();
			echo '<div class="ct-breadcrumbs" data-source="' . $source . '">';
			seopress_display_breadcrumbs();
			echo '</div>';
			return ob_get_clean();
		}

		if (
			function_exists('bcn_display')
			&&
			$source === 'bcnxt'
		) {
			ob_start();
			echo '<div class="ct-breadcrumbs" data-source="' . $source . '">';
			bcn_display();
			echo '</div>';
			return ob_get_clean();
		}

		$items = $this->get_breadcrumbs();

		$separators = [
			'type-1' => '<svg class="separator" fill="currentColor" width="8" height="8" viewBox="0 0 8 8" aria-hidden="true" focusable="false">
				<path d="M2,6.9L4.8,4L2,1.1L2.6,0l4,4l-4,4L2,6.9z"/>
			</svg>',

			'type-2' => '<svg class="separator" fill="currentColor" width="8" height="8" viewBox="0 0 8 8" aria-hidden="true" focusable="false">
				<polygon points="2.5,0 6.9,4 2.5,8 "/>
			</svg>',

			'type-3' => '<span class="separator">/</span>'
		];

		$separator = $separators[
			blocksy_get_theme_mod('breadcrumb_separator', 'type-1')
		];

		if (count($items) < 1) {
			return '';
		}

		$class = 'ct-breadcrumbs';
		$style = '';

		if (! empty($args['class'])) {
			$class .= ' ' . $args['class'];
		}

		if (! empty($args['style'])) {
			$style .= 'style="' . $args['style'] . '"';
		}

		ob_start();

		?>

			<nav class="<?php echo $class ?>" data-source="<?php echo $source; ?>" <?php echo $style; ?> <?php echo blocksy_schema_org_definitions('breadcrumb_list') ?>><?php

				for ($i = 0; $i < count($items); $i++) {
					if ($i === (count($items) - 1)) {
						$should_be_link = false;

						if (is_single() || blocksy_is_page()) {
							$has_single = blocksy_get_theme_mod(
								'breadcrumb_page_title',
								'yes'
							) === 'yes';

							if (! $has_single) {
								$should_be_link = true;
							}
						}

						if (is_category() || is_tag() || is_tax()) {
							$has_taxonomy = blocksy_get_theme_mod(
								'breadcrumb_taxonomy_title',
								'yes'
							) === 'yes';

							if (! $has_taxonomy) {
								$should_be_link = true;
							}
						}

						echo '<span class="last-item" aria-current="page" ' . blocksy_schema_org_definitions('breadcrumb_item') . '>';

						if (blocksy_has_schema_org_markup()) {
							echo '<meta itemprop="position" content="' . ($i + 1) . '">';
						}

						if (isset($items[$i]['url']) && $should_be_link) {
							echo '<a href="' . esc_attr( $items[ $i ]['url'] ) . '" ' . blocksy_schema_org_definitions('item'). '>';

							$span_attr = blocksy_schema_org_definitions('name', [
								'array' => true
							]);

							if (
								isset($items[$i]['icon'])
								&&
								! empty($items[$i]['icon'])
							) {
								$span_attr['class'] = 'screen-reader-text';
								echo $items[$i]['icon'];
							}

							echo '<span ' . blocksy_attr_to_html($span_attr) . '>';
							echo $items[ $i ]['name'];
							echo '</span>';

							echo '</a>';
						} else {
							$span_attr = blocksy_schema_org_definitions('name', [
								'array' => true
							]);

							if (
								isset($items[$i]['icon'])
								&&
								! empty($items[$i]['icon'])
							) {
								$span_attr['class'] = 'screen-reader-text';
								echo $items[$i]['icon'];
							}

							echo '<span ' . blocksy_attr_to_html($span_attr) . '>';
							echo $items[ $i ]['name'];
							echo '</span>';
						}

						if (
							blocksy_has_schema_org_markup()
							&&
							isset($items[$i]['url'])
						) {
							echo '<meta itemprop="url" content="' . esc_attr( $items[ $i ]['url'] ) . '"/>';
						}

						echo '</span>';
					} else if ($i === 0) {
						echo '<span class="first-item" ' .  blocksy_schema_org_definitions('breadcrumb_item') . '>';

						if (blocksy_has_schema_org_markup()) {
							echo '<meta itemprop="position" content="' . ($i + 1) . '">';
						}

						if (isset($items[$i]['url'])) {
							echo '<a href="' . esc_attr($items[$i]['url']) . '" ' . blocksy_schema_org_definitions('item') . '>';

							$span_attr = blocksy_schema_org_definitions('name', [
								'array' => true
							]);

							if (
								isset($items[$i]['icon'])
								&&
								! empty($items[$i]['icon'])
							) {
								$span_attr['class'] = 'screen-reader-text';
								echo $items[$i]['icon'];
							}

							echo '<span ' . blocksy_attr_to_html($span_attr) . '>';
							echo $items[$i]['name'];
							echo '</span>';

							echo '</a>';
						} else {
							echo $items[$i]['name'];
						}

						if (
							blocksy_has_schema_org_markup()
							&&
							isset($items[$i]['url'])
						) {
							echo '<meta itemprop="url" content="' . esc_attr($items[$i]['url']) . '"/>';
						}

						echo $separator;

						echo '</span>';
					} else {
						echo '<span class="item-' . ($i - 1) . '"' . blocksy_schema_org_definitions('breadcrumb_item') . '>';

						if (blocksy_has_schema_org_markup()) {
							echo '<meta itemprop="position" content="' . ($i + 1) . '">';
						}

						if (isset($items[$i]['url'])) {
							echo '<a href="' . esc_attr( $items[ $i ]['url'] ) . '" ' . blocksy_schema_org_definitions('item') . '>';

							$span_attr = blocksy_schema_org_definitions('name', [
								'array' => true
							]);

							if (
								isset($items[$i]['icon'])
								&&
								! empty($items[$i]['icon'])
							) {
								$span_attr['class'] = 'screen-reader-text';
								echo $items[$i]['icon'];
							}

							echo '<span ' . blocksy_attr_to_html($span_attr) . '>';
							echo $items[ $i ]['name'];
							echo '</span>';

							echo '</a>';
						} else {
							echo $items[$i]['name'];
						}

						if (
							blocksy_has_schema_org_markup()
							&&
							isset($items[$i]['url'])
						) {
							echo '<meta itemprop="url" content="' . esc_attr( $items[ $i ]['url'] ) . '"/>';
						}

						echo $separator;

						echo '</span>';
					}

				} ?>
			</nav>

		<?php

		return ob_get_clean();
	}
}


