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
 * Backup and restore tests for mod_leitbox.
 *
 * @package   mod_leitbox
 * @category  test
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_leitbox;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * Backup and restore tests for mod_leitbox.
 */
final class backup_restore_test extends \advanced_testcase {
    /**
     * A course backup with user data restores the cards and the learners' progress on them.
     *
     * @covers \backup_leitbox_activity_structure_step
     * @covers \restore_leitbox_activity_structure_step
     */
    public function test_backup_and_restore_with_user_data(): void {
        global $CFG, $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $student = $generator->create_and_enrol($course, 'student');
        $leitbox = $generator->create_module('leitbox', ['course' => $course->id]);
        $cards = $DB->get_records('leitbox_cards', ['leitboxid' => $leitbox->id], 'id ASC');
        $this->assertCount(5, $cards);
        // A card that links to its own activity: the link must point to the
        // restored activity afterwards.
        $linkcard = end($cards);
        $link = '<a href="' . $CFG->wwwroot . '/mod/leitbox/view.php?id=' . $leitbox->cmid . '">Link</a>';
        $DB->set_field('leitbox_cards', 'question', $link, ['id' => $linkcard->id]);
        reset($cards);
        foreach (array_slice($cards, 0, 2) as $index => $card) {
            $DB->insert_record('leitbox_progress', (object)[
                'userid' => $student->id,
                'cardid' => $card->id,
                'box_number' => $index + 2,
                'count_correct' => 4,
                'count_wrong' => 1,
                'last_reviewed' => 1700000000,
            ]);
        }

        // Back up the whole course including user data.
        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_value(true);
        $bc->execute_plan();
        $file = $bc->get_results()['backup_destination'];
        $backupid = 'leitbox_backup_restore_test';
        $packer = get_file_packer('application/vnd.moodle.backup');
        $file->extract_to_pathname($packer, $CFG->tempdir . '/backup/' . $backupid);
        $bc->destroy();

        // Restore it into a new course.
        $newcourseid = \restore_dbops::create_new_course('Restored course', 'RESTORED', $course->category);
        $rc = new \restore_controller(
            $backupid,
            $newcourseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id,
            \backup::TARGET_NEW_COURSE
        );
        $rc->get_plan()->get_setting('users')->set_value(true);
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        $newleitbox = $DB->get_record('leitbox', ['course' => $newcourseid], '*', MUST_EXIST);
        $newcards = $DB->get_records('leitbox_cards', ['leitboxid' => $newleitbox->id], 'id ASC');
        $this->assertCount(5, $newcards);

        $progress = $DB->get_records_sql(
            "SELECT p.*, c.question
               FROM {leitbox_progress} p
               JOIN {leitbox_cards} c ON c.id = p.cardid
              WHERE c.leitboxid = ?
           ORDER BY p.box_number",
            [$newleitbox->id]
        );
        $this->assertCount(2, $progress);
        $first = reset($progress);
        $this->assertEquals($student->id, $first->userid);
        $this->assertEquals(2, $first->box_number);
        $this->assertEquals(reset($cards)->question, $first->question);

        // Links in card texts are rewritten to the restored activity.
        $newcm = get_coursemodule_from_instance('leitbox', $newleitbox->id, $newcourseid, false, MUST_EXIST);
        $this->assertNotEquals($leitbox->cmid, $newcm->id);
        $where = 'leitboxid = ? AND ' . $DB->sql_like('question', '?');
        $pattern = '%/mod/leitbox/view.php?id=' . $newcm->id . '"%';
        $this->assertEquals(1, $DB->count_records_select('leitbox_cards', $where, [$newleitbox->id, $pattern]));

        // The original activity keeps its own progress.
        $this->assertEquals(2, $DB->count_records_sql(
            "SELECT COUNT(p.id) FROM {leitbox_progress} p JOIN {leitbox_cards} c ON c.id = p.cardid WHERE c.leitboxid = ?",
            [$leitbox->id]
        ));
    }
}
