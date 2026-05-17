#!/usr/bin/env bash
#
# Nur „Subdomain frei“-Landingpage aktivieren (kein erneutes Löschen von DB/Dateien).
# Beispiel: sudo clh-tenant-available-landing.sh --slug test --domain test.app.creatorlinkhub.eu
#
set -euo pipefail
exec /usr/local/bin/clh-delete-tenant.sh "$@" --landing-only
