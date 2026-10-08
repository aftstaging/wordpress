#!/usr/bin/env bash
# Copy only approved custom code from a local WordPress tree into this repo.
# Usage: bash scripts/sync-custom-code.sh /path/to/wordpress
set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd -- "${SCRIPT_DIR}/.." && pwd)"
SOURCE_ROOT="${1:-}"

if [[ -z "${SOURCE_ROOT}" || ! -d "${SOURCE_ROOT}/wp-content" ]]; then
  echo "Usage: $0 /path/to/source/wordpress" >&2
  exit 2
fi
SOURCE_ROOT="$(cd -- "${SOURCE_ROOT}" && pwd)"

command -v rsync >/dev/null 2>&1 || { echo "ERROR: rsync is required." >&2; exit 1; }

mkdir -p "${REPO_ROOT}/wp-content/themes/aft" "${REPO_ROOT}/wp-content/plugins/aft-migration-assistant" "${REPO_ROOT}/wp-content/plugins/aft-portal-auth" "${REPO_ROOT}/wp-content/mu-plugins"

rsync -a --delete "${SOURCE_ROOT}/wp-content/themes/aft/" "${REPO_ROOT}/wp-content/themes/aft/"
rsync -a --delete "${SOURCE_ROOT}/wp-content/plugins/aft-migration-assistant/" "${REPO_ROOT}/wp-content/plugins/aft-migration-assistant/"
rsync -a --delete "${SOURCE_ROOT}/wp-content/plugins/aft-portal-auth/" "${REPO_ROOT}/wp-content/plugins/aft-portal-auth/"

for file in \
  aft-spam-student-cleaner.php \
  aft-tutor-external-lesson-video.php \
  aft-tutor-private.php; do
  if [[ -f "${SOURCE_ROOT}/wp-content/mu-plugins/${file}" ]]; then
    cp "${SOURCE_ROOT}/wp-content/mu-plugins/${file}" "${REPO_ROOT}/wp-content/mu-plugins/${file}"
  else
    echo "NOTICE: source file not found: wp-content/mu-plugins/${file}" >&2
  fi
done

cd "${REPO_ROOT}"
echo "Approved custom code copied from ${SOURCE_ROOT}. Review this diff before committing:"
git status --short
git diff --stat
