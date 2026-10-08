#!/usr/bin/env bash
# Pull the reviewed custom-code update into an EC2 WordPress document root.
# Usage: bash deploy/ec2-pull-update.sh [/var/www/html]
set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
DEFAULT_ROOT="$(cd -- "${SCRIPT_DIR}/.." && pwd)"
WP_ROOT="${1:-${DEFAULT_ROOT}}"
REMOTE="${AFT_GIT_REMOTE:-origin}"
BRANCH="${AFT_GIT_BRANCH:-main}"

if [[ ! -d "${WP_ROOT}/.git" ]]; then
  echo "ERROR: ${WP_ROOT} is not a Git checkout." >&2
  echo "Clone https://github.com/aftstaging/wordpress.git there first, or pass the checkout path." >&2
  exit 1
fi

cd "${WP_ROOT}"
if [[ -n "$(git status --porcelain --untracked-files=no)" ]]; then
  echo "ERROR: tracked local changes exist in ${WP_ROOT}. Commit or stash them before pulling." >&2
  git status --short
  exit 1
fi

echo "Fetching ${REMOTE}/${BRANCH}…"
git fetch --prune "${REMOTE}" "${BRANCH}"
BEFORE="$(git rev-parse HEAD)"
TARGET="$(git rev-parse "${REMOTE}/${BRANCH}")"
if [[ "${BEFORE}" == "${TARGET}" ]]; then
  echo "Already up to date at ${BEFORE}."
else
  git pull --ff-only "${REMOTE}" "${BRANCH}"
  echo "Updated from ${BEFORE} to $(git rev-parse HEAD)."
  git diff --stat "${BEFORE}..HEAD" || true
fi

if command -v php >/dev/null 2>&1; then
  echo "Checking tracked PHP files…"
  while IFS= read -r file; do
    php -l "${file}" >/dev/null
  done < <(git ls-files '*.php')
  echo "PHP syntax checks passed."
else
  echo "NOTICE: PHP CLI is not installed; skipped PHP syntax checks."
fi

if command -v wp >/dev/null 2>&1; then
  if wp plugin is-active aft-portal-auth --path="${WP_ROOT}" >/dev/null 2>&1; then
    echo "Plugin aft-portal-auth is already active."
  elif [[ -f "${WP_ROOT}/wp-content/plugins/aft-portal-auth/aft-portal-auth.php" ]]; then
    wp plugin activate aft-portal-auth --path="${WP_ROOT}"
    echo "Plugin aft-portal-auth activated."
  fi

  if [[ -z "$(wp option get aft_portal_secret --path="${WP_ROOT}" 2>/dev/null || true)" ]]; then
    echo "WARNING: aft_portal_secret is not set; the exam portal verify endpoint will answer 503 not_configured." >&2
    echo "         Set it with: wp option update aft_portal_secret '<shared secret>' --path=${WP_ROOT}" >&2
  fi
else
  echo "NOTICE: wp-cli is not installed; activate aft-portal-auth manually after the first pull."
fi

if [[ "${AFT_FLUSH_CACHE:-0}" == "1" ]] && command -v wp >/dev/null 2>&1; then
  wp cache flush --path="${WP_ROOT}"
fi

echo "AFT custom-code update complete. Current commit: $(git rev-parse --short HEAD)"
