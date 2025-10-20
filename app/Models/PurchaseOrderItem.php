<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Ingredient;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
    /**
     * Relasi ke GoodsReceiptItems
     */
    public function goodsReceiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class, 'purchase_order_item_id');
    }
    /**
     * Get total quantity yang sudah diterima
     */
    public function getTotalReceivedAttribute()
    {
        return $this->goodsReceiptItems()->sum('quantity_received');
    }
    /**
     * Get sisa quantity yang belum diterima
     */
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->total_received;
    }
    /**
     * Check apakah item sudah diterima semua
     */
    public function isFullyReceivedAttribute()
    {
        return $this->remaining_quantity <= 0;
    }
}
