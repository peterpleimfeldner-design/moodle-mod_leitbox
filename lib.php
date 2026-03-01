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
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Adds a new leitbox instance
 *
 * @param stdClass $leitbox
 * @return int The instance ID
 */
function leitbox_add_instance($leitbox) {
    global $DB;
    $leitbox->timecreated = time();
    $leitbox->timemodified = $leitbox->timecreated;
    $id = $DB->insert_record('leitbox', $leitbox);

    // Insert tutorial demo cards using localized strings
    $demo_cards = [
        [
            'leitboxid' => $id,
            'question' => get_string('demo_q1', 'mod_leitbox'),
            'answer' => get_string('demo_a1', 'mod_leitbox'),
            'hint' => get_string('demo_h1', 'mod_leitbox'),
        ],
        [
            'leitboxid' => $id,
            'question' => get_string('demo_q2', 'mod_leitbox'),
            'answer' => get_string('demo_a2', 'mod_leitbox'),
            'hint' => get_string('demo_h2', 'mod_leitbox'),
        ],
        [
            'leitboxid' => $id,
            'question' => get_string('demo_q3', 'mod_leitbox'),
            'answer' => get_string('demo_a3', 'mod_leitbox'),
            'hint' => get_string('demo_h3', 'mod_leitbox'),
        ],
        [
            'leitboxid' => $id,
            'question' => get_string('demo_q4', 'mod_leitbox'),
            'answer' => get_string('demo_a4', 'mod_leitbox'),
            'hint' => get_string('demo_h4', 'mod_leitbox'),
        ],
        [
            'leitboxid' => $id,
            'question' => get_string('demo_q5', 'mod_leitbox'),
            'answer' => get_string('demo_a5', 'mod_leitbox'),
            'hint' => get_string('demo_h5', 'mod_leitbox'),
        ]
    ];

    foreach ($demo_cards as $card) {
        $DB->insert_record('leitbox_cards', (object)$card);
    }

    return $id;
}

/**
 * Updates an existing leitbox instance
 *
 * @param stdClass $leitbox
 * @return bool
 */
function leitbox_update_instance($leitbox) {
    global $DB;
    $leitbox->timemodified = time();
    $leitbox->id = $leitbox->instance;
    return $DB->update_record('leitbox', $leitbox);
}

/**
 * Deletes a leitbox instance
 *
 * @param int $id The instance ID
 * @return bool
 */
function leitbox_delete_instance($id) {
    global $DB;
    if (!$leitbox = $DB->get_record('leitbox', ['id' => $id])) {
        return false;
    }
    
    $sql = "SELECT id FROM {leitbox_cards} WHERE leitboxid = ?";
    if ($cards = $DB->get_records_sql($sql, [$id])) {
        list($in, $params) = $DB->get_in_or_equal(array_keys($cards));
        $DB->delete_records_select('leitbox_progress', "cardid $in", $params);
    }
    
    $DB->delete_records('leitbox_cards', ['leitboxid' => $id]);
    $DB->delete_records('leitbox', ['id' => $id]);
    return true;
}

/**
 * Returns features supported by the module.
 *
 * @param string $feature
 * @return mixed
 */
