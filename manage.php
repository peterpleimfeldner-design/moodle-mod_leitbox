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
require_once(__DIR__ . '/classes/import.php');

$id = required_param('id', PARAM_INT); // Course Module ID
$action = optional_param('action', '', PARAM_ALPHA);
$cardid = optional_param('cardid', 0, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'recall');
$recall = $DB->get_record('recall', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('moodle/course:manageactivities', $context);

$PAGE->set_url('/mod/recall/manage.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($recall->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

/**
 * Automatically cleans up the 5 tutorial demo cards when the teacher adds their first custom card.
 */
function mod_recall_auto_delete_demos($recallid) {
    global $DB;
    $cards = $DB->get_records('recall_cards', ['recallid' => $recallid], 'id ASC');
    if (count($cards) === 5) {
        $first_card = reset($cards);
        if (strpos($first_card->question, 'Willkommen bei Recall') !== false || strpos($first_card->question, 'Welcome to Recall') !== false) {
            list($in, $params) = $DB->get_in_or_equal(array_keys($cards));
            if (!empty($params)) {
                 $DB->delete_records_select('recall_progress', "cardid $in", $params);
            }
            $DB->delete_records('recall_cards', ['recallid' => $recallid]);
        }
    }
}

// Process actions
if ($action === 'delete' && $cardid && confirm_sesskey()) {
    $DB->delete_records('recall_progress', ['cardid' => $cardid]);
    $DB->delete_records('recall_cards', ['id' => $cardid, 'recallid' => $recall->id]);
    redirect(new moodle_url('/mod/recall/manage.php', ['id' => $cm->id]), get_string('carddeleted', 'mod_recall'));
}

if ($action === 'bulkdelete' && data_submitted() && confirm_sesskey()) {
    $cardids = optional_param_array('cardids', [], PARAM_INT);
    $deleted_count = 0;
    
    if (!empty($cardids)) {
        list($in, $params) = $DB->get_in_or_equal($cardids);
        
        // Double check they belong to this recall instance
        $params[] = $recall->id;
        $valid_cards = $DB->get_fieldset_sql("SELECT id FROM {recall_cards} WHERE id $in AND recallid = ?", $params);
        
        if (!empty($valid_cards)) {
            $deleted_count = count($valid_cards);
            list($in_valid, $params_valid) = $DB->get_in_or_equal($valid_cards);
            $DB->delete_records_select('recall_progress', "cardid $in_valid", $params_valid);
            $DB->delete_records_select('recall_cards', "id $in_valid", $params_valid);
        }
    }
    
    if ($deleted_count > 0) {
        redirect(new moodle_url('/mod/recall/manage.php', ['id' => $cm->id]), get_string('cardsdeleted', 'mod_recall', $deleted_count));
    } else {
        redirect(new moodle_url('/mod/recall/manage.php', ['id' => $cm->id]));
    }
}

if ($action === 'export' && confirm_sesskey()) {
    $cards = $DB->get_records('recall_cards', ['recallid' => $recall->id], 'id ASC');
    $export_content = "";
    foreach ($cards as $c) {
        $export_content .= "===CARD===\n";
        $export_content .= "Q: " . $c->question . "\n";
        $export_content .= "A: " . $c->answer . "\n";
        if (!empty($c->hint)) {
            $export_content .= "H: " . $c->hint . "\n";
        }
        $export_content .= "\n";
    }
    
    $filename = clean_filename($recall->name) . '_export.txt';
    send_file($export_content, $filename, 0, 0, true, true, 'text/plain');
    die();
}

if ($action === 'add' && data_submitted() && confirm_sesskey()) {
    $current_count = $DB->count_records('recall_cards', ['recallid' => $recall->id]);
    if ($current_count >= 200) {
        redirect(new moodle_url('/mod/recall/manage.php', ['id' => $cm->id]), get_string('error_limit_reached', 'mod_recall'), null, \core\output\notification::NOTIFY_ERROR);
    }

    $q = required_param('question', PARAM_RAW);
    $a = required_param('answer', PARAM_RAW);
    $h = optional_param('hint', '', PARAM_RAW);
    
    if (!empty($q) && !empty($a)) {
        mod_recall_auto_delete_demos($recall->id);
        
        $newcard = new stdClass();
        $newcard->recallid = $recall->id;
        $newcard->question = $q;
        $newcard->answer = $a;
        $newcard->hint = $h;
        $DB->insert_record('recall_cards', $newcard);
        redirect(new moodle_url('/mod/recall/manage.php', ['id' => $cm->id]), get_string('cardadded', 'mod_recall'));
    }
}

if ($action === 'update' && data_submitted() && confirm_sesskey() && $cardid) {
    // Security check: Ensure the card belongs to this recall instance
    if (!$DB->record_exists('recall_cards', ['id' => $cardid, 'recallid' => $recall->id])) {
        print_error('invalidrecord');
    }

    $q = required_param('question', PARAM_RAW);
    $a = required_param('answer', PARAM_RAW);
    $h = optional_param('hint', '', PARAM_RAW);
    
    if (!empty($q) && !empty($a)) {
        $updatecard = new stdClass();
        $updatecard->id = $cardid;
        $updatecard->question = $q;
        $updatecard->answer = $a;
        $updatecard->hint = $h;
        $DB->update_record('recall_cards', $updatecard);
        redirect(new moodle_url('/mod/recall/manage.php', ['id' => $cm->id]), get_string('cardupdated', 'mod_recall'));
    }
}

if ($action === 'import' && data_submitted() && confirm_sesskey()) {
    $importtext = required_param('importdata', PARAM_RAW);
    $parsed_cards = \mod_recall\import_handler::parse_text($importtext);
    
    $current_count = $DB->count_records('recall_cards', ['recallid' => $recall->id]);
    $new_total = $current_count + count($parsed_cards);
    
    if ($new_total > 200) {
        redirect(new moodle_url('/mod/recall/manage.php', ['id' => $cm->id]), get_string('error_limit_exceeded_import', 'mod_recall', max(0, 200 - $current_count)), null, \core\output\notification::NOTIFY_ERROR);
    }
    
    if (!empty($parsed_cards)) {
        mod_recall_auto_delete_demos($recall->id);
    }

    $count = 0;
    foreach ($parsed_cards as $card) {
        $newcard = new stdClass();
        $newcard->recallid = $recall->id;
        $newcard->question = $card['question'];
        $newcard->answer = $card['answer'];
        $newcard->hint = $card['hint'];
        $DB->insert_record('recall_cards', $newcard);
        $count++;
    }
    redirect(new moodle_url('/mod/recall/manage.php', ['id' => $cm->id]), get_string('cardsimported', 'mod_recall', $count));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecards', 'mod_recall'));

// Display didactic limit notice
echo $OUTPUT->notification(get_string('didactic_limit_notice', 'mod_recall'), \core\output\notification::NOTIFY_INFO);

// 1. Link back to activity
echo html_writer::link(new moodle_url('/mod/recall/view.php', ['id' => $cm->id]), get_string('backtoactivity', 'mod_recall'), ['class' => 'btn btn-secondary mb-4']);


// 2. Add or Edit Single Card Form
$editcard = null;
if ($action === 'edit' && $cardid) {
    $editcard = $DB->get_record('recall_cards', ['id' => $cardid, 'recallid' => $recall->id]);
}

echo $OUTPUT->box_start('generalbox boxwidthwide boxaligncenter');
if ($editcard) {
    echo html_writer::tag('h3', get_string('editsinglecard', 'mod_recall'));
} else {
    echo html_writer::tag('h3', get_string('addsinglecard', 'mod_recall'));
}

echo html_writer::start_tag('form', ['action' => 'manage.php', 'method' => 'post', 'class' => 'mform']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => $editcard ? 'update' : 'add']);
if ($editcard) {
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'cardid', 'value' => $editcard->id]);
}
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

