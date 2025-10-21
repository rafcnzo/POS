<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    // Definisikan tipe (opsional tapi bagus)
    const TYPE_TEMPO      = 'tempo';
    const TYPE_PETTY_CASH = 'petty_cash';

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'address',
        'type', // <-- TAMBAHKAN INI
        'credit_limit',
        'jatuh_tempo1', // <-- BARU
        'jatuh_tempo2', // <-- BARU
    ];

    // Tambahkan casts agar otomatis jadi objek Carbon/Date
    protected $casts = [
        'credit_limit' => 'decimal:2',
        'jatuh_tempo1' => 'date', // <-- BARU
        'jatuh_tempo2' => 'date', // <-- BARU
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // BARU: Relasi ke Pembayaran Supplier
    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
