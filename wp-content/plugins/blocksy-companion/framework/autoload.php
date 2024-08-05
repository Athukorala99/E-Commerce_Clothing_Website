<?php

namespace Blocksy;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Blocksy autoloader.
 */
class Autoloader {
	private static $classes_map = null;

	/**
	 * Classes map.
	 *
	 * Maps Blocksy classes to file names.
     *
	 * @static
	 *
	 * @var array Classes used by blocksy.
	 */
	private static function get_classes_map() {
		if (self::$classes_map) {
			return self::$classes_map;
		}

		self::$classes_map = [
			'Capabilities' => 'framework/includes/capabilities.php',

			'ExtensionsManager' => 'framework/includes/extensions-manager.php',
			'ExtensionWithFeatures' => 'framework/includes/extension-with-features.php',
			'ExtensionsManagerApi' => 'framework/includes/extensions-manager-api.php',

			'Dashboard' => 'framework/dashboard.php',
			'ThemeIntegration' => 'framework/theme-integration.php',
			'AccountAuth' => 'framework/features/account-auth.php',
			'CacheResetManager' => 'framework/cache-reset-manager.php',

			'GoogleAnalytics' => 'framework/features/google-analytics.php',
			'OpenGraphMetaData' => 'framework/features/opengraph-meta-data.php',
			'HeaderAdditions' => 'framework/features/header.php',
			'ConditionsManager' => 'framework/features/conditions-manager.php',
			'ConditionsRulesResolver' => 'framework/features/conditions/rules-resolver.php',

			'Cli' => 'framework/cli.php',

			'SvgHandling' => 'framework/features/svg.php',
			'DynamicCss' => 'framework/features/dynamic-css.php',
			'CustomizerOptionsManager' => 'framework/features/customizer-options-manager.php',
			'DemoInstall' => 'framework/features/demo-install.php',
			'DemoInstallContentExport' => 'framework/features/demo-install/content-export.php',
			'DemoInstallWidgetsExport' => 'framework/features/demo-install/widgets-export.php',
			'DemoInstallOptionsExport' => 'framework/features/demo-install/options-export.php',

			'DemoInstallChildThemeInstaller' => 'framework/features/demo-install/child-theme.php',
			'DemoInstallPluginsInstaller' => 'framework/features/demo-install/required-plugins.php',
			'DemoInstallPluginsUninstaller' => 'framework/features/demo-install/plugins-uninstaller.php',
			'DemoInstallContentInstaller' => 'framework/features/demo-install/content-installer.php',
			'DemoInstallOptionsInstaller' => 'framework/features/demo-install/options-import.php',
			'DemoInstallWidgetsInstaller' => 'framework/features/demo-install/widgets-import.php',
			'DemoInstallContentEraser' => 'framework/features/demo-install/content-eraser.php',
			'DemoInstallFakeContentEraser' => 'framework/features/demo-install/fake-content-eraser.php',
			'DemoInstallFinalActions' => 'framework/features/demo-install/install-finish.php',
			'Premium' => 'framework/premium/pro.php',

			/**
			 * No namespace
			 */
			'_Blocksy_WP_Import' => 'framework/features/demo-install/wp-importer.php',
		];

		$autoload = [];

		if (is_readable(dirname(__FILE__) . '/premium/autoload.php')) {
			require dirname(__FILE__) . '/premium/autoload.php';
		}

		foreach ($autoload as $class_name => $file_name) {
			self::$classes_map[$class_name] = $file_name;
		}

		return self::$classes_map;
	}

	/**
	 * Run autoloader.
	 *
	 * Register a function as `__autoload()` implementation.
	 *
	 * @static
	 */
	public static function run() {
		spl_autoload_register([__CLASS__, 'autoload']);
	}

	/**
	 * Load class.
	 *
	 * For a given class name, require the class file.
	 *
	 * @static
	 *
	 * @param string $relative_class_name Class name.
	 */
	private static function load_class($relative_class_name) {
		if (isset(self::get_classes_map()[$relative_class_name])) {
			$filename = BLOCKSY_PATH . '/' . self::get_classes_map()[$relative_class_name];
		} else {
			$filename = strtolower(
				preg_replace(
					['/([a-z])([A-Z])/', '/_/', '/\\\/'],
					['$1-$2', '-', DIRECTORY_SEPARATOR],
					$relative_class_name
				)
			);

			$filename = BLOCKSY_PATH . $filename . '.php';
		}

		if (is_readable($filename)) {
			require $filename;
		}
	}

	/**
	 * Autoload.
	 *
	 * For a given class, check if it exist and load it.
	 *
	 * @static
	 *
	 * @param string $class Class name.
	 */
	private static function autoload($class) {
		if (
			0 !== strpos($class, __NAMESPACE__ . '\\')
			&&
			! isset(self::get_classes_map()['_' . $class])
		) {
			return;
		}

		$relative_class_name = preg_replace('/^' . __NAMESPACE__ . '\\\/', '', $class);

		$final_class_name = __NAMESPACE__ . '\\' . $relative_class_name;

		if (isset(self::get_classes_map()['_' . $relative_class_name])) {
			$final_class_name = $relative_class_name;
			$relative_class_name = '_' . $relative_class_name;
		}

		if (! class_exists($final_class_name)) {
			self::load_class($relative_class_name);
		}
	}
}
