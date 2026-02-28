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
 * Library unit tests for mod_leitbox.
 *
 * @package   mod_leitbox
 * @category  test
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/leitbox/lib.php');

/**
 * Library unit tests for mod_leitbox.
 */
class mod_leitbox_lib_testcase extends advanced_testcase {

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
        $leitbox = new \stdClass();
        $leitbox->course = $course->id;
        $leitbox->name = 'LeitBox Test Activity';
        $leitbox->intro = 'Intro text';
        $leitbox->introformat = FORMAT_HTML;
        $leitbox->cardorder = 0;
        
        $module = $this->getDataGenerator()->create_module('leitbox', (array)$leitbox);
        $this->assertNotEmpty($module->id);

        // Verify demo cards were created by our custom add_instance logic.
        $cardcount = $DB->count_records('leitbox_cards', ['leitboxid' => $module->id]);
        $this->assertEquals(5, $cardcount); // add_instance inserts 5 demo cards

        // 2. Test Update
        $module->name = 'Updated LeitBox Name';
        $module->instance = $module->id;
        $result = leitbox_update_instance($module);
        $this->assertTrue($result);

        $updated = $DB->get_record('leitbox', ['id' => $module->id]);
        $this->assertEquals('Updated LeitBox Name', $updated->name);

        // 3. Test Delete
        $result = leitbox_delete_instance($module->id);
        $this->assertTrue($result);

        $deleted = $DB->get_record('leitbox', ['id' => $module->id]);
        $this->assertFalse($deleted);
        
        // Cards should also be deleted
        $cardcount_after = $DB->count_records('leitbox_cards', ['leitboxid' => $module->id]);
        $this->assertEquals(0, $cardcount_after);
    }
}
