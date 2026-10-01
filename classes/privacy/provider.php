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
 * Privacy API implementation for mod_leitbox.
 *
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_leitbox\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\request\plugin\provider as plugin_provider;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\transform;

/**
 * Privacy API provider for mod_leitbox.
 */
class provider implements core_userlist_provider, metadata_provider, plugin_provider {
    /**
     * Subquery for the card ids of one leitbox instance (one positional parameter).
     *
     * Deletes from {leitbox_progress} filter by this list of card ids. The
     * subquery must not read {leitbox_progress} itself: MySQL rejects a
     * DELETE that selects from its own target table (error 1093).
     */
    private const CARDS_SQL = 'SELECT id FROM {leitbox_cards} WHERE leitboxid = ?';

    /**
     * Returns metadata about the personal data stored by this plugin.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'leitbox_progress',
            [
                'userid' => 'privacy:metadata:leitbox_progress:userid',
                'cardid' => 'privacy:metadata:leitbox_progress:cardid',
                'box_number' => 'privacy:metadata:leitbox_progress:box_number',
                'count_correct' => 'privacy:metadata:leitbox_progress:count_correct',
                'count_wrong' => 'privacy:metadata:leitbox_progress:count_wrong',
                'last_reviewed' => 'privacy:metadata:leitbox_progress:last_reviewed',
                'status' => 'privacy:metadata:leitbox_progress:status',
            ],
            'privacy:metadata:leitbox_progress'
        );
        return $collection;
    }

    /**
     * Returns the list of contexts containing personal data for a user.
     *
     * @param int $userid The user id.
     * @return contextlist The list of contexts containing personal data.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {modules} m ON m.name = :modname AND m.id = cm.module
                  JOIN {leitbox} r ON r.id = cm.instance
                  JOIN {leitbox_cards} rc ON rc.leitboxid = r.id
                  JOIN {leitbox_progress} rp ON rp.cardid = rc.id
                 WHERE rp.userid = :userid";
        $params = [
            'modname' => 'leitbox',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Adds the userids of all users with personal data in the given context.
     *
     * @param userlist $userlist The userlist to add userids to.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $sql = "SELECT rp.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.name = :modname AND m.id = cm.module
                  JOIN {leitbox} r ON r.id = cm.instance
                  JOIN {leitbox_cards} rc ON rc.leitboxid = r.id
                  JOIN {leitbox_progress} rp ON rp.cardid = rc.id
                 WHERE cm.id = :cmid";

        $params = [
            'modname' => 'leitbox',
            'cmid' => $context->instanceid,
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Exports personal data for the given approved contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export data for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                $cm = get_coursemodule_from_id('leitbox', $context->instanceid);
                if (!$cm) {
                    continue;
                }

                $sql = "SELECT rp.*, rc.question, rc.answer, rc.hint
                          FROM {leitbox_progress} rp
                          JOIN {leitbox_cards} rc ON rc.id = rp.cardid
                         WHERE rc.leitboxid = ? AND rp.userid = ?";
                $progressrecords = $DB->get_records_sql($sql, [$cm->instance, $userid]);

                if (!empty($progressrecords)) {
                    $exportdata = [];
                    foreach ($progressrecords as $rec) {
                        $exportdata[] = (object)[
                            'question' => format_text(self::resolve_demo_text($rec->question), FORMAT_HTML,
                                ['context' => $context]),
                            'answer' => format_text(self::resolve_demo_text($rec->answer), FORMAT_HTML,
                                ['context' => $context]),
                            'hint' => format_text(self::resolve_demo_text((string)$rec->hint), FORMAT_HTML,
                                ['context' => $context]),
                            'box_number' => $rec->box_number,
                            'status' => $rec->status,
                            'count_correct' => $rec->count_correct,
                            'count_wrong' => $rec->count_wrong,
                            'last_reviewed' => transform::datetime($rec->last_reviewed),
                        ];
                    }

                    \core_privacy\local\request\writer::with_context($context)->export_data(
                        [get_string('pluginname', 'mod_leitbox'), get_string('cards', 'mod_leitbox')],
                        (object)['progress' => $exportdata]
                    );
                }
            }
        }
    }

    /**
     * Resolves the language-neutral demo card markers (e.g. ##demo_q1##) to text.
     *
     * @param string $text The stored card text.
     * @return string The text to export.
     */
    private static function resolve_demo_text(string $text): string {
        if (preg_match('/^##(demo_[a-z0-9]+)##$/', $text, $matches)) {
            return get_string($matches[1], 'mod_leitbox');
        }
        return $text;
    }

    /**
     * Deletes all personal data for all users in the given context.
     *
     * @param \context $context The context to delete data in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        if ($cm = get_coursemodule_from_id('leitbox', $context->instanceid)) {
            $DB->delete_records_select('leitbox_progress', 'cardid IN (' . self::CARDS_SQL . ')', [$cm->instance]);
        }
    }

    /**
     * Deletes personal data for a user in the given approved contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to delete data in.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                if ($cm = get_coursemodule_from_id('leitbox', $context->instanceid)) {
                    $DB->delete_records_select(
                        'leitbox_progress',
                        'cardid IN (' . self::CARDS_SQL . ') AND userid = ?',
                        [$cm->instance, $userid]
                    );
                }
            }
        }
    }

    /**
     * Deletes personal data for multiple users in the given approved userlist.
     *
     * @param approved_userlist $userlist The approved userlist to delete data for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        if ($cm = get_coursemodule_from_id('leitbox', $context->instanceid)) {
            [$insql, $inparams] = $DB->get_in_or_equal($userids);
            $params = array_merge([$cm->instance], $inparams);
            $DB->delete_records_select('leitbox_progress', 'cardid IN (' . self::CARDS_SQL . ") AND userid $insql", $params);
        }
    }
}
