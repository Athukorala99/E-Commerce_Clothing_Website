<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Blocksy
 */

/**
 * Single entry title.
 *
 * @param string $tag HTML tag.
 */
if (! function_exists('blocksy_entry_title')) {
function blocksy_entry_title( $tag = 'h2' ) {
	if (empty(get_the_title())) {
		return '';
	}

	ob_start();

	?>

	<<?php echo esc_attr( $tag ); ?> class="entry-title">
		<a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark">
			<?php the_title(); ?>
		</a>
	</<?php echo esc_attr( $tag ); ?>>

	<?php

	return ob_get_clean();
}
}

/**
 * Output entry excerpt.
 *
 * @param number $length Number of words allowed in excerpt.
 */
if (! function_exists('blocksy_entry_excerpt')) {
	function blocksy_entry_excerpt($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'length' => 40,
				'container_tag' => 'div',
				'class' => 'entry-excerpt',
				'post_id' => null,

				// excerpt | full | custom
				'source' => 'excerpt',
				'custom_exceprt' => '', // for custom only
				'skip_container' => false
			]
		);

		add_filter(
			'excerpt_length',
			'blocksy_excerpt_length',
			PHP_INT_MAX
		);

		ob_start();
		$post_excerpt = get_the_excerpt($args['post_id']);
		$excerpt_additions = ob_get_clean();

		remove_filter(
			'excerpt_length',
			'blocksy_excerpt_length',
			PHP_INT_MAX
		);

		if ($args['source'] === 'excerpt' && empty(trim($post_excerpt))) {
			return '';
		}

		$post = get_post($args['post_id']);

		$has_native_excerpt = $post->post_excerpt;

		// Check for woo product ( wysiwyg editor )
		$is_product = $post->post_type === 'product';

		$excerpt = null;

		if ($args['source'] === 'excerpt') {
			if (! $is_product) {
				if ($has_native_excerpt) {
					$excerpt = $post_excerpt;

					$excerpt = apply_filters(
						'blocksy:excerpt:output',
						$excerpt
					);
				}

				if (! $excerpt) {
					$excerpt = $post_excerpt;

					ob_start();
					blocksy_trim_excerpt($excerpt, $args['length']);
					$excerpt = ob_get_clean();
				}
			}

			if ($is_product) {
				$excerpt = apply_filters(
					'woocommerce_short_description',
					$post->post_excerpt
				);

				if (! empty($excerpt)) {
					ob_start();
					blocksy_trim_excerpt($excerpt, $args['length']);
					$excerpt = ob_get_clean();
				}

				$excerpt = apply_filters(
					'blocksy:excerpt:output',
					$excerpt
				);
			}
		}

		if ($args['source'] === 'full') {
			$args['class'] .= ' entry-content';

			ob_start();
			the_content(
				sprintf(
					wp_kses(
						/* translators: 1: span open 2: Name of current post. Only visible to screen readers 3: span closing */
						__(
							'Continue reading%1$s "%2$s"%3$s',
							'blocksy'
						),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					'<span class="screen-reader-text">',
					get_the_title(),
					'</span>'
				)
			);
			$excerpt = ob_get_clean();

			$excerpt = apply_filters('blocksy:excerpt:output', $excerpt);
		}

		if ($args['source'] === 'custom') {
			ob_start();
			blocksy_trim_excerpt($args['custom_exceprt'], $args['length']);
			$excerpt = ob_get_clean();

			$excerpt = apply_filters('blocksy:excerpt:output', $excerpt);
		}

		if ($args['skip_container']) {
			return $excerpt_additions . do_shortcode($excerpt);
		}

		return blocksy_html_tag(
			$args['container_tag'],
			[
				'class' => esc_attr($args['class'])
			],
			$excerpt_additions . do_shortcode($excerpt)
		);
	}
}

/**
 * Output post navigation.
 */
