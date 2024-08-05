<?php
namespace Depicter\Document\Models\Common\Styles;

use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\Helper\Helper;

class Transform extends States
{
	/**
	 * style name
	 */
	const NAME = 'transform';

	public function set( $css ) {
		$devices = Breakpoints::names();
		foreach ( $devices as $device ) {
			$transform_styles = '';

			if ( !empty( $this->{$device} ) ) {
				$rotate = $this->{$device}->rotate ?? Helper::getParentValue( $this, 'rotate', $device, '' );
				if ( !empty( $rotate ) ) {
					$transform_styles .= "rotate(" . $rotate . "deg) ";
				}

				$scaleX = $this->{$device}->scaleX ?? Helper::getParentValue( $this, 'scaleX', $device, '' );
				if ( !empty( $scaleX ) ) {
					$transform_styles .= "scaleX(" . $scaleX . ") ";
				}

				$scaleY = $this->{$device}->scaleY ?? Helper::getParentValue( $this, 'scaleY', $device, '' );
				if ( !empty( $scaleY ) ) {
					$transform_styles .= "scaleY(" . $scaleY . ") ";
				}

				$scale = $this->{$device}->scale ?? Helper::getParentValue( $this, 'scale', $device, '' );
				if ( !empty( $scale ) ) {
					$transform_styles .= "scale(" . $scale . ") ";
				}

				if ( $transform_styles = trim( $transform_styles ) ){
					$css[ $device ][ self::NAME ] = $transform_styles;
				}
			}
		}

		return $css;
	}
}
