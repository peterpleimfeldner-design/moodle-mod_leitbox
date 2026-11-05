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
 * Upgrade check, step 1: creates realistic LeitBox data with the INSTALLED (previous) release.
 *
 * Creates a course "LeitBox upgrade check" with a teacher, four students and one activity per
 * card order and completion rule (minimum cards, minimum mastered, all mastered with "view",
 * a combination, manual completion). Next to the five demo cards each activity gets own cards
 * with HTML, hints, categories and special characters. The students answer cards through the
 * web service function \mod_leitbox\external::submit_answer() as themselves, so the progress
 * rows (box, correct and wrong counts, last review) and the completion states are calculated
 * exactly as in the learning view. One completion state is set by a teacher (override).
 * Only uses functions that exist since LeitBox 1.6.1, so it runs against the previous release.
 * Does nothing if the course already exists.
 *
 * Usage: php seed.php /path/to/moodle/config.php
 *
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require($argv[1] ?? die("Usage: php seed.php /path/to/moodle/config.php\n"));
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/testing/generator/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

global $CFG, $DB;
// Test data only: no welcome or notification mails to the generated users.
$CFG->noemailever = true;
\core\session\manager::set_user(get_admin());

if ($DB->record_exists('course', ['shortname' => 'LEITBOXUPG'])) {
    echo "Course LEITBOXUPG exists already, nothing to do.\n";
    exit(0);
}

/** Rating "Hard" (red): the card moves back one box, never below box 1. */
const UPGRADECHECK_HARD = 0;
/** Rating "Again" (yellow): the card stays in its box. */
const UPGRADECHECK_AGAIN = 1;
/** Rating "Got it" (green): the card moves forward one box. */
const UPGRADECHECK_GOTIT = 2;

/**
 * Answers a card as the given user through the web service function, like the learning view does.
 *
 * @param stdClass $user The learner
 * @param int $cardid Card id
 * @param int[] $ratings Ratings in the order they are given
 */
function upgradecheck_answer(stdClass $user, int $cardid, array $ratings): void {
    \core\session\manager::set_user($user);
    foreach ($ratings as $rating) {
        // A fresh page per call, as every web service request gets one.
        $GLOBALS['PAGE'] = new moodle_page();
        \mod_leitbox\external::submit_answer($cardid, $rating);
    }
    \core\session\manager::set_user(get_admin());
}

$gen = new testing_data_generator();
$course = $gen->create_course([
    'fullname' => 'LeitBox upgrade check',
    'shortname' => 'LEITBOXUPG',
    'enablecompletion' => 1,
]);
$teacher = $gen->create_user(['username' => 'upg_lb_teacher', 'firstname' => 'Tina', 'lastname' => 'Teacher']);
$gen->enrol_user($teacher->id, $course->id, 'editingteacher');
$students = [];
foreach (['Ana', 'Ben', 'Cem', 'Dana'] as $i => $name) {
    $students[$i] = $gen->create_user([
        'username' => 'upg_lb_student' . ($i + 1),
        'firstname' => $name,
        'lastname' => 'Student',
    ]);
    $gen->enrol_user($students[$i]->id, $course->id, 'student');
}
[$ana, $ben, $cem, $dana] = $students;

$auto = ['completion' => COMPLETION_TRACKING_AUTOMATIC];
$activities = [
    'mincards' => $auto + ['name' => 'Random order, rule: 3 cards answered correctly', 'cardorder' => 0,
        'completion_min_cards' => 3],
    'minmastered' => $auto + ['name' => 'Sequential order, rule: 2 cards mastered', 'cardorder' => 1,
        'completion_min_mastered' => 2],
    'allmastered' => $auto + ['name' => 'Rule: all cards mastered and viewed', 'cardorder' => 1,
        'completion_all_mastered' => 1, 'completionview' => 1],
    'combined' => $auto + ['name' => 'Rules: 2 cards correct and 1 mastered', 'cardorder' => 0,
        'completion_min_cards' => 2, 'completion_min_mastered' => 1],
    'manual' => ['name' => 'Manual completion, demo cards only', 'cardorder' => 1,
        'completion' => COMPLETION_TRACKING_MANUAL],
];
$leitboxes = [];
foreach ($activities as $key => $record) {
    $leitboxes[$key] = $gen->create_module('leitbox', $record + [
        'course' => $course->id,
        'intro' => '<p>Upgrade check: <b>' . $key . '</b></p>',
        'introformat' => FORMAT_HTML,
    ]);
}

// Own cards next to the five demo cards. They are inserted directly, so the demo cards stay (adding
// cards on the management page would remove them). Covers HTML, hints, categories, empty optional
// fields and special characters.
$owncards = [
    ['question' => '<p>What is the capital of <b>Austria</b>?</p>', 'answer' => '<p>Vienna</p>',
        'hint' => 'It is on the Danube.', 'category' => 'Geography'],
    ['question' => 'Übung „Ä“ & <i>Umlaute</i>: äöüß?', 'answer' => 'Größe – "quotes" & <tags>',
        'hint' => null, 'category' => 'Übung „Ä“ & <b>'],
    ['question' => '2 + 2 = ?', 'answer' => '4', 'hint' => '', 'category' => null],
    ['question' => '<ul><li>One</li><li>Two</li></ul>Which list is this?', 'answer' => '<ol><li>Unordered</li></ol>',
        'hint' => '<em>Look at the tag.</em>', 'category' => 'HTML'],
    ['question' => '¿Qué hora es?', 'answer' => 'Es la una.', 'hint' => 'Spanish', 'category' => 'Español'],
    ['question' => str_repeat('Long question. ', 30), 'answer' => str_repeat('Long answer. ', 40),
        'hint' => null, 'category' => 'Geography'],
];
$cards = [];
foreach ($leitboxes as $key => $leitbox) {
    $demo = $DB->get_fieldset_select('leitbox_cards', 'id', "leitboxid = ? AND category = 'demo'", [$leitbox->id]);
    sort($demo);
    $cards[$key] = ['demo' => array_map('intval', $demo), 'own' => []];
    $count = ['mincards' => 6, 'minmastered' => 4, 'allmastered' => 1, 'combined' => 3, 'manual' => 0][$key];
    foreach (array_slice($owncards, 0, $count) as $card) {
        $cards[$key]['own'][] = (int)$DB->insert_record('leitbox_cards', (object)($card + ['leitboxid' => $leitbox->id]));
    }
}
rebuild_course_cache($course->id, true);

