---
name: frontend-design
description: Entwickelt hochwertige, produktionsreife Frontend-Interfaces mit klarer visueller Handschrift. Verwenden bei Anfragen zu UI-Design, Komponenten, Seiten, Landingpages, Dashboards, React/HTML/CSS-Styling oder visueller Überarbeitung bestehender Oberflächen. Liefert kreative, konsistente Umsetzung mit Fokus auf Nutzbarkeit, Accessibility und Performance.
license: Complete terms in LICENSE.txt
---

# Frontend Design (Production)

Dieser Skill erstellt markante, produktionsfähige Frontend-Umsetzungen, die nicht generisch wirken und gleichzeitig robust im Betrieb sind.

## Wann dieser Skill greift

- Nutzer wünscht neues UI, Redesign oder visuelles Feintuning
- Aufgabe betrifft Web-Komponenten, Seiten, Layouts, Dashboards oder App-Oberflächen
- Es soll nicht nur „funktionieren“, sondern auch gestalterisch überzeugen

## Prioritäten (in dieser Reihenfolge)

1. **Funktionalität & Korrektheit**
2. **Accessibility** (Semantik, Tastaturbedienung, Kontrast, Fokuszustände)
3. **Performance** (vernünftige Effekte, keine unnötig teuren Renderpfade)
4. **Visuelle Qualität & Differenzierung**
5. **Wartbarkeit** (klare Struktur, konsistente Tokens/Variablen)

## Arbeitsweise

### 1) Kontext verstehen
- Zweck der Oberfläche, Zielgruppe, Content-Typ und Nutzungsszenario erfassen
- Technische Constraints beachten (Framework, bestehendes Designsystem, Browser-/Device-Ziel)
- Wenn unklar: gezielt 1-3 Rückfragen stellen; sonst direkt umsetzen

### 2) Gestaltungsrichtung festlegen
- Eine klare Richtung wählen (z. B. minimal, editorial, verspielt, brutalistisch, premium)
- Die Richtung begründen: warum passt sie zur Aufgabe?
- Ein prägnantes Differenzierungsmerkmal definieren („was bleibt im Kopf?“)

### 3) Produktionsreif implementieren
- Reales, lauffähiges UI bauen (kein Pseudocode)
- Konsistente Struktur und Benennung
- Design-Tokens/Variablen verwenden (Farben, Abstände, Typografie, Radius, Schatten)
- Mobile-first und responsive Breakpoints sauber berücksichtigen

## Gestaltungsrichtlinien

### Typografie
- Schriftwahl passend zur Markenwirkung und Lesbarkeit
- Keine dogmatischen Verbote: Systemfonts sind erlaubt, wenn sie zum Kontext passen
- Klare Hierarchie (Heading, Subheading, Body, Meta)

### Farbe & Theme
- Kohärente Palette mit klaren Kontrasten
- Primär-/Sekundär-/Akzentrollen klar trennen
- Zustände sichtbar gestalten (hover, active, disabled, focus, error, success)

### Layout & Komposition
- Rhythmus über konsistente Spacing-Skalen
- Visuelle Führung über Größe, Kontrast, Position
- Asymmetrie/Overlaps nur gezielt und nachvollziehbar einsetzen

### Motion
- Animation nur mit Zweck (Feedback, Orientierung, Fokus)
- Respektiere `prefers-reduced-motion`
- Subtile, performante Transitions bevorzugen; komplexe Effekte sparsam einsetzen

### Details
- Leere Zustände, Fehlermeldungen und Ladezustände visuell mitdenken
- Interaktive Elemente mit klaren Hit-Areas und Fokusindikatoren
- Keine austauschbaren Standardmuster ohne Kontextbezug

## Qualitäts-Checklist vor Abgabe

- UI ist funktional und ohne offensichtliche Fehler
- Accessibility-Grundlagen sind erfüllt
- Responsives Verhalten ist plausibel
- Visuelle Richtung ist konsistent und erkennbar
- Code bleibt wartbar und projektkonform

## Anti-Patterns

- Styling ohne Konzept („random hübsch machen“)
- Effektlastiges UI zulasten von Lesbarkeit/Performance
- Unklare Interaktionszustände (kein Fokus, kein Hover, kein Disabled-Feedback)
- Harte Design-Entscheidungen ohne Bezug zu Produktziel oder Nutzerkontext
