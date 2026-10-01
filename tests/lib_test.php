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

namespace mod_leitbox;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/leitbox/lib.php');

/**
 * Library unit tests for mod_leitbox.
 */
final class lib_test extends \advanced_testcase {
    /**
     * Set up for every test.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Test adding, updating, and deleting an instance.
     *
     * @covers ::leitbox_add_instance
     * @covers ::leitbox_update_instance
     * @covers ::leitbox_delete_instance
     */
    public function test_instance_lifecycle(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();

        // 1. Test Add.
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
        $this->assertEquals(5, $cardcount); // Add_instance inserts 5 demo cards.

        // 2. Test Update.
        $module->name = 'Updated LeitBox Name';
        $module->instance = $module->id;
        $result = leitbox_update_instance($module);
        $this->assertTrue($result);

        $updated = $DB->get_record('leitbox', ['id' => $module->id]);
        $this->assertEquals('Updated LeitBox Name', $updated->name);

        // 3. Test Delete.
        $result = leitbox_delete_instance($module->id);
        $this->assertTrue($result);

        $deleted = $DB->get_record('leitbox', ['id' => $module->id]);
        $this->assertFalse($deleted);

        // Cards should also be deleted.
        $cardcountafter = $DB->count_records('leitbox_cards', ['leitboxid' => $module->id]);
        $this->assertEquals(0, $cardcountafter);
    }

    /**
     * Creates a LeitBox with progress of one learner on each of its cards.
     *
     * @param \stdClass $course The course.
     * @param \stdClass $user The learner.
     * @return \stdClass The LeitBox instance.
     */
    private function create_leitbox_with_progress(\stdClass $course, \stdClass $user): \stdClass {
        global $DB;
        $leitbox = $this->getDataGenerator()->create_module('leitbox', ['course' => $course->id]);
        foreach ($DB->get_fieldset_select('leitbox_cards', 'id', 'leitboxid = ?', [$leitbox->id]) as $cardid) {
            $DB->insert_record('leitbox_progress', (object)[
                'userid' => $user->id,
                'cardid' => $cardid,
                'box_number' => 1,
                'count_correct' => 1,
                'count_wrong' => 0,
                'last_reviewed' => time(),
            ]);
        }
        return $leitbox;
    }

    /**
     * Counts the progress rows that belong to one LeitBox.
     *
     * @param int $leitboxid The LeitBox instance id.
     * @return int Number of progress rows.
     */
    private function count_progress(int $leitboxid): int {
        global $DB;
        return $DB->count_records_sql(
            "SELECT COUNT(p.id)
               FROM {leitbox_progress} p
               JOIN {leitbox_cards} c ON c.id = p.cardid
              WHERE c.leitboxid = ?",
            [$leitboxid]
        );
    }

    /**
     * Course reset removes progress in that course only and keeps all cards.
     *
     * Runs on every CI database, including MySQL, which rejects a DELETE that
     * selects from its own table.
     *
     * @covers ::leitbox_reset_userdata
     */
    public function test_reset_userdata(): void {
        global $DB;
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();
        $leitbox1 = $this->create_leitbox_with_progress($course1, $user);
        $leitbox2 = $this->create_leitbox_with_progress($course2, $user);
        $this->assertEquals(5, $this->count_progress($leitbox1->id));

        // Option not ticked: nothing happens.
        $status = leitbox_reset_userdata((object)['courseid' => $course1->id]);
        $this->assertSame([], $status);
        $this->assertEquals(5, $this->count_progress($leitbox1->id));

        $status = leitbox_reset_userdata((object)['courseid' => $course1->id, 'reset_leitbox_progress' => 1]);
        $this->assertCount(1, $status);
        $this->assertFalse($status[0]['error']);
        $this->assertEquals(0, $this->count_progress($leitbox1->id));
        $this->assertEquals(5, $this->count_progress($leitbox2->id));
        $this->assertEquals(5, $DB->count_records('leitbox_cards', ['leitboxid' => $leitbox1->id]));
    }

    /**
     * Card deletion only touches cards of the given activity.
     *
     * A card id from another activity (for example another course) must
     * neither delete that card nor any learner progress for it.
     *
     * @covers ::leitbox_delete_cards
     */
    public function test_delete_cards_only_in_own_activity(): void {
        global $DB;
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $own = $this->create_leitbox_with_progress($generator->create_course(), $user);
        $foreign = $this->create_leitbox_with_progress($generator->create_course(), $user);
        $owncards = $DB->get_fieldset_select('leitbox_cards', 'id', 'leitboxid = ?', [$own->id]);
        $foreigncards = $DB->get_fieldset_select('leitbox_cards', 'id', 'leitboxid = ?', [$foreign->id]);

        // A foreign card id is ignored completely.
        $this->assertEquals(0, leitbox_delete_cards($own->id, [$foreigncards[0]]));
        $this->assertEquals(5, $this->count_progress($foreign->id));
        $this->assertTrue($DB->record_exists('leitbox_cards', ['id' => $foreigncards[0]]));

        // A mixed list deletes only the own card and its progress.
        $this->assertEquals(1, leitbox_delete_cards($own->id, [$owncards[0], $foreigncards[1]]));
        $this->assertFalse($DB->record_exists('leitbox_cards', ['id' => $owncards[0]]));
        $this->assertFalse($DB->record_exists('leitbox_progress', ['cardid' => $owncards[0]]));
        $this->assertEquals(4, $this->count_progress($own->id));
        $this->assertEquals(5, $this->count_progress($foreign->id));

        // An empty list is a no-op.
        $this->assertEquals(0, leitbox_delete_cards($own->id, []));
    }

    /**
     * Only editing teachers and managers may manage cards by default.
     *
     * @coversNothing
     */
    public function test_managecards_capability(): void {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $leitbox = $generator->create_module('leitbox', ['course' => $course->id]);
        $context = \context_module::instance($leitbox->cmid);

        $expected = ['editingteacher' => true, 'teacher' => false, 'student' => false];
        foreach ($expected as $role => $allowed) {
            $user = $generator->create_and_enrol($course, $role);
            $this->assertSame($allowed, has_capability('mod/leitbox:managecards', $context, $user), $role);
        }
    }
}
