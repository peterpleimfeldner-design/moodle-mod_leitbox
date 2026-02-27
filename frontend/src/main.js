import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import { initApi } from './api'

// Look for the config injected by Moodle block
const rootElement = document.getElementById('v-app-mod-recall')

if (rootElement) {
    const config = JSON.parse(rootElement.getAttribute('data-config') || '{}')
    initApi(config)
    createApp(App).mount('#v-app-mod-recall')
} else {
    console.error("Mount point #v-app-mod-recall not found.");
}
