<?php

namespace Depicter\Document\Models\Elements;

use Depicter\Document\CSS\Selector;
use Depicter\Document\Models\Element;
use Depicter\Html\Html;
use Depicter\Utility\Sanitize;

class TagList extends Element {

	public function render(){
		$args = $this->getDefaultAttributes();

		return Html::div( $args, $this->getContent() ) . "\n";
	}

	/**
	 * Retrieves the content of element
	 *
	 * @return string
	 */
	protected function getContent(){
		$args = [
			'limit' => !empty( $this->options->tagList->limit ) ? Sanitize::int( $this->options->tagList->limit ) : '',
			'linkTags' => !! ( $this->options->tagList->linkTags ?? true )
		];

		if ( $this->options->tagList->useSeparator ?? true ) {
			$args['separator'] = $this->options->tagList->separator ?? ',';
		}

		return $this->maybeReplaceDataSheetTags( $this->options->content, null, $args );
	}

	/**
	 * Get selector of tag item
	 * @return string
	 */
	protected function getTagItemSelector() {
		return '.' . $this->getSelector() . ' .' .Selector::prefixify('tag-item');
	}


	/**
	 * Get list of selector and CSS for element
	 *
	 * @return array
	 * @throws \JsonMapper_Exception
	 */
	public function getSelectorAndCssList(){
		$this->selectorCssList = parent::getSelectorAndCssList();

		foreach ( $this->devices as $device ){
			if ( !empty( $this->options->space->{$device} ) ) {
				$this->selectorCssList[ '.' . $this->getStyleSelector() ][ $device ]['gap'] = '0 ' . $this->options->space->{$device} . 'px';
			}
		}

		$transitionCss = $this->prepare()->styles->getTransitionCss();
		$innerStyles = $this->prepare()->innerStyles;
		if ( !empty( $innerStyles ) && !empty( $innerStyles->items ) ) {
			$this->selectorCssList[ $this->getTagItemSelector() ] = $innerStyles->items->getGeneralCss('normal');
			if ( !empty( $this->prepare()->styles->hover->enable ) ) {
				$innerStyles->items->hover->enable = $this->prepare()->styles->hover->enable;
			}
			
			$hoverCss = $innerStyles->items->getGeneralCss('hover');
			$transitionCss['hover'] = $hoverCss['hover'];
		}
		
		$this->selectorCssList[ $this->getTagItemSelector() . ':not(.depicter-hover-off)' ] = $transitionCss;

		return $this->selectorCssList;
	}
}
