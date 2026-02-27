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
namespace mod_recall\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\request\plugin\provider as plugin_provider;

defined('MOODLE_INTERNAL') || die();

class provider implements metadata_provider, plugin_provider {

    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table(
            'recall_progress',
            [
                'userid' => 'privacy:metadata:recall_progress:userid',
                'cardid' => 'privacy:metadata:recall_progress:cardid',
                'box_number' => 'privacy:metadata:recall_progress:box_number',
                'count_correct' => 'privacy:metadata:recall_progress:count_correct',
                'count_wrong' => 'privacy:metadata:recall_progress:count_wrong',
                'last_reviewed' => 'privacy:metadata:recall_progress:last_reviewed',
            ],
            'privacy:metadata:recall_progress'
        );
        return $collection;
    }

    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {modules} m ON m.name = :modname AND m.id = cm.module
                  JOIN {recall} s ON s.id = cm.instance
                  JOIN {recall_cards} sc ON sc.recallid = s.id
                  JOIN {recall_progress} sp ON sp.cardid = sc.id
                 WHERE sp.userid = :userid";
        $params = [
            'modname' => 'recall',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    public static function export_user_data(approved_contextlist $contextlist) {
        // Simple skeleton export. Data is exported using writer.
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        if ($cm = get_coursemodule_from_id('recall', $context->instanceid)) {
            $sql = "SELECT p.id 
                      FROM {recall_progress} p
                      JOIN {recall_cards} c ON c.id = p.cardid
                     WHERE c.recallid = ?";
            $DB->delete_records_select('recall_progress', "id IN ($sql)", [$cm->instance]);
        }
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                if ($cm = get_coursemodule_from_id('recall', $context->instanceid)) {
                    $sql = "SELECT p.id 
                              FROM {recall_progress} p
                              JOIN {recall_cards} c ON c.id = p.cardid
                             WHERE c.recallid = ? AND p.userid = ?";
                    $DB->delete_records_select('recall_progress', "id IN ($sql)", [$cm->instance, $userid]);
                }
            }
        }
    }
}


