<?php

namespace Depicter\Document\Models\Traits;

use Depicter\Html\Html;

trait MediaSourceTrait {

	/**
	 * URL dictionary
	 *
	 * @var array
	 */
	protected $urlsDictionary = [];

	/**
	 *
	 * @param array  $sourceUrls
	 * @param string $mediaQueryCondition
	 * @param string $mediaQuerySize
	 * @param string $mediaType
	 *
	 * @return void
	 */
	protected function addMediaUlrToDictionary( $sourceUrls, $mediaQueryCondition = 'max-width', $mediaQuerySize = '', $mediaType = '' ){
		if( empty( $sourceUrls ) ){
			return;
		}
		$this->urlsDictionary[] = [
			'sources'             => (array) $sourceUrls,
			'mediaQueryCondition' => $mediaQueryCondition,
			'mediaQuerySize'      => $mediaQuerySize,
			'mediaType'           => $mediaType
		];
	}

	public function getMediaUrls(){
		return $this->urlsDictionary;
	}

	public function getPreloadMarkup(){
		$output ='';

		$urlCount = count( $this->urlsDictionary );

		foreach( $this->urlsDictionary as $key => $mediaInfo ){
			if( empty( $mediaInfo ) ){
				continue;
			}

			$attributes = [
				'rel'   => 'preload',
				'as'    => 'image',
				'href'  => $mediaInfo['sources'][0] ?? ''
			];

			if( ! empty( $mediaInfo['mediaType'] ) ){
				$attributes['type'] = $mediaInfo['mediaType'];
			}

			if( !empty( $mediaInfo['sources'][1] ) ){
				$attributes['imagesrcset'] = $mediaInfo['sources'][1];
			}

			if( $urlCount > 1 ){
				if( $key > 0 ){
					$mediaQueryCondition = 'min-width';
					$mediaQuerySize      = !empty( $mediaQuerySize ) ? $mediaQuerySize : $mediaInfo['mediaQuerySize'];
					$attributes['media'] = '(' . $mediaQueryCondition . ':' . $mediaQuerySize . '.1px)';
					if( $urlCount > $key + 1  ){
						$attributes['media'] .= ' and (max-width:' . $mediaInfo['mediaQuerySize'] . 'px)';
					}
					$mediaQuerySize = $mediaInfo['mediaQuerySize'];
				} else {
					$mediaQueryCondition = 'max-width';
					$mediaQuerySize      = $mediaInfo['mediaQuerySize'];
					$attributes['media'] = '(' . $mediaQueryCondition . ':' . $mediaQuerySize . 'px)';
				}
			}

			$linkTag = Html::link( $attributes );
			$output .= $linkTag . "\n";
		}

		return $output;
	}

}
