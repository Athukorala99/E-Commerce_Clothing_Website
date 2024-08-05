<?php
namespace Depicter\Document\Models\Common\Styles;


use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\Helper\Helper;

class BlendingMode extends States
{
	/**
	 * style name
	 */
	const NAME = 'mix-blend-mode';

	public function set( $css ) {
		$devices = Breakpoints::names();
		foreach ( $devices as $device ) {

			// If it is disabled in a breakpoint other than default, generate a reset style for breakpoint
			if( $device !== 'default' && ! Helper::isStyleEnabled( $this, $device ) ) {
				$css[$device][ self::NAME ] = 'normal';

			} elseif( ! empty( $this->{$device}->type ) && Helper::isStyleEnabled( $this, $device ) ) {
				$css[ $device ][ self::NAME ] = $this->{$device}->type;
			}
		}

		return $css;
	}
}
