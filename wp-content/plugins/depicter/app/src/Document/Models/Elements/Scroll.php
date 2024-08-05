<?php
namespace Depicter\Document\Models\Elements;

use Depicter\Html\Html;

class Scroll extends Svg {

	/**
	 * render scroll markup
	 *
	 * @return \TypeRocket\Html\Html
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
		return '.'. $this->getSelector() .' .depicter-symbol-container';
	}

	/**
	 * Get element style selector
	 *
	 * @return string
	 */
	public function getStyleSelector() {
		return '.'. $this->getSelector() .' .depicter-symbol-container';
	}

}
