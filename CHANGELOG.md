# Changelog

Alle nennenswerten Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

## [1.1.0] - 2026-02-27
### Geändert
- **Rebranding zu "Recall"**: Plugin-Name von "SmartCards" auf "Recall" geändert (`mod_recall`).
- **Didaktik-Update**: Button-Beschriftungen auf Option A ("Nochmal lernen", "Bald wiederholen", "Später wiederholen") aktualisiert, um die Selbststeuerung erwachsener Lernender zu fördern.
- **Visuelles Branding**: Neues Logo (Icon) hinzugefügt und Farbpalette auf Deep Indigo (#4F46E5) umgestellt.
- **UI-Refinement**: Glassmorphismus-Effekte und verbesserte Schatten für die Karteikarten hinzugefügt.

## [1.0.0] - 2026-02-27
### Hinzugefügt
- Deutsche Benutzeroberfläche und Moodle-Sprachpaket (`lang/de/recall.php`).
- Möglichkeit, die Karteikarte durch Antippen der Rückseite wieder auf die Frage (Vorderseite) zurückzudrehen.
- "Hinweis anzeigen"-Funktion auf der Kartenvorderseite versteckt, um selbstständiges Denken besser zu unterstützen.

### Geändert
- Das Frontend wurde auf Version 1.0.0 mit Vite neu gebündelt (`npm run build`).
- `version.php` auf Version `2026022700` (Release `1.0`, Maturity `MATURITY_BETA`) aktualisiert.

## [0.1.0] - initial implementation
### Hinzugefügt
- Grundlegendes Moodle-Modul-Setup mit Datenbanktabellen.
- Vue.js/Tailwind CSS Frontend-Setup.
- Leitner-System-Logik für verteilte Wiederholung (Spaced Repetition).
- Text-Import-Handler für Karteikarten.