if (! function_exists('blocksy_post_navigation')) {
function blocksy_post_navigation() {
	$prefix = blocksy_manager()->screen->get_prefix();

	$next_post = apply_filters(
		'blocksy:post-navigation:next-post',
		get_adjacent_post(false, '', true)
	);

	$previous_post = apply_filters(
		'blocksy:post-navigation:previous-post',
		get_adjacent_post(false, '', false)
	);

	$post_nav_criteria = blocksy_get_theme_mod($prefix . '_post_nav_criteria', 'default');

	if ($post_nav_criteria !== 'default') {
		$post_type = get_post_type();
		$post_nav_taxonomy_default = array_keys(blocksy_get_taxonomies_for_cpt(
			$post_type
		))[0];

		$post_nav_taxonomy = blocksy_get_theme_mod(
			$prefix . '_post_nav_taxonomy',
			$post_nav_taxonomy_default
		);

		$next_post = apply_filters(
			'blocksy:post-navigation:next-post',
			get_adjacent_post(true, '', true, $post_nav_taxonomy)
		);

		$previous_post = apply_filters(
			'blocksy:post-navigation:previous-post',
			get_adjacent_post(true, '', false, $post_nav_taxonomy)
		);
	}

	if (! $next_post && ! $previous_post) {
		return '';
	}

	$title_class = 'item-title';

	$title_class .= ' ' . blocksy_visibility_classes(blocksy_get_theme_mod(
		$prefix . '_post_nav_title_visibility',
		[
			'desktop' => true,
			'tablet' => true,
			'mobile' => false,
		]
	));

	$thumb_size = blocksy_get_theme_mod($prefix . '_post_nav_thumb_size', 'medium');

	$thumb_class = '';

	$thumb_class .= ' ' . blocksy_visibility_classes(blocksy_get_theme_mod(
		$prefix . '_post_nav_thumb_visibility',
		[
			'desktop' => true,
			'tablet' => true,
			'mobile' => true,
		]
	));

	$container_class = 'post-navigation';

	$container_class .= ' ' . blocksy_visibility_classes(blocksy_get_theme_mod(
		$prefix . '_post_nav_visibility',
		[
			'desktop' => true,
			'tablet' => true,
			'mobile' => true,
		]
	));

	$home_page_url = get_home_url();

	$post_slug = get_post_type_object(get_post_type())->labels->singular_name;
	$post_slug = '<span>' . $post_slug . '</span>';

	$next_post_image_output = '';
	$previous_post_image_output = '';

	if ($next_post) {
		$next_title = '';

		$next_title = get_the_title($next_post);

		if (get_post_thumbnail_id($next_post)) {
			$next_post_image_output = blocksy_media(
				[
					'attachment_id' => get_post_thumbnail_id($next_post),
					'post_id' => $next_post->ID,
					'ratio' => '1/1',
					'size' => $thumb_size,
					'class' => $thumb_class,
					'inner_content' => '<svg width="20px" height="15px" viewBox="0 0 20 15" fill="#ffffff"><polygon points="0,7.5 5.5,13 6.4,12.1 2.4,8.1 20,8.1 20,6.9 2.4,6.9 6.4,2.9 5.5,2 "/></svg>',
					'tag_name' => 'figure'
				]
			);
		}
	}

	if ($previous_post) {
		$previous_title = '';

		$previous_title = get_the_title($previous_post);

		if (get_post_thumbnail_id($previous_post)) {
			$previous_post_image_output = blocksy_media(
				[
					'attachment_id' => get_post_thumbnail_id($previous_post),
					'post_id' => $previous_post->ID,
					'ratio' => '1/1',
					'size' => $thumb_size,
					'class' => $thumb_class,
					'inner_content' => '<svg width="20px" height="15px" viewBox="0 0 20 15" fill="#ffffff"><polygon points="14.5,2 13.6,2.9 17.6,6.9 0,6.9 0,8.1 17.6,8.1 13.6,12.1 14.5,13 20,7.5 "/></svg>',
					'tag_name' => 'figure'
				]
			);
		}
	}

	$prefix = blocksy_manager()->screen->get_prefix();

	$deep_link_args = [
		'prefix' => $prefix,
		'suffix' => $prefix . '_has_post_nav'
	];

	ob_start();

	?>

		<nav class="<?php echo esc_attr( $container_class ); ?>" <?php echo blocksy_generic_get_deep_link($deep_link_args); ?>>
			<?php if ($next_post) { ?>
				<a href="<?php echo esc_url(get_permalink($next_post)); ?>" class="nav-item-prev">
					<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $next_post_image_output;
					?>

					<div class="item-content">
						<span class="item-label">
							<?php
								echo wp_kses_post(sprintf(
									apply_filters(
										'blocksy:post-navigation:previous-post:label',
										// translators: post title
										__('Previous %s', 'blocksy')
									),
									$post_slug
								));
							?>
						</span>

						<?php if ( ! empty( $next_title ) ) { ?>
							<span class="<?php echo esc_attr( $title_class ); ?>">
								<?php echo wp_kses_post($next_title); ?>
							</span>
						<?php } ?>
					</div>

				</a>
			<?php } else { ?>
				<div class="nav-item-prev"></div>
			<?php } ?>

			<?php if ($previous_post) { ?>
				<a href="<?php echo esc_url(get_permalink($previous_post)); ?>" class="nav-item-next">
					<div class="item-content">
						<span class="item-label">
							<?php
								echo wp_kses_post(sprintf(
									apply_filters(
										'blocksy:post-navigation:next-post:label',
										// translators: post title
										__('Next %s', 'blocksy')
									),
									$post_slug
								));
							?>
						</span>

						<?php if ( ! empty( $previous_title ) ) { ?>
							<span class="<?php echo esc_attr( $title_class ); ?>">
								<?php echo wp_kses_post($previous_title); ?>
							</span>
						<?php } ?>
					</div>

					<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $previous_post_image_output;
					?>
				</a>
			<?php } else { ?>
				<div class="nav-item-next"></div>
			<?php } ?>

		</nav>

	<?php

	return ob_get_clean();
}
}

