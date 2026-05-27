#!/usr/bin/env bash
#
# Bestehende Tenant-Sites: Nginx client_max_body_size + PHP upload/post-Limits für Profilbilder (bis 8 MB).
# Neu-Provisionierung setzt das bereits in clh-provision-tenant.sh; dieses Skript patcht alte Tenants.
#
#   sudo bash scripts/clh-tenant-patch-upload-limits.sh <tenant-slug>
# Beispiel:
#   sudo bash /var/www/clh-tenants/seb/scripts/clh-tenant-patch-upload-limits.sh seb
#
set -euo pipefail

die() { echo "Fehler: $*" >&2; exit 1; }
info() { echo "[clh-upload-limits] $*" >&2; }

SLUG="${1:-}"
[[ -n "$SLUG" ]] || die "Usage: sudo bash $0 <tenant-slug>"

CONF="/etc/nginx/sites-available/clh-${SLUG}.conf"
[[ -f "$CONF" ]] || die "Nginx-Site nicht gefunden: $CONF (Slug korrekt?)"

if grep -q 'client_max_body_size' "$CONF"; then
  info "Nginx: client_max_body_size bereits gesetzt in $CONF"
else
  if grep -q 'index index.php;' "$CONF"; then
    sed -i '/index index\.php;/a\    client_max_body_size 8m;' "$CONF"
  else
    die "Konnte keine Einfügestelle in $CONF finden — bitte manuell client_max_body_size 8m; im HTTPS-server-Block setzen."
  fi
  nginx -t
  systemctl reload nginx
  info "Nginx neu geladen (client_max_body_size 8m)."
fi

PHP_VER="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)"
[[ -n "$PHP_VER" ]] || die "php CLI nicht gefunden"

FPM_INI_DIR="/etc/php/${PHP_VER}/fpm/conf.d"
[[ -d "$FPM_INI_DIR" ]] || die "PHP-FPM conf.d fehlt: $FPM_INI_DIR"

DROP_IN="${FPM_INI_DIR}/99-clh-uploads.ini"
cat >"$DROP_IN" <<'INI'
; Creator Link Hub — Profilbild-/Datei-Uploads (Nginx client_max_body_size 8m)
upload_max_filesize = 8M
post_max_size = 9M
INI
info "PHP-FPM: $DROP_IN geschrieben."

if systemctl list-units --type=service --all 2>/dev/null | grep -q "php${PHP_VER}-fpm"; then
  systemctl reload "php${PHP_VER}-fpm"
  info "php${PHP_VER}-fpm neu geladen."
else
  info "php${PHP_VER}-fpm nicht per systemctl gefunden — ggf. manuell reloaden."
fi

info "Fertig für Tenant „${SLUG}“. Test: Profilbild unter /hub/bio hochladen."
