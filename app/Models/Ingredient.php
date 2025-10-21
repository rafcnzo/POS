<?php
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    const CATEGORY_KITCHEN = 'kitchen';
    const CATEGORY_BAR = 'bar';

    protected $fillable = [
        'name',
        'unit',
        'cost_price',
        'supplier_id',
        'stock',
        'category',
        'minimum_stock',
    ];

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'ingredient_menu_item')
            ->withPivot('quantity');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function storeRequestItems()
    {
        return $this->morphMany(StoreRequestItem::class, 'itemable');
    }
    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getAverageCost(): float
    {
        static $cache = [];
        if (isset($cache[$this->id])) {
            return $cache[$this->id];
        }

        $result = $this->purchaseOrderItems()
            ->select(DB::raw('SUM(price * quantity) as total_value, SUM(quantity) as total_quantity'))
            ->first();
        if (!$result || $result->total_quantity == 0) {
            return (float) $this->cost_price;
        }

        $averageCost = $result->total_value / $result->total_quantity;
        $cache[$this->id] = (float) $averageCost;

        return (float) $averageCost;
    }
}
