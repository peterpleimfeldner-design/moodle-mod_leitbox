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
 * Manage cards page for mod_leitbox.
 *
 * Uses the Moodle Output API (render_from_template), Mustache templates,
 * and an AMD JavaScript module.
 *
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(__DIR__ . '/lib.php');

$id     = required_param('id', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$cardid = optional_param('cardid', 0, PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'leitbox');
$leitbox = $DB->get_record('leitbox', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('moodle/course:manageactivities', $context);

$PAGE->set_url('/mod/leitbox/manage.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($leitbox->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Action processing (data changes only, no HTML output yet).

if ($action === 'delete' && $cardid && confirm_sesskey()) {
    $DB->delete_records('leitbox_progress', ['cardid' => $cardid]);
    $DB->delete_records('leitbox_cards', ['id' => $cardid, 'leitboxid' => $leitbox->id]);
    redirect(
        new moodle_url('/mod/leitbox/manage.php', ['id' => $cm->id]),
        get_string('carddeleted', 'mod_leitbox')
    );
}

if ($action === 'bulkdelete' && data_submitted() && confirm_sesskey()) {
    $cardids      = optional_param_array('cardids', [], PARAM_INT);
    $deletedcount = 0;

    if (!empty($cardids)) {
        [$in, $params] = $DB->get_in_or_equal($cardids);
        $params[]  = $leitbox->id;
        $validcards = $DB->get_fieldset_sql(
            "SELECT id FROM {leitbox_cards} WHERE id $in AND leitboxid = ?",
            $params
        );

        if (!empty($validcards)) {
            $deletedcount = count($validcards);
            [$invalid, $paramsvalid] = $DB->get_in_or_equal($validcards);
            $DB->delete_records_select('leitbox_progress', "cardid $invalid", $paramsvalid);
            $DB->delete_records_select('leitbox_cards', "id $invalid", $paramsvalid);
        }
    }

    if ($deletedcount > 0) {
        redirect(
            new moodle_url('/mod/leitbox/manage.php', ['id' => $cm->id]),
            get_string('cardsdeleted', 'mod_leitbox', $deletedcount)
        );
    } else {
        redirect(new moodle_url('/mod/leitbox/manage.php', ['id' => $cm->id]));
    }
}

if ($action === 'export' && confirm_sesskey()) {
    $cards         = $DB->get_records('leitbox_cards', ['leitboxid' => $leitbox->id], 'id ASC');
    $exportcontent = "";
    // Resolve language-neutral demo card markers (e.g. ##demo_q1##) to plain
    // text, the same way classes/external.php does for the learner-facing
    // view - otherwise exporting before adding any real cards yields the
    // raw, meaningless marker keys instead of the actual demo content.
    $resolvedemo = function ($text) {
        if (preg_match('/^##(demo_[a-z0-9]+)##$/', $text, $m)) {
            return get_string($m[1], 'mod_leitbox');
        }
        return $text;
    };
    foreach ($cards as $c) {
        $exportcontent .= "===CARD===\n";
        $exportcontent .= "Q: " . $resolvedemo($c->question) . "\n";
        $exportcontent .= "A: " . $resolvedemo($c->answer) . "\n";
        if (!empty($c->hint)) {
            $exportcontent .= "H: " . $resolvedemo($c->hint) . "\n";
        }
        $exportcontent .= "\n";
    }
    $filename = clean_filename($leitbox->name) . '_export.txt';
    send_file($exportcontent, $filename, 0, 0, true, true, 'text/plain');
    die();
}

if ($action === 'add' && data_submitted() && confirm_sesskey()) {
    $currentcount = $DB->count_records('leitbox_cards', ['leitboxid' => $leitbox->id]);
    if ($currentcount >= 200) {
        redirect(
            new moodle_url('/mod/leitbox/manage.php', ['id' => $cm->id]),
            get_string('error_limit_reached', 'mod_leitbox'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    $q = required_param('question', PARAM_CLEANHTML);
    $a = required_param('answer', PARAM_CLEANHTML);
    $h = optional_param('hint', '', PARAM_CLEANHTML);

    if (!empty($q) && !empty($a)) {
        leitbox_auto_delete_demos($leitbox->id);
        $newcard            = new stdClass();
        $newcard->leitboxid = $leitbox->id;
        $newcard->question  = $q;
        $newcard->answer    = $a;
        $newcard->hint      = $h;
        $DB->insert_record('leitbox_cards', $newcard);
        redirect(
            new moodle_url('/mod/leitbox/manage.php', ['id' => $cm->id]),
            get_string('cardadded', 'mod_leitbox')
        );
    }
}

if ($action === 'update' && data_submitted() && confirm_sesskey() && $cardid) {
    // Security check: ensure the card belongs to this leitbox instance.
    if (!$DB->record_exists('leitbox_cards', ['id' => $cardid, 'leitboxid' => $leitbox->id])) {
        throw new \moodle_exception('invalidrecord');
    }

    $q = required_param('question', PARAM_CLEANHTML);
    $a = required_param('answer', PARAM_CLEANHTML);
    $h = optional_param('hint', '', PARAM_CLEANHTML);

    if (!empty($q) && !empty($a)) {
        $updatecard           = new stdClass();
        $updatecard->id       = $cardid;
        $updatecard->question = $q;
        $updatecard->answer   = $a;
        $updatecard->hint     = $h;
        $DB->update_record('leitbox_cards', $updatecard);
        redirect(
            new moodle_url('/mod/leitbox/manage.php', ['id' => $cm->id]),
            get_string('cardupdated', 'mod_leitbox')
        );
    }
}

if ($action === 'import' && data_submitted() && confirm_sesskey()) {
    // PARAM_RAW is required here because the bulk-import block uses a custom
    // Q:/A:/H:/===CARD=== text syntax that must keep its exact line breaks to
    // be parsed correctly. The syntax itself carries no HTML. Every field
    // extracted from it is sanitised with PARAM_CLEANHTML below, before it is
    // ever written to the database - the same rule that already applies to
    // the single add/edit card forms above.
    $importtext  = required_param('importdata', PARAM_RAW);
    $parsedcards = \mod_leitbox\import_handler::parse_text($importtext);

    $currentcount = $DB->count_records('leitbox_cards', ['leitboxid' => $leitbox->id]);
    $newtotal     = $currentcount + count($parsedcards);

    if ($newtotal > 200) {
        redirect(
            new moodle_url('/mod/leitbox/manage.php', ['id' => $cm->id]),
            get_string(
                'error_limit_exceeded_import',
                'mod_leitbox',
                max(0, 200 - $currentcount)
            ),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    if (!empty($parsedcards)) {
        leitbox_auto_delete_demos($leitbox->id);
    }

    $count = 0;
    foreach ($parsedcards as $card) {
        $newcard            = new stdClass();
        $newcard->leitboxid = $leitbox->id;
        $newcard->question  = clean_param($card['question'], PARAM_CLEANHTML);
        $newcard->answer    = clean_param($card['answer'], PARAM_CLEANHTML);
        $newcard->hint      = clean_param($card['hint'], PARAM_CLEANHTML);
        $DB->insert_record('leitbox_cards', $newcard);
        $count++;
    }
    redirect(
        new moodle_url('/mod/leitbox/manage.php', ['id' => $cm->id]),
        get_string('cardsimported', 'mod_leitbox', $count)
    );
}

// Build template data.

// Card being edited (if applicable).
$editcard = null;
if ($action === 'edit' && $cardid) {
    $editcard = $DB->get_record(
        'leitbox_cards',
        ['id' => $cardid, 'leitboxid' => $leitbox->id]
    );
}

// AI prompt templates (passed to AMD module for client-side switching).
$prompts = [
    'standard' => get_string('prompt_template_standard', 'mod_leitbox'),
    'tf'       => get_string('prompt_template_tf', 'mod_leitbox'),
    'vocab'    => get_string('prompt_template_vocab', 'mod_leitbox'),
    'cloze'    => get_string('prompt_template_cloze', 'mod_leitbox'),
    'jeopardy' => get_string('prompt_template_jeopardy', 'mod_leitbox'),
    'transfer' => get_string('prompt_template_transfer', 'mod_leitbox'),
];

// Pagination.
$page       = optional_param('page', 0, PARAM_INT);
$perpage    = 50;
$totalcards = $DB->count_records('leitbox_cards', ['leitboxid' => $leitbox->id]);
$cards      = $DB->get_records(
    'leitbox_cards',
    ['leitboxid' => $leitbox->id],
    'id ASC',
    '*',
    $page * $perpage,
    $perpage
);

// Build the cards array for the Mustache template.
$cardrows = [];
$rownum   = ($page * $perpage) + 1;
foreach ($cards as $c) {
    $editurl = (new moodle_url(
        '/mod/leitbox/manage.php',
        ['id' => $cm->id, 'action' => 'edit', 'cardid' => $c->id]
    ))->out(false);
    $delurl  = (new moodle_url(
        '/mod/leitbox/manage.php',
        ['id' => $cm->id, 'action' => 'delete', 'cardid' => $c->id,
        'sesskey' => sesskey()]
    ))->out(false);

    $cardrows[] = [
        'id'            => $c->id,
        'rownum'        => $rownum++,
        'question'      => format_text($c->question, FORMAT_HTML, ['context' => $context, 'para' => false]),
        'answer'        => format_text($c->answer, FORMAT_HTML, ['context' => $context, 'para' => false]),
        'hint'          => format_text($c->hint, FORMAT_HTML, ['context' => $context, 'para' => false]),
        'editurl'       => $editurl,
        'delurl'        => $delurl,
        'editiconhtml'  => $OUTPUT->pix_icon('t/edit', get_string('edit')),
        'deleteiconhtml' => $OUTPUT->pix_icon('t/delete', get_string('delete')),
    ];
}

// Pre-render paging bars (injected via triple-mustache {{{ }}}).
$manageurl      = new moodle_url('/mod/leitbox/manage.php', ['id' => $cm->id]);
$pagingbartop   = '';
$pagingbarbottom = '';
if (!empty($cards)) {
    $pagingbartop    = $OUTPUT->paging_bar($totalcards, $page, $perpage, $manageurl);
    $pagingbarbottom = $pagingbartop;
}

$templatedata = [
    // Navigation.
    'backurl'      => (new moodle_url('/mod/leitbox/view.php', ['id' => $cm->id]))->out(false),
    'managebaseurl' => (new moodle_url('/mod/leitbox/manage.php'))->out(false),
    'cmid'         => $cm->id,
    'sesskey'      => sesskey(),

    // Localised strings.
    'strbacktoactivity'  => get_string('backtoactivity', 'mod_leitbox'),
    'straddsinglecard'   => get_string('addsinglecard', 'mod_leitbox'),
    'streditsinglecard'  => get_string('editsinglecard', 'mod_leitbox'),
    'strquestion'        => get_string('question', 'mod_leitbox'),
    'stranswer'          => get_string('answer', 'mod_leitbox'),
    'strhint'            => get_string('hint', 'mod_leitbox'),
    'stroptional'        => get_string('optional', 'moodle'),
    'straddcard'         => get_string('addcard', 'mod_leitbox'),
    'strupdatecard'      => get_string('updatecard', 'mod_leitbox'),
    'strcancel'          => get_string('cancel', 'mod_leitbox'),
    'strbulkimport'      => get_string('bulkimport', 'mod_leitbox'),
    'strbulkimportdesc'  => get_string('bulkimportdesc', 'mod_leitbox'),
    'strprompttypesel'   => get_string('prompt_type_selection', 'mod_leitbox'),
    'strpromptinstruct'  => get_string('prompt_instruction', 'mod_leitbox'),
    'strimportcards'     => get_string('importcards', 'mod_leitbox'),
    'strimportph'        => get_string('import_placeholder', 'mod_leitbox'),
    'strexistingcards'   => get_string('existingcards', 'mod_leitbox'),
    'strnocards'         => get_string('nocards', 'mod_leitbox'),
    'strquestioncol'     => get_string('question', 'mod_leitbox'),
    'stranswercol'       => get_string('answer', 'mod_leitbox'),
    'strhintcol'         => get_string('hint', 'mod_leitbox'),
    'stractionscol'      => get_string('actions', 'moodle'),
    'strdeleteselected'  => get_string('deleteselected', 'mod_leitbox'),
    'strexportcards'     => get_string('exportcards', 'mod_leitbox'),
    'strselectall'       => get_string('selectall', 'moodle'),

    // Card form fields.
    'isedit'      => (bool)$editcard,
    'editcardid'  => $editcard ? $editcard->id : 0,
    'qval'        => $editcard ? $editcard->question : '',
    'aval'        => $editcard ? $editcard->answer : '',
    'hval'        => $editcard ? $editcard->hint : '',

    // Prompt type selector options.
    'prompttypes' => [
        ['key' => 'standard', 'label' => get_string('prompt_type_standard', 'mod_leitbox'), 'selected' => true],
        ['key' => 'tf', 'label' => get_string('prompt_type_tf', 'mod_leitbox')],
        ['key' => 'vocab', 'label' => get_string('prompt_type_vocab', 'mod_leitbox')],
        ['key' => 'cloze', 'label' => get_string('prompt_type_cloze', 'mod_leitbox')],
        ['key' => 'jeopardy', 'label' => get_string('prompt_type_jeopardy', 'mod_leitbox')],
        ['key' => 'transfer', 'label' => get_string('prompt_type_transfer', 'mod_leitbox')],
    ],
    'initialprompt'  => get_string('prompt_template_standard', 'mod_leitbox'),

    // Cards table.
    'hascards'       => !empty($cards),
    'cards'          => $cardrows,
    'pagingbartop'   => $pagingbartop,
    'pagingbarbottom' => $pagingbarbottom,
    'exporturl'      => (new moodle_url(
        '/mod/leitbox/manage.php',
        ['id' => $cm->id, 'action' => 'export', 'sesskey' => sesskey()]
    ))->out(false),
];

// Pass prompt templates and confirmation strings to the AMD module via the
// template (avoids Moodle's 1024-character limit on js_call_amd arguments).
$amdparams = [
    'prompts'            => $prompts,
    'confirmDelete'      => get_string('confirmdeletecard', 'mod_leitbox'),
    'confirmBulkDelete'  => get_string('confirmbulkdelete', 'mod_leitbox'),
];
$templatedata['jsconfig'] = json_encode($amdparams);

// Output.

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecards', 'mod_leitbox'));
echo $OUTPUT->notification(
    get_string('didactic_limit_notice', 'mod_leitbox'),
    \core\output\notification::NOTIFY_INFO
);
echo $OUTPUT->render_from_template('mod_leitbox/manage', $templatedata);
echo $OUTPUT->footer();
