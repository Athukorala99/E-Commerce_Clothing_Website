<?php
namespace Depicter\Document\Models\Common\Styles;


use Depicter\Document\CSS\Breakpoints;

class Corner extends States
{
	/**
	 * style name
	 */
	const NAME = 'border-radius';

	public function set( $css ) {
		$devices = Breakpoints::names();
		foreach ( $devices as $device ) {
			if ( !empty( $this->{$device} ) ) {
				if ( isset( $this->{$device}->link ) && $this->{$device}->link ) {
					$css[ $device ][ self::NAME ] = $this->{$device}->topRight->value . $this->{$device}->topRight->unit;
				} elseif( isset( $this->{$device}->topLeft ) ) {
					$css[ $device ][ self::NAME ] = $this->{$device}->topLeft->value . $this->{$device}->topLeft->unit ." ". $this->{$device}->topRight->value . $this->{$device}->topRight->unit ." " . $this->{$device}->bottomRight->value . $this->{$device}->bottomRight->unit ." " .$this->{$device}->bottomLeft->value . $this->{$device}->bottomLeft->unit;
				}
			}
		}

		return $css;
	}
}
