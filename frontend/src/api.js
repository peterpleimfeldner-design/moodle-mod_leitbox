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
 * Calls the mod_leitbox web services through Moodle's core/ajax module.
 *
 * core/ajax adds the sesskey, handles an expired session and reports errors
 * the Moodle way. main.js loads it through Moodle's AMD loader and passes it
 * in via initApi() before the Vue app is mounted.
 *
 * @module    mod_leitbox/frontend/api
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

let config = {};
let ajax = null;

/**
 * Stores the page configuration and the core/ajax module.
 *
 * @param {Object} cfg The configuration from the data-config attribute in view.php.
 * @param {Object} ajaxModule Moodle's core/ajax module.
 */
export const initApi = (cfg, ajaxModule) => {
    config = cfg;
    ajax = ajaxModule;
};

export const getConfig = () => config;

/**
 * Calls one Moodle web service function.
 *
 * @param {string} methodname The web service function name.
 * @param {Object} args The function arguments.
 * @returns {Promise} Resolves with the function's return value.
 */
export const moodleCall = (methodname, args) => {
    return ajax.call([{methodname, args}])[0];
};

export const getCardsByBox = (boxnumber) => {
    return moodleCall('mod_leitbox_get_cards_by_box', {
        instanceid: config.instanceid,
        boxnumber
    });
};

export const getBoxCounts = async() => {
    const counts = await moodleCall('mod_leitbox_get_box_counts', {
        instanceid: config.instanceid
    });

    const result = {"0": 0, "1": 0, "2": 0, "3": 0, "4": 0, "5": 0};
    for (const item of counts) {
        result[item.box_number] = item.count;
    }
    return result;
};

export const submitAnswer = (cardid, rating) => {
    return moodleCall('mod_leitbox_submit_answer', {
        cardid,
        rating
    });
};

// Served through Moodle's theme image handler, which adds the theme revision
// to the URL, so browsers load the new file after an update.
export const getLogoUrl = () => window.M.util.image_url('logo', 'mod_leitbox');

export const resetProgress = (instanceid) => {
    return moodleCall('mod_leitbox_reset_progress', {instanceid});
};
