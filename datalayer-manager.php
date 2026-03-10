<?php
/**
 * Plugin Name: Scripts + Pixels DataLayer Manager
 * Description: Automatically detects WordPress context and injects dataLayer variables for analytics tools (GA4/GTM). Custom variables per page/post. No coding required.
 * Version: 1.0.0
 * Author: Scripts + Pixels
 * Author URI: https://scriptsandpixels.studio
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: scripts-and-pixels-datalayer-manager
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
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

// Free version flag. If license file is missing (e.g. WP.org zip), run as free version so we never require the missing file.
$datalayer_manager_license_file = plugin_dir_path( __FILE__ ) . 'includes/class-license-manager.php';
if ( ! defined( 'DATALAYER_MANAGER_FREE_VERSION' ) ) {
    define( 'DATALAYER_MANAGER_FREE_VERSION', ! file_exists( $datalayer_manager_license_file ) );
}

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
                /* translators: %s: Minimum required WordPress version. */
                esc_html__( 'Scripts + Pixels DataLayer Manager requires WordPress %s or higher. Please update WordPress.', 'scripts-and-pixels-datalayer-manager' ),
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
                /* translators: %1$s: Minimum required PHP version, %2$s: Current PHP version. */
                esc_html__( 'Scripts + Pixels DataLayer Manager requires PHP %1$s or higher. You are running PHP %2$s. Please contact your hosting provider to update PHP.', 'scripts-and-pixels-datalayer-manager' ),
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

// Only load license manager when not free version and file exists (Pro build).
if ( ! DATALAYER_MANAGER_FREE_VERSION && file_exists( $datalayer_manager_license_file ) ) {
    require_once $datalayer_manager_license_file;
}

// Custom variables (Pro only). Not included in WP.org build — no locked features in free plugin.
$datalayer_manager_custom_vars_file = DATALAYER_MANAGER_PLUGIN_DIR . 'includes/class-datalayer-manager-custom-variables.php';
if ( ! DATALAYER_MANAGER_FREE_VERSION && file_exists( $datalayer_manager_custom_vars_file ) ) {
    require_once $datalayer_manager_custom_vars_file;
}

require_once DATALAYER_MANAGER_PLUGIN_DIR . 'includes/class-datalayer-manager.php';

/**
 * Load plugin textdomain for translations.
 * Note: WordPress 4.6+ automatically loads plugin translations, so this is not needed.
 * Kept for backward compatibility with older WordPress versions (if needed).
 * For WordPress.org hosted plugins, translations are automatically loaded.
 */
// Removed load_plugin_textdomain() - WordPress 4.6+ handles this automatically.
// Translations will be automatically loaded by WordPress for plugins hosted on WordPress.org.

/**
 * Plugin activation handler.
 * Grants capabilities to roles.
 */
function datalayer_manager_activate() {
    // Check requirements on activation.
    if ( ! datalayer_manager_check_requirements() ) {
        deactivate_plugins( plugin_basename( DATALAYER_MANAGER_PLUGIN_FILE ) );
        wp_die(
            esc_html__( 'Scripts + Pixels DataLayer Manager could not be activated. Please check the requirements.', 'scripts-and-pixels-datalayer-manager' ),
            esc_html__( 'Plugin Activation Error', 'scripts-and-pixels-datalayer-manager' ),
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


/**
 * Add plugin row meta links.
 *
 * @param array  $links Existing row meta links.
 * @param string $file  Plugin file.
 * @return array Modified row meta links.
 */
function datalayer_manager_plugin_row_meta( $links, $file ) {
    // Only add links for this plugin.
    if ( plugin_basename( DATALAYER_MANAGER_PLUGIN_FILE ) !== $file ) {
        return $links;
    }

    // WP.org (free) version only: add Overview link at the end of the description row.
    if ( DATALAYER_MANAGER_FREE_VERSION ) {
        $overview_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'options-general.php?page=scripts-and-pixels-datalayer-manager' ) ),
            esc_html__( 'Overview', 'scripts-and-pixels-datalayer-manager' )
        );
        $links[] = $overview_link;
    }

    // Only show "Activate License" link if not free version and license is not active.
    if ( ! DATALAYER_MANAGER_FREE_VERSION && ! datalayer_manager_is_premium() ) {
        $license_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'options-general.php?page=scripts-and-pixels-datalayer-manager&screen=license' ) ),
            esc_html__( 'Activate License', 'scripts-and-pixels-datalayer-manager' )
        );
        $links[] = $license_link;
    }

    return $links;
}
add_filter( 'plugin_row_meta', 'datalayer_manager_plugin_row_meta', 10, 2 );

/**
 * Check if premium features are active.
 *
 * @return bool True if premium license is valid, false otherwise.
 */
function datalayer_manager_is_premium() {
    // Free version always returns false.
    if ( DATALAYER_MANAGER_FREE_VERSION ) {
        return false;
    }
    
    // Check if license manager class exists (may not be loaded in free version).
    if ( ! class_exists( 'DataLayer_Manager_License' ) ) {
        return false;
    }
    
    static $license_manager = null;
    
    if ( null === $license_manager ) {
        $license_manager = new DataLayer_Manager_License();
    }
    
    return $license_manager->is_license_valid();
}

