<?php

namespace Depicter\Media\Image;


interface FileEditInterface
{
	/**
	 * Shorthand method for calling resize and crop methods.
	 *
	 * @param int  $resizeW   Resize width
	 * @param int  $resizeH   Resize height
	 * @param int  $cropW     Crop with
	 * @param int  $cropH     Crop height
	 * @param array $args    Resizing options
	 *
	 * @return mixed
	 */
	public function process( $resizeW = null, $resizeH = null, $cropW = null, $cropH = null, $args = [] );


	/**
	 * Resize the current image.
	 *
	 * @param int  $width    Resize width
	 * @param int  $height   Resize height
	 * @param array $args    Resizing options
	 *
	 * @return mixed
	 */
	public function resize( $width = null, $height = null, $args = [] );


	/**
	 * Crops the images
	 *
	 * @param int $width  Width to crop
	 * @param int $height Height to crop
	 * @param array $args Cropping options
	 *
	 * @return mixed
	 */
	public function crop( $width = null, $height = null, $args = [] );
}
