#!/usr/bin/env bash
#
# Cloud-App-VPS: GitHub-Stand in den Host-Klon holen, dann ALLE Tenant-Slugs unter
# tenants_root mit diesem Stand synchronisieren und je Instanz scripts/update-application.sh
# ausführen (Composer, npm, Migrationen, Caches).
#
# Voraussetzungen:
#   • Als root ausführen
#   • /etc/clh-provisioner/install-paths.env mit CLH_REPO_ROOT (wie clh-cloud-host-update.sh)
#   • Optional /etc/clh-provisioner/config.json → tenants_root (sonst /var/www/clh-tenants)
#
# Aufruf:
#   sudo /usr/local/bin/clh-rollout-all-tenants.sh
#   sudo /usr/local/bin/clh-rollout-all-tenants.sh --skip-host-update   # Repo bereits aktuell, nur Tenants
#   sudo /usr/local/bin/clh-rollout-all-tenants.sh --with-zip           # wie clh-cloud-host-update --with-zip
#
# Hinweis: .env, storage/ und bootstrap/cache/ pro Tenant werden nicht überschrieben (rsync --exclude).
#
# update-application.sh ermittelt das Laravel-ROOT über den Pfad zur Skriptdatei — es muss jeweils die
# Kopie unter $tenant_dir/scripts/ ausgeführt werden, nicht die unter CLH_REPO_ROOT.
#
set -euo pipefail

die() { echo "[clh-rollout] Fehler: $*" >&2; exit 1; }
log() { echo "[clh-rollout] $*"; }

[[ "${EUID:-0}" -eq 0 ]] || die "Bitte als root: sudo $0"

PATH_CFG=/etc/clh-provisioner/install-paths.env
[[ -f "$PATH_CFG" ]] || die "Konfiguration fehlt: $PATH_CFG — bootstrap-cloud-host.sh oder Datei manuell anlegen."

# shellcheck disable=SC1090
source "$PATH_CFG"
: "${CLH_REPO_ROOT:?In $PATH_CFG muss CLH_REPO_ROOT gesetzt sein}"

CONFIG_JSON=/etc/clh-provisioner/config.json
TENANTS_ROOT="/var/www/clh-tenants"
if [[ -f "$CONFIG_JSON" ]] && command -v python3 >/dev/null 2>&1; then
  TR=$(python3 -c "import json; print(json.load(open('$CONFIG_JSON')).get('tenants_root','/var/www/clh-tenants'))" 2>/dev/null) || true
  [[ -n "${TR:-}" ]] && TENANTS_ROOT="$TR"
fi

SKIP_HOST=0
WITH_ZIP=0
for arg in "$@"; do
  case "$arg" in
    --skip-host-update) SKIP_HOST=1 ;;
    --with-zip) WITH_ZIP=1 ;;
    -h|--help)
      sed -n '2,20p' "$0" | sed 's/^# \{0,1\}//'
      exit 0
      ;;
    *) die "Unbekannte Option: $arg (siehe $0 --help)" ;;
  esac
done

HOST_UPDATER=/usr/local/bin/clh-cloud-host-update.sh
if [[ "$SKIP_HOST" -eq 0 ]]; then
  [[ -x "$HOST_UPDATER" ]] || die "$HOST_UPDATER nicht gefunden oder nicht ausführbar — zuerst clh-cloud-host-update deployen."
  log "Schritt 1/2: Host-Update (git pull, Provisioner, Skripte$( [[ "$WITH_ZIP" -eq 1 ]] && echo ', ZIP' )…) …"
  if [[ "$WITH_ZIP" -eq 1 ]]; then
    "$HOST_UPDATER" --with-zip
  else
    "$HOST_UPDATER"
  fi
  # CLH_REPO_ROOT nach Pull erneut einlesen (falls install-paths.env sich ändert — selten)
  # shellcheck disable=SC1090
  source "$PATH_CFG"
  : "${CLH_REPO_ROOT:?}"
fi

[[ -d "$CLH_REPO_ROOT" ]] || die "CLH_REPO_ROOT existiert nicht: $CLH_REPO_ROOT"
[[ -f "$CLH_REPO_ROOT/composer.json" && -f "$CLH_REPO_ROOT/artisan" ]] || die "Kein Laravel-Projekt unter $CLH_REPO_ROOT"
[[ -d "$TENANTS_ROOT" ]] || die "tenants_root existiert nicht: $TENANTS_ROOT"

[[ -f "$CLH_REPO_ROOT/scripts/update-application.sh" ]] || die "update-application.sh fehlt im Repo: $CLH_REPO_ROOT/scripts/update-application.sh"

log "Schritt 2/2: Tenants unter $TENANTS_ROOT …"

count=0
mapfile -d '' tenant_dirs < <(find "$TENANTS_ROOT" -mindepth 1 -maxdepth 1 -type d ! -name '.*' -print0 | sort -z)

if [[ "${#tenant_dirs[@]}" -eq 0 ]]; then
  log "Keine Unterverzeichnisse unter $TENANTS_ROOT — nichts zu tun."
  exit 0
fi

for tenant_dir in "${tenant_dirs[@]}"; do
  [[ -n "$tenant_dir" ]] || continue
  slug=$(basename "$tenant_dir")

  if [[ ! -f "$tenant_dir/composer.json" || ! -f "$tenant_dir/artisan" ]]; then
    log "Überspringe (kein Laravel: composer.json/artisan fehlt): $slug"
    continue
  fi

  log "—— Tenant: $slug ——"
  rsync -a --delete \
    --exclude '.env' \
    --exclude 'storage/' \
    --exclude 'bootstrap/cache/' \
    "$CLH_REPO_ROOT/" \
    "$tenant_dir/"

  chown -R www-data:www-data "$tenant_dir"

  tenant_update="$tenant_dir/scripts/update-application.sh"
  [[ -f "$tenant_update" ]] || die "update-application.sh fehlt nach rsync: $tenant_update"

  log "update-application.sh (Tenant-ROOT über Skriptpfad) …"
  ( cd "$tenant_dir" && bash "$tenant_update" ) || die "update-application.sh fehlgeschlagen für Tenant: $slug"

  count=$((count + 1))
done

log "Fertig. Laravel-Tenants erfolgreich aktualisiert: $count"
echo ""
