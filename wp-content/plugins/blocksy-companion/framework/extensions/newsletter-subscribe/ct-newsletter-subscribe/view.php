<?php
/**
 * Newsletter Subscribe widget
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package Blocksy
 */

// Widget title
$title = blocksy_default_akg(
	'title',
	$atts,
	__('Newsletter', 'blocksy-companion')
);

// Button text
$button_text = blocksy_default_akg(
	'newsletter_subscribe_button_text',
	$atts,
	__('Subscribe', 'blocksy-companion')
);

$style = '';

$newsletter_subscribe_height = blocksy_default_akg('newsletter_subscribe_height', $atts, '');
$newsletter_subscribe_gap = blocksy_default_akg('newsletter_subscribe_gap', $atts, '');

if (! empty($newsletter_subscribe_height)) {
	$style .= '--theme-form-field-height:' . $newsletter_subscribe_height . 'px;';
}

if (! empty($newsletter_subscribe_gap)) {
	$style .= '--theme-form-field-gap:' . $newsletter_subscribe_gap . 'px;';
}

if (isset($atts['style']['border']['radius'])) {
	if (
		gettype($atts['style']['border']['radius']) === 'string'
		&&
		! empty(gettype($atts['style']['border']['radius']))
	) {
		$style .= '--theme-form-field-border-radius:' . $atts['style']['border']['radius'] . ';';
	} else if (
		gettype($atts['style']['border']['radius']) === 'array'
		&&
		! empty($atts['style']['border']['radius'])
	) {
		$style .= '--theme-form-field-border-radius:' . $atts['style']['border']['radius']['topLeft'] . $atts['style']['border']['radius']['topRight'] . $atts['style']['border']['radius']['bottomLeft'] . $atts['style']['border']['radius']['bottomRight'] . ';';
	}

	unset($atts['style']['border']);
}

$colors = [
	'--theme-form-text-initial-color' => blocksy_default_akg('customInputFontColor', $atts, ''),
	'--theme-form-text-focus-color' => blocksy_default_akg('customInputFontFocusColor', $atts, ''),
	'--theme-form-field-border-initial-color' => blocksy_default_akg('customInputBorderColor', $atts, ''),
	'--theme-form-field-border-focus-color' => blocksy_default_akg('customInputBorderColorFocus', $atts, ''),
	'--theme-form-field-background-initial-color' => blocksy_default_akg('customInputBackgroundColor', $atts, ''),
	'--theme-form-field-background-focus-color' => blocksy_default_akg('customInputBackgroundColorFocus', $atts, ''),
];

