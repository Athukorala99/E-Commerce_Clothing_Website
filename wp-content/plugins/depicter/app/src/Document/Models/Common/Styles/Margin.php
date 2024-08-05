<?php
namespace Depicter\Document\Models\Common\Styles;


use Depicter\Document\CSS\Breakpoints;

class Margin extends States
{
	/**
	 * style name
	 */
	const NAME = 'margin';

	public function set( $css ) {
		$devices = Breakpoints::names();
		foreach ( $devices as $device ) {
			if ( ! empty( $this->{$device}->value ) ) {
				if ( $this->{$device}->link ) {
					$css[ $device ][ self::NAME ] = $this->{$device}->value->top->value . $this->{$device}->value->top->unit;
				} else {
					$css[ $device ][ self::NAME ] = $this->{$device}->value->top->value . $this->{$device}->value->top->unit . ' ' . $this->{$device}->value->right->value . $this->{$device}->value->right->unit . ' ' . $this->{$device}->value->bottom->value . $this->{$device}->value->bottom->unit . ' ' . $this->{$device}->value->left->value . $this->{$device}->value->left->unit;
				}

			}
		}

		return $css;
	}
}
