<?php

$config = [
	'name' => __('Mobile Menu', 'blocksy'),
	'typography_keys' => ['mobileMenuFont'],
	'devices' => ['mobile'],
	'allowed_in' => ['offcanvas'],
	'selective_refresh' => [
		'menu',
		'mobile_menu_type',
		'mobile_menu_interactive',
		'mobile_menu_toggle_shape'
	]
];

