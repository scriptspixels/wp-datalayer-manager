<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package DataLayer_Manager
 */

// Exit if uninstall not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin options.
delete_option( 'datalayer_manager_license' );
delete_option( 'datalayer_manager_license_status' );

// Delete plugin options for multisite (if applicable).
if ( is_multisite() ) {
    // Get all sites in the network.
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable, not global.
    $datalayer_sites = get_sites( array( 'fields' => 'ids' ) );
    
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable in foreach loop, not global.
    foreach ( $datalayer_sites as $datalayer_site_id ) {
        switch_to_blog( $datalayer_site_id );
        delete_option( 'datalayer_manager_license' );
        delete_option( 'datalayer_manager_license_status' );
        restore_current_blog();
    }
}

// Note: We intentionally do NOT delete custom post meta (_datalayer_manager_custom_variables)
// as users may want to keep their custom variables if they reinstall the plugin.
// If you want to delete them, uncomment the following:
/*
global $wpdb;
$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_datalayer_manager_custom_variables'" );
*/

