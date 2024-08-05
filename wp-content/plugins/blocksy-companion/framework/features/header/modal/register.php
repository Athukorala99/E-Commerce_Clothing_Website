<?php

$has_username = true;
$has_password = false;

if (
	\Blocksy\Plugin::instance()->account_auth->get_registration_strategy() === 'woocommerce'
	&&
	'no' !== get_option('woocommerce_registration_generate_username')
) {
	$has_username = false;
}

$class = "register";
$password_class = 'account-password-input';

if (
	\Blocksy\Plugin::instance()->account_auth->get_registration_strategy() === 'woocommerce'
) {
	if ('no' === get_option('woocommerce_registration_generate_password')) {
		$has_password = true;
	}

    $class .= " woocommerce-form-register";
    $password_class .= " password-input";
}


?>

<form name="registerform" id="registerform" class="<?php echo $class ?>" action="#" method="post" novalidate="novalidate">
	<?php
		if (function_exists('WC')) {
			do_action('woocommerce_register_form_start');
		}
	?>
	<?php do_action('blocksy:account:modal:register:start'); ?>

	<?php if ($has_username) { ?>
		<p>
			<label for="user_login_register"><?php echo __('Username', 'blocksy-companion') ?></label>
			<input type="text" name="user_login" id="user_login_register" class="input" value="" size="20" autocomplete="username" autocapitalize="off">
		</p>
	<?php } ?>

	<p>
		<label for="ct_user_email"><?php echo __('Email', 'blocksy-companion') ?></label>
		<input type="email" name="user_email" id="ct_user_email" class="input" value="" size="20" autocomplete="email">
	</p>

	<?php if ($has_password) { ?>
		<p>
			<label for="user_pass_register"><?php echo __('Password', 'blocksy-companion') ?></label>
			<span class="<?php echo $password_class ?>">
				<input type="password" name="user_pass" id="user_pass_register" class="input" value="" size="20" autocapitalize="off" autocomplete="new-password">
			</span>
		</p>
	<?php } ?>

	<?php if (\Blocksy\Plugin::instance()->account_auth->get_registration_strategy() === 'woocommerce' && ! $has_password) { ?>
		<p>
			<?php echo __('A link to set a new password will be sent to your email address.', 'blocksy-companion') ?>
		</p>
	<?php } ?>

	<?php
	if (blc_site_has_feature()) {
		if (
			class_exists('NextendSocialLogin')
			&&
			! class_exists('NextendSocialLoginPRO', false)
		) {
			\NextendSocialLogin::addRegisterFormButtons();
		}
	}

	if (class_exists('LoginNocaptcha')) {
		remove_action(
			'woocommerce_register_form',
			array('LoginNocaptcha', 'nocaptcha_form')
		);
	}

	do_action('register_form');
	if (function_exists('WC')) {
		do_action('woocommerce_register_form');
	}
	?>

	<?php if (!\Blocksy\Plugin::instance()->account_auth->get_registration_strategy() === 'woocommerce') { ?>
		<p id="reg_passmail">
			<?php echo __('Registration confirmation will be emailed to you.', 'blocksy-companion') ?>
		</p>
	<?php } ?>

	<p>
		<button class="ct-button has-text-align-center" name="wp-submit">
			<?php echo __('Register', 'blocksy-companion') ?>

			<svg class="ct-button-loader" width="16" height="16" viewBox="0 0 24 24">
				<circle cx="12" cy="12" r="10" opacity="0.2" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="2.5"/>

				<path d="m12,2c5.52,0,10,4.48,10,10" fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2.5">
					<animateTransform
						attributeName="transform"
						attributeType="XML"
						type="rotate"
						dur="0.6s"
						from="0 12 12"
						to="360 12 12"
						repeatCount="indefinite"
					/>
				</path>
			</svg>
		</button>

		<?php
			if (
				\Blocksy\Plugin::instance()->account_auth->get_registration_strategy() === 'woocommerce'
				&&
				function_exists('dokan')
			) {
				echo blocksy_html_tag(
					'input',
					[
						'type' => 'hidden',
						'name' => 'redirect_to',
						'value' => apply_filters(
							'dokan_seller_setup_wizard_url',
							site_url('?page=dokan-seller-setup')
						)
					]
				);
			}
		?>
	</p>

	<?php do_action('blocksy:account:modal:register:end'); ?>
	<?php
		if (function_exists('WC')) {
			do_action('woocommerce_register_form_end');
		}
	?>
	<?php wp_nonce_field('blocksy-register', 'blocksy-register-nonce'); ?>
</form>
