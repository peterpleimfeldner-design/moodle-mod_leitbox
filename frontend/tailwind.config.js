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
 * Tailwind CSS build configuration for the LeitBox Vue frontend.
 *
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @type      {import('tailwindcss').Config}
 */
export default {
    content: [
        "./index.html",
        "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],
    theme: {
        extend: {},
    },
    // This bundle mounts into an existing Moodle page, not a blank document -
    // Preflight is a global reset (bare `h1`, `button`, `*` selectors, etc.)
    // that leaks onto the whole page and fights Moodle/Bootstrap's own base
    // styles (observed breaking the theme's responsive grid on narrow
    // viewports). Utility classes are unaffected; only the base reset layer
    // is skipped.
    corePlugins: {
        preflight: false,
    },
    plugins: [],
}
