<?php
define('CLI_SCRIPT', true);
require('C:\xampp\htdocs\moodle\config.php');
require_once($CFG->dirroot . '/mod/recall/classes/privacy/provider.php');

// We need a recall instance. Let's find one.
$recall = $DB->get_record('recall', [], '*', IGNORE_MULTIPLE);
if (!$recall) {
    die("No recall instance found to test.");
}
$cm = get_coursemodule_from_instance('recall', $recall->id);
$context = context_module::instance($cm->id);

$USER = $DB->get_record('user', ['id' => 2]); // Admin user ID usually 2
if (!$USER) {
    die("No admin user found.");
}

// Let's create a dummy progress record if none exists.
$cards = $DB->get_records('recall_cards', ['recallid' => $recall->id]);
if (empty($cards)) {
   die("No cards found in instance.");
}
$card = reset($cards);

$progress = $DB->get_record('recall_progress', ['userid' => $USER->id, 'cardid' => $card->id]);
if (!$progress) {
    $progress = new stdClass();
    $progress->userid = $USER->id;
    $progress->cardid = $card->id;
    $progress->box_number = 3;
    $progress->count_correct = 5;
    $progress->count_wrong = 1;
    $progress->last_reviewed = time();
    $progress->status = 0;
    $DB->insert_record('recall_progress', $progress);
    echo "Created dummy progress for testing.\n";
}

// Now test export.
$contextlist = new \core_privacy\local\request\approved_contextlist($USER, 'mod_recall', [$context->id]);
\core_privacy\local\request\writer::reset();
\mod_recall\privacy\provider::export_user_data($contextlist);

$writer = \core_privacy\local\request\writer::with_context($context);
$data = $writer->get_data([get_string('pluginname', 'mod_recall'), get_string('cards', 'mod_recall')]);

echo "\n--- SUCCESS: EXPORTED DATA ---\n";
print_r($data);
echo "------------------------------\n";
