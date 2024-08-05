<?php
namespace Depicter\Document\Models\Options;

use Averta\Core\Utility\Arr;
use Averta\WordPress\Utility\JSON;
use Depicter\Document\Helper\Helper;
use Depicter\Document\Models\Document;

class Script
{
	/**
	 * @param Document $document
	 *
	 * @return string
	 */
	public function getDocumentInitScript( $document )
	{
		$width  = array_values( $document->options->getSizes( 'width' ) );
		$height = array_values( $document->options->getSizes( 'height') );

		$attributes = [
			'width'             => $width,
			'height'            => $height,
			'keepAspectRatio'   => $document->options->general->keepAspect ?? false,
			'preload'           => isset( $document->options->loading ) ? $document->options->loading->getValue() : 0,
			'layout'            => $document->options->getLayout(),
			'rtl'               => $document->options->navigation->rtl ?? false,
			'initAfterAppear'   => isset( $document->options->loading ) && !empty( $document->options->loading->initAfterAppear ),
		];

		if ( $document->isBuildWithAI ) {
			$attributes['disableAnimations'] = true;
		}

		if( isset( $document->options->sectionTransition->type ) ){
			$attributes['view'] = !empty( $document->options->sectionTransition->type ) ? $document->options->sectionTransition->type : 'basic';
		}

		// viewOptions property
		$viewOptions = [];
		if( isset( $document->options->navigation->loop ) ){
			$viewOptions['loop'] = $document->options->navigation->loop;
		}
		if( $attributes['view'] === 'mask' ){
			if( isset( $document->options->sectionTransition->options->mask->maskParallax ) ){
				$viewOptions['maskParallax'] = $document->options->sectionTransition->options->mask->maskParallax;
			}
		} elseif( $attributes['view'] === 'transform' ){
			if( isset( $document->options->sectionTransition->options->transform->transformType ) ){
				$viewOptions['transformStyle'] = $document->options->sectionTransition->options->transform->transformType;
			}
		} elseif( $attributes['view'] === 'cube' ){
			if( isset( $document->options->sectionTransition->options->cube->shadow ) ){
				$viewOptions['shadow'] = $document->options->sectionTransition->options->cube->shadow;
			}
			if( isset( $document->options->sectionTransition->options->cube->dolly ) ){
				$viewOptions['dolly'] = $document->options->sectionTransition->options->cube->dolly;
			}
		}
		if( $attributes['view'] !== 'fade' ) {
			if ( isset( $document->options->sectionTransition->options->basic->space ) ) {
				$viewOptions['space'] = $document->options->sectionTransition->options->basic->space;
			}
			if ( isset( $document->options->sectionTransition->options->basic->direction ) ) {
				$viewOptions['dir'] = $document->options->sectionTransition->options->basic->direction;
			}
		}

		if( in_array( $attributes['view'], ['basic', 'transform'] ) ){
			if ( isset( $document->options->sectionTransition->options->basic->nearbyVisibility ) ) {
				$viewOptions['nearbyVisibility'] = $document->options->sectionTransition->options->basic->nearbyVisibility;
			}
			if ( isset( $document->options->sectionTransition->options->basic->nearbyVisibilityAmount->value ) ) {
				$viewOptions['nearbyVisibilityAmount'] = $document->options->sectionTransition->options->basic->nearbyVisibilityAmount->value . $document->options->sectionTransition->options->basic->nearbyVisibilityAmount->unit;
			}
		}

		if( $viewOptions ){
			$attributes['viewOptions'] = $viewOptions;
		}

		// slideShow property
		if( ! empty( $document->options->navigation->slideshow->enable ) ){
			$slideShow = [];
			if( isset( $document->options->navigation->slideshow->duration ) ){
				$slideShow['duration'] = $document->options->navigation->slideshow->duration;
			}
			if( isset( $document->options->navigation->slideshow->pauseOnLastSlide ) ){
				$slideShow['pauseAtEnd'] = $document->options->navigation->slideshow->pauseOnLastSlide;
			}
			if( isset( $document->options->navigation->slideshow->pauseOnHover ) ){
				$slideShow['pauseOnHover'] = $document->options->navigation->slideshow->pauseOnHover;
			}

			if( isset( $document->options->navigation->slideshow->resetTimerOnBlur ) ){
				$slideShow['resetTimerOnBlur'] = $document->options->navigation->slideshow->resetTimerOnBlur;
			}

			$slideShow['autostart'] = $document->options->navigation->slideshow->enable;

			$attributes['slideshow'] = $slideShow;
		}

		if( !empty( $document->options->navigation->swipe->enable ) ){
			if( isset( $document->options->navigation->swipe->mouseSwipe ) ){
				$attributes['mouseSwipe'] = $document->options->navigation->swipe->mouseSwipe;
			}
			if( isset( $document->options->navigation->swipe->touchSwipe ) ){
				$attributes['touchSwipe'] = $document->options->navigation->swipe->touchSwipe;
			}
			if( isset( $document->options->navigation->swipe->direction ) ){
				$attributes['swipeDir'] = $document->options->navigation->swipe->direction;
			}
		} else {
			$attributes['mouseSwipe'] = false;
			$attributes['touchSwipe'] = false;
		}

		if( isset( $document->options->navigation->mouseWheel ) ){
			$attributes['mouseWheel'] = $document->options->navigation->mouseWheel;
		}

		if( isset( $document->options->navigation->keyboardNavigation ) ){
			$attributes['keyboard'] = $document->options->navigation->keyboardNavigation;
		}

		if( isset( $document->options->general->fullscreenMargin ) ){
			$attributes['fullscreenMargin'] = $document->options->general->fullscreenMargin;
		}

		// navigator property
		$navigator = [];

		if( !empty( $document->options->navigator ) ){
			$navigator = (array) $document->options->navigator;
		}
		if( !empty( $document->options->navigator->duration->value ) ){
			$navigator['duration'] = $document->options->navigator->duration->value;
		}
		if( !empty( $document->startSection ) ){
			$navigator['start'] = $document->startSection;
		}

		if( $navigator ){
			$attributes['navigator'] = $navigator;
		}

		$sliderName = 'dpPlayer';
		$displayExtensionScript = $this->generateDisplayExtensionScript( $document, $sliderName );

		if ( ! empty( $displayExtensionScript ) ) {
			$attributes['detachBeforeInit'] = true;
		}

		$script  = "\n(window.depicterSetups = window.depicterSetups || []).push(function(){";
		$script .= "\n\tconst $sliderName = Depicter.setup('.{$document->getSelector()}',\n\t\t";

		$attributesString = JSON::encode( $attributes );

		$script .= "{$attributesString}\n\t);\n";

		$script .= $document->options->getCallbacks( $sliderName );

		$script .= $displayExtensionScript;

		$script .= "});\n";

		return $script;
	}

