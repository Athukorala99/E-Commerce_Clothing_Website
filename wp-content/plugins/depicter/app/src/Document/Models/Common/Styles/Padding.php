<?php
namespace Depicter\Document\Models\Common\Styles;


use Depicter\Document\CSS\Breakpoints;

class Padding extends States
{
	/**
	 * style name
	 */
	const NAME = 'padding';

	public function set( $css ) {
		$devices = Breakpoints::names();
		foreach ( $devices as $device ) {
			if ( ! empty( $this->{$device} ) ) {
				if ( !empty( $this->{$device}->link ) ) {
					$css[ $device ][ self::NAME ] = $this->{$device}->top->value . $this->{$device}->top->unit;
				} elseif( isset( $this->{$device}->top ) ) {
					$css[ $device ][ self::NAME ] = $this->{$device}->top->value . $this->{$device}->top->unit . ' ' . $this->{$device}->right->value . $this->{$device}->right->unit . ' ' . $this->{$device}->bottom->value . $this->{$device}->bottom->unit . ' ' . $this->{$device}->left->value . $this->{$device}->left->unit;
				}
			}
		}

		return $css;
	}
}
