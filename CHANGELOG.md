# Changelog

Alle nennenswerten Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

## [1.4.20] - 2026-03-01 (Moodle 4.3+ Checkbox State Fix)

### Behoben
- **Activity Completion (Bugfix):** Es wurde nun eine exakte Replikation der nativen `mod_quiz`-Logik für Moodle 4.3+ Abschlussbedingungen eingebaut. Moodle vergibt bei Sammelbearbeitungen (Bulk Edit) im Hintergrund Suffixe an Formularfelder, was dazu führte, dass die Checkboxen der Abschlussbedingungen ihre Werte beim Speichern und Laden (data_preprocessing / data_postprocessing) verloren haben. Die Checkboxen setzen jetzt den vorausgehenden Zahlenwert (z.B. 10 Karten) korrekt zurück, wenn sie deaktiviert werden, und ihr Haken bleibt verlässlich gespeichert!

---

## [1.4.19] - 2026-03-01 (Moodle 4 Form UI Completion Fix)

### Behoben
- **Activity Completion (Bugfix):** Es wurde ein kritischer Fehler im Einstellungsformular in Moodle 4.0+ behoben. Weil die neuen numerischen Moodle-Regeln intern in einer `Checkbox-Gruppe` zusammengefasst waren, hat die Formular-Validierung fälschlicherweise beim Speichern "Keine Regeln aktiviert" an die Backend-Datenbank von Moodle gemeldet, selbst wenn ein Wert wie "10 Karten" eingetippt wurde. Die verwirrenden Checkboxen für numerische Werte wurden komplett entfernt. Ab sofort reicht es, native Moodle-Logik anzuwenden: Tippt man eine Zahl (`>0`) ein, ist die Regel automatisch aktiv! Ist das Feld leer oder `0`, ist die Regel deaktiviert.

---

## [1.4.18] - 2026-03-01 (Moodle 4+ Completion Tracking Fix)

### Behoben
- **Activity Completion (Bugfix):** Es wurde ein kritischer Fehler in der Kommunikation mit der Moodle 4+ Completion API behoben. Da die neuen `custom_completion` Standard-Klassen für Moodle 4 fehlten, interpretierte der Moodle-Core die Zwischenstände von LeitBox als "Ohne Regeln abgeschlossen" und markierte die Aktivität oft fälschlicherweise nach der ersten gelernten Karte als "Erledigt". Die Architektur wurde nun nativ an Moodle 4 (Klasse `mod_leitbox\completion\custom_completion`) angepasst und die Fallback-Berechnungen strikt abgedichtet.

---

## [1.4.17] - 2026-03-01 (Completion Data Preprocessing Fix)

### Behoben
- **Activity Completion (Bugfix):** Ein Fehler wurde behoben, durch den benutzerdefinierte Abschlussbedingungen (z.B. "Mindestens 6 Karten üben") beim Bearbeiten der Aktivität im Moodle-Formular nicht korrekt geladen und beim erneuten Speichern der Aktivität ungewollt auf `0` (deaktiviert) zurückgesetzt wurden. Dies führte dazu, dass die Aktivität bereits nach der ersten Karte fälschlicherweise als "Erledigt" markiert wurde. Die fehlende `data_preprocessing`-Methode wurde in der `mod_form.php` nachgepflegt.

---

## [1.4.16] - 2026-03-01 (Support Email Integration)

### Hinzugefügt
- **Kontakt & Support:** Die offizielle Support-E-Mail-Adresse (`leitbox.moodle@gmail.com`) wurde in die grundlegenden Projektdokumente (README, Marketing-Materialien, Code-Header) integriert, um eine zentrale Anlaufstelle für Anfragen und Bug-Reports zu etablieren.

---

## [1.4.15] - 2026-03-01 (Wording Refinement English Translation)

### Geändert
- **Sprachdateien (Englisch):** Die methodische und historische Richtigstellung im 'Wie funktioniert das?' Modal wurde nun auch für die internationale Version in `lang/en/leitbox.php` akkurat ins Englische übersetzt und übernommen.

---

## [1.4.14] - 2026-03-01 (Wording Refinement)

### Geändert
- **Modal "Wie funktioniert das?":** Der Einleitungstext, welcher das Lernstapel-System erklärt, wurde umgeschrieben. Die Methodik wird nun historisch korrekt Sebastian Leitner (1972) zugeschrieben, anstatt rein den allgemeinen Begriff "Spaced Repetition System" zu verwenden. Dies betrifft sowohl die PHP-Sprachdateien als auch die Hardcoded-Fallbacks im Vue-Frontend.

