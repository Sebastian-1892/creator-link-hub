#!/usr/bin/env bash
#
# VPS: Host komplett aktualisieren (GitHub → Klon, Provisioner, Skripte, Landingpages).
# Hochladen z. B. nach ~/clh-update-host.sh, dann: sudo bash ~/clh-update-host.sh
# Optional: sudo bash ~/clh-update-host.sh --with-zip
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

UPDATER=/usr/local/bin/clh-cloud-host-update.sh
if [[ ! -x "$UPDATER" ]]; then
  UPDATER="$CLH_REPO_ROOT/scripts/clh-cloud-host-update.sh"
fi

[[ -x "$UPDATER" ]] || {
  echo "Fehler: weder /usr/local/bin/clh-cloud-host-update.sh noch $UPDATER gefunden." >&2
  exit 1
}

exec "$UPDATER" "$@"
