<?php

class Blocksy_Manager {
	public static $instance = null;

	public $db = null;
	public $db_versioning = null;

	public $builder = null;

	public $header_builder = null;
	public $footer_builder = null;

	public $post_types = null;

	public $screen = null;

	public $dynamic_css = null;
	public $dynamic_styles_descriptor = null;
	public $woocommerce = null;
	public $colors = null;

	private $hooks = null;

	private $current_template = null;

	private $scripts_enqueued = null;

	public static function instance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_current_template() {
		if (!$this->current_template) {
			// return apply_filters('template_include', '__DEFAULT__');
		}

		return $this->current_template;
	}

	private function __construct() {
		$this->early_init();
	}

	private function early_init() {
		$this->register_autoloader();

		$this->db = new \Blocksy\Database();
		$this->db_versioning = new \Blocksy\DbVersioning();

		$this->builder = new Blocksy_Customizer_Builder();

		$this->header_builder = new Blocksy_Header_Builder();
		$this->footer_builder = new Blocksy_Footer_Builder();

		$this->post_types = new \Blocksy\CustomPostTypes();
		$this->screen = new Blocksy_Screen_Manager();
		$this->colors = new \Blocksy\Colors();

		$breadcrumbs = new \Blocksy\BreadcrumbsBuilder();
		$breadcrumbs->mount_shortcode();

		new \Blocksy\SearchModifications();

		if (class_exists('WooCommerce')) {
			$this->woocommerce = new \Blocksy\WooCommerce();
		}

		$this->dynamic_css = new \Blocksy\ThemeDynamicCss();

		$i18n_manager = new Blocksy_Translations_Manager();
		$i18n_manager->init();

		new \Blocksy\Blocks();

		register_block_pattern_category(
			'blocksy',
			[
				'label' => _x(
					'Blocksy',
					'Block pattern category',
					'blocksy'
				),
				'description' => __(
					'Patterns that contain buttons and call to actions.',
					'blocksy'
				),
			]
		);

		add_action('customize_save_after', function () {
			$i18n_manager = new Blocksy_Translations_Manager();
			$i18n_manager->register_wpml_translation_keys();
		});

		if (is_admin()) {
			add_action(
				'admin_init',
				function () {
					$i18n_manager = new Blocksy_Translations_Manager();
					$i18n_manager->register_translation_keys();
				}
			);
		}

		add_action('customize_save', function ($obj) {
			if (! $obj) {
				return;
			}

			$header_placements = $obj->get_setting('header_placements');

			if ($header_placements) {
				$current_value = $header_placements->post_value();

				if ($current_value) {
					unset($current_value['__forced_static_header__']);
					unset($current_value['__should_refresh_item__']);
					unset($current_value['__should_refresh__']);

					foreach ($current_value as $key => $value) {
						if (floatval($key)) {
							unset($current_value[$key]);
						}
					}

					$header_placements->manager->set_post_value(
						'header_placements',
						$current_value
					);
				}
			}

			$footer_placements = $obj->get_setting('footer_placements');

			if ($footer_placements) {
				$current_value = $footer_placements->post_value();

				if ($current_value) {
					unset($current_value['__forced_static_footer__']);
					unset($current_value['__should_refresh__']);
					unset($current_value['__should_refresh_item__']);

					foreach ($current_value as $key => $value) {
						if (floatval($key)) {
							unset($current_value[$key]);
						}
					}

					$footer_placements->manager->set_post_value(
						'footer_placements',
						$current_value
					);
				}
			}
		});

		add_action(
			'init',
			function () {
				$this->screen->wipe_caches();
				$this->post_types->wipe_caches();
			},
			PHP_INT_MAX
		);

		add_filter('block_parser_class', function () {
			return 'Blocksy_WP_Block_Parser';
		});

		add_filter('template_include', function ($template) {
			$this->current_template = $template;
			return $template;
		}, 900000000);

		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts'], 50);

		add_action(
			'wp_head',
			function () {
				if (defined('IFRAME_REQUEST') && IFRAME_REQUEST) {
					return;
				}

				$this->dynamic_css->load_frontend_css([
					'descriptor' => $this->dynamic_styles_descriptor
				]);
			},
			10
		);
	}

	public function register_autoloader() {
		require get_template_directory() . '/inc/classes/autoload.php';
		\Blocksy\ThemeAutoloader::run();
	}

