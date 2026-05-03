#!/usr/bin/env bash
#
# Creator Link Hub — interaktive Server-Installation (Debian, Nginx, PHP-FPM, PostgreSQL oder MariaDB)
#
# Ausführung nur als root:
#   sudo bash scripts/install-debian-server.sh
#
# Voraussetzung: Internet. Debian 12+ empfohlen.
# Repository: https://github.com/Sebastian-1892/creator-link-hub.git
#
set -euo pipefail

die() { echo "Fehler: $*" >&2; exit 1; }
info() { echo "[Info] $*"; }
warn() { echo "[Warnung] $*" >&2; }

if [[ "${EUID:-0}" -ne 0 ]]; then
  die "Bitte als root ausführen, z. B.: sudo bash $0"
fi

frage() {
  local prompt="$1"
  local def="${2:-}"
  local r
  if [[ -n "$def" ]]; then
    read -r -p "$prompt [$def]: " r
    echo "${r:-$def}"
  else
    read -r -p "$prompt: " r
    echo "$r"
  fi
}

frage_ja() {
  local prompt="$1"
  local def="${2:-j}"
  local r
  read -r -p "$prompt (j/n) [$def]: " r
  r="${r:-$def}"
  [[ "${r,,}" == "j" || "${r,,}" == "ja" || "${r,,}" == "y" || "${r,,}" == "yes" ]]
}

validiere_db_ident() {
  local x="$1"
  local name="$2"
  [[ "$x" =~ ^[a-zA-Z][a-zA-Z0-9_]*$ ]] || die "$name: nur Buchstaben, Ziffern und Unterstrich, erstes Zeichen Buchstabe."
}

passwort_ok() {
  local p="$1"
  [[ "$p" != *"'"* ]] || die "Passwort darf kein einfaches Hochkomma (') enthalten — bitte anderes wählen."
  [[ "$p" != *'"'* ]] || die "Passwort darf kein doppeltes Hochkomma (\") enthalten — bitte anderes wählen."
}

echo ""
echo "=========================================="
echo "  Creator Link Hub — Server-Installation"
echo "=========================================="
echo ""
echo "Interaktives Setup: Pakete, Datenbank, Webserver, Anwendung."
echo "Zielsystem: Debian (Bookworm oder neuer empfohlen)."
echo ""

if ! frage_ja "Installation starten?" "j"; then
  echo "Abgebrochen."
  exit 0
fi

if frage_ja "apt update && apt upgrade -y ausführen? (kann länger dauern)" "j"; then
  apt-get update -qq
  DEBIAN_FRONTEND=noninteractive apt-get upgrade -y -qq
fi

info "Installiere Basis-Pakete …"
DEBIAN_FRONTEND=noninteractive apt-get install -y -qq curl git unzip acl ca-certificates lsb-release gnupg python3

apt-get update -qq
PHP_VER=""
for v in 8.3 8.2; do
  if apt-cache show "php${v}-fpm" &>/dev/null; then
    PHP_VER="$v"
    break
  fi
done
[[ -n "$PHP_VER" ]] || die "Kein php8.3-fpm oder php8.2-fpm in apt. Bitte Sury-PHP oder neueres Debian nutzen."

info "Verwende PHP ${PHP_VER} …"
DEBIAN_FRONTEND=noninteractive apt-get install -y -qq \
  "php${PHP_VER}-fpm" "php${PHP_VER}-cli" "php${PHP_VER}-common" "php${PHP_VER}-mbstring" "php${PHP_VER}-xml" \
  "php${PHP_VER}-curl" "php${PHP_VER}-zip" "php${PHP_VER}-intl" "php${PHP_VER}-bcmath" \
  "php${PHP_VER}-pgsql" "php${PHP_VER}-redis" "php${PHP_VER}-sqlite3"

FPM_SOCK="/run/php/php${PHP_VER}-fpm.sock"
[[ -S "$FPM_SOCK" ]] || die "PHP-FPM-Socket nicht gefunden: $FPM_SOCK (Dienst neu starten: systemctl restart php${PHP_VER}-fpm)"

if ! command -v composer &>/dev/null; then
  info "Installiere Composer …"
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  chmod +x /usr/local/bin/composer
fi

