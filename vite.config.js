import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/css/modules/home.css',
                'resources/css/modules/pricing.css',
                'resources/css/modules/contact.css',
                'resources/css/modules/dashboard.css',
                'resources/css/modules/listings.css',
                'resources/css/modules/auth.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
