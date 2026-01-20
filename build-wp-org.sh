#!/bin/bash

# Build script for WordPress.org version of DataLayer Manager Plugin
# Strips premium/license functionality for WP.org compliance

PLUGIN_NAME="datalayer-manager"
PLUGIN_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BUILD_DIR="$PLUGIN_DIR/build-wp-org"
PLUGIN_FILE="$PLUGIN_DIR/${PLUGIN_NAME}.php"

# Extract version from plugin header
VERSION=$(grep -i "Version:" "$PLUGIN_FILE" | head -1 | sed -e 's/.*[Vv]ersion:[[:space:]]*\([0-9.]*\).*/\1/' | tr -d '\r\n ')

# Validate version was found
if [ -z "$VERSION" ]; then
    echo "Error: Could not extract version from plugin header."
    exit 1
fi

ZIP_NAME="${PLUGIN_NAME}-${VERSION}-wp-org.zip"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}Building WordPress.org version of ${PLUGIN_NAME} plugin v${VERSION}...${NC}"

# Clean previous builds
if [ -d "$BUILD_DIR" ]; then
    echo "Cleaning previous build..."
    rm -rf "$BUILD_DIR"
fi

# Create build directory
mkdir -p "$BUILD_DIR/$PLUGIN_NAME"

# Files to include in the plugin
echo "Copying plugin files..."

# Core plugin files
cp "$PLUGIN_DIR/datalayer-manager.php" "$BUILD_DIR/$PLUGIN_NAME/"
cp "$PLUGIN_DIR/uninstall.php" "$BUILD_DIR/$PLUGIN_NAME/"

# Copy includes directory
cp -r "$PLUGIN_DIR/includes" "$BUILD_DIR/$PLUGIN_NAME/"

# Copy languages directory
cp -r "$PLUGIN_DIR/languages" "$BUILD_DIR/$PLUGIN_NAME/"

# Copy readme.txt for WordPress.org
if [ -f "$PLUGIN_DIR/readme.txt" ]; then
    cp "$PLUGIN_DIR/readme.txt" "$BUILD_DIR/$PLUGIN_NAME/"
else
    echo -e "${YELLOW}Warning: readme.txt not found. WordPress.org requires this file.${NC}"
fi

# Remove license manager class (premium feature)
echo "Removing premium/license functionality..."
rm -f "$BUILD_DIR/$PLUGIN_NAME/includes/class-license-manager.php"

# Remove development files
echo "Cleaning development files..."
find "$BUILD_DIR/$PLUGIN_NAME" -name ".DS_Store" -delete
find "$BUILD_DIR/$PLUGIN_NAME" -name "*.log" -delete
find "$BUILD_DIR/$PLUGIN_NAME" -name ".git*" -delete

# Modify main plugin file to disable license functionality
echo "Modifying plugin files for WordPress.org compliance..."

# Set DATALAYER_MANAGER_FREE_VERSION constant to true
# Find the line with DATALAYER_MANAGER_FREE_VERSION and update it, or add it if it doesn't exist
if grep -q "DATALAYER_MANAGER_FREE_VERSION" "$BUILD_DIR/$PLUGIN_NAME/datalayer-manager.php"; then
    # Update existing constant
    sed -i.bak "s/define( 'DATALAYER_MANAGER_FREE_VERSION', false );/define( 'DATALAYER_MANAGER_FREE_VERSION', true );/" "$BUILD_DIR/$PLUGIN_NAME/datalayer-manager.php"
else
    # Add constant before DATALAYER_MANAGER_PLUGIN_FILE
    sed -i.bak "s/define( 'DATALAYER_MANAGER_PLUGIN_FILE'/define( 'DATALAYER_MANAGER_FREE_VERSION', true );\ndefine( 'DATALAYER_MANAGER_PLUGIN_FILE'/" "$BUILD_DIR/$PLUGIN_NAME/datalayer-manager.php"
fi
rm -f "$BUILD_DIR/$PLUGIN_NAME/datalayer-manager.php.bak"

# Update plugin description to remove "Premium:" mention
sed -i.bak 's/Description: Automatically detects WordPress context and injects dataLayer variables for analytics tools (GA4\/GTM). Premium: Custom variables per page\/post. No coding required./Description: Automatically detects WordPress context and injects dataLayer variables for analytics tools (GA4\/GTM). No coding required./' "$BUILD_DIR/$PLUGIN_NAME/datalayer-manager.php"
rm -f "$BUILD_DIR/$PLUGIN_NAME/datalayer-manager.php.bak"

# Create zip file
echo "Creating zip archive..."
cd "$BUILD_DIR"
zip -r "$ZIP_NAME" "$PLUGIN_NAME" -x "*.DS_Store" "*.log" ".git*" "*.bak" > /dev/null

# Move zip to plugin directory
mv "$ZIP_NAME" "$PLUGIN_DIR/"

# Clean up build directory
rm -rf "$BUILD_DIR"

echo -e "${GREEN}✓ WordPress.org build complete!${NC}"
echo -e "${YELLOW}Plugin zip: $PLUGIN_DIR/$ZIP_NAME${NC}"
echo -e "${YELLOW}Version: ${VERSION}${NC}"
echo ""
echo "Files included:"
echo "  - datalayer-manager.php (main plugin file, modified)"
echo "  - includes/ (PHP classes, license manager removed)"
echo "  - languages/ (translation files)"
echo "  - uninstall.php (cleanup script)"
echo "  - readme.txt (WordPress.org readme)"
echo ""
echo "Modifications made:"
echo "  - License manager class removed"
echo "  - Premium features disabled"
echo "  - Plugin description updated"
echo "  - Upgrade prompts made informational only"
