<?php
namespace Depicter\Document\Models\Common\Styles;

use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\Helper\Helper;

class BackgroundBlur extends States
{
	/**
	 * style name
	 */
	const NAME = 'backdrop-filter';

	/**
	 * @var int
	 */
	public $blur = 0;

	/**
	 * @var int
	 */
	public $opacity = 100;

	/**
	 * @var int
	 */
	public  $brightness = 100;

	public function set( $css ) {
		$devices = Breakpoints::names();
		foreach ( $devices as $device ) {

			// If it is disabled in a breakpoint other than default, generate a reset style for breakpoint
			if( $device !== 'default' && ! Helper::isStyleEnabled( $this, $device ) ) {
				$css[$device][ self::NAME ] = 'none';

			} elseif( Helper::isStyleEnabled( $this, $device ) ) {
				$this->blur = $this->{$device}->blur ?? $this->blur;
				$this->brightness = $this->{$device}->brightness ?? $this->brightness;
				$this->opacity = $this->{$device}->opacity ?? $this->opacity;

				$css[$device][self::NAME] = "blur(" . $this->blur . "px) brightness(" . $this->brightness . "%) opacity(" . $this->opacity . "%)";
			}
		}

		return $css;
	}
}
