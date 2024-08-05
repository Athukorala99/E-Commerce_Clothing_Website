<?php

$default_logo = blocksy_expand_responsive_value(
	blocksy_default_akg('custom_logo', $atts, blocksy_get_theme_mod('custom_logo', ''))
);

$custom_logo_id = '';

if (! empty($default_logo[$device])) {
	$custom_logo_id = $default_logo[$device];
}

$additional_logos = apply_filters(
	'blocksy:panel-builder:offcanvas-logo:additional-logos',
	[],
	$atts,
	$device,
	$panel_type
);

if ($custom_logo_id) {
	$custom_logo_attr = [
		'class' => 'default-logo',
		'itemprop' => 'logo',
		'loading' => false
	];

	/**
	 * If the logo alt attribute is empty, get the site title and explicitly
	 * pass it to the attributes used by wp_get_attachment_image().
	 */
	$image_alt = get_post_meta(
		$custom_logo_id,
		'_wp_attachment_image_alt',
		true
	);

	if (empty($image_alt)) {
		$custom_logo_attr['alt'] = get_bloginfo('name', 'display');
	}

	$image_logo_html = wp_get_attachment_image(
		$custom_logo_id,
		'full',
		false,
		$custom_logo_attr
	);

	$inline_svg_logos = blocksy_akg('inline_svg_logos', $atts, 'no');

	if ($inline_svg_logos === 'yes') {
		$maybe_file = get_attached_file($custom_logo_id);

		if (
			$maybe_file
			&&
			file_exists($maybe_file)
			&&
			strpos($maybe_file, '.svg') !== false
		) {
			$svg = file_get_contents($maybe_file);

			$parser = new Blocksy_Attributes_Parser();

			unset($custom_logo_attr['loading']);
			$custom_logo_attr['aria-label'] = $custom_logo_attr['alt'];
			$custom_logo_attr['role'] = 'img';
			unset($custom_logo_attr['alt']);

			foreach ($custom_logo_attr as $svg_attr => $svg_attr_value) {
				$svg = $parser->add_attribute_to_images_with_tag(
					$svg,
					$svg_attr,
					$svg_attr_value,
					'svg',
					false
				);
			}

			$image_logo_html = $svg;
		}
	}

	foreach ($additional_logos as $additional_logo) {
		$custom_logo_attr['class'] = $additional_logo['class'];

		$additional_logo_html = wp_get_attachment_image(
			$additional_logo['id'],
			'full',
			false,
			$custom_logo_attr
		);

		if ($inline_svg_logos === 'yes') {
			$maybe_file = get_attached_file($additional_logo['id']);

			if (
				$maybe_file
				&&
				file_exists($maybe_file)
				&&
				strpos($maybe_file, '.svg') !== false
			) {
				$svg = file_get_contents($maybe_file);

				$parser = new Blocksy_Attributes_Parser();

				foreach ($custom_logo_attr as $svg_attr => $svg_attr_value) {
					$svg = $parser->add_attribute_to_images_with_tag(
						$svg,
						$svg_attr,
						$svg_attr_value,
						'svg',
						false
					);
				}

				$additional_logo_html = $svg;
			}
		}

		$image_logo_html = $additional_logo_html . $image_logo_html;
	}
}

$href = esc_url(
	apply_filters('blocksy:header:offcanvas-logo:url', home_url('/'))
);

$old_data_id = $attr['data-id'];
unset($attr['data-id']);

$attr['href'] = $href;
$attr['class'] = 'site-logo-container';
$attr['data-id'] = $old_data_id;
$attr['rel'] = 'home';
$attr['itemprop'] = 'url';

?>

<a <?php echo blocksy_attr_to_html($attr) ?>>
	<?php if ($custom_logo_id) { ?>
		<?php echo $image_logo_html; ?>
	<?php } ?>
</a>

