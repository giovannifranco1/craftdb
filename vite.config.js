import {
    defineConfig
} from 'vite';
import path from 'node:path';
import fs from 'fs-extra';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    resolve: {
        alias: {
            '@images': path.resolve(__dirname, 'resources/images'),
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/canvas.css',
                'resources/css/canvas-public.css',
                'resources/js/canvas/main.js',
                'resources/css/filepond.css',
                'resources/css/landing-page.css'
            ],
            refresh: true,
        }),
        {
            name: 'copy-images',
            apply: 'build',
            buildEnd() {
              fs.copySync('resources/images', 'public/images', { overwrite: true });
            },
        },
        tailwindcss(),
    ],
    server: {
        cors: true,
    },
});
