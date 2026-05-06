#!/usr/bin/env bash
#
# Release-ZIP für Cloud-Multi-Tenant (Provisioner: clh-provision-tenant.sh).
# Voraussetzung: Node/npm für Vite (public/build ist in .gitignore, muss gebaut werden).
# Zusätzlich: CLI „zip“ (Debian-Paket zip) — beim Cloud-Bootstrap mitinstalliert.
#
# Siehe docs/cloud-hosting-installation/README.md — ZIP nach /opt/clh-releases/current.zip auf dem VPS.
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

log() { echo "[build-cloud-release-zip]" "$@"; }

command -v npm >/dev/null 2>&1 || {
  log "npm nicht im PATH. Node.js installieren (VPS: docs/cloud-hosting-installation/README.md → Schritt 3 „Node.js auf dem VPS“), dann erneut ausführen."
  exit 1
}

command -v zip >/dev/null 2>&1 || {
  log "zip nicht im PATH. Debian/Ubuntu: sudo apt-get install -y zip — danach erneut ausführen."
  exit 1
}

log "npm ci"
npm ci
log "npm run build (Vite → public/build)"
npm run build

OUT_DIR="$ROOT/distribution/releases"
mkdir -p "$OUT_DIR"
STAMP="$(date +%Y%m%d-%H%M)"
OUT="$OUT_DIR/creator-link-hub-cloud-${STAMP}.zip"
rm -f "$OUT"

log "zip → $OUT"
zip -r "$OUT" . \
  -x '.git/*' \
  -x 'vendor/*' \
  -x 'vendor/*/*' \
  -x 'vendor/*/*/*' \
  -x 'node_modules/*' \
  -x 'node_modules/*/*' \
  -x 'node_modules/*/*/*' \
  -x '.env' \
  -x '.phpunit.cache/*' \
  -x 'distribution/releases/*.zip' \
  -x 'storage/logs/*' \
  -x '*.log' \
  -x '.cursor/*' \
  -x 'plan/*' \
  -x 'plan/*/*' \
  -x 'tests/*' \
  -x 'tests/*/*' \
  -x '.github/*' \
  -x 'storage/framework/cache/*' \
  -x 'storage/framework/sessions/*' \
  -x 'storage/framework/views/*' \
  -x 'bootstrap/cache/*.php' \
  -x 'database/database.sqlite'

ln -sf "$(basename "$OUT")" "$OUT_DIR/current-cloud.zip"

unzip -t -q "$OUT"
log "OK: $OUT"
log "Symlink: $OUT_DIR/current-cloud.zip → $(basename "$OUT")"
log "SHA256: $(sha256sum "$OUT" | awk '{print $1}')"

echo ""
echo "Auf dem VPS:"
echo "  sudo cp $(basename "$OUT") /opt/clh-releases/current.zip && sudo chmod 644 /opt/clh-releases/current.zip"
echo "  sudo unzip -t /opt/clh-releases/current.zip"
