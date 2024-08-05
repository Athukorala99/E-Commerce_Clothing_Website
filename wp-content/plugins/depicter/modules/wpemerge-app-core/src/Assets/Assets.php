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
use WPEmerge\Helpers\Url;
use WPEmergeAppCore\Config\Config;

class Assets {
	/**
	 * App root path.
	 *
	 * @var string
	 */
	protected $path = '';

	/**
	 * App root URL.
	 *
	 * @var string
	 */
	protected $url = '';

	/**
	 * Config.
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 * Manifest.
	 *
	 * @var Manifest
	 */
	protected $manifest = null;

	/**
	 * Filesystem.
	 *
	 * @var \WP_Filesystem_Base
	 */
	protected $filesystem = null;

	/**
	 * Constructor.
	 *
	 * @param string              $path
	 * @param string              $url
	 * @param Config              $config
	 * @param Manifest            $manifest
	 * @param \WP_Filesystem_Base $filesystem
	 */
	public function __construct( $path, $url, Config $config, Manifest $manifest, \WP_Filesystem_Base $filesystem ) {
		$this->path = MixedType::removeTrailingSlash( $path );
		$this->url = Url::removeTrailingSlash( $url );
		$this->config = $config;
		$this->manifest = $manifest;
		$this->filesystem = $filesystem;
	}

	/**
	 * Remove the protocol from an http/https url.
	 *
	 * @param  string $url
	 * @return string
	 */
	protected function removeProtocol( $url ) {
		return preg_replace( '~^https?:~i', '', $url );
	}

	/**
	 * Get if a url is external or not.
	 *
	 * @param  string  $url
	 * @param  string  $home_url
	 * @return boolean
	 */
	protected function isExternalUrl( $url, $home_url ) {
		$delimiter = '~';
		$pattern_home_url = preg_quote( $home_url, $delimiter );
		$pattern = $delimiter . '^' . $pattern_home_url . $delimiter . 'i';
		return ! preg_match( $pattern, $url );
	}

	/**
	 * Generate a version for a given asset src.
	 *
	 * @param  string          $src
	 * @return integer|boolean
	 */
	protected function generateFileVersion( $src ) {
		// Normalize both URLs in order to avoid problems with http, https
		// and protocol-less cases.
		$src = $this->removeProtocol( $src );
		$home_url = $this->removeProtocol( WP_CONTENT_URL );
		$version = false;

		if ( ! $this->isExternalUrl( $src, $home_url ) ) {
			// Generate the absolute path to the file.
			$file_path = MixedType::normalizePath( str_replace(
				[$home_url, '/'],
				[WP_CONTENT_DIR, DIRECTORY_SEPARATOR],
				$src
			) );

			if ( $this->filesystem->exists( $file_path ) ) {
				// Use the last modified time of the file as a version.
				$version = $this->filesystem->mtime( $file_path );
			}
		}

		return $version;
	}

	/**
	 * Get the public URL to the app root.
	 *
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * Get the public URL to a generated asset based on manifest.json.
	 *
	 * @param string $asset
	 *
	 * @return string
	 */
	public function getAssetUrl( $asset ) {
		// Path with unix-style slashes.
		$path = $this->manifest->get( $asset, '' );

		if ( ! $path ) {
			return '';
		}

		$url = wp_parse_url( $path );

		if ( isset( $url['scheme'] ) ) {
			// Path is an absolute URL.
			return $path;
		}

		// Path is relative.
		return $this->getUrl() . '/dist/' . $path;
	}

	/**
	 * Get the public URL to a generated JS or CSS bundle.
	 * Handles SCRIPT_DEBUG and hot reloading.
	 *
	 * @param string  $name Source basename (no extension).
	 * @param string  $extension Source extension - '.js' or '.css'.
	 * @return string
	 */
	public function getBundleUrl( $name, $extension ) {
		$url_path = '.css' === $extension ? "styles/{$name}" : $name;
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$file_path = implode(
			DIRECTORY_SEPARATOR,
			array_filter(
				[
					$this->path,
					'dist',
					'.css' === $extension ? 'styles' : '',
					$name . $suffix . $extension,
				]
			)
		);

		if ( $this->filesystem->exists( $file_path ) ) {
			return "{$this->getUrl()}/dist/{$url_path}{$suffix}{$extension}";
		}

		$hot_url = wp_parse_url( $this->config->get( 'development.hotUrl', 'http://localhost/' ) );
		$hot_port = $this->config->get( 'development.port', 3000 );

		return "${hot_url['scheme']}://{$hot_url['host']}:{$hot_port}/{$url_path}{$extension}";
	}

	/**
	 * Enqueue a style, dynamically generating a version for it.
	 *
	 * @param  string        $handle
	 * @param  string        $src
	 * @param  array<string> $dependencies
	 * @param  string        $media
	 * @return void
	 */
	public function enqueueStyle( $handle, $src, $dependencies = [], $media = 'all' ) {
		wp_enqueue_style( $handle, $src, $dependencies, $this->generateFileVersion( $src ), $media );
	}

	/**
	 * Enqueue a script, dynamically generating a version for it.
	 *
	 * @param  string        $handle
	 * @param  string        $src
	 * @param  array<string> $dependencies
	 * @param  boolean       $in_footer
	 * @return void
	 */
	public function enqueueScript( $handle, $src, $dependencies = [], $in_footer = false ) {
		wp_enqueue_script( $handle, $src, $dependencies, $this->generateFileVersion( $src ), $in_footer );
	}

	/**
	 * Add favicon meta.
	 *
	 * @return void
	 */
	public function addFavicon() {
		if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
			// allow users to override the favicon using the WordPress Customizer
			return;
		}

		$favicon_url = apply_filters( 'wpemerge_app_core_favicon_url', $this->getAssetUrl( 'images/favicon.ico' ) );

		echo '<link rel="shortcut icon" href="' . $favicon_url . '" />' . "\n";
	}
}
