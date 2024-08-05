<?php

$header_button_user_visibility = blocksy_akg(
	'user_visibility',
	$atts,
	[
		'logged_in' => true,
		'logged_out' => true,
	]
);

if (
	get_current_user_id() && ! $header_button_user_visibility['logged_in']
	||
	! get_current_user_id() && ! $header_button_user_visibility['logged_out']
) {
	return;
}

$class = 'ct-header-cta';

$visibility = blocksy_default_akg('visibility', $atts, [
	'tablet' => true,
	'mobile' => true,
]);

$class .= ' ' . blocksy_visibility_classes($visibility);

$header_button_open = blocksy_akg(
	'header_button_open',
	$atts,
	'link'
);

$type = blocksy_default_akg('header_button_type', $atts, 'type-1');
$size = blocksy_default_akg('header_button_size', $atts, 'small');
$link = do_shortcode(
	blocksy_translate_dynamic(
		blocksy_default_akg('header_button_link', $atts, '#'),
		$panel_type . ':' . $section_id . ':' . $item_id . ':header_button_link'
	)
);

if ($header_button_open === 'popup') {
	$popup_id = blocksy_akg(
		'header_button_select_popup',
		$atts,
		''
	);

	$link = '#';

	if (
		$popup_id
		&&
		class_exists('\Blocksy\Plugin')
		&&
		\Blocksy\Plugin::instance()->premium
		&&
		\Blocksy\Plugin::instance()
			->premium
			->content_blocks
			->is_hook_eligible_for_display($popup_id, [
				'match_conditions' => false
			])
	) {
		$values = blocksy_get_post_options($popup_id);

		if (blocksy_default_akg('is_hook_enabled', $values, 'yes') === 'yes') {
			$link = '#ct-popup-' . $popup_id;
		}
	}
}

$link_attr = [];

$text = do_shortcode(
	blocksy_translate_dynamic(
		blocksy_default_akg(
			'header_button_text',
			$atts,
			__('Download', 'blocksy')
		),
		$panel_type . ':' . $section_id . ':' . $item_id . ':header_button_text'
	)
);

$aria_label = do_shortcode(
	blocksy_translate_dynamic(
		blocksy_default_akg(
			'button_aria_label',
			$atts,
			''
		),
		$panel_type . ':' . $section_id . ':' . $item_id . ':header_button_aria_label'
	)
);

if (empty(trim($aria_label)) && ! empty($text)) {
	$aria_label = $text;
}

$link_attr['data-size'] = $size;

if (! empty($aria_label)) {
	$link_attr['aria-label'] = $aria_label;
}

if (blocksy_default_akg('header_button_target', $atts, 'no') === 'yes') {
	$link_attr['target'] = '_blank';
	$link_attr['rel'] = 'noopener noreferrer';
}

if (blocksy_default_akg('header_button_nofollow', $atts, 'no') === 'yes') {
	if (! isset($link_attr['rel'])) {
		$link_attr['rel'] = '';
	}

	$link_attr['rel'] .= ' nofollow';
	$link_attr['rel'] = trim($link_attr['rel']);
}

if (blocksy_default_akg('header_button_sponsored', $atts, 'no') === 'yes') {
	if (! isset($link_attr['rel'])) {
		$link_attr['rel'] = '';
	}

	$link_attr['rel'] .= ' sponsored';
	$link_attr['rel'] = trim($link_attr['rel']);
}


$button_class = 'ct-button';

if ($type === 'type-2') {
	$button_class = 'ct-button-ghost';
}

$button_class = trim($button_class . ' ' . blocksy_default_akg(
	'header_button_class',
	$atts,
	''
));


$icon = '';

$icon_position = blocksy_akg('icon_position', $atts, 'left');

if (function_exists('blc_get_icon')) {
	$icon = blc_get_icon([
		'icon_descriptor' => blocksy_akg('icon', $atts, [
			'icon' => ''
		]),
		'icon_container' => false,
		'icon_html_atts' => [
			'class' => 'ct-icon',
		]
	]);
}

if ($icon_position === 'left') {
	$text = $icon . $text;
}

if ($icon_position === 'right') {
	$text .= $icon;
}

?>

<div
	class="<?php echo esc_attr(trim($class)) ?>"
	<?php echo blocksy_attr_to_html($attr) ?>>
	<a
		href="<?php echo esc_url(do_shortcode($link)) ?>"
		class="<?php echo $button_class ?>"
		<?php echo blocksy_attr_to_html($link_attr) ?>>
		<?php echo $text ?>
	</a>
</div>
