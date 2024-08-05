<?php
namespace Depicter\Document\Models\Elements;

use Depicter\Html\Html;

class PlayAndPause extends Svg {

	/**
	 * render play and pause markup
	 * @return \TypeRocket\Html\Html|void
	 */
	public function render() {
		$args = $this->getDefaultAttributes();

		$playIcon  = $this->options->playIcon;
		$pauseIcon = $this->options->content;

		$playIcon  = str_replace('<svg ', '<svg class="depicter-play-icon" ', $playIcon );
		$pauseIcon = str_replace('<svg ', '<svg class="depicter-pause-icon" ', $pauseIcon );

		return Html::div( $args, "\n" . $playIcon . "\n" . $pauseIcon . "\n" );
	}

	/**
	 * Get svg selector
	 *
	 * @return string
	 */
	public function getSvgSelector() {
		return '.' . $this->getSelector() . ' svg';
	}
}
