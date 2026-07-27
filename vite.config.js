import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin'
import livewire from '@defstudio/vite-livewire-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/app.jsx',
                'resources/js/hot-reload.js',
            ],
            refresh: [
                'resources/views/**',
                'resources/views/blocks/**',
                'resources/js/**',
            ],
        }),
        react(),
        livewire({
            refresh: [
                ...refreshPaths,
                'resources/views/**',
                'routes/**',
                'admin/**',
                'resources/views/vendor/filament-forms/**',
            ]
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        watch: {
            ignored: ['**/vendor/**']
        }
    }
});
