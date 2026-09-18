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
 * Restore step for mod_leitbox.
 *
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines the complete restore structure for the leitbox activity.
 */
class restore_leitbox_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines the restore structure of the module.
     *
     * @return array of restore_path_element.
     */
    protected function define_structure() {
        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('leitbox', '/activity/leitbox');
        $paths[] = new restore_path_element('leitbox_card', '/activity/leitbox/cards/card');

        if ($userinfo) {
            $paths[] = new restore_path_element('leitbox_progress', '/activity/leitbox/cards/card/progresses/progress');
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Processes a restored leitbox instance.
     *
     * @param array $data The restored data.
     */
    protected function process_leitbox($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be happening here.
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the leitbox record.
        $newitemid = $DB->insert_record('leitbox', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Processes a restored leitbox card.
     *
     * @param array $data The restored data.
     */
    protected function process_leitbox_card($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->leitboxid = $this->get_new_parentid('leitbox');

        $newitemid = $DB->insert_record('leitbox_cards', $data);
        $this->set_mapping('leitbox_cards', $oldid, $newitemid);
    }

    /**
     * Processes a restored leitbox progress record.
     *
     * @param array $data The restored data.
     */
    protected function process_leitbox_progress($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Ensure user ID mapping is found.
        $data->userid = $this->get_mappingid('user', $data->userid);
        if (!$data->userid) {
            return; // Don't restore if the user isn't found.
        }

        $data->cardid = $this->get_new_parentid('leitbox_cards');

        $newitemid = $DB->insert_record('leitbox_progress', $data);
        $this->set_mapping('leitbox_progress', $oldid, $newitemid);
    }

    /**
     * Runs after the whole restore process has finished.
     */
    protected function after_execute() {
        // Add leitbox related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_leitbox', 'intro', null);
    }
}
