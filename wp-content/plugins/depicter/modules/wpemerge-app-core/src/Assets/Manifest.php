<?php
/**
 * @package   WPEmergeAppCore
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2020 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmergeAppCore\Assets;

use WPEmerge\Helpers\MixedType;
use WPEmergeAppCore\Concerns\JsonFileNotFoundException;
use WPEmergeAppCore\Concerns\ReadsJsonTrait;

class Manifest {
	use ReadsJsonTrait {
		load as traitLoad;
	}

	/**
	 * App root path.
	 *
	 * @var string
	 */
	protected $path = '';

	/**
	 * Constructor.
	 *
	 * @param string $path
	 */
	public function __construct( $path ) {
		$this->path = $path;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getJsonPath() {
		return MixedType::normalizePath( $this->path . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'manifest.json' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function load( $file ) {
		try {
			return $this->traitLoad( $file );
		} catch ( JsonFileNotFoundException $e ) {
			// We used to throw an exception here but it just causes confusion for new users.
		}

		return [];
	}
}
