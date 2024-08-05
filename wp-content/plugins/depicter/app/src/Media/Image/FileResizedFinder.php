<?php
namespace Depicter\Media\Image;

use Depicter\Media\Uri;

/**
 * Search for cropped size of current image file
 *
 * @package Depicter\Media
 */
class FileResizedFinder extends File implements FileEditInterface
{
	/**
	 * Last founded file size path
	 *
	 * @var string
	 */
	protected $lastFoundPath;

	/**
	 * Constructor.
	 *
	 * @param string $path  Path to the file or attachment id to load.
	 */
	public function __construct( $path ){
		parent::__construct( $path );
		// Get original dimensions
		$this->reset();
	}

	/**
	 * check to find image
	 *
	 * @param int  $resizeW   Resize width
	 * @param int  $resizeH   Resize height
	 * @param int  $cropW     Crop with
	 * @param int  $cropH     Crop height
	 * @param array $args     Resizing options
	 *
	 * @return false|string
	 */
	public function process( $resizeW = null, $resizeH = null, $cropW = null, $cropH = null, $args = [] ){

		$this->orig_w = $this->getSize('width');
		$this->orig_h = $this->getSize('height');

		if ( empty( $this->orig_h ) || empty( $this->orig_w ) ) {
			return false;
		}

		$aspect_ratio = $this->orig_w / $this->orig_h;

		$this->focal[0] = !empty( $args['focalX'] ) ? $args['focalX'] : $this->focal[0];
		$this->focal[1] = !empty( $args['focalY'] ) ? $args['focalY'] : $this->focal[1];

		// auto calculate the width or height if it was set to 'auto'
		if( 'auto' === $resizeH && is_numeric( $resizeW ) ){
			$resizeH = $resizeW / $aspect_ratio;
		}
		if( 'auto' === $resizeW && is_numeric( $resizeH ) ){
			$resizeW = $resizeH * $aspect_ratio;
		}

		if ( !empty( $cropW ) || !empty( $cropH )  ) {

			if( !empty( $args['upscale'] ) ){
				$new_w = $resizeW;
				$new_h = $resizeH;
			} else {
				[ $new_w, $new_h ] = wp_constrain_dimensions( $this->orig_w, $this->orig_h, $resizeW, $resizeH );
			}

			if ( $cropW && $cropW < $new_w ) {
				$new_w = $cropW;
			}

			if ( $cropH && $cropH < $new_h ) {
				$new_h = $cropH;
			}

			// don't crop, just resize using $dest_w x $dest_h as a maximum bounding box
		} else {
			if( !empty( $args['upscale'] ) ){
				$new_w = $resizeW;
				$new_h = $resizeH;
			} else {
				[ $new_w, $new_h ] = wp_constrain_dimensions( $this->orig_w, $this->orig_h, $resizeW, $resizeH );
			}

		}

		$newFilePath = $this->getFileInfo('dirname') . '/' . $this->getFileInfo('filename') . '-' . $this->getSuffix( $new_w, $new_h ) . '.' . $this->getExtension();

		if ( file_exists( $newFilePath ) ) {
			$this->lastFoundPath = $newFilePath;
			// convert uri to url
			return Uri::toUrl( $newFilePath );
		}

		return false;
	}

	/**
	 * Resize the current image.
	 *
	 * @param int  $width    Resize width
	 * @param int  $height   Resize height
	 * @param array $args    Cropping options
	 *
	 * @return false|string
	 */
	public function resize( $width = null, $height = null, $args = [] ){
		return $this->process( $width, $height, null, null, $args );
	}


	/**
	 * Crops the images
	 *
	 * @param int $width  Width to crop
	 * @param int $height Height to crop
	 * @param array $args Cropping options
	 *
	 * @return false|string
	 */
	public function crop( $width = null, $height = null, $args = [] ){
		return $this->process( null, null, $width, $height, $args );
	}

	/**
	 * Get image suffix
	 *
	 * @param $width
	 * @param $height
	 *
	 * @return string
	 */
	protected function getSuffix( $width, $height ){
		return $this->focalPointSuffix() . $width . 'x' . $height;
	}

	/**
	 * Last founded file size path
	 *
	 * @return mixed
	 */
	public function last() {
		return $this->lastFoundPath;
	}
}
