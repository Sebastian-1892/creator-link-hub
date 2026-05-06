#!/usr/bin/env bash
#
# Interaktiver Cloud-Host-Installer (Ubuntu/Debian VPS):
#   Git-Clone (oder Aktualisieren), Bootstrap (inkl. UFW Firewall), optional Release-ZIP, Nginx, optional Certbot.
#
# Aufruf (empfohlen):
#   sudo bash scripts/install-cloud-host-interactive.sh
#
# Von einem „nackten“ Server aus (nur curl + sudo):
#   sudo apt-get update && sudo apt-get install -y curl ca-certificates
#   curl -fsSL https://raw.githubusercontent.com/Sebastian-1892/creator-link-hub/main/scripts/install-cloud-host-interactive.sh -o /tmp/clh-install.sh && sudo bash /tmp/clh-install.sh
#
# Farben Abschalten: NO_COLOR=1 oder CLH_NO_COLOR=1
#
set -euo pipefail

DEFAULT_GIT_URL="https://github.com/Sebastian-1892/creator-link-hub.git"

C_R=""
C_B=""
C_D=""
C_CY=""
C_GR=""
C_YE=""
C_RE=""
C_MA=""
if [[ -t 1 ]] && [[ -z "${NO_COLOR:-}${CLH_NO_COLOR:-}" ]]; then
  C_R=$'\033[0m'
  C_B=$'\033[1m'
  C_D=$'\033[90m'
  C_CY=$'\033[38;5;39m'
  C_GR=$'\033[38;5;71m'
  C_YE=$'\033[38;5;178m'
  C_RE=$'\033[38;9m'
  C_MA=$'\033[38;5;183m'
fi

banner() {
  echo ""
  echo "${C_CY}${C_B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${C_R}"
  printf '%s\n' "${C_CY}${C_B}       $*${C_R}"
  echo "${C_CY}${C_B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${C_R}"
  echo ""
}

die() {
  echo ""
  echo "${C_RE}${C_B}✖ Fehler:${C_R} $*" >&2
  exit 1
}

section() {
  echo ""
  echo "${C_MA}${C_B}━━ $* ━━${C_R}"
  echo ""
}

info() { echo "${C_GR}${C_B}●${C_R} ${C_GR}[install-cloud]${C_R} $*"; }

[[ "${EUID:-0}" -eq 0 ]] || die "Bitte als root starten: sudo bash $0"

INPUT_DEV=/dev/stdin
if [[ -r /dev/tty ]]; then INPUT_DEV=/dev/tty; fi

_prompt_pre="${C_CY}[install-cloud]${C_R} ${C_B}"

prompt() {
  local text="$1"
  local default="${2:-}"
  local reply=""
  if [[ -n "$default" ]]; then
    read -r -p "${_prompt_pre}${text} ${C_D}[$default]${C_R}: " reply <"$INPUT_DEV" || true
    echo "${reply:-$default}"
  else
    read -r -p "${_prompt_pre}${text}: " reply <"$INPUT_DEV" || true
    echo "$reply"
  fi
}

