<?php
/**
 * @package   WPEmergeAppCore
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2020 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

/**
 * Absolute path to app core's directory
 */
if ( ! defined( 'WPEMERGE_APP_CORE_DIR' ) ) {
	define( 'WPEMERGE_APP_CORE_DIR', __DIR__ );
}

/**
 * Absolute path to app core's src directory
 */
if ( ! defined( 'WPEMERGE_APP_CORE_SRC_DIR' ) ) {
	define( 'WPEMERGE_APP_CORE_SRC_DIR', WPEMERGE_APP_CORE_DIR . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR );
}
