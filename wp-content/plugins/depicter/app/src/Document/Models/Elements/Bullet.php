<?php
namespace Depicter\Document\Models\Elements;

use Depicter\Document\Models;
use Depicter\Html\Html;

class Bullet extends Models\Element
{

	public function render() {

		$args = $this->getDefaultAttributes();

		if ( isset( $this->options->direction ) ) {
			$args['data-direction'] = $this->options->direction;
		}

		return Html::div( $args );
	}

	/**
	 * Get bullet item selector
	 *
	 * @return string
	 */
	protected function getBulletItemSelector() {
		return '.' . $this->getSelector() . ' .depicter-bullet-item';
	}

	/**
	 * Get bullet item selector
	 *
	 * @return string
	 */
	protected function getBulletsWrapperSelector() {
		return '.' . $this->getSelector() . ' .depicter-bullets-wrapper';
	}

	/**
	 * get active bullet item selector
	 * @return string
	 */
	protected function getActiveBulletItemSelector() {
		return '.' . $this->getSelector() . ' .depicter-bullet-item.depicter-bullet-active';
	}

	/**
	 * get bullet items css
	 * @return array
	 */
	protected function getBulletItemCss() {
		$styles = [];

		foreach ( $this->devices as $device ) {

			if ( isset( $this->options->bullet->styles->color->{$device} ) ) {
				$styles[ $device ]['background-color'] = $this->options->bullet->styles->color->{$device};
			}

			if ( isset( $this->options->bullet->hover->color->{$device} ) ) {
				$styles['hover'][ $device ]['background-color'] = $this->options->bullet->hover->color->{$device};
			}

			if ( isset( $this->options->bullet->styles->corner->{$device} ) ) {
				$styles[ $device ]['border-radius'] = $this->getBulletItemBorderRadius( $this->options->bullet->styles->corner->{$device} );
			}

			if ( isset( $this->options->bullet->styles->size->{$device} ) ) {
				$styles[ $device ]['width']  = $this->options->bullet->styles->size->{$device}->width->value . $this->options->bullet->styles->size->{$device}->width->unit;
				$styles[ $device ]['height'] = $this->options->bullet->styles->size->{$device}->height->value . $this->options->bullet->styles->size->{$device}->height->unit;
			}
		}

		return $styles;
	}

	/**
	 * get bullet item border radius
	 * @param $deviceObject
	 *
	 * @return string
	 */
	protected function getBulletItemBorderRadius( $deviceObject ) {
		return $deviceObject->topRight->value . $deviceObject->topRight->unit . ' ' . $deviceObject->bottomRight->value . $deviceObject->bottomRight->unit . ' ' . $deviceObject->bottomLeft->value . $deviceObject->bottomLeft->unit . ' ' . $deviceObject->topLeft->value . $deviceObject->topLeft->unit . ' ';
	}

	/**
	 * Get active bullets item css
	 *
	 * @return array
	 */
	protected function getActiveBulletItemCss() {
		$styles = [];
		foreach ( $this->devices as $device ) {
			if ( isset( $this->options->bullet->styles->activeColor->{$device} ) ) {
				$styles[ $device ]['background-color'] = $this->options->bullet->styles->activeColor->{$device};
			}

			if ( isset( $this->options->bullet->hover->color->{$device} ) ) {
				$styles['hover'][ $device ]['background-color'] = $this->options->bullet->hover->color->{$device};
			}
		}

		return $styles;
	}

	/**
	 * Get styles for bullets wrapper
	 *
	 * @return array
	 */
	protected function getBulletsWrapperCss() {
		$styles = [];
		foreach ( $this->devices as $device ) {
			if ( isset( $this->options->bullet->styles->space->{$device}->right ) ) {
				$styles[ $device ]['gap'] = $this->options->bullet->styles->space->{$device}->right->value . $this->options->bullet->styles->space->{$device}->right->unit;
			}
		}
		$styles['default']['flex-direction'] = ($this->options->direction ?? 'h') === 'h' ? 'row' : 'column';

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

		$this->selectorCssList[ $this->getBulletItemSelector() ] = $this->getBulletItemCss();
		$this->selectorCssList[ $this->getActiveBulletItemSelector() ] = $this->getActiveBulletItemCss();
		$this->selectorCssList[ $this->getBulletsWrapperSelector() ] = $this->getBulletsWrapperCss();

		return $this->selectorCssList;
	}
}
