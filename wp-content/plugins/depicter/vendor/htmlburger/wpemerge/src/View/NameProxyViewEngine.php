<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\View;

use WPEmerge\Application\Application;

/**
 * Render view files with different engines depending on their filename
 */
class NameProxyViewEngine implements ViewEngineInterface {
	/**
	 * Container key of default engine to use
	 *
	 * @var string
	 */
	protected $default = WPEMERGE_VIEW_PHP_VIEW_ENGINE_KEY;

	/**
	 * Application.
	 *
	 * @var Application
	 */
	protected $app = null;

	/**
	 * Array of filename_suffix=>engine_container_key bindings
	 *
	 * @var array
	 */
	protected $bindings = [];

	/**
	 * Constructor
	 *
	 * @param Application $app
	 * @param array       $bindings
	 * @param string      $default
	 */
	public function __construct( Application $app, $bindings, $default = '' ) {
		$this->app = $app;
		$this->bindings = $bindings;

		if ( ! empty( $default ) ) {
			$this->default = $default;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function exists( $view ) {
		$engine_key = $this->getBindingForFile( $view );
		$engine = $this->app->resolve( $engine_key );
		return $engine->exists( $view );
	}

	/**
	 * {@inheritDoc}
	 */
	public function canonical( $view ) {
		$engine_key = $this->getBindingForFile( $view );
		$engine = $this->app->resolve( $engine_key );
		return $engine->canonical( $view );
	}

	/**
	 * {@inheritDoc}
	 * @throws ViewNotFoundException
	 */
	public function make( $views ) {
		foreach ( $views as $view ) {
			if ( $this->exists( $view ) ) {
				$engine_key = $this->getBindingForFile( $view );
				$engine = $this->app->resolve( $engine_key );
				return $engine->make( [$view] );
			}
		}

		throw new ViewNotFoundException( 'View not found for "' . implode( ', ', $views ) . '"' );
	}

	/**
	 * Get the default binding
	 *
	 * @return string $binding
	 */
	public function getDefaultBinding() {
		return $this->default;
	}

	/**
	 * Get all bindings
	 *
	 * @return array  $bindings
	 */
	public function getBindings() {
		return $this->bindings;
	}

	/**
	 * Get the engine key binding for a specific file
	 *
	 * @param  string $file
	 * @return string
	 */
	public function getBindingForFile( $file ) {
		$engine_key = $this->default;

		foreach ( $this->bindings as $suffix => $engine ) {
			if ( substr( $file, -strlen( $suffix ) ) === $suffix ) {
				$engine_key = $engine;
				break;
			}
		}

		return $engine_key;
	}
}
