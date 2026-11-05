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
 * Behat steps and page definitions for mod_leitbox.
 *
 * @package   mod_leitbox
 * @category  test
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException;

/**
 * Behat steps and page definitions for mod_leitbox.
 */
class behat_mod_leitbox extends behat_base {
    /**
     * Converts page names to URLs for steps like 'I am on the "[identifier]" "mod_leitbox > [page type]" page'.
     *
     * Recognised page types:
     * | Page type    | Identifier       | Description                    |
     * | Manage cards | Activity name    | The card management page       |
     *
     * @param string $type The page type, e.g. 'Manage cards'.
     * @param string $identifier The activity name.
     * @return moodle_url The URL of the page.
     */
    protected function resolve_page_instance_url(string $type, string $identifier): moodle_url {
        switch (strtolower($type)) {
            case 'manage cards':
                $cm = $this->get_cm_by_activity_name('leitbox', $identifier);
                return new moodle_url('/mod/leitbox/manage.php', ['id' => $cm->id]);
            default:
                throw new Exception('Unrecognised mod_leitbox page type "' . $type . '."');
        }
    }

    /**
     * Checks the theme the current page was rendered with, as Moodle tells its JavaScript (M.cfg.theme),
     * so a scenario for another theme cannot silently run with Boost when that theme is missing.
     * The step name differs from the one in mod_leafr, so both plugins can share a test site.
     *
     * @Then /^the LeitBox page should use the "(?P<theme_string>[^"]*)" theme$/
     * @param string $theme The theme name, e.g. "boost_union".
     */
    public function the_leitbox_page_should_use_the_theme(string $theme): void {
        $this->require_javascript();
        $actual = $this->evaluate_script('return (window.M && M.cfg) ? M.cfg.theme : null;');
        if ($actual !== $theme) {
            throw new ExpectationException(
                'The page uses the theme "' . $actual . '", not "' . $theme . '"',
                $this->getSession()
            );
        }
    }
}
