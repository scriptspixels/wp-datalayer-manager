# WordPress.org Submission Guide

This document explains how to prepare the DataLayer Manager plugin for WordPress.org submission.

## Overview

The plugin uses a **dual-build approach**:
- **Main branch**: Contains full premium-capable code
- **WordPress.org build**: Strips license functionality and makes upgrade prompts informational only

## Compliance Checklist

✅ **No paywalls blocking core functionality**
- Auto-detection works fully in free version
- All core features are accessible

✅ **Upgrade prompts are informational, not intrusive**
- No external purchase links in free version
- Informational messages only

✅ **No external upsell nags**
- No popups or persistent banners
- Upgrade prompts are contextual and informational

✅ **Clear "Free vs Pro" explanation**
- Documented in readme.txt
- Clear feature comparison

## Build Process

### 1. Build WordPress.org Version

Run the WordPress.org build script:

```bash
./build-wp-org.sh
```

This script:
- Sets `DATALAYER_MANAGER_FREE_VERSION` constant to `true`
- Removes `class-license-manager.php` file
- Updates plugin description to remove "Premium:" mention
- Creates a clean zip file: `datalayer-manager-{VERSION}-wp-org.zip`

### 2. Build Premium Version

Run the premium build script:

```bash
./build-plugin.sh
```

This creates: `datalayer-manager-{VERSION}.zip` (for Lemon Squeezy)

## What Gets Changed in Free Version

### Code Changes

1. **Constant Flag**: `DATALAYER_MANAGER_FREE_VERSION` set to `true`
2. **License Manager**: Not loaded (file removed from build)
3. **Premium Checks**: Always return `false`
4. **License Tab**: Hidden from admin navigation
5. **Upgrade Prompts**: Made informational (no external links)

### UI Changes

- License tab removed from admin navigation
- "Activate License" link removed from plugin row meta
- Upgrade prompts show informational messages only
- Status messages indicate "Free Version" instead of "Premium License Not Active"

## Files Included in WordPress.org Build

- `datalayer-manager.php` (main plugin file, modified)
- `includes/class-capabilities.php`
- `includes/class-datalayer-manager.php` (license checks disabled)
- `languages/` (translation files)
- `uninstall.php` (cleanup script)
- `readme.txt` (WordPress.org readme)

### Files Excluded

- `includes/class-license-manager.php` (premium feature)
- `license-api-endpoint.php` (marketing site only)
- `LICENSE-API-SETUP.md` (setup docs)
- `build-plugin.sh` / `build-wp-org.sh` (build scripts)
- `.gitignore` and other dev files

## Testing the Free Version Locally

To test the free version locally before building:

1. Edit `datalayer-manager.php` line 33:
   ```php
   define( 'DATALAYER_MANAGER_FREE_VERSION', true );
   ```

2. Comment out the license manager require (line 98):
   ```php
   // require_once DATALAYER_MANAGER_PLUGIN_DIR . 'includes/class-license-manager.php';
   ```

3. Test the plugin:
   - License tab should not appear
   - Upgrade prompts should be informational only
   - No "Activate License" links

## WordPress.org Submission Checklist

Before submitting to WordPress.org:

- [ ] Run `./build-wp-org.sh` to create free version
- [ ] Test the free version zip locally
- [ ] Verify no license functionality is accessible
- [ ] Verify upgrade prompts are informational only
- [ ] Check `readme.txt` is complete and accurate
- [ ] Ensure plugin description doesn't mention "Premium"
- [ ] Test auto-detection works correctly
- [ ] Verify WooCommerce integration (if applicable)

## Version Management

When updating the plugin:

1. Update version in `datalayer-manager.php` header (line 6)
2. Update version in `readme.txt` (Stable tag)
3. Update `CHANGELOG.md` (if maintaining)
4. Run `./build-wp-org.sh` for WordPress.org
5. Run `./build-plugin.sh` for premium version

## Support

For questions about WordPress.org submission:
- Review [WordPress.org Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)
- Check [Freemium Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/#7-plugins-may-not-contain-freemium-upsells)
