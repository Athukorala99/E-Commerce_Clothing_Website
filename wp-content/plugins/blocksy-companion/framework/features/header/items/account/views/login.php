<?php

if (! isset($device)) {
	$device = 'desktop';
}

$loggedin_account_label_visibility = blocksy_akg(
	'loggedin_account_label_visibility',
	$atts,
	[
		'desktop' => false,
		'tablet' => false,
		'mobile' => false,
	]
);

// Logged in
$loggedin_interaction_type = blocksy_akg('loggedin_interaction_type', $atts, 'dropdown');
$account_link = blocksy_akg('account_link', $atts, 'profile');
$dropdown_html = '';

$link = '#';

if ($loggedin_interaction_type !== 'dropdown') {
	$link = get_edit_profile_url();

	if ($account_link === 'dashboard') {
		$link = admin_url();
	}

	if ($account_link === 'custom') {
		$link = do_shortcode(blocksy_akg('account_custom_page', $atts, ''));
	}

	if ($account_link === 'woocommerce_account' && class_exists('WooCommerce')) {
		$link = get_permalink(get_option('woocommerce_myaccount_page_id'));
	}

	if ($account_link === 'logout') {
		$link = wp_logout_url(blocksy_current_url());
	}

	$link = apply_filters('wpml_permalink', $link);
}

// Media
$media_html = '';
$loggedin_media = blocksy_akg('loggedin_media', $atts, 'avatar');

$avatar_size = intval(
	blocksy_expand_responsive_value(
		blocksy_akg('accountHeaderAvatarSize', $atts, 18)
	)['desktop']
);

if ($loggedin_media === 'avatar') {
	$media_html = blocksy_simple_image(
		blocksy_get_avatar_url([
			'avatar_entity' => $current_user_id,
			'size' => $avatar_size * 2
		]),
		[
			'img_atts' => [
				'width' => $avatar_size,
				'height' => $avatar_size,
				'aria-hidden' => 'true',
			],
		]
	);
}

if ($loggedin_media === 'icon') {
	$media_html = $icon[blocksy_akg('account_loggedin_icon', $atts, 'type-1')];

	if (function_exists('blc_get_icon')) {
		$icon_source = blocksy_default_akg(
			'loggedin_icon_source',
			$atts,
			'default'
		);

		if ($icon_source === 'custom') {
			$media_html = blc_get_icon([
				'icon_descriptor' => blocksy_akg(
					'loggedin_custom_icon',
					$atts,
					['icon' => 'blc blc-user']
				),
				'icon_container' => false,
				'icon_html_atts' => [
					'class' => 'ct-icon',
				]
			]);
		}
	}
}

// Label
$loggedin_label = blocksy_expand_responsive_value(
	blocksy_default_akg('loggedin_label', $atts, __('My Account', 'blocksy-companion'))
)[$device];


$loggedin_label = do_shortcode(
	blocksy_translate_dynamic(
		$loggedin_label,
		'header:' . $section_id . ':' . $item_id . ':loggedin_label'
	)
);

if (blocksy_akg('loggedin_text', $atts, 'label') === 'username') {
	$user = wp_get_current_user();
	$loggedin_label = $user->display_name;
}

$loggedin_label_position = blocksy_expand_responsive_value(
	blocksy_akg('loggedin_label_position', $atts, 'left')
);

$attr['data-state'] = 'in';
$attr['data-interaction'] = $loggedin_interaction_type;

$link_attr = [
	'class' => 'ct-account-item',
	'aria-label' => $loggedin_label,
	'href' => $link,
];

$link_tag_name = 'a';

if (! empty($media_html)) {
	$link_attr['data-label'] = $loggedin_label_position[$device];
}

