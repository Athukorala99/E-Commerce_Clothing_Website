<?php

namespace Blocksy;

class ConditionsRulesResolver {
	private $rule = [];

	// prefix | current-screen
	private $strategy_or_prefix = 'current-screen';

	public function __construct($rule, $args = []) {
		$args = wp_parse_args($args, [
			// prefix | current-screen
			'strategy' => 'current-screen'
		]);

		$this->rule = $rule;
		$this->strategy_or_prefix = $args['strategy'];
	}

	public function resolve() {
		if ($this->strategy_or_prefix === 'current-screen') {
			return $this->resolve_single_condition($this->rule);
		}

		return $this->resolve_single_condition_with_prefix(
			$this->rule,
			$this->strategy_or_prefix
		);
	}

	private function resolve_single_condition($rule) {
		if ($rule['rule'] === 'everywhere') {
			return true;
		}

		if ($rule['rule'] === 'singulars') {
			return is_singular();
		}

		if ($rule['rule'] === 'archives') {
			return is_archive();
		}

		if ($rule['rule'] === '404') {
			return is_404();
		}

		if ($rule['rule'] === 'search') {
			return is_search();
		}

		if ($rule['rule'] === 'blog') {
			return ! is_front_page() && is_home();
		}

		if ($rule['rule'] === 'front_page') {
			return is_front_page();
		}

		if ($rule['rule'] === 'privacy_policy_page') {
			$is_blocksy_page = blocksy_is_page();

			if (is_singular() || $is_blocksy_page) {
				$post_id = get_the_ID();

				if ($is_blocksy_page) {
					$post_id = $is_blocksy_page;
				}

				return intval($post_id) === intval(
					get_option('wp_page_for_privacy_policy')
				);
			}
		}

		if ($rule['rule'] === 'date') {
			return is_date();
		}

		if ($rule['rule'] === 'author') {
			if (
				isset($rule['payload'])
				&&
				isset($rule['payload']['user_id'])
			) {
				return is_author($rule['payload']['user_id']);
			}

			return is_author();
		}

		if (
			$rule['rule'] === 'request_referer'
			&&
			! empty($rule['payload']['referer'])
		) {
			return strpos(
				wp_get_raw_referer(),
				$rule['payload']['referer']
			) !== false;
		}

		if (
			$rule['rule'] === 'request_url'
			&&
			! empty($rule['payload']['url'])
		) {
			return strpos(
				blocksy_current_url(),
				$rule['payload']['url']
			) !== false;
		}

		if (
			$rule['rule'] === 'request_cookie'
			&&
			! empty($rule['payload']['cookie'])
		) {
			return (
				isset($_COOKIE[$rule['payload']['cookie']])
				&&
				! empty($_COOKIE[$rule['payload']['cookie']])
			);
		}

		if (
			$rule['rule'] === 'start_end_date'
			&&
			! empty($rule['payload']['start'])
			&&
			! empty($rule['payload']['end'])
		) {
			$timezone = null;

			if (function_exists('wp_timezone')) {
				$timezone = wp_timezone();
			}

			$start = new \DateTime($rule['payload']['start'], $timezone);
			$end = new \DateTime($rule['payload']['end'], $timezone);

			$now = new \DateTime('now', $timezone);

			return (
				$start < $now
				&&
				$end > $now
			);
		}

		if ($rule['rule'] === 'schedule_date') {
			$day_matches = true;
			$time_matches = true;

			if (
				isset($rule['payload'])
				&&
				isset($rule['payload']['days'])
			) {
				$days = $rule['payload']['days'];

				if (! is_array($days)) {
					$days = [
						'monday' => true,
						'tuesday' => true,
						'wednesday' => true,
						'thursday' => true,
						'friday' => true,
						'saturday' => true,
						'sunday' => true
					];
				}

				$day = strtolower(date('l'));

				$day_matches = isset($days[$day]) && $days[$day];
			}

			if (
				isset($rule['payload'])
				&&
				isset($rule['payload']['time_start'])
				&&
				isset($rule['payload']['time_end'])
			) {
				$current_time = current_datetime();

				$start = current_datetime()->setTime(
					explode(':', $rule['payload']['time_start'])[0],
					explode(':', $rule['payload']['time_start'])[1]
				);

				$end = current_datetime()->setTime(
					explode(':', $rule['payload']['time_end'])[0],
					explode(':', $rule['payload']['time_end'])[1]
				);

				if ($current_time < $start || $current_time > $end) {
					$time_matches = false;
				}
			}

			return $day_matches && $time_matches;
		}

		if ($rule['rule'] === 'woo_shop') {
			return function_exists('is_shop') && is_shop();
		}

		if ($rule['rule'] === 'single_post') {
			return is_singular('post');
		}

		if ($rule['rule'] === 'all_post_archives') {
			global $post;
			global $wp_query;

			return is_post_type_archive('post') || (
				$wp_query->in_the_loop
				&&
				get_post_type($post) === 'post'
			);
		}

		if ($rule['rule'] === 'post_categories') {
			return is_category();
		}

		if ($rule['rule'] === 'post_tags') {
			return is_tag();
		}

		if ($rule['rule'] === 'single_page') {
			return is_singular('page');
		}

		if ($rule['rule'] === 'single_product') {
			return function_exists('is_product') && is_product();
		}

		if ($rule['rule'] === 'all_product_archives') {
			if (function_exists('is_shop')) {
				return is_shop() || is_product_tag() || is_product_category();
			}
		}

		if ($rule['rule'] === 'all_product_categories') {
			if (function_exists('is_shop')) {
				return is_product_category();
			}
		}

		if ($rule['rule'] === 'all_product_tags') {
			if (function_exists('is_shop')) {
				return is_product_tag();
			}
		}

		if ($rule['rule'] === 'user_logged_in') {
			return is_user_logged_in();
		}

		if ($rule['rule'] === 'user_logged_out') {
			return ! is_user_logged_in();
		}

		if ($rule['rule'] === 'user_post_author_id') {
			global $post;

			if (
				$post
				&&
				$post->post_author
				&&
				isset($rule['payload'])
				&&
				isset($rule['payload']['user_id'])
			) {
				$user_id = $rule['payload']['user_id'];
				$post_author = intval($post->post_author);

				if ($user_id === 'current_user') {
					return $post_author === intval(get_current_user_id());
				}

				return intval($user_id) === $post_author;
			}
		}

		if (strpos($rule['rule'], 'user_role_') !== false) {
			if (! is_user_logged_in()) {
				return false;
			}

			return in_array(
				str_replace('user_role_', '', $rule['rule']),
				get_userdata(wp_get_current_user()->ID)->roles
			);
		}

		if (strpos($rule['rule'], 'post_type_single_') !== false) {
			return is_singular(str_replace(
				'post_type_single_',
				'',
				$rule['rule']
			));
		}

		if (strpos($rule['rule'], 'post_type_archive_') !== false) {
			global $post;
			global $wp_query;

			$custom_post_type = str_replace(
				'post_type_archive_',
				'',
				$rule['rule']
			);

			return is_post_type_archive($custom_post_type) || (
				$wp_query->in_the_loop
				&&
				get_post_type($post) === $custom_post_type
			);
		}

		if (strpos($rule['rule'], 'post_type_taxonomy_') !== false) {
			return is_tax(str_replace(
				'post_type_taxonomy_',
				'',
				$rule['rule']
			));
		}

		if (
			$rule['rule'] === 'post_ids'
			||
			$rule['rule'] === 'page_ids'
			||
			$rule['rule'] === 'product_ids'
			||
			$rule['rule'] === 'custom_post_type_ids'
		) {
			if (function_exists('blocksy_is_page')) {
				$is_blocksy_page = blocksy_is_page();

				if (is_singular() || $is_blocksy_page) {
					$post_id = get_the_ID();

					if ($is_blocksy_page) {
						$post_id = $is_blocksy_page;
					}

					global $post;

					if (intval($post_id) === 0 && isset($post->post_name)) {
						$maybe_post = get_page_by_path($post->post_name);

						if ($maybe_post) {
							$post_id = $maybe_post->ID;
						}
					}

					if (
						isset($rule['payload'])
						&&
						isset($rule['payload']['post_id'])
						&&
						$post_id
						&&
						intval($post_id) === intval($rule['payload']['post_id'])
					) {
						return true;
					}
				}
			}
		}

		if (
			$rule['rule'] === 'current_language'
			&&
			function_exists('blocksy_get_current_language')
			&&
			! empty($rule['payload']['language'])
		) {
			return $rule['payload']['language'] === blocksy_get_current_language();
		}

		if (
			$rule['rule'] === 'bbpress_profile'
			&&
			function_exists('bbp_is_single_user_profile')
		) {
			return bbp_is_single_user_profile();
		}

		if (
			$rule['rule'] === 'taxonomy_ids'
			||
			$rule['rule'] === 'product_taxonomy_ids'
		) {
			if (is_tax() || is_category() || is_tag()) {
				$tax_id = get_queried_object_id();

				if (
					isset($rule['payload'])
					&&
					isset($rule['payload']['taxonomy_id'])
					&&
					$tax_id
					&&
					intval($tax_id) === intval($rule['payload']['taxonomy_id'])
				) {
					return true;
				}
			}
		}

		if (
			$rule['rule'] === 'post_with_taxonomy_ids'
			||
			$rule['rule'] === 'product_with_taxonomy_ids'
		) {
			$is_blocksy_page = blocksy_is_page();
			global $blocksy_is_quick_view;
			global $wp_query;

			global $post;

			if (is_singular() || $is_blocksy_page || $wp_query->in_the_loop) {
				$post_id = get_the_ID();

				if ($is_blocksy_page) {
					$post_id = $is_blocksy_page;
				}

				if (wp_doing_ajax() && isset($_GET['product_id'])) {
					$post_id = sanitize_text_field($_GET['product_id']);
				}

				if (
					isset($rule['payload'])
					&&
					isset($rule['payload']['taxonomy_id'])
					&&
					$post_id
					&&
					get_term($rule['payload']['taxonomy_id'])
					&&
					in_array(
						get_term($rule['payload']['taxonomy_id'])->taxonomy,
						get_object_taxonomies([
							'post_type' => get_post_type($post_id)
						])
					)
				) {
					return has_term(
						$rule['payload']['taxonomy_id'],
						get_term($rule['payload']['taxonomy_id'])->taxonomy,
						$post_id
					);
				}
			}
		}

		return false;
	}

