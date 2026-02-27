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
 * @package   mod_recall
 * @copyright 2026 Recall Author
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['modulename'] = 'Recall Aktivität';
$string['modulename_help'] = 'Die Recall-Aktivität ermöglicht es Schülern, Karteikarten mit einem System der verteilten Wiederholung (Spaced Repetition) nach der Leitner-Methode zu üben. Die Benutzeroberfläche nutzt einen modernen Single-Page-Application-Ansatz.';
$string['modulenameplural'] = 'Recall Aktivitäten';
$string['pluginadministration'] = 'Recall Administration';
$string['pluginname'] = 'Recall Aktivität';
$string['recall:addinstance'] = 'Neue Recall-Aktivität hinzufügen';
$string['recall:view'] = 'Recall-Aktivität ansehen';

$string['recallname'] = 'Name der Aktivität';
$string['settings'] = 'Recall Einstellungen';
$string['completion_min_cards'] = 'Minimale Anzahl zu übender Karten';
$string['completion_min_cards_desc'] = 'Schüler muss mindestens diese Anzahl an Karteikarten üben:';
$string['completion_min_cards_help'] = 'Die minimale Anzahl an Karten, mit denen ein Schüler/in interagieren muss, um diese Aktivität abzuschließen.';

$string['completion_min_mastered'] = 'Gelernte Karten (Box 5)';
$string['completion_min_mastered_desc'] = 'Mindestanzahl an Karten, die die letzte Box (Gelernt) erreichen müssen:';
$string['completion_min_mastered_help'] = 'Die Anzahl an Karten, die durch korrekte Antworten befördert werden müssen, damit die Aktivität als bestanden gilt.';

$string['box0'] = 'Neu';
$string['box1'] = 'Einsteiger';
$string['box2'] = 'Lernender';
$string['box3'] = 'Fortgeschritten';
$string['box4'] = 'Erfahren';
$string['box5'] = 'Experte';

$string['cardorder'] = 'Kartenreihenfolge';
$string['cardorder_random'] = 'Zufällig';
$string['cardorder_sequential'] = 'Der Reihe nach';
$string['cardorder_help'] = 'Bestimmt, ob die Karten in zufälliger Reihenfolge oder in der Reihenfolge, in der sie hinzugefügt wurden, angezeigt werden.';

$string['question'] = 'Frage';
$string['answer'] = 'Antwort';
$string['hint'] = 'Hinweis';

// Privacy metadata.
$string['privacy:metadata:recall_progress'] = 'Informationen über den Lernfortschritt bei Recall.';
$string['privacy:metadata:recall_progress:userid'] = 'Die Benutzer-ID.';
$string['privacy:metadata:recall_progress:cardid'] = 'Die Karten-ID.';
$string['privacy:metadata:recall_progress:box_number'] = 'Die Leitner-Box, in der sich die Karte aktuell befindet.';
$string['privacy:metadata:recall_progress:count_correct'] = 'Die Anzahl, wie oft der Benutzer die Karte richtig beantwortet hat.';
$string['privacy:metadata:recall_progress:count_wrong'] = 'Die Anzahl, wie oft der Benutzer die Karte falsch beantwortet hat.';
$string['privacy:metadata:recall_progress:last_reviewed'] = 'Der Zeitstempel, wann die Karte zuletzt überprüft wurde.';

