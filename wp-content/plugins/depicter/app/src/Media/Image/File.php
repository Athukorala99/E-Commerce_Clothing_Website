<?php

namespace Depicter\Media\Image;


use Depicter\Media\File as MediaFile;

class File extends MediaFile
{

	/**
	 * Resized width in pixels.
	 *
	 * @var int
	 */
	protected $resized_w;

	/**
	 * Resized height in pixels.
	 *
	 * @var int
	 */
	protected $resized_h;

	/**
	 * Crop width in pixels.
	 *
	 * @var int
	 */
	protected $crop_w;

	/**
	 * Crop height in pixels.
	 *
	 * @var int
	 */
	protected $crop_h;

	/**
	 * Focal points
	 *
	 * @example [50, 50] or ["center", "center"]
	 *
	 * @var array
	 */
	protected $focal = [0.5, 0.5];

	/**
	 * Set focal point
	 *
	 * @param string|float $x
	 * @param string|float $y
	 *
	 * @return $this
	 */
	public function setFocal( $x, $y ){
		$this->focal = [ $x, $y ];

		return $this;
	}

	/**
	 * Generates a name suffix for focal point if is not default focal
	 *
	 * @return string
	 */
	protected function focalPointSuffix(){
		if( $this->focal[0] == 0.5 && $this->focal[0] == 0.5 ){
			return '';
		}
		return round( $this->focal[0] * 1000 ) . round( $this->focal[1] * 1000 ) . '-';
	}

}
