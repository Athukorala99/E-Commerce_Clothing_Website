<?php

if (! isset($device)) {
	$device = 'desktop';
}

$default_logo = blocksy_expand_responsive_value(
	blocksy_default_akg('custom_logo', $atts, blocksy_get_theme_mod('custom_logo', ''))
);

$transparent_logo = blocksy_expand_responsive_value(
	blocksy_default_akg('transparent_logo', $atts, '')
);

$sticky_logo = blocksy_expand_responsive_value(
	blocksy_default_akg('sticky_logo', $atts, '')
);

$custom_logo_id = '';
$additional_logos = [];

$logo_position = blocksy_expand_responsive_value(
	blocksy_default_akg('logo_position', $atts, '')
);

if (
	isset($has_transparent_header)
	&&
	$has_transparent_header
	&&
	is_array($has_transparent_header)
	&&
	in_array($device, $has_transparent_header)
	&&
	! empty($transparent_logo[$device])
) {
	$custom_logo_id = $transparent_logo[$device];
} else {
	if (! empty($default_logo[$device])) {
		$custom_logo_id = $default_logo[$device];
	}
}

if (
	isset($has_transparent_header)
	&&
	$has_transparent_header
	&&
	is_array($has_transparent_header)
	&&
	in_array($device, $has_transparent_header)
	&&
	! empty($transparent_logo[$device])
) {
	$custom_logo_id = $transparent_logo[$device];
} else {
	if (! empty($default_logo[$device])) {
		$custom_logo_id = $default_logo[$device];
	}
}

if (
	isset($has_sticky_header)
	&&
	is_array($has_sticky_header)
	&&
	is_array($has_sticky_header['devices'])
	&&
	in_array($device, $has_sticky_header['devices'])
	&&
	! empty($sticky_logo[$device])
    &&
	(
		$has_sticky_header['behaviour'] === 'entire_header'
		||
		strpos(
			$has_sticky_header['behaviour'],
			str_replace('-row', '', $row_id)
		) !== false
	)
) {
	$additional_logos[] = [
		'class' => 'sticky-logo',
		'id' => $sticky_logo[$device]
	];
}

$additional_logos = apply_filters(
	'blocksy:panel-builder:logo:additional-logos',
	$additional_logos,
	$atts,
	$device,
	$panel_type
);

$custom_logo_id = apply_filters(
	'blocksy:' . $panel_type . ':logo:image-id',
	$custom_logo_id
);

if ($custom_logo_id) {
	$custom_logo_attr = [
		'class' => 'default-logo',
		'itemprop' => 'logo'
	];

	if ($panel_type === 'header') {
		$custom_logo_attr['loading'] = false;
	}

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

	$aria_label = blocksy_akg('header_logo_aria_label', $atts, '');

	if (! empty($aria_label)) {
		$aria_label = 'aria-label="' . esc_attr($aria_label) . '"';
	}

	/**
	 * If the alt attribute is not empty, there's no need to explicitly pass
	 * it because wp_get_attachment_image() already adds the alt attribute.
	 */
	$logo_html = sprintf(
		'<a href="%1$s" class="site-logo-container" rel="home" itemprop="url" %2$s>%3$s</a>',
		esc_url(
			apply_filters('blocksy:' . $panel_type . ':logo:url', home_url('/'))
		),
		$aria_label,
		$image_logo_html
	);
}

$tagline_class = 'site-description ' . blocksy_visibility_classes(
	blocksy_default_akg('blogdescription_visibility', $atts, [
		'desktop' => true,
		'tablet' => true,
		'mobile' => true,
	])
);

$site_title_class = 'site-title ' . blocksy_visibility_classes(
	blocksy_default_akg('blogname_visibility', $atts, [
		'desktop' => true,
		'tablet' => true,
		'mobile' => true,
	])
);

$tag = 'span';
$tag = apply_filters('blocksy:' . $panel_type . ':logo:tag', $tag);
$wrapper_tag = apply_filters('blocksy:' . $panel_type . ':logo:wrapper-tag', 'div');

$has_site_title = blocksy_akg('has_site_title', $atts, 'yes') === 'yes';
$has_tagline = blocksy_akg('has_tagline', $atts, 'no') === 'yes';

$logo_position = '';

if (
	$custom_logo_id
	&&
	(
		$has_site_title
		||
		$has_tagline
	)
) {
	$logo_position_v = blocksy_expand_responsive_value(
		blocksy_default_akg('logo_position', $atts, 'top')
	);

	$logo_position = 'data-logo="' . $logo_position_v[$device] . '"';
}

$wrapper_class = 'site-branding';

$wrapper_class = trim($wrapper_class . ' ' . blocksy_default_akg(
	'header_logo_class',
	$atts,
	''
));

$wrapper_class = trim($wrapper_class . ' ' . blocksy_visibility_classes(
	blocksy_akg('visibility', $atts, [
		'desktop' => true,
		'tablet' => true,
		'mobile' => true,
	])
));

?>

<<?php echo $wrapper_tag ?>
	class="<?php echo $wrapper_class ?>"
	<?php echo blocksy_attr_to_html($attr) ?>
	<?php echo $logo_position ?>
	<?php echo blocksy_schema_org_definitions('logo', ['condition' => $device === 'desktop']) ?>>

	<?php if ($custom_logo_id) { ?>
		<?php echo $logo_html; ?>
	<?php } ?>

	<?php if ($has_site_title || $has_tagline) { ?>
		<div class="site-title-container">
			<?php if ($has_site_title) { ?>
				<<?php echo $tag ?> class="<?php echo $site_title_class ?>" <?php echo blocksy_schema_org_definitions('name', ['condition' => $device === 'desktop']) ?>>
					<a href="<?php echo esc_url(apply_filters('blocksy:' . $panel_type . ':logo:url', home_url('/'))); ?>" rel="home" <?php echo blocksy_schema_org_definitions('url', ['condition' => $device === 'desktop'])?>>
						<?php
							echo blocksy_translate_dynamic(blocksy_default_akg(
								'blogname',
								$atts,
								get_bloginfo('name')
							), $panel_type . ':' . $section_id . ':logo:blogname');
						?>
					</a>
				</<?php echo $tag ?>>
			<?php } ?>

			<?php if ($has_tagline) { ?>
				<p class="<?php echo $tagline_class ?>" <?php echo blocksy_schema_org_definitions('description', ['condition' => $device === 'desktop']) ?>>
					<?php
						echo blocksy_translate_dynamic(blocksy_default_akg(
							'blogdescription',
							$atts,
							get_bloginfo('description')
						), $panel_type . ':' . $section_id . ':logo:blogdescription');
					?>
				</p>
			<?php } ?>
		</div>
	  <?php } ?>
</<?php echo $wrapper_tag ?>>

