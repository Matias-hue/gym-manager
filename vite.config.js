import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/sidebar.css',
                'resources/js/app.js',
                'resources/js/sidebar.js',
                'resources/js/members.js',
                'resources/js/payments/create.js',
                'resources/css/attendance.css',
                'resources/js/attendance.js',
                'resources/js/form-guard.js',
                'resources/js/auth.js',
                'resources/js/members/bulk.js',
                'resources/css/reports.css',
                'resources/js/reports.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});