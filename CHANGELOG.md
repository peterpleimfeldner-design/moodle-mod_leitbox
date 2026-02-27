# Changelog

Alle nennenswerten Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

## [1.1.0] - 2026-02-28 (Feedback & Onboarding Update)

### Hinzugefügt
- **Abgestuftes Session-Feedback:** Dynamisches 4-Stufen-Feedback (Starkes Ergebnis, Solide Leistung, Auf dem richtigen Weg, Wiederholung empfohlen) nach Abschluss eines Stapels basierend auf dem Anteil korrekter Antworten.
- **Masterpiece-Feedback:** Ein spezielles "Meisterwerk"-Erfolgsfenster erscheint, wenn alle Karten des Systems in der Experten-Box liegen und fehlerfrei absolviert wurden.
- **Visuelle Statistiken:** Zeigt nach dem Absolvieren einen Fortschrittsbalken und Couter für "Gewusst" (Grün), "Nochmal" (Gelb) und "Schwer" (Rot) in der jeweiligen Durchgangs-Zusammenfassung an.

### Geändert
- **Gezielte Ersteindruck-Kontrolle:** Die "Willkommen bei Recall"-Demokarte wird nun serverseitig *immer* an die erste Stelle gezwungen, selbst wenn die "Karten mischen"-Option in Moodle aktiv ist.
- **Texte & Sprachpakete:** Sämtliche Demotexte sowie die Feedback-Meldungen wurden sachlicher und erwachsener formuliert ("alle" statt "die meisten"). Vue-Fallbacks hinzugefügt, um Cache-Fehler zu vermeiden.
- **Logo Update:** Das Recall-Logo in der Moodle-Aktivität verwendet nun die saubere, freigestellte PNG-Version.

---

## [1.0.0] - 2026-02-27 (Stable Marketplace Release)

### Hinzugefügt
- **Reset-Funktion (API & UI):** Neues Moodle Web-Service-Backend und ein User-Interface Icon (`🔄`), um den eigenen Lernfortschritt sicher (inkl. Warnungs-Pop-up) vor Prüfungen auf "Neu" zurückzusetzen.
- **Barrierefreiheit (WCAG 2.1 AA):** Komplette Überarbeitung des Vue-Frontends für volle Screenreader-Kompatibilität (`aria`-Attribute), Tastaturfokus und semantisches HTML.
- **Branding-Assets:** Integration der finalen Logos (`Symbol.png` und `logo.png` im Header) mit angepasster Icon-Platzierung für Moodle-Aktivitäten.
- **Umbrella Fallbacks:** Neues Fallback-String-System in Vue integriert, um Platzhalter `[[...]]` bei noch nicht geladenen Sprachpaketen zu verhindern.

### Geändert
- **Rebranding zu "Recall":** Kompletter Rename aller Dateien, Sprachpakete und Datenbanktabellen von `mod_smartcards` auf `mod_recall`.
- **Didaktik-Update & Benennung:** Motivierende Box-Namen ("Einsteiger", "Lernender", "Fortgeschritten", "Erfahren", "Experte") statt nackter Zahlen integriert.
- **Spaced Repetition Logik:** "Schwer/Nicht gewusst"-Karten fallen nun motivierend nur um 1 Box zurück, anstatt komplett auf Box 0 zurückzusetzen.
- **Visuelles Branding & UI:** Komplettes Redesign des Dashboards mit entspannenden Teal/Navy Pastellfarben, schwebenden Boxen und dezenten Fortschrittsbalken.
- **Maturity Level:** Moodle Plugin-Status offiziell auf `MATURITY_STABLE` gesetzt (`version.php` Bump auf `2026022707`).
- Frontend Bundle neu kompiliert (`npm run build`), optimiert für Production.

---

## [0.1.0] - Initial Implementation (Beta)
### Hinzugefügt
- Grundlegendes Moodle-Modul-Setup mit Datenbanktabellen.
- Vue.js / Tailwind CSS Frontend-Basis.
- Leitner-System-Logik (Standard).
- Text-Import-Handler für KI-generierte Karteikarten.
- Basis Moodle Backup/Restore & Privacy APIs implementiert.
