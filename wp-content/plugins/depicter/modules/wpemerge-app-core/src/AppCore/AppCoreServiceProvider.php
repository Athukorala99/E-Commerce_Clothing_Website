<?php
/**
 * @package   WPEmergeAppCore
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2020 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmergeAppCore\AppCore;

use WPEmerge\ServiceProviders\ExtendsConfigTrait;
use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Provide theme dependencies.
 *
 * @codeCoverageIgnore
 */
class AppCoreServiceProvider implements ServiceProviderInterface {
	use ExtendsConfigTrait;

	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		$this->extendConfig( $container, 'app_core', [
			'path' => '',
			'url' => '',
		] );

		$container['wpemerge_app_core.app_core.app_core'] = function( $c ) {
			return new AppCore( $c[ WPEMERGE_APPLICATION_KEY ] );
		};

		$app = $container[ WPEMERGE_APPLICATION_KEY ];
		$app->alias( 'core', 'wpemerge_app_core.app_core.app_core' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		// Nothing to bootstrap.
	}
}