yn() {
  local text="$1"
  local default="${2:-n}"
  local p="j/N"
  [[ "$default" == "y" ]] && p="J/n"
  local r
  r=$(prompt "$text (${p})" "")
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

banner "Creator Link Hub — Cloud-Host (Wizard)"

echo "  ${C_D}Dieser Wizard klont oder aktualisiert das Repo, führt dann das Bootstrap aus."
echo "  Dabei werden u. a. installiert:${C_R} Nginx · MariaDB · PHP-FPM · ${C_GR}UFW (Firewall:${C_R} SSH, 80, 443)."
echo ""

echo "  ${C_YE}${C_B}Hinweise${C_R}"
echo ""

echo "  ${C_D}• DNS:${C_R} A-Record für den späteren Provisioner-Host auf diese VPS-IP."
echo ""

echo "  ${C_D}• Wildcard-DNS (*.app…)${C_R} für Kunden-Subdomains vorbereiten."
echo ""

section "Git & Zielpfad"

GIT_URL="$(prompt "Git-Repository-URL" "$DEFAULT_GIT_URL")"
[[ -n "$GIT_URL" ]] || die "Git-URL darf nicht leer sein."

CLONE_PARENT="$(prompt "Ordner für den Klon (Elternverzeichnis)" "/opt/creator-link-hub-src")"
[[ -n "$CLONE_PARENT" ]] || die "Klon-Elternordner darf nicht leer sein."

GIT_REF="$(prompt "Git-Branch oder Tag auschecken (z. B. main)" "main")"
[[ -n "$GIT_REF" ]] || die "Branch/Tag darf nicht leer sein."

REPO="${CLONE_PARENT%/}/creator-link-hub"

echo ""
info "Paketgrundlage (git, curl) …"

export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq git curl ca-certificates

install -d -m 0755 "$CLONE_PARENT"

section "Repository"

if [[ -d "$REPO/.git" ]]; then
  info "Vorhandenes Repository gefunden unter ${C_B}$REPO${C_R}"
  git -C "$REPO" fetch --quiet origin || die "git fetch fehlgeschlagen"
  git -C "$REPO" checkout --quiet "$GIT_REF" || die "checkout $GIT_REF fehlgeschlagen"
  git -C "$REPO" pull --quiet --ff-only origin "$GIT_REF" 2>/dev/null || git -C "$REPO" pull --quiet --ff-only || info "Hinweis: Pull nicht strikt FF — Branch manuell prüfen."
else
  if [[ -e "$REPO" ]]; then
    die "Pfad existiert, ist aber kein Git-Repo: $REPO — bitte umbenennen oder löschen."
  fi
  info "Klone nach ${C_B}$REPO${C_R} …"
  git clone --depth 1 --branch "$GIT_REF" "$GIT_URL" "$REPO" 2>/dev/null || {
    git clone "$GIT_URL" "$REPO" || die "git clone fehlgeschlagen"
    git -C "$REPO" checkout "$GIT_REF" || die "checkout $GIT_REF fehlgeschlagen"
  }
fi

is_repo_root "$REPO" || die "Kein erwartetes Repo-Layout unter $REPO"

BOOTSTRAP="$REPO/scripts/bootstrap-cloud-host.sh"
chmod +x "$BOOTSTRAP" 2>/dev/null || true

section "Provisioner-Hostnamen"

PROVISIONER_FQDN=""
while [[ -z "$PROVISIONER_FQDN" ]]; do
  PROVISIONER_FQDN="$(prompt "Provisioner-FQDN (Nginx/TLS für diesen Host, z. B. provision.app.example.de)" "")"
  PROVISIONER_FQDN="${PROVISIONER_FQDN// /}"
done

echo ""
echo "  ${C_D}Nach dem Bootstrap ist die Firewall aktiv: nur SSH sowie HTTP/S auf Port 80/443.${C_R}"
echo ""

section "Bootstrap (Pakete · UFW · Nginx-Vorlage · MariaDB · Provisioner)"

info "Starte ${C_B}scripts/bootstrap-cloud-host.sh${C_R} …"

export CLH_GIT_REF="$GIT_REF"

bash "$BOOTSTRAP"

NGINX_SITE="/etc/nginx/sites-available/clh-provisioner.conf"
[[ -f "$NGINX_SITE" ]] || die "Nginx-Beispieldatei fehlt: $NGINX_SITE"

section "Provisioner Nginx"


info "server_name auf ${C_B}$PROVISIONER_FQDN${C_R} setzen …"

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
  info "Provisioner-Nginx aktiv (Reverse-Proxy zu 127.0.0.1:9100)."
fi

section "Release-ZIP (optional)"

DO_BUILD=true
yn "Release-ZIP bauen (npm ci · Vite · zip → /opt/clh-releases/current.zip)? Kann einige Minuten dauern." "y" || DO_BUILD=false

if [[ "$DO_BUILD" == true ]]; then
  echo ""
  echo "  ${C_D}Welche Node-Variante soll installiert werden?${C_R}"
  echo ""

  echo "      ${C_YE}1${C_R}  Pakete ${C_D}nodejs / npm${C_R} über apt (Version abhängig von Ubuntu)"
  echo ""

  echo "      ${C_YE}2${C_R}  ${C_D}NodeSource 20.x LTS${C_R} (gleiche Major-Version wie lokal oft sinnvoll)"
  echo ""

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
  info "Baue ZIP im Repo-Verzeichnis …"
  (cd "$REPO" && bash scripts/build-cloud-release-zip.sh)
  install -d -m 0755 /opt/clh-releases
  cp "$REPO/distribution/releases/current-cloud.zip" /opt/clh-releases/current.zip
  chmod 644 /opt/clh-releases/current.zip
  info "ZIP liegt unter ${C_B}/opt/clh-releases/current.zip${C_R}"
else
  if [[ ! -f /opt/clh-releases/current.zip ]]; then
    echo ""
    info "${C_YE}Ohne ZIP können keine neuen Tenants angelegt werden.${C_R} Pfad später befüllen."
    echo ""
  fi
fi

section "Let's Encrypt für den Provisioner (optional)"

if yn "Jetzt TLS mit Certbot für den Provisioner-FQDN? (DNS muss auf dieses System zeigen.)" "n"; then
  CERTBOT_EMAIL="$(prompt "E-Mail für Let's Encrypt (ACME-Kontakt)" "")"
  [[ -n "$CERTBOT_EMAIL" ]] || die "Für Certbot wird eine gültige E-Mail benötigt."
  apt-get install -y -qq certbot python3-certbot-nginx
  certbot --nginx -d "$PROVISIONER_FQDN" --non-interactive --agree-tos -m "$CERTBOT_EMAIL" --redirect \
    || info "Certbot konnte nicht automatisch durchlaufen — DNS / Port 443 prüfen: ${C_D}sudo certbot --nginx -d $PROVISIONER_FQDN${C_R}"
fi

systemctl restart clh-provisioner.service 2>/dev/null || true

banner "Fertig"

echo ""

echo "${C_GR}${C_B}Provisioner (Beispiel-URL für das Marketing):${C_R}"
echo ""

echo "  ${C_CY}https://${PROVISIONER_FQDN}/${C_R}"
echo ""

echo "${C_GR}${C_B}Geheimnis (HMAC für provisioner.hmac_secret):${C_R}"
echo ""

echo "  ${C_D}sudo cat /etc/clh-provisioner/secret${C_R}"
echo ""

echo "${C_GR}${C_B}Firewall prüfen:${C_R}"
echo ""

echo "  ${C_D}sudo ufw status numbered${C_R}"
echo ""

echo "${C_GR}${C_B}Nach Git-Push — Host aktualisieren:${C_R}"
echo ""

echo "  ${C_D}sudo /usr/local/bin/clh-cloud-host-update.sh${C_R}"
echo "  ${C_D}(optional ZIP neu bauen und nach /opt/clh-releases/current.zip: … --with-zip)${C_R}"
echo ""

echo "${C_GR}${C_B}Lokaler Health-Check:${C_R}"
echo ""

echo "  ${C_D}curl -sS http://127.0.0.1:9100/health | head${C_R}"
echo ""
