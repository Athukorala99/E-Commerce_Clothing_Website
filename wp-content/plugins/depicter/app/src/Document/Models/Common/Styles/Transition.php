<?php
namespace Depicter\Document\Models\Common\Styles;

use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\Helper\Helper;

class Transition extends States
{
	/**
	 * @var string
	 */
	public $timingFunction;

	/**
	 * @var int
	 */
	public $duration;

	/**
	 * style name
	 */
	const NAME = 'transition';

	public function set( $css ) {
		$devices = Breakpoints::names();
		foreach ( $devices as $device ) {
			// if ( !empty( $this->{$device} ) || !empty( $this->default ) ) {
				if ( Helper::isStyleEnabled( $this, $device ) ) {
					$timingFunction = $this->{$device}->timingFunction ?? Helper::getParentValue( $this, 'timingFunction', $device, 'ease');
					$duration = $this->{$device}->duration ?? Helper::getParentValue( $this, 'duration', $device, 1 );
					$css[ $device ][ self::NAME ] = "all " . $timingFunction . ' ' . $duration . 's';
				}
			// }
		}

		return $css;
	}
}
