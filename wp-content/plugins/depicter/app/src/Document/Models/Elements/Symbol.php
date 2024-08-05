<?php
namespace Depicter\Document\Models\Elements;

use Depicter\Document\Models;
use Depicter\Html\Html;

class Symbol extends Models\Element
{

	public function render() {
		$symbolContent = '';

		if( ! empty( $this->options->content ) ){
			$symbolContent = $this->options->content;
		}

		$args = $this->getDefaultAttributes();
		$div = Html::div( $args, "\n\t" . $symbolContent . "\n" );

		if ( false !== $a = $this->getLinkTag() ) {
			return $a->nest( "\n" .$div ) . "\n";
		}

		return $div . "\n";
	}

	/**
	 * Get styles of svg
	 *
	 * @return array|array[]
	 */
	protected function getSymbolCss( $state = 'normal' ) {
		// Get styles list from styles property
		$symbolIconStyles = ! empty( $this->styles ) ? $this->styles->generateCssForModulesOfState( ['svg'], $state ) : [];

		if( !empty( $this->options->iconScale->default ) ){
			$symbolIconStyles['default']['transform'] = "scale( {$this->options->iconScale->default} )";
		}
		if( !empty( $this->options->iconScale->tablet ) ){
			$symbolIconStyles['tablet']['transform'] = "scale( {$this->options->iconScale->tablet} )";
		}
		if( !empty( $this->options->iconScale->mobile ) ){
			$symbolIconStyles['mobile']['transform'] = "scale( {$this->options->iconScale->mobile} )";
		}

		return $symbolIconStyles;
	}

	/**
	 * Get list of selector and CSS for element
	 *
	 * @return array
	 * @throws \JsonMapper_Exception
	 */
	public function getSelectorAndCssList(){
		parent::getSelectorAndCssList();

		$this->selectorCssList[ '.'. $this->getSelector() .' .depicter-symbol-container' ] = $this->getSymbolCss( 'normal' );
		$this->selectorCssList[ '.'. $this->getSelector() .':hover .depicter-symbol-container' ] = $this->getSymbolCss( 'hover' );

		$transition = $this->prepare()->styles->getTransitionCss();
		if ( !empty( $transition ) ) {
			$this->selectorCssList[ '.'. $this->getSelector() .' .depicter-symbol-container' ] = array_merge_recursive( $this->selectorCssList[ '.'. $this->getSelector() .' .depicter-symbol-container' ], $transition );
		}
		
		return $this->selectorCssList;
	}
}
