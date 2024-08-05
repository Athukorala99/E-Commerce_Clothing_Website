<?php
namespace Depicter\Document\Models\Elements;

use Depicter\Document\Models\Element;
use Depicter\Html\Html;

class LineTimer extends Element {

	/**
	 * render lineTimer markup
	 * @return \TypeRocket\Html\Html|void
	 */
	public function render() {
		$args = $this->getDefaultAttributes();
		return Html::div( $args );
	}

	/**
	 * get timer bar selector
	 * @return string
	 */
	protected function getTimerBarSelector() {
		return '.' . $this->getSelector() . ' .depicter-timer-bar';
	}

	/**
	 * get timer bar styles
	 * @return array
	 */
	protected function getTimerBarCss() {
		$styles = [];

		foreach ( $this->devices as $device ) {
			if ( isset( $this->options->lineTimer->styles->color->{$device} ) ) {
				$styles[ $device ]['background-color'] = $this->options->lineTimer->styles->color->{$device};
			}

			if ( isset( $this->options->lineTimer->styles->radius->{$device} ) ) {
				$borderRadius = $this->options->lineTimer->styles->radius->{$device}->value;
				$styles[ $device ]['border-radius'] = $borderRadius->topRight->value . $borderRadius->topRight->unit ." " . $borderRadius->bottomRight->value . $borderRadius->bottomRight->unit ." " .$borderRadius->bottomLeft->value . $borderRadius->bottomLeft->unit ." ". $borderRadius->topLeft->value . $borderRadius->topLeft->unit;
			}
		}

		return $styles;
	}

	/**
	 * Get list of selector and CSS for element
	 *
	 * @return array
	 * @throws \JsonMapper_Exception
	 */
	public function getSelectorAndCssList(){
		parent::getSelectorAndCssList();

		$this->selectorCssList[ $this->getTimerBarSelector() ] = $this->getTimerBarCss();

		return $this->selectorCssList;
	}
}
