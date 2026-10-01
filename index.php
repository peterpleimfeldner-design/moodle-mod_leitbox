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
 * List of all leitbox instances in a course.
 *
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT); // Course ID.

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

require_course_login($course);
$coursecontext = context_course::instance($course->id);

$event = \mod_leitbox\event\course_module_instance_list_viewed::create(['context' => $coursecontext]);
$event->add_record_snapshot('course', $course);
$event->trigger();

$PAGE->set_url('/mod/leitbox/index.php', ['id' => $id]);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(format_string($course->fullname, true, ['context' => $coursecontext]));
$PAGE->set_heading(format_string($course->fullname, true, ['context' => $coursecontext]));
$PAGE->navbar->add(get_string('modulenameplural', 'mod_leitbox'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_leitbox'));

if (!$leitboxes = get_all_instances_in_course('leitbox', $course)) {
    notice(
        get_string('thereareno', 'moodle', get_string('modulenameplural', 'mod_leitbox')),
        new moodle_url('/course/view.php', ['id' => $course->id])
    );
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

$table->head  = [
    get_string('section', 'moodle'),
    get_string('name'),
];
$table->align = ['center', 'left'];

foreach ($leitboxes as $leitbox) {
    $link = html_writer::link(
        new moodle_url('/mod/leitbox/view.php', ['id' => $leitbox->coursemodule]),
        format_string($leitbox->name, true, ['context' => context_module::instance($leitbox->coursemodule)])
    );
    $table->data[] = [
        get_section_name($course, $leitbox->section),
        $link,
    ];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
