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
require_once('../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT); // Course module ID
$cm = get_coursemodule_from_id('recall', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$recall = $DB->get_record('recall', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Ensure the user has the required capability.
require_capability('mod/recall:view', $context);

// Generate modern page layout.
$PAGE->set_url('/mod/recall/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($recall->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Include standard Moodle JS for ajax if needed.
$PAGE->requires->js_call_amd('core/ajax', 'init');

// Export strings for Vue frontend
$vuestrings = [
    'dashboardtitle', 'dashboardsbtitle', 'howitworks', 'cards', 'systemtitle', 
    'systemintro', 'known_btn', 'known_desc', 'again_btn', 'again_desc', 
    'hard_btn', 'hard_desc', 'systemtip', 'gotit', 'showhint', 'hint',
    'taptoflip', 'action_back', 'action_stay', 'action_next', 'backtodashboard', 
    'cardxofy_x', 'cardxofy_y', 'loadingcards', 'sessiondone', 'sessiondonedesc', 
    'completed', 'error_loading_cards', 'box0', 'box1', 'box2', 'box3', 'box4', 'box5'
];
$PAGE->requires->strings_for_js($vuestrings, 'mod_recall');

echo $OUTPUT->header();

// Display intro if present.
if (!empty(trim($recall->intro))) {
    echo $OUTPUT->box(format_module_intro('recall', $recall, $cm->id), 'generalbox mod_introbox', 'intro');
}

// Pass parameters to the Vue app via a data attribute.
$appdata = [
    'wwwroot'    => $CFG->wwwroot,
    'sesskey'    => sesskey(),
    'instanceid' => (int)$recall->id,
    'cmid'       => (int)$cm->id,
];

// Mount point for Vue
echo \html_writer::tag('div', '', [
    'id' => 'v-app-mod-recall',
    'data-config' => json_encode($appdata)
]);

// Include Vue build scripts manually to prevent Moodle header/require_js module errors
if (file_exists(__DIR__ . '/dist/assets/index.js')) {
    $jsurl = new \moodle_url('/mod/recall/dist/assets/index.js');
    echo '<script type="module" crossorigin src="' . $jsurl->out() . '"></script>';
} else {
    echo \html_writer::tag('p', 'Warning: Vue frontend bundle not found. Please run npm build.', ['class' => 'alert alert-warning mt-3']);
}

if (file_exists(__DIR__ . '/dist/assets/index.css')) {
    $cssurl = new \moodle_url('/mod/recall/dist/assets/index.css');
    echo '<link rel="stylesheet" href="' . $cssurl->out() . '">';
}

echo $OUTPUT->footer();
