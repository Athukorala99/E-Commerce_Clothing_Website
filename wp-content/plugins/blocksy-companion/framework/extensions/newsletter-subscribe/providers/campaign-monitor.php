<?php

namespace Blocksy\Extensions\NewsletterSubscribe;

class CampaignMonitorProvider extends Provider {
	public function fetch_lists($api_key) {
		if (! $api_key) {
			return 'api_key_invalid';
		}

		$response = wp_remote_get(
			'https://api.createsend.com/api/v3.3/clients.json',
			[
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode($api_key . ':x')
				]
			]
		);

		if (is_wp_error($response)) {
			return 'api_key_invalid';
		}

		if (200 !== wp_remote_retrieve_response_code($response)) {
			return 'api_key_invalid';
		}

		$clients = json_decode(wp_remote_retrieve_body($response), true);

		if (
			! $clients
			||
			empty($clients)
			||
			! isset($clients[0]['ClientID'])
		) {
			return 'api_key_invalid';
		}

		$response = wp_remote_get(
			'https://api.createsend.com/api/v3.3/clients/' . $clients[0]['ClientID'] . '/lists.json',
			[
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode($api_key . ':x')
				]
			]
		);

		if (is_wp_error($response)) {
			return 'api_key_invalid';
		}

		if (200 !== wp_remote_retrieve_response_code($response)) {
			return 'api_key_invalid';
		}

		$lists = json_decode(wp_remote_retrieve_body($response), true);

		if (
			! $lists
			||
			empty($lists)
		) {
			return 'api_key_invalid';
		}

		return array_map(function($list) {
			return [
				'name' => $list['Name'],
				'id' => $list['ListID']
			];
		}, $lists);
	}

	public function get_form_url_and_gdpr_for($maybe_custom_list = null) {
		return [
			'form_url' => '#',
			'has_gdpr_fields' => false,
			'provider' => 'campaignmonitor'
		];
	}

	public function subscribe_form($args = []) {
		$args = wp_parse_args($args, [
			'email' => '',
			'name' => '',
			'group' => ''
		]);

		$settings = $this->get_settings();

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://api.createsend.com/api/v3.3/subscribers/' . $args['group'] . '.json',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode([
				'EmailAddress' => $args['email'],
				'Name' => $args['name'],
				'ConsentToTrack' => 'Yes'
			]),
			CURLOPT_HTTPHEADER => [
				"content-type: application/json"
			],
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD => $settings['api_key'] . ':x'
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return [
				'result' => 'no',
				'error' => $err
			];
		} else {
			$response = json_decode($response, true);

			if (isset($response['Code'])) {
				return [
					'result' => 'no',
					'message' => $response['Message']
				];
			}

			return [
				'result' => 'yes',
				'message' => __('Thank you for subscribing to our newsletter!', 'blocksy-companion')
			];
		}
	}
}

