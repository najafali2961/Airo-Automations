import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    server: {
        cors: {
            origin: 'https://automation.omni-sync.com',
            methods: 'GET,POST,HEAD,PUT,PATCH,DELETE,OPTIONS',
            credentials: true,
        },
    },
});
