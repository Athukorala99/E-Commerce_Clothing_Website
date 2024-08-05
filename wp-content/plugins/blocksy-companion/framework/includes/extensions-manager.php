<?php

namespace Blocksy;

class ExtensionsManager {
	/**
	 * Collection of all the activated extensions.
	 *
	 * @var array The array of all the extension objects.
	 */
	private $extensions = [];
	private $exts_classes_map = [];

	private function get_option_name() {
		return 'blocksy_active_extensions';
	}

	public function get($id, $args = []) {
		$args = wp_parse_args($args, [
			// regular | preboot | full
			'type' => 'regular',
		]);

		if (! isset($this->extensions[$id])) {
			return null;
		}

		if ($args['type'] === 'full') {
			return $this->extensions[$id];
		}

		if ($args['type'] === 'preboot') {
			if (! isset($this->extensions[$id]['__object_preboot'])) {
				return null;
			}

			return $this->extensions[$id]['__object_preboot'];
		}

		if (! isset($this->extensions[$id]['__object'])) {
			return null;
		}

		return $this->extensions[$id]['__object'];
	}

	/**
	 * Collect all available extensions and activate the ones that have to be so.
	 */
	public function __construct() {
		$this->read_installed_extensions();

		spl_autoload_register([$this, 'perform_autoload']);

		if (wp_doing_ajax()) {
			add_action('init', function () {
				if (
					$this->is_dashboard_page()
					||
					(
						isset($_REQUEST['action'])
						&&
						strpos($_REQUEST['action'], 'blocksy') !== false
					)
				) {
					$this->do_extensions_preboot();
				}
			});
		} else {
			if ($this->is_dashboard_page()) {
				$this->do_extensions_preboot();
			}
		}

		foreach ($this->get_activated_extensions() as $single_id) {
			$this->boot_activated_extension_for($single_id);
		}

		add_action(
			'activate_blocksy-companion/blocksy-companion.php',
			[$this, 'handle_activation'],
			11
		);

		add_action(
			'deactivate_blocksy-companion/blocksy-companion.php',
			[$this, 'handle_deactivation'],
			11
		);
	}

	public function handle_activation() {
		ob_start();

		foreach ($this->get_activated_extensions() as $id) {
			if (method_exists($this->get_class_name_for($id), "onActivation")) {
				call_user_func([
					$this->get_class_name_for($id),
					'onActivation'
				]);
			}
		}

		ob_get_clean();
	}

	public function handle_deactivation() {
		foreach ($this->get_activated_extensions() as $id) {
			if (method_exists($this->get_class_name_for($id), "onDeactivation")) {
				call_user_func([
					$this->get_class_name_for($id),
					'onDeactivation'
				]);
			}
		}
	}

	public function do_extensions_preboot() {
		foreach (array_keys($this->get_extensions()) as $single_id) {
			$this->maybe_do_extension_preboot($single_id);
		}
	}

	private function is_dashboard_page() {
		global $pagenow;

		if (
			isset($_SERVER['HTTP_REFERER'])
			&&
			strpos($_SERVER['HTTP_REFERER'], 'ct-dashboard') !== false
		) {
			return true;
		}

		$is_ct_settings =
			// 'themes.php' === $pagenow &&
			isset( $_GET['page'] ) && 'ct-dashboard' === $_GET['page'];

		return $is_ct_settings;
	}

	public function get_extensions($args = []) {
		$args = wp_parse_args($args, [
			'require_config' => false,
		]);

		if ($args['require_config']) {
			foreach ($this->extensions as $id => $extension) {
				$this->extensions[$id]['config'] = $this->read_config_for(
					$extension['path']
				);
			}

			$this->register_fake_extensions();
		}

		return $this->extensions;
	}

	public function can($capability = 'install_plugins') {
		$user = wp_get_current_user();

		// return array_intersect(['administrator'], $user->roles );

		if (is_multisite()) {
			// Only network admin can change files that affects the entire network.
			$can = current_user_can_for_blog(
				get_current_blog_id(),
				$capability
			);
		} else {
			$can = current_user_can($capability);
		}

		if ($can) {
			// Also you can use this method to get the capability.
			$can = $capability;
		}

		return $can;
	}