/**
 * Output related posts for a single post.
 *
 * @param number $per_page Number of posts to output.
 */
if (! function_exists('blocksy_related_posts')) {
function blocksy_related_posts($location = null) {
	global $post;

	$prefix = blocksy_manager()->screen->get_prefix();
	$per_page = intval(blocksy_get_theme_mod($prefix . '_related_posts_count', 3));

	if (blocksy_get_theme_mod($prefix . '_related_posts_slideshow', 'default') === 'slider') {
		$per_page = intval(blocksy_get_theme_mod($prefix . '_related_posts_slideshow_number_of_items', 6));
	}

	$post_type = get_post_type($post);

	$taxonomy = blocksy_get_theme_mod(
		$prefix . '_related_criteria',
		array_keys(blocksy_get_taxonomies_for_cpt($post_type))[0]
	);


	$all_taxonomy_ids = [];

	if ($taxonomy) {
		$all_taxonomies = get_the_terms($post->ID, $taxonomy);

		if ($all_taxonomies) {
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
		}
	}

	$query_args = array_merge(
		[
			'ignore_sticky_posts' => 0,
			'posts_per_page' => $per_page,
			'post__not_in' => [$post->ID],
			'post_type' => $post_type,
			'fields' => 'ids',
			'no_found_rows' => true,
		],
		! empty($all_taxonomy_ids) ? [
			'tax_query' => [
				[
					'field' => 'id',
					'taxonomy' => $taxonomy,
					'terms' => $all_taxonomy_ids,
				]
			]
		] : []
	);

	$sort = blocksy_get_theme_mod(
		$prefix . '_related_sort',
		'recent'
	);

	if ($sort !== 'default') {
		$orderby_map = [
			'random' => 'rand',
			'recent' => 'post_date',
			'commented' => 'comment_count'
		];

		if (isset($orderby_map[$sort])) {
			$query_args['orderby'] = $orderby_map[$sort];
		}
	}

	$query_args = apply_filters('blocksy:related-posts:query-args', $query_args);

	$query = apply_filters(
		'blocksy:related-posts:query',
		new WP_Query($query_args),
		$query_args,
		$prefix
	);

	$label = do_shortcode(
		apply_filters(
			'blocksy:related-posts:module-label',
			blocksy_get_theme_mod(
				$prefix . '_related_label',
				__( 'Related Posts', 'blocksy')
			)
		)
	);

	$meta_elements = blocksy_get_theme_mod(
		$prefix . '_related_single_meta_elements',
		blocksy_post_meta_defaults([
			[
				'id' => 'post_date',
				'enabled' => true,
			],

			[
				'id' => 'comments',
				'enabled' => true,
			],
		])
	);

	$related_visibility = blocksy_visibility_classes(blocksy_get_theme_mod(
		$prefix . '_related_visibility',
		[
			'desktop' => true,
			'tablet' => true,
			'mobile' => true,
		]
	));

	$class = trim(
		'ct-related-posts-container' . ' ' . $related_visibility
	);

	$boxed_container_class = 'ct-related-posts';

	if ($location !== 'separated') {
		$boxed_container_class = trim(
			$boxed_container_class . ' ' . $related_visibility
		);
	}

	if (! $query->have_posts()) {
		wp_reset_postdata();
		return;
	}

	$label_tag = blocksy_get_theme_mod($prefix . '_related_label_wrapper', 'h3');
	$posts_title_tag = blocksy_get_theme_mod($prefix . '_related_posts_title_tag', 'h4');

	$container_class = 'ct-container';

	if (blocksy_get_theme_mod($prefix . '_related_structure', 'normal') === 'narrow') {
		$container_class = 'ct-container-narrow';
	}

	$container_attributes = [
		'class' => 'ct-related-posts-items',
		'data-layout' => "grid"
	];

	$container_attributes = apply_filters(
		'blocksy:related-posts:container-attributes',
		$container_attributes
	);

	$item_attributes = apply_filters(
		'blocksy:related-posts:item-attributes',
		[]
	);

	$prefix = blocksy_manager()->screen->get_prefix();

	$deep_link_args = [
		'prefix' => $prefix,
		'suffix' => $prefix . '_has_related_posts'
	];

	?>

	<?php if ($location === 'separated') { ?>
	<div class="<?php echo esc_attr($class) ?>" <?php echo blocksy_generic_get_deep_link($deep_link_args); ?>>
		<div class="<?php echo $container_class ?>">
	<?php } ?>

		<div
			class="<?php echo $boxed_container_class ?>"
			<?php echo $location === 'separated' || empty($location) ? '' : blocksy_generic_get_deep_link($deep_link_args); ?>
		>
			<?php do_action('blocksy:single:related_posts:top') ?>

			<?php if (! empty($label)) { ?>
				<?php do_action('blocksy:single:related_posts:title:before') ?>
				<<?php echo $label_tag ?> class="ct-block-title">
					<?php echo wp_kses_post($label) ?>
				</<?php echo $label_tag ?>>
				<?php do_action('blocksy:single:related_posts:title:after') ?>
			<?php } ?>

			<?php do_action('blocksy:single:related_posts:before_loop') ?>

			<div <?php echo blocksy_attr_to_html($container_attributes); ?>>
			<?php while ($query->have_posts()) { ?>
				<?php $query->the_post(); ?>

				<article <?php echo blocksy_attr_to_html($item_attributes); ?> <?php echo blocksy_schema_org_definitions('creative_work:related_posts') ?>>
					<?php
						do_action('blocksy:single:related_posts:card:top');

						if (
							get_post_thumbnail_id()
							&&
							blocksy_get_theme_mod(
								$prefix . '_has_related_featured_image',
								'yes'
							) === 'yes'
						) {
							do_action('blocksy:single:related_posts:featured_image:before');

							echo blocksy_media(
								[
									'attachment_id' => get_post_thumbnail_id(),
									'post_id' => get_the_ID(),
									'ratio' => blocksy_get_theme_mod(
										$prefix . '_related_featured_image_ratio',
										'16/9'
									),
									'tag_name' => 'a',
									'size' => blocksy_get_theme_mod(
										$prefix . '_related_featured_image_size',
										'medium'
									),
									'html_atts' => [
										'href' => esc_url( get_permalink() ),
										'aria-label' => wp_strip_all_tags( get_the_title() ),
										'tabindex' => "-1"
									],

									'lazyload' => blocksy_get_theme_mod(
										'has_lazy_load_related_posts_image',
										'yes'
									) === 'yes'
								]
							);

							do_action('blocksy:single:related_posts:featured_image:after');
						}
					?>

					<?php if (! empty(get_the_title())) { ?>
						<<?php echo $posts_title_tag ?> class="related-entry-title" <?php echo blocksy_schema_org_definitions('name') ?>>
							<a href="<?php echo esc_url( get_permalink() ); ?>" <?php echo blocksy_schema_org_definitions('url') ?> rel="bookmark"><?php the_title(); ?></a>
						</<?php echo $posts_title_tag ?>>
					<?php } ?>

					<?php
						echo blocksy_post_meta($meta_elements, [
							'meta_divider' => 'slash'
						]);

						do_action('blocksy:single:related_posts:card:bottom');
					?>
				</article>
			<?php } ?>
			</div>

			<?php do_action('blocksy:single:related_posts:after_loop') ?>

			<?php do_action('blocksy:single:related_posts:bottom') ?>
		</div>

	<?php if ($location === 'separated') { ?>
		</div>
	</div>
	<?php } ?>

	<?php

	wp_reset_postdata();
}
}

function blocksy_before_current_template() {
	do_action('blocksy:template:before');
}

function blocksy_after_current_template() {
	do_action('blocksy:template:after');
}
