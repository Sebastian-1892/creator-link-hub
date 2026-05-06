#!/usr/bin/env bash
#
# Tenant deaktivieren: Nginx-Site aus, Dateien/DB bleiben.
#
set -euo pipefail

log() { echo "[clh-suspend-tenant]" "$@" >&2; }
die_json() {
  msg=$(printf '%s' "$*" | sed 's/\\/\\\\/g; s/"/\\"/g')
  printf '%s\n' "{\"error\":\"${msg}\"}"
  exit 1
}

SLUG=""
TENANT_ROOT="/var/www/clh-tenants"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --slug) SLUG="${2:-}"; shift 2 ;;
    --domain) _D="${2:-}"; shift 2 ;; # API-Kompatibilität
    --tenant-root) TENANT_ROOT="${2:-}"; shift 2 ;;
    --db-driver) shift 2 ;;
    *) die_json "unknown argument: $1" ;;
  esac
done

[[ -n "$SLUG" ]] || die_json "missing --slug"
[[ "$SLUG" =~ ^[a-z0-9]([a-z0-9-]{1,30}[a-z0-9])?$ ]] || die_json "invalid slug"

SITE_NAME="clh-${SLUG}.conf"
SITE_EN="/etc/nginx/sites-enabled/${SITE_NAME}"
rm -f "$SITE_EN" 2>/dev/null || true
nginx -t 2>/dev/null && systemctl reload nginx || true

SUP="/etc/supervisor/conf.d/clh-worker-${SLUG}.conf"
if [[ -f "$SUP" ]]; then
  rm -f "$SUP"
  supervisorctl reread 2>/dev/null || true
  supervisorctl update 2>/dev/null || true
fi

printf '%s\n' '{"ok":true,"suspended":true}'
log "suspended slug=$SLUG"
