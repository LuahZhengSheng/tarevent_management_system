import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/forums/forum.css',
                'resources/css/forum-create.css',
                'resources/css/my-posts.css',
                'resources/css/media-lightbox.css',
                'resources/js/app.js',
                'resources/js/forum.js',
                'resources/js/forum-create.js',
                'resources/css/my-posts.js',
                'resources/js/media-lightbox.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