DEFAULT_REPO="https://github.com/Sebastian-1892/creator-link-hub.git"
INSTALL_DIR="$(frage "Installationsverzeichnis (ohne Schrägstrich am Ende)" "/var/www/creator-link-hub")"
INSTALL_DIR="${INSTALL_DIR%/}"
GIT_URL="$(frage "Git-Repository-URL" "$DEFAULT_REPO")"
GIT_BRANCH="$(frage "Git-Branch" "main")"

if [[ -d "$INSTALL_DIR/.git" ]]; then
  warn "Git-Repository existiert bereits: $INSTALL_DIR"
  if frage_ja "Mit git pull aktualisieren (Branch $GIT_BRANCH)?" "j"; then
    git -C "$INSTALL_DIR" fetch origin
    git -C "$INSTALL_DIR" checkout "$GIT_BRANCH"
    git -C "$INSTALL_DIR" pull origin "$GIT_BRANCH"
  fi
elif [[ -e "$INSTALL_DIR" ]]; then
  die "Pfad existiert, ist aber kein Git-Repository: $INSTALL_DIR — bitte leeren oder anderen Pfad wählen."
else
  mkdir -p "$(dirname "$INSTALL_DIR")"
  info "Klone Repository …"
  git clone -b "$GIT_BRANCH" "$GIT_URL" "$INSTALL_DIR"
fi

cd "$INSTALL_DIR" || die "Konnte nicht nach $INSTALL_DIR wechseln."

DOMAIN="$(frage "Domain für Nginx (server_name), z. B. app.example.de" "localhost")"
APP_URL_DEF="https://${DOMAIN}"
if [[ "$DOMAIN" == "localhost" ]]; then
  APP_URL_DEF="http://127.0.0.1"
fi
APP_URL="$(frage "APP_URL (öffentliche Basis-URL, exakt mit Schema)" "$APP_URL_DEF")"
APP_NAME="$(frage "APP_NAME" "Creator Link Hub")"

echo ""
echo "--- Datenbank ---"
echo "  1) PostgreSQL (empfohlen)"
echo "  2) MariaDB / MySQL"
DB_WAHL="$(frage "Wahl (1 oder 2)" "1")"
DB_NAME="$(frage "Datenbankname" "clh_production")"
DB_USER="$(frage "Datenbank-Benutzer" "clh_app")"
echo -n "Datenbank-Passwort: "
read -rs DB_PASS
echo ""
[[ -n "$DB_PASS" ]] || die "Datenbank-Passwort darf nicht leer sein."
passwort_ok "$DB_PASS"
validiere_db_ident "$DB_NAME" "Datenbankname"
validiere_db_ident "$DB_USER" "Datenbank-Benutzer"

if frage_ja "Vorhandene Datenbank/Benutzer GLEICHEN Namens auf diesem Server vorher entfernen? (nur für Tests / Neuaufbau)" "n"; then
  DB_RESET=1
else
  DB_RESET=0
fi

if [[ "$DB_WAHL" == "2" ]]; then
  DB_DRIVER="mysql"
  info "Installiere MariaDB und php${PHP_VER}-mysql …"
  DEBIAN_FRONTEND=noninteractive apt-get install -y -qq mariadb-server "php${PHP_VER}-mysql"
  systemctl enable --now mariadb
  if [[ "$DB_RESET" -eq 1 ]]; then
    mysql -u root -e "DROP DATABASE IF EXISTS \`${DB_NAME}\`;"
    mysql -u root -e "DROP USER IF EXISTS '${DB_USER}'@'localhost';"
  fi
  mysql -u root <<EOSQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOSQL
else
  DB_DRIVER="pgsql"
  info "Installiere PostgreSQL …"
  DEBIAN_FRONTEND=noninteractive apt-get install -y -qq postgresql
  systemctl enable --now postgresql
  if [[ "$DB_RESET" -eq 1 ]]; then
    sudo -u postgres psql -v ON_ERROR_STOP=1 -c "DROP DATABASE IF EXISTS \"${DB_NAME}\";" || true
    sudo -u postgres psql -v ON_ERROR_STOP=1 -c "DROP USER IF EXISTS \"${DB_USER}\";" || true
  fi
  if ! sudo -u postgres psql -v ON_ERROR_STOP=1 -c "CREATE USER \"${DB_USER}\" WITH PASSWORD '${DB_PASS}';" 2>/dev/null; then
    info "Benutzer existiert — setze Passwort neu …"
    sudo -u postgres psql -v ON_ERROR_STOP=1 -c "ALTER USER \"${DB_USER}\" WITH PASSWORD '${DB_PASS}';"
  fi
  if ! sudo -u postgres psql -v ON_ERROR_STOP=1 -c "CREATE DATABASE \"${DB_NAME}\" OWNER \"${DB_USER}\";" 2>/dev/null; then
    info "Datenbank existiert bereits — überspringe CREATE DATABASE."
  fi
fi

USE_REDIS=false
if frage_ja "Redis für Cache, Session und Queue installieren und in .env aktivieren? (empfohlen)" "j"; then
  DEBIAN_FRONTEND=noninteractive apt-get install -y -qq redis-server
  systemctl enable --now redis-server
  USE_REDIS=true
fi

INSTALL_NODE=false
if frage_ja "Node.js 20.x über NodeSource installieren (für npm run build / Vite)?" "j"; then
  INSTALL_NODE=true
  if [[ ! -f /etc/apt/sources.list.d/nodesource.list ]]; then
    info "Richte NodeSource ein …"
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  fi
  DEBIAN_FRONTEND=noninteractive apt-get install -y -qq nodejs
fi

if [[ "$INSTALL_NODE" == "false" ]] && ! command -v npm &>/dev/null; then
  warn "Kein npm gefunden. Ohne Build schlägt die Oberfläche in Produktion fehl. Bitte später: npm ci && npm run build"
fi

info "Schreibe .env …"
if [[ ! -f .env.example ]]; then
  die ".env.example fehlt im Projekt."
fi
cp -f .env.example .env

QUEUE_CONN="database"
CACHE_STORE="database"
SESSION_DRIVER="database"
if [[ "$USE_REDIS" == "true" ]]; then
  QUEUE_CONN="redis"
  CACHE_STORE="redis"
  SESSION_DRIVER="redis"
fi

DB_PORT="5432"
if [[ "$DB_DRIVER" == "mysql" ]]; then
  DB_PORT="3306"
fi

# .env per Python sicher setzen (Werte können Leerzeichen enthalten)
export PY_APP_NAME="$APP_NAME"
export PY_APP_URL="$APP_URL"
export PY_DB_DRIVER="$DB_DRIVER"
export PY_DB_NAME="$DB_NAME"
export PY_DB_USER="$DB_USER"
export PY_DB_PASS="$DB_PASS"
export PY_DB_PORT="$DB_PORT"
export PY_QUEUE="$QUEUE_CONN"
export PY_CACHE="$CACHE_STORE"
export PY_SESSION="$SESSION_DRIVER"

python3 <<'PY'
import os, pathlib, re

def esc_env_val(v: str) -> str:
    if re.search(r'[\s#]', v) or v == "":
        return '"' + v.replace("\\", "\\\\").replace('"', '\\"') + '"'
    return v

def line_key(line: str):
    m = re.match(r"^([A-Za-z_][A-Za-z0-9_]*)=", line)
    return m.group(1) if m else None

updates = {
    "APP_NAME": esc_env_val(os.environ["PY_APP_NAME"]),
    "APP_ENV": "production",
    "APP_DEBUG": "false",
    "APP_URL": esc_env_val(os.environ["PY_APP_URL"]),
    "DB_CONNECTION": os.environ["PY_DB_DRIVER"],
    "DB_HOST": "127.0.0.1",
    "DB_PORT": os.environ["PY_DB_PORT"],
    "DB_DATABASE": os.environ["PY_DB_NAME"],
    "DB_USERNAME": os.environ["PY_DB_USER"],
    "DB_PASSWORD": esc_env_val(os.environ["PY_DB_PASS"]),
    "QUEUE_CONNECTION": os.environ["PY_QUEUE"],
    "CACHE_STORE": os.environ["PY_CACHE"],
    "SESSION_DRIVER": os.environ["PY_SESSION"],
}