$mastered = array_fill(0, 5, UPGRADECHECK_GOTIT);

// Ana: completes every rule.
foreach (array_slice($cards['mincards']['own'], 0, 4) as $cardid) {
    upgradecheck_answer($ana, $cardid, [UPGRADECHECK_GOTIT]);
}
upgradecheck_answer($ana, $cards['mincards']['own'][4], [UPGRADECHECK_HARD]);
upgradecheck_answer($ana, $cards['mincards']['demo'][0], [UPGRADECHECK_AGAIN, UPGRADECHECK_GOTIT]);
foreach (array_slice($cards['minmastered']['own'], 0, 2) as $cardid) {
    upgradecheck_answer($ana, $cardid, $mastered);
}
upgradecheck_answer($ana, $cards['minmastered']['own'][2], [UPGRADECHECK_GOTIT, UPGRADECHECK_GOTIT]);
foreach (array_merge($cards['allmastered']['demo'], $cards['allmastered']['own']) as $cardid) {
    upgradecheck_answer($ana, $cardid, $mastered);
}
upgradecheck_answer($ana, $cards['combined']['own'][0], $mastered);
upgradecheck_answer($ana, $cards['combined']['own'][1], [UPGRADECHECK_GOTIT]);
upgradecheck_answer($ana, $cards['manual']['demo'][0], [UPGRADECHECK_GOTIT]);

// Ben: partly done, with wrong answers in between.
upgradecheck_answer($ben, $cards['mincards']['own'][0], [UPGRADECHECK_GOTIT, UPGRADECHECK_GOTIT, UPGRADECHECK_HARD]);
upgradecheck_answer($ben, $cards['mincards']['own'][1], [UPGRADECHECK_AGAIN]);
upgradecheck_answer($ben, $cards['mincards']['own'][2], [UPGRADECHECK_HARD, UPGRADECHECK_HARD, UPGRADECHECK_GOTIT]);
upgradecheck_answer($ben, $cards['minmastered']['own'][0], $mastered);
upgradecheck_answer($ben, $cards['minmastered']['own'][1], [UPGRADECHECK_GOTIT, UPGRADECHECK_GOTIT, UPGRADECHECK_GOTIT]);
upgradecheck_answer($ben, $cards['allmastered']['own'][0], $mastered);
upgradecheck_answer($ben, $cards['combined']['demo'][2], [UPGRADECHECK_GOTIT, UPGRADECHECK_HARD]);

// Cem: only wrong or uncertain answers.
foreach ($cards['mincards']['own'] as $cardid) {
    upgradecheck_answer($cem, $cardid, [UPGRADECHECK_HARD]);
}
foreach ($cards['minmastered']['demo'] as $cardid) {
    upgradecheck_answer($cem, $cardid, [UPGRADECHECK_AGAIN]);
}
upgradecheck_answer($cem, $cards['manual']['demo'][4], [UPGRADECHECK_HARD, UPGRADECHECK_AGAIN]);

// Dana answers nothing.

// Different review times (the web service always stores "now").
$DB->set_field('leitbox_progress', 'last_reviewed', strtotime('2026-01-15 10:00:00'), ['userid' => $cem->id]);
$DB->set_field('leitbox_progress', 'last_reviewed', strtotime('2026-09-30 23:59:59'), [
    'userid' => $ben->id,
    'cardid' => $cards['mincards']['own'][0],
]);

// Views and manual completion: Ana and Dana viewed "allmastered", Ana completed "manual" herself,
// the teacher marked Ben complete in "manual" and Dana complete in "mincards" (overrides).
$completion = new completion_info($course);
$modinfo = get_fast_modinfo($course);
$cmallmastered = $modinfo->get_cm($leitboxes['allmastered']->cmid);
$cmmanual = $modinfo->get_cm($leitboxes['manual']->cmid);
foreach ([$ana, $dana] as $student) {
    $completion->set_module_viewed($cmallmastered, $student->id);
}
\core\session\manager::set_user($ana);
$completion->update_state($cmmanual, COMPLETION_COMPLETE, $ana->id);
\core\session\manager::set_user($teacher);
$completion->update_state($cmmanual, COMPLETION_COMPLETE, $ben->id, true);
$completion->update_state($modinfo->get_cm($leitboxes['mincards']->cmid), COMPLETION_COMPLETE, $dana->id, true);
\core\session\manager::set_user(get_admin());

[$insql, $inparams] = $DB->get_in_or_equal(array_column($leitboxes, 'id'));
$progress = $DB->count_records_select(
    'leitbox_progress',
    "cardid IN (SELECT id FROM {leitbox_cards} WHERE leitboxid $insql)",
    $inparams
);
echo "Created course LEITBOXUPG (id {$course->id}) with " . count($leitboxes) . " LeitBox activities and "
    . "{$progress} progress rows.\n";
