<?php
namespace Depicter\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

class SVGServiceProvider implements ServiceProviderInterface
{

	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		// Nothing to register.
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		// Enable SVG support 
		add_filter( 'wp_check_filetype_and_ext', [ $this, 'checkFileType' ], 10, 4 );
		add_filter( 'upload_mimes', [ $this, 'addSvgMimeType' ] );
	}

	/**
	 * Allow SVG
	 *
	 * @param $data
	 * @param $file
	 * @param $filename
	 * @param $mimes
	 *
	 * @return array
	 */
	public function checkFileType( $data, $file, $filename, $mimes ) {
		$fileType = wp_check_filetype( $filename, $mimes );

	  	return [
	  		'ext'             => $fileType['ext'],
			'type'            => $fileType['type'],
			'proper_filename' => $data['proper_filename']
		];
	}

	/**
	 * Add SVG mime type
	 *
	 * @param $mimes
	 *
	 * @return mixed
	 */
	public function addSvgMimeType( $mimes ){
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}

}