path = pathlib.Path(".env")
lines = path.read_text(encoding="utf-8").splitlines()
out = []
seen = set()
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

info "Stripe-Keys optional — leer lassen zum Ausprobieren ohne Zahlungen."
STRIPE_KEY="$(frage "STRIPE_KEY (Publishable, leer = überspringen)" "")"
STRIPE_SECRET="$(frage "STRIPE_SECRET (leer = überspringen)" "")"
STRIPE_WH="$(frage "STRIPE_WEBHOOK_SECRET (leer = überspringen)" "")"
STRIPE_P1="$(frage "STRIPE_PRICE_STARTER (leer = überspringen)" "")"
STRIPE_P2="$(frage "STRIPE_PRICE_PRO (leer = überspringen)" "")"

if [[ -n "$STRIPE_KEY" || -n "$STRIPE_SECRET" ]]; then
  export PY_SK="$STRIPE_KEY" PY_SS="$STRIPE_SECRET" PY_SW="$STRIPE_WH" PY_S1="$STRIPE_P1" PY_S2="$STRIPE_P2"
  python3 <<'PY'
import os, pathlib, re

def esc(v):
    if v is None:
        return ""
    v = str(v)
    if v == "":
        return ""
    if re.search(r'[\s#]', v):
        return '"' + v.replace("\\", "\\\\").replace('"', '\\"') + '"'
    return v

def line_key(line):
    m = re.match(r"^([A-Za-z_][A-Za-z0-9_]*)=", line)
    return m.group(1) if m else None

u = {
    "STRIPE_KEY": esc(os.environ.get("PY_SK", "")),
    "STRIPE_SECRET": esc(os.environ.get("PY_SS", "")),
    "STRIPE_WEBHOOK_SECRET": esc(os.environ.get("PY_SW", "")),
    "STRIPE_PRICE_STARTER": esc(os.environ.get("PY_S1", "")),
    "STRIPE_PRICE_PRO": esc(os.environ.get("PY_S2", "")),
}
path = pathlib.Path(".env")
lines = path.read_text(encoding="utf-8").splitlines()
out = []
for line in lines:
    k = line_key(line)
    if k in u and not line.strip().startswith("#"):
        if u[k]:
            out.append(f"{k}={u[k]}")
        else:
            out.append(line)
    else:
        out.append(line)
path.write_text("\n".join(out) + "\n", encoding="utf-8")
PY
fi

if frage_ja "SMTP für E-Mail jetzt konfigurieren? (sonst bleibt log/lokal)" "n"; then
  M_HOST="$(frage "MAIL_HOST" "127.0.0.1")"
  M_PORT="$(frage "MAIL_PORT" "587")"
  M_USER="$(frage "MAIL_USERNAME" "")"
  echo -n "MAIL_PASSWORD: "
  read -rs M_PASS
  echo ""
  M_FROM="$(frage "MAIL_FROM_ADDRESS" "noreply@${DOMAIN}")"
  M_SCHEME="$(frage "MAIL_SCHEME (tls/null)" "tls")"
  export PY_MH="$M_HOST" PY_MP="$M_PORT" PY_MU="$M_USER" PY_MPW="$M_PASS" PY_MF="$M_FROM" PY_MS="$M_SCHEME"
  python3 <<'PY'
import os, pathlib, re

def esc(v):
    v = "" if v is None else str(v)
    if v == "":
        return ""
    if re.search(r'[\s#]', v):
        return '"' + v.replace("\\", "\\\\").replace('"', '\\"') + '"'
    return v

def line_key(line):
    m = re.match(r"^([A-Za-z_][A-Za-z0-9_]*)=", line)
    return m.group(1) if m else None

u = {
    "MAIL_MAILER": "smtp",
    "MAIL_HOST": esc(os.environ["PY_MH"]),
    "MAIL_PORT": os.environ["PY_MP"],
    "MAIL_USERNAME": esc(os.environ["PY_MU"]),
    "MAIL_PASSWORD": esc(os.environ["PY_MPW"]),
    "MAIL_FROM_ADDRESS": esc(os.environ["PY_MF"]),
    "MAIL_SCHEME": os.environ.get("PY_MS", "tls"),
}
path = pathlib.Path(".env")
lines = path.read_text(encoding="utf-8").splitlines()
out = []
for line in lines:
    k = line_key(line)
    if k in u and not line.strip().startswith("#"):
        out.append(f"{k}={u[k]}")
    else:
        out.append(line)
