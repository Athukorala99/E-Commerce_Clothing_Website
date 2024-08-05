<?php
namespace Depicter\Media\Image;


use Depicter\Media\Uri;
use Depicter\Media\Url;

class ImageEditor
{
	/**
	 * Shorthand method for calling resize and crop methods for an image.
	 *
	 * @param string $file  Path or url of file or attachment ID
	 * @param int    $resizeW  Resize width
	 * @param int    $resizeH  Resize height
	 * @param int    $cropW    Crop with
	 * @param int    $cropH    Crop height
	 * @param array  $args     Resizing options
	 *
	 * @return mixed
	 */
	public static function process( $file, $resizeW = null, $resizeH = null, $cropW = null, $cropH = null, $args = [] ){
		try {
			$resizer = new Resizer( $file );
			return $resizer->process( $resizeW, $resizeH, $cropW, $cropH, $args );
		} catch( ImageEditorException $exception ) {
			return self::originalFile( $file );
		}
	}

	/**
	 * Resize an image.
	 *
	 * @param string $file   Path or url of file or attachment ID
	 * @param int  $width    Resize width
	 * @param int  $height   Resize height
	 * @param array  $args   Resizing options
	 *
	 * @return string
	 */
	public static function resize( $file, $width = null, $height = null, $args = [] ){
		try {
			$resizer = new Resizer( $file );
			return $resizer->resize( $width, $height, $args );
		} catch( ImageEditorException $exception ) {
			return self::originalFile( $file );
		}
	}

	/**
	 * Crops an image
	 *
	 * @param string $file  Path or url of file or attachment ID
	 * @param int   $width  Width to crop
	 * @param int   $height Height to crop
	 * @param array $args Cropping options
	 *
	 * @return mixed
	 */
	public static function crop( $file, $width = null, $height = null, $args = [] ){
		try {
			$resizer = new Resizer( $file );
			return $resizer->crop( $width, $height, $args );
		} catch( ImageEditorException $exception ) {
			return self::originalFile( $file );
		}
	}

	/**
	 * return original file
	 * @param $file
	 *
	 * @return mixed|string
	 */
	public static function originalFile( $file ) {
		if ( is_numeric( $file ) ) {
			return wp_get_attachment_image_src( $file, 'full')[0];
		} else {
			$urlHandler = new Url();
			if ( $urlHandler->isUrl( $file ) ) {
				return $file;
			} else {
				if ( file_exists( $file ) ) {
					return Uri::toUrl($file);
				}
			}
		}
		return '';
	}
}
