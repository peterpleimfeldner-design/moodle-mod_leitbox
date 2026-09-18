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
 * Restore task for mod_leitbox.
 *
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/leitbox/backup/moodle2/restore_leitbox_stepslib.php');

/**
 * Restore task that provides all the settings and steps to perform a
 * restore of a leitbox activity.
 */
class restore_leitbox_activity_task extends restore_activity_task {
    /**
     * Defines particular settings this activity can have.
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Defines particular steps this activity can have.
     */
    protected function define_my_steps() {
        // Add the leitbox structure step.
        $this->add_step(new restore_leitbox_activity_structure_step('leitbox_structure', 'leitbox.xml'));
    }

    /**
     * Defines the contents in the activity that must be processed by the
     * link decoder.
     *
     * @return array of restore_decode_content objects.
     */
    public static function define_decode_contents() {
        $contents = [];
        $contents[] = new restore_decode_content('leitbox', ['intro'], 'leitbox');
        $contents[] = new restore_decode_content('leitbox_cards', ['question', 'answer', 'hint'], 'leitbox_cards');
        return $contents;
    }

    /**
     * Defines the decoding rules for links belonging to this activity.
     *
     * @return array of restore_decode_rule objects.
     */
    public static function define_decode_rules() {
        $rules = [];
        // New rules for LeitBox backups.
        $rules[] = new restore_decode_rule('LEITBOXINDEX', '/mod/leitbox/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('LEITBOXVIEWBYID', '/mod/leitbox/view.php?id=$1', 'course_module');
        // Old rules for compatibility with pre-v1.0.0 Smartcards backups.
        $rules[] = new restore_decode_rule('SMARTCARDSINDEX', '/mod/leitbox/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('SMARTCARDSVIEWBYID', '/mod/leitbox/view.php?id=$1', 'course_module');
        return $rules;
    }

    /**
     * Defines the restore log rules that will be applied by the restore
     * logs processor when restoring leitbox logs.
     *
     * @return array of restore_log_rule objects.
     */
    public static function define_restore_log_rules() {
        $rules = [];
        $rules[] = new restore_log_rule('leitbox', 'add', 'view.php?id={course_module}', '{leitbox}');
        $rules[] = new restore_log_rule('leitbox', 'update', 'view.php?id={course_module}', '{leitbox}');
        $rules[] = new restore_log_rule('leitbox', 'view', 'view.php?id={course_module}', '{leitbox}');
        return $rules;
    }

    /**
     * Defines the restore log rules that will be applied by the restore
     * logs processor when restoring course logs, with "view all" advanced
     * items.
     *
     * @return array of restore_log_rule objects.
     */
    public static function define_restore_log_rules_for_course() {
        $rules = [];
        $rules[] = new restore_log_rule('leitbox', 'view all', 'index.php?id={course}', null);
        return $rules;
    }
}