path.write_text("\n".join(out) + "\n", encoding="utf-8")
PY
fi

info "Composer install …"
composer install --no-dev --no-interaction --optimize-autoloader

info "APP_KEY erzeugen …"
php artisan key:generate --force

if command -v npm &>/dev/null; then
  info "npm ci && npm run build …"
  npm ci
  npm run build
else
  warn "npm nicht verfügbar — Vite-Build übersprungen. Später im Projektordner ausführen: npm ci && npm run build"
fi

info "Migrationen …"
php artisan migrate --force

if frage_ja "Demo-Daten (Seeder) laden? NICHT für echte Produktion empfohlen." "n"; then
  php artisan db:seed --force
fi

php artisan storage:link --force || true
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

info "Laravel-Optimierung …"
php artisan config:cache
php artisan route:cache
php artisan view:cache

NGINX_SITE="/etc/nginx/sites-available/creator-link-hub"
if frage_ja "Nginx-Site anlegen und aktivieren?" "j"; then
  DEBIAN_FRONTEND=noninteractive apt-get install -y -qq nginx
  cat >"$NGINX_SITE" <<NGX
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

    location ~ /\\.(?!well-known).* {
        deny all;
    }
}
NGX
  ln -sf "$NGINX_SITE" /etc/nginx/sites-enabled/creator-link-hub
  if frage_ja "Standard-Site default deaktivieren (empfohlen)?" "j"; then
    rm -f /etc/nginx/sites-enabled/default
  fi
  nginx -t
  systemctl reload nginx
  systemctl restart "php${PHP_VER}-fpm"
fi

SUP_FILE="/etc/supervisor/conf.d/creator-link-hub-worker.conf"
if frage_ja "Supervisor-Queue-Worker einrichten (${QUEUE_CONN})?" "j"; then
  DEBIAN_FRONTEND=noninteractive apt-get install -y -qq supervisor
  cat >"$SUP_FILE" <<SUP
[program:creator-link-hub-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${INSTALL_DIR}/artisan queue:work ${QUEUE_CONN} --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=${INSTALL_DIR}/storage/logs/worker.log
stopwaitsecs=3600
SUP
  mkdir -p "${INSTALL_DIR}/storage/logs"
  chown www-data:www-data "${INSTALL_DIR}/storage/logs"
  supervisorctl reread
  supervisorctl update
  supervisorctl start creator-link-hub-worker:* || supervisorctl restart creator-link-hub-worker:* || true
fi

CRON_FILE="/etc/cron.d/creator-link-hub-scheduler"
if frage_ja "Cron für Laravel-Scheduler (www-data) anlegen?" "j"; then
  echo "* * * * * www-data cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1" >"$CRON_FILE"
  chmod 644 "$CRON_FILE"
fi

if frage_ja "SSL mit Certbot einrichten? (Domain muss per DNS auf diesen Server zeigen)" "n"; then
  DEBIAN_FRONTEND=noninteractive apt-get install -y -qq certbot python3-certbot-nginx
  certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --register-unsafely-without-email || \
    warn "Certbot ist fehlgeschlagen — manuell: certbot --nginx -d $DOMAIN"
fi

echo ""
echo "=========================================="
echo "  Installation abgeschlossen"
echo "=========================================="
echo ""
echo "Projekt:     $INSTALL_DIR"
echo "Öffentlich:  $APP_URL"
echo "Nginx root:  $INSTALL_DIR/public"
echo ""
echo "Nächste Schritte:"
echo "  - Admin: Benutzer in der DB mit is_admin=1 versehen (Filament: /admin)."
echo "  - Stripe: Webhook $APP_URL/stripe/webhook und STRIPE_WEBHOOK_SECRET setzen."
echo "  - Ohne npm-Build: cd $INSTALL_DIR && npm ci && npm run build"
echo "  - Logs: $INSTALL_DIR/storage/logs/"
echo ""
