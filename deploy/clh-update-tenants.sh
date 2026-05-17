#!/usr/bin/env bash
#
# VPS: Alle Tenants unter /var/www/clh-tenants/ auf Repo-Stand bringen (rsync + update-application.sh).
# Hochladen z. B. nach ~/clh-update-tenants.sh
#
#   sudo bash ~/clh-update-tenants.sh              # Host-Update + alle Tenants
#   sudo bash ~/clh-update-tenants.sh --with-zip   # inkl. neuem current.zip
#   sudo bash ~/clh-update-tenants.sh --skip-host-update   # nur Tenants
#
set -euo pipefail

[[ "${EUID:-0}" -eq 0 ]] || exec sudo bash "$0" "$@"

PATH_CFG=/etc/clh-provisioner/install-paths.env
CLH_REPO_ROOT="${CLH_REPO_ROOT:-/opt/creator-link-hub-src/creator-link-hub}"

if [[ -f "$PATH_CFG" ]]; then
  # shellcheck disable=SC1090
  source "$PATH_CFG"
fi

if [[ -d "$CLH_REPO_ROOT/.git" ]]; then
  git config --global --add safe.directory "$CLH_REPO_ROOT" 2>/dev/null || true
fi

ROLLOUT=/usr/local/bin/clh-rollout-all-tenants.sh
if [[ ! -x "$ROLLOUT" ]]; then
  ROLLOUT="$CLH_REPO_ROOT/scripts/clh-rollout-all-tenants.sh"
fi

[[ -x "$ROLLOUT" ]] || {
  echo "Fehler: weder /usr/local/bin/clh-rollout-all-tenants.sh noch $ROLLOUT gefunden." >&2
  exit 1
}

exec "$ROLLOUT" "$@"
