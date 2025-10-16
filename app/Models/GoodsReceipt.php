<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\PurchaseOrder;
use App\Models\User;

class GoodsReceipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'user_id',
        'receipt_date',
        'proof_document',
        'notes',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
