<?php

namespace Depicter\Media\Image;

use Depicter\Media\Uri;
use Depicter\Media\Url;

/**
 * Finds or generates resized/cropped size of image file.
 *
 * @package Depicter\Media\Image
 */
class Resizer implements FileEditInterface
{
	/**
	 * @var string
	 */
	public $file = '';

	/**
	 * Resizer constructor.
	 *
	 * @param $file
	 */
	public function __construct( $file ) {
		if ( is_numeric( $file ) ) {
			$this->file = get_attached_file( $file );
		} else {
			$urlHandler = new Url();
			if ( $urlHandler->isUrl( $file ) ) {

				/* WPML Fix */
				if ( function_exists('icl_object_id') ) {
					$current_language = apply_filters( 'wpml_current_language', NULL );
					$file = apply_filters( 'wpml_permalink', $file, $current_language, true );
				}

				/* PolyLang Fix */
				if( function_exists( 'pll_current_language' ) && $current_language = pll_current_language() ){
					$file = str_replace('/' . $current_language, '', $file );
				}

				$uri = $urlHandler->toUri( $file );
				$this->file = $uri ? $uri : '';
			} else {
				if ( file_exists( $file ) ) {
					$this->file = $file;
				}
			}
		}
	}

	/**
	 * Shorthand method for calling resize and crop methods.
	 *
	 * @param int  $resizeW  Resize width
	 * @param int  $resizeH  Resize height
	 * @param int  $cropW    Crop with
	 * @param int  $cropH    Crop height
	 * @param array $args    Resizing options
	 *
	 * @return mixed
	 * @throws ImageEditorException
	 */
	public function process( $resizeW = null, $resizeH = null, $cropW = null, $cropH = null, $args = [] ){
		if ( empty( $this->file ) ) {
			return '';
		}

		$finder = new FileResizedFinder( $this->file );

		// try to find the resized image from disk
		if( $imagePath = $finder->process( $resizeW, $resizeH, $cropW, $cropH, $args ) ){
			return $imagePath;
		}

		// check if resizer should create the image or not
		if ( empty( $args['dry'] ) ) {
			$result = (new FileEdit( $this->file ))->process( $resizeW, $resizeH, $cropW, $cropH, $args )->save();

			if( ! is_wp_error( $result ) && !empty( $result['path'] ) ){
				return Uri::toUrl( $result['path'] );
			}
		}

		return '';
	}

	/**
	 * Resize the current image.
	 *
	 * @param int  $width    Resize width
	 * @param int  $height   Resize height
	 * @param array $args    Cropping options
	 *
	 * @return mixed
	 * @throws ImageEditorException
	 */
	public function resize( $width = null, $height = null, $args = [] ){
		if ( empty( $this->file ) ) {
			return '';
		}

		$finder = new FileResizedFinder( $this->file );

		// try to find the resized image from disk
		if( $imagePath = $finder->resize( $width, $height, $args ) ){
			return $imagePath;
		}

		// check if resizer should create the image or not
		if ( empty( $args['dry'] ) ) {
			$result = ( new FileEdit( $this->file ) )->resize( $width, $height, $args )->save();

			if( !empty( $result['path'] ) ){
				return Uri::toUrl( $result['path'] );
			}
		}

		return '';
	}

	/**
	 * Crops the images
	 *
	 * @param int   $width   Width to crop
	 * @param int   $height  Height to crop
	 * @param array $args    Cropping options
	 *
	 * @return mixed
	 * @throws ImageEditorException
	 */
	public function crop( $width = null, $height = null, $args = [] ){
		if ( empty( $this->file ) ) {
			return '';
		}

		$finder = new FileResizedFinder( $this->file );

		// try to find the resized image from disk
		if( $imagePath = $finder->crop( $width, $height, $args ) ){
			return $imagePath;
		}

		$result = ( new FileEdit( $this->file ) )->crop( $width, $height, $args )->save();

		if( !empty( $result['path'] ) ){
			return Uri::toUrl( $result['path'] );
		}

		return '';
	}
}
