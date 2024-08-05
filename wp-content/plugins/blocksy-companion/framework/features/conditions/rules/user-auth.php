<?php

$user_roles = [];

foreach (get_editable_roles() as $role_id => $role_info) {
	if ($filter === 'maintenance-mode') {
		if ($role_id === 'administrator') {
			continue;
		}
	}

	$user_roles[] = [
		'id' => 'user_role_' . $role_id,
		'title' => $role_info['name']
	];
}

$options = [
	[
		'title' => __('User Auth', 'blocksy-companion'),
		'rules' => [
			[
				'id' => 'user_logged_in',
				'title' => __('User Logged In', 'blocksy-companion')
			],

			[
				'id' => 'user_logged_out',
				'title' => __('User Logged Out', 'blocksy-companion')
			],

			[
				'id' => 'user_role',
				'title' => __('User Role', 'blocksy-companion'),
				'sub_ids' => $user_roles
			]
		]
	],
];
