<?php
/**
 * License Manager Class
 *
 * Handles license key validation, activation, deactivation, and status management.
 *
 * @package DataLayer_Manager
 * @since 1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * License Manager Class
 */
class DataLayer_Manager_License {

    /**
     * License option name.
     *
     * @var string
     */
    private $option_name = 'datalayer_manager_license';

    /**
     * License status option name.
     *
     * @var string
     */
    private $status_option_name = 'datalayer_manager_license_status';

    /**
     * License API endpoint (placeholder - replace with your actual endpoint).
     *
     * @var string
     */
    private $api_url = 'https://your-license-server.com/api/';

    /**
     * Product ID/name for license validation.
     *
     * @var string
     */
    private $product_id = 'datalayer-manager-premium';

    /**
     * Test mode flag.
     * Set to true to enable test mode (bypasses API calls).
     *
     * @var bool
     */
    private $test_mode = false;

    /**
     * Test license key (for testing purposes only).
     *
     * @var string
     */
    private $test_license_key = 'TEST-LICENSE-KEY-12345';

    /**
     * Cache duration in seconds (24 hours).
     *
     * @var int
     */
    private $cache_duration = 86400;

    /**
     * Initialize license manager.
     */
    public function __construct() {
        // Hook into WordPress.
        add_action( 'admin_init', array( $this, 'handle_license_action' ) );
        add_action( 'admin_notices', array( $this, 'show_license_notices' ) );
    }

    /**
     * Get license key.
     *
     * @return string License key or empty string.
     */
    public function get_license_key() {
        $license_data = get_option( $this->option_name, array() );
        return isset( $license_data['key'] ) ? $license_data['key'] : '';
    }

    /**
     * Get license status.
     *
     * @param bool $force_check Force check with API (bypass cache).
     * @return string License status: 'valid', 'invalid', 'expired', 'inactive', or 'none'.
     */
    public function get_license_status( $force_check = false ) {
        // Check cache first (unless forcing check).
        if ( ! $force_check ) {
            $cached_status = $this->get_cached_status();
            if ( false !== $cached_status ) {
                return $cached_status;
            }
        }

        $license_key = $this->get_license_key();

        // No license key means no license.
        if ( empty( $license_key ) ) {
            return 'none';
        }

        // Validate with API.
        $status = $this->validate_license( $license_key );

        // Cache the status.
        $this->cache_status( $status );

        return $status;
    }

    /**
     * Check if license is valid and active.
     *
     * @return bool True if license is valid, false otherwise.
     */
    public function is_license_valid() {
        $status = $this->get_license_status();
        return 'valid' === $status;
    }

    /**
     * Validate license key with API.
     *
     * @param string $license_key License key to validate.
     * @return string License status.
     */
    private function validate_license( $license_key ) {
        // Test mode: Simulate license validation.
        if ( $this->is_test_mode() ) {
            return $this->validate_test_license( $license_key );
        }

        // Prepare API request.
        $api_params = array(
            'edd_action' => 'check_license',
            'license'    => $license_key,
            'item_name'  => urlencode( $this->product_id ),
            'url'        => home_url(),
        );

        // Make API request.
        $response = wp_remote_post(
            $this->api_url,
            array(
                'timeout'   => 15,
                'sslverify' => true,
                'body'      => $api_params,
            )
        );

        // Check for errors.
        if ( is_wp_error( $response ) ) {
            // On error, return cached status or 'none'.
            $cached = $this->get_cached_status();
            return false !== $cached ? $cached : 'none';
        }

        // Parse response.
        $license_data = json_decode( wp_remote_retrieve_body( $response ), true );

        // Check if response is valid.
        if ( ! isset( $license_data['license'] ) ) {
            return 'none';
        }

        // Return status.
        return $license_data['license'];
    }

    /**
     * Validate test license (for testing purposes only).
     *
     * @param string $license_key License key to validate.
     * @return string License status.
     */
    private function validate_test_license( $license_key ) {
        // Accept test license key.
        if ( $this->test_license_key === $license_key ) {
            return 'valid';
        }

        // Reject invalid test keys.
        return 'invalid';
    }

