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
$string['modulename'] = 'Recall Activity';
$string['modulename_help'] = 'The Recall activity allows students to practice flashcards using a spaced repetition system based on the Leitner method. The UI uses a modern single-page application approach.';
$string['modulenameplural'] = 'Recall Activities';
$string['pluginadministration'] = 'Recall administration';
$string['pluginname'] = 'Recall Activity';
$string['recall:addinstance'] = 'Add a new Recall activity';
$string['recall:view'] = 'View Recall activity';

$string['recallname'] = 'Activity Name';
$string['settings'] = 'Recall Settings';
$string['completion_min_cards'] = 'Minimum cards to practice';
$string['completion_min_cards_desc'] = 'Student must practice at least this many flashcards:';
$string['completion_min_cards_help'] = 'The minimum number of cards a student must interact with to complete this activity.';

$string['completion_min_mastered'] = 'Mastered cards (Box 5)';
$string['completion_min_mastered_desc'] = 'Student must master at least this many flashcards (reach Box 5):';
$string['completion_min_mastered_help'] = 'The number of cards a student must successfully move to the final box (Box 5) to complete the activity. This ensures true mastery rather than just interaction.';

$string['box0'] = 'New';
$string['box1'] = 'Beginner';
$string['box2'] = 'Learner';
$string['box3'] = 'Advanced';
$string['box4'] = 'Experienced';
$string['box5'] = 'Expert';

$string['cardorder'] = 'Card Order';
$string['cardorder_random'] = 'Random';
$string['cardorder_sequential'] = 'Sequential';
$string['cardorder_help'] = 'Determines whether cards are presented in a random order or sequentially as they were added.';

$string['question'] = 'Question';
$string['answer'] = 'Answer';
$string['hint'] = 'Hint';

// Privacy metadata.
$string['privacy:metadata:recall_progress'] = 'Information about user progress on Recall.';
$string['privacy:metadata:recall_progress:userid'] = 'The user ID.';
$string['privacy:metadata:recall_progress:cardid'] = 'The card ID.';
$string['privacy:metadata:recall_progress:box_number'] = 'The Leitner box the card is currently in.';
$string['privacy:metadata:recall_progress:count_correct'] = 'The number of times the user answered the card correctly.';
$string['privacy:metadata:recall_progress:count_wrong'] = 'The number of times the user answered the card incorrectly.';
$string['privacy:metadata:recall_progress:last_reviewed'] = 'The timestamp when the card was last reviewed.';

// Management Interface
$string['demo_q1'] = 'Welcome to Recall.<br><br>What is the goal of this learning system?';
$string['demo_a1'] = 'The goal is to move all flashcards to the final deck ("Expert") by answering them correctly.<br><br>The system uses the method of <b>spaced repetition</b> for this purpose.';
$string['demo_h1'] = 'Think about how you remember things best long-term.';
$string['demo_q2'] = 'How do I move a card to the next deck?';
$string['demo_a2'] = 'By clicking the <b>green</b> button <b>Got it</b>.<br><br>This indicates that the answer was known. The card advances one deck and will be queried less frequently in the future.';
$string['demo_h2'] = 'It has to do with the green checkmark.';
$string['demo_q3'] = 'What happens when clicking the <b>red</b> button <b>Hard</b>?';
$string['demo_a3'] = 'The card moves back exactly <b>one deck</b>.<br><br>Select this option if you did not know the answer. The card will be repeated more frequently without completely resetting your previous learning progress.';
$string['demo_h3'] = 'Did not know it!';
$string['demo_q4'] = 'What is the <b>yellow</b> button <b>Again</b> used for?';
$string['demo_a4'] = 'This option is intended for cases where your answer was uncertain or incomplete.<br><br>The card remains in its current deck. It will be presented again within the same learning session, but your overall progress is maintained.';
$string['demo_h4'] = 'The sweet spot between easy and hard.';
$string['demo_q5'] = 'Usage note: Can these demo cards be deleted?';
$string['demo_a5'] = 'Yes. As soon as you import or create your own flashcards, the system will remove these demo cards automatically.';
$string['demo_h5'] = '';

