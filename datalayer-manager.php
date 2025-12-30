<?php
/**
 * Plugin Name: DataLayer Manager
 * Plugin URI: https://scriptsandpixels.studio
 * Description: Automatically detects WordPress context and injects dataLayer variables for analytics tools (e.g., GA4/GTM). No configuration needed - works automatically.
 * Version: 1.0.0
 * Author: Scripts + Pixels
 * Author URI: https://scriptsandpixels.studio
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: datalayer-manager
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Network: false
 *
 * @package DataLayer_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'DATALAYER_MANAGER_VERSION', '1.0.0' );
define( 'DATALAYER_MANAGER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DATALAYER_MANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DATALAYER_MANAGER_PLUGIN_FILE', __FILE__ );
define( 'DATALAYER_MANAGER_MIN_WP_VERSION', '5.0' );
define( 'DATALAYER_MANAGER_MIN_PHP_VERSION', '7.4' );

/**
 * Check minimum requirements before loading plugin.
 */
function datalayer_manager_check_requirements() {
    global $wp_version;

    // Check WordPress version.
    if ( version_compare( $wp_version, DATALAYER_MANAGER_MIN_WP_VERSION, '<' ) ) {
        add_action( 'admin_notices', 'datalayer_manager_wp_version_notice' );
        return false;
    }

    // Check PHP version.
    if ( version_compare( PHP_VERSION, DATALAYER_MANAGER_MIN_PHP_VERSION, '<' ) ) {
        add_action( 'admin_notices', 'datalayer_manager_php_version_notice' );
        return false;
    }

    return true;
}

/**
 * Display WordPress version notice.
 */
function datalayer_manager_wp_version_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            printf(
                esc_html__( 'DataLayer Manager requires WordPress %s or higher. Please update WordPress.', 'datalayer-manager' ),
                esc_html( DATALAYER_MANAGER_MIN_WP_VERSION )
            );
            ?>
        </p>
    </div>
    <?php
}

/**
 * Display PHP version notice.
 */
function datalayer_manager_php_version_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            printf(
                esc_html__( 'DataLayer Manager requires PHP %s or higher. You are running PHP %s. Please contact your hosting provider to update PHP.', 'datalayer-manager' ),
                esc_html( DATALAYER_MANAGER_MIN_PHP_VERSION ),
                esc_html( PHP_VERSION )
            );
            ?>
        </p>
    </div>
    <?php
}

// Check requirements before loading.
if ( ! datalayer_manager_check_requirements() ) {
    return;
}

// Load plugin classes.
require_once DATALAYER_MANAGER_PLUGIN_DIR . 'includes/class-capabilities.php';
require_once DATALAYER_MANAGER_PLUGIN_DIR . 'includes/class-datalayer-manager.php';

/**
 * Load plugin textdomain for translations.
 */
function datalayer_manager_load_textdomain() {
    load_plugin_textdomain(
        'datalayer-manager',
        false,
        dirname( plugin_basename( DATALAYER_MANAGER_PLUGIN_FILE ) ) . '/languages'
    );
}
add_action( 'plugins_loaded', 'datalayer_manager_load_textdomain' );

/**
 * Plugin activation handler.
 * Grants capabilities to roles.
 */
function datalayer_manager_activate() {
    // Check requirements on activation.
    if ( ! datalayer_manager_check_requirements() ) {
        deactivate_plugins( plugin_basename( DATALAYER_MANAGER_PLUGIN_FILE ) );
        wp_die(
            esc_html__( 'DataLayer Manager could not be activated. Please check the requirements.', 'datalayer-manager' ),
            esc_html__( 'Plugin Activation Error', 'datalayer-manager' ),
            array( 'back_link' => true )
        );
    }

    DataLayer_Manager_Capabilities::grant_capabilities();

    // Allow other plugins to hook into activation.
    do_action( 'datalayer_manager_activated' );
}
register_activation_hook( DATALAYER_MANAGER_PLUGIN_FILE, 'datalayer_manager_activate' );

/**
 * Plugin deactivation handler.
 * Removes capabilities from roles.
 */
function datalayer_manager_deactivate() {
    DataLayer_Manager_Capabilities::remove_capabilities();

    // Allow other plugins to hook into deactivation.
    do_action( 'datalayer_manager_deactivated' );
}
register_deactivation_hook( DATALAYER_MANAGER_PLUGIN_FILE, 'datalayer_manager_deactivate' );

/**
 * Initialize the plugin.
 */
function datalayer_manager_init() {
    // Double-check requirements.
    if ( ! datalayer_manager_check_requirements() ) {
        return;
    }

    $plugin = new DataLayer_Manager();
    $plugin->init();

    // Allow other plugins to hook into initialization.
    do_action( 'datalayer_manager_init', $plugin );
}
add_action( 'plugins_loaded', 'datalayer_manager_init', 10 );

