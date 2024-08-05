<?php
namespace Depicter\Document\Models\Common\Styles;


use Depicter\Document\CSS\Breakpoints;

class Svg extends States
{

	public function set( $css ) {
		$devices = Breakpoints::names();
		foreach ( $devices as $device ) {
			if ( !empty( $this->{$device}->fill ) ) {
				$css[ $device ][ 'fill' ] = $this->{$device}->fill;
			}

			if ( !empty( $this->{$device}->stroke ) ) {
				$css[ $device ][ 'stroke' ] = $this->{$device}->stroke;
			}

			if ( !empty( $this->{$device}->strokeWidth ) ) {
				$css[ $device ][ 'stroke-width' ] = $this->{$device}->strokeWidth->value . $this->{$device}->strokeWidth->unit;
			}
		}

		return $css;
	}
}