    /**
     * Check if test mode is enabled.
     *
     * @return bool True if test mode is enabled.
     */
    private function is_test_mode() {
        // Check for test mode constant (can be set in wp-config.php).
        if ( defined( 'DATALAYER_MANAGER_TEST_MODE' ) && DATALAYER_MANAGER_TEST_MODE ) {
            return true;
        }

        // Check for test mode option (can be set via filter).
        return apply_filters( 'datalayer_manager_license_test_mode', $this->test_mode );
    }

    /**
     * Activate license.
     *
     * @param string $license_key License key to activate.
     * @return array Result with 'success' and 'message' keys.
     */
    public function activate_license( $license_key ) {
        // Sanitize license key.
        $license_key = sanitize_text_field( trim( $license_key ) );

        if ( empty( $license_key ) ) {
            return array(
                'success' => false,
                'message' => __( 'License key is required.', 'datalayer-manager' ),
            );
        }

        // Test mode: Simulate license activation.
        if ( $this->is_test_mode() ) {
            return $this->activate_test_license( $license_key );
        }

        // Prepare API request.
        $api_params = array(
            'edd_action' => 'activate_license',
            'license'    => $license_key,
            'item_name'  => urlencode( $this->product_id ),
            'url'        => home_url(),
        );

        // Make API request.
        $response = wp_remote_post(
            $this->api_url,
            array(
                'timeout'   => 15,
                'sslverify' => true,
                'body'      => $api_params,
            )
        );

        // Check for errors.
        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => __( 'Error connecting to license server. Please try again later.', 'datalayer-manager' ),
            );
        }

        // Parse response.
        $license_data = json_decode( wp_remote_retrieve_body( $response ), true );

        // Check if activation was successful.
        if ( isset( $license_data['license'] ) && 'valid' === $license_data['license'] ) {
            // Save license key.
            update_option(
                $this->option_name,
                array(
                    'key'      => $license_key,
                    'activated' => time(),
                )
            );

            // Cache status.
            $this->cache_status( 'valid' );

            return array(
                'success' => true,
                'message' => __( 'License activated successfully!', 'datalayer-manager' ),
            );
        } else {
            // Get error message.
            $error_message = isset( $license_data['error'] ) ? $license_data['error'] : __( 'Unknown error occurred.', 'datalayer-manager' );

            // Map error codes to user-friendly messages.
            $error_messages = array(
                'expired'     => __( 'Your license key has expired.', 'datalayer-manager' ),
                'revoked'     => __( 'Your license key has been revoked.', 'datalayer-manager' ),
                'missing'     => __( 'Invalid license key.', 'datalayer-manager' ),
                'invalid'     => __( 'Invalid license key.', 'datalayer-manager' ),
                'site_inactive' => __( 'License is not active for this site.', 'datalayer-manager' ),
                'item_name_mismatch' => __( 'License key does not match this product.', 'datalayer-manager' ),
                'no_activations_left' => __( 'No activations left for this license key.', 'datalayer-manager' ),
            );

            if ( isset( $error_messages[ $error_message ] ) ) {
                $error_message = $error_messages[ $error_message ];
            }

            return array(
                'success' => false,
                'message' => $error_message,
            );
        }
    }

    /**
     * Activate test license (for testing purposes only).
     *
     * @param string $license_key License key to activate.
     * @return array Result with 'success' and 'message' keys.
     */
    private function activate_test_license( $license_key ) {
        // Accept test license key.
        if ( $this->test_license_key === $license_key ) {
            // Save license key.
            update_option(
                $this->option_name,
                array(
                    'key'       => $license_key,
                    'activated' => time(),
                )
            );

            // Cache status.
            $this->cache_status( 'valid' );

            return array(
                'success' => true,
                'message' => __( 'Test license activated successfully! (Test Mode)', 'datalayer-manager' ),
            );
        }

        // Reject invalid test keys.
        return array(
            'success' => false,
            'message' => __( 'Invalid test license key. Use: TEST-LICENSE-KEY-12345', 'datalayer-manager' ),
        );
    }

    /**
     * Deactivate license.
     *
     * @return array Result with 'success' and 'message' keys.
     */
    public function deactivate_license() {
        $license_key = $this->get_license_key();

        if ( empty( $license_key ) ) {
            return array(
                'success' => false,
                'message' => __( 'No license key found.', 'datalayer-manager' ),
            );
        }

        // Prepare API request.
        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license'    => $license_key,
            'item_name'  => urlencode( $this->product_id ),
            'url'        => home_url(),
        );

        // Make API request.
        $response = wp_remote_post(
            $this->api_url,
            array(
                'timeout'   => 15,
                'sslverify' => true,
                'body'      => $api_params,
            )
        );

        // Check for errors (but still deactivate locally).
        if ( is_wp_error( $response ) ) {
            // Still remove license locally.
            delete_option( $this->option_name );
            delete_option( $this->status_option_name );

            return array(
                'success' => true,
                'message' => __( 'License deactivated locally. (Could not reach license server.)', 'datalayer-manager' ),
            );
        }

        // Remove license locally.
        delete_option( $this->option_name );
        delete_option( $this->status_option_name );

        return array(
            'success' => true,
            'message' => __( 'License deactivated successfully.', 'datalayer-manager' ),
        );
    }

    /**
     * Get cached license status.
     *
     * @return string|false License status or false if not cached/expired.
     */
    private function get_cached_status() {
        $cached = get_option( $this->status_option_name, false );

        if ( false === $cached ) {
            return false;
        }

        // Check if cache is expired.
        if ( isset( $cached['timestamp'] ) && ( time() - $cached['timestamp'] ) > $this->cache_duration ) {
            return false;
        }

        return isset( $cached['status'] ) ? $cached['status'] : false;
    }

    /**
     * Cache license status.
     *
     * @param string $status License status to cache.
     */
    private function cache_status( $status ) {
        update_option(
            $this->status_option_name,
            array(
                'status'    => $status,
                'timestamp' => time(),
            )
        );
    }

    /**
     * Handle license actions (activate/deactivate).
     */
    public function handle_license_action() {
        // Check if user has permission.
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Check nonce.
        if ( ! isset( $_POST['datalayer_manager_license_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['datalayer_manager_license_nonce'] ) ), 'datalayer_manager_license_action' ) ) {
            return;
        }

        // Handle activate action.
        if ( isset( $_POST['datalayer_manager_activate_license'] ) ) {
            $license_key = isset( $_POST['datalayer_manager_license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['datalayer_manager_license_key'] ) ) : '';
            $result = $this->activate_license( $license_key );

            // Store result in transient for display.
            set_transient( 'datalayer_manager_license_notice', $result, 30 );

            // Redirect to prevent resubmission.
            wp_safe_redirect( add_query_arg( 'datalayer_license_action', 'activate', admin_url( 'options-general.php?page=datalayer-manager&screen=license' ) ) );
            exit;
        }

        // Handle deactivate action.
        if ( isset( $_POST['datalayer_manager_deactivate_license'] ) ) {
            $result = $this->deactivate_license();

            // Store result in transient for display.
            set_transient( 'datalayer_manager_license_notice', $result, 30 );

            // Redirect to prevent resubmission.
            wp_safe_redirect( add_query_arg( 'datalayer_license_action', 'deactivate', admin_url( 'options-general.php?page=datalayer-manager&screen=license' ) ) );
            exit;
        }
    }

    /**
     * Show license notices.
     */
    public function show_license_notices() {
        // Only show on DataLayer Manager page with license screen.
        if ( ! isset( $_GET['page'] ) || 'datalayer-manager' !== $_GET['page'] ) {
            return;
        }
        
        if ( ! isset( $_GET['screen'] ) || 'license' !== $_GET['screen'] ) {
            return;
        }

        // Get notice from transient.
        $notice = get_transient( 'datalayer_manager_license_notice' );

        if ( false === $notice ) {
            return;
        }

        // Delete transient.
        delete_transient( 'datalayer_manager_license_notice' );

        // Display notice.
        $class = $notice['success'] ? 'notice-success' : 'notice-error';
        ?>
        <div class="notice <?php echo esc_attr( $class ); ?> is-dismissible">
            <p><?php echo esc_html( $notice['message'] ); ?></p>
        </div>
        <?php
    }

    /**
     * Set API URL (for testing or custom endpoints).
     *
     * @param string $url API URL.
     */
    public function set_api_url( $url ) {
        $this->api_url = $url;
    }

    /**
     * Set product ID.
     *
     * @param string $product_id Product ID.
     */
    public function set_product_id( $product_id ) {
        $this->product_id = $product_id;
    }

    /**
     * Enable test mode (for testing purposes only).
     */
    public function enable_test_mode() {
        $this->test_mode = true;
    }

    /**
     * Disable test mode.
     */
    public function disable_test_mode() {
        $this->test_mode = false;
    }
}

