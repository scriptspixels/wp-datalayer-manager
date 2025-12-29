<?php
/**
 * Capabilities management for DataLayer Manager.
 *
 * @package DataLayer_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Capabilities class for managing permissions.
 */
class DataLayer_Manager_Capabilities {

    /**
     * Capability: View DataLayer Manager screens.
     */
    const CAP_VIEW = 'ga_view';

    /**
     * Capability: Create and edit drafts.
     */
    const CAP_EDIT_DRAFT = 'ga_edit_draft';

    /**
     * Capability: Publish and rollback configurations.
     */
    const CAP_PUBLISH = 'ga_publish';

    /**
     * Grant capabilities to roles on plugin activation.
     * Multisite-safe: grants capabilities per site.
     */
    public static function grant_capabilities() {
        // Administrators: all capabilities.
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $admin_role->add_cap( self::CAP_VIEW );
            $admin_role->add_cap( self::CAP_EDIT_DRAFT );
            $admin_role->add_cap( self::CAP_PUBLISH );
        }

        // Editors: view and edit draft (no publish).
        $editor_role = get_role( 'editor' );
        if ( $editor_role ) {
            $editor_role->add_cap( self::CAP_VIEW );
            $editor_role->add_cap( self::CAP_EDIT_DRAFT );
        }

        // Authors and Subscribers: view only (transparency/safety).
        // This allows them to see what's published but prevents any modifications.
        $author_role = get_role( 'author' );
        if ( $author_role ) {
            $author_role->add_cap( self::CAP_VIEW );
        }

        $subscriber_role = get_role( 'subscriber' );
        if ( $subscriber_role ) {
            $subscriber_role->add_cap( self::CAP_VIEW );
        }
    }

    /**
     * Remove capabilities from roles on plugin deactivation.
     * Multisite-safe: removes capabilities per site.
     */
    public static function remove_capabilities() {
        $roles = array( 'administrator', 'editor', 'author', 'subscriber' );
        
        foreach ( $roles as $role_name ) {
            $role = get_role( $role_name );
            if ( $role ) {
                $role->remove_cap( self::CAP_VIEW );
                $role->remove_cap( self::CAP_EDIT_DRAFT );
                $role->remove_cap( self::CAP_PUBLISH );
            }
        }
    }

    /**
     * Check if current user can view DataLayer Manager.
     *
     * @return bool True if user can view.
     */
    public static function current_user_can_view() {
        return current_user_can( self::CAP_VIEW );
    }

    /**
     * Check if current user can edit drafts.
     *
     * @return bool True if user can edit drafts.
     */
    public static function current_user_can_edit_draft() {
        return current_user_can( self::CAP_EDIT_DRAFT );
    }

    /**
     * Check if current user can publish.
     *
     * @return bool True if user can publish.
     */
    public static function current_user_can_publish() {
        return current_user_can( self::CAP_PUBLISH );
    }

    /**
     * Check if a specific user can view.
     *
     * @param int $user_id User ID.
     * @return bool True if user can view.
     */
    public static function user_can_view( $user_id ) {
        $user = get_userdata( $user_id );
        return $user && $user->has_cap( self::CAP_VIEW );
    }

    /**
     * Check if a specific user can edit drafts.
     *
     * @param int $user_id User ID.
     * @return bool True if user can edit drafts.
     */
    public static function user_can_edit_draft( $user_id ) {
        $user = get_userdata( $user_id );
        return $user && $user->has_cap( self::CAP_EDIT_DRAFT );
    }

    /**
     * Check if a specific user can publish.
     *
     * @param int $user_id User ID.
     * @return bool True if user can publish.
     */
    public static function user_can_publish( $user_id ) {
        $user = get_userdata( $user_id );
        return $user && $user->has_cap( self::CAP_PUBLISH );
    }
}

