import {
    defineConfig
} from 'vite';
import fs from 'fs-extra';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
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
