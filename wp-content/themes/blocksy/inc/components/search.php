<?php

namespace Blocksy;

class SearchModifications {
	use WordPressActionsManager;

	private $filters = [
		[
			'action' => 'rest_post_query',
			'priority' => 999,
			'args' => 2
		],

		[
			'action' => 'pre_get_posts'
		]
	];

	public function __construct() {
		$this->attach_hooks();
	}

	public function rest_post_query($args, $request) {
		if (
			isset($request['post_type'])
			&&
			(strpos($request['post_type'], 'ct_forced') !== false)
		) {
			$post_type = explode(
				':',
				str_replace('ct_forced_', '', $request['post_type'])
			);

			if ($post_type[0] === 'any') {
				$post_type = array_diff(
					get_post_types(['public' => true]),
					['ct_content_block']
				);
			}

			$args = [
				'posts_per_page' => $args['posts_per_page'],
				'post_type' => $post_type,
				'paged' => 1,
				's' => isset($args['s']) ? $args['s'] : '',
			];
		}

		if (
			isset($request['post_type'])
			&&
			(strpos($request['post_type'], 'ct_cpt') !== false)
		) {
			$next_args = [
				'posts_per_page' => $args['posts_per_page'],
				'post_type' => array_diff(
					get_post_types(['public' => true]),
					['post', 'page', 'attachment', 'ct_content_block']
				),
				'paged' => 1
			];

			if (isset($args['s'])) {
				$next_args['s'] = $args['s'];
			}

			$args = $next_args;
		}

		if (
			is_array($args['post_type'])
			&&
			in_array('product', $args['post_type'])
		) {
			if ('yes' === get_option('woocommerce_hide_out_of_stock_items')) {
				$meta_query = [];

				if (isset($args['meta_query'])) {
					$meta_query = $args['meta_query'];
				}

				$meta_query[] = array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => '!=',
				);

				$args['meta_query'] = $meta_query;
			}

			if (function_exists('wc_get_product_visibility_term_ids')) {
				$product_visibility_term_ids = wc_get_product_visibility_term_ids();

				$tax_query = [];

				if (isset($args['tax_query'])) {
					$tax_query = $args['tax_query'];
				}

				$tax_query['relation'] = 'AND';

				$tax_query[] = [
					[
						'taxonomy' => 'product_visibility',
						'field' => 'term_taxonomy_id',
						'terms' => $product_visibility_term_ids['exclude-from-search'],
						'operator' => 'NOT IN',
					]
				];

				$args['tax_query'] = $tax_query;
			}

			if (class_exists('Addify_Products_Visibility_Front')) {
				$visibility = new \Addify_Products_Visibility_Front();

				$q = new WP_Query();

				$visibility->afpvu_custom_pre_get_posts_query($q);

				foreach ($q->query_vars as $key => $value) {
					$args[$key] = $value;
				}
			}
		}

		$tax_query = isset($args['tax_query']) && is_array($args['tax_query']) ? $args['tax_query'] : [];

		if (
			isset($request['ct_tax_query'])
			&&
			!empty($request['ct_tax_query'])
		) {
			$tax_params = explode(':', $request['ct_tax_query']);
			$tax_query[] = [
				'relation' => 'AND',
				array(
					'taxonomy' => $tax_params[0],
					'field' => 'id',
					'terms' => $tax_params[1],
					'operator' => 'IN',
				),
			];
		}

		$args['tax_query'] = $tax_query;

		return $args;
	}

	public function pre_get_posts($query) {
		if (is_admin() || ! $query->is_search) {
			return $query;
		}

		if (
			! is_search()
			&&
			! wp_doing_ajax()
		) {
			return $query;
		}

		$this->maybe_apply_post_type($query);
		$this->maybe_apply_tax_query($query);

		return $query;
	}

	private function maybe_apply_post_type($query) {
		if (empty($_GET['ct_post_type'])) {
			return;
		}

		$custom_post_types = blocksy_manager()->post_types->get_supported_post_types();

		if (function_exists('is_bbpress')) {
			$custom_post_types[] = 'forum';
			$custom_post_types[] = 'topic';
			$custom_post_types[] = 'reply';
		}

		$allowed_post_types = [];

		$post_types = explode(
			':',
			sanitize_text_field($_GET['ct_post_type'])
		);

		$known_cpts = ['post', 'page'];

		if (get_post_type_object('product')) {
			$known_cpts[] = 'product';
		}

		foreach ($post_types as $single_post_type) {
			if (
				in_array($single_post_type, $custom_post_types)
				||
				in_array($single_post_type, $known_cpts)
			) {
				$allowed_post_types[] = $single_post_type;
			}
		}

		$query->set('post_type', $allowed_post_types);

		if (in_array('product', $allowed_post_types)) {
			if ('yes' === get_option('woocommerce_hide_out_of_stock_items')) {
				$meta_query = [];

				if (! empty($query->get('meta_query'))) {
					$meta_query = $query->get('meta_query');
				}

				$meta_query[] = array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => '!=',
				);

				$query->set('meta_query', $meta_query);
			}

			if (function_exists('wc_get_product_visibility_term_ids')) {
				$product_visibility_term_ids = wc_get_product_visibility_term_ids();

				$tax_query = [];

				if (! empty($query->get('tax_query'))) {
					$tax_query = $query->get('tax_query');
				}

				$tax_query['relation'] = 'AND';

				$tax_query[] = [
					[
						'taxonomy' => 'product_visibility',
						'field' => 'term_taxonomy_id',
						'terms' => $product_visibility_term_ids['exclude-from-search'],
						'operator' => 'NOT IN',
					]
				];

				$query->set('tax_query', $tax_query);
			}
		}
	}

	// TODO: check if existing tax query exists before overriding new one
	private function maybe_apply_tax_query($query) {
		if (empty($_GET['ct_tax_query'])) {
			return;
		}

		$tax_query = [
			'relation' => 'AND',
		];

		$tax_params = explode(':', $_GET['ct_tax_query']);

		$tax_query[] = [
			[
				'taxonomy' => $tax_params[0],
				'field' => 'id',
				'terms' => $tax_params[1],
				'operator' => 'IN',
			]
		];

		$query->set('tax_query', [
			'relation' => 'AND',
			$tax_query
		]);
	}
}

