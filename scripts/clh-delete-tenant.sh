#!/usr/bin/env bash
#
# Tenant entfernen: DB, Dateien, Tenant-Nginx. Statt leerer Subdomain → „frei“-Landingpage
# (analog suspend, aber Werbung für creatorlinkhub.eu).
#
# Umgebung:
#   CLH_AVAILABLE_ROOT — statische Seite (default: /var/www/clh-available)
#
set -euo pipefail

log() { echo "[clh-delete-tenant]" "$@" >&2; }
die_json() {
  log "ERROR: $*"
  msg=$(printf '%s' "$*" | sed 's/\\/\\\\/g; s/"/\\"/g')
  printf '%s\n' "{\"error\":\"${msg}\"}"
  exit 1
}

rollback_available_vhost() {
  rm -f "$AVAIL_EN" 2>/dev/null || true
}

SLUG=""
DOMAIN=""
TENANT_ROOT="/var/www/clh-tenants"
DB_DRIVER="mysql"
LANDING_ONLY=0

while [[ $# -gt 0 ]]; do
  case "$1" in
    --slug) SLUG="${2:-}"; shift 2 ;;
    --domain) DOMAIN="${2:-}"; shift 2 ;;
    --tenant-root) TENANT_ROOT="${2:-}"; shift 2 ;;
    --db-driver) DB_DRIVER="${2:-}"; shift 2 ;;
    --landing-only) LANDING_ONLY=1; shift ;;
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
SUSP_EN="/etc/nginx/sites-enabled/clh-${SLUG}-suspended.conf"
AVAIL_EN="/etc/nginx/sites-enabled/clh-${SLUG}-available.conf"
MAINT_ROOT="${CLH_AVAILABLE_ROOT:-/var/www/clh-available}"

LE_CERT="/etc/letsencrypt/live/${DOMAIN}/fullchain.pem"
LE_KEY="/etc/letsencrypt/live/${DOMAIN}/privkey.pem"

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
    return 1
  fi
  return 1
}

if [[ -n "$DOMAIN" ]]; then
  if [[ ! -f "$LE_CERT" || ! -f "$LE_KEY" ]]; then
    resolve_ssl_paths_from_site_avail || true
  fi
fi

log "remove nginx (tenant + suspend vhost)"
rm -f "$SITE_EN" 2>/dev/null || true
rm -f "$SITE_AVAIL" 2>/dev/null || true
rm -f "$SUSP_EN" 2>/dev/null || true
rm -f "$AVAIL_EN" 2>/dev/null || true

if [[ "$LANDING_ONLY" -eq 0 ]]; then
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
else
  log "landing-only: DB und Dateien unverändert"
fi

# Let's-Encrypt-Zertifikat bleibt für HTTPS-Landingpage (nicht certbot delete).

if [[ -n "$DOMAIN" && -f "${MAINT_ROOT}/index.html" ]]; then
  mkdir -p "${MAINT_ROOT}/.well-known/acme-challenge"
  NG_DH_LINE=""
  [[ -f /etc/letsencrypt/ssl-dhparams.pem ]] && NG_DH_LINE=$'    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;\n'

  if [[ -f "$LE_CERT" && -f "$LE_KEY" ]]; then
    log "nginx available vhost (HTTPS) for ${DOMAIN}"
    cat >"$AVAIL_EN" <<NGX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    location ^~ /.well-known/acme-challenge/ {
        root ${MAINT_ROOT};
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
        root ${MAINT_ROOT};
        default_type text/plain;
    }
    location / {
        root ${MAINT_ROOT};
        try_files /index.html =404;
        add_header Cache-Control "no-cache, no-store, must-revalidate";
        add_header Pragma "no-cache";
        add_header Expires "0";
    }
}
NGX
  else
    log "nginx available vhost (HTTP only) for ${DOMAIN}"
    cat >"$AVAIL_EN" <<NGX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};

    location ^~ /.well-known/acme-challenge/ {
        root ${MAINT_ROOT};
        default_type text/plain;
    }

    location / {
        root ${MAINT_ROOT};
        try_files /index.html =404;
        add_header Cache-Control "no-cache, no-store, must-revalidate";
        add_header Pragma "no-cache";
        add_header Expires "0";
    }
}
NGX
  fi

  if ! nginx -t 2>/dev/null; then
    rollback_available_vhost
    die_json "nginx -t failed after available vhost — prüfe ${AVAIL_EN}"
  fi
  if ! systemctl reload nginx 2>/dev/null; then
    rollback_available_vhost
    die_json "nginx reload failed after available vhost"
  fi
  log "available landing page active: ${DOMAIN} → ${MAINT_ROOT}"
elif [[ -n "$DOMAIN" ]]; then
  log "WARN: ${MAINT_ROOT}/index.html fehlt — keine Landingpage, nginx ohne Tenant-vHost"
  nginx -t 2>/dev/null && systemctl reload nginx || true
else
  log "WARN: --domain fehlt — keine available-Landingpage möglich"
  nginx -t 2>/dev/null && systemctl reload nginx || true
fi

SUP="/etc/supervisor/conf.d/clh-worker-${SLUG}.conf"
if [[ -f "$SUP" ]]; then
  rm -f "$SUP"
  supervisorctl reread 2>/dev/null || true
  supervisorctl update 2>/dev/null || true
fi

printf '%s\n' '{"ok":true}'
log "done slug=$SLUG"
