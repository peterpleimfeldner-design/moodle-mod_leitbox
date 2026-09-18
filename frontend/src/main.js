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
 * Vue application entry point, mounted into the Moodle-rendered page.
 *
 * @module    mod_leitbox/frontend/main
 * @package   mod_leitbox
 * @copyright 2026 Peter Pleimfeldner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import { initApi } from './api'

// Look for the config injected by Moodle block
const rootElement = document.getElementById('v-app-mod-leitbox')

if (rootElement) {
    const config = JSON.parse(rootElement.getAttribute('data-config') || '{}')
    initApi(config)
    createApp(App).mount('#v-app-mod-leitbox')
} else {
    console.error("Mount point #v-app-mod-leitbox not found.");
}
