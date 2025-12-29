<?php
/**
 * Main plugin class.
 *
 * @package DataLayer_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main DataLayer Manager class.
 */
class DataLayer_Manager {

    /**
     * Initialize the plugin.
     */
    public function init() {
        // Register admin menu.
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        
        // Frontend injection.
        add_action( 'wp_head', array( $this, 'inject_datalayer' ), 1 );
    }

    /**
     * Register admin menu under Settings.
     */
    public function register_admin_menu() {
        add_options_page(
            __( 'DataLayer Manager', 'datalayer-manager' ),
            __( 'DataLayer Manager', 'datalayer-manager' ),
            DataLayer_Manager_Capabilities::CAP_VIEW,
            'datalayer-manager',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * Render the admin page (routes to different screens).
     */
    public function render_admin_page() {
        // Check capabilities.
        $can_view = DataLayer_Manager_Capabilities::current_user_can_view();

        if ( ! $can_view ) {
            wp_die( esc_html__( 'You do not have permission to view this page.', 'datalayer-manager' ) );
        }

        // Route to appropriate screen.
        $screen = isset( $_GET['screen'] ) ? sanitize_text_field( wp_unslash( $_GET['screen'] ) ) : 'overview';

        switch ( $screen ) {
            case 'view':
                $this->render_screen_view();
                break;
            case 'overview':
            default:
                $this->render_screen_overview();
                break;
        }
    }

    /**
     * Render Screen 0: Status Overview.
     */
    private function render_screen_overview() {
        // Get auto-detected variables.
        $variables = $this->get_automatic_datalayer_variables();

        // Get all possible variables documentation.
        $all_possible_variables = $this->get_all_possible_variables_doc();

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <div class="datalayer-manager-status">
                <h2><?php esc_html_e( 'Current Status', 'datalayer-manager' ); ?></h2>
                <p>
                    <strong><?php esc_html_e( 'Auto-Detection Active', 'datalayer-manager' ); ?></strong>
                </p>
                <p>
                    <?php esc_html_e( 'DataLayer variables are automatically detected from WordPress context and injected on the frontend.', 'datalayer-manager' ); ?>
                </p>

                <h3><?php esc_html_e( 'All Default Possible Variables', 'datalayer-manager' ); ?></h3>
                <p>
                    <?php esc_html_e( 'The following variables can be automatically detected by the plugin:', 'datalayer-manager' ); ?>
                </p>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 25%;"><?php esc_html_e( 'Variable Name', 'datalayer-manager' ); ?></th>
                            <th scope="col" style="width: 20%;"><?php esc_html_e( 'Type', 'datalayer-manager' ); ?></th>
                            <th scope="col" style="width: 55%;"><?php esc_html_e( 'Description', 'datalayer-manager' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $all_possible_variables as $var ) : ?>
                            <tr>
                                <td><strong><code><?php echo esc_html( $var['name'] ); ?></code></strong></td>
                                <td><?php echo esc_html( $var['type'] ); ?></td>
                                <td><?php echo esc_html( $var['description'] ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h3><?php esc_html_e( 'How It Works', 'datalayer-manager' ); ?></h3>
                <p>
                    <?php esc_html_e( 'Variables are automatically detected from WordPress context (page type, post info, categories, etc.) and injected into window.dataLayer on the frontend using the .push() method. No configuration needed - it works automatically.', 'datalayer-manager' ); ?>
                </p>
                
                <h4><?php esc_html_e( 'How to View the DataLayer in Your Browser', 'datalayer-manager' ); ?></h4>
                <ol>
                    <li>
                        <?php esc_html_e( 'Visit any page on your website (frontend, not admin)', 'datalayer-manager' ); ?>
                    </li>
                    <li>
                        <?php esc_html_e( 'Open your browser\'s Developer Tools:', 'datalayer-manager' ); ?>
                        <ul style="list-style-type: disc; margin-left: 20px; margin-top: 5px;">
                            <li><?php esc_html_e( 'Chrome/Edge: Press F12 or right-click → Inspect', 'datalayer-manager' ); ?></li>
                            <li><?php esc_html_e( 'Firefox: Press F12 or right-click → Inspect Element', 'datalayer-manager' ); ?></li>
                            <li><?php esc_html_e( 'Safari: Enable Developer menu in Preferences → Advanced, then press Cmd+Option+I', 'datalayer-manager' ); ?></li>
                        </ul>
                    </li>
                    <li>
                        <?php esc_html_e( 'Go to the Console tab', 'datalayer-manager' ); ?>
                    </li>
                    <li>
                        <?php esc_html_e( 'Type the following command and press Enter:', 'datalayer-manager' ); ?>
                        <div style="background: #f5f5f5; border: 1px solid #ddd; padding: 10px; margin: 10px 0; font-family: monospace;">
                            <code>window.dataLayer</code>
                        </div>
                    </li>
                    <li>
                        <?php esc_html_e( 'You should see an array containing all the detected variables for the current page.', 'datalayer-manager' ); ?>
                    </li>
                </ol>
                <p>
                    <strong><?php esc_html_e( 'Tip:', 'datalayer-manager' ); ?></strong>
                    <?php esc_html_e( 'You can also expand the array in the console to see individual variables and their values.', 'datalayer-manager' ); ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Get documentation for all possible variables that can be detected.
     *
     * @return array Array of variable documentation with name, type, and description.
     */
    private function get_all_possible_variables_doc() {
        return array(
            array(
                'name' => 'pageType',
                'type' => 'string',
                'description' => __( 'The type of page: "home", "blog", "post", "page", "category", "tag", "archive", "search", "404", or "other"', 'datalayer-manager' ),
            ),
            array(
                'name' => 'postType',
                'type' => 'string',
                'description' => __( 'The post type (e.g., "post", "page", custom post type). Only on single post pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'postId',
                'type' => 'number',
                'description' => __( 'The ID of the post. Only on single post pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'postTitle',
                'type' => 'string',
                'description' => __( 'The title of the post. Only on single post pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'postCategory',
                'type' => 'array',
                'description' => __( 'Array of category names assigned to the post. Only on single post pages if categories exist.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'postTags',
                'type' => 'array',
                'description' => __( 'Array of tag names assigned to the post. Only on single post pages if tags exist.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'pageId',
                'type' => 'number',
                'description' => __( 'The ID of the page. Only on page pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'pageTitle',
                'type' => 'string',
                'description' => __( 'The title of the page. Only on page pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'pageSlug',
                'type' => 'string',
                'description' => __( 'The URL slug of the page. Only on page pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'categoryName',
                'type' => 'string',
                'description' => __( 'The name of the category. Only on category archive pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'categoryId',
                'type' => 'number',
                'description' => __( 'The ID of the category. Only on category archive pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'tagName',
                'type' => 'string',
                'description' => __( 'The name of the tag. Only on tag archive pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'tagId',
                'type' => 'number',
                'description' => __( 'The ID of the tag. Only on tag archive pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'archiveType',
                'type' => 'string',
                'description' => __( 'The post type for post type archive pages. Only on post type archive pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'searchQuery',
                'type' => 'string',
                'description' => __( 'The search query term. Only on search result pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'userLoggedIn',
                'type' => 'boolean',
                'description' => __( 'Whether a user is currently logged in (true/false). Always present.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'userId',
                'type' => 'number',
                'description' => __( 'The ID of the logged-in user. Only present if user is logged in.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'siteName',
                'type' => 'string',
                'description' => __( 'The name of the WordPress site. Always present.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'siteUrl',
                'type' => 'string',
                'description' => __( 'The URL of the WordPress site home page. Always present.', 'datalayer-manager' ),
            ),
        );
    }

    /**
     * Render Screen 1: Current dataLayer View (Read-Only).
     * Shows what would be injected on the current page.
     */
    private function render_screen_view() {
        // Get auto-detected variables (what would be injected on frontend).
        $variables = $this->get_automatic_datalayer_variables();

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Current dataLayer', 'datalayer-manager' ); ?></h1>

            <p>
                <a href="<?php echo esc_url( admin_url( 'options-general.php?page=datalayer-manager' ) ); ?>" class="button">
                    <?php esc_html_e( '← Back to Overview', 'datalayer-manager' ); ?>
                </a>
            </p>

            <?php if ( empty( $variables ) ) : ?>
                <div class="notice notice-info">
                    <p>
                        <strong><?php esc_html_e( 'No Variables Detected', 'datalayer-manager' ); ?></strong>
                    </p>
                    <p>
                        <?php esc_html_e( 'No dataLayer variables were detected for the current context.', 'datalayer-manager' ); ?>
                    </p>
                </div>
            <?php else : ?>
                <div class="notice notice-info">
                    <p>
                        <strong><?php esc_html_e( 'Auto-Detected Variables', 'datalayer-manager' ); ?></strong>
                    </p>
                    <p>
                        <?php esc_html_e( 'These variables are automatically detected from WordPress context and injected on the frontend.', 'datalayer-manager' ); ?>
                    </p>
                </div>

                <h2><?php esc_html_e( 'Active Variables', 'datalayer-manager' ); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="column-name"><?php esc_html_e( 'Name', 'datalayer-manager' ); ?></th>
                            <th scope="col" class="column-value"><?php esc_html_e( 'Value', 'datalayer-manager' ); ?></th>
                            <th scope="col" class="column-type"><?php esc_html_e( 'Type', 'datalayer-manager' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $variables as $name => $value ) : ?>
                            <tr>
                                <td class="column-name">
                                    <strong><?php echo esc_html( $name ); ?></strong>
                                </td>
                                <td class="column-value">
                                    <?php echo esc_html( $this->format_value_for_display( $value ) ); ?>
                                </td>
                                <td class="column-type">
                                    <?php echo esc_html( $this->get_value_type( $value ) ); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Get the type of a value for display.
     *
     * @param mixed $value Value to check.
     * @return string Type name.
     */
    private function get_value_type( $value ) {
        if ( is_string( $value ) ) {
            return 'string';
        } elseif ( is_int( $value ) || is_float( $value ) ) {
            return 'number';
        } elseif ( is_bool( $value ) ) {
            return 'boolean';
        } elseif ( is_array( $value ) ) {
            return 'array';
        } elseif ( is_object( $value ) ) {
            return 'object';
        } elseif ( is_null( $value ) ) {
            return 'null';
        }
        return 'unknown';
    }

    /**
     * Format a value for display in the table.
     *
     * @param mixed $value Value to format.
     * @return string Formatted value string.
     */
    private function format_value_for_display( $value ) {
        if ( is_bool( $value ) ) {
            return $value ? 'true' : 'false';
        } elseif ( is_null( $value ) ) {
            return 'null';
        } elseif ( is_array( $value ) || is_object( $value ) ) {
            return wp_json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
        }
        return (string) $value;
    }

    /**
     * Auto-detect WordPress context and build dataLayer variables.
     *
     * @return array Array of dataLayer variables.
     */
    private function get_automatic_datalayer_variables() {
        $variables = array();

        // Page type detection.
        if ( is_front_page() ) {
            $variables['pageType'] = 'home';
        } elseif ( is_home() ) {
            $variables['pageType'] = 'blog';
        } elseif ( is_single() ) {
            $variables['pageType'] = 'post';
            $post = get_queried_object();
            if ( $post ) {
                $variables['postType'] = get_post_type( $post );
                $variables['postId'] = $post->ID;
                $variables['postTitle'] = get_the_title( $post );
                
                // Post categories.
                $categories = get_the_category( $post->ID );
                if ( ! empty( $categories ) ) {
                    $variables['postCategory'] = array();
                    foreach ( $categories as $cat ) {
                        $variables['postCategory'][] = $cat->name;
                    }
                }
                
                // Post tags.
                $tags = get_the_tags( $post->ID );
                if ( ! empty( $tags ) ) {
                    $variables['postTags'] = array();
                    foreach ( $tags as $tag ) {
                        $variables['postTags'][] = $tag->name;
                    }
                }
            }
        } elseif ( is_page() ) {
            $variables['pageType'] = 'page';
            $post = get_queried_object();
            if ( $post ) {
                $variables['pageId'] = $post->ID;
                $variables['pageTitle'] = get_the_title( $post );
                $variables['pageSlug'] = $post->post_name;
            }
        } elseif ( is_category() ) {
            $variables['pageType'] = 'category';
            $category = get_queried_object();
            if ( $category ) {
                $variables['categoryName'] = $category->name;
                $variables['categoryId'] = $category->term_id;
            }
        } elseif ( is_tag() ) {
            $variables['pageType'] = 'tag';
            $tag = get_queried_object();
            if ( $tag ) {
                $variables['tagName'] = $tag->name;
                $variables['tagId'] = $tag->term_id;
            }
        } elseif ( is_archive() ) {
            $variables['pageType'] = 'archive';
            if ( is_post_type_archive() ) {
                $variables['archiveType'] = get_post_type();
            }
        } elseif ( is_search() ) {
            $variables['pageType'] = 'search';
            $variables['searchQuery'] = get_search_query();
        } elseif ( is_404() ) {
            $variables['pageType'] = '404';
        } else {
            $variables['pageType'] = 'other';
        }

        // User information (if logged in).
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $variables['userId'] = $current_user->ID;
            $variables['userLoggedIn'] = true;
            // Note: We don't include sensitive user data by default.
        } else {
            $variables['userLoggedIn'] = false;
        }

        // Site information.
        $variables['siteName'] = get_bloginfo( 'name' );
        $variables['siteUrl'] = home_url();

        return $variables;
    }

    /**
     * Inject dataLayer script into frontend head.
     * Auto-detects WordPress context and builds dataLayer automatically.
     */
    public function inject_datalayer() {
        // Only inject on frontend, not in admin.
        if ( is_admin() ) {
            return;
        }

        // Auto-detect WordPress context and build variables.
        $variables = $this->get_automatic_datalayer_variables();

        if ( empty( $variables ) ) {
            // No variables - fail safely, do nothing.
            if ( $this->is_debug_mode() ) {
                echo "<!-- DataLayer Manager: No variables detected -->\n";
            }
            return;
        }

        // Debug mode output (admin-only).
        if ( $this->is_debug_mode() ) {
            echo "<!-- DataLayer Manager: Auto-detected " . esc_html( count( $variables ) ) . " variables -->\n";
        }

        // Allow filtering of variables before injection (for extensibility).
        $variables = apply_filters( 'datalayer_manager_variables', $variables );

        // Generate JavaScript.
        $js_variables = wp_json_encode( $variables, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        
        // Output script tag with .push() method (best practice).
        ?>
        <script type="text/javascript">
        try {
            // Ensure dataLayer exists.
            window.dataLayer = window.dataLayer || [];
            
            // Push variables to dataLayer using .push() method (best practice).
            window.dataLayer.push(<?php echo $js_variables; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped - JSON is safe. ?>);
        } catch ( error ) {
            // Fail safely - avoid fatal JS errors.
            console.error( 'DataLayer Manager: Error injecting variables', error );
        }
        </script>
        <?php

        // Allow other plugins to hook after injection.
        do_action( 'datalayer_manager_after_injection', $variables );
    }

    /**
     * Check if debug mode is enabled.
     * Debug mode can be enabled via constant or option.
     * Only works for administrators.
     *
     * @return bool True if debug mode is enabled.
     */
    private function is_debug_mode() {
        // Only show debug output to administrators.
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        // Check for constant (can be defined in wp-config.php).
        if ( defined( 'DATALAYER_MANAGER_DEBUG' ) && DATALAYER_MANAGER_DEBUG ) {
            return true;
        }

        // Check for option (can be set via admin UI in future).
        // For now, we'll use a constant only approach.
        return false;
    }
}
