<?php

namespace Depicter\DataSources\Tags;

use Averta\WordPress\Utility\Plugin;

class ACF extends TagBase implements TagInterface {

	/**
	 *  Asset group ID
	 */
	const ASSET_GROUP_ID = 'acf';

	/**
	 * Get label of asset group
	 *
	 * @return string
	 */
	public function getName(){
		return __( "Advanced Custom Fields", 'depicter' );
	}

	/**
	 * Whether the asset group is enabled (available) or not
	 *
	 * @param array  $args
	 *
	 * @return bool
	 */
	public function isAvailable( array $args = [] ){
		return Plugin::isActive( 'advanced-custom-fields/acf.php' );
	}

	/**
     * Get all acf defined fields
     *
     * @param array $args
	 *
     * @return array $result  List of assets for this module (asset group)
     */
    public function getAssetBlocks( array $args = [] ) {

        $result = [];

        $fields = $this->getFieldGroups( $args );

        if ( !empty( $fields ) ) {
            foreach ( $fields as $field ) {

                if ( !empty( $args['inputType'] ) ) {
                    if ( is_array( $args['inputType'] ) ) {
                        if ( !in_array( $field['type'], $args['inputType'] ) ) {
                            continue;
                        }
                    } else {
                        if ( $args['inputType'] != $field['type'] ) {
                            continue;
                        }
                    }
                }

                $asset = [
                    'id'    => $field['id'],
                    'title' => $field['title'],
                    'previewOptions' => [
	                    "size" => 50,
	                    'multiline' => false,
	                    'textSize' => 'regular',
	                    'badge' => 'ACF'
                    ],
                    'type'   => 'dynamicText',
                    'input'  => $field['type'],
                    'func'  => null,
	                'payload' => [
                        'source' => $this->wrapCurly( 'acf->' . $field['id'] )
                    ]
                ];

				if( in_array( $field['type'], ['url', 'page_link'] ) ){
					$asset['type']                         = 'dynamicLink';
					$asset['previewOptions']['variant']    = 'link';
					$asset['previewOptions']['buttonText'] = $field['title'];
					$asset['payload']['url']               = $this->wrapCurly( 'acf->' . $field['id'] );
				} elseif( $field['type'] === 'link' ){
					$asset['type'] = 'dynamicLink';
					$asset['previewOptions']['variant'] = 'link';
					$asset['payload']['source'] = $this->wrapCurly( 'acf->' . $field['id']. '.title' );
					$asset['payload']['url'] = $this->wrapCurly( 'acf->' . $field['id']. '.url' );
				} elseif( $field['type'] === 'image' ){
					$asset['type'] = 'dynamicMedia';
					$asset['sourceType'] = 'image';
					$asset['payload']['source'] = $this->wrapCurly( 'acf->' . $field['id'] );
				}

				$result[] = $asset;
            }
        }

        return $result;
    }

	public function getFieldGroups( array $args = [] ){
		$result = [];

        if ( !function_exists( 'acf_get_field_groups' ) ) {
            return $result;
        }

        $queryArgs = [
            'post_type' => 'acf-field',
            'numberposts' => -1,
            'post_status' => 'publish'
        ];

        if ( !empty( $args['postType'] ) ) {
            $fieldGroups = acf_get_field_groups([
                'post_type' => $args['postType']
            ]);

			// skip if there is no field group for this post type
            if ( empty( $fieldGroups ) ) {
				return [];
            }
			$fieldGroupIds = [];
			foreach ( $fieldGroups as $fieldGroup ) {
				$fieldGroupIds[] = $fieldGroup['ID'];
			}
			if ( !empty( $fieldGroupIds ) ) {
				$queryArgs['post_parent__in'] = $fieldGroupIds;
			}
        }

        $fields = get_posts( $queryArgs );

		if ( ! empty( $fields ) ) {
            foreach ( $fields as $field ) {
                $content = maybe_unserialize( $field->post_content );

				$result[ $field->post_excerpt ] = [
					'id'   => $field->post_excerpt,
					'title' => $field->post_title,
					'type' => $content['type'],
					'return_format' => $content['return_format'] ?? ''
				];
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

		if( $fieldValue = get_post_meta( $post->ID, $tagName, true ) ){
			return $fieldValue;
		}

		// try to get value of a dot-separated tagName
		$filedParts = explode( '.', $tagName );
		if( ! empty( $filedParts[0] ) && ! empty( $filedParts[1] ) ){
			$fieldValue = get_post_meta( $post->ID, $filedParts[0], true );
			if( is_string( $fieldValue ) ){
				return $fieldValue;
			}
			if( is_array( $fieldValue ) && ! empty( $fieldValue[ $filedParts[1] ] ) ){
				return $fieldValue[ $filedParts[1] ];
			}
		}

		return '';
	}

	/**
	 * Converts attachment ID to attachment source
	 *
	 * @param mixed $value      The tag value to be piped
	 * @param array $funcArgs   Function args presented in dynamic tag
	 * @param array $args       Arguments of current document section
	 *
	 * @return mixed|string
	 */
	public function toSrc( $value, array $funcArgs = [], array $args = [] ){
		$imageSize = $funcArgs[0] ?? 'full';

		$attachmentImageInfo = wp_get_attachment_image_src( $value, $imageSize );
		return ! empty( $attachmentImageInfo[0] ) ? $attachmentImageInfo[0] : '';
	}

	/**
	 * Converts the date by specified date format
	 *
	 * @param mixed $value      The tag value to be piped
	 * @param array $funcArgs   Function args presented in dynamic tag
	 * @param array $args       Arguments of current document section
	 *
	 * @return mixed|string
	 */
	public function date( $value, array $funcArgs = [], array $args = [] ){
		$format = $funcArgs[0] ?? '';
		return date( $format, strtotime( $value ) );
	}


}
