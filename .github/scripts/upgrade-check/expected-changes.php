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
 * The changes an upgrade from the previous release may make to existing data (see compare.php).
 *
 * Update this file with every release that changes the database: list each new column with the
 * value db/upgrade.php gives existing rows (as 'added' => [column => function (array $row): ?string]),
 * list columns an upgrade step may rewrite under 'changed', and explain why.
 *
 * 1.6.2 (from 1.6.1) only corrects texts: no database change, so only the plugin version changes.
 *
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

return [
    // Plugin versions are what an upgrade changes.
    'config_plugins (versions)' => [
        'changed' => ['value'],
    ],
];
