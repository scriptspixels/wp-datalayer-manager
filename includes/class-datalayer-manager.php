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
