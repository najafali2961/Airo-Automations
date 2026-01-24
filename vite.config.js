import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    return {
        plugins: [
            tailwindcss(),
            laravel({
                input: ['resources/js/app.jsx'], // Remove resources/css/app.css from here
                refresh: true,
            }),
            react()

        ],
        server: {
            cors: {
                origin: env.APP_URL,
                methods: 'GET,POST,HEAD,PUT,PATCH,DELETE,OPTIONS',
                credentials: true,
            },
        },
    };
});
