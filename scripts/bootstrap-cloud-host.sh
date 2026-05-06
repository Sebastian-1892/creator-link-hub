#!/usr/bin/env bash
#
# Einmaliges Setup eines Cloud-App-Hosts (Debian/Ubuntu): Basis-Pakete, User clh-provisioner,
# Skripte unter /usr/local/bin, Provisioner-HTTP (php -S hinter Nginx), Verzeichnisse.
# Als root ausführen: sudo bash scripts/bootstrap-cloud-host.sh
#
set -euo pipefail

die() { echo "Fehler: $*" >&2; exit 1; }
info() { echo "[bootstrap]" "$*"; }
step() {
  # $1 = laufende Nummer, $2 = von, $3 = Kurztext
  echo ""
  info "Schritt $1/$2: $3"
}

[[ "${EUID:-0}" -eq 0 ]] || die "Bitte als root: sudo bash $0"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

STEPS=11
echo ""
echo "=========================================="
echo "  Creator Link Hub — Cloud-Host bootstrap"
echo "=========================================="
echo ""
info "Dieses Skript richtet Nginx, MariaDB, PHP-FPM, den Provisioner-User und"
info "Systemd ein. Der längste Teil ist oft Schritt 3/11 („apt install“), mehrere"
info "Minuten — die vielen apt/dpkg-Zeilen dazwischen sind normaler Fortschritt."
echo ""

export DEBIAN_FRONTEND=noninteractive

step 1 "$STEPS" "Paketquellen aktualisieren (apt-get update) …"
apt-get update
info "apt-get update erledigt."

step 2 "$STEPS" "Passende PHP-Version (8.4 / 8.3 / 8.2) per apt-cache ermitteln …"
PHP_VER=""
for v in 8.4 8.3 8.2; do
  if apt-cache show "php${v}-fpm" &>/dev/null; then
    PHP_VER="$v"
    break
  fi
done
[[ -n "$PHP_VER" ]] || die "Kein php8.2–8.4-fpm in apt."
info "Gewählt: PHP ${PHP_VER}"

step 3 "$STEPS" "Pakete installieren (nginx, mariadb-server, php${PHP_VER}-*, unzip, …) — bitte warten …"
apt-get install -y nginx mariadb-server unzip acl curl ca-certificates openssl git \
  "php${PHP_VER}-fpm" "php${PHP_VER}-cli" "php${PHP_VER}-mbstring" "php${PHP_VER}-xml" \
  "php${PHP_VER}-curl" "php${PHP_VER}-zip" "php${PHP_VER}-intl" "php${PHP_VER}-bcmath" \
  "php${PHP_VER}-mysql"
info "Paketinstallation erledigt."

step 4 "$STEPS" "Composer (falls noch nicht vorhanden) …"
if ! command -v composer &>/dev/null; then
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  chmod +x /usr/local/bin/composer
  info "Composer nach /usr/local/bin/composer installiert."
else
  info "Composer war schon installiert ($(command -v composer))."
fi

step 5 "$STEPS" "Verzeichnisse und Systembenutzer clh-provisioner …"
install -d -m 0755 /var/www/clh-tenants
install -d -m 0755 /opt/clh-releases
# 0750 + Gruppe clh-provisioner: Dienst muss /etc/clh-provisioner/secret und config.json lesen dürfen
install -d -m 0750 /etc/clh-provisioner
install -d -m 0700 /var/lib/clh-provisioner/nonces
install -d -m 0755 /var/log

if ! id clh-provisioner &>/dev/null; then
  useradd --system --home /opt/clh-provisioner --shell /usr/sbin/nologin --comment "CLH provisioner" clh-provisioner
fi
id clh-provisioner &>/dev/null || die "Systembenutzer clh-provisioner fehlt."

chown root:clh-provisioner /etc/clh-provisioner
chmod 0750 /etc/clh-provisioner

# Home für WorkingDirectory + Provisioner-Dateien (useradd ohne -m legt das Verzeichnis nicht an)
install -d -m 0755 /opt/clh-provisioner
chown clh-provisioner:clh-provisioner /opt/clh-provisioner /var/lib/clh-provisioner /var/lib/clh-provisioner/nonces
info "Verzeichnisse und Benutzer fertig."

step 6 "$STEPS" "Tenant-Skripte nach /usr/local/bin kopieren …"
for s in clh-provision-tenant.sh clh-delete-tenant.sh clh-suspend-tenant.sh; do
  install -m 0755 "${SCRIPT_DIR}/${s}" "/usr/local/bin/${s}"
done
info "Skripte installiert: clh-provision-tenant.sh, clh-delete-tenant.sh, clh-suspend-tenant.sh"

step 7 "$STEPS" "Provisioner-PHP (provisioner.php + router.php) nach /opt/clh-provisioner …"
PROV_SRC=""
ROUTER_SRC=""
for cand in \
  "${REPO_ROOT}/deployment/cloud-host/provisioner.php" \
  "${REPO_ROOT}/../creatorlinkhub.eu/deployment/cloud-host/provisioner.php" \
  "${SCRIPT_DIR}/../../creatorlinkhub.eu/deployment/cloud-host/provisioner.php"; do
  if [[ -f "$cand" ]]; then
    PROV_SRC="$cand"
    break
  fi
