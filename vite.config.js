import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/theme.css',
                'resources/css/forum.css',
                'resources/css/forum-create.css',
                'resources/css/media-lightbox.css',
                'resources/js/app.js',
                'resources/js/forum.js',
                'resources/js/forum-create.js',
                'resources/js/media-lightbox.js',
                'resources/js/forum-edit.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
