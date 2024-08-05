<?php

namespace Depicter\DataSources\Tags;

class Manager {

	/**
	 * Replace dynamic tag with real value
	 *
	 * @param string $dynamicString
	 * @param array  $args
	 *
	 * @return string
	 */
	public function convert( string $dynamicString = '', $args = [] ): string{
		if( empty( $dynamicString ) ){
			return '';
		}

		// Temporary support for legacy dynamic tags
		if( $moduleInstance = \Depicter::resolve('depicter.dataSources.tags.legacy' ) ){
			if( $tag = $moduleInstance->findLegacyTag( $dynamicString ) ){
				$tagValue = $moduleInstance->getSlugValue( $tag, $args );
				return str_replace( $tag, $tagValue, $dynamicString );
			}
		}

		$regex  = '/{{{(.*?)->(.*?)(\|.*?)?}}}/m';

		preg_match_all( $regex, $dynamicString, $matches, PREG_SET_ORDER, 0 );

		if( !empty( $matches ) && is_array( $matches ) ){
			foreach( $matches as $match ){
				$currentOriginalTag = $match[0] ?? '';
				$moduleName = $match[1] ?? '';
				$slug = $match[2] ?? '';

				$fullFunction   = !empty( $match[3] ) ? trim( $match[3], '|' ) : '';
				$parsedFunction = $this->getFuncDetails( $fullFunction );

				$tagValue = $this->callDesignatedModule( $moduleName, $slug, $parsedFunction['name'], $parsedFunction['args'], $args );
				if( ! is_null( $tagValue ) ){
					$dynamicString = str_replace( $currentOriginalTag, $tagValue, $dynamicString );
				}
			}
		}

		return $dynamicString;
	}

	/**
	 * Collects asset blocks from specified groups
	 *
	 * @param $groups
	 *
	 * @return array
	 */
	public function getAssetsInGroups( $groups = [] ){
		$assets = [];

		foreach( $groups as $groupId => $groupArgs ){
			$groupAssets = $this->getAssetsOfGroup( $groupId, $groupArgs );
			if( ! empty( $groupAssets['items'] ) ){
				$assets[] = $groupAssets;
			}
		}

		return $assets;
	}

	/**
	 * Get a tag module by name
	 *
	 * @param string $moduleName
	 *
	 * @return TagInterface|null
	 */
	public function getModule( string $moduleName ){
		return \Depicter::resolve( 'depicter.dataSources.tags.' . $moduleName );
	}

	/**
	 * Retrieves assets of a group
	 *
	 * @param string $groupName
	 * @param array  $groupArgs
	 *
	 * @return array
	 */
	public function getAssetsOfGroup( string $groupName, array $groupArgs = [] ){
		// get module of asset group
		if( ! $moduleInstance = \Depicter::resolve('depicter.dataSources.tags.' . $groupName ) ){
			return [];
		}
		// skip if functionality of this group is not available (activated)
		if( method_exists( $moduleInstance, 'isAvailable' ) && ! $moduleInstance->isAvailable()  ){
			return [];
		}

		$assets = [];

		if( method_exists( $moduleInstance, 'getAssetBlocks' ) ){
			$groupAssets = call_user_func( [ $moduleInstance, 'getAssetBlocks' ], $groupArgs );
			if( $groupAssets ){
				$assets = [
					'id'    => $moduleInstance->getID(),
					'title' => $moduleInstance->getName(),
					'items' => $groupAssets
				];
			}
		}

		return $assets;
	}

	/**
	 * Extract function name and params from the dynamic tag
	 *
	 * @param string $fullFunction  Full function name
	 *
	 * @return array
	 */
	protected function getFuncDetails( string $fullFunction = '' ): array{
		$regex  = '/(\w+)(\s*\((.*)\))?/';

		$result = [
			'name' => '', 
			'args' => []
		];

		preg_match( $regex, $fullFunction, $matches, PREG_OFFSET_CAPTURE, 0);

		if ( empty( $matches ) ) {
			return $result;
		}

		$result['name'] = $matches[1][0] ?? '';
		$result['name'] = trim( $result['name'] );

		$params = !empty( $matches[3][0] ) ? trim( $matches[3][0] ) : '';
		$params = str_replace( "'", '"', $params );
		$result['args'] = str_getcsv( $params ); // convert helper function params into an array of args

		// remove unnecessary single and double quotes
		if( !empty( $result['args'] ) ){
			array_walk($result['args'], function( &$v, $k ){ $v = trim( $v, '"\'' ); });
		}

		return $result;
	}

	/**
	 * Get tag value from corresponding module
	 *
	 * @param string $moduleName  Module name
	 * @param string $slug        Slug name
	 * @param string $funcName    Function name to pipe to
	 * @param array  $funcArgs    Function arguments
	 * @param array  $args        Section arguments
	 *
	 * @return string|null
	 */
	protected function callDesignatedModule( string $moduleName, string $slug, string $funcName = '', array $funcArgs = [], array $args = [] ){
		if( ! $slug ){
			return null;
		}
		if( ! $moduleInstance = \Depicter::resolve('depicter.dataSources.tags.' . $moduleName ) ){
			return null;
		}

		$slugValue = $moduleInstance->getSlugValue( $slug, $args );

		// If a custom pipeFunction was not specified, use default pipe function
		$funcName  = $funcName ?: 'defaultPipeFunction';
		// pipe tag value to pipeFunction
		if( method_exists( $moduleInstance, $funcName ) ){
			$slugValue = call_user_func( [ $moduleInstance, $funcName ], $slugValue, $funcArgs, $args );
		}

		return apply_filters('depicter/dataSource/tags/slug/value', $slugValue, $moduleName, $slug, $funcName, $funcArgs, $args );
	}
}
