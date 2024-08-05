<?php

namespace Depicter\DataSources\Tags;

interface TagInterface {

	/**
	 * Get label of asset group
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Whether the asset group is enabled (available) or not
	 *
	 * @param array  $args  module params
	 *
	 * @return bool
	 */
	public function isAvailable( array $args = [] );

	/**
     * Get list of assets in this group
     *
     * @param array $args
	 *
     * @return array $result  List of assets for this group
     */
    public function getAssetBlocks( array $args = [] );

	/**
	 * Get value of tag by tag name (slug)
	 *
	 * @param string $tagName  Tag name
	 * @param array  $args     Arguments of current document section
	 *
	 * @return mixed
	 */
	public function getSlugValue( string $tagName = '', array $args = [] );

	/**
	 * Get list of tags and values for this asset group
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function getValuesForRecord( array $args = [] );
}
