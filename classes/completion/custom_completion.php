<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ...

namespace mod_leitbox\completion;

defined('MOODLE_INTERNAL') || die();

use core_completion\activity_custom_completion;

/**
 * Custom completion class for Leitbox.
 *
 * @package   mod_leitbox
 */
class custom_completion extends activity_custom_completion {

    public static function get_defined_custom_rules(): array {
        return [
            'completion_min_cards',
            'completion_min_mastered',
            'completion_all_mastered'
        ];
    }

    public function get_state(string $rule): int {
        global $DB;

        $userid = $this->userid;
        $cm = $this->cm;
        
        $leitbox = $DB->get_record('leitbox', ['id' => $cm->instance], '*', MUST_EXIST);
        
        if ($rule === 'completion_min_cards') {
            if (!$leitbox->completion_min_cards) {
                return COMPLETION_COMPLETE;
            }
            $sql = "SELECT COUNT(DISTINCT cardid) FROM {leitbox_progress} WHERE userid = :userid AND cardid IN (SELECT id FROM {leitbox_cards} WHERE leitboxid = :instanceid)";
            $count = $DB->get_field_sql($sql, ['userid' => $userid, 'instanceid' => $leitbox->id]) ?: 0;
            return ($count >= $leitbox->completion_min_cards) ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
            
        } else if ($rule === 'completion_min_mastered') {
            if (!$leitbox->completion_min_mastered) {
                return COMPLETION_COMPLETE;
            }
            $sql_mastered = "SELECT COUNT(DISTINCT cardid) FROM {leitbox_progress} WHERE userid = :userid AND box_number = 5 AND cardid IN (SELECT id FROM {leitbox_cards} WHERE leitboxid = :instanceid)";
            $mastered = $DB->get_field_sql($sql_mastered, ['userid' => $userid, 'instanceid' => $leitbox->id]) ?: 0;
            return ($mastered >= $leitbox->completion_min_mastered) ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
            
        } else if ($rule === 'completion_all_mastered') {
            if (empty($leitbox->completion_all_mastered)) {
                return COMPLETION_COMPLETE;
            }
            $total_cards = $DB->count_records('leitbox_cards', ['leitboxid' => $leitbox->id]);
            $sql_mastered = "SELECT COUNT(DISTINCT p.cardid) FROM {leitbox_progress} p JOIN {leitbox_cards} c ON p.cardid = c.id WHERE c.leitboxid = :instanceid AND p.userid = :userid AND p.box_number = 5";
            $mastered_cards = $DB->get_field_sql($sql_mastered, ['instanceid' => $leitbox->id, 'userid' => $userid]) ?: 0;
            if ($total_cards > 0 && $total_cards == $mastered_cards) {
                return COMPLETION_COMPLETE;
            }
            return COMPLETION_INCOMPLETE;
        }

        return COMPLETION_INCOMPLETE;
    }

    public function get_custom_rule_descriptions(): array {
        return [
            'completion_min_cards' => get_string('completion_min_cards_desc', 'mod_leitbox'),
            'completion_min_mastered' => get_string('completion_min_mastered_desc', 'mod_leitbox'),
            'completion_all_mastered' => get_string('completion_all_mastered', 'mod_leitbox'),
        ];
    }
    
    public function get_sort_order(): array {
        return [
            'completion_min_cards',
            'completion_min_mastered',
            'completion_all_mastered'
        ];
    }
}
