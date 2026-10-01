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
 * Privacy provider tests for mod_leitbox.
 *
 * @package   mod_leitbox
 * @category  test
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_leitbox\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;

/**
 * Privacy provider tests for mod_leitbox.
 *
 * Every deletion test uses two LeitBox activities and two learners, and
 * checks that only the requested rows disappear. The delete queries must
 * also run on MySQL, which rejects a DELETE that selects from its own table
 * (error 1093), so these tests are part of the MySQL CI job.
 *
 * @covers \mod_leitbox\privacy\provider
 */
final class provider_test extends provider_testcase {
    /** @var \stdClass First LeitBox instance. */
    private $leitbox1;

    /** @var \stdClass Second LeitBox instance (must stay untouched). */
    private $leitbox2;

    /** @var \context_module Context of the first LeitBox. */
    private $context1;

    /** @var \context_module Context of the second LeitBox. */
    private $context2;

    /** @var \stdClass First learner. */
    private $user1;

    /** @var \stdClass Second learner. */
    private $user2;

    /**
     * Creates two activities with two cards each, and progress for two learners on every card.
     */
    public function setUp(): void {
        global $DB;
        parent::setUp();
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $this->user1 = $generator->create_user();
        $this->user2 = $generator->create_user();
        $generator->enrol_user($this->user1->id, $course->id, 'student');
        $generator->enrol_user($this->user2->id, $course->id, 'student');

        $this->leitbox1 = $generator->create_module('leitbox', ['course' => $course->id]);
        $this->leitbox2 = $generator->create_module('leitbox', ['course' => $course->id]);
        $this->context1 = \context_module::instance($this->leitbox1->cmid);
        $this->context2 = \context_module::instance($this->leitbox2->cmid);

        foreach ([$this->leitbox1, $this->leitbox2] as $leitbox) {
            $cardids = $DB->get_fieldset_select('leitbox_cards', 'id', 'leitboxid = ?', [$leitbox->id]);
            foreach (array_slice($cardids, 0, 2) as $cardid) {
                foreach ([$this->user1, $this->user2] as $user) {
                    $DB->insert_record('leitbox_progress', (object)[
                        'userid' => $user->id,
                        'cardid' => $cardid,
                        'box_number' => 2,
                        'count_correct' => 3,
                        'count_wrong' => 1,
                        'last_reviewed' => 1700000000,
                    ]);
                }
            }
        }
    }

    /**
     * Counts progress rows of one user in one activity.
     *
     * @param int $leitboxid The LeitBox instance id.
     * @param int $userid The user id.
     * @return int Number of progress rows.
     */
    private function count_progress(int $leitboxid, int $userid): int {
        global $DB;
        return $DB->count_records_sql(
            "SELECT COUNT(p.id)
               FROM {leitbox_progress} p
               JOIN {leitbox_cards} c ON c.id = p.cardid
              WHERE c.leitboxid = ? AND p.userid = ?",
            [$leitboxid, $userid]
        );
    }

    /**
     * The metadata lists the progress table.
     */
    public function test_get_metadata(): void {
        $collection = provider::get_metadata(new collection('mod_leitbox'));
        $items = $collection->get_collection();
        $this->assertCount(1, $items);
        $this->assertEquals('leitbox_progress', $items[0]->get_name());
    }

    /**
     * A learner with progress in both activities has both contexts.
     */
    public function test_get_contexts_for_userid(): void {
        $contextids = provider::get_contexts_for_userid($this->user1->id)->get_contextids();
        sort($contextids);
        $expected = [$this->context1->id, $this->context2->id];
        sort($expected);
        $this->assertEquals($expected, array_map('intval', $contextids));

        $nobody = $this->getDataGenerator()->create_user();
        $this->assertCount(0, provider::get_contexts_for_userid($nobody->id));
    }

    /**
     * Both learners are found in an activity context.
     */
    public function test_get_users_in_context(): void {
        $userlist = new userlist($this->context1, 'mod_leitbox');
        provider::get_users_in_context($userlist);
        $userids = $userlist->get_userids();
        sort($userids);
        $expected = [$this->user1->id, $this->user2->id];
        sort($expected);
        $this->assertEquals($expected, array_map('intval', $userids));
    }

    /**
     * The export contains the learner's progress for the approved context.
     */
    public function test_export_user_data(): void {
        $contextlist = new approved_contextlist($this->user1, 'mod_leitbox', [$this->context1->id]);
        provider::export_user_data($contextlist);

        $writer = writer::with_context($this->context1);
        $this->assertTrue($writer->has_any_data());
        $data = $writer->get_data([get_string('pluginname', 'mod_leitbox'), get_string('cards', 'mod_leitbox')]);
        $this->assertCount(2, $data->progress);
        $this->assertEquals(2, $data->progress[0]->box_number);

        $this->assertFalse(writer::with_context($this->context2)->has_any_data());
    }

    /**
     * Deleting all users in one context leaves the other activity untouched.
     */
    public function test_delete_data_for_all_users_in_context(): void {
        provider::delete_data_for_all_users_in_context($this->context1);

        $this->assertEquals(0, $this->count_progress($this->leitbox1->id, $this->user1->id));
        $this->assertEquals(0, $this->count_progress($this->leitbox1->id, $this->user2->id));
        $this->assertEquals(2, $this->count_progress($this->leitbox2->id, $this->user1->id));
        $this->assertEquals(2, $this->count_progress($this->leitbox2->id, $this->user2->id));
    }

    /**
     * Deleting one user in one context leaves every other row untouched.
     */
    public function test_delete_data_for_user(): void {
        $contextlist = new approved_contextlist($this->user1, 'mod_leitbox', [$this->context1->id]);
        provider::delete_data_for_user($contextlist);

        $this->assertEquals(0, $this->count_progress($this->leitbox1->id, $this->user1->id));
        $this->assertEquals(2, $this->count_progress($this->leitbox1->id, $this->user2->id));
        $this->assertEquals(2, $this->count_progress($this->leitbox2->id, $this->user1->id));
        $this->assertEquals(2, $this->count_progress($this->leitbox2->id, $this->user2->id));
    }

    /**
     * Deleting a list of users in one context leaves every other row untouched.
     */
    public function test_delete_data_for_users(): void {
        $userlist = new approved_userlist($this->context1, 'mod_leitbox', [$this->user2->id]);
        provider::delete_data_for_users($userlist);

        $this->assertEquals(2, $this->count_progress($this->leitbox1->id, $this->user1->id));
        $this->assertEquals(0, $this->count_progress($this->leitbox1->id, $this->user2->id));
        $this->assertEquals(2, $this->count_progress($this->leitbox2->id, $this->user1->id));
        $this->assertEquals(2, $this->count_progress($this->leitbox2->id, $this->user2->id));
    }
}
