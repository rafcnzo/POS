import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'node_modules/toastr/build/toastr.min.css',
                'node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css',
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