---

## [1.4.13] - 2026-03-01 (Language String Cleanup & UX Polish)

### Behoben
- **Sprachdateien (Deutsch & Englisch):** Es wurden identische (doppelte) veraltete Übersetzungs-Strings für das Session-Feedback und den "Karte gelöscht" Hinweistext im Kopf der jeweiligen Dateien `lang/de/leitbox.php` und `lang/en/leitbox.php` identifiziert und sicher entfernt. Der Quellcode ist nun sauber und verwendet ausschließlich die aktuellen Übersetzungen.

### Hinzugefügt
- **Admin-Interface:** In den Aktivitäts-Einstellungen (`mod_form.php`) für Moodle-Trainer wurden die drei offiziellen **Hilfe-Buttons** (Fragezeichen-Icons) neben den Abschlussbedingungen "Minimale Anzahl zu übender Karten", "Minimale Anzahl gelernter Karten" und "Alle Karten lückenlos bis Status Experte lernen" reaktiviert. Die erklärenden Tooltips waren in den Sprachdateien bereits vorhanden, aber in der Nutzeroberfläche zuvor nicht angebunden.

---

## [1.4.12] - 2026-03-01 (Robust Demo Deletion & Unit Tests)

### Geändert
- **Demo-Karten Bereinigung:** Die Logik zum automatischen Löschen der initialen Demo-Karten in `manage.php` wurde deutlich robuster programmiert. Anstatt blind die ersten 5 Karten zu betrachten, wertet das System nun per striktem Datenbank-Befehl aus, ob bereits eigene (Nicht-Demo) Karten angelegt wurden, bevor die Bereinigung zugelassen wird.

### Hinzugefügt
- **Unit Tests:** In `tests/external_test.php` wurden neue automatisierte PHPUnit-Tests hinzugefügt, um die Fehlerbehandlung bei ungültigen Box-Nummer-Abfragen (API Bounds Check) sowie die korrekte `DISTINCT`-Identifikation von einzigartigen Karten-Lernvorgängen innerhalb der Abschlussverfolgung mathematisch zu beweisen.

---

## [1.4.11] - 2026-03-01 (Code Review & Cleanup)

### Behoben
- **Backup API:** Ein ungültiger (`annotate_ids`) Aufruf mit totem Code in `backup_leitbox_stepslib.php` wurde entfernt, da das Plugin keine direkten User-IDs in der Haupt-Tabelle speichert.
- **Exception Handling:** Die veraltete Moodle 3.x Funktion `print_error()` in `manage.php` wurde durch die korrekte `throw new \moodle_exception()` ersetzt.
- **Demo-Karten Erkennung:** Das Erkennen von automatisch generierten Demo-Karten basierte auf fragilen String-Vergleichen (Text-basiert), die bei Übersetzungen gebrochen wären. Die Demo-Karten erhalten nun bei Installation intern den Datenbank-Category-Flag `demo` und werden darüber manipulationssicher identifiziert und gelöscht.
- **API Validierung:** In `external.php` wurde bei `get_cards_by_box` eine strenge Validierung für die Box-Nummer eingeführt (`$box < 0 || $box > 5`), die nun mit einem fatalen Fehler abbricht, wenn manipulierte REST-Anfragen gesendet werden.
- **Upgrade Pfade:** Anachronistische Versionsnummern (2024...) in der `db/upgrade.php` wurden bereinigt und an das Release-Jahr 2026 angepasst.

---

## [1.4.10] - 2026-03-01 (Moodle Forms API Checkbox Fix)

### Behoben
- **Abschlussbedingung Formular (Speicher-Bug):** Ein kritischer Fehler in `mod_form.php` wurde behoben. Die Checkbox für "Alle Karten gemeistert" war fälschlicherweise in einer Moodle Formular-Gruppe (`addGroup()`) gekapselt. Dies verursachte beim Speichern ein verschachteltes Array (`$data['completion_all_mastered_group']['completion_all_mastered']`), weshalb Moodle den Wert auf der obersten Ebene nicht fand und immer `0` (deaktiviert) in die Datenbank speicherte. Die Checkbox ist nun ein vollwertiges `addElement` auf Hauptebene. Das Feld wird nun korrekt aus den Einstellungen im Backend gespeichert.

---

