<?php

function blocksy_woocommerce_has_flexy_view() {
	global $blocksy_is_quick_view;

	if ($blocksy_is_quick_view) {
		return true;
	}

	if (is_customize_preview() && wp_doing_ajax()) {
		return true;
	}

	$is_variations_action = (isset($_REQUEST['action'])
		&&
		$_REQUEST['action'] === 'woocommerce_load_variations'
	);

	if (
		(is_product() || wp_doing_ajax())
		&&
		!blocksy_manager()->screen->uses_woo_default_template()
		&&
		!is_customize_preview()
		&&
		!$is_variations_action
	) {
		return false;
	}

	return !apply_filters('blocksy:woocommerce:product-view:use-default', false);
}

// Remove Default actions.
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);

if (!wp_doing_ajax()) {
	add_filter('template_include', function ($template) {
		if (blocksy_woocommerce_has_flexy_view()) {
			remove_action(
				'woocommerce_product_thumbnails',
				'woocommerce_show_product_thumbnails',
				20
			);
		}

		return $template;
	}, 900000009);
} else {
	add_action('init', function () {
		if (blocksy_woocommerce_has_flexy_view()) {
			remove_action(
				'woocommerce_product_thumbnails',
				'woocommerce_show_product_thumbnails',
				20
			);
		}
	});
}

add_filter('blocksy_woo_single_options_layers:defaults', function ($opt) {
	return array_merge($opt, [
		[
			'id' => 'product_tabs',
			'enabled' => true,
		],
	]);
});

add_filter('blocksy_woo_single_right_options_layers:defaults', function ($opt) {
	return array_merge($opt, [
		[
			'id' => 'product_tabs',
			'enabled' => true,
		],
	]);
});

add_filter('blocksy_woo_single_options_layers:extra', function ($opt) {
	return array_merge($opt, [
		'product_tabs' => [
			'label' => __('Product Tabs', 'blocksy'),
			'condition' => [
				'all' => [
					'woo_tabs_type' => 'type-3|type-4',
					'woo_accordion_in_summary' => 'summary',
				]
			],
			'options' => [
				'spacing' => [
					'label' => __('Bottom Spacing', 'blocksy'),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 10,
					'responsive' => true,
					'sync' => [
						'id' => 'woo_single_layout_skip',
					],
				],
			],
		]
	]);
});

add_action(
	'blocksy:woocommerce:product:custom:layer',
	function ($layer, $classes = 'ct-product-tabs') {
		if (
			$layer['id'] === 'product_tabs'
			&&
			blocksy_get_theme_mod('woo_accordion_in_summary', 'default') === 'summary'
		) {
			$tabs_type = blocksy_get_theme_mod('woo_tabs_type', 'type-1');
			$res = $tabs_type;

			$result = '';

			ob_start();
			if ($tabs_type === 'type-3') {
				$result = blocksy_custom_accordion_tabs();
			}

			if ($tabs_type === 'type-4') {
				$result = blocksy_custom_simple_tabs();
			}

			$result = ob_get_clean();

			$res .= ':' . blocksy_get_theme_mod('woo_tabs_alignment', 'center');

			echo str_replace(
				'wc-tabs-wrapper"',
				'wc-tabs-wrapper" data-type="' . $res . '"',
				$result
			);
		}
	}
);

$action_to_hook = 'wp';

if (wp_doing_ajax()) {
	$action_to_hook = 'init';
}

add_action($action_to_hook, function () {
	if (blocksy_get_theme_mod('woo_has_product_tabs', 'yes') === 'no') {
		add_filter('woocommerce_product_tabs', function ($tabs) {
			return [];
		}, 99);
	} else {
		$tabs_type = blocksy_get_theme_mod('woo_tabs_type', 'type-1');

		if (
			blocksy_get_theme_mod('woo_has_product_tabs_description', 'no') === 'no'
			&&
			$tabs_type !== 'type-4'
		) {
			add_filter(
				'woocommerce_product_description_heading',
				'__return_null'
			);

			add_filter(
				'woocommerce_product_additional_information_heading',
				'__return_null'
			);
		}
	}

	add_action(
		'woocommerce_before_single_product',
		function() {
			blocksy_manager()->get_hooks()->redirect_callbacks([
				'token' => 'single_product_before_title',
				'source' => ['woocommerce_single_product_summary'],
				'destination' => 'blocksy:woocommerce:product-single:title:before',

				'priority_min' => 2,
				'priority_max' => 4
			]);

			blocksy_manager()->get_hooks()->redirect_callbacks([
				'token' => 'single_product_after_title',
				'source' => ['woocommerce_single_product_summary'],
				'destination' => 'blocksy:woocommerce:product-single:title:after',

				'priority_min' => 5,
				'priority_max' => 10
			]);

			blocksy_manager()->get_hooks()->redirect_callbacks([
				'token' => 'single_product_after_price',
				'source' => ['woocommerce_single_product_summary'],
				'destination' => 'blocksy:woocommerce:product-single:price:after',

				'priority_min' => 11,
				'priority_max' => 20
			]);

			blocksy_manager()->get_hooks()->redirect_callbacks([
				'token' => 'single_product_after_excerpt',
				'source' => ['woocommerce_single_product_summary'],
				'destination' => 'blocksy:woocommerce:product-single:excerpt:after',

				'priority_min' => 20,
				'priority_max' => 30
			]);

			blocksy_manager()->get_hooks()->redirect_callbacks([
				'token' => 'single_product_after_add_to_cart',
				'source' => ['woocommerce_single_product_summary'],
				'destination' => 'blocksy:woocommerce:product-single:add_to_cart:after',

				'priority_min' => 30,
				'priority_max' => 40
			]);

			blocksy_manager()->get_hooks()->redirect_callbacks([
				'token' => 'single_product_after_meta',
				'source' => ['woocommerce_single_product_summary'],
				'destination' => 'blocksy:woocommerce:product-single:meta:after',

				'priority_min' => 40,
				'priority_max' => 50
			]);
		}
	);

	$product_view_type = blocksy_get_product_view_type();

	if (
		$product_view_type === 'default-gallery'
		||
		$product_view_type === 'stacked-gallery'
	) {
		add_action(
			'woocommerce_single_product_summary',
			function () {
				blocksy_manager()->woocommerce->single->render_layout(
					[
						'defaults' => blocksy_get_woo_single_layout_defaults()
					]
				);
			},
			1
		);
	}
}, 9000000000);

