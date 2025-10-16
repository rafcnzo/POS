import './bootstrap'; // Pastikan ini mengimpor file bootstrap.js jika ada

// Impor jQuery dan tetapkan ke global
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

// Impor Bootstrap dan tetapkan ke global
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Impor Chart.js dan tetapkan ke global
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Impor dependensi lain
import 'metismenu';
import toastr from 'toastr';
import Swal from 'sweetalert2';
import Alpine from 'alpinejs';
import 'select2';

// Tetapkan ke global
window.toastr = toastr;
window.Swal = Swal;
window.Alpine = Alpine;
import moment from 'moment';
window.moment = moment;

// Mulai Alpine
Alpine.start();

// Pastikan DOM siap sebelum inisialisasi
document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi metisMenu
    if (window.jQuery && $.fn.metisMenu) {
        $('#menu').metisMenu();
    }

    // Set sidebar default ke toggled (hide)
    if (!$('.wrapper').hasClass('toggled')) {
        $('.wrapper').addClass('toggled');
    }

    // Toggle sidebar
    $('#sidebar-toggle-btn').on('click', function () {
        $('.wrapper').toggleClass('toggled');
        const isToggled = $('.wrapper').hasClass('toggled');
        $(this).find('i').toggleClass('bi-chevron-left bi-chevron-right');
    });

    $('.mobile-toggle-menu').on('click', function () {
        $('.wrapper').toggleClass('toggled');
    });

    // Inisialisasi select2
    if (window.jQuery && $.fn.select2) {
        $('.select2').select2({
            theme: "bootstrap-5" // Terapkan tema Bootstrap 5
        });
    }
});