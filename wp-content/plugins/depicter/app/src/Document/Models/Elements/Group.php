<?php
namespace Depicter\Document\Models\Elements;

use Averta\Core\Utility\Arr;
use Depicter\Document\Models;
use Depicter\Html\Html;

class Group extends Models\Element
{

	public function render() {

		if ( empty( $this->childrenObjects ) ) {
			return '';
		}

		$args = $this->getDefaultAttributes();

		$div = Html::div( $args, "\n" );
		foreach ( $this->childrenObjects as $element ) {
			$div->nest( $element->prepare()->render() );
		}

		if ( false !== $a = $this->getLinkTag() ) {
			return $a->nest( $div );
		}

		return $div . "\n";
	}

	/**
	 * Get list of selector and CSS for element and belonging child elements
	 *
	 * @return array
	 * @throws \JsonMapper_Exception
	 */
	public function getSelectorAndCssList(){
		parent::getSelectorAndCssList();

		foreach ( $this->childrenObjects as $element ) {
			$this->selectorCssList = Arr::merge( $this->selectorCssList, $element->prepare()->getSelectorAndCssList() );
		}

		return $this->selectorCssList;
	}

	/**
	 * Retrieves list of fonts used in typography options
	 *
	 * @return array
	 * @throws \JsonMapper_Exception
	 */
	public function getFontsList()
	{
		if ( empty( $this->childrenObjects ) ) {
			return '';
		}

		foreach ( $this->childrenObjects as $element ) {
			$fontsList = ! empty( $element->prepare()->styles ) ? $element->prepare()->styles->getFontsList() : [];
			\Depicter::app()->documentFonts()->addFonts( $element->getDocumentID(), $fontsList, 'google' );
		}

		return $fontsList;
	}
}
