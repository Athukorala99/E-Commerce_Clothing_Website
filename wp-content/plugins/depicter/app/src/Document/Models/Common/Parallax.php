<?php
namespace Depicter\Document\Models\Common;

use Averta\Core\Utility\Data;
use Averta\WordPress\Utility\JSON;
use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\Helper\Helper;

class Parallax extends States{

	/**
	 * Get all parallax attributes
	 *
	 * @return array
	 */
	public function getParallaxAttrs() {
		$attrs = [];

		// Collect animation attributes
		foreach ( Breakpoints::names() as $breakpoint  ){
            $breakpoint_prefix = $breakpoint ? $breakpoint . '-' : $breakpoint;
            $breakpoint_prefix = $breakpoint == 'default' ? '' : $breakpoint_prefix;

			if( Helper::isStyleEnabled( $this, $breakpoint, 'enabled' ) ) {
                $attrs[ 'data-'.  $breakpoint_prefix .'parallax' ] = !empty($this->{$breakpoint}->enabled) ? $this->getParallaxOption( $this->{$breakpoint} ) : 'false';
			}

		}

		return $attrs;
	}

    /**
     * Get parallax option
     *
     * @param object $parallaxOptions
     * @return string
     */
    public function getParallaxOption( $parallaxOptions ) {
        $options['type'] = $parallaxOptions->type = $parallaxOptions->type ?? '2d';

        if ( $parallaxOptions->type === '2d' ) {
	        isset( $parallaxOptions->x ) && $options['x'] = $parallaxOptions->x;
            isset( $parallaxOptions->y ) && $options['y'] = $parallaxOptions->y;
        } elseif ( $parallaxOptions->type === '3d' ) {
	        isset( $parallaxOptions->x ) && $options['x'] = $parallaxOptions->x;
            isset( $parallaxOptions->y ) && $options['y'] = $parallaxOptions->y;
            isset( $parallaxOptions->rx ) && $options['rx'] = $parallaxOptions->rx;
            isset( $parallaxOptions->ry ) && $options['ry'] = $parallaxOptions->ry;
            isset( $parallaxOptions->zOrigin ) && $options['zOrigin'] = $parallaxOptions->zOrigin;
        } elseif (  $parallaxOptions->type == 'scroll' ||  $parallaxOptions->type == 'viewScroll' ) {
	        isset( $parallaxOptions->dir ) && $options['dir'] = $parallaxOptions->dir;
	        isset( $parallaxOptions->movement ) && $options['movement'] = $parallaxOptions->movement;
	        isset( $parallaxOptions->scale ) && $options['scale'] = $parallaxOptions->scale;
	        isset( $parallaxOptions->rotate ) && $options['rotate'] = $parallaxOptions->rotate;
	        // Add value for following params even if params are not set
			$options['fade']   = isset( $parallaxOptions->fade ) ? Data::isTrue( $parallaxOptions->fade ) : true;
			$options['twoWay'] = isset( $parallaxOptions->twoWay ) ? Data::isTrue( $parallaxOptions->twoWay ) : true;
        }

		$options['smooth'] = isset( $parallaxOptions->smooth ) ? Data::isTrue( $parallaxOptions->smooth ) : true;

        return JSON::encode( $options );
    }
}
