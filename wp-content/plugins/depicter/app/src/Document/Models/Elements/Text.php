<?php
namespace Depicter\Document\Models\Elements;

use Averta\Core\Utility\Arr;
use Depicter\Document\Models;
use Depicter\Html\Html;

class Text extends Models\Element
{

	public function render() {
		$tag = $this->options->tag ?? 'p';

		$args = $this->getDefaultAttributes();

		$output =  Html::$tag( $args, $this->getContent() );

		if ( false !== $a = $this->getLinkTag() ) {
			return $a->nest( $output ) . "\n";
		}
		return $output . "\n";
	}

	/**
	 * Retrieves the content of element
	 *
	 * @return string
	 */
	protected function getContent(){
		$content = $this->maybeReplaceDataSheetTags( $this->options->content );
		return str_replace("\n", "<br>", $content);
	}
}