done
for cand in \
  "${REPO_ROOT}/deployment/cloud-host/router.php" \
  "${REPO_ROOT}/../creatorlinkhub.eu/deployment/cloud-host/router.php" \
  "${SCRIPT_DIR}/../../creatorlinkhub.eu/deployment/cloud-host/router.php"; do
  if [[ -f "$cand" ]]; then
    ROUTER_SRC="$cand"
    break
  fi
done
if [[ -z "$PROV_SRC" ]]; then
  die "provisioner.php nicht gefunden. Repos creator-link-hub und creatorlinkhub.eu nebeneinander legen oder Pfad in diesem Skript setzen."
fi
if [[ -z "$ROUTER_SRC" ]]; then
  die "router.php nicht gefunden (sollte zu provisioner.php im selben deployment/cloud-host/ Ordner liegen)."
fi
install -m 0644 "$PROV_SRC" /opt/clh-provisioner/provisioner.php
install -m 0644 "$ROUTER_SRC" /opt/clh-provisioner/router.php
info "Quelle provisioner.php: $PROV_SRC"
info "Quelle router.php: $ROUTER_SRC"

chown clh-provisioner:clh-provisioner /opt/clh-provisioner/provisioner.php /opt/clh-provisioner/router.php
info "Provisioner-Dateien liegen unter /opt/clh-provisioner/."

step 8 "$STEPS" "Geheimnis und config.json (falls neu) unter /etc/clh-provisioner …"
if [[ ! -f /etc/clh-provisioner/secret ]]; then
  openssl rand -hex 32 >/etc/clh-provisioner/secret
fi
chmod 0640 /etc/clh-provisioner/secret
chown root:clh-provisioner /etc/clh-provisioner/secret

if [[ ! -f /etc/clh-provisioner/config.json ]]; then
  cat >/etc/clh-provisioner/config.json <<'CFG'
{
  "tenants_root": "/var/www/clh-tenants",
  "release_zip": "/opt/clh-releases/current.zip",
  "db_driver": "mysql"
}
CFG
  chmod 0640 /etc/clh-provisioner/config.json
  chown root:clh-provisioner /etc/clh-provisioner/config.json
fi
info "Secret und Konfiguration gesetzt (bestehende Dateien werden nicht überschrieben)."

step 9 "$STEPS" "sudoers-Regel für clh-provisioner (NOPASSWD für die drei Skripte) …"
cat >/etc/sudoers.d/clh-provisioner <<'SUDO'
Defaults:clh-provisioner secure_path=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
clh-provisioner ALL=(root) NOPASSWD: /usr/local/bin/clh-provision-tenant.sh, /usr/local/bin/clh-delete-tenant.sh, /usr/local/bin/clh-suspend-tenant.sh
SUDO
chmod 0440 /etc/sudoers.d/clh-provisioner
visudo -c -f /etc/sudoers.d/clh-provisioner
info "sudoers ok."

step 10 "$STEPS" "systemd-Dienst clh-provisioner.service schreiben, aktivieren und starten …"
cat >/etc/systemd/system/clh-provisioner.service <<UNIT
[Unit]
Description=Creator Link Hub provisioner (PHP)
After=network.target mariadb.service
Wants=mariadb.service

[Service]
User=clh-provisioner
Group=clh-provisioner
WorkingDirectory=/opt/clh-provisioner
Environment=CLH_CONFIG=/etc/clh-provisioner/config.json
ExecStart=/usr/bin/php -S 127.0.0.1:9100 /opt/clh-provisioner/router.php
Restart=on-failure
RestartSec=3

[Install]
WantedBy=multi-user.target
UNIT

systemctl daemon-reload
if systemctl enable --now clh-provisioner.service; then
  info "Dienst clh-provisioner läuft (php -S 127.0.0.1:9100)."
else
  info "Hinweis: clh-provisioner.service konnte nicht gestartet werden — Status: systemctl status clh-provisioner.service"
fi
info "Geheimnis für HMAC: /etc/clh-provisioner/secret (mit Marketing / provisioner.hmac_secret abgleichen)."

step 11 "$STEPS" "Nginx-Beispielkonfiguration schreiben (noch nicht aktiviert) …"
cat >/etc/nginx/sites-available/clh-provisioner.conf <<'NGX'
server {
    listen 80;
    server_name provision.app.creatorlinkhub.eu;
    location / {
        proxy_pass http://127.0.0.1:9100;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        client_max_body_size 8m;
    }
}
NGX
info "Beispiel-Site: /etc/nginx/sites-available/clh-provisioner.conf"

echo ""
info "Bootstrap abgeschlossen. Nächste Schritte:"
echo "  1) Release-ZIP nach /opt/clh-releases/current.zip legen (Laravel-App mit composer.json + artisan)."
echo "  2) Wildcard-DNS *.app.creatorlinkhub.eu → diese Server-IP."
echo "  3) Nginx-Site aktivieren: ln -sf /etc/nginx/sites-available/clh-provisioner.conf /etc/nginx/sites-enabled/ && nginx -t && systemctl reload nginx"
echo "  4) TLS (certbot) für provision.* und Wildcard für *.app…"
echo "  5) Geheimnis aus /etc/clh-provisioner/secret in creatorlinkhub.eu config provisioner.hmac_secret + provisioner.url (z. B. https://provision.app.creatorlinkhub.eu/ — exakt die POST-URL wie in Nginx)"
echo "  6) journalctl -u clh-provisioner -f"
