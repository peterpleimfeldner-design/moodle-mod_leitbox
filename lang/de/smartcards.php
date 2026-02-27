<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   mod_smartcards
 * @copyright 2026 SmartCards Author
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['modulename'] = 'Smartcards Aktivität';
$string['modulename_help'] = 'Die Smartcards-Aktivität ermöglicht es Schülern, Karteikarten mit einem System der verteilten Wiederholung (Spaced Repetition) nach der Leitner-Methode zu üben. Die Benutzeroberfläche nutzt einen modernen Single-Page-Application-Ansatz.';
$string['modulenameplural'] = 'Smartcards Aktivitäten';
$string['pluginadministration'] = 'Smartcards Administration';
$string['pluginname'] = 'Smartcards Aktivität';
$string['smartcards:addinstance'] = 'Neue Smartcards-Aktivität hinzufügen';
$string['smartcards:view'] = 'Smartcards-Aktivität ansehen';

$string['smartcardsname'] = 'Name der Aktivität';
$string['settings'] = 'Smartcards Einstellungen';
$string['completion_min_cards'] = 'Minimale Anzahl zu übender Karten';
$string['completion_min_cards_desc'] = 'Schüler muss mindestens diese Anzahl an Karteikarten üben:';
$string['completion_min_cards_help'] = 'Die minimale Anzahl an Karten, mit denen ein Schüler/in interagieren muss, um diese Aktivität abzuschließen.';

$string['completion_min_mastered'] = 'Gelernte Karten (Box 5)';
$string['completion_min_mastered_desc'] = 'Mindestanzahl an Karten, die die letzte Box (Gelernt) erreichen müssen:';
$string['completion_min_mastered_help'] = 'Die Anzahl an Karten, die durch korrekte Antworten befördert werden müssen, damit die Aktivität als bestanden gilt.';

$string['box0'] = 'Neu';
$string['box1'] = 'Box 1 (Falsch)';
$string['box2'] = 'Box 2';
$string['box3'] = 'Box 3';
$string['box4'] = 'Box 4';
$string['box5'] = 'Box 5 (Gelernt)';

$string['cardorder'] = 'Kartenreihenfolge';
$string['cardorder_random'] = 'Zufällig';
$string['cardorder_sequential'] = 'Der Reihe nach';
$string['cardorder_help'] = 'Bestimmt, ob die Karten in zufälliger Reihenfolge oder in der Reihenfolge, in der sie hinzugefügt wurden, angezeigt werden.';

$string['question'] = 'Frage';
$string['answer'] = 'Antwort';
$string['hint'] = 'Hinweis';

// Privacy metadata.
$string['privacy:metadata:smartcards_progress'] = 'Informationen über den Lernfortschritt bei Smartcards.';
$string['privacy:metadata:smartcards_progress:userid'] = 'Die Benutzer-ID.';
$string['privacy:metadata:smartcards_progress:cardid'] = 'Die Karten-ID.';
$string['privacy:metadata:smartcards_progress:box_number'] = 'Die Leitner-Box, in der sich die Karte aktuell befindet.';
$string['privacy:metadata:smartcards_progress:count_correct'] = 'Die Anzahl, wie oft der Benutzer die Karte richtig beantwortet hat.';
$string['privacy:metadata:smartcards_progress:count_wrong'] = 'Die Anzahl, wie oft der Benutzer die Karte falsch beantwortet hat.';
$string['privacy:metadata:smartcards_progress:last_reviewed'] = 'Der Zeitstempel, wann die Karte zuletzt überprüft wurde.';

