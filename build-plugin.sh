#!/bin/bash

# Build script for Scripts + Pixels DataLayer Manager (Pro/full version)
# Creates a clean zip file ready for distribution

PLUGIN_NAME="datalayer-manager"
PLUGIN_SLUG="scripts-and-pixels-datalayer-manager"
PLUGIN_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BUILD_DIR="$PLUGIN_DIR/build"
PLUGIN_FILE="$PLUGIN_DIR/${PLUGIN_NAME}.php"

# Extract version from plugin header
VERSION=$(grep -i "Version:" "$PLUGIN_FILE" | head -1 | sed -e 's/.*[Vv]ersion:[[:space:]]*\([0-9.]*\).*/\1/' | tr -d '\r\n ')

# Validate version was found
if [ -z "$VERSION" ]; then
    echo "Error: Could not extract version from plugin header."
    exit 1
fi

# Zip filename uses public slug for consistency with plugin name and WP.org
ZIP_NAME="${PLUGIN_SLUG}-${VERSION}.zip"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Building ${PLUGIN_SLUG} v${VERSION}...${NC}"

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

# Include directories
cp -r "$PLUGIN_DIR/includes" "$BUILD_DIR/$PLUGIN_NAME/"
cp -r "$PLUGIN_DIR/languages" "$BUILD_DIR/$PLUGIN_NAME/"

# wp-assets/ is for WordPress.org SVN assets/ (banners, icons), not distribution zips
rm -rf "$BUILD_DIR/$PLUGIN_NAME/wp-assets"

# Optional documentation (uncomment if you want to include)
# cp "$PLUGIN_DIR/README.md" "$BUILD_DIR/$PLUGIN_NAME/"
# cp "$PLUGIN_DIR/CHANGELOG.md" "$BUILD_DIR/$PLUGIN_NAME/"

# Remove development files if they exist
echo "Cleaning development files..."
find "$BUILD_DIR/$PLUGIN_NAME" -name ".DS_Store" -delete
find "$BUILD_DIR/$PLUGIN_NAME" -name "*.log" -delete
find "$BUILD_DIR/$PLUGIN_NAME" -name ".git*" -delete

# Create zip file
echo "Creating zip archive..."
cd "$BUILD_DIR"
zip -r "$ZIP_NAME" "$PLUGIN_NAME" -x "*.DS_Store" "*.log" ".git*" "*/wp-assets/*" > /dev/null

# Move zip to plugin directory
mv "$ZIP_NAME" "$PLUGIN_DIR/"

# Clean up build directory
rm -rf "$BUILD_DIR"

echo -e "${GREEN}✓ Build complete!${NC}"
echo -e "${YELLOW}Plugin zip: $PLUGIN_DIR/$ZIP_NAME${NC}"
echo -e "${YELLOW}Version: ${VERSION}${NC}"
echo ""
echo "Files included:"
echo "  - datalayer-manager.php (main plugin file)"
echo "  - includes/ (all PHP classes)"
echo "  - languages/ (translation files)"
echo "  - uninstall.php (cleanup script)"
echo ""
echo "Files excluded:"
echo "  - license-api-endpoint.php (marketing site only)"
echo "  - LICENSE-API-SETUP.md (setup docs)"
echo "  - .gitignore and other dev files"
