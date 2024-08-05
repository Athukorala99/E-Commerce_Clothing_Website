<?php

require_once dirname(__FILE__) . '/helpers.php';

class BlocksyExtensionNewsletterSubscribe {
	public function __construct() {
		add_action('enqueue_block_editor_assets', function () {
			if (! function_exists('get_plugin_data')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$data = get_plugin_data(BLOCKSY__FILE__);

			wp_enqueue_script(
				'blocksy-ext-newsletter-subscribe-admin-scripts',
				BLOCKSY_URL .
					'framework/extensions/newsletter-subscribe/admin-static/bundle/main.js',
				['ct-options-scripts'],
				$data['Version']
			);

			wp_localize_script(
				'blocksy-ext-newsletter-subscribe-admin-scripts',
				'blocksy_ext_newsletter_subscribe_localization',
				[
					'public_url' =>
						BLOCKSY_URL .
						'framework/extensions/newsletter-subscribe/admin-static/bundle/',
				]
			);
		});

		add_action('customize_controls_enqueue_scripts', function () {
			if (! function_exists('get_plugin_data')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$data = get_plugin_data(BLOCKSY__FILE__);

			wp_register_script(
				'blocksy-ext-newsletter-subscribe-admin-scripts',
				BLOCKSY_URL . 'framework/extensions/newsletter-subscribe/admin-static/bundle/main.js',
				[],
				$data['Version'],
				true
			);

			wp_localize_script(
				'blocksy-ext-newsletter-subscribe-admin-scripts',
				'blocksy_ext_newsletter_subscribe_localization',
				[
					'public_url' => BLOCKSY_URL . 'framework/extensions/newsletter-subscribe/admin-static/bundle/',
				]
			);
		});

		add_filter(
			'render_block',
			function ($block_content, $block) {
				if ($block['blockName'] === 'blocksy/newsletter') {
					wp_enqueue_style('blocksy-block-newsletter-styles');
				}

				return $block_content;
			},
			10,
			2
		);

		add_action(
			'wp_enqueue_scripts',
			function () {
				if (! function_exists('get_plugin_data')) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				$data = get_plugin_data(BLOCKSY__FILE__);

				if (is_admin()) {
					return;
				}

				wp_register_style(
					'blocksy-block-newsletter-styles',
					BLOCKSY_URL . 'framework/extensions/newsletter-subscribe/static/bundle/main.min.css',
					['ct-main-styles'],
					$data['Version']
				);

				$obj = get_queried_object();

				if (
					$obj
					&&
					! empty($obj->post_content)
					&&
					has_shortcode(
						$obj->post_content,
						'blocksy_newsletter_subscribe'
					)
				) {
					wp_enqueue_style('blocksy-block-newsletter-styles');
				}

				if (
					blocksy_get_theme_mod('newsletter_subscribe_single_post_enabled', 'yes') === 'yes'
					&&
					get_post_type() === 'post'
				) {
					wp_enqueue_style('blocksy-block-newsletter-styles');
				}
			},
			45
		);

		add_filter('blocksy:frontend:dynamic-js-chunks', function ($chunks) {
			$chunks[] = [
				'id' => 'blocksy_ext_newsletter_subscribe',
				'selector' => implode(', ', [
					'.ct-newsletter-subscribe-form:not([data-skip-submit])',
				]),
				'url' => blocksy_cdn_url(
					BLOCKSY_URL .
						'framework/extensions/newsletter-subscribe/static/bundle/main.js'
				),
				'trigger' => 'submit',
			];

			return $chunks;
		});

		add_filter(
			'blocksy_single_posts_end_customizer_options',
			function ($opts, $prefix) {
				if ($prefix !== 'single_blog_post') {
					return $opts;
				}

				$opts['newsletter_subscribe_single_post_enabled'] = blocksy_get_options(
					dirname(__FILE__) . '/customizer.php',
					[],
					false
				);

				return $opts;
			},
			10,
			2
		);

		add_filter(
			'blocksy_extensions_metabox_post:elements:before',
			function ($opts) {
				$opts['disable_subscribe_form'] = [
					'label' => __(
						'Disable Subscribe Form',
						'blocksy-companion'
					),
					'type' => 'ct-switch',
					'value' => 'no',
				];

				return $opts;
			},
			5
		);

		add_action('customize_preview_init', function () {
			if (!function_exists('get_plugin_data')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$data = get_plugin_data(BLOCKSY__FILE__);

			wp_enqueue_script(
				'blocksy-newsletter-subscribe-customizer-sync',
				BLOCKSY_URL .
					'framework/extensions/newsletter-subscribe/admin-static/bundle/sync.js',
				['customize-preview', 'ct-customizer'],
				$data['Version'],
				true
			);
		});

		add_action('wp_ajax_blc_newsletter_subscribe_process_ajax_subscribe', [
			$this,
			'newsletter_subscribe_process_ajax_subscribe',
		]);

		add_action(
			'wp_ajax_nopriv_blc_newsletter_subscribe_process_ajax_subscribe',
			[$this, 'newsletter_subscribe_process_ajax_subscribe']
		);

		add_shortcode('blocksy_newsletter_subscribe', function (
			$args,
			$content
		) {
			$args = wp_parse_args($args, [
				'has_title' => false,
				'has_description' => false,

				'button_text' => __('Subscribe', 'blocksy-companion'),

				// no | yes
				'has_name' => 'no',

				'name_label' => __('Your name', 'blocksy-companion'),
				'email_label' => __('Your email', 'blocksy-companion'),
				'list_id' => '',
				'class' => '',
			]);

			$args['class'] =
				'ct-newsletter-subscribe-shortcode ' . $args['class'];

			return blc_ext_newsletter_subscribe_output_form($args);
		});

		add_action(
			'blocksy:global-dynamic-css:enqueue',
			'BlocksyExtensionNewsletterSubscribe::add_global_styles',
			10,
			3
		);

		add_action('init', [$this, 'blocksy_newsletter_block']);
		add_action('enqueue_block_editor_assets', [$this, 'enqueue_admin']);


		add_filter('blocksy:gutenberg-blocks-data', function ($data) {
			$options_file =
				BLOCKSY_PATH .
				'framework/extensions/newsletter-subscribe/ct-newsletter-subscribe/options.php';

			$options = blocksy_akg(
				'options',
				blocksy_get_variables_from_file(
					$options_file,
					['options' => []]
				)
			);

			$data['newsletter'] = $options;

			return $data;
		});
	}

	public function render_block($attributes) {
		$file_path = BLOCKSY_PATH . 'framework/extensions/newsletter-subscribe/ct-newsletter-subscribe/view.php';

		if (! file_exists($file_path)) {
			return '<p>Default widget view. Please create a <i>view.php</i> file.</p>';
		}

		return blocksy_render_view($file_path, [
			'atts' => $attributes,
		]);
	}

	public function blocksy_newsletter_block() {
		register_block_type('blocksy/newsletter', [
			'render_callback' => [$this, 'render_block'],
		]);
	}

	public function enqueue_admin() {
		$deps = [
			'wp-blocks',
			'wp-element',
			'wp-block-editor',
		];

		global $wp_customize;

		if ($wp_customize) {
			$deps[] = 'ct-customizer-controls';
		} else {
			$deps[] = 'ct-options-scripts';
		}

		wp_enqueue_script(
			'blocksy/newsletter',
			BLOCKSY_URL .
				'framework/extensions/newsletter-subscribe/admin-static/bundle/newsletter-block.js',
				$deps
		);

		$data = [
			'has_cookies_checkbox' => function_exists('blocksy_ext_cookies_checkbox'),
		];

		wp_localize_script(
			'blocksy/newsletter',
			'blc_newsletter_data',
			$data
		);

		wp_enqueue_style(
			'blocksy/newsletter',
			BLOCKSY_URL .
				'framework/extensions/newsletter-subscribe/admin-static/bundle/admin.min.css'
		);
	}

	public static function add_global_styles($args) {
		blocksy_theme_get_dynamic_styles(
			array_merge(
				[
					'path' => dirname(__FILE__) . '/global.php',
					'chunk' => 'global',
				],
				$args
			)
		);
	}

	public static function onDeactivation() {
		remove_action(
			'blocksy:global-dynamic-css:enqueue',
			'BlocksyExtensionNewsletterSubscribe::add_global_styles',
			10,
			3
		);
	}

	public function newsletter_subscribe_process_ajax_subscribe() {
		if (!isset($_POST['EMAIL'])) {
			wp_send_json_error();
		}

		if (!isset($_POST['GROUP'])) {
			wp_send_json_error();
		}

		$email = $_POST['EMAIL'];
		$name = '';
		$group = $_POST['GROUP'];

		if (isset($_POST['FNAME'])) {
			$name = $_POST['FNAME'];
		}

		$manager = \Blocksy\Extensions\NewsletterSubscribe\Provider::get_for_settings();

		$result = $manager->subscribe_form([
			'email' => $email,
			'name' => $name,
			'group' => $group,
		]);

		wp_send_json_success($result);
	}
}
