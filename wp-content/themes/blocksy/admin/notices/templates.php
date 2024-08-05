<?php

add_action('admin_notices', function () {
	blocksy_output_companion_notice();
});

function blocksy_output_companion_notice() {
	if (! apply_filters(
		'blocksy:admin:display-companion-plugin-notice',
		true
	)) {
		return;
	}

	if (! current_user_can('activate_plugins') ) return;
	if (get_option('dismissed-blocksy_plugin_notice', false)) return;

	$manager = new Blocksy_Plugin_Manager();
	$status = $manager->get_companion_status()['status'];

	if ($status === 'active') return;

	$url = admin_url('themes.php?page=ct-dashboard');
	$plugin_url = admin_url('admin.php?page=ct-dashboard');
	$plugin_link = 'https://creativethemes.com/blocksy/companion/';

	echo '<div class="notice notice-blocksy-plugin">';
	echo '<div class="notice-blocksy-plugin-root" data-url="' . esc_attr($url) . '" data-plugin-url="' . esc_attr($plugin_url) . '" data-plugin-status="' . esc_attr($status) . '" data-link="' . esc_attr($plugin_link) . '">';

	?>

	<div class="ct-blocksy-plugin-inner">
		<span class="ct-notification-icon">
			<svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
				<path fill="#000000" d="M50,25C50,11.2,38.8,0,25,0C11.2,0,0,11.2,0,25c0,13.8,11.2,25,25,25C38.8,50,50,38.8,50,25z"/>
				<path fill="#ffffff" d="M23.4,19.5H29c0.7,0,1.3,0.6,1.3,1.4c0,0.8-0.6,1.4-1.4,1.4h-4.4L23.4,19.5z M34.6,25.1c0.9-1.2,1.4-2.7,1.4-4.2c0-1.6-0.5-3-1.4-4.2c-1.3-1.7-3.3-2.9-5.6-2.9c-0.1,0-0.1,0-0.2,0v0H15.5c-0.4,0-0.6,0.4-0.5,0.7l3.2,7.8h-2.8c-0.4,0-0.6,0.4-0.5,0.7l5.6,13.6h8.2c3.9,0,7.1-3.2,7.1-7.1C36,27.8,35.5,26.4,34.6,25.1C34.6,25.2,34.6,25.1,34.6,25.1zM23.4,28H29c0.7,0,1.3,0.6,1.3,1.4c0,0.8-0.6,1.4-1.4,1.4h-4.4L23.4,28z"/>
		</span>

		<div class="ct-notification-content">
			<h2><?php esc_html_e( 'Thanks for installing Blocksy, you rock!', 'blocksy' ); ?></h2>
			<p>
				<?php esc_html_e( 'We strongly recommend you to activate the', 'blocksy' ); ?>
				<b><?php esc_html_e( 'Blocksy Companion', 'blocksy' ); ?></b> plugin.
				<br>
				<?php esc_html_e( 'This way you will have access to custom extensions, demo templates and many other awesome features', 'blocksy' ); ?>.
			</p>
		</div>
	</div>
	<?php

	echo '</div>';
	echo '</div>';
}