$qval = $editcard ? htmlspecialchars($editcard->question) : '';
$aval = $editcard ? htmlspecialchars($editcard->answer) : '';
$hval = $editcard ? htmlspecialchars($editcard->hint) : '';

echo '<div class="mb-3"><label>' . get_string('question', 'mod_recall') . '</label><br>';
echo '<textarea name="question" rows="2" class="form-control w-100" required="required">' . $qval . '</textarea></div>';

echo '<div class="mb-3"><label>' . get_string('answer', 'mod_recall') . '</label><br>';
echo '<textarea name="answer" rows="2" class="form-control w-100" required="required">' . $aval . '</textarea></div>';

echo '<div class="mb-3"><label>' . get_string('hint', 'mod_recall') . ' (' . get_string('optional', 'moodle') . ')</label><br>';
echo '<textarea name="hint" rows="1" class="form-control w-100">' . $hval . '</textarea></div>';

if ($editcard) {
    echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('updatecard', 'mod_recall'), 'class' => 'btn btn-primary']);
    echo html_writer::link(new moodle_url('/mod/recall/manage.php', ['id' => $cm->id]), get_string('cancel', 'mod_recall'), ['class' => 'btn btn-secondary', 'style' => 'margin-left:10px;']);
} else {
    echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('addcard', 'mod_recall'), 'class' => 'btn btn-primary']);
}

