#!/usr/bin/env bash
#
# Postfix als SMTP-Client mit Authentifizierung zum Relay-Anbieter (Debian/Ubuntu).
# Nur als root. Zugangsdaten landen in /etc/postfix/sasl_passwd (0600).
#
# Nicht-interaktiv per Umgebung:
#   CLH_SMTP_RELAY_HOST  — z. B. smtp.mailprovider.example
#   CLH_SMTP_RELAY_PORT  — Standard 587 (STARTTLS), 465 für SMTPS
#   CLH_SMTP_RELAY_USER
#   CLH_SMTP_RELAY_PASSWORD  — kein einfaches Hochkomma im Passwort verwenden (postmap/shell)
#
set -euo pipefail

die() { echo "Fehler: $*" >&2; exit 1; }
info() { echo "[configure-postfix-smtp-relay] $*" >&2; }

[[ "${EUID:-0}" -eq 0 ]] || die "Bitte als root ausführen: sudo bash $0"

command -v postconf >/dev/null 2>&1 || die "postconf nicht gefunden — Postfix installieren."

RELAY_HOST="${CLH_SMTP_RELAY_HOST:-}"
RELAY_PORT="${CLH_SMTP_RELAY_PORT:-587}"
RELAY_USER="${CLH_SMTP_RELAY_USER:-}"
RELAY_PASS="${CLH_SMTP_RELAY_PASSWORD:-}"

[[ -n "$RELAY_HOST" ]] || die "CLH_SMTP_RELAY_HOST ist leer."
[[ -n "$RELAY_USER" ]] || die "CLH_SMTP_RELAY_USER ist leer."
[[ -n "$RELAY_PASS" ]] || die "CLH_SMTP_RELAY_PASSWORD ist leer."
[[ "$RELAY_PASS" != *"'"* ]] || die "Passwort darf kein einfaches Hochkomma (') enthalten."

[[ "$RELAY_PORT" =~ ^[0-9]+$ ]] || die "CLH_SMTP_RELAY_PORT muss eine Zahl sein: $RELAY_PORT"

export DEBIAN_FRONTEND=noninteractive
apt-get install -y -qq libsasl2-modules ca-certificates >/dev/null

RELAY_BRACKET="[${RELAY_HOST}]:${RELAY_PORT}"

postconf -e "relayhost = ${RELAY_BRACKET}"
postconf -e "smtp_sasl_auth_enable = yes"
postconf -e "smtp_sasl_security_options = noanonymous"
postconf -e "smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd"
postconf -e "smtp_tls_CAfile = /etc/ssl/certs/ca-certificates.crt"
postconf -e "smtp_tls_security_level = encrypt"

if [[ "$RELAY_PORT" == "465" ]]; then
  postconf -e "smtp_tls_wrappermode = yes"
else
  postconf -e "smtp_tls_wrappermode = no"
fi

umask 077
printf '%s\t%s\n' "$RELAY_BRACKET" "${RELAY_USER}:${RELAY_PASS}" >/etc/postfix/sasl_passwd
chmod 600 /etc/postfix/sasl_passwd
postmap /etc/postfix/sasl_passwd
chmod 600 /etc/postfix/sasl_passwd.db 2>/dev/null || true

if postfix check 2>/dev/null; then
  systemctl reload postfix || systemctl restart postfix
  info "Postfix neu geladen — Relay ${RELAY_BRACKET} (Benutzer: ${RELAY_USER})."
else
  die "postfix check fehlgeschlagen — main.cf prüfen."
fi
