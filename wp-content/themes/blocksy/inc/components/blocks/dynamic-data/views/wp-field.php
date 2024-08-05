<?php

$value_fallback = blocksy_akg('fallback', $attributes, '');

$value = '';

$has_fallback = false;

if ($field === 'wp:archive_title') {
	$archive_title_renderer = new \Blocksy\ArchiveTitleRenderer([
		'has_label' => false
	]);

	add_filter(
		'get_the_archive_title',
		[$archive_title_renderer, 'render_title'],
		10, 3
	);

	$value = get_the_archive_title();

	remove_filter(
		'get_the_archive_title',
		[$archive_title_renderer, 'render_title'],
		10, 3
	);
}

if ($field === 'wp:archive_description') {
	$value = get_the_archive_description();
}

if ($field === 'wp:title') {
	$value = get_the_title();

	if (blocksy_akg('has_field_link', $attributes, 'no') === 'yes') {
		$value = sprintf(
			'<a href="%s" %s %s>%s</a>',

			get_permalink(),

			blocksy_akg('has_field_link_new_tab', $attributes, 'no') === 'yes'
			? 'target="_blank"'
			: '',

			! empty(blocksy_akg('has_field_link_rel', $attributes, ''))
			? 'rel="' . blocksy_akg('has_field_link_rel', $attributes, '') . '"'
			: '',

			$value
		);
	}
}

if ($field === 'wp:excerpt') {
	$value = blocksy_entry_excerpt([
		'length' => intval(blocksy_akg('excerpt_length', $attributes, 40)),
		'skip_container' => true
	]);

	if (empty($value) && ! empty($value_fallback)) {
		$has_fallback = true;
		$value = do_shortcode($value_fallback);
	}
}

if ($field === 'wp:date') {
	$date_format = get_option('date_format', 'F j, Y');

	if (blocksy_akg('default_format', $attributes, 'published') === 'no') {
		$date_format = blocksy_akg('date_format', $attributes, 'F j, Y');

		if ($date_format === 'custom') {
			$date_format = blocksy_akg('custom_date_format', $attributes, 'F j, Y');
		}
	}

	$value = get_the_date($date_format);

	if (blocksy_akg('date_type', $attributes, 'published') === 'modified') {
		$value = get_the_modified_date($date_format);
	}

	if (blocksy_akg('has_field_link', $attributes, 'no') === 'yes') {
		$value = sprintf(
			'<a href="%s" %s %s>%s</a>',

			get_permalink(),

			blocksy_akg('has_field_link_new_tab', $attributes, 'no') === 'yes'
			? 'target="_blank"'
			: '',

			! empty(blocksy_akg('has_field_link_rel', $attributes, ''))
			? 'rel="' . blocksy_akg('has_field_link_rel', $attributes, '') . '"'
			: '',

			$value
		);
	}
}

if ($field === 'wp:comments') {
	$value = get_comments_number_text(
		blocksy_akg('zero_text', $attributes, __('No comments', 'blocksy')),
		blocksy_akg('single_text', $attributes, __('One comment', 'blocksy')),
		blocksy_akg('multiple_text', $attributes, __('% comments', 'blocksy'))
	);

	if (blocksy_akg('has_field_link', $attributes, 'no') === 'yes') {
		$value = blocksy_html_tag(
			'a',
			array_merge(
				[
					'href' => get_comments_link()
				],

				blocksy_akg('has_field_link_new_tab', $attributes, 'no') === 'yes' ?
				[
					'target' => '_blank'
				] : [],

				! empty(blocksy_akg('has_field_link_rel', $attributes, '')) ? [
					'rel' => blocksy_akg('has_field_link_rel', $attributes, '')
				] : []
			),
			$value
		);
	}
}

