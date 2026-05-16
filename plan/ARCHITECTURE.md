### 🧩 Technische Architektur (High-Level)

Frontend
- Web-App mit getrenntem Marketing- und Produktbereich
- Dashboard in rollenbasierten Views
- Public Profile Pages serverseitig gerendert für schnelle Ladezeiten und SEO
- Drag-and-drop Editor für Layouts
- Responsive First, Mobile Preview Pflicht

Backend
Module:
- Auth & Identity
- Profile Management
- Link Management
- Page Rendering
- Analytics & Event Tracking
- Referral Engine
- Rewards & Wallet
- Billing & Subscriptions
- Integrations
- Notifications
- Admin & Moderation
- Fraud Detection

Datenbank
- PostgreSQL als primäre relationale Datenbank
- Begründung:
  - starke Beziehungen zwischen User, Profile, Links, Referrals, Rewards
  - saubere Transaktionslogik für Wallet/Payouts
  - Reporting und Konsistenz wichtiger als reine NoSQL-Geschwindigkeit
- Ergänzend:
  - Redis für Cache, Sessions, Rate Limits, Queue States
  - Objektstorage für Medienassets

APIs
- Interne APIs für Dashboard, Analytics, Reward-Regeln, Billing
- Externe APIs:
  - Stripe für Payments
  - Mailanbieter für Email
  - Shopify/Calendly/YouTube/TikTok/Instagram nach Verfügbarkeit
- Webhooks für Ereignisse:
  - click.created
  - conversion.created
  - referral.credited
  - reward.paid
  - subscription.changed

Hosting
- SaaS: Cloud-Hosting mit CDN, Object Storage, Managed DB
- Public pages über CDN ausliefern
- Analytics ingestion über separate Event-Pipeline

---

### 🗂️ Datenstruktur

User:
- id
- email
- passwordHash
- name
- role
- status
- createdAt
- lastLoginAt

Workspace:
- id
- ownerUserId
- name
- plan
- createdAt

Profile:
- id
- workspaceId
- slug
- displayName
- bio
- avatarUrl
- themeId
- customDomainId
- publishedAt
- isPublished

Link:
- id
- profileId
- title
- url
- type
- position
- isActive
- opensInNewTab
- trackingEnabled
- createdAt

Widget:
- id
- profileId
- type
- configJson
- position
- isActive

Theme:
- id
- name
- variablesJson
- previewImageUrl

Referral:
- id
- referrerUserId
- profileId
- campaignId
- referralCode
- status
- createdAt

ReferralClick:
- id
- referralId
- sessionId
- ipHash
- userAgent
- country
- createdAt

ConversionEvent:
- id
- profileId
- linkId
- referralId
- eventType
- value
- currency
- metadataJson
- createdAt

RewardLedgerEntry:
- id
- userId
- referralId
- amount
- points
- type
- status
- createdAt

Payout:
- id
- userId
- provider
- amount
- status
- requestedAt
- paidAt

Subscription:
- id
- workspaceId
- provider
- plan
- status
- currentPeriodEnd
- createdAt

Integration:
- id
- workspaceId
- provider
- accessTokenEncrypted
- configJson
- status

EventLog:
- id
- workspaceId
- eventType
- payloadJson
- createdAt

Relationships:
- User 1:n Workspace
- Workspace 1:1 oder 1:n Profile
- Profile 1:n Link
- Profile 1:n Widget
- User 1:n Referral
- Referral 1:n ReferralClick
- Referral 1:n RewardLedgerEntry
- Workspace 1:n Integration
- Workspace 1:n Subscription

---

### 🧱 Module / Komponenten

1. Auth
- Login, Signup, Passwort-Reset, SSO
- Rollen- und Session-Verwaltung

2. Profile Builder
- Erstellung und Bearbeitung der Bio-Seite
- Theme- und Layout-Konfiguration
- Veröffentlichungsworkflow

3. Link Manager
- Links anlegen, sortieren, deaktivieren
- Ziel-URLs validieren
- Tracking-Flags setzen

4. Analytics Engine
- Klick-Events sammeln
- Metriken aggregieren
- Funnels und Referrer-Auswertung

5. Referral Engine
- Referral-Codes generieren
- Attribution verwalten
- Belohnungsregeln anwenden

6. Wallet / Rewards
- Punkte- und Geldguthaben
- Ledger-basierte Buchhaltung
- Auszahlungsfreigaben

7. Billing
- Abo-Verwaltung
- Planlimits
- Rechnungen
- Stripe-Events verarbeiten

8. Integrations
- Drittanbieter verbinden
- Token speichern
- Daten synchronisieren

9. Notifications
- E-Mail
- In-App
- Event-basierte Trigger

10. Admin
- Nutzerverwaltung
- Missbrauchsprüfung
- Support-Tools
- Systemkonfiguration

---

### 🔐 Sicherheit & Rechte
Rollenmodell:
- Visitor: öffentlicher Besucher, nur lesen
- User: eigene Profile verwalten
- Team Member: zugewiesene Workspaces bearbeiten
- Admin: systemweit administrieren
- Super Admin: technische Gesamtverantwortung

Zugriffskontrolle:
- Workspace-Isolation strikt
- Jeder Schreibzugriff nur mit Ownership-/Rollenprüfung
- Referral- und Reward-Daten nur für berechtigte User sichtbar
- Administrative Tools nur serverseitig zugänglich
- Rate Limits auf Click- und Referral-Endpunkte

Typische Risiken:
- Referral-Fraud durch Self-Referrals
- Click-Spam und Bot-Traffic
- Token-Leaks bei Integrationen
- Offene Redirects / Phishing
- Manipulation von Reward-Events
- Mehrfachzählung von Conversions

Gegenmaßnahmen:
- IP-/Device-Fingerprinting mit Datenschutzbeachtung
- Session-basierte Deduplication
- Event-Signaturen
- Moderations-Queue
- Audit-Logs
- Payout-Freigabe erst nach Fraud-Check

---

### ⚡ Performance & Skalierung
Engpässe:
- Hohe Klickzahlen auf Public Profiles
- Event-Ingestion bei Kampagnen
- Analytics-Aggregation
- Bild-/Asset-Auslieferung
- Referral-Attribution in Echtzeit

Skalierungsstrategie:
- CDN für öffentliche Seiten und Assets
- Event-Queue für Tracking- und Reward-Events
- Asynchrone Aggregation für Analytics
- Read-Replicas für Reporting
- Caching für Profile, Themes, Pricing, public data
- Separate Services für Ingestion und Dashboard-Queries

Caching / Queueing:
- Redis für Short-Lived Cache und Rate Limits
- Message Queue für click.created und conversion.created
- Batch-Jobs für Nachtaggregation
- Precomputed Leaderboards für Rewards