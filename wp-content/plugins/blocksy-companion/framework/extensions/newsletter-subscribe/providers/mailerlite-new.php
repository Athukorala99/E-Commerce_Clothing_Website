<?php

namespace Blocksy\Extensions\NewsletterSubscribe;

class MailerliteNewProvider extends Provider {
	public function fetch_lists($api_key) {
		if (! $api_key) {
			return 'api_key_invalid';
		}

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://connect.mailerlite.com/api/groups',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"content-type: application/json",
				"Authorization: Bearer " . $api_key
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return 'api_key_invalid';
		}

		$response = json_decode($response, true);

		if (
			isset($response['error'])
			||
			! isset($response['data'])
			||
			! is_array($response['data'])
		) {
			return 'api_key_invalid';
		}

		return array_map(function($list) {
			return [
				'name' => $list['name'],
				'id' => $list['id'],
			];
		}, $response['data']);
	}

	public function get_form_url_and_gdpr_for($maybe_custom_list = null) {
		return [
			'form_url' => '#',
			'has_gdpr_fields' => false,
			'provider' => 'mailerlite'
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
			CURLOPT_URL => 'https://connect.mailerlite.com/api/subscribers',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode([
				'email' => $args['email'],
				'fields' => [
					'name' => $args['name']
				],
				'groups' => [
					$args['group']
				]
			]),
			CURLOPT_HTTPHEADER => array(
				"content-type: application/json",
				"Authorization: Bearer " . $settings['api_key']
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return [
				'result' => 'no',
				'error' => $err
			];
		}

		$response = json_decode($response, true);

		if (isset($response['error'])) {
			return [
				// 'response' => $response,
				'result' => 'no',
				'message' => $response['error']['message']
			];
		}

		return [
			// 'response' => $response,
			'result' => 'yes',
			'message' => __('Thank you for subscribing to our newsletter!', 'blocksy-companion')
		];
	}
}

