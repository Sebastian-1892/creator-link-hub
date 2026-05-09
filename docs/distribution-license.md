# Lizenzserver (`creatorlinkhub.eu`)

Die **öffentliche Lizenzprüfung** erfolgt über die API:

**`GET https://creatorlinkhub.eu/license/check?key=<64-stelliger-hex-key>`**

Antwort (Beispiele):

- `{"ok":true,"status":"trial","valid_until":"2026-06-02","package_url":"https://…/creator-link-hub.zip"}`
- `{"ok":false,"reason":"unknown|expired|blocked|unverified|rate_limited"}`

Der Kunden-Installer [`distribution/install.sh`](../distribution/install.sh) ruft diese URL auf (`CLH_LICENSE_CHECK_URL` überschreibbar).

## Lizenzkey-Algorithmus (Kunde + Verkäufer)

```
normalized = lower(trim(email)) + "|" + lower(trim(domain))
license_key = hex( SHA-256( normalized UTF-8 ) )
```

Test (E-Mail `seb.wulf@mailbox.org`, Domain `testing.sebastian-wulf.de`):

```bash
./distribution/license/generate-license-key.sh seb.wulf@mailbox.org testing.sebastian-wulf.de
# → 673d946e82120631fe8669e7efbfbb89d770722b934a8582c8251fdd3b8bafea
```

Registrierung und Trial-Verwaltung liegen auf **creatorlinkhub.eu** (MySQL). Die statische Datei `public/license/keys.json` in diesem Repo wird **nicht** mehr für produktive Lizenzen genutzt.

## Sicherheit

Die Check-URL ist öffentlich, aber **rate-limitiert**. Keys sind nicht mehr in einer öffentlichen JSON-Liste aller Kunden sichtbar.
