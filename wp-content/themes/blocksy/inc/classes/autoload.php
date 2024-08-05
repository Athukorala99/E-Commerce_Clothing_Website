<?php

namespace Blocksy;

class ThemeAutoloader {

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
		return apply_filters('blocksy_theme_autoloader_classes_map', [
			'WordPressActionsManager' => 'inc/classes/trait-wordpress-actions-manager.php',

			'SearchModifications' => 'inc/components/search.php',

			'Database' => 'inc/classes/database.php',
			'DbVersioning' => 'inc/classes/theme-db-versioning.php',

			'DbVersioning\\V200' => 'inc/classes/db-versioning/v2-0-0.php',
			'DbVersioning\\V202' => 'inc/classes/db-versioning/v2-0-2.php',
			'DbVersioning\\V203' => 'inc/classes/db-versioning/v2-0-3.php',
			'DbVersioning\\V209' => 'inc/classes/db-versioning/v2-0-9.php',

			'DbVersioning\\DefaultValuesCleaner' => 'inc/classes/db-versioning/utils/db-default-values-cleaner.php',

			'Database\\SearchReplace' => 'inc/classes/db-versioning/utils/db-search-replace.php',
			'Database\\Utils' => 'inc/classes/db-versioning/utils/db-utils.php',
			'Database\\SearchReplacer' => 'inc/classes/db-versioning/utils/db-search-replacer.php',

			'FontsManager' => 'inc/css/fonts-manager.php',

			'WpHooksManager' => 'inc/classes/hooks-manager.php',

			'CustomPostTypes' => 'inc/integrations/custom-post-types.php',
			'ThemeDynamicCss' => 'inc/dynamic-css.php',

			'BreadcrumbsBuilder' => 'inc/components/breadcrumbs.php',

			'WooCommerce' => 'inc/components/woocommerce-integration.php',

			'Blocks' => 'inc/components/blocks/blocks.php',
			'GutenbergBlock' => 'inc/components/blocks/gutenberg-block.php',
			'LegacyWidgetsTransformer' => 'inc/components/blocks/legacy-widgets-transformer.php',
			'LegacyWidgetsPostsTransformer' => 'inc/components/blocks/legacy/legacy-posts-transformer.php',
			'LegacyWidgetsAboutMeTransformer' => 'inc/components/blocks/legacy/legacy-about-me-transformer.php',
			'LegacyWidgetsContactInfoTransformer' => 'inc/components/blocks/legacy/legacy-contact-info-transformer.php',
			'LegacyWidgetsSocialsTransformer' => 'inc/components/blocks/legacy/legacy-socials-transformer.php',
			'LegacyWidgetsAdvertisementTransformer' => 'inc/components/blocks/legacy/legacy-advertisement-transformer.php',
			'LegacyWidgetsNewsletterSubscribeTransformer' => 'inc/components/blocks/legacy/legacy-newsletter-subscribe.php',
			'LegacyWidgetsQuoteTransformer' => 'inc/components/blocks/legacy/legacy-quote-transformer.php',

			'Blocks\\BlockWrapper' => 'inc/components/blocks/block-wrapper/block.php',
			'Blocks\\BreadCrumbs' => 'inc/components/blocks/breadcrumbs/block.php',
			'Blocks\\Query' => 'inc/components/blocks/query/block.php',
			'Blocks\\DynamicData' => 'inc/components/blocks/dynamic-data/block.php',

			/**
			 * No namespace
			 */
			'_Blocksy_Css_Injector' => 'inc/classes/class-ct-css-injector.php',
		]);
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
			$filename = get_template_directory() . '/' . self::get_classes_map()[$relative_class_name];
		} else {
			$filename = strtolower(
				preg_replace(
					['/([a-z])([A-Z])/', '/_/', '/\\\/'],
					['$1-$2', '-', DIRECTORY_SEPARATOR],
					$relative_class_name
				)
			);

			$filename = get_template_directory() . $filename . '.php';
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

