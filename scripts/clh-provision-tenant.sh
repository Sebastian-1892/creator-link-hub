#!/usr/bin/env bash
#
# Creator Link Hub — Tenant anlegen (nicht-interaktiv). Nur vom Provisioner-Daemon per sudo aufrufen.
# Stdout: genau eine JSON-Zeile. Diagnostik nach stderr.
# Voraussetzung: MariaDB/MySQL lokal (root per Socket), Nginx, PHP-FPM, Composer im PATH.
#
# TLS: Let's Encrypt via certbot **certonly --webroot** (public/.well-known), danach eigene Nginx-HTTP/HTTPS-
# Sites — vermeidet certbot „--nginx“-Patches (return 404 / zerstückelte Konfiguration).
# Voraussetzung: Port 80 + DNS A für --domain. Ohne TLS: --no-tls.
# ACME/E-Mail für Certbot (-m): fest certbot@creatorlinkhub.eu, überschreibbar mit Umgebung CLH_ACME_EMAIL
# (unabhängig von --admin-email, das der Tenant-Admin für die App bleibt).
#
# Mail: Laravel **MAIL_MAILER=sendmail**. Fehlt **/usr/sbin/sendmail**, installiert dieses Skript **postfix**
# (debconf non-interactive) — zwingend für Cloud-Mail ohne SMTP beim Kundenkauf.
# Absender **noreply@<domain>**; Versand **MAIL_MAILER=sendmail** → Postfix auf dem Host (ideal mit SMTP-Relay aus bootstrap).
#
set -euo pipefail

readonly CLH_DEFAULT_ACME_EMAIL='certbot@creatorlinkhub.eu'

log() { echo "[clh-provision-tenant]" "$@" >&2; }
die_json() {
  log "ERROR: $*"
  msg=$(printf '%s' "$*" | sed 's/\\/\\\\/g; s/"/\\"/g')
  printf '%s\n' "{\"error\":\"${msg}\"}"
  exit 1
}

ensure_postfix_for_cloud_mail() {
  if [[ -x /usr/sbin/sendmail ]]; then
    return 0
  fi
  log "Installiere Postfix (debconf non-interactive) für /usr/sbin/sendmail — erforderlich für Tenant-Mail …"
  export DEBIAN_FRONTEND=noninteractive
  local mn
  mn="$(hostname -f 2>/dev/null || hostname)"
  echo "postfix postfix/mailname string ${mn}" | debconf-set-selections
  echo "postfix postfix/main_mailer_type select Internet Site" | debconf-set-selections
  apt-get update -qq || die_json "apt-get update failed (postfix/network?)"
  apt-get install -y -qq postfix || die_json "apt install postfix failed — Cloud-Mail ohne sendmail nicht nutzbar"
  [[ -x /usr/sbin/sendmail ]] || die_json "postfix installiert aber /usr/sbin/sendmail fehlt oder ist nicht ausführbar"
}

SLUG=""
DOMAIN=""
ADMIN_EMAIL=""
ADMIN_NAME="Admin"
TENANT_ROOT="/var/www/clh-tenants"
RELEASE_ZIP=""
DB_DRIVER="mysql"
ENABLE_TLS=1

while [[ $# -gt 0 ]]; do
  case "$1" in
    --slug) SLUG="${2:-}"; shift 2 ;;
    --domain) DOMAIN="${2:-}"; shift 2 ;;
    --admin-email) ADMIN_EMAIL="${2:-}"; shift 2 ;;
    --admin-name) ADMIN_NAME="${2:-}"; shift 2 ;;
    --tenant-root) TENANT_ROOT="${2:-}"; shift 2 ;;
    --release-zip) RELEASE_ZIP="${2:-}"; shift 2 ;;
    --db-driver) DB_DRIVER="${2:-}"; shift 2 ;;
    --no-tls) ENABLE_TLS=0; shift ;;
    *) die_json "unknown argument: $1" ;;
  esac
done