// Management Interface
$string['demo_q1'] = 'Willkommen bei Recall.<br><br>Was ist das Ziel dieses Lernsystems?';
$string['demo_a1'] = 'Das Ziel ist es, alle Lernkarten durch korrektes Beantworten in den letzten Stapel ("Experte") zu befördern.<br><br>Das System nutzt dafür die Methode der <b>verteilten Wiederholung</b> (Spaced Repetition).';
$string['demo_h1'] = 'Denke daran, wie man sich Dinge langfristig am besten merkt.';
$string['demo_q2'] = 'Wie bewege ich eine Karte in den nächsten Stapel?';
$string['demo_a2'] = 'Indem du auf den <b>grünen</b> Button <b>Gewusst</b> klickst.<br><br>Dies bedeutet, dass dir die Antwort geläufig war. Die Karte rückt einen Stapel weiter und wird in künftigen Durchgängen seltener abgefragt.';
$string['demo_h2'] = 'Es hat mit dem grünen Häkchen zu tun.';
$string['demo_q3'] = 'Was passiert bei Klick auf den <b>roten</b> Button <b>Schwer</b>?';
$string['demo_a3'] = 'Die Karte wandert genau <b>einen Stapel zurück</b>.<br><br>Wähle diese Option, wenn du die Antwort nicht wusstest. Die Karte wird dadurch häufiger wiederholt, ohne dass der bisherige Lernfortschritt komplett zurückgesetzt wird.';
$string['demo_h3'] = 'Nicht gewusst!';
$string['demo_q4'] = 'Wofür steht der <b>gelbe</b> Button <b>Nochmal</b>?';
$string['demo_a4'] = 'Diese Option ist für Fälle gedacht, in denen die Antwort unsicher oder unvollständig war.<br><br>Die Karte bleibt im aktuellen Stapel. Sie wird in derselben Lernrunde erneut vorgelegt, der generelle Fortschritt bleibt jedoch erhalten.';
$string['demo_h4'] = 'Die goldene Mitte zwischen Leicht und Schwer.';
$string['demo_q5'] = 'Bedienungshinweis: Können diese Demo-Karten gelöscht werden?';
$string['demo_a5'] = 'Ja. Sobald eigene Lernkarten in das System importiert oder angelegt werden, entfernt das System diese Demo-Karten automatisch.';
$string['demo_h5'] = '';

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
$string['dashboardtitle'] = 'Deine Recall Karten';
$string['dashboardsbtitle'] = 'Wähle einen Lernstapel zum Üben aus';
$string['howitworks'] = 'Wie funktioniert das?';
$string['cards'] = 'Karten';
$string['systemtitle'] = 'Das Lernstapel-System';
$string['systemintro'] = 'Dieses Plugin verwendet das bewährte <strong>Spaced Repetition System</strong> (verteilte Wiederholung). Ziel ist es, Karten von links nach rechts in den letzten Stapel ("Gelernt") zu befördern.';
$string['known_btn'] = 'Gewusst';
$string['known_desc'] = 'Die Karte war einfach! Sie rückt einen Stapel weiter nach rechts.';
$string['again_btn'] = 'Nochmal';
$string['again_desc'] = 'Du warst dir unsicher. Die Karte bleibt im aktuellen Stapel.';
$string['hard_btn'] = 'Schwer';
$string['hard_desc'] = 'Nicht gewusst! Die Karte rückt einen Stapel zurück.';
$string['systemtip'] = '<strong>Tipp:</strong> Beschäftige dich mit den Themen hinter den Karten, die du nicht wusstest – bevor du einen neuen Versuch startest.';
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
$string['sessiondonedesc'] = 'Gute Arbeit. Gehe zurück zum Dashboard für den nächsten Stapel.';
$string['feedback_perfect_title'] = 'Starkes Ergebnis';
$string['feedback_perfect_desc'] = 'Du hast die meisten Inhalte korrekt ins Gedächtnis gerufen. Das Material sitzt.';
$string['feedback_good_title'] = 'Solide Leistung';
$string['feedback_good_desc'] = 'Du hast einen guten Überblick. Die verbleibenden Lücken schließen sich mit der Zeit.';
$string['feedback_okay_title'] = 'Auf dem richtigen Weg';
$string['feedback_okay_desc'] = 'Die Basis steht. Eine weitere Wiederholung wird dein Wissen festigen.';
$string['feedback_learn_title'] = 'Wiederholung empfohlen';
$string['feedback_learn_desc'] = 'Einige Antworten fielen dir schwer. Nutze die nächste Runde, um diese zu trainieren.';
$string['completed'] = 'Abgeschlossen';
$string['error_loading_cards'] = 'Karten konnten nicht geladen werden. Bitte überprüfe deine Verbindung.';
$string['reset_progress'] = 'Lernfortschritt zurücksetzen';
$string['reset_progress_confirm_title'] = 'Lernfortschritt zurücksetzen?';
$string['reset_progress_confirm_msg'] = 'Wirklich zurücksetzen? Dadurch geht dein bisheriger Fortschritt verloren.';
$string['reset_progress_btn'] = 'Ja, zurücksetzen';
$string['reset_progress_cancel'] = 'Abbrechen';
$string['reset_progress_done'] = 'Lernfortschritt wurde zurückgesetzt!';
