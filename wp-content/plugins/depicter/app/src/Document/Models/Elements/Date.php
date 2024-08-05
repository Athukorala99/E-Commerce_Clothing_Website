<?php
namespace Depicter\Document\Models\Elements;

use Averta\Core\Utility\Arr;
use Depicter\Document\Models;
use Depicter\Html\Html;

class Date extends Models\Element
{

	public function render() {
		$args = $this->getDefaultAttributes();

		$time = strtotime( $this->getContent() );
		$args['datetime'] = date( "Y-m-d H:i:s", $time );
		$args['data-use-relative'] = !empty( $this->options->date->useRelative ) ? 'true' : 'false';
		$args['data-display-time'] = !empty( $this->options->date->displayTime ) ? 'true' : 'false';

		if ( !empty( $this->options->date->formatStyle ) ) {
			$args['data-format-style'] = $this->options->date->formatStyle;
		}

		$content = date( 'F d, Y', $time );
		$output =  Html::time( $args, $content );
		return $output . "\n";
	}

	/**
	 * Retrieves the content of element
	 *
	 * @return string
	 */
	protected function getContent(){
		return $this->maybeReplaceDataSheetTags( $this->options->content );
	}
}
