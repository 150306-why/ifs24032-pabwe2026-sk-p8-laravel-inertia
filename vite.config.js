import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { fileURLToPath } from 'node:url';
import { readdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { brotliCompressSync, gzipSync, constants } from 'node:zlib';

/**
 * Membuat salinan terkompresi (.br dan .gz) untuk aset build. `server.php` mengirim salinan ini
 * (Content-Encoding: br/gzip) karena `php artisan serve` tidak mengompres respons sendiri.
 */
function precompress() {
    let outDir;
    return {
        name: 'precompress-assets',
        apply: 'build',
        configResolved(config) {
            outDir = config.build.outDir;
        },
        closeBundle() {
            const dir = join(outDir, 'assets');
            for (const file of readdirSync(dir)) {
                if (!/\.(js|mjs|css|map|svg|json)$/.test(file)) continue;
                const source = readFileSync(join(dir, file));
                writeFileSync(join(dir, `${file}.gz`), gzipSync(source, { level: constants.Z_BEST_COMPRESSION }));
                writeFileSync(
                    join(dir, `${file}.br`),
                    brotliCompressSync(source, {
                        params: {
                            [constants.BROTLI_PARAM_QUALITY]: constants.BROTLI_MAX_QUALITY,
                            [constants.BROTLI_PARAM_SIZE_HINT]: source.length,
                        },
                    }),
                );
            }
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({ input: ['resources/css/app.css', 'resources/js/app.jsx'], refresh: true }),
        react(),
        tailwindcss(),
        precompress(),
    ],
    build: {
        // Source map untuk debugging produksi (audit Lighthouse "Missing source maps").
        // sourcesContent tidak disertakan agar file .map kecil dan cepat diambil Lighthouse.
        sourcemap: true,
        target: 'es2022',
        rollupOptions: {
            output: {
                sourcemapExcludeSources: true,
                // Library dipecah per paket: tiap file lebih kecil, cache lebih awet, dan
                // porsi kode yang belum terpakai per file tetap di bawah ambang Lighthouse.
                manualChunks(id) {
                    if (!id.includes('node_modules')) return undefined;
                    if (id.includes('/react-dom/')) return 'vendor-react-dom';
                    if (id.includes('/@inertiajs/core/')) return 'vendor-inertia-core';
                    if (id.includes('/@inertiajs/react/')) return 'vendor-inertia-react';
                    if (id.includes('/es-toolkit/') || id.includes('/laravel-precognition/')) return 'vendor-utils';
                    if (id.includes('/react/') || id.includes('/scheduler/')) return 'vendor-react';
                    return undefined;
                },
            },
        },
    },
    resolve: {
        alias: { '@': fileURLToPath(new URL('./resources/js', import.meta.url)) },
    },
});
