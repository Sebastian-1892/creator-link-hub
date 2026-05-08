#!/usr/bin/env bash
#
# TLS nachträglich für einen Tenant aktivieren (Let's Encrypt + Nginx HTTPS), z. B. wenn beim
# ersten Provision Certbot scheiterte (DNS noch nicht auf den VPS) und die Site nur HTTP hat.
#
# ACME/Certbot-Kontakt: Standard certbot@creatorlinkhub.eu; überschreiben mit CLH_ACME_EMAIL oder
# optional --acme-email (einmaliger Aufruf).
#
# Als root:
#   sudo /usr/local/bin/clh-tenant-enable-tls.sh \
#     --slug vpstest-einmalig \
#     --domain vpstest-einmalig.app.creatorlinkhub.eu
#
set -euo pipefail

readonly CLH_DEFAULT_ACME_EMAIL='certbot@creatorlinkhub.eu'

log() { echo "[clh-tenant-enable-tls]" "$@" >&2; }
die() { log "ERROR: $*"; exit 1; }

SLUG=""
DOMAIN=""
ACME_EMAIL_CLI=""
TENANT_ROOT="/var/www/clh-tenants"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --slug) SLUG="${2:-}"; shift 2 ;;
    --domain) DOMAIN="${2:-}"; shift 2 ;;
    --acme-email) ACME_EMAIL_CLI="${2:-}"; shift 2 ;;
    --admin-email) ACME_EMAIL_CLI="${2:-}"; shift 2 ;; # Altname, gleiche Bedeutung wie --acme-email
    --tenant-root) TENANT_ROOT="${2:-}"; shift 2 ;;
    *) die "unknown argument: $1" ;;
  esac
done

[[ -n "$SLUG" ]] || die "missing --slug"
[[ -n "$DOMAIN" ]] || die "missing --domain"
[[ "$SLUG" =~ ^[a-z0-9]([a-z0-9-]{1,30}[a-z0-9])?$ ]] || die "invalid slug"

INSTALL_DIR="${TENANT_ROOT%/}/${SLUG}"
[[ -d "$INSTALL_DIR" ]] || die "tenant dir missing: $INSTALL_DIR"
[[ -d "$INSTALL_DIR/public" ]] || die "public/ missing under $INSTALL_DIR"

ACME_EMAIL="${CLH_ACME_EMAIL:-$CLH_DEFAULT_ACME_EMAIL}"
[[ -n "$ACME_EMAIL_CLI" ]] && ACME_EMAIL="$ACME_EMAIL_CLI"
[[ "$ACME_EMAIL" == *"@"* ]] || die "invalid ACME e-mail: $ACME_EMAIL"

FPM_SOCK="$(ls -1 /run/php/php*-fpm.sock 2>/dev/null | head -1 || true)"
[[ -n "$FPM_SOCK" && -S "$FPM_SOCK" ]] || die "PHP-FPM socket not found under /run/php/"

SITE_NAME="clh-${SLUG}.conf"
SITE_AVAIL="/etc/nginx/sites-available/${SITE_NAME}"
SITE_EN="/etc/nginx/sites-enabled/${SITE_NAME}"
[[ -f "$SITE_AVAIL" ]] || die "nginx site missing: $SITE_AVAIL (Tenant wirklich provisioniert?)"

export DEBIAN_FRONTEND=noninteractive
if ! command -v certbot &>/dev/null; then
  apt-get update -qq
  apt-get install -y -qq certbot
fi

log "certbot certonly (webroot) für ${DOMAIN} — ACME/E-Mail: ${ACME_EMAIL}"
certbot certonly --webroot \
  -w "$INSTALL_DIR/public" \
  -d "$DOMAIN" \
  --non-interactive \
  --agree-tos \
  -m "$ACME_EMAIL" \
  --preferred-challenges http \
  || die "certbot fehlgeschlagen — Auswertung: sudo tail -120 /var/log/letsencrypt/letsencrypt.log · Häufig: Let's-Encrypt-Wartung/Störung (https://letsencrypt.status.io/) — später erneut ausführen · sonst DNS-A auf diesen Host und Port 80 von außen prüfen."

NG_DH_LINE=""
[[ -f /etc/letsencrypt/ssl-dhparams.pem ]] && NG_DH_LINE=$'    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;\n'

log "nginx: HTTPS + ACME auf :80 (wie clh-provision-tenant.sh)"
cat >"$SITE_AVAIL" <<NGX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
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
    server_name ${DOMAIN};
    ssl_certificate /etc/letsencrypt/live/${DOMAIN}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${DOMAIN}/privkey.pem;
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
NGX
ln -sf "$SITE_AVAIL" "$SITE_EN"
nginx -t
systemctl reload nginx

APP_URL="https://${DOMAIN}"
if grep -qE '^APP_URL=' "$INSTALL_DIR/.env"; then
  sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" "$INSTALL_DIR/.env"
else
  echo "APP_URL=${APP_URL}" >>"$INSTALL_DIR/.env"
fi
cd "$INSTALL_DIR"
php artisan config:clear --no-ansi
php artisan config:cache --no-ansi

log "TLS aktiv — https://${DOMAIN}/"
echo "{\"ok\":true,\"instance_url\":\"${APP_URL}/\"}"
