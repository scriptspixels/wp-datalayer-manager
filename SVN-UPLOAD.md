# Uploading DataLayer Manager to WordPress.org via SVN

Step-by-step guide to push the plugin to the WordPress.org Plugin Directory using Subversion.

## Prerequisites

- **Subversion** installed (`svn --version`).
- **WordPress.org username** (case-sensitive) and **SVN password** set at  
  [WordPress.org → Profile → SVN Password](https://profiles.wordpress.org/me/profile/edit/group/3/?screen=svn-password).
- Plugin **accepted** and SVN repo created (you receive the repo URL when the plugin is approved).

---

## Step 1: Plugin slug and URLs

- **SVN URL:** `https://plugins.svn.wordpress.org/scripts-and-pixels-datalayer-manager`
- **Public URL:** `https://wordpress.org/plugins/scripts-and-pixels-datalayer-manager`
- **Slug:** `scripts-and-pixels-datalayer-manager`

The main plugin file in `trunk` must be `scripts-and-pixels-datalayer-manager.php`. The wp-org zip already uses this name, so no renaming is needed.

---

## Step 2: Checkout the SVN repository

From a directory **outside** your plugin (e.g. your home or a `wp-plugins-svn` folder):

```bash
svn co https://plugins.svn.wordpress.org/scripts-and-pixels-datalayer-manager scripts-and-pixels-datalayer-manager-svn
cd scripts-and-pixels-datalayer-manager-svn
```

You should see `trunk/`, `tags/`, and `assets/`. Do not work from inside your Git plugin folder; use this separate SVN checkout.

---

## Step 3: Prepare the files that go in trunk

Only the **WordPress.org (free) build** should go into SVN. Use the existing build script so license/custom-variables are stripped and the free version is correct.

**3a. Build the WordPress.org zip** (from your Git plugin directory):

```bash
cd /path/to/datalayer-manager
./build-wp-org.sh
```

This creates e.g. `scripts-and-pixels-datalayer-manager-1.0.0-wp-org.zip`.

**3b. Unzip and copy into the SVN trunk**

Unzip the wp-org zip somewhere temporary, then copy the **contents** of the inner folder into your SVN `trunk/` (so the main PHP file and `includes/`, `languages/`, etc. are directly inside `trunk/`). Only the contents of this zip are uploaded to WordPress.org—nothing else.

```bash
# From the directory where the zip was created (datalayer-manager plugin folder)
unzip -q scripts-and-pixels-datalayer-manager-*-wp-org.zip -d /tmp/wp-org-build
cp -R /tmp/wp-org-build/scripts-and-pixels-datalayer-manager/* /path/to/scripts-and-pixels-datalayer-manager-svn/trunk/
```

Replace `/path/to/scripts-and-pixels-datalayer-manager-svn` with the path to your SVN checkout from Step 2. No renaming is needed; the main file is already `scripts-and-pixels-datalayer-manager.php`.

---

## Step 4: Add and commit trunk

From the root of your SVN checkout:

```bash
cd /path/to/scripts-and-pixels-datalayer-manager-svn
svn add trunk/*
svn status
```

Review the list; only the plugin files you want on WordPress.org should be added. Then commit:

```bash
svn ci -m "Initial release (1.0.0)" --username YOUR_WP_ORG_USERNAME
```

If prompted, use your WordPress.org **SVN password**. Username is case-sensitive.

---

## Step 5: Tag the release

WordPress.org uses **tags** for versions. Create a tag from trunk (e.g. for 1.0.0):

```bash
svn cp trunk tags/1.0.0
svn ci -m "Tagging version 1.0.0" --username YOUR_WP_ORG_USERNAME
```

Use the same version number as in your plugin header and `readme.txt` Stable tag.

---

## Step 6: Assets (optional, can do later)

Screenshots, banner, and icons go in `assets/`, not in trunk. See [Plugin assets](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/). You can add these in a follow-up commit.

---

## What we’re excluding (no need to upload)

The repo has a **`.distignore`** file that lists what **not** to put in SVN. In short, do **not** upload:

- `.git/`, `.gitignore`, `.distignore`
- Build scripts (`build-wp-org.sh`, `build-plugin.sh`)
- Dev docs (`README.md`, `CHANGELOG.md`, internal `.md` files)
- IDE/OS junk (`.DS_Store`, `.vscode/`, etc.)
- `node_modules/`, `vendor/`, `.env`, `*.log`

Using **build-wp-org.sh** and copying only the **unzipped build output** into trunk ensures the right files are included and everything else is excluded.

---

## Quick reference: later updates

1. Update version in `datalayer-manager.php` and `readme.txt` (Stable tag).
2. Run `./build-wp-org.sh` in the plugin directory.
3. Unzip the new wp-org zip and copy contents into `scripts-and-pixels-datalayer-manager-svn/trunk/` (overwrite).
4. Commit trunk: `svn ci -m "Release 1.0.1"`.
5. Tag: `svn cp trunk tags/1.0.1` then `svn ci -m "Tagging 1.0.1"`.

Only push when you have a **finished** release; avoid many small SVN commits.
