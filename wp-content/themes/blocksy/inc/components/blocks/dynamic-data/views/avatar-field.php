<?php

$avatar_size = blocksy_akg('avatar_size', $attributes, 96);

$img_attr = [
	'style' => ''
];

$img_class = '';

$border_result = get_block_core_post_featured_image_border_attributes(
	$attributes
);

if (! empty($border_result['class'])) {
	$img_class = $border_result['class'];
}

if (! empty($border_result['style'])) {
	$img_attr['style'] .= $border_result['style'];
}

$author_id = blocksy_get_author_id();

$value = get_avatar(
	$author_id,
	$avatar_size,
	'',
	sprintf(
		__('%s Avatar', 'blocksy'),
		get_the_author_meta('display_name', $author_id)
	),
	[
		'extra_attr' => blocksy_attr_to_html($img_attr),
		'class' => $img_class
	]
);

$classes = [
	// 'wp-block-image'
];

$styles = [];

if (! empty($attributes['imageAlign'])) {
	$classes[] = 'align' . $attributes['imageAlign'];
}

if (! empty($attributes['className'])) {
	$classes[] = $attributes['className'];
}

$wrapper_attr = [
	'class' => 'ct-dynamic-data'
];

$wrapper_attr['class'] .= ' ' . implode(' ', $classes);

$wrapper_attr['class'] = trim($wrapper_attr['class']);

$has_field_link = blocksy_akg('has_field_link', $attributes, 'no');

if ($has_field_link === 'yes') {
	$link_attr = [
		'href' => get_author_posts_url($author_id),
	];

	$has_field_link_new_tab = blocksy_akg('has_field_link_new_tab', $attributes, '_self');
	$has_field_link_rel = blocksy_akg('has_field_link_rel', $attributes, '');

	if ($has_field_link_new_tab !== '_self') {
		$link_attr['target'] = $has_field_link_new_tab;
	}

	if (! empty($has_field_link_rel)) {
		$link_attr['rel'] = $has_field_link_rel;
	}

	$value = blocksy_html_tag('a', $link_attr, $value);
}

$wrapper_attr['style'] = implode(' ', $styles);

if (empty($value)) {
	return;
}

$wrapper_attr = get_block_wrapper_attributes($wrapper_attr);

echo blocksy_html_tag('figure', $wrapper_attr, $value);

