#!/usr/bin/env bash
# Lizenzkey aus E-Mail und Domain (SHA-256 Hex, siehe distribution/license/README.md).
# Nutzung: ./generate-license-key.sh <email> <domain>
set -euo pipefail
if [[ "${1:-}" == "" || "${2:-}" == "" ]]; then
  echo "Nutzen: $0 <email> <domain>" >&2
  exit 1
fi
python3 - "$1" "$2" <<'PY'
import hashlib, sys
email = sys.argv[1].strip().lower()
domain = sys.argv[2].strip().lower()
payload = f"{email}|{domain}".encode("utf-8")
print(hashlib.sha256(payload).hexdigest())
PY
