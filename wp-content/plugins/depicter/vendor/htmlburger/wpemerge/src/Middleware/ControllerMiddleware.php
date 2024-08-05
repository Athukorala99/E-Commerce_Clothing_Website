<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Middleware;

use WPEmerge;

/**
 * Redirect users who do not have a capability to a specific URL.
 */
class ControllerMiddleware {
	/**
	 * Middleware.
	 *
	 * @var string[]
	 */
	protected $middleware = [];

	/**
	 * Methods the middleware applies to.
	 *
	 * @var string[]
	 */
	protected $whitelist = [];

	/**
	 * Methods the middleware does not apply to.
	 *
	 * @var string[]
	 */
	protected $blacklist = [];

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 * @param  string|string[] $middleware
	 */
	public function __construct( $middleware ) {
		$this->middleware = (array) $middleware;
	}

	/**
	 * Get middleware.
	 *
	 * @codeCoverageIgnore
	 * @return string[]
	 */
	public function get() {
		return $this->middleware;
	}

	/**
	 * Set methods the middleware should apply to.
	 *
	 * @codeCoverageIgnore
	 * @param  string|string[] $methods
	 * @return static
	 */
	public function only( $methods ) {
		$this->whitelist = (array) $methods;

		return $this;
	}

	/**
	 * Set methods the middleware should not apply to.
	 *
	 * @codeCoverageIgnore
	 * @param  string|string[] $methods
	 * @return static
	 */
	public function except( $methods ) {
		$this->blacklist = (array) $methods;

		return $this;
	}

	/**
	 * Get whether the middleware applies to the specified method.
	 *
	 * @param  string $method
	 * @return boolean
	 */
	public function appliesTo( $method ) {
		if ( in_array( $method, $this->blacklist, true ) ) {
			return false;
		}

		if ( empty( $this->whitelist ) ) {
			return true;
		}

		return in_array( $method, $this->whitelist, true );
	}
}
