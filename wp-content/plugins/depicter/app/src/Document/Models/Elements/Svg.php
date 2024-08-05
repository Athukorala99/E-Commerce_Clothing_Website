<?php
namespace Depicter\Document\Models\Elements;

use Depicter\Document\Models;
use Depicter\Html\Html;

class Svg extends Models\Element
{

	public function render() {
		$shapeContent = '';

		if( ! empty( $this->options->content ) ){
			$shapeContent = $this->options->content;
		} elseif ( ! empty( $this->options->source ) && $shapeUrl = wp_get_attachment_url( $this->options->source ) ) {
			$shapeContent = file_get_contents( $shapeUrl );
		}

		$args = $this->getDefaultAttributes();
		$div = Html::div( $args, "\n\t" . $shapeContent . "\n" );

		if ( false !== $a = $this->getLinkTag() ) {
			return $a->nest( "\n" .$div ) . "\n";
		}

		return $div . "\n";
	}

	/**
	 * Get svg selector
	 *
	 * @return string
	 */
	protected function getSvgSelector() {
		return '.' . $this->getSelector() . ' svg, .' . $this->getSelector() . ' path';
	}

	/**
	 * Get styles of svg
	 *
	 * @return array|array[]
	 * @throws \JsonMapper_Exception
	 */
	protected function getSvgCss() {
		// Get styles list from styles property
		return ! empty( $this->styles ) ? $this->styles->getSvgCss() : [];
	}

	/**
	 * Get list of selector and CSS for element
	 *
	 * @return array
	 * @throws \JsonMapper_Exception
	 */
	public function getSelectorAndCssList(){
		parent::getSelectorAndCssList();

		// Add SVG selector and css
		$this->selectorCssList[ $this->getSvgSelector() ] = $this->getSvgCss();

		return $this->selectorCssList;
	}
}
