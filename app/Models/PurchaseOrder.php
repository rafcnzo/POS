<?php
namespace App\Models;

use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    const PAYMENT_CASH  = 'petty_cash';
    const PAYMENT_TEMPO = 'tempo';

    const STATUS_BELUM_DIBAYAR = 'belum_dibayar';
    const STATUS_SEBAGIAN      = 'sebagian_dibayar';
    const STATUS_LUNAS         = 'lunas';

    protected $casts = [
        'order_date'             => 'date',
        'expected_delivery_date' => 'date',
        'total_amount'           => 'decimal:2',
        'paid_amount'            => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function storeRequest()
    {
        return $this->belongsTo(StoreRequest::class);
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class);
}

    // BARU: Accessor untuk Sisa Tagihan
    protected function outstandingAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => max(0, $this->total_amount - $this->paid_amount),
        );
    }
}
