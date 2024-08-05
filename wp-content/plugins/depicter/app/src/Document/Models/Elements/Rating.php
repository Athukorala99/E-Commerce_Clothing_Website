<?php
namespace Depicter\Document\Models\Elements;

use Depicter\Front\Symbols;
use Depicter\Html\Html;

class Rating extends Svg {

	/**
	 * Render arrow markup
	 *
	 * @return \TypeRocket\Html\Html|void
	 * @throws \JsonMapper_Exception
	 */
	public function render() {

		\Depicter::symbolsProvider()->add( $this->options->rating->symbol );

		$args = $this->getDefaultAttributes();
        $args['data-rate-value'] = $this->maybeReplaceDataSheetTags( $this->options->content );
        if ( $this->options->rating->round ) {
        	$args['data-rate-value'] = round( $args['data-rate-value'] );
        }
        $args['data-symbol'] = $this->options->rating->symbol;

		return Html::div( $args, "\n" );
	}


	/**
	 * Get list of selector and CSS for element
	 *
	 * @return array
	 * @throws \JsonMapper_Exception
	 */
	public function getSelectorAndCssList(){
		$this->selectorCssList = parent::getSelectorAndCssList();

		foreach ( $this->devices as $device ) {
			if ( !empty( $this->options->rating->styles->trackColor->{$device} ) ){
				$this->selectorCssList[ '.' . $this->getSelector() . ' .depicter-track-container' ][$device]['fill'] = $this->options->rating->styles->trackColor->{$device};
			}

			if ( !empty( $this->options->rating->hover->trackColor->{$device} ) ){
				$this->selectorCssList[ '.' . $this->getSelector() . ':hover .depicter-track-container' ][$device]['fill'] = $this->options->rating->hover->trackColor->{$device};
			}

			if ( !empty( $this->options->rating->styles->patternColor->{$device} ) ){
				$this->selectorCssList[ '.' . $this->getSelector() . ' .depicter-symbol-container' ][$device]['fill'] = $this->options->rating->styles->patternColor->{$device};
			}

			if ( !empty( $this->options->rating->hover->patternColor->{$device} ) ){
				$this->selectorCssList[ '.' . $this->getSelector() . ':hover .depicter-symbol-container' ][$device]['fill'] = $this->options->rating->hover->patternColor->{$device};
			}
		}

		return $this->selectorCssList;
	}
}
