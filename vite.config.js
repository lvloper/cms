import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin'
import livewire from '@defstudio/vite-livewire-plugin'; // <-- import

// import livewire from '@defstudio/vite-livewire-plugin'; // import plugin

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/hot-reload.js',
            ],
            refresh: [
                'resources/views/**',
                'resources/views/blocks/**',
            ],
        }),
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
    server: {
        watch: {
            ignored: ['**/vendor/**']
        }
    }
});
