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
defined('MOODLE_INTERNAL') || die();

$functions = array(
    'mod_recall_get_box_counts' => array(
        'classname' => 'mod_recall\external',
        'methodname' => 'get_box_counts',
        'classpath' => 'mod/recall/classes/external.php',
        'description' => 'Get aggregated flashcard counts per Leitner box',
        'type' => 'read',
        'ajax' => true,
    ),
    'mod_recall_get_cards_by_box' => array(
        'classname' => 'mod_recall\external',
        'methodname' => 'get_cards_by_box',
        'classpath' => 'mod/recall/classes/external.php',
        'description' => 'Get flashcards by Leitner box number',
        'type' => 'read',
        'ajax' => true,
    ),
    'mod_recall_submit_answer' => array(
        'classname' => 'mod_recall\external',
        'methodname' => 'submit_answer',
        'classpath' => 'mod/recall/classes/external.php',
        'description' => 'Submit a flashcard review rating',
        'type' => 'write',
        'ajax' => true,
    ),
    'mod_recall_reset_progress' => array(
        'classname' => 'mod_recall\external',
        'methodname' => 'reset_progress',
        'classpath' => 'mod/recall/classes/external.php',
        'description' => 'Reset all card progress for the current user',
        'type' => 'write',
        'ajax' => true,
    ),
);
