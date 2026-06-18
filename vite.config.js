import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/app/overrides/57-responsive-hardening.css',
                'resources/js/app.js',
                'resources/css/modules/home.css',
                'resources/css/app/pages/53-homepage-style-polish.css',
                'resources/css/modules/pricing.css',
                'resources/css/modules/contact.css',
'resources/css/modules/dashboard.css',
                'resources/css/modules/listings.css',
                'resources/css/modules/auth.css',
                'resources/css/modules/agent-directory.css',
                'resources/css/modules/admin-agent-profiles.css',
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
