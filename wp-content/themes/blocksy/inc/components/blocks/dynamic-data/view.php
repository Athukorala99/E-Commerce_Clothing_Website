<?php

$field = blocksy_akg('field', $attributes, 'wp:title');

if (strpos($field, 'woo:') === 0) {
	echo blocksy_render_view(
		dirname(__FILE__) . '/views/woo-field.php',
		[
			'attributes' => $attributes,
			'field' => $field
		]
	);

	return;
}

if (strpos($field, 'wp:') === 0) {
	if ($field !== 'wp:featured_image' && $field !== 'wp:author_avatar') {
		echo blocksy_render_view(
			dirname(__FILE__) . '/views/wp-field.php',
			[
				'attributes' => $attributes,
				'field' => $field
			]
		);
	}

	if ($field === 'wp:featured_image') {
		echo blocksy_render_view(
			dirname(__FILE__) . '/views/image-field.php',
			[
				'attributes' => $attributes,
				'field' => $field
			]
		);
	}

	if ($field === 'wp:author_avatar') {
		echo blocksy_render_view(
			dirname(__FILE__) . '/views/avatar-field.php',
			[
				'attributes' => $attributes,
				'field' => $field
			]
		);
	}

	return;
}

if (! function_exists('blc_get_ext')) {
	return;
}

$field_descriptor = explode(':', $field);

$field_render = blc_get_ext('post-types-extra')
	->dynamic_data
	->get_field_to_render([
		'id' => $field_descriptor[0] . '_field',
		'field' => $field_descriptor[1]
	], [
		'allow_images' => true
	]);

if (! $field_render) {
	return;
}

if (
	is_array($field_render['value'])
	&&
	$field_render['value']['type'] === 'image'
) {
	echo blocksy_render_view(
		dirname(__FILE__) . '/views/image-field.php',
		[
			'attributes' => $attributes,
			'field' => $field,
			'value' => $field_render['value']['value']
		]
	);

	return;
}

echo blocksy_render_view(
	dirname(__FILE__) . '/views/custom-text-field.php',
	[
		'attributes' => $attributes,
		'value' => $field_render['value']
	]
);
