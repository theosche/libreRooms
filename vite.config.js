import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/contacts/contact-form.js',
                'resources/js/custom-fields/custom-field-form.js',
                'resources/js/owners/owner-form.js',
                'resources/js/reservations/reservation-form.js',
                'resources/js/rooms/room-form.js',
                'resources/js/system-settings/system-settings-form.js',
                'resources/js/config-test.js',
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
