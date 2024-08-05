<?php
namespace Depicter\Document\Models\Common;

use Averta\Core\Utility\Data;
use Averta\WordPress\Utility\JSON;
use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\Helper\Helper;

class KenBurns extends States {

	/**
	 * Get all kenburn attributes
	 *
	 * @return array
	 */
	public function getKenBurnsAttrs(): array{
		$attrs = [];
		// Collect animation attributes
		foreach ( Breakpoints::names() as $breakpoint  ){
			$breakpoint_prefix = $breakpoint ? $breakpoint . '-' : $breakpoint;
			$breakpoint_prefix = $breakpoint == 'default' ? '' : $breakpoint_prefix;

			if( Helper::isStyleEnabled( $this, $breakpoint ) ){
				$attrs[ 'data-'.  $breakpoint_prefix .'ken-burns' ] = $this->getKenBurnsOption( $breakpoint );
			} else if ( isset( $this->default->enable ) ) {
				$attrs[ 'data-'.  $breakpoint_prefix .'ken-burns' ] = "false";
			}

		}
		return $attrs;
	}

	/**
	 * Get kenburns option
	 *
	 * @param string $breakpoint
	 * @return string
	 */
	public function getKenBurnsOption( $breakpoint ): string{
		$options = [];

		$options['scale'] = $this->{$breakpoint}->params->scale ?? $this->getParentValue( 'scale', $breakpoint, '');
		$options['duration'] = $this->{$breakpoint}->params->duration ?? $this->getParentValue( 'duration', $breakpoint, '5000');
		$options['focalPoint']['x'] = $this->{$breakpoint}->params->focalPoint->x ?? $this->getParentValue( 'focalX', $breakpoint, '');
		$options['focalPoint']['y'] = $this->{$breakpoint}->params->focalPoint->y ?? $this->getParentValue( 'focalY', $breakpoint, '');
		$options['easing'] = $this->{$breakpoint}->params->easing ?? $this->getParentValue( 'easing', $breakpoint, 'linear');

		return JSON::encode( $options );
	}

	/**
	 * Get parent device value for a css variable
	 *
	 * @param $variable
	 * @param $device
	 * @param $default
	 *
	 * @return mixed
	 */
	public function getParentValue( $variable, $device, $default ) {
		$parentDevice = Breakpoints::getParentDevice( $device );
		if ( !empty( $parentDevice ) ) {
			if ( strpos( $variable, 'focal' ) === false ) {
				return $this->{$parentDevice}->params->{$variable} ?? $this->getParentValue( $variable, $parentDevice, $default );
			} else {
				$focalAxis = str_replace( 'focal', '', strtolower( $variable ) );
				return $this->{$parentDevice}->params->focalPoint->{$focalAxis} ?? $this->getParentValue( $variable, $parentDevice, $default );
			}
			
		}

		return $default;
	}
}
