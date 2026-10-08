AFT Migration Assistant
========================

Version 1.1.1

Purpose
-------
This is an admin-only WordPress plugin for moving a site through a local-machine
handoff:

1. Install and activate this plugin on both the current/source site and the
   destination site.
2. On the source site, open Tools > AFT Migration.
3. Download the database and content archives. The browser downloads them to
   the administrator's local computer; no third-party transfer service is used.
4. On the destination site, open Tools > AFT Migration.
5. Import the database first. Enter the exact source URL stored in the source
   database and the current destination URL. The browser uploader sends the
   file in small authenticated chunks, then the plugin imports the SQL and
   performs a serialized-data-safe URL replacement.
6. Import either the complete content bundle or each separate ZIP archive. ZIP
   uploads also use small chunks and content extraction runs in short progress
   steps, so the browser does not hold one large request open.
7. Log in again if the source database replaced the destination users. Check
   Settings > Permalinks and save once, then test the site.

Downloads
---------
- Database: compressed SQL containing all tables visible to the WordPress DB
  user. The export records the source table prefix and the importer maps it to
  the destination WordPress table prefix.
- Complete content bundle: wp-content/uploads, themes, plugins, and
  mu-plugins.
- Separate archives: uploads, themes, plugins, and mu-plugins.
- Content ZIPs are streamed directly to the browser using ZIP store mode and
  continuous flushing. This avoids building a large temporary archive before
  the download starts and prevents hosting connection timeouts. Media and
  plugin files are usually already compressed, so CPU-heavy recompression is
  not useful.

Security and operational notes
------------------------------
- All screens and actions require a logged-in user with manage_options and a
  WordPress nonce.
- Keep the downloaded database and ZIP files private. They may contain user
  records, settings, private media, and credentials stored by plugins.
- Take a backup of the destination before importing. Existing files with the
  same path are overwritten, and destination database tables with the same
  names are dropped and recreated by the SQL dump.
- Do not leave this plugin active on a public site after the migration unless
  you still need it. Deactivate it after validation.
- The PHP ZipArchive extension is required for content imports.
- Destination uploads use 1 MB browser chunks, so the normal PHP/web-server
  request limit only needs to permit a small chunk rather than the entire
  database or archive. The browser still performs the final upload from the
  local machine.
- The database and archive processing still requires sufficient disk space on
  the destination. The temporary files are stored in
  wp-content/uploads/aft-migration-inbox and are removed after a successful
  import. If an import is abandoned, remove old .part and .job.json files from
  that directory after checking that no migration is running.
- This plugin does not change DNS, cPanel settings, email, external API
  credentials, or provider-hosted media URLs. Validate those separately.
- The first migration can target the IP URL. After DNS cutover, use the
  URL-replacement-only form to change the IP URL to the final HTTPS domain.

Compatibility
-------------
Requires WordPress 5.8+, PHP 7.4+, and ZipArchive for content exports/imports.
The plugin does not edit vendor plugin files.
