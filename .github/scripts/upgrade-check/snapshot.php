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
 * Upgrade check, step 2 and 4: writes every LeitBox related row of the site to a JSON file.
 *
 * Covers all LeitBox tables (leitbox, leitbox_*), the course modules of LeitBox activities, their
 * completion states and views, and the plugin version. LeitBox has no gradebook items, user
 * preferences or files of its own. Rows are keyed by id, so compare.php can show exactly what an
 * upgrade changed.
 *
 * Usage: php snapshot.php /path/to/moodle/config.php /path/to/output.json
 *
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
if (count($argv) < 3) {
    die("Usage: php snapshot.php /path/to/moodle/config.php /path/to/output.json\n");
}
// Moodle may change the working directory while it starts, so the output path is fixed first.
$outfile = preg_match('~^([a-zA-Z]:)?[/\\\\]~', $argv[2]) ? $argv[2] : getcwd() . '/' . $argv[2];
require($argv[1]);

global $DB;
$tables = [];
foreach ($DB->get_tables(false) as $table) {
    if ($table === 'leitbox' || strpos($table, 'leitbox_') === 0) {
        $tables[$table] = $DB->get_records($table, null, 'id');
    }
}
ksort($tables);
$moduleid = $DB->get_field('modules', 'id', ['name' => 'leitbox'], MUST_EXIST);
$tables['course_modules (leitbox)'] = $DB->get_records(
    'course_modules',
    ['module' => $moduleid],
    'id',
    'id, course, instance, section, visible, groupmode, groupingid, completion, completionview, completionexpected'
);
// Before Moodle 4.3 views are a column of the completion table, since then they have their own table.
$viewed = array_key_exists('viewed', $DB->get_columns('course_modules_completion')) ? ', cmc.viewed' : '';
$tables['course_modules_completion (leitbox)'] = $DB->get_records_sql(
    "SELECT cmc.id, cmc.coursemoduleid, cmc.userid, cmc.completionstate, cmc.overrideby, cmc.timemodified$viewed
       FROM {course_modules_completion} cmc
       JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid
      WHERE cm.module = :moduleid
   ORDER BY cmc.id",
    ['moduleid' => $moduleid]
);
if ($DB->get_manager()->table_exists('course_modules_viewed')) {
    $tables['course_modules_viewed (leitbox)'] = $DB->get_records_sql(
        "SELECT cmv.id, cmv.coursemoduleid, cmv.userid, cmv.timecreated
           FROM {course_modules_viewed} cmv
           JOIN {course_modules} cm ON cm.id = cmv.coursemoduleid
          WHERE cm.module = :moduleid
       ORDER BY cmv.id",
        ['moduleid' => $moduleid]
    );
}
$tables['config_plugins (versions)'] = $DB->get_records_select(
    'config_plugins',
    "name = 'version' AND plugin = 'mod_leitbox'",
    null,
    'id'
);

$out = [];
foreach ($tables as $name => $rows) {
    $clean = [];
    foreach ($rows as $id => $row) {
        $clean[(string)$id] = array_map(function ($value) {
            return $value === null ? null : (string)$value;
        }, (array)$row);
    }
    $out[$name] = $clean;
}
file_put_contents($outfile, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
foreach ($out as $name => $rows) {
    printf("%-38s %6d rows\n", $name, count($rows));
}
