import './bootstrap';
import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import Home from './components/Home.vue'
import Download from './components/Download.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: Home
        },
        {
            path: '/download/:id',
            name: 'download',
            component: Download
        }
    ]
})

const app = createApp(App)
app.use(router)
app.mount('#app')
