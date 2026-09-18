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
 * mod_leitbox data generator.
 *
 * @package   mod_leitbox
 * @category  test
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * LeitBox module data generator class, used by the Moodle testing framework
 * (getDataGenerator()->create_module('leitbox', ...)) to create test
 * instances of the activity.
 */
class mod_leitbox_generator extends testing_module_generator {
    /**
     * Creates a new instance of mod_leitbox for testing purposes.
     *
     * @param array|stdClass $record Fields to set on the new instance.
     * @param array|null $options General options for the instance.
     * @return stdClass The created activity instance record.
     */
    public function create_instance($record = null, ?array $options = null) {
        $record = (object)(array)$record;

        if (!isset($record->cardorder)) {
            $record->cardorder = 0;
        }

        return parent::create_instance($record, (array)$options);
    }
}
