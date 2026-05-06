#!/usr/bin/env bash
#
# Cloud-App-Host mit dem GitHub-Stand synchronisieren (nach Push ins Repo creator-link-hub):
#   git pull · provisioner.php + router.php · Tenant-Shell-Skripte · optional neue Release-ZIP
# · clh-provisioner neu starten · Nginx reload
#
# Pfade/Branch: /etc/clh-provisioner/install-paths.env (wird vom Bootstrap geschrieben)
#
# Aufruf (als root):
#   sudo /usr/local/bin/clh-cloud-host-update.sh
#   sudo /usr/local/bin/clh-cloud-host-update.sh --with-zip    # zusätzlich npm ci + vite + ZIP → /opt/clh-releases/current.zip
#
# Bestehende Tenant-Instanzen unter /var/www/clh-tenants/<slug>/ werden NICHT angepasst.
#
set -euo pipefail

# Kein interaktives Git-Prompt (sonst hängt das Skript z. B. bei HTTPS + fehlendem Token als root).
export GIT_TERMINAL_PROMPT=0

PATH_CFG=/etc/clh-provisioner/install-paths.env

die() { echo "[clh-update] Fehler: $*" >&2; exit 1; }
log() { echo "[clh-update] $*"; }

[[ "${EUID:-0}" -eq 0 ]] || die "Bitte als root: sudo $0"
[[ -f "$PATH_CFG" ]] || die "Konfiguration fehlt: $PATH_CFG — bootstrap-cloud-host.sh erneut ausführen oder Datei manuell anlegen."

# shellcheck disable=SC1090
source "$PATH_CFG"

: "${CLH_REPO_ROOT:?In $PATH_CFG muss CLH_REPO_ROOT gesetzt sein}"

CLH_GIT_REF="${CLH_GIT_REF:-main}"
WITH_ZIP=false
if [[ "${1:-}" == "--with-zip" ]]; then
  WITH_ZIP=true
fi

[[ -d "$CLH_REPO_ROOT/.git" ]] || die "Kein Git-Repository unter: $CLH_REPO_ROOT"

command -v git >/dev/null 2>&1 || die "git nicht im PATH — z. B. apt install git (als root)."

log "Repository: $CLH_REPO_ROOT (Branch/Ref: $CLH_GIT_REF)"

cd "$CLH_REPO_ROOT"

log "git fetch --prune origin …"
git fetch --prune origin || die "git fetch fehlgeschlagen — Netzwerk, Remote „origin“ oder Zugriff (öffentliches Repo: URL prüfen; privat: Deploy-Key/Credential für den User, der hier pullt, root hat oft keine GitHub-Credentials)."

log "git checkout $CLH_GIT_REF …"
git checkout "$CLH_GIT_REF" || die "git checkout fehlgeschlagen — existiert Branch/Tag „$CLH_GIT_REF“? In $PATH_CFG ggf. CLH_GIT_REF anpassen."

log "git pull --ff-only origin $CLH_GIT_REF …"
if ! git pull --ff-only "origin" "$CLH_GIT_REF"; then
  log "Hinweis: pull mit explizitem Ref fehlgeschlagen, versuche git pull --ff-only (Upstream) …"
  git pull --ff-only || die "git pull fehlgeschlagen — Konflikte oder abweichender Upstream (git status, git branch -vv)."
fi

PROV="$CLH_REPO_ROOT/deployment/cloud-host/provisioner.php"
ROUT="$CLH_REPO_ROOT/deployment/cloud-host/router.php"
[[ -f "$PROV" && -f "$ROUT" ]] || die "deployment/cloud-host/provisioner.php oder router.php fehlt im Repo."

log "Provisioner → /opt/clh-provisioner/"
install -m 0644 "$PROV" /opt/clh-provisioner/provisioner.php
install -m 0644 "$ROUT" /opt/clh-provisioner/router.php
chown clh-provisioner:clh-provisioner /opt/clh-provisioner/provisioner.php /opt/clh-provisioner/router.php

log "Tenant-Skripte → /usr/local/bin/"
for s in clh-provision-tenant.sh clh-delete-tenant.sh clh-suspend-tenant.sh; do
  [[ -f "$CLH_REPO_ROOT/scripts/$s" ]] || die "Skript fehlt: scripts/$s"
  install -m 0755 "$CLH_REPO_ROOT/scripts/$s" "/usr/local/bin/$s"
done

SELF="$CLH_REPO_ROOT/scripts/clh-cloud-host-update.sh"
[[ -f "$SELF" ]] || die "Skript fehlt: scripts/clh-cloud-host-update.sh"
install -m 0755 "$SELF" /usr/local/bin/clh-cloud-host-update.sh

if [[ "$WITH_ZIP" == true ]]; then
  log "Release-ZIP bauen (npm ci, vite, zip) …"
  command -v npm >/dev/null 2>&1 || die "npm nicht im PATH — Node installieren oder ohne --with-zip ausführen."
  command -v zip >/dev/null 2>&1 || die "zip nicht im PATH — apt install zip"
  ( cd "$CLH_REPO_ROOT" && bash scripts/build-cloud-release-zip.sh )
  install -d -m 0755 /opt/clh-releases
  cp "$CLH_REPO_ROOT/distribution/releases/current-cloud.zip" /opt/clh-releases/current.zip
  chmod 644 /opt/clh-releases/current.zip
  log "/opt/clh-releases/current.zip aktualisiert."
fi

log "Dienste: clh-provisioner neu starten, Nginx testen …"
systemctl restart clh-provisioner.service
nginx -t && systemctl reload nginx || log "WARN: nginx -t/reload ist fehlgeschlagen — manuell prüfen."

log "Fertig. Bestehende Tenants: weiterhin einzeln aktualisieren (Migration/Deploy pro Instanz)."
echo ""
echo "  Health (lokal): curl -sS http://127.0.0.1:9100/health"
