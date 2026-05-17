#!/usr/bin/env bash
#
# Tenant suspendieren: Original-Nginx-Site aus sites-enabled nehmen, Maintenance-vhost aktivieren.
# sites-available/clh-SLUG.conf bleibt unverändert (Resume stellt nur den Symlink wieder her).
#
# Umgebung:
#   CLH_SUSPENDED_ROOT — statische Maintenance-Seite (default: /var/www/clh-suspended)
#
set -euo pipefail

log() { echo "[clh-suspend-tenant]" "$@" >&2; }
die_json() {
  msg=$(printf '%s' "$*" | sed 's/\\/\\\\/g; s/"/\\"/g')
  printf '%s\n' "{\"error\":\"${msg}\"}"
  exit 1
}

rollback_suspend_vhost() {
  rm -f "$SUSP_EN" 2>/dev/null || true
}

SLUG=""
DOMAIN=""
TENANT_ROOT="/var/www/clh-tenants"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --slug) SLUG="${2:-}"; shift 2 ;;
    --domain) DOMAIN="${2:-}"; shift 2 ;;
    --tenant-root) TENANT_ROOT="${2:-}"; shift 2 ;;
    --db-driver) shift 2 ;;
    *) die_json "unknown argument: $1" ;;
  esac
done

[[ -n "$SLUG" ]] || die_json "missing --slug"
[[ -n "$DOMAIN" ]] || die_json "missing --domain"
[[ "$SLUG" =~ ^[a-z0-9]([a-z0-9-]{1,30}[a-z0-9])?$ ]] || die_json "invalid slug"

MAINT_ROOT="${CLH_SUSPENDED_ROOT:-/var/www/clh-suspended}"
SITE_NAME="clh-${SLUG}.conf"
SITE_AVAIL="/etc/nginx/sites-available/${SITE_NAME}"
SITE_EN="/etc/nginx/sites-enabled/${SITE_NAME}"
SUSP_EN="/etc/nginx/sites-enabled/clh-${SLUG}-suspended.conf"
ACME_ROOT="${TENANT_ROOT%/}/${SLUG}/public"
LE_CERT="/etc/letsencrypt/live/${DOMAIN}/fullchain.pem"
LE_KEY="/etc/letsencrypt/live/${DOMAIN}/privkey.pem"

[[ -f "${MAINT_ROOT}/index.html" ]] || die_json "maintenance page missing: ${MAINT_ROOT}/index.html (run bootstrap or clh-cloud-host-update)"

# Original-Symlink entfernen (Konfiguration in sites-available bleibt)
rm -f "$SITE_EN" 2>/dev/null || true

# Maintenance-vhost schreiben
NG_DH_LINE=""
[[ -f /etc/letsencrypt/ssl-dhparams.pem ]] && NG_DH_LINE=$'    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;\n'

if [[ -f "$LE_CERT" && -f "$LE_KEY" ]]; then
  cat >"$SUSP_EN" <<NGX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    location ^~ /.well-known/acme-challenge/ {
        root ${ACME_ROOT};
        default_type text/plain;
    }
    location / {
        return 301 https://\$host\$request_uri;
    }
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name ${DOMAIN};
    ssl_certificate ${LE_CERT};
    ssl_certificate_key ${LE_KEY};
    include /etc/letsencrypt/options-ssl-nginx.conf;
${NG_DH_LINE}

    location ^~ /.well-known/acme-challenge/ {
        root ${ACME_ROOT};
        default_type text/plain;
    }

    location / {
        root ${MAINT_ROOT};
        try_files /index.html =503;
        add_header Cache-Control "no-cache, no-store, must-revalidate";
        add_header Pragma "no-cache";
        add_header Expires "0";
    }
}
NGX
else
  log "no Let's Encrypt cert for ${DOMAIN} — HTTP-only maintenance vhost"
  cat >"$SUSP_EN" <<NGX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};

    location ^~ /.well-known/acme-challenge/ {
        root ${ACME_ROOT};
        default_type text/plain;
    }

    location / {
        root ${MAINT_ROOT};
        try_files /index.html =503;
        add_header Cache-Control "no-cache, no-store, must-revalidate";
        add_header Pragma "no-cache";
        add_header Expires "0";
    }
}
NGX
fi

if ! nginx -t 2>/dev/null; then
  rollback_suspend_vhost
  die_json "nginx -t failed after suspend vhost — prüfe ${SUSP_EN}"
fi

if ! systemctl reload nginx 2>/dev/null; then
  rollback_suspend_vhost
  die_json "nginx reload failed after suspend"
fi

SUP="/etc/supervisor/conf.d/clh-worker-${SLUG}.conf"
if [[ -f "$SUP" ]]; then
  rm -f "$SUP"
  supervisorctl reread 2>/dev/null || true
  supervisorctl update 2>/dev/null || true
fi

printf '%s\n' '{"ok":true,"suspended":true}'
log "suspended slug=$SLUG domain=$DOMAIN maintenance=${MAINT_ROOT}"
