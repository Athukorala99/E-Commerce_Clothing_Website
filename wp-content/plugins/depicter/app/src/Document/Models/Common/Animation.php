<?php
namespace Depicter\Document\Models\Common;


use Averta\WordPress\Utility\JSON;
use Depicter\Document\CSS\Breakpoints;

class Animation
{
	/**
	 * Animation in phase
	 *
	 * @var object
	 */
	public $in;

	/**
	 * Animation out phase
	 *
	 * @var object
	 */
	public $out;

	/**
	 * @var bool|null
	 */
	public $waitForAction;

	/**
	 * Known phases
	 *
	 * @var array
	 */
	protected $phases = ['in', 'out'];


	/**
	 * @param string $phase
	 * @param string $breakpoint
	 *
	 * @return false|string
	 */
	public function getAnimation( $phase, $breakpoint ) {

		if( empty( $this->{$phase}->data->{$breakpoint}->type ) || ! isset( $this->{$phase}->data->{$breakpoint}->params ) ){
			return false;
		}

		$params = $this->{$phase}->data->{$breakpoint}->params;
		$params->type = $this->{$phase}->data->{$breakpoint}->type;

		return JSON::encode( $params );
	}

	private function getWaitForAnimationAttr( $phase, $breakpoint ) {

	}

	/**
	 * Get all animation attributes
	 *
	 * @return array
	 */
	public function getAnimationAttrs() {
		$attrs = [];

		// Collect animation attributes
		foreach ( Breakpoints::names() as $breakpoint  ){
			foreach ( $this->phases as $phase  ){
				$breakpoint_prefix = $breakpoint ? $breakpoint . '-' : $breakpoint;
				$breakpoint_prefix = $breakpoint == 'default' ? '' : $breakpoint_prefix;
				if( $animation_value = $this->getAnimation( $phase, $breakpoint ) ){
					$attrs[ 'data-'.  $breakpoint_prefix .'animation-' . $phase ] = $this->getAnimation( $phase, $breakpoint );
				}
			}
		}

		// Get animation interactive attributes
		if( ! is_null( $this->waitForAction ) ){
			$attrs[ "data-wait-for-action" ] = $this->waitForAction ? "true" : "false";
		}

		if( isset( $this->out->wait ) ){
			$attrs[ "data-animation-out-wait" ] = $this->out->wait ? "true" : "false";
		}

		// Get animation interactive attributes
		foreach ( $this->phases as $phase  ){
			if( isset( $this->{$phase}->interactive ) ){
				$attrs[ "data-animation-{$phase}-interactive" ] = $this->{$phase}->interactive ? "true" : "false";
			}
		}

		return $attrs;
	}
}
