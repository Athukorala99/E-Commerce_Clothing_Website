<?php
namespace Depicter\Document\Models\Elements;

use Depicter\Html\Html;

class Arrow extends Svg {

	/**
	 * Render arrow markup
	 *
	 * @return \TypeRocket\Html\Html|void
	 * @throws \JsonMapper_Exception
	 */
	public function render() {
		$args = $this->getDefaultAttributes();
		return Html::div( $args, "\n" . $this->options->content . "\n" );
	}

	/**
	 * Get svg selector
	 *
	 * @return string
	 */
	public function getSvgSelector() {
		return '.' . $this->getSelector() . ' .depicter-arrow-icon';
	}

	/**
	 * Get list of selector and CSS for element
	 *
	 * @return array
	 * @throws \JsonMapper_Exception
	 */
	public function getSelectorAndCssList(){
		$this->selectorCssList = parent::getSelectorAndCssList();
		if ( !empty( $this->selectorCssList[ $this->getSvgSelector() ]['hover'] ) ) {
			$this->selectorCssList[ '.' . $this->getSelector() . ':not(.depicter-hover-off):hover .depicter-arrow-icon'] = $this->selectorCssList[ $this->getSvgSelector() ]['hover'];

			$transition = $this->prepare()->styles->getTransitionCss();
			if ( !empty( $transition ) ) {
				$this->selectorCssList[ $this->getSvgSelector() ] = array_merge_recursive( $this->selectorCssList[ $this->getSvgSelector() ], $transition );
			}

			unset( $this->selectorCssList[ $this->getSvgSelector() ]['hover'] );
		}
		return $this->selectorCssList;
	}
}
