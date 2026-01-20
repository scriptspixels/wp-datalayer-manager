# GitHub Best Practices for Plugin Distribution

## Current Setup

Your `.gitignore` already excludes `*.zip` files (line 34), which is **correct** for the main repository.

## Recommended Approach: GitHub Releases

**Best Practice:** Use GitHub Releases for distribution files instead of committing them to the repository.

### Why GitHub Releases?

1. ✅ **Clean Repository** - Keeps your main branch focused on source code
2. ✅ **Version Management** - Each release is tagged with a version number
3. ✅ **Download Tracking** - GitHub tracks download statistics
4. ✅ **Release Notes** - Document changes for each version
5. ✅ **Automatic Updates** - Users can find updates easily
6. ✅ **No Repository Bloat** - Zip files don't clutter your git history

### How to Use GitHub Releases

1. **Create a Release:**
   - Go to your GitHub repository
   - Click "Releases" → "Create a new release"
   - Tag: `v1.0.0` (matches your plugin version)
   - Title: `DataLayer Manager 1.0.0`
   - Description: Add release notes, changelog, etc.

2. **Upload Files:**
   - Upload `datalayer-manager-1.0.0.zip` (premium version for Lemon Squeezy)
   - Upload `datalayer-manager-1.0.0-wp-org.zip` (WordPress.org version)
   - Both files will be available for download

3. **Future Releases:**
   - When you update to 1.0.1, create a new release with tag `v1.0.1`
   - Upload the new zip files
   - Users can see all versions in one place

### Alternative: Releases Directory (If You Must Commit)

If you need to commit zip files to the repository (not recommended), you can:

1. **Update `.gitignore`** to exclude zip files EXCEPT in a releases directory:
   ```gitignore
   # Build / Distribution
   *.zip
   !releases/*.zip
   *.tar.gz
   build/
   dist/
   release/
   ```

2. **Create releases directory:**
   ```bash
   mkdir releases
   cp datalayer-manager-1.0.0.zip releases/
   cp datalayer-manager-1.0.0-wp-org.zip releases/
   ```

3. **Commit the releases directory:**
   ```bash
   git add releases/
   git commit -m "Add release files for v1.0.0"
   ```

**⚠️ Note:** This approach bloats your repository over time. GitHub Releases is preferred.

## Recommended Workflow

### For Each Release:

1. **Build both versions:**
   ```bash
   ./build-plugin.sh          # Creates premium version
   ./build-wp-org.sh          # Creates WordPress.org version
   ```

2. **Test both zip files** in clean WordPress installations

3. **Create GitHub Release:**
   - Tag: `v1.0.0`
   - Upload both zip files
   - Add release notes

4. **Don't commit zip files** to the repository (they're already ignored)

## Current Files

You have two zip files:
- `datalayer-manager-1.0.0.zip` - Premium version (for Lemon Squeezy)
- `datalayer-manager-1.0.0-wp-org.zip` - WordPress.org version

**Recommendation:** Upload both to GitHub Releases, not to the repository.

## Summary

✅ **DO:**
- Use GitHub Releases for distribution files
- Keep zip files out of the repository (already done via .gitignore)
- Tag releases with version numbers (`v1.0.0`)
- Include release notes and changelog

❌ **DON'T:**
- Commit zip files to the main branch
- Remove `*.zip` from `.gitignore` (unless using releases/ directory)
- Mix source code and distribution files
