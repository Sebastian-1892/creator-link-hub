---
name: php-developer
description: Entwickelt und verbessert professionelle PHP-Anwendungen mit Fokus auf saubere Architektur, Wartbarkeit, Sicherheit und Tests. Verwenden bei Feature-Implementierungen, Refactorings, Bugfixes, API-Entwicklung, Datenbankzugriff, Performance-Optimierung und Code-Reviews in PHP-Projekten.
---

# PHP Developer

## Ziel

Dieser Skill liefert produktionsreifen PHP-Code mit klarer Struktur, stabiler Fehlerbehandlung und hoher Wartbarkeit.

## Wann dieser Skill verwendet wird

- Bei Aufgaben in PHP-Codebasen (Feature, Fix, Refactoring)
- Bei Arbeiten an Controllern, Services, Repositories, Models oder Middleware
- Bei API-Design, Validierung, Authentifizierung/Autorisierung und Datenbanklogik
- Bei Anforderungen zu Testbarkeit, Sicherheit oder Performance

## Technische Leitplanken

1. **Korrektheit zuerst:** Funktionale Anforderungen müssen zuverlässig erfüllt sein.
2. **Lesbarkeit vor Cleverness:** Klarer, nachvollziehbarer Code statt unnötig komplexer Patterns.
3. **Defensive Programmierung:** Eingaben validieren, Fehlerfälle sauber behandeln.
4. **Wartbarkeit:** Kleine, fokussierte Methoden; klare Verantwortlichkeiten; wenig Seiteneffekte.
5. **Sicherheit:** SQL-Injection, XSS, CSRF, Auth-/Permission-Checks aktiv berücksichtigen.
6. **Testbarkeit:** Änderungen so bauen, dass Unit-/Integrationstests möglich und sinnvoll sind.

## Arbeitsweise

### 1) Kontext erfassen
- Betroffene Flows, Dateien und Abhängigkeiten identifizieren
- Vorhandene Architektur und Namenskonventionen übernehmen
- Bestehendes Verhalten nicht unbeabsichtigt ändern

### 2) Implementieren
- Strict Types beibehalten (`declare(strict_types=1);`)
- Aussagekräftige Typen, Rückgabewerte und klare Schnittstellen verwenden
- Datenzugriff kapseln (Repository/Service statt SQL in Views/Controller)
- Duplizierte Logik reduzieren, aber keine überzogene Abstraktion einführen

### 3) Absichern
- Eingaben prüfen und normalisieren
- Berechtigungen/Middleware an den richtigen Stellen durchsetzen
- Fehler mit verständlichen Messages behandeln (ohne interne Details zu leaken)

### 4) Verifizieren
- Syntaxcheck und relevante Tests ausführen
- Geänderte Pfade auf Seiteneffekte prüfen
- Dokumentation bei Verhaltensänderungen aktualisieren

## PHP-Codequalität

- PSR-konformer Stil und konsistente Projektkonventionen
- Methoden kurz und fokussiert halten
- Keine toten Pfade, keine ungenutzten Variablen
- Magische Werte vermeiden; Konstanten/Config nutzen
- Öffentliche APIs stabil und klar dokumentieren

## Datenbank & Persistenz

- Parametrisierte Queries / sichere Query-Builder verwenden
- Transaktionen einsetzen, wenn mehrere Writes konsistent bleiben müssen
- Migrationen idempotent und nachvollziehbar halten
- Indizes und Query-Kosten bei Listen-/Filter-Endpunkten beachten

## Performance-Grundsätze

- N+1-Abfragen vermeiden
- Teure Operationen cachen, wenn sinnvoll
- Nur benötigte Felder laden
- I/O reduzieren und Hot Paths gezielt optimieren

## Sicherheits-Checklist

- SQL-Injection-Schutz vorhanden
- XSS-sichere Ausgabe im Template/Response-Kontext
- CSRF-Schutz auf zustandsändernden Requests
- Authentifizierung + Autorisierung korrekt geprüft
- Keine Secrets in Logs/Responses

## Tests & Qualitätssicherung

- Für kritische Logik Unit-Tests ergänzen
- Für Integrationspfade realistische Integrationstests ergänzen
- Bugfixes mit reproduzierbarem Test absichern
- Bei vorhandener CI: lokal möglichst nahe an CI verifizieren

## Anti-Patterns

- Business-Logik in Templates oder Routing-Dateien
- God-Classes mit zu vielen Verantwortlichkeiten
- Stille Fehlerbehandlung ohne Logging/Signal
- Harte Kopplung, die Tests erschwert
- „Quick Fixes“, die technische Schulden stark erhöhen