	public function activate_extension($id) {
		if (! isset($this->extensions[$id])) {
			return;
		}

		if (! $this->extensions[$id]['path']) {
			return;
		}

		$activated = $this->get_activated_extensions();

		if (! in_array(strtolower($id), $activated)) {
			$path = $this->extensions[$id]['path'];
			require_once($path . '/extension.php');

			if (method_exists($this->get_class_name_for($id), "onActivation")) {
				call_user_func([
					$this->get_class_name_for($id),
					'onActivation'
				]);
			}

			$class = $this->get_class_name_for($id);

			// Init extension right away.
			new $class;
		}

		$activated[] = strtolower($id);

		update_option($this->get_option_name(), array_unique($activated));

		do_action('blocksy:dynamic-css:refresh-caches');
	}

	public function deactivate_extension($id) {
		if (! isset($this->extensions[$id])) {
			return;
		}

		if (! $this->extensions[$id]['path']) {
			return;
		}

		$activated = $this->get_activated_extensions();

		if (in_array(strtolower($id), $activated)) {
			if (method_exists($this->get_class_name_for($id), "onDeactivation")) {
				call_user_func([
					$this->get_class_name_for($id),
					'onDeactivation'
				]);
			}
		}

		update_option($this->get_option_name(), array_diff(
			$activated,
			[$id]
		));

		do_action('blocksy:dynamic-css:refresh-caches');
	}

	private function read_installed_extensions() {
		$paths_to_look_for_extensions = apply_filters(
			'blocksy_extensions_paths',
			[
				BLOCKSY_PATH . 'framework/extensions'
			]
		);

		foreach ($paths_to_look_for_extensions as $single_path) {
			$all_extensions = glob($single_path . '/*', GLOB_ONLYDIR);

			foreach ($all_extensions as $single_extension) {
				$this->register_extension_for($single_extension);
			}
		}

		$this->register_fake_extensions();
	}

	private function register_fake_extensions() {
		if (
			blc_can_use_premium_code()
			&&
			blc_get_capabilities()->get_plan() !== 'free'
		) {
			return;
		}

		$preliminary_info = $this->get_preliminary_exts_info();

		foreach ($preliminary_info as $id => $info) {
			$this->extensions[$id] = [
				'path' => null,
				'__object' => null,
				'config' => $info,
				'readme' => '',
				'data' => null
			];
		}
	}

	public function get_preliminary_exts_info() {
		return blc_exts_get_preliminary_config();
	}

	private function register_extension_for($path) {
		$id = str_replace('_', '-', basename($path));

		if (isset($this->extensions[$id])) return;

		$this->extensions[$id] = [
			'path' => $path,
			'__object' => null,
			'config' => null,
			'data' => null
		];

		$this->read_autoload_for($id, $path);
	}

	private function maybe_do_extension_preboot($id) {
		if (! isset($this->extensions[$id])) return false;
		if (isset($this->extensions[$id]['__object_preboot'])) return;

		$class_name = explode('-', $id);
		$class_name = array_map('ucfirst', $class_name);
		$class_name = 'BlocksyExtension' . implode('', $class_name) . 'PreBoot';

		$maybe_config = null;

		if (isset($this->get_preliminary_exts_info()[$id])) {
			$maybe_config = $this->get_preliminary_exts_info()[$id];
		}

		if (
			$maybe_config
			&&
			isset($maybe_config['pro'])
			&&
			$maybe_config['pro']
		) {
			if (blc_get_capabilities()->get_plan() === 'free') {
				return;
			}

			if (isset($maybe_config['plans'])) {
				if (! in_array(
					blc_get_capabilities()->get_plan(),
					$maybe_config['plans']
				)) {
					return;
				}
			}
		}

		$path = $this->extensions[$id]['path'];

		if (! $path) {
			return;
		}

		if (! @is_readable($path . '/pre-boot.php')) {
			return;
		}

		if (! file_exists($path . '/pre-boot.php')) {
			return;
		}

		require_once($path . '/pre-boot.php');

		$this->extensions[$id]['__object_preboot'] = new $class_name();

		if (method_exists(
			$this->extensions[$id]['__object_preboot'], 'ext_data'
		)) {
			$this->extensions[$id]['data'] = $this->extensions[
				$id
			]['__object_preboot']->ext_data();
		}
	}

