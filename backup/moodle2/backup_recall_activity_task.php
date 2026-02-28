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

require_once($CFG->dirroot . '/mod/recall/backup/moodle2/backup_recall_stepslib.php');

class backup_recall_activity_task extends backup_activity_task {

    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    protected function define_my_steps() {
        // Add the recall structure step.
        $this->add_step(new backup_recall_activity_structure_step('recall_structure', 'recall.xml'));
    }

    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of recall.
        $search = "/(" . $base . "\/mod\/recall\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@RECALLINDEX*$2@$', $content);

        // Link to recall view by moduleid.
        $search = "/(" . $base . "\/mod\/recall\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@RECALLVIEWBYID*$2@$', $content);

        return $content;
    }
}
