# Scripts + Pixels DataLayer Manager: Free vs Premium Features

The plugin is distributed in two builds:

- **Free build** (e.g. WordPress.org): License manager file is not included. Runs as free version only.
- **Premium build**: Includes license manager. Free features always work; premium features unlock when a valid license is activated.

---

## Free (all builds)

- **Automatic dataLayer detection** — Injects variables on every page based on WordPress and WooCommerce context (no configuration required).
- **WordPress variables** — e.g. `pageType`, `postId`, `postTitle`, `postAuthor`, `postCategory`, `postTags`, `isLoggedIn`, `siteName`, `siteUrl`, and more.
- **WooCommerce variables** (when WooCommerce is active) — e.g. `productId`, `productName`, `productPrice`, `cartTotal`, `checkoutStep`, and more.
- **Settings → DataLayer Manager** — Overview screen: what the plugin does, how to view variables in the browser console, links to License when in Premium build.
- **View screen** (Premium build only; in Free build the menu may differ) — In Premium build, the “View” tab shows current dataLayer; auto-detected variables always appear. Custom variables appear when licensed.

---

## Premium (Premium build + active license)

- **Custom variables per page/post/product** — Add your own key/value pairs for specific posts, pages, or products. Merged with auto-detected variables on the frontend (custom take precedence on conflict).
- **Visual editor in admin** — DataLayer Variables meta box on post, page, and product edit screens: add, edit, remove custom variables; preview auto-detected variables for that content.
- **Preview before publishing** — See auto-detected variables for the current post/page/product in the editor.
- **License activation** — Settings → DataLayer Manager → License: activate or deactivate your premium license key (Lemon Squeezy via scriptsandpixels.studio).
- **Priority support and updates** — As per your product terms.

---

## Summary

| Feature                              | Free build | Premium build (unlicensed) | Premium build (licensed) |
|--------------------------------------|------------|----------------------------|---------------------------|
| Auto-detected dataLayer variables    | ✓          | ✓                          | ✓                         |
| Overview / View screens              | ✓          | ✓                          | ✓                         |
| Custom variables per page/post/product | —        | — (section hidden or locked) | ✓                      |
| Visual editor (meta box) for custom vars | —      | —                          | ✓                         |
| License tab                          | —          | ✓ (activate license)       | ✓ (manage license)        |

---

## Build types

- **Free version** — `DATALAYER_MANAGER_FREE_VERSION` is true when `includes/class-license-manager.php` is missing (e.g. WP.org zip). No License tab, no custom variables UI.
- **Premium version** — Same codebase with `class-license-manager.php` included. License tab appears; custom variables UI appears but only saves/uses data when license is valid (`datalayer_manager_is_premium()` returns true).
