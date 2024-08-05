<?php
/**
 * Declare any actions and filters here.
 * In most cases you should use a service provider, but in cases where you
 * just need to add an action/filter and forget about it you can add it here.
 *
 * @package Depicter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore
// add_action( 'some_action', 'some_function' );
function depicter_add_thumbnail_size() {
	add_image_size( 'depicter-thumbnail', 200, 9999, false );
}
add_action( 'after_setup_theme', 'depicter_add_thumbnail_size' );


/**
 * Make assets and cache for slider after publishing the changes
 *
 * @param $documentID
 * @param $properties
 *
 * @return void
 */
function depicter_make_document_cache( $documentID, $properties ) {
	if ( $properties['status'] === 'publish' ) {
		\Depicter::front()->render()->flushDocumentCache( $documentID );
		\Depicter::front()->render()->document( $documentID, [ 'echo' => false ] );
	}
}
add_action( 'depicter/editor/after/store', 'depicter_make_document_cache', 10, 2 );


/**
 * Remove document cache after document was deleted
 *
 * @param $documentID
 *
 * @return void
 */
function depicter_purge_document_cache( $documentID ) {
	\Depicter::front()->render()->flushDocumentCache( $documentID );
}
add_action( 'depicter/editor/after/delete', 'depicter_purge_document_cache', 10 );


/**
 * Depicter sanitize html tags for depicter slider output
 *
 * @param array $allowed_tags
 * @return void
 */
function depicter_sanitize_html_tags_for_output( $allowed_tags ) {
	return array_merge_recursive( $allowed_tags, wp_kses_allowed_html( 'post' ) );
}
add_filter( 'averta/wordpress/sanitize/html/tags/depicter/output', 'depicter_sanitize_html_tags_for_output' );


function depicter_disable_nocache_headers( $headers ) {
	unset( $headers['Expires'] );
	unset( $headers['Cache-Control'] );
	return $headers;
}


/**
 * Set Svg Meta Data
 *
 * Adds dimensions metadata to uploaded SVG files, since WordPress doesn't do it.
 *
 * @param array $data
 * @param int $id
 * @return array $data
 */
function depicter_set_svg_meta_data( $data, $id ) {
	// If the attachment is an svg
	if ( 'image/svg+xml' === get_post_mime_type( $id ) ) {
		// If the svg metadata are empty or the width is empty or the height is empty.
		// then get the attributes from xml.
		if ( empty( $data ) || empty( $data['width'] ) || empty( $data['height'] ) ) {
			$attachment = get_the_guid( $id );
			$xml = simplexml_load_file( $attachment );

			if ( ! empty( $xml ) ) {
				$attr = $xml->attributes();
				$view_box = explode( ' ', $attr->viewBox );// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$data['width'] = isset( $attr->width ) && preg_match( '/\d+/', $attr->width, $value ) ? (int) $value[0] : ( 4 === count( $view_box ) ? (int) $view_box[2] : null );
				$data['height'] = isset( $attr->height ) && preg_match( '/\d+/', $attr->height, $value ) ? (int) $value[0] : ( 4 === count( $view_box ) ? (int) $view_box[3] : null );
			}
		}
	}

	return $data;
}
add_filter( 'wp_update_attachment_metadata', 'depicter_set_svg_meta_data', 10, 2 );

function depicter_clear_cache() {
	\Depicter::cache('document')->clear();
}

function depicter_clear_cache_by_cache_enabler() {
	$cacheEnabled =  !empty( $_GET['_cache'] ) && $_GET['_cache'] == 'cache-enabler' ? true : false;
	$isClearAction = !empty( $_GET['_action'] ) && ( $_GET['_action'] == 'clear' || $_GET['_action'] == 'clearurl' );
	if ( $cacheEnabled && $isClearAction && !empty( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'cache_enabler_clear_cache_nonce' ) ) {
		depicter_clear_cache();
	}
}
add_action( 'init', 'depicter_clear_cache_by_cache_enabler' );
add_action( 'post_updated', 'depicter_clear_cache' );

add_filter( 'style_loader_tag',  'depicter_add_preload_to_styles', 10, 2 );
function depicter_add_preload_to_styles( $html, $handle ){
	// skip if resource preloading option was disabled
	if( \Depicter::options()->get('resource_preloading' ) === 'off' ){
		return $html;
	}
    if( strpos( $handle, 'depicter--') === 0 ){
        $html = str_replace("rel='stylesheet'", 'rel="preload" as="style" onload="this.rel=\'stylesheet\';this.onload=null"', $html);
    }
    return $html;
}

function depicter_add_defer_to_scripts( $tag, $handle ) {
	if( strpos( $handle, 'depicter--') === 0 ){
        $tag = str_replace(' src=', ' defer src=', $tag );
    }
    return $tag;
}
add_filter( 'script_loader_tag', 'depicter_add_defer_to_scripts', 15, 2 );

/**
 * Check if imported media deleted then remove its ID from imported assets dictionary
 *
 * @param int $attachment_id
 * @return void
 */
function depicter_check_deleted_imported_media( $attachment_id ) {
	\Depicter::media()->maybeRemoveAttachmentFromDictionary( $attachment_id );
}
add_action( 'delete_attachment', 'depicter_check_deleted_imported_media');

function depicter_check_activation() {
	if ( isset( $_GET['depicter_upgraded'] ) || ( isset( $_GET['page'] ) && $_GET['page'] == 'depicter-dashboard' ) || ( isset( $_GET['action'] ) && $_GET['action'] == 'depicter' ) ) {
		if ( \Depicter::client()->validateActivation() ) {
			\Depicter::client()->getRefreshToken( true );
		}
	}
}
add_action( 'admin_init', 'depicter_check_activation' );

/**
 * Renew expired tokens
 *
 * @return void
 */
function depicter_renew_tokens() {
	if ( false === \Depicter::cache('base')->get( 'access_token' ) ) {
		\Depicter\Services\UserAPIService::renewTokens();
	}
}
add_action( 'admin_init', 'depicter_renew_tokens' );

