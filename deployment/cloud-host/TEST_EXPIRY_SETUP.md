# Cloud-Tenant-Sperre bei abgelaufener Lizenz

## Übersicht

Wenn eine Lizenz gesperrt oder gekündigt ist (`status = blocked` oder `cancelled`), muss der zugehörige Cloud-Tenant auf dem VPS nicht mehr erreichbar sein. Das Marketing stellt das **statusgetrieben** über den Cron sicher — unabhängig von der Spalte `test_expiry_date`.

## Ablauf (Marketing-Cron)

1. `blockExpiredTrials` / `blockExpiredGrace` / `blockExpiredPaid` setzen `licenses.status = blocked`.
2. **`LicenseService::suspendBlockedCloudInstances()`** findet Cloud-Lizenzen mit:
   - `status IN ('blocked', 'cancelled')`
   - `cloud_slug` gesetzt
   - `cloud_status IN ('ready', 'provisioning', 'failed')`
3. Pro Treffer: `cloud_status = suspended`, `provision_jobs.action = suspend`, danach `Provisioning::runPending` → HMAC-POST an VPS-Provisioner.

`test_expiry_date` wird bei Registrierung weiter gesetzt (Reporting/UI), löst aber **keinen** separaten Suspend mehr aus.

## VPS (Suspend + Maintenance-Seite)

Nach `git pull` auf dem App-VPS:

```bash
sudo /usr/local/bin/clh-cloud-host-update.sh
```

Das deployt u. a. `/var/www/clh-suspended/index.html` und aktualisiert [`vps/scripts/clh-suspend-tenant.sh`](../../../vps/scripts/clh-suspend-tenant.sh).

**Suspend-Verhalten:**

1. Symlink `sites-enabled/clh-{slug}.conf` entfernen (Original in `sites-available` bleibt für Resume).
2. `sites-enabled/clh-{slug}-suspended.conf` aktivieren — **HTTPS mit denselben Let's-Encrypt-Dateien** wie zuvor (`/etc/letsencrypt/live/…` wird **nicht** gelöscht oder widerrufen).
3. Statische Seite aus `/var/www/clh-suspended/` (HTTP 503).
4. `/.well-known/acme-challenge/` bleibt für `certbot renew` erreichbar.
5. Supervisor-Worker stoppen (falls vorhanden).

Optional: `CLH_SUSPENDED_ROOT` für abweichendes Verzeichnis der Maintenance-HTML.

## Manueller Test

1. Cloud-Trial-Lizenz mit `cloud_status = ready` und gesetztem `cloud_slug` wählen.
2. Trial ablaufen lassen:
   ```sql
   UPDATE licenses
   SET trial_ends_at = UTC_TIMESTAMP() - INTERVAL 1 HOUR
   WHERE id = ?;
   ```
3. Cron ausführen:
   ```bash
   curl -s "https://creatorlinkhub.eu/cron/run.php?token=IHR_CRON_TOKEN"
   ```
4. Erwartung:
   ```sql
   SELECT status, cloud_status FROM licenses WHERE id = ?;
   -- status = blocked, cloud_status = suspended

   SELECT action, status FROM provision_jobs
   WHERE license_id = ? ORDER BY id DESC LIMIT 1;
   -- action = suspend, status = done (nach erfolgreichem runPending)
   ```
5. Auf dem VPS:
   - `ls /etc/nginx/sites-enabled/clh-{slug}*` → nur `clh-{slug}-suspended.conf`
   - Browser: `https://{slug}.app.creatorlinkhub.eu/` → Maintenance-Seite (DE/EN), **kein** SSL-Fehler

## Rollback (ein Tenant)

```sql
UPDATE licenses SET cloud_status = 'ready', status = 'trial' WHERE id = ?;
```

Admin: Tenant wieder freischalten → `enqueueResume` (siehe Admin-Panel) oder manuell `clh-resume-tenant.sh` auf dem VPS.
