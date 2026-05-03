# Go-Live Checkliste

- [ ] Rechtstexte (Impressum, AGB, Datenschutz) mit echten Firmendaten
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` korrekt
- [ ] PostgreSQL + Redis produktiv, `QUEUE_CONNECTION=redis`
- [ ] `npm ci && npm run build` auf dem Server / in CI-Artefakt
- [ ] Stripe Live-Keys, Webhooks, Price-IDs, Steuern (Stripe Tax) geprüft
- [ ] Mail-Versand (Postmark/Resend) getestet
- [ ] SSL (Let’s Encrypt), HSTS optional
- [ ] Admin-Zugang (`is_admin`) nur für vertrauenswürdige Accounts
- [ ] Smoke: Registrierung → Onboarding → Link → Publish → `/p/{slug}` → Klick zählt
- [ ] Support-Mailbox & Help-Center-Link (`/help`)
