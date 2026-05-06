#!/usr/bin/env bash
#
# Interaktiver Cloud-Host-Installer (Ubuntu/Debian VPS):
#   Git-Clone (oder Aktualisieren), Bootstrap, optional Release-ZIP bauen, Nginx, optional Certbot.
#
# Aufruf (empfohlen):
#   sudo bash scripts/install-cloud-host-interactive.sh
#
# Von einem „nackten“ Server aus (nur curl + sudo):
#   sudo apt-get update && sudo apt-get install -y curl ca-certificates
#   curl -fsSL https://raw.githubusercontent.com/Sebastian-1892/creator-link-hub/main/scripts/install-cloud-host-interactive.sh | sudo bash
#
set -euo pipefail

DEFAULT_GIT_URL="https://github.com/Sebastian-1892/creator-link-hub.git"

die() { echo "[install-cloud] Fehler: $*" >&2; exit 1; }
info() { echo "[install-cloud] $*"; }

[[ "${EUID:-0}" -eq 0 ]] || die "Bitte als root starten: sudo bash $0"

# Eingaben vom Terminal lesen — bei „curl | sudo bash“ ist stdin das Skript, nicht die Tastatur
INPUT_DEV=/dev/stdin
if [[ -r /dev/tty ]]; then INPUT_DEV=/dev/tty; fi

prompt() {
  local text="$1"
  local default="${2:-}"
  local reply=""
  if [[ -n "$default" ]]; then
    read -r -p "[install-cloud] ${text} [${default}]: " reply <"$INPUT_DEV" || true
    echo "${reply:-$default}"
  else
    read -r -p "[install-cloud] ${text}: " reply <"$INPUT_DEV" || true
    echo "$reply"
  fi
}

yn() {
  local text="$1"
  local default="${2:-n}"
  local p="j/N"; [[ "$default" == "y" ]] && p="J/n"
  local r
  r=$(prompt "$text ($p)" "")
  r="${r,,}"
  if [[ -z "$r" ]]; then
    [[ "$default" == "y" ]] && return 0
    return 1
  fi
  [[ "$r" == j || "$r" == ja || "$r" == y || "$r" == yes ]]
}

is_repo_root() {
  [[ -f "$1/scripts/bootstrap-cloud-host.sh" && -f "$1/deployment/cloud-host/provisioner.php" ]]
}

echo ""
echo "=========================================="
echo "  Creator Link Hub — Cloud-Host (Wizard)"
echo "=========================================="
echo ""
info "Vorarbeit: DNS A-Record für den **Provisioner-Host** sollte auf diese Server-IP zeigen,"
info "bevor Let's Encrypt läuft. Wildcard (*.app…) ist für spätere Kunden-Tenants nötig."
echo ""

GIT_URL="$(prompt "Git-Repository-URL" "$DEFAULT_GIT_URL")"
[[ -n "$GIT_URL" ]] || die "Git-URL darf nicht leer sein."

CLONE_PARENT="$(prompt "Ordner für den Klon (Elternverzeichnis)" "/opt/creator-link-hub-src")"
[[ -n "$CLONE_PARENT" ]] || die "Klon-Elternordner darf nicht leer sein."

GIT_REF="$(prompt "Git-Branch oder Tag auschecken (z.B. main)" "main")"
[[ -n "$GIT_REF" ]] || die "Branch/Tag darf nicht leer sein."

REPO="${CLONE_PARENT%/}/creator-link-hub"

info "Paketgrundlage (git, curl) …"
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq git curl ca-certificates

install -d -m 0755 "$CLONE_PARENT"

if [[ -d "$REPO/.git" ]]; then
  info "Vorhandenes Repository: $REPO — aktualisiere …"
  git -C "$REPO" fetch --quiet origin || die "git fetch fehlgeschlagen"
  git -C "$REPO" checkout --quiet "$GIT_REF" || die "checkout $GIT_REF fehlgeschlagen"
  git -C "$REPO" pull --quiet --ff-only origin "$GIT_REF" 2>/dev/null || git -C "$REPO" pull --quiet --ff-only || info "Hinweis: pull nicht strikt FF — manuell prüfen"
else
  if [[ -e "$REPO" ]]; then
    die "Pfad existiert, ist aber kein Git-Repo: $REPO — bitte umbenennen oder löschen."
  fi
  info "Klone nach $REPO …"
  git clone --depth 1 --branch "$GIT_REF" "$GIT_URL" "$REPO" 2>/dev/null || {
    git clone "$GIT_URL" "$REPO" || die "git clone fehlgeschlagen"
    git -C "$REPO" checkout "$GIT_REF" || die "checkout $GIT_REF fehlgeschlagen"
  }
fi

is_repo_root "$REPO" || die "Kein erwartetes Repo-Layout unter $REPO"

