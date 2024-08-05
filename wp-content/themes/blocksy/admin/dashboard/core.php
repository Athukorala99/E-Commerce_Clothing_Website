<?php
/**
 * Admin Dashboard
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package Blocksy
 */

defined( 'ABSPATH' ) || die( "Don't run this file directly!" );

function blocksy_is_dashboard_page() {
	global $pagenow;

	$is_ct_settings =
		// 'themes.php' === $pagenow &&
		isset( $_GET['page'] ) && 'ct-dashboard' === $_GET['page'];

	return $is_ct_settings;
}

class Blocksy_Dashboard_Page {
	private $templates;

	private $page_slug = 'ct-dashboard';

	public function is_dashboard_page() {
		return blocksy_is_dashboard_page();
	}

	public function __construct() {
		add_action(
			'admin_menu',
			[$this, 'setup_framework_page'],
			5
		);

		if (is_admin() && defined('DOING_AJAX') && DOING_AJAX) {
			$plugins_api = new Blocksy_Admin_Dashboard_API_Premium_Plugins();
			$plugins_api->attach_ajax_actions();

			$api = new Blocksy_Admin_Dashboard_API();
			$api->attach_ajax_actions();
		}

		if ($this->is_dashboard_page()) {
			add_action(
				'admin_enqueue_scripts',
				[$this, 'enqueue_static']
			);
		}

		if ($this->is_dashboard_page()) {
			add_action(
				'admin_print_scripts',
				function () {
					global $wp_filter;

					if (is_user_admin()) {
						if (isset($wp_filter['user_admin_notices'])) {
							unset($wp_filter['user_admin_notices']);
						}
					} elseif (isset($wp_filter['admin_notices'])) {
						unset($wp_filter['admin_notices']);
					}

					if (isset($wp_filter['all_admin_notices'])) {
						unset($wp_filter['all_admin_notices']);
					}
				}
			);
		}
	}

	public function enqueue_static() {
		$theme = blocksy_get_wp_parent_theme();

		$dependencies = [
			'underscore',
			'wp-util',
			'ct-events',
			'ct-options-scripts'
		];

		wp_enqueue_script(
			'ct-dashboard-scripts',
			get_template_directory_uri() . '/admin/dashboard/static/bundle/main.js',
			$dependencies,
			$theme->get('Version'),
            false
		);

		if (defined('WP_DEBUG')) {
			wp_localize_script(
				'ct-dashboard-scripts',
				'WP_DEBUG',
				[ 'debug' => true ]
			);
		}

		$manager = new Blocksy_Plugin_Manager();
		$plugins_config = $manager->get_config();

		wp_localize_script(
			'ct-dashboard-scripts',
			'ctDashboardLocalizations',
			[
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
				'customizer_url' => admin_url('/customize.php?autofocus'),
				'theme_version' => $theme->get('Version'),
				'theme_name'    => $theme->get('Name'),
				'theme_custom_description' => $theme->get('CustomDescription'),
				'is_child_theme' => is_child_theme(),
				'child_theme_exists' => isset(wp_get_themes()['blocksy-child']),
				'home_url' => home_url(),
				'clean_install_plugins' => $plugins_config,
				'is_companion_active' => $manager->get_companion_status()['status'],
				'companion_download_link' => 'https://creativethemes.com/blocksy/companion/',
				'child_download_link' => 'https://creativethemes.com/downloads/blocksy-child.zip',
				'plugin_data' => apply_filters('blocksy_dashboard_localizations', []),
				'support_url' => apply_filters(
					'blocksy_dashboard_support_url',
					'https://creativethemes.com/blocksy/support/'
				),
				'dashboard_has_heading' => apply_filters(
					'blocksy_dashboard_has_heading',
					'yes'
				)
			]
		);

		wp_enqueue_style(
			'ct-dashboard-styles',
			get_template_directory_uri() . '/admin/dashboard/static/bundle/main.min.css',
			[],
			$theme->get('Version')
		);

		if (is_rtl()) {
			wp_enqueue_style(
				'ct-dashboard-rtl-styles',
				get_template_directory_uri() . '/admin/dashboard/static/bundle/main-rtl.min.css',
				['ct-dashboard-styles'],
				$theme->get('Version')
			);
		}
	}

	public function setup_framework_page() {
		$theme = blocksy_get_wp_parent_theme();

		if (! current_user_can('manage_options')) {
			return;
		}

		$welcome_page_options = [
			'title'            => $theme->get('Name'),
			'menu-title'       => $theme->get('Name'),
			'permision'        => 'manage_options',
			'top-level-handle' => $this->page_slug,
			'callback'         => [ $this, 'welcome_page_template' ],
			'icon-url' => apply_filters(
				'blocksy:dashboard:icon-url',
				'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKCSB2aWV3Qm94PSIwIDAgMzUgMzUiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDM1IDM1OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxwYXRoIGQ9Ik0yMS42LDIxLjNjMCwwLjYtMC41LDEuMS0xLjEsMS4xaC0zLjVsLTAuOS0yLjJoNC40QzIxLjEsMjAuMiwyMS42LDIwLjcsMjEuNiwyMS4zeiBNMjAuNiwxMy41aC00LjRsMC45LDIuMmgzLjUKCWMwLjYsMCwxLjEtMC41LDEuMS0xLjFDMjEuNiwxNCwyMS4xLDEzLjUsMjAuNiwxMy41eiBNMzUsMTcuNUMzNSwyNy4yLDI3LjIsMzUsMTcuNSwzNUM3LjgsMzUsMCwyNy4yLDAsMTcuNUMwLDcuOCw3LjgsMCwxNy41LDAKCUMyNy4yLDAsMzUsNy44LDM1LDE3LjV6IE0yNSwxNy45YzAuNy0wLjksMS4xLTIuMSwxLjEtMy40YzAtMS4yLTAuNC0yLjQtMS4xLTMuM2MtMS0xLjQtMi42LTIuMy00LjQtMi4zYzAsMC0wLjEsMC0wLjEsMHYwSDkuOQoJYy0wLjMsMC0wLjUsMC4zLTAuNCwwLjVsMi42LDYuMkg5LjljLTAuMywwLTAuNSwwLjMtMC40LDAuNUwxNCwyNi45aDYuNWMzLjEsMCw1LjYtMi41LDUuNi01LjZDMjYuMiwyMCwyNS44LDE4LjksMjUsMTcuOQoJQzI1LjEsMTcuOSwyNS4xLDE3LjksMjUsMTcuOXoiLz4KPC9zdmc+Cg=='
			),
			'position' => 2,
		];

		$result = apply_filters(
			'blocksy_add_menu_page',
			false,
			$welcome_page_options
		);

		if (! $result) {
			add_theme_page(
				$welcome_page_options['title'],
				$welcome_page_options['menu-title'],
				$welcome_page_options['permision'],
				$welcome_page_options['top-level-handle'],
				$welcome_page_options['callback']
			);
		}
	}

	public function welcome_page_template() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( __( 'You do not have sufficient permissions to access this page.', 'blocksy' ) ) );
		}

		echo '<div id="ct-dashboard"></div>';
	}
}

new Blocksy_Dashboard_Page();
