<?php
/**
 * Uninstall plugin
 */

// If uninstall not called from WordPress exit
defined( 'WP_UNINSTALL_PLUGIN' ) or die( 'Keep Silent' );

global $wpdb;

// change to select type when uninstall
$table_name = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
$query      = $wpdb->query( "UPDATE `$table_name` SET `attribute_type` = 'select' WHERE `attribute_type` != 'text'" );
$wpdb->query( $query );

// Remove Option
delete_option( 'woo_variation_swatches' );
// Site options in Multisite
delete_site_option( 'woo_variation_swatches' );

// Clear any cached data that has been removed.
wp_cache_flush();
	