BOOTSTRAP="$REPO/scripts/bootstrap-cloud-host.sh"
chmod +x "$BOOTSTRAP" 2>/dev/null || true

PROVISIONER_FQDN=""
while [[ -z "$PROVISIONER_FQDN" ]]; do
  PROVISIONER_FQDN="$(prompt "Provisioner-Hostnamen (FQDN, nur dieser Host für Nginx/Certbot, z.B. provision.app.example.de)" "")"
  PROVISIONER_FQDN="${PROVISIONER_FQDN// /}"
done

info "Bootstrap (Nginx, MariaDB, PHP-FPM, Provisioner, systemd) …"
bash "$BOOTSTRAP"

NGINX_SITE="/etc/nginx/sites-available/clh-provisioner.conf"
[[ -f "$NGINX_SITE" ]] || die "Nginx-Beispieldatei fehlt: $NGINX_SITE (Bootstrap prüfen)"

info "Setze server_name in $NGINX_SITE → $PROVISIONER_FQDN"
export CLH_NGINX_FQDN="$PROVISIONER_FQDN"
if command -v perl >/dev/null 2>&1; then
  perl -0777 -i -pe 's/^([\t ]*server_name\s+)[^;]+;/$1$ENV{CLH_NGINX_FQDN};/m' "$NGINX_SITE"
else
  sed -i "s/^[[:space:]]*server_name[[:space:]].*;/    server_name $PROVISIONER_FQDN;/" "$NGINX_SITE"
fi
unset CLH_NGINX_FQDN

DO_NGINX_ENABLE=true
yn "Nginx-Site aktivieren und Nginx neu laden?" "y" || DO_NGINX_ENABLE=false
if [[ "$DO_NGINX_ENABLE" == true ]]; then
  ln -sf "$NGINX_SITE" /etc/nginx/sites-enabled/clh-provisioner.conf
  nginx -t
  systemctl reload nginx
  info "Nginx aktiv (HTTP → Provisioner)."
fi

DO_BUILD=true
yn "Release-ZIP bauen (npm ci + vite + zip → /opt/clh-releases/current.zip)? Dauert mehrere Minuten." "y" || DO_BUILD=false

if [[ "$DO_BUILD" == true ]]; then
  echo ""
  info "Welche Node-Installation?"
  info "  1 = apt paket nodejs + npm (schnell, Version abhängig von Ubuntu)"
  info "  2 = NodeSource 20.x LTS (einheitliche Version)"
  NODE_CHOICE="$(prompt "Auswahl [1 oder 2]" "1")"
  apt-get install -y -qq zip
  case "$NODE_CHOICE" in
    2)
      apt-get install -y -qq ca-certificates gnupg
      curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
      apt-get install -y -qq nodejs
      ;;
    *)
      apt-get install -y -qq nodejs npm
      ;;
  esac
  command -v npm >/dev/null 2>&1 || die "npm fehlt nach Node-Installation"
  command -v zip >/dev/null 2>&1 || die "zip fehlt (apt-get install zip)"
  info "Baue ZIP im Repo …"
  ( cd "$REPO" && bash scripts/build-cloud-release-zip.sh )
  install -d -m 0755 /opt/clh-releases
  cp "$REPO/distribution/releases/current-cloud.zip" /opt/clh-releases/current.zip
  chmod 644 /opt/clh-releases/current.zip
  info "Release liegt unter /opt/clh-releases/current.zip"
else
  if [[ ! -f /opt/clh-releases/current.zip ]]; then
    info "Hinweis: /opt/clh-releases/current.zip fehlt — ohne ZIP können keine neuen Tenants angelegt werden."
  fi
fi

if yn "Jetzt TLS mit Certbot (--nginx)? DNS muss bereits auf diesen Host zeigen." "n"; then
  CERTBOT_EMAIL="$(prompt "E-Mail für Let's Encrypt (ACME)" "")"
  [[ -n "$CERTBOT_EMAIL" ]] || die "Für Certbot wird eine gültige E-Mail benötigt (Abbruch TLS)."
  apt-get install -y -qq certbot python3-certbot-nginx
  certbot --nginx -d "$PROVISIONER_FQDN" --non-interactive --agree-tos -m "$CERTBOT_EMAIL" --redirect \
    || info "Certbot ist fehlgeschlagen — DNS/T Ports prüfen, später manuell: sudo certbot --nginx -d $PROVISIONER_FQDN"
fi

systemctl restart clh-provisioner.service 2>/dev/null || true

echo ""
echo "=========================================="
info "Setup abgeschlossen."
echo "=========================================="
echo ""
info "Provisioner-URL für das Marketing (Beispiel): https://${PROVISIONER_FQDN}/"
info "HMAC-Geheimnis (provisioner.hmac_secret): sudo cat /etc/clh-provisioner/secret"
echo ""
info "Lokaler Health-Check:"
echo "    curl -sS http://127.0.0.1:9100/health | head"
echo ""
