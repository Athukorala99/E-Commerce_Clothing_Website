<?php

add_action('init', function () {
	add_filter(
		'rest_request_after_callbacks',
		function ($response, $handler, \WP_REST_Request $request) {
			$route = $request->get_route();

			if ($route === '/zionbuilder/v1/options') {
				$data = $response->get_data();

				$data['local_colors'][] = 'var(--theme-palette-color-1)';
				$data['local_colors'][] = 'var(--theme-palette-color-2)';
				$data['local_colors'][] = 'var(--theme-palette-color-3)';
				$data['local_colors'][] = 'var(--theme-palette-color-4)';
				$data['local_colors'][] = 'var(--theme-palette-color-5)';
				$data['local_colors'][] = 'var(--theme-palette-color-6)';
				$data['local_colors'][] = 'var(--theme-palette-color-7)';
				$data['local_colors'][] = 'var(--theme-palette-color-8)';

				$response->set_data($data);
			}

			return $response;
		},
		1000, 3
	);
});