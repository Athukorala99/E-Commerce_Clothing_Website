<?php
namespace Depicter\Document\Models\Elements;

use Depicter\Document\Models;
use Depicter\Html\Html;

class Shape extends Models\Element
{

	public function render() {
		$shapeContent = '';

		$args = $this->getDefaultAttributes();
		$div = Html::div( $args, $shapeContent );

		if ( false !== $a = $this->getLinkTag() ) {
			return $a->nest( "\n" . $div ) . "\n" ;
		}

		return $div . "\n";
	}
}
