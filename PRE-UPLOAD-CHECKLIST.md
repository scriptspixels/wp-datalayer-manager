# Pre-Upload Checklist for WordPress.org

## ✅ Checklist Results

### 1. PHPCS WordPress Standards
**Status:** ⚠️ **PHPCS not installed**

**Action Required:**
```bash
# Install PHPCS (if using Composer)
composer require --dev squizlabs/php_codesniffer wp-coding-standards/wpcs

# Or install globally
pear install PHP_CodeSniffer
```

**Manual Check:** Review code follows WordPress coding standards:
- ✅ Proper escaping (`esc_html`, `esc_attr`, `esc_url`)
- ✅ Nonce verification for forms
- ✅ Capability checks
- ✅ Proper text domain usage
- ✅ No direct database queries (using WP functions)

### 2. WP Plugin Linter
**Status:** ❌ **Command not available**

**Note:** The `wp plugin plugin-linter` command is not a standard WP-CLI command. WordPress.org uses their own validation tools during submission.

**Alternative:** Use WordPress.org's online validator:
- Upload your zip file to: https://wordpress.org/plugins/developers/add/
- The system will automatically validate during submission

### 3. Readme.txt Validation
**Status:** ✅ **Ready for validation**

**Required Headers:** ✅ All present
```
=== DataLayer Manager ===
Contributors: scriptsandpixels
Tags: analytics, google-analytics, google-tag-manager, gtm, ga4, datalayer, tracking, woocommerce
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
```

**Validate Online:**
- Use WordPress.org readme validator: https://wordpress.org/plugins/developers/readme-validator/

### 4. Plugin Headers Check
**Status:** ✅ **Headers correct in built version**

**Built Plugin Headers:**
```
Plugin Name: DataLayer Manager
Plugin URI: https://scriptsandpixels.studio
Description: Automatically detects WordPress context and injects dataLayer variables for analytics tools (GA4/GTM). No coding required.
Version: 1.0.0
Author: Scripts + Pixels
Author URI: https://scriptsandpixels.studio
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: datalayer-manager
Domain Path: /languages
Requires at least: 5.0
Requires PHP: 7.4
```

**Note:** ✅ Description correctly updated (removed "Premium:" mention)

### 5. No Pro/Licensing Code in Free Build
**Status:** ✅ **Clean - No licensing code**

**Verification:**
- ✅ `class-license-manager.php` **NOT** in zip file
- ✅ `DATALAYER_MANAGER_FREE_VERSION` set to `true` in built version
- ✅ License manager class not loaded
- ✅ License-related code is gated behind `DATALAYER_MANAGER_FREE_VERSION` checks
- ✅ No external purchase links in free version
- ✅ Upgrade prompts are informational only

**Files in Zip:**
- ✅ `datalayer-manager.php` (main file)
- ✅ `includes/class-capabilities.php`
- ✅ `includes/class-datalayer-manager.php` (license checks disabled)
- ✅ `languages/datalayer-manager.pot`
- ✅ `uninstall.php`
- ✅ `readme.txt`
- ❌ `class-license-manager.php` (correctly excluded)

## 📋 Final Pre-Upload Steps

### Before Uploading:

1. **Test the Free Version Locally**
   ```bash
   # Extract and test
   unzip datalayer-manager-1.0.0-wp-org.zip -d /tmp/test-plugin
   # Install in a test WordPress site and verify:
   # - No license tab appears
   # - Upgrade prompts are informational
   # - Auto-detection works
   ```

2. **Validate Readme.txt Online**
   - Visit: https://wordpress.org/plugins/developers/readme-validator/
   - Upload your `readme.txt` file
   - Fix any warnings/errors

3. **Review Code Standards** (if PHPCS available)
   ```bash
   phpcs --standard=WordPress datalayer-manager.php includes/
   ```

4. **Final Manual Checks**
   - [ ] No hardcoded URLs to premium purchase pages
   - [ ] No external API calls for licensing
   - [ ] All upgrade prompts are informational
   - [ ] Core functionality works without premium
   - [ ] No console errors or warnings

## 🚀 Ready to Upload?

**Your plugin appears ready!** The built zip file:
- ✅ Has correct headers
- ✅ No licensing code
- ✅ Informational upgrade prompts only
- ✅ Includes proper readme.txt

**Next Steps:**
1. Validate readme.txt online (recommended)
2. Test the zip file in a clean WordPress install
3. Submit to WordPress.org: https://wordpress.org/plugins/developers/add/