[[ -n "$SLUG" ]] || die_json "missing --slug"
[[ -n "$DOMAIN" ]] || die_json "missing --domain"
[[ -n "$ADMIN_EMAIL" ]] || die_json "missing --admin-email"
[[ "$SLUG" =~ ^[a-z0-9]([a-z0-9-]{1,30}[a-z0-9])?$ ]] || die_json "invalid slug"
[[ -f "$RELEASE_ZIP" ]] || die_json "release zip not found: $RELEASE_ZIP"
[[ "$DB_DRIVER" == "mysql" ]] || die_json "only --db-driver mysql is supported in this script"

DB_SLUG="${SLUG//-/_}"
DB_NAME="clh_${DB_SLUG}"
DB_USER="clh_${DB_SLUG}"
DB_PASS="$(openssl rand -hex 20)"
INSTALL_DIR="${TENANT_ROOT%/}/${SLUG}"
SITE_NAME="clh-${SLUG}.conf"
SITE_AVAIL="/etc/nginx/sites-available/${SITE_NAME}"
SITE_EN="/etc/nginx/sites-enabled/${SITE_NAME}"

FPM_SOCK="$(ls -1 /run/php/php*-fpm.sock 2>/dev/null | head -1 || true)"
[[ -n "$FPM_SOCK" && -S "$FPM_SOCK" ]] || die_json "PHP-FPM socket not found under /run/php/"

if [[ -d "$INSTALL_DIR" ]]; then
  die_json "tenant directory already exists: $INSTALL_DIR"
fi

ensure_postfix_for_cloud_mail

TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT
log "unpack $RELEASE_ZIP"
unzip -q -o "$RELEASE_ZIP" -d "$TMP" || die_json "unzip failed — prüfe Datei (zip -l / unzip -t): $RELEASE_ZIP"
mapfile -t COMPOSERS < <(find "$TMP" -name composer.json -type f 2>/dev/null | sort)
[[ ${#COMPOSERS[@]} -ge 1 ]] || die_json "no composer.json in release zip"

# Robuster Root-Finder: nur Kandidaten mit artisan akzeptieren;
# bevorzugt Kandidaten mit .env.example (echter App-Root statt Vendor-Unterordner).
APPROOT=""
for comp in "${COMPOSERS[@]}"; do
  cand="$(dirname "$comp")"
  [[ -f "$cand/artisan" ]] || continue
  if [[ -f "$cand/.env.example" ]]; then
    APPROOT="$cand"
    break
  fi
  if [[ -z "$APPROOT" ]]; then
    APPROOT="$cand"
  fi
done
[[ -n "$APPROOT" ]] || die_json "no valid app root found (composer.json + artisan) in release zip"
mv "$APPROOT" "$INSTALL_DIR" || die_json "mv to install dir failed: $INSTALL_DIR"
# mktemp-Extrakt liegt typisch unter drwx------: Nginx/php-fpm (www-data) müssen INSTALL_DIR traversieren (root …/public).
chmod 0755 "$INSTALL_DIR" "$INSTALL_DIR/public" || die_json "chmod 755 tenant root/public failed"

mysql -u root <<EOSQL || die_json "mysql bootstrap failed (root socket / permissions?)"
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOSQL

# Zunächst http (ACME HTTP-01 / bis Zertifikat da ist); bei ENABLE_TLS=1 wird danach auf https umgestellt.
APP_URL="http://${DOMAIN}"
if [[ -f "$INSTALL_DIR/.env.example" ]]; then
  cp -f "$INSTALL_DIR/.env.example" "$INSTALL_DIR/.env"
elif [[ -f "$INSTALL_DIR/.env.production.example" ]]; then
  cp -f "$INSTALL_DIR/.env.production.example" "$INSTALL_DIR/.env"
elif [[ -f "$INSTALL_DIR/.env.dist" ]]; then
  cp -f "$INSTALL_DIR/.env.dist" "$INSTALL_DIR/.env"
else
  # ZIP enthält manchmal keine Punktdateien oder Vorlage liegt in einem anderen Extraktionsbaum
  ENV_TMPL="$(find "$INSTALL_DIR" "$TMP" \( -name '.env.example' -o -name '.env.production.example' -o -name '.env.dist' \) -type f 2>/dev/null | head -1)"
  if [[ -n "$ENV_TMPL" ]]; then
    log "using env template found at: $ENV_TMPL"
    cp -f "$ENV_TMPL" "$INSTALL_DIR/.env"
  else
    log "WARN: no .env template in zip — creating empty .env (Python block below adds required keys)"
    umask 077
    : >"$INSTALL_DIR/.env"
  fi
fi
export PY_APP_URL="$APP_URL" PY_MAIL_DOMAIN="$DOMAIN" PY_DB_NAME="$DB_NAME" PY_DB_USER="$DB_USER" PY_DB_PASS="$DB_PASS" PY_INSTALL_DIR="$INSTALL_DIR"
python3 <<'PY'
import os, pathlib, re

def esc(v: str) -> str:
    if re.search(r'[\s#]', v) or v == "":
        return '"' + v.replace("\\", "\\\\").replace('"', '\\"') + '"'
    return v

def line_key(line: str):
    m = re.match(r"^([A-Za-z_][A-Za-z0-9_]*)=", line)
    return m.group(1) if m else None

mail_from = f"noreply@{os.environ['PY_MAIL_DOMAIN']}"
updates = {
    "APP_NAME": esc("Creator Link Hub"),
    "APP_ENV": "production",
    "APP_DEBUG": "false",
    "APP_URL": esc(os.environ["PY_APP_URL"]),
    "CLH_APP_ROOT": esc(os.environ["PY_INSTALL_DIR"]),
    "CLH_DEPLOYMENT": esc("cloud"),
    "DB_CONNECTION": "mysql",
    "DB_HOST": "127.0.0.1",
    "DB_PORT": "3306",
    "DB_DATABASE": os.environ["PY_DB_NAME"],
    "DB_USERNAME": os.environ["PY_DB_USER"],
    "DB_PASSWORD": esc(os.environ["PY_DB_PASS"]),
    "QUEUE_CONNECTION": "database",
    "CACHE_STORE": "database",
    "SESSION_DRIVER": "database",
    # Outgoing mail ohne SMTP-Credentials: Laravel sendmail → /usr/sbin/sendmail (Host: typ. postfix)
    "MAIL_MAILER": "sendmail",
    "MAIL_FROM_ADDRESS": esc(mail_from),
    "MAIL_FROM_NAME": esc("Creator Link Hub"),
}
path = pathlib.Path(os.environ["PY_INSTALL_DIR"]) / ".env"
lines = path.read_text(encoding="utf-8").splitlines()
out, seen = [], set()
for line in lines:
    k = line_key(line)
    if k and k in updates and not line.strip().startswith("#"):
        out.append(f"{k}={updates[k]}")
        seen.add(k)
    else:
        out.append(line)
for k, v in updates.items():
    if k not in seen:
        out.append(f"{k}={v}")
path.write_text("\n".join(out) + "\n", encoding="utf-8")
PY

cd "$INSTALL_DIR"
# build-cloud-release-zip.sh excludes cached files under storage/framework/*; ohne diese
# Verzeichnisse scheitert composer post-autoload (artisan package:discover): "Please provide a valid cache path".
mkdir -p \
  bootstrap/cache \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs
export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_MEMORY_LIMIT="${COMPOSER_MEMORY_LIMIT:--1}"
log "composer install (COMPOSER_MEMORY_LIMIT=${COMPOSER_MEMORY_LIMIT})"
composer install --no-dev --no-interaction --optimize-autoloader --no-ansi >/dev/stderr
log "artisan"
php artisan key:generate --force --no-interaction --no-ansi -q >/dev/stderr
php artisan migrate --force --no-interaction --no-ansi -q >/dev/stderr
php artisan db:seed --class=Database\\Seeders\\ThemeSeeder --force --no-interaction --no-ansi -q >/dev/stderr
ADMIN_PW="$(openssl rand -hex 12)"
# Wie install-server.sh: Variablen direkt am Artisan-Prozess — zuverlässiger als nur export (Laravel/.env).
CLH_ADMIN_EMAIL="$ADMIN_EMAIL" CLH_ADMIN_PASSWORD="$ADMIN_PW" CLH_ADMIN_NAME="$ADMIN_NAME" \
  php artisan db:seed --class=Database\\Seeders\\InstallAdminSeeder --force --no-interaction --no-ansi -q >/dev/stderr

bash "$INSTALL_DIR/scripts/ensure-laravel-storage.sh" "$INSTALL_DIR" >/dev/stderr
php artisan config:cache --no-interaction --no-ansi -q >/dev/stderr
php artisan route:cache --no-interaction --no-ansi -q >/dev/stderr
php artisan view:cache --no-interaction --no-ansi -q >/dev/stderr

# Composer/npm im Filament-Dashboard laufen als www-data (PHP-FPM). Nach composer als root
# gehört vendor/ sonst root → „Permission denied“ bei Admin → Anwendungs-Update.
chown -R www-data:www-data "$INSTALL_DIR"
chmod -R ug+rwx "$INSTALL_DIR/storage" "$INSTALL_DIR/bootstrap/cache" 2>/dev/null || true

log "nginx $SITE_AVAIL"
cat >"$SITE_AVAIL" <<NGX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    root ${INSTALL_DIR}/public;
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
nginx -t >/dev/stderr
systemctl reload nginx >/dev/stderr

if [[ "$ENABLE_TLS" -eq 1 ]]; then
  ACME_EMAIL="${CLH_ACME_EMAIL:-$CLH_DEFAULT_ACME_EMAIL}"
  log "Let's Encrypt (webroot) für ${DOMAIN} — ACME-Mail: ${ACME_EMAIL} (Tenant-Admin-Login: ${ADMIN_EMAIL})"
  export DEBIAN_FRONTEND=noninteractive
  if ! command -v certbot &>/dev/null; then
    apt-get update -qq
    apt-get install -y -qq certbot
  fi

  certbot certonly --webroot \
    -w "$INSTALL_DIR/public" \
    -d "$DOMAIN" \
    --non-interactive \
    --agree-tos \
    -m "$ACME_EMAIL" \
    --preferred-challenges http \
    >/dev/stderr \
    || die_json "certbot fehlgeschlagen für ${DOMAIN} — LE-Status https://letsencrypt.status.io/ · Log /var/log/letsencrypt/letsencrypt.log · oder DNS/Port 80 · Alternative: --no-tls"

  NG_DH_LINE=""
  [[ -f /etc/letsencrypt/ssl-dhparams.pem ]] && NG_DH_LINE=$'    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;\n'

  log "nginx final (HTTPS + ACME /.well-known auf :80)"
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
  nginx -t >/dev/stderr
  systemctl reload nginx >/dev/stderr

  APP_URL="https://${DOMAIN}"
  sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" "$INSTALL_DIR/.env" || die_json "APP_URL in .env konnte nicht auf https gesetzt werden"
  cd "$INSTALL_DIR"
  php artisan config:clear --no-interaction --no-ansi -q >/dev/stderr
  php artisan config:cache --no-interaction --no-ansi -q >/dev/stderr
  log "TLS aktiv — APP_URL=${APP_URL}"
else
  log "TLS übersprungen (--no-tls). APP_URL bleibt http."
fi

ADMIN_URL="${APP_URL%/}/admin"
APP_INST="${APP_URL%/}/"
export ADMIN_PW ADMIN_URL APP_INST
python3 <<'PY'
import json, os

print(
    json.dumps(
        {
            "instance_url": os.environ["APP_INST"],
            "ok": True,
            "admin_url": os.environ["ADMIN_URL"],
            "initial_admin_password": os.environ["ADMIN_PW"],
        },
        separators=(",", ":"),
    )
)
PY
log "tenant ready slug=$SLUG instance=${APP_INST} admin=${ADMIN_URL}"