## [1.4.9] - 2026-03-01 (Completion SQL DISTINCT Fix)

### Geändert
- **Abschlussbedingung SQL (Fix für alle Bedingungen):** Alle 3 Completion-SQL-Abfragen verwenden jetzt `DISTINCT` um Mehrfachzählungen bei wiederholten Karten-Interaktionen zu verhindern. Bei der Bedingung "Alle Karten gemeistert" (Bedingung 3) wurde zudem der Aufruf von `count_records_sql` durch `get_field_sql` ersetzt, um eine fehlerhafte SQL-Übersetzung der Moodle-Core Engine beim Einsatz von `DISTINCT` abzufangen.

---

## [1.4.8] - 2026-03-01 (SQL Semantic Refinement)

### Geändert
- **Abschlussbedingung "Minimale Anzahl zu übender Karten":** Die zugrunde liegende SQL-Abfrage verwendet nun explizit `COUNT(DISTINCT cardid)` anstelle von `COUNT(id)`. Dies erhöht die semantische Klarheit massiv und garantiert, dass immer streng nach einzigartigen gelernten Karten (und nicht nach der Anzahl der Klicks) abgerechnet wird, falls zukünftige Updates die Struktur der Progress-Tabelle erweitern sollten.

---

## [1.4.7] - 2026-03-01 (Completion Default Logic Overhaul)

### Behoben
- **Vorzeitiger Abschluss (Basislogik):** Die interne Logik der Moodle-Abschlussverfolgung in `lib.php` (`leitbox_get_completion_state`) wurde von Grund auf korrigiert. Bisher ging das System optimistisch von `$completed = true` aus und suchte nur nach Fehlern. Dies führte bei neu angelegten Aktivitäten dazu, dass Bedingungen sofort "erfüllt" wurden, bevor der Schüler überhaupt startete. Die Funktion verlangt nun explizite, messbare Erfolge (Echte Datenbankeinträge) pro aktivierter Regel.

---

## [1.4.6] - 2026-03-01 (Completion Logic & Trigger Hotfix)

### Behoben
- **Vorzeitiger Abschluss (API Event):** Die in `1.4.1` eingeführte Live-Aktualisierung überschrieb Moodles Berechnungs-Logik ungeprüft mit `COMPLETION_COMPLETE`, weshalb bei der allerersten beantworteten Karte sofort alle Regeln als erfüllt galten. Das Skript ruft Moodles Evaluierungsnetzwerk nun formal mit dem Flag `COMPLETION_UNKNOWN` auf, wodurch die echten Backend-Regeln wieder das Zepter übernehmen.
- **Logikfehler "Mindestkarten":** Die Bedingung "Mindestanzahl an Karten" zählte bisher versehentlich alle Karteikarten-Klicks (also auch, wenn man drei Mal dieselbe Karte gelernt hat). Der SQL-Query zählt nun korrekterweise strikt die Anzahl an einzigartigen (einzelnen) Karteikarten, mit denen der User interagiert hat.

---

## [1.4.5] - 2026-03-01 (Syntax Error Hotfix)

### Behoben
- **Server Error (PHP Parse Error):** Behebt einen fatalen Syntaxfehler (fehlende Array-Klammer `];`) in der `view.php`, der aus einem fehlerhaften automatischen Merge des Completion-Tracker-Updates resultierte und zum HTTP 500 Fehler führte.

---

## [1.4.4] - 2026-03-01 (Moodle API Registration Fix)

### Behoben
- **Server Error (`coding_exception`):** Der verbleibende HTTP 500 Fehler trat bei der Neuanlage von LeitBox-Aktivitäten mit aktiven Abschlussbedingungen auf. Dies war eine sehr strikte Moodle-Regel: Ein Plugin, das Abschlussregeln definiert (`FEATURE_COMPLETION_HAS_RULES = true`), erfordert zwangsweise die Callback-Funktion `pluginname_get_custom_completion_rules()`. Diese fehlte im Moodle-Core des Plugins und wurde nun in `lib.php` nachgerüstet. Moodle stürzt beim Anlegen jetzt nicht mehr ab.

---

## [1.4.3] - 2026-03-01 (Moodle Core Database Hotfix)