$string['managecards'] = 'Manage Cards';
$string['backtoactivity'] = 'Back to activity';
$string['addsinglecard'] = 'Add single card';
$string['editsinglecard'] = 'Edit card';
$string['addcard'] = 'Save Card';
$string['updatecard'] = 'Save changes';
$string['cardadded'] = 'Card added successfully.';
$string['cardupdated'] = 'Card updated successfully.';
$string['carddeleted'] = 'Card deleted.';
$string['cardsdeleted'] = 'Successfully deleted {$a} cards.';
$string['deleteselected'] = 'Delete selected';
$string['confirmbulkdelete'] = 'Are you sure you want to delete the selected cards? This action cannot be undone.';
$string['error_limit_reached'] = 'Limit reached: For didactic reasons, a maximum of 200 cards per set is allowed.';
$string['error_limit_exceeded_import'] = 'Import failed: The limit of 200 cards would be exceeded. You can only add {$a} more cards to this set.';
$string['didactic_limit_notice'] = '<strong>Learning Tip:</strong> To ensure optimal learning success and avoid cognitive overload, each activity is limited to <strong>200 cards</strong>. For larger topics, we highly recommend splitting the material across multiple Recall activities (e.g., "Chapter 1", "Chapter 2") in your course.';
$string['bulkimport'] = 'Bulk Import (AI / Text)';
$string['bulkimportdesc'] = 'Paste a formatted text block to import multiple cards at once.';
$string['exportcards'] = 'Export cards (.txt)';
$string['prompt_instruction'] = 'Copy this prompt and use it with any AI to generate cards from your material:';
$string['prompt_template'] = 'Create flashcards from the following text for studying. 
Format each card EXACTLY like this, with no markdown (no asterisks, no hashes), and separate all cards with ===CARD===. 
Do not write an introduction, only the pure text code.

Example of the requested output format:
===CARD===
Q: What is the capital of France?
A: Paris
H: City of Love

===CARD===
Q: Who formulated the theory of relativity?
A: Albert Einstein

Here is the text you should create the cards from:';
$string['importcards'] = 'Import Cards';
$string['cardsimported'] = '{$a} cards were imported successfully.';
$string['existingcards'] = 'Existing cards';
$string['nocards'] = 'No cards found in this activity.';
$string['carddeleted'] = 'Card deleted.';
$string['confirmdeletecard'] = 'Are you sure you want to delete this card?';

// Vue Frontend App Strings
$string['dashboardtitle'] = 'Your Recall Cards';
$string['dashboardsbtitle'] = 'Select a learning deck to practice';
$string['howitworks'] = 'How does this work?';
$string['cards'] = 'Cards';
$string['systemtitle'] = 'The Spaced Repetition System';
$string['systemintro'] = 'This plugin uses the proven <strong>Spaced Repetition System</strong>. The goal is to move cards from left to right into the final deck ("Graduated").';
$string['known_btn'] = 'Got it';
$string['known_desc'] = 'Easy! The card moves one stack to the right.';
$string['again_btn'] = 'Again';
$string['again_desc'] = 'Not sure. The card stays in the current stack.';
$string['hard_btn'] = 'Hard';
$string['hard_desc'] = 'Did not know it! The card moves back one stack.';
$string['systemtip'] = '<strong>Tip:</strong> Review the topics behind the cards you did not know – before you start a new attempt.';
$string['gotit'] = 'Got it, let\'s go!';
$string['showhint'] = 'Show hint';
$string['taptoflip'] = 'Tap to flip';
$string['action_back'] = 'Back';
$string['action_stay'] = 'Stay';
$string['action_next'] = 'Next';
$string['backtodashboard'] = 'Back to dashboard';
$string['cardxofy_x'] = 'Card';
$string['cardxofy_y'] = 'of';
$string['loadingcards'] = 'Shuffling cards...';
$string['sessiondone'] = 'All done!';
$string['sessiondonedesc'] = 'Good job. Return to the dashboard for the next deck.';
$string['feedback_grand_title'] = 'Masterpiece!';
$string['feedback_grand_desc'] = 'Congratulations! You successfully moved all cards into the Expert deck and reviewed them flawlessly. You have truly mastered this subject!';
$string['feedback_perfect_title'] = 'Strong Result';
$string['feedback_perfect_desc'] = 'You correctly recalled all of the content. You have a solid grasp of this material.';
$string['feedback_good_title'] = 'Solid Performance';
$string['feedback_good_desc'] = 'You have a good overview. Consistent practice will close the remaining gaps.';
$string['feedback_okay_title'] = 'On the Right Track';
$string['feedback_okay_desc'] = 'The foundation is there. Another review session will reinforce your knowledge.';
$string['feedback_learn_title'] = 'Review Recommended';
$string['feedback_learn_desc'] = 'Some answers were difficult to recall. Use the next round to train these specific topics.';
$string['completed'] = 'Completed';
$string['error_loading_cards'] = 'Could not load cards. Please check your connection.';
$string['reset_progress'] = 'Reset Learning Progress';
$string['reset_progress_confirm_title'] = 'Reset Learning Progress?';
$string['reset_progress_confirm_msg'] = 'Warning: This will reset all your learning progress. All cards will be moved back to "New". This cannot be undone!';
$string['reset_progress_btn'] = 'Yes, Reset';
$string['reset_progress_cancel'] = 'Cancel';
$string['reset_progress_done'] = 'Learning progress has been reset!';
