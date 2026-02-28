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

require_once($CFG->dirroot . '/mod/recall/backup/moodle2/restore_recall_stepslib.php');

class restore_recall_activity_task extends restore_activity_task {

    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    protected function define_my_steps() {
        // Add the recall structure step.
        $this->add_step(new restore_recall_activity_structure_step('recall_structure', 'recall.xml'));
    }

    static public function define_decode_contents() {
        $contents = [];
        $contents[] = new restore_decode_content('recall', ['intro'], 'recall');
        $contents[] = new restore_decode_content('recall_cards', ['question', 'answer', 'hint'], 'recall_cards');
        return $contents;
    }

    static public function define_decode_rules() {
        $rules = [];
        // New rules for Recall backups
        $rules[] = new restore_decode_rule('RECALLINDEX', '/mod/recall/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('RECALLVIEWBYID', '/mod/recall/view.php?id=$1', 'course_module');
        // Old rules for compatibility with pre-v1.0.0 Smartcards backups
        $rules[] = new restore_decode_rule('SMARTCARDSINDEX', '/mod/recall/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('SMARTCARDSVIEWBYID', '/mod/recall/view.php?id=$1', 'course_module');
        return $rules;
    }

    static public function define_restore_log_rules() {
        $rules = [];
        $rules[] = new restore_log_rule('recall', 'add', 'view.php?id={course_module}', '{recall}');
        $rules[] = new restore_log_rule('recall', 'update', 'view.php?id={course_module}', '{recall}');
        $rules[] = new restore_log_rule('recall', 'view', 'view.php?id={course_module}', '{recall}');
        return $rules;
    }

    static public function define_restore_log_rules_for_course() {
        $rules = [];
        $rules[] = new restore_log_rule('recall', 'view all', 'index.php?id={course}', null);
        return $rules;
    }
}
