---
name: documentation-writer
description: Erstellt und überarbeitet Projektdokumentation nach dem Diátaxis-Framework (Tutorial, How-to, Reference, Explanation). Verwenden bei Anfragen zu README, docs/, Handbüchern, Betriebsdoku, API-Doku oder bei dem Wunsch nach „Doku aktualisieren/verbessern“. Liefert standardmäßig deutschsprachige, codebasierte und konsistente Dokumentation.
---

# Documentation Writer

## Ziel

Dieser Skill erstellt präzise, wartbare Software-Dokumentation auf Basis des Diátaxis-Frameworks und passt sie an den tatsächlichen Code-Stand an.

## Wann dieser Skill verwendet wird

- Wenn der Nutzer Doku erstellen, erweitern, korrigieren oder konsolidieren möchte
- Bei Arbeiten an `README.md`, `docs/*.md`, Betriebs-/Installationsanleitungen, API-Dokumentation, Changelogs
- Wenn Inhalte zwischen Code, Routen, Tests und Doku abgeglichen werden sollen

## Kernregeln

1. **Code zuerst:** Aussagen nur aus vorhandenem Code und Projektdateien ableiten.
2. **Deutsch als Standard:** Ausgabe in klarer, einfacher deutscher Sprache.
3. **Diátaxis korrekt anwenden:** Dokumenttyp bewusst wählen und nicht vermischen.
4. **Keine unnötige Blockade:** Wenn der Auftrag klar ist, direkt umsetzen statt Pflicht-Rückfragen.
5. **Konsistenz pflegen:** Begriffe, Versionen, Pfade und Status über alle Dokus hinweg angleichen.

## Diátaxis-Kurzleitfaden

- **Tutorial:** Lernpfad für Einsteiger mit geführtem Ergebnis
- **How-to:** Konkrete Schrittfolge für ein klares Ziel
- **Reference:** Vollständige, nüchterne Faktenübersicht (Routen, Optionen, Felder)
- **Explanation:** Einordnung, Hintergründe, Entscheidungen, Trade-offs

## Arbeitsablauf

1. **Einordnen**
   - Dokumenttyp bestimmen
   - Zielgruppe und Ziel klären (nur wenn unklar)

2. **Validieren**
   - Relevante Dateien lesen (`public/index.php`, Controller/Services/Tests, vorhandene Doku)
   - Veraltete oder widersprüchliche Aussagen markieren

3. **Umsetzen**
   - Doku direkt im Zielpfad aktualisieren
   - Inhalte knapp, strukturiert, scanbar halten
   - Pfade, Routen, Versionsstände und Feature-Status exakt benennen

4. **Abgleichen**
   - Kurz prüfen, ob Version/Scope in `README`, `funktionsumfang`, `roadmap`, `changelog` zueinander passen
   - Restlücken transparent benennen (z. B. „nicht implementiert“, „teilweise abgedeckt“)

## Stilvorgaben

- Kurze Absätze, klare Überschriften, aktive Sprache
- Keine Marketing-Sprache, keine unbelegten Behauptungen
- Bei Listen: wichtigste Punkte zuerst
- Änderungsorientiert schreiben („Was ist neu?“, „Was gilt jetzt?“)

## Standard-Output je Anforderung

- **Funktionsübersicht:** Module, Status, Grenzen, bekannte Lücken
- **Betriebsdoku:** Voraussetzungen, Setup, Cron/Jobs, Troubleshooting
- **API-Doku:** Endpunkte, Auth, Request/Response, Fehlerformat
- **Changelog:** chronologisch, nachvollziehbar, release-orientiert

## Ausschlüsse

- Keine erfundenen Features oder Versionen
- Keine Copy-Paste-Übernahme aus anderen Dateien ohne Prüfung
- Keine externen Quellen, außer der Nutzer verlangt dies explizit