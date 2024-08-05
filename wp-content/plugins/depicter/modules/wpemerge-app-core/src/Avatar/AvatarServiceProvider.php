<?php
/**
 * @package   WPEmergeAppCore
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2020 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmergeAppCore\Avatar;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Provide avatar dependencies.
 *
 * @codeCoverageIgnore
 */
class AvatarServiceProvider implements ServiceProviderInterface {
	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		$container['wpemerge_app_core.avatar.avatar'] = function() {
			return new Avatar();
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		$container['wpemerge_app_core.avatar.avatar']->bootstrap();
	}
}
