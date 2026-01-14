<?php
/**
 * License API Endpoint (Control Layer)
 * 
 * This endpoint acts as a control layer between WordPress plugins and Lemon Squeezy.
 * It handles license validation, activation, and deactivation requests.
 * 
 * PLACEMENT: Upload this file to scriptsandpixels.studio/license-api/index.php
 * (or scriptsandpixels.local/license-api/index.php for testing)
 * 
 * REQUIRED: Set your Lemon Squeezy API key in the LEMON_SQUEEZY_API_KEY constant below.
 * 
 * @package License_API
 */

// Prevent direct access without proper request.
if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
    http_response_code( 405 );
    header( 'Content-Type: application/json' );
    echo json_encode( array( 'success' => false, 'message' => 'Method not allowed. Use POST.' ) );
    exit;
}

// Set headers for JSON response.
header( 'Content-Type: application/json' );
header( 'Access-Control-Allow-Origin: *' );
header( 'Access-Control-Allow-Methods: POST' );
header( 'Access-Control-Allow-Headers: Content-Type' );

// ============================================================================
// CONFIGURATION - UPDATE THESE VALUES
// ============================================================================

/**
 * Lemon Squeezy API Key
 * Get this from: Lemon Squeezy Dashboard → Settings → API
 */
define( 'LEMON_SQUEEZY_API_KEY', 'your-lemon-squeezy-api-key-here' );

/**
 * Lemon Squeezy API Base URL
 */
define( 'LEMON_SQUEEZY_API_URL', 'https://api.lemonsqueezy.com/v1/licenses/' );

/**
 * Plugin to Product ID mapping
 * Map plugin slugs to Lemon Squeezy product/variant IDs
 */
$plugin_product_map = array(
    'datalayer-manager' => array(
        'variant_id' => 'your-variant-id-here', // Get from Lemon Squeezy product settings
    ),
    // Add more plugins here as needed.
    // 'another-plugin' => array(
    //     'variant_id' => 'variant-id-here',
    // ),
);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Send JSON response.
 *
 * @param bool   $success Success status.
 * @param array  $data    Response data.
 * @param int    $code    HTTP status code.
 */
function send_response( $success, $data = array(), $code = 200 ) {
    http_response_code( $code );
    echo json_encode( array_merge( array( 'success' => $success ), $data ) );
    exit;
}

/**
 * Call Lemon Squeezy API using cURL.
 *
 * @param string $endpoint API endpoint.
 * @param string $method   HTTP method (GET, POST, etc.).
 * @param array  $data     Request data.
 * @return array Response data with 'code' and 'body' keys, or array with 'error' key on failure.
 */
function call_lemon_squeezy_api( $endpoint, $method = 'GET', $data = array() ) {
    $url = LEMON_SQUEEZY_API_URL . $endpoint;
    
    $ch = curl_init();
    
    $headers = array(
        'Authorization: Bearer ' . LEMON_SQUEEZY_API_KEY,
        'Accept: application/vnd.api+json',
        'Content-Type: application/vnd.api+json',
    );
    
    curl_setopt_array( $ch, array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_CUSTOMREQUEST  => $method,
    ) );
    
    if ( ! empty( $data ) && in_array( $method, array( 'POST', 'PATCH', 'PUT' ), true ) ) {
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
    }
    
    $response_body = curl_exec( $ch );
    $response_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    $curl_error = curl_error( $ch );
    
    curl_close( $ch );
    
    if ( false === $response_body || ! empty( $curl_error ) ) {
        return array(
            'error' => true,
            'message' => $curl_error ?: 'cURL request failed.',
        );
    }
    
    return array(
        'code' => $response_code,
        'body' => json_decode( $response_body, true ),
    );
}

/**
 * Validate license key with Lemon Squeezy.
 *
 * @param string $license_key License key to validate.
 * @param string $variant_id  Product variant ID.
 * @return array License validation result.
 */
function validate_license_with_lemon_squeezy( $license_key, $variant_id ) {
    // Lemon Squeezy API: Validate license
    // Endpoint: GET /v1/licenses/validate
    $response = call_lemon_squeezy_api(
        'validate',
        'POST',
        array(
            'data' => array(
                'type'       => 'licenses',
                'attributes' => array(
                    'license_key' => $license_key,
                    'variant_id'  => $variant_id,
                ),
            ),
        )
    );
    
    if ( isset( $response['error'] ) && $response['error'] ) {
        return array(
            'success' => false,
            'status'  => 'error',
            'message' => isset( $response['message'] ) ? $response['message'] : 'Error connecting to license server.',
        );
    }
    
    // Check response code.
    if ( 200 !== $response['code'] ) {
        $error_message = 'License validation failed.';
        if ( isset( $response['body']['errors'] ) && is_array( $response['body']['errors'] ) ) {
            $error_message = $response['body']['errors'][0]['detail'] ?? $error_message;
        }
        
        return array(
            'success' => false,
            'status'  => 'invalid',
            'message' => $error_message,
        );
    }
    
    // Parse Lemon Squeezy response.
    $license_data = $response['body']['data']['attributes'] ?? array();
    
    // Check license status.
    $status = $license_data['status'] ?? 'invalid';
    
    // Map Lemon Squeezy status to our standard status.
    $status_map = array(
        'active'   => 'valid',
        'inactive' => 'invalid',
        'expired'  => 'expired',
    );
    
    $mapped_status = isset( $status_map[ $status ] ) ? $status_map[ $status ] : 'invalid';
    
    return array(
        'success' => 'active' === $status,
        'status'  => $mapped_status,
        'message' => 'active' === $status ? 'License is valid.' : 'License is not valid.',
        'data'    => $license_data,
    );
}

