#!/bin/bash

# Build script for WordPress.org version of Scripts + Pixels DataLayer Manager
# Uses WP.org slug as folder name; strips premium/license for WP.org compliance.

PLUGIN_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
WP_ORG_SLUG="scripts-and-pixels-datalayer-manager"
BUILD_DIR="$PLUGIN_DIR/build-wp-org"
MAIN_FILE_SRC="$PLUGIN_DIR/datalayer-manager.php"

# Extract version from plugin header
VERSION=$(grep -i "Version:" "$MAIN_FILE_SRC" | head -1 | sed -e 's/.*[Vv]ersion:[[:space:]]*\([0-9.]*\).*/\1/' | tr -d '\r\n ')

# Validate version was found
if [ -z "$VERSION" ]; then
    echo "Error: Could not extract version from plugin header."
    exit 1
fi

ZIP_NAME="${WP_ORG_SLUG}-${VERSION}-wp-org.zip"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}Building WordPress.org version (${WP_ORG_SLUG}) v${VERSION}...${NC}"

# Clean previous builds
if [ -d "$BUILD_DIR" ]; then
    echo "Cleaning previous build..."
    rm -rf "$BUILD_DIR"
fi

# Create build directory with WP.org slug as folder name
mkdir -p "$BUILD_DIR/$WP_ORG_SLUG"

# Files to include in the plugin
echo "Copying plugin files..."

# Main plugin file: copy and rename to match slug (WP.org expects plugin-slug/plugin-slug.php)
cp "$MAIN_FILE_SRC" "$BUILD_DIR/$WP_ORG_SLUG/${WP_ORG_SLUG}.php"
cp "$PLUGIN_DIR/uninstall.php" "$BUILD_DIR/$WP_ORG_SLUG/"

# Copy includes and languages
cp -r "$PLUGIN_DIR/includes" "$BUILD_DIR/$WP_ORG_SLUG/"
cp -r "$PLUGIN_DIR/languages" "$BUILD_DIR/$WP_ORG_SLUG/"

if [ -f "$PLUGIN_DIR/readme.txt" ]; then
    cp "$PLUGIN_DIR/readme.txt" "$BUILD_DIR/$WP_ORG_SLUG/"
else
    echo -e "${YELLOW}Warning: readme.txt not found. WordPress.org requires this file.${NC}"
fi

# Remove license manager class (premium feature)
echo "Removing premium/license functionality..."
rm -f "$BUILD_DIR/$WP_ORG_SLUG/includes/class-license-manager.php"

# Remove custom variables class (Guideline 5: no locked/trialware features in WP.org build)
echo "Removing custom variables (Pro-only) for WP.org compliance..."
rm -f "$BUILD_DIR/$WP_ORG_SLUG/includes/class-datalayer-manager-custom-variables.php"

# Remove development files
echo "Cleaning development files..."
find "$BUILD_DIR/$WP_ORG_SLUG" -name ".DS_Store" -delete
find "$BUILD_DIR/$WP_ORG_SLUG" -name "*.log" -delete
find "$BUILD_DIR/$WP_ORG_SLUG" -name ".git*" -delete

# Modify main plugin file to disable license functionality
MAIN_FILE="$BUILD_DIR/$WP_ORG_SLUG/${WP_ORG_SLUG}.php"
echo "Modifying plugin files for WordPress.org compliance..."

if grep -q "DATALAYER_MANAGER_FREE_VERSION" "$MAIN_FILE"; then
    sed -i.bak "s/define( 'DATALAYER_MANAGER_FREE_VERSION', false );/define( 'DATALAYER_MANAGER_FREE_VERSION', true );/" "$MAIN_FILE"
else
    sed -i.bak "s/define( 'DATALAYER_MANAGER_PLUGIN_FILE'/define( 'DATALAYER_MANAGER_FREE_VERSION', true );\ndefine( 'DATALAYER_MANAGER_PLUGIN_FILE'/" "$MAIN_FILE"
fi
rm -f "$MAIN_FILE.bak"

# Free build description: do not advertise custom variables (not in WP.org build).
sed -i.bak "s/Custom variables per page\/post. //" "$MAIN_FILE"
rm -f "$MAIN_FILE.bak"

# Create zip file
echo "Creating zip archive..."
cd "$BUILD_DIR"
zip -r "$ZIP_NAME" "$WP_ORG_SLUG" -x "*.DS_Store" "*.log" ".git*" "*.bak" > /dev/null

mv "$ZIP_NAME" "$PLUGIN_DIR/"
rm -rf "$BUILD_DIR"

echo -e "${GREEN}✓ WordPress.org build complete!${NC}"
echo -e "${YELLOW}Plugin zip: $PLUGIN_DIR/$ZIP_NAME${NC}"
echo -e "${YELLOW}Version: ${VERSION} | Slug: ${WP_ORG_SLUG}${NC}"
echo ""
echo "Files included:"
echo "  - ${WP_ORG_SLUG}.php (main plugin file, FREE_VERSION=true)"
echo "  - includes/ (license manager removed)"
echo "  - languages/"
echo "  - uninstall.php"
echo "  - readme.txt"
echo ""
echo "Modifications: License manager removed, premium disabled (free version)."
