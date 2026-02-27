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
 * @package   mod_smartcards
 * @copyright 2026 SmartCards Author
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['modulename'] = 'Smartcards Activity';
$string['modulename_help'] = 'The Smartcards activity allows students to practice flashcards using a spaced repetition system based on the Leitner method. The UI uses a modern single-page application approach.';
$string['modulenameplural'] = 'Smartcards Activities';
$string['pluginadministration'] = 'Smartcards administration';
$string['pluginname'] = 'Smartcards Activity';
$string['smartcards:addinstance'] = 'Add a new Smartcards activity';
$string['smartcards:view'] = 'View Smartcards activity';

$string['smartcardsname'] = 'Activity Name';
$string['settings'] = 'Smartcards Settings';
$string['completion_min_cards'] = 'Minimum cards to practice';
$string['completion_min_cards_desc'] = 'Student must practice at least this many flashcards:';
$string['completion_min_cards_help'] = 'The minimum number of cards a student must interact with to complete this activity.';

$string['completion_min_mastered'] = 'Mastered cards (Box 5)';
$string['completion_min_mastered_desc'] = 'Student must master at least this many flashcards (reach Box 5):';
$string['completion_min_mastered_help'] = 'The number of cards a student must successfully move to the final box (Box 5) to complete the activity. This ensures true mastery rather than just interaction.';

$string['box0'] = 'Box 0 (New)';
$string['box1'] = 'Box 1 (Incorrect)';
$string['box2'] = 'Box 2';
$string['box3'] = 'Box 3';
$string['box4'] = 'Box 4';
$string['box5'] = 'Box 5 (Graduated)';

$string['cardorder'] = 'Card Order';
$string['cardorder_random'] = 'Random';
$string['cardorder_sequential'] = 'Sequential';
$string['cardorder_help'] = 'Determines whether cards are presented in a random order or sequentially as they were added.';

$string['question'] = 'Question';
$string['answer'] = 'Answer';
$string['hint'] = 'Hint';

// Privacy metadata.
$string['privacy:metadata:smartcards_progress'] = 'Information about user progress on smartcards.';
$string['privacy:metadata:smartcards_progress:userid'] = 'The user ID.';
$string['privacy:metadata:smartcards_progress:cardid'] = 'The card ID.';
$string['privacy:metadata:smartcards_progress:box_number'] = 'The Leitner box the card is currently in.';
$string['privacy:metadata:smartcards_progress:count_correct'] = 'The number of times the user answered the card correctly.';
$string['privacy:metadata:smartcards_progress:count_wrong'] = 'The number of times the user answered the card incorrectly.';
$string['privacy:metadata:smartcards_progress:last_reviewed'] = 'The timestamp when the card was last reviewed.';

// Management Interface
$string['managecards'] = 'Manage Cards';
$string['backtoactivity'] = 'Back to activity';
$string['addsinglecard'] = 'Add single card';
$string['editsinglecard'] = 'Edit card';
$string['addcard'] = 'Save Card';
$string['updatecard'] = 'Save changes';
$string['cardadded'] = 'Card added successfully.';
$string['cardupdated'] = 'Card updated successfully.';
$string['cancel'] = 'Cancel';
$string['bulkimport'] = 'Bulk Import (AI / Text)';
$string['bulkimportdesc'] = 'Paste a formatted text block to import multiple cards at once.';
$string['prompt_instruction'] = 'Copy this prompt and use it with ChatGPT/Claude to generate cards from your material:';
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
$string['dashboardtitle'] = 'Your SmartCards';
$string['dashboardsbtitle'] = 'Select a learning deck to practice';
$string['howitworks'] = 'How does this work?';
$string['cards'] = 'Cards';
$string['systemtitle'] = 'The Spaced Repetition System';
$string['systemintro'] = 'This plugin uses the proven <strong>Spaced Repetition System</strong>. The goal is to move cards from left to right into the final deck ("Graduated").';
$string['known_btn'] = 'Known';
$string['known_desc'] = 'The card was easy! It moves one deck to the right. It will be asked less frequently in the future.';
$string['again_btn'] = 'Again';
$string['again_desc'] = 'You were unsure. The card stays in the current deck and will be repeated soon.';
$string['hard_btn'] = 'Hard';
$string['hard_desc'] = 'Completely forgot! The card immediately goes back to the "Hard" deck (Box 1), no matter how far it was.';
$string['systemtip'] = '<strong>Tip:</strong> Always focus on the cards on the left first (Box 1 and 2). These are the cards you currently need to practice the most!';
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
$string['sessiondonedesc'] = 'Great job! Keep it up in the next deck.';
$string['completed'] = 'Completed';
$string['error_loading_cards'] = 'Cards could not be loaded. Please check your connection.';
