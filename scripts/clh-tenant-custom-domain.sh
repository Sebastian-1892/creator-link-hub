#!/usr/bin/env bash
#
# Custom Domain: Nginx vHost + Let's Encrypt für zusätzlichen Hostname (CNAME auf Cloud-Subdomain).
# Args: --slug, --custom-domain, --canonical-domain, --mode add|remove, --tenant-root
# Stdout: eine JSON-Zeile bei Erfolg.
#
set -euo pipefail

readonly CLH_DEFAULT_ACME_EMAIL='certbot@creatorlinkhub.eu'
readonly HOST_RE='^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)+$'

log() { echo "[clh-tenant-custom-domain]" "$@" >&2; }
die_json() {
  log "ERROR: $*"
  msg=$(printf '%s' "$*" | sed 's/\\/\\\\/g; s/"/\\"/g')
  printf '%s\n' "{\"error\":\"${msg}\"}"
  exit 1
}

valid_host() {
  local h="${1,,}"
  h="${h%.}"
  [[ "$h" =~ $HOST_RE ]] && [[ ${#h} -le 253 ]]
}

SLUG=""
CUSTOM=""
CANONICAL=""
MODE="add"
TENANT_ROOT="/var/www/clh-tenants"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --slug) SLUG="${2:-}"; shift 2 ;;
    --custom-domain) CUSTOM="${2:-}"; shift 2 ;;
    --canonical-domain) CANONICAL="${2:-}"; shift 2 ;;
    --mode) MODE="${2:-}"; shift 2 ;;
    --tenant-root) TENANT_ROOT="${2:-}"; shift 2 ;;
    *) die_json "unknown argument: $1" ;;
  esac
done

[[ -n "$SLUG" ]] || die_json "missing --slug"
[[ "$SLUG" =~ ^[a-z0-9]([a-z0-9-]{1,30}[a-z0-9])?$ ]] || die_json "invalid slug"
[[ -n "$CUSTOM" ]] || die_json "missing --custom-domain"
[[ -n "$CANONICAL" ]] || die_json "missing --canonical-domain"
[[ "$MODE" == "add" || "$MODE" == "remove" ]] || die_json "invalid --mode"
valid_host "$CUSTOM" || die_json "invalid custom-domain"
valid_host "$CANONICAL" || die_json "invalid canonical-domain"

CUSTOM="${CUSTOM,,}"
CUSTOM="${CUSTOM%.}"
CANONICAL="${CANONICAL,,}"
CANONICAL="${CANONICAL%.}"

INSTALL_DIR="${TENANT_ROOT%/}/${SLUG}"
SITE_NAME="clh-${SLUG}.conf"
SITE_AVAIL="/etc/nginx/sites-available/${SITE_NAME}"
ENV_FILE="${INSTALL_DIR}/.env"
MARKER_BEGIN="# CLH_CUSTOM_DOMAIN_BEGIN ${CUSTOM}"
MARKER_END="# CLH_CUSTOM_DOMAIN_END ${CUSTOM}"

[[ -d "$INSTALL_DIR/public" ]] || die_json "tenant not found: $INSTALL_DIR"
[[ -f "$SITE_AVAIL" ]] || die_json "nginx site missing: $SITE_AVAIL"

FPM_SOCK="$(ls -1 /run/php/php*-fpm.sock 2>/dev/null | head -1 || true)"
[[ -n "$FPM_SOCK" && -S "$FPM_SOCK" ]] || die_json "PHP-FPM socket not found"

set_env_key() {
  local key="$1" val="$2"
  [[ -f "$ENV_FILE" ]] || die_json ".env missing"
  if grep -q "^${key}=" "$ENV_FILE"; then
    sed -i "s|^${key}=.*|${key}=${val}|" "$ENV_FILE"
  else
    echo "${key}=${val}" >>"$ENV_FILE"
  fi
}

remove_custom_blocks() {
  if [[ ! -f "$SITE_AVAIL" ]]; then
    return 0
  fi
  if ! grep -qF "$MARKER_BEGIN" "$SITE_AVAIL" 2>/dev/null; then
    return 0
  fi
  local tmp
  tmp="$(mktemp)"
  awk -v b="$MARKER_BEGIN" -v e="$MARKER_END" '
    $0 == b { skip=1; next }
    $0 == e { skip=0; next }
    !skip { print }
  ' "$SITE_AVAIL" >"$tmp"
  mv "$tmp" "$SITE_AVAIL"
}