// Management Interface
$string['managecards'] = 'Karten verwalten';
$string['backtoactivity'] = 'Zurück zur Aktivität';
$string['addsinglecard'] = 'Einzelne Karte hinzufügen';
$string['editsinglecard'] = 'Karte bearbeiten';
$string['addcard'] = 'Karte speichern';
$string['updatecard'] = 'Änderungen speichern';
$string['cardadded'] = 'Karte wurde erfolgreich hinzugefügt.';
$string['cardupdated'] = 'Karte wurde erfolgreich aktualisiert.';
$string['cancel'] = 'Abbrechen';
$string['bulkimport'] = 'Massen-Import (KI / Text)';
$string['bulkimportdesc'] = 'Füge einen formatierten Textblock ein, um mehrere Karten auf einmal zu importieren.';
$string['prompt_instruction'] = 'Kopiere diesen Prompt und nutze ihn mit ChatGPT/Claude, um aus deinen Scripten Karten zu generieren:';
$string['prompt_template'] = 'Erstelle aus folgendem Text Karteikarten zum Lernen. 
Formatiere jede Karte EXAKT in diesem Format, ohne Abweichungen, ohne Markdown (keine Sternchen, keine Rauten) und trenne alle Karten mit ===CARD===. 
Schreibe keine Einleitung, nur den reinen Text-Code.

Beispiel für das gewünschte Ausgabeformat:
===CARD===
Q: Was ist die Hauptstadt von Frankreich?
A: Paris
H: Stadt der Liebe

===CARD===
Q: Wer hat die Relativitätstheorie formuliert?
A: Albert Einstein

Hier ist der Text, aus dem du die Karten erstellen sollst:';
$string['importcards'] = 'Karten importieren';
$string['cardsimported'] = '{$a} Karten wurden erfolgreich importiert.';
$string['existingcards'] = 'Vorhandene Karten';
$string['nocards'] = 'Keine Karten in dieser Aktivität gefunden.';
$string['carddeleted'] = 'Karte wurde gelöscht.';
$string['confirmdeletecard'] = 'Bist du sicher, dass du diese Karte löschen möchtest?';

// Vue Frontend App Strings
$string['dashboardtitle'] = 'Deine SmartCards';
$string['dashboardsbtitle'] = 'Wähle einen Lernstapel zum Üben aus';
$string['howitworks'] = 'Wie funktioniert das?';
$string['cards'] = 'Karten';
$string['systemtitle'] = 'Das Lernstapel-System';
$string['systemintro'] = 'Dieses Plugin verwendet das bewährte <strong>Spaced Repetition System</strong> (verteilte Wiederholung). Ziel ist es, Karten von links nach rechts in den letzten Stapel ("Gelernt") zu befördern.';
$string['known_btn'] = 'Gewusst';
$string['known_desc'] = 'Die Karte war einfach! Sie rückt einen Stapel weiter nach rechts. Sie wird in Zukunft seltener abgefragt.';
$string['again_btn'] = 'Nochmal';
$string['again_desc'] = 'Du warst dir unsicher. Die Karte bleibt im aktuellen Stapel und wird bald wiederholt.';
$string['hard_btn'] = 'Schwer';
$string['hard_desc'] = 'Komplett vergessen! Die Karte wandert sofort wieder zurück in den Stapel "Schwer" (Box 1), egal wie weit sie war.';
$string['systemtip'] = '<strong>Tipp:</strong> Konzentriere dich immer zuerst auf die Karten, die links liegen (Box 1 und 2). Das sind die Karten, die du aktuell am intensivsten üben musst!';
$string['gotit'] = 'Verstanden, los geht\'s!';
$string['showhint'] = 'Hinweis anzeigen';
$string['taptoflip'] = 'Antippen zum Umdrehen';
$string['action_back'] = 'Zurück';
$string['action_stay'] = 'Bleibt';
$string['action_next'] = 'Nächste';
$string['backtodashboard'] = 'Zurück zum Dashboard';
$string['cardxofy_x'] = 'Karte';
$string['cardxofy_y'] = 'von';
$string['loadingcards'] = 'Karten werden gemischt...';
$string['sessiondone'] = 'Alles erledigt!';
$string['sessiondonedesc'] = 'Tolle Arbeit! Mache weiter so im nächsten Stapel.';
$string['completed'] = 'Abgeschlossen';
$string['error_loading_cards'] = 'Karten konnten nicht geladen werden. Bitte überprüfe deine Verbindung.';