	/**
	 * Generate display extension script
	 * 
	 * @param Document $document
	 * @param string   $sliderName
	 *
	 * @return string
	 */
	public function generateDisplayExtensionScript( $document, $sliderName ) {

		if ( ! $document->isDisplayExtension() ) {
			return '';
		}
		if( empty( $document->options->documentTypeOptions->displayOptions ) ){
			return '';
		}
		$displayOptions = $document->options->documentTypeOptions->displayOptions;

		unset( $displayOptions->animation );
		unset( $displayOptions->backdropColor );
		unset( $displayOptions->backdropBlur );

		$extensionParams = [
			"type" => $document->getType(),
			"id"   => $document->getCssId(),
			"className" => $document->getDisplayStyleSelector(),
			"displayOptions" => $displayOptions
		];

		$triggerParams = '';

		if ( ! \Depicter::front()->preview()->isPreview() ){
			$displayAgain = Helper::getDisplayAgainProperties( $document->getDocumentID() );
			$extensionParams = Arr::merge( $displayAgain, $extensionParams );

			if( $triggers = Helper::getDisplayRuleTriggers( $document->getDocumentID() ) ){
				$triggerParams = ",\n\t\t" . JSON::encode( $triggers );
			}
		}

		return "\n\tDepicter.display( $sliderName, \n\t\t". JSON::encode( $extensionParams ). "{$triggerParams}\n\t);\n";
	}
}