remove_mode() {
  log "remove custom domain ${CUSTOM} for ${SLUG}"
  remove_custom_blocks
  if command -v certbot &>/dev/null; then
    certbot delete --cert-name "$CUSTOM" --non-interactive 2>/dev/null || true
  fi
  set_env_key "APP_EXTRA_HOSTS" ""
  if [[ -f "$INSTALL_DIR/artisan" ]]; then
    cd "$INSTALL_DIR"
    sudo -u www-data php artisan config:cache --no-interaction --no-ansi -q >/dev/stderr || true
  fi
  nginx -t >/dev/stderr
  systemctl reload nginx >/dev/stderr
  printf '%s\n' "{\"ok\":true,\"custom_domain\":\"${CUSTOM}\",\"mode\":\"remove\"}"
}

if [[ "$MODE" == "remove" ]]; then
  remove_mode
  exit 0
fi

append_acme_http_only() {
  cat >>"$SITE_AVAIL" <<NGX

${MARKER_BEGIN}
server {
    listen 80;
    listen [::]:80;
    server_name ${CUSTOM};
    root ${INSTALL_DIR}/public;
    location ^~ /.well-known/acme-challenge/ {
        root ${INSTALL_DIR}/public;
        default_type text/plain;
    }
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    location ~ \\.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${FPM_SOCK};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
}
${MARKER_END}
NGX
}

append_full_custom_vhosts() {
  NG_DH_LINE=""
  [[ -f /etc/letsencrypt/ssl-dhparams.pem ]] && NG_DH_LINE=$'    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;\n'
  cat >>"$SITE_AVAIL" <<NGX

${MARKER_BEGIN}
server {
    listen 80;
    listen [::]:80;
    server_name ${CUSTOM};
    root ${INSTALL_DIR}/public;
    location ^~ /.well-known/acme-challenge/ {
        root ${INSTALL_DIR}/public;
        default_type text/plain;
    }
    location / {
        return 301 https://\$host\$request_uri;
    }
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name ${CUSTOM};
    ssl_certificate /etc/letsencrypt/live/${CUSTOM}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${CUSTOM}/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
${NG_DH_LINE}    root ${INSTALL_DIR}/public;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    index index.php;
    charset utf-8;
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    error_page 404 /index.php;
    location ~ \\.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${FPM_SOCK};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    location ~ /\\.(?!well-known).* { deny all; }
}
${MARKER_END}
NGX
}

if [[ ! -f "/etc/letsencrypt/live/${CUSTOM}/fullchain.pem" ]]; then
  if ! grep -qF "$MARKER_BEGIN" "$SITE_AVAIL" 2>/dev/null; then
    log "temporary HTTP vHost for ACME (${CUSTOM})"
    append_acme_http_only
    nginx -t >/dev/stderr || die_json "nginx -t failed (HTTP custom)"
    systemctl reload nginx >/dev/stderr
  fi
  ACME_EMAIL="${CLH_ACME_EMAIL:-$CLH_DEFAULT_ACME_EMAIL}"
  export DEBIAN_FRONTEND=noninteractive
  if ! command -v certbot &>/dev/null; then
    apt-get update -qq
    apt-get install -y -qq certbot
  fi
  log "certbot webroot for ${CUSTOM}"
  certbot certonly --webroot \
    -w "$INSTALL_DIR/public" \
    -d "$CUSTOM" \
    --non-interactive \
    --agree-tos \
    -m "$ACME_EMAIL" \
    --preferred-challenges http \
    >/dev/stderr \
    || die_json "certbot failed for ${CUSTOM}"
fi

if ! grep -qF "$MARKER_BEGIN" "$SITE_AVAIL" 2>/dev/null || ! grep -q "listen 443 ssl" "$SITE_AVAIL" 2>/dev/null; then
  log "write full HTTP/HTTPS vHosts for ${CUSTOM}"
  remove_custom_blocks
  append_full_custom_vhosts
fi

set_env_key "APP_EXTRA_HOSTS" "$CUSTOM"
cd "$INSTALL_DIR"
sudo -u www-data php artisan config:cache --no-interaction --no-ansi -q >/dev/stderr \
  || die_json "artisan config:cache failed"

nginx -t >/dev/stderr || die_json "nginx -t failed"
systemctl reload nginx >/dev/stderr

printf '%s\n' "{\"ok\":true,\"custom_domain\":\"${CUSTOM}\",\"canonical_domain\":\"${CANONICAL}\",\"mode\":\"add\"}"
