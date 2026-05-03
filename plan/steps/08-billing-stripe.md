# Step 08 – Billing (Stripe)

## Ziel
Cashier, Produkte/Preise in Stripe, Checkout, Customer Portal, Webhooks, Stripe Tax optional.

## Checkliste
- [ ] `composer require laravel/cashier`
- [ ] `Subscription` an Workspace koppeln (Billable auf User oder Workspace – hier: **Workspace** via eigenes Modell oder User billable + workspace_id in metadata)
- [ ] Empfehlung MVP: **User** billable, `workspace.plan` aus Subscription spiegeln
- [ ] Webhook `cashier`, Preise `STARTER`, `PRO` in config
- [ ] Billing-Seite: Upgrade, Portal-Link

## Abnahme
- Testmodus: erfolgreicher Checkout, Plan in DB sichtbar
