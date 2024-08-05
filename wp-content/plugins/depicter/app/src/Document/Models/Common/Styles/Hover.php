<?php
namespace Depicter\Document\Models\Common\Styles;

use Depicter\Document\CSS\Breakpoints;

class Hover
{
	/**
	 * @var object
	 */
	public $enable;

	/**
	 * @var Transform
	 */
	public $transform;

	/**
	 * @var Opacity
	 */
	public $opacity;

	/**
	 * @var BoxShadow
	 */
	public $boxShadow;

	/**
	 * @var BackgroundBlur
	 */
	public $backgroundBlur;

	/**
	 * @var BackgroundColor
	 */
	public $backgroundColor;

	/**
	 * @var Border
	 */
	public $border;

	/**
	 * @var Corner
	 */
	public $corner;

	/**
	 * @var Filter
	 */
	public $filter;

	/**
	 * @var TextShadow
	 */
	public $textShadow;

	/**
	 * @var Margin
	 */
	public $margin;

	/**
	 * @var Padding
	 */
	public $padding;

	/**
	 * @var BlendingMode
	 */
	public $blendingMode;

	/**
	 * @var Typography
	 */
	public $typography;

	/**
	 * @var Transition
	 */
	public $transition;

	/**
	 * @var Svg
	 */
	public $svg;


	public function getDisabledDeviceList()
	{
		$disabledDevices = [];

		foreach ( Breakpoints::names() as $device ){
			if( isset( $this->enable->{$device} ) && ! $this->enable->{$device} ) {
				$disabledDevices[] = $device;
			}
		}

		return $disabledDevices;
	}

}
