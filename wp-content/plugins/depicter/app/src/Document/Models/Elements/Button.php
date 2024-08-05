<?php
namespace Depicter\Document\Models\Elements;

use Depicter\Document\Models;
use Depicter\Html\Html;

class Button extends Models\Element
{

	public function render() {

		$args = $this->getDefaultAttributes();
		$content = $this->maybeReplaceDataSheetTags( $this->options->content );
		$div = Html::div( $args, $content );

		if ( false !== $a = $this->getLinkTag() ) {
			return $a->nest( $div );
		}
		return $div;
	}
}