### Behoben
- **Server Error (`dml_read_exception`):** Der verbleibende HTTP 500 Fehler wurde behoben. Die Ursache lag in einer systemfremden SQL-Abfrage (`SELECT ... ohne FROM`) innerhalb der `lib.php`, an der die strenge Moodle 4.x Datenbank-API bei der Abschluss-Prüfung abstürzte. Die Abfrage wurde in native Moodle-Funktionen (`count_records`) umgeschrieben.

---

## [1.4.2] - 2026-03-01 (HTTP 500 Hotfix)

### Behoben
- **Server Error (`view.php`):** Behebt einen kritischen HTTP 500 Fehler, der auftrat, weil die Moodle `completionlib.php` Bibliothek vor dem Aufruf des Trackers nicht explizit in die Laufzeit geladen wurde.

---

## [1.4.1] - 2026-03-01 (Completion Hotfix)

### Behoben
- **Abschlussverfolgung (Echtzeit):** Die Aktivität triggerte Moodles Abschluss-API (`update_state`) nicht korrekt live im AJAX-Backend. Die Abschlussprüfung wird nun nach jeder Karte sofort ausgelöst.
- **Abschlussverfolgung (Anzeige):** Aufruf von `$completion->set_module_viewed($cm)` zur `view.php` hinzugefügt, sodass "Aktivität muss aufgerufen werden" ebenfalls zuverlässig erfüllt wird.

---

## [1.4.0] - 2026-03-01 (Rebranding & Custom Completion Update)

### Geändert
- **Komplette Umbenennung:** Der Plugin-Name wurde aufgrund von Namenskonflikten projektweit von "Recall" in "LeitBox" umgewandelt (Dateien, Variablen, Pfade, Language-Strings).
- **Logos & Branding:** Einbau der neuen, zu 100% freigestellten transparenten PNG-Logos (`logo.png`, `icon.png`).

### Hinzugefügt
- **Neue Moodle-Abschlussbedingung ("Alle Karten gelernt"):** Lehrer können nun erzwingen, dass eine LeitBox-Aktivität erst dann den grünen Haken erhält, wenn der Schüler ausnahmslos *alle* Lernkarten erfolgreich in die Experten-Box (Box 5) befördert hat. Die Datenbank und Backupfunktionen wurden hierfür erweitert.

---

## [1.3.1] - 2026-02-28 (Performance & Reliability Update)

### Geändert
- **Performance (Dashboard Load):** Die `getBoxCounts` Funktion triggert nicht länger 6 parallele API-Aufrufe mit dem gesamten Kartendatenstrom. Ein neuer dedizierter, aggregierter REST-Endpunkt `mod_leitbox_get_box_counts` summiert die Karten serverseitig in Millisekunden und überträgt nur noch die 6 Zahlenwerte. Dies reduziert den Netzwerk-Payload bei 200 Karten um ca. 98%.

### Behoben
- **Race-Condition bei Frontend-Übersetzungen:** Wenn Moodle beim Ladezyklus Strings blockierte oder nicht fand, zeigte die Ansicht leere Platzhalter wie `[[showhint]]`. Ein robustes Fallback-Dictionary (`FALLBACKS`) in `Card.vue` fängt dies nun verlässlich ab.
- **Backup & Restore Link-Dekodierung:** Fehlende `LEITBOXINDEX` Decode-Rules in `restore_leitbox_activity_task.php` ergänzt, sodass in Kursen platzierte Links auf Flashcard-Decks bei der Wiederherstellung korrekt aufgelöst statt als `$@LEITBOXVIEWBYID*...` roh im Text stehengelassen werden. Abwärtskompatibilität für alte `SMARTCARDS`-Backups bleibt bestehen.

---

## [1.2.0] - 2026-02-28 (Kartenverwaltung & KI-Prompt Update)

### Hinzugefügt
- **KI-Prompt-Vorlagenauswahl:** 6 verschiedene Prompt-Templates (Standard, Wahr/Falsch, Vokabeln, Lückentext, Jeopardy, Transfer & Alltagsbezug) per Dropdown auswählbar. Der gewählte Prompt wird live im Vorschaufenster angezeigt und kann direkt kopiert werden.
- **Massenauswahl & Bulk-Delete:** Checkbox-System mit „Alle auswählen"-Funktion zum gleichzeitigen Löschen mehrerer Karten.
- **Kartenexport (.txt):** Exportfunktion, die alle Karten im nativen `===CARD===`-Format als Textdatei herunterlädt (reimportfähig).
- **Didaktisches Kartenlimit:** Hartes Limit von 200 Karten pro Aktivität mit erklärendem Hinweis. Server-seitig für Einzelkarten und Massenimport erzwungen.
- **Session-Feedback-Übersetzungen:** Alle 10 Feedback-Texte (Meisterwerk, Starkes Ergebnis, etc.) sind jetzt vollständig in Deutsch und Englisch verfügbar.
- **Fortschrittsbalken-Übersetzung:** Der Fortschrittstext und sein barrierefreies aria-label sind jetzt korrekt internationalisiert.

