<?php
namespace Depicter\Document\Models\Elements;

use Averta\Core\Utility\Arr;
use Depicter;
use Depicter\Document\Models;
use Depicter\Html\Html;
use Depicter\Services\MediaBridge;

class Video extends Models\Element
{

	public function render() {

		if ( empty( $this->options->source ) ) {
			return '';
		}

		$elementsAttrs = [];

		if( isset( $this->options->autoPlay ) ){
			$elementsAttrs['data-autoplay'] = $this->options->autoPlay ? "true" : "false";
		}
		if( isset( $this->options->autoPause ) ){
			$elementsAttrs['data-auto-pause'] = $this->options->autoPause ? "true" : "false";
		}

		if( isset( $this->options->goNextSection ) ){
			$elementsAttrs['data-goto-next'] = $this->options->goNextSection ? "true" : "false";
		}
		if( isset( $this->options->loop ) ){
			$elementsAttrs['data-loop'] = $this->options->loop ? "true" : "false";
		} else {
			$elementsAttrs['data-loop'] = "true";
		}

		$videoAttrs = [
			'src'     => Depicter::media()->getSourceUrl( $this->options->source ),
			'preload' => 'metadata'
		];

		if( !empty( $this->options->controls ) ){
			$videoAttrs['controls'] = "";
		}
		if( !empty( $this->options->mute ) ){
			$videoAttrs['muted'] = "";
		}

		$elementsAttrs = Arr::merge( $this->getDefaultAttributes(), $elementsAttrs );
		if ( isset( $elementsAttrs['data-height'] ) ) {
			$elementsAttrs['data-height'] = 'auto,,';
		}

		$div = Html::div( $elementsAttrs );

		$video = "\n\t" . Html::video( $videoAttrs ) . "\n";

		if ( false !== $a = $this->getLinkTag() ) {
			return $a->nest( $div->nest( $video ) );
		}

		return $div->nest( $video ) . "\n";
	}
}
