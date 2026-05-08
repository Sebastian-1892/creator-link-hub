#!/usr/bin/env bash
#
# Cloud-App-Host mit dem GitHub-Stand synchronisieren (nach Push ins Repo creator-link-hub):
#   git pull · provisioner.php + router.php · Tenant-Shell-Skripte (provision/delete/suspend/resume) · optional neue Release-ZIP
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

# VPS: oft lokale Edits (z. B. an bootstrap-cloud-host.sh) — ohne Stash blockiert pull --ff-only.
STASHED=0
if [[ -n "$(git status --porcelain 2>/dev/null)" ]]; then
  log "Lokale Änderungen im Arbeitsbaum → temporärer Stash vor git pull …"
  git stash push -m "clh-cloud-host-update autostash $(date -Iseconds 2>/dev/null || date +%Y%m%d%H%M%S)" || die "git stash fehlgeschlagen — manuell committen/stashen und erneut ausführen."
  STASHED=1
fi

log "git pull --ff-only origin $CLH_GIT_REF …"
if ! git pull --ff-only "origin" "$CLH_GIT_REF"; then
  log "Hinweis: pull mit explizitem Ref fehlgeschlagen, versuche git pull --ff-only (Upstream) …"
  git pull --ff-only || die "git pull fehlgeschlagen — Konflikte oder abweichender Upstream (git status, git branch -vv)."
fi

if [[ "$STASHED" -eq 1 ]]; then
  if git stash pop; then
    log "Stash nach Pull wieder eingespielt."
  else
    log "WARN: git stash pop fehlgeschlagen (Konflikt mit upstream). cd $CLH_REPO_ROOT — git status, git stash list — Konflikt lösen; sonst stash verwerfen: git stash drop"
  fi
fi

PROV="$CLH_REPO_ROOT/deployment/cloud-host/provisioner.php"
ROUT="$CLH_REPO_ROOT/deployment/cloud-host/router.php"
[[ -f "$PROV" && -f "$ROUT" ]] || die "deployment/cloud-host/provisioner.php oder router.php fehlt im Repo."

log "Provisioner → /opt/clh-provisioner/"
install -m 0644 "$PROV" /opt/clh-provisioner/provisioner.php
install -m 0644 "$ROUT" /opt/clh-provisioner/router.php
chown clh-provisioner:clh-provisioner /opt/clh-provisioner/provisioner.php /opt/clh-provisioner/router.php

SMOKE="$CLH_REPO_ROOT/deployment/cloud-host/vps-smoke-provision.php"
if [[ -f "$SMOKE" ]]; then
  install -m 0644 "$SMOKE" /opt/clh-provisioner/vps-smoke-provision.php
  chown root:root /opt/clh-provisioner/vps-smoke-provision.php
  log "Smoke-Tester: sudo php /opt/clh-provisioner/vps-smoke-provision.php 'http://127.0.0.1:9100/' VPSTEST-SLUG"
else
  log "WARN: vps-smoke-provision.php fehlt unter deployment/cloud-host/ — Smoke-Tests manuell aus Marketing-Repo kopieren."
fi

log "Tenant-Skripte → /usr/local/bin/"
for s in clh-provision-tenant.sh clh-delete-tenant.sh clh-suspend-tenant.sh clh-resume-tenant.sh clh-tenant-enable-tls.sh; do
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

SUDO_FP=/etc/sudoers.d/clh-provisioner
if [[ -f "$SUDO_FP" ]] && ! grep -qF 'clh-resume-tenant.sh' "$SUDO_FP" 2>/dev/null; then
  if grep -qE 'NOPASSWD:.*clh-suspend-tenant\.sh' "$SUDO_FP"; then
    log "sudoers: clh-resume-tenant.sh nach clh-suspend-tenant ergänzen …"
    sed -i 's#/usr/local/bin/clh-suspend-tenant\.sh#/usr/local/bin/clh-suspend-tenant.sh, /usr/local/bin/clh-resume-tenant.sh#' "$SUDO_FP"
    chmod 0440 "$SUDO_FP"
    visudo -c -f "$SUDO_FP" || die "sudoers nach Ergänzung von resume ungültig — Datei prüfen: $SUDO_FP"
  else
    log "WARN: $SUDO_FP passt nicht zum erwarteten Muster — resume manuell wie in bootstrap-cloud-host.sh (Schritt 9) ergänzen."
  fi
fi

if [[ -f "$SUDO_FP" ]] && ! grep -qF 'clh-tenant-enable-tls.sh' "$SUDO_FP" 2>/dev/null; then
  if grep -qF 'clh-resume-tenant.sh' "$SUDO_FP"; then
    log "sudoers: clh-tenant-enable-tls.sh ergänzen …"
    sed -i 's#/usr/local/bin/clh-resume-tenant\.sh#/usr/local/bin/clh-resume-tenant.sh, /usr/local/bin/clh-tenant-enable-tls.sh#' "$SUDO_FP"
    chmod 0440 "$SUDO_FP"
    visudo -c -f "$SUDO_FP" || die "sudoers nach TLS-Skript ungültig — Datei prüfen: $SUDO_FP"
  elif grep -qE 'NOPASSWD:.*clh-suspend-tenant\.sh' "$SUDO_FP"; then
    log "sudoers: clh-resume + enable-tls fehlen — bitte /etc/sudoers.d/clh-provisioner mit bootstrap-cloud-host Schritt 9 abgleichen."
  fi
fi

log "Fertig. Bestehende Tenants: weiterhin einzeln aktualisieren (Migration/Deploy pro Instanz)."
echo ""
echo "  Health (lokal): curl -sS http://127.0.0.1:9100/health"
