<?php

namespace Depicter\DataSources\Tags;


use Averta\WordPress\Utility\Plugin;

class MetaBoxIO extends TagBase implements TagInterface {

	/**
	 *  Asset group ID
	 */
	const ASSET_GROUP_ID = 'mataboxio';

	/**
	 * Get label of asset group
	 *
	 * @return string
	 */
	public function getName(){
		return __( "MetaBox.io Fields", 'depicter' );
	}

	/**
	 * Whether the asset group is enabled (available) or not
	 *
	 * @param array  $args
	 *
	 * @return bool
	 */
	public function isAvailable( array $args = [] ){
		return Plugin::isActive( 'meta-box/meta-box.php' );
	}

	/**
     * Get all metafields registered by metabox.io plugin
     *
     * @param array $args
	 *
     * @return array $result  List of assets for this module (asset group)
     */
    public function getAssetBlocks( array $args = [] ) {

        $configs = apply_filters( 'rwmb_meta_boxes', array() );

        $result = [];
        foreach ( $configs as $config ) {

            if ( !empty( $args['postType' ] ) && !empty( $config['post_types']) ) {
                if ( is_array( $config['post_types'] ) && !in_array( $args['postType'], $config['post_types'] ) ) {
                    continue;
                } elseif ( $args['postType'] != $config['post_types'] ) {
                    continue;
                }
            }

            if ( !empty( $config['fields'] ) ) {
                foreach ( $config['fields'] as $field ) {

                    if ( empty( $field['id'] || empty( $field['type'] ) ) ) {
                        continue;
                    }

                    if ( !empty( $args['type'] ) ) {
                        if ( is_array( $args['type'] ) ) {
                            if ( !in_array( $field['type'], $args['type'] ) ) {
                                continue;
                            }
                        } else {
                            if ( $args['type'] != $field['type'] ) {
                                continue;
                            }
                        }
                    }

                    $result[] = [
                        'id'    => $field['id'],
                        'title' => $field['name'] ?: '',
                        'previewOptions' => [
	                        "size" => 50,
	                        'multiline' => false,
	                        'textSize' => 'regular',
	                        'badge' => 'MetaBox.io'
                        ],
                        'input'  => $field['type'],
                        'type'  => 'dynamicText',
                        'func'  => null,
                        'payload' => [
                            'source' => $this->wrapCurly( 'metaboxio->' . $field['id'] )
                        ]
                    ];
                }
            }
		}

        return $result;
    }


	/**
	 * Get value of tag by tag name (slug)
	 *
	 * @param string $tagName  Tag name
	 * @param array  $args     Arguments of current document section
	 *
	 * @return mixed|string
	 */
	public function getSlugValue( string $tagName = '', array $args = [] ){
		if( ! $post = get_post( $args['post'] ?? null ) ){
			return $tagName;
		}

		return get_post_meta( $post->ID, $tagName, true );
	}

}