echo html_writer::end_tag('form');
echo $OUTPUT->box_end();


// 3. AI Import Form
echo $OUTPUT->box_start('generalbox boxwidthwide boxaligncenter mt-4');
echo html_writer::tag('h3', get_string('bulkimport', 'mod_recall'));
echo html_writer::tag('p', get_string('bulkimportdesc', 'mod_recall'));

// Display AI Prompt template
$prompt_standard = get_string('prompt_template_standard', 'mod_recall');
$prompt_tf       = get_string('prompt_template_tf', 'mod_recall');
$prompt_vocab    = get_string('prompt_template_vocab', 'mod_recall');
$prompt_cloze    = get_string('prompt_template_cloze', 'mod_recall');
$prompt_jeopardy = get_string('prompt_template_jeopardy', 'mod_recall');
$prompt_transfer = get_string('prompt_template_transfer', 'mod_recall');

$prompt_options = [
    'standard' => get_string('prompt_type_standard', 'mod_recall'),
    'tf'       => get_string('prompt_type_tf', 'mod_recall'),
    'vocab'    => get_string('prompt_type_vocab', 'mod_recall'),
    'cloze'    => get_string('prompt_type_cloze', 'mod_recall'),
    'jeopardy' => get_string('prompt_type_jeopardy', 'mod_recall'),
    'transfer' => get_string('prompt_type_transfer', 'mod_recall'),
];

echo html_writer::tag('div', 
    html_writer::tag('strong', get_string('prompt_type_selection', 'mod_recall')) . '<br>' .
    html_writer::select($prompt_options, 'prompt_type_selector', 'standard', false, ['id' => 'prompt_selector', 'class' => 'custom-select form-control mb-3 mt-1 w-100'])
, ['class' => 'mb-2']);

echo html_writer::tag('div', 
    html_writer::tag('strong', get_string('prompt_instruction', 'mod_recall')) . '<br>' .
    html_writer::tag('pre', s($prompt_standard), ['id' => 'prompt_display', 'class' => 'bg-light p-3 border rounded', 'style' => 'white-space: pre-wrap; font-size: 0.9em;'])
, ['class' => 'mb-4']);

echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    var prompts = {
        "standard": ' . json_encode($prompt_standard) . ',
        "tf": ' . json_encode($prompt_tf) . ',
        "vocab": ' . json_encode($prompt_vocab) . ',
        "cloze": ' . json_encode($prompt_cloze) . ',
        "jeopardy": ' . json_encode($prompt_jeopardy) . ',
        "transfer": ' . json_encode($prompt_transfer) . '
    };
    var selector = document.getElementById("prompt_selector");
    if (selector) {
        selector.addEventListener("change", function(e) {
            document.getElementById("prompt_display").innerText = prompts[e.target.value];
        });
    }
});
</script>';

echo html_writer::start_tag('form', ['action' => 'manage.php', 'method' => 'post', 'class' => 'mform']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'import']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

