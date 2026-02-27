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
defined('MOODLE_INTERNAL') || die();

/**
 * Adds a new recall instance
 *
 * @param stdClass $recall
 * @return int The instance ID
 */
function recall_add_instance($recall) {
    global $DB;
    $recall->timecreated = time();
    $recall->timemodified = $recall->timecreated;
    $id = $DB->insert_record('recall', $recall);

    // Insert tutorial demo cards
    $demo_cards = [
        [
            'recallid' => $id,
            'question' => 'Willkommen bei Recall! 🎉<br><br>Was ist das Ziel dieses Lernsystems?',
            'answer' => 'Das Ziel ist es, alle Karten in den letzten Stapel ("Gelernt") zu befördern. <br><br>Das System nutzt die Methode der <b>verteilten Wiederholung</b> (Spaced Repetition).',
            'hint' => 'Denke daran, wie man sich Dinge langfristig am besten merkt.',
        ],
        [
            'recallid' => $id,
            'question' => 'Wie bewege ich diese Karte in den nächsten Stapel?',
            'answer' => 'Indem du auf den grünen Button <b>🟢 Gewusst</b> tippst. <br><br>Wenn du dir absolut sicher warst, rückt die Karte einen Schritt weiter. Sie wird dann in Zukunft seltener abgefragt.',
            'hint' => 'Es hat mit dem grünen Button zu tun.',
        ],
        [
            'recallid' => $id,
            'question' => 'Was passiert, wenn ich auf den roten Button <b>🔴 Schwer</b> drücke?',
            'answer' => 'Dann wandert die Karte sofort wieder ganz an den Anfang in den Stapel "Schwer" (Box 1) zurück!<br><br>Mache das immer dann, wenn du die Antwort komplett vergessen hast. So stellst du sicher, dass du sie bald wieder lernst.',
            'hint' => 'Rot bedeutet Gefahr bzw. völliger Gedächtnisverlust.',
        ],
        [
            'recallid' => $id,
            'question' => 'Wofür ist der gelbe Button <b>🟡 Nochmal</b> gedacht?',
            'answer' => 'Wenn du einen Fehler gemacht hast, aber eigentlich wusstest worum es geht, oder ziemlich unsicher warst. <br><br>Die Karte bleibt dann im aktuellen Stapel und wird dir bald wieder vorgelegt.',
            'hint' => 'Zwischen Grün und Rot.',
        ],
        [
            'recallid' => $id,
            'question' => 'Tipp: Kann ich diese Demo-Karten später löschen?',
            'answer' => 'Klar! Sobald du erste eigene Karten importiert hast, kannst du diese Erklärungs-Karten in der Verwaltungsoberfläche einfach herauslöschen.',
            'hint' => null,
        ]
    ];

    foreach ($demo_cards as $card) {
        $DB->insert_record('recall_cards', (object)$card);
    }

    return $id;
}

/**
 * Updates an existing recall instance
 *
 * @param stdClass $recall
 * @return bool
 */
function recall_update_instance($recall) {
    global $DB;
    $recall->timemodified = time();
    $recall->id = $recall->instance;
    return $DB->update_record('recall', $recall);
}

/**
 * Deletes a recall instance
 *
 * @param int $id The instance ID
 * @return bool
 */
function recall_delete_instance($id) {
    global $DB;
    if (!$recall = $DB->get_record('recall', ['id' => $id])) {
        return false;
    }
    
    $sql = "SELECT id FROM {recall_cards} WHERE recallid = ?";
    if ($cards = $DB->get_records_sql($sql, [$id])) {
        list($in, $params) = $DB->get_in_or_equal(array_keys($cards));
        $DB->delete_records_select('recall_progress', "cardid $in", $params);
    }
    
    $DB->delete_records('recall_cards', ['recallid' => $id]);
    $DB->delete_records('recall', ['id' => $id]);
    return true;
}

/**
 * Returns features supported by the module.
 *
 * @param string $feature
 * @return mixed
 */
function recall_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO: return true;
        case FEATURE_SHOW_DESCRIPTION: return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES: return true;
        case FEATURE_BACKUP_MOODLE2: return true;
        default: return null;
    }
}

/**
 * Returns the custom completion rules for this module.
 *
 * @param stdClass $course
 * @param cm_info $cm
 * @return array
 */
function recall_get_completion_active_rule_descriptions($course, $cm) {
    $rules = [];
    if (!empty($cm->customdata['customcompletionrules']['completion_min_cards'])) {
        $rules[] = get_string('completion_min_cards_desc', 'mod_recall') . ' ' . $cm->customdata['customcompletionrules']['completion_min_cards'];
    }
    if (!empty($cm->customdata['customcompletionrules']['completion_min_mastered'])) {
        $rules[] = get_string('completion_min_mastered_desc', 'mod_recall') . ' ' . $cm->customdata['customcompletionrules']['completion_min_mastered'];
    }
    return $rules;
}

/**
 * Obtains the automatic completion state for this module based on any custom
 * conditions in the mod_recall record.
 *
 * @param stdClass $course Course
 * @param cm_info $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function recall_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    $recall = $DB->get_record('recall', ['id' => $cm->instance], '*', MUST_EXIST);
    
    // Check if the conditions are set
    if (!$recall->completion_min_cards && !$recall->completion_min_mastered) {
        return $type; // Rules not enabled
    }

    $completed = true;

    if ($recall->completion_min_cards) {
        $sql = "SELECT COALESCE(SUM(count_correct + count_wrong), 0) AS total_reviews
                  FROM {recall_progress}
                 WHERE userid = :userid AND cardid IN (
                     SELECT id FROM {recall_cards} WHERE recallid = :instanceid
                 )";
        $total_reviews = $DB->get_field_sql($sql, ['userid' => $userid, 'instanceid' => $recall->id]);

        if ($total_reviews < $recall->completion_min_cards) {
            $completed = false;
        }
    }

    if ($recall->completion_min_mastered) {
        $sql_mastered = "SELECT COUNT(*)
                           FROM {recall_progress}
                          WHERE userid = :userid 
                            AND box_number = 5
                            AND cardid IN (
                                SELECT id FROM {recall_cards} WHERE recallid = :instanceid
                            )";
        $total_mastered = $DB->get_field_sql($sql_mastered, ['userid' => $userid, 'instanceid' => $recall->id]);

        if ($total_mastered < $recall->completion_min_mastered) {
            $completed = false;
        }
    }

    return $completed;
}

/**
 * Extends the settings navigation for the recall module.
 *
 * @param settings_navigation $settings
 * @param navigation_node $recallnode
 */
function recall_extend_settings_navigation(settings_navigation $settings, navigation_node $recallnode) {
    global $PAGE;

    if (has_capability('moodle/course:manageactivities', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/recall/manage.php', ['id' => $PAGE->cm->id]);
        $recallnode->add(
            get_string('managecards', 'mod_recall'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'managecards',
            new pix_icon('i/settings', '')
        );
    }
}