add_action(
	'woocommerce_after_single_product_summary',
	function () {
		do_action('blocksy:woocommerce:product-single:tabs:before');
	},
	9
);

add_action(
	'woocommerce_after_single_product_summary',
	function () {
		do_action('blocksy:woocommerce:product-single:tabs:after');
	},
	11
);

add_action(
	'woocommerce_before_single_product_summary',
	function () {
		global $blocksy_single_product_summary_buffering_started;
		$blocksy_single_product_summary_buffering_started = true;

		echo '<div class="product-entry-wrapper">';
		ob_start();
	},
	1
);

add_action('woocommerce_single_product_summary', function () {
	global $blocksy_single_product_summary_buffering_started;

	if (! $blocksy_single_product_summary_buffering_started) {
		return;
	}

	$product_view_type = blocksy_get_product_view_type();

	$content = ob_get_clean();

	$prefix = blocksy_manager()->screen->get_prefix();
	$deep_link_args = [
		'prefix' => $prefix,
		'suffix' => 'woo_product_elements',
		'shortcut' => 'border'
	];

	$content = str_replace(
		'class="summary',
		blocksy_generic_get_deep_link($deep_link_args) . ' class="summary',
		$content
	);

	if (
		$product_view_type !== 'top-gallery'
		&&
		$product_view_type !== 'columns-top-gallery'
	) {
		$content = str_replace(
			'entry-summary',
			'entry-summary entry-summary-items',
			$content
		);
	}

	echo $content;
}, 0);

add_action(
	'woocommerce_after_single_product_summary',
	function () {
		echo '</div>';
	},
	1
);

remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);

add_action(
	'woocommerce_after_main_content',
	'blocksy_woo_single_product_after_main_content',
	5
);

if (! function_exists('blocksy_woo_single_product_after_main_content')) {
	function blocksy_woo_single_product_after_main_content() {
		if (
			! is_product()
			||
			blocksy_get_theme_mod('woo_has_related_upsells', 'yes') !== 'yes'
		) {
			return;
		}

		if (
			blocksy_some_device(
				blocksy_get_theme_mod(
					'upsell_products_visibility',
					[
						'desktop' => true,
						'tablet' => false,
						'mobile' => false,
					]
				)
			)
			||
			is_customize_preview()
		) {
			woocommerce_upsell_display();
		}

		if (
			blocksy_some_device(blocksy_get_theme_mod(
				'related_products_visibility',
				[
					'desktop' => true,
					'tablet' => false,
					'mobile' => false,
				]
			))
			||
			is_customize_preview()
		) {
			woocommerce_output_related_products();
		}
	}
}

add_filter('woocommerce_output_related_products_args', function ($args) {
	$columns = intval(blocksy_get_theme_mod(
		'woo_product_related_cards_columns',
		[
			'desktop' => 4,
			'tablet' => 3,
			'mobile' => 1
		]
	)['desktop']);

	$args['columns'] = $columns;
	$args['posts_per_page'] = $columns * intval(blocksy_get_theme_mod(
		'woo_product_related_cards_rows',
		1
	));

	return $args;
}, 10);

add_filter('woocommerce_upsell_display_args', function ($args) {
	$columns = intval(blocksy_get_theme_mod(
		'woo_product_related_cards_columns',
		[
			'desktop' => 4,
			'tablet' => 3,
			'mobile' => 1
		]
	)['desktop']);

	$args['columns'] = $columns;
	$args['posts_per_page'] = $columns * intval(blocksy_get_theme_mod(
		'woo_product_related_cards_rows',
		1
	));

	return $args;
}, 10);

add_filter('woocommerce_upsells_columns', function ($columns) {
	return intval(blocksy_get_theme_mod(
		'woo_product_related_cards_columns',
		[
			'desktop' => 4,
			'tablet' => 3,
			'mobile' => 1
		]
	)['desktop']);
});

add_filter('comment_class', function ($classes, $class, $comment_id, $comment) {
	if (! is_product()) {
		return $classes;
	}

	$has_avatar = get_option('show_avatars', 1);

	if ($has_avatar) {
		$classes[] = 'ct-has-avatar';
	}
	return $classes;
}, 10, 4);
