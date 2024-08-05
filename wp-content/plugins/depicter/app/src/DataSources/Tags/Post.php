<?php

namespace Depicter\DataSources\Tags;

use Averta\Core\Utility\Str;
use Averta\WordPress\Utility\Post as UtilityPost;

/**
 * Asset Group for WP posts
 *
 * {{{module->slug}}}
 * {{{module->slug|func('a','b')}}}
 *
 */
class Post extends TagBase implements TagInterface {

	/**
	 *  Asset group ID
	 */
	const ASSET_GROUP_ID = 'post';

	/**
	 * Get label of asset group
	 *
	 * @return string
	 */
	public function getName(){
		return __( "General", 'depicter' );
	}

	/**
	 * Get list of assets in this group
	 *
	 * @param array  $args
	 *
	 * @return array
	 */
	public function getAssetBlocks( array $args = [] ){

		return [
			[
				'id'    => 'title',
				'title' => __( 'Title', 'depicter' ),
				'previewOptions' => [
					"size" => 100,
					'multiline' => false,
					'textSize' => 'large',
					'badge' => null,
				],
				'type'  => 'dynamicText',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'post->title' )
				]
			],
			[
				'id'    => 'content',
				'title' => __( 'Content', 'depicter' ),
				'previewOptions' => [
					"size" => 100,
					'multiline' => true,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicText',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'post->content' )
				]
			],
			[
				'id'    => 'excerpt',
				'title' => __( 'Excerpt', 'depicter' ),
				'previewOptions' => [
					"size" => 100,
					'multiline' => true,
					'textSize' => 'regular',
					'badge' => null
				],
				'type'  => 'dynamicText',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'post->excerpt' )
				]
			],
			[
				'id'    => 'url',
				'title' => $args['postType'] === 'product' ? __( 'Product Link', 'depicter' ) : __( 'Post Link', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'variant' => 'button',
					'buttonText' => 'Read More',
					'badge' => null
				],
				'type'  => 'dynamicLink',
				'func'  => null,
				'payload' => [
					'url' => $this->wrapCurly( 'post->url' )
				]
			],
			[
				'id'    => 'featuredImage',
				'title' => $args['postType'] === 'product' ? __( 'Product Image', 'depicter' ) : __( 'Featured Image', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'badge' => null
				],
				'type'  => 'dynamicMedia',
				'func'  => null,
				'sourceType' => 'image',
				'payload' => [
					'source' => $this->wrapCurly( 'post->featuredImage' ),
					'src'    => $this->wrapCurly( 'post->featuredImage.src' ),
				]
			],
			[
				'id'    => 'date',
				'title' => __( 'Date', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'multiline' => false,
					'badge' => null
				],
				'type'  => 'dynamicDate',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'post->date' ),
				]
			],
			[
				'id'    => 'authorName',
				'title' => __( 'Author', 'depicter' ),
				'previewOptions' => [
					"size" => 50,
					'variant' => 'link',
					'buttonText' => __( 'Author', 'depicter' ),
					'badge' => null
				],
				'type'  => 'dynamicLink',
				'func'  => null,
				'payload' => [
					'source' => $this->wrapCurly( 'post->author.name' ),
					'url' => $this->wrapCurly('post->author.page' )
				]
			]
		];

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

		if( ! $post = get_post( $args['post'] ?? null ) ){
			return $tagName;
		}

		$result = $tagName;

		switch ( $tagName ) {
			case 'uuid':
			case 'id':
				$result = $post->ID;
				break;

			case 'url':
				$result = get_permalink( $post->ID );
				break;

			case 'title':
				$result = get_the_title( $post->ID );
				break;

			case 'featuredImage':
				$result = get_post_thumbnail_id( $post->ID );
				break;

			case 'featuredImage.src':
				$attachmentId = get_post_thumbnail_id( $post->ID );
				$result = $this->toSrc( $attachmentId );
				break;

			case 'date':
				$result = get_the_date('Y-m-d h:m:s', $post->ID );
				break;

			case 'excerpt':
				$result = !empty( $args['excerptLength'] ) ? UtilityPost::getExcerptTrimmedByChars( $post->ID, $args['excerptLength'] ) : UtilityPost::getExcerptTrimmedByChars( $post->ID );
				break;

			case 'author.name':
				$result = get_the_author_meta( 'display_name', $post->post_author );
				break;

			case 'author.page':
				$result = get_author_posts_url( $post->post_author );
				break;

			case 'content':
				$result = get_the_content(null, false, $post->ID );
				break;

			default:
				$result = null;
				break;
		}

		return $result;
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
	 * Trim string by max number of words
	 *
	 * @param mixed $value      The tag value to be piped
	 * @param array $funcArgs   Function args presented in dynamic tag
	 * @param array $args       Arguments of current document section
	 *
	 * @return mixed|string
	 */
	public function wTrim( $value, array $funcArgs = [], array $args = [] ){
		$maxlength = $funcArgs[0] ?? 0;
		return $maxlength ? Str::trimByWords( $value, $maxlength ) : $value;
	}

	/**
	 * Trim string by max number of characters
	 *
	 * @param mixed $value      The tag value to be piped
	 * @param array $funcArgs   Function args presented in dynamic tag
	 * @param array $args       Arguments of current document section
	 *
	 * @return mixed|string
	 */
	public function cTrim( $value, array $funcArgs = [], array $args = [] ){
		$maxlength = $funcArgs[0] ?? 0;
		return $maxlength ? Str::trimByChars( $value, $maxlength ) : $value;
	}

}
