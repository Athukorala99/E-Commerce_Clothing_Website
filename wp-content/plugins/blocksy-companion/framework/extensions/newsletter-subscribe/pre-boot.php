<?php

class BlocksyExtensionNewsletterSubscribePreBoot {
	public function __construct() {
		add_filter('blocksy-dashboard-scripts-dependencies', function ($s) {
			$s[] = 'blocksy-ext-mailchimp-dashboard-scripts';
			return $s;
		});

		add_action('admin_enqueue_scripts', function () {
			if (! function_exists('get_plugin_data')) {
				require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			}

			$data = get_plugin_data(BLOCKSY__FILE__);

			wp_register_script(
				'blocksy-ext-mailchimp-dashboard-scripts',
				BLOCKSY_URL . 'framework/extensions/newsletter-subscribe/dashboard-static/bundle/main.js',
				[],
				$data['Version'],
				true
			);
		});

		add_action(
			'wp_ajax_blocksy_ext_newsletter_subscribe_maybe_get_lists',
			[$this, 'get_lists']
		);

		add_action(
			'wp_ajax_blocksy_ext_newsletter_subscribe_get_actual_lists',
			[$this, 'get_actual_lists']
		);

		add_action(
			'wp_ajax_blocksy_ext_newsletter_subscribe_maybe_save_credentials',
			[$this, 'save_credentials']
		);
	}

	public function ext_data() {
		$m = new \Blocksy\Extensions\NewsletterSubscribe\MailchimpProvider();
		return $m->get_settings();
	}

	public function save_credentials() {
		$this->maybe_save_credentials();
	}

	public function get_actual_lists() {
		$m = \Blocksy\Extensions\NewsletterSubscribe\Provider::get_for_settings();

		if (! $m->can()) {
			wp_send_json_error();
		}

		$settings = $m->get_settings();

		$lists = $m->fetch_lists($settings['api_key']);

		wp_send_json_success([
			'result' => $lists
		]);
	}

	public function get_lists() {
		$this->maybe_save_credentials(false);
	}

	public function maybe_save_credentials($save = true) {
		$provider = $this->get_provider_from_request();

		$m = \Blocksy\Extensions\NewsletterSubscribe\Provider::get_for_provider($provider);

		if (! $m->can()) {
			wp_send_json_error();
		}

		$lists = $m->fetch_lists($this->get_api_key_from_request());

		if ($save) {
			if (is_array($lists)) {
				$m->set_settings([
					'provider' => $this->get_provider_from_request(),
					'api_key' => $this->get_api_key_from_request(),
					'list_id' => $this->get_list_id_from_request(),
				]);
			}
		}

		wp_send_json_success([
			'result' => $lists
		]);
	}

	public function get_provider_from_request() {
		if (! isset($_POST['provider'])) {
			wp_send_json_error();
		}

		return addslashes($_POST['provider']);
	}

	public function get_api_key_from_request() {
		if (! isset($_POST['api_key'])) {
			wp_send_json_error();
		}

		return addslashes($_POST['api_key']);
	}

	public function get_list_id_from_request() {
		if (! isset($_POST['list_id'])) {
			wp_send_json_error();
		}

		return addslashes($_POST['list_id']);
	}
}
