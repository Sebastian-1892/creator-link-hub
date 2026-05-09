#!/usr/bin/env bash
#
# Creator Link Hub — Abhängigkeiten & Migrationen (ohne Git)
#
# Nach Austausch der App-Dateien (z. B. neues Release-ZIP) im Projektroot ausführen.
# Führt composer install, npm ci/build, migrate --force, Caches, optional Supervisor aus.
# .env und Datenbankinhalte bleiben unverändert; nur ausstehende Migrationen werden angewendet.
#
# Wenn als root gestartet: nach composer vendor/storage/cache → www-data; npm läuft immer als
# www-data (vermeidet root-eigene node_modules). Als www-data (Filament-Dashboard): unverändert.
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

# Vor Composer/Artisan: Verzeichnisse müssen existieren (Rollout rsync schließt bootstrap/cache u. U. aus).
mkdir -p "$ROOT/bootstrap/cache"
mkdir -p "$ROOT/storage/framework/cache/data" "$ROOT/storage/framework/sessions" "$ROOT/storage/framework/views" "$ROOT/storage/logs"
chmod ug+rwx "$ROOT/bootstrap/cache" 2>/dev/null || true
if [[ "${EUID:-0}" -eq 0 ]] && id www-data &>/dev/null; then
  chown www-data:www-data "$ROOT/bootstrap/cache"
  chown -R www-data:www-data "$ROOT/storage" 2>/dev/null || true
fi

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

# Tenant-Kopien können .git vom Host-Klon enthalten (www-data) — Composer läuft oft als root → „dubious ownership“.
if [[ -d "$ROOT/.git" ]] && [[ "${EUID:-0}" -eq 0 ]]; then
  git config --global --add safe.directory "$ROOT" 2>/dev/null || true
fi

info "Composer …"
composer install "${COMPOSER_NO_DEV[@]}" --no-interaction --optimize-autoloader

# Nach composer als root: vendor/cache/storage für PHP-FPM (www-data) konsistent — vermeidet gemischte root/www-data-Bäume.
if [[ "${EUID:-0}" -eq 0 ]] && id www-data &>/dev/null; then
  chown -R www-data:www-data "$ROOT/vendor" "$ROOT/bootstrap/cache" "$ROOT/storage" 2>/dev/null || true
fi

if command -v npm &>/dev/null; then
  # Projektbezogener npm-Cache — nie /var/www/.npm mit root-only (Dashboard läuft als www-data).
  export NPM_CONFIG_CACHE="${ROOT}/storage/npm-cache"
  mkdir -p "$NPM_CONFIG_CACHE"

  RUN_NPM_AS_WWW=0
  if [[ "${EUID:-0}" -eq 0 ]] && id www-data &>/dev/null; then
    RUN_NPM_AS_WWW=1
    info "npm wird als www-data ausgeführt (keine root-eigenen node_modules)."
    chown -R www-data:www-data "$NPM_CONFIG_CACHE"
    if [[ -d "$ROOT/node_modules" ]]; then
      if ! chown -R www-data:www-data "$ROOT/node_modules" 2>/dev/null; then
        warn "node_modules ließen sich nicht auf www-data umbiegen — Verzeichnis wird entfernt, npm ci baut neu auf."
        rm -rf "$ROOT/node_modules"
      fi
    fi
  elif [[ -d "$ROOT/node_modules" ]]; then
    # Läuft bereits als www-data (z. B. Filament-Dashboard): fremde Besitzer nur melden.
    foreign=$(find "$ROOT/node_modules" -not -user "$(id -u)" 2>/dev/null | head -1)
    if [[ -n "${foreign:-}" ]]; then
      die "node_modules enthält Dateien anderer Besitzer (z. B. ${foreign}). Einmal als root: sudo bash \"$ROOT/scripts/update-application.sh\" oder: sudo rm -rf \"$ROOT/node_modules\" && sudo chown -R www-data:www-data \"$ROOT\""
    fi
    if ! touch "$ROOT/node_modules/.clh_write_probe" 2>/dev/null; then
      die "Schreibzugriff auf node_modules fehlgeschlagen. Als root: sudo rm -rf \"$ROOT/node_modules\" && sudo chown -R www-data:www-data \"$ROOT\""
    fi
    rm -f "$ROOT/node_modules/.clh_write_probe"
  fi

  info "npm-Cache: ${NPM_CONFIG_CACHE}"

  npm_do() {
    local inner=$1
    if [[ "$RUN_NPM_AS_WWW" -eq 1 ]]; then
      sudo -u www-data env NPM_CONFIG_CACHE="$NPM_CONFIG_CACHE" HOME=/var/www bash -lc "cd \"$ROOT\" && $inner"
    else
      bash -lc "cd \"$ROOT\" && $inner"
    fi
  }

  if [[ -f package-lock.json ]]; then
    info "npm ci && npm run build …"
    npm_do "npm ci --no-fund --no-audit"
  else
    warn "package-lock.json fehlt — npm install"
    npm_do "npm install --no-fund --no-audit"
  fi
  npm_do "npm run build"
else
  warn "npm nicht im PATH — überspringe Frontend-Build. Bei Bedarf: npm ci && npm run build"
fi

info "Datenbank-Migrationen (nur ausstehende; keine Daten löschen) …"
php artisan migrate --force

info "Filament-Assets aktualisieren (falls Filament beteiligt) …"
php artisan filament:upgrade -n 2>/dev/null || true

info "Storage (Uploads, Logs), Symlink public/storage und Rechte für www-data …"
bash "$ROOT/scripts/ensure-laravel-storage.sh" "$ROOT"

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
