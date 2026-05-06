#!/usr/bin/env bash
#
# Tenant entfernen: DB, Nginx-Site, Dateien.
#
set -euo pipefail

log() { echo "[clh-delete-tenant]" "$@" >&2; }
die_json() {
  log "ERROR: $*"
  msg=$(printf '%s' "$*" | sed 's/\\/\\\\/g; s/"/\\"/g')
  printf '%s\n' "{\"error\":\"${msg}\"}"
  exit 1
}

SLUG=""
DOMAIN=""
TENANT_ROOT="/var/www/clh-tenants"
DB_DRIVER="mysql"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --slug) SLUG="${2:-}"; shift 2 ;;
    --domain) DOMAIN="${2:-}"; shift 2 ;;
    --tenant-root) TENANT_ROOT="${2:-}"; shift 2 ;;
    --db-driver) DB_DRIVER="${2:-}"; shift 2 ;;
    *) die_json "unknown argument: $1" ;;
  esac
done

[[ -n "$SLUG" ]] || die_json "missing --slug"
[[ "$SLUG" =~ ^[a-z0-9]([a-z0-9-]{1,30}[a-z0-9])?$ ]] || die_json "invalid slug"
[[ "$DB_DRIVER" == "mysql" ]] || die_json "only mysql supported"

DB_SLUG="${SLUG//-/_}"
DB_NAME="clh_${DB_SLUG}"
DB_USER="clh_${DB_SLUG}"
INSTALL_DIR="${TENANT_ROOT%/}/${SLUG}"
SITE_NAME="clh-${SLUG}.conf"
SITE_AVAIL="/etc/nginx/sites-available/${SITE_NAME}"
SITE_EN="/etc/nginx/sites-enabled/${SITE_NAME}"

log "remove nginx"
rm -f "$SITE_EN" 2>/dev/null || true
rm -f "$SITE_AVAIL" 2>/dev/null || true
nginx -t 2>/dev/null && systemctl reload nginx || true

log "drop mysql $DB_NAME"
mysql -u root <<EOSQL || true
DROP DATABASE IF EXISTS \`${DB_NAME}\`;
DROP USER IF EXISTS '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOSQL

log "remove files $INSTALL_DIR"
if [[ -d "$INSTALL_DIR" ]]; then
  rm -rf "$INSTALL_DIR"
fi

printf '%s\n' '{"ok":true}'
log "done slug=$SLUG"
