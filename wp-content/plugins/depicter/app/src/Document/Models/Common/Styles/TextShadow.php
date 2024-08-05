<?php
namespace Depicter\Document\Models\Common\Styles;


use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\Helper\Helper;

class TextShadow extends States
{
	/**
	 * style name
	 */
	const NAME = 'text-shadow';

	/**
	 * @var int
	 */
	public $offsetX = 2;

	/**
	 * @var int
	 */
	public $offsetY = 2;

	/**
	 * @var int
	 */
	public $blur = 5;

	/**
	 * @var string
	 */
	public $color = 'rgba( 0, 0, 0, 0.5 )';

	public function set( $css ) {
		$devices = Breakpoints::names();
		foreach ( $devices as $device ) {

			// If it is disabled in a breakpoint other than default, generate a reset style for breakpoint
			if( $device != 'default' && ! Helper::isStyleEnabled( $this, $device ) ) {
				$css[$device][ self::NAME ] = 'none';

			} elseif ( Helper::isStyleEnabled( $this, $device ) ) {
				$this->offsetX = $this->{$device}->offsetX ?? $this->offsetX;
				$this->offsetY = $this->{$device}->offsetY ?? $this->offsetY;
				$this->blur = $this->{$device}->blur ?? $this->blur;
				$this->color = $this->{$device}->color ?? $this->color;

				$css[$device][self::NAME] = $this->offsetX . "px " . $this->offsetY . 'px ' . $this->blur . 'px ' . $this->color;
			}
		}

		return $css;
	}
}
