<?php

$options = [];

$has_woo = class_exists('WooCommerce');
$woo_rules = [];

if ($filter === 'all') {
	$woo_rules = [
		[
			'id' => 'product_ids',
			'title' => __('Product ID', 'blocksy-companion')
		],

		[
			'id' => 'product_with_taxonomy_ids',
			'title' => __('Product with Taxonomy ID', 'blocksy-companion'),
			'post_type' => 'product'
		],

		[
			'id' => 'product_taxonomy_ids',
			'title' => __('Product Taxonomy ID', 'blocksy-companion')
		],

		[
			'id' => 'woo_shop',
			'title' => __('Shop Home', 'blocksy-companion')
		],

		[
			'id' => 'single_product',
			'title' => __('Single Product', 'blocksy-companion')
		],

		[
			'id' => 'all_product_archives',
			'title' => __('Product Archives', 'blocksy-companion')
		],

		[
			'id' => 'all_product_categories',
			'title' => __('Product Categories', 'blocksy-companion')
		],

		[
			'id' => 'all_product_tags',
			'title' => __('Product Tags', 'blocksy-companion')
		]
	];
}

if ($filter === 'product_tabs') {
	$woo_rules = [
		[
			'id' => 'product_ids',
			'title' => __('Product ID', 'blocksy-companion')
		],

		[
			'id' => 'product_with_taxonomy_ids',
			'title' => __('Product with Taxonomy ID', 'blocksy-companion'),
			'post_type' => 'product'
		]
	];
}

if ($has_woo) {
	$options = [
		[
			'title' => __('WooCommerce', 'blocksy-companion'),
			'rules' => $woo_rules
		]
	];
}
