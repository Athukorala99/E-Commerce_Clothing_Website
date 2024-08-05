<?php
/**
 * @package   WPEmergeAppCore
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2020 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmergeAppCore\AppCore;

use WPEmerge\Application\Application;

/**
 * Main communication channel with the theme.
 */
class AppCore {
	/**
	 * Application instance.
	 *
	 * @var Application
	 */
	protected $app = null;

	/**
	 * Constructor.
	 *
	 * @param Application $app
	 */
	public function __construct( $app ) {
		$this->app = $app;
	}

	/**
	 * Shortcut to \WPEmergeAppCore\Assets\Assets.
	 *
	 * @return \WPEmergeAppCore\Assets\Assets
	 */
	public function assets() {
		return $this->app->resolve( 'wpemerge_app_core.assets.assets' );
	}

	/**
	 * Shortcut to \WPEmergeAppCore\Avatar\Avatar.
	 *
	 * @return \WPEmergeAppCore\Avatar\Avatar
	 */
	public function avatar() {
		return $this->app->resolve( 'wpemerge_app_core.avatar.avatar' );
	}

	/**
	 * Shortcut to \WPEmergeAppCore\Config\Config.
	 *
	 * @return \WPEmergeAppCore\Config\Config
	 */
	public function config() {
		return $this->app->resolve( 'wpemerge_app_core.config.config' );
	}

	/**
	 * Shortcut to \WPEmergeAppCore\Image\Image.
	 *
	 * @return \WPEmergeAppCore\Image\Image
	 */
	public function image() {
		return $this->app->resolve( 'wpemerge_app_core.image.image' );
	}

	/**
	 * Shortcut to \WPEmergeAppCore\Sidebar\Sidebar.
	 *
	 * @return \WPEmergeAppCore\Sidebar\Sidebar
	 */
	public function sidebar() {
		return $this->app->resolve( 'wpemerge_app_core.sidebar.sidebar' );
	}
}
