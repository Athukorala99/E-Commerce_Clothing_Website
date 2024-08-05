<?php

// wp_login_form([]);

$redirect_to_url = apply_filters(
	'blocksy:account:modal:login:redirect_to',
	$current_url
);

$forgot_password_inline = apply_filters(
	'blocksy:account:modal:login:forgot-password-inline',
	true
);

$forgot_pass_class = 'ct-forgot-password';

if (! $forgot_password_inline) {
	$forgot_pass_class .= '-static';
}

?>

<form name="loginform" id="loginform" class="login" action="#" method="post">
	<?php do_action('woocommerce_login_form_start'); ?>
	<?php do_action('blocksy:account:modal:login:start'); ?>

	<p>
		<label for="user_login"><?php echo __('Username or Email Address', 'blocksy-companion') ?></label>
		<input type="text" name="log" id="user_login" class="input" value="" size="20" autocomplete="username" autocapitalize="off">
	</p>

	<p>
		<label for="user_pass"><?php echo __('Password', 'blocksy-companion') ?></label>
		<span class="account-password-input">
			<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" autocomplete="current-password" spellcheck="false">
			<span class="show-password-input"></span>
		</span>
	</p>

	<p class="login-remember col-2">
		<span>
			<input name="rememberme" type="checkbox" id="rememberme" class="ct-checkbox" value="forever">
			<label for="rememberme"><?php echo __('Remember Me', 'blocksy-companion') ?></label>
		</span>

		<a href="#" class="<?php echo $forgot_pass_class ?>">
			<?php echo __('Forgot Password?', 'blocksy-companion') ?>
		</a>
	</p>

	<?php
		if (blc_site_has_feature()) {
			if (
				class_exists('NextendSocialLogin', false)
				&&
				! class_exists('NextendSocialLoginPRO', false)
			) {
				\NextendSocialLogin::addLoginFormButtons();
			}
		}

		remove_action("login_form", "wp_login_attempt_focus_start");

		do_action('login_form')
	?>

	<p class="login-submit">
		<button class="ct-button has-text-align-center" name="wp-submit">
			<?php echo __('Log In', 'blocksy-companion') ?>

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

		<input type="hidden" name="redirect_to" value="<?php echo $redirect_to_url ?>">
	</p>

	<?php do_action('blocksy:account:modal:login:end'); ?>
	<?php do_action('woocommerce_login_form_end'); ?>
</form>

