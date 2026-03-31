import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        hmr: {
            overlay: false
        },
        // Windows-specific configuration to reduce warnings
        fs: {
            strict: false
        }
    },
    // Suppress forking warnings on Windows
    define: {
        global: 'globalThis',
    },
    optimizeDeps: {
        force: true
    }
});
