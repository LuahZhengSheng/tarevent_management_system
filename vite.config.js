import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/forums/forum.css',
                'resources/css/forums/forum-create.css',
                'resources/css/forums/my-posts.css',
                'resources/css/forums/media-lightbox.css',
                'resources/js/app.js',
                'resources/js/forum.js',
                'resources/js/forum-create.js',
                'resources/js/my-posts.js',
                'resources/js/media-lightbox.js',
                'resources/js/post-feed.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
