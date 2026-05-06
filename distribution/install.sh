#!/usr/bin/env bash
#
# Creator Link Hub — Kunden-Installer (Lizenz prüfen, ZIP laden, Server-Installation starten)
#
# Voraussetzung: root auf Debian/Ubuntu, Internet, curl, unzip, python3.
# Lizenz: GET https://creatorlinkhub.eu/license/check?key=… (JSON)
# Paket:  Feld „package_url“ in der JSON-Antwort oder Standard-URL unten.
#
# Ausführung:
#   curl -fsSL https://creatorlinkhub.eu/install.sh | sudo bash
#   # oder gespeicherte Datei:
#   sudo bash install.sh
#
set -euo pipefail

die() { echo "Fehler: $*" >&2; exit 1; }
info() { echo "[Info] $*"; }

LICENSE_CHECK_URL="${CLH_LICENSE_CHECK_URL:-https://creatorlinkhub.eu/license/check}"
DEFAULT_PACKAGE_URL="${CLH_PACKAGE_URL:-https://filedn.eu/lFa08iL0cJzHeyFFtNiVfqY/creator-link-hub/main/creator-link-hub.zip}"
DEFAULT_INSTALL_DIR="/var/www/creator-link-hub"

if [[ "${EUID:-0}" -ne 0 ]]; then
  die "Bitte als root ausführen: sudo bash $0"
fi

command -v curl >/dev/null || die "curl fehlt (apt install curl)."
command -v unzip >/dev/null || die "unzip fehlt (apt install unzip)."
command -v python3 >/dev/null || die "python3 fehlt (apt install python3)."

echo ""
echo "=========================================="
echo "  Creator Link Hub — Installation"
echo "=========================================="
echo ""

read -r -p "Lizenzkey (64 Zeichen Hex): " LICENSE_KEY
LICENSE_KEY="$(echo -n "$LICENSE_KEY" | tr -d '[:space:]' | tr '[:upper:]' '[:lower:]')"
[[ ${#LICENSE_KEY} -ge 32 ]] || die "Lizenzkey zu kurz oder leer."

info "Prüfe Lizenz am Server …"
set +e
PKG_LINE="$(python3 - "$LICENSE_CHECK_URL" "$LICENSE_KEY" <<'PY'
import json
import sys
import urllib.parse
import urllib.request

base = sys.argv[1].strip().split("?")[0].rstrip("/")
key = sys.argv[2].lower().strip()
qs = urllib.parse.urlencode({"key": key})
url = base + "?" + qs
try:
    raw = urllib.request.urlopen(url, timeout=60).read().decode("utf-8")
    j = json.loads(raw)
except Exception as e:
    print("Lizenzserver nicht erreichbar oder ungültige Antwort: " + str(e), file=sys.stderr)
    sys.exit(1)
if not j.get("ok"):
    r = str(j.get("reason", "unknown"))
    print(r, file=sys.stderr)
    if r in ("expired", "blocked"):
        sys.exit(2)
    if r == "unverified":
        sys.exit(3)
    if r == "rate_limited":
        sys.exit(4)
    sys.exit(1)
pu = j.get("package_url")
if isinstance(pu, str) and pu.strip():
    print(pu.strip())
else:
    print("DEFAULT")
sys.exit(0)
PY
)"
PY_EXIT=$?
set -e

if [[ "$PY_EXIT" -ne 0 ]]; then
  [[ "$PY_EXIT" -eq 2 ]] && die "Lizenz abgelaufen oder gesperrt."
  [[ "$PY_EXIT" -eq 3 ]] && die "E-Mail noch nicht bestätigt — bitte Postfach prüfen (creatorlinkhub.eu)."
  [[ "$PY_EXIT" -eq 4 ]] && die "Zu viele Anfragen — bitte später erneut."
  die "Lizenz unbekannt oder Serverfehler ($LICENSE_CHECK_URL)."
fi

PACKAGE_URL="$DEFAULT_PACKAGE_URL"
if [[ -n "$PKG_LINE" && "$PKG_LINE" != "DEFAULT" ]]; then
  PACKAGE_URL="$PKG_LINE"
fi

info "Lizenz akzeptiert."
info "Paket-URL: $PACKAGE_URL"

read -r -p "Installationsverzeichnis [$DEFAULT_INSTALL_DIR]: " IN_DIR
INSTALL_DIR="${IN_DIR:-$DEFAULT_INSTALL_DIR}"
INSTALL_DIR="${INSTALL_DIR%/}"

if [[ -e "$INSTALL_DIR" ]] && [[ -n "$(ls -A "$INSTALL_DIR" 2>/dev/null || true)" ]]; then
  die "Zielverzeichnis ist nicht leer: $INSTALL_DIR — bitte leeren oder anderen Pfad wählen."
fi

mkdir -p "$(dirname "$INSTALL_DIR")"
mkdir -p "$INSTALL_DIR"

ZIP_TMP="$(mktemp)"
STAGE=""
cleanup() {
  rm -rf "${STAGE:-}" "${ZIP_TMP:-}"
}
trap cleanup EXIT

info "Lade Software-Paket …"
if ! curl -fSL "$PACKAGE_URL" -o "$ZIP_TMP"; then
  die "Download fehlgeschlagen. Prüfe package_url und Dateiname (z. B. creator-link-hub.zip unter …/main/)."
fi

info "Entpacke nach $INSTALL_DIR …"
STAGE="$(mktemp -d)"
unzip -q "$ZIP_TMP" -d "$STAGE"

shopt -s dotglob nullglob
ENTRIES=("$STAGE"/*)
if [[ ${#ENTRIES[@]} -eq 1 && -d "${ENTRIES[0]}" ]]; then
  mv "${ENTRIES[0]}"/* "$INSTALL_DIR/" || die "ZIP-Struktur: ein Unterordner, Verschieben fehlgeschlagen."
else
  mv "$STAGE"/* "$INSTALL_DIR/" || die "ZIP-Struktur unerwartet."
fi
shopt -u dotglob nullglob

[[ -f "$INSTALL_DIR/composer.json" && -f "$INSTALL_DIR/artisan" ]] || die "Nach dem Entpacken fehlt composer.json oder artisan — ZIP-Inhalt prüfen."

[[ -f "$INSTALL_DIR/scripts/install-server.sh" ]] || die "scripts/install-server.sh fehlt im Archiv."

chmod +x "$INSTALL_DIR/scripts/install-server.sh" || true

trap - EXIT
rm -rf "$STAGE" "$ZIP_TMP"

info "Starte interaktive Server-Installation …"
export CLH_INSTALL_TARGET="$INSTALL_DIR"
exec bash "$INSTALL_DIR/scripts/install-server.sh"
