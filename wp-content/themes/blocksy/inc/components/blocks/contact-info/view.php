<?php
/**
 * Contact Info widget
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package Blocksy
 */

$classes = blocksy_default_akg('className', $atts, '');
$contact_information = blocksy_default_akg('contact_information', $atts, [
	[
		'id' => 'address',
		'enabled' => true,
		'title' => __('Address:', 'blocksy'),
		'content' => 'Street Name, NY 38954',
		'link' => '',
	],

	[
		'id' => 'phone',
		'enabled' => true,
		'title' => __('Phone:', 'blocksy'),
		'content' => '578-393-4937',
		'link' => 'tel:578-393-4937',
	],

	[
		'id' => 'mobile',
		'enabled' => true,
		'title' => __('Mobile:', 'blocksy'),
		'content' => '578-393-4937',
		'link' => 'tel:578-393-4937',
	],
]);

$classes = ['ct-contact-info-block', $classes];

$type = blocksy_akg('contacts_icon_shape', $atts, 'rounded');
$fill = blocksy_akg('contacts_icon_fill_type', $atts, 'outline');

$content = blc_get_contacts_output(
	[
		'data' => $contact_information,
		'link_target' => blocksy_default_akg(
			'contact_link_target',
			$atts,
			'no'
		),
		'type' => $type,
		'fill' => $fill,
		'link_icons' => blocksy_akg('link_icons', $atts, 'no'),
	]
);

$colors = [
	'--theme-block-text-color' => blocksy_default_akg('customTextColor', $atts, ''),
	'--theme-link-initial-color' => blocksy_default_akg('customTextInitialColor', $atts, ''),
	'--theme-link-hover-color' => blocksy_default_akg('customTextHoverColor', $atts, ''),
	'--theme-icon-color' => blocksy_default_akg('customIconsColor', $atts, ''),
	'--theme-icon-hover-color' => blocksy_default_akg('customIconsHoverColor', $atts, ''),
];

if ($type !== 'simple') {
	$base_color = blocksy_default_akg('customBorderColor', $atts, 'rgba(218, 222, 228, 0.5)');
	$hover_color = blocksy_default_akg('customBorderHoverColor', $atts, 'rgba(218, 222, 228, 0.7)');

	if (isset($atts['borderColor'])) {
		$var = $atts['borderColor'];
		$base_color = "var(--wp--preset--color--$var)";
	}

	if (isset($atts['borderHoverColor'])) {
		$var = $atts['borderHoverColor'];
		$hover_color = "var(--wp--preset--color--$var)";
	}

	if ($fill === 'solid') {
		$base_color = blocksy_default_akg('customBackgroundColor', $atts, 'rgba(218, 222, 228, 0.5)');
		$hover_color = blocksy_default_akg('customBackgroundHoverColor', $atts, 'rgba(218, 222, 228, 0.7)');

		if (isset($atts['backgroundColor'])) {
			$var = $atts['backgroundColor'];
			$base_color = "var(--wp--preset--color--$var)";
		}

		if (isset($atts['backgroundHoverColor'])) {
			$var = $atts['backgroundHoverColor'];
			$hover_color = "var(--wp--preset--color--$var)";
		}
	}

	$colors = array_merge(
		$colors,
		[
			'--background-color' => $base_color,
			'--background-hover-color' => $hover_color
		]
	);
}

if (isset($atts['textColor'])) {
	$var = $atts['textColor'];
	$colors['--theme-block-text-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['textInitialColor'])) {
	$var = $atts['textInitialColor'];
	$colors['--theme-link-initial-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['textHoverColor'])) {
	$var = $atts['textHoverColor'];
	$colors['--theme-link-hover-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['iconsColor'])) {
	$var = $atts['iconsColor'];
	$colors['--theme-icon-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['iconsHoverColor'])) {
	$var = $atts['iconsHoverColor'];
	$colors['--theme-icon-hover-color'] = "var(--wp--preset--color--$var)";
}

$colors_css = '';

foreach ($colors as $key => $value) {
	if (empty($value)) {
		continue;
	}

	$colors_css .= $key . ':' . $value . ';';
}

$wp_styles_css = '';

if (isset($atts['style'])) {
	$wp_styles = wp_style_engine_get_styles($atts['style']);
	$wp_styles_css = blocksy_akg('css', $wp_styles, '');
}

$style = '';

$icons_size = blocksy_akg('contacts_icons_size', $atts, 20);

if (! empty($icons_size)) {
	$style .= '--theme-icon-size:' . $icons_size . 'px;';
}

$items_spacing = blocksy_akg('contacts_items_spacing', $atts, '');

if (! empty($items_spacing)) {
	$style .= '--items-spacing:' . $items_spacing . 'px;';
}

if (blocksy_default_akg('contacts_items_direction', $atts, 'column') === 'column') {
	$style .= '--items-direction:' . blocksy_default_akg('contacts_items_direction', $atts, 'column') . ';';
}

echo blocksy_html_tag(
	'div',
	[
		'class' => implode(' ', $classes),
		'style' => $style . $colors_css . $wp_styles_css
	],
	$content
);

