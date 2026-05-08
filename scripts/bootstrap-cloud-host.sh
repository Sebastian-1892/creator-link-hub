#!/usr/bin/env bash
#
# Einmaliges Setup eines Cloud-App-Hosts (Debian/Ubuntu): Basis-Pakete, User clh-provisioner,
# Skripte unter /usr/local/bin, Provisioner-HTTP (php -S hinter Nginx), Verzeichnisse.
# Als root ausführen: sudo bash scripts/bootstrap-cloud-host.sh
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

# Farben (Abschalten: NO_COLOR=1 oder keine TTY)
C_R=""
C_B=""
C_D=""
C_CY=""
C_GR=""
C_YE=""
C_RE=""
if [[ -t 1 ]] && [[ -z "${NO_COLOR:-}${CLH_NO_COLOR:-}" ]]; then
  C_R=$'\033[0m'
  C_B=$'\033[1m'
  C_D=$'\033[90m'
  C_CY=$'\033[38;5;39m'
  C_GR=$'\033[38;5;71m'
  C_YE=$'\033[38;5;178m'
  C_RE=$'\033[38;9m'
fi

die() { echo "${C_RE}${C_B}✖ Fehler:${C_R} $*" >&2; exit 1; }
info() { echo "${C_GR}${C_B}●${C_R} ${C_GR}[bootstrap]${C_R} $*"; }

step() {
  # $1 = laufende Nummer, $2 = von, $3 = Kurztext
  echo ""
  echo "  ${C_YE}${C_B}▶ Schritt $1 / $2${C_R}"
  echo "  ${C_D}$3${C_R}"
  echo ""
}

banner_line() {
  echo "${C_CY}${C_B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${C_R}"
}

[[ "${EUID:-0}" -eq 0 ]] || die "Bitte als root: sudo bash $0"

STEPS=13

echo ""
banner_line
echo ""
echo "${C_CY}${C_B}       Creator Link Hub — Cloud-Host bootstrap${C_R}"
echo ""
banner_line
echo ""
info "Dieses Skript installiert Basis-Pakete, UFW (Firewall), Nginx,"
info "MariaDB, PHP-FPM, den Provisioner und die Tenant-Skripte."
echo ""
echo "  ${C_D}Der längste Block ist oft Schritt 3 / ${STEPS} (apt install) — viele apt-Zeilen"
echo "  sind normal. Port ${C_GR}9100${C_D} läuft nur auf 127.0.0.1 (kein Firewall-Öffnen nötig).${C_R}"
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

step 3 "$STEPS" "Pakete installieren (nginx, mariadb-server, php${PHP_VER}-*, unzip, zip, …) — bitte warten …"
apt-get install -y nginx mariadb-server unzip zip acl curl ca-certificates openssl git \
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
for s in clh-provision-tenant.sh clh-delete-tenant.sh clh-suspend-tenant.sh clh-resume-tenant.sh clh-tenant-enable-tls.sh; do
  install -m 0755 "${SCRIPT_DIR}/${s}" "/usr/local/bin/${s}"
done
info "Skripte installiert: clh-provision-tenant, delete, suspend, resume, tenant-enable-tls (.sh)"

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

SMOKE_SRC="$(dirname "$PROV_SRC")/vps-smoke-provision.php"
if [[ -f "$SMOKE_SRC" ]]; then
  install -m 0644 "$SMOKE_SRC" /opt/clh-provisioner/vps-smoke-provision.php
  chown root:root /opt/clh-provisioner/vps-smoke-provision.php
  info "Smoke-Tester: sudo php /opt/clh-provisioner/vps-smoke-provision.php 'http://127.0.0.1:9100/' VPSTEST-SLUG"
else
  info "Hinweis: vps-smoke-provision.php fehlt — nach git pull erneut ausführen oder clh-cloud-host-update.sh nutzen."
fi

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

step 9 "$STEPS" "sudoers-Regel für clh-provisioner (NOPASSWD für die Tenant-Skripte) …"
cat >/etc/sudoers.d/clh-provisioner <<'SUDO'
Defaults:clh-provisioner secure_path=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
clh-provisioner ALL=(root) NOPASSWD: /usr/local/bin/clh-provision-tenant.sh, /usr/local/bin/clh-delete-tenant.sh, /usr/local/bin/clh-suspend-tenant.sh, /usr/local/bin/clh-resume-tenant.sh, /usr/local/bin/clh-tenant-enable-tls.sh
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

