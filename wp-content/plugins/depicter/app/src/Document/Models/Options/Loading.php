<?php
namespace Depicter\Document\Models\Options;


use Depicter\Document\CSS\Selector;
use Depicter\Html\Html;

class Loading
{
	/**
	 * @var object
	 */
	public $lazyload;

	/**
	 * @var string
	 */
	public $loadingSymbol = 'dotFlashing-dark';

	/**
	 * @var bool
	 */
	public $initAfterAppear;

	/**
	 * get html of loading symbol
	 * @return \TypeRocket\Html\Html
	 */
	public function render() {

		// loadingSymbol consist of two part , the symbolID and dark or light mode
		$loadingSymbol = explode( '-', $this->loadingSymbol );
		$loadingMarkup = '';
		$loadingPath = DEPICTER_PLUGIN_PATH . '/resources/scripts/loadings/snippets/'.$loadingSymbol[0].'/'.$loadingSymbol[1].'.html';

		if ( in_array( $loadingSymbol[0], $this->loadingsList() ) && is_file( $loadingPath ) ) {
			$loadingMarkup = file_get_contents( $loadingPath );
		}

		return Html::div(
			[
				'class'    => Selector::prefixify('loading-container') . ' ' . Selector::prefixify('loading ' . $this->loadingSymbol ),
			],
		    $loadingMarkup
		);
	}

	/**
	 * Get preload value
	 *
	 * @return false|string
	 */
	public function getValue() {
		$type = $this->lazyload->type ?? 'sequential';

		switch ( $type ):
			case 'nearby':
				return $this->lazyload->nearbyNum ?? 1;
				break;
			case 'sequential':
				return 0;
				break;
			case 'all':
				return 'all';
				break;
			default:
				return false;
				break;
		endswitch;

	}

	/**
	 * The list of valid loading symbol names
	 *
	 * @return array
	 */
	public function loadingsList() {
		return [
			'audio',
			'ballTriangle',
			'bars',
			'circles',
			'dotFlashing',
			'dotReplacing',
			'dotStraightSwing',
			'dotSwing',
			'grid',
			'hearts',
			'oval',
			'puff',
			'rings',
			'spinningCircles',
			'tailSpin',
			'threeDots'
		];
	}
}