	private function resolve_single_condition_with_prefix($rule, $prefix) {
		if ($rule['rule'] === 'everywhere') {
			return true;
		}

		if ($rule['rule'] === 'singulars') {
			return (
				$prefix === 'single_blog_post'
				||
				$prefix === 'single_page'
				||
				strpos($prefix, '_single') !== false
			);
		}

		if ($rule['rule'] === 'archives') {
			return is_archive();
		}

		if ($rule['rule'] === '404') {
			return $prefix === '404';
		}

		if ($rule['rule'] === 'search') {
			return $prefix === 'search';
		}

		if ($rule['rule'] === 'blog') {
			return $prefix === 'blog';
		}

		if ($rule['rule'] === 'front_page') {
			return $prefix === 'blog';
		}

		if ($rule['rule'] === 'date') {
			return is_date();
		}

		if ($rule['rule'] === 'author') {
			return is_author();
		}

		if ($rule['rule'] === 'woo_shop') {
			return function_exists('is_shop') && is_shop();
		}

		if ($rule['rule'] === 'single_post') {
			return $prefix === 'single_blog_post';
		}

		if ($rule['rule'] === 'all_post_archives') {
			return is_post_type_archive('post');
		}

		if ($rule['rule'] === 'post_categories') {
			return $prefix === 'categories';
		}

		if ($rule['rule'] === 'post_tags') {
			return $prefix === 'categories';
		}

		if ($rule['rule'] === 'single_page') {
			return $prefix === 'single_page';
		}

		if ($rule['rule'] === 'single_product') {
			return $prefix === 'product';
		}

		if ($rule['rule'] === 'all_product_archives') {
			return $prefix === 'woo_categories';
		}

		if ($rule['rule'] === 'all_product_categories') {
			return $prefix === 'woo_categories';
		}

		if ($rule['rule'] === 'all_product_tags') {
			return $prefix === 'woo_categories';
		}

		if (strpos($rule['rule'], 'post_type_single_') !== false) {
			return $prefix === str_replace(
				'post_type_single_',
				'',
				$rule['rule']
			) . '_single';
		}

		if (strpos($rule['rule'], 'post_type_archive_') !== false) {
			return $prefix === str_replace(
				'post_type_archive_',
				'',
				$rule['rule']
			) . '_archive';
		}

		if (strpos($rule['rule'], 'post_type_taxonomy_') !== false) {
			return $prefix === str_replace(
				'post_type_taxonomy_',
				'',
				$rule['rule']
			) . '_archive';
		}

		return false;
	}
}
