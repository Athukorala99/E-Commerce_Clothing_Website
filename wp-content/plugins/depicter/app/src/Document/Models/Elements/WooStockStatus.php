<?php
namespace Depicter\Document\Models\Elements;

class WooStockStatus extends Text
{

	const IN_STOCK_CLASS= 'in-stock';
	const OUT_OF_STOCK_CLASS = 'out-of-stock';

	/**
	 * Retrieves the content of element
	 *
	 * @return string
	 */
	protected function getContent(){
		$content = $this->maybeReplaceDataSheetTags( $this->options->content );
		$statusClass = $this->maybeReplaceDataSheetTags( '{{{stockStatusClass}}}' );

		if( $statusClass === self::IN_STOCK_CLASS ){
			$content = $this->options->stockStatus->inStockText ?? $content;
		} elseif( $statusClass === self::OUT_OF_STOCK_CLASS ) {
			$content = $this->options->stockStatus->outOfStockText ?? $content;
		}

		return $content;
	}

	/**
	 * Get element class names
	 *
	 * @return string
	 */
	public function getClassNames() {
		return parent::getClassNames() . ' ' . $this->maybeReplaceDataSheetTags( '{{{stockStatusClass}}}' );
	}

	/**
	 * Get list of selector and CSS for element
	 *
	 * @return array
	 * @throws \JsonMapper_Exception
	 */
	public function getSelectorAndCssList(){

		parent::getSelectorAndCssList();

		if ( !empty( $this->options->stockStatus->styles ) ) {

			$styles = $this->options->stockStatus->styles;
			foreach ( $this->devices  as $device ) {
				if ( !empty( $styles->inStockTextColor->{$device} ) ) {
					$this->selectorCssList[ '.' . $this->getStyleSelector() . '.' . self::IN_STOCK_CLASS ][ $device ]['color'] = $styles->inStockTextColor->{$device};
				}

				if ( !empty( $styles->outOfStockTextColor->{$device} ) ) {
					$this->selectorCssList[ '.' . $this->getStyleSelector() . '.' . self::OUT_OF_STOCK_CLASS ][ $device ]['color'] = $styles->outOfStockTextColor->{$device};
				}
			}
		}

		if ( !empty( $this->options->stockStatus->hover ) ) {

			$styles = $this->options->stockStatus->hover;
			foreach ( $this->devices  as $device ) {
				if ( !empty( $styles->inStockTextColor->{$device} ) ) {
					$this->selectorCssList[ '.' . $this->getStyleSelector() . '.' . self::IN_STOCK_CLASS ]['hover'][ $device ]['color'] = $styles->inStockTextColor->{$device};
				}

				if ( !empty( $styles->outOfStockTextColor->{$device} ) ) {
					$this->selectorCssList[ '.' . $this->getStyleSelector() . '.' . self::OUT_OF_STOCK_CLASS ]['hover'][ $device ]['color'] = $styles->outOfStockTextColor->{$device};
				}
			}
		}

		return $this->selectorCssList;
	}
}
