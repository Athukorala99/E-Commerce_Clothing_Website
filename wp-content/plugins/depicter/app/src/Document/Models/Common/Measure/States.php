<?php
namespace Depicter\Document\Models\Common\Measure;


use Depicter\Document\CSS\Breakpoints;

class States
{
	/**
	 * @var Dimension
	 */
	public $default;

	/**
	 * @var Dimension
	 */
	public $tablet;

	/**
	 * @var Dimension
	 */
	public $mobile;


	public function getStylesList( $properties = [] ){
		$responsiveSizes = [];

		foreach ( $properties as $property ) {
			foreach ( Breakpoints::names() as $breakpoint ){
				if ( !empty($this->{$breakpoint}->{$property}->value) ) {
					$responsiveSizes[$breakpoint][$property] = (string) $this->{$breakpoint}->{$property};
				}
			}
		}

		return $responsiveSizes;
	}
}
