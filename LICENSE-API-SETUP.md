# License API Setup Guide

This guide explains how to set up the licensing control layer between your WordPress plugin and Lemon Squeezy.

## Architecture

```
WordPress Plugin (plugin-datalayer.local)
   ↓ (sends: license_key, plugin_name, action)
scriptsandpixels.studio/license-api (scriptsandpixels.local)
   ↓ (validates with Lemon Squeezy API)
Lemon Squeezy License API
   ↓ (returns validation)
scriptsandpixels.studio/license-api
   ↓ (returns standardized response)
WordPress Plugin
```

## Step 1: Get Lemon Squeezy API Credentials

1. Log in to your Lemon Squeezy dashboard
2. Go to **Settings → API**
3. Create a new API key or copy your existing one
4. Note your **Product Variant ID** (found in your product settings)

## Step 2: Set Up the License API Endpoint

### On scriptsandpixels.local (for testing):

1. Create a directory: `scriptsandpixels.local/license-api/`
2. Copy `license-api-endpoint.php` to `scriptsandpixels.local/license-api/index.php`
3. Open `index.php` and update these values:

```php
// Line 37: Add your Lemon Squeezy API key
define( 'LEMON_SQUEEZY_API_KEY', 'your-actual-api-key-here' );

// Line 50: Add your product variant ID
$plugin_product_map = array(
    'datalayer-manager' => array(
        'variant_id' => 'your-actual-variant-id-here',
    ),
);
```

### On scriptsandpixels.studio (for production):

1. Create a directory: `scriptsandpixels.studio/license-api/`
2. Upload `index.php` (same file as above)
3. Update the same configuration values with production credentials

## Step 3: Update Plugin to Use Local Endpoint (for testing)

In `class-license-manager.php`, line 40, change:

```php
// For testing:
private $api_url = 'http://scriptsandpixels.local/license-api/';

// For production:
private $api_url = 'https://scriptsandpixels.studio/license-api/';
```

## Step 4: Test the Endpoint

### Test 1: Check if endpoint is accessible

Visit: `http://scriptsandpixels.local/license-api/`

Expected: JSON error message saying "Method not allowed. Use POST."

### Test 2: Test with cURL (from terminal)

```bash
curl -X POST http://scriptsandpixels.local/license-api/ \
  -H "Content-Type: application/json" \
  -d '{
    "action": "check",
    "plugin": "datalayer-manager",
    "license_key": "test-key-123",
    "site_url": "http://plugin-datalayer.local"
  }'
```

Expected: JSON response with license validation result.

### Test 3: Test from WordPress Plugin

1. Go to `plugin-datalayer.local/wp-admin`
2. Navigate to **Settings → DataLayer Manager → License tab**
3. Enter a test license key
4. Click "Activate License"
5. Check browser console/network tab for API calls

## Step 5: Lemon Squeezy API Endpoints

The endpoint uses these Lemon Squeezy API endpoints:

- **Validate License**: `POST /v1/licenses/validate`
- **Get License**: `GET /v1/licenses/{id}` (if needed)

### Lemon Squeezy API Request Format

```json
{
  "data": {
    "type": "licenses",
    "attributes": {
      "license_key": "abc123...",
      "variant_id": "12345"
    }
  }
}
```

### Lemon Squeezy API Response Format

```json
{
  "data": {
    "type": "licenses",
    "id": "123",
    "attributes": {
      "status": "active",
      "license_key": "abc123...",
      "expires_at": "2025-12-31T23:59:59.000000Z"
    }
  }
}
```

## Troubleshooting

### Error: "Error connecting to license server"

- Check that cURL is enabled on your server
- Verify the API endpoint URL is correct
- Check server error logs

### Error: "Invalid or unknown plugin"

- Make sure the plugin slug matches exactly: `datalayer-manager`
- Check `$plugin_product_map` array in `index.php`

### Error: "Plugin configuration error"

- Verify `variant_id` is set correctly in `$plugin_product_map`
- Check that the variant ID matches your Lemon Squeezy product

### Error: "License validation failed"

- Verify your Lemon Squeezy API key is correct
- Check that the license key format matches Lemon Squeezy's format
- Verify the variant ID matches the product the license was purchased for

## Security Notes

1. **API Key Security**: Never commit your API key to version control
2. **HTTPS**: Always use HTTPS in production
3. **Rate Limiting**: Consider adding rate limiting to prevent abuse
4. **Input Validation**: The endpoint validates all inputs, but you can add more strict validation if needed

## Next Steps

Once testing is complete:

1. Update the plugin's `api_url` to production: `https://scriptsandpixels.studio/license-api/`
2. Deploy the endpoint to production
3. Test with a real license key from Lemon Squeezy
4. Monitor for any errors or issues
