import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { fileURLToPath } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({ input: ['resources/css/app.css', 'resources/js/app.jsx'], refresh: true }),
        react(),
        tailwindcss(),
    ],
    build: {
        // Source map untuk debugging produksi (audit Lighthouse "Missing source maps").
        sourcemap: true,
        target: 'es2022',
    },
    resolve: {
        alias: { '@': fileURLToPath(new URL('./resources/js', import.meta.url)) },
    },
});