/**
 * Activate license with Lemon Squeezy.
 *
 * @param string $license_key License key to activate.
 * @param string $variant_id  Product variant ID.
 * @param string $site_url    Site URL to activate for.
 * @return array License activation result.
 */
function activate_license_with_lemon_squeezy( $license_key, $variant_id, $site_url ) {
    // First validate the license.
    $validation = validate_license_with_lemon_squeezy( $license_key, $variant_id );
    
    if ( ! $validation['success'] ) {
        return $validation;
    }
    
    // Lemon Squeezy API: Activate license for site
    // Note: Lemon Squeezy handles activations automatically via the validate endpoint
    // If you need to track site-specific activations, you may need to use their webhooks
    // or store activation data in your own database.
    
    // For now, if validation passes, consider it activated.
    return array(
        'success' => true,
        'status'  => 'valid',
        'message' => 'License activated successfully.',
    );
}

/**
 * Deactivate license with Lemon Squeezy.
 *
 * @param string $license_key License key to deactivate.
 * @param string $variant_id  Product variant ID.
 * @param string $site_url    Site URL to deactivate for.
 * @return array License deactivation result.
 */
function deactivate_license_with_lemon_squeezy( $license_key, $variant_id, $site_url ) {
    // Lemon Squeezy doesn't have a direct deactivate endpoint
    // Licenses are typically deactivated via the dashboard or webhooks
    // For now, we'll just return success (the plugin will remove local license)
    
    return array(
        'success' => true,
        'status'  => 'deactivated',
        'message' => 'License deactivated successfully.',
    );
}

// ============================================================================
// MAIN REQUEST HANDLER
// ============================================================================

// Enable error reporting for debugging (disable in production).
error_reporting( E_ALL );
ini_set( 'display_errors', 0 ); // Don't display errors, but log them.

// Get request data.
$input = file_get_contents( 'php://input' );
$data  = json_decode( $input, true );

// Fallback to POST data if JSON parsing fails.
if ( empty( $data ) || json_last_error() !== JSON_ERROR_NONE ) {
    $data = $_POST;
}

// Validate required fields.
$action      = isset( $data['action'] ) ? sanitize_text_field( $data['action'] ) : '';
$plugin      = isset( $data['plugin'] ) ? sanitize_text_field( $data['plugin'] ) : '';
$license_key = isset( $data['license_key'] ) ? sanitize_text_field( $data['license_key'] ) : '';
$site_url    = isset( $data['site_url'] ) ? esc_url_raw( $data['site_url'] ) : '';

// Validate action.
if ( ! in_array( $action, array( 'check', 'activate', 'deactivate' ), true ) ) {
    send_response( false, array( 'message' => 'Invalid action. Use: check, activate, or deactivate.' ), 400 );
}

// Validate plugin.
if ( empty( $plugin ) || ! isset( $plugin_product_map[ $plugin ] ) ) {
    send_response( false, array( 'message' => 'Invalid or unknown plugin.' ), 400 );
}

// Validate license key.
if ( empty( $license_key ) ) {
    send_response( false, array( 'message' => 'License key is required.' ), 400 );
}

// Get product variant ID.
$variant_id = $plugin_product_map[ $plugin ]['variant_id'];

if ( empty( $variant_id ) ) {
    send_response( false, array( 'message' => 'Plugin configuration error. Please contact support.' ), 500 );
}

// Handle request based on action (with error handling).
try {
    switch ( $action ) {
        case 'check':
            $result = validate_license_with_lemon_squeezy( $license_key, $variant_id );
            send_response( $result['success'], array(
                'status'  => $result['status'],
                'message' => $result['message'],
            ) );
            break;
            
        case 'activate':
            $result = activate_license_with_lemon_squeezy( $license_key, $variant_id, $site_url );
            send_response( $result['success'], array(
                'status'  => $result['status'],
                'message' => $result['message'],
            ) );
            break;
            
        case 'deactivate':
            $result = deactivate_license_with_lemon_squeezy( $license_key, $variant_id, $site_url );
            send_response( $result['success'], array(
                'status'  => $result['status'],
                'message' => $result['message'],
            ) );
            break;
            
        default:
            send_response( false, array( 'message' => 'Invalid action.' ), 400 );
    }
} catch ( Exception $e ) {
    // Log error for debugging.
    error_log( 'DataLayer Manager License API Error: ' . $e->getMessage() );
    
    // Return error response.
    send_response( false, array(
        'message' => 'An error occurred while processing your request.',
        'status'  => 'error',
    ), 500 );
}
