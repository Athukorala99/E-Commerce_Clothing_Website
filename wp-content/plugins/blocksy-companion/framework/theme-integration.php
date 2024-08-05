<?php

namespace Blocksy;

class ThemeIntegration {
	public function __construct() {
		add_action('wp_enqueue_scripts', function () {
			if (! function_exists('get_plugin_data')){
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			$data = get_plugin_data(BLOCKSY__FILE__);

			if (is_admin()) return;

			wp_register_script(
				'blocksy-zxcvbn',
				includes_url('/js/zxcvbn.min.js')
			);
		});

		add_filter('blocksy:frontend:dynamic-js-chunks', function ($chunks) {
			$render = new \Blocksy_Header_Builder_Render();

			if (
				$render->contains_item('account')
				||
				is_customize_preview()
			) {
				$deps = [];
				$global_data = [];

				if (class_exists('woocommerce')) {
					$deps = [
						'blocksy-zxcvbn',
						'wp-hooks',
						'wp-i18n',
						'password-strength-meter',
					];

					$global_data = [
						[
							'var' => 'wc_password_strength_meter_params',
							'data' => [
								'min_password_strength' => apply_filters(
									'woocommerce_min_password_strength',
									3
								),
								'stop_checkout' => apply_filters(
									'woocommerce_enforce_password_strength_meter_on_checkout',
									false
								),
								'i18n_password_error'=> esc_attr__(
									'Please enter a stronger password.',
									'woocommerce'
								),
								'i18n_password_hint' => addslashes(wp_get_password_hint()),
							]
						],

						[
							'var' => 'pwsL10n',
							'data' => [
								'unknown'  => _x( 'Password strength unknown', 'password strength' ),
								'short'    => _x( 'Very weak', 'password strength' ),
								'bad'      => _x( 'Weak', 'password strength' ),
								'good'     => _x( 'Medium', 'password strength' ),
								'strong'   => _x( 'Strong', 'password strength' ),
								'mismatch' => _x( 'Mismatch', 'password mismatch' ),
							]
						]
					];
				}

				if (function_exists('dokan')) {
					$deps[] = 'dokan-form-validate';
					$deps[] = 'dokan-vendor-registration';

					$global_data[] = [
						'var' => 'DokanValidateMsg',
						'data' => apply_filters('DokanValidateMsg_args', [
							'required'        => __( 'This field is required', 'dokan-lite' ),
							'remote'          => __( 'Please fix this field.', 'dokan-lite' ),
							'email'           => __( 'Please enter a valid email address.', 'dokan-lite' ),
							'url'             => __( 'Please enter a valid URL.', 'dokan-lite' ),
							'date'            => __( 'Please enter a valid date.', 'dokan-lite' ),
							'dateISO'         => __( 'Please enter a valid date (ISO).', 'dokan-lite' ),
							'number'          => __( 'Please enter a valid number.', 'dokan-lite' ),
							'digits'          => __( 'Please enter only digits.', 'dokan-lite' ),
							'creditcard'      => __( 'Please enter a valid credit card number.', 'dokan-lite' ),
							'equalTo'         => __( 'Please enter the same value again.', 'dokan-lite' ),
							'maxlength_msg'   => __( 'Please enter no more than {0} characters.', 'dokan-lite' ),
							'minlength_msg'   => __( 'Please enter at least {0} characters.', 'dokan-lite' ),
							'rangelength_msg' => __( 'Please enter a value between {0} and {1} characters long.', 'dokan-lite' ),
							'range_msg'       => __( 'Please enter a value between {0} and {1}.', 'dokan-lite' ),
							'max_msg'         => __( 'Please enter a value less than or equal to {0}.', 'dokan-lite' ),
							'min_msg'         => __( 'Please enter a value greater than or equal to {0}.', 'dokan-lite' ),
						])
					];
				}

				$chunks[] = [
					'id' => 'blocksy_account',
					'selector' => implode(', ', [
						'.ct-account-item[href*="account-modal"]',
						'.must-log-in a'
					]),
					'url' => blocksy_cdn_url(
						BLOCKSY_URL . 'static/bundle/account.js'
					),
					'deps' => $deps,
					'global_data' => $global_data,

					'trigger' => 'click',
				];
			}

			$chunks[] = [
				'id' => 'blocksy_sticky_header',
				'selector' => 'header [data-sticky]',
				'url' => blocksy_cdn_url(
					BLOCKSY_URL . 'static/bundle/sticky.js'
				),
			];

			return $chunks;
		});

		add_shortcode('blocksy_posts', function ($args, $content) {
			$args = wp_parse_args(
				$args,
				[
					'post_type' => 'post',
					'limit' => 5,

					// post_date | comment_count
					'orderby' => 'post_date',
					'order' => 'DESC',
					'meta_value' => '',
					'meta_key' => '',

					// yes | no
					'has_pagination' => 'yes',

					// yes | no
					'ignore_sticky_posts' => 'no',

					'term_ids' => null,
					'exclude_term_ids' => null,
					'post_ids' => null,

					// archive | slider
					'view' => 'archive',
					'slider_image_ratio' => '2/1',
					'slider_autoplay' => 'no',

					'filtering' => false,
					'filtering_use_children_tax_ids' => false,

					// 404 | skip
					'no_results' => '404',

					'class' => ''
				]
			);

			$file_path = dirname(__FILE__) . '/views/blocksy-posts.php';

			return blocksy_render_view(
				$file_path,
				[
					'args' => $args,
					'content' => $content
				]
			);
		});

		add_filter('blocksy:general:ct-scripts-localizations', function ($data) {
			$data['dynamic_styles_selectors'][] = [
				'selector' => '#account-modal',
				'url' => blocksy_cdn_url(
					BLOCKSY_URL . 'static/bundle/header-account-modal-lazy.min.css'
				)
			];

			return $data;
		});

		add_action('wp_ajax_blocksy_conditions_get_all_taxonomies', function () {
			if (! current_user_can('manage_options')) {
				wp_send_json_error();
			}

			$cpts = blocksy_manager()->post_types->get_supported_post_types();

			$cpts[] = 'post';
			$cpts[] = 'page';
			$cpts[] = 'product';

			$taxonomies = [];

			foreach ($cpts as $cpt) {
				$taxonomies = array_merge($taxonomies, array_values(array_diff(
					get_object_taxonomies($cpt),
					['post_format']
				)));
			}

			$terms = [];

			foreach ($taxonomies as $taxonomy) {
				$taxonomy_object = get_taxonomy($taxonomy);

				if (! $taxonomy_object->public) {
					continue;
				}

				$local_terms = array_map(function ($tax) {
					return [
						'id' => $tax->term_id,
						'name' => $tax->name,
						'group' => get_taxonomy($tax->taxonomy)->label,
						'post_types' => get_taxonomy($tax->taxonomy)->object_type
					];
				}, get_terms(['taxonomy' => $taxonomy, 'lang' => '']));

				if (empty($local_terms)) {
					continue;
				}

				$terms = array_merge($terms, $local_terms);
			}

			$languages = [];

			if (function_exists('blocksy_get_current_language')) {
				$languages = blocksy_get_all_i18n_languages();
			}

			$users = [];

			foreach (get_users([
				'number' => 2000
			]) as $user) {
				$users[] = [
					'id' => $user->ID,
					'name' => $user->user_nicename
				];
			}

			wp_send_json_success([
				'taxonomies' => $terms,
				'languages' => $languages,
				'users' => $users
			]);
		});

		add_action('wp_ajax_blocksy_conditions_get_all_posts', function () {
			if (! current_user_can('manage_options')) {
				wp_send_json_error();
			}

			$maybe_input = json_decode(file_get_contents('php://input'), true);

			if (! $maybe_input) {
				wp_send_json_error();
			}

			if (! isset($maybe_input['post_type'])) {
				wp_send_json_error();
			}

			$query_args = [
				'posts_per_page' => 10,
				'post_type' => $maybe_input['post_type'],
				'suppress_filters' => true,
				'lang' => ''
			];

			if (
				isset($maybe_input['search_query'])
				&&
				! empty($maybe_input['search_query'])
			) {
				if (intval($maybe_input['search_query'])) {
					$query_args['p'] = intval($maybe_input['search_query']);
				} else {
					$query_args['s'] = $maybe_input['search_query'];
				}
			}

			$initial_query_args_post_type = $query_args['post_type'];

			if (strpos($initial_query_args_post_type, 'ct_cpt') !== false) {
				$query_args['post_type'] = array_diff(
					get_post_types(['public' => true]),
					['post', 'page', 'product', 'attachment', 'ct_content_block']
				);
			}

			if (strpos($initial_query_args_post_type, 'ct_all_posts') !== false) {
				$query_args['post_type'] = array_diff(
					get_post_types(['public' => true]),
					['attachment', 'ct_content_block']
				);
			}

			$query = new \WP_Query($query_args);

			$posts_result = $query->posts;

			if (isset($maybe_input['alsoInclude'])) {
				$maybe_post = get_post($maybe_input['alsoInclude'], 'display');

				if ($maybe_post) {
					$posts_result[] = $maybe_post;
				}
			}

			wp_send_json_success([
				'posts' => $posts_result
			]);
		});

		add_filter(
			'user_contactmethods',
			function ( $field ) {
				$fields['facebook'] = __( 'Facebook', 'blocksy-companion' );
				$fields['twitter'] = __( 'X (Twitter)', 'blocksy-companion' );
				$fields['linkedin'] = __( 'LinkedIn', 'blocksy-companion' );
				$fields['dribbble'] = __( 'Dribbble', 'blocksy-companion' );
				$fields['instagram'] = __( 'Instagram', 'blocksy-companion' );
				$fields['pinterest'] = __( 'Pinterest', 'blocksy-companion' );
				$fields['wordpress'] = __( 'WordPress', 'blocksy-companion' );
				$fields['github'] = __( 'GitHub', 'blocksy-companion' );
				$fields['medium'] = __( 'Medium', 'blocksy-companion' );
				$fields['youtube'] = __( 'YouTube', 'blocksy-companion' );
				$fields['vimeo'] = __( 'Vimeo', 'blocksy-companion' );
				$fields['vkontakte'] = __( 'VKontakte', 'blocksy-companion' );
				$fields['odnoklassniki'] = __( 'Odnoklassniki', 'blocksy-companion' );
				$fields['tiktok'] = __( 'TikTok', 'blocksy-companion' );
				$fields['mastodon'] = __( 'Mastodon', 'blocksy-companion' );

				$additional_fields = apply_filters(
					'blocksy:author-profile:custom-social-network',
					[]
				);

				foreach ($additional_fields as $field) {
					if (
						isset($field['id'])
						&&
						isset($field['name'])
					)  {
						$fields[$field['id']] = $field['name'];
					}
				}

				return $fields;
			}
		);

		add_filter('blocksy_changelogs_list', function ($changelogs) {
			$changelog = null;
			$access_type = get_filesystem_method();

			if ($access_type === 'direct') {
				$creds = request_filesystem_credentials(
					site_url() . '/wp-admin/',
					'', false, false,
					[]
				);

				if (WP_Filesystem($creds)) {
					global $wp_filesystem;

					$readme = $wp_filesystem->get_contents(
						BLOCKSY_PATH . '/readme.txt'
					);

					if ($readme) {
						$readme = explode('== Changelog ==', $readme);

						if (isset($readme[1])) {
							$changelogs[] = [
								'title' => __('Companion', 'blocksy-companion'),
								'changelog' => trim($readme[1])
							];
						}
					}

					if (
						blc_can_use_premium_code()
						&&
						file_exists(
							BLOCKSY_PATH . '/framework/premium/changelog.txt'
						)
					) {
						$pro_changelog = $wp_filesystem->get_contents(
							BLOCKSY_PATH . '/framework/premium/changelog.txt'
						);

						$changelogs[] = [
							'title' => __('PRO', 'blocksy-companion'),
							'changelog' => trim($pro_changelog)
						];
					}
				}
			}

			return $changelogs;
		});

		add_action('wp_enqueue_scripts', function () {
			if (! function_exists('get_plugin_data')){
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			$data = get_plugin_data(BLOCKSY__FILE__);

			if (is_admin()) return;

			if (! class_exists('Blocksy_Header_Builder_Render')) {
				return;
			}

			$render = new \Blocksy_Header_Builder_Render();

			if (
				$render->contains_item('account')
				||
				is_customize_preview()
			) {
				wp_enqueue_style(
					'blocksy-companion-header-account-styles',
					BLOCKSY_URL . 'static/bundle/header-account.min.css',
					['ct-main-styles'],
					$data['Version']
				);
			}
		}, 50);

		add_action(
			'customize_preview_init',
			function () {
				if (! function_exists('get_plugin_data')){
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}

				$data = get_plugin_data(BLOCKSY__FILE__);

				wp_enqueue_script(
					'blocksy-companion-sync-scripts',
					BLOCKSY_URL . 'static/bundle/sync.js',
					['customize-preview', 'ct-scripts', 'wp-date', 'ct-scripts', 'ct-customizer'],
					$data['Version'],
					true
				);
			}
		);
	}
}