step 11 "$STEPS" "Firewall (UFW): eingehend nur SSH · HTTP · HTTPS …"
DEBIAN_FRONTEND=noninteractive apt-get install -y -qq ufw
UFW_STA="$(ufw status 2>/dev/null || true)"
if echo "$UFW_STA" | grep -qi 'Status: inactive'; then
  ufw default deny incoming
  ufw default allow outgoing
  info "${C_B}Standard:${C_R} eingehend blockiert, ausgehend erlaubt."
fi
# SSH (Port aus OpenSSH; bei eigenem SSH-Port nach dem Lauf manuell: ufw allow …/tcp)
ufw limit OpenSSH >/dev/null 2>&1 || ufw allow OpenSSH
ufw allow 80/tcp comment 'HTTP (ACME, Redirects)'
ufw allow 443/tcp comment 'HTTPS (Provisioner, Tenant-Apps)'
ufw --force enable
info "${C_B}UFW aktiv.${C_R} Von außen: ${C_YE}22/tcp (SSH)${C_R}, ${C_YE}80/tcp${C_R}, ${C_YE}443/tcp${C_R}."
echo "  ${C_D}MariaDB und Provisioner (127.0.0.1:9100) bleiben nicht öffentlich erreichbar.${C_R}"
echo ""

step 12 "$STEPS" "Nginx-Beispielkonfiguration schreiben (noch nicht aktiviert) …"
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

step 13 "$STEPS" "Update-Skript + Pfade-Datei (install-paths.env) …"
GIT_REF_PULL="${CLH_GIT_REF:-main}"
{
  printf '%s\n' \
    '# Automatisch von bootstrap-cloud-host.sh — bei Umzug des Git-Klons CLH_REPO_ROOT anpassen.' \
    '# Branch für „git pull“ (z. B. main, develop):' \
    "CLH_GIT_REF=${GIT_REF_PULL}"
  printf 'CLH_REPO_ROOT=%q\n' "$REPO_ROOT"
} >/etc/clh-provisioner/install-paths.env
chmod 0640 /etc/clh-provisioner/install-paths.env
chown root:clh-provisioner /etc/clh-provisioner/install-paths.env
install -m 0755 "${SCRIPT_DIR}/clh-cloud-host-update.sh" /usr/local/bin/clh-cloud-host-update.sh
info "Updates: ${C_B}sudo /usr/local/bin/clh-cloud-host-update.sh${C_R} · optional ${C_D}--with-zip${C_R} (neue Tenant-ZIP)."
info "Pfade: ${C_D}/etc/clh-provisioner/install-paths.env${C_R}"

echo ""
banner_line
echo ""
echo "${C_CY}${C_B}  Bootstrap abgeschlossen ${C_GR}✓${C_R}"
echo ""
banner_line
echo ""
info "${C_B}Nächste Schritte${C_R} (über die Cloud-Doku abarbeiten):"
echo ""
echo "  ${C_YE}1.${C_R} Release-ZIP → ${C_D}/opt/clh-releases/current.zip${C_R}"
echo ""
echo "  ${C_YE}2.${C_R} Wildcard-DNS für Kunden-App-Hosts"
echo ""
echo "  ${C_YE}3.${C_R} Nginx aktivieren (${C_D}sites-enabled … && nginx -t …${C_R})"
echo ""
echo "  ${C_YE}4.${C_R} TLS mit Certbot (Provisioner + später Tenant-Domains)"
echo ""
echo "  ${C_YE}5.${C_R} Marketing: Secret + Provisioner-URL"
echo ""
echo "  ${C_YE}6.${C_R} Logs: ${C_D}journalctl -u clh-provisioner -f${C_R}"
echo ""
echo "  ${C_YE}7.${C_R} Nach GitHub-Push auf dem VPS: ${C_B}sudo /usr/local/bin/clh-cloud-host-update.sh${C_R}"
echo "     ${C_D}(optional neue Release-ZIP: …/clh-cloud-host-update.sh --with-zip)${C_R}"
echo ""
echo "  ${C_GR}${C_B}Firewall:${C_R} ${C_D}sudo ufw status numbered${C_R} — Ports ${C_GR}22 · 80 · 443${C_R} sollten ALLOW sein."
echo ""
