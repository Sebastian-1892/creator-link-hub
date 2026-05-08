#!/usr/bin/env bash
#
# Tenant wieder aktivieren: Nginx-Site aus sites-available nach sites-enabled verlinken (Gegenstück zu clh-suspend-tenant.sh).
#
set -euo pipefail

log() { echo "[clh-resume-tenant]" "$@" >&2; }
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
SITE_AVAIL="/etc/nginx/sites-available/${SITE_NAME}"
SITE_EN="/etc/nginx/sites-enabled/${SITE_NAME}"

[[ -f "$SITE_AVAIL" ]] || die_json "missing nginx site ${SITE_AVAIL} (tenant not provisioned or files removed?)"

ln -sf "$SITE_AVAIL" "$SITE_EN"
if ! nginx -t 2>/dev/null; then
  die_json "nginx -t failed after enable — prüfe ${SITE_AVAIL}"
fi
systemctl reload nginx

printf '%s\n' '{"ok":true,"resumed":true}'
log "resumed slug=$SLUG"
