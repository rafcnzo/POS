<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    use HasFactory;

    protected $table = 'goods_receipt_items';

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'quantity_received',
        'quantity_rejected',
        'notes',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:2',
        'quantity_rejected' => 'decimal:2',
    ];

    /**
     * Relasi ke GoodsReceipt (header penerimaan)
     */
    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    /**
     * Relasi ke PurchaseOrderItem (item dari PO)
     */
    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    /**
     * Relasi langsung ke Ingredient (via PurchaseOrderItem)
     */
    public function ingredient()
    {
        return $this->hasOneThrough(
            Ingredient::class,
            PurchaseOrderItem::class,
            'id', // Foreign key di purchase_order_items
            'id', // Foreign key di ingredients
            'purchase_order_item_id', // Local key di goods_receipt_items
            'ingredient_id' // Local key di purchase_order_items
        );
    }

    /**
     * Get total quantity yang benar-benar masuk stock (received - rejected)
     */
    public function getNetQuantityAttribute()
    {
        return $this->quantity_received - $this->quantity_rejected;
    }

    /**
     * Get persentase reject
     */
    public function getRejectPercentageAttribute()
    {
        if ($this->quantity_received == 0) {
            return 0;
        }

        return round(($this->quantity_rejected / $this->quantity_received) * 100, 2);
    }

    /**
     * Format quantity received untuk display
     */
    public function getFormattedQuantityReceivedAttribute()
    {
        return number_format((float) $this->quantity_received, 2, ',', '.');
    }

    /**
     * Format quantity rejected untuk display
     */
    public function getFormattedQuantityRejectedAttribute()
    {
        return number_format((float) $this->quantity_rejected, 2, ',', '.');
    }

    /**
     * Scope untuk filter berdasarkan goods receipt
     */
    public function scopeByGoodsReceipt($query, $goodsReceiptId)
    {
        return $query->where('goods_receipt_id', $goodsReceiptId);
    }

    /**
     * Scope untuk item dengan reject
     */
    public function scopeWithRejection($query)
    {
        return $query->where('quantity_rejected', '>', 0);
    }

    /**
     * Scope untuk item tanpa reject
     */
    public function scopeWithoutRejection($query)
    {
        return $query->where('quantity_rejected', 0);
    }

    /**
     * Scope untuk filter berdasarkan ingredient
     */
    public function scopeByIngredient($query, $ingredientId)
    {
        return $query->whereHas('purchaseOrderItem', function ($q) use ($ingredientId) {
            $q->where('ingredient_id', $ingredientId);
        });
    }

    /**
     * Check apakah ada barang yang direject
     */
    public function hasRejection()
    {
        return $this->quantity_rejected > 0;
    }

    /**
     * Get status penerimaan
     */
    public function getStatusAttribute()
    {
        if ($this->quantity_rejected == 0) {
            return 'Full Acceptance';
        }

        if ($this->quantity_received == $this->quantity_rejected) {
            return 'Full Rejection';
        }

        return 'Partial Acceptance';
    }
}
