<?php

namespace Depicter\DataSources\Tags;

/**
 * Asset Group for Catalog
 *
 * {{{module->slug}}}
 * {{{module->slug|func('a','b')}}}
 *
 */
class Catalog extends TagBase implements TagInterface {

	/**
	 *  Asset group ID
	 */
	const ASSET_GROUP_ID = 'catalog';

	/**
	 * Get label of asset group
	 *
	 * @return string
	 */
	public function getName(){
		return __( "Catalog", 'depicter' );
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
		return $args[ $tagName ] ?? $tagName;
	}

}
