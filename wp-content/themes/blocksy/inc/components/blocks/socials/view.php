<?php
/**
 * Socials Widget
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package Blocksy
 */

$classes = blocksy_default_akg('className', $atts, '');
$size = blocksy_default_akg('social_icons_size', $atts, 20);
$color = blocksy_default_akg('social_icons_color', $atts, 'default');
$type = blocksy_default_akg('social_type', $atts, 'simple');
$fill = blocksy_default_akg('social_icons_fill', $atts, 'outline');
$link_target = blocksy_default_akg('link_target', $atts, 'no');

if ($link_target === 'yes') {
	$link_target = '_blank';
} else {
	$link_target = false;
}

$link_rel = blocksy_default_akg('link_nofollow', $atts, 'no');

if ($link_rel === 'yes') {
	$link_rel = 'noopener noreferrer nofollow';
} else {
	$link_rel = 'noopener';
}

$colors = [
	'--theme-icon-color' => blocksy_default_akg('customIconColor', $atts, ''),
	'--theme-icon-hover-color' => blocksy_default_akg('customIconHoverColor', $atts, ''),
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

if (isset($atts['initialColor'])) {
	$var = $atts['initialColor'];
	$colors['--theme-icon-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['hoverColor'])) {
	$var = $atts['hoverColor'];
	$colors['--theme-icon-hover-color'] = "var(--wp--preset--color--$var)";
}

$colors_css = '';

foreach ($colors as $key => $value) {
	if (empty($value)) {
		continue;
	}
	$colors_css .= $key . ':' . $value . ';';
}

$style = '';

$icons_size = blocksy_akg('social_icons_size', $atts, '');

if (! empty($icons_size)) {
	$style .= '--theme-icon-size:' . $icons_size . 'px;';
}

$items_spacing = blocksy_akg('items_spacing', $atts, '');

if (! empty($items_spacing)) {
	$style .= '--items-spacing:' . $items_spacing . 'px;';
}

echo '<div class="ct-socials-block ' . $classes . '" style="' . $colors_css . $style . '">';

/**
 * blocksy_social_icons() function is already properly escaped.
 * Escaping it again here would cause SVG icons to not be outputed
 */
echo blocksy_social_icons(
	blocksy_default_akg('socials', $atts, [
		[
			'id' => 'facebook',
			'enabled' => true,
		],

		[
			'id' => 'twitter',
			'enabled' => true,
		],

		[
			'id' => 'instagram',
			'enabled' => true,
		],
	]),
	[
		'size' => '',
		'icons-color' => $color,
		'fill' => $fill,
		'type' => $type,
		'links_target' => $link_target,
		'links_rel' => $link_rel,
	]
);

echo '</div>';
?>
