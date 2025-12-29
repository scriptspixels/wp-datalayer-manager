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
        
        // Register meta boxes for premium features.
        add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
        
        // Customize meta boxes panel label in block editor.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        
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

        // Get WordPress and WooCommerce variables documentation.
        $wordpress_variables = $this->get_wordpress_variables_doc();
        $woocommerce_variables = $this->get_woocommerce_variables_doc();

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

                <h3><?php esc_html_e( 'WordPress Default Variables', 'datalayer-manager' ); ?></h3>
                <p>
                    <?php esc_html_e( 'The following WordPress variables can be automatically detected:', 'datalayer-manager' ); ?>
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
                        <?php foreach ( $wordpress_variables as $var ) : ?>
                            <tr>
                                <td><strong><code><?php echo esc_html( $var['name'] ); ?></code></strong></td>
                                <td><?php echo esc_html( $var['type'] ); ?></td>
                                <td><?php echo esc_html( $var['description'] ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                
                <?php if ( $this->is_woocommerce_active() ) : ?>
                    <h3><?php esc_html_e( 'WooCommerce Variables', 'datalayer-manager' ); ?></h3>
                    <p>
                        <?php esc_html_e( 'The following WooCommerce variables can be automatically detected when WooCommerce is active:', 'datalayer-manager' ); ?>
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
                            <?php foreach ( $woocommerce_variables as $var ) : ?>
                                <tr>
                                    <td><strong><code><?php echo esc_html( $var['name'] ); ?></code></strong></td>
                                    <td><?php echo esc_html( $var['type'] ); ?></td>
                                    <td><?php echo esc_html( $var['description'] ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="notice notice-info">
                        <p>
                            <?php esc_html_e( 'WooCommerce is not currently active. Install and activate WooCommerce to enable e-commerce variable detection (product pricing, cart totals, checkout information, etc.).', 'datalayer-manager' ); ?>
                        </p>
                    </div>
                <?php endif; ?>

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
     * Get documentation for WordPress variables that can be detected.
     *
     * @return array Array of variable documentation with name, type, and description.
     */
    private function get_wordpress_variables_doc() {
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
     * Get documentation for WooCommerce variables that can be detected.
     *
     * @return array Array of variable documentation with name, type, and description.
     */
    private function get_woocommerce_variables_doc() {
        return array(
            // WooCommerce Product Variables.
            array(
                'name' => 'productId',
                'type' => 'number',
                'description' => __( 'WooCommerce product ID. Only on single product pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productName',
                'type' => 'string',
                'description' => __( 'WooCommerce product name. Only on single product pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productSku',
                'type' => 'string',
                'description' => __( 'WooCommerce product SKU. Only on single product pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productPrice',
                'type' => 'number',
                'description' => __( 'WooCommerce product current price. Only on single product pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productRegularPrice',
                'type' => 'number',
                'description' => __( 'WooCommerce product regular price. Only on single product pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productSalePrice',
                'type' => 'number',
                'description' => __( 'WooCommerce product sale price (null if not on sale). Only on single product pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productStockStatus',
                'type' => 'string',
                'description' => __( 'WooCommerce product stock status (instock, outofstock, etc.). Only on single product pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productStockQuantity',
                'type' => 'number',
                'description' => __( 'WooCommerce product stock quantity. Only on single product pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productType',
                'type' => 'string',
                'description' => __( 'WooCommerce product type (simple, variable, grouped, etc.). Only on single product pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productOnSale',
                'type' => 'boolean',
                'description' => __( 'Whether WooCommerce product is on sale. Only on single product pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productCategory',
                'type' => 'array',
                'description' => __( 'Array of WooCommerce product category names. Only on single product pages if categories exist.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productTag',
                'type' => 'array',
                'description' => __( 'Array of WooCommerce product tag names. Only on single product pages if tags exist.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productBrand',
                'type' => 'array',
                'description' => __( 'Array of product brand names (if WooCommerce Brands plugin is active). Only on single product pages.', 'datalayer-manager' ),
            ),
            // WooCommerce Page Type Variables.
            array(
                'name' => 'pageType',
                'type' => 'string',
                'description' => __( 'WooCommerce page types: "shop", "product_category", "product_tag", "cart", "checkout", "account". Overrides standard WordPress pageType.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productCategoryName',
                'type' => 'string',
                'description' => __( 'WooCommerce product category name. Only on product category archive pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productCategoryId',
                'type' => 'number',
                'description' => __( 'WooCommerce product category ID. Only on product category archive pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productTagName',
                'type' => 'string',
                'description' => __( 'WooCommerce product tag name. Only on product tag archive pages.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'productTagId',
                'type' => 'number',
                'description' => __( 'WooCommerce product tag ID. Only on product tag archive pages.', 'datalayer-manager' ),
            ),
            // WooCommerce Cart Variables.
            array(
                'name' => 'cartTotal',
                'type' => 'number',
                'description' => __( 'WooCommerce cart total amount. Only on cart page.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'cartSubtotal',
                'type' => 'number',
                'description' => __( 'WooCommerce cart subtotal amount. Only on cart page.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'cartItemCount',
                'type' => 'number',
                'description' => __( 'WooCommerce cart item count. Only on cart page.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'cartItemQuantity',
                'type' => 'number',
                'description' => __( 'WooCommerce cart total quantity. Only on cart page.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'cartItems',
                'type' => 'array',
                'description' => __( 'Array of WooCommerce cart items with productId, productName, productSku, productPrice, quantity, lineTotal. Only on cart page.', 'datalayer-manager' ),
            ),
            // WooCommerce Checkout Variables.
            array(
                'name' => 'checkoutTotal',
                'type' => 'number',
                'description' => __( 'WooCommerce checkout total amount. Only on checkout page.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'checkoutSubtotal',
                'type' => 'number',
                'description' => __( 'WooCommerce checkout subtotal amount. Only on checkout page.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'checkoutItemCount',
                'type' => 'number',
                'description' => __( 'WooCommerce checkout item count. Only on checkout page.', 'datalayer-manager' ),
            ),
            array(
                'name' => 'checkoutItems',
                'type' => 'array',
                'description' => __( 'Array of WooCommerce checkout items with productId, productName, productSku, productPrice, quantity, lineTotal. Only on checkout page.', 'datalayer-manager' ),
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
     * Check if WooCommerce is active.
     *
     * @return bool True if WooCommerce is active.
     */
    private function is_woocommerce_active() {
        return class_exists( 'WooCommerce' );
    }

    /**
     * Get WooCommerce-specific variables.
     *
     * @return array Array of WooCommerce variables.
     */
    private function get_woocommerce_variables() {
        $variables = array();

        if ( ! $this->is_woocommerce_active() ) {
            return $variables;
        }

        // Product page detection.
        if ( function_exists( 'is_product' ) && is_product() ) {
            $variables['pageType'] = 'product';
            global $product;
            
            if ( $product && is_a( $product, 'WC_Product' ) ) {
                $variables['productId'] = $product->get_id();
                $variables['productName'] = $product->get_name();
                $variables['productSku'] = $product->get_sku();
                $variables['productPrice'] = (float) $product->get_price();
                $variables['productRegularPrice'] = (float) $product->get_regular_price();
                $variables['productSalePrice'] = $product->get_sale_price() ? (float) $product->get_sale_price() : null;
                $variables['productStockStatus'] = $product->get_stock_status();
                $variables['productStockQuantity'] = $product->get_stock_quantity();
                $variables['productType'] = $product->get_type();
                $variables['productOnSale'] = $product->is_on_sale();

                // Product categories (WooCommerce taxonomy).
                $product_categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
                if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
                    $variables['productCategory'] = $product_categories;
                }

                // Product tags (WooCommerce taxonomy).
                $product_tags = wp_get_post_terms( $product->get_id(), 'product_tag', array( 'fields' => 'names' ) );
                if ( ! empty( $product_tags ) && ! is_wp_error( $product_tags ) ) {
                    $variables['productTag'] = $product_tags;
                }

                // Product brand (if WooCommerce Brands plugin is active).
                if ( taxonomy_exists( 'product_brand' ) ) {
                    $product_brands = wp_get_post_terms( $product->get_id(), 'product_brand', array( 'fields' => 'names' ) );
                    if ( ! empty( $product_brands ) && ! is_wp_error( $product_brands ) ) {
                        $variables['productBrand'] = $product_brands;
                    }
                }
            }
        }

        // Shop page detection.
        if ( function_exists( 'is_shop' ) && is_shop() ) {
            $variables['pageType'] = 'shop';
        }
        // Product category page.
        elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
            $category = get_queried_object();
            if ( $category && isset( $category->term_id ) ) {
                $variables['pageType'] = 'product_category';
                $variables['productCategoryName'] = $category->name;
                $variables['productCategoryId'] = $category->term_id;
            }
        }
        // Product tag page.
        elseif ( function_exists( 'is_product_tag' ) && is_product_tag() ) {
            $tag = get_queried_object();
            if ( $tag && isset( $tag->term_id ) ) {
                $variables['pageType'] = 'product_tag';
                $variables['productTagName'] = $tag->name;
                $variables['productTagId'] = $tag->term_id;
            }
        }

        // Cart page.
        if ( function_exists( 'is_cart' ) && is_cart() ) {
            $variables['pageType'] = 'cart';
            
            if ( function_exists( 'WC' ) && WC()->cart ) {
                $cart = WC()->cart;
                $variables['cartTotal'] = (float) $cart->get_total( 'edit' );
                $variables['cartSubtotal'] = (float) $cart->get_subtotal();
                $variables['cartItemCount'] = $cart->get_cart_contents_count();
                $variables['cartItemQuantity'] = $cart->get_cart_contents_count();

                // Cart items.
                $cart_items = array();
                foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
                    $product = $cart_item['data'];
                    if ( $product && is_a( $product, 'WC_Product' ) ) {
                        $cart_items[] = array(
                            'productId' => $product->get_id(),
                            'productName' => $product->get_name(),
                            'productSku' => $product->get_sku(),
                            'productPrice' => (float) $product->get_price(),
                            'quantity' => $cart_item['quantity'],
                            'lineTotal' => (float) $cart_item['line_total'],
                        );
                    }
                }
                if ( ! empty( $cart_items ) ) {
                    $variables['cartItems'] = $cart_items;
                }
            }
        }

        // Checkout page.
        if ( function_exists( 'is_checkout' ) && is_checkout() ) {
            $variables['pageType'] = 'checkout';
            
            if ( function_exists( 'WC' ) && WC()->cart ) {
                $cart = WC()->cart;
                $variables['checkoutTotal'] = (float) $cart->get_total( 'edit' );
                $variables['checkoutSubtotal'] = (float) $cart->get_subtotal();
                $variables['checkoutItemCount'] = $cart->get_cart_contents_count();

                // Checkout items.
                $checkout_items = array();
                foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
                    $product = $cart_item['data'];
                    if ( $product && is_a( $product, 'WC_Product' ) ) {
                        $checkout_items[] = array(
                            'productId' => $product->get_id(),
                            'productName' => $product->get_name(),
                            'productSku' => $product->get_sku(),
                            'productPrice' => (float) $product->get_price(),
                            'quantity' => $cart_item['quantity'],
                            'lineTotal' => (float) $cart_item['line_total'],
                        );
                    }
                }
                if ( ! empty( $checkout_items ) ) {
                    $variables['checkoutItems'] = $checkout_items;
                }
            }
        }

        // Account page.
        if ( function_exists( 'is_account_page' ) && is_account_page() ) {
            $variables['pageType'] = 'account';
        }

        return $variables;
    }

    /**
     * Auto-detect WordPress context and build dataLayer variables.
     *
     * @return array Array of dataLayer variables.
     */
    private function get_automatic_datalayer_variables() {
        $variables = array();

        // WooCommerce detection (check before general WordPress detection).
        if ( $this->is_woocommerce_active() ) {
            $woocommerce_vars = $this->get_woocommerce_variables();
            if ( ! empty( $woocommerce_vars ) ) {
                $variables = array_merge( $variables, $woocommerce_vars );
                
                // If WooCommerce page type is set, skip general WordPress detection.
                if ( isset( $variables['pageType'] ) && in_array( $variables['pageType'], array( 'shop', 'product_category', 'product_tag', 'cart', 'checkout', 'account' ), true ) ) {
                    // Continue to add user and site info below.
                } elseif ( isset( $variables['pageType'] ) && $variables['pageType'] === 'product' ) {
                    // Product page - already handled, continue to add user and site info.
                } else {
                    // Fall through to general WordPress detection.
                }
            }
        }

        // Page type detection (only if not already set by WooCommerce).
        if ( ! isset( $variables['pageType'] ) ) {
            if ( is_front_page() ) {
            $variables['pageType'] = 'home';
        } elseif ( is_home() ) {
            $variables['pageType'] = 'blog';
        } elseif ( is_single() ) {
            // Skip if this is a WooCommerce product (already handled).
            if ( ! ( $this->is_woocommerce_active() && function_exists( 'is_product' ) && is_product() ) ) {
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
            // Skip if this is a WooCommerce archive (already handled).
            if ( ! ( $this->is_woocommerce_active() && ( ( function_exists( 'is_shop' ) && is_shop() ) || ( function_exists( 'is_product_category' ) && is_product_category() ) || ( function_exists( 'is_product_tag' ) && is_product_tag() ) ) ) ) {
                $variables['pageType'] = 'archive';
                if ( is_post_type_archive() ) {
                    $variables['archiveType'] = get_post_type();
                }
            }
        } elseif ( is_search() ) {
            $variables['pageType'] = 'search';
            $variables['searchQuery'] = get_search_query();
        } elseif ( is_404() ) {
            $variables['pageType'] = '404';
        } else {
            $variables['pageType'] = 'other';
        }
        } // End if ( ! isset( $variables['pageType'] ) ).

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
     * Enqueue admin scripts for block editor customization.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_scripts( $hook ) {
        // Only load on post/page edit screens.
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
            return;
        }

        // Get current post type.
        global $post_type;
        $allowed_types = apply_filters( 'datalayer_manager_meta_box_post_types', array( 'post', 'page' ) );
        
        if ( ! in_array( $post_type, $allowed_types, true ) ) {
            return;
        }

        // Add script to change meta boxes panel label in block editor.
        add_action( 'admin_footer', array( $this, 'change_meta_boxes_label_script' ) );
    }

    /**
     * Output script to change meta boxes panel label.
     */
    public function change_meta_boxes_label_script() {
        ?>
        <script type="text/javascript">
        (function() {
            function changeMetaBoxesLabel() {
                var button = document.querySelector('.edit-post-meta-boxes-main__presenter button[aria-expanded]');
                if (button && button.textContent.trim().includes('Meta Boxes')) {
                    button.innerHTML = button.innerHTML.replace(/Meta Boxes/g, '<?php echo esc_js( __( 'DataLayer Manager', 'datalayer-manager' ) ); ?>');
                }
            }
            
            // Try immediately.
            changeMetaBoxesLabel();
            
            // Try after DOM is ready.
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', changeMetaBoxesLabel);
            }
            
            // Try after delays (block editor loads async).
            setTimeout(changeMetaBoxesLabel, 500);
            setTimeout(changeMetaBoxesLabel, 1000);
            setTimeout(changeMetaBoxesLabel, 2000);
            
            // Also watch for dynamic changes (block editor may update the DOM).
            if (typeof MutationObserver !== 'undefined') {
                var observer = new MutationObserver(function(mutations) {
                    changeMetaBoxesLabel();
                });
                
                var target = document.querySelector('.edit-post-meta-boxes-main__presenter');
                if (target) {
                    observer.observe(target, {
                        childList: true,
                        subtree: true,
                        characterData: true
                    });
                }
            }
        })();
        </script>
        <?php
    }

    /**
     * Register meta boxes for posts and pages.
     */
    public function register_meta_boxes() {
        // Add meta box to posts and pages.
        $post_types = array( 'post', 'page' );
        
        // Allow filtering to add custom post types.
        $post_types = apply_filters( 'datalayer_manager_meta_box_post_types', $post_types );
        
        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'datalayer-manager-variables',
                __( 'DataLayer Variables', 'datalayer-manager' ),
                array( $this, 'render_meta_box' ),
                $post_type,
                'normal', // Use 'normal' context (bottom area) for better space
                'high'    // High priority to appear near the top
            );
        }
    }

    /**
     * Render the meta box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box( $post ) {
        // Add nonce for security.
        wp_nonce_field( 'datalayer_manager_meta_box', 'datalayer_manager_meta_box_nonce' );
        
        // Get custom variables from post meta.
        $custom_variables = get_post_meta( $post->ID, '_datalayer_manager_custom_variables', true );
        if ( ! is_array( $custom_variables ) ) {
            $custom_variables = array();
        }
        
        // Get auto-detected variable keys that are reserved/uneditable.
        $auto_detected_keys = $this->get_auto_detected_variable_keys( $post );
        
        // Get preview of auto-detected variables with their values.
        $auto_detected_variables = $this->get_auto_detected_variables_preview( $post );
        
        // Filter out custom variables that match auto-detected keys (they shouldn't exist, but clean up if they do).
        $filtered_custom_variables = array();
        foreach ( $custom_variables as $key => $value ) {
            if ( ! in_array( $key, $auto_detected_keys, true ) ) {
                $filtered_custom_variables[ $key ] = $value;
            }
        }
        
        ?>
        <div class="datalayer-manager-meta-box">

            <?php if ( ! empty( $auto_detected_variables ) ) : ?>
                <div style="margin-bottom: 10px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                    <p>
                        <strong><?php esc_html_e( 'Auto-Detected Variables', 'datalayer-manager' ); ?></strong>
                    </p>
                    <p class="description" style="font-size: 12px; margin-top: 5px;">
                        <?php esc_html_e( 'These variables are automatically detected and locked. They cannot be modified.', 'datalayer-manager' ); ?>
                    </p>
                    <div style="background: #f5f5f5; padding: 10px; margin-top: 10px; border-radius: 3px; max-height: 200px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <?php foreach ( $auto_detected_variables as $key => $value ) : ?>
                                <tr style="border-bottom: 1px solid #ddd;">
                                    <td style="padding: 6px 8px; width: 35%; vertical-align: top;">
                                        <code style="color: #666; font-weight: bold;"><?php echo esc_html( $key ); ?></code>
                                    </td>
                                    <td style="padding: 6px 8px; vertical-align: top;">
                                        <span style="color: #333;"><?php echo esc_html( $this->format_value_for_display( $value ) ); ?></span>
                                    </td>
                                    <td style="padding: 6px 8px; width: 15%; vertical-align: top;">
                                        <span style="color: #666; font-size: 11px;"><?php echo esc_html( $this->get_value_type( $value ) ); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            <p style="margin-top: 20px;">
                <strong><?php esc_html_e( 'Custom Variables', 'datalayer-manager' ); ?></strong>
            </p>
            <p class="description">
                <?php esc_html_e( 'Add custom dataLayer variables that will merge with auto-detected variables for this page.', 'datalayer-manager' ); ?>
            </p>
            
            <div id="datalayer-custom-variables">
                <?php if ( ! empty( $filtered_custom_variables ) ) : ?>
                    <table class="wp-list-table widefat fixed striped" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th style="width: 30%;"><?php esc_html_e( 'Name', 'datalayer-manager' ); ?></th>
                                <th style="width: 40%;"><?php esc_html_e( 'Value', 'datalayer-manager' ); ?></th>
                                <th style="width: 20%;"><?php esc_html_e( 'Type', 'datalayer-manager' ); ?></th>
                                <th style="width: 10%;"><?php esc_html_e( 'Actions', 'datalayer-manager' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $index = 0; ?>
                            <?php foreach ( $filtered_custom_variables as $key => $value ) : ?>
                                <?php
                                $type = $this->get_value_type( $value );
                                $display_value = $this->format_value_for_edit( $value, $type );
                                ?>
                                <tr class="datalayer-variable-row">
                                    <td>
                                        <input type="text" name="datalayer_variables[<?php echo esc_attr( $index ); ?>][key]" value="<?php echo esc_attr( $key ); ?>" class="regular-text datalayer-variable-key" pattern="[A-Za-z0-9_]+" required />
                                    </td>
                                    <td>
                                        <input type="text" name="datalayer_variables[<?php echo esc_attr( $index ); ?>][value]" value="<?php echo esc_attr( $display_value ); ?>" class="regular-text" required />
                                    </td>
                                    <td>
                                        <select name="datalayer_variables[<?php echo esc_attr( $index ); ?>][type]" class="regular-text">
                                            <option value="string" <?php selected( $type, 'string' ); ?>><?php esc_html_e( 'String', 'datalayer-manager' ); ?></option>
                                            <option value="number" <?php selected( $type, 'number' ); ?>><?php esc_html_e( 'Number', 'datalayer-manager' ); ?></option>
                                            <option value="boolean" <?php selected( $type, 'boolean' ); ?>><?php esc_html_e( 'Boolean', 'datalayer-manager' ); ?></option>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="button button-small remove-variable-row"><?php esc_html_e( 'Remove', 'datalayer-manager' ); ?></button>
                                    </td>
                                </tr>
                                <?php $index++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="description" style="margin-top: 10px;">
                        <?php esc_html_e( 'No custom variables added yet. Click "Add Variable" to create one.', 'datalayer-manager' ); ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <p style="margin-top: 15px;">
                <button type="button" class="button button-secondary" id="add-datalayer-variable">
                    <?php esc_html_e( '+ Add Variable', 'datalayer-manager' ); ?>
                </button>
            </p>
            
            <input type="hidden" id="datalayer-auto-detected-keys" value="<?php echo esc_attr( wp_json_encode( $auto_detected_keys ) ); ?>" />
        </div>
        
        <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                var autoDetectedKeys = [];
                try {
                    autoDetectedKeys = JSON.parse($('#datalayer-auto-detected-keys').val() || '[]');
                } catch(e) {
                    autoDetectedKeys = [];
                }
                
                // Validate key against auto-detected keys.
                function validateVariableKey(key, inputElement) {
                    if (autoDetectedKeys.indexOf(key) !== -1) {
                        inputElement.css('border-color', '#dc3232');
                        var errorMsg = inputElement.siblings('.datalayer-error-message');
                        if (errorMsg.length === 0) {
                            inputElement.after('<span class="datalayer-error-message" style="color: #dc3232; font-size: 11px; display: block; margin-top: 3px;"><?php echo esc_js( __( 'This key is reserved for auto-detected variables and cannot be used.', 'datalayer-manager' ) ); ?></span>');
                        }
                        return false;
                    } else {
                        inputElement.css('border-color', '');
                        inputElement.siblings('.datalayer-error-message').remove();
                        return true;
                    }
                }
                
                // Add variable row.
                $('#add-datalayer-variable').on('click', function() {
                    var index = Date.now();
                    var tbody = $('#datalayer-custom-variables tbody');
                    
                    // Create table structure if it doesn't exist.
                    if (tbody.length === 0) {
                        var table = '<table class="wp-list-table widefat fixed striped" style="margin-top: 10px;">' +
                            '<thead>' +
                            '<tr>' +
                            '<th style="width: 30%;"><?php echo esc_js( __( 'Name', 'datalayer-manager' ) ); ?></th>' +
                            '<th style="width: 40%;"><?php echo esc_js( __( 'Value', 'datalayer-manager' ) ); ?></th>' +
                            '<th style="width: 20%;"><?php echo esc_js( __( 'Type', 'datalayer-manager' ) ); ?></th>' +
                            '<th style="width: 10%;"><?php echo esc_js( __( 'Actions', 'datalayer-manager' ) ); ?></th>' +
                            '</tr>' +
                            '</thead>' +
                            '<tbody></tbody>' +
                            '</table>';
                        $('#datalayer-custom-variables').html(table);
                        tbody = $('#datalayer-custom-variables tbody');
                    }
                    
                    var row = '<tr class="datalayer-variable-row">' +
                        '<td><input type="text" name="datalayer_variables[' + index + '][key]" value="" class="regular-text datalayer-variable-key" pattern="[A-Za-z0-9_]+" required /></td>' +
                        '<td><input type="text" name="datalayer_variables[' + index + '][value]" value="" class="regular-text" required /></td>' +
                        '<td><select name="datalayer_variables[' + index + '][type]" class="regular-text">' +
                        '<option value="string"><?php echo esc_js( __( 'String', 'datalayer-manager' ) ); ?></option>' +
                        '<option value="number"><?php echo esc_js( __( 'Number', 'datalayer-manager' ) ); ?></option>' +
                        '<option value="boolean"><?php echo esc_js( __( 'Boolean', 'datalayer-manager' ) ); ?></option>' +
                        '</select></td>' +
                        '<td><button type="button" class="button button-small remove-variable-row"><?php echo esc_js( __( 'Remove', 'datalayer-manager' ) ); ?></button></td>' +
                        '</tr>';
                    tbody.append(row);
                });
                
                // Validate on key input.
                $(document).on('blur', '.datalayer-variable-key', function() {
                    var key = $(this).val().trim();
                    if (key) {
                        validateVariableKey(key, $(this));
                    }
                });
                
                // Remove variable row.
                $(document).on('click', '.remove-variable-row', function() {
                    $(this).closest('.datalayer-variable-row').remove();
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Save meta box data.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     */
    public function save_meta_box( $post_id, $post ) {
        // Check if nonce is set.
        if ( ! isset( $_POST['datalayer_manager_meta_box_nonce'] ) ) {
            return;
        }

        // Verify nonce.
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['datalayer_manager_meta_box_nonce'] ) ), 'datalayer_manager_meta_box' ) ) {
            return;
        }

        // Check if autosave.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check user permissions.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check post type.
        $allowed_types = apply_filters( 'datalayer_manager_meta_box_post_types', array( 'post', 'page' ) );
        if ( ! in_array( $post->post_type, $allowed_types, true ) ) {
            return;
        }

        // Get auto-detected variable keys (to prevent overriding).
        $auto_detected_keys = $this->get_auto_detected_variable_keys( $post );

        // Process custom variables.
        $custom_variables = array();
        
        if ( isset( $_POST['datalayer_variables'] ) && is_array( $_POST['datalayer_variables'] ) ) {
            foreach ( $_POST['datalayer_variables'] as $var ) {
                $key = isset( $var['key'] ) ? trim( sanitize_text_field( wp_unslash( $var['key'] ) ) ) : '';
                $value = isset( $var['value'] ) ? trim( sanitize_text_field( wp_unslash( $var['value'] ) ) ) : '';
                $type = isset( $var['type'] ) ? sanitize_text_field( wp_unslash( $var['type'] ) ) : 'string';

                // Skip if key is empty.
                if ( empty( $key ) ) {
                    continue;
                }

                // Validate key format.
                if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $key ) ) {
                    continue;
                }

                // Prevent using auto-detected variable keys.
                if ( in_array( $key, $auto_detected_keys, true ) ) {
                    continue; // Skip this variable - it's reserved.
                }

                // Convert value by type.
                $converted_value = $this->convert_value_by_type( $value, $type );
                if ( null !== $converted_value ) {
                    $custom_variables[ $key ] = $converted_value;
                }
            }
        }

        // Save custom variables.
        if ( ! empty( $custom_variables ) ) {
            update_post_meta( $post_id, '_datalayer_manager_custom_variables', $custom_variables );
        } else {
            delete_post_meta( $post_id, '_datalayer_manager_custom_variables' );
        }
    }

    /**
     * Convert value by type.
     *
     * @param string $value Value to convert.
     * @param string $type  Target type.
     * @return mixed Converted value or null if invalid.
     */
    private function convert_value_by_type( $value, $type ) {
        switch ( $type ) {
            case 'number':
                if ( ! is_numeric( $value ) ) {
                    return null;
                }
                return strpos( $value, '.' ) !== false ? (float) $value : (int) $value;

            case 'boolean':
                $lower_value = strtolower( trim( $value ) );
                if ( 'true' === $lower_value || '1' === $lower_value || 'yes' === $lower_value ) {
                    return true;
                } elseif ( 'false' === $lower_value || '0' === $lower_value || 'no' === $lower_value || '' === $lower_value ) {
                    return false;
                }
                return null;

            case 'string':
            default:
                return $value;
        }
    }

    /**
     * Format value for editing (convert back to string for form input).
     *
     * @param mixed  $value Value to format.
     * @param string $type  Type of value.
     * @return string Formatted value for form input.
     */
    private function format_value_for_edit( $value, $type ) {
        if ( 'boolean' === $type ) {
            return $value ? 'true' : 'false';
        }
        return (string) $value;
    }

    /**
     * Get auto-detected variable keys for a specific post (uneditable/reserved).
     *
     * @param WP_Post $post Post object.
     * @return array Array of reserved variable keys.
     */
    private function get_auto_detected_variable_keys( $post ) {
        $variables = $this->get_auto_detected_variables_preview( $post );
        return array_keys( $variables );
    }

    /**
     * Get preview of auto-detected variables with their values for a specific post.
     * This simulates what would be detected on the frontend.
     *
     * @param WP_Post $post Post object.
     * @return array Array of auto-detected variables with keys and values.
     */
    private function get_auto_detected_variables_preview( $post ) {
        $variables = array();
        
        // Common variables (always present).
        if ( 'post' === $post->post_type ) {
            $variables['pageType'] = 'post';
            $variables['postType'] = $post->post_type;
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
        } elseif ( 'page' === $post->post_type ) {
            $variables['pageType'] = 'page';
            $variables['pageId'] = $post->ID;
            $variables['pageTitle'] = get_the_title( $post );
            $variables['pageSlug'] = $post->post_name;
        }
        
        // WooCommerce variables (if WooCommerce is active and this is a product).
        if ( $this->is_woocommerce_active() && 'product' === $post->post_type ) {
            $product = wc_get_product( $post->ID );
            if ( $product && is_a( $product, 'WC_Product' ) ) {
                $variables['pageType'] = 'product';
                $variables['productId'] = $product->get_id();
                $variables['productName'] = $product->get_name();
                $variables['productSku'] = $product->get_sku();
                $variables['productPrice'] = (float) $product->get_price();
                $variables['productRegularPrice'] = (float) $product->get_regular_price();
                $sale_price = $product->get_sale_price();
                $variables['productSalePrice'] = $sale_price ? (float) $sale_price : null;
                $variables['productStockStatus'] = $product->get_stock_status();
                $variables['productStockQuantity'] = $product->get_stock_quantity();
                $variables['productType'] = $product->get_type();
                $variables['productOnSale'] = $product->is_on_sale();
                
                // Product categories.
                $product_categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
                if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
                    $variables['productCategory'] = $product_categories;
                }
                
                // Product tags.
                $product_tags = wp_get_post_terms( $product->get_id(), 'product_tag', array( 'fields' => 'names' ) );
                if ( ! empty( $product_tags ) && ! is_wp_error( $product_tags ) ) {
                    $variables['productTag'] = $product_tags;
                }
                
                // Product brand (if WooCommerce Brands plugin is active).
                if ( taxonomy_exists( 'product_brand' ) ) {
                    $product_brands = wp_get_post_terms( $product->get_id(), 'product_brand', array( 'fields' => 'names' ) );
                    if ( ! empty( $product_brands ) && ! is_wp_error( $product_brands ) ) {
                        $variables['productBrand'] = $product_brands;
                    }
                }
            }
        }
        
        // User information.
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $variables['userId'] = $current_user->ID;
            $variables['userLoggedIn'] = true;
        } else {
            $variables['userLoggedIn'] = false;
        }
        
        // Site information (always present).
        $variables['siteName'] = get_bloginfo( 'name' );
        $variables['siteUrl'] = home_url();
        
        return $variables;
    }

    /**
     * Get custom variables for a specific post.
     *
     * @param int $post_id Post ID.
     * @return array Custom variables array.
     */
    private function get_custom_variables( $post_id ) {
        $custom_variables = get_post_meta( $post_id, '_datalayer_manager_custom_variables', true );
        if ( ! is_array( $custom_variables ) ) {
            return array();
        }
        return $custom_variables;
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

        // Get custom variables for current post/page (if on singular page).
        if ( is_singular() ) {
            $post_id = get_queried_object_id();
            $custom_variables = $this->get_custom_variables( $post_id );
            
            // Merge custom variables with auto-detected ones (custom overrides auto).
            if ( ! empty( $custom_variables ) ) {
                $variables = array_merge( $variables, $custom_variables );
                
                if ( $this->is_debug_mode() ) {
                    echo "<!-- DataLayer Manager: Merged " . esc_html( count( $custom_variables ) ) . " custom variables -->\n";
                }
            }
        }

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
