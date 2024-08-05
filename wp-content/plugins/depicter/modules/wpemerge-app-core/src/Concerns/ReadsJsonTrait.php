<?php
/**
 * @package   WPEmergeAppCore
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2020 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmergeAppCore\Concerns;

use WPEmerge\Support\Arr;

trait ReadsJsonTrait {
	/**
	 * Cache.
	 *
	 * @var array|null
	 */
	protected $cache = null;

	/**
	 * Get the path to the JSON that should be read.
	 *
	 * @return string
	 */
	abstract protected function getJsonPath();

	/**
	 * Load the json file.
	 *
	 * @param string $file
	 *
	 * @return array
	 */
	protected function load( $file ) {
		/** @var \WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';

		WP_Filesystem();

		if ( ! $wp_filesystem->exists( $file ) ) {
			throw new JsonFileNotFoundException( 'The required ' . basename( $file ) . ' file is missing.' );
		}

		$contents = $wp_filesystem->get_contents( $file );
		$json = json_decode( $contents, true );
		$json_error = json_last_error();

		if ( $json_error !== JSON_ERROR_NONE ) {
			throw new JsonFileInvalidException( 'The required ' . basename( $file ) . ' file is not valid JSON (error code ' . $json_error . ').' );
		}

		return $json;
	}

	/**
	 * Get the entire json array.
	 *
	 * @return array
	 */
	protected function getAll() {
		if ($this->cache === null) {
			$this->cache = $this->load( $this->getJsonPath() );
		}

		return $this->cache;
	}

	/**
	 * Get a json value.
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function get( $key, $default = null ) {
		return Arr::get( $this->getAll(), $key, $default );
	}
}
