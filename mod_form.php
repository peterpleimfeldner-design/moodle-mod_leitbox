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
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_leitbox_mod_form extends moodleform_mod {
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // General section.
        $mform->addElement('header', 'general', get_string('general', 'form'));
        
        $mform->addElement('text', 'name', get_string('leitboxname', 'mod_leitbox'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // Specific settings.
        $mform->addElement('header', 'leitboxfieldset', get_string('settings', 'mod_leitbox'));

        $mform->addElement('select', 'cardorder', get_string('cardorder', 'mod_leitbox'), array(
            0 => get_string('cardorder_random', 'mod_leitbox'),
            1 => get_string('cardorder_sequential', 'mod_leitbox')
        ));
        $mform->setDefault('cardorder', 0);
        $mform->addHelpButton('cardorder', 'cardorder', 'mod_leitbox');

        // Settings are now handled by add_completion_rules()

        // Standard course module elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    public function add_completion_rules() {
        $mform = $this->_form;
        $suffix = method_exists($this, 'get_suffix') ? $this->get_suffix() : '';

        // 1. Min Cards Rule
        $group = array();
        $completion_min_cards_enabled = 'completion_min_cards_enabled' . $suffix;
        $completion_min_cards = 'completion_min_cards' . $suffix;
        $completion_min_cards_group = 'completion_min_cards_group' . $suffix;

        $group[] = $mform->createElement('checkbox', $completion_min_cards_enabled, '',
            get_string('completion_min_cards_desc', 'mod_leitbox'));
        $group[] = $mform->createElement('text', $completion_min_cards, '', array('size' => 3));
        $mform->setType($completion_min_cards, PARAM_INT);
        $mform->addGroup($group, $completion_min_cards_group, get_string('completion_min_cards', 'mod_leitbox'), array(' '), false);
        $mform->hideIf($completion_min_cards, $completion_min_cards_enabled, 'notchecked');

        // 2. Min Mastered Rule
        $group2 = array();
        $completion_min_mastered_enabled = 'completion_min_mastered_enabled' . $suffix;
        $completion_min_mastered = 'completion_min_mastered' . $suffix;
        $completion_min_mastered_group = 'completion_min_mastered_group' . $suffix;

        $group2[] = $mform->createElement('checkbox', $completion_min_mastered_enabled, '',
            get_string('completion_min_mastered_desc', 'mod_leitbox'));
        $group2[] = $mform->createElement('text', $completion_min_mastered, '', array('size' => 3));
        $mform->setType($completion_min_mastered, PARAM_INT);
        $mform->addGroup($group2, $completion_min_mastered_group, get_string('completion_min_mastered', 'mod_leitbox'), array(' '), false);
        $mform->hideIf($completion_min_mastered, $completion_min_mastered_enabled, 'notchecked');

        // 3. All Mastered Rule
        $completion_all_mastered = 'completion_all_mastered' . $suffix;
        $mform->addElement('checkbox', $completion_all_mastered, 
            get_string('completion_all_mastered', 'mod_leitbox'),
            get_string('completion_all_mastered_desc', 'mod_leitbox'));

        return array($completion_min_cards_group, $completion_min_mastered_group, $completion_all_mastered);
    }

    public function completion_rule_enabled($data) {
        $suffix = method_exists($this, 'get_suffix') ? $this->get_suffix() : '';
        return (!empty($data['completion_min_cards_enabled' . $suffix]) && $data['completion_min_cards' . $suffix] > 0) ||
               (!empty($data['completion_min_mastered_enabled' . $suffix]) && $data['completion_min_mastered' . $suffix] > 0) ||
               (!empty($data['completion_all_mastered' . $suffix]));
    }

    public function data_preprocessing(&$default_values) {
        $suffix = method_exists($this, 'get_suffix') ? $this->get_suffix() : '';
        
        $completion_min_cards = 'completion_min_cards' . $suffix;
        $completion_min_cards_enabled = 'completion_min_cards_enabled' . $suffix;
        if (empty($default_values[$completion_min_cards])) {
            $default_values[$completion_min_cards] = 10;
            $default_values[$completion_min_cards_enabled] = 0;
        } else {
            $default_values[$completion_min_cards_enabled] = $default_values[$completion_min_cards] > 0 ? 1 : 0;
        }
        
        $completion_min_mastered = 'completion_min_mastered' . $suffix;
        $completion_min_mastered_enabled = 'completion_min_mastered_enabled' . $suffix;
        if (empty($default_values[$completion_min_mastered])) {
            $default_values[$completion_min_mastered] = 0;
            $default_values[$completion_min_mastered_enabled] = 0;
        } else {
            $default_values[$completion_min_mastered_enabled] = $default_values[$completion_min_mastered] > 0 ? 1 : 0;
        }
        
        $completion_all_mastered = 'completion_all_mastered' . $suffix;
        if (!isset($default_values[$completion_all_mastered])) {
            $default_values[$completion_all_mastered] = 0;
        }
    }

    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        $suffix = method_exists($this, 'get_suffix') ? $this->get_suffix() : '';
        
        if (!empty($data->completionunlocked)) {
            $completion = $data->{'completion' . $suffix} ?? 0;
            $autocompletion = !empty($completion) && $completion == COMPLETION_TRACKING_AUTOMATIC;
            
            if (empty($data->{'completion_min_cards_enabled' . $suffix}) || !$autocompletion) {
                $data->{'completion_min_cards' . $suffix} = 0;
            }
            if (empty($data->{'completion_min_mastered_enabled' . $suffix}) || !$autocompletion) {
                $data->{'completion_min_mastered' . $suffix} = 0;
            }
            if (!$autocompletion) {
                $data->{'completion_all_mastered' . $suffix} = 0;
            }
        }
    }
}
