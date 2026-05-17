#!/usr/bin/env bash
#
# Tenant suspendieren: Original-Nginx-Site aus sites-enabled nehmen, Maintenance-vhost aktivieren.
# sites-available/clh-SLUG.conf bleibt unverändert (Resume stellt nur den Symlink wieder her).
#
# SSL / Let's Encrypt:
#   - Zertifikate unter /etc/letsencrypt/ werden NICHT gelöscht, widerrufen oder per certbot entfernt.
#   - Der Suspend-vhost nutzt dieselben ssl_certificate-Pfade wie die Original-Site (HTTPS bleibt gültig).
#   - ACME /.well-known bleibt für certbot renew erreichbar.
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

# Pfade aus Original-Nginx-Site übernehmen (falls --domain und LE-Verzeichnis abweichen)
resolve_ssl_paths_from_site_avail() {
  [[ -f "$SITE_AVAIL" ]] || return 1
  local cert key had_tls=0
  if grep -qE 'listen[[:space:]]+443[[:space:]]+ssl' "$SITE_AVAIL" 2>/dev/null; then
    had_tls=1
  fi
  cert="$(grep -m1 -E '^[[:space:]]*ssl_certificate[[:space:]]+' "$SITE_AVAIL" 2>/dev/null \
    | sed -E 's/^[[:space:]]*ssl_certificate[[:space:]]+([^;[:space:]]+);.*/\1/' || true)"
  key="$(grep -m1 -E '^[[:space:]]*ssl_certificate_key[[:space:]]+' "$SITE_AVAIL" 2>/dev/null \
    | sed -E 's/^[[:space:]]*ssl_certificate_key[[:space:]]+([^;[:space:]]+);.*/\1/' || true)"
  if [[ -n "$cert" && -n "$key" && -f "$cert" && -f "$key" ]]; then
    LE_CERT="$cert"
    LE_KEY="$key"
    return 0
  fi
  if [[ "$had_tls" -eq 1 ]]; then
    die_json "tenant had HTTPS in ${SITE_AVAIL} but ssl_certificate paths are missing — certs are not deleted; fix paths or renew LE cert"
  fi
  return 1
}

[[ -f "${MAINT_ROOT}/index.html" ]] || die_json "maintenance page missing: ${MAINT_ROOT}/index.html (run bootstrap or clh-cloud-host-update)"

if [[ ! -f "$LE_CERT" || ! -f "$LE_KEY" ]]; then
  resolve_ssl_paths_from_site_avail || true
fi

# Original-Symlink entfernen (Konfiguration in sites-available bleibt — inkl. ssl_certificate-Zeilen)
rm -f "$SITE_EN" 2>/dev/null || true

# Maintenance-vhost schreiben (bestehende LE-Dateien wiederverwenden — nichts löschen)
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
  if [[ -f "$SITE_AVAIL" ]] && grep -qE 'listen[[:space:]]+443[[:space:]]+ssl' "$SITE_AVAIL" 2>/dev/null; then
    die_json "HTTPS tenant but no readable ssl_certificate — LE files are preserved; check ${SITE_AVAIL} and /etc/letsencrypt/live/"
  fi
  log "no TLS cert for ${DOMAIN} — HTTP-only maintenance vhost (certs under /etc/letsencrypt are never deleted by suspend)"
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
