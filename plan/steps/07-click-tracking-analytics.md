# Step 07 – Click-Tracking & Analytics

## Ziel
`/go/{link}` Redirect, Bot-Filter, Queue-Job → `click_events`, Dashboard mit Charts.

## Checkliste
- [ ] Controller: 302 zur Ziel-URL, nur wenn Link aktiv und Profil published
- [ ] Job: IP hashen (Salt aus env), Session-ID Cookie
- [ ] Dashboard: Zeitraum-Filter, Top-Links, Klicks pro Tag

## Abnahme
- Klick erzeugt Event; Bots werden verworfen oder markiert
