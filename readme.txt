=== DataLayer Manager ===
Contributors: scriptsandpixels
Tags: datalayer, google-tag-manager, analytics, tracking, woocommerce
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically detects WordPress context and injects dataLayer variables for analytics tools (GA4/GTM). No coding required.

== Description ==

DataLayer Manager automatically creates and manages a dataLayer object for your WordPress site. This object contains structured data about your pages, posts, products, and user interactions that can be used by analytics tools like Google Tag Manager (GTM) and Google Analytics 4 (GA4).

**No coding required!** The plugin automatically detects WordPress and WooCommerce data and injects it into the dataLayer on every page.

= Key Features =

* **Automatic Detection** - Automatically detects page type, post data, categories, tags, and more
* **WooCommerce Support** - Detects product data, pricing, cart information, and checkout details
* **Zero Configuration** - Works out of the box with no setup required
* **Clean Code** - Follows WordPress coding standards and best practices
* **Developer Friendly** - Well-documented code and extensible hooks

= How It Works =

1. Install and activate the plugin
2. Visit any page on your website
3. Open browser Developer Tools (F12)
4. Type `dataLayer` in the console
5. See all automatically detected variables!

= WordPress Variables =

The plugin automatically detects and injects variables like:

* `pageType` - Type of page (post, page, category, tag, archive, search, 404, etc.)
* `postId` - Post/Page ID
* `postTitle` - Post/Page title
* `postAuthor` - Author name
* `postCategory` - Post categories
* `postTags` - Post tags
* `isLoggedIn` - Whether user is logged in
* `siteName` - Site name
* `siteUrl` - Site URL
* And more...

= WooCommerce Variables =

When WooCommerce is active, additional variables are automatically detected:

* `productId` - Product ID
* `productName` - Product name
* `productPrice` - Product price
* `productQuantity` - Product quantity
* `cartTotal` - Cart total
* `checkoutStep` - Checkout step
* And more...

= Free vs Premium =

**Free Version Includes:**
* Automatic detection of WordPress and WooCommerce variables
* Works on all page types (posts, pages, products, archives, etc.)
* Zero configuration required

**Premium Version Adds:**
* Custom variables per page/post/product
* Visual editor in WordPress admin
* Preview auto-detected variables before publishing
* Priority support and updates

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/datalayer-manager` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Visit any page on your website and check the browser console for `dataLayer` variables

== Frequently Asked Questions ==

= Does this work with Google Tag Manager? =

Yes! The plugin creates a `dataLayer` object that is compatible with Google Tag Manager and Google Analytics 4.

= Do I need to configure anything? =

No configuration is required. The plugin automatically detects and injects variables on all pages.

= Does this work with WooCommerce? =

Yes! When WooCommerce is active, the plugin automatically detects product, cart, and checkout data.

= Can I add custom variables? =

Custom variables per page are available in the premium version. The free version includes automatic detection of WordPress and WooCommerce variables.

= Where can I see the dataLayer variables? =

Open your browser's Developer Tools (F12), go to the Console tab, and type `dataLayer`. You'll see all the automatically detected variables.

== Screenshots ==

1. Overview screen showing auto-detection status
2. Auto-detected variables documentation
3. Meta box showing auto-detected variables (always visible)
4. Custom variables section (premium feature)

== Changelog ==

= 1.0.0 =
* Initial release
* Automatic detection of WordPress variables
* WooCommerce support
* Admin interface for viewing status and documentation
* Meta box for viewing auto-detected variables

== Upgrade Notice ==

= 1.0.0 =
Initial release. Automatically detects WordPress and WooCommerce dataLayer variables with zero configuration.
