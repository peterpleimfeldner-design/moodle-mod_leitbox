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
 * Thin wrapper around Moodle's core AJAX web service endpoint.
 *
 * @module    mod_leitbox/frontend/api
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import axios from 'axios';

let config = {};

export const initApi = (cfg) => {
    config = cfg;
};

export const getConfig = () => config;

export const moodleCall = async (methodname, args) => {
    const url = `${config.wwwroot}/lib/ajax/service.php?sesskey=${config.sesskey}`;
    const payload = [{
        index: 0,
        methodname,
        args
    }];
    const response = await axios.post(url, payload);
    const data = response.data[0];
    if (data.error) {
        throw new Error(data.exception);
    }
    return data.data;
};

export const getCardsByBox = (boxnumber) => {
    return moodleCall('mod_leitbox_get_cards_by_box', {
        instanceid: config.instanceid,
        boxnumber
    });
};

export const getBoxCounts = async () => {
    const counts = await moodleCall('mod_leitbox_get_box_counts', {
        instanceid: config.instanceid
    });

    const result = { 0: 0, 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
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

export const getLogoUrl = () => {
    return `${config.wwwroot}/mod/leitbox/pix/logo.png`;
};

export const resetProgress = (instanceid) => {
    return moodleCall('mod_leitbox_reset_progress', { instanceid });
};