	public function enqueue_scripts() {
		if ($this->scripts_enqueued) {
			return;
		}

		$this->scripts_enqueued = true;

		$theme = blocksy_get_wp_parent_theme();

		$m = new \Blocksy\FontsManager();

		$this->dynamic_styles_descriptor = $this
			->dynamic_css
			->get_dynamic_styles_descriptor();

		$m->load_dynamic_google_fonts($this->dynamic_styles_descriptor['google_fonts']);

		$static_files = new Blocksy_Static_Css_Files();
		$static_files->enqueue_static_files($theme);

		wp_register_script(
			'ct-events',
			get_template_directory_uri() . '/static/bundle/events.js',
			[],
			$theme->get('Version'),
			true
		);

		wp_enqueue_script(
			'ct-scripts',
			get_template_directory_uri() . '/static/bundle/main.js',
			[],
			$theme->get('Version'),
			true
		);

		$data = apply_filters('blocksy:general:ct-scripts-localizations', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'public_url' => blocksy_cdn_url(
				get_template_directory_uri() . '/static/bundle/'
			),
			'rest_url' => get_rest_url(),
			'search_url' => get_search_link('QUERY_STRING'),
			'show_more_text' => __('Show more', 'blocksy'),
			'more_text' => __('More', 'blocksy'),
			'search_live_results' => __('Search results', 'blocksy'),

			'search_live_no_result' => __('No results', 'blocksy'),
			'search_live_one_result' => _n(
				'You got %s result. Please press Tab to select it.',
				'You got %s results. Please press Tab to select one.',
				1,
				'blocksy'
			),
			'search_live_many_results' => _n(
				'You got %s result. Please press Tab to select it.',
				'You got %s results. Please press Tab to select one.',
				5,
				'blocksy'
			),

			'expand_submenu' => __('Expand dropdown menu', 'blocksy'),
			'collapse_submenu' => __('Collapse dropdown menu', 'blocksy'),

			'dynamic_js_chunks' => blocksy_manager()->get_dynamic_js_chunks(),

			'dynamic_styles' => [
				'lazy_load' => add_query_arg(
					'ver',
					$theme->get('Version'),
					blocksy_cdn_url(
						get_template_directory_uri() . '/static/bundle/non-critical-styles.min.css'
					)
				),

				'flexy_styles' => add_query_arg(
					'ver',
					$theme->get('Version'),
					blocksy_cdn_url(
						get_template_directory_uri() . '/static/bundle/flexy.min.css'
					)
				),

				'search_lazy' => add_query_arg(
					'ver',
					$theme->get('Version'),
					blocksy_cdn_url(
						get_template_directory_uri() . '/static/bundle/non-critical-search-styles.min.css'
					)
				),

				'back_to_top' => add_query_arg(
					'ver',
					$theme->get('Version'),
					blocksy_cdn_url(
						get_template_directory_uri() . '/static/bundle/back-to-top.min.css'
					)
				)
			],

			'dynamic_styles_selectors' => []
		]);

		foreach ($data['dynamic_styles_selectors'] as $dynamic_style_index => $dynamic_style) {
			$data['dynamic_styles_selectors'][$dynamic_style_index]['url'] = add_query_arg(
				'ver',
				$theme->get('Version'),
				$dynamic_style['url']
			);
		}

		$maybe_current_language = blocksy_get_current_language('slug');

		if ($maybe_current_language !== '__NOT_KNOWN__') {
			$data['lang'] = $maybe_current_language;
		}

		if (is_customize_preview()) {
			$data['customizer_sync'] = blocksy_customizer_sync_data();
		}

		wp_localize_script(
			'ct-scripts',
			'ct_localizations',
			$data
		);

		if (defined('WP_DEBUG') && WP_DEBUG) {
			wp_localize_script(
				'ct-scripts',
				'WP_DEBUG',
				['debug' => true]
			);
		}

		if (is_singular() && comments_open() && get_option('thread_comments')) {
			wp_enqueue_script('comment-reply');
		}
	}

	public function get_dynamic_js_chunks() {
		$all_chunks = apply_filters(
			'blocksy:frontend:dynamic-js-chunks',
			[]
		);

		global $wp_scripts;

		$theme = blocksy_get_wp_parent_theme();

		foreach ($all_chunks as $index => $chunk) {
			$all_chunks[$index]['url'] = add_query_arg(
				'ver',
				$theme->get('Version'),
				$chunk['url']
			);

			if (! isset($chunk['deps'])) {
				continue;
			}

			$deps_data = [];

			foreach ($chunk['deps'] as $dep_id) {
				if (!isset($wp_scripts->registered[$dep_id])) {
					continue;
				}

				$src = $wp_scripts->registered[$dep_id]->src;
				$deps_data[$dep_id] = '';

				if (strpos($src, site_url()) === false) {
					$deps_data[$dep_id] = site_url();
				}

				$deps_data[$dep_id] .= $wp_scripts->registered[$dep_id]->src;
			}

			$all_chunks[$index]['deps_data'] = $deps_data;
		}

		return $all_chunks;
	}

	public function get_prefix_title_actions($args = []) {
		$args = wp_parse_args($args, [
			'prefix' => '',
			'areas' => []
		]);

		return apply_filters(
			'blocksy:options:prefix-global-actions',
			[],
			$args
		);
	}

	public function get_hooks() {
		if (! $this->hooks) {
			$this->hooks = new \Blocksy\WpHooksManager();
		}

		return $this->hooks;
	}
}

function blocksy_manager() {
	return Blocksy_Manager::instance();
}
