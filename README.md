# AFT WordPress custom-code repository

This repository is the controlled update channel for the custom WordPress code used by Accountants For Tomorrow.

## What is tracked

- `wp-content/themes/aft/` — the AFT custom theme, including the portable Dashboard and Exam Portal navigation links.
- `wp-content/mu-plugins/aft-spam-student-cleaner.php`
- `wp-content/mu-plugins/aft-tutor-external-lesson-video.php`
- `wp-content/mu-plugins/aft-tutor-private.php`
- `wp-content/plugins/aft-migration-assistant/` — the browser/local-machine migration assistant.
- `wp-content/plugins/aft-portal-auth/` — the read-only REST endpoint the exam portal uses to verify WordPress credentials and Tutor enrolment.
- `deploy/` — manual EC2 update and verification helpers.
- `.github/workflows/` — PHP validation and repository checks.

## What is deliberately not tracked

This is a **custom-code** repository, not a public copy of the complete WordPress installation. It excludes:

- WordPress core (`wp-admin`, `wp-includes`, and root core files)
- `wp-content/uploads` and all media
- caches, logs, backups, migration inbox files, and generated artifacts
- `wp-config.php`, credentials, API keys, and provider passwords
- vendor plugins and themes, including licensed Tutor, Elementor, WooCommerce, and other third-party packages

Those items must be installed or maintained separately on EC2. This prevents credentials, private student/media data, licensed vendor packages, and large runtime files from entering Git history.

## Normal EC2 update

The repository should be checked out at the WordPress document root, for example `/var/www/html`, so the tracked paths line up with `wp-content/...`:

```bash
cd /var/www/html
bash deploy/ec2-pull-update.sh
```

The script uses `git pull --ff-only`, never deletes untracked files, runs PHP syntax checks when PHP CLI is available, activates `aft-portal-auth` when wp-cli is available, and prints the commit that was deployed. Take a database/files backup before production updates.

### After pulling: exam portal handshake

`wp-content/plugins/aft-portal-auth` registers two read-only routes
(`aft-portal/v1/verify` and `aft-portal/v1/ping`). The shared secret is
**not** stored in this repository. After the first pull, configure it once on
the server:

```bash
wp plugin activate aft-portal-auth --path=/var/www/html
wp option update aft_portal_secret 'same-value-as-WP_SHARED_SECRET-on-portal' --path=/var/www/html
```

The exam portal must be configured with the same value in its `WP_SHARED_SECRET`
environment variable, and `WP_VERIFY_URL` pointing at this site's REST root
(for example `https://example.com/wp-json`). Without a stored secret the routes
answer `503 not_configured`, so a missing secret fails closed.

If the repository is kept outside the document root, pass the WordPress root explicitly:

```bash
bash /srv/aftstaging-wordpress/deploy/ec2-pull-update.sh /var/www/html
```

## Updating this repository from the current site

Only approved custom code should be synchronized from the current site. Before committing a source-site change, compare it with this repository and update the relevant tracked path. Do not copy `wp-config.php`, uploads, backups, or third-party vendor directories into the repository.

The initial snapshot in this repository contains the latest AFT custom files available in the workspace, including the current migration plugin v1.1.1. The source site and EC2 should be reviewed before each commit because Git is not a database or media migration mechanism.

## GitHub Actions

Every push and pull request runs `.github/workflows/validate.yml`. It:

- checks all tracked PHP files with `php -l`
- rejects obvious secret/private-key patterns
- confirms the approved custom paths exist
- creates a reviewable custom-code artifact

The workflow validates changes; it does not automatically deploy to EC2. EC2 remains a manual pull as requested.

## Important operational constraints

- Keep Student Status, graduation/certificate access, registration protection, and Tutor behavior intact when changing lesson/media integrations.
- Tutor vendor files must not be edited directly. The AFT Tutor behavior belongs in the tracked MU-plugin/private update approach.
- Migration archives and database exports remain local/private and are not committed here.