	private function boot_activated_extension_for($id) {
		if (! isset($this->extensions[$id])) return false;
		if (! isset($this->extensions[$id]['path'])) return false;
		if (! $this->extensions[$id]['path']) return false;

		if (isset($this->extensions[$id]['__object'])) return;

		$maybe_config = null;

		if (isset($this->get_preliminary_exts_info()[$id])) {
			$maybe_config = $this->get_preliminary_exts_info()[$id];
		}

		if (
			$maybe_config
			&&
			isset($maybe_config['pro'])
			&&
			$maybe_config['pro']
		) {
			if (blc_get_capabilities()->get_plan() === 'free') {
				return;
			}

			if (isset($maybe_config['plans'])) {
				if (! in_array(
					blc_get_capabilities()->get_plan(),
					$maybe_config['plans']
				)) {
					return;
				}
			}
		}

		$class_name = explode('-', $id);
		$class_name = array_map('ucfirst', $class_name);
		$class_name = 'BlocksyExtension' . implode('', $class_name);

		$path = $this->extensions[$id]['path'];

		if (! $path) {
			return;
		}

		if (! @is_readable($path . '/extension.php')) {
			return;
		}

		if (! file_exists($path . '/extension.php')) {
			return;
		}

		require_once($path . '/extension.php');

		$this->extensions[$id]['__object'] = new $class_name();
	}

	private function get_class_name_for($id, $args = []) {
		$args = wp_parse_args($args, [
			'prefix' => true
		]);

		$class_name = explode('-', $id);
		$class_name = array_map('ucfirst', $class_name);
		$class_name = implode('', $class_name);

		if ($args['prefix']) {
			$class_name = 'BlocksyExtension' . $class_name;
		}

		return $class_name;
	}

	private function read_config_for($file_path) {
		$_extract_variables = ['config' => []];

		if (is_readable($file_path . '/config.php')) {
			require $file_path . '/config.php';

			foreach ($_extract_variables as $variable_name => $default_value) {
				if (isset($$variable_name)) {
					$_extract_variables[$variable_name] = $$variable_name;
				}
			}
		}

		$name = explode('-', basename($file_path));
		$name = array_map('ucfirst', $name);
		$name = implode(' ', $name);

		$_extract_variables['config'] = array_merge(
			[
				'name' => $name,
				'description' => '',
				'pro' => false,
				'hidden' => false
			],
			$_extract_variables['config']
		);

		return $_extract_variables['config'];
	}

	private function read_autoload_for($id, $file_path) {
		$_extract_variables = ['autoload' => []];

		if (is_readable($file_path . '/autoload.php')) {
			require $file_path . '/autoload.php';

			foreach ($_extract_variables as $variable_name => $default_value) {
				if (isset($$variable_name)) {
					$_extract_variables[$variable_name] = $$variable_name;
				}
			}
		}

		$ext_class_name = $this->get_class_name_for($id, ['prefix' => false]);

		foreach ($_extract_variables['autoload'] as $class_name => $path) {
			$final_class_name = 'Blocksy\\Extensions\\';
			$final_class_name .= $ext_class_name;
			$final_class_name .= '\\' . $class_name;

			$this->exts_classes_map[$final_class_name] = path_join(
				$file_path,
				$path
			);
		}
	}

	private function get_activated_extensions() {
		return get_option($this->get_option_name(), []);
	}

	private function perform_autoload($class) {
		if (! isset($this->exts_classes_map[$class])) {
			return;
		}

		$filename = $this->exts_classes_map[$class];

		if (is_readable($filename)) {
			require $filename;
		}
	}
}

