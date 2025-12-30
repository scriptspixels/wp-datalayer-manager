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
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    
    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
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

