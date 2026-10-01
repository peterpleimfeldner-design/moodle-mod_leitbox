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
 * Access to the language strings that view.php passes to the page.
 *
 * All learner-facing text comes from lang/<language>/leitbox.php through
 * $PAGE->requires->strings_for_js() in view.php. There are deliberately no
 * built-in fallback texts here. A key that view.php does not export shows
 * up as [[key]], the same marker Moodle itself uses for a missing string.
 *
 * @module    mod_leitbox/frontend/strings
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns a mod_leitbox language string.
 *
 * @param {string} key The string identifier.
 * @returns {string} The string in the current language.
 */
export const getString = (key) => {
    const value = window.M?.str?.mod_leitbox?.[key];
    return value ?? `[[${key}]]`;
};
