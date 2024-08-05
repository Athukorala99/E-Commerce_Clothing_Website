<?php
/**
 * Plugin Name: Depicter
 * Plugin URI: https://depicter.com
 * Description: Make animated and interactive image slider, video slider, post slider and carousel which work smoothly across devices.
 * Version: 2.0.9
 * Requires at least: 4.9
 * Requires PHP: 7.2.5
 * Author: Averta
 * Author URI: http://averta.net
 * License: GPL-2.0-only
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: depicter
 * Domain Path: /languages
 *
 *
 * @package Depicter
 */

const DEPICTER_VERSION = '2.0.8 ';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$name = trim( get_file_data( __FILE__, [ 'Plugin Name' ] )[0] );


// Make sure requirements are met
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'requirement.php';

if( ! depicter_requirements_satisfied( $name, '7.2.5' ) ){
	// requirements are not met, stop further execution.
	// depicter_requirements_satisfied() will automatically add an admin notice.
	return;
}


// Make sure we can load a compatible version of WP Emerge.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'version.php';

if ( ! depicter_should_load_framework( $name, '0.16.0', '2.0.0' )) {
	// An incompatible WP Emerge version is already loaded - stop further execution.
	// depicter_should_load_framework() will automatically add an admin notice.
	return;
}

// CONSTANTS -------

const DEPICTER_PLUGIN_ID = 'depicter';
const DEPICTER_PLUGIN_FILE = __FILE__;
const DEPICTER_PLUGIN_PATH = __DIR__;
define( 'DEPICTER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DEPICTER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// -----------------

// Load composer dependencies.
if ( file_exists( __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' ) ) {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
}

depicter_declare_loaded_framework( $name, 'plugin', __FILE__ );

// Load helpers.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'App.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'helpers.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'functions.php';

// Bootstrap plugin after all dependencies and helpers are loaded.
\Depicter::make()->bootstrap( require __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config.php' );

// Register hooks.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'hooks.php';
