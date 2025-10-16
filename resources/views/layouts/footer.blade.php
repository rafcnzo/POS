@php
    $storeName = \App\Models\Setting::where('key', 'store_name')->value('value') ?? 'Restoran Sukses Maju Jaya';
    $year = date('Y');
@endphp
<footer class="page-footer">
    <p class="mb-0">Copyright © {{ $year }}. {{ $storeName }}</p>
</footer>
