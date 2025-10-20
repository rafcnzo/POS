<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'table_number',
        'pax',
        'reservation_time',
        'deposit_amount',
        'contact_number',
        'notes',
        'status',
        'sale_id', // Penting untuk link ke transaksi akhir
        'user_id',
    ];

    protected $casts = [
        'reservation_time' => 'datetime',
        'deposit_amount'   => 'decimal:2',
    ];

    /**
     * Relasi ke Sale (transaksi akhir yang menggunakan deposit ini)
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relasi ke User (yang membuat reservasi)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Payment (record payment yang dibuat dari deposit ini)
     * Sebuah reservasi HANYA menghasilkan SATU payment record (jika dipakai)
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
