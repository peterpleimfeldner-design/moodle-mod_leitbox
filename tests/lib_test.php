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
 * Library unit tests for mod_recall.
 *
 * @package   mod_recall
 * @category  test
 * @copyright 2026 Recall Author
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/recall/lib.php');

/**
 * Library unit tests for mod_recall.
 */
class mod_recall_lib_testcase extends advanced_testcase {

    public function setUp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Test adding, updating, and deleting an instance.
     */
    public function test_instance_lifecycle() {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        
        // 1. Test Add
        $recall = new \stdClass();
        $recall->course = $course->id;
        $recall->name = 'Recall Test Activity';
        $recall->intro = 'Intro text';
        $recall->introformat = FORMAT_HTML;
        $recall->cardorder = 0;
        
        $module = $this->getDataGenerator()->create_module('recall', (array)$recall);
        $this->assertNotEmpty($module->id);

        // Verify demo cards were created by our custom add_instance logic.
        $cardcount = $DB->count_records('recall_cards', ['recallid' => $module->id]);
        $this->assertEquals(5, $cardcount); // add_instance inserts 5 demo cards

        // 2. Test Update
        $module->name = 'Updated Recall Name';
        $module->instance = $module->id;
        $result = recall_update_instance($module);
        $this->assertTrue($result);

        $updated = $DB->get_record('recall', ['id' => $module->id]);
        $this->assertEquals('Updated Recall Name', $updated->name);

        // 3. Test Delete
        $result = recall_delete_instance($module->id);
        $this->assertTrue($result);

        $deleted = $DB->get_record('recall', ['id' => $module->id]);
        $this->assertFalse($deleted);
        
        // Cards should also be deleted
        $cardcount_after = $DB->count_records('recall_cards', ['recallid' => $module->id]);
        $this->assertEquals(0, $cardcount_after);
    }
}
