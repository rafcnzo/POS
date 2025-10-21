import './bootstrap';

// Impor jQuery dan tetapkan ke global PERTAMA
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

// Impor Bootstrap
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// PENTING: Import Select2 dengan cara yang benar untuk Vite
import select2 from 'select2';
// Inisialisasi Select2 dengan jQuery
select2($);

// Atau gunakan cara ini jika yang atas tidak work:
// import 'select2';
// import 'select2/dist/js/select2.full.js';

// Impor Chart.js
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Impor dependensi lain
import 'metismenu';
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';
import Swal from 'sweetalert2';
import Alpine from 'alpinejs';
import moment from 'moment';

// 2. Impor file JavaScript SimpleBar
import SimpleBar from 'simplebar';

import * as XLSX from 'xlsx';


// Tetapkan ke global
window.toastr = toastr;
window.Swal = Swal;
window.Alpine = Alpine;
window.moment = moment;
window.SimpleBar = SimpleBar;
window.XLSX = XLSX;

// Mulai Alpine
Alpine.start();

document.addEventListener('DOMContentLoaded', function () {
    console.log('App.js loaded');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Select2 available:', typeof $.fn.select2);
    console.log('Select2 function:', $.fn.select2);

    // Inisialisasi metisMenu
    if ($.fn.metisMenu) {
        $('#menu').metisMenu();
    }

    // Set sidebar default ke toggled (hide)
    if (!$('.wrapper').hasClass('toggled')) {
        $('.wrapper').addClass('toggled');
    }

    // Toggle sidebar
    document.querySelectorAll('#sidebar-toggle-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.wrapper').forEach(function(wrapper) {
                wrapper.classList.toggle('toggled');
            });
            this.querySelectorAll('i').forEach(function(icon) {
                icon.classList.toggle('bi-chevron-left');
                icon.classList.toggle('bi-chevron-right');
            });
        });
    });

    $('.mobile-toggle-menu').on('click', function () {
        $('.wrapper').toggleClass('toggled');
    });

    // Jika sidebar dalam keadaan tersembunyi (toggled) dan li diklik, langsung buka
    $('#menu').on('click', 'li.mb-1 > a', function(e) {
        // Cari wrapper
        var $wrapper = $('.wrapper');
        if ($wrapper.hasClass('toggled')) {
            // Keluarkan toggled (buka sidebar)
            $wrapper.removeClass('toggled');
        }
    });

    // Inisialisasi select2 untuk elemen yang memiliki class select2
    if ($.fn.select2) {
        $('.select2:not([data-manual-init])').each(function() {
            $(this).select2({
                theme: "bootstrap-5",
                width: '100%'
            });
        });
        console.log('Select2 initialized for .select2 elements');
    } else {
        console.error('Select2 is NOT available!');
    }

    // Dispatch custom event bahwa libraries sudah ready
    window.dispatchEvent(new CustomEvent('app-libraries-loaded'));
    console.log('Libraries loaded event dispatched');
});
