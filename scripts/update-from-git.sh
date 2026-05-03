#!/usr/bin/env bash
#
# Creator Link Hub — Update aus Git (ohne Datenverlust)
#
# Zieht Änderungen vom konfigurierten Remote/Branch, aktualisiert Composer/npm,
# führt nur neue Migrationen aus (php artisan migrate --force).
# .env, Datenbank-Inhalte und User bleiben unangetastet.
#
# Ausführung im Projektroot oder von überall (Skript wechselt selbst ins Repo):
#   bash scripts/update-from-git.sh
#   bash scripts/update-from-git.sh --dev          # Composer mit Dev-Abhängigkeiten
#   bash scripts/update-from-git.sh --yes         # Bei lokalem git status „dirty“ trotzdem fortfahren
#
# Server-Installation setzt CLH_APP_ROOT in .env (Projektroot). Weicht das aktuelle Verzeichnis davon ab,
# bricht das Skript ab — verhindert Updates/artisan in einem zweiten Klon (z. B. /root/...) mit falscher DB.
#
set -euo pipefail

die() { echo "Fehler: $*" >&2; exit 1; }
info() { echo "[Info] $*"; }
warn() { echo "[Warnung] $*" >&2; }

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$ROOT" || die "Projektroot nicht gefunden: $ROOT"

# Gleiches Verzeichnis wie bei der Server-Installation (siehe install-server.sh → CLH_APP_ROOT in .env).
if [[ -f .env ]]; then
  CLH_RAW="$(grep -E '^[[:space:]]*CLH_APP_ROOT=' .env 2>/dev/null | tail -n1 || true)"
  if [[ -n "$CLH_RAW" ]]; then
    CLH_VAL="${CLH_RAW#*=}"
    CLH_VAL="${CLH_VAL//$'\r'/}"
    CLH_VAL="${CLH_VAL#"${CLH_VAL%%[![:space:]]*}"}"
    CLH_VAL="${CLH_VAL%"${CLH_VAL##*[![:space:]]}"}"
    if [[ "$CLH_VAL" == '"'*'"' ]]; then
      CLH_VAL="${CLH_VAL:1:${#CLH_VAL}-2}"
    elif [[ "$CLH_VAL" == "'"* ]]; then
      CLH_VAL="${CLH_VAL#\'}"
      CLH_VAL="${CLH_VAL%\'}"
    fi
    if [[ -n "$CLH_VAL" ]]; then
      ROOT_RP="$(realpath "$ROOT")"
      if [[ -d "$CLH_VAL" ]]; then
        EXPECT_RP="$(realpath "$CLH_VAL")"
      else
        EXPECT_RP=""
      fi
      if [[ -n "$EXPECT_RP" && "$ROOT_RP" != "$EXPECT_RP" ]]; then
        die "Abbruch: Du bist in \"${ROOT_RP}\", in .env steht aber CLH_APP_ROOT=\"${CLH_VAL}\" → erwartet \"${EXPECT_RP}\". Nginx/PHP-FPM nutzen die Installation dort. Wechsel: cd \"${EXPECT_RP}\" && bash scripts/update-from-git.sh — keinen zweiten Klon für artisan/Updates nutzen."
      fi
    fi
  fi
fi

COMPOSER_NO_DEV=(--no-dev)
FORCE_DIRTY=false
for arg in "$@"; do
  case "$arg" in
    --dev) COMPOSER_NO_DEV=() ;;
    --yes|-y) FORCE_DIRTY=true ;;
    -h|--help)
      echo "Nutzen: bash scripts/update-from-git.sh [--dev] [--yes]"
      echo "  --dev   Composer ohne --no-dev (Entwicklung)"
      echo "  --yes   Fortfahren trotz uncommitteter lokaler Änderungen (Vorsicht)"
      echo "  (Auf dem Server muss CLH_APP_ROOT in .env zum aktuellen Projektroot passen — siehe install-server.sh.)"
      exit 0
      ;;
  esac
done

[[ -d .git ]] || die "Kein Git-Repository (.git fehlt). Bitte im geklonten creator-link-hub ausführen."

echo ""
echo "=========================================="
echo "  Creator Link Hub — Update aus Git"
echo "=========================================="
echo ""
echo "Verzeichnis: $ROOT"
echo "Branch:      $(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo '?')"
echo ""

if [[ -n "$(git status --porcelain 2>/dev/null)" ]]; then
  warn "Es gibt lokale, nicht committete Änderungen."
  if [[ "$FORCE_DIRTY" != true ]]; then
    die "Abbruch. Änderungen committen/stashen oder mit --yes fortfahren (riskant)."
  fi
fi

info "git fetch …"
git fetch origin

BRANCH="$(git rev-parse --abbrev-ref HEAD)"
COMMIT_BEFORE="$(git rev-parse --short HEAD)"
info "Aktueller Stand: $COMMIT_BEFORE ($BRANCH)"
info "git pull --ff-only (Branch: $BRANCH) …"
if ! git pull --ff-only origin "$BRANCH"; then
  die "Fast-Forward nicht möglich (abweichende Historie oder Konflikte). Bitte manuell: git status, git pull/rebase/merge."
fi
COMMIT_AFTER="$(git rev-parse --short HEAD)"
if [[ "$COMMIT_BEFORE" == "$COMMIT_AFTER" ]]; then
  warn "Git meldet: Branch ist bereits auf dem neuesten Stand (kein neuer Commit von origin/$BRANCH). Wenn du trotzdem alte Oberfläche siehst: Browser-Cache leeren, PHP-FPM/Webserver neu laden (Opcache), oder prüfen ob du auf dem richtigen Server/Branch bist."
else
  info "Neuer Stand: $COMMIT_AFTER (vorher: $COMMIT_BEFORE)"
fi

if [[ "${EUID:-0}" -eq 0 ]]; then
  export COMPOSER_ALLOW_SUPERUSER=1
fi

info "Composer …"
composer install "${COMPOSER_NO_DEV[@]}" --no-interaction --optimize-autoloader

if command -v npm &>/dev/null; then
  if [[ -f package-lock.json ]]; then
    info "npm ci && npm run build …"
    npm ci --no-fund --no-audit
  else
    warn "package-lock.json fehlt — npm install"
    npm install --no-fund --no-audit
  fi
  npm run build
else
  warn "npm nicht im PATH — überspringe Frontend-Build. Bei Bedarf: npm ci && npm run build"
fi

info "Datenbank-Migrationen (nur ausstehende; keine Daten löschen) …"
php artisan migrate --force

info "Filament-Assets aktualisieren (falls Filament beteiligt) …"
php artisan filament:upgrade -n 2>/dev/null || true

php artisan storage:link --force 2>/dev/null || true

info "Laravel-Cache neu aufbauen …"
php artisan config:cache
php artisan route:cache
php artisan view:cache

info "Queue-Worker zum Neustart signalisieren (nach nächstem Job) …"
php artisan queue:restart 2>/dev/null || warn "queue:restart nicht ausgeführt (Queue evtl. nicht konfiguriert)."

if [[ "${EUID:-0}" -eq 0 ]] && command -v supervisorctl &>/dev/null; then
  info "Supervisor-Worker neu starten (falls Programmname passt) …"
  supervisorctl restart 'creator-link-hub-worker:*' 2>/dev/null || true
fi

echo ""
echo "Update abgeschlossen."
echo "Hinweis: .env wurde nicht geändert. Neue ENV-Keys ggf. aus .env.example übernehmen."
echo ""