if (isset($atts['inputFontColor'])) {
	$var = $atts['inputFontColor'];
	$colors['--theme-form-text-initial-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['inputFontColorFocus'])) {
	$var = $atts['inputFontColorFocus'];
	$colors['--theme-form-text-focus-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['inputBorderColor'])) {
	$var = $atts['inputBorderColor'];
	$colors['--theme-form-field-border-initial-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['inputBorderColorFocus'])) {
	$var = $atts['inputBorderColorFocus'];
	$colors['--theme-form-field-border-focus-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['inputBackgroundColor'])) {
	$var = $atts['inputBackgroundColor'];
	$colors['--theme-form-field-background-initial-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['inputBackgroundColorFocus'])) {
	$var = $atts['inputBackgroundColorFocus'];
	$colors['--theme-form-field-background-focus-color'] = "var(--wp--preset--color--$var)";
}

$colors_css = '';

foreach ($colors as $key => $value) {
	if (empty($value)) {
		continue;
	}
	$colors_css .= $key . ':' . $value . ';';
}

// Form name
$has_name =
	blocksy_default_akg('has_newsletter_subscribe_name', $atts, 'no') === 'yes';

$list_id = null;

if (
	blocksy_default_akg(
		'newsletter_subscribe_list_id_source',
		$atts,
		'default'
	) === 'custom'
) {
	$list_id = blocksy_default_akg('newsletter_subscribe_list_id', $atts, '');
}

$manager = \Blocksy\Extensions\NewsletterSubscribe\Provider::get_for_settings();

// Button value
$provider_data = $manager->get_form_url_and_gdpr_for($list_id);

if (!$provider_data) {
	return;
}

if ($provider_data['provider'] === 'mailerlite') {
	$settings = $manager->get_settings();
	$provider_data['provider'] .= ':' . $settings['list_id'];
}

$form_url = $provider_data['form_url'];
$has_gdpr_fields = $provider_data['has_gdpr_fields'];

$name_label = blocksy_default_akg(
	'newsletter_subscribe_name_label',
	$atts,
	__('Your name', 'blocksy-companion')
);
$email_label = blocksy_default_akg(
	'newsletter_subscribe_mail_label',
	$atts,
	__('Your email *', 'blocksy-companion')
);

$view_type = blocksy_default_akg(
	'newsletter_subscribe_view_type',
	$atts,
	'inline'
);

$fields_number = '2';

if ($has_name) {
	$fields_number = '3';
}

echo '<div class="ct-newsletter-subscribe-block">';

$form_attrs = [
	'action' => esc_attr($form_url),
	'method' => 'post',
	'target' => '_blank',
	'class' => 'ct-newsletter-subscribe-form',
	'data-provider' => $provider_data['provider'],
];

if ($view_type === 'inline') {
	$form_attrs['data-columns'] = $fields_number;
}

$skip_submit_output = '';

if ($has_gdpr_fields) {
	$form_attrs['data-skip-submit'] = '';
}

if (! empty($style) || ! empty($colors_css)) {
	$form_attrs['style'] = $style . $colors_css;
}

$button_colors = [];

$button_colors = array_merge(
	$button_colors,
	[
		'--theme-button-text-initial-color' => blocksy_default_akg('customInputIconColor', $atts, ''),
		'--theme-button-text-hover-color' => blocksy_default_akg('customInputIconColorFocus', $atts, ''),
		'--theme-button-background-initial-color' => blocksy_default_akg('customButtonBackgroundColor', $atts, ''),
		'--theme-button-background-hover-color' => blocksy_default_akg('customButtonBackgroundColorHover', $atts, ''),
	]
);

if (isset($atts['inputIconColor'])) {
	$var = $atts['inputIconColor'];
	$button_colors['--theme-button-text-initial-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['inputIconColorFocus'])) {
	$var = $atts['inputIconColorFocus'];
	$button_colors['--theme-button-text-hover-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['buttonBackgroundColor'])) {
	$var = $atts['buttonBackgroundColor'];
	$button_colors['--theme-button-background-initial-color'] = "var(--wp--preset--color--$var)";
}

if (isset($atts['buttonBackgroundColorHover'])) {
	$var = $atts['buttonBackgroundColorHover'];
	$button_colors['--theme-button-background-hover-color'] = "var(--wp--preset--color--$var)";
}

$button_colors_css = '';

foreach ($button_colors as $key => $value) {
	if (empty($value)) {
		continue;
	}
	$button_colors_css .= $key . ':' . $value . ';';
}

?>
	<form <?php echo blocksy_attr_to_html($form_attrs); ?>>

		<?php if ($has_name) { ?>
			<input
				type="text"
				name="FNAME"
				placeholder="<?php esc_attr_e($name_label, 'blocksy-companion'); ?>"
				title="<?php echo __('Name', 'blocksy-companion'); ?>">
		<?php } ?>

		<input
			type="email"
			name="EMAIL"
			placeholder="<?php esc_attr_e($email_label, 'blocksy-companion'); ?>"
			title="<?php echo __('Email', 'blocksy-companion'); ?>"
			required>

		<button class="wp-element-button" <?php echo ! empty($button_colors_css) ? 'style="' . $button_colors_css . '"' : '' ?>>
			<?php echo esc_html($button_text); ?>
		</button>

		<?php if (function_exists('blocksy_ext_cookies_checkbox')) {
  	echo blocksy_ext_cookies_checkbox('newsletter-subscribe');
  } ?>

		<div class="ct-newsletter-subscribe-message"></div>
	</form>

</div>
