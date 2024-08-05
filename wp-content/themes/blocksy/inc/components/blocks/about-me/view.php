<?php
/**
 * About me widget
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package Blocksy
 */

$about_source = blocksy_default_akg('about_source', $atts, 'from_wp');
$alignment = blocksy_default_akg('about_alignment', $atts, 'center');
$avatar_size = blocksy_default_akg('about_avatar_size', $atts, 'small');
$avatar_shape = blocksy_default_akg('avatar_shape', $atts, 'rounded');
$classes = blocksy_default_akg('className', $atts, '');

$sizes = [
	'small' => 90,
	'medium' => 140,
	'large' => 200,
];

$user_id = blocksy_akg('wp_user', $atts, null);

$image_output = blocksy_media(
	[
		'attachment_id' => blocksy_default_akg(
			'about_avatar/attachment_id',
			$atts,
			null
		),
		'ratio' => '1/1',
		'tag_name' => 'figure',
		'size' => $avatar_size === 'small' ? 'thumb' : 'medium',
		'html_atts' => [
			'data-size' => $avatar_size,
			'data-shape' => $avatar_shape,
		],
	]
);

$about_name = blocksy_default_akg('about_name', $atts, 'John Doe');
$about_text = do_shortcode(
	blocksy_default_akg(
		'about_text',
		$atts,
		'Lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua tincidunt tortor aliquam.'
	)
);

if ($about_source === 'from_wp') {
	if (!$user_id) {
		require_once dirname(__FILE__) . '/helpers.php';
		$user_id = array_keys(blc_get_user_choices())[0];
	}

	$image_output = blocksy_simple_image(
		blocksy_get_avatar_url([
			'size' => $sizes[$avatar_size] * 2,
			'avatar_entity' => $user_id
		]),
		[
			'tag_name' => 'figure',
			'ratio' => '1/1',
			'html_atts' => [
				'data-size' => $avatar_size,
				'data-shape' => $avatar_shape,
			],
		]
	);

	$about_name = get_the_author_meta('display_name', $user_id);
	$about_text = get_the_author_meta('description', $user_id);
}

$size = blocksy_default_akg('about_social_icons_size', $atts, 'small');
$type = blocksy_default_akg('about_social_type', $atts, 'rounded');
$fill = blocksy_default_akg('about_social_icons_fill', $atts, 'outline');

$colors = [
	'--theme-block-text-color' => blocksy_default_akg('customTextColor', $atts, ''),
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

$style = '';

$icons_size = blocksy_akg('about_social_icons_size', $atts, '');

if (! empty($icons_size)) {
	$style .= '--theme-icon-size:' . $icons_size . 'px;';
}

$items_spacing = blocksy_akg('about_items_spacing', $atts, '');

if (! empty($items_spacing)) {
	$style .= '--items-spacing:' . $items_spacing . 'px;';
}

?>

<div
	class="ct-about-me-block <?php echo $classes; ?>"
	data-alignment="<?php echo esc_attr($alignment); ?>"
	style="<?php echo $colors_css . $style ?>">

	<?php echo $image_output; ?>

	<div class="ct-about-me-name">
		<span><?php echo $about_name; ?></span>

		<?php if ($about_source === 'from_wp') { ?>
			<a href="<?php echo get_author_posts_url($user_id); ?>">
				<?php echo __('View Profile', 'blocksy'); ?>
			</a>
		<?php } ?>
	</div>

	<div class="ct-about-me-text"><?php echo $about_text; ?></div>

	<?php
		echo blocksy_social_icons(
			blocksy_default_akg('about_socials', $atts, [
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
				'size' => $size,
				'type' => $type,
				'fill' => $fill,
				'icons-color' => blocksy_default_akg('about_social_icons_color', $atts, 'default'),
			]
		);
	?>
</div>