echo '<div class="mb-3">';
echo '<textarea name="importdata" rows="10" class="form-control w-100" placeholder="===CARD==='."\n".'Q: Frage'."\n".'A: Antwort'."\n".'H: Optionaler Hinweis"></textarea>';
echo '</div>';

echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('importcards', 'mod_recall'), 'class' => 'btn btn-success']);
echo html_writer::end_tag('form');
echo $OUTPUT->box_end();


// 4. Existing Cards List
echo html_writer::tag('h3', get_string('existingcards', 'mod_recall'), ['class' => 'mt-5']);

$page = optional_param('page', 0, PARAM_INT);
$perpage = 50;

$totalcards = $DB->count_records('recall_cards', ['recallid' => $recall->id]);
$cards = $DB->get_records('recall_cards', ['recallid' => $recall->id], 'id ASC', '*', $page * $perpage, $perpage);

if ($cards) {
    echo $OUTPUT->paging_bar($totalcards, $page, $perpage, new moodle_url('/mod/recall/manage.php', ['id' => $cm->id]));
    
    // Form for bulk deletion
    echo html_writer::start_tag('form', ['action' => 'manage.php', 'method' => 'post', 'id' => 'bulkdeleteform']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'bulkdelete']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

    $table = new html_table();
    $table->head = [
        '<input type="checkbox" id="selectallcards" title="'.get_string('selectall', 'moodle').'">',
        '#', get_string('question', 'mod_recall'), get_string('answer', 'mod_recall'), get_string('hint', 'mod_recall'), get_string('actions')
    ];
    $table->attributes['class'] = 'generaltable w-100';

    $rownum = ($page * $perpage) + 1;
    foreach ($cards as $c) {
        $checkbox = '<input type="checkbox" name="cardids[]" value="'.$c->id.'" class="cardcheckbox">';
        $editurl = new moodle_url('/mod/recall/manage.php', ['id' => $cm->id, 'action' => 'edit', 'cardid' => $c->id]);
        $editbtn = html_writer::link($editurl, $OUTPUT->pix_icon('t/edit', get_string('edit')), ['class' => 'mr-2', 'style' => 'margin-right:8px;']);

        $delurl = new moodle_url('/mod/recall/manage.php', ['id' => $cm->id, 'action' => 'delete', 'cardid' => $c->id, 'sesskey' => sesskey()]);
        $delbtn = html_writer::link($delurl, $OUTPUT->pix_icon('t/delete', get_string('delete')), ['onclick' => 'return confirm("'.get_string('confirmdeletecard', 'mod_recall').'")']);
        
        $table->data[] = [
            $checkbox,
            $rownum++,
            format_text($c->question),
            format_text($c->answer),
            format_text($c->hint),
            $editbtn . $delbtn
        ];
    }
    echo html_writer::table($table);
    
    echo '<div class="mt-3">';
    echo html_writer::empty_tag('input', [
        'type' => 'submit', 
        'value' => get_string('deleteselected', 'mod_recall'), 
        'class' => 'btn btn-danger',
        'onclick' => 'return confirm("'.get_string('confirmbulkdelete', 'mod_recall').'")'
    ]);
    echo '</div>';
    
    echo html_writer::end_tag('form');
    
    // Quick script to toggle all checkboxes
    echo '<script>
    document.getElementById("selectallcards").addEventListener("change", function(e) {
        var checkboxes = document.querySelectorAll(".cardcheckbox");
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = e.target.checked;
        }
    });
    </script>';
    
    // Bottom paging bar for convenience
    echo $OUTPUT->paging_bar($totalcards, $page, $perpage, new moodle_url('/mod/recall/manage.php', ['id' => $cm->id]));
    
    // Export Button
    $exporturl = new moodle_url('/mod/recall/manage.php', ['id' => $cm->id, 'action' => 'export', 'sesskey' => sesskey()]);
    echo '<div class="mt-5 text-right">';
    echo html_writer::link($exporturl, get_string('exportcards', 'mod_recall'), ['class' => 'btn btn-outline-secondary']);
    echo '</div>';
} else {
    echo html_writer::tag('p', get_string('nocards', 'mod_recall'));
}

echo $OUTPUT->footer();