### Geändert
- **Plugin-Name vereinfacht:** Von „LeitBox Aktivität" zu „LeitBox" – konsistent mit Moodle-Namenskonventionen (Forum, Glossar, etc.).
- **Reset-Button:** Visuelle Umgestaltung von rot zu neutralem Grau für ein harmonischeres Dashboard-Layout.
- **Header-Layout:** Logo/Titel links, Buttons rechts – bündige Ausrichtung mit dem Fortschrittsbalken darunter.
- **KI-Prompt-Text:** Herstellerunabhängig formuliert („nutze ihn mit einer beliebigen KI" statt spezifischer Markennamen).

### Behoben
- **PHP Warning bei Bulk-Delete ohne Auswahl:** `Undefined variable $valid_cards` behoben, wenn „Ausgewählte löschen" ohne Checkbox-Auswahl geklickt wurde.
- **Fehlender `cancel`-String:** Der „Abbrechen"-Button zeigte `[[cancel]]` statt des übersetzten Texts – Sprachstring wiederhergestellt.
- **Hardcodierte deutsche Strings im Frontend:** Zwei Fortschrittstexte im Vue-Frontend waren nicht übersetzbar – durch `getString()` ersetzt.

---

## [1.1.0] - 2026-02-28 (Feedback & Onboarding Update)

### Hinzugefügt
- **Abgestuftes Session-Feedback:** Dynamisches 4-Stufen-Feedback (Starkes Ergebnis, Solide Leistung, Auf dem richtigen Weg, Wiederholung empfohlen) nach Abschluss eines Stapels basierend auf dem Anteil korrekter Antworten.
- **Masterpiece-Feedback:** Ein spezielles "Meisterwerk"-Erfolgsfenster erscheint, wenn alle Karten des Systems in der Experten-Box liegen und fehlerfrei absolviert wurden.
- **Visuelle Statistiken:** Zeigt nach dem Absolvieren einen Fortschrittsbalken und Couter für "Gewusst" (Grün), "Nochmal" (Gelb) und "Schwer" (Rot) in der jeweiligen Durchgangs-Zusammenfassung an.

### Geändert
- **Gezielte Ersteindruck-Kontrolle:** Die "Willkommen bei LeitBox"-Demokarte wird nun serverseitig *immer* an die erste Stelle gezwungen, selbst wenn die "Karten mischen"-Option in Moodle aktiv ist.
- **Texte & Sprachpakete:** Sämtliche Demotexte sowie die Feedback-Meldungen wurden sachlicher und erwachsener formuliert ("alle" statt "die meisten"). Vue-Fallbacks hinzugefügt, um Cache-Fehler zu vermeiden.
- **Logo Update:** Das LeitBox-Logo in der Moodle-Aktivität verwendet nun die saubere, freigestellte PNG-Version.

---

## [1.0.0] - 2026-02-27 (Stable Marketplace Release)

### Hinzugefügt
- **Reset-Funktion (API & UI):** Neues Moodle Web-Service-Backend und ein User-Interface Icon (`🔄`), um den eigenen Lernfortschritt sicher (inkl. Warnungs-Pop-up) vor Prüfungen auf "Neu" zurückzusetzen.
- **Barrierefreiheit (WCAG 2.1 AA):** Komplette Überarbeitung des Vue-Frontends für volle Screenreader-Kompatibilität (`aria`-Attribute), Tastaturfokus und semantisches HTML.
- **Branding-Assets:** Integration der finalen Logos (`Symbol.png` und `logo.png` im Header) mit angepasster Icon-Platzierung für Moodle-Aktivitäten.
- **Umbrella Fallbacks:** Neues Fallback-String-System in Vue integriert, um Platzhalter `[[...]]` bei noch nicht geladenen Sprachpaketen zu verhindern.

### Geändert
- **Rebranding zu "LeitBox":** Kompletter Rename aller Dateien, Sprachpakete und Datenbanktabellen von `mod_smartcards` auf `mod_leitbox`.
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
