<?php

namespace Blocksy\Extensions\NewsletterSubscribe;

class DemoProvider extends Provider {
	public function __construct() {
	}

	public function fetch_lists($api_key) {
		if (! $api_key) {
			return 'api_key_invalid';
		}

		return [
			[
				'name' => __('Demo List', 'blocksy-companion'),
				'id' => 'demolist'
			]
		];
	}

	public function get_form_url_and_gdpr_for($maybe_custom_list = null) {
		return [
			'form_url' => '#',
			'has_gdpr_fields' => false,
			'provider' => 'demo'
		];
	}

	public function subscribe_form($args = []) {
		$args = wp_parse_args($args, [
			'email' => '',
			'name' => '',
			'group' => ''
		]);

		return [
			// 'response' => $response,
			'result' => 'yes',
			'message' => __('Thank you for subscribing to our newsletter!', 'blocksy-companion')
		];
	}
}


