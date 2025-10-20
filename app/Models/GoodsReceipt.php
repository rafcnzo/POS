<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    use HasFactory;
    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'receipt_date',
        'user_id',
        'proof_document',
    ];
    protected $casts = [
        'receipt_date' => 'date',
    ];
    /**
     * Relasi ke PurchaseOrder
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
    /**
     * Relasi ke User (yang input penerimaan)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Relasi ke GoodsReceiptItems (detail items yang diterima)
     */
    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class, 'goods_receipt_id');
    }
    /**
     * Relasi ke Supplier via PurchaseOrder
     */
    public function supplier()
    {
        return $this->hasOneThrough(
            Supplier::class,
            PurchaseOrder::class,
            'id',
            'id',
            'purchase_order_id',
            'supplier_id'
        );
    }
    /**
     * Get total items yang diterima
     */
    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    /**
     * Get total quantity diterima (sum semua items)
     */
    public function getTotalQuantityReceivedAttribute()
    {
        return $this->items()->sum('quantity_received');
    }
    /**
     * Get total quantity direject
     */
    public function getTotalQuantityRejectedAttribute()
    {
        return $this->items()->sum('quantity_rejected');
    }
    /**
     * Check apakah ada item yang direject
     */
    public function hasRejectedItemsAttribute()
    {
        return $this->items()->where('quantity_rejected', '>', 0)->exists();
    }
}