// dropdown menu
if ($loggedin_interaction_type === 'dropdown') {
	$link_tag_name = 'span';

	$link_attr['tabindex'] = '0';

	unset($link_attr['href'], $link_attr['aria-label']);

	$dropdown_html = '';
	$dropdown_items = blocksy_akg('dropdown_items', $atts, [
		[
			'id' => 'user_info',
			'enabled' => true,
			'label' => __('User Info', 'blocksy-companion'),
		],

		[
			'id' => 'divider',
			'enabled' => true,
		],

		[
			'id' => 'dashboard',
			'enabled' => true,
			'label' => __('Dashboard', 'blocksy-companion'),
		],

		[
			'id' => 'profile',
			'enabled' => true,
			'label' => __('Edit Profile', 'blocksy-companion'),
		],

		[
			'id' => 'logout',
			'enabled' => true,
			'label' => __('Log Out', 'blocksy-companion'),
		],
	]);

	$dropdown_items_html = [];

	foreach ($dropdown_items as $dropdown_row) {
		if (
			! isset($dropdown_row['enabled'])
			||
			! $dropdown_row['enabled']
		) {
			continue;
		}

		if ($dropdown_row['id'] === 'user_info') {
			$user = wp_get_current_user();
			$user_fistname = '';
			$user_lastname = '';

			if ($user->has_prop('user_firstname')) {
				$user_firstname = $user->get('user_firstname');
			}

			if ($user->has_prop('user_lastname')) {
				$user_lastname = $user->get('user_lastname');
			}

			$user_display_name =
				!empty($user_firstname) || !empty($user_lastname)
				? $user_firstname . ' ' . $user_lastname
				: $user->display_name;

			$image_html = '';

			if (blocksy_akg('has_account_dropdown_avatar', $dropdown_row, 'yes') === 'yes') {
				$image_html = blocksy_simple_image(
					get_avatar_url($current_user_id, [
						'size' => $avatar_size * 2,
					]),
					[
						'img_atts' => [
							'width' => $avatar_size,
							'height' => $avatar_size,
							'aria-hidden' => 'true',
						],
					]
				);
			}

			$dropdown_items_html[] = blocksy_html_tag(
				'li',
				[
					'class' => 'ct-header-account-user-info'
				],
				$image_html . blocksy_html_tag(
					'span',
					[],
					'<b>' . $user_display_name . '</b><small>' . $user->user_email . '</small>'
				)
			);
		}

		if ($dropdown_row['id'] === 'dashboard') {
			$user = wp_get_current_user();

			$dropdown_items_html[] = blocksy_html_tag(
				'li',
				[],
				blocksy_html_tag(
					'a',
					[
						'href' => get_dashboard_url(
							isset($user->ID) ? (int) $user->ID : 0
						)
					],
					do_shortcode(
						blocksy_default_akg(
							'label',
							$dropdown_row,
							__('Dashboard', 'blocksy-companion')
						)
					)
				)
			);
		}

		if ($dropdown_row['id'] === 'divider') {
			$dropdown_items_html[] = blocksy_html_tag(
				'li',
				[
					'class' => 'ct-dropdown-divider'
				],
				true
			);
		}

		if ($dropdown_row['id'] === 'profile') {
			$dropdown_items_html[] = blocksy_html_tag(
				'li',
				[],
				blocksy_html_tag(
					'a',
					[
						'href' => get_edit_profile_url()
					],
					do_shortcode(
						blocksy_default_akg(
							'label',
							$dropdown_row,
							__('Edit Profile', 'blocksy-companion')
						)
					)
				)
			);
		}

		if ($dropdown_row['id'] === 'logout') {
			$dropdown_items_html[] = blocksy_html_tag(
				'li',
				[
					'class' => 'ct-header-account-logout'
				],
				blocksy_html_tag(
					'a',
					[
						'href' => wp_logout_url(get_permalink())
					],
					do_shortcode(
						blocksy_default_akg(
							'label',
							$dropdown_row,
							__('Log Out', 'blocksy-companion')
						)
					)
				)
			);
		}

		if ($dropdown_row['id'] === 'custom_link') {
			$dropdown_items_html[] = blocksy_html_tag(
				'li',
				[],
				blocksy_html_tag(
					'a',
					[
						'href' => do_shortcode(
							blocksy_default_akg(
								'link',
								$dropdown_row,
								'#'
							)
						)
					],
					do_shortcode(
						blocksy_default_akg(
							'label',
							$dropdown_row,
							__('Custom Link', 'blocksy-companion')
						)
					)
				)
			);
		}

		if ($dropdown_row['id'] === 'woo_account') {
			$dropdown_items_html[] = blocksy_html_tag(
				'li',
				[],
				blocksy_html_tag(
					'a',
					[
						'href' => get_permalink(get_option('woocommerce_myaccount_page_id'))
					],
					do_shortcode(
						blocksy_default_akg(
							'label',
							$dropdown_row,
							__('My Account', 'blocksy-companion')
						)
					)
				)
			);
		}

		if (
			$dropdown_row['id'] === 'wishlist'
			&&
			function_exists('wc_get_endpoint_url')
		) {
			$url = wc_get_endpoint_url(
				apply_filters(
					'blocksy:pro:woocommerce-extra:wish-list:slug',
					'woo-wish-list'
				),
				'',
				get_permalink(get_option('woocommerce_myaccount_page_id'))
			);

			$maybe_page_id = blocksy_get_theme_mod('woocommerce_wish_list_page');

			if (!empty($maybe_page_id)) {
				$maybe_permalink = get_permalink($maybe_page_id);

				if ($maybe_permalink) {
					$url = $maybe_permalink;
				}
			}

			$dropdown_items_html[] = blocksy_html_tag(
				'li',
				[],
				blocksy_html_tag(
					'a',
					['href' => $url],
					do_shortcode(
						blocksy_default_akg(
							'label',
							$dropdown_row,
							__('Wishlist', 'blocksy-companion')
						)
					)
				)
			);
		}

		if (
			$dropdown_row['id'] === 'dokan_dashboard'
			&&
			function_exists('dokan')
		) {
			$user = wp_get_current_user();
			$roles = (array) $user->roles;

			if (in_array('seller', $roles)) {
				$vendor = dokan()->vendor->get($user->ID);
				$url = $vendor->get_dashboard_url();

				$dropdown_items_html[] = blocksy_html_tag(
					'li',
					[],
					blocksy_html_tag(
						'a',
						['href' => $url],
						do_shortcode(
							blocksy_default_akg(
								'label',
								$dropdown_row,
								__('Dokan Dashboard', 'blocksy-companion')
							)
						)
					)
				);
			}
		}

		if (
			$dropdown_row['id'] === 'dokan_shop'
			&&
			function_exists('dokan')
		) {
			$user = wp_get_current_user();
			$roles = (array) $user->roles;

			if (in_array('seller', $roles)) {
				$vendor = dokan()->vendor->get($user->ID);
				$url = $vendor->get_shop_url();

				$dropdown_items_html[] = blocksy_html_tag(
					'li',
					[],
					blocksy_html_tag(
						'a',
						['href' => $url],
						do_shortcode(
							blocksy_default_akg(
								'label',
								$dropdown_row,
								__('Dokan Shop', 'blocksy-companion')
							)
						)
					)
				);
			}
		}

		if (
			$dropdown_row['id'] === 'tutor_lms'
			&&
			function_exists('tutor_utils')
		) {
			$dashboard_page_id = tutor_utils()->get_option(
				'tutor_dashboard_page_id'
			);

			$url = get_permalink($dashboard_page_id);

			$dropdown_items_html[] = blocksy_html_tag(
				'li',
				[],
				blocksy_html_tag(
					'a',
					['href' => $url],
					do_shortcode(
						blocksy_default_akg(
							'label',
							$dropdown_row,
							__('Tutor LMS Dashboard', 'blocksy-companion')
						)
					)
				)
			);
		}

		if (
			$dropdown_row['id'] === 'bbpress'
			&&
			class_exists('bbPress')
		) {
			$url = bbp_get_user_profile_url( bbp_get_current_user_id() ) ;

			$dropdown_items_html[] = blocksy_html_tag(
				'li',
				[],
				blocksy_html_tag(
					'a',
					['href' => $url],
					do_shortcode(
						blocksy_default_akg(
							'label',
							$dropdown_row,
							__('bbPress Dashboard', 'blocksy-companion')
						)
					)
				)
			);
		}

		if (
			$dropdown_row['id'] === 'content-block'
			&&
			! empty($dropdown_row['hook_id'])
			&&
			\Blocksy\Plugin::instance()
				->premium
				->content_blocks
				->is_hook_eligible_for_display($dropdown_row['hook_id'], [
					'match_conditions' => false
				])
		) {
			$classes = 'ct-header-account-content-block';

			$content = $content = \Blocksy\Plugin::instance()
				->premium
				->content_blocks
				->output_hook($dropdown_row['hook_id'], [
					'layout' => false
				]);

			$dropdown_items_html[] = blocksy_html_tag(
				'li',
				[
					'class' => $classes,
					'data-id' => $dropdown_row['__id'],
				],
				$content
			);
		}
	}

	if (count($dropdown_items_html) > 0) {
		$dropdown_html = blocksy_html_tag(
			'ul',
			[],
			implode('', $dropdown_items_html)
		);
	}
}

?>

<div <?php echo blocksy_attr_to_html($attr); ?>>
	<?php
		echo blocksy_html_tag(
			$link_tag_name,
			$link_attr,
			(
				!empty($loggedin_label) ?
				blocksy_html_tag(
					'span',
					[
						'class' => trim('ct-label ' . blocksy_visibility_classes($loggedin_account_label_visibility))
					],
					$loggedin_label
				) : ''
			) .
			$media_html
		);
	?>

	<?php
		if (!empty($dropdown_html)) {
 			echo $dropdown_html;
 		}
	?>

</div>
