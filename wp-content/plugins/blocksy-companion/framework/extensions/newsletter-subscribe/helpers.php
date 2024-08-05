<?php

function blc_ext_newsletter_subscribe_form() {
	if (blocksy_get_theme_mod('newsletter_subscribe_single_post_enabled', 'yes') !== 'yes') {
		return '';
	}

	if (
		blocksy_default_akg(
			'disable_subscribe_form',
			blocksy_get_post_options(),
			'no'
		) === 'yes'
	) {
		return '';
	}

	$args = [
		'title' => blocksy_get_theme_mod(
			'newsletter_subscribe_title',
			__('Newsletter Updates', 'blocksy-companion')
		),

		'description' => blocksy_get_theme_mod('newsletter_subscribe_text', __(
			'Enter your email address below and subscribe to our newsletter',
			'blocksy-companion'
		)),

		'button_text' => blocksy_get_theme_mod(
			'newsletter_subscribe_button_text',
			__('Subscribe', 'blocksy-companion')
		),
		'has_name' => blocksy_get_theme_mod('has_newsletter_subscribe_name', 'no'),
		'name_label' => blocksy_get_theme_mod(
			'newsletter_subscribe_name_label',
			__('Your name', 'blocksy-companion')
		),
		'email_label' => blocksy_get_theme_mod(
			'newsletter_subscribe_mail_label',
			__('Your email', 'blocksy-companion')
		)
	];

	$list_id = null;

	if (blocksy_get_theme_mod(
		'newsletter_subscribe_list_id_source',
		'default'
	) === 'custom') {
		$args['list_id'] = blocksy_get_theme_mod('newsletter_subscribe_list_id', '');
	}

	$args['class'] = 'ct-newsletter-subscribe-container ' . blocksy_visibility_classes(
		blocksy_get_theme_mod('newsletter_subscribe_subscribe_visibility', [
			'desktop' => true,
			'tablet' => true,
			'mobile' => false,
		])
	);

	return blc_ext_newsletter_subscribe_output_form($args);
}

function blc_ext_newsletter_subscribe_output_form($args = []) {
	$args = wp_parse_args($args, [
		'has_title' => true,
		'has_description' => true,
		'title' => __(
			'Newsletter Updates', 'blocksy-companion'
		),
		'description' => __(
			'Enter your email address below to subscribe to our newsletter',
			'blocksy-companion'
		),
		'button_text' => __(
			'Subscribe', 'blocksy-companion'
		),
		'has_name' => 'no',
		'name_label' => __('Your name', 'blocksy-companion'),
		'email_label' => __('Your email', 'blocksy-companion'),
		'list_id' => '',
		'class' => ''
	]);

	$has_name = $args['has_name'] === 'yes';

	$manager = \Blocksy\Extensions\NewsletterSubscribe\Provider::get_for_settings();
	$provider_data = $manager->get_form_url_and_gdpr_for($args['list_id']);

	if (! $provider_data) {
		return '';
	}

	if ($provider_data['provider'] !== 'mailchimp') {
		$settings = $manager->get_settings();
		$provider_data['provider'] .= ':' . $settings['list_id'];
	}

	$form_url = $provider_data['form_url'];
	$has_gdpr_fields = $provider_data['has_gdpr_fields'];

	$skip_submit_output = '';

	if ($has_gdpr_fields) {
		$skip_submit_output = 'data-skip-submit';
	}

	$fields_number = '2';

	if ($has_name) {
		$fields_number = '3';
	}

	ob_start();

	?>

	<div class="<?php echo esc_attr(trim($args['class'])) ?>">
		<?php if ($args['has_title']) { ?>
			<h3><?php echo esc_html($args['title']) ?></h3>
		<?php } ?>

		<?php if ($args['has_description'] && ! empty($args['description'])) { ?>
			<p>
				<?php echo $args['description'] ?>
			</p>
		<?php } ?>

		<form target="_blank" action="<?php echo esc_attr($form_url) ?>" method="post"
			data-provider="<?php echo $provider_data['provider'] ?>"
			class="ct-newsletter-subscribe-form"
			data-columns="<?php echo $fields_number ?>"
			<?php echo $skip_submit_output ?>>

			<?php if ($has_name) { ?>
				<input type="text" name="FNAME" placeholder="<?php esc_attr_e($args['name_label'], 'blocksy-companion'); ?>" aria-label="<?php echo __('First name', 'blocksy-companion') ?>">
			<?php } ?>

			<input type="email" name="EMAIL" placeholder="<?php esc_attr_e($args['email_label'], 'blocksy-companion'); ?> *" aria-label="<?php echo __('Email address', 'blocksy-companion') ?>" required>

			<button class="wp-element-button">
				<?php echo esc_html($args['button_text']) ?>
			</button>

			<?php if (function_exists('blocksy_ext_cookies_checkbox')) {
				echo blocksy_ext_cookies_checkbox('subscribe');
			} ?>

			<div class="ct-newsletter-subscribe-message"></div>
		</form>

	</div>

	<?php

	return ob_get_clean();
}
