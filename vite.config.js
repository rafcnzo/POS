import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173, // bisa disesuaikan
        hmr: {
            host: '192.168.66.200', // <-- ganti dengan IP lokal kamu
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'node_modules/toastr/build/toastr.min.css',
                'node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css',
                'resources/css/admin.css'
            ],
            refresh: true,
            build: {
                rollupOptions: {
                    output: {
                        globals: {
                            jquery: '$'
                        }
                    }
                }
            },
            // Tambahkan resolve untuk mendukung dependensi
            resolve: {
                alias: {
                    'jquery': 'jquery/dist/jquery'
                }
            }
        }),
    ],
});
