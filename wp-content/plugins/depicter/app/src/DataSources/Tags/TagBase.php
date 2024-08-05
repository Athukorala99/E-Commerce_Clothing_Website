<?php

namespace Depicter\DataSources\Tags;

class TagBase {

	/**
	 *  Asset group ID
	 */
	const ASSET_GROUP_ID = 'base';

	/**
	 * Get label of asset group
	 *
	 * @return string
	 */
	public function getID(){
		return static::ASSET_GROUP_ID;
	}

	/**
	 * Get label of asset group
	 *
	 * @return string
	 */
	public function getName(){
		return __( "Base", 'depicter' );
	}

	/**
	 * Whether the asset group is enabled (available) or not
	 *
	 * @param array  $args  module params
	 *
	 * @return bool
	 */
	public function isAvailable( array $args = [] ){
		return true;
	}

	/**
	 * Get list of assets in this group
	 *
	 * @param array  $args
	 *
	 * @return array
	 */
	public function getAssetBlocks( array $args = [] ){
		return [];
	}

	/**
	 * Get value of tag by tag name (slug)
	 *
	 * @param string $tagName  Tag name
	 * @param array  $args     Arguments of current document section
	 *
	 * @return string
	 */
	public function getSlugValue( string $tagName = '', array $args = [] ){
		return $tagName;
	}

	/**
	 * Get list of tags and values for this asset group
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function getValuesForRecord( array $args = [] ){
		$result = [];

		$tagsList = $this->getAssetBlocks( $args );
		foreach( $tagsList as $tagInfo ){
			$result[ $tagInfo['id'] ] = $this->getSlugValue( $tagInfo['id'], $args );
		}
		return $result;
	}

	/**
	 * Default function to process tag value if a custom tag function was not specified
	 *
	 * @param mixed $value      The tag value to be piped
	 * @param array $funcArgs   Function args presented in dynamic tag
	 * @param array $args       Arguments of current document section
	 *
	 * @return mixed|string
	 */
	public function defaultPipeFunction( $value, array $funcArgs = [], array $args = [] ){
		return $value;
	}

	/**
	 * Wrap tag value in curly brackets
	 *
	 * @param $value
	 *
	 * @return string
	 */
	protected function wrapCurly( $value ){
		return '{{{'. $value .'}}}';
	}

	/**
	 * Get list of assets ids which should be excluded from asset blocks list
	 *
	 * @param array  $args
	 *
	 * @return array
	 */
	protected function getExcludedAssetIds( $args = [] ){
		return [];
	}
}