if ($field === 'wp:author') {
	$author_id = get_post_field('post_author', get_the_ID());
	$author_field = blocksy_akg('author_field', $attributes, 'email');

	$overide_link = '';

	if ($author_field === 'email') {
		$value = get_the_author_meta('user_email', $author_id);

		if (! empty($value)) {
			$overide_link = 'mailto:' . $value;
		}
	}

	if ($author_field === 'nicename') {
		$value = get_the_author_meta('nickname', $author_id);
	}

	if ($author_field === 'display_name') {
		$value = get_the_author_meta('nickname', $author_id);
	}

	if ($author_field === 'first_name') {
		$value = get_the_author_meta('first_name', $author_id);
	}

	if ($author_field === 'last_name') {
		$value = get_the_author_meta('last_name', $author_id);
	}

	if ($author_field === 'description') {
		$value = get_the_author_meta('description', $author_id);
	}

	if (empty($value) && ! empty($value_fallback)) {
		$has_fallback = true;
		$value = do_shortcode($value_fallback);
	}

	if (
		! empty($value)
		&&
		blocksy_akg('has_field_link', $attributes, 'no') === 'yes'
	) {
		$value = blocksy_html_tag(
			'a',
			array_merge(
				[
					'href' => ! empty($overide_link) ? $overide_link : get_author_posts_url($author_id)
				],

				blocksy_akg('has_field_link_new_tab', $attributes, 'no') === 'yes' ? [
					'target' => '_blank'
				] : [],

				! empty(blocksy_akg('has_field_link_rel', $attributes, '')) ? [
					'rel' => blocksy_akg('has_field_link_rel', $attributes, '')
				] : []
			),
			$value
		);
	}
}

if ($field === 'wp:terms') {
	$taxonomy = blocksy_akg('taxonomy', $attributes, '');

	if (empty($taxonomy)) {
		$internal_taxonomies = get_object_taxonomies([
			'post_type' => get_post_type(),
			'public' => true,
			'show_in_nav_menus' => true,
		]);

		$taxonomies = [];

		foreach ($internal_taxonomies as $tax) {
			$taxonomy_object = get_taxonomy($tax);

			if (! $taxonomy_object->public) {
				continue;
			}

			$taxonomies[] = $tax;
		}

		if (! empty($taxonomies)) {
			$taxonomy = $taxonomies[0];
		}
	}

	$value = '';

	if (! empty($taxonomy)) {
		$terms = get_the_terms(get_the_ID(), $taxonomy);

		if (! empty($terms)) {
			$terms = array_map(function ($term) use ($taxonomy, $attributes) {
				$tagName = 'span';

				$attrs = [];

				$classes = [];

				$termAccentColor = blocksy_akg('termAccentColor', $attributes, 'yes');

				if ($termAccentColor === 'yes') {
					$classes[] = 'ct-term-' . $term->term_id;
				}

				$termClass = blocksy_akg('termClass', $attributes, '');

				if (! empty($termClass)) {
                    $classes[] = $termClass;
				}

				if (! empty($classes)) {
					$attrs['class'] = implode(' ', $classes);
				}

				if (blocksy_akg('has_field_link', $attributes, 'no') === 'yes') {
					$tagName = 'a';

					$attrs['href'] = get_term_link($term, $taxonomy);

					if (blocksy_akg('has_field_link_new_tab', $attributes, 'no') === 'yes') {
						$attrs['target'] = '_blank';
					}

					if (! empty(blocksy_akg('has_field_link_rel', $attributes, ''))) {
						$attrs['rel'] = blocksy_akg('has_field_link_rel', $attributes, '');
					}
				}

				return '<' . $tagName . ' ' . trim(blocksy_attr_to_html($attrs)) . '>' . $term->name . '</' . $tagName . '>';
			}, $terms);

			$value = implode(
				preg_replace('/ /', "\u{00A0}", blocksy_akg('separator', $attributes, ', ')),
				$terms
			);
		}
	}

	if (empty($value) && ! empty($value_fallback)) {
		$has_fallback = true;
		$value = do_shortcode($value_fallback);
	}
}

if (empty(trim($value))) {
	return;
}

$value_after = blocksy_akg('after', $attributes, '');
$value_before = blocksy_akg('before', $attributes, '');

if (! empty($value_after) && ! $has_fallback) {
	$value .= $value_after;
}

if (! empty($value_before) && ! $has_fallback) {
	$value = $value_before . $value;
}

$tagName = blocksy_akg('tagName', $attributes, 'div');

$classes = ['ct-dynamic-data'];

if (! empty($attributes['align'])) {
	$classes[] = 'has-text-align-' . $attributes['align'];
}

$wrapper_attr['class'] = implode(' ', $classes);

$border_result = get_block_core_post_featured_image_border_attributes(
	$attributes
);

if (! empty($border_result['class'])) {
	$wrapper_attr['class'] .= ' ' . $border_result['class'];
}

if (! empty($border_result['style'])) {
	$wrapper_attr['style'] = $border_result['style'];
}

$wrapper_attr = get_block_wrapper_attributes($wrapper_attr);

echo blocksy_html_tag(
	$tagName,
	$wrapper_attr,
	$value
);