function leitbox_supports($feature) {
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
 * Obtains the custom completion rules for this module.
 *
 * @return array Array of strings
 */
function leitbox_get_custom_completion_rules() {
    return ['completion_min_cards', 'completion_min_mastered', 'completion_all_mastered'];
}

/**
 * Returns the custom completion rules for this module.
 *
 * @param stdClass $course
 * @param cm_info $cm
 * @return array
 */
function leitbox_get_completion_active_rule_descriptions($course, $cm) {
    $rules = [];
    if (!empty($cm->customdata['customcompletionrules']['completion_min_cards'])) {
        $rules[] = get_string('completion_min_cards_desc', 'mod_leitbox') . ' ' . $cm->customdata['customcompletionrules']['completion_min_cards'];
    }
    if (!empty($cm->customdata['customcompletionrules']['completion_min_mastered'])) {
        $rules[] = get_string('completion_min_mastered_desc', 'mod_leitbox') . ' ' . $cm->customdata['customcompletionrules']['completion_min_mastered'];
    }

    if (!empty($cm->customdata['customcompletionrules']['completion_all_mastered'])) {
        $rules[] = get_string('completion_all_mastered', 'mod_leitbox');
    }
    return $rules;
}

/**
 * Obtains the automatic completion state for this module based on any custom
 * conditions in the mod_leitbox record.
 *
 * @param stdClass $course Course
 * @param cm_info $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function leitbox_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    $leitbox = $DB->get_record('leitbox', ['id' => $cm->instance], '*', MUST_EXIST);
    
    // Check if the conditions are set
    if (!$leitbox->completion_min_cards && !$leitbox->completion_min_mastered && empty($leitbox->completion_all_mastered)) {
        return $type; // Rules not enabled
    }

    // Initialize flags for each possible condition
    $req_min_cards = false;
    $met_min_cards = true;
    
    $req_min_mastered = false;
    $met_min_mastered = true;

    $req_all_mastered = false;
    $met_all_mastered = true;

    // 1. Min Cards Condition
    if ($leitbox->completion_min_cards) {
        $req_min_cards = true;
        $sql = "SELECT COUNT(DISTINCT cardid) AS total_reviews
                  FROM {leitbox_progress}
                 WHERE userid = :userid AND cardid IN (
                     SELECT id FROM {leitbox_cards} WHERE leitboxid = :instanceid
                 )";
        $total_reviews = $DB->get_field_sql($sql, ['userid' => $userid, 'instanceid' => $leitbox->id]);
        if ($total_reviews < $leitbox->completion_min_cards) {
            $met_min_cards = false;
        }
    }

    // 2. Min Mastered Condition
    if ($leitbox->completion_min_mastered) {
        $req_min_mastered = true;
        $sql_mastered = "SELECT COUNT(*)
                           FROM {leitbox_progress}
                          WHERE userid = :userid 
                            AND box_number = 5
                            AND cardid IN (
                                SELECT id FROM {leitbox_cards} WHERE leitboxid = :instanceid
                            )";
        $total_mastered = $DB->get_field_sql($sql_mastered, ['userid' => $userid, 'instanceid' => $leitbox->id]);
        if ($total_mastered < $leitbox->completion_min_mastered) {
            $met_min_mastered = false;
        }
    }

    // 3. All Mastered Condition
    if (!empty($leitbox->completion_all_mastered)) {
        $req_all_mastered = true;
        $total_cards = $DB->count_records('leitbox_cards', ['leitboxid' => $leitbox->id]);
        $sql_mastered = "SELECT COUNT(p.id) 
                           FROM {leitbox_progress} p 
                           JOIN {leitbox_cards} c ON p.cardid = c.id 
                          WHERE c.leitboxid = :instanceid 
                            AND p.userid = :userid 
                            AND p.box_number = 5";
        $mastered_cards = $DB->count_records_sql($sql_mastered, ['instanceid' => $leitbox->id, 'userid' => $userid]);
        if ($total_cards == 0 || $total_cards != $mastered_cards) {
            $met_all_mastered = false;
        }
    }

    // A condition is only truly completed if ALL required rules are successfully met
    // If a rule is NOT required, it technically "meets" the criteria (remains true)
    $completed = $met_min_cards && $met_min_mastered && $met_all_mastered;

    return $completed;
}

/**
 * Extends the settings navigation for the leitbox module.
 *
 * @param settings_navigation $settings
 * @param navigation_node $leitboxnode
 */
function leitbox_extend_settings_navigation(settings_navigation $settings, navigation_node $leitboxnode) {
    global $PAGE;

    if (has_capability('moodle/course:manageactivities', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/leitbox/manage.php', ['id' => $PAGE->cm->id]);
        $leitboxnode->add(
            get_string('managecards', 'mod_leitbox'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'managecards',
            new pix_icon('i/settings', '')
        );
    }
}

