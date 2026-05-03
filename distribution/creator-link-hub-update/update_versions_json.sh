#!/usr/bin/env sh
# Neue Version zur versions.json hinzufügen (Creator Link Hub, pCloud Public URL).
#
# Verwendung:
#   ./update_versions_json.sh <version> <sha256-hex>
#
# Beispiel:
#   ./update_versions_json.sh 0.1.1 abc123def456...
#
set -eu

if [ "${1:-}" = "" ] || [ "${2:-}" = "" ]; then
  echo "Verwendung: $0 <version> <sha256>"
  exit 1
fi

VERSION="$1"
SIGNATURE="$2"

BASE_URL="https://filedn.eu/lFa08iL0cJzHeyFFtNiVfqY/creator-link-hub/update"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
VERSIONS_FILE="${SCRIPT_DIR}/versions.json"

if ! command -v python3 >/dev/null 2>&1; then
  echo "Fehler: python3 wird benötigt."
  exit 1
fi

python3 - "$VERSION" "$SIGNATURE" "$BASE_URL" "$VERSIONS_FILE" <<'PYEOF'
import sys, json

version = sys.argv[1]
signature = sys.argv[2]
base_url = sys.argv[3].rstrip('/')
versions_file = sys.argv[4]

try:
    with open(versions_file, 'r', encoding='utf-8') as f:
        data = json.load(f)
except (FileNotFoundError, json.JSONDecodeError):
    data = {}

if 'versions' not in data or not isinstance(data['versions'], list):
    data['versions'] = []

new_entry = {
    "version": version,
    "download_url": f"{base_url}/downloads/creator-link-hub-{version}.zip",
    "changelog_url": f"{base_url}/changelogs/{version}.md",
    "sql_updates": [],
    "signature": signature,
}

found = False
for i, v in enumerate(data['versions']):
    if isinstance(v, dict) and v.get('version') == version:
        data['versions'][i] = new_entry
        found = True
        break
if not found:
    data['versions'].append(new_entry)

try:
    from functools import cmp_to_key
    import re

    def ver_key(entry):
        parts = re.split(r'[.\-]', str(entry.get('version', '')))
        out = []
        for p in parts:
            if p.isdigit():
                out.append(int(p))
            else:
                out.append(p)
        return out

    data['versions'].sort(key=ver_key)
except Exception:
    pass

data['latest_version'] = data['versions'][-1]['version'] if data['versions'] else version
if 'min_php_version' not in data:
    data['min_php_version'] = '8.2'

with open(versions_file, 'w', encoding='utf-8') as f:
    json.dump(data, f, ensure_ascii=False, indent=2)
    f.write('\n')

print(f"versions.json aktualisiert: {version} (latest: {data['latest_version']})")
PYEOF
