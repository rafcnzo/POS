<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Sale;
use App\Models\MenuItem;
use App\Models\SaleItemModifier;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = ['sale_id', 'menu_item_id', 'quantity', 'price', 'subtotal','notes',];

    // protected $keyType   = 'string';
    // public $incrementing = false;

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
    public function selectedModifiers()
    {
        return $this->hasMany(SaleItemModifier::class);
    }
}
