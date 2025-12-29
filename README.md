# DataLayer Manager

A WordPress plugin that automatically detects WordPress context and injects dataLayer variables for analytics tools (e.g., GA4/GTM). No configuration needed - works automatically.

## Features

- **Automatic Detection**: Automatically detects page type, post information, categories, tags, and more
- **Zero Configuration**: Works out of the box - no setup required
- **Frontend Injection**: Automatically injects variables into `window.dataLayer` using the `.push()` method
- **View-Only Admin**: Simple admin interface to view detected variables
- **Extensible**: Hooks available for developers to customize variables

## Installation

1. Upload the `datalayer-manager` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Settings → DataLayer Manager to view detected variables

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Usage

### Viewing Detected Variables

1. Go to Settings → DataLayer Manager
2. View the overview of currently detected variables
3. Click "View Current dataLayer" for detailed information

### Viewing DataLayer in Browser

1. Visit any page on your website (frontend)
2. Open browser Developer Tools (F12)
3. Go to the Console tab
4. Type: `window.dataLayer`
5. Press Enter to see all detected variables

## Available Variables

The plugin automatically detects the following variables based on WordPress context:

- **pageType**: Type of page (home, blog, post, page, category, tag, archive, search, 404, other)
- **postType**: Post type (on single post pages)
- **postId**: Post ID (on single post pages)
- **postTitle**: Post title (on single post pages)
- **postCategory**: Array of category names (on single post pages with categories)
- **postTags**: Array of tag names (on single post pages with tags)
- **pageId**: Page ID (on page pages)
- **pageTitle**: Page title (on page pages)
- **pageSlug**: Page URL slug (on page pages)
- **categoryName**: Category name (on category archive pages)
- **categoryId**: Category ID (on category archive pages)
- **tagName**: Tag name (on tag archive pages)
- **tagId**: Tag ID (on tag archive pages)
- **archiveType**: Post type (on post type archive pages)
- **searchQuery**: Search query term (on search result pages)
- **userLoggedIn**: Boolean indicating if user is logged in (always present)
- **userId**: User ID (only if user is logged in)
- **siteName**: WordPress site name (always present)
- **siteUrl**: WordPress site URL (always present)

## Developer Hooks

### Filters

- `datalayer_manager_variables`: Filter variables before injection
  ```php
  add_filter( 'datalayer_manager_variables', function( $variables ) {
      // Modify $variables array
      $variables['customVar'] = 'custom value';
      return $variables;
  } );
  ```

### Actions

- `datalayer_manager_init`: Fires after plugin initialization
- `datalayer_manager_activated`: Fires on plugin activation
- `datalayer_manager_deactivated`: Fires on plugin deactivation
- `datalayer_manager_after_injection`: Fires after dataLayer injection

## Debug Mode

Enable debug mode by adding this to `wp-config.php`:

```php
define( 'DATALAYER_MANAGER_DEBUG', true );
```

When enabled, HTML comments will be output in the frontend (visible only to administrators) showing:
- Number of variables detected
- Debug information for troubleshooting

## Security

- All admin inputs are sanitized
- All outputs are escaped
- Capability checks on all admin routes
- Safe error handling prevents JavaScript errors

## Multisite Compatibility

The plugin is multisite-safe and works correctly on multisite installations.

## Support

For issues, feature requests, or contributions, please refer to the plugin repository.

## License

GPL v2 or later
