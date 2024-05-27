import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';


export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
    build: {
        outDir: 'public/build',
        // rollupOptions: {
        //   output: {
        //     manualChunks: {
        //       vendor: ['react', 'react-dom'],
        //     },
        //   },
        // },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
        },
    },
    resolve: {
        alias: {
            '@': '/resources/js',
            '@css': '/resources/css',
            // '@uppy/core/dist/style.css': resolve(__dirname, 'node_modules/@uppy/core/dist/style.css'),
            // '@uppy/drag-drop/dist/style.css': resolve(__dirname, 'node_modules/@uppy/drag-drop/dist/style.css'),
        },
    }
});
