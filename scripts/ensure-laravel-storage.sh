#!/usr/bin/env bash
#
# Creator Link Hub — Laravel storage/bootstrap/cache für PHP-FPM (typisch www-data):
#   - benötigte Unterverzeichnisse
#   - Besitz + Schreibrechte auf storage/ und bootstrap/cache/
#   - Symlink public/storage → storage/app/public (wie artisan storage:link)
#
# Aufruf aus dem Host oder im Projektroot:
#   sudo bash scripts/ensure-laravel-storage.sh [ABSOLUTER_PROJEKTROOT]
#
# Ohne root: nur mkdir (falls erlaubt) und Symlink-Versuch — chown entfällt.
#
# Hinweis: Fehlt vendor/ (noch kein composer install), wird nur der Symlink per ln gesetzt —
# die Laravel-App braucht vendor/ trotzdem für den Betrieb.
#
set -euo pipefail

die() { echo "Fehler: $*" >&2; exit 1; }
info() { echo "[ensure-laravel-storage] $*" >&2; }

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="${1:-$(cd "$SCRIPT_DIR/.." && pwd)}"
ROOT="${ROOT%/}"
cd "$ROOT" || die "Konnte nicht nach $ROOT wechseln."

[[ -f artisan ]] || die "Kein artisan unter $ROOT — bitte Laravel-Projektroot angeben."
[[ -d public ]] || die "Verzeichnis public/ fehlt unter $ROOT."

mkdir -p \
  "$ROOT/bootstrap/cache" \
  "$ROOT/storage/framework/cache/data" \
  "$ROOT/storage/framework/sessions" \
  "$ROOT/storage/framework/views" \
  "$ROOT/storage/logs" \
  "$ROOT/storage/app/public"

WEB_USER="www-data"
WEB_GROUP="www-data"
if ! id "$WEB_USER" &>/dev/null; then
  WEB_USER=""
fi

if [[ "${EUID:-0}" -eq 0 ]] && [[ -n "$WEB_USER" ]]; then
  chown -R "${WEB_USER}:${WEB_GROUP}" "$ROOT/storage" "$ROOT/bootstrap/cache"
  chmod -R ug+rwx "$ROOT/storage" "$ROOT/bootstrap/cache"
elif [[ "${EUID:-0}" -ne 0 ]]; then
  info "Nicht als root gestartet — chown/chmod für storage übersprungen (bei Bedarf: sudo $0 \"$ROOT\")."
fi

# Ohne vendor/ ist artisan nicht lauffähig — Symlink trotzdem per ln setzen (wie storage:link).
if [[ -f "$ROOT/vendor/autoload.php" ]]; then
  if php artisan storage:link --force 2>/dev/null; then
    :
  else
    info "php artisan storage:link fehlgeschlagen — Symlink per ln …"
  fi
else
  info "vendor/autoload.php fehlt — artisan wird übersprungen (zuerst: composer install). Symlink per ln …"
fi

if [[ ! -e "$ROOT/public/storage" ]]; then
  ln -sfn ../storage/app/public "$ROOT/public/storage" || die "Konnte Symlink $ROOT/public/storage nicht anlegen."
  info "Symlink angelegt: public/storage → ../storage/app/public"
fi

info "Fertig: $ROOT (storage + bootstrap/cache beschreibbar für www-data, public/storage gesetzt)."
