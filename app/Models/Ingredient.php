<?php
namespace App\Models;

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
}
