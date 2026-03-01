<?php
define('CLI_SCRIPT', true);
require('C:\Moodle\server\moodle\config.php');
require_once('C:\smartcards\lib.php');
require_once('C:\smartcards\classes\external.php');

global $DB;
// Simulating an answer for User 2 in Leitbox ID 14
$leitbox_id = 14;
$cm = get_coursemodule_from_instance('leitbox', $leitbox_id);

// Pick a card from Box 1 and simulate answering "Green"
$progress = $DB->get_record_sql("SELECT * FROM {leitbox_progress} WHERE box_number = 1 AND userid = 2 AND cardid IN (SELECT id FROM {leitbox_cards} WHERE leitboxid = ?) LIMIT 1", [$leitbox_id]);

if ($progress) {
    echo "Testing submission for card {$progress->cardid}...\n";
    // Setup fake Moodle user session
    $USER = $DB->get_record('user', ['id' => 2]);
    \core\session\manager::set_user($USER);

    // Call external service
    $result = \mod_leitbox\external::submit_answer($cm->id, $progress->cardid, 2);
    echo "Result: "; print_r($result);
    
    // Check completion state again
    $state = $DB->get_record('course_modules_completion', ['coursemoduleid' => $cm->id, 'userid' => 2]);
    echo "New Completion State: " . $state->completionstate . "\n";
} else {
    echo "No card found in Box 1.\n";
}
