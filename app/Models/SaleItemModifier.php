<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SaleItem;

class SaleItemModifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_item_id',
        'modifier_id',
        'price',
    ];

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function modifier()
    {
        return $this->belongsTo(Modifier::class);
    }
}
