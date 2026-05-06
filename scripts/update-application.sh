#!/usr/bin/env bash
#
# Creator Link Hub — Abhängigkeiten & Migrationen (ohne Git)
#
# Nach Austausch der App-Dateien (z. B. neues Release-ZIP) im Projektroot ausführen.
# Führt composer install, npm ci/build, migrate --force, Caches, optional Supervisor aus.
# .env und Datenbankinhalte bleiben unverändert; nur ausstehende Migrationen werden angewendet.
#
#   bash scripts/update-application.sh
#   bash scripts/update-application.sh --dev   # Composer ohne --no-dev
#
set -euo pipefail

die() { echo "Fehler: $*" >&2; exit 1; }
info() { echo "[Info] $*"; }
warn() { echo "[Warnung] $*" >&2; }

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$ROOT" || die "Projektroot nicht gefunden: $ROOT"

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
        die "Abbruch: Du bist in \"${ROOT_RP}\", in .env steht aber CLH_APP_ROOT=\"${CLH_VAL}\" → erwartet \"${EXPECT_RP}\". Nginx/PHP-FPM nutzen die Installation dort. Wechsel: cd \"${EXPECT_RP}\" && bash scripts/update-application.sh — nicht in einer zweiten Kopie ausführen."
      fi
    fi
  fi
fi

COMPOSER_NO_DEV=(--no-dev)
for arg in "$@"; do
  case "$arg" in
    --dev) COMPOSER_NO_DEV=() ;;
    -h|--help)
      echo "Nutzen: bash scripts/update-application.sh [--dev]"
      echo "  --dev   Composer ohne --no-dev (Entwicklung)"
      echo "  (Auf dem Server muss CLH_APP_ROOT in .env zum aktuellen Projektroot passen — siehe install-server.sh.)"
      exit 0
      ;;
  esac
done

[[ -f composer.json && -f artisan ]] || die "Kein Laravel-Projekt (composer.json/artisan fehlt)."

echo ""
echo "=========================================="
echo "  Creator Link Hub — Anwendungs-Update"
echo "=========================================="
echo ""
echo "Verzeichnis: $ROOT"
echo ""

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